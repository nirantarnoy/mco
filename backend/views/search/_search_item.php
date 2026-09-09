<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\StringHelper;

/* @var $model array */
/* @var $searchQuery string */

$type = $model['type'];
$obj = $model['model'];

$highlight = function ($text) use ($searchQuery) {
    if (empty($searchQuery) || empty($text)) {
        return Html::encode($text);
    }
    
    $keywords = array_unique(array_filter(array_map('trim', explode(',', $searchQuery))));
    $text = Html::encode($text);
    
    foreach ($keywords as $keyword) {
        if (empty($keyword)) continue;
        $pattern = '/' . preg_quote($keyword, '/') . '/iu';
        $text = preg_replace($pattern, '<span style="background-color: yellow; color: black;">$0</span>', $text);
    }
    return $text;
};

// Define properties based on type
$config = [
    'job' => [
        'title' => 'ใบสั่งงาน (Job Order)',
        'icon' => 'fas fa-briefcase',
        'color' => 'primary',
        'doc_no' => isset($obj->job_no) ? $obj->job_no : '',
        'date' => isset($obj->job_date) ? $obj->job_date : '',
        'amount' => isset($obj->job_amount) ? $obj->job_amount : null,
        'lines' => isset($obj->jobLines) ? $obj->jobLines : [],
        'url' => ['job/timeline', 'id' => $obj->id],
        'product_rel' => 'product',
        'product_id_field' => 'product_id',
    ],
    'quotation' => [
        'title' => 'ใบเสนอราคา (Quotation)',
        'icon' => 'fas fa-file-invoice',
        'color' => 'info',
        'doc_no' => isset($obj->quotation_no) ? $obj->quotation_no : '',
        'date' => isset($obj->quotation_date) ? $obj->quotation_date : '',
        'amount' => isset($obj->grand_total) ? $obj->grand_total : null,
        'lines' => isset($obj->quotationLines) ? $obj->quotationLines : [],
        'url' => ['quotation/view', 'id' => $obj->id],
        'product_rel' => 'product',
        'product_id_field' => 'product_id',
    ],
    'po' => [
        'title' => 'ใบสั่งซื้อ (PO)',
        'icon' => 'fas fa-shopping-cart',
        'color' => 'success',
        'doc_no' => isset($obj->purch_no) ? $obj->purch_no : '',
        'date' => isset($obj->purch_date) ? $obj->purch_date : '',
        'amount' => isset($obj->total_amount) ? $obj->total_amount : null,
        'lines' => isset($obj->purchLines) ? $obj->purchLines : [],
        'url' => ['purch/view', 'id' => $obj->id],
        'product_rel' => 'product',
        'product_id_field' => 'product_id',
    ],
    'npr' => [
        'title' => 'บันทึกซื้อ (NPR)',
        'icon' => 'fas fa-truck-loading',
        'color' => 'success',
        'doc_no' => isset($obj->docnum) ? $obj->docnum : '',
        'date' => isset($obj->docdat) ? $obj->docdat : '',
        'amount' => isset($obj->total_amount) ? $obj->total_amount : null,
        'lines' => isset($obj->purchaseDetails) ? $obj->purchaseDetails : [],
        'url' => ['purchasemaster/view', 'id' => $obj->id],
        'product_rel' => null, // Needs special handling because it uses stkcod
        'product_id_field' => 'stkcod',
    ],
    'so' => [
        'title' => 'ใบสั่งขาย (Sales Order)',
        'icon' => 'fas fa-shopping-bag',
        'color' => 'warning',
        'doc_no' => isset($obj->order_no) ? $obj->order_no : '',
        'date' => isset($obj->order_date) ? $obj->order_date : '',
        'amount' => isset($obj->total_amount) ? $obj->total_amount : null,
        'lines' => isset($obj->ordersLines) ? $obj->ordersLines : [],
        'url' => ['orders/view', 'id' => $obj->id],
        'product_rel' => 'product',
        'product_id_field' => 'product_id',
    ],
    'dn' => [
        'title' => 'ใบส่งของ (Delivery Note)',
        'icon' => 'fas fa-truck',
        'color' => 'secondary',
        'doc_no' => isset($obj->dn_no) ? $obj->dn_no : '',
        'date' => isset($obj->date) ? $obj->date : '',
        'amount' => null,
        'lines' => isset($obj->deliveryNoteLines) ? $obj->deliveryNoteLines : [],
        'url' => ['delivery-note/view', 'id' => $obj->id],
        'product_rel' => 'product',
        'product_id_field' => 'product_id',
    ],
    'invoice' => [
        'title' => 'ใบแจ้งหนี้ (Invoice)',
        'icon' => 'fas fa-file-invoice-dollar',
        'color' => 'danger',
        'doc_no' => isset($obj->invoice_no) ? $obj->invoice_no : '',
        'date' => isset($obj->invoice_date) ? $obj->invoice_date : '',
        'amount' => isset($obj->total_amount) ? $obj->total_amount : null,
        'lines' => isset($obj->invoiceItems) ? $obj->invoiceItems : [],
        'url' => ['invoice/view', 'id' => $obj->id],
        'product_rel' => 'product',
        'product_id_field' => 'product_id',
    ],
];

$conf = $config[$type];
?>

<div class="card search-result-item border-left-<?= $conf['color'] ?>">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-1">
                    <span class="badge badge-<?= $conf['color'] ?> px-2 py-1">
                        <i class="<?= $conf['icon'] ?>"></i> <?= $conf['title'] ?>
                    </span>
                </div>
                <h5 class="card-title mb-2">
                    <a href="<?= Url::to($conf['url']) ?>" class="text-<?= $conf['color'] ?> font-weight-bold" target="_blank">
                        <?= $highlight($conf['doc_no']) ?>
                    </a>
                </h5>

                <?php if ($conf['date']): ?>
                    <p class="text-muted mb-1">
                        <i class="far fa-calendar"></i>
                        <strong>วันที่เอกสาร:</strong> <?= date('d/m/Y', strtotime($conf['date'])) ?>
                    </p>
                <?php endif; ?>

                <?php if ($conf['amount'] !== null): ?>
                    <p class="text-muted mb-1">
                        <strong>มูลค่ารวม:</strong> <?= Yii::$app->formatter->asDecimal($conf['amount'], 2) ?>
                    </p>
                <?php endif; ?>

                <!-- Products in this document -->
                <?php if ($conf['lines']): ?>
                    <div class="mt-2">
                        <small class="text-muted"><i class="fas fa-box"></i> <strong>สินค้าในเอกสาร (Products):</strong></small>
                        <div class="mt-1">
                            <?php
                            $displayCount = 0;
                            foreach ($conf['lines'] as $line):
                                if ($displayCount >= 5) break;
                                
                                $code = '';
                                $name = '';
                                $qty = isset($line->qty) ? $line->qty : (isset($line->uqnty) ? $line->uqnty : 0);
                                
                                if ($type == 'npr') {
                                    $code = $line->stkcod;
                                    $name = $line->stkdes;
                                } else {
                                    $rel = $conf['product_rel'];
                                    if ($rel && isset($line->$rel)) {
                                        $code = $line->$rel->code;
                                        $name = $line->$rel->name;
                                    } else {
                                        $field = $conf['product_id_field'];
                                        $code = $line->$field;
                                    }
                                }
                                
                                // Only highlight if matched keywords? Or just show all?
                                // Show first 5 lines
                                $displayCount++;
                                ?>
                                <span class="badge badge-light border mr-1 mb-1" style="white-space: normal; text-align: left; max-width: 100%;">
                                    <?= $highlight($code) ?> <?= $name ? ' - ' . $highlight(StringHelper::truncate($name, 50)) : '' ?>
                                    <small class="text-muted">(Qty: <?= floatval($qty) ?>)</small>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($conf['lines']) > 5): ?>
                                <span class="badge badge-secondary">+<?= count($conf['lines']) - 5 ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-4 text-right">
                <a href="<?= Url::to($conf['url']) ?>" class="btn btn-outline-<?= $conf['color'] ?> btn-sm mt-2" target="_blank">
                    <i class="fas fa-external-link-alt"></i> เปิดดูเอกสาร
                </a>
            </div>
        </div>
    </div>
    <div class="card-footer text-muted p-2">
        <small>
            <i class="far fa-clock"></i>
            สร้างเมื่อ: <?= isset($obj->created_at) ? date('d/m/Y H:i', $obj->created_at) : '-' ?>
            <?php if (isset($obj->updated_at)): ?>
                | แก้ไขเมื่อ: <?= date('d/m/Y H:i', $obj->updated_at) ?>
            <?php endif; ?>
        </small>
    </div>
</div>

<style>
.border-left-primary { border-left: 4px solid #4e73df !important; }
.border-left-success { border-left: 4px solid #1cc88a !important; }
.border-left-info { border-left: 4px solid #36b9cc !important; }
.border-left-warning { border-left: 4px solid #f6c23e !important; }
.border-left-danger { border-left: 4px solid #e74a3b !important; }
.border-left-secondary { border-left: 4px solid #858796 !important; }
</style>