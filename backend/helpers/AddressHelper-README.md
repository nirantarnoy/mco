# AddressHelper - คู่มือการใช้งาน

## ภาพรวม
`AddressHelper` เป็น Helper class สำหรับจัดการการแสดงผลที่อยู่ให้ถูกต้องตามรูปแบบไทย

### การแยกการแสดงผลอัตโนมัติ:

**กรุงเทพมหานคร:**
```
เลขที่ 123 ซอยรามคำแหง 24 ถนนรามคำแหง แขวงหัวหมาก เขตบางกะปิ กรุงเทพมหานคร 10240
```

**จังหวัดอื่นๆ (เช่น ระยอง):**
```
เลขที่ 2 ถนนRoad ตำบลMuang อำเภอRayong จังหวัดRayong 21150
```

**ข้อมูลไม่สมบูรณ์ (ไม่มีซอย):**
```
i05 ถนนCECIL STREET จังหวัดSingapore 69534
```

---

## ฟังก์ชันหลัก

### 1. formatFullAddress($addressData)
จัดรูปแบบที่อยู่เต็มรูปแบบจาก array โดยแยกการแสดงผลระหว่างกรุงเทพฯ และจังหวัดอื่นอัตโนมัติ

**ตัวอย่าง - กรุงเทพฯ:**
```php
use backend\helpers\AddressHelper;

$address = AddressHelper::formatFullAddress([
    'home_number' => '123',
    'aisle' => 'รามคำแหง 24',
    'street' => 'รามคำแหง',
    'district_name' => 'หัวหมาก',
    'city_name' => 'บางกะปิ',
    'province_name' => 'กรุงเทพมหานคร',
    'zipcode' => '10240'
]);

// ผลลัพธ์: "เลขที่ 123 ซอยรามคำแหง 24 ถนนรามคำแหง แขวงหัวหมาก เขตบางกะปิ กรุงเทพมหานคร 10240"
```

**ตัวอย่าง - จังหวัดอื่น:**
```php
$address = AddressHelper::formatFullAddress([
    'home_number' => '456',
    'street' => 'เพชรเกษม',
    'district_name' => 'สามพราน',
    'city_name' => 'สามพราน',
    'province_name' => 'นครปฐม',
    'zipcode' => '73110'
]);

// ผลลัพธ์: "เลขที่ 456 ถนนเพชรเกษม ตำบลสามพราน อำเภอสามพราน จังหวัดนครปฐม 73110"
```

### 2. formatCustomerAddress($customer)
จัดรูปแบบที่อยู่จาก Customer model โดยตรง

**ตัวอย่าง:**
```php
use backend\helpers\AddressHelper;

$customer = Customer::findOne($id);
$address = AddressHelper::formatCustomerAddress($customer);
```

### 3. cleanAddress($address)
ทำความสะอาดที่อยู่ที่มีข้อมูลไม่สมบูรณ์ (ลบคำนำหน้าที่ตามด้วย "-")

**ตัวอย่าง:**
```php
use backend\helpers\AddressHelper;

$dirtyAddress = "เลขที่ 123 ซอย- ถนนรามคำแหง แขวง- เขตบางกะปิ";
$cleanAddress = AddressHelper::cleanAddress($dirtyAddress);

// ผลลัพธ์: "เลขที่ 123 ถนนรามคำแหง เขตบางกะปิ"
```

### 4. isBangkok($province)
ตรวจสอบว่าเป็นกรุงเทพมหานครหรือไม่

**ตัวอย่าง:**
```php
use backend\helpers\AddressHelper;

AddressHelper::isBangkok('กรุงเทพมหานคร');  // true
AddressHelper::isBangkok('กรุงเทพ');         // true
AddressHelper::isBangkok('กทม');             // true
AddressHelper::isBangkok('Bangkok');         // true
AddressHelper::isBangkok('ระยอง');           // false
```

---

## การใช้งานในโปรเจค

### ไฟล์ที่ได้อัพเดทแล้ว:

1. **InvoiceController.php** - `actionGetCustomer()`
2. **Job.php** - `findCustomerData()`
3. **Quotation.php** - `findCustomerData()`
4. **Customer.php** - `findFullAddress()`
5. **print-bill-placement.php** - ทำความสะอาดที่อยู่

### ตัวอย่างการใช้งาน:

#### InvoiceController.php
```php
public function actionGetCustomer($code)
{
    $customer = Customer::findOne(['customer_code' => $code]);
    
    if ($customer) {
        // ใช้ AddressHelper จัดรูปแบบที่อยู่ (แยกกรุงเทพฯ กับจังหวัดอื่นอัตโนมัติ)
        $formattedAddress = \backend\helpers\AddressHelper::formatCustomerAddress($customer);
        
        return [
            'success' => true,
            'data' => [
                'customer_address' => $formattedAddress,
                // ...
            ]
        ];
    }
}
```

#### print-bill-placement.php
```php
<?php
// ใช้ AddressHelper ทำความสะอาดที่อยู่
$address = \backend\helpers\AddressHelper::cleanAddress($model->customer_address ?: '');
echo Html::encode($address);
?>
```

---

## การทำงานของ AddressHelper

### ตรวจสอบจังหวัด
```php
private static function isBangkok($province)
{
    $bangkokNames = [
        'กรุงเทพ', 'กรุงเทพฯ', 'กรุงเทพมหานคร',
        'bangkok', 'Bangkok', 'BANGKOK',
        'กทม', 'กทม.',
    ];
    
    // ตรวจสอบว่า province มีคำใดคำหนึ่งข้างต้นหรือไม่
}
```

### จัดรูปแบบที่อยู่
```php
// ถ้าเป็นกรุงเทพฯ
if ($isBangkok) {
    $parts[] = 'แขวง' . $district_name;  // ไม่ใช่ "ตำบล"
    $parts[] = 'เขต' . $city_name;       // ไม่ใช่ "อำเภอ"
    $parts[] = 'กรุงเทพมหานคร';          // ไม่ใช่ "จังหวัด..."
}
// ถ้าเป็นจังหวัดอื่น
else {
    $parts[] = 'ตำบล' . $district_name;
    $parts[] = 'อำเภอ' . $city_name;
    $parts[] = 'จังหวัด' . $province_name;
}
```

---

## ข้อดี

1. ✅ **แยกการแสดงผลอัตโนมัติ** - ไม่ต้องกังวลว่าเป็นกรุงเทพฯ หรือจังหวัดอื่น
2. ✅ **ใช้งานซ้ำได้** - เรียกใช้ได้จากทุกที่ในระบบ
3. ✅ **โค้ดสะอาด** - ไม่ต้องเขียนโค้ดจัดรูปแบบซ้ำๆ
4. ✅ **ทำความสะอาดข้อมูล** - ลบข้อมูลที่ไม่สมบูรณ์ออกอัตโนมัติ
5. ✅ **บำรุงรักษาง่าย** - แก้ไขที่เดียว ใช้ได้ทุกที่

---

## หมายเหตุ

- ฟังก์ชันจะตรวจสอบว่าข้อมูลแต่ละส่วนว่างหรือไม่ก่อนนำมาแสดง
- ถ้าข้อมูลว่าง จะไม่แสดงคำนำหน้า (เช่น "ซอย", "ถนน")
- รองรับการตรวจสอบกรุงเทพฯ หลายรูปแบบ
- **ไม่ใช้ "ตำบล/แขวง" หรือ "อำเภอ/เขต" แบบคู่กันอีกต่อไป**
