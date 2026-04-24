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
Add Job Work In – Raw Material
@else
Add Job Work In – Finished Goods
@endif
            </h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('jobworkin.store') }}" id="jobWorkForm">
            <input type="hidden" name="page_type" id="page_type" value="{{ $type }}">
            @csrf
            <input type="hidden" name="voucher_prefix" value="">

{{-- 🎯 ROW 1: Series No | Date | Voucher No --}}
<div class="row mb-3">
    <div class="col-md-3">
        <label class="form-label font-14 font-heading">Series No.</label>
        <select id="series_no" name="series_no" class="form-select" required>
            <option value="">Select</option>
            @foreach($GstSettings as $value)
                <option value="{{ $value->series }}"
                    data-mat_center="{{ $value->mat_center }}"
                    data-gst_no="{{ $value->gst_no }}"
                    data-invoice_start_from="{{ $value->invoice_start_from }}"
                    data-invoice_prefix="{{ $value->invoice_prefix }}"
                    data-manual_enter_invoice_no="{{ $value->manual_enter_invoice_no }}"
                    data-last_bill_date="{{ $value->last_bill_date ?? '' }}"
                    @if(count($GstSettings) == 1) selected @endif>
                    {{ $value->series }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Date</label>
        <input type="date"
       name="date"
       class="form-control"
       value="{{ old('date', $jobWork->date ?? date('Y-m-d')) }}"
       required>

    </div>
    <div class="col-md-4">
        <label class="form-label">Voucher No.</label>
        <input type="text" name="voucher_no" id="voucher_no" class="form-control"
       value="{{ old('voucher_no', $jobWork->voucher_no ?? '') }}" required>
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
                id="party_id"
                data-modal="accountModal">
            <option value="">Select Account</option>
            @foreach($party_list as $party)
                <option value="{{ $party->id }}"
                    {{ old('party_id', $jobWork->party_id ?? '') == $party->id ? 'selected' : '' }}>
                    {{ $party->account_name }}
                </option>
            @endforeach
        </select>
        <p id="partyaddress" style="font-size: 9px;"></p>
        @error('party_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Job Work OUT Voucher (hidden initially) --}}
    <div class="col-md-4" id="outVoucherCol"
     style="{{ $type == 'finished' ? '' : 'display:none;' }}">
        <label class="form-label">Job Work OUT Voucher</label>
        <select class="form-select select2-single"
                name="job_work_out_id"
                id="job_work_out_id">
            <option value="">Select OUT Voucher</option>
        </select>
    </div>

    {{-- Material Center --}}
    <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Material Center</label>
                     <select name="material_center" id="material_center" class="form-select" required>
                        <option value="">Select</option>
                        <?php
                        if(count($GstSettings) > 0) {
                           foreach ($GstSettings as $value){ ?>
                              <option value="<?php echo $value->mat_center;?>" <?php if(count($GstSettings)==1) { echo "selected";} ?>><?php echo $value->mat_center; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <ul style="color: red;">
                       @error('material_center'){{$message}}@enderror                        
                     </ul>
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
                        @if(false)
                           @foreach ($sale_order_items as $sale_order_item)
                              <tr id="tr_{{$add_more_count}}" class="font-14 font-heading bg-white">
                                 <td class="w-min-50" id="srn_{{$add_more_count}}">{{$add_more_count}}</td>
                                 <td class="w-min-50">
                                    <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_{{$add_more_count}}" data-id="{{$add_more_count}}" data-modal="itemModal">
                                       <option value="">Select Item</option>
                                       @foreach($items as $item_list)
                                          <option value="{{$item_list->id}}" @if($item_list->id==$sale_order_item['item_id']) selected @endif data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}" data-available_item="{{$item_list->available_item}}" data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>
                                       @endforeach
                                    </select>                                    
                                 </td>                           
                                 <td class="w-min-50">
                                    <input type="number" class="quantity w-100 form-control" id="quantity_tr_{{$add_more_count}}" name="qty[]" placeholder="Quantity" style="text-align:right;" data-id="{{$add_more_count}}" value="{{$sale_order_item['total_weight']}}" />
                                 </td>
                                 <td class="w-min-50">                              
                                    <input type="text" class="w-100 form-control unit" id="unit_tr_{{$add_more_count}}" readonly style="text-align:center;" data-id="{{$add_more_count}}"/>
                                    <input type="hidden" class="units w-100" name="units[]" id="units_tr_{{$add_more_count}}" />
                                 </td>
                                 <td class="w-min-50">
                                    <input type="number" class="price form-control" id="price_tr_{{$add_more_count}}" name="price[]" placeholder="Price" style="text-align:right;" data-id="{{$add_more_count}}" value="{{$sale_order_item['price']}}"/>
                                 </td>
                                 <td class=""><input type="number" id="amount_tr_{{$add_more_count}}" class="amount w-100 form-control" name="amount[]" placeholder="Amount"  style="text-align:right;" data-id="{{$add_more_count}}"/></td>
                                 <td class="" style="display:flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" data-id="{{$add_more_count}}"class="bg-primary rounded-circle add_more_wrapper" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                                 </td>
                              </tr>
                              @php $add_more_count++; @endphp
                           @endforeach
                           
                        @else
                           <tr id="tr_1" class="item-row font-14 font-heading bg-white">
    <td class="w-min-50 text-center py-3" id="srn_1">1</td>
    
    <!-- Item Selection Column -->
    <td class="py-2 align-middle">
        <div class="d-flex align-items-center gap-2 h-100">
            <select class="form-control item_id select2-single flex-grow-1" 
        name="goods_discription[]" id="item_id_1" data-id="1">
    <option value="">Select Item</option>
    @foreach($items as $item)
        <option value="{{ $item->id }}"
            data-unit="{{ $item->unit }}"
            data-parameterized_stock_status="{{ $item->parameterized_stock_status }}">
            {{ $item->name }}
        </option>
    @endforeach
</select>

        </div>
    </td>
    
    <!-- Item Description Column - CLEAN DESIGN -->
    <td class="py-2 align-middle">
        <div class="position-relative">
            <textarea class="form-control form-control-sm item_description h-100" 
                      name="item_description[]" id="item_description_1" data-id="1" 
                      rows="2" placeholder="Item description (optional)"
                      style="resize: none; font-size: 13px; line-height: 1.3; padding: 6px 8px; border-radius: 6px; border: 1px solid #d1d5db;"></textarea>
            <div class="position-absolute top-0 end-0 p-1">
                <small class="text-muted">Optional</small>
            </div>
        </div>
    </td>
    
    <!-- Rest of columns (Qty, Unit, Rate, Amount, Action) remain SAME -->
    <td class="text-right pr-3 py-2">
        <input type="number" class="quantity form-control form-control-sm" id="quantity_tr_1" 
               name="qty[]" placeholder="0.00" style="text-align:right; height: 34px;" data-id="1"/>
    </td>
    <td class="text-center py-2">
        <input type="text" class="unit form-control form-control-sm" id="unit_tr_1" 
               readonly style="text-align:center; height: 34px;" data-id="1"/>
        <input type="hidden" class="units" name="units[]" id="units_tr_1" />
    </td>
    <td class="text-right pr-3 py-2">
        <input type="number" class="price form-control form-control-sm" id="price_tr_1" 
               name="price[]" placeholder="0.00" style="text-align:right; height: 34px;" data-id="1"/>
    </td>
    <td class="text-right pr-3 py-2">
        <input type="number" class="amount form-control form-control-sm" id="amount_tr_1" 
               name="amount[]" placeholder="0.00" style="text-align:right; height: 34px;" data-id="1"/>
    </td>
    <td class="text-center py-2">
        <svg xmlns="http://www.w3.org/2000/svg" data-id="1" 
             class="bg-primary rounded-circle add_more_wrapper mx-auto d-block" 
             width="24" height="24" viewBox="0 0 24 24" fill="none" 
             style="cursor: pointer;" tabindex="0" role="button">
            <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
        </svg>
    </td>
</tr>


                        @endif
                        
                        
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
    let OUT_ITEMS = [];
let DEFAULT_ITEMS_HTML = '';
$(document).ready(function () {
    $('.select2-single').select2({
        placeholder: "Select Item",
        allowClear: true,
        width: '100%'
    });
    DEFAULT_ITEMS_HTML = $('#item_id_1').html();
    let pageType = $('#page_type').val();

if(pageType === 'raw'){
    $('#outVoucherCol').hide();
}
});
$(document).on('select2:opening', '.item_id', function (e) {
    let partyId = $('#party_id').val();

    if (!partyId) {
        e.preventDefault();   //  stop dropdown
        alert('Please select Party first.');
        $('#party_id').select2('open'); // focus party
    }
});
$(document).on('change', '.item_id', function () {

    let partyId = $('#party_id').val();
    if (!partyId) {
        alert('Please select Party first.');
        $(this).val(null).trigger('change');
        $('#party_id').focus();
        return;
    }

    let $opt   = $(this).find(':selected');
    let rowId  = $(this).data('id');

    let unit    = $opt.data('unit') || '';
    let pending = $opt.data('pending');

    // set unit
    $('#unit_tr_' + rowId).val(unit);
    $('#units_tr_' + rowId).val(unit);

    // reset qty & amount
    let $qty = $('#quantity_tr_' + rowId);
    $qty.val('');
    $qty.removeAttr('max');

    $('#amount_tr_' + rowId).val('');

    // OUT voucher pending restriction
    if (pending !== undefined) {
        $qty.attr('max', pending);
        $qty.focus();
    }

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

$(document).on('input', '.quantity, .price', function () {
    const rowId = $(this).data('id');
    const qty   = parseFloat($('#quantity_tr_' + rowId).val()) || 0;
    const price = parseFloat($('#price_tr_' + rowId).val()) || 0;

    $('#amount_tr_' + rowId).val((qty * price).toFixed(2));
    calculateJobWorkTotal();
});
$(document).on('input', '.amount', function () {
    calculateJobWorkTotal();
});

let row_uid = 1; 

/* ===============================
   ADD ROW (LIKE ADD SALE)
================================ */
function addMoreItem() {
    // validation: don't allow empty previous row
    let hasEmpty = false;
    $('.item_id').each(function () {
        const id = $(this).data('id');
        if (
            !$(this).val() ||
            !$('#quantity_tr_' + id).val() ||
            !$('#price_tr_' + id).val()
        ) {
            hasEmpty = true;
        }
    });

    if (hasEmpty) {
        alert('Please fill item, qty and price first');
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

            <button type="button"
        class="btn btn-outline-secondary btn-sm p-1 editItemDetailsBtn d-none"
        data-row="tr_${row_uid}">
                <i class="fas fa-cog text-muted"></i>
            </button>
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

let $newItem = $('#item_id_' + row_uid);

// decide items source
if ($('#job_work_out_id').val()) {
    populateItemDropdown($newItem);   // OUT items
} else {
    $newItem.html(DEFAULT_ITEMS_HTML); // ALL items
}

$newItem.select2({
    placeholder: "Select Item",
    allowClear: true,
    width: '100%'
});

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
    class="add_more_wrapper"
    data-id="${id}"
    width="24" height="24"
    style="cursor:pointer"
    viewBox="0 0 24 24" fill="none">
    <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
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


$(document).on('change', '#party_id', function () {

    let pageType = $('#page_type').val();
    let partyId  = $(this).val();

    if(pageType === 'raw'){
        $('#outVoucherCol').hide();
        return;
    }

    $('#outVoucherCol').show();

    $('#job_work_out_id')
        .empty()
        .append('<option value="">Select OUT Voucher</option>');

    if (!partyId) {
        return;
    }

    $.get("{{ route('jobworkin.getOutVouchers') }}", {
        party_id: partyId
    }, function (data) {

        if (data.length > 0) {

            data.forEach(v => {
                $('#job_work_out_id').append(
                    `<option value="${v.id}">${v.voucher_no_prefix}</option>`
                );
            });

        }

    });

});
$('#job_work_out_id').on('change', function () {

    let outId = $(this).val();
    OUT_ITEMS = [];

    $('.item_id').val(null).trigger('change');

    if (!outId) {

    OUT_ITEMS = []; 

    $('.item_id').each(function () {
        let $sel = $(this);

        if ($sel.hasClass("select2-hidden-accessible")) {
            $sel.select2('destroy');
        }

        $sel.html(DEFAULT_ITEMS_HTML);

        $sel.select2({
            placeholder: "Select Item",
            allowClear: true,
            width: '100%'
        });
    });

    return;
}


    $.get("{{ route('jobworkin.outItems') }}", {
    job_work_out_id: outId
}, function (res) {

    console.log('OUT ITEMS RESPONSE:', res); 

    OUT_ITEMS = res;

    $('.item_id').each(function () {
        populateItemDropdown($(this));
    });
});

});


function populateItemDropdown($select) {

    if (!OUT_ITEMS.length) {
        $select.html(DEFAULT_ITEMS_HTML).select2({ width: '100%' });
        return;
    }

    if ($select.hasClass("select2-hidden-accessible")) {
        $select.select2('destroy');
    }

    $select.empty().append('<option value="">Select Item</option>');

    OUT_ITEMS.forEach(item => {
        $select.append(`
            <option 
                value="${item.out_desc_id}"
                data-desc-id="${item.out_desc_id}"
                data-pending="${item.pending_qty}"
                data-unit="${item.unit}"
                data-parameterized_stock_status="${item.parameterized_stock_status}">
                ${item.item_name} (Pending: ${item.pending_qty})
            </option>
        `);
    });

    $select.select2({
        placeholder: "Select Item",
        allowClear: true,
        width: '100%'
    });
}

$(document).on('input', '.quantity', function () {

    let max = parseFloat($(this).attr('max'));
    let val = parseFloat($(this).val());

    if (!isNaN(max) && val > max) {
        alert('Quantity cannot exceed pending quantity');
        $(this).val(max);
    }
});
// ✅ SERIES CHANGE (material center + duplicate check)
$('#series_no').on('change', function () {
    let matCenter = $('option:selected', this).data('mat_center') || '';
    $('#material_center').val(matCenter);

    checkDuplicateVoucher();
});

// ✅ VOUCHER BLUR
$('#voucher_no').on('blur', function () {
    checkDuplicateVoucher();
});

// ✅ PARTY CHANGE
$('#party_id').on('change', function () {
    checkDuplicateVoucher();
});

// ✅ COMMON FUNCTION (MAIN LOGIC)
function checkDuplicateVoucher() {

    let voucherNo = $('#voucher_no').val();
    let partyId   = $('#party_id').val();
    let seriesNo  = $('#series_no').val();

    if (!voucherNo || !partyId || !seriesNo) return;

    $.ajax({
        url: "{{ route('jobworkin.checkVoucher') }}",
        type: "POST",
        data: {
            voucher_no: voucherNo,
            party_id: partyId,
            series_no: seriesNo,
            _token: "{{ csrf_token() }}"
        },
        success: function (res) {

            if (res.exists) {
                alert('This voucher number already exists. Please change it.');
                $('#voucher_no').val('').focus();
            }
        }
    });
}

// ✅ FINAL SUBMIT CHECK
$('#jobWorkForm').on('submit', function(e){

    let voucherNo = $('#voucher_no').val();
    let partyId   = $('#party_id').val();
    let seriesNo  = $('#series_no').val();
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