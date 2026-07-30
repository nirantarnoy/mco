<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\Response;
use yii\helpers\Html;
use backend\models\TempInvoice;
use backend\models\TempInvoiceLine;

use backend\models\OcrPattern;

/**
 * OcrController handles Google Vision OCR tasks.
 */
class OcrController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Process the OCR request.
     * @return array|string|Response
     */
    public function actionProcess()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        ini_set('memory_limit', '512M');
        set_time_limit(120);
        
        $file = UploadedFile::getInstanceByName('ocr_file');
        if (!$file) {
            return [
                'success' => false,
                'message' => 'กรุณาแนบไฟล์รูปภาพ'
            ];
        }

        // Validate file type
        if (!in_array($file->extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
            return [
                'success' => false,
                'message' => 'รองรับเฉพาะไฟล์ JPG, JPEG, PNG และ PDF'
            ];
        }

        // Save file temporarily
        $tempDir = Yii::getAlias('@runtime/ocr');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $filePath = $tempDir .  '/' . time() . '_' . $file->baseName . '.' . $file->extension;
        
        if ($file->saveAs($filePath)) {
            try {
                $service = Yii::$app->googleVision;
                $result = $service->scanText($filePath);
                
                // OCR result clean up
                unlink($filePath);

                // Save to Database
                $saveResult = $this->saveToTempInvoice($result);

                if ($saveResult['success']) {
                    return [
                        'success' => true,
                        'fullText' => $result['fullText'],
                        'details' => $result['details'],
                        'temp_invoice_id' => $saveResult['model']->id,
                        'message' => 'สแกนสำเร็จและบันทึกข้อมูลเข้าฐานข้อมูลชั่วคราวแล้ว'
                    ];
                } else {
                    return [
                        'success' => true, // Still success OCR but fail save
                        'fullText' => $result['fullText'],
                        'details' => $result['details'],
                        'message' => 'สแกนสำเร็จ แต่ไม่สามารถบันทึกข้อมูลได้: ' . $saveResult['error']
                    ];
                }

            } catch (\Exception $e) {
                if (file_exists($filePath)) unlink($filePath);
                return [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'ไม่สามารถบันทึกไฟล์ได้'
        ];
    }

    /**
     * Save OCR results to temp_invoice tables with robust multi-line parsing
     */
    protected function saveToTempInvoice($ocrResult)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $fullText = $ocrResult['fullText'];
            $lines_raw = explode("\n", $fullText);
            
            $model = new TempInvoice();
            $model->raw_text = $fullText;
            $model->company_id = Yii::$app->session->get('company_id');
            $model->status = TempInvoice::STATUS_PENDING;
            $model->invoice_date = date('Y-m-d'); 

            // 1. Detect Tax IDs (13 digits continuous or with hyphens/spaces e.g. 0-1055-58123-45-6)
            $taxIdFound = null;
            if (preg_match_all('/(?:TAX\s*ID|เลขประจำตัวผู้เสียภาษี|ผู้เสียภาษี|Tax\s*No\.?)?\s*[:.]?\s*(\d[\s\-]?\d{4}[\s\-]?\d{5}[\s\-]?\d{2}[\s\-]?\d)/iu', $fullText, $taxMatches)) {
                foreach ($taxMatches[1] as $rawTax) {
                    $cleanedTax = preg_replace('/\D/', '', $rawTax);
                    if (strlen($cleanedTax) === 13) {
                        $taxIdFound = $cleanedTax;
                        break;
                    }
                }
            }
            if (!$taxIdFound) {
                if (preg_match_all('/\d{13}/', $fullText, $plainMatches)) {
                    $taxIdFound = $plainMatches[0][0];
                }
            }
            if ($taxIdFound) {
                $model->customer_tax_id = $taxIdFound;
            }
            
            // 2. Look for Pattern based on Tax ID
            $pattern = null;
            if ($model->customer_tax_id) {
                $pattern = OcrPattern::findOne(['tax_id' => $model->customer_tax_id, 'status' => 1]);
                if ($pattern) {
                    $model->vendor_name = $pattern->name;
                }
            }

            // 3. Extract Invoice Number (Global + Pattern)
            $regexInvoice = $pattern && $pattern->regex_invoice_no ? $pattern->regex_invoice_no : '/(?:เลขที่ใบกำกับภาษี|เลขที่ใบเสร็จ|เลขที่เอกสาร|เลขที่|TAX\s*INVOICE\s*NO|TAX\s*INV\s*NO|INV\s*NO|INVOICE\s*NO|POS\s*NO|DOCUMENT\s*NO|RECEIPT\s*NO|BILL\s*NO|DOC\s*NO|No\.?|Inv\s*#)\s*[:.]?\s*([A-Z0-9\-\/]{3,30})/iu';
            if (@preg_match($regexInvoice, $fullText, $matches)) {
                $model->invoice_number = isset($matches[1]) ? trim($matches[1]) : trim($matches[0]);
            } else {
                // Secondary check for patterns like S001-IV-12345 or IV-2026-001
                if (preg_match('/([A-Z0-9]{2,}[\-\/][A-Z0-9\-\/]{4,20})/', $fullText, $matches)) {
                    $model->invoice_number = trim($matches[1]);
                }
            }

            // 4. Extract Date with B.E. to C.E. conversion & Multi-language support
            $regexDate = $pattern && $pattern->regex_date ? $pattern->regex_date : null;
            $dateParsed = false;
            
            if ($regexDate && @preg_match($regexDate, $fullText, $matches)) {
                $dateStr = isset($matches[1]) ? trim($matches[1]) : trim($matches[0]);
                $parsed = $this->parseDateString($dateStr);
                if ($parsed) {
                    $model->invoice_date = $parsed;
                    $dateParsed = true;
                }
            }
            
            if (!$dateParsed) {
                $datePatterns = [
                    '/(?:วันที่|Date)\s*[:.]?\s*(\d{1,2})[\/\.-](\d{1,2})[\/\.-](\d{2,4})/iu',
                    '/(\d{1,2})\s+(ม\.ค\.|ก\.พ\.|มี\.ค\.|เม\.ย\.|พ\.ค\.|มิ\.ย\.|ก\.ค\.|ส\.ค\.|ก\.ย\.|ต\.ค\.|พ\.ย\.|ธ\.ค\.|มกราคม|กุมภาพันธ์|มีนาคม|เมษายน|พฤษภาคม|มิถุนายน|กรกฎาคม|สิงหาคม|กันยายน|ตูลายน|พฤศจิกายน|ธันวาคม|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{2,4})/iu',
                    '/(\d{4})[\/\.-](\d{1,2})[\/\.-](\d{1,2})/'
                ];
                foreach ($datePatterns as $dp) {
                    if (preg_match($dp, $fullText, $m)) {
                        $parsed = $this->parseDateMatch($m);
                        if ($parsed) {
                            $model->invoice_date = $parsed;
                            break;
                        }
                    }
                }
            }

            // 5. Extract Totals, VAT, Subtotal
            $regexTotal = $pattern && $pattern->regex_total ? $pattern->regex_total : '/(?:จำนวนเงินรวมทั้งสิ้น|จำนวนเงินรวมภาษี|รวมเงินทั้งสิ้น|ยอดรวมสุทธิ|จำนวนเงินรวม|ยอดโอน|ยอดชำระ|Grand\s*Total|Total\s*Amount|Total\s*Due|Amount\s*Due|Net\s*Total|TOTAL|Net\s*Amount).{0,50}?([0-9,]+\.[0-9]{2})/is';
            
            if (@preg_match($regexTotal, $fullText, $m)) {
                $model->total_amount = (float)str_replace(',', '', $m[1]);
            } 
            
            if (preg_match('/(?:มูลค่าสินค้า|มูลค่าบริการ|รวมเป็นเงิน|Subtotal|Sub\s*Total|Net\s*Amount).{0,50}?([0-9,]+\.[0-9]{2})/is', $fullText, $m)) {
                $model->subtotal = (float)str_replace(',', '', $m[1]);
            }

            if (preg_match('/(?:ภาษีมูลค่าเพิ่ม|VAT\s*7%|VAT|Value\s*Added\s*Tax).{0,30}?([0-9,]+\.[0-9]{2})/is', $fullText, $m)) {
                $model->vat_amount = (float)str_replace(',', '', $m[1]);
            }

            // Fallback: If total_amount still 0, look at the last 5 numbers and pick the largest 
            if ($model->total_amount == 0) {
                 if (preg_match_all('/([0-9,]+\.[0-9]{2})/', $fullText, $allMatches)) {
                    $nums = array_map(function($n) { return (float)str_replace(',', '', $n); }, $allMatches[0]);
                    $lastFew = array_slice($nums, -5);
                    if (!empty($lastFew)) {
                        $model->total_amount = max($lastFew);
                    }
                }
            }

            // Cross-validation logic: If total > 0 and subtotal is 0
            if ($model->total_amount > 0 && $model->subtotal == 0) {
                if ($model->vat_amount > 0) {
                    $model->subtotal = round($model->total_amount - $model->vat_amount, 2);
                } else {
                    $model->subtotal = round($model->total_amount / 1.07, 2);
                    $model->vat_amount = round($model->total_amount - $model->subtotal, 2);
                }
            }

            if ($model->save()) {
                // Use Pattern-specific Regex if available
                $regexStart = $pattern && $pattern->regex_item_start ? $pattern->regex_item_start : '/^(\d{1,2})\s+([A-Z0-9-]{4,20})\s+(.+)$/u';
                $strategy = $pattern && $pattern->parsing_strategy ? $pattern->parsing_strategy : 'block';

                // Prepare rows from spatial data if available
                $logicalLines = $this->reconstructRows($ocrResult['details'] ?? []);
                
                $items = [];

                if ($strategy == 'collector') {
                    $items = $this->runCollector($logicalLines, $model);
                } else {
                    $items = $this->runBlockStrategy($logicalLines, $model, $regexStart, $pattern);
                    // Automatic Fallback: If block strategy found nothing, try collector
                    if (empty($items) && $model->total_amount > 0) {
                        $items = $this->runCollector($logicalLines, $model);
                    }
                }

                // Save items with Final Calculation Check
                foreach ($items as $item) {
                    if ($item['amount'] == 0 && $item['price'] > 0) {
                        $item['amount'] = $item['price'];
                        if ($item['qty'] > 1) {
                             $item['price'] = round($item['amount'] / $item['qty'], 2);
                        }
                    } elseif ($item['amount'] > 0 && $item['amount'] < $item['price']) {
                        $tmp = $item['price'];
                        $item['price'] = $item['amount'];
                        $item['amount'] = $tmp;
                    }

                    if ($item['amount'] > 0 || !empty($item['desc'])) {
                        $tl = new TempInvoiceLine();
                        $tl->temp_invoice_id = $model->id;
                        $tl->product_code = $item['code'] ?? '';
                        $tl->description = trim($item['desc']);
                        $tl->quantity = $item['qty'];
                        $tl->unit = $item['unit'] ?? 'รายการ';
                        $tl->unit_price = $item['price'];
                        $tl->amount = $item['amount'];
                        $tl->save();
                    }
                }

                // Global fallback for total-only invoices
                if (TempInvoiceLine::find()->where(['temp_invoice_id' => $model->id])->count() == 0 && $model->total_amount > 0) {
                    $line = new TempInvoiceLine();
                    $line->temp_invoice_id = $model->id;
                    $line->description = 'รายการจาก OCR';
                    $line->amount = $model->total_amount;
                    $line->quantity = 1;
                    $line->unit_price = $model->total_amount;
                    $line->save();
                }

                $transaction->commit();
                return ['success' => true, 'model' => $model, 'message' => 'สแกนสำเร็จและบันทึกข้อมูลเรียบร้อยแล้ว'];
            } else {
                $errorMsg = "";
                foreach ($model->getErrors() as $attribute => $errors) {
                    $errorMsg .= $attribute . ": " . implode(", ", $errors) . "; ";
                }
                $transaction->rollBack();
                return ['success' => false, 'error' => $errorMsg];
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('OCR Save Error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Group OCR words into rows based on their Y-coordinates with dynamic thresholding
     */
    protected function reconstructRows($words)
    {
        if (empty($words)) return [];

        // Compute dynamic Y-threshold based on average word bounding box height
        $heights = [];
        foreach ($words as $word) {
            $vertices = $word['boundingPoly']['vertices'] ?? [];
            if (count($vertices) >= 4) {
                $yMin = min(array_column($vertices, 'y'));
                $yMax = max(array_column($vertices, 'y'));
                $h = $yMax - $yMin;
                if ($h > 0 && $h < 500) $heights[] = $h;
            }
        }

        $yThreshold = 10;
        if (!empty($heights)) {
            $avgHeight = array_sum($heights) / count($heights);
            $yThreshold = max(6, (int)round($avgHeight * 0.45));
        }

        // Sort words by Y-coordinate of top-left vertex
        usort($words, function($a, $b) {
            $ay = $a['boundingPoly']['vertices'][0]['y'] ?? 0;
            $by = $b['boundingPoly']['vertices'][0]['y'] ?? 0;
            return $ay <=> $by;
        });

        $rows = [];
        $currentRow = [];
        $lastY = -1;

        foreach ($words as $word) {
            $y = $word['boundingPoly']['vertices'][0]['y'] ?? 0;
            
            if ($lastY == -1 || abs($y - $lastY) <= $yThreshold) {
                $currentRow[] = $word;
            } else {
                $rows[] = $this->sortRowByX($currentRow);
                $currentRow = [$word];
            }
            $lastY = $y;
        }
        if (!empty($currentRow)) {
            $rows[] = $this->sortRowByX($currentRow);
        }

        return array_map(function($rowWords) {
            return implode(' ', array_column($rowWords, 'description'));
        }, $rows);
    }

    protected function sortRowByX($rowWords)
    {
        usort($rowWords, function($a, $b) {
            $ax = $a['boundingPoly']['vertices'][0]['x'] ?? 0;
            $bx = $b['boundingPoly']['vertices'][0]['x'] ?? 0;
            return $ax <=> $bx;
        });
        return $rowWords;
    }

    /**
     * Parse date string DD/MM/YYYY
     */
    protected function parseDateString($dateStr)
    {
        $parts = preg_split('/[\/\.-]/', $dateStr);
        if (count($parts) == 3) {
            $d = (int)$parts[0];
            $m = (int)$parts[1];
            $y = (int)$parts[2];
            if ($y < 100) $y += 2000;
            if ($y > 2400) $y -= 543;
            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        }
        return null;
    }

    /**
     * Parse regex date match
     */
    protected function parseDateMatch($m)
    {
        if (count($m) >= 4) {
            if (strlen($m[1]) == 4) { // YYYY-MM-DD
                return sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]);
            }
            $d = (int)$m[1];
            $monthStr = $m[2];
            $y = (int)$m[3];

            $months = [
                'ม.ค.' => 1, 'ก.พ.' => 2, 'มี.ค.' => 3, 'เม.ย.' => 4, 'พ.ค.' => 5, 'มิ.ย.' => 6,
                'ก.ค.' => 7, 'ส.ค.' => 8, 'ก.ย.' => 9, 'ต.ค.' => 10, 'พ.ย.' => 11, 'ธ.ค.' => 12,
                'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'มีนาคม' => 3, 'เมษายน' => 4, 'พฤษภาคม' => 5, 'มิถุนายน' => 6,
                'กรกฎาคม' => 7, 'สิงหาคม' => 8, 'กันยายน' => 9, 'ตูลายน' => 10, 'พฤศจิกายน' => 11, 'ธันวาคม' => 12,
                'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'may' => 5, 'jun' => 6,
                'jul' => 7, 'aug' => 8, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12
            ];

            $monthLower = mb_strtolower($monthStr, 'UTF-8');
            $mNum = is_numeric($monthStr) ? (int)$monthStr : ($months[$monthLower] ?? 1);

            if ($y < 100) $y += 2000;
            if ($y > 2400) $y -= 543;
            return sprintf('%04d-%02d-%02d', $y, $mNum, $d);
        }
        return null;
    }

    /**
     * Strategy for well-structured multi-line items
     */
    protected function runBlockStrategy($logicalLines, $model, $regexStart, $pattern)
    {
        $items = [];
        $currentItem = null;

        foreach ($logicalLines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Skip table header lines
            if (preg_match('/^(ลำดับ|รายการ|รายละเอียด|จำนวน|ราคา|หน่วย|จำนวนเงิน|No\.|Description|Qty|Price|Amount|Unit)$/iu', $line)) {
                continue;
            }

            if (@preg_match($regexStart, $line, $m)) {
                if ($currentItem) $items[] = $currentItem;
                $currentItem = [
                    'code' => $m[2] ?? '',
                    'desc' => $m[3] ?? $m[1],
                    'qty' => 1,
                    'unit' => 'รายการ',
                    'price' => 0,
                    'amount' => 0
                ];
                continue;
            } 
            elseif (!$pattern && preg_match('/^(\d{1,2})\s+((?!รวม|ภาษี|VAT|Tel|ID)[ก-ฮA-Z]{3,}.+)$/iu', $line, $m)) {
                if ($currentItem) $items[] = $currentItem;
                $currentItem = [
                    'code' => '',
                    'desc' => $m[2],
                    'qty' => 1,
                    'unit' => 'รายการ',
                    'price' => 0,
                    'amount' => 0
                ];
                continue;
            }

            if ($currentItem) {
                if (preg_match('/(\d+)\s+([ก-ฮ]{1,10}|Unit|Pcs|Qty)/iu', $line, $m)) {
                    $currentItem['qty'] = (float)$m[1];
                    $currentItem['unit'] = $m[2];
                }
                
                if (preg_match_all('/([0-9,]+\.[0-9]{2})/', $line, $priceMatches)) {
                    foreach ($priceMatches[0] as $num) {
                        $val = (float)str_replace(',', '', $num);
                        if (abs($val - $model->total_amount) > 0.5 && abs($val - $model->vat_amount) > 0.5) {
                            if ($currentItem['price'] == 0) $currentItem['price'] = $val;
                            else $currentItem['amount'] = $val;
                        }
                    }
                }
            }
        }
        if ($currentItem) $items[] = $currentItem;
        return $items;
    }

    /**
     * Strategy for jumbled vertical layouts
     */
    protected function runCollector($logicalLines, $model)
    {
        $descriptions = [];
        $quantities = [];
        $prices_pool = [];
        
        $isTableArea = false;
        foreach ($logicalLines as $line) {
            $line = trim($line);
            if (preg_match('/(รายการ|Description|No\.|Qty|Amount)/iu', $line)) $isTableArea = true;
            if (preg_match('/(รวมเงิน|Total|ภาษี|VAT|ชำระ|ทอน|Cash)/iu', $line)) $isTableArea = false;

            if ($isTableArea) {
                if (preg_match('/[ก-ฮA-Z]{4,}/iu', $line) && !preg_match('/(\d{10}|No|Qty|Price|Unit|Amount|ID|Tel)/i', $line)) {
                     if (!in_array($line, ['รายการ', 'Description', 'Quantity', 'รายละเอียด', 'จำนวน', 'ราคา'])) $descriptions[] = $line;
                }
                if (preg_match('/^\d{1,3}$/', $line)) {
                    $quantities[] = (float)$line;
                }
            }
            if (preg_match_all('/([0-9,]+\.[0-9]{2})/', $line, $pm)) {
                foreach($pm[0] as $n) {
                    $val = (float)str_replace(',', '', $n);
                    if (abs($val - $model->total_amount) > 0.5) $prices_pool[] = $val;
                }
            }
        }

        $items = [];
        $count = count($descriptions);
        for ($i = 0; $i < $count; $i++) {
            $item = ['code' => '', 'desc' => $descriptions[$i], 'qty' => $quantities[$i] ?? 1, 'unit' => 'รายการ', 'price' => 0, 'amount' => 0];
            if (count($prices_pool) >= $count * 2) {
                $item['price'] = $prices_pool[$i*2];
                $item['amount'] = $prices_pool[$i*2+1];
            } elseif (isset($prices_pool[$i])) {
                $item['amount'] = $prices_pool[$i];
            }
            if ($item['amount'] > 0 || !empty($item['desc'])) $items[] = $item;
        }
        return $items;
    }
}

