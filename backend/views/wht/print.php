<?php
use yii\helpers\Html;
$formatter = \Yii::$app->formatter;

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
            $baht_text .= 'บาท';
        }
        
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
        return $baht_text;
    }
}

$is_sec_6 = ($model->wht_desc == 'อื่นๆ' || $model->wht_desc == 'อื่นๆ (ระบุ)');
$is_sec_5 = !$is_sec_6;
$desc_text = $model->wht_desc == 'อื่นๆ' || $model->wht_desc == 'อื่นๆ (ระบุ)' ? $model->other_desc : $model->wht_desc;

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>หนังสือรับรองการหักภาษี ณ ที่จ่าย : <?= Html::encode($model->wht_no) ?></title>
    <style>
        body { font-family: 'Sarabun', 'Arial', sans-serif; font-size: 14px; margin: 0; padding: 20px; line-height: 1.4; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .pull-right { float: right; }
        .pull-left { float: left; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .wht-form { border: 1px solid #000; padding: 10px; position: relative; }
        .doc-no { position: absolute; top: 10px; left: 10px; font-size: 12px; }
        .doc-ref { position: absolute; top: 10px; right: 10px; font-size: 12px; }
        
        .section-box { border: 1px solid #000; padding: 10px; margin-bottom: 5px; }
        .grid-container { display: flex; }
        .col-left { width: 150px; font-weight: bold; }
        .col-right { flex-grow: 1; border-bottom: 1px dotted #000; }
        
        table.wht-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.wht-table th, table.wht-table td { border: 1px solid #000; padding: 5px; font-size: 12px; }
        table.wht-table th { text-align: center; }
        .valign-bottom { vertical-align: bottom; }
        
        .check-box { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; vertical-align: middle; text-align: center; line-height: 12px; font-size: 10px; }
        .checked::after { content: '✓'; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9; display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
        <div>
            <b>ตั้งค่าการพิมพ์:</b>
            <span style="margin-left: 15px;">
                <label>ตราประทับ:</label>
                <select id="stamp-selector" onchange="updateStamp(this.value)" style="padding: 3px;">
                    <option value="">-- ไม่ใช้ตราประทับ --</option>
                    <option value="<?= Yii::$app->request->baseUrl ?>/uploads/logo/mco_logo.png">MCO</option>
                    <option value="<?= Yii::$app->request->baseUrl ?>/uploads/logo/aricat.png">บจก. อริแคท ต่างด้าว</option>
                </select>
            </span>
            <span style="margin-left: 15px;">
                <label>ผู้ลงชื่อ:</label>
                <select id="sig-selector" onchange="updateSig(this.options[this.selectedIndex])" style="padding: 3px;">
                    <option value="" data-name="">-- ไม่ใช้ Electronic sign --</option>
                    <?php
                    $employees = \backend\models\Employee::find()
                        ->where(['like', 'first_name', 'อดิศร'])
                        ->orWhere(['like', 'first_name', 'สิริลักษณ์'])
                        ->orWhere(['like', 'first_name', 'อิสรียะ'])
                        ->all();
                    foreach ($employees as $emp) {
                        if ($emp->user_id) {
                            $sig = \backend\models\User::findEmployeeSignature($emp->user_id);
                            $sigUrl = $sig ? Yii::$app->request->baseUrl . '/uploads/employee_signature/' . $sig : '';
                            echo '<option value="'.$sigUrl.'" data-name="'.$emp->first_name.' '.$emp->last_name.'">'.$emp->first_name.'</option>';
                        }
                    }
                    ?>
                </select>
            </span>
        </div>
        <button onclick="window.print();" style="padding: 8px 20px; font-size: 14px; cursor: pointer; background-color: #007bff; color: white; border: none; border-radius: 4px;">Print</button>
    </div>

    <div style="font-size: 12px; margin-bottom: 5px;">
        ฉบับที่ 1 (สำหรับผู้ถูกหักภาษี ณ ที่จ่าย ใช้แนบพร้อมกับแสดงรายการภาษี)<br>
        ฉบับที่ 2 (สำหรับผู้ถูกหักภาษี ณ ที่จ่าย เก็บไว้เป็นหลักฐาน)
    </div>
    <div class="wht-form">
        <div class="doc-no">เล่มที่ / เลขที่ <b><?= Html::encode($model->wht_no) ?></b></div>
        
        <div class="text-center title" style="margin-top: 30px;">
            หนังสือรับรองการหักภาษี ณ ที่จ่าย<br>
            <span style="font-size: 14px; font-weight: normal;">ตามมาตรา 50 ทวิ แห่งประมวลรัษฎากร</span>
        </div>

        <div class="section-box" style="margin-top: 15px;">
            <b>ผู้มีหน้าที่หักภาษี ณ ที่จ่าย:</b><br>
            ชื่อ: <b>บริษัท เอ็ม.ซี.โอ. จำกัด (สำนักงานใหญ่)</b><br>
            ที่อยู่: 8/18 ถ.เกาะกลอย ต.เชิงเนิน อ.เมือง จ.ระยอง 21000<br>
            เลขประจำตัวผู้เสียภาษีอากร: <b>0215543000985</b>
        </div>

        <div class="section-box">
            <?php 
            $vendorBranch = '';
            if ($model->vendor) {
                $vendorBranch = trim($model->vendor->branch_name);
                if (!empty($vendorBranch)) {
                    $vendorBranch = ' (' . $vendorBranch . ')';
                }
            }
            ?>
            <b>ผู้ถูกหักภาษี ณ ที่จ่าย:</b><br>
            ชื่อ: <b><?= $model->vendor ? Html::encode($model->vendor->name) . Html::encode($vendorBranch) : '-' ?></b><br>
            ที่อยู่: <b><?= $model->vendor ? Html::encode(\backend\models\Vendor::findFullAddress($model->vendor_id)) : '-' ?></b><br>
            เลขประจำตัวผู้เสียภาษีอากร: <b><?= $model->vendor ? Html::encode($model->vendor->taxid) : '-' ?></b>
        </div>

        <div style="margin-top: 10px; font-size: 13px;">
            <b>แบบที่ยื่น:</b>
            <span class="check-box"></span> (1) ภ.ง.ด. 1 ก &nbsp;
            <span class="check-box"></span> (2) ภ.ง.ด. 1 ก พิเศษ &nbsp;
            <span class="check-box"></span> (3) ภ.ง.ด. 2 &nbsp;
            <span class="check-box <?= $model->wht_type == 3 ? 'checked' : '' ?>"></span> (4) ภ.ง.ด. 3 &nbsp;
            <span class="check-box"></span> (5) ภ.ง.ด. 2 ก &nbsp;
            <span class="check-box"></span> (6) ภ.ง.ด. 3 ก &nbsp;
            <span class="check-box <?= $model->wht_type == 53 ? 'checked' : '' ?>"></span> (7) ภ.ง.ด. 53
        </div>

        <table class="wht-table">
            <thead>
                <tr>
                    <th width="55%">ประเภทเงินได้ที่จ่าย</th>
                    <th width="15%">วัน เดือน ปี<br>ที่จ่ายเงิน</th>
                    <th width="15%">จำนวนเงิน<br>ที่จ่าย</th>
                    <th width="15%">ภาษี<br>หัก ณ ที่จ่าย</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1. เงินเดือน ค่าจ้าง เบี้ยเลี้ยง โบนัส ฯลฯ ตามมาตรา 40 (1)</td>
                    <td></td><td></td><td></td>
                </tr>
                <tr>
                    <td>2. ค่าธรรมเนียม ค่านายหน้า ฯลฯ ตามมาตรา 40 (2)</td>
                    <td></td><td></td><td></td>
                </tr>
                <tr>
                    <td>3. ค่าแห่งลิขสิทธิ์ ฯลฯ ตามมาตรา 40 (3)</td>
                    <td></td><td></td><td></td>
                </tr>
                <tr>
                    <td>4. (ก) ค่าดอกเบี้ย ฯลฯ ตามมาตรา 40(4) (ก)</td>
                    <td></td><td></td><td></td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">
                        (ข) เงินปันผล เงินส่วนแบ่งกำไร ฯลฯ ตามมาตรา 40 (4) (ข)<br>
                        <div style="padding-left: 20px;">
                            (1) กรณีผู้ได้รับเงินปันผลได้รับเครดิตภาษี โดยจ่ายจาก<br>
                            <div style="padding-left: 20px;">
                                กำไรสุทธิของกิจการที่ต้องเสียภาษีเงินได้นิติบุคคลในอัตราดังนี้<br>
                                (1.1) อัตราร้อยละ 30 ของกำไรสุทธิ<br>
                                (1.2) อัตราร้อยละ 25 ของกำไรสุทธิ<br>
                                (1.3) อัตราร้อยละ 20 ของกำไรสุทธิ<br>
                                (1.4) อัตราอื่น ๆ ระบุ ........ ของกำไรสุทธิ<br>
                            </div>
                            (2) กรณีผู้ได้รับเงินปันผลไม่ได้รับเครดิตภาษี เนื่องจากจ่ายจาก<br>
                            <div style="padding-left: 20px;">
                                (2.1) กำไรสุทธิของกิจการที่ได้รับยกเว้น<br>
                                (2.2) เงินปันผลหรือเงินส่วนแบ่งของกำไรที่ได้รับยกเว้นไม่ต้อง<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;นำมารวมคำนวณเป็นรายได้เพื่อเสียภาษีเงินได้นิติบุคคล<br>
                                (2.3) กำไรสุทธิส่วนที่ได้หักผลขาดทุนสุทธิยกมาไม่เกิน 5 ปี<br>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ก่อนรอบระยะบัญชีปีปัจจุบัน<br>
                                (2.4) กำไรที่รับรู้ทางบัญชีโดยวิธีส่วนได้เสีย (equity method)<br>
                                (2.5) อื่น ๆ (ระบุ) ..............................................................
                            </div>
                        </div>
                    </td>
                    <td></td><td></td><td></td>
                </tr>
                <tr>
                    <td>
                        5. การจ่ายเงินได้ที่ต้องหักภาษี ณ ที่จ่าย ตามคำสั่งกรมสรรพากรที่ออกตาม<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;มาตรา 3 เตรส (ระบุ) <b><?= $is_sec_5 ? Html::encode($desc_text) : '........................................................' ?></b><br>
                        <span style="color: #333;">
                        &nbsp;&nbsp;&nbsp;&nbsp;(เช่น รางวัล ส่วนลดหรือประโยชน์ใด ๆ เนื่องจากการส่งเสริมการขาย รางวัล<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;ในการประกวด การแข่งขัน การชิงโชค ค่าแสดงของนักแสดงสาธารณะ ค่าจ้าง<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;ทำของ ค่าโฆษณา ค่าเช่า ค่าขนส่ง ค่าบริการ ค่าเบี้ยประกันวินาศภัย ฯลฯ)
                        </span>
                    </td>
                    <td class="text-center valign-bottom"><?= $is_sec_5 && $model->trans_date ? $formatter->asDate($model->trans_date, 'php:d/m/Y') : '' ?></td>
                    <td class="text-right valign-bottom"><?= $is_sec_5 ? number_format($model->base_amount, 2) : '' ?></td>
                    <td class="text-right valign-bottom"><?= $is_sec_5 ? number_format($model->wht_amount, 2) : '' ?></td>
                </tr>
                <tr>
                    <td>
                        6. อื่น ๆ (ระบุ) <b><?= $is_sec_6 ? Html::encode($desc_text) : '.......................................................................' ?></b>
                    </td>
                    <td class="text-center valign-bottom"><?= $is_sec_6 && $model->trans_date ? $formatter->asDate($model->trans_date, 'php:d/m/Y') : '' ?></td>
                    <td class="text-right valign-bottom"><?= $is_sec_6 ? number_format($model->base_amount, 2) : '' ?></td>
                    <td class="text-right valign-bottom"><?= $is_sec_6 ? number_format($model->wht_amount, 2) : '' ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="text-right" style="padding-right: 20px;"><b>รวมเงินที่จ่ายและภาษีที่หักนำส่ง</b></td>
                    <td class="text-right"><b><?= number_format($model->base_amount, 2) ?></b></td>
                    <td class="text-right"><b><?= number_format($model->wht_amount, 2) ?></b></td>
                </tr>
                <tr>
                    <td colspan="4">
                        รวมเงินภาษีที่หักนำส่ง (ตัวอักษร): &nbsp;&nbsp; 
                        <b><?= bahtText($model->wht_amount) ?></b>
                    </td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 10px; font-size: 13px;">
            <b>ผู้จ่ายเงิน</b> &nbsp;&nbsp;&nbsp;
            <span class="check-box <?= $model->pay_condition == 3 ? 'checked' : '' ?>"></span> (1) ออกภาษีให้ครั้งเดียว &nbsp;&nbsp;
            <span class="check-box <?= $model->pay_condition == 2 ? 'checked' : '' ?>"></span> (2) ออกภาษีให้ตลอดไป &nbsp;&nbsp;
            <span class="check-box <?= $model->pay_condition == 1 ? 'checked' : '' ?>"></span> (3) หักภาษี ณ ที่จ่าย &nbsp;&nbsp;
            <span class="check-box"></span> (4) อื่น ๆ (ระบุ).........................
        </div>

        <div style="margin-top: 20px; text-align: center; position: relative;">
            <p><b>ขอรับรองว่าข้อความและตัวเลขดังกล่าวข้างต้นถูกต้องตรงกับความจริงทุกประการ</b></p>
            <br>
            <div style="position: relative; display: inline-block; width: 300px; height: 30px;">
                <div id="sig-container" style="position: absolute; bottom: 5px; left: 0; width: 100%; text-align: center; z-index: 2;"></div>
                <div style="position: absolute; bottom: 0; left: 0; width: 100%;">ลงชื่อ ............................................................................</div>
            </div> ผู้มีหน้าที่หักภาษี ณ ที่จ่าย<br>
            <div id="sig-name" style="margin-top: 5px; font-size: 13px;"></div>
            
            <span style="display: inline-block; margin-top: 10px;">
                วันที่ <?= $model->trans_date ? $formatter->asDate($model->trans_date, 'php:d/m/Y') : '......./......./.......' ?>
            </span>
            
            <!-- ตรายางบริษัท (จำลองตำแหน่งตามรูปภาพ) -->
            <div id="stamp-container" style="position: absolute; right: 80px; top: 10px; width: 100px; height: 100px; border: 1px dashed #ccc; border-radius: 50%; display: flex; align-items: center; justify-content: center; opacity: 0.3; z-index: 1;">
                <span id="stamp-text" style="font-size: 10px; transform: rotate(-15deg);">ประทับตรา<br>นิติบุคคล</span>
                <img id="stamp-img" src="" style="display:none; max-width: 100px; max-height: 100px; border-radius: 50%; mix-blend-mode: multiply;">
            </div>
        </div>

        <script>
        function updateStamp(url) {
            var img = document.getElementById('stamp-img');
            var text = document.getElementById('stamp-text');
            var container = document.getElementById('stamp-container');
            if (url) {
                img.src = url;
                img.style.display = 'block';
                text.style.display = 'none';
                container.style.border = 'none';
                container.style.opacity = '0.8';
            } else {
                img.src = '';
                img.style.display = 'none';
                text.style.display = 'block';
                container.style.border = '1px dashed #ccc';
                container.style.opacity = '0.3';
            }
        }
        function updateSig(option) {
            var url = option.value;
            var name = option.getAttribute('data-name');
            var sigContainer = document.getElementById('sig-container');
            var nameContainer = document.getElementById('sig-name');
            
            if (url) {
                sigContainer.innerHTML = '<img src="' + url + '" style="height: 45px;">';
            } else {
                sigContainer.innerHTML = '';
            }
            
            if (name) {
                nameContainer.innerHTML = '(' + name + ')';
            } else {
                nameContainer.innerHTML = '';
            }
        }
        </script>

        <div style="margin-top: 30px; font-size: 11px; border-top: 1px solid #000; padding-top: 10px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="border: none; border-collapse: collapse; margin-top: 0;">
                <tr>
                    <td width="70" valign="top" style="border: none; padding: 2px;"><b>หมายเหตุ *</b></td>
                    <td valign="top" style="border: none; padding: 2px;">ให้สามารถอ้างอิงหรือสอบยันกันได้ระหว่างลำดับที่ตามหนังสือรับรองฯ กับแบบยื่นรายการภาษีหัก ณ ที่จ่าย</td>
                </tr>
                <tr>
                    <td valign="top" style="border: none; padding: 2px;"><b>คำเตือน</b></td>
                    <td valign="top" style="border: none; padding: 2px;">ผู้มีหน้าที่ออกหนังสือรับรองการหักภาษี ณ ที่จ่าย ฝ่าฝืนไม่ปฏิบัติตามมาตรา 50 ทวิ แห่งประมวลรัษฎากร ต้องรับโทษทาง<br>อาญา ตามมาตรา 35 แห่งประมวลรัษฎากร</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div style="text-align: right; font-size: 11px; font-weight: bold; margin-top: 5px; margin-right: 5px;">
        F-WP-FMA-005-006 REV.N
    </div>
</body>
</html>

