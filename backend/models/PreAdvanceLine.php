<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class PreAdvanceLine extends ActiveRecord
{
    public static function tableName()
    {
        return 'pre_advance_line';
    }

    public function rules()
    {
        return [
            [['pre_advance_id'], 'integer'],
            [['amount'], 'number'],
            [['line_date'], 'safe'],
            [['description'], 'string', 'max' => 255],
            [['remark'], 'string'],
        ];
    }

    public function getPreAdvance()
    {
        return $this->hasOne(PreAdvance::class, ['id' => 'pre_advance_id']);
    }

    /**
     * ดึงคำอธิบายรายการโดยแปลงเลขอ้างอิง QT ที่อาจเป็นตัวเลข ID ให้เป็นเลขที่ใบเสนอราคาหรือ Job จริง
     *
     * @return string
     */
    public function getDisplayDescription()
    {
        if (class_exists('\backend\controllers\PreAdvanceController')) {
            return \backend\controllers\PreAdvanceController::resolveLineDescription($this->description);
        }
        return $this->description;
    }

    /**
     * ดึงรายละเอียดรายการสินค้า/บริการ จากเอกสารต้นทาง (NPR หรือ PO)
     *
     * @return string
     */
    public function getItemDetails()
    {
        $desc = $this->description;
        if (empty($desc)) {
            return '';
        }

        if (preg_match('/เลขที่:\s*(NPR\d+)/', $desc, $m)) {
            $npr = \backend\models\PurchaseMaster::findOne(['docnum' => $m[1]]);
            if ($npr && $npr->purchaseDetails) {
                $details = [];
                foreach ($npr->purchaseDetails as $pd) {
                    $txt = trim($pd->stkdes ?? '');
                    if (!empty($txt)) {
                        $details[] = $txt;
                    }
                }
                return implode(', ', $details);
            }
        } elseif (preg_match('/เลขที่:\s*(PO-[^\s\(]+)/', $desc, $m)) {
            $refNo = $m[1];
            $po = \backend\models\Purch::findOne(['purch_no' => $refNo]);
            if (!$po) {
                // Try finding by prefix before -QT or without dot
                $cleanRef = preg_replace('/-QT[A-Za-z0-9.-]+$/i', '', $refNo);
                $po = \backend\models\Purch::find()->where(['like', 'purch_no', $cleanRef])->one();
            }
            if ($po) {
                $details = [];
                // 1. ตรวจสอบจาก Purch Lines ก่อนเป็นอันดับแรก
                $poLines = method_exists($po, 'getPurchLines') ? $po->getPurchLines()->all() : [];
                if (!empty($poLines)) {
                    $count = 0;
                    foreach ($poLines as $pl) {
                        $txt = trim(($pl->product_name ?? '') . ' ' . ($pl->product_description ?? ''));
                        if (empty(trim($txt)) && $pl->product) {
                            $txt = trim(($pl->product->name ?? '') . ' ' . ($pl->product_description ?? ''));
                        }
                        if (empty(trim($txt))) {
                            $txt = trim($pl->note ?? '');
                        }
                        if (!empty(trim($txt))) {
                            $details[] = trim($txt);
                            $count++;
                            if ($count >= 3) break;
                        }
                    }
                }
                // 2. ถ้าไม่มีรายการใน Purch Lines ให้ดึงจาก Quotation Lines
                if (empty($details) && $po->job && $po->job->quotation && $po->job->quotation->quotationLines) {
                    foreach ($po->job->quotation->quotationLines as $ql) {
                        $txt = trim($ql->product_name ?? '');
                        if (empty($txt) && $ql->product) {
                            $txt = trim($ql->product->name ?? '');
                        }
                        if (empty($txt)) {
                            $txt = trim($ql->note ?? '');
                        }
                        if (!empty($txt)) {
                            $details[] = $txt;
                        }
                    }
                }
                return implode(', ', $details);
            }
        }

        return '';
    }
}
