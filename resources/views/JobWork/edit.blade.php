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
   .add_more_wrapper {
    background-color: #0d6efd; /* Bootstrap primary */
    border-radius: 50%;
    padding: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.add_more_wrapper path {
    fill: #ffffff !important;
}

.remove {
    color: #dc3545;
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
Edit Job Work Out – Raw Material
@else
Edit Job Work Out – Finished Goods
@endif
</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('jobwork.update', $jobWork->id) }}" id="jobWorkForm">
            @csrf
            <input type="hidden" name="page_type" id="page_type" value="{{ $type }}">
<input type="hidden" name="job_work_in_id" id="job_work_in_id_hidden" value="{{ $selectedInId }}">
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
        <input type="date" name="date" class="form-control" value="{{ old('date', $jobWork->date) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label font-14 font-heading">Voucher No. <span class="text-danger">*</span></label>
        @error('voucher_no')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
        <input type="text"
       class="form-control"
       id="voucher_display"
       style="text-align:right"
       value="{{  $jobWork->voucher_no_prefix }}">
        {{-- HIDDEN FIELDS --}}
        <input type="hidden" name="voucher_prefix" id="voucher_prefix" value="{{ $jobWork->voucher_no_prefix }}">
        <input type="hidden" name="voucher_no" id="voucher_no" value="{{ $jobWork->voucher_no }}">
        <input type="hidden" id="manual_enter_invoice_no">
        <input type="hidden" id="merchant_gst">
    </div>
</div>

{{-- 🎯 ROW 2: Party | Material Center --}}
<div class="row mb-4">
    <div class="col-md-4">
        <label for="party_id" class="form-label font-14 font-heading">Party</label>
        <select class="form-select select2-single" name="party_id" id="party_id" data-modal="accountModal">
            <option value="">Select Account</option>
            @foreach($party_list as $party)
                <option value="{{ $party->id }}" {{ old('party_id', $jobWork->party_id) == $party->id ? 'selected' : '' }}>
                    {{ $party->account_name }}
                </option>
            @endforeach
        </select>
        <p id="partyaddress" style="font-size: 9px;"></p>
        @error('party_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    {{-- 🎯 JOB WORK IN VOUCHER (EDIT OUT) --}}
    <div class="col-md-4">
        <label class="form-label font-14 font-heading">Job Work IN Voucher</label>

        <select class="form-select select2-single"
                name="job_work_in_id"
                id="job_work_in_id">
            <option value="">-- Without Job Work IN --</option>

            @foreach($inVouchers as $in)
                <option value="{{ $in->id }}"
                    {{ $selectedInId == $in->id ? 'selected' : '' }}>
                    {{ $in->voucher_no }}
                </option>
            @endforeach
        </select>
    </div>

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
         <th class="w-min-50 border-none bg-light-pink text-body" style="width: 1%;">S.No</th>
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
      @php 
         $add_more_count = 1; 
         $row_uid = count($jobWorkDescriptions);
      @endphp
      
      @forelse($jobWorkDescriptions as $row)
         <tr id="tr_{{ $add_more_count }}" class="item-row font-14 font-heading bg-white">
            <td class="w-min-50 text-center py-3" id="srn_{{ $add_more_count }}">{{ $add_more_count }}</td>
            
            <!-- 🎯 ITEM COLUMN (1st column) -->
            <td class="py-2 align-middle">
               <div class="d-flex align-items-center gap-2 h-100">
                  <select class="form-control item_id select2-single flex-grow-1" 
                          name="goods_discription[]" 
                          id="item_id_{{ $add_more_count }}" 
                          data-id="{{ $add_more_count }}"
                          data-prev-item="{{ $row->item_id }}">
                     <option value="">Select Item</option>
                     @foreach($items as $item_list)
                        <option value="{{ $item_list->id }}" 
    {{ $row->item_id == $item_list->id ? 'selected' : '' }}
    data-unit="{{ $item_list->unit }}"
    data-parameterized_stock_status="{{ $item_list->parameterized_stock_status }}"
    data-desc_id="{{ $row->jw_in_description_id }}"
    data-percent="{{ $item_list->gst_rate }}"
    data-val="{{ $item_list->unit }}"
    data-id="{{ $item_list->id }}"
    data-itemid="{{ $item_list->id }}"
    data-config_status="{{ $item_list->config_status }}"
    data-group_id="{{ $item_list->group_id }}">

                           {{ $item_list->name }}
                        </option>
                     @endforeach
                  </select>
               </div>
            </td>
            
            <!-- 🎯 DESCRIPTION COLUMN (2nd column) - NEW! -->
            <td class="py-2 align-middle">
               <div class="position-relative">
                  <textarea class="form-control form-control-sm item_description h-100" 
                            name="item_description[]" 
                            id="item_description_{{ $add_more_count }}" 
                            data-id="{{ $add_more_count }}" 
                            rows="2" 
                            placeholder="Item description (optional)"
                            style="resize: none; font-size: 13px; line-height: 1.3; padding: 6px 8px; border-radius: 6px; border: 1px solid #d1d5db;">{{ $row->item_description ?? '' }}</textarea>
                  <div class="position-absolute top-0 end-0 p-1">
                     <small class="text-muted">Optional</small>
                  </div>
               </div>
            </td>
            
            <!-- Qty -->
            <td class="text-right pr-3 py-2">
               <input type="number" class="quantity form-control form-control-sm" 
                      id="quantity_tr_{{ $add_more_count }}" 
                      name="qty[]" 
                      placeholder="0.00" 
                      style="text-align:right; height: 34px;" 
                      data-id="{{ $add_more_count }}" 
                      value="{{ $row->qty }}" />
            </td>
            
            <!-- Unit -->
            <td class="text-center py-2">
               <input type="text" class="unit form-control form-control-sm" 
                      id="unit_tr_{{ $add_more_count }}" 
                      readonly 
                      style="text-align:center; height: 34px;" 
                      data-id="{{ $add_more_count }}"
                      value="{{ $row->unit }}"/>
               <input type="hidden" class="units" name="units[]" id="units_tr_{{ $add_more_count }}" value="{{ $row->unit }}"/>
            </td>
            
            <!-- Price -->
            <td class="text-right pr-3 py-2">
               <input type="number" class="price form-control form-control-sm" 
                      id="price_tr_{{ $add_more_count }}" 
                      name="price[]" 
                      placeholder="0.00" 
                      style="text-align:right; height: 34px;" 
                      data-id="{{ $add_more_count }}"
                      value="{{ $row->price }}"/>
            </td>
            
            <!-- Amount -->
            <td class="text-right pr-3 py-2">
               <input type="number" class="amount form-control form-control-sm" 
                      id="amount_tr_{{ $add_more_count }}" 
                      name="amount[]" 
                      placeholder="0.00" 
                      style="text-align:right; height: 34px;" 
                      data-id="{{ $add_more_count }}"
                      value="{{ $row->amount }}"/>
            </td>
            
            <!-- Action -->
            <td class="action-cell text-center py-2">
               <svg xmlns="http://www.w3.org/2000/svg" 
                    data-id="{{ $add_more_count }}"
                    class="bg-primary rounded-circle add_more_wrapper mx-auto d-block" 
                    width="24" height="24" 
                    viewBox="0 0 24 24" 
                    fill="none" 
                    style="cursor: pointer;" 
                    tabindex="0" role="button">
                  <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
               </svg>
            </td>
            <input type="hidden" name="desc_id[]" value="{{ $row->id }}">
<input type="hidden" name="in_desc_id[]" id="in_desc_id_{{ $add_more_count }}" value="{{ $row->jw_in_description_id }}">
         </tr>
         @php $add_more_count++; @endphp
      @empty
         <!-- Empty row (same as your existing code but with Description column) -->
         <tr id="tr_1" class="item-row font-14 font-heading bg-white">
            <!-- Same structure as above but empty values -->
            <td class="w-min-50 text-center py-3" id="srn_1">1</td>
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
                  <button type="button" class="btn btn-outline-secondary btn-sm p-1 editItemDetailsBtn d-none" 
                          data-row="tr_1" title="Configure item">
                     <i class="fas fa-cog text-muted"></i>
                  </button>
               </div>
            </td>
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
            <td class="action-cell text-center py-2"></td>
         </tr>
      @endforelse
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
            <span id="totalSum">{{ array_sum(array_column($jobWorkDescriptions->toArray(), 'amount')) ?? 0 }}</span>
            <input type="hidden" name="total" id="total_taxable_amounts" value="{{ array_sum(array_column($jobWorkDescriptions->toArray(), 'amount')) ?? 0 }}">
         </td>
         <td></td>
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
                                data-other_address='@json($party->otherAddress ?? [])'
                                {{ old('shipping_name', $jobWork->shipping_name ?? '') == $party->id ? 'selected' : '' }}>
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
                           class="form-control form-control-sm"
                           value="{{ old('shipping_address', $jobWork->shipping_address ?? '') }}">
                </div>

                <div class="col-6">
                    <label class="form-label small text-muted mb-1">Pincode</label>
                    <input type="text" name="shipping_pincode" id="shipping_pincode"
                           class="form-control form-control-sm"
                           value="{{ old('shipping_pincode', $jobWork->shipping_pincode ?? '') }}">
                </div>

                <div class="col-6">
                    <label class="form-label small text-muted mb-1">GST</label>
                    <input type="text" name="shipping_gst" id="shipping_gst"
                           class="form-control form-control-sm"
                           value="{{ old('shipping_gst', $jobWork->shipping_gst ?? '') }}">
                </div>

                <div class="col-12">
                    <label class="form-label small text-muted mb-1">PAN</label>
                    <input type="text" name="shipping_pan" id="shipping_pan"
                           class="form-control form-control-sm"
                           value="{{ old('shipping_pan', $jobWork->shipping_pan ?? '') }}">
                    <input type="hidden" name="shipping_state" id="shipping_state"
                           value="{{ old('shipping_state', $jobWork->shipping_state ?? '') }}">
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
    <button type="submit" class="btn btn-xs-primary">UPDATE</button>
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
    let CURRENT_IN_ITEMS = [];
$(document).ready(function () {
    $('.select2-single').select2({
        placeholder: "Select Item",
        allowClear: true,
        width: '100%'
    });
    let pageType = $('#page_type').val();

if(pageType === 'raw'){
    $('#job_work_in_id').closest('.col-md-4').hide();
}
    
    // Initialize totals and units
    calculateJobWorkTotal();
    $('.item_id').each(function() {
        let rowId = $(this).data('id');
        let unit = $(this).find(':selected').data('unit');
        if (unit) {
            $('#unit_tr_' + rowId).val(unit);
            $('#units_tr_' + rowId).val(unit);
        }
        
    });
// 🔥 EDIT MODE: initialize pending qty limits on load
$('.item_id').each(function () {

    const rowId = $(this).data('id');
    const selected = $(this).find(':selected');
    const pendingQty = parseFloat(selected.data('qty')) || 0;

    if (pendingQty > 0) {
        $('#quantity_tr_' + rowId)
            .data('max', pendingQty)
            .attr('max', pendingQty);
    }
});

    rebuildRowIcons();
    reindexSRN();
    $('#shipping_name').trigger('change');

    let selectedOption = $('#series_no option:selected');

    let manual = selectedOption.data('manual_enter_invoice_no');

    if (manual == 0) {
        $('#voucher_display').prop('readonly', true);
    } else {
        $('#voucher_display').prop('readonly', false);
    }
});
/* =========================================
   JOB WORK IN → FILTER ITEMS (EDIT)
========================================= */
function populateItemsForAllRows(items) {

    $('.item_id').each(function () {

        let $select = $(this);

        // 🔒 EXISTING VALUES (EDIT SAFE)
        let selectedVal   = $select.val();
        let selectedText  = $select.find('option:selected').text();
        let selectedUnit  = $select.find('option:selected').data('unit');
        let selectedParam = $select.find('option:selected').data('parameterized_stock_status');
        let selectedDesc  = $select.find('option:selected').data('desc_id') || '';

        // 🔥 RESET DROPDOWN
        $select.empty().append('<option value="">Select Item</option>');

        // 🔒 RE-ADD EXISTING SELECTED ITEM (EVEN IF COMPLETED)
        if (selectedVal) {
            $select.append(`
                <option value="${selectedVal}"
                        data-unit="${selectedUnit}"
                        data-parameterized_stock_status="${selectedParam}"
                        data-desc_id="${selectedDesc}"
                        selected>
                    ${selectedText}
                </option>
            `);
        }

        // 🔥 ADD ONLY PENDING IN ITEMS
        items.forEach(i => {
            if (i.item_id == selectedVal) return;

            $select.append(`
                <option value="${i.item_id}"
                        data-unit="${i.unit}"
                        data-desc_id="${i.in_desc_id}"
                        data-qty="${i.pending_qty}"
                        data-parameterized_stock_status="${i.parameterized_stock_status}">
                    ${i.item_name} (Qty: ${i.pending_qty})
                </option>
            `);
        });

        $select.trigger('change.select2');
    });

    // 🔥 VERY IMPORTANT: set hidden in_desc_id on load
    $('.item_id').each(function () {
        let rowId   = $(this).data('id');
        let inDesc  = $(this).find(':selected').data('desc_id') || '';
        $('#in_desc_id_' + rowId).val(inDesc);
    });
}


/* ON CHANGE JOB WORK IN */
$('#job_work_in_id').on('change', function () {

    let inId = $(this).val();

    // 🔁 reset if no IN selected
if (!inId) {
    HAS_JOB_WORK_IN = false;

    // Restore ALL items (Blade list)
    $('.item_id').each(function () {
        let selectedVal = $(this).val();

        $(this).empty().append(`<option value="">Select Item</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}"
                    data-unit="{{ $item->unit }}"
                    data-parameterized_stock_status="{{ $item->parameterized_stock_status }}">
                    {{ $item->name }}
                </option>
            @endforeach
        `);

        if (selectedVal) $(this).val(selectedVal);
        $(this).trigger('change.select2');
    });

    return;
}


    // 🔥 fetch IN items
    $.get("{{ route('jobwork.getInItems') }}", {
    job_work_in_id: inId
}, function (items) {
    populateItemsForAllRows(items);
});

});

// ===== ALL JS FROM CREATE - NO CHANGES NEEDED =====
$(document).on('change', '.item_id', function () {

    const rowId = $(this).data('id');
    const selected = $(this).find(':selected');

    const unit = selected.data('unit') || '';
    const inDescId = selected.data('desc_id') || '';
    const pendingQty = parseFloat(selected.data('qty')) || 0;

    $('#in_desc_id_' + rowId).val(inDescId);
    $('#unit_tr_' + rowId).val(unit);
    $('#units_tr_' + rowId).val(unit);

    // 🔥 STORE MAX PENDING QTY
    if (pendingQty > 0) {
        $('#quantity_tr_' + rowId)
            .data('max', pendingQty)
            .attr('max', pendingQty);
    } else {
        $('#quantity_tr_' + rowId)
            .removeData('max')
            .removeAttr('max');
    }

    $('#quantity_tr_' + rowId).val('');
    $('#amount_tr_' + rowId).val('');

    calculateJobWorkTotal();
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

// Update input handler to include item_description
$(document).on('input', '.quantity', function () {

    const rowId   = $(this).data('id');
    const entered = parseFloat($(this).val()) || 0;
    const maxQty  = parseFloat($(this).data('max')) || 0;

    // 🔥 BLOCK excess quantity
    if (maxQty > 0 && entered > maxQty) {
        alert(`Quantity cannot exceed pending qty (${maxQty}).`);
        $(this).val(maxQty);
    }

    recalcRowAmount(rowId);
});

$(document).on('input', '.price', function () {
    const rowId = $(this).data('id');
    recalcRowAmount(rowId);
});

let row_uid = {{ count($jobWorkDescriptions) }}; 

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
    <td class="w-min-50 text-center py-3 srn"></td>

    <!-- ITEM -->
    <td class="py-2 align-middle">
        <div class="d-flex align-items-center gap-2 h-100">
            <select class="form-control item_id select2-single flex-grow-1"
                name="goods_discription[]"
                id="item_id_${row_uid}"
                data-id="${row_uid}"
                data-prev-item=""
                data-modal="itemModal">
                <option value="">Select Item</option>
                @foreach($items as $item_list)
                    <option value="{{ $item_list->id }}"
                        data-unit="{{ $item_list->unit }}"
                        data-parameterized_stock_status="{{ $item_list->parameterized_stock_status }}"
                        data-percent="{{ $item_list->gst_rate }}"
                        data-val="{{ $item_list->unit }}"
                        data-id="{{ $item_list->id }}"
                        data-itemid="{{ $item_list->id }}"
                        data-config_status="{{ $item_list->config_status }}"
                        data-group_id="{{ $item_list->group_id }}">
                        {{ $item_list->name }}
                    </option>
                @endforeach
            </select>

            <button type="button"
                class="btn btn-outline-secondary btn-sm p-1 editItemDetailsBtn d-none"
                data-row="tr_${row_uid}"
                title="Configure item">
                <i class="fas fa-cog text-muted"></i>
            </button>
        </div>
    </td>

    <!-- DESCRIPTION -->
    <td class="py-2 align-middle">
        <div class="position-relative">
            <textarea class="form-control form-control-sm item_description h-100"
                name="item_description[]"
                id="item_description_${row_uid}"
                data-id="${row_uid}"
                rows="2"
                placeholder="Item description (optional)"
                style="resize:none;font-size:13px;line-height:1.3;padding:6px 8px;border-radius:6px;border:1px solid #d1d5db;"></textarea>
            <div class="position-absolute top-0 end-0 p-1">
                <small class="text-muted">Optional</small>
            </div>
        </div>
    </td>

    <!-- QTY -->
    <td class="text-right pr-3 py-2">
        <input type="number"
            class="quantity form-control form-control-sm"
            id="quantity_tr_${row_uid}"
            name="qty[]"
            data-id="${row_uid}"
            placeholder="0.00"
            style="text-align:right;height:34px;">
    </td>

    <!-- UNIT -->
    <td class="text-center py-2">
        <input type="text"
            class="unit form-control form-control-sm"
            id="unit_tr_${row_uid}"
            readonly
            style="text-align:center;height:34px;">
        <input type="hidden"
            class="units"
            name="units[]"
            id="units_tr_${row_uid}">
    </td>

    <!-- RATE -->
    <td class="text-right pr-3 py-2">
        <input type="number"
            class="price form-control form-control-sm"
            id="price_tr_${row_uid}"
            name="price[]"
            data-id="${row_uid}"
            placeholder="0.00"
            style="text-align:right;height:34px;">
    </td>

    <!-- AMOUNT -->
    <td class="text-right pr-3 py-2">
        <input type="number"
            class="amount form-control form-control-sm"
            id="amount_tr_${row_uid}"
            name="amount[]"
            data-id="${row_uid}"
            placeholder="0.00"
            style="text-align:right;height:34px;">
    </td>

    <!-- ACTION -->
    <td class="action-cell text-center py-2"></td>
    <input type="hidden" name="desc_id[]" value="">
<input type="hidden"
       name="in_desc_id[]"
       id="in_desc_id_${row_uid}"
       value="">

</tr>`;


    $('#example11 tbody').append(newRow);
let $newSelect = $('#item_id_' + row_uid);

// ✅ If Job Work IN is selected → filter items
let currentInId = $('#job_work_in_id').val();

if (currentInId && CURRENT_IN_ITEMS.length > 0) {

    // Build filtered dropdown
    $newSelect.empty().append('<option value="">Select Item</option>');

    CURRENT_IN_ITEMS.forEach(i => {
        $newSelect.append(`
            <option value="${i.item_id}"
                    data-unit="${i.unit}"
                    data-desc_id="${i.in_desc_id}"
                    data-qty="${i.pending_qty}"
                    data-parameterized_stock_status="${i.parameterized_stock_status}">
                ${i.item_name} (Qty: ${i.pending_qty})
            </option>
        `);
    });

} 
// ❌ ELSE: keep default Blade items (already present)

$newSelect.val(null).trigger('change.select2');

    $('.select2-single').select2({ width: '100%' });
    rebuildRowIcons();
    reindexSRN();
}

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


function refreshAllSizeSelects() {

    $(".item_size").each(function () {
        let $select = $(this);

        // enable everything first
        $select.find("option").prop("disabled", false);

        // disable only currently locked sizes
        $select.find("option").each(function () {
            let val = $(this).val();
            if (!val) return;

            if (globalSelectedSizes.has(val) && $select.val() !== val) {
                $(this).prop("disabled", true);
            }
        });
    });

    // force Select2 UI refresh
    $(".item_size").trigger("change.select2");
}

/* =========================================
   🔥 EDIT LOAD FIX (MOST IMPORTANT)
========================================= */
$(document).ready(function () {

    let preselectedInId = $('#job_work_in_id').val();

    // ✅ If Job Work IN Voucher already selected on load
    if (preselectedInId) {
        $.get("{{ route('jobwork.getInItems') }}", {
            job_work_in_id: preselectedInId
        }, function (items) {
            CURRENT_IN_ITEMS = items; 
            populateItemsForAllRows(items);
        });
    }
    // 🔥 FORCE LOAD DB VALUE (EDIT FIX)
    $('#voucher_display').val("{{ $jobWork->voucher_no_prefix }}");
    $('#voucher_no').val("{{ $jobWork->voucher_no }}");
    $('#voucher_prefix').val("{{ $jobWork->voucher_no_prefix }}");

    // =============================
// 🔥 SHIPPING EDIT FIX (CORRECT)
// =============================
let existingAddress = "{{ $jobWork->shipping_address }}";

// trigger change first
$('#shipping_name').trigger('change');

// wait for dropdown build
setTimeout(function(){

    if($('#shipping_address_select').is(':visible')){

        let found = false;

        $('#shipping_address_select option').each(function(){

            if($(this).val().trim() === existingAddress.trim()){
                $(this).prop('selected', true);
                found = true;
            }

        });

        if(found){
            $('#shipping_address_select').trigger('change');
        }

    }

}, 300);
});
// 🔥 DUPLICATE CHECK (EDIT - SERIES + VOUCHER)
let voucherTimer = null;

$(document).on('input', '#voucher_display', function () {

    clearTimeout(voucherTimer);

    let voucher = $(this).val().trim();
    let series  = $('#series_no').val();
    let currentId = "{{ $jobWork->id }}"; // 🔥 VERY IMPORTANT (ignore self)

    if (!voucher || !series) return;

    voucherTimer = setTimeout(() => {

        $.ajax({
            url: "{{ route('jobwork.checkVoucher') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                voucher_no: voucher,
                series_no: series,
                edit_id: currentId // 🔥 send current id
            },
            success: function (res) {

                if (res.exists) {

                    alert('This voucher number already exists for this series. Please change it.');

                    $('#voucher_display').val('').focus();
                    $('#voucher_no').val('');
                    $('#voucher_prefix').val('');
                }
            }
        });

    }, 500);
});
// 🔥 MANUAL AMOUNT EDIT → RECALCULATE TOTAL
$(document).on('input', '.amount', function () {
    calculateJobWorkTotal();
});
$('#series_no').on('change', function () {

    let fullVoucher = $('option:selected', this).data('invoice_prefix');
    let numberPart  = $('option:selected', this).data('invoice_start_from');
    let manual      = $('option:selected', this).data('manual_enter_invoice_no');
    let matCenter   = $('option:selected', this).data('mat_center');
    let gst         = $('option:selected', this).data('gst_no');

    $('#merchant_gst').val(gst);
    $('#material_center').val(matCenter).trigger('change');

    let initialSeries = "{{ $jobWork->series_no }}";

    // ✅ SAME SERIES → KEEP EXISTING
    if (initialSeries == $(this).val()) {

        $('#voucher_display').val("{{ $jobWork->voucher_no_prefix }}");

        if (manual == 0) {
            $('#voucher_display').prop('readonly', true);
        } else {
            $('#voucher_display').prop('readonly', false);
        }

        $('#voucher_prefix').val("{{ $jobWork->voucher_no_prefix }}");
        $('#voucher_no').val("{{ $jobWork->voucher_no }}");

        return;
    }

    // 🔥 DIFFERENT SERIES → SAME AS CREATE
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


// 🔥 FINAL SAFETY BEFORE SUBMIT (CLEAN)
$('#jobWorkForm').on('submit', function(e){

    // =========================
    // 🔥 VOUCHER SYNC
    // =========================
    let val = $('#voucher_display').val();
    $('#voucher_no').val(val);
    $('#voucher_prefix').val(val);

    // =========================
    // 🔥 FINISHED VALIDATION
    // =========================
    let pageType = $('#page_type').val();

    if(pageType === 'finished'){

        let voucher = $('#job_work_in_id').val();

        if(!voucher){
            alert('Job Work IN Voucher is required for Finished Goods.');
            $('#job_work_in_id').focus();
            e.preventDefault();
            return false;
        }
    }

    // =========================
    // 🔥 SHIPPING VALIDATION
    // =========================
    let shippingName = $('#shipping_name').val();
    let address      = $('#shipping_address').val();
    let pincode      = $('#shipping_pincode').val();
    let gst          = $('#shipping_gst').val();
    let pan          = $('#shipping_pan').val();

    // ✅ OPTIONAL (same as ADD)
    if(!shippingName){
        return true;
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
// Sync hidden job_work_in_id
$('#job_work_in_id').on('change', function () {
    $('#job_work_in_id_hidden').val($(this).val() || '');
});
/* =========================================
   PARTY CHANGE → RESET JOB WORK IN VOUCHER
========================================= */

$('#party_id').on('change', function () {

    let partyId = $(this).val();
    let pageType = $('#page_type').val();

    // only needed for Finished Goods
    if(pageType !== 'finished'){
        return;
    }

    // clear current voucher
    $('#job_work_in_id')
        .val('')
        .empty()
        .append('<option value="">-- Without Job Work IN --</option>')
        .trigger('change');

    $('#job_work_in_id_hidden').val('');

    // reset item rows
    $('.item_id').each(function(){
        $(this).val('').trigger('change');
    });

    if(!partyId){
        return;
    }

    // load vouchers for selected party
    $.get("{{ route('jobwork.getInVouchers') }}", {
        party_id: partyId
    }, function(res){

        if(res.length > 0){

            res.forEach(v => {
                $('#job_work_in_id').append(
                    `<option value="${v.id}">${v.voucher_no}</option>`
                );
            });

        }

        $('#job_work_in_id').trigger('change.select2');

    });

});
$('#shipping_name').on('change', function () {

    let selected = $('option:selected', this);

    let address = selected.data('address') || '';
    let pincode = selected.data('pincode') || '';
    let gst     = selected.data('gst') || '';
    let pan     = selected.data('pan') || '';
    let state   = selected.data('state') || '';
    let other = selected.data('other_address') || [];

    // RESET
    $('#shipping_address').val('').show();
    $('#shipping_address_select').hide().empty();
    $('#shipping_pincode, #shipping_gst, #shipping_pan').val('');

    // 🟢 SINGLE ADDRESS
   if (!other || other.length <= 0) {

        $('#shipping_address').val(address).prop('readonly', true);
        $('#shipping_pincode').val(pincode).prop('readonly', true);
        $('#shipping_gst').val(gst).prop('readonly', true);
        $('#shipping_pan').val(pan).prop('readonly', true);
        $('#shipping_state').val(state);

    } 
    // 🟡 MULTIPLE ADDRESS
    else {

        $('#shipping_address').hide();

        let dropdown = $('#shipping_address_select');
        dropdown.show();

        dropdown.append(`<option value="">Select Address</option>`);

        // 🔥 GET STATE
        let state = selected.data('state') || '';

        // MAIN
        dropdown.append(`<option value="${address}" 
            data-pincode="${pincode}" 
            data-gst="${gst}" 
            data-pan="${pan}"
            data-state="${state}">
            ${address}
        </option>`);

        // OTHERS
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

        // 🔥 RESET STATE UNTIL ADDRESS SELECTED
        $('#shipping_state').val('');
    }
});


// ADDRESS CHANGE
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
