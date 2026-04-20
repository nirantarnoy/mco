<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "temp_invoice".
 *
 * @property int $id
 * @property string|null $invoice_type ประเภทเอกสาร
 * @property string|null $invoice_number เลขที่ใบแจ้งหนี้
 * @property string|null $invoice_date วันที่ในเอกสาร
 * @property string|null $vendor_name ชื่อผู้ขาย/ผู้ออกเอกสาร
 * @property string|null $customer_name ชื่อลูกค้า
 * @property string|null $customer_tax_id เลขประจำตัวผู้เสียภาษี
 * @property string|null $customer_address ที่อยู่ลูกค้า
 * @property float|null $subtotal
 * @property float|null $vat_amount
 * @property float|null $total_amount
 * @property string|null $raw_text ข้อความดิบจากการ OCR
 * @property int|null $status 0:รอยืนยัน, 1:ยืนยันแล้ว, 9:ยกเลิก
 * @property int|null $company_id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $created_by
 *
 * @property TempInvoiceLine[] $tempInvoiceLines
 */
class TempInvoice extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_CANCELLED = 9;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temp_invoice';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['invoice_type', 'invoice_number', 'invoice_date', 'vendor_name', 'customer_name', 'customer_tax_id', 'customer_address', 'raw_text', 'company_id', 'created_at', 'updated_at', 'created_by'], 'default', 'value' => null],
            [['total_amount'], 'default', 'value' => 0.00],
            [['status'], 'default', 'value' => 0],
            [['invoice_date'], 'safe'],
            [['customer_address', 'raw_text'], 'string'],
            [['subtotal', 'vat_amount', 'total_amount'], 'number'],
            [['status', 'company_id', 'created_at', 'updated_at', 'created_by'], 'integer'],
            [['invoice_type'], 'string', 'max' => 50],
            [['invoice_number'], 'string', 'max' => 100],
            [['vendor_name', 'customer_name'], 'string', 'max' => 255],
            [['customer_tax_id'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'invoice_type' => 'Invoice Type',
            'invoice_number' => 'Invoice Number',
            'invoice_date' => 'Invoice Date',
            'vendor_name' => 'Vendor Name',
            'customer_name' => 'Customer Name',
            'customer_tax_id' => 'Customer Tax ID',
            'customer_address' => 'Customer Address',
            'subtotal' => 'Subtotal',
            'vat_amount' => 'Vat Amount',
            'total_amount' => 'Total Amount',
            'raw_text' => 'Raw Text',
            'status' => 'Status',
            'company_id' => 'Company ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
        ];
    }

    /**
     * Gets query for [[TempInvoiceLines]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTempInvoiceLines()
    {
        return $this->hasMany(TempInvoiceLine::class, ['temp_invoice_id' => 'id']);
    }

}
