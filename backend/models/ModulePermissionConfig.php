<?php

namespace backend\models;

use Yii;
use common\models\UserModulePermission;

class ModulePermissionConfig
{
    /**
     * นิยามรายการโมดูล / เมนูทั้งหมดของ MCO ตามโครงสร้างในภาพ Matrix และ Sidebar
     */
    public static function getModuleList()
    {
        return [
            // 1. ภาพรวมระบบ
            'site' => [
                'name' => 'ภาพรวมระบบ',
                'group' => 'ทั่วไป',
                'description' => 'เข้าหน้าแรกและ Dashboard ภาพรวม',
                'routes_view' => ['site/index'],
                'routes_manage' => ['site/index'],
            ],

            // 2. ค้นหาข้อมูลแต่ละ Job และข้อมูลเก่าทั้งหมด
            'search' => [
                'name' => '1. ค้นหาข้อมูลแต่ละ Job และข้อมูลเก่าทั้งหมด',
                'group' => 'ทั่วไป',
                'description' => 'ค้นหาประวัติ Job และข้อมูลเอกสารเก่าในระบบ',
                'routes_view' => ['search/index', 'search/searchitem'],
                'routes_manage' => ['search/index', 'search/searchitem'],
            ],

            // 3. ข้อมูล Stock และ เครื่องมือ
            'stock_master' => [
                'name' => '2. ข้อมูล Stock และ เครื่องมือ',
                'group' => 'คลังสินค้า & สต๊อก',
                'description' => 'ดูรายการสินค้าคงเหลือและรายงานเบิก-คืนเครื่องมือ',
                'routes_view' => ['stocksum/index', 'stocksum/stockreport', 'stocksum/borrowreturnreport', 'inventoryreport/stockcard'],
                'routes_manage' => ['stocksum/index', 'stocksum/stockreport', 'stocksum/borrowreturnreport', 'inventoryreport/stockcard'],
            ],

            // 4. ทำรายการ (บันทึกรายการสต็อก / เบิก-รับเข้า / ปรับปรุง)
            'stock_journal' => [
                'name' => 'ทำรายการ (บันทึกรายการสต๊อก)',
                'group' => 'คลังสินค้า & สต๊อก',
                'description' => 'บันทึกรายการต่างๆ และประวัติการทำรายการสต๊อก',
                'routes_view' => ['journaltrans/index', 'journaltrans/view', 'stocktrans/index', 'stocktrans/view'],
                'routes_manage' => ['journaltrans/index', 'journaltrans/view', 'journaltrans/create', 'journaltrans/update', 'journaltrans/delete', 'stocktrans/index', 'stocktrans/view'],
            ],

            // 5. ตั้งค่าทั่วไป
            'general_setting' => [
                'name' => 'ตั้งค่าทั่วไป (เงื่อนไขชำระ, บัญชี, ผังบัญชี, สกุลเงิน)',
                'group' => 'การเงิน & บัญชี',
                'description' => 'จัดการการตั้งค่าระบบ บัญชีธนาคาร ผังบัญชี หมวดบัญชี',
                'routes_view' => ['paymentterm/index', 'paymentmethod/index', 'bankaccount/index', 'accountcategory/index', 'chartofaccount/index', 'currency/index', 'paymentextraoption/index'],
                'routes_manage' => [
                    'paymentterm/index', 'paymentterm/create', 'paymentterm/update', 'paymentterm/delete',
                    'paymentmethod/index', 'paymentmethod/create', 'paymentmethod/update', 'paymentmethod/delete',
                    'bankaccount/index', 'bankaccount/create', 'bankaccount/update', 'bankaccount/delete',
                    'accountcategory/index', 'accountcategory/create', 'accountcategory/update', 'accountcategory/delete',
                    'chartofaccount/index', 'chartofaccount/create', 'chartofaccount/update', 'chartofaccount/delete',
                    'currency/index', 'currency/create', 'currency/update', 'currency/delete',
                    'paymentextraoption/index', 'paymentextraoption/create', 'paymentextraoption/update', 'paymentextraoption/delete',
                ],
            ],

            // 6. พนักงาน
            'employee' => [
                'name' => 'พนักงาน (แผนก, ตำแหน่ง, รายชื่อ)',
                'group' => 'บุคคลากร',
                'description' => 'ข้อมูลพนักงาน แผนก และตำแหน่ง',
                'routes_view' => ['department/index', 'department/view', 'position/index', 'position/view', 'employee/index', 'employee/view'],
                'routes_manage' => [
                    'department/index', 'department/view', 'department/create', 'department/update', 'department/delete',
                    'position/index', 'position/view', 'position/create', 'position/update', 'position/delete',
                    'employee/index', 'employee/view', 'employee/create', 'employee/update', 'employee/delete',
                ],
            ],

            // 7. จัดการสต๊อกสินค้า
            'stock_manage' => [
                'name' => 'จัดการสต๊อกสินค้า (กลุ่ม, หน่วยนับ, ยี่ห้อ, สินค้า, คลัง)',
                'group' => 'คลังสินค้า & สต๊อก',
                'description' => 'กำหนดกลุ่มสินค้า หน่วยนับ ยี่ห้อ สินค้า และคลังสินค้า',
                'routes_view' => ['productgroup/index', 'unit/index', 'productbrand/index', 'product/index', 'product/view', 'warehouse/index', 'warehouse/view'],
                'routes_manage' => [
                    'productgroup/index', 'productgroup/create', 'productgroup/update', 'productgroup/delete',
                    'unit/index', 'unit/create', 'unit/update', 'unit/delete',
                    'productbrand/index', 'productbrand/create', 'productbrand/update', 'productbrand/delete',
                    'product/index', 'product/view', 'product/create', 'product/update', 'product/delete',
                    'warehouse/index', 'warehouse/view', 'warehouse/create', 'warehouse/update', 'warehouse/delete',
                ],
            ],

            // 8. เงินสดย่อย
            'petty_cash' => [
                'name' => 'เงินสดย่อย (บันทึก, เบิกทดแทน, รายงาน, ตั้งค่า)',
                'group' => 'การเงิน & บัญชี',
                'description' => 'บันทึกเงินสดย่อย เบิกเงินทดแทน รายงาน และตั้งค่าวงเงิน',
                'routes_view' => ['pettycashvoucher/index', 'pettycashvoucher/view', 'pettycashadvance/index', 'pettycashreport/index', 'pettycashsetting/index'],
                'routes_manage' => [
                    'pettycashvoucher/index', 'pettycashvoucher/view', 'pettycashvoucher/create', 'pettycashvoucher/update', 'pettycashvoucher/delete',
                    'pettycashadvance/index', 'pettycashadvance/view', 'pettycashadvance/create', 'pettycashadvance/update', 'pettycashadvance/delete',
                    'pettycashreport/index', 'pettycashsetting/index', 'pettycashsetting/create', 'pettycashsetting/update',
                ],
            ],

            // 9. เอกสารวางบิล/ใบเสร็จ
            'billing_invoice' => [
                'name' => 'เอกสารวางบิล/ใบเสร็จ (แจ้งหนี้, เพิ่มหนี้, ลดหนี้)',
                'group' => 'การเงิน & บัญชี',
                'description' => 'ใบแจ้งหนี้ วางบิล ใบลดหนี้ ใบเพิ่มหนี้',
                'routes_view' => ['invoice/index', 'invoice/view', 'debitnote/index', 'debitnote/view', 'creditnote/index', 'creditnote/view'],
                'routes_manage' => [
                    'invoice/index', 'invoice/view', 'invoice/create', 'invoice/update', 'invoice/delete',
                    'debitnote/index', 'debitnote/view', 'debitnote/create', 'debitnote/update', 'debitnote/delete',
                    'creditnote/index', 'creditnote/view', 'creditnote/create', 'creditnote/update', 'creditnote/delete',
                ],
            ],

            // 10. รายการสั่งซื้อ
            'purchasing' => [
                'name' => 'รายการสั่งซื้อ (ผู้ขาย, ใบขอซื้อ, ใบสั่งซื้อ, สรุปยอด, None PR)',
                'group' => 'จัดซื้อ',
                'description' => 'ผู้ขาย ใบขอซื้อ PR ใบสั่งซื้อ PO บันทึกซื้อ None PR',
                'routes_view' => ['vendor/index', 'vendor/view', 'purchreq/index', 'purchreq/view', 'purch/index', 'purch/view', 'purch/reportvendorsummary', 'purchasemaster/index', 'purchasemaster/view'],
                'routes_manage' => [
                    'vendor/index', 'vendor/view', 'vendor/create', 'vendor/update', 'vendor/delete',
                    'purchreq/index', 'purchreq/view', 'purchreq/create', 'purchreq/update', 'purchreq/delete', 'purchreq/approve',
                    'purch/index', 'purch/view', 'purch/create', 'purch/update', 'purch/delete', 'purch/approve', 'purch/reportvendorsummary',
                    'purchasemaster/index', 'purchasemaster/view', 'purchasemaster/create', 'purchasemaster/update', 'purchasemaster/delete',
                    'checkduplicate/index',
                ],
            ],

            // 11. A & F Dept
            'af_dept' => [
                'name' => 'A & F Dept (Dashboard, AP, Payment Advance, Payment Voucher, WHT, ภาษีซื้อ)',
                'group' => 'การเงิน & บัญชี',
                'description' => 'ระบบงานบัญชี-การเงิน รายการรอตั้งหนี้ AP, PV, Advance, WHT',
                'routes_view' => ['accountingdashboard/index', 'accountpayable/worklist', 'preadvance/index', 'preadvance/view', 'paymentvoucher/index', 'paymentvoucher/view', 'wht/index', 'purchasetaxreport/index'],
                'routes_manage' => [
                    'accountingdashboard/index', 'accountpayable/worklist',
                    'preadvance/index', 'preadvance/view', 'preadvance/create', 'preadvance/update', 'preadvance/delete', 'preadvance/approve',
                    'paymentvoucher/index', 'paymentvoucher/view', 'paymentvoucher/create', 'paymentvoucher/update', 'paymentvoucher/delete',
                    'wht/index', 'wht/create', 'wht/update', 'wht/delete', 'purchasetaxreport/index',
                ],
            ],

            // 12. รายการเสนอราคา
            'sales_quote' => [
                'name' => 'รายการเสนอราคา (ลูกค้า, ใบเสนอราคา, ใบงาน, ใบตรวจรับงาน, ค่าใช้จ่ายรถ)',
                'group' => 'ขาย & การตลาด',
                'description' => 'ลูกค้า ใบเสนอราคา QT ใบงาน Job ใบตรวจรับงาน Delivery Note และค่าใช้จ่ายรถ',
                'routes_view' => ['customer/index', 'customer/view', 'quotation/index', 'quotation/view', 'job/index', 'job/view', 'deliverynote/index', 'deliverynote/view', 'vehicleexpense/list'],
                'routes_manage' => [
                    'customer/index', 'customer/view', 'customer/create', 'customer/update', 'customer/delete',
                    'quotation/index', 'quotation/view', 'quotation/create', 'quotation/update', 'quotation/delete',
                    'job/index', 'job/view', 'job/create', 'job/update', 'job/delete',
                    'deliverynote/index', 'deliverynote/view', 'deliverynote/create', 'deliverynote/update', 'deliverynote/delete',
                    'vehicleexpense/list', 'vehicleexpense/create', 'vehicleexpense/update', 'vehicleexpense/delete',
                ],
            ],

            // 13. Executive Dash สำหรับผู้บริหาร
            'executive_dash' => [
                'name' => 'Executive Dash สำหรับผู้บริหาร',
                'group' => 'รายงาน & วิเคราะห์',
                'description' => 'Executive Dashboard ภาพรวมผู้บริหาร',
                'routes_view' => ['executivedashboard/index'],
                'routes_manage' => ['executivedashboard/index'],
            ],

            // 14. Report Portal
            'report_portal' => [
                'name' => 'Report Portal (ศูนย์รวมรายงาน, สถานะใบงานรวม, ค่าแรงพนักงานขับรถ)',
                'group' => 'รายงาน & วิเคราะห์',
                'description' => 'Portal Hub รวมรายงาน รายงานใบงาน และรายงานค่าแรงพนักงานขับรถ',
                'routes_view' => ['reportportal/index', 'jobreport/index', 'driverwagereport/index'],
                'routes_manage' => ['reportportal/index', 'jobreport/index', 'driverwagereport/index'],
            ],

            // 15. สแกนเอกสารใหม่
            'ocr_scan' => [
                'name' => 'สแกนเอกสารใหม่',
                'group' => 'OCR & AI',
                'description' => 'อัปโหลดสแกนบิล/เอกสารใหม่เข้าระบบ OCR',
                'routes_view' => ['ocr/index'],
                'routes_manage' => ['ocr/index', 'ocr/upload', 'ocr/process'],
            ],

            // 16. การสแกน OCR
            'ocr_list' => [
                'name' => 'รายการสแกน OCR (Temp Invoice)',
                'group' => 'OCR & AI',
                'description' => 'ดูและตรวจสอบรายการบิลที่ผ่านการสแกน OCR',
                'routes_view' => ['tempinvoice/index', 'tempinvoice/view'],
                'routes_manage' => ['tempinvoice/index', 'tempinvoice/view', 'tempinvoice/create', 'tempinvoice/update', 'tempinvoice/delete', 'tempinvoice/approve'],
            ],

            // 17. จัดการ OCR Pattern
            'ocr_pattern' => [
                'name' => 'จัดการ OCR Pattern',
                'group' => 'OCR & AI',
                'description' => 'ตั้งค่ารูปแบบการอ่านข้อมูล OCR แยกตาม Vendor',
                'routes_view' => ['ocrpattern/index', 'ocrpattern/view'],
                'routes_manage' => ['ocrpattern/index', 'ocrpattern/view', 'ocrpattern/create', 'ocrpattern/update', 'ocrpattern/delete'],
            ],

            // 18. ผู้ใช้งาน
            'user_management' => [
                'name' => 'ผู้ใช้งาน (กลุ่มผู้ใช้, ผู้ใช้งาน, กำหนดสิทธิ์, Action Log)',
                'group' => 'จัดการระบบ',
                'description' => 'บริหารจัดการ User, กลุ่มผู้ใช้งาน และสิทธิ์',
                'routes_view' => ['usergroup/index', 'user/index', 'user/view', 'authitem/index', 'authitem/view', 'actionlog/index', 'userpermission/index'],
                'routes_manage' => [
                    'usergroup/index', 'usergroup/create', 'usergroup/update', 'usergroup/delete',
                    'user/index', 'user/view', 'user/create', 'user/update', 'user/delete',
                    'authitem/index', 'authitem/view', 'authitem/create', 'authitem/update', 'authitem/delete',
                    'actionlog/index', 'userpermission/index', 'userpermission/save',
                ],
            ],

            // 19. สำรองข้อมูล
            'db_backup' => [
                'name' => 'สำรองข้อมูล (Backup & Restore)',
                'group' => 'จัดการระบบ',
                'description' => 'สำรองฐานข้อมูลและกู้คืนข้อมูล',
                'routes_view' => ['dbbackup/backuplist', 'dbrestore/restorepage'],
                'routes_manage' => ['dbbackup/backuplist', 'dbbackup/create', 'dbbackup/download', 'dbrestore/restorepage', 'dbrestore/restore'],
            ],
        ];
    }

    /**
     * ซิงค์สิทธิ์จาก Matrix เข้าสู่ระบบ RBAC ของ Yii2 สำหรับ User ในบริษัทที่กำหนด
     *
     * @param int $userId
     * @param int $companyId
     * @param array $modulePermissions [module_key => 'x'|'0'|'1']
     * @return bool
     */
    public static function syncUserRbacPermissions($userId, $companyId, $modulePermissions)
    {
        $auth = Yii::$app->authManager;
        $modules = self::getModuleList();

        // รวบรวม routes ทั้งหมดที่ต้องมอบให้ User ตามระดับสิทธิ์
        $routesToAssign = [];

        foreach ($modulePermissions as $modKey => $pType) {
            if (!isset($modules[$modKey])) continue;
            $conf = $modules[$modKey];

            if ($pType === UserModulePermission::PERM_VIEW) {
                // ดูอย่างเดียว (0)
                foreach ($conf['routes_view'] as $r) {
                    $routesToAssign[$r] = true;
                }
            } elseif ($pType === UserModulePermission::PERM_MANAGE) {
                // ทำรายการ (1)
                foreach ($conf['routes_manage'] as $r) {
                    $routesToAssign[$r] = true;
                }
            }
            // ถ้าเป็น 'x' จะไม่ใส่ route ใดๆ ให้
        }

        // อนุญาต route พื้นฐานที่จำเป็นสำหรับผู้ใช้ทุกคนเสมอ
        $routesToAssign['site/index'] = true;
        $routesToAssign['site/logout'] = true;
        $routesToAssign['site/changepassword'] = true;
        $routesToAssign['site/changecompany'] = true;
        $routesToAssign['site/change-company'] = true;

        // ดึงสิทธิ์เดิมของ User
        $currentAssignments = $auth->getAssignments($userId);

        // ดึงรายการ Permission routes ทั้งหมดที่มีในระบบเพื่อเปรียบเทียบ
        $allSystemPermissions = $auth->getPermissions();

        // สร้างหรือ Assign permissions ให้ User
        foreach (array_keys($routesToAssign) as $route) {
            $permName = strtolower(str_replace('-', '', $route));
            
            // ตรวจสอบว่าใน auth_item มี Permission นี้หรือยัง หากยังไม่มีให้สร้าง
            $item = $auth->getPermission($permName);
            if (!$item) {
                // ลองหาชื่อตรงๆ
                $item = $auth->getPermission($route);
            }
            if (!$item) {
                $item = $auth->createPermission($permName);
                $item->description = 'Auto created for ' . $route;
                $auth->add($item);
            }

            // Assign ให้ User ถ้ายังไม่มี
            if (!isset($currentAssignments[$item->name])) {
                try {
                    $auth->assign($item, $userId);
                } catch (\Exception $e) {
                    // ข้ามกรณี unique constraint หรือ error เล็กน้อย
                }
            }
        }

        return true;
    }

    /**
     * สลับหรือโหลดสิทธิ์ RBAC ของ User ตามบริษัทที่กำลังทำงานอยู่ในปัจจุบัน
     *
     * @param int $userId
     * @param int $companyId
     * @return void
     */
    public static function applyCompanyPermissionsToUser($userId, $companyId)
    {
        // หากเป็น System Admin มีสิทธิ์ทั้งหมดอยู่แล้ว
        if (\backend\models\User::isUserAdmin($userId)) {
            return;
        }

        // ดึงค่า Matrix สิทธิ์ของ User ในบริษัทนี้
        $userPerms = UserModulePermission::find()
            ->where(['user_id' => $userId, 'company_id' => $companyId])
            ->asArray()
            ->all();

        if (empty($userPerms)) {
            return;
        }

        $moduleMap = [];
        foreach ($userPerms as $up) {
            $moduleMap[$up['module_key']] = $up['permission_type'];
        }

        self::syncUserRbacPermissions($userId, $companyId, $moduleMap);
    }
}
