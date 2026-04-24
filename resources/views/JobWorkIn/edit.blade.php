@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
   .text-ellipsis {
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
   }
   .w-min-50 {
      min-width: 50px;
   }
   .dataTables_filter,
   .dataTables_info,
   .dataTables_length,
   .dataTables_paginate {
      display: none;
   }
   .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 29px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
      height: 30px !important;
   }
   .select2-container .select2-selection--single{
      height: 30px !important;
   }
   .select2-container{
          width: 300 px !important;
   }
   .select2-container--default .select2-selection--single{
      border-radius: 12px !important;
   }
   .selection{
      font-size: 14px;
   }
   .form-control {
      height: 28px;
   }
   .form-select {
      height: 34px;
   }
   input[type=number]::-webkit-inner-spin-button, 
   input[type=number]::-webkit-outer-spin-button { 
       -webkit-appearance: none;
       -moz-appearance: none;
       appearance: none;
       margin: 0; 
   }
   .item-row td {
    vertical-align: middle !important;
    border-color: #e5e7eb;
}

.form-control-sm {
    padding: 4px 8px;
    font-size: 13px;
}

.textarea-description {
    background-color: #fafbfc;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}

.action-cell svg {
    transition: all 0.2s ease;
}

.action-cell svg:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.table th {
    font-weight: 600;
    letter-spacing: 0.025em;
}

</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
             @if (session('error'))
             <div class="alert alert-danger" role="alert"> {{session('error')}}
             </div>
             @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                @if($type == 'raw')
Edit Job Work In – Raw Material
@else
Edit Job Work In – Finished Goods
@endif
            </h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('jobworkin.update', $jobWork->id) }}" id="jobWorkForm">
            <input type="hidden" id="edit_id" value="{{ $jobWork->id }}">
            <input type="hidden" name="page_type" id="page_type" value="{{ $type }}">
            @csrf
            <input type="hidden" name="voucher_prefix" value="">

{{-- 🎯 ROW 1: Series No | Date | Voucher No --}}
<div class="row mb-3">
    <div class="col-md-3">
        <label class="form-label font-14 font-heading">Series No. <span class="text-danger">*</span></label>
        @error('series_no')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
        <select id="series_no" name="series_no" class="form-select @error('series_no') is-invalid @enderror" required>
            <option value="">Select</option>
            @foreach($GstSettings as $value)
                <option value="{{ $value->series }}"
                        data-mat_center="{{ $value->mat_center }}"
                        data-gst_no="{{ $value->gst_no }}"
                        data-invoice_start_from="{{ $value->invoice_start_from }}"
                        data-invoice_prefix="{{ $value->invoice_prefix }}"
                        data-manual_enter_invoice_no="{{ $value->manual_enter_invoice_no }}"
                        {{ old('series_no', $jobWork->series_no) == $value->series ? 'selected' : '' }}>
                    {{ $value->series }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Date</label>
        <input type="date" name="date" class="form-control" value="{{ old('date', $jobWork->date ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Voucher No.</label>
        <input type="text" id="voucher_no" name="voucher_no" class="form-control" value="{{ old('voucher_no', $jobWork->voucher_no ?? '') }}" required>
    </div>
</div>

{{-- 🎯 ROW 2: Party | Material Center --}}
{{-- 🎯 ROW 2: Party | Job Work OUT | Material Center --}}
<div class="row mb-4">

    {{-- Party --}}
    <div class="col-md-4">
        <label for="party_id" class="form-label font-14 font-heading">Party</label>
        <select class="form-select select2-single"
                name="party_id"
                id="party_id">
            <option value="">Select Account</option>
            @foreach($party_list as $party)
                <option value="{{ $party->id }}"
                    {{ $jobWork->party_id == $party->id ? 'selected' : '' }}>
                    {{ $party->account_name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Job Work OUT Voucher (same as Add) --}}
    <div class="col-md-4" id="outVoucherCol" style="display:none;">
        <label class="form-label">Job Work OUT Voucher</label>
        <select class="form-select select2-single"
                name="job_work_out_id"
                id="job_work_out_id">
            <option value="">Select OUT Voucher</option>
        </select>
    </div>

    {{-- Material Center --}}
    <div class="col-md-4">
    <label class="form-label font-14 font-heading">Material Center <span class="text-danger">*</span></label>
    @error('material_center')
        <div class="text-danger small">{{ $message }}</div>
    @enderror
    <select name="material_center" id="material_center" class="form-select @error('material_center') is-invalid @enderror" required>
        <option value="">Select</option>
        @foreach($GstSettings as $value)
            <option value="{{ $value->mat_center }}" {{ old('material_center', $jobWork->material_center) == $value->mat_center ? 'selected' : '' }}>
                {{ $value->mat_center }}
            </option>
        @endforeach
    </select>
</div>

</div>

                <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
<tr class="font-12 text-body bg-light-pink">
    <th class="w-min-50 border-none bg-light-pink text-body" text-center py-2 style="width: 1%;">S.No</th>
    <th class="w-min-50 border-none bg-light-pink text-body" style="width: 22%;">Item</th>
    <th class="w-min-50 border-none bg-light-pink text-body" style="width: 22%;">Description</th>
    <th class="w-min-50 border-none bg-light-pink text-body text-right pr-3 py-2" style="width: 8%;">Qty</th>
    <th class="w-min-50 border-none bg-light-pink text-body text-center py-2" style="width: 8%;">Unit</th>
    <th class="w-min-50 border-none bg-light-pink text-body text-right pr-3 py-2" style="width: 10%;">Rate</th>
    <th class="w-min-50 border-none bg-light-pink text-body text-right pr-3 py-2" style="width: 10%;">Amount</th>
    <th class="w-min-50 border-none bg-light-pink text-body text-center py-2" style="width: 8%;">Action</th>
</tr>
</thead>

                     <tbody>
                       
                        @php $add_more_count = 1; @endphp
                        
                           @php $row = 1; @endphp
@foreach($jobWorkDescriptions as $desc)
<tr id="tr_{{ $row }}" class="item-row font-14 font-heading bg-white">
    <td class="w-min-50 text-center py-3 srn">{{ $row }}</td>

    <td class="py-2 align-middle">
        <div class="d-flex align-items-center gap-2 h-100">
            <select class="form-control item_id select2-single"
                name="goods_discription[]"
                id="item_id_{{ $row }}"
                data-id="{{ $row }}">
                <option value="">Select Item</option>
                @foreach($items as $item)
                    <option value="{{ $desc->jw_out_description_id ?? $item->id }}"
                        data-unit="{{ $item->unit }}"
                        {{ $item->id == $desc->item_id ? 'selected' : '' }}>
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </td>

    <td>
        <textarea name="item_description[]"
            class="form-control form-control-sm item_description"
            data-id="{{ $row }}">{{ $desc->item_description }}</textarea>
    </td>

    <td>
        <input type="number"
            name="qty[]"
            id="quantity_tr_{{ $row }}"
            class="quantity form-control form-control-sm"
            data-id="{{ $row }}"
            value="{{ $desc->qty }}">
    </td>

    <td>
        <input type="text"
            class="unit form-control form-control-sm"
            id="unit_tr_{{ $row }}"
            value="{{ $desc->unit }}"
            readonly>
        <input type="hidden" name="units[]" id="units_tr_{{ $row }}" value="{{ $desc->unit }}">
    </td>

    <td>
        <input type="number"
    name="price[]"
    id="price_tr_{{ $row }}"
    class="price form-control form-control-sm"
    data-id="{{ $row }}"
    value="{{ $desc->price }}">
    </td>

    <td>
        <input type="number"
    name="amount[]"
    id="amount_tr_{{ $row }}"
    class="amount form-control form-control-sm"
    value="{{ $desc->amount }}">
    </td>

    <td class="action-cell"></td>

    <input type="hidden" name="desc_id[]" value="{{ $desc->id }}">

</tr>
@php $row++; @endphp
@endforeach

                        
                        
                     </tbody>
                     <tfoot>
   <tr class="font-14 font-heading bg-white">
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td></td>
      <td class="fw-bold">Total</td>
      <td class="fw-bold">
         <span id="totalSum" style="float:right;"></span>
         <input type="hidden" name="total" id="total_taxable_amounts" value="0">
      </td>
      <td></td>
   </tr>
</tfoot>
                  </table>
               </div>
               {{-- ================= TRANSPORT INFO ================= --}}
{{-- 🎯 SIMPLE RIGHT-ALIGNED TRANSPORT --}}
<div class="row mt-4">
    <div class="col-md-8"></div> <!-- Empty space left -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 bg-white" style="border-radius: 12px;">
            <h6 class="mb-3 font-14 fw-bold text-dark">Transport Info</h6>
            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small text-muted mb-1">Vehicle No.</label>
                    <input type="text" name="vehicle_no" class="form-control form-control-sm" 
                           placeholder="Vehicle No." value="{{ old('vehicle_no', $jobWork->vehicle_no ?? '') }}">
                </div>
                <div class="col-6">
                    <label class="form-label small text-muted mb-1">GR/RR No.</label>
                    <input type="text" name="gr_rr_no" class="form-control form-control-sm" 
                           placeholder="GR/RR No." value="{{ old('gr_rr_no', $jobWork->gr_rr_no ?? '') }}">
                </div>
                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Transport Name</label>
                    <input type="text" name="transport_name" class="form-control form-control-sm" 
                           placeholder="Transport Name" value="{{ old('transport_name', $jobWork->transport_name ?? '') }}">
                </div>
                <div class="col-6">
                    <label class="form-label small text-muted mb-1">Station</label>
                    <input type="text" name="station" class="form-control form-control-sm" 
                           placeholder="Station" value="{{ old('station', $jobWork->station ?? '') }}">
                </div>
                <div class="col-6">
                    <label class="form-label small text-muted mb-1">Reverse Charge</label>
                    <select name="reverse_charge" class="form-select form-select-sm">
                        <option value="">Select</option>
                        <option value="Yes" {{ old('reverse_charge', $jobWork->reverse_charge ?? '') == 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('reverse_charge', $jobWork->reverse_charge ?? '') == 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="d-flex">
  <div class="ms-auto">
    <button type="submit" class="btn btn-xs-primary">SAVE</button>
@if($type == 'raw')
<a href="{{ route('jobworkin.raw') }}" class="btn btn-black">QUIT</a>
@else
<a href="{{ route('jobworkin.finished') }}" class="btn btn-black">QUIT</a>
@endif
  </div>
</div>
    </form>
         </div>
      </div>
   </section>
</div>

@include('layouts.footer')
<script>
    let EDIT_SELECTED_ITEMS = {};
let EDIT_ITEM_META = {};
let EDIT_BOOTSTRAP_DONE = false;
let EDIT_INITIALIZING = true;
    let OUT_ITEMS = [];
let DEFAULT_ITEMS_HTML = '';
    const EDIT_OUT_ID = "{{ $jobWork->job_work_out_id ?? '' }}";
    const IS_EDIT = true;
    let row_uid = {{ count($jobWorkDescriptions) }};

$(document).ready(function () {
    let pageType = $('#page_type').val();

    // 🎯 FIXED: Control OUT Voucher column visibility
    if(pageType === 'raw') {
        $('#outVoucherCol').hide();
    } else {
        $('#outVoucherCol').show(); // ALWAYS show for FINISHED
    }

    $('.select2-single').select2({
        placeholder: "Select Item",
        allowClear: true,
        width: '100%'
    });
    
    setTimeout(function () {
        calculateJobWorkTotal();
    }, 100);
});

$(document).on('input', '.amount', function () {
    calculateJobWorkTotal();
});

$(document).ready(function () {

    // Save default item list HTML (full list)
    if (!DEFAULT_ITEMS_HTML) {
        let $tmp = $('.item_id').first().clone();
$tmp.find('option').prop('selected', false);   // 🔥 clear selection
DEFAULT_ITEMS_HTML = $tmp.html();
    }

});

$(document).on('change', '.item_id', function () {

    const rowId = $(this).data('id');
    const unit  = $(this).find(':selected').data('unit') || '';

    $('#unit_tr_' + rowId).val(unit);
    $('#units_tr_' + rowId).val(unit);

    $('#quantity_tr_' + rowId).val('');
    $('#price_tr_' + rowId).val('');
    $('#amount_tr_' + rowId).val('');

    calculateJobWorkTotal();
});

function calculateJobWorkTotal() {
    let total = 0;

    $('.amount').each(function () {
        const val = parseFloat($(this).val());
        if (!isNaN(val)) {
            total += val;
        }
    });

    $('#totalSum').text(total.toFixed(2));
    $('#total_taxable_amounts').val(total.toFixed(2));
}

$(document).on('input', '.quantity', function () {

    let row = $(this).closest('tr');
    let qtyInput = $(this);
    let qty = parseFloat(qtyInput.val()) || 0;

    let select = row.find('.item_id');
    let pendingQty = parseFloat(
        select.find(':selected').data('pending-qty')
    );

    // 🔥 ONLY enforce when OUT voucher item
    if (!isNaN(pendingQty)) {

        if (qty > pendingQty) {
            qtyInput.val(pendingQty);
            alert('Entered quantity cannot be greater than pending quantity (' + pendingQty + ')');
        }
    }

    recalcRowByElement(this);
});

$(document).on('input', '.price', function () {
    recalcRowByElement(this);
});

/* ===============================
   ADD ROW (LIKE ADD SALE)
================================ */
function addMoreItem() {

    let hasInvalid = false;

    $('.item-row').each(function () {
        let item  = $(this).find('.item_id').val();
        let qty   = parseFloat($(this).find('.quantity').val());
        let rate  = parseFloat($(this).find('.price').val());

        if (!item || isNaN(qty) || qty <= 0 || isNaN(rate) || rate <= 0) {
            hasInvalid = true;
            return false; // break loop
        }
    });

    if (hasInvalid) {
        alert('Please fill item, qty and rate (greater than 0)');
        return;
    }

    row_uid++;

let newRow = `
<tr id="tr_${row_uid}" class="item-row font-14 font-heading bg-white">
    <td class="w-min-50 text-center py-3 srn">${row_uid}</td>
    
    <!-- Item Selection -->
    <td class="py-2 align-middle">
        <div class="d-flex align-items-center gap-2 h-100">
            <select class="form-control item_id select2-single flex-grow-1"
                    name="goods_discription[]"
                    id="item_id_${row_uid}"
                    data-id="${row_uid}"
                    style="height: 34px;">
                <option value="">Select Item</option>
            </select>
        </div>
    </td>
    
    <!-- Item Description - Professional Look -->
    <td class="py-2 align-middle">
        <div class="position-relative">
            <textarea class="form-control form-control-sm item_description h-100" 
                      name="item_description[]" id="item_description_${row_uid}" 
                      data-id="${row_uid}" rows="2" 
                      placeholder="Item description (optional)"
                      style="resize: none; font-size: 13px; line-height: 1.3; padding: 6px 8px; border-radius: 6px; border: 1px solid #d1d5db; height: 68px;"></textarea>
            <div class="position-absolute top-0 end-0 p-1">
                <small class="text-muted">Opt.</small>
            </div>
        </div>
    </td>
    
    <td class="text-right pr-3 py-2">
        <input type="number" class="quantity form-control form-control-sm" 
               name="qty[]" id="quantity_tr_${row_uid}" data-id="${row_uid}" 
               placeholder="0.00" style="text-align:right; height: 34px;">
    </td>
    
    <td class="text-center py-2">
        <input type="text" class="unit form-control form-control-sm" 
               id="unit_tr_${row_uid}" readonly data-id="${row_uid}" 
               style="text-align:center; height: 34px;">
        <input type="hidden" name="units[]" id="units_tr_${row_uid}">
    </td>
    
    <td class="text-right pr-3 py-2">
        <input type="number" class="price form-control form-control-sm" 
               name="price[]" id="price_tr_${row_uid}" data-id="${row_uid}" 
               placeholder="0.00" style="text-align:right; height: 34px;">
    </td>
    
    <td class="text-right pr-3 py-2">
        <input type="number" class="amount form-control form-control-sm" 
               name="amount[]" id="amount_tr_${row_uid}" data-id="${row_uid}" 
               placeholder="0.00" style="text-align:right; height: 34px;">
    </td>
    
    <td class="action-cell text-center py-2"></td>
</tr>`;


    $('#example11 tbody').append(newRow);
    EDIT_SELECTED_ITEMS[row_uid] = null;
    // 🔥 Populate item dropdown ONLY from OUT voucher items
let $newSelect = $('#item_id_' + row_uid);

// 🔥 CASE 1: OUT voucher selected
if ($('#job_work_out_id').val()) {

    // ✅ Pending OUT items exist → show them
if (OUT_ITEMS.length > 0 || IS_EDIT) {
    populateItemDropdown($newSelect);
} else {
    $newSelect.html('<option value="">No items available</option>');
}


}
// 🔥 CASE 2: No OUT voucher → normal Job Work IN
else {
    $newSelect.html(DEFAULT_ITEMS_HTML);
}

$newSelect.val(null).trigger('change.select2');
$newSelect.select2({ width: '100%' });

    $('.select2-single').select2({ width: '100%' });
    rebuildRowIcons();
    reindexSRN();
}


/* ===============================
   REMOVE ROW
================================ */
function removeItem(id) {
    $('#tr_' + id).remove();
    rebuildRowIcons();
    reindexSRN();
    calculateJobWorkTotal();
}

/* ===============================
   ICON RULES (SAME AS SALE)
================================ */
function rebuildRowIcons() {

    let rows = $('.item-row');
    let total = rows.length;

    rows.each(function (index) {

        let row  = $(this);
        let id   = row.find('.item_id').data('id');
        let cell = row.find('.action-cell');

let addIcon = `
<svg xmlns="http://www.w3.org/2000/svg"
    class="add_more_wrapper add-btn"
    data-id="${id}"
    width="28" height="28"
    viewBox="0 0 24 24"
    style="cursor:pointer;">
    <circle cx="12" cy="12" r="12" fill="#2563eb"/>
    <path d="M11 17V13H7V11H11V7H13V11H17V13H13V17H11Z" fill="#ffffff"/>
</svg>`;

        let removeIcon = `
<svg xmlns="http://www.w3.org/2000/svg"
    class="remove"
    data-id="${id}"
    width="22" height="22"
    style="cursor:pointer;color:red;margin-right:6px"
    viewBox="0 0 16 16" fill="currentColor">
    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
</svg>`;

        cell.empty();

        if (total === 1) {
            cell.append(addIcon);
        } else if (index === total - 1) {
            cell.append(removeIcon + addIcon);
        } else {
            cell.append(removeIcon);
        }
    });
}

/* ===============================
   SERIAL NUMBER FIX
================================ */
function reindexSRN() {
    let i = 1;
    $('.item-row').each(function () {
        $(this).find('.srn').text(i++);
    });
}

/* ===============================
   EVENTS
================================ */
$(document).on('click', '.add_more_wrapper', function () {
    addMoreItem();
});

$(document).on('click', '.remove', function () {
    let id = $(this).data('id');
    removeItem(id);
});
$(document).ready(function () {
    rebuildRowIcons();
    reindexSRN();
});

function recalcRowByElement(el) {
    let row = $(el).closest('tr');

    let qty   = parseFloat(row.find('.quantity').val()) || 0;
    let rate  = parseFloat(row.find('.price').val()) || 0;

    let amount = qty * rate;

    row.find('.amount').val(amount.toFixed(2));

    calculateJobWorkTotal();
}
$(document).on('change', '#party_id', function () {
    let partyId = $(this).val();
    let pageType = $('#page_type').val();

    $('#job_work_out_id')
        .empty()
        .append('<option value="">Select OUT Voucher</option>');

    // 🔹 RAW: Always hide
    if (pageType === 'raw') {
        $('#outVoucherCol').hide();
        return;
    }

    // 🔹 FINISHED: ALWAYS SHOW column (never hide)
    $('#outVoucherCol').show();

    // Only load vouchers if party selected (FINISHED only)
    if (!partyId) {
        return; // Keep column visible but empty
    }

    $.ajax({
        url: "{{ route('jobworkin.getOutVouchers') }}",
        type: "GET",
        data: { party_id: partyId },
        success: function (data) {
            if (data.length > 0) {
                $.each(data, function (i, v) {
                    let voucher = '';
                    if (v.voucher_no_prefix && v.voucher_no) {
                        voucher = v.voucher_no_prefix + v.voucher_no;
                    } 
                    else if (v.voucher_no) {
                        voucher = v.voucher_no;
                    }
                    else if (v.voucher_no_prefix) {
                        voucher = v.voucher_no_prefix;
                    }
                    else {
                        voucher = 'Voucher #' + v.id;
                    }

                    $('#job_work_out_id').append(
                        `<option value="${v.id}">${voucher}</option>`
                    );
                });

                // 🔥 PRESELECT for EDIT (FINISHED only)
                if (EDIT_OUT_ID) {
                    $('#job_work_out_id')
                        .val(EDIT_OUT_ID)
                        .trigger('change');
                }
            }
            // ✅ NO ELSE - Don't hide column even if no vouchers
        }
    });
});


$(document).ready(function () {
    // 🔥 Only for FINISHED with existing OUT ID
    if ($('#party_id').val() && EDIT_OUT_ID && $('#page_type').val() !== 'raw') {
        loadOutVouchersForEdit();
    }
    rebuildRowIcons();
    reindexSRN();
});

function loadOutVouchersForEdit() {

    let partyId = $('#party_id').val();
    if (!partyId) return;

    $.get("{{ route('jobworkin.getOutVouchers') }}", {
        party_id: partyId,
        current_out_id: EDIT_OUT_ID   // 🔥 CRITICAL for edit mode
    }, function (data) {

        let $outSelect = $('#job_work_out_id');

        $outSelect
            .empty()
            .append('<option value="">Select OUT Voucher</option>');

        data.forEach(v => {

    let voucher = '';

    if (v.voucher_no_prefix && v.voucher_no) {
        voucher = v.voucher_no_prefix + v.voucher_no;
    } 
    else if (v.voucher_no) {
        voucher = v.voucher_no;
    }
    else if (v.voucher_no_prefix) {
        voucher = v.voucher_no_prefix;
    }
    else {
        voucher = 'Voucher #' + v.id;
    }

    $outSelect.append(
        `<option value="${v.id}">${voucher}</option>`
    );

});

        $('#outVoucherCol').show();

        // ✅ FORCE PREFILL (even if completed)
        if (EDIT_OUT_ID) {
            $outSelect.val(EDIT_OUT_ID).trigger('change.select2');
        }

        // ✅ BOOTSTRAP COMPLETE
        EDIT_BOOTSTRAP_DONE = true;

        // 🔥 LOAD OUT ITEMS ONLY ONCE (SAFE)
        loadOutItemsForEdit();
    });
}

function loadOutItemsForEdit() {

    let outId = EDIT_OUT_ID;
    if (!outId) return;

    // 🔐 Save selected item + its metadata BEFORE dropdown rebuild
    $('.item_id').each(function () {

        let rowId = $(this).data('id');
        let opt   = $(this).find('option:selected');

        EDIT_SELECTED_ITEMS[rowId] = opt.val() || null;

        if (opt.val()) {
            EDIT_ITEM_META[rowId] = {
                id: opt.val(),
                name: opt.text(),
                unit: opt.data('unit'),
                parameterized: opt.data('parameterized_stock_status')
            };
        }
    });

    // 🔁 Load OUT items (pending only)
    $.get("{{ route('jobworkin.outItems') }}", {
        job_work_out_id: outId
    }, function (res) {

        OUT_ITEMS = res;

        // 🔄 Rebuild item dropdowns safely
        $('.item_id').each(function () {
            populateItemDropdown($(this));
        });

        // 🔓 EDIT MODE FULLY READY
        EDIT_INITIALIZING = false;
    });
}



$(document).on('change', '#job_work_out_id', function () {

    // 🚫 BLOCK auto-fire during edit load
    if (IS_EDIT && !EDIT_BOOTSTRAP_DONE) {
        return;
    }

    let outId = $(this).val();
    OUT_ITEMS = [];

    // 🔐 Save current selections
    $('.item_id').each(function () {
        EDIT_SELECTED_ITEMS[$(this).data('id')] = $(this).val();
    });

    // Clear dropdowns
    $('.item_id').html('<option value="">Select Item</option>');

    if (!outId) {
        $('.item_id').each(function () {
            $(this).html(DEFAULT_ITEMS_HTML);
            let rowId = $(this).data('id');
            if (EDIT_SELECTED_ITEMS[rowId]) {
                $(this).val(EDIT_SELECTED_ITEMS[rowId]);
            }
            $(this).trigger('change.select2');
        });
        return;
    }

    $.get("{{ route('jobworkin.outItems') }}", {
        job_work_out_id: outId
    }, function (res) {

        OUT_ITEMS = res;

        $('.item_id').each(function () {
            populateItemDropdown($(this));
        });
    });
});

function populateItemDropdown($select) {

    let rowId = $select.data('id');
    let selectedVal = EDIT_SELECTED_ITEMS[rowId] || null;
    let found = false;

    $select.empty().append('<option value="">Select Item</option>');

    OUT_ITEMS.forEach(item => {

        if (item.out_desc_id == selectedVal) {
            found = true;
        }

        $select.append(`
            <option value="${item.out_desc_id}"
    data-item-id="${item.item_id}"
    data-unit="${item.unit}"
    data-pending-qty="${item.pending_qty}"
    data-parameterized_stock_status="${item.parameterized_stock_status}">
    ${item.item_name} (${item.pending_qty})
</option>

        `);
    });

    // EDIT SAFE
    if (selectedVal && !found && EDIT_ITEM_META[rowId]) {

        let meta = EDIT_ITEM_META[rowId];

        $select.append(`
            <option value="${selectedVal}"
                data-item-id="${meta.item_id}"
                data-unit="${meta.unit}"
                data-parameterized_stock_status="${meta.parameterized}">
                ${meta.name} (already used)
            </option>
        `);
    }

    if (selectedVal) {
        $select.val(selectedVal);
    }

    $select.trigger('change.select2');
}

$('#series_no').on('change', function () {
    let matCenter = $('option:selected', this).data('mat_center') || '';
    $('#material_center').val(matCenter);
});
// ✅ DUPLICATE CHECK ON BLUR (EDIT SAFE)
$('#voucher_no').on('blur', function () {

    let voucherNo = $(this).val();
    let partyId   = $('#party_id').val();
    let seriesNo  = $('#series_no').val();
    let editId    = $('#edit_id').val();

    if (!voucherNo || !partyId || !seriesNo) return;

    $.ajax({
        url: "{{ route('jobworkin.checkVoucher') }}",
        type: "POST",
        data: {
            voucher_no: voucherNo,
            party_id: partyId,
            series_no: seriesNo,
            id: editId, // ✅ IMPORTANT
            _token: "{{ csrf_token() }}"
        },
        success: function (res) {

            if (res.exists) {
                alert('This voucher number already exists. Please change it.');
                $('#voucher_no').val('').focus();
            }
        }
    });

});


// ✅ FINAL SUBMIT CHECK (EDIT SAFE)
$('#jobWorkForm').on('submit', function(e){

    let voucherNo = $('#voucher_no').val();
    let partyId   = $('#party_id').val();
    let seriesNo  = $('#series_no').val();
    let editId    = $('#edit_id').val();
    let pageType  = $('#page_type').val();

    let isDuplicate = false;

    $.ajax({
        url: "{{ route('jobworkin.checkVoucher') }}",
        type: "POST",
        async: false,
        data: {
            voucher_no: voucherNo,
            party_id: partyId,
            series_no: seriesNo,
            id: editId, // ✅ IMPORTANT
            _token: "{{ csrf_token() }}"
        },
        success: function (res) {
            if (res.exists) {
                isDuplicate = true;
            }
        }
    });

    if (isDuplicate) {
        alert('Duplicate voucher not allowed.');
        $('#voucher_no').val('').focus();
        e.preventDefault();
        return false;
    }

    // 🔴 existing validation
    if(pageType === 'finished'){

        let outVoucher = $('#job_work_out_id').val();

        if(!outVoucher){
            alert('Job Work OUT Voucher is required for Finished Goods.');
            $('#job_work_out_id').focus();
            e.preventDefault();
            return false;
        }
    }

});
</script>
@endsection