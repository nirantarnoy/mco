<?php
// common/models/BillingInvoiceItem.php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

class BillingInvoiceItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'billing_invoice_items';
    }

    public function rules()
    {
        return [
            [['billing_invoice_id', 'invoice_id', 'item_seq'], 'required'],
            [['billing_invoice_id', 'invoice_id', 'item_seq', 'sort_order'], 'integer'],
            [['amount'], 'number'],
        ];
    }

    public function getBillingInvoice()
    {
        return $this->hasOne(BillingInvoice::class, ['id' => 'billing_invoice_id']);
    }

    public function getInvoice()
    {
        return $this->hasOne(Invoice::class, ['id' => 'invoice_id']);
    }
}