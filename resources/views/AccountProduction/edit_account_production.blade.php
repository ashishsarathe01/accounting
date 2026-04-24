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
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Manage Consumption</h5>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-production.update',$production->id) }}">
               @method('put')
               @csrf
               <div class="row">
                  <div class="mb-3 col-md-3">
                     <label for="series_no" class="form-label font-14 font-heading">Series</label>
                     <select id="series_no" name="series_no" class="form-select" required style="pointer-events:none;">
                        <option value="">Select</option>
                        @foreach($series_list as $key => $value)
                           <option value="{{$value->series}}" data-invoice_start_from="" data-invoice_prefix="" data-manual_enter_invoice_no="1" data-duplicate_voucher="" data-blank_voucher="" @if($value->series==$production->series_no) selected  @endif >{{ $value->series }}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="date" class="form-label font-14 font-heading">Date</label>
                     <input type="date" readonly
                              id="date" 
                              class="form-control " 
                              name="date" 
                              required 
                              value="{{$production->production_date}}">
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="voucher_prefix" class="form-label font-14 font-heading">Production No.</label>
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" readonly style="text-align: right;" placeholder="Voucher No" value="{{$production->voucher_no_prefix}}">
                     <input type="hidden" class="form-control" id="voucher_no" name="voucher_no" value="{{$production->voucher_no_prefix}}">
                     
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="material_center" class="form-label font-14 font-heading">Material Center</label>
                     <select class="form-select" name="material_center" id="material_center" required style="pointer-events:none;">
                        <option value="">Select Material Center</option>
                        @foreach($series_list as $key => $value)
                           <option value="{{ $value->mat_center }}" @if ($value->mat_center==$production->material_center) selected  @endif>{{ $value->mat_center }}</option>
                        @endforeach
                     </select> 
                  </div>
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr><th colspan="7" style="text-align:center">ITEMS CONSUMED</th></tr>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="width: 36%;">DESCRIPTION OF GOODS</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;padding-right: 24px;">QUANTITY</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: center;">UNIT</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Amount</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $consume_index = 1; @endphp
                        @foreach($production->productionDetail as $key => $value)
                           @if ($value->new_item!='')
                              @continue;
                           @endif
                           <tr id="tr_{{$consume_index}}" class="font-14 font-heading bg-white consumed_row">
                              <td class="w-min-50" id="consume_srn_{{$consume_index}}">{{$consume_index}}</td>
                              <td class="w-min-50">
                                 <select class="form-select consume_item select2-single" name="consume_item[]" data-id="{{$consume_index}}" id="consume_item_{{$consume_index}}">
                                    <option value="">Select Item</option>
                                            @foreach($items as $item)
                                                <option 
                                                    value="{{ $item->id }}"
                                                    data-unit_id="{{ $item->u_name }}"
                                                    data-unit_name="{{ $item->unit }}" @if($item->id==$value->consume_item) selected  @endif>
                                                    {{ $item->name }}
                                                </option>
                                            @endforeach
                                 </select>
                              </td>
                              <td class="">
                                 <input type="number" step="any" name="consume_weight[]" class="form-control consume_weight" data-id="{{$consume_index}}" id="consume_weight_{{$consume_index}}" placeholder="Weight" style="text-align: right;" value="{{$value->consume_weight}}">
                              </td>
                              <td class="w-min-50">                              
                                 <input type="text" class="w-100 form-control consume_unit" id="consume_unit_tr_{{$consume_index}}" readonly style="text-align:center;" data-id="{{$consume_index}}" name="consume_unit_name[]" value="{{$value->consume_item_unit_name}}">
                                 <input type="hidden" class="consume_units w-100" name="consume_units[]" id="consume_units_tr_{{$consume_index}}" value="{{$value->consume_item_unit}}">
                              </td>
                              <td class="">
                                 <input type="number" step="any" name="consume_price[]" class="form-control consume_price" data-id="{{$consume_index}}" id="consume_price_{{$consume_index}}" placeholder="Price" style="text-align: right;" value="{{$value->consume_price}}">
                              </td>
                              <td class="">
                                 <input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="{{$consume_index}}" id="consume_amount_{{$consume_index}}" placeholder="Amount" readonly style="text-align: right;" value="{{$value->consume_amount}}">
                              </td>
                              <td class="">
                                 @if($consume_index==1)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more" width="24" data-id="{{$consume_index}}" data-index="{{$consume_index}}" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                    </svg>
                                 @endif

                              </td>
                           </tr>
                           @php $consume_index++; @endphp
                        @endforeach                        
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
                  <!-- Electricity Consumption Table -->
                  <table id="electricity_table" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr>
                           <th colspan="7" style="text-align:center">ELECTRICITY CONSUMED</th>
                        </tr>
                        <tr class="font-12 text-body bg-light-pink">
                           <th style="width: 5%;">S.No.</th>
                           <th style="width: 15%;">Description</th>
                           <th style="width: 20%; text-align:right;">Consumed Units (Day)</th>
                           <th style="width: 20%; text-align:right;">Consumed Units (Night)</th>
                           <th style="width: 10%; text-align:right;">Price (₹/unit)</th>
                           <th style="width: 14%; text-align:right;">Consumed Units</th>
                           <th style="width: 20%; text-align:right;">Amount (₹)</th>
                        </tr>
                     </thead>
                     <tbody>
                        <!-- Electricity Row -->
                        <tr id="electricity_row_1" class="font-14 font-heading bg-white">
                           <td>1</td>
                           <td>
                              <input type="text" class="form-control" id="electricity_desc" name="electricity_desc" value="Electricity" readonly  />
                              <input type="hidden" id="previous_units" value="{{ $previousElectricity->electricity_units ?? 0 }}">
                           </td>
                           <td>
                              <input type="number" step="any" class="form-control text-end" id="electricity_consumed_units_day" name="electricity_consumed_units_day"  value="{{$electricity->electricity_units}}"/>
                           </td>
                           <td>
                              <input type="number" step="any" class="form-control text-end" id="electricity_consumed_units_night" name="electricity_consumed_units_night"  value="{{$electricity->electricity_unit_night}}"/>
                           </td>
                           <td>
                              <input type="number" step="any" class="form-control text-end" id="electricity_unit_price" name="electricity_unit_price" value="{{ $electricity->unit_price ?? 0 }}" />
                           </td>
                           <td>
                              <input type="number" step="any" class="form-control text-end" id="electricity_consumed_total_units" name="electricity_consumed_total_units" readonly  value="{{ $electricity->electricity_units + $electricity->electricity_unit_night}}"/>
                           </td>
                           <td>
                              <input type="number" step="any" class="form-control text-end" id="electricity_amount" name="electricity_amount" readonly value="{{ ($electricity->electricity_units + $electricity->electricity_unit_night)*$electricity->unit_price}}"/>
                           </td>
                        </tr>
                        <!-- Fixed Cost Row -->
                        <tr id="electricity_row_2" class="font-14 font-heading bg-white">
                           <td>2</td>
                           <td><input type="text" class="form-control" value="Fixed Cost" readonly name="fixed_cost_head" id="fixed_cost_head" /></td>
                           <td colspan="4"></td>
                           <td>
                           <input type="number" step="any" class="form-control text-end" id="fixed_cost" name="fixed_cost" value="{{ $electricity->fixed_cost ?? 0 }}" />
                           </td>
                        </tr>
                        <!-- Total Row -->
                        <tr id="electricity_total_row" class="font-14 font-heading bg-white fw-bold">
                           <td></td>
                           <td>Grand Total</td>
                           <td></td>
                           <td></td>
                           <td></td>
                           <td></td>
                           <td id="electricity_total" class="text-end">{{ ($electricity->electricity_units + $electricity->electricity_unit_night)*$electricity->unit_price + $electricity->fixed_cost  }}</td>
                        </tr>
                     </tbody>
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
                     <tbody id="generated_items_table">
                        <input type="hidden" id="out_poproll_weight" name="out_poproll_weight">
                        <input type="hidden" id="out_poproll_price" name="out_poproll_price">
                        @php $rowId = 0; $generated_item_quantity_total = 0; $generated_item_amount_total = 0; @endphp
                        @foreach($production->productionDetail as $index => $item)
                           @if ($item->consume_item!='')
                              @continue;
                           @endif
                           @php $rowId = $rowId + 1; 
                           $generated_item_quantity_total += $item->new_weight;
                           $generated_item_amount_total += $item->new_amount;
                           @endphp
                           <tr class="font-14 font-heading bg-white">
                              <td class="w-min-50" id="generated_srn_{{ $rowId }}">{{ $rowId }}</td>
                              <td>
                                 <input type="text"
                                 class="form-control generated_item_name"
                                 name="generated_item[]"
                                 id="generated_item_{{ $rowId }}"
                                 data-id="{{ $rowId }}"
                                 value="{{ $item->item_name }}"
                                 readonly>
                                 <input type="hidden" name="generated_item_id[]" value="{{ $item->new_item }}" id="generated_item_id_{{ $rowId }}">
                              </td>
                              <td>
                                 <input type="number"
                                    name="generated_weight[]"
                                    class="form-control generated_weight"
                                    id="generated_weight_{{ $rowId }}"
                                    data-id="{{ $rowId }}"
                                    value="{{ $item->new_weight }}"
                                    style="text-align: right;"
                                    readonly step="any">
                              </td>
                              <td class="w-min-50">
                                 <input type="text"
                                    class="w-100 form-control generated_unit"
                                    id="generated_unit_tr_{{ $rowId }}"
                                    data-id="{{ $rowId }}"
                                    value="{{ $item->new_item_unit_name }}"
                                    readonly
                                    style="text-align:center;"
                                    name="generated_unit_name[]"/>
                                 <input type="hidden"
                                    class="generated_units w-100"
                                    name="generated_units[]"
                                    id="generated_units_tr_{{ $rowId }}"
                                    value="{{ $item->new_item_unit }}" />
                              </td>
                              <!-- Price -->
                              <td>
                                 <input type="text"
                                    name="generated_price[]"
                                    class="form-control generated_price"
                                    id="generated_price_{{ $rowId }}"
                                    data-id="{{ $rowId }}"
                                    placeholder="Price"
                                    style="text-align: right;" value="{{$item->new_price}}">
                              </td>
                              <!-- Amount -->
                              <td>
                                 <input type="text"
                                    name="generated_amount[]"
                                    class="form-control generated_amount"
                                    id="generated_amount_{{ $rowId }}"
                                    data-id="{{ $rowId }}"
                                    placeholder="Amount"
                                    readonly
                                    style="text-align: right;" value="{{$item->new_amount}}">
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                     <tr id="generated_total"></tr>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold"></td>
                           <td class="fw-bold">Total</td>
                           <td class="fw-bold" id="consume_weight_total1" style="text-align: right;">{{$generated_item_quantity_total}}</td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold" id="consume_amount_total1" style="text-align: right;">{{$generated_item_amount_total}}</td>
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
                     <!--<a href="{{ route('consumption.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>-->
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
</body>
@include('layouts.footer')
<script>
   
   $(document).ready(function(){
      calculateConsumeGrandTotal();
   });
   //Calculate Consume Quantity Total , Consume Amount Total and Grand Total
   function calculateConsumeGrandTotal(){
      let consume_weight_total = 0;
      let consume_amount_total = 0;
      let grand_total = 0;
      let srn = 1;
      $(".consume_item").each(function(){
         var id = $(this).data('id');
         var weight = parseFloat($("#consume_weight_"+id).val()) || 0;
         var price = parseFloat($("#consume_price_"+id).val()) || 0;
         var amount = weight * price;
         $("#consume_amount_"+id).val(amount.toFixed(2));
         consume_weight_total += weight;
         consume_amount_total += amount;
         $("#consume_srn_"+id).text(srn);
         srn++;
      });
      $("#consume_weight_total").text(consume_weight_total.toFixed(2));
      $("#consume_amount_total").text(consume_amount_total.toFixed(2));
      grand_total += parseFloat(consume_amount_total);
      let electricity_amount = parseFloat($("#electricity_amount").val()) || 0;
      let fixed_cost = parseFloat($("#fixed_cost").val()) || 0;
      grand_total += electricity_amount + fixed_cost;
      $("#electricity_total").text(grand_total.toFixed(2));
   }
    //Calculate Electricity Amount and Grand Total
   function calculateElectricity() {
      let electricity_consumed_units_day = parseFloat($("#electricity_consumed_units_day").val()) || 0;
      let electricity_consumed_units_night = parseFloat($("#electricity_consumed_units_night").val()) || 0;
      let total_consumed_units = parseFloat(electricity_consumed_units_day) + parseFloat(electricity_consumed_units_night)
      let price = parseFloat($("#electricity_unit_price").val()) || 0;
      let fixed = parseFloat($("#fixed_cost").val()) || 0;
      let amount = 0;
      let electricity_amount = total_consumed_units * price;
      $("#electricity_amount").val(electricity_amount.toFixed(2));
      $("#electricity_consumed_total_units").val(total_consumed_units.toFixed(2));
      calculateConsumeGrandTotal();
      calculateGeneratedItemPrice();
   }
   $(document).on('input', '.consume_weight, .consume_price', function(){
      calculateConsumeGrandTotal();
      calculateGeneratedItemPrice();
   });
   $(document).on('input', '#electricity_consumed_units_day, #electricity_consumed_units_night, #electricity_unit_price, #fixed_cost', function(){
      calculateElectricity();
   });
   //Add More Consume Item
   $(document).on("click", ".add_more", function () {
      let add_more_count = $(this).data("index");
      let empty_status = 0;
      $('.consume_item').each(function(){   
         let i = $(this).attr('data-id');
         if($(this).val()=="" || $("#consume_weight_"+i).val()=="" || $("#consume_price_"+i).val()==""){
            empty_status = 1;
         }
      });
      if(empty_status==1){
         alert("Please enter required fields");
         return;
      }
      add_more_count++;
      $(".add_more").data("index", add_more_count);
      var $curRow = $("#consum_total").closest('tr');
      var optionElements = '<?php echo $items_list;?>';
      newRow = `<tr id="tr_${add_more_count}" class="font-14 font-heading bg-white">
         <td class="w-min-50" id="consume_srn_${add_more_count}">
            ${add_more_count}
         </td>

      <td class="">
         <select class="form-select consume_item select2-single" name="consume_item[]" data-id="${add_more_count}" id="consume_item_${add_more_count}">
            ${optionElements}
         </select>
      </td>
      <td class="">
         <input type="number"
               name="consume_weight[]"
               class="form-control consume_weight"
               data-id="${add_more_count}"
               id="consume_weight_${add_more_count}"
               placeholder="Weight"
               style="text-align: right;" step="any">
      </td>
      <td class="w-min-50">
         <input type="text"
               class="w-100 form-control consume_unit"
               id="consume_unit_tr_${add_more_count}"
               readonly
               style="text-align:center;"
               data-id="${add_more_count}"
               name="consume_unit_name[]">

         <input type="hidden"
               class="consume_units w-100"
               name="consume_units[]"
               id="consume_units_tr_${add_more_count}">
      </td>
      <td class="">
         <input type="number"
               name="consume_price[]"
               class="form-control consume_price"
               data-id="${add_more_count}"
               id="consume_price_${add_more_count}"
               placeholder="Price"
               style="text-align: right;" step="any">
      </td>
      <td class="">
         <input type="text"
               name="consume_amount[]"
               class="form-control consume_amount"
               data-id="${add_more_count}"
               id="consume_amount_${add_more_count}"
               placeholder="Amount"
               readonly
               style="text-align: right;">
      </td>
      <td>
         <!-- Remove button -->
         <svg xmlns="http://www.w3.org/2000/svg"
            width="16" height="16"
            fill="currentColor"
            class="bi bi-file-minus-fill remove"
            data-id="${add_more_count}"
            style="color:red; cursor:pointer;"
            viewBox="0 0 16 16">
            <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
         </svg>
      </td>
      </tr>`;
      $curRow.before(newRow);
      $( ".select2-single, .select2-multiple" ).select2();      
   });
   $(document).on("click", ".remove", function(){
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();

      calculateConsumeGrandTotal();
   });
   
   var generated_items_quantity = [];
   var consume_items_quantity = [];
   getConsumptionItems();
   function getConsumptionItems() {
      let base_url = "{{ url('/') }}"; // Laravel base URL
      let allRequests = [];
      var generatedData = [];
      // Step 1: Collect all generated items and weights
      $(".generated_weight").each(function () {
         let row = $(this).closest("tr");
         let generatedItemId = row.find("[name='generated_item_id[]']").val();
         let generatedWeight = parseFloat($(this).val()) || 0;
         if (generatedItemId && generatedWeight > 0) {
            generatedData.push({ generatedItemId, generatedWeight });
         }
      });
      if (generatedData.length === 0) {
         $("#example11 tbody tr.consumed_row").remove();
         return;
      }      
      // Step 2: Make AJAX request for each generated item
      generatedData.forEach(item => {
         allRequests.push(
            $.ajax({
               url: base_url + "/get-consumption-items/" + item.generatedItemId,
               type: "GET",
               dataType: "json",
               success: function (res) {
                  if (!res || !res.status) {
                     //console.error("Invalid API response for item:", item.generatedItemId);
                     return;
                  }
                  item.consumptionItems = res.items;
                  item.perKg = parseFloat(res.per_kg ?? 0);
                  item.variance = parseFloat(res.variance_rate ?? 0);
               },
               error: function (xhr, status, error) {
                  console.error("AJAX Error for item", item.generatedItemId, error);
               }
            })
         );
      });
      $.when.apply($, allRequests).done(function () {
         let totalItems = [];
         // Step 4: Calculate total required quantity per item
         generatedData.forEach(itemData => {
            let factor = (itemData.generatedWeight / itemData.perKg) || 0;
            if(itemData.consumptionItems){
               itemData.consumptionItems.forEach(ci => {
                  let qty = ci.consumption_rate * factor;
                  qty = parseFloat(qty.toFixed(3));
                    
                  generated_items_quantity.push({"generated_item_id":itemData.generatedItemId,'consumed_item_id':ci.item_id,"quantity":qty,'generatedWeight':itemData.generatedWeight});
               });
            }
         });
         //console.log(generated_items_quantity);

         generated_items_quantity.forEach(i => {
            if (totalItems[i.consumed_item_id] !== undefined) {
               totalItems[i.consumed_item_id] += i.quantity; // sum if exists
            } else {
               totalItems[i.consumed_item_id] = i.quantity; // initialize
            }
         });
         
         generated_items_quantity.forEach(i => {
            i.consume_average =   (i.quantity * 100) / totalItems[i.consumed_item_id];
            i.consume_average = parseFloat(i.consume_average.toFixed(2));
            i.consume_total_qty = totalItems[i.consumed_item_id];
         });
         // console.log(generated_items_quantity);
         // let grouped = generated_items_quantity.reduce((acc, item) => {
         //    let key = item.consumed_item_id;        
         //    if (!acc[key]) {
         //       acc[key] = [];
         //    }        
         //    acc[key].push(item);
         //    return acc;
         // }, {});
         
         // consume_items_quantity = Object.values(grouped).map(items => {
         //    return {
         //       consumed_item_id: items[0].consumed_item_id,
         //       total_qty: items.reduce((sum, item) => sum + item.quantity, 0),
         //       generated_items: items.map(i => ({generated_item_id: i.generated_item_id, quantity: i.quantity, consume_average: i.consume_average}))
         //    };
         // });
         
      });
   }
   
   function calculateGeneratedItemPrice(){
      
     //return;
      let electricity_amount = $("#electricity_amount").val();
      let fixed_cost = $("#fixed_cost").val();
      let electricity_fixed_amount = parseFloat(electricity_amount) + parseFloat(fixed_cost);
      let totalWeight = 0;
      $(".generated_weight").each(function(){
         totalWeight += parseFloat($(this).val()) || 0;
      });
      let electricity_fixed_average =  electricity_fixed_amount/totalWeight;
      electricity_fixed_average = electricity_fixed_average.toFixed(2);
      let consume_item_price_arr = []; let consume_item_weight_arr = [];
      $(".consume_item").each(function(){
         if($(this).val()!=""){
            consume_item_price_arr[$(this).val()] = $("#consume_price_"+$(this).attr('data-id')).val();
         }
         let cweight = parseFloat($("#consume_weight_"+$(this).attr('data-id')).val()) || 0;
         
         consume_item_weight_arr[$(this).val()] = cweight;
      });
      console.log(consume_item_weight_arr)
      generated_items_quantity.forEach(i => { 
         if(consume_item_weight_arr[i.consumed_item_id]){            
            i.consume_weight_percentage = (consume_item_weight_arr[i.consumed_item_id]*i.consume_average)/100;
            if(i.consumed_item_id==159){
               console.log("Item ID 159 Weight: " + consume_item_weight_arr[i.consumed_item_id]+"..."+i.consume_average+"...."+i.consume_weight_percentage);
            }
         }
      });
      setTimeout(function() {
            let grouped = generated_items_quantity.reduce((acc, item) => {
            let key = item.generated_item_id;        
            if (!acc[key]) {
               acc[key] = [];
            }        
            acc[key].push(item);
            return acc;
         }, {});
      console.log(grouped);
       //return;
      $(".generated_price").each(function(){
            let id = $(this).data('id');
            // Update corresponding amount
            let weight = parseFloat($("#generated_weight_" + id).val()) || 0;
            //Calculate Avearge Price based on Item
            let generated_item_id =  $("#generated_item_id_"+id).val();
            let generated_item_amount = 0
            Object.entries(grouped).forEach(([generatedItemId, items]) => {
               if(generatedItemId==generated_item_id){
                  items.forEach(item => {
                        if(consume_item_price_arr[item.consumed_item_id] && consume_item_price_arr[item.consumed_item_id]!='' && consume_item_price_arr[item.consumed_item_id]!=undefined){
                           generated_item_amount = parseFloat(generated_item_amount) + (item.consume_weight_percentage*consume_item_price_arr[item.consumed_item_id]);
                        }
                  });
               }
            }); 
            generated_item_amount = parseFloat(generated_item_amount) + (weight*electricity_fixed_average);
            generated_item_amount = generated_item_amount.toFixed(2);
            pricePerUnit = generated_item_amount/weight;
            pricePerUnit = pricePerUnit.toFixed(2);
            $(this).val(pricePerUnit);
            $("#generated_amount_" + id).val((weight * pricePerUnit).toFixed(2));
      });   
      }, 1000);
       
   }
    $(document).on('keyup','.generated_price',function(){
      let id = $(this).attr('data-id');
      let amount = parseFloat($("#generated_weight_"+id).val()) * parseFloat($(this).val());
      amount = amount.toFixed(2);
      $("#generated_amount_"+id).val(amount);
      let grand_total = 0;
      $(".generated_amount").each(function(){
          grand_total = parseFloat(grand_total) + parseFloat($(this).val());
      })
      $("#consume_amount_total1").html(grand_total);
      //calculateAmountNew(id);
   });
</script>
@endsection