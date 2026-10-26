<?php

namespace backend\models;

use Yii;
use common\models\UserModulePermission;

class ModulePermissionConfig
{
    /**
     * นิยามรายการโมดูล / เมนูทั้งหมดของ MCO แยกเป็น หมวดทั่วไป, จัดการเอกสาร, และ สิทธิ์การอนุมัติ & Recall
     */
    public static function getModuleList()
    {
        return [
            // ================= 1. ทั่วไป & ค้นหา =================
            'site' => [
                'name' => 'ภาพรวมระบบ',
                'group' => '1. ทั่วไป',
                'description' => 'เข้าหน้าแรกและ Dashboard ภาพรวม',
                'routes_view' => ['site/index'],
                'routes_manage' => ['site/index'],
            ],
            'search' => [
                'name' => '1. ค้นหาข้อมูลแต่ละ Job และข้อมูลเก่าทั้งหมด',
                'group' => '1. ทั่วไป',
                'description' => 'ค้นหาประวัติ Job และข้อมูลเอกสารเก่าในระบบ',
                'routes_view' => ['search/index', 'search/searchitem'],
                'routes_manage' => ['search/index', 'search/searchitem'],
            ],

            // ================= 2. คลังสินค้า & สต๊อก =================
            'stock_master' => [
                'name' => '2. ข้อมูล Stock และ เครื่องมือ (สินค้าคงเหลือ & รายงาน)',
                'group' => '2. คลังสินค้า & สต๊อก',
                'description' => 'ดูรายการสินค้าคงเหลือ รายงานเบิก-คืน และสต็อกการ์ด',
                'routes_view' => ['stocksum/index', 'stocksum/stockreport', 'stocksum/borrowreturnreport', 'inventoryreport/stockcard'],
                'routes_manage' => ['stocksum/index', 'stocksum/stockreport', 'stocksum/borrowreturnreport', 'inventoryreport/stockcard'],
            ],
            'stock_manage' => [
                'name' => 'จัดการสต๊อกสินค้า (กลุ่ม, หน่วยนับ, ยี่ห้อ, สินค้า, คลัง)',
                'group' => '2. คลังสินค้า & สต๊อก',
                'description' => 'กำหนดกลุ่มสินค้า หน่วยนับ ยี่ห้อ ข้อมูลสินค้า และคลังสินค้า',
                'routes_view' => ['productgroup/index', 'unit/index', 'productbrand/index', 'product/index', 'product/view', 'warehouse/index', 'warehouse/view'],
                'routes_manage' => [
                    'productgroup/index', 'productgroup/create', 'productgroup/update', 'productgroup/delete',
                    'unit/index', 'unit/create', 'unit/update', 'unit/delete',
                    'productbrand/index', 'productbrand/create', 'productbrand/update', 'productbrand/delete',
                    'product/index', 'product/view', 'product/create', 'product/update', 'product/delete',
                    'warehouse/index', 'warehouse/view', 'warehouse/create', 'warehouse/update', 'warehouse/delete',
                ],
            ],
            'stock_journal' => [
                'name' => 'ทำรายการสต๊อก (บันทึกรับ-จ่าย-โอน & ประวัติสต๊อก)',
                'group' => '2. คลังสินค้า & สต๊อก',
                'description' => 'บันทึกรายการสต๊อกต่างๆ และประวัติการทำรายการสต๊อก',
                'routes_view' => ['journaltrans/index', 'journaltrans/view', 'stocktrans/index', 'stocktrans/view'],
                'routes_manage' => [
                    'journaltrans/index', 'journaltrans/view', 'journaltrans/create', 'journaltrans/update', 'journaltrans/delete',
                    'stocktrans/index', 'stocktrans/view',
                ],
            ],

            // ================= 3. งานจัดซื้อ (Purchasing) =================
            'vendor_manage' => [
                'name' => 'ผู้ขาย (Vendor)',
                'group' => '3. จัดซื้อ (Purchasing)',
                'description' => 'ข้อมูลผู้ขายและผู้ให้บริการ',
                'routes_view' => ['vendor/index', 'vendor/view'],
                'routes_manage' => ['vendor/index', 'vendor/view', 'vendor/create', 'vendor/update', 'vendor/delete'],
            ],
            'purch_req' => [
                'name' => 'ใบขอซื้อ (PR - Purchase Request)',
                'group' => '3. จัดซื้อ (Purchasing)',
                'description' => 'สร้างและจัดการใบขอซื้อ (ผู้ทำรายการส่ง Request Recall ได้)',
                'routes_view' => ['purchreq/index', 'purchreq/view'],
                'routes_manage' => [
                    'purchreq/index', 'purchreq/view', 'purchreq/create', 'purchreq/update', 'purchreq/delete',
                    'purchreq/requestrecall',
                ],
            ],
            'purch_order' => [
                'name' => 'ใบสั่งซื้อ (PO - Purchase Order)',
                'group' => '3. จัดซื้อ (Purchasing)',
                'description' => 'สร้างและจัดการใบสั่งซื้อ (ผู้ทำรายการส่ง Request Recall ได้)',
                'routes_view' => ['purch/index', 'purch/view', 'purch/reportvendorsummary'],
                'routes_manage' => [
                    'purch/index', 'purch/view', 'purch/create', 'purch/update', 'purch/delete',
                    'purch/requestrecall', 'purch/reportvendorsummary',
                ],
            ],
            'purch_none_pr' => [
                'name' => 'บันทึกข้อมูลซื้อ (None PR)',
                'group' => '3. จัดซื้อ (Purchasing)',
                'description' => 'บันทึกรายการซื้อที่ไม่มีใบขอซื้อ',
                'routes_view' => ['purchasemaster/index', 'purchasemaster/view'],
                'routes_manage' => [
                    'purchasemaster/index', 'purchasemaster/view', 'purchasemaster/create', 'purchasemaster/update', 'purchasemaster/delete',
                    'purchasemaster/requestrecall',
                ],
            ],

            // ================= 4. การเงิน & บัญชี (A & F Dept) =================
            'general_setting' => [
                'name' => 'ตั้งค่าทั่วไป (เงื่อนไขชำระ, บัญชีธนาคาร, ผังบัญชี, หมวดบัญชี)',
                'group' => '4. การเงิน & บัญชี',
                'description' => 'เงื่อนไขชำระเงิน วิธีชำระเงิน บัญชีธนาคาร ผังบัญชี และหมวดบัญชี',
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
            'petty_cash' => [
                'name' => 'เงินสดย่อย (บันทึก, เบิกทดแทน, รายงาน, ตั้งค่าวงเงิน)',
                'group' => '4. การเงิน & บัญชี',
                'description' => 'บันทึกใบสำคัญจ่ายเงินสดย่อย เบิกเงินทดแทน รายงาน (ขอ Recall ได้)',
                'routes_view' => ['pettycashvoucher/index', 'pettycashvoucher/view', 'pettycashadvance/index', 'pettycashreport/index', 'pettycashsetting/index'],
                'routes_manage' => [
                    'pettycashvoucher/index', 'pettycashvoucher/view', 'pettycashvoucher/create', 'pettycashvoucher/update', 'pettycashvoucher/delete',
                    'pettycashvoucher/requestrecall',
                    'pettycashadvance/index', 'pettycashadvance/view', 'pettycashadvance/create', 'pettycashadvance/update', 'pettycashadvance/delete',
                    'pettycashadvance/requestrecall',
                    'pettycashreport/index', 'pettycashsetting/index', 'pettycashsetting/create', 'pettycashsetting/update',
                ],
            ],
            'pre_advance' => [
                'name' => 'Payment Advance (เบิกเงินทดรองจ่ายล่วงหน้า)',
                'group' => '4. การเงิน & บัญชี',
                'description' => 'บันทึกและจัดการเงินทดรองจ่ายล่วงหน้า (ขอ Recall ได้)',
                'routes_view' => ['preadvance/index', 'preadvance/view'],
                'routes_manage' => [
                    'preadvance/index', 'preadvance/view', 'preadvance/create', 'preadvance/update', 'preadvance/delete',
                    'preadvance/requestrecall',
                ],
            ],
            'payment_voucher' => [
                'name' => 'Payment Voucher (ใบสำคัญจ่าย)',
                'group' => '4. การเงิน & บัญชี',
                'description' => 'จัดการใบสำคัญจ่าย PV',
                'routes_view' => ['paymentvoucher/index', 'paymentvoucher/view'],
                'routes_manage' => ['paymentvoucher/index', 'paymentvoucher/view', 'paymentvoucher/create', 'paymentvoucher/update', 'paymentvoucher/delete'],
            ],
            'billing_invoice' => [
                'name' => 'เอกสารวางบิล/ใบเสร็จ (ใบแจ้งหนี้, เพิ่มหนี้, ลดหนี้)',
                'group' => '4. การเงิน & บัญชี',
                'description' => 'ใบแจ้งหนี้ วางบิล ใบลดหนี้ ใบเพิ่มหนี้',
                'routes_view' => ['invoice/index', 'invoice/view', 'debitnote/index', 'debitnote/view', 'creditnote/index', 'creditnote/view'],
                'routes_manage' => [
                    'invoice/index', 'invoice/view', 'invoice/create', 'invoice/update', 'invoice/delete',
                    'debitnote/index', 'debitnote/view', 'debitnote/create', 'debitnote/update', 'debitnote/delete',
                    'creditnote/index', 'creditnote/view', 'creditnote/create', 'creditnote/update', 'creditnote/delete',
                ],
            ],
            'af_tax_report' => [
                'name' => 'ภาษี & บัญชี (WHT หัก ณ ที่จ่าย, รายงานภาษีซื้อ, AP Worklist)',
                'group' => '4. การเงิน & บัญชี',
                'description' => 'จัดการหัก ณ ที่จ่าย WHT, รายงานภาษีซื้อ, รายการรอตั้งหนี้ AP',
                'routes_view' => ['accountingdashboard/index', 'accountpayable/worklist', 'wht/index', 'purchasetaxreport/index'],
                'routes_manage' => [
                    'accountingdashboard/index', 'accountpayable/worklist',
                    'wht/index', 'wht/create', 'wht/update', 'wht/delete', 'purchasetaxreport/index',
                ],
            ],

            // ================= 5. งานขาย & บริการ =================
            'sales_quote' => [
                'name' => 'รายการเสนอราคา & ลูกค้า (ลูกค้า, QT, ใบงาน Job, ใบตรวจรับงาน, ค่าใช้จ่ายรถ)',
                'group' => '5. ขาย & บริการ',
                'description' => 'ลูกค้า ใบเสนอราคา QT ใบงาน Job ใบตรวจรับงาน Delivery Note และค่าใช้จ่ายรถ',
                'routes_view' => ['customer/index', 'customer/view', 'quotation/index', 'quotation/view', 'job/index', 'job/view', 'deliverynote/index', 'deliverynote/view', 'vehicleexpense/list'],
                'routes_manage' => [
                    'customer/index', 'customer/view', 'customer/create', 'customer/update', 'customer/delete',
                    'quotation/index', 'quotation/view', 'quotation/create', 'quotation/update', 'quotation/delete',
                    'quotation/requestrecall',
                    'job/index', 'job/view', 'job/create', 'job/update', 'job/delete',
                    'deliverynote/index', 'deliverynote/view', 'deliverynote/create', 'deliverynote/update', 'deliverynote/delete',
                    'vehicleexpense/list', 'vehicleexpense/create', 'vehicleexpense/update', 'vehicleexpense/delete',
                ],
            ],

            // ================= 6. สิทธิ์การอนุมัติ & จัดการ Recall (Approver Rights) =================
            'appr_purch_req' => [
                'name' => 'ผู้อนุมัติใบขอซื้อ (Approve / Reject PR)',
                'group' => '6. สิทธิ์การอนุมัติ & Recall (Approver Rights)',
                'description' => 'สิทธิ์ในการกดอนุมัติ (Approve) หรือไม่อนุมัติ (Reject) ใบขอซื้อ PR',
                'routes_view' => [],
                'routes_manage' => [
                    'purchreq/approve',
                    'purchreq/reject',
                ],
            ],
            'appr_purch_order' => [
                'name' => 'ผู้อนุมัติใบสั่งซื้อ (Approve / Reject PO)',
                'group' => '6. สิทธิ์การอนุมัติ & Recall (Approver Rights)',
                'description' => 'สิทธิ์ในการกดอนุมัติ (Approve) หรือไม่อนุมัติ (Reject) ใบสั่งซื้อ PO',
                'routes_view' => [],
                'routes_manage' => [
                    'purch/approve',
                    'purch/reject',
                ],
            ],
            'appr_purch_none_pr' => [
                'name' => 'ผู้อนุมัติข้อมูลซื้อ None PR (Approve None PR)',
                'group' => '6. สิทธิ์การอนุมัติ & Recall (Approver Rights)',
                'description' => 'สิทธิ์ในการกดอนุมัติการซื้อ None PR',
                'routes_view' => [],
                'routes_manage' => [
                    'purchasemaster/approve',
                ],
            ],
            'appr_petty_cash' => [
                'name' => 'ผู้อนุมัติเงินสดย่อย / ทดแทน (Approve Petty Cash & Advance)',
                'group' => '6. สิทธิ์การอนุมัติ & Recall (Approver Rights)',
                'description' => 'สิทธิ์ในการกดอนุมัติเงินสดย่อยและเงินเบิกทดแทน',
                'routes_view' => [],
                'routes_manage' => [
                    'pettycashvoucher/approve',
                    'pettycashadvance/approve',
                    'pettycashadvance/reject',
                ],
            ],
            'appr_pre_advance' => [
                'name' => 'ผู้อนุมัติ Payment Advance (Approve Pre-Advance)',
                'group' => '6. สิทธิ์การอนุมัติ & Recall (Approver Rights)',
                'description' => 'สิทธิ์ในการกดอนุมัติเงินทดรองจ่ายล่วงหน้า',
                'routes_view' => [],
                'routes_manage' => [
                    'preadvance/approve',
                    'preadvance/reject',
                    'pre_advance_approver',
                ],
            ],
            'appr_quotation' => [
                'name' => 'ผู้อนุมัติใบเสนอราคา (Approve Quotation)',
                'group' => '6. สิทธิ์การอนุมัติ & Recall (Approver Rights)',
                'description' => 'สิทธิ์ในการกดอนุมัติใบเสนอราคา QT',
                'routes_view' => [],
                'routes_manage' => [
                    'quotation/approve',
                    'quotation/reject',
                ],
            ],
            'appr_recall_hub' => [
                'name' => 'ศูนย์อนุมัติ Recall เอกสาร (Approve / Reject Recall Hub)',
                'group' => '6. สิทธิ์การอนุมัติ & Recall (Approver Rights)',
                'description' => 'เข้าหน้าศูนย์คำร้องขอ Recall และมีสิทธิ์อนุมัติ/ปฏิเสธการดึงเอกสารกลับทุกโมดูล',
                'routes_view' => ['recall/index'],
                'routes_manage' => [
                    'recall/index',
                    'recall/approve',
                    'recall/reject',
                    'purchreq/approverecall',
                    'purchreq/rejectrecall',
                    'purch/approverecall',
                    'purch/rejectrecall',
                    'purchasemaster/approverecall',
                    'purchasemaster/rejectrecall',
                    'pettycashvoucher/approverecall',
                    'pettycashvoucher/rejectrecall',
                    'pettycashadvance/approverecall',
                    'pettycashadvance/rejectrecall',
                    'preadvance/approverecall',
                    'preadvance/rejectrecall',
                    'quotation/approverecall',
                    'quotation/rejectrecall',
                    'approver',
                ],
            ],

            // ================= 7. บุคลากร & พนักงาน =================
            'employee' => [
                'name' => 'พนักงาน (แผนก, ตำแหน่ง, รายชื่อ)',
                'group' => '7. บุคลากร & พนักงาน',
                'description' => 'ข้อมูลพนักงาน แผนก และตำแหน่ง',
                'routes_view' => ['department/index', 'department/view', 'position/index', 'position/view', 'employee/index', 'employee/view'],
                'routes_manage' => [
                    'department/index', 'department/view', 'department/create', 'department/update', 'department/delete',
                    'position/index', 'position/view', 'position/create', 'position/update', 'position/delete',
                    'employee/index', 'employee/view', 'employee/create', 'employee/update', 'employee/delete',
                ],
            ],

            // ================= 8. รายงาน & ผู้บริหาร =================
            'executive_dash' => [
                'name' => 'Executive Dash สำหรับผู้บริหาร',
                'group' => '8. รายงาน & ผู้บริหาร',
                'description' => 'Executive Dashboard ภาพรวมผู้บริหาร',
                'routes_view' => ['executivedashboard/index'],
                'routes_manage' => ['executivedashboard/index'],
            ],
            'report_portal' => [
                'name' => 'Report Portal (ศูนย์รวมรายงาน, สถานะใบงานรวม, ค่าแรงคนขับรถ)',
                'group' => '8. รายงาน & ผู้บริหาร',
                'description' => 'Portal Hub รวมรายงาน รายงานใบงาน และรายงานค่าแรงพนักงานขับรถ',
                'routes_view' => ['reportportal/index', 'jobreport/index', 'driverwagereport/index'],
                'routes_manage' => ['reportportal/index', 'jobreport/index', 'driverwagereport/index'],
            ],

            // ================= 9. OCR & สแกนเอกสาร =================
            'ocr_scan' => [
                'name' => 'สแกนเอกสารใหม่ & รายการสแกน OCR (Temp Invoice)',
                'group' => '9. OCR & AI',
                'description' => 'อัปโหลดสแกนบิลและตรวจสอบรายการบิลที่ผ่านการสแกน OCR',
                'routes_view' => ['ocr/index', 'tempinvoice/index', 'tempinvoice/view'],
                'routes_manage' => ['ocr/index', 'ocr/upload', 'ocr/process', 'tempinvoice/index', 'tempinvoice/view', 'tempinvoice/create', 'tempinvoice/update', 'tempinvoice/delete', 'tempinvoice/approve'],
            ],
            'ocr_pattern' => [
                'name' => 'จัดการ OCR Pattern',
                'group' => '9. OCR & AI',
                'description' => 'ตั้งค่ารูปแบบการอ่านข้อมูล OCR แยกตาม Vendor',
                'routes_view' => ['ocrpattern/index', 'ocrpattern/view'],
                'routes_manage' => ['ocrpattern/index', 'ocrpattern/view', 'ocrpattern/create', 'ocrpattern/update', 'ocrpattern/delete'],
            ],

            // ================= 10. จัดการระบบ =================
            'user_management' => [
                'name' => 'ผู้ใช้งาน (กลุ่มผู้ใช้, ผู้ใช้งาน, กำหนดสิทธิ์ Matrix, Action Log)',
                'group' => '10. จัดการระบบ',
                'description' => 'บริหารจัดการ User, กลุ่มผู้ใช้งาน และกำหนดสิทธิ์',
                'routes_view' => ['usergroup/index', 'user/index', 'user/view', 'authitem/index', 'authitem/view', 'actionlog/index', 'userpermission/index'],
                'routes_manage' => [
                    'usergroup/index', 'usergroup/create', 'usergroup/update', 'usergroup/delete',
                    'user/index', 'user/view', 'user/create', 'user/update', 'user/delete',
                    'authitem/index', 'authitem/view', 'authitem/create', 'authitem/update', 'authitem/delete',
                    'actionlog/index', 'userpermission/index', 'userpermission/save',
                ],
            ],
            'db_backup' => [
                'name' => 'สำรองข้อมูล (Backup & Restore)',
                'group' => '10. จัดการระบบ',
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
                if (!empty($conf['routes_view'])) {
                    foreach ($conf['routes_view'] as $r) {
                        $routesToAssign[$r] = true;
                    }
                }
            } elseif ($pType === UserModulePermission::PERM_MANAGE) {
                // ทำรายการ (1) หรือ อนุมัติ (1)
                if (!empty($conf['routes_manage'])) {
                    foreach ($conf['routes_manage'] as $r) {
                        $routesToAssign[$r] = true;
                    }
                }
            }
            // ถ้าเป็น 'x' จะไม่ใส่ route ใดๆ ให้
        }

        // รวบรวม routes ที่ครอบคลุมใน matrix เพื่อจัดการ revoke สิทธิ์เดิมออกเมื่อเลือก 'x'
        $allMatrixPermNames = [];
        foreach ($modules as $conf) {
            if (!empty($conf['routes_view'])) {
                foreach ($conf['routes_view'] as $r) {
                    $allMatrixPermNames[strtolower(str_replace('-', '', $r))] = true;
                    $allMatrixPermNames[$r] = true;
                }
            }
            if (!empty($conf['routes_manage'])) {
                foreach ($conf['routes_manage'] as $r) {
                    $allMatrixPermNames[strtolower(str_replace('-', '', $r))] = true;
                    $allMatrixPermNames[$r] = true;
                }
            }
        }

        // อนุญาต route พื้นฐานที่จำเป็นสำหรับผู้ใช้ทุกคนเสมอ (ไม่รวม site/index ซึ่งขึ้นกับสิทธิ์ Matrix)
        $routesToAssign['site/logout'] = true;
        $routesToAssign['site/changepassword'] = true;
        $routesToAssign['site/changecompany'] = true;
        $routesToAssign['site/change-company'] = true;

        // แปลง routesToAssign ให้อยู่ในรูป permission names ที่จะมอบให้
        $targetPermNames = [];
        foreach (array_keys($routesToAssign) as $route) {
            $permName = strtolower(str_replace('-', '', $route));
            $targetPermNames[$permName] = $route;
        }

        // ดึงสิทธิ์เดิมของ User
        $currentAssignments = $auth->getAssignments($userId);

        // 1. Revoke สิทธิ์เดิมของ Matrix ที่ไม่ได้อยู่ใน $targetPermNames ออกไป (เมื่อเลือกเป็น 'x')
        foreach ($currentAssignments as $permName => $assignment) {
            // ลบเฉพาะ permission ที่อยู่ในขอบเขตของ Matrix และไม่ได้อยู่ในสิทธิ์ที่เลือกใหม่
            if (isset($allMatrixPermNames[$permName]) && !isset($targetPermNames[$permName])) {
                $item = $auth->getPermission($permName);
                if ($item) {
                    try {
                        $auth->revoke($item, $userId);
                    } catch (\Exception $e) {
                        // ข้ามกรณีมีข้อผิดพลาด
                    }
                }
            }
        }

        // 2. สร้างหรือ Assign permissions ใหม่ให้ User
        foreach ($targetPermNames as $permName => $route) {
            // ตรวจสอบว่าใน auth_item มี Permission หรือยัง หากยังไม่มีให้สร้าง
            $item = $auth->getPermission($permName);
            if (!$item) {
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
                    // ข้ามกรณี duplicate
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
