<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use backend\models\Customer;
use backend\models\Invoice;
use backend\models\Unit;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\DebitNote */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelsItem backend\models\DebitNoteItem[] */

$js = '
// ตัวแปรเก็บข้อมูลสินค้า
var productsData = [];
var isProductsLoaded = false;

// ตัวแปรเก็บข้อมูลหน่วย
var unitsData = ' . json_encode(ArrayHelper::map(Unit::find()->where(['status' => 1])->all(), 'id', 'name')) . ';

// ฟังก์ชันสร้าง options สำหรับ unit dropdown
function createUnitOptions(selectedUnit = "") {
    var options = "<option value=\"\">เลือกหน่วย</option>";
    for (var unitCode in unitsData) {
        var selected = (unitCode === selectedUnit) ? "selected" : "";
        options += "<option value=\"" + unitCode + "\" " + selected + ">" + unitsData[unitCode] + "</option>";
    }
    return options;
}

// ฟังก์ชันโหลดข้อมูลสินค้า
function loadProductsData() {
    if (isProductsLoaded) return;
    
    $.ajax({
        url: "' . Url::to(['get-product-info']) . '",
        type: "GET",
        data: { action: "get-all-products" },
        dataType: "json",
        success: function(data) {
            console.log("Products loaded:", data);
            productsData = data || [];
            isProductsLoaded = true;
        },
        error: function() {
            console.log("Error loading products data");
            productsData = [];
        }
    });
}

// ฟังก์ชันค้นหาสินค้า
function searchProducts(query) {
    if (!query || query.length < 1) return [];
    
    query = query.toLowerCase();
    return productsData.filter(function(product) {
        var description = (product.description || product.item_description || "").toLowerCase();
        var code = (product.code || product.item_code || "").toLowerCase();
        var name = (product.name || product.item_name || "").toLowerCase();
        
        return description.includes(query) || 
               code.includes(query) ||
               name.includes(query);
    }).slice(0, 10);
}

// ฟังก์ชันแสดงผลลัพธ์
function showAutocompleteResults(input, results) {
    var row = input.closest("tr");
    var dropdown = row.find(".autocomplete-dropdown");
    
    dropdown.empty();
    
    if (results.length === 0) {
        dropdown.hide();
        return;
    }
    
    results.forEach(function(product) {
        var description = product.description || product.item_description || product.name || "ไม่ระบุ";
        var code = product.code || product.item_code || "";
        
        var item = $("<div class=\"autocomplete-item\">")
            .html("<div>" + description + "</div>" + 
                  (code ? "<div class=\"product-code\">" + code + "</div>" : ""))
            .data("product", product);
        dropdown.append(item);
    });
    
    dropdown.show();
}

// ฟังก์ชันซ่อน dropdown
function hideAutocomplete(row) {
    setTimeout(function() {
        row.find(".autocomplete-dropdown").hide();
    }, 200);
}

// ฟังก์ชันเลือกสินค้า
function selectProduct(input, product) {
    var row = input.closest("tr");
    
    // อัพเดตค่า
    var description = product.description || product.item_description || product.name || "";
    input.val(description);
    
    // อัพเดต product_id
    var productId = product.id || product.product_id || "";
    row.find(".product-id-input").val(productId);
    
    // อัพเดตราคา
    var price = product.unit_price || product.price || product.item_price || "0.000";
    row.find(".unit-price").val(price);
    
    // อัพเดตหน่วย
    var unit = product.unit || product.item_unit || "หน่วย";
    row.find("select[name*=\"[unit]\"]").val(unit);
    
    // ซ่อน dropdown
    row.find(".autocomplete-dropdown").hide();
    
    // คำนวณยอดรวม
    calculateItemAmount(row);
}

// Initialize autocomplete for item description
function initializeAutocomplete(element) {
    // Initialize autocomplete events for the element
    element.off("input.autocomplete focus.autocomplete blur.autocomplete keydown.autocomplete");
    
    element.on("input.autocomplete", function() {
        var input = $(this);
        var query = input.val();
        
        if (!isProductsLoaded) {
            loadProductsData();
            return;
        }
        
        var results = searchProducts(query);
        showAutocompleteResults(input, results);
    });
    
    element.on("focus.autocomplete", function() {
        var input = $(this);
        var query = input.val();
        
        if (!isProductsLoaded) {
            loadProductsData();
            return;
        }
        
        if (query) {
            var results = searchProducts(query);
            showAutocompleteResults(input, results);
        }
    });
    
    element.on("blur.autocomplete", function() {
        var row = $(this).closest("tr");
        hideAutocomplete(row);
    });
    
    element.on("keydown.autocomplete", function(e) {
        var row = $(this).closest("tr");
        var dropdown = row.find(".autocomplete-dropdown");
        var items = dropdown.find(".autocomplete-item");
        var highlighted = items.filter(".highlighted");
        
        if (e.keyCode === 40) { // Arrow Down
            e.preventDefault();
            if (highlighted.length === 0) {
                items.first().addClass("highlighted");
            } else {
                highlighted.removeClass("highlighted");
                var next = highlighted.next(".autocomplete-item");
                if (next.length) {
                    next.addClass("highlighted");
                } else {
                    items.first().addClass("highlighted");
                }
            }
        } else if (e.keyCode === 38) { // Arrow Up
            e.preventDefault();
            if (highlighted.length === 0) {
                items.last().addClass("highlighted");
            } else {
                highlighted.removeClass("highlighted");
                var prev = highlighted.prev(".autocomplete-item");
                if (prev.length) {
                    prev.addClass("highlighted");
                } else {
                    items.last().addClass("highlighted");
                }
            }
        } else if (e.keyCode === 13) { // Enter
            e.preventDefault();
            if (highlighted.length) {
                var product = highlighted.data("product");
                selectProduct($(this), product);
            }
        } else if (e.keyCode === 27) { // Escape
            dropdown.hide();
        }
    });
}

// ฟังก์ชันเพิ่มรายการใหม่ - แก้ไขให้ไม่เพิ่มแถวเปล่าเมื่อมีข้อมูลอยู่แล้ว
function addItemRow(itemData = null) {
    var existingRows = $("#items-table tbody tr");
    var rowIndex = existingRows.length;
    
    var description = itemData ? itemData.description || "" : "";
    var quantity = itemData ? itemData.quantity || "1.000" : "1.000";
    var unit = itemData ? itemData.unit || "หน่วย" : "หน่วย";
    var unitPrice = itemData ? itemData.unit_price || "0.000" : "0.000";
    var amount = itemData ? itemData.amount || "0.000" : "0.000";
    var productId = itemData ? itemData.product_id || "" : "";
    
    var newRowHtml = `
    <tr>
        <td class="text-center">` + (rowIndex + 1) + `</td>
        <td style="position: relative;">
            <input type="hidden" name="DebitNoteItem[` + rowIndex + `][product_id]" class="product-id-input" value="` + productId + `">
            <input type="text" name="DebitNoteItem[` + rowIndex + `][description]" class="form-control form-control-sm item-description" placeholder="รายละเอียดสินค้า/บริการ" autocomplete="off" value="` + description + `">
            <div class="autocomplete-dropdown"></div>
        </td>
        <td>
            <input type="number" name="DebitNoteItem[` + rowIndex + `][quantity]" class="form-control form-control-sm quantity text-right" step="0.001" min="0" value="` + quantity + `">
        </td>
        <td>
            <select name="DebitNoteItem[` + rowIndex + `][unit_id]" class="form-control form-control-sm">
                ` + createUnitOptions(itemData ? itemData.unit : "") + `
            </select>
        </td>
        <td>
            <input type="number" name="DebitNoteItem[` + rowIndex + `][unit_price]" class="form-control form-control-sm unit-price text-right" step="0.001" min="0" value="` + unitPrice + `">
        </td>
        <td>
            <input type="number" name="DebitNoteItem[` + rowIndex + `][amount]" class="form-control form-control-sm amount text-right" readonly style="background-color: #f8f9fa;" value="` + amount + `">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger btn-remove-item" title="ลบรายการ">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;
    
    $("#items-table tbody").append(newRowHtml);
    
    // Initialize autocomplete for the new row
    initializeAutocomplete($("#items-table tbody tr:last .item-description"));
    
    // คำนวณยอดรวมสำหรับแถวใหม่
    if (itemData) {
        calculateItemAmount($("#items-table tbody tr:last"));
    }
    
    updateRowNumbers();
}


// ฟังก์ชันลบรายการ
function removeItemRow(button) {
    var rowCount = $("#items-table tbody tr").length;
    if (rowCount > 1) {
        $(button).closest("tr").remove();
        updateRowNumbers();
        calculateTotal();
    } else {
        alert("ต้องมีรายการอย่างน้อย 1 รายการ");
    }
}

// ฟังก์ชันอัพเดตหมายเลขแถว
function updateRowNumbers() {
    $("#items-table tbody tr").each(function(index) {
        $(this).find("td:first").text(index + 1);
        
        // อัพเดต name attributes
        $(this).find("input, textarea, select").each(function() {
            var name = $(this).attr("name");
            if (name) {
                var newName = name.replace(/\[\d+\]/, "[" + index + "]");
                $(this).attr("name", newName);
            }
        });
    });
}

// ฟังก์ชันคำนวณยอดแต่ละรายการ
function calculateItemAmount(row) {
    var quantity = parseFloat(row.find(".quantity").val()) || 0;
    var unitPrice = parseFloat(row.find(".unit-price").val()) || 0;
    var amount = quantity * unitPrice;
    
    row.find(".amount").val(amount.toFixed(3));
    calculateTotal();
}

// ฟังก์ชันจัดรูปแบบตัวเลขให้มี comma
function formatNumber(num) {
    if (num === null || num === undefined || num === "") {
        return "0.00";
    }
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// ฟังก์ชันคำนวณยอดรวมทั้งหมด
function calculateTotal() {
    var subtotal = 0;
    $(".amount").each(function() {
        subtotal += parseFloat($(this).val()) || 0;
    });
    
    var vatPercent = parseFloat($("#debit-note-vat_percent").val()) || 0;
    var vat = subtotal * (vatPercent / 100);
    var total = subtotal + vat;
    
    // Update display fields with formatted values
    $("#debit-note-adjust_amount").val(formatNumber(subtotal.toFixed(2)));
    $("#debit-note-vat_amount").val(formatNumber(vat.toFixed(2)));
    $("#debit-note-total_amount").val(formatNumber(total.toFixed(2)));
    
    // Update hidden fields with unformatted values for form submission
    $("#debit-note-adjust_amount-hidden").val(subtotal.toFixed(2));
    $("#debit-note-vat_amount-hidden").val(vat.toFixed(2));
    $("#debit-note-total_amount-hidden").val(total.toFixed(2));
}

// ฟังก์ชันล้างรายการทั้งหมด
function clearAllItems() {
    $("#items-table tbody").empty();
   // addItemRow(); // เพิ่มแถวเปล่า 1 แถว
}

// ฟังก์ชันโหลดข้อมูลจากใบแจ้งหนี้
function loadInvoiceItems(invoiceId) {
    if (invoiceId) {
        $.ajax({
            url: "' . Url::to(['debit-note/get-invoice-items']) . '",
            data: {id: invoiceId},
            dataType: "json",
            type: "GET",
            success: function(response) {
                console.log("Invoice items response:", response);
                if (response.success && response.items && response.items.length > 0) {
                    // แสดงข้อความยืนยัน
                    if (confirm("พบรายการสินค้า/บริการจากใบแจ้งหนี้นี้ ต้องการโหลดรายการหรือไม่?\\n\\n" +
                               "หมายเหตุ: การโหลดจะเขียนทับรายการที่มีอยู่")) {
                        clearAllItems();
                        
                        // เพิ่มรายการจากใบแจ้งหนี้
                        response.items.forEach(function(item) {
                            addItemRow({
                                description: item.item_description || item.description,
                                quantity: item.quantity,
                                unit: item.unit,
                                unit_price: item.unit_price,
                                amount: item.amount,
                                product_id: item.product_id || ""
                            });
                        });
                        
                        calculateTotal();
                        
                        // แสดงข้อความสำเร็จ
                        showMessage("success", "โหลดรายการสินค้า/บริการเรียบร้อยแล้ว (" + response.items.length + " รายการ)");
                    }
                } else {
                    showMessage("info", "ไม่พบรายการสินค้า/บริการในใบแจ้งหนี้นี้");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading invoice items:", error);
                showMessage("error", "เกิดข้อผิดพลาดในการโหลดรายการสินค้า/บริการ");
            }
        });
    }
}

// ฟังก์ชันโหลดใบแจ้งหนี้ตามลูกค้า
function loadInvoicesByCustomer(customerId) {
    var invoiceSelect = $("#debit-note-invoice_id");
    
    // ล้าง options เดิม
    invoiceSelect.empty().append("<option value=\"\">เลือกใบแจ้งหนี้...</option>");
    
    if (customerId) {
        $.ajax({
            url: "' . Url::to(['debit-note/get-invoices-by-customer']) . '",
            data: {customer_id: customerId},
            dataType: "json",
            type: "GET",
            success: function(response) {
                console.log("Customer invoices response:", response);
                if (response.success && response.invoices && response.invoices.length > 0) {
                    // เพิ่ม options ใหม่
                    response.invoices.forEach(function(invoice) {
                        invoiceSelect.append("<option value=\"" + invoice.id + "\">" + invoice.invoice_number + "</option>");
                    });
                    
                    // Refresh Select2 if it\'s initialized
                    if (invoiceSelect.hasClass("select2-hidden-accessible")) {
                        invoiceSelect.trigger("change");
                    }
                    
                    showMessage("info", "โหลดใบแจ้งหนี้ของลูกค้านี้เรียบร้อยแล้ว (" + response.invoices.length + " รายการ)");
                } else {
                    showMessage("warning", "ไม่พบใบแจ้งหนี้ของลูกค้านี้");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading customer invoices:", error);
                showMessage("error", "เกิดข้อผิดพลาดในการโหลดใบแจ้งหนี้");
            }
        });
    }
}

// ฟังก์ชันแสดงข้อความ
function showMessage(type, message) {
    var alertClass = "alert-info";
    switch(type) {
        case "success": alertClass = "alert-success"; break;
        case "error": alertClass = "alert-danger"; break;
        case "warning": alertClass = "alert-warning"; break;
    }
    
    var alertHtml = "<div class=\"alert " + alertClass + " alert-dismissible fade show\" role=\"alert\">" +
                    message +
                    "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
                    "<span aria-hidden=\"true\">&times;</span>" +
                    "</button>" +
                    "</div>";
    
    $(".debit-note-form").prepend(alertHtml);
    
    // ซ่อนอัตโนมัติหลัง 5 วินาที
    setTimeout(function() {
        $(".alert").fadeOut();
    }, 5000);
}

// Event handlers
$(document).on("input", ".quantity, .unit-price", function() {
    calculateItemAmount($(this).closest("tr"));
});

$(document).on("input", "#debit-note-vat_percent", function() {
    calculateTotal();
});

$(document).on("click", ".btn-add-item", function() {
    addItemRow();
});

$(document).on("click", ".btn-remove-item", function() {
    removeItemRow(this);
});

$(document).on("click", ".btn-load-invoice-items", function() {
    var invoiceId = $("#debit-note-invoice_id").val();
    if (invoiceId) {
        loadInvoiceItems(invoiceId);
    } else {
        showMessage("warning", "กรุณาเลือกใบแจ้งหนี้ก่อน");
    }
});

// Event สำหรับการเปลี่ยนแปลงลูกค้า - โหลดใบแจ้งหนี้ตามลูกค้า
$("#debit-note-customer_id").on("change", function() {
    var customerId = $(this).val();
    
    // ล้างข้อมูลใบแจ้งหนี้เดิม
    $("#debit-note-invoice_id").val("").trigger("change");
    $("#debit-note-original_invoice_no").val("");
    $("#debit-note-original_invoice_date").val("");
    $("#debit-note-original_amount").val("");
    
    if (customerId) {
        loadInvoicesByCustomer(customerId);
    }
});

// โหลดข้อมูลใบแจ้งหนี้เมื่อเปลี่ยนการเลือก
$("#debit-note-invoice_id").on("change", function() {
    var invoiceId = $(this).val();
    if (invoiceId) {
        $.get("' . Url::to(['debit-note/get-invoice-data']) . '", {id: invoiceId}, function(data) {
            if (data) {
                $("#debit-note-original_invoice_no").val(data.invoice_number);
                $("#debit-note-original_invoice_date").val(data.invoice_date);
                $("#debit-note-original_amount").val(data.total_amount);
            }
        });
    }
});

// Event สำหรับการเปลี่ยนแปลงลูกค้า - โหลดใบแจ้งหนี้ตามลูกค้า
$("#credit-note-vendor_id").on("change", function() {
    var vendorId = $(this).val();
    
    // ล้างข้อมูลใบแจ้งหนี้เดิม
    $("#debit-note-invoice_id").val("").trigger("change");
    $("#debit-note-original_invoice_no").val("");
    $("#debit-note-original_invoice_date").val("");
    $("#debit-note-original_amount").val("");
    
    if (vendorId) {
        loadPurchByVendor(vendorId);
    }
});

// ฟังก์ชันโหลดใบแจ้งหนี้ตามลูกค้า
function loadPurchByVendor(vendorId) {
    var invoiceSelect = $("#debit-note-invoice_id");
    
    // ล้าง options เดิม
    invoiceSelect.empty().append("<option value=\"\">เลือกใบแจ้งหนี้...</option>");
    
    if (vendorId) {
        $.ajax({
            url: "' . Url::to(['debit-note/get-purch-by-vendor']) . '",
            data: {vendor_id: vendorId},
            dataType: "json",
            type: "GET",
            success: function(response) {
                console.log("Vendor invoices response:", response);
                if (response.success && response.invoices && response.invoices.length > 0) {
                    // เพิ่ม options ใหม่
                    response.invoices.forEach(function(invoice) {
                        invoiceSelect.append("<option value=\"" + invoice.id + "\">" + invoice.purch_no + "</option>");
                    });
                    
                    // Refresh Select2 if it\'s initialized
                    if (invoiceSelect.hasClass("select2-hidden-accessible")) {
                        invoiceSelect.trigger("change");
                    }
                    
                    showMessage("info", "โหลดใบแจ้งหนี้ของลูกค้านี้เรียบร้อยแล้ว (" + response.invoices.length + " รายการ)");
                } else {
                    showMessage("warning", "ไม่พบใบแจ้งหนี้ของลูกค้านี้");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error loading vendor purchage:", error);
                showMessage("error", "เกิดข้อผิดพลาดในการโหลดใบสั่งซื้อ");
            }
        });
    }
}

$(document).on("click", ".autocomplete-item", function() {
    var product = $(this).data("product");
    var dropdown = $(this).closest(".autocomplete-dropdown");
    var row = dropdown.closest("tr");
    var input = row.find(".item-description");
    selectProduct(input, product);
});

// เริ่มต้นเมื่อโหลดหน้า
$(document).ready(function() {
    loadProductsData(); // โหลดข้อมูลสินค้าตอนเริ่มต้น
    calculateTotal();
    
    // Initialize autocomplete for existing items
    $(".item-description").each(function() {
        initializeAutocomplete($(this));
    });
    
//    // เพิ่มแถวเปล่าถ้ายังไม่มีรายการ
//    if ($("#items-table tbody tr").length === 0) {
//        addItemRow();
//    }

});
';

$this->registerJs($js);
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap');

    .debit-note-form {
        font-family: 'Prompt', sans-serif;
    }

    .debit-note-form .card {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        border: none;
    }

    .debit-note-form .card-header {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-bottom: 1px solid #dee2e6;
    }

    .debit-note-form .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
        font-weight: 600;
        font-size: 13px;
    }

    .debit-note-form .form-control-sm {
        font-size: 13px;
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }

    #debit-note-total_amount {
        font-size: 18px !important;
        font-weight: bold !important;
        background-color: #e3f2fd !important;
    }

    .btn-load-invoice-items {
        margin-right: 8px;
    }

    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 9999 !important;
        background: white;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        max-height: 200px;
        overflow-y: auto;
        width: 100%;
        display: none;
    }

    .autocomplete-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }

    .autocomplete-item:hover,
    .autocomplete-item.highlighted {
        background-color: #007bff;
        color: white;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .product-code {
        color: #666;
        font-size: 12px;
    }

    .autocomplete-item.highlighted .product-code {
        color: #e9ecef;
    }

    /* ป้องกันปัญหา overflow ของ table */
    .table-responsive {
        overflow: visible !important;
    }

    .table {
        overflow: visible !important;
    }

    .card-body {
        overflow: visible !important;
    }
</style>

<!-- Flash Messages -->
<?php if (\Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= \Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (\Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= \Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (\Yii::$app->session->hasFlash('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= \Yii::$app->session->getFlash('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (\Yii::$app->session->hasFlash('info')): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <?= \Yii::$app->session->getFlash('info') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="debit-note-form">

    <?php $form = ActiveForm::begin(['id' => 'debit-note-form']); ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-invoice-dollar"></i> ข้อมูลใบเพิ่มหนี้
            </h3>
        </div>
        <div class="card-body">

            <div class="form-section">
                <h4 class="section-title">ข้อมูลเอกสาร</h4>
                <h4 class="section-title">ข้อมูลใบกำกับภาษีเดิม</h4>
                <div class="row">
                    <div class="col-md-3">
                        <?= $form->field($model, 'invoice_id')->widget(Select2::class, [
                            'data' => [], // เริ่มต้นเป็นอาร์เรย์ว่าง จะโหลดผ่าน AJAX
                            'options' => [
                                'placeholder' => 'เลือกใบแจ้งหนี้...',
                                'id' => 'debit-note-invoice_id'
                            ],
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'original_invoice_no')->textInput(['maxlength' => true, 'id' => 'debit-note-original_invoice_no']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'original_invoice_date')->widget(DatePicker::class, [
                            'options' => [
                                'placeholder' => 'เลือกวันที่...',
                                'id' => 'debit-note-original_invoice_date'
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd'
                            ]
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'original_amount')->textInput([
                            'type' => 'number',
                            'step' => '0.01',
                            'id' => 'debit-note-original_amount'
                        ]) ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'reason')->textarea(['rows' => 3]) ?>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="section-title mb-0">
                            <i class="fas fa-list"></i> รายการสินค้า/บริการ
                        </h4>
                        <div>
                            <button type="button" class="btn btn-sm btn-info btn-load-invoice-items">
                                <i class="fas fa-download"></i> โหลดจากใบแจ้งหนี้
                            </button>
                            <button type="button" class="btn btn-sm btn-primary btn-add-item">
                                <i class="fas fa-plus"></i> เพิ่มรายการ
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="items-table" class="table table-bordered table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">ลำดับ</th>
                                        <th width="35%">รายละเอียด</th>
                                        <th width="10%">จำนวน</th>
                                        <th width="10%">หน่วย</th>
                                        <th width="15%">ราคาต่อหน่วย</th>
                                        <th width="15%">จำนวนเงิน</th>
                                        <th width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($modelsItem as $i => $modelItem): ?>
                                        <tr>
                                            <td class="text-center"><?= ($i + 1) ?></td>
                                            <td style="position: relative;">
                                                <?php
                                                // necessary for update action.
                                                if (! $modelItem->isNewRecord) {
                                                    echo Html::activeHiddenInput($modelItem, "[{$i}]id");
                                                }
                                                ?>
                                                <?= Html::activeHiddenInput($modelItem, "[{$i}]product_id", ['class' => 'product-id-input']) ?>
                                                <?= $form->field($modelItem, "[{$i}]description")->textInput([
                                                    'class' => 'form-control form-control-sm item-description',
                                                    'placeholder' => 'รายละเอียดสินค้า/บริการ',
                                                    'autocomplete' => 'off'
                                                ])->label(false) ?>
                                                <div class="autocomplete-dropdown"></div>
                                            </td>
                                            <td>
                                                <?= $form->field($modelItem, "[{$i}]quantity")->textInput([
                                                    'type' => 'number',
                                                    'step' => '0.001',
                                                    'class' => 'form-control form-control-sm quantity text-right',
                                                    'min' => '0'
                                                ])->label(false) ?>
                                            </td>
                                            <td>
                                                <?= $form->field($modelItem, "[{$i}]unit_id")->dropDownList(
                                                    ArrayHelper::map(Unit::find()->where(['status' => 1])->orderBy('name')->all(), 'id', 'name'),
                                                    [
                                                        'prompt' => 'เลือกหน่วย',
                                                        'class' => 'form-control form-control-sm'
                                                    ]
                                                )->label(false) ?>
                                            </td>
                                            <td>
                                                <?= $form->field($modelItem, "[{$i}]unit_price")->textInput([
                                                    'type' => 'number',
                                                    'step' => '0.001',
                                                    'class' => 'form-control form-control-sm unit-price text-right',
                                                    'min' => '0'
                                                ])->label(false) ?>
                                            </td>
                                            <td>
                                                <?= $form->field($modelItem, "[{$i}]amount")->textInput([
                                                    'type' => 'number',
                                                    'step' => '0.001',
                                                    'class' => 'form-control form-control-sm amount text-right',
                                                    'readonly' => true,
                                                    'style' => 'background-color: #f8f9fa;'
                                                ])->label(false) ?>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove-item" title="ลบรายการ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="section-title">สรุปยอดเงิน</h4>
                <div class="row">
                    <div class="col-md-6">
                        <!-- สามารถเพิ่มฟิลด์อื่นๆ ที่นี่ได้ -->
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>รวมมูลค่าเพิ่มหนี้</strong></td>
                                <td>
                                    <?= Html::hiddenInput('DebitNote[adjust_amount]', $model->adjust_amount, ['id' => 'debit-note-adjust_amount-hidden']) ?>
                                    <?= $form->field($model, 'adjust_amount')->textInput([
                                        'type' => 'text',
                                        'readonly' => true,
                                        'disabled' => true,
                                        'id' => 'debit-note-adjust_amount',
                                        'class' => 'form-control text-right',
                                        'style' => 'background-color: #f8f9fa;'
                                    ])->label(false) ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span>ภาษีมูลค่าเพิ่ม</span>
                                        <div class="input-group ml-2" style="width: 80px;">
                                            <?= $form->field($model, 'vat_percent')->textInput([
                                                'type' => 'number',
                                                'step' => '0.01',
                                                'id' => 'debit-note-vat_percent',
                                                'class' => 'form-control form-control-sm text-right'
                                            ])->label(false) ?>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= Html::hiddenInput('DebitNote[vat_amount]', $model->vat_amount, ['id' => 'debit-note-vat_amount-hidden']) ?>
                                    <?= $form->field($model, 'vat_amount')->textInput([
                                        'type' => 'text',
                                        'readonly' => true,
                                        'disabled' => true,
                                        'id' => 'debit-note-vat_amount',
                                        'class' => 'form-control text-right',
                                        'style' => 'background-color: #f8f9fa;'
                                    ])->label(false) ?>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>รวมเป็นเงินทั้งสิ้น</strong></td>
                                <td>
                                    <?= Html::hiddenInput('DebitNote[total_amount]', $model->total_amount, ['id' => 'debit-note-total_amount-hidden']) ?>
                                    <?= $form->field($model, 'total_amount')->textInput([
                                        'type' => 'text',
                                        'readonly' => true,
                                        'disabled' => true,
                                        'id' => 'debit-note-total_amount',
                                        'class' => 'form-control text-right font-weight-bold'
                                    ])->label(false) ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <div class="card-footer text-center">
            <?= Html::submitButton('<i class="fas fa-save"></i> บันทึก', ['class' => 'btn btn-success btn-lg']) ?>
            <?= Html::a('<i class="fas fa-times"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary btn-lg']) ?>
            <?php if (!$model->isNewRecord): ?>
                <?= Html::a('<i class="fas fa-print"></i> พิมพ์', ['print', 'id' => $model->id], [
                    'class' => 'btn btn-info btn-lg',
                    'target' => '_blank'
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

    <?php
    $model_doc = \common\models\DebitNoteDoc::find()->where(['debit_note_id' => $model->id])->all();
    ?>
    <hr>
    <br />
    <div class="label">
        <h4>เอกสารแนบ</h4>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <table class="table table-bordered table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th style="width: 5%;text-align: center">#</th>
                        <th style="width: 50%;text-align: center">ชื่อไฟล์</th>
                        <th style="width: 10%;text-align: center">ดูเอกสาร</th>
                        <th style="width: 5%;text-align: center">-</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($model_doc != null): ?>

                        <?php foreach ($model_doc as $key => $value): ?>
                            <tr>
                                <td style="width: 10px;text-align: center"><?= $key + 1 ?></td>
                                <td><?= $value->doc ?></td>
                                <td style="text-align: center">
                                    <a href="<?= Yii::$app->request->BaseUrl . '/uploads/debitnote_doc/' . $value->doc ?>"
                                        target="_blank">
                                        ดูเอกสาร
                                    </a>
                                </td>
                                <td style="text-align: center">
                                    <div class="btn btn-danger" data-var="<?= trim($value->doc) ?>"
                                        onclick="delete_doc($(this))">ลบ
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <br />

    <form action="<?= Url::to(['debit-note/add-doc-file'], true) ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <div style="padding: 10px;background-color: lightgrey;border-radius: 5px">
            <div class="row">
                <div class="col-lg-12">
                    <label for="">เอกสารแนบ</label>
                    <input type="file" name="file_doc" multiple>
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-lg-12">
                    <button class="btn btn-info">
                        <i class="fas fa-upload"></i> อัพโหลดเอกสารแนบ
                    </button>
                </div>
            </div>
        </div>
    </form>
    <form id="form-delete-doc-file" action="<?= Url::to(['debit-note/delete-doc-file'], true) ?>" method="post">
        <input type="hidden" name="id" value="<?= $model->id ?>">
        <input type="hidden" class="delete-doc-list" name="doc_delete_list" value="">
    </form>

</div>

<?php
$script = <<< JS
function delete_doc(e){
    var file_name = e.attr('data-var');
    if(file_name != null){
        $(".delete-doc-list").val(file_name);
        $("#form-delete-doc-file").submit();
    }
}
JS;
$this->registerJs($script, static::POS_END);
?>