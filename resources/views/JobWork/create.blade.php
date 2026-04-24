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
                Add Job Work Out – Raw Material
                @else
                Add Job Work Out – Finished Goods
                @endif
            </h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('jobwork.store') }}" id="jobWorkForm">
            @csrf
            <input type="hidden" name="page_type" id="page_type" value="{{ $type }}">
<input type="hidden" name="job_work_in_id" id="job_work_in_id_hidden">

{{-- 🎯 ROW 1: Series No | Date | Voucher No --}}
<div class="row mb-3">

    {{-- SERIES --}}
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

    {{-- DATE --}}
    <div class="col-md-3">
        <label class="form-label font-14 font-heading">Date</label>
        <input type="date"
               id="date"
               name="date"
               class="form-control"
               value="{{ date('Y-m-d') }}"
               min="{{ $fy_start_date }}"
               max="{{ $fy_end_date }}"
               required>
    </div>

    {{-- VOUCHER --}}
    <div class="col-md-3">
        <label class="form-label font-14 font-heading">Voucher No.</label>

        {{-- UI DISPLAY (READ ONLY) --}}
<input type="text"
       class="form-control"
       id="voucher_display"
       style="text-align:right">

{{-- DB STORAGE --}}
<input type="hidden" name="voucher_prefix" id="voucher_prefix">
<input type="hidden" name="voucher_no" id="voucher_no">
<input type="hidden" id="manual_enter_invoice_no">
<input type="hidden" id="merchant_gst">
    </div>
</div>

{{-- 🎯 ROW 2: Party | Material Center --}}
<div class="row mb-4">

    {{-- Party --}}
    <div class="col-md-4">
        <label class="form-label font-14 font-heading">Party</label>
        <select class="form-select select2-single"
                name="party_id"
                id="party_id">
            <option value="">Select Account</option>
            @foreach($party_list as $party)
                <option value="{{ $party->id }}"
    data-address="{{ $party->address }}"
    data-pincode="{{ $party->pin_code }}"
    data-gst="{{ $party->gstin }}"
    data-pan="{{ $party->pan ?? '' }}"
    data-other_address='@json($party->otherAddress ?? [])'>
    {{ $party->account_name }}
</option>
            @endforeach
        </select>
    </div>

    {{-- Job Work IN Voucher --}}
    <div class="col-md-4" id="inVoucherCol"
     style="{{ $type == 'finished' ? '' : 'display:none;' }}">
        <label class="form-label">Job Work IN Voucher</label>
        <select class="form-select select2-single"
                id="job_work_in_id">
            <option value="">Select Voucher</option>
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
                    name="goods_discription[]" id="item_id_1" data-id="1" data-modal="itemModal">
                <option value="">Select Item</option>
                @foreach($items as $item_list)
                    <option value="{{ $item_list->id }}"
                            data-unit="{{ $item_list->unit }}"
                            data-parameterized_stock_status="{{ $item_list->parameterized_stock_status }}">
                        {{ $item_list->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="in_desc_id[]" id="in_desc_id_1">
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
                        <td></td> <!-- S.No -->
                        <td></td> <!-- Item -->
                        <td></td> <!-- Description -->
                        <td></td> <!-- Qty -->
                        <td></td> <!-- Unit -->

                        <!-- Rate column -->
                        <td class="fw-bold text-end">Total</td>

                        <!-- Amount column -->
                        <td class="fw-bold text-end">
                            <span id="totalSum">0.00</span>
                            <input type="hidden" name="total" id="total_taxable_amounts" value="0">
                        </td>

                        <td></td> <!-- Action -->
                    </tr>
                    </tfoot>

                  </table>
               </div>

<div class="row mt-4 justify-content-end">

    {{-- SHIPPING INFO (ONLY FOR FINISHED) --}}
    @if($type == 'finished')
    <div class="col-md-4">
        <div class="card border shadow-sm p-3 bg-white h-100" style="border-radius: 12px;">
            <h6 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
    Shipping Info
</h6>

            <div class="row g-2">

                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Shipping Name</label>
                    <select name="shipping_name" id="shipping_name"
                            class="form-select form-select-sm select2-single">
                        <option value="">Select</option>
                        @foreach($party_list as $party)
                            <option value="{{ $party->id }}"
                                data-address="{{ $party->address }}"
                                data-pincode="{{ $party->pin_code }}"
                                data-gst="{{ $party->gstin }}"
                                data-pan="{{ $party->pan ?? '' }}"
                                data-state="{{ $party->state_code ?? '' }}"
                                data-other_address='@json($party->otherAddress ?? [])'>
                                {{ $party->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Shipping Address</label>
                    <select id="shipping_address_select"
                            class="form-select form-select-sm"
                            style="display:none;"></select>

                    <input type="text" name="shipping_address" id="shipping_address"
                           class="form-control form-control-sm">
                </div>

                <div class="col-6">
                    <label class="form-label small text-muted mb-1">Pincode</label>
                    <input type="text" name="shipping_pincode" id="shipping_pincode"
                           class="form-control form-control-sm">
                </div>

                <div class="col-6">
                    <label class="form-label small text-muted mb-1">GST</label>
                    <input type="text" name="shipping_gst" id="shipping_gst"
                           class="form-control form-control-sm">
                </div>

                <div class="col-12">
                    <label class="form-label small text-muted mb-1">PAN</label>
                    <input type="text" name="shipping_pan" id="shipping_pan"
                           class="form-control form-control-sm">
                    <input type="hidden" name="shipping_state" id="shipping_state">
                </div>

            </div>
        </div>
    </div>
    @endif


    {{-- TRANSPORT INFO (ALWAYS) --}}
    <div class="col-md-4">
        <div class="card border shadow-sm p-3 bg-white h-100" style="border-radius: 12px;">
            <h6 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
    Transport Info
</h6>

            <div class="row g-2">
                <div class="col-6">
                    <label class="form-label small text-muted mb-1">Vehicle No.</label>
                    <input type="text" name="vehicle_no" class="form-control form-control-sm"
                           value="{{ old('vehicle_no', $jobWork->vehicle_no ?? '') }}">
                </div>

                <div class="col-6">
                    <label class="form-label small text-muted mb-1">GR/RR No.</label>
                    <input type="text" name="gr_rr_no" class="form-control form-control-sm"
                           value="{{ old('gr_rr_no', $jobWork->gr_rr_no ?? '') }}">
                </div>

                <div class="col-12">
                    <label class="form-label small text-muted mb-1">Transport Name</label>
                    <input type="text" name="transport_name" class="form-control form-control-sm"
                           value="{{ old('transport_name', $jobWork->transport_name ?? '') }}">
                </div>

                <div class="col-6">
                    <label class="form-label small text-muted mb-1">Station</label>
                    <input type="text" name="station" class="form-control form-control-sm"
                           value="{{ old('station', $jobWork->station ?? '') }}">
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
<a href="{{ route('jobwork.out.raw') }}" class="btn btn-black">QUIT</a>
@else
<a href="{{ route('jobwork.out.finished') }}" class="btn btn-black">QUIT</a>
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
    let pageType = $('#page_type').val();
    let isVoucherSelected = false;
    let allItems = @json($items);
    let inVoucherItems = [];
$(document).ready(function () {
    $('.select2-single').select2({
        placeholder: "Select Item",
        allowClear: true,
        width: '100%'
    });
if(pageType === 'raw'){
    $('#inVoucherCol').hide();
    $('#job_work_in_id').val('');
}

if(pageType === 'finished'){
    $('#inVoucherCol').show();
}
$('#series_no').trigger('change');
});

$(document).on('change', '.item_id', function () {

    const rowId   = $(this).data('id');
    const selected = $(this).find(':selected');

    let partyId = $('#party_id').val();
    if (!partyId) {
        alert('Please select Party first.');
        $(this).val(null).trigger('change');
        $('#party_id').focus();
        return false;
    }

    const inDescId = selected.data('desc_id') || '';
    $('#in_desc_id_' + rowId).val(inDescId);

const unit = selected.data('unit') || '';

$('#unit_tr_' + rowId).val(unit);
$('#units_tr_' + rowId).val(unit);

    $('#quantity_tr_' + rowId).val('');
    $('#amount_tr_' + rowId).val('');

    calculateJobWorkTotal();
});


$(document).on('select2:opening', '.item_id', function (e) {
    let partyId = $('#party_id').val();

    if (!partyId) {
        e.preventDefault(); // stop dropdown
        alert('Please select Party first.');
        $('#party_id').select2('open'); // focus party
    }
});

function recalcRowAmount(rowId) {
    const qty   = parseFloat($('#quantity_tr_' + rowId).val()) || 0;
    const price = parseFloat($('#price_tr_' + rowId).val()) || 0;

    $('#amount_tr_' + rowId).val((qty * price).toFixed(2));
    calculateJobWorkTotal();
}

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

    const rowId     = $(this).data('id');
    const entered   = parseFloat($(this).val()) || 0;
    const maxQty    = parseFloat($(this).data('max')) || 0;

    if (maxQty > 0 && entered > maxQty) {
        alert(`Entered quantity (${entered}) cannot be greater than pending quantity (${maxQty}).`);
        $(this).val(maxQty); // auto-correct
    }

    recalcRowAmount(rowId);
});

$(document).on('input', '.price', function () {
    const rowId = $(this).data('id');
    recalcRowAmount(rowId);
});

function populateItemDropdown($select) {

    $select.empty().append('<option value="">Select Item</option>');

    if (isVoucherSelected) {

        if (inVoucherItems.length === 0) {
            return;
        }

        inVoucherItems.forEach(i => {
            $select.append(`
                <option value="${i.item_id}"
                    data-desc_id="${i.in_desc_id}"
                    data-unit="${i.unit}"
                    data-qty="${i.pending_qty}"
                    data-parameterized_stock_status="${i.parameterized_stock_status}">
                    ${i.item_name} (Qty: ${i.pending_qty})
                </option>
            `);
        });

        return;
    }

    allItems.forEach(i => {
        $select.append(`
            <option value="${i.id}"
                data-unit="${i.unit}"
                data-parameterized_stock_status="${i.parameterized_stock_status}">
                ${i.name}
            </option>
        `);
    });

    $select.trigger('change.select2');
}


let row_uid = 1; 

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

        </div>

        <input type="hidden" name="in_desc_id[]" id="in_desc_id_${row_uid}">

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

$('.select2-single').select2({ width: '100%' });

populateItemDropdown($('#item_id_' + row_uid));
    rebuildRowIcons();
    reindexSRN();
}

function removeItem(id) {
    $('#tr_' + id).remove();
    rebuildRowIcons();
    reindexSRN();
    calculateJobWorkTotal();
}

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

function reindexSRN() {
    let i = 1;
    $('.item-row').each(function () {
        $(this).find('.srn').text(i++);
    });
}

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


let voucherTimer = null;

$(document).on('input', '#voucher_display', function () {

    clearTimeout(voucherTimer);

    let voucher = $(this).val().trim();
    let series  = $('#series_no').val();

    if (!voucher || !series) return;

    voucherTimer = setTimeout(() => {

        $.ajax({
            url: "{{ route('jobwork.checkVoucher') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                voucher_no: voucher,
                series_no: series
            },
            success: function (res) {

                if (res.exists) {

                    alert('This voucher number already exists for this series. Please change it.');

                    $('#voucher_display').val('').focus();
                    $('#voucher_no').val('');
                    $('#voucher_prefix').val('');
                }
            },
            error: function () {
                console.error('Voucher check failed');
            }
        });

    }, 500); 
});

$('#party_id').on('change', function () {

    let partyId = $(this).val();

    if(pageType === 'raw'){
        $('#inVoucherCol').hide();
        return;
    }

    $('#inVoucherCol').show();

    $('#job_work_in_id')
        .empty()
        .append('<option value="">Select Voucher</option>');

    if (!partyId) {
        return;
    }

    $.get("{{ route('jobwork.getInVouchers') }}", {
        party_id: partyId
    }, function (res) {

        if (res.length > 0) {

            res.forEach(v => {
                $('#job_work_in_id').append(
                    `<option value="${v.id}">${v.voucher_no}</option>`
                );
            });

        }


    });

});
$('#job_work_in_id').on('change', function () {

    let voucherId = $(this).val();

    isVoucherSelected = !!voucherId; 

    // reset cached items
    inVoucherItems = [];

    // reset all item dropdowns
    $('.item_id').each(function () {
        $(this).empty().append('<option value="">Select Item</option>');
    });

    $('input[name="in_desc_id[]"]').val('');

    if (!voucherId) return;

    $.get("{{ route('jobwork.getInItems') }}", {
        job_work_in_id: voucherId
    }, function (items) {

        // store result (can be empty)
        inVoucherItems = items;

        // populate ALL rows
        $('.item_id').each(function () {
            populateItemDropdown($(this));
        });
    });
});

$('#job_work_in_id').on('change', function () {
    $('#job_work_in_id_hidden').val($(this).val() || '');
});
$(document).on('input', '.amount', function () {
    calculateJobWorkTotal();
});
$(document).on('input', '#voucher_display', function () {
    $('#voucher_no').val($(this).val());
});
$('#series_no').on('change', function () {

    let fullVoucher = $('option:selected', this).data('invoice_prefix');
    let numberPart  = $('option:selected', this).data('invoice_start_from');
    let manual      = $('option:selected', this).data('manual_enter_invoice_no');
    let matCenter   = $('option:selected', this).data('mat_center');
    let gst         = $('option:selected', this).data('gst_no');

    $('#merchant_gst').val(gst);
    $('#material_center').val(matCenter).trigger('change');

    if (manual == 0) {

        $('#voucher_display').val(fullVoucher).prop('readonly', true);

        $('#voucher_prefix').val(fullVoucher);
        $('#voucher_no').val(numberPart);

    } else {

        $('#voucher_display').val('').prop('readonly', false);

        $('#voucher_prefix').val('');
        $('#voucher_no').val('');

    }
});
$('#jobWorkForm').on('submit', function(e){

    $('#voucher_no').val($('#voucher_display').val());

    if(pageType === 'finished'){

        let voucher = $('#job_work_in_id').val();

        if(!voucher){
            alert('Job Work IN Voucher is required for Finished Goods.');
            $('#job_work_in_id').focus();
            e.preventDefault();
            return false;
        }
    }

    let shippingName = $('#shipping_name').val();
    let address      = $('#shipping_address').val();
    let addressDrop  = $('#shipping_address_select').val();
    let pincode      = $('#shipping_pincode').val();
    let gst          = $('#shipping_gst').val();
    let pan          = $('#shipping_pan').val();

    if(!shippingName){
        return true;
    }

    if($('#shipping_address_select').is(':visible') && !addressDrop){
        alert('Please select Shipping Address.');
        $('#shipping_address_select').focus();
        e.preventDefault();
        return false;
    }

    if(!address){
        alert('Shipping Address is required.');
        $('#shipping_address').focus();
        e.preventDefault();
        return false;
    }

    if(!pincode || !gst || !pan){
        alert('Please fill all Shipping details (Pincode, GST, PAN).');
        e.preventDefault();
        return false;
    }

});
$('#shipping_name').on('change', function () {

    let selected = $('option:selected', this);

    if (!$(this).val()) {

        $('#shipping_address').val('').prop('readonly', false).show();
        $('#shipping_address_select').hide().empty();

        $('#shipping_pincode').val('').prop('readonly', false);
        $('#shipping_gst').val('').prop('readonly', false);
        $('#shipping_pan').val('').prop('readonly', false);
        $('#shipping_state').val('');

        return;
    }

    let address = selected.data('address') || '';
    let pincode = selected.data('pincode') || '';
    let gst     = selected.data('gst') || '';
    let pan     = selected.data('pan') || '';
    let state   = selected.data('state') || '';
    let other   = selected.data('other_address') || [];

    $('#shipping_address').val('').show();
    $('#shipping_address_select').hide().empty();
    $('#shipping_pincode, #shipping_gst, #shipping_pan').val('');

    if (!other || other.length === 0) {

        $('#shipping_address').val(address).prop('readonly', true);
        $('#shipping_pincode').val(pincode).prop('readonly', true);
        $('#shipping_gst').val(gst).prop('readonly', true);
        $('#shipping_pan').val(pan).prop('readonly', true);
        $('#shipping_state').val(state);

    } 
    else {

        $('#shipping_address').hide();

        let dropdown = $('#shipping_address_select');
        dropdown.show();

        dropdown.append(`<option value="">Select Address</option>`);

        dropdown.append(`<option value="${address}" 
            data-pincode="${pincode}" 
            data-gst="${gst}" 
            data-pan="${pan}"
            data-state="${state}">
            ${address}
        </option>`);

        other.forEach(addr => {
            dropdown.append(`<option value="${addr.address}" 
                data-pincode="${addr.pincode}" 
                data-gst="${gst}" 
                data-pan="${pan}"
                data-state="${state}">
                ${addr.address}
            </option>`);
        });

        $('#shipping_pincode, #shipping_gst, #shipping_pan').prop('readonly', false);
        $('#shipping_state').val('');
    }
});

$('#shipping_address_select').on('change', function () {

    let selected = $('option:selected', this);

    $('#shipping_address').val(selected.val());
    $('#shipping_pincode').val(selected.data('pincode') || '');
    $('#shipping_gst').val(selected.data('gst') || '');
    $('#shipping_pan').val(selected.data('pan') || '');
    $('#shipping_state').val(selected.data('state') || '');
});
</script>
@endsection