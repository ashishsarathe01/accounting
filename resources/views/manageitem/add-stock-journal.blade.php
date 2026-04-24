@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style type="text/css">
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
   .desc-cell {
      gap: 6px;
   }
   .desc-cell .select2-container {
      flex: 1;
   }
   .configure-size-btn {
      width: 32px;
      height: 32px;
      padding: 0;
   }
   .select2-container {
      width: 100% !important;
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
</style>
<div class="list-of-view-company ">
   <?php
      $items_list = '<option value="">Select Item</option>';
      foreach($items as $item){
         $items_list.='<option value="'.$item->id.'" data-unit_id="'.$item->u_name.'"  data-unit_name="'.$item->unit.'">'.$item->name.'</option>';
      }
   ?>
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Stock Journal</h5>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('save-stock-journal') }}">
               @csrf
               <div class="row">
                   <input type="hidden" name="part_life_entry_id" value="{{ request('part_life_entry_id') }}">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Series</label>
                     <select id="series_no" name="series_no" class="form-select" required >
                        <option value="">Select</option>
                        @foreach($series_list as $key => $value)
                           <option value="{{$value->series}}" data-invoice_start_from="{{$value->invoice_start_from}}" data-invoice_prefix="{{$value->invoice_prefix}}" data-manual_enter_invoice_no="{{ $value->manual_enter_invoice_no}}" data-duplicate_voucher="{{$value->duplicate_voucher}}" data-blank_voucher="{{$value->blank_voucher}}" @if(count($series_list)==1) selected  @endif>{{ $value->series }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required value="{{$date}}" min="{{ $fy_start_date }}" max="{{ $fy_end_date }}">
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Stock Journal No.</label>
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" readonly style="text-align: right;" placeholder="Voucher No">
                     <input type="hidden" class="form-control" id="voucher_no" name="voucher_no">
                     <input type="hidden" class="form-control" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Material Center</label>
                     <select class="form-select" name="material_center" id="material_center" required>
                        <option value="">Select Material Center</option>
                        @foreach($series_list as $key => $value)
                           <option value="{{ $value->mat_center }}" @if(count($series_list)==1) selected  @endif>{{ $value->mat_center }}</option>
                        @endforeach
                     </select> 
                  </div>
                  
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">

                     <thead>
                        <tr>
                              <th colspan="7" style="text-align:center">ITEMS CONSUMED</th>
                        </tr>
                        <tr class="font-12 text-body bg-light-pink">
                              <th class="w-min-50">S No.</th>
                              <th style="width:36%">DESCRIPTION OF GOODS</th>
                              <th style="text-align:right;padding-right:24px;">QUANTITY</th>
                              <th style="text-align:center;">UNIT</th>
                              <th style="text-align:right;padding-right:24px;">Price</th>
                              <th style="text-align:right;">Amount</th>
                              <th></th>
                        </tr>
                     </thead>

                     <tbody>    
                          <tr id="tr_1" class="font-14 font-heading bg-white">
                           <td class="w-min-50" id="consume_srn_1">1</td>
                           <td class="w-min-50">
                              <div class="d-flex align-items-center">
                                 <select class="form-control consume_item select2-single" name="consume_item[]" data-id="1" id="consume_item_1">
                                       <option value="">Select Item</option>      
                                       @foreach($items as $item)
                                          <option value="{{$item->id}}" data-unit_id="{{$item->u_name}}" data-unit_name="{{$item->unit}}">
                                             {{$item->name}}
                                          </option>
                                       @endforeach
                                 </select>

                                 <!-- ⚙️ Configure button next to select with your preferred style -->
                                 <button type="button" class="btn btn-outline-secondary p-1 px-2 configure-size-btn" data-id="1" title="Configure Item ⚙️">⚙️</button>

                                 <input type="hidden" name="item_size_info[]" id="item_size_info_1">
                              </div>
                           </td>

                           <td class="">
                              <input type="number" name="consume_weight[]" class="form-control consume_weight" data-id="1" id="consume_weight_1" placeholder="QUANTITY" style="text-align: right;" readonly>
                           </td>
                           <td class="w-min-50">                              
                                 <input type="text" class="w-100 form-control consume_unit" id="consume_unit_tr_1" readonly style="text-align:center;" data-id="1" name="consume_unit_name[]"/>
                                 <input type="hidden" class="consume_units w-100" name="consume_units[]" id="consume_units_tr_1" />
                           </td>
                           <td class="">
                              <input type="number" name="consume_price[]" class="form-control consume_price" data-id="1" id="consume_price_1" placeholder="Price" style="text-align: right;">
                           </td>
                           <td class="">
                              <input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="1" id="consume_amount_1" placeholder="Amount" readonly style="text-align: right;">
                           </td>
                           <td class="">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                 <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                 </svg>
                           </td>
                        </tr>
                        <tr id="consum_total"></tr>
                     </tbody>                     
                     <div class="total" >
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold"></td>
                           <td class="fw-bold">Total</td>
                           <td class="fw-bold" id="consume_weight_total" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold" id="consume_amount_total" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>
                        </tr>
                     </div>
                  </table>
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr><th colspan="7" style="text-align:center">ITEMS GENERATED</th></tr>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="width: 36%;">DESCRIPTION OF GOODS</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">QUANTITY</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: center;">UNIT</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Amount</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-50" id="generated_srn_1">1</td>
                           <td class="w-min-50">
                              <div class="d-flex align-items-center">
                                 <select class="form-control generated_item select2-single" name="generated_item[]" data-id="1" id="generated_item_1">
                                       <option value="">Select Item</option>      
                                       @foreach($items as $item)
                                          <option value="{{$item->id}}" data-unit_id="{{$item->u_name}}" data-unit_name="{{$item->unit}}">
                                             {{$item->name}}
                                          </option>
                                       @endforeach
                                 </select>

                                 <!-- ⚙️ Configure button -->
                                 <button type="button" class="btn btn-outline-secondary p-1 px-2 generated-configure-btn" data-id="1" title="Configure Item ⚙️">⚙️</button>

                                 <input type="hidden" name="generated_size_info[]" id="generated_size_info_1" data-id="1">
                              </div>
                              </td>

                           <td class="">
                              <input type="number" name="generated_weight[]" class="form-control generated_weight" data-id="1" id="generated_weight_1" placeholder="QUANTITY" style="text-align: right;" readonly>
                           </td>
                           <td class="w-min-50">                              
                                 <input type="text" class="w-100 form-control generated_unit" id="generated_unit_tr_1" readonly style="text-align:center;" data-id="1" name="generated_unit_name[]"/>
                                 <input type="hidden" class="generated_units w-100" name="generated_units[]" id="generated_units_tr_1" />
                           </td>
                           <td class="">
                              <input type="number" name="generated_price[]" class="form-control generated_price" data-id="1" id="generated_price_1" placeholder="Price" style="text-align: right;">
                           </td>
                           <td class="">
                              <input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="1" id="generated_amount_1" placeholder="Amount" readonly style="text-align: right;">
                           </td>                           
                           <td>
                              <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more1" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                              <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                              </svg>
                           </td>
                        </tr>
                        <tr id="generated_total"></tr>
                     </tbody>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold"></td>
                           <td class="fw-bold">Total</td>
                           <td class="fw-bold" id="consume_weight_total1" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold" id="consume_amount_total1" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>
                        </tr>
                     </div>
                  </table>
               </div>
               <div class="mb-3 col-md-12">
                     <input type="text" id="narration" class="form-control" name="narration" placeholder="Enter Narration">
                  </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <a href="{{ route('stock-journal') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     <input type="submit" value="SUBMIT" class="btn btn-xs-primary savebtn">
                  </div>
               </div>
            </form>
         </div>
         <div class="col-lg-1 d-flex justify-content-center">
            <div class="shortcut-key ">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 ">F1
                  <span class="ps-1 fw-normal text-body">Help</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F1</span>
                  <span class="ps-1 fw-normal text-body">Add Account</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F2</span>
                  <span class="ps-1 fw-normal text-body">Add Item</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  F3
                  <span class="ps-1 fw-normal text-body">Add Master</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F3</span>
                  <span class="ps-1 fw-normal text-body">Add Voucher</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F5</span>
                  <span class="ps-1 fw-normal text-body">Add Payment</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F6</span>
                  <span class="ps-1 fw-normal text-body">Add Receipt</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F7</span>
                  <span class="ps-1 fw-normal text-body">Add Journal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F8</span>
                  <span class="ps-1 fw-normal text-body">Add Sales</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 ">
                  <span class="border-bottom-black">F9</span>
                  <span class="ps-1 fw-normal text-body">Add Purchase</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">B</span>
                  <span class="ps-1 fw-normal text-body">Balance Sheet</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">T</span>
                  <span class="ps-1 fw-normal text-body">Trial Balance</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">S</span>
                  <span class="ps-1 fw-normal text-body">Stock Status</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">L</span>
                  <span class="ps-1 fw-normal text-body">Acc. Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">I</span>
                  <span class="ps-1 fw-normal text-body">Item Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">D</span>
                  <span class="ps-1 fw-normal text-body">Item Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">G</span>
                  <span class="ps-1 fw-normal text-body">GST Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">U</span>
                  <span class="ps-1 fw-normal text-body">Switch User</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F</span>
                  <span class="ps-1 fw-normal text-body">Configuration</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">K</span>
                  <span class="ps-1 fw-normal text-body">Lock Program</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="ps-1 fw-normal text-body">Training Videos</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="ps-1 fw-normal text-body">GST Portal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 ">
                  Search Menu
               </button>
            </div>
         </div>
      </div>
   </section>
</div>
<!-- Modal -->
<div class="modal fade" id="sizeModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-3">
         <div class="modal-header">
            <h5 class="modal-title">Size List</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            <div class="table-responsive">
               <table class="table table-bordered table-striped mb-0 item_size_table">
                  <thead>
                     <tr>
                        <th style="width: 42%;">Size</th>
                        <th>Weight</th>
                        <th>Reel No.</th>
                     </tr>
                  </thead>
                  <tbody>

                  </tbody>
                  <div class="mt-2 text-end">
                     <strong>Total Weight: <span id="total_weight">0</span></strong>
                  </div>
              </table>
            </div>
         </div>
         <div class="modal-footer">
            <input type="hidden" id="item_size_row_id">
            <button class="btn btn-info item_size_btn">Submit</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>

<!-- Modal For ITEMS GENERATED -->
<div class="modal fade" id="generatedSizeModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-3">
         <div class="modal-header">
            <h5 class="modal-title">Generated Item - Size List</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <div class="modal-body">

            <div class="table-responsive">
               <table class="table table-bordered table-striped mb-0 generated_item_size_table">
                 <thead>
                        <tr>
                           <th style="width:35%;">Size</th>
                           <th>Reel No.</th>
                           <th>Weight</th>
                           <th>Unit</th>
                           <th style="width: 30px;">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <!-- First Row -->
                        <tr class="gsize_row" data-index="1" id="gsize_tr_1">
                           <td><input type="text" class="form-control gen_size"  placeholder="Enter Size"></td>
                           <td><input type="text" class="form-control gen_reel"  placeholder="Enter Reel No."></td>
                           <td><input type="number" class="form-control gen_weight"  placeholder="Enter Weight"></td>
                           <td>
                              <select class="form-select me-2 gen_unit">
                                 <option value="">Select Unit</option>
                                 <option value="INCH">INCH</option>
                                 <option value="CM">CM</option>
                                 <option value="MM">MM</option>
                              </select>
                           </td>
                           <td><button type="button" class="btn btn-danger btn-sm remove-gsize-row">X</button></td>
                        </tr>
                     </tbody>

               </table>
            </div>

            <div class="mt-2 text-end">
               <strong>Total Weight: <span id="generated_total_weight">0</span></strong>
            </div>

         </div>

         <div class="modal-footer">
            <input type="hidden" id="generated_item_row_id">
            <button class="btn btn-info generated_item_size_btn">Submit</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>

      </div>
   </div>
</div>

</body>
@include('layouts.footer')
<script>
var production_module_status = "<?php echo $production_module_status; ?>";
var itemIds = {!! json_encode($itemIds) !!};
var add_more_count = 1;

$(document).ready(function() {
    // Initialize select2
    $(".select2-single, .select2-multiple").select2();

    // Trigger series_no change if needed
    $("#series_no").change();

    // Function to update disabled options
    function updateItemOptions() {
        let selectedItems = [];
        $('.consume_item').each(function() {
            let val = $(this).val();
            if (val) selectedItems.push(val);
        });

        $('.consume_item').each(function() {
            let current = $(this).val();
            $(this).find('option').each(function() {
                let optionVal = $(this).val();
                if (optionVal !== "" && optionVal !== current && selectedItems.includes(optionVal)) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
        });

        // Refresh select2 to reflect disabled changes
        $('.consume_item').select2();
    }

    // Add More button
    $(".add_more").click(function() {
    let empty_status = 0;

    // Check for empty required fields
    $('.consume_item').each(function() {
        let i = $(this).attr('data-id');
        if ($(this).val() == "" || $("#consume_weight_" + i).val() == "" || $("#consume_price_" + i).val() == "") {
            empty_status = 1;
        }
    });

    if (empty_status == 1) {
        alert("Please enter required fields");
        return;
    }

    add_more_count++;

    // Get last row serial number
    let last_srn = $("#consum_total").closest('tr').prev().find("td:first").html();
    let srn = last_srn ? parseInt(last_srn) + 1 : 1;

    // Option elements
    var optionElements = '<?php echo addslashes($items_list); ?>';

    // Conditionally add ⚙️ button
    var configureButton = '';
    if (production_module_status == 1) {
        configureButton = `<button type="button" class="btn btn-outline-secondary p-1 px-2 configure-size-btn" data-id="${add_more_count}" title="Configure Item ⚙️">⚙️</button>`;
    }

    // Build new row
    var newRow = `
        <tr id="tr_${add_more_count}" class="font-14 font-heading bg-white">
            <td class="w-min-50" id="consume_srn_${add_more_count}">${srn}</td>
            <td>
                <div class="d-flex align-items-center">
                    <select class="form-control consume_item select2-single" name="consume_item[]" data-id="${add_more_count}" id="consume_item_${add_more_count}">
                        ${optionElements}
                    </select>
                    ${configureButton}
                    <input type="hidden" name="item_size_info[]" id="item_size_info_${add_more_count}">
                </div>
            </td>
            <td>
                <input type="number" name="consume_weight[]" class="form-control consume_weight" data-id="${add_more_count}" id="consume_weight_${add_more_count}" placeholder="QUANTITY" style="text-align: right;" readonly>
            </td>
            <td class="w-min-50">
                <input type="text" class="form-control consume_unit" id="consume_unit_tr_${add_more_count}" readonly style="text-align:center;" data-id="${add_more_count}" name="consume_unit_name[]">
                <input type="hidden" class="consume_units" name="consume_units[]" id="consume_units_tr_${add_more_count}">
            </td>
            <td>
                <input type="number" name="consume_price[]" class="form-control consume_price" data-id="${add_more_count}" id="consume_price_${add_more_count}" placeholder="Price" style="text-align: right;">
            </td>
            <td>
                <input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="${add_more_count}" id="consume_amount_${add_more_count}" placeholder="Amount" readonly style="text-align: right;">
            </td>
            <td>
                <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="${add_more_count}" viewBox="0 0 16 16">
                    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                </svg>
            </td>
        </tr>
    `;

    // Insert new row before totals row
    $("#consum_total").closest('tr').before(newRow);

    // Initialize select2 for new row
    $(".select2-single").select2();

    // Update options to disable already selected items
    updateItemOptions();
});


   var add_more_count1 = 1;

// Add new generated item row
$(".add_more1").click(function() {
    let empty_status = 0;
    $('.generated_item').each(function(){   
        let i = $(this).attr('data-id');
        if($(this).val()=="" || $("#generated_weight_"+i).val()=="" || $("#generated_price_"+i).val()==""){
            empty_status=1;            
        }                   
    });
    if(empty_status==1){
        alert("Please enter required fields");
        return;
    }

    let srn = $("#generated_srn_"+add_more_count1).html();
    srn++;
    add_more_count1++;
    var $curRow = $("#generated_total").closest('tr');
    var optionElements = '<?php echo $items_list;?>';

    let newRow = `<tr id="tr1_${add_more_count1}" class="font-14 font-heading bg-white">
        <td class="w-min-50" id="generated_srn_${add_more_count1}">${srn}</td>
        <td>
            <div class="d-flex align-items-center">
                <select class="form-control generated_item select2-single" name="generated_item[]" data-id="${add_more_count1}" id="generated_item_${add_more_count1}">
                    ${optionElements}
                </select>
                <button type="button" class="btn btn-outline-secondary p-1 px-2 generated-configure-btn" data-id="${add_more_count1}" title="Configure Item ⚙️">⚙️</button>
                <input type="hidden" name="generated_size_info[]" id="generated_size_info_${add_more_count1}" data-id="${add_more_count1}">
            </div>
        </td>
        <td><input type="number" name="generated_weight[]" class="form-control generated_weight" data-id="${add_more_count1}" id="generated_weight_${add_more_count1}" placeholder="QUANTITY" style="text-align: right;" readonly></td>
        <td>
            <input type="text" class="w-100 form-control generated_unit" id="generated_unit_tr_${add_more_count1}" readonly style="text-align:center;" data-id="${add_more_count1}" name="generated_unit_name[]"/>
            <input type="hidden" class="generated_units w-100" name="generated_units[]" id="generated_units_tr_${add_more_count1}"/>
        </td>
        <td><input type="number" name="generated_price[]" class="form-control generated_price" data-id="${add_more_count1}" id="generated_price_${add_more_count1}" placeholder="Price" style="text-align: right;"></td>
        <td><input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="${add_more_count1}" id="generated_amount_${add_more_count1}" placeholder="Amount" readonly style="text-align: right;"></td>
        <td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove1" data-id="${add_more_count1}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td>
    </tr>`;

    $curRow.before(newRow);
    $(".select2-single").select2();
});
    // Remove row
    $(document).on('click', '.remove', function() {
        let rowId = $(this).data('id');
        $("#tr_" + rowId).remove();

        // Re-number serial numbers
        $("#consum_table tbody tr").each(function(index){
            $(this).find('td:first').html(index + 1);
        });

        // Update options to re-enable removed item if possible
        updateItemOptions();
    });

    // Update disabled options on selection change
    $(document).on('change', '.consume_item', function() {
        updateItemOptions();
    });
});

   // Remove row
$(document).on("click", ".remove1", function() {
    let id = $(this).attr('data-id');
    $("#tr1_" + id).remove();
    calculateAmountNew(1);
});
   $(".savebtn").click(function(){
      if(confirm("Are you sure to submit?")==true){            
         $("#frm").validate({
            ignore: [], 
            rules: {
               series_no: "required",
               voucher_no: "required",
               material_center: "required",
               "consume_item[]": "required",
               "consume_weight[]" : "required",
               "consume_price[]" : "required",
               "generated_item[]": "required",
               "generated_weight[]" : "required",
               "generated_price[]" : "required"
            },
            messages: {
               series_no: "Please select series no",
               voucher_no: "Please enter voucher no",
               material_center: "Please select material center",
               "consume_item[]" : "Please select item",
               "consume_weight[]" : "Please enter quantity",
               "consume_price[]" : "Please enter price",  
               "generated_item[]" : "Please select item",
               "generated_weight[]" : "Please enter quantity",
               "generated_price[]" : "Please enter price",              
            }
         });
      }else{
         return false;
      }
      // return false;
      // let date = $("#date").val();
      // let item_count = 0;
      // $(".consume_item").each(function(){
      //    let id = $(this).attr('data-id');
      //    let consume_item = $(this).val();
      //    let consume_weight = $("#consume_weight_"+id).val();
      //    let new_item = $("#new_item_"+id).val();
      //    let new_weight = $("#new_weight_"+id).val();
      //    if(consume_item!="" && consume_weight!="" && new_item!="" && new_weight!=""){
      //       item_count++;
      //    }
      // });
      // if(item_count==0){
      //    alert("Please item and weight.");
      //    return false;
      // }
      // $("#frm").submit();
   });
   $(document).on('keyup','.consume_weight',function(){
      let id = $(this).attr('data-id');
      calculateAmount(id);
   });
   $(document).on('keyup','.consume_price',function(){
      let id = $(this).attr('data-id');
      calculateAmount(id);
   });
   $(document).on('keyup','.generated_weight',function(){
      let id = $(this).attr('data-id');
      calculateAmountNew(id);
   });
   $(document).on('keyup','.generated_price',function(){
      let id = $(this).attr('data-id');
      calculateAmountNew(id);
   });
   function calculateAmount(id){
      let consume_price = $("#consume_price_"+id).val();
      let consume_weight = $("#consume_weight_"+id).val();
      let cweight = 0;
      $(".consume_weight").each(function(){
         if($(this).val()!=''){
            cweight = parseFloat(cweight) + parseFloat($(this).val());
         }
      });
      $("#consume_weight_total").html(cweight);
      if(consume_price=="" || consume_weight==""){
         let camount = 0;
         $(".consume_amount").each(function(){
            if($(this).val()!=''){
               camount = parseFloat(camount) + parseFloat($(this).val());
            }
         });
         $("#consume_amount_total").html(camount);
            return;
         }
      let amount = parseFloat(consume_price)*parseFloat(consume_weight);
      $("#consume_amount_"+id).val(amount.toFixed(2));
      let camount = 0;
      $(".consume_amount").each(function(){
         if($(this).val()!=''){
            camount = parseFloat(camount) + parseFloat($(this).val());
         }
      });
      $("#consume_amount_total").html(camount);

   } 
   // Calculate amounts and totals
function calculateAmountNew(id) {
    let generated_price = $("#generated_price_" + id).val();
    let generated_weight = $("#generated_weight_" + id).val();

    // TOTAL WEIGHT
    let nweight = 0;
    $(".generated_weight").each(function() {
        if ($(this).val() != '') nweight += parseFloat($(this).val());
    });
    $(".gen_weight").each(function() {
        if ($(this).val() != '') nweight += parseFloat($(this).val());
    });
    $("#consume_weight_total1").html(nweight.toFixed(3));

    // TOTAL AMOUNT
    let namount = 0;
    $(".generated_amount").each(function() {
        if ($(this).val() != '') namount += parseFloat($(this).val());
    });
    $("#consume_amount_total1").html(namount.toFixed(2));

    // Row amount
    if (generated_price != "" && generated_weight != "") {
        let amount = parseFloat(generated_price) * parseFloat(generated_weight);
        $("#generated_amount_" + id).val(amount.toFixed(2));
    }

    // Recalculate overall total
    namount = 0;
    $(".generated_amount").each(function() {
        if ($(this).val() != '') namount += parseFloat($(this).val());
    });
    $("#consume_amount_total1").html(namount.toFixed(2));
}

   $("#voucher_prefix").keyup(function(){
      $("#voucher_no").val($(this).val());
   });
   $("#series_no").change(function(){
      $("#voucher_prefix").prop('readonly',true);
      $("#voucher_no").attr('required',true);
      let series = $(this).val();
      let invoice_prefix = $('option:selected', this).attr('data-invoice_prefix');
      let manual_enter_invoice_no = $('option:selected', this).attr('data-manual_enter_invoice_no');
      $("#manual_enter_invoice_no").val(manual_enter_invoice_no);
      if(manual_enter_invoice_no==0){
         if(invoice_prefix!=""){
            $("#voucher_prefix").val(invoice_prefix);
         }else{
            $("#voucher_prefix").val($('option:selected', this).attr('data-invoice_start_from'));
         }      
         $("#voucher_no").val($('option:selected', this).attr('data-invoice_start_from'));
      }else if(manual_enter_invoice_no==1){
         $("#voucher_no").attr('required',false);
         $("#voucher_prefix").val("");
         $("#voucher_prefix").prop('readonly',false);
      }                  
   });
   //WHEN ITEM IS CHANGED
   $(document).on('change', '.consume_item', function () {
      let rowId = $(this).data("id");
      // Set unit fields
      $('#consume_unit_tr_' + rowId).val($('option:selected', this).data('unit_name'));
      $('#consume_units_tr_' + rowId).val($('option:selected', this).data('unit_id'));
      // Reset when empty
      if ($(this).val() == "") {
         $("#consume_weight_" + rowId).val('');
         $("#consume_price_" + rowId).val('');
         $("#consume_amount_" + rowId).val('');
         calculateAmount(rowId);
         return;
      }
      $("#consume_weight_"+rowId).attr('readonly',false);
      //FETCH PRICE FOR SELECTED ITEM
      let itemId = $(this).val();
      let seriesNo = $("#series_no").val();
      let rawDate = $("#date").val();     // dd-mm-yyyy
      let date = formatToYMD(rawDate);  
      // yyyy-mm-dd
      let item_id = parseInt($(this).val());
      if (itemIds.includes(item_id)) {
         $.ajax({
            url: "{{ url('/get-item-price') }}",
            type: "GET",
            data: {
               item_id: itemId,
               series_no: seriesNo,
               date: date
            },
            success: function (res) {
               let price = res.price;
               $("#consume_price_" + rowId).val(price);
            }
         });
      }       //PRODUCTION ITEM SIZE LOGIC
      if (production_module_status == 1) {
         $("#quantity_tr_" + rowId).val('');
         $("#quantity_tr_" + rowId).attr('readonly', false);
         $("#item_size_info_" + rowId).val('');

         $.ajax({
            url: '{{url("get-item-size-quantity")}}',
            type: 'POST',
            dataType: 'JSON',
            async: false,
            data: {
               _token: '{{ csrf_token() }}',
               item_id: itemId,
               series: $("#series_no").val()
            },
            success: function (res) {
               if (res.length == 0) {
                  //alert("No Size Available For This Item");
                  $("#consume_weight_"+rowId).attr('readonly',false);
                  return;
               }
               $("#consume_weight_"+rowId).attr('readonly',true);
               let size_html = "<option value=''>Select Size</option>";
               res.forEach(function (e) {
                  size_html += "<option value='" + e.id + "' data-size='" + e.size +
                        "' data-weight='" + e.weight + "' data-reel_no='" + e.reel_no +
                        "'>Size : " + e.size + " | Weight : " + e.weight + " | Reel : " + e.reel_no + "</option>";
               });
               let bodyRow =
                    "<tr id='size_tr_1'>" +
                    "<td><select class='form-select select2-single item_size' data-index='1'>" +
                    size_html +
                    "</select></td>" +
                    "<td><input type='text' class='form-control item_weight' readonly id='item_weight_1'></td>" +
                    "<td><input type='text' class='form-control item_reel_no' readonly id='item_reel_no_1'></td>" +
                    "<td><button type='button' class='btn btn-sm btn-danger remove-row'>X</button></td>" +
                    "</tr>";

               $(".item_size_table tbody").html(bodyRow);

               $(".item_size").select2({
                  dropdownParent: $('#sizeModal'),
                  width: '100%'
               });
               $("#item_size_row_id").val(rowId);
               $("#sizeModal").modal('show');
            }
         });
      }
   });
   //ITEM SIZE CHANGE
$(document).on('change', '.item_size', function () {

    let selectedValue = $(this).val();

    // prevent duplicate size in the same modal
    let duplicate = false;
    $('.item_size').not(this).each(function () {
        if ($(this).val() == selectedValue && selectedValue !== '') {
            duplicate = true;
        }
    });

    if (duplicate) {
        alert("This size is already selected.");
        $(this).val('').trigger('change');
        return;
    }

    if (selectedValue == "") return;

    let index = parseInt($(this).data('index'));
    let nextIndex = index + 1;

    let weight = $(this).find(':selected').data('weight');
    let reel = $(this).find(':selected').data('reel_no');

    $("#item_weight_" + index).val(weight);
    $("#item_reel_no_" + index).val(reel);

    updateTotalWeight();

    // Only add new row if next row doesn't exist
    if ($("#size_tr_" + nextIndex).length > 0) return;

    // Clone row 1
    let clone = $("#size_tr_1").clone();
    clone.attr('id', 'size_tr_' + nextIndex);

    clone.find(".select2-container").remove();

    let newSelect = $("#size_tr_1").find(".select2-single").clone();
    clone.find(".select2-single").replaceWith(newSelect);
    clone.find(".item_size").val('').attr('data-index', nextIndex);

    clone.find(".item_weight").attr("id", "item_weight_" + nextIndex).val('');
    clone.find(".item_reel_no").attr("id", "item_reel_no_" + nextIndex).val('');

    $("#size_tr_" + index).after(clone);

    clone.find(".select2-single").select2({
        dropdownParent: $("#sizeModal"),
        width: "100%"
    });
});
   //UPDATE TOTAL WEIGHT
   function updateTotalWeight() {

      let total = 0;
      let sizeInfo = [];
      let reelInfo = [];
      let sizeIds = [];

      $(".item_size").each(function () {

         if ($(this).val() != "") {
               let size = $(this).find(":selected").data("size");
               let reel = $(this).find(":selected").data("reel_no");
               let weight = $(this).find(":selected").data("weight");
               let id = $(this).val();

               sizeInfo.push(size);
               reelInfo.push(reel);
               sizeIds.push(id);

               if (!isNaN(weight)) total += parseFloat(weight);
         }
      });

      $("#total_weight").text(total);

      $("#sizeModal").data("total_weight", total);
      $("#sizeModal").data("size_list", sizeInfo);
      $("#sizeModal").data("reel_list", reelInfo);
      $("#sizeModal").data("size_ids", sizeIds);
   }
   //SIZE MODAL SUBMIT
   $(".item_size_btn").click(function () {

      let rowId = $("#item_size_row_id").val();

      let totalWeight = $("#sizeModal").data("total_weight") || 0;
      let sizeIds = $("#sizeModal").data("size_ids") || [];
      let sizeList = $("#sizeModal").data("size_list") || [];
      let reelList = $("#sizeModal").data("reel_list") || [];
      let itemId = $("") 

      // Fill the row values
      $("#quantity_tr_" + rowId).val(totalWeight).attr("readonly", true);
      $("#consume_weight_" + rowId).val(totalWeight);


      $("#item_size_info_" + rowId).val(JSON.stringify(sizeIds));


      $("#sizeModal").modal("hide");

      calculateAmount(rowId);

   });
   //HELPER: Convert dd-mm-yyyy → yyyy-mm-dd
   function formatToYMD(d) {
      let p = d.split("-");
      return p[2] + "-" + p[1] + "-" + p[0];
   }
  //WEIGHT TYPE → UPDATE AMOUNT
   $(document).on("keyup", ".consume_weight", function () {
      let rowId = $(this).data("id");
      let qty = parseFloat($(this).val()) || 0;
      let price = parseFloat($("#consume_price_" + rowId).val()) || 0;
      
      
      $("#consume_amount_" + rowId).val((qty * price).toFixed(2));
      //$("#generated_amount_" + rowId).val((qty * price).toFixed(2));
   });

   // Generated item change + modal logic
$(document).on('change', '.generated_item', function() {
    let rowId = $(this).data("id");
    let itemId = $(this).val();

    $("#generated_unit_tr_" + rowId).val($('option:selected', this).data('unit_name'));
    $("#generated_units_tr_" + rowId).val($('option:selected', this).data('unit_id'));

    if (!itemId) {
        $("#generated_weight_" + rowId).val('');
        $("#generated_price_" + rowId).val('');
        $("#generated_amount_" + rowId).val('');
        calculateAmountNew(rowId);
        return;
    }
    $("#generated_weight_"+rowId).attr('readonly',false);
    let seriesNo = $("#series_no").val();
    let rawDate = $("#date").val();
    let date = formatToYMD(rawDate);

    $.ajax({
        url: "{{ url('/get-item-price') }}",
        type: "GET",
        data: { item_id: itemId, series_no: seriesNo, date: date },
        success: function(res) {
            let price = res.price ?? 0;
            $("#generated_price_" + rowId).val(price);
            let weight = parseFloat($("#generated_weight_" + rowId).val()) || 0;
            $("#generated_amount_" + rowId).val((price * weight).toFixed(2));
            calculateAmountNew(rowId);
        }
    });
    $("#generated_weight_"+rowId).attr('readonly',false);
    // Open modal only if production item
    if (production_module_status == 1 && itemIds.includes(parseInt(itemId))) {
        resetGeneratedSizeModal();
        $("#generated_weight_"+rowId).attr('readonly',true);
        // Prefill modal
        let sizeInfoJson = $("#generated_size_info_" + rowId).val();
        if (sizeInfoJson) {
            let sizeInfo = JSON.parse(sizeInfoJson);
            if (sizeInfo.sizes && sizeInfo.sizes.length) {
                sizeInfo.sizes.forEach((size, index) => {
                    let reel = sizeInfo.reels[index] || '';
                    let weight = sizeInfo.weights[index] || '';
                    let unit = sizeInfo.units[index] || '';

                    let rowIndex = index + 1;
                    let row = `<tr class="gsize_row" data-index="${rowIndex}" id="gsize_tr_${rowIndex}">
                        <td><input type="text" class="form-control gen_size" value="${size}"></td>
                        <td><input type="text" class="form-control gen_reel" value="${reel}"></td>
                        <td><input type="number" class="form-control gen_weight" value="${weight}"></td>
                        <td>
                            <select class="form-select me-2 gen_unit">
                                <option value="">Select Unit</option>
                                <option value="INCH" ${unit=='INCH'?'selected':''}>INCH</option>
                                <option value="CM" ${unit=='CM'?'selected':''}>CM</option>
                                <option value="MM" ${unit=='MM'?'selected':''}>MM</option>
                            </select>
                        </td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-gsize-row">X</button></td>
                    </tr>`;
                    $(".generated_item_size_table tbody").append(row);
                });
            }
        }

        $("#generated_item_row_id").val(rowId);
        $("#generatedSizeModal").modal("show");
        updateGeneratedTotalWeight();
    }
});

// Modal weight change + dynamic row
$(document).on("keyup", ".gen_weight", function() {
    let tr = $(this).closest("tr");
    let index = parseInt(tr.data("index"));
    let nextIndex = index + 1;
    let rowId = $("#generated_item_row_id").val();

    let weight = parseFloat($("#generated_weight_" + rowId).val()) || 0;
    let price = parseFloat($("#generated_price_" + rowId).val()) || 0;
    $("#generated_amount_" + rowId).val((weight * price).toFixed(2));

    if ($("#gsize_tr_" + nextIndex).length === 0) {
        let newRow = `<tr class="gsize_row" data-index="${nextIndex}" id="gsize_tr_${nextIndex}">
            <td><input type="text" class="form-control gen_size" placeholder="Enter Size"></td>
            <td><input type="text" class="form-control gen_reel" placeholder="Enter Reel No."></td>
            <td><input type="number" class="form-control gen_weight" placeholder="Enter Weight"></td>
            <td>
                <select class="form-select me-2 gen_unit">
                    <option value="">Select Unit</option>
                    <option value="INCH">INCH</option>
                    <option value="CM">CM</option>
                    <option value="MM">MM</option>
                </select>
            </td>
            <td><button type="button" class="btn btn-danger btn-sm remove-gsize-row">X</button></td>
        </tr>`;
        $(".generated_item_size_table tbody").append(newRow);
    }

    updateGeneratedTotalWeight();
});

// Remove modal row
$(document).on("click", ".remove-gsize-row", function() {
    $(this).closest("tr").remove();
    updateGeneratedTotalWeight();
});

// Update modal total weight
function updateGeneratedTotalWeight() {
    let total = 0;

    // Sum ONLY modal weights
    $(".gen_weight").each(function () {
        let w = parseFloat($(this).val());
        if (!isNaN(w)) total += w;
    });

    // Update modal total display
    $("#generated_total_weight").text(total.toFixed(3));

    // Update main table row weight for this item
    let rowId = $("#generated_item_row_id").val();
    $("#generated_weight_" + rowId).val(total);

    // Update main row amount (weight * price)
    let price = parseFloat($("#generated_price_" + rowId).val()) || 0;
    $("#generated_amount_" + rowId).val((total * price).toFixed(2));

    // Update overall TOTAL quantity (consume_weight_total1)
    let totalWeightAll = 0;
    $(".generated_weight").each(function() {
        let w = parseFloat($(this).val());
        if (!isNaN(w)) totalWeightAll += w;
    });

    $("#consume_weight_total1").text(totalWeightAll.toFixed(3));

    // Update overall TOTAL amount
    let totalAmountAll = 0;
    $(".generated_amount").each(function() {
        let a = parseFloat($(this).val());
        if (!isNaN(a)) totalAmountAll += a;
    });
    $("#consume_amount_total1").text(totalAmountAll.toFixed(2));
}



// Modal submit
$(".generated_item_size_btn").click(function() {
    let rowId = $("#generated_item_row_id").val();
    let sizes=[], weights=[], reels=[], units=[];

    $(".gsize_row").each(function() {
        let size = $(this).find(".gen_size").val();
        let weight = $(this).find(".gen_weight").val();
        let reel = $(this).find(".gen_reel").val();
        let unit = $(this).find(".gen_unit").val();
        if (size !== "" && weight !== "") {
            sizes.push(size);
            weights.push(weight);
            reels.push(reel);
            units.push(unit);
        }
    });

    $("#generated_size_info_" + rowId).val(JSON.stringify({
        sizes, weights, reels, units
    }));

    $("#generatedSizeModal").modal("hide");
});


// Reset modal completely
function resetGeneratedSizeModal() {
    $(".generated_item_size_table tbody").html("");
    let firstRow = `<tr class="gsize_row" data-index="1" id="gsize_tr_1">
        <td><input type="text" class="form-control gen_size" placeholder="Enter Size"></td>
        <td><input type="text" class="form-control gen_reel" placeholder="Enter Reel No."></td>
        <td><input type="number" class="form-control gen_weight" placeholder="Enter Weight"></td>
        <td>
            <select class="form-select me-2 gen_unit">
                <option value="">Select Unit</option>
                <option value="INCH">INCH</option>
                <option value="CM">CM</option>
                <option value="MM">MM</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm remove-gsize-row">X</button></td>
    </tr>`;
    $(".generated_item_size_table tbody").append(firstRow);
    $("#generated_total_weight").text(0);
}
// ⚙️ Configure size button
$(document).on('click', '.configure-size-btn', function() {
    let rowId = $(this).data('id');
    let sizeInfoJSON = $("#item_size_info_" + rowId).val();
    let itemId = $("#consume_item_" + rowId).val();
    let seriesNo = $("#series_no").val();

    $(".item_size_table tbody").html("");

    $.ajax({
        url: '{{ url("get-item-size-quantity") }}',
        type: 'POST',
        dataType: 'JSON',
        data: {
            _token: '{{ csrf_token() }}',
            item_id: itemId,
            series: seriesNo
        },
        success: function(res) {
            if (res.length == 0) {
                alert("No size available for this item");
                return;
            }

            let selectedSizeIds = sizeInfoJSON ? JSON.parse(sizeInfoJSON) : [];
            let index = 1;
            let bodyHTML = "";

            // Prefill selected sizes
            selectedSizeIds.forEach(function(selectedId) {
                let sizeData = res.find(e => e.id == selectedId);
                if (sizeData) {
                    bodyHTML += buildSizeRow(res, sizeData.id, index, sizeData.weight, sizeData.reel_no);
                    index++;
                }
            });
            // After prefill
            $(".item_size").select2({
               dropdownParent: $('#sizeModal'),
               width: '100%'
            });

            // Always add **one empty row** at the bottom
            bodyHTML += buildSizeRow(res, '', index, '', '');
            
            $(".item_size_table tbody").html(bodyHTML);

            $(".item_size").select2({
                dropdownParent: $('#sizeModal'),
                width: '100%'
            });
            $(".item_size").select2({
               dropdownParent: $('#sizeModal'),
               width: '100%'
            });
            $("#item_size_row_id").val(rowId);
            $("#sizeModal").modal('show');
            updateAvailableSizes();
            updateTotalWeight();
        }
    });
});

// Function to build a single size row
function buildSizeRow(res, selectedId, index, weightVal, reelVal) {
    let selectHTML = "<option value=''>Select Size</option>";
    res.forEach(function(opt) {
        let selected = (opt.id == selectedId) ? "selected" : "";
        selectHTML += "<option value='" + opt.id + "' data-size='" + opt.size + "' data-weight='" + opt.weight + "' data-reel_no='" + opt.reel_no + "' " + selected + ">Size: " + opt.size + " | Weight: " + opt.weight + " | Reel: " + opt.reel_no + "</option>";
    });

    return "<tr id='size_tr_" + index + "'>" +
            "<td><select class='form-select select2-single item_size' data-index='" + index + "'>" + selectHTML + "</select></td>" +
            "<td><input type='text' class='form-control item_weight' readonly id='item_weight_" + index + "' value='" + weightVal + "'></td>" +
            "<td><input type='text' class='form-control item_reel_no' readonly id='item_reel_no_" + index + "' value='" + reelVal + "'></td>" +
            "<td><button type='button' class='btn btn-sm btn-danger remove-row'>X</button></td>" +
           "</tr>";
}

// Handle size change to add new row automatically
$(document).on('change', '.item_size', function() {
    let currentRow = $(this).closest('tr');
    let index = parseInt($(this).data('index'));

    let weight = $(this).find(':selected').data('weight') || '';
    let reel = $(this).find(':selected').data('reel_no') || '';
    $("#item_weight_" + index).val(weight);
    $("#item_reel_no_" + index).val(reel);

    updateTotalWeight();

    // Add a new empty row if last row is filled
    if (currentRow.is(":last-child") && $(this).val() !== "") {
        let nextIndex = index + 1;
        let itemId = $("#consume_item_" + $("#item_size_row_id").val()).val();
        let seriesNo = $("#series_no").val();

        $.ajax({
            url: '{{ url("get-item-size-quantity") }}',
            type: 'POST',
            dataType: 'JSON',
            data: {
                _token: '{{ csrf_token() }}',
                item_id: itemId,
                series: seriesNo
            },
            success: function(res) {
                let newRow = buildSizeRow(res, '', nextIndex, '', '');
                $(".item_size_table tbody").append(newRow);
                $(".item_size").select2({
                    dropdownParent: $('#sizeModal'),
                    width: '100%'
                });
            }
        });
    }
});

$(".item_size_btn").click(function() {
    let rowId = $("#item_size_row_id").val();
    let sizeIds = [];

    $(".item_size").each(function() {
        let val = $(this).val();
        if (val) sizeIds.push(val);
    });

    let totalWeight = 0;
    $(".item_weight").each(function() {
        let w = parseFloat($(this).val()) || 0;
        totalWeight += w;
    });

    // Overwrite old values
    $("#quantity_tr_" + rowId).val(totalWeight).attr("readonly", true);
    $("#consume_weight_" + rowId).val(totalWeight);
    $("#item_size_info_" + rowId).val(JSON.stringify(sizeIds));

    $("#sizeModal").modal("hide");
    calculateAmount(rowId);
});

// REMOVE SIZE ROW
$(document).on('click', '.remove-row', function() {
    let row = $(this).closest('tr');
    row.remove();

    // Reindex remaining rows
    $(".item_size_table tbody tr").each(function(index) {
        $(this).attr('id', 'size_tr_' + (index + 1));
        $(this).find('.item_size').attr('data-index', index + 1);
        $(this).find('.item_weight').attr('id', 'item_weight_' + (index + 1));
        $(this).find('.item_reel_no').attr('id', 'item_reel_no_' + (index + 1));
    });

    // Update total weight
    updateTotalWeight();

    // Always keep at least one empty row
    if ($(".item_size_table tbody tr").length == 0) {
        let rowId = $("#item_size_row_id").val();
        let itemId = $("#consume_item_" + rowId).val();
        let seriesNo = $("#series_no").val();
        
        $.ajax({
            url: '{{ url("get-item-size-quantity") }}',
            type: 'POST',
            dataType: 'JSON',
            data: {
                _token: '{{ csrf_token() }}',
                item_id: itemId,
                series: seriesNo
            },
            success: function(res) {
                let newRow = buildSizeRow(res, '', 1, '', '');
                $(".item_size_table tbody").html(newRow);
                $(".item_size").select2({
                    dropdownParent: $('#sizeModal'),
                    width: '100%'
                });
            }
        });
    }
});
// FUNCTION TO UPDATE AVAILABLE SIZES
function updateAvailableSizes() {
    let selectedSizes = [];

    // Collect all currently selected sizes
    $(".item_size").each(function() {
        let val = $(this).val();
        if (val !== "") selectedSizes.push(val);
    });

    // Update each select box
    $(".item_size").each(function() {
        let currentVal = $(this).val(); // Keep current selection
        $(this).find("option").each(function() {
            let optionVal = $(this).attr("value");
            if (optionVal === "") return; // skip placeholder
            // Disable option if selected in another row
            if (selectedSizes.includes(optionVal) && optionVal !== currentVal) {
                $(this).attr("disabled", true);
            } else {
                $(this).attr("disabled", false);
            }
        });
    });
}

$(document).on('change', '.item_size', function() {
    let row = $(this).closest('tr');
    let index = $(this).data('index');
    let selectedValue = $(this).val();

    // Update weight & reel
    if (selectedValue !== "") {
        let weight = $(this).find(':selected').data('weight');
        let reel = $(this).find(':selected').data('reel_no');
        $("#item_weight_" + index).val(weight);
        $("#item_reel_no_" + index).val(reel);
    } else {
        $("#item_weight_" + index).val('');
        $("#item_reel_no_" + index).val('');
    }

    // Update totals & disable selected sizes
    updateTotalWeight();
    updateAvailableSizes();

    // ✅ Auto-add a new row if last row is filled
    let lastRow = $(".item_size_table tbody tr").last();
    if (lastRow.find(".item_size").val() !== "") {
        let nextIndex = $(".item_size_table tbody tr").length + 1;
        let newRow = lastRow.clone();
        newRow.attr("id", "size_tr_" + nextIndex);
        newRow.find(".item_size").val("").attr("data-index", nextIndex);
        newRow.find(".item_weight").val("").attr("id", "item_weight_" + nextIndex);
        newRow.find(".item_reel_no").val("").attr("id", "item_reel_no_" + nextIndex);
        newRow.find(".select2-container").remove();
        lastRow.after(newRow);
        newRow.find(".item_size").select2({ dropdownParent: $('#sizeModal'), width: "100%" });

        updateAvailableSizes();
    }
});
$(document).ready(function(){

    // Function to toggle ⚙️ visibility per row
    function toggleConfigureButton(rowId) {
        let $btn = $('.configure-size-btn[data-id="'+rowId+'"]');
        let $select = $('#consume_item_' + rowId);

        if(production_module_status == 1 && $select.val() != ""){
            $btn.show();
        } else {
            $btn.hide();
        }
    }

    // Initialize existing rows
    $('.consume_item').each(function(){
        let rowId = $(this).data('id');
        toggleConfigureButton(rowId);
    });

    // Handle select change dynamically
    $(document).on('change', '.consume_item', function(){
        let rowId = $(this).data('id');
        toggleConfigureButton(rowId);
    });

    // Handle ⚙️ click dynamically
    $(document).on('click', '.configure-size-btn', function(){
        let rowId = $(this).data('id');
        let selectedItem = $('#consume_item_' + rowId).val();
        if(selectedItem){
            console.log('Open modal for item:', selectedItem);
            // call your modal function here
            // openProductionModal(selectedItem, rowId);
        }
    });

    // After adding new row
    $(document).on('click', '.add_more', function(){
        // your existing add_more logic...

        // After inserting new row, toggle ⚙️ visibility for that row
        toggleConfigureButton(add_more_count);
    });

});
// ⚙️ button click to open modal for selected item
$(document).on("click", ".generated-configure-btn", function() {
    let rowId = $(this).data("id");
    let itemVal = $("#generated_item_" + rowId).val();

    if (!itemVal) {
        alert("Please select an item first.");
        return;
    }

    // CLEAR modal
    $(".generated_item_size_table tbody").html("");

    // PREFILL modal if existing data
    let sizeInfo = $("#generated_size_info_" + rowId).val();
    if (sizeInfo) {
        let info = JSON.parse(sizeInfo);
        if (info.sizes && info.sizes.length) {
            info.sizes.forEach((size, index) => {
                let rowIndex = index + 1;
                $(".generated_item_size_table tbody").append(
                    getGsizeRowHtml(rowIndex, size, info.reels[index], info.weights[index], info.units[index])
                );
            });
        }
    }

    // ALWAYS add 1 empty row at the end
    let lastIndex = $(".generated_item_size_table tbody tr").length;
    addEmptyGsizeRow(lastIndex + 1);

    $("#generated_item_row_id").val(rowId);
    $("#generatedSizeModal").modal("show");
    updateGeneratedTotalWeight();
});




// Auto-add new row when typing in last row
$(document).on("keyup", ".gen_weight", function() {
    let tr = $(this).closest("tr");
    let index = parseInt(tr.data("index"));
    updateGeneratedTotalWeight();

    // If last row and weight is entered, add new row
    if (index === $(".generated_item_size_table tbody tr").length && $(this).val() != "") {
        addEmptyGsizeRow(index + 1);
    }
});

function getGsizeRowHtml(index, size='', reel='', weight='', unit='') {
    return `<tr class="gsize_row" data-index="${index}" id="gsize_tr_${index}">
        <td><input type="text" class="form-control gen_size" placeholder="Enter Size" value="${size}"></td>
        <td><input type="text" class="form-control gen_reel" placeholder="Enter Reel No." value="${reel}"></td>
        <td><input type="number" class="form-control gen_weight" placeholder="Enter Weight" value="${weight}"></td>
        <td>
            <select class="form-select me-2 gen_unit">
                <option value="">Select Unit</option>
                <option value="INCH" ${unit=='INCH'?'selected':''}>INCH</option>
                <option value="CM" ${unit=='CM'?'selected':''}>CM</option>
                <option value="MM" ${unit=='MM'?'selected':''}>MM</option>
            </select>
        </td>
        <td><button type="button" class="btn btn-danger btn-sm remove-gsize-row">X</button></td>
    </tr>`;
}

function addEmptyGsizeRow(index) {
    $(".generated_item_size_table tbody").append(getGsizeRowHtml(index));
}

// Remove modal row
$(document).on("click", ".remove-gsize-row", function() {
    $(this).closest("tr").remove();
    updateGeneratedTotalWeight();
});

$(document).ready(function () {

    let params = new URLSearchParams(window.location.search);

    let entryDate = params.get('entry_date');
    let items = params.get('items');

    if (entryDate) {
        $("#date").val(entryDate);
    }

    if (!items) return;

    items = JSON.parse(items);
   
    if (items.length === 0) return;

    // STEP 1: REMOVE DEFAULT FIRST ROW
    $("#tr_1").remove();

    let optionHtml = `<?php echo addslashes($items_list); ?>`;

    let rowCount = 0;

    items.forEach(function (item) {

        rowCount++;

        let srn = rowCount;

        // SELECT ITEM
        let options = optionHtml.replace(
            'value="' + item.item_id + '"',
            'value="' + item.item_id + '" selected'
        );

        let row = `
        <tr id="tr_${rowCount}" class="font-14 font-heading bg-white">

            <td>${srn}</td>

            <td>
                <div class="d-flex align-items-center">
                    <select class="form-control consume_item select2-single"
                            name="consume_item[]"
                            data-id="${rowCount}"
                            id="consume_item_${rowCount}">
                        ${options}
                    </select>

                    <button type="button" class="btn btn-outline-secondary p-1 px-2 configure-size-btn" data-id="${rowCount}">⚙️</button>

                    <input type="hidden" name="item_size_info[]" id="item_size_info_${rowCount}">
                </div>
            </td>

            <td>
                <input type="number"
                       name="consume_weight[]"
                       class="form-control consume_weight"
                       data-id="${rowCount}"
                       id="consume_weight_${rowCount}"
                       value="${item.qty}"
                       style="text-align:right;">
            </td>

            <td>
                <input type="text"
                       class="form-control consume_unit"
                       id="consume_unit_tr_${rowCount}"
                       readonly style="text-align:center;" name="consume_unit_name[]">

                <input type="hidden"
                       class="consume_units"
                       name="consume_units[]"
                       id="consume_units_tr_${rowCount}">
            </td>

            <td>
                <input type="number"
                       name="consume_price[]"
                       class="form-control consume_price"
                       data-id="${rowCount}"
                       id="consume_price_${rowCount}"
                       value="${item.rate}"
                       style="text-align:right;">
            </td>

            <td>
                <input type="text"
                       name="consume_amount[]"
                       class="form-control consume_amount"
                       id="consume_amount_${rowCount}"
                       readonly style="text-align:right;">
            </td>

            <td></td>
        </tr>
        `;

        $("#consum_total").before(row);
    });

    // INIT SELECT2 AGAIN
    $(".select2-single").select2();

    // 🔥 IMPORTANT: TRIGGER CHANGE → THIS FILLS UNIT + PRICE LOGIC
    setTimeout(() => {
        $(".consume_item").trigger('change');

        // 🔥 TRIGGER AMOUNT CALCULATION
        $(".consume_weight").trigger('keyup');
        $(".consume_price").trigger('keyup');
    }, 300);

});

</script>
@endsection