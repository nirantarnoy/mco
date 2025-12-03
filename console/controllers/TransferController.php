<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use backend\models\Purch;
use backend\models\PurchReq;
use backend\models\PurchReqLine;

class TransferController extends Controller
{
    public function actionIndex()
    {
        $poNo = 'PO-00198-QT25-000073.21';
        $prNo = 'PR-00424-QT25-000073.23';

        echo "Finding PO: $poNo\n";
        $po = Purch::find()->where(['purch_no' => $poNo])->one();
        if (!$po) {
            echo "Error: PO not found.\n";
            return 1;
        }

        echo "Finding PR: $prNo\n";
        $pr = PurchReq::find()->where(['purch_req_no' => $prNo])->one();
        if (!$pr) {
            echo "Error: PR not found.\n";
            return 1;
        }

        echo "Copying lines...\n";
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($po->purchLines as $poLine) {
                $prLine = new PurchReqLine();
                $prLine->purch_req_id = $pr->id;
                $prLine->product_id = $poLine->product_id;
                $prLine->product_name = $poLine->product_name;
                $prLine->product_description = $poLine->product_description;
                $prLine->product_type = $poLine->product_type;
                $prLine->qty = $poLine->qty;
                $prLine->line_price = $poLine->line_price;
                $prLine->unit_id = $poLine->unit; // Mapping unit string to unit_id
                $prLine->line_total = $poLine->line_total;
                $prLine->note = $poLine->note;
                $prLine->status = 1; // Active

                if (!$prLine->save()) {
                    echo "Error saving PR line: " . print_r($prLine->errors, true) . "\n";
                    throw new \Exception("Failed to save line");
                }
                echo "Copied item: " . $poLine->product_name . "\n";
            }

            // Update PR totals
            $total = 0;
            // Re-query lines to be sure
            $lines = PurchReqLine::find()->where(['purch_req_id' => $pr->id])->all();
            foreach ($lines as $line) {
                $total += $line->line_total;
            }
            
            $pr->total_amount = $total;
            
            $discountVal = 0;
            if ($pr->discount_percent > 0) {
                $discountVal += $total * ($pr->discount_percent / 100);
            }
            if ($pr->discount_amount > 0) {
                $discountVal += $pr->discount_amount;
            }
            
            $afterDiscount = $total - $discountVal;
            
            $vatVal = 0;
            if ($pr->is_vat == 1) {
                $vatPercent = $pr->vat_percent > 0 ? $pr->vat_percent : 7;
                $vatVal = $afterDiscount * ($vatPercent / 100);
            }
            
            $pr->vat_amount = $vatVal;
            $pr->net_amount = $afterDiscount + $vatVal;
            
            if (!$pr->save()) {
                 echo "Error updating PR header: " . print_r($pr->errors, true) . "\n";
                 throw new \Exception("Failed to update PR header");
            }

            $transaction->commit();
            echo "Success! PR updated with new totals: Total=$total, Net=" . $pr->net_amount . "\n";

        } catch (\Exception $e) {
            $transaction->rollBack();
            echo "Failed: " . $e->getMessage() . "\n";
            return 1;
        }
        
        return 0;
    }
}
