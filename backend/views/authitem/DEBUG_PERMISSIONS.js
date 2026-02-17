/**
 * วิธีทดสอบว่า Permissions ถูกส่งหรือไม่
 * 
 * ทำตามขั้นตอนนี้:
 */

// 1. เปิด Browser Console (กด F12)
// 2. ไปที่ tab Console
// 3. Paste โค้ดนี้แล้วกด Enter:

// ดูจำนวน checkboxes ทั้งหมด
console.log('Total checkboxes:', $('.permission-checkbox').length);

// ดูจำนวนที่ถูกเลือก
console.log('Checked checkboxes:', $('.permission-checkbox:checked').length);

// ดูรายการที่ถูกเลือก
var selected = [];
$('.permission-checkbox:checked').each(function() {
    selected.push($(this).val());
});
console.log('Selected permissions:', selected);

// ดู form data ที่จะถูกส่ง
console.log('Form data:', $('#authitem-form-new').serialize());

// ตรวจสอบว่า permissions[] อยู่ใน form data หรือไม่
var formData = $('#authitem-form-new').serialize();
if (formData.indexOf('permissions') > -1) {
    console.log('✅ Permissions found in form data');
} else {
    console.log('❌ Permissions NOT found in form data');
}

/**
 * 4. หลังจากกดบันทึก ให้ดูที่ Network tab:
 * 
 * - เปิด tab Network
 * - กดบันทึก
 * - คลิกที่ request ที่ส่งไป
 * - ดูที่ tab "Payload" หรือ "Form Data"
 * - ตรวจสอบว่ามี permissions[] หรือไม่
 */

/**
 * 5. ดู Flash Message:
 * 
 * หลังจากบันทึกเสร็จ ดูที่ flash message ว่าแสดงจำนวน permissions เท่าไหร่
 * ถ้าแสดง "อัพเดท 0 permissions" แปลว่าไม่มีข้อมูลส่งมา
 * ถ้าแสดง "อัพเดท 100 permissions" แปลว่ามีข้อมูลส่งมา
 */

/**
 * 6. ตรวจสอบ Log File:
 * 
 * ดูที่ไฟล์ log:
 * - backend/runtime/logs/app.log
 * 
 * ค้นหา:
 * - "Received permissions count"
 * - "Received permissions:"
 * 
 * จะเห็นว่า Controller ได้รับข้อมูลอะไรบ้าง
 */
