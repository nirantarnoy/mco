<?php
namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "purch_line".
 *
 * @property int $id
 * @property int|null $purch_id
 * @property int|null $product_id
 * @property string|null $product_name
 * @property string|null $product_description
 * @property int|null $product_type
 * @property float|null $qty
 * @property float|null $line_price
 * @property string|null $unit
 * @property float|null $line_total
 * @property int|null $status
 * @property string|null $note
 * @property int|null $migrate
 * @property float|null $value_amount
 *
 * @property Purch $purch
 * @property Product $product
 */
class PurchLine extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_CANCELLED = 0;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'purch_line';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['purch_id', 'product_id', 'migrate'], 'integer'],
            [['qty', 'line_price', 'line_total', 'value_amount'], 'number'],
            [['product_name', 'doc_ref_no'], 'string', 'max' => 255],
            [['product_description', 'note'], 'string'],
            [['qty', 'line_price'], 'required'],
            [['purch_id'], 'exist', 'skipOnError' => true, 'targetClass' => Purch::class, 'targetAttribute' => ['purch_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['product_description', 'unit', 'product_type', 'status'],'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'purch_id' => 'รหัสใบสั่งซื้อ',
            'product_id' => 'รหัสสินค้า',
            'product_name' => 'ชื่อสินค้า',
            'product_description' => 'รายละเอียด',
            'product_type' => 'ประเภทสินค้า',
            'qty' => 'จำนวน',
            'line_price' => 'ราคา/หน่วย',
            'unit' => 'หน่วยนับ',
            'line_total' => 'ราคารวม',
            'status' => 'สถานะ',
            'note' => 'หมายเหตุ',
            'migrate' => 'Migrate',
            'value_amount' => 'มูลค่าแปลงตามเรท',
        ];
    }

    /**
     * Gets query for [[Purch]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPurch()
    {
        return $this->hasOne(Purch::class, ['id' => 'purch_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    /**
     * Get product code with robust fallback mechanisms
     * @return string
     */
    public function getProductCode()
    {
        $productCode = '';

        // 1. Try to get from the related Product model
        if ($this->product) {
            $productCode = $this->product->code;
        }

        // 2. Try to query the Product model directly if relation is null but product_id exists
        if (empty($productCode) && $this->product_id) {
            $product = Product::findOne($this->product_id);
            if ($product) {
                $productCode = $product->code;
            }
        }

        // 3. Try to find the Product model by product_name prefix or exact match
        if (empty($productCode) && !empty($this->product_name)) {
            $trimmedName = trim($this->product_name);
            
            // Try exact match first (case-insensitive)
            $product = Product::find()->where(['name' => $trimmedName])->one();
            if ($product) {
                $productCode = $product->code;
            } else {
                // Find products where the database product name is a prefix of the line's product name.
                // We order by length of the product name descending so we get the most specific/longest match first.
                $product = Product::find()
                    ->where(['and',
                        ['not', ['name' => null]],
                        ['!=', 'name', ''],
                        new \yii\db\Expression('LOCATE(name, :product_name) = 1', [':product_name' => $trimmedName])
                    ])
                    ->orderBy([new \yii\db\Expression('LENGTH(name) DESC')])
                    ->one();
                if ($product) {
                    $productCode = $product->code;
                }
            }
        }

        // 4. Try to parse/extract the code from product_name (e.g. "CODE (NAME)" or "CODE - NAME" or just "CODE")
        if (empty($productCode) && !empty($this->product_name)) {
            $trimmedName = trim($this->product_name);
            // Check if name starts with bracket pattern like "CODE (NAME)" or "CODE(NAME)"
            if (preg_match('/^([A-Za-z0-9\-_]+)\s*\(/', $trimmedName, $matches)) {
                $potentialCode = trim($matches[1]);
                $product = Product::find()->where(['code' => $potentialCode])->one();
                if ($product) {
                    $productCode = $product->code;
                } else {
                    $productCode = $potentialCode;
                }
            }
            // Check if name contains dash like "CODE-NAME" or "CODE - NAME"
            elseif (strpos($trimmedName, '-') !== false) {
                $parts = explode('-', $trimmedName);
                $potentialCode = trim($parts[0]);
                $product = Product::find()->where(['code' => $potentialCode])->one();
                if ($product) {
                    $productCode = $product->code;
                } else {
                    if (preg_match('/^[A-Za-z0-9\-_]+$/', $potentialCode)) {
                        $productCode = $potentialCode;
                    }
                }
            }
            // Check if the product_name itself looks like a code (e.g., alphanumeric, no spaces, typical code format)
            elseif (preg_match('/^[A-Za-z0-9\-_]+$/', $trimmedName)) {
                $product = Product::find()->where(['code' => $trimmedName])->one();
                if ($product) {
                    $productCode = $product->code;
                } else {
                    $productCode = $trimmedName;
                }
            }
        }

        return $productCode;
    }

    /**
     * Before save
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Calculate line total
            $this->line_total = (double)$this->qty * (double)$this->line_price;
            return true;
        }
        return false;
    }

}