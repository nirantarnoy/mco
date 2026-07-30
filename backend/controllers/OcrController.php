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
            
            // 2. Look for Vendor Name & Pattern based on Tax ID or Text
            $pattern = null;
            if ($model->customer_tax_id) {
                $pattern = OcrPattern::findOne(['tax_id' => $model->customer_tax_id, 'status' => 1]);
                if ($pattern) {
                    $model->vendor_name = $pattern->name;
                }
            }
            if (!$model->vendor_name) {
                // Detect company/partnership names in header
                if (preg_match('/((?:ห้างหุ้นส่วนจำกัด|บริษัท|บจก\.|หจก\.|ร้าน)\s*[ก-ฮA-Za-z0-9\s()]+)/u', $fullText, $vMatch)) {
                    $model->vendor_name = trim($vMatch[1]);
                }
            }

            // 3. Extract Invoice Number (Check Book No + Invoice No pattern first)
            if (preg_match('/เล่มที่\s*[:.]?\s*(\d+)\s*เลขที่\s*[:.]?\s*(\d+)/iu', $fullText, $bookMatches)) {
                $model->invoice_number = $bookMatches[1] . '/' . $bookMatches[2];
            } else {
                $regexInvoice = $pattern && $pattern->regex_invoice_no ? $pattern->regex_invoice_no : '/(?:เลขที่ใบกำกับภาษี|เลขที่ใบเสร็จ|เลขที่เอกสาร|เลขที่|TAX\s*INVOICE\s*NO|TAX\s*INV\s*NO|INV\s*NO|INVOICE\s*NO|POS\s*NO|DOCUMENT\s*NO|RECEIPT\s*NO|BILL\s*NO|DOC\s*NO|No\.?|Inv\s*#)\s*[:.]?\s*([A-Z0-9\-\/]{3,30})/iu';
                if (@preg_match($regexInvoice, $fullText, $matches)) {
                    $model->invoice_number = isset($matches[1]) ? trim($matches[1]) : trim($matches[0]);
                } else {
                    if (preg_match('/([A-Z0-9]{2,}[\-\/][A-Z0-9\-\/]{4,20})/', $fullText, $matches)) {
                        $model->invoice_number = trim($matches[1]);
                    }
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
            $regexTotal = $pattern && $pattern->regex_total ? $pattern->regex_total : '/(?:จำนวนเงินรวมทั้งสิ้น|จำนวนเงินรวมภาษี|รวมเงินทั้งสิ้น|ยอดรวมสุทธิ|จำนวนเงินรวม|ยอดโอน|ยอดชำระ|Grand\s*Total|Total\s*Amount|Total\s*Due|Amount\s*Due|Net\s*Total|TOTAL|Net\s*Amount).{0,50}?([0-9,]+\.[0-9]{2}|\d+)/is';
            
            if (@preg_match($regexTotal, $fullText, $m)) {
                $model->total_amount = (float)str_replace(',', '', $m[1]);
            } 
            
            if (preg_match('/(?:รวมราคาสินค้า|มูลค่าสินค้า|มูลค่าบริการ|รวมเป็นเงิน|Subtotal|Sub\s*Total|Net\s*Amount).{0,50}?([0-9,]+\.[0-9]{2})/is', $fullText, $m)) {
                $model->subtotal = (float)str_replace(',', '', $m[1]);
            }

            if (preg_match('/(?:จำนวนภาษีมูลค่าเพิ่ม|ภาษีมูลค่าเพิ่ม|VAT\s*7%|VAT|Value\s*Added\s*Tax).{0,30}?([0-9,]+\.[0-9]{2})/is', $fullText, $m)) {
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

            // Detect Customer Name & Address
            if (preg_match('/(?:นาม|ชื่อ|นามผู้ซื้อ|นามผู้รับ|Customer\s*Name|Customer|Sold\s*To)\s*[:.]?\s*([ก-ฮA-Za-z0-9\.\s()-]+)/u', $fullText, $cMatch)) {
                $cName = trim(str_replace(['...', '..', '.', '........................'], '', $cMatch[1]));
                if (mb_strlen($cName, 'UTF-8') > 2) {
                    $model->customer_name = $cName;
                }
            }
            if (preg_match('/(?:ที่อยู่|Address)\s*[:.]?\s*([ก-ฮA-Za-z0-9\/\.\s\d-]+)/u', $fullText, $aMatch)) {
                $cAddr = trim(str_replace(['...', '..', '........................'], '', $aMatch[1]));
                if (mb_strlen($cAddr, 'UTF-8') > 3) {
                    $model->customer_address = $cAddr;
                }
            }

            // Extract Remarks & Extra Info (e.g., License plate, Baht text)
            $remarksArr = [];
            if (preg_match('/\(ตัวอักษร\)\s*([ก-ฮ\s-]+)/u', $fullText, $bahtMatch)) {
                $remarksArr[] = 'ตัวอักษร: ' . trim($bahtMatch[1]);
            }
            if (preg_match('/([rA-Z0-9\s]{2,10}\s*\d{3,4}\s*[a-zA-Z]*)/i', $fullText, $refMatch)) {
                $remarksArr[] = 'อ้างอิง/ทะเบียน: ' . trim($refMatch[1]);
            }
            if (!empty($remarksArr) && property_exists($model, 'remarks')) {
                $model->remarks = implode(' | ', $remarksArr);
            }

            if ($model->save()) {
                // Prepare rows from spatial data if available
                $logicalLines = $this->reconstructRows($ocrResult['details'] ?? []);
                
                $items = [];

                // 1. Try Fuel / Gas Receipt Direct Parser first if applicable
                $gasItems = $this->tryGasFuelReceiptParsing($fullText, $model);
                if (!empty($gasItems)) {
                    $items = $gasItems;
                } else {
                    $regexStart = $pattern && $pattern->regex_item_start ? $pattern->regex_item_start : '/^(\d{1,2})\s+([A-Z0-9-]{4,20})\s+(.+)$/u';
                    $strategy = $pattern && $pattern->parsing_strategy ? $pattern->parsing_strategy : 'block';

                    if ($strategy == 'collector') {
                        $items = $this->runCollector($logicalLines, $model);
                    } else {
                        $items = $this->runBlockStrategy($logicalLines, $model, $regexStart, $pattern);
                        if (empty($items) && $model->total_amount > 0) {
                            $items = $this->runCollector($logicalLines, $model);
                        }
                    }
                }

                // Save items with Final Calculation Check
                foreach ($items as $item) {
                    // Ignore column header text as item description
                    if ($this->isHeaderOrLabelLine($item['desc'])) {
                        continue;
                    }

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
     * Specialized parser for Gas / Fuel / Petroleum receipt forms
     */
    protected function tryGasFuelReceiptParsing($fullText, $model)
    {
        if (preg_match('/(แก๊ส[ก-ฮA-Za-z0-9\s()]+|ดีเซล[ก-ฮA-Za-z0-9\s()]+|โซฮอล์[ก-ฮA-Za-z0-9\s()]+|Gasohol[ก-ฮA-Za-z0-9\s()]+|เบนซิน[ก-ฮA-Za-z0-9\s()]+|น้ำมัน[ก-ฮA-Za-z0-9\s()]+)/iu', $fullText, $gasMatch)) {
            $desc = trim($gasMatch[1]);
            $qty = 1.0;
            $unit = 'ลิตร';
            $price = 0.0;

            if (preg_match('/(\d+\.\d{2,3})\s*(?:ลิตร|L|Liter)?/iu', $fullText, $qm)) {
                $qty = (float)$qm[1];
            }

            if (preg_match_all('/(\d+\.\d{2})/', $fullText, $pm)) {
                foreach ($pm[1] as $pValStr) {
                    $pVal = (float)$pValStr;
                    if ($pVal != $model->total_amount && $pVal != $model->subtotal && $pVal != $model->vat_amount && $pVal != $qty) {
                        if ($price == 0) $price = $pVal;
                    }
                }
            }

            $amount = $model->total_amount > 0 ? $model->total_amount : ($price * $qty);

            return [
                [
                    'code' => '',
                    'desc' => $desc,
                    'qty' => $qty,
                    'unit' => $unit,
                    'price' => $price,
                    'amount' => $amount
                ]
            ];
        }
        return [];
    }

    /**
     * Check if a line is a table header or pre-printed label line
     */
    protected function isHeaderOrLabelLine($line)
    {
        $clean = trim($line);
        if (empty($clean)) return true;
        
        $headersRegex = '/^(จำนวน\s*\([^)]*\)|จำนวน|รายการ|รายละเอียด|ราคาต่อหน่วย|หน่วยละ|หน่วย|จำนวนเงิน|รวมราคาสินค้า|จำนวนภาษีมูลค่าเพิ่ม|จำนวนเงินรวมทั้งสิ้น|รวมเงินทั้งสิ้น|เล่มที่|เลขที่|เลขประจำตัวผู้เสียภาษี|เลขประจำตัวผู้เสียภาษีอากร|วันที่|นาม|ที่อยู่|สาขาที่|ผู้รับเงิน|ลงชื่อ|No\.|Qty|Price|Amount|Unit|Description|Total|VAT|Subtotal)$/iu';
        if (preg_match($headersRegex, $clean)) {
            return true;
        }

        if (in_array($clean, ['จำนวน (ลิตร)', 'จำนวน', 'รายการ', 'รายละเอียด', 'ราคาต่อหน่วย', 'จำนวนเงิน', 'รวมราคาสินค้า', 'จำนวนภาษีมูลค่าเพิ่ม', 'จำนวนเงินรวมทั้งสิ้น'])) {
            return true;
        }

        return false;
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
            if ($this->isHeaderOrLabelLine($line)) {
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
                     if (!$this->isHeaderOrLabelLine($line)) {
                        $descriptions[] = $line;
                     }
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

