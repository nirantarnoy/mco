<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use backend\models\Vendor;

class VendorMergeTestController extends BaseController
{
    public function actionMerge()
    {
        set_time_limit(0);
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Find duplicate taxids
            $sql = "SELECT taxid FROM vendor WHERE status != 0 AND taxid IS NOT NULL AND taxid != '' GROUP BY taxid HAVING COUNT(*) > 1";
            $duplicateTaxids = Yii::$app->db->createCommand($sql)->queryColumn();

            $log = [];
            foreach ($duplicateTaxids as $taxid) {
                // Get all vendors with this taxid, ordered by id ascending (oldest first)
                $vendors = Vendor::find()->where(['taxid' => $taxid, 'status' => 1])->orderBy(['id' => SORT_ASC])->all();
                
                if (count($vendors) <= 1) continue;

                $primaryVendor = $vendors[0];
                $duplicates = array_slice($vendors, 1);

                $log[] = "TaxID: {$taxid} | Primary: {$primaryVendor->id} ({$primaryVendor->code})";

                foreach ($duplicates as $dup) {
                    $dupId = $dup->id;
                    $dupCode = $dup->code;

                    // Update related tables (vendor_id)
                    $tablesWithVendorId = [
                        'purch', 'purch_req', 'wht', 'payment_voucher', 
                        'petty_cash_voucher', 'pre_advance', 'debit_note', 'credit_note'
                    ];

                    foreach ($tablesWithVendorId as $table) {
                        try {
                            $count = Yii::$app->db->createCommand()
                                ->update($table, ['vendor_id' => $primaryVendor->id], ['vendor_id' => $dupId])
                                ->execute();
                            if ($count > 0) {
                                $log[] = "  - Updated {$count} records in table {$table} (vendor_id {$dupId} -> {$primaryVendor->id})";
                            }
                        } catch (\Exception $e) {
                            // Table might not exist or field missing, ignore silently
                        }
                    }

                    // Update PurchaseMaster (None PR)
                    try {
                        $count = Yii::$app->db->createCommand()
                            ->update('purchase_master', [
                                'supcod' => $primaryVendor->code,
                                'supnam' => $primaryVendor->name,
                                'taxid' => $primaryVendor->taxid
                            ], ['supcod' => $dupCode])
                            ->execute();
                        if ($count > 0) {
                            $log[] = "  - Updated {$count} records in purchase_master (supcod {$dupCode} -> {$primaryVendor->code})";
                        }
                    } catch (\Exception $e) {
                        // Ignore
                    }

                    // Soft delete duplicate
                    $dup->status = 0;
                    $dup->save(false);
                    $log[] = "  - Soft deleted duplicate vendor ID {$dupId} ({$dupCode})";
                }
            }

            $transaction->commit();
            return "<pre>Merge Completed Successfully:\n" . implode("\n", $log) . "</pre>";
        } catch (\Exception $e) {
            $transaction->rollBack();
            return "Error: " . $e->getMessage();
        }
    }
}
