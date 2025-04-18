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
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Stock Journal</h5>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('save-stock-journal') }}">
               @csrf
               <div class="row">
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
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required value="{{$date}}" >
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
                        <tr><th colspan="7" style="text-align:center">ITEMS CONSUMED</th></tr>
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
                        <tr id="tr_1" class="font-14 font-heading bg-white">
                           <td class="w-min-50" id="consume_srn_1">1</td>
                           <td class="w-min-50">
                              <select class="form-control consume_item select2-single" name="consume_item[]" data-id="1" id="consume_item_1">
                                 <option value="">Select Item</option>      
                                 @foreach($items as $item)
                                    <option value="{{$item->id}}" data-unit_id="{{$item->u_name}}"  data-unit_name="{{$item->unit}}"  >{{$item->name}}</option>
                                 @endforeach
                              </select>
                           </td>
                           <td class="">
                              <input type="number" name="consume_weight[]" class="form-control consume_weight" data-id="1" id="consume_weight_1" placeholder="Weight" style="text-align: right;">
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
                           <td class="">
                              <select class="form-control generated_item select2-single" name="generated_item[]" data-id="1" id="generated_item_1">
                                 <option value="">Select Item</option>      
                                 @foreach($items as $item)
                                    <option value="{{$item->id}}" data-unit_id="{{$item->u_name}}"  data-unit_name="{{$item->unit}}">{{$item->name}}</option>
                                 @endforeach
                              </select>
                           </td>
                           <td class="">
                              <input type="number" name="generated_weight[]" class="form-control generated_weight" data-id="1" id="generated_weight_1" placeholder="Weight" style="text-align: right;">
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
</body>
@include('layouts.footer')
<script>

   $(document).ready(function(){
      $("#series_no").change();
      $( ".select2-single, .select2-multiple" ).select2(); 
   })
   
   var add_more_count = 1;
   $(".add_more").click(function() {
      let empty_status = 0;
      $('.consume_item').each(function(){   
         let i = $(this).attr('data-id');
         if($(this).val()=="" || $("#consume_weight_"+i).val()=="" || $("#consume_price_"+i).val()==""){
               empty_status=1;            
         }                   
      });
      if(empty_status==1){
         alert("Please enter required fields");
         return;
      }
      let srn = $("#consume_srn_"+add_more_count).html();
      srn++
      add_more_count++;
      var $curRow = $("#consum_total").closest('tr');
      var optionElements = '<?php echo $items_list;?>';
      newRow = '<tr id="tr_'+add_more_count+'" class="font-14 font-heading bg-white"><td class="w-min-50" id="consume_srn_'+add_more_count+'">' + srn + '</td><td class=""><select class="form-control consume_item select2-single" name="consume_item[]" data-id="'+add_more_count+'" id="consume_item_1'+add_more_count+'">'+optionElements+'</select></td><td class=""><input type="number" name="consume_weight[]" class="form-control consume_weight" data-id="'+add_more_count+'" id="consume_weight_'+add_more_count+'" placeholder="Weight" style="text-align: right;"></td><td class="w-min-50"><input type="text" class="w-100 form-control consume_unit" id="consume_unit_tr_'+add_more_count+'" readonly="" style="text-align:center;" data-id="'+add_more_count+'" name="consume_unit_name[]"><input type="hidden" class="consume_units w-100" name="consume_units[]" id="consume_units_tr_'+add_more_count+'"></td><td class=""><input type="number" name="consume_price[]" class="form-control consume_price" data-id="'+add_more_count+'" id="consume_price_'+add_more_count+'" placeholder="Price" style="text-align: right;"></td><td class=""><input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="'+add_more_count+'" id="consume_amount_'+add_more_count+'" placeholder="Amount" readonly style="text-align: right;"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
      $( ".select2-single, .select2-multiple" ).select2();
      
   });
   var add_more_count1 = 1;
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
      srn++
      add_more_count1++;
      var $curRow = $("#generated_total").closest('tr');
      var optionElements = '<?php echo $items_list;?>';
      newRow = '<tr id="tr1_'+add_more_count1+'" class="font-14 font-heading bg-white"><td class="w-min-50" id="generated_srn_'+add_more_count1+'">'+srn+'</td><td class=""><select class="form-control generated_item select2-single" name="generated_item[]" data-id="'+add_more_count1+'" id="generated_item_1'+add_more_count1+'">'+optionElements+'</select></td><td class=""><input type="number" name="generated_weight[]" class="form-control generated_weight" data-id="'+add_more_count1+'" id="generated_weight_'+add_more_count1+'" placeholder="Qty" style="text-align: right;"></td><td class="w-min-50"><input type="text" class="w-100 form-control generated_unit" id="generated_unit_tr_'+add_more_count1+'" readonly="" style="text-align:center;" data-id="'+add_more_count1+'" name="generated_unit_name[]"/><input type="hidden" class="generated_units w-100" name="generated_units[]" id="generated_units_tr_'+add_more_count1+'"></td><td class=""><input type="number" name="generated_price[]" class="form-control generated_price" data-id="'+add_more_count1+'" id="generated_price_'+add_more_count1+'" placeholder="Price" style="text-align: right;"></td><td class=""><input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="'+add_more_count1+'" id="generated_amount_'+add_more_count1+'" placeholder="Amount" readonly style="text-align: right;"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove1" data-id="' + add_more_count1+ '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
      $( ".select2-single, .select2-multiple" ).select2();
   });
   $(document).on("click", ".remove", function(){
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      calculateAmount(1)
   });
   $(document).on("click", ".remove1", function(){
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
   function calculateAmountNew(id){
      let generated_price = $("#generated_price_"+id).val();
      let generated_weight = $("#generated_weight_"+id).val();
      let nweight = 0;
      $(".generated_weight").each(function(){
         if($(this).val()!=''){
            nweight = parseFloat(nweight) + parseFloat($(this).val());
         }
      });
      $("#consume_weight_total1").html(nweight);
      if(generated_price=="" || generated_weight==""){
         let namount = 0;
         $(".generated_amount").each(function(){
            if($(this).val()!=''){
               namount = parseFloat(namount) + parseFloat($(this).val());
            }
         });
         $("#consume_amount_total1").html(namount);
         return;
      }
      let amount = parseFloat(generated_price)*parseFloat(generated_weight);
      $("#generated_amount_"+id).val(amount.toFixed(2));

      let namount = 0;
      $(".generated_amount").each(function(){
         if($(this).val()!=''){
            namount = parseFloat(namount) + parseFloat($(this).val());
         }
      });
      $("#consume_amount_total1").html(namount);
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
   $(document).on('change', '.consume_item', function(){
      $('#consume_unit_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-unit_name'));
      $('#consume_units_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-unit_id'));
      if($(this).val()==""){
         $("#consume_weight_"+$(this).attr('data-id')).val('');
         $("#consume_price_"+$(this).attr('data-id')).val('');
         $("#consume_amount_"+$(this).attr('data-id')).val('');
         calculateAmount($(this).attr('data-id'))
      } 
   });
   $(document).on('change', '.generated_item', function(){
      $('#generated_unit_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-unit_name'));
      $('#generated_units_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-unit_id'));
      if($(this).val()==""){
         $("#generated_weight_"+$(this).attr('data-id')).val('');
         $("#generated_price_"+$(this).attr('data-id')).val('');
         $("#generated_amount_"+$(this).attr('data-id')).val('');
         calculateAmountNew($(this).attr('data-id'))
      } 
   });
</script>
@endsection