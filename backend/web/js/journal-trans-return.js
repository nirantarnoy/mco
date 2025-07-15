/**
 * JavaScript for Journal Transaction Return Functionality
 * File: web/js/journal-trans-return.js
 */

// Global variables
var originalTransactionData = {};
var currentConditionIndex = -1;

// Initialize when document is ready
$(document).ready(function() {
    initializeReturnTransaction();
    setupEventListeners();
});

// Initialize return transaction functionality
function initializeReturnTransaction() {
    var transType = $('#trans-type-select').val();

    if (transType == '4' || transType == '5') {
        enableReturnTransactionMode();
    } else {
        disableReturnTransactionMode();
    }

    // If return_for_trans_id is already selected (update mode)
    var returnForTransId = $('#return-for-trans-select').val();
    if (returnForTransId) {
        loadOriginalTransaction(returnForTransId);
    }
}

// Setup event listeners
function setupEventListeners() {
    // Transaction type change event
    $('#trans-type-select').off('change.returnTrans').on('change.returnTrans', function() {
        var transType = parseInt($(this).val());

        if (transType === 4 || transType === 5) {
            enableReturnTransactionMode();
        } else {
            disableReturnTransactionMode();
        }

        updateStockType(transType);
    });

    // Return transaction selection change
    $('#return-for-trans-select').off('change.returnTrans').on('change.returnTrans', function() {
        var selectedTransId = $(this).val();
        if (selectedTransId) {
            loadOriginalTransaction(selectedTransId);
        } else {
            clearReturnTransactionData();
        }
    });

    // Quantity input validation for return transactions
    $(document).off('input.returnTrans', '.qty-input').on('input.returnTrans', '.qty-input', function() {
        var index = $(this).data('index');
        var availableQty = parseFloat($(this).data('available-qty')) || 0;
        var inputQty = parseFloat($(this).val()) || 0;
        var transType = $('#trans-type-select').val();

        // Validate quantity for return transactions
        if ((transType == '4' || transType == '5') && inputQty > availableQty) {
            $(this).val(availableQty);
            showAlert('จำนวนที่คืนไม่สามารถเกินจำนวนที่สามารถคืนได้ (' + availableQty + ')', 'warning');
            inputQty = availableQty;
        }

        // Show/hide condition button for return borrow
        toggleConditionButton(index, inputQty, transType);
    });

    // Condition modal events
    $('#condition-good-qty, #condition-damaged-qty, #condition-missing-qty').off('input.condition').on('input.condition', function() {
        updateConditionTotal();
        validateConditionQuantities();
    });
}

// Enable return transaction mode
function enableReturnTransactionMode() {
    $('#return-for-trans-select').prop('disabled', false);
    $('.return-fields').addClass('show');
    $('.add-item').hide();
    updateTableHeadersForReturn();
}

// Disable return transaction mode
function disableReturnTransactionMode() {
    $('#return-for-trans-select').prop('disabled', true).val('');
    $('.return-fields').removeClass('show');
    $('.add-item').show();
    clearReturnTransactionData();
    updateTableHeadersForNormal();
}

// Update table headers for return mode
function updateTableHeadersForReturn() {
    var headerRow = $('.table thead tr');
    headerRow.find('th').eq(2).text('คลังจัดเก็บ');
    headerRow.find('th').eq(3).text('ยอดเบิกไป');
    headerRow.find('th').eq(4).text('คงเหลือคืนได้');
    headerRow.find('th').eq(5).text('จำนวนคืน');
    headerRow.find('th').eq(7).text('สภาพสินค้า');
}

// Update table headers for normal mode
function updateTableHeadersForNormal() {
    var headerRow = $('.table thead tr');
    headerRow.find('th').eq(2).text('คลังจัดเก็บ');
    headerRow.find('th').eq(3).text('ยอดคงเหลือ');
    headerRow.find('th').eq(4).text('ยอดเบิก');
    headerRow.find('th').eq(5).text('ราคาต่อหน่วย');
    headerRow.find('th').eq(7).text('การดำเนินการ');
}

// Load original transaction data
function loadOriginalTransaction(transactionId) {
    if (!transactionId) {
        clearReturnTransactionData();
        return;
    }

    showLoading();

    // Get the URL from the form's data attribute or construct it
    var getTransactionLinesUrl = $('meta[name="get-transaction-lines-url"]').attr('content') ||
        '/journaltrans/get-transaction-lines';

    $.ajax({
        url: getTransactionLinesUrl,
        type: 'GET',
        data: { transaction_id: transactionId },
        dataType: 'json',
        success: function(response) {
            hideLoading();

            if (response.success) {
                originalTransactionData = response.data;
                displayOriginalTransactionInfo(response.data);
                populateReturnTransactionLines(response.data.lines);
                showAlert('โหลดข้อมูลรายการต้นฉบับเรียบร้อยแล้ว', 'success');
            } else {
                showAlert('ไม่สามารถโหลดข้อมูลรายการได้: ' + response.message, 'error');
                clearReturnTransactionData();
            }
        },
        error: function(xhr, status, error) {
            hideLoading();
            showAlert('เกิดข้อผิดพลาดในการโหลดข้อมูลรายการ: ' + error, 'error');
            clearReturnTransactionData();
        }
    });
}

// Display original transaction info
function displayOriginalTransactionInfo(data) {
    $('.original-transaction-info').remove();

    var infoHtml = `
        <div class="original-transaction-info">
            <h6><i class="fa fa-info-circle"></i> ข้อมูลรายการต้นฉบับ</h6>
            <div class="row">
                <div class="col-md-6">
                    <span class="info-label">เลขที่:</span> ${data.journal_no}<br>
                    <span class="info-label">วันที่:</span> ${data.trans_date}<br>
                    <span class="info-label">ลูกค้า:</span> ${data.customer_name || '-'}
                </div>
                <div class="col-md-6">
                    <span class="info-label">ประเภท:</span> ${data.trans_type_name}<br>
                    <span class="info-label">สถานะ:</span> ${data.status}<br>
                    <span class="info-label">จำนวนรายการ:</span> ${data.lines.length} รายการ
                </div>
            </div>
        </div>
    `;

    $('.card-body').prepend(infoHtml);
}

// Populate return transaction lines
function populateReturnTransactionLines(lines) {
    $('.container-items').empty();

    if (!lines || lines.length === 0) {
        $('.container-items').html('<tr class="item"><td colspan="9" class="text-center text-muted">ไม่พบรายการสินค้า</td></tr>');
        return;
    }

    lines.forEach(function(line, index) {
        addReturnTransactionLine(line, index);
    });
}

// Add return transaction line
function addReturnTransactionLine(lineData, index) {
    var availableQty = lineData.available_return_qty || 0;

    var rowHtml = `
        <tr class="item return-transaction-line">
            <td class="text-center align-middle">
                <span class="item-number">${index + 1}</span>
                <input type="hidden" name="JournalTransLine[${index}][product_id]" value="${lineData.product_id}">
            </td>
            <td>
                <input type="text" class="form-control readonly-field" 
                       name="JournalTransLine[${index}][product_name]" 
                       value="${lineData.product_name}" readonly>
                <small class="text-muted">รหัส: ${lineData.product_code}</small>
            </td>
            <td>
                <input type="text" class="form-control readonly-field" 
                       value="${lineData.warehouse_name}" readonly>
                <input type="hidden" name="JournalTransLine[${index}][warehouse_id]" value="${lineData.warehouse_id}">
            </td>
            <td>
                <input type="text" class="form-control text-center readonly-field" 
                       value="${lineData.qty}" readonly>
                <small class="text-muted d-block">ยืมไป</small>
            </td>
            <td>
                <input type="text" class="form-control text-center readonly-field" 
                       value="${availableQty}" readonly>
                <small class="text-muted d-block">คงเหลือ</small>
            </td>
            <td>
                <div class="stock-alert" style="position: relative;">
                    <input type="number" class="form-control qty-input" 
                           name="JournalTransLine[${index}][qty]" 
                           step="0.01" min="0" max="${availableQty}" 
                           placeholder="0" data-index="${index}" 
                           data-original-qty="${lineData.qty}" 
                           data-available-qty="${availableQty}">
                    <div class="alert-message" data-index="${index}"></div>
                </div>
            </td>
            <td>
                <input type="text" class="form-control readonly-field" 
                       value="${lineData.unit_name}" readonly>
                <input type="hidden" name="JournalTransLine[${index}][unit_id]" value="${lineData.unit_id}">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-info btn-sm condition-btn" 
                        onclick="openConditionModal(${index})" 
                        style="display: none;" data-index="${index}">
                    <i class="fa fa-edit"></i> สภาพ
                </button>
                <div class="condition-summary" data-index="${index}"></div>
            </td>
            <td class="text-center align-middle">
                <span class="text-muted">-</span>
            </td>
        </tr>
    `;

    $('.container-items').append(rowHtml);
}

// Toggle condition button visibility
function toggleConditionButton(index, qty, transType) {
    var conditionBtn = $(`.condition-btn[data-index="${index}"]`);

    if (qty > 0 && transType == '6') { // Return Borrow
        conditionBtn.show();
    } else {
        conditionBtn.hide();
        clearConditionData(index);
    }
}

// Clear condition data
function clearConditionData(index) {
    setConditionHiddenFields(index, 0, 0, 0, '', '');
    updateConditionSummary(index, 0, 0, 0);
}

// Clear return transaction data
function clearReturnTransactionData() {
    originalTransactionData = {};
    $('.original-transaction-info').remove();
    $('.container-items').html('<tr class="item"><td colspan="9" class="text-center text-muted">กรุณาเลือกรายการที่ต้องการคืนก่อน</td></tr>');
}

// Open condition modal
function openConditionModal(index) {
    currentConditionIndex = index;
    var currentData = getConditionData(index);
    var qtyInput = $('.qty-input[data-index="' + index + '"]');
    var totalQty = parseFloat(qtyInput.val()) || 0;

    if (totalQty <= 0) {
        alert('กรุณากรอกจำนวนที่ต้องการคืนก่อน');
        return;
    }

    // เติมข้อมูลใน modal
    $('#condition-good-qty').val(currentData.good_qty || '');
    $('#condition-damaged-qty').val(currentData.damaged_qty || '');
    $('#condition-missing-qty').val(currentData.missing_qty || '');
    $('#condition-note').val(currentData.condition_note || '');
    $('#return-note').val(currentData.return_note || '');

    // แสดงจำนวนที่ต้องการคืนใน modal
    $('#conditionModal .modal-title').html('<i class="fa fa-edit"></i> บันทึกสภาพสินค้า (จำนวนคืน: ' + totalQty + ')');

    updateConditionTotal();
    $('#conditionModal').modal('show');
}

// Save condition data
function saveConditionData() {
    var index = currentConditionIndex;
    var qtyInput = $('.qty-input[data-index="' + index + '"]');
    var totalQty = parseFloat(qtyInput.val()) || 0;

    var goodQty = parseFloat($('#condition-good-qty').val()) || 0;
    var damagedQty = parseFloat($('#condition-damaged-qty').val()) || 0;
    var missingQty = parseFloat($('#condition-missing-qty').val()) || 0;
    var conditionNote = $('#condition-note').val();
    var returnNote = $('#return-note').val();

    var conditionTotal = goodQty + damagedQty + missingQty;

    if (conditionTotal !== totalQty) {
        alert('ผลรวมจำนวนสภาพสินค้า (' + conditionTotal + ') ต้องเท่ากับจำนวนที่คืน (' + totalQty + ')');
        return;
    }

    // บันทึกข้อมูลใน hidden fields
    setConditionHiddenFields(index, goodQty, damagedQty, missingQty, conditionNote, returnNote);

    // อัพเดตการแสดงผล
    updateConditionSummary(index, goodQty, damagedQty, missingQty);

    $('#conditionModal').modal('hide');
}

// Set condition hidden fields
function setConditionHiddenFields(index, goodQty, damagedQty, missingQty, conditionNote, returnNote) {
    var container = $('.item').eq(index);

    // ลบ hidden fields เดิม
    container.find('input[name*="[good_qty]"], input[name*="[damaged_qty]"], input[name*="[missing_qty]"], input[name*="[condition_note]"], input[name*="[return_note]"]').remove();

    // เพิ่ม hidden fields ใหม่
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][good_qty]" value="' + goodQty + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][damaged_qty]" value="' + damagedQty + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][missing_qty]" value="' + missingQty + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][condition_note]" value="' + conditionNote + '">');
    container.append('<input type="hidden" name="JournalTransLine[' + index + '][return_note]" value="' + returnNote + '">');
}

// Update condition summary
function updateConditionSummary(index, goodQty, damagedQty, missingQty) {
    var summary = [];
    if (goodQty > 0) summary.push('ดี: ' + goodQty);
    if (damagedQty > 0) summary.push('เสีย: ' + damagedQty);
    if (missingQty > 0) summary.push('หาย: ' + missingQty);

    $('.condition-summary[data-index="' + index + '"]').text(summary.join(', '));
}

// Get condition data
function getConditionData(index) {
    var container = $('.item').eq(index);
    return {
        good_qty: container.find('input[name*="[good_qty]"]').val(),
        damaged_qty: container.find('input[name*="[damaged_qty]"]').val(),
        missing_qty: container.find('input[name*="[missing_qty]"]').val(),
        condition_note: container.find('input[name*="[condition_note]"]').val(),
        return_note: container.find('input[name*="[return_note]"]').val()
    };
}

// Update condition total
function updateConditionTotal() {
    var goodQty = parseFloat($('#condition-good-qty').val()) || 0;
    var damagedQty = parseFloat($('#condition-damaged-qty').val()) || 0;
    var missingQty = parseFloat($('#condition-missing-qty').val()) || 0;
    var total = goodQty + damagedQty + missingQty;

    $('#condition-total').text(total);

    var qtyInput = $('.qty-input[data-index="' + currentConditionIndex + '"]');
    var expectedTotal = parseFloat(qtyInput.val()) || 0;

    if (total === expectedTotal) {
        $('#condition-total').removeClass('text-danger').addClass('text-success');
        $('.modal-footer .btn-primary').prop('disabled', false);
    } else {
        $('#condition-total').removeClass('text-success').addClass('text-danger');
        $('.modal-footer .btn-primary').prop('disabled', true);
    }
}

// Validate condition quantities
function validateConditionQuantities() {
    var currentIndex = currentConditionIndex;
    if (currentIndex < 0) return false;

    var qtyInput = $(`.qty-input[data-index="${currentIndex}"]`);
    var totalReturnQty = parseFloat(qtyInput.val()) || 0;

    var goodQty = parseFloat($('#condition-good-qty').val()) || 0;
    var damagedQty = parseFloat($('#condition-damaged-qty').val()) || 0;
    var missingQty = parseFloat($('#condition-missing-qty').val()) || 0;

    var conditionTotal = goodQty + damagedQty + missingQty;
    var isValid = conditionTotal === totalReturnQty;

    $('#condition-total').toggleClass('text-success', isValid).toggleClass('text-danger', !isValid);
    $('.modal-footer .btn-primary').prop('disabled', !isValid);

    return isValid;
}

// Show loading indicator
function showLoading() {
    if ($('.loading-overlay').length === 0) {
        $('.card-body').append('<div class="loading-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 1000; display: flex; align-items: center; justify-content: center;"><i class="fa fa-spinner fa-spin fa-2x"></i> กำลังโหลด...</div>');
    }
}

// Hide loading indicator
function hideLoading() {
    $('.loading-overlay').remove();
}

// Show alert message
function showAlert(message, type) {
    var alertClass = 'alert-info';
    var icon = 'fa-info-circle';

    switch(type) {
        case 'success':
            alertClass = 'alert-success';
            icon = 'fa-check-circle';
            break;
        case 'warning':
            alertClass = 'alert-warning';
            icon = 'fa-exclamation-triangle';
            break;
        case 'error':
            alertClass = 'alert-danger';
            icon = 'fa-exclamation-circle';
            break;
    }

    var alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fa ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;

    // Remove existing alerts
    $('.alert').not('.flash-message').remove();

    // Add new alert at the top
    $('.journal-trans-form').prepend(alertHtml);

    // Auto-hide after 5 seconds for success messages
    if (type === 'success') {
        setTimeout(function() {
            $('.alert-success').fadeOut();
        }, 5000);
    }
}

// Transaction type to stock type mapping
const transTypeStockTypeMap = {
    '1': '1', // PO Receive -> Stock In
    '2': '2', // Cancel PO Receive -> Stock Out
    '3': '2', // Issue Stock -> Stock Out
    '4': '1', // Return Issue -> Stock In
    '5': '2', // Issue Borrow -> Stock Out
    '6': '1'  // Return Borrow -> Stock In
};

// Update stock type based on transaction type
function updateStockType(transTypeId) {
    const stockTypeSelect = document.getElementById('stock-type-select');
    if (transTypeStockTypeMap[transTypeId]) {
        stockTypeSelect.value = transTypeStockTypeMap[transTypeId];
    }
}

// Expose functions to global scope for onclick handlers
window.openConditionModal = openConditionModal;
window.saveConditionData = saveConditionData;
window.updateConditionTotal = updateConditionTotal;
window.updateStockType = updateStockType;