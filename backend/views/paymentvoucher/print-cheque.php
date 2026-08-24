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
            width: 17cm;
            height: 8cm;
            position: relative;
            margin: 0 auto;
            /* Border for preview only, hidden in print */
            border: 1px solid #ccc;
        }
        .ac-payee {
            position: absolute;
            top: 1cm;
            left: 2cm;
            font-weight: bold;
        }
        .cheque-date {
            position: absolute;
            top: 1cm;
            right: 1cm;
            letter-spacing: 2px;
        }
        .payee-name {
            position: absolute;
            top: 2.5cm;
            left: 2cm;
            font-weight: bold;
            font-size: 18px;
        }
        .amount-text {
            position: absolute;
            top: 3.5cm;
            left: 2.5cm;
        }
        .amount-num {
            position: absolute;
            top: 3.5cm;
            right: 2cm;
            font-weight: bold;
            font-size: 18px;
        }

        @media print {
            .cheque-container {
                border: none;
            }
            .no-print {
                display: none;
            }
            @page {
                size: 17.5cm 8.5cm; /* Standard Cheque size */
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
