# การแก้ไขปัญหาระบบ AuthItem

## ปัญหาที่พบและวิธีแก้ไข

### 1. ⚠️ หน้าหมุนนานเมื่อกดบันทึก

**สาเหตุ:**
- `PermissionScanner::scanAllControllers()` ถูกเรียกทุกครั้งที่โหลดหน้า
- การสแกน controllers ทั้งหมดใช้เวลานาน
- ไม่มีการป้องกันการ submit ฟอร์มซ้ำ

**วิธีแก้ไข:**

#### 1.1 เพิ่ม Cache
```php
// ใน __form.php
$cacheKey = 'permission_scanner_controllers';
$cacheDuration = 3600; // 1 ชั่วโมง

$controllers = Yii::$app->cache->getOrSet($cacheKey, function() {
    return PermissionScanner::scanAllControllers();
}, $cacheDuration);
```

#### 1.2 ปรับปรุง Form Submit Handler
```javascript
// ป้องกันการ submit ซ้ำ
var isSubmitting = false;
var submitTimeout;

$('#authitem-form-new').on('beforeSubmit', function(e) {
    if (isSubmitting) {
        return false; // ป้องกันการ submit ซ้ำ
    }
    
    isSubmitting = true;
    var submitBtn = $('#submit-btn');
    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> กำลังบันทึก...');
    submitBtn.prop('disabled', true);
    
    // แสดงข้อความหากใช้เวลานานเกิน 5 วินาที
    submitTimeout = setTimeout(function() {
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> กำลังประมวลผล กรุณารอสักครู่...');
    }, 5000);
    
    return true;
});
```

#### 1.3 เพิ่มการเคลียร์ Cache
```php
// ใน AuthitemController
public function actionClearCache()
{
    Yii::$app->cache->delete('permission_scanner_controllers');
    Yii::$app->session->setFlash('success', 'เคลียร์ cache เรียบร้อยแล้ว');
    return $this->redirect(['index']);
}

public function actionSyncPermissions()
{
    $result = PermissionScanner::syncPermissions();
    
    // เคลียร์ cache หลังซิงค์
    Yii::$app->cache->delete('permission_scanner_controllers');
    
    // ...
}
```

---

### 2. ⚠️ Undefined variable $btn

**สาเหตุ:**
- ใช้ตัวแปร `$btn` ใน setTimeout callback แต่ตัวแปรอยู่นอก scope

**วิธีแก้ไข:**
```javascript
// เปลี่ยนจาก $btn เป็น submitBtn
var submitBtn = $('#submit-btn');
submitBtn.html('...');

setTimeout(function() {
    submitBtn.html('...'); // ใช้ได้เพราะอยู่ใน closure
}, 5000);
```

---

### 3. ⚠️ Duplicate Entry Error

**Error:**
```
SQLSTATE[23000]: Integrity constraint violation: 1062 
Duplicate entry 'System Administrator-actionlog/bulkdelete' for key 'PRIMARY'
```

**สาเหตุ:**
- ฟังก์ชัน `updateRolePermissions` ลบทั้งหมดแล้วเพิ่มใหม่
- บางครั้งมี permission ซ้ำในฐานข้อมูล
- ไม่มีการตรวจสอบก่อนเพิ่ม

**วิธีแก้ไข:**
```php
protected function updateRolePermissions($roleName, $selectedPermissions = [])
{
    $auth = Yii::$app->authManager;
    $role = $auth->getRole($roleName);

    if (!$role) {
        return;
    }

    // ดึง permissions ปัจจุบัน
    $currentPermissions = $auth->getPermissionsByRole($roleName);
    $currentPermissionNames = array_keys($currentPermissions);
    
    // หา permissions ที่ต้องลบ (มีอยู่แต่ไม่ได้เลือก)
    $toRemove = array_diff($currentPermissionNames, $selectedPermissions);
    
    // หา permissions ที่ต้องเพิ่ม (เลือกแต่ยังไม่มี)
    $toAdd = array_diff($selectedPermissions, $currentPermissionNames);

    // ลบ permissions ที่ไม่ต้องการ
    foreach ($toRemove as $permissionName) {
        $permission = $auth->getPermission($permissionName);
        if ($permission) {
            try {
                $auth->removeChild($role, $permission);
            } catch (\Exception $e) {
                Yii::error("Error removing permission {$permissionName}: " . $e->getMessage());
            }
        }
    }

    // เพิ่ม permissions ใหม่
    foreach ($toAdd as $permissionName) {
        $permission = $auth->getPermission($permissionName);
        if ($permission) {
            try {
                // ตรวจสอบว่ามีอยู่แล้วหรือไม่
                if (!$auth->hasChild($role, $permission)) {
                    $auth->addChild($role, $permission);
                }
            } catch (\Exception $e) {
                Yii::error("Error adding permission {$permissionName}: " . $e->getMessage());
            }
        }
    }
}
```

**ข้อดีของวิธีนี้:**
- ✅ ไม่ลบทั้งหมดแล้วเพิ่มใหม่ (มีประสิทธิภาพกว่า)
- ✅ เพิ่มเฉพาะที่ยังไม่มี ลบเฉพาะที่ไม่ต้องการ
- ✅ มี error handling ป้องกัน exception
- ✅ ตรวจสอบด้วย `hasChild()` ก่อนเพิ่ม

---

## สรุปการปรับปรุง

### ไฟล์ที่แก้ไข

1. **backend/views/authitem/__form.php**
   - เพิ่ม cache สำหรับ controllers
   - ปรับปรุง form submit handler
   - แก้ไข JavaScript variable scope

2. **backend/controllers/AuthitemController.php**
   - ปรับปรุง `updateRolePermissions()` ให้มีประสิทธิภาพและปลอดภัยกว่า
   - เพิ่ม `actionClearCache()`
   - เคลียร์ cache ใน `actionSyncPermissions()`

3. **backend/views/authitem/index.php**
   - เพิ่มปุ่ม "เคลียร์ Cache"

### ผลลัพธ์

✅ **หน้าโหลดเร็วขึ้น** - ใช้ cache แทนการสแกนทุกครั้ง
✅ **ป้องกัน duplicate error** - ตรวจสอบก่อนเพิ่ม permission
✅ **UX ดีขึ้น** - แสดงสถานะการบันทึกชัดเจน
✅ **ปลอดภัยกว่า** - มี error handling ครบถ้วน
✅ **มีประสิทธิภาพกว่า** - อัพเดทเฉพาะส่วนที่เปลี่ยนแปลง

---

## การใช้งาน

### เคลียร์ Cache
```
1. ไปที่ AuthItem > Index
2. คลิกปุ่ม "เคลียร์ Cache"
```

### ซิงค์ Permissions
```
1. ไปที่ AuthItem > Index
2. คลิกปุ่ม "ซิงค์ Permissions"
3. Cache จะถูกเคลียร์อัตโนมัติ
```

### บันทึก Role
```
1. แก้ไข Role
2. เลือก Permissions
3. คลิก "บันทึก"
4. รอจนกว่าจะเสร็จ (ไม่ควรกดซ้ำ)
```

---

## Tips

1. **หากหน้ายังช้า** - ลองเคลียร์ cache
2. **หากเจอ duplicate error** - ตรวจสอบฐานข้อมูล `auth_item_child` ว่ามีข้อมูลซ้ำหรือไม่
3. **หากเพิ่ม controller ใหม่** - อย่าลืมซิงค์ permissions
4. **Cache จะหมดอายุ** - ทุก 1 ชั่วโมง (3600 วินาที)

---

## การแก้ปัญหาเพิ่มเติม

### ถ้ายังมี Duplicate Entry
```sql
-- ตรวจสอบข้อมูลซ้ำ
SELECT parent, child, COUNT(*) as count 
FROM auth_item_child 
GROUP BY parent, child 
HAVING count > 1;

-- ลบข้อมูลซ้ำ (ระวัง!)
DELETE t1 FROM auth_item_child t1
INNER JOIN auth_item_child t2 
WHERE t1.parent = t2.parent 
AND t1.child = t2.child 
AND t1.id > t2.id;
```

### ถ้าต้องการเพิ่มเวลา Cache
```php
// ใน __form.php
$cacheDuration = 7200; // 2 ชั่วโมง
```

### ถ้าต้องการปิด Cache
```php
// ใน __form.php
$controllers = PermissionScanner::scanAllControllers(); // ไม่ใช้ cache
```

---

**อัพเดทล่าสุด:** 2026-02-15 19:10:00
