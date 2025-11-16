<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "job_doc_complete".
 *
 * @property int $id
 * @property int|null $job_id
 * @property int|null $purch_req_doc
 * @property int|null $purch_doc
 * @property int|null $quotation_doc
 * @property int|null $bill_placement_doc
 * @property int|null $invoice_doc
 * @property int|null $debit_doc
 * @property int|null $tax_invoice_doc
 * @property int|null $purch_receive_doc
 * @property int|null $bill_receipt_doc
 * @property int|null $bill_payment_doc
 * @property int|null $product_issue_doc
 */
class JobDocComplete extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'job_doc_complete';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['job_id', 'purch_req_doc', 'purch_doc', 'quotation_doc', 'bill_placement_doc', 'invoice_doc', 'debit_doc', 'tax_invoice_doc', 'purch_receive_doc', 'bill_receipt_doc', 'bill_payment_doc', 'product_issue_doc'], 'default', 'value' => null],
            [['job_id', 'purch_req_doc', 'purch_doc', 'quotation_doc', 'bill_placement_doc', 'invoice_doc', 'debit_doc', 'tax_invoice_doc', 'purch_receive_doc', 'bill_receipt_doc', 'bill_payment_doc', 'product_issue_doc'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'job_id' => 'Job ID',
            'purch_req_doc' => 'Purch Req Doc',
            'purch_doc' => 'Purch Doc',
            'quotation_doc' => 'Quotation Doc',
            'bill_placement_doc' => 'Bill Placement Doc',
            'invoice_doc' => 'Invoice Doc',
            'debit_doc' => 'Debit Doc',
            'tax_invoice_doc' => 'Tax Invoice Doc',
            'purch_receive_doc' => 'Purch Receive Doc',
            'bill_receipt_doc' => 'Bill Receipt Doc',
            'bill_payment_doc' => 'Bill Payment Doc',
            'product_issue_doc' => 'Product Issue Doc',
        ];
    }

    public static function checkActivityDoc($job_id){
       $prDoc = self::checkPrDoc($job_id);
       $purchDoc = self::checkPurchDoc($job_id);
       $debitNoteDoc = self::checkDebitNoteDoc($job_id);
       $creditNoteDoc = self::checkCreditNoteDoc($job_id);
       $billPlacementDoc = self::checkBillPlacementDoc($job_id);
       $quotationDoc = 0;
       $billPaymentDoc = self::checkBillPlacementDoc($job_id);
       $productIssueDoc = 0;
       $taxInvoiceDoc = self::checkTaxInvoiceDoc($job_id);
       $receiptDoc = self::checkReceiptDoc($job_id);

       $check_model = \backend\models\JobDocComplete::find()->where(['job_id' => $job_id])->one();
       if($check_model){
          $check_model->purch_req_doc = $prDoc;
          $check_model->purch_doc = $purchDoc;
          $check_model->quotation_doc = $quotationDoc;
          $check_model->bill_placement_doc = $billPlacementDoc;
          $check_model->invoice_doc = $taxInvoiceDoc;
          $check_model->debit_doc = $debitNoteDoc;
          $check_model->credit_doc = $creditNoteDoc;
          $check_model->product_issue_doc = $productIssueDoc;
          $check_model->tax_invoice_doc = $taxInvoiceDoc;
          $check_model->bill_payment_doc = $billPaymentDoc;
          $check_model->bill_receipt_doc = $billPlacementDoc;
          if($check_model->save(false)){

          }
       }else{
           $model = new \backend\models\JobDocComplete();
           $model->job_id = $job_id;
           $model->purch_req_doc = $prDoc;
           $model->purch_doc = $purchDoc;
           $model->quotation_doc = $quotationDoc;
           $model->bill_placement_doc = $billPlacementDoc;
           $model->invoice_doc = $taxInvoiceDoc;
           $model->debit_doc = $debitNoteDoc;
           $model->credit_doc = $creditNoteDoc;
           $model->product_issue_doc = $productIssueDoc;
           $model->tax_invoice_doc = $taxInvoiceDoc;
           $model->bill_payment_doc = $billPaymentDoc;
           $model->bill_receipt_doc = $billPlacementDoc;
           if($model->save(false)){

           }
       }


    }
    protected static function checkPrDoc($job_id){
        $pr = \backend\models\PurchReq::find()->where(['job_id'=>$job_id])->one();
        if($pr){
            $pr_doc = \common\models\PurchReqDoc::find()->where(['purch_req_id'=>$pr->id])->one();
            if($pr_doc){
                if($pr_doc->doc_name){
                    return 1;
                }
            }
        }
        return 0;
    }
    protected static function checkPurchDoc($job_id){
        $po = \backend\models\Purch::find()->where(['job_id'=>$job_id])->one();
        if($po){
            $po_doc = \common\models\PurchDoc::find()->where(['purch_id'=>$po->id])->one();
            if($po_doc){
                if($po_doc->doc_name){
                    return 1;
                }
            }
        }
        return 0;
    }

    protected static function checkDebitNoteDoc($job_id){
        $debit_id = 0;
       $sql='SELECT
              dn.id as debit_note_id,
                inv.id AS invoice_id, 
                inv.quotation_id, 
                j.id AS job_id
            FROM
              debit_note dn INNER JOIN
                invoices inv ON dn.invoice_id = inv.id
                LEFT JOIN
                job j
                ON 
                    inv.quotation_id = j.quotation_id
            WHERE j.id = :job_id';
       $params = [':job_id' => $job_id];
       $query = Yii::$app->db->createCommand($sql, $params)->query();
       if($query){
           foreach ($query as $row){
               $debit_id = $row['debit_note_id'];
           }
           if($debit_id){
               $debit_doc = \common\models\DebitNoteDoc::find()->where(['debit_note_id'=>$debit_id])->one();
               if($debit_doc){
                   if($debit_doc->doc!=''){
                       return 1;
                   }
               }
           }
       }
       return 0;
    }
    protected static function checkCreditNoteDoc($job_id){
        $credit_id = 0;
        $sql='SELECT
                cn.id as credit_note_id,
                inv.id AS invoice_id, 
                inv.quotation_id, 
                j.id AS job_id
            FROM
               credit_note cn INNER JOIN
                invoices inv ON cn.invoice_id = inv.id
                LEFT JOIN
                job j
                ON 
                    inv.quotation_id = j.quotation_id
            WHERE j.id = :job_id';
        $params = [':job_id' => $job_id];
        $query = Yii::$app->db->createCommand($sql, $params)->query();
        if($query){
            foreach ($query as $row){
                $credit_id = $row['credit_note_id'];
            }
            if($credit_id){
                $credit_doc = \common\models\CreditNoteDoc::find()->where(['credit_note_id'=>$credit_id])->one();
                if($credit_doc){
                    if($credit_doc->doc!=''){
                        return 1;
                    }
                }
            }
        }
        return 0;
    }

    protected static function checkTaxInvoiceDoc($job_id){
        $tax_model = \backend\models\Invoice::find()->where(['job_id'=>$job_id,'invoice_type'=>'tax_invoice'])->one();
        if($tax_model){
            $doc = \common\models\InvoiceDoc::find()->where(['invoice_id'=>$tax_model->id])->one();
            if($doc){
                if($doc->doc!=''){
                    return 1;
                }
            }
        }
        return 0;
    }
    protected static function checkBillPlacementDoc($job_id){
        $bp_model = \backend\models\Invoice::find()->where(['job_id'=>$job_id,'invoice_type'=>'bill_placement'])->one();
        if($bp_model){
            $doc = \common\models\InvoiceDoc::find()->where(['invoice_id'=>$bp_model->id])->one();
            if($doc){
                if($doc->doc!=''){
                    return 1;
                }
            }
        }
        return 0;
    }
    protected static function checkReceiptDoc($job_id){
        $rc_model = \backend\models\Invoice::find()->where(['job_id'=>$job_id,'invoice_type'=>'receipt'])->one();
        if($rc_model){
            $doc = \common\models\InvoiceDoc::find()->where(['invoice_id'=>$rc_model->id])->one();
            if($doc){
                if($doc->doc!=''){
                    return 1;
                }
            }
        }
        return 0;
    }

}
