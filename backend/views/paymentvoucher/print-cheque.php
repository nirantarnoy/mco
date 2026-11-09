<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\PaymentVoucher */

if (!function_exists('bahtText')) {
    function bahtText($amount) {
        $amount = number_format($amount, 2, '.', '');
        $baht = explode('.', $amount);
        $baht[1] = isset($baht[1]) ? $baht[1] : '00';
        
        if ($baht[0] == 0 && $baht[1] == 0) return 'ศูนย์บาทถ้วน';
        
        $number = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
        $digit = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
        
        $baht_text = '';
        if ($baht[0] > 0) {
            $length = strlen($baht[0]);
            for ($i = 0; $i < $length; $i++) {
                $n = substr($baht[0], $i, 1);
                if ($n != 0) {
                    if ($i == ($length - 1) && $n == 1 && $length > 1) {
                        $baht_text .= 'เอ็ด';
                    } elseif ($i == ($length - 2) && $n == 2) {
                        $baht_text .= 'ยี่';
                    } elseif ($i == ($length - 2) && $n == 1) {
                        $baht_text .= '';
                    } else {
                        $baht_text .= $number[$n];
                    }
                    $baht_text .= $digit[$length - $i - 1];
                }
            }
        }
        $baht_text .= 'บาท';
        
        if ($baht[1] == '00') {
            $baht_text .= 'ถ้วน';
        } else {
            $length = strlen($baht[1]);
            for ($i = 0; $i < $length; $i++) {
                $n = substr($baht[1], $i, 1);
                if ($n != 0) {
                    if ($i == ($length - 1) && $n == 1 && $length > 1) {
                        $baht_text .= 'เอ็ด';
                    } elseif ($i == ($length - 2) && $n == 2) {
                        $baht_text .= 'ยี่';
                    } elseif ($i == ($length - 2) && $n == 1) {
                        $baht_text .= '';
                    } else {
                        $baht_text .= $number[$n];
                    }
                    $baht_text .= $digit[$length - $i - 1];
                }
            }
            $baht_text .= 'สตางค์';
        }
        return '-' . $baht_text . '-';
    }
}

// Format date for cheque: DDMMYYYY with spacing
$chequeDateStr = '';
if ($model->cheque_date) {
    $dateObj = new \DateTime($model->cheque_date);
    $d = $dateObj->format('d');
    $m = $dateObj->format('m');
    $y = $dateObj->format('Y') + 543; // Convert to Buddhist Era
    
    $fullStr = $d . $m . $y;
    $chars = str_split($fullStr);
    $chequeDateStr = implode('&nbsp;&nbsp;', $chars);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Print Cheque</title>
    <style>
        body {
            font-family: "Sarabun", "Arial", sans-serif;
            font-size: 16px;
            margin: 0;
            padding: 0;
            color: #000;
        }
        .cheque-container {
            width: 8.87in;
            height: 3.87in;
            position: relative;
            margin: 0 auto;
            /* Border for preview only, hidden in print */
            border: 1px dashed #999;
            box-sizing: border-box;
            background: #fff;
        }
        .ac-payee {
            position: absolute;
            top: 0.4in;
            left: 2.8in;
            font-weight: bold;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .cheque-date {
            position: absolute;
            top: 0.45in;
            right: 0.55in;
            letter-spacing: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .payee-name {
            position: absolute;
            top: 1.15in;
            left: 1.4in;
            font-weight: bold;
            font-size: 17px;
        }
        .amount-text {
            position: absolute;
            top: 1.65in;
            left: 1.8in;
            font-size: 15px;
            font-weight: bold;
        }
        .amount-num {
            position: absolute;
            top: 1.95in;
            right: 0.6in;
            font-weight: bold;
            font-size: 18px;
            letter-spacing: 1px;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .cheque-container {
                border: none;
                margin: 0;
                width: 8.87in;
                height: 3.87in;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: 8.87in 3.87in landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">Print Cheque</button>
    </div>

    <div class="cheque-container">
        <div class="ac-payee">A/C PAYEE ONLY</div>
        <div class="cheque-date">
            <?= $chequeDateStr ?>
        </div>
        <div class="payee-name">
            <?= Html::encode($model->recipient_name) ?>
        </div>
        <div class="amount-text">
            <?= bahtText($model->amount) ?>
        </div>
        <div class="amount-num">
            -<?= number_format($model->amount, 2) ?>-
        </div>
    </div>
</body>
</html>
