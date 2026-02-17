# ระบบจัดการสิทธิ์ (AuthItem) แบบใหม่

## ภาพรวม

ระบบจัดการสิทธิ์ที่ได้รับการออกแบบใหม่ทั้งหมด เพื่อให้การจัดการสิทธิ์ของผู้ใช้งานเป็นไปอย่างง่ายดาย ครบถ้วน และมีประสิทธิภาพ

## ฟีเจอร์หลัก

### 1. **การสแกน Permissions อัตโนมัติ**
- สแกนหา Controllers และ Actions ทั้งหมดในระบบอัตโนมัติ
- ไม่ต้องสร้าง permissions ด้วยตนเองอีกต่อไป
- อัพเดทอัตโนมัติเมื่อมี controller หรือ action ใหม่

### 2. **เทมเพลต Role สำเร็จรูป**
มีเทมเพลตสำหรับตำแหน่งงานต่างๆ:
- **ผู้ดูแลระบบ (Admin)** - สิทธิ์เต็มรูปแบบ
- **ผู้จัดการ (Manager)** - จัดการและอนุมัติ
- **ฝ่ายขาย (Sales)** - จัดการเอกสารการขาย
- **ฝ่ายจัดซื้อ (Purchase)** - จัดการเอกสารการซื้อ
- **พนักงานคลัง (Warehouse)** - จัดการสต็อกสินค้า
- **ฝ่ายผลิต (Production)** - จัดการการผลิต
- **ฝ่ายบัญชี (Accountant)** - ตรวจสอบเอกสารทางการเงิน
- **ผู้ดูข้อมูล (Viewer)** - ดูข้อมูลอย่างเดียว
- **พนักงานบันทึกข้อมูล (Data Entry)** - บันทึกข้อมูลพื้นฐาน

### 3. **UI ที่ใช้งานง่าย**
- **จัดกลุ่มตาม Category** - แยกเป็นหมวดหมู่ชัดเจน (ระบบ, การขาย, การซื้อ, คลังสินค้า, ฯลฯ)
- **Accordion Layout** - ขยาย/ย่อเพื่อดูรายละเอียด
- **จัดกลุ่มตามประเภท Action** - แยกเป็น อ่าน, เขียน, ลบ, ส่งออก, อนุมัติ
- **Bulk Operations** - เลือกทั้งหมด, เลือกตามประเภท, เลือกตาม module
- **Search & Filter** - ค้นหาและกรองได้ง่าย
- **Progress Bar** - แสดงความคืบหน้าการกำหนดสิทธิ์
- **สถิติแบบเรียลไทม์** - แสดงจำนวนสิทธิ์ที่เลือกแล้ว

### 4. **ระบบจัดหมวดหมู่**
- **System** - ระบบหลัก, ผู้ใช้งาน, สิทธิ์
- **Sales** - ใบเสนอราคา, ใบสั่งขาย, ใบกำกับภาษี
- **Purchase** - ใบสั่งซื้อ, ใบขอซื้อ
- **Inventory** - สินค้า, คลังสินค้า, สต็อก
- **Production** - แผนการผลิต, ใบงาน
- **Master** - ข้อมูลหลัก (ลูกค้า, ผู้จัดจำหน่าย, พนักงาน)
- **Report** - รายงานต่างๆ

### 5. **ระบุ Action ที่อันตราย**
- ระบบจะแสดงเครื่องหมายเตือนสำหรับ actions ที่อันตราย เช่น delete, bulkdelete
- ช่วยป้องกันการให้สิทธิ์ที่อันตรายโดยไม่ตั้งใจ

## การใช้งาน

### 1. ซิงค์ Permissions
```
ไปที่: AuthItem > Index
คลิก: "ซิงค์ Permissions"
```
ระบบจะสแกนหา controllers และ actions ทั้งหมด แล้วสร้าง/อัพเดท permissions อัตโนมัติ

### 2. สร้าง Role จากเทมเพลต
```
ไปที่: AuthItem > Index
คลิก: "สร้างจากเทมเพลต" > เลือกเทมเพลตที่ต้องการ
```
ระบบจะสร้าง role พร้อมกำหนดสิทธิ์ตามเทมเพลตที่เลือก

### 3. สร้าง Role แบบกำหนดเอง
```
ไปที่: AuthItem > สร้างใหม่
กรอกข้อมูล: ชื่อ, คำอธิบาย
เลือกสิทธิ์: ใช้ตาราง Matrix หรือเลือกจากเทมเพลต
บันทึก
```

### 4. แก้ไข Role
```
ไปที่: AuthItem > Index > คลิกแก้ไข
ปรับเปลี่ยนสิทธิ์: ใช้ checkbox หรือ bulk operations
บันทึก
```

## โครงสร้างไฟล์

```
backend/
├── controllers/
│   └── AuthitemController.php          # Controller หลัก
├── helpers/
│   ├── PermissionScanner.php           # สแกน permissions อัตโนมัติ
│   └── RoleTemplate.php                # จัดการเทมเพลต
├── views/
│   └── authitem/
│       ├── index.php                   # หน้ารายการ
│       ├── create.php                  # หน้าสร้าง
│       ├── update.php                  # หน้าแก้ไข
│       ├── __form.php                  # ฟอร์มใหม่ (ใช้งาน)
│       └── _form.php                   # ฟอร์มเก่า (สำรอง)
└── models/
    ├── Authitem.php                    # Model หลัก
    └── AuthitemSearch.php              # Search model
```

## คุณสมบัติของ PermissionScanner

### Methods
- `scanAllControllers()` - สแกนหา controllers ทั้งหมด
- `syncPermissions()` - ซิงค์ permissions กับฐานข้อมูล
- `getCategories()` - รับรายการ categories
- `createPermissionName($controller, $action)` - สร้างชื่อ permission

### ข้อมูลที่ได้จากการสแกน
```php
[
    'name' => 'customer',
    'label' => 'ลูกค้า',
    'className' => 'backend\controllers\CustomerController',
    'category' => 'master',
    'description' => 'จัดการข้อมูลลูกค้า',
    'actions' => [
        'index' => [
            'name' => 'index',
            'label' => 'ดูรายการ',
            'type' => 'read',
            'dangerous' => false
        ],
        // ...
    ]
]
```

## คุณสมบัติของ RoleTemplate

### Methods
- `getTemplates()` - รับรายการเทมเพลตทั้งหมด
- `createRoleFromTemplate($templateKey)` - สร้าง role จากเทมเพลต
- `getTemplatePermissionCount($templateKey)` - นับจำนวน permissions

### โครงสร้างเทมเพลต
```php
[
    'name' => 'ชื่อเทมเพลต',
    'description' => 'คำอธิบาย',
    'icon' => 'fas fa-icon',
    'color' => '#hexcolor',
    'permissions' => [
        'categories' => ['sales', 'purchase'],
        'controllers' => ['quotation', 'invoice'],
        'actions' => ['index', 'view', 'create', 'update']
    ]
]
```

## Bulk Operations

### ปุ่มควบคุม
- **เลือกทั้งหมด** - เลือก permissions ทั้งหมด
- **ยกเลิกทั้งหมด** - ยกเลิกการเลือกทั้งหมด
- **อ่านอย่างเดียว** - เลือกเฉพาะ actions ประเภท read (index, view)
- **พื้นฐาน** - เลือก read + write (index, view, create, update)
- **เต็มรูปแบบ** - เลือกทั้งหมดยกเว้น actions ที่อันตราย
- **ขยายทั้งหมด** - ขยาย accordions ทั้งหมด
- **ย่อทั้งหมด** - ย่อ accordions ทั้งหมด

### Selectors
- **Category Selector** - เลือกทั้ง category
- **Controller Selector** - เลือกทั้ง controller
- **Action Column Selector** - เลือกทั้งคอลัมน์ (ทุก controller ของ action นั้น)

## การปรับแต่ง

### เพิ่ม Controller Label
แก้ไขใน `PermissionScanner::getControllerLabel()`

### เพิ่ม Action Label
แก้ไขใน `PermissionScanner::getActionLabel()`

### เพิ่ม Category
แก้ไขใน `PermissionScanner::getControllerCategory()` และ `PermissionScanner::getCategories()`

### สร้างเทมเพลตใหม่
แก้ไขใน `RoleTemplate::getTemplates()`

## Tips & Best Practices

1. **ซิงค์ Permissions เป็นประจำ** - ทุกครั้งที่เพิ่ม controller หรือ action ใหม่
2. **ใช้เทมเพลตเป็นจุดเริ่มต้น** - แล้วค่อยปรับแต่งตามความต้องการ
3. **ตรวจสอบ Actions ที่อันตราย** - ก่อนให้สิทธิ์ delete หรือ bulkdelete
4. **ใช้ Search & Filter** - เมื่อมี controllers จำนวนมาก
5. **ตรวจสอบ Progress Bar** - เพื่อดูว่าให้สิทธิ์ไปแล้วเท่าไหร่

## Troubleshooting

### ไม่พบ Controller บางตัว
- ตรวจสอบว่า controller อยู่ใน `backend/controllers`
- ตรวจสอบว่า class name ลงท้ายด้วย `Controller`
- ลองซิงค์ permissions ใหม่

### Permissions ไม่อัพเดท
- คลิก "ซิงค์ Permissions" เพื่ออัพเดท
- ตรวจสอบ database table `auth_item`

### Template ไม่ทำงาน
- ตรวจสอบว่าได้ซิงค์ permissions แล้ว
- ตรวจสอบ category และ controller names ใน template

## การอัพเกรดจากระบบเก่า

ระบบใหม่ยังคง backward compatible กับระบบเก่า:
- ไฟล์ `_form.php` เก่ายังคงอยู่
- ข้อมูล permissions เดิมยังใช้งานได้
- สามารถสลับกลับไปใช้ form เก่าได้ตลอดเวลา

## สรุป

ระบบจัดการสิทธิ์แบบใหม่นี้ออกแบบมาเพื่อ:
- ✅ ลดเวลาในการจัดการสิทธิ์
- ✅ ครบถ้วนทุก action ของทุก controller
- ✅ ใช้งานง่าย มี UI ที่สวยงาม
- ✅ ยืดหยุ่น ปรับแต่งได้ตามต้องการ
- ✅ ปลอดภัย มีการเตือนสำหรับ actions ที่อันตราย
- ✅ อัตโนมัติ สแกนและอัพเดทเอง

---

**หมายเหตุ:** หากพบปัญหาหรือต้องการความช่วยเหลือ กรุณาติดต่อทีมพัฒนา
