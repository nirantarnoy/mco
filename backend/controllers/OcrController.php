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

            // 1. Detect Tax IDs
            preg_match_all('/\d{13}/', $fullText, $taxMatches);
            if (!empty($taxMatches[0])) {
                $model->customer_tax_id = count($taxMatches[0]) > 1 ? $taxMatches[0][1] : $taxMatches[0][0];
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
            $regexInvoice = $pattern && $pattern->regex_invoice_no ? $pattern->regex_invoice_no : '/(?:เลขที่|No\.?|Doc No|Inv No|Inv\s*#)\s*[:.]?\s*([A-Z0-9\-\/]+)/iu';
            if (preg_match($regexInvoice, $fullText, $matches)) {
                $model->invoice_number = isset($matches[1]) ? trim($matches[1]) : trim($matches[0]);
            } else {
                // Secondary check for patterns like S001-IV... or anything looking like an ID
                if (preg_match('/([A-Z0-9]{2,}-\w+-\d+-\d+)/', $fullText, $matches)) {
                    $model->invoice_number = $matches[1];
                }
            }

            // 4. Extract Date
            $regexDate = $pattern && $pattern->regex_date ? $pattern->regex_date : '/วันที่\s*(\d{2}\/\d{2}\/\d{4})/';
            if (preg_match($regexDate, $fullText, $matches)) {
                $dateStr = isset($matches[1]) ? $matches[1] : $matches[0];
                $parts = explode('/', $dateStr);
                if (count($parts) == 3) {
                    $y = (int)$parts[2];
                    if ($y > 2400) $y -= 543;
                    $model->invoice_date = $y . '-' . $parts[1] . '-' . $parts[0];
                }
            }

            // 5. Extract Totals, VAT, Subtotal
            $regexTotal = $pattern && $pattern->regex_total ? $pattern->regex_total : '/(?:จำนวนเงินรวมภาษี|รวมเงินทั้งสิ้น|ยอดรวมสุทธิ|ยอดโอน|Grand Total|Total Amount|Total).{0,50}?([0-9,]+\.[0-9]{2})/is';
            
            // Try specific labels first
            if (preg_match($regexTotal, $fullText, $m)) {
                $model->total_amount = (float)str_replace(',', '', $m[1]);
            } 
            
            // Try specific Net Amount/Subtotal label
            if (preg_match('/(?:มูลค่าสินค้า|Subtotal|Net Amount|มูลค่าบริการ).{0,50}?([0-9,]+\.[0-9]{2})/is', $fullText, $m)) {
                $model->subtotal = (float)str_replace(',', '', $m[1]);
            }

            // Try specific VAT label
            if (preg_match('/(?:ภาษีมูลค่าเพิ่ม|VAT|Value Added).{0,30}?([0-9,]+\.[0-9]{2})/is', $fullText, $m)) {
                $model->vat_amount = (float)str_replace(',', '', $m[1]);
            }

            // Fallback: If total_amount still 0, look at the last 5 numbers and pick the largest 
            // (Common for jumbled OCR where total is in a cluster)
            if ($model->total_amount == 0) {
                 if (preg_match_all('/([0-9,]+\.[0-9]{2})/', $fullText, $allMatches)) {
                    $nums = array_map(function($n) { return (float)str_replace(',', '', $n); }, $allMatches[0]);
                    $lastFew = array_slice($nums, -5);
                    if (!empty($lastFew)) {
                        $model->total_amount = max($lastFew);
                    }
                }
            }

            // Logic check: if we have total and vat but no subtotal
            if ($model->total_amount > 0 && $model->subtotal == 0) {
                if ($model->vat_amount > 0) {
                    $model->subtotal = $model->total_amount - $model->vat_amount;
                } else {
                    $model->subtotal = $model->total_amount / 1.07;
                    $model->vat_amount = $model->total_amount - $model->subtotal;
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
                    // Logic: If we only have one number, it's safer to assume it's the TOTAL 
                    // and calculate unit price backwards, especially if Qty > 1
                    if ($item['amount'] == 0 && $item['price'] > 0) {
                        $item['amount'] = $item['price'];
                        if ($item['qty'] > 1) {
                             $item['price'] = $item['amount'] / $item['qty'];
                        }
                    } elseif ($item['amount'] > 0 && $item['amount'] < $item['price']) {
                        // Swap if somehow total < unit price
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
     * Group OCR words into rows based on their Y-coordinates
     */
    protected function reconstructRows($words)
    {
        if (empty($words)) return [];

        // 1. Sort words by Y-coordinate
        usort($words, function($a, $b) {
            $ay = $a['boundingPoly']['vertices'][0]['y'] ?? 0;
            $by = $b['boundingPoly']['vertices'][0]['y'] ?? 0;
            return $ay <=> $by;
        });

        $rows = [];
        $currentRow = [];
        $lastY = -1;
        $yThreshold = 10; // Pixels distance to be considered same row

        foreach ($words as $word) {
            $y = $word['boundingPoly']['vertices'][0]['y'] ?? 0;
            
            if ($lastY == -1 || abs($y - $lastY) <= $yThreshold) {
                $currentRow[] = $word;
            } else {
                // Finish current row
                $rows[] = $this->sortRowByX($currentRow);
                $currentRow = [$word];
            }
            $lastY = $y;
        }
        if (!empty($currentRow)) {
            $rows[] = $this->sortRowByX($currentRow);
        }

        // Convert word arrays to strings
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
     * Strategy for well-structured multi-line items
     */
    protected function runBlockStrategy($logicalLines, $model, $regexStart, $pattern)
    {
        $items = [];
        $currentItem = null;

        foreach ($logicalLines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match($regexStart, $line, $m)) {
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
                     if (!in_array($line, ['รายการ', 'Description', 'Quantity'])) $descriptions[] = $line;
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

