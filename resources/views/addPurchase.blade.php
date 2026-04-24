@extends('layouts.app')
@section('content')
@include('layouts.header')
@section('title', 'Add Purchase')
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
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
             @if (session('error'))
             <div class="alert alert-danger" role="alert"> {{session('error')}}
             </div>
             @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Purchase Voucher</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('purchase.store')}}" id="purchaseForm">
               @csrf
              <input type="hidden" id="spare_part_id" name="spare_part_id" value="" >
               <input type="hidden" name="vehicle_entry_id" value="{{ request('vehicle_entry_id') }}">
               <div class="row">
                  <input type="hidden" name="rowId" value="{{$rowId}}">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" name="series_no" class="form-select" required autofocus>
                        <option value="">Select</option>
                        <?php
                        if(count($GstSettings) > 0) {
                           foreach ($GstSettings as $value){ ?>
                              <option value="<?php echo $value->series;?>" data-mat_center="<?php echo $value->mat_center;?>" data-gst_no="<?php echo $value->gst_no;?>" data-invoice_start_from="<?php echo $value->invoice_start_from;?>" <?php if(count($GstSettings)==1) { echo "selected";} ?>><?php echo $value->series; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <ul style="color: red;">
                       @error('series_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date"id="date"class="form-control"name="date"value="{{ $invoice_date ?? $bill_date }}" min="{{ $fy_start_date }}" max="{{ $fy_end_date }}">
                     <ul style="color: red;">
                       @error('date'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  @if(isset($stockEntryEnabled) && $stockEntryEnabled == 1)
                  <div class="mb-3 col-md-3">
                     <label class="form-label font-14 font-heading">
                        Stock Entry Date
                     </label>
                     <input type="date"
                           class="form-control"
                           name="stock_entry_date"
                           id="stock_entry_date"
                           value="{{ old('stock_entry_date', $vehicleEntryDate ?? ($invoice_date ?? $bill_date)) }}">
                  </div>
                  @endif
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                      <input type="text"class="form-control"id="voucher_no"name="voucher_no"value="{{ $invoice_no ?? '' }}">
                      <input type="hidden" class="form-control" id="merchant_gst" name="merchant_gst">
                     <ul style="color: red;">
                       @error('voucher_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">PURCHASE TYPE</label>
                     <input type="text" class="form-control" id="purchase_type" name="purchase_type" placeholder="PURCHASE TYPE" readonly>
                  </div>
                  <div class="mb-4 col-md-5">
                     <label for="name" class="form-label font-14 font-heading">Party</label><br>
                     <select class="form-select select2-single" id="party_id" name="party_id" required data-modal="accountModal">
                        <option value="">Select </option>
                        <?php
                        foreach ($party_list as $value) { ?>
                           <option value="<?php echo $value->id; ?>" 
                              data-gstin="<?php echo $value->gstin; ?>" 
                              data-address="<?php echo $value->address.",".$value->pin_code; ?>" 
                              data-state_code="{{$value->state_code}}"
                              data-group="{{$value->under_group}}"
                              data-allow_without_gst="<?php echo $value->allow_without_gst ?? 0; ?>"
                              <?php if(isset($accountId) && $value->id==$accountId){ echo "selected";} ?>>
                              <?php echo $value->account_name; ?>
                           </option>
                           <?php 
                        } ?>
                     </select>
                     <p id="partyaddress" style="font-size: 9px;"></p>
                     <ul style="color: red;">
                       @error('party'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Material Center</label>
                     <select name="material_center" class="form-select" id="material_center" required>
                        <option value="">Select</option>
                        <?php
                        if(count($GstSettings) > 0) {
                           foreach ($GstSettings as $value){ ?>
                              <option value="<?php echo $value->mat_center;?>"  <?php if(count($GstSettings)==1) { echo "selected";} ?>><?php echo $value->mat_center; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <ul style="color: red;">
                       @error('material_center'){{$message}}@enderror
                     </ul> 
                  </div>
               </div>
               <div class=" transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="purchase_tbl" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-50 border-none bg-light-pink text-body" style="    width: 36%;">Description of Goods
                           </th>                           
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Qty</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: center;">Unit</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Amount</th>
                           <th class="w-min-50 border-none bg-light-pink text-body "></th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr id="tr_1" class="font-14 font-heading bg-white">
                           <td class="w-min-50">1</td>
                           <td class="">
                              <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_1" data-id="1" data-modal="itemModal">
                                 <option value="">Select Item</option>
                                 @foreach($items as $item_list)
                                    <option value="{{$item_list->id}}" data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}"  data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>
                                 @endforeach
                              </select>
                           </td>                           
                           <td class=" w-min-50">
                              <input type="number" step="any" class="quantity w-100 form-control" id="quantity_tr_1" name="qty[]" placeholder="Quantity" style="text-align:right;" value="@isset($in_quantity){{$in_quantity}}@endisset"/>
                           </td>
                           <td class=" w-min-50">
                              <input type="text" class="w-100 form-control unit" id="unit_tr_1" readonly style="text-align:center;" data-id="1"/>
                              <input type="hidden" class="units" name="units[]" id="units_tr_1" />
                           </td>
                           <td class=" w-min-50">
                              <input type="number" step="any" class="price form-control" id="price_tr_1" name="price[]" data-id="1" placeholder="Price" style="text-align:right;" value="@isset($in_price){{$in_price}}@endisset"/>
                           </td>
                           <td class=" w-min-50">
                              <input type="number" step="any" id="amount_tr_1" class="amount w-100 form-control" name="amount[]"  data-id="1" placeholder="Amount" style="text-align:right;" />
                           </td>
                           <td class="" style="display:flex">
                           <svg xmlns="http://www.w3.org/2000/svg" data-id="1"class="bg-primary rounded-circle add_more_wrapper" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;" tabindex="0" role="button"> <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                           </td>
                           <input type="hidden" name="item_parameters[]" id="item_parameters_1">
                           <input type="hidden" name="config_status[]" id="config_status_1">
                           
                        </tr>
                     </tbody>
                     
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-50 fw-bold"></td>
                           <td class="fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold">Total</td>
                           <td class="w-min-50 fw-bold">
                              <span id="totalSum" style="float:right;"></span>
                              <input type="hidden" name="taxable_amt" id="total_taxable_amounts" value="0">
                           </td>
                        </tr>
                     </div>
                  </table>
               </div>
               <div class="row">
                  <div class="col-lg-5">
                     <div class="transaction-table transacton-extra-table bg-white table-view shadow-sm border-radius-8 mb-4">
                        <table id="transcton-sale3" class="table-striped table m-0 shadow-sm table-bordered">
                           <thead>
                              <tr class=" font-12 text-body bg-light-pink ">
                                 <th class="border-none bg-light-pink text-body">Tax Rate </th>
                                 <th class="border-none bg-light-pink text-body ">Taxable Amt.</th>
                                 <th class="border-none bg-light-pink text-body ">Tax </th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr class="font-14 font-heading bg-white">
                                 <td class=""><span></span></td>
                                 <td class=""><span></span></td>
                                 <td class=""><span></span></td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
                  <div class="col-lg-7">
                     <div class="transaction-table transacton-extra-table bg-white table-view shadow-sm border-radius-8 mb-4">
                        <table id="sundry_up_table" class="table-striped table m-0 shadow-sm table-bordered">
                           <thead>
                              <tr class=" font-12 text-body bg-light-pink">
                                 <th class="w-min-50 border-none bg-light-pink text-body " style="padding-left:24px">Bill Sundry</th>
                                 <th class="w-min-50 border-none bg-light-pink text-body ">@</th>
                                 <th class="border-none bg-light-pink text-body " style="width: 29%;text-align: right;padding-right: 24px;">Amount (Rs.)</th>
                                 <th></th>
                              </tr>
                           </thead>
                           <tbody class="testWrapper">
                              <tr id="billtr_1" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_1" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="1">
                                       <option value="">Select</option>
                                       <?php
                                       foreach ($billsundry as $value) {
                                          if($value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
                                             <option value="<?php echo $value->id;?>" <?php if(isset($bill_sundry_id) && $bill_sundry_id == $value->id) echo 'selected'; ?> data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_1" id="sundry_option_<?php echo $value->id;?>_1" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_1"></span>
                                    <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_1">
                                 </td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" step="any" name="bill_sundry_amount[]" id="bill_sundry_amount_1" data-id="1" readonly style="text-align: right;" value="<?php echo isset($freight_amount) ? $freight_amount : ''; ?>">
                                 </td>
                                 <td>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more_bill_sundry_up" tabindex="0" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor:pointer">
                                    <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                                 </td>
                              </tr>
                              
                              <tr id="billtr_cgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_cgst" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="cgst">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='CGST'){?>
                                          <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_cgst" id="sundry_option_cgst" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 "><span name="tax_amt[]" class="tax_amount" id="tax_amt_cgst"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_cgst"></td>
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_cgst" data-id="cgst" readonly style="text-align: right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_sgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_sgst" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="sgst">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='SGST'){
                                          ?>
                                          <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_sgst" id="sundry_option_sgst" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                          <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 "><span name="tax_amt[]" class="tax_amount" id="tax_amt_sgst"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_sgst"></td>
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_sgst" data-id="sgst" readonly style="text-align: right;" ></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_igst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_igst" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="igst">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='IGST'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_igst" id="sundry_option_igst" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 "><span name="tax_amt[]" class="tax_amount" id="tax_amt_igst"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_igst"></td>
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_igst" data-id="igst" readonly style="text-align: right;"></td>
                                 <td></td>
                              </tr>
                              <div class="plus-icon" >
                                 <tr class="font-14 font-heading bg-white" style="display: none;">
                                    <td class="w-min-120 " colspan="5" >
                                       <a class="add_more_bill_sundry_gst"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
                                    </td>
                                 </tr>
                              </div>
                              <!-- <tr id="billtr_2" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_2" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="2">
                                       <option value="">Select</option>
                                       <?php
                                       foreach ($billsundry as $value) {
                                          if($value->effect_gst_calculation==0){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_2" id="sundry_option_<?php echo $value->id;?>_2" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 "><span name="tax_amt[]" class="tax_amount" id="tax_amt_2"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_2"></td>
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_2" data-id="2" readonly style="text-align: right;"></td>
                                 <td></td>
                              </tr> -->
                              <!-- <div class="plus-icon">
                                 <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120 " colspan="5">
                                       <a class="add_more_bill_sundry_down"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
                                    </td>
                                 </tr>
                              </div> -->
                              <tr id="billtr_round_plus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_plus" class=" w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="round_plus">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='ROUNDED OFF (+)'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_round_plus" id="sundry_option_round_plus" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 "><span name="tax_amt[]" class="tax_amount" id="tax_amt_round_plus"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_round_plus"></td>
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_plus" data-id="round_plus" readonly style="text-align: right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_round_minus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_minus" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="round_minus">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='ROUNDED OFF (-)'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_round_minus" id="sundry_option_round_minus" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 "><span name="tax_amt[]" class="tax_amount" id="tax_amt_round_minus"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_round_minus"></td>
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_minus" data-id="round_minus" readonly style="text-align: right;"></td>
                                 <td></td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="fw-bold w-min-50">Total</td>
                                 <td class="w-min-50"></td>      
                                 <td class="fw-bold w-min-50">
                                    <span id="bill_sundry_amt" style="float: right;"></span>
                                    <input type="hidden" name="total" id="total_amounts" value="0">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                        
                        <table id="transcton-sale3" class="table-striped table m-0 shadow-sm table-bordered">
                           <tbody>
                              <div>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="4" class="pl-40"><button type="button" class="btn btn-info transport_info" style="float: right;">Transport Info</button></td>
                                 </tr>
                              </div>
                              <div class="modal fade" id="transport_info_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                 <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content p-4 border-divider border-radius-8">
                                       <div class="modal-header border-0 p-0">
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                       </div>
                                       <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                                       Transport Info
                                       </h5>
                                       <br>
                                       <div class="row">
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Vehicle No.</label>
                                             <input type="text"name="vehicle_no"class="form-control"value="{{ $vehicle_no ?? '' }}">
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Transport Name</label>
                                             <input type="text"name="transport_name"class="form-control"value="{{ $transport ?? '' }}">
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Reverse Charge</label>
                                             <select class="w-95-parsent form-select" id="reverse_charge" id="reverse_charge" name="reverse_charge">
                                                <option value="">Select</option>
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                             </select>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">GR/RR No.</label>
                                             <input type="text" id="gr_pr_no" name="gr_pr_no" class="form-control" placeholder="GR/RR No"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Station</label>
                                             <input type="text" id="station" name="station" class="form-control" placeholder="Station" value="{{$station}}"/>
                                          </div>
                                       </div>
                                       <br>
                                       <div class="text-start">
                                          <button type="button"  class="btn  btn-xs-primary save_transport_info">
                                                SAVE
                                          </button>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <div class="modal fade" id="parameter_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                     <div class="modal-content p-4 border-divider border-radius-8">
                        <div class="modal-header border-0 p-0">
                           <p><h5 class="modal-title">Parameterized Stock Details (Purchase Voucher)</h5></p>
                           <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div>Item : <span id="parameter_item"></span></div>
                        <div>Qty. In : <span id="parameter_qty"></span></div>
                        <div class="modal-body parameter_body">            
                        </div>
                        <input type="hidden" id="parameter_modal_id">
                        <input type="hidden" id="parameter_modal_qty">
                        <div class="modal-footer border-0 mx-auto p-0">
                           <button type="button" class="btn btn-border-body close" data-bs-dismiss="modal">CANCEL</button>
                           <button type="button" class="ms-3 btn btn-red parameter_save_btn" style="display:none">SUBMIT</button>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="mb-3">
               <label class="form-label fw-bold">Narration</label>
               <input 
                  type="text"
                  id="narration"
                  name="narration"
                  class="form-control"
                  placeholder="Enter narration for the entry...">
            </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <input type="submit" value="SAVE" class="btn btn-xs-primary" id="purchaseBtn">
                     <a href="{{ route('purchase.index') }}" class="btn  btn-black ">QUIT</a>
                  </div>
                  <input type="hidden" clas="max_sale_descrption" name="max_sale_descrption" value="1" id="max_sale_descrption">
                  <input type="hidden" name="max_sale_sundry" id="max_sale_sundry" value="1" />
               </div>
            </form>
         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key w-100">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help">F1
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Help</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Account">
                  <span class="border-bottom-black">F1</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Account</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Item">
                  <span class="border-bottom-black">F2</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Item</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Master">F3
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Master</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Voucher">
                  <span class="border-bottom-black">F3</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Voucher</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Payment">
                  <span class="border-bottom-black">F5</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Payment</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Receipt">
                  <span class="border-bottom-black">F6</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Receipt</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Journal">
                  <span class="border-bottom-black">F7</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Journal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Sales">
                  <span class="border-bottom-black">F8</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Sales</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Purchase">
                  <span class="border-bottom-black">F9</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Purchase</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Balance Sheet">
                  <span class="border-bottom-black">B</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Balance Sheet</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Trial Balance">
                  <span class="border-bottom-black">T</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Trial Balance</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Stock Status">
                  <span class="border-bottom-black">S</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Stock Status</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Acc. Ledger">
                  <span class="border-bottom-black">L</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Acc. Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Summary">
                  <span class="border-bottom-black">I</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Ledger">
                  <span class="border-bottom-black">D</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Summary">
                  <span class="border-bottom-black">G</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Switch User">
                  <span class="border-bottom-black">U</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Switch User</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configuration">
                  <span class="border-bottom-black">F</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Configuration</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lock Program">
                  <span class="border-bottom-black">K</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Lock Program</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Training Videos">
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Training Videos</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Portal">
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Portal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 text-ellipsis d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search Menu">
                  Search Menu
               </button>
            </div>
         </div>
      </div>
   </section>
</div>

<div class="modal fade" id="accountModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Account</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="accountForm">
          @csrf

          <div class="row">

            <div class="col-md-4 mb-3">
              <label>ACCOUNT NAME</label>
              <input type="text" id="modal_account_name" name="account_name" class="form-control" placeholder="ENTER ACCOUNT NAME" required>
            </div>

            <div class="col-md-4 mb-3">
              <label>PRINT NAME</label>
              <input type="text" id="modal_print_name" name="print_name" class="form-control" placeholder="ENTER PRINT NAME">
            </div>

            <div class="col-md-4 mb-3">
              <label>UNDER GROUP</label>
              <select name="under_group" class="form-select" required>
  <option value="">SELECT GROUP</option>
  @foreach($allowedAccountGroups as $group)
    <option value="{{ $group->id }}">{{ $group->name }}</option>
  @endforeach
</select>

<input type="hidden" name="under_group_type" id="modal_under_group_type" value="">
<input type="hidden" name="form_type" value="modal">
            </div>

            <div class="col-md-4 mb-3">
              <label>OPENING BALANCE</label>
              <input type="number" name="opening_balance" class="form-control" placeholder="ENTER OPENING BALANCE">
            </div>

            <div class="col-md-4 mb-3">
              <label>BALANCE TYPE</label>
              <select name="opening_balance_type" class="form-select">
                <option value="">SELECT BALANCE TYPE</option>
                <option value="debit">Debit</option>
                <option value="credit">Credit</option>
              </select>
            </div>

            <div class="col-md-4 mb-3">
              <label>GST NO.</label>
              <input type="text" id="modal_gstin" name="gstin" class="form-control" placeholder="ENTER GST NO.">
            </div>

            <div class="col-md-4 mb-3">
              <label>STATE</label>
              <select id="modal_state" class="form-select">
                <option value="">SELECT STATE</option>
                @foreach($state_list as $state)
                  <option value="{{ $state->id }}">
                    {{ $state->state_code }} - {{ $state->name }}
                  </option>
                @endforeach
              </select>
              <input type="hidden" name="state" id="modal_state_hidden">
            </div>

            <div class="col-md-8 mb-3">
              <label>ADDRESS</label>
              <textarea id="modal_address" name="address" class="form-control" placeholder="ENTER ADDRESS"></textarea>
            </div>

            <div class="col-md-4 mb-3">
              <label>PINCODE</label>
              <input type="number" id="modal_pincode" name="pincode" class="form-control" placeholder="ENTER PINCODE">
            </div>

            <div class="col-md-4 mb-3">
              <label>PAN</label>
              <input type="text" id="modal_pan" name="pan" class="form-control" placeholder="ENTER PAN">
            </div>

            <div class="col-md-4 mb-3">
              <label>SMS Send Status</label>
              <select name="sms_status" class="form-select">
                <option value="">Select</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
              </select>
            </div>

            <div class="col-md-4 mb-3">
              <label>Credit Days</label>
              <select name="credit_days" class="form-select">
  <option value="">Select</option>
  @foreach($credit_days as $cd)
    <option value="{{ $cd->days }}">{{ $cd->days }} Days</option>
  @endforeach
</select>

            </div>

            <div class="col-md-4 mb-3">
              <label>DUE DAYS</label>
              <input type="number" name="due_day" class="form-control" placeholder="ENTER DUE DAYS">
            </div>

            <div class="col-md-4 mb-3">
              <label>CONTACT PERSON</label>
              <input type="text" name="contact_person" class="form-control" placeholder="ENTER CONTACT PERSON">
            </div>

            <div class="col-md-4 mb-3">
              <label>MOBILE NO.</label>
              <input type="number" name="mobile_no" class="form-control" placeholder="ENTER MOBILE NO.">
            </div>

            <div class="col-md-4 mb-3">
              <label>WHATSAPP NO.</label>
              <input type="number" name="whatsapp_no" class="form-control" placeholder="ENTER WHATSAPP NO.">
            </div>

            <div class="col-md-4 mb-3">
              <label>E-MAIL ID</label>
              <input type="email" name="email" class="form-control" placeholder="ENTER E-MAIL ID">
            </div>

            <div class="col-md-4 mb-3">
              <label>BANK ACCOUNT NO.</label>
              <input type="number" name="account_no" class="form-control" placeholder="ENTER BANK ACCOUNT NO.">
            </div>

            <div class="col-md-4 mb-3">
              <label>BANK IFSC CODE</label>
              <input type="text" name="ifsc_code" class="form-control" placeholder="ENTER BANK IFSC CODE">
            </div>

            <div class="col-md-4 mb-3">
              <label>STATUS</label>
              <select name="status" class="form-select">
                <option value="1">Enable</option>
                <option value="0">Disable</option>
              </select>
            </div>

          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" id="saveAccountBtn" class="btn btn-primary">Save</button>
      </div>

    </div>
  </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

<form id="modalItemForm">
@csrf

<div class="row">

<!-- ================= PART A ================= -->
<div class="col-md-8">
  <h5>PART A</h5>
  <hr>

  <div class="row">

    <div class="mb-3 col-md-5">
      <label class="form-label font-14 font-heading">ITEM NAME</label>
      <input type="text" class="form-control"
             name="name" id="modal_name"
             placeholder="ENTER ITEM NAME" required>
    </div>

    <div class="mb-3 col-md-12"></div>

    <div class="mb-3 col-md-5">
      <label class="form-label font-14 font-heading">PRINT NAME</label>
      <input type="text" class="form-control"
             name="p_name" id="modal_p_name"
             placeholder="ENTER PRINT NAME">
    </div>

    <div class="mb-3 col-md-12"></div>

    <div class="mb-3 col-md-5">
      <label class="form-label font-14 font-heading">UNDER GROUP</label>
      <select class="form-select select2-single"
              name="g_name" id="modal_g_name" required>
        <option value="">SELECT GROUP</option>
        @foreach($itemGroups as $value)
          <option value="{{ $value->id }}">{{ $value->group_name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3 col-md-12"></div>

    @foreach($series as $key => $value)
  <div class="col-md-3 mb-3">
    <label class="form-label font-14 font-heading">BRANCH</label>
    <input type="text"
           class="form-control"
           name="series[]"
           value="{{ $value->series }}"
           readonly>
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label font-14 font-heading">
      OPENING BAL. (Rs.)
    </label>
    <input type="text"
           class="form-control"
           name="opening_amount[]"
           id="modal_opening_amount_{{ $key }}"
           placeholder="OPENING BALANCE"
           onkeyup="typevalidation({{ $key }})">
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label font-14 font-heading">
      OPENING BAL. (Qty.)
    </label>
    <input type="text"
           class="form-control"
           name="opening_qty[]"
           placeholder="OPENING BALANCE">
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label font-14 font-heading">
      BALANCE TYPE
      <span id="balance_type_required_{{ $key }}"
            style="color:red; display:none;">*</span>
    </label>
    <select class="form-select"
            name="opening_balance_type[]"
            id="opening_balance_type_{{ $key }}">
      <option value="">BALANCE TYPE</option>
      <option value="Debit">Debit</option>
      <option value="Credit">Credit</option>
    </select>
  </div>
@endforeach

    <div class="mb-3 col-md-12"></div>

    <div class="mb-3 col-md-3">
      <label class="form-label font-14 font-heading">UNIT NAME</label>
      <select class="form-select select2-single"
              name="u_name" id="modal_u_name" required>
        <option value="">SELECT UNIT</option>
        @foreach($accountunit as $value)
          <option value="{{ $value->id }}">{{ $value->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="mb-3 col-md-12"></div>

    <div class="mb-3 col-md-3">
      <label class="form-label font-14 font-heading">GST RATE</label>
      <select class="form-select select2-single"
              name="gst_rate" id="modal_gst_rate" required>
        <option value="">SELECT GST RATE</option>
        <option value="0" data-type="nil_rated">0% (Nil Rated Goods)</option>
        <option value="0" data-type="exempted">(Exempted Goods)</option>
        <option value="0.25" data-type="taxable">0.25% (Precious stones, etc.)</option>
        <option value="3" data-type="taxable">3% (Gold, jewelry)</option>
        <option value="5" data-type="taxable">5%</option>
        <option value="12" data-type="taxable">12%</option>
        <option value="18" data-type="taxable">18%</option>
        <option value="28" data-type="taxable">28%</option>
      </select>
      <input type="hidden" name="item_type" id="modal_item_type">
    </div>

    <div class="mb-3 col-md-3">
      <label class="form-label font-14 font-heading">HSN CODE</label>
      <input type="text" class="form-control"
             name="hsn_code" placeholder="ENTER HSN CODE" required>
    </div>

    <div class="mb-3 col-md-12"></div>

    <div class="mb-3 col-md-3">
      <label class="form-label font-14 font-heading">STATUS</label>
      <select class="form-select select2-single"
              name="status" required>
        <option value="">SELECT STATUS</option>
        <option value="1">Enable</option>
        <option value="0">Disable</option>
      </select>
    </div>

  </div>
</div>

<!-- ================= PART B ================= -->
<div class="col-md-4">
  <h5>
    <input type="checkbox" id="modal_partb"> PART B
  </h5>
  <hr>

  <div class="row">
    <div class="col-md-6 modal_partb_div" style="display:none">
      <label>
        <input type="checkbox" id="modal_tcs_applicable">
        TCS APPLICABLE
      </label>
    </div>

    <div class="col-md-12"></div>

    <div class="col-md-6 modal_tcs_div" style="display:none">
      <label>SECTION</label>
      <select class="form-select">
        <option value="">SELECT SECTION</option>
        <option value="206CE-Scarp" data-rate="1">206CE-Scarp</option>
      </select>
    </div>

    <div class="col-md-6 modal_tcs_div" style="display:none">
      <label>RATE OF TCS</label>
      <input type="text" class="form-control" readonly>
    </div>
  </div>
</div>

</div>
</form>

</div>


      <div class="modal-footer">
        <button type="button" id="saveItemBtn" class="btn btn-primary">
          Save Item
        </button>
      </div>

    </div>
  </div>
</div>
<div class="modal fade" id="gstAccountModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          Add GST for <span id="gst_modal_account_name"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="gst_modal_account_id">

        <!-- GST NO -->
        <div class="mb-3">
          <label class="form-label">GST No</label>
          <input type="text" class="form-control" id="gstin" placeholder="GST No">
        </div>

        <!-- STATE -->
        <div class="mb-3">
          <label class="form-label">State</label>
          <select class="form-select select2-single" id="state">
            <option value="">Select State</option>
            @foreach($state_list as $state)
              <option value="{{ $state->id }}"
                data-state_code="{{ $state->state_code }}">
                {{ $state->state_code }} - {{ $state->name }}
              </option>
            @endforeach
          </select>
          <input type="hidden" id="state_hidden">
        </div>

        <!-- ADDRESS -->
        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea class="form-control" id="address" placeholder="Address"></textarea>
        </div>

        <!-- PINCODE -->
        <div class="mb-3">
          <label class="form-label">Pincode</label>
          <input type="number" class="form-control" id="pincode" placeholder="Pincode">
        </div>

        <!-- PAN -->
        <div class="mb-3">
          <label class="form-label">PAN</label>
          <input type="text" class="form-control" id="pan" readonly placeholder="PAN">
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelGstModal">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" id="saveGstModal">
          Save GST
        </button>
      </div>

    </div>
  </div>
</div>
</body>
@include('layouts.footer')
<script>
   var bill_sundry_array = @json($billsundry);
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
   var customer_gstin = "";
   var merchant_gstin = "";
   var percent_arr = [];
   var add_more_count = 1;
   var add_more_counts = 1;
   var add_more_bill_sundry_up_count = 2;
   var parameter_assign_item_arr = [];
   var gst_disabled_for_party = 0;
   var noGSTGroups = @json($no_gst_group_ids);
   var partyGSTData = {};
   function addMoreItem() {
      let empty_status = 0;
      $('.item_id').each(function(){   
         let i = $(this).attr('data-id');

         if($(this).val()=="" || $("#quantity_tr_"+i).val()=="" || $("#price_tr_"+i).val()==""){
            empty_status = 1;            
         }                   
      });
      if(empty_status==1){
         alert("Please enter required fields");
         return;
      }
      add_more_count++;
      var optionElements = $('#goods_discription_tr_1').html();
      var tr_id = 'tr_' + add_more_count;
      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50">' + add_more_count + '</td><td class=""><select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_' + add_more_count + '" data-id="' + add_more_count + '" data-modal="itemModal"><option value="">Select Item</option>@foreach($items as $item_list)<option value="{{$item_list->id}}" data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}"  data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>@endforeach</select>';
      //newRow += optionElements;
      newRow += '</td><td class=""><input type="number" step="any" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '" placeholder="Quantity" style="text-align:right;"  /></td><td class=" w-min-50"><input type="text" class="w-100 form-control unit" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;" data-id="'+add_more_count+'"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class=" w-min-50"><input type="number" step="any" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '" placeholder="Price" style="text-align:right;" /></td><td class=" w-min-50"><input type="number" step="any" class="amount w-100 form-control" name="amount[]" id="amount_tr_' + add_more_count + '" data-id="' + add_more_count + '" placeholder="Amount" style="text-align:right;" /></td><td class="w-min-50" style="display:flex" ></td><input type="hidden" name="item_parameters[]" id="item_parameters_'+add_more_count+'"><input type="hidden" name="config_status[]" id="config_status_'+add_more_count+'"></tr>';
      $("#max_sale_descrption").val(add_more_count);
      $("#purchase_tbl").append(newRow);
      
      let k = 1;
      $('.item_id').each(function(){   
         let i = $(this).attr('data-id');
         $("#srn_"+i).html(k);  
         k++;           
      });
      
   // Reset all icon cells
   $(".item_id").each(function () {
      let dataId = $(this).attr("data-id");
      $("#tr_" + dataId + " td:last").html('');
   });

   let totalRows = $(".item_id").length;

   $(".item_id").each(function (index) {
      let dataId = $(this).attr("data-id");
      let removeIcon = '<svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + dataId + '" viewBox="0 0 16 16">' +
         '<path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>' +
         '</svg>';

      let addIcon = '<svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="bg-primary rounded-circle add_more_wrapper" data-id="' + dataId + '" >' +
         '<path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>' +
         '</svg>';

         if (dataId == "1") {
         // Clear the icon from the last <td> of the first row
         $("#tr_" + dataId + " td:last").html('');
      }
      else if (index < totalRows - 1) {
         $("#tr_" + dataId + " td:last").html(removeIcon);
      } else {
         $("#tr_" + dataId + " td:last").html(removeIcon + addIcon);
      }
   });
      $( ".select2-single, .select2-multiple" ).select2();
   }
   function removeItem() {
  $(document).on("click", ".remove", function () {
    let id = $(this).attr("data-id");
    $("#tr_" + id).remove();

    // Re-index SRNs
    let k = 1;
    $(".item_id").each(function () {
      let i = $(this).attr("data-id");
      $("#srn_" + i).html(k);
      k++;
    });

    // Update max counter
    let max_val = $("#max_sale_descrption").val();
    $("#max_sale_descrption").val(--max_val);

    let totalRows = $(".item_id").length;

    // Loop through all remaining item rows to reassign icons
    $(".item_id").each(function (index) {
      let rowId = $(this).attr("data-id");
      let $iconCell = $("#tr_" + rowId + " td:last");

      let removeIcon = `
        <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="${rowId}" viewBox="0 0 16 16">
          <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
        </svg>`;

      let addIcon = `
        <svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="bg-primary rounded-circle add_more_wrapper" data-id="${rowId}">
          <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
        </svg>`;
        

      $iconCell.html(""); // Reset first

      if (totalRows === 1) {
        // Only one row → show Add
        $iconCell.html(addIcon);
      } else if (index === 0) {
        // First row → no icon
        $iconCell.html("");
      } else if (index === totalRows - 1) {
        // Last row → Remove + Add
        $iconCell.html(removeIcon + addIcon);
      } else {
        // Middle rows → Remove only
        $iconCell.html(removeIcon);
      }
    });

    calculateAmount();
  });
}
  
      
   $(document).ready(function(){
       var mat_series = "<?php echo count($GstSettings);?>";
      // Function to calculate amount and update total sum
      window.calculateAmount = function(key=null) {         
         customer_gstin = $('#party_id option:selected').attr('data-state_code'); 
         if(customer_gstin==undefined){
            return;
         }
         
         if(gst_disabled_for_party == 0 && customer_gstin==merchant_gstin.substring(0,2)){
            $("#billtr_cgst").show();
            $("#billtr_sgst").show();
            $(".extra_gst").show();
            $("#bill_sundry_amount_igst").val('');
            $("#billtr_igst").hide();
            $("#tax_rate_tr_igst").val(0);
            $("#tax_amt_igst").html('');
         }else if(gst_disabled_for_party == 0){
            $("#billtr_igst").show();
            $("#billtr_cgst").hide();
            $("#billtr_sgst").hide();
            $(".extra_gst").show();
            $("#bill_sundry_amount_cgst").val('');
            $("#bill_sundry_amount_sgst").val('');         
            $("#tax_rate_tr_cgst").val(0);
            $("#tax_rate_tr_sgst").val(0);         
            $("#tax_amt_cgst").html('');
            $("#tax_amt_sgst").html('');
         }
         percent_arr = [];
         var total = 0;
         let count = 2;
         var tax_rate = 0;
         var tax_rate_display = "";
         var tax_amount = 0;
         var final_total = 0;
         $('#purchase_tbl tbody tr').each(function() {         
            var price = $(this).find('.price').val();
            var quantity = $(this).find('.quantity').val();
            if(key=="A"){
               var amount = $(this).find('.amount').val();
            }else{
               var amount = (price && quantity) ? (price * quantity) : 0;
               if(price==0 && quantity==0){
                  amount = $(this).find('.amount').val();
               }
               //if(amount!=0){
                  $(this).find('.amount').val(parseFloat(amount).toFixed(2));
                  $(this).find('.amount').keyup();
               //} 
            }
            if(amount!=undefined){
               total += parseFloat(amount);
            }
         });
         let k = 1;
         $('.item_id').each(function(){   
            let i = $(this).attr('data-id');          
            if($("#amount_tr_"+i).val()!="" && $(this).val()!=''){
               percent_arr.push({"percent":$('option:selected', this).attr('data-percent'),"amount":$("#amount_tr_"+i).val()});
            }
            $("#srn_"+i).html(k);  
            k++;           
         });
         let freight_amount_arr = [];let discouint_amount_arr = [];
         let billSundryArray = [];
         let taxSundryArray = [];
         
         
         $(".bill_sundry_tax_type").each(function(){          
            let id = $(this).attr('data-id');
            if($("#bill_sundry_amount_"+id).val()!='' && ($('option:selected', this).attr('data-sundry_percent')==undefined || $('option:selected', this).attr('data-sundry_percent')=="")){
               billSundryArray.push({'id':$(this).val(),'value':$("#bill_sundry_amount_"+id).val(),'type':$('option:selected', this).attr('data-type'),'adjust_sale_amt':$('option:selected', this).attr('data-adjust_sale_amt'),'effect_gst_calculation':$('option:selected', this).attr('data-effect_gst_calculation'),'sequence':$('option:selected', this).attr('data-sequence'),'nature_of_sundry':$('option:selected', this).attr('data-nature_of_sundry')});
               taxSundryArray[id] = $("#bill_sundry_amount_"+id).val();
            }
         });         
         var result = []; 
         percent_arr.reduce(function(res, value){
            if (!res[value.percent]) {
               res[value.percent] = { percent: value.percent, amount: 0 };
               result.push(res[value.percent]);
            }
            res[value.percent].amount += parseFloat(value.amount);
            return res;
         }, {});
         $("#totalSum").html(total.toFixed(2));
         let taxable_amount = total;
         final_total = total;
         let total_item_taxable_amount = 0;
         let on_tcs_amount = 0;
         // 🚫 STOP GST CALCULATION IF DISABLED
            

         if(customer_gstin==merchant_gstin.substring(0,2)){
            var maxPercent = Math.max.apply(null, result.map(function(item){
              return item.percent;
            }))
            if(result.length>0){
               let index = 1;
               $(".extra_gst").remove();
               let bill_sundry_total = 0;
               let item_total_amount = 0; //New Changes By Ashish
               result.forEach(function(e){  //New Changes By Ashish
                  item_total_amount = parseFloat(item_total_amount) + parseFloat(e.amount); //New Changes By Ashish
               }); //New Changes By Ashish
               
               result.forEach(function(e,i){
                  let item_taxable_amount = e.amount;
                  if(i==0){ //New Changes By Ashish
                     if(billSundryArray.length>0){
                        billSundryArray.forEach(function(e){
                           if(e.nature_of_sundry=='OTHER'){
                              if(e.value>0){
                                 if(e.type=='additive'){
                                    bill_sundry_total = bill_sundry_total + parseFloat(e.value);
                                    final_total = final_total + parseFloat(e.value);
                                 }else if(e.type=='subtractive'){
                                    bill_sundry_total = bill_sundry_total - parseFloat(e.value);
                                    final_total = final_total - parseFloat(e.value);
                                 }
                              }
                           }else if(e.nature_of_sundry=='TCS'){
                              final_total = final_total + parseFloat(e.value);
                              on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(e.value);
                           }
                        });
                     }
                  } //New Changes By Ashish
                  
                  if(i==0){
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount) + parseFloat(bill_sundry_total);
                  }else{
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount);
                  }
                  //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(total_item_taxable_amount);
                  //let a = e.amount/item_total_amount;
                  let taxable_amount_per_item = (e.amount/item_total_amount)*(bill_sundry_total);//New Changes By Ashish
                  taxable_amount_per_item = parseFloat(e.amount) + parseFloat(taxable_amount_per_item); //New Changes By Ashish
                  if(index==1){
                     if(enter_gst_status==0 && item_taxable_amount!=0 && auto_gst_calculation==1){
                        let sundry_amount = (taxable_amount_per_item*e.percent/2)/100; //New Changes By Ashish
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                        enter_gst_status = 1;                        
                     }else{
                        let sundry_amount = (taxable_amount_per_item*e.percent/2)/100; //New Changes By Ashish
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                     }
                     //CGST
                     if(gst_disabled_for_party == 0){
                        $("#bill_sundry_amount_cgst").val(taxSundryArray['cgst']);
                     }
                     //$("#bill_sundry_amount_cgst").prop('readonly',true);
                     $("#tax_amt_cgst").html(e.percent/2+" %");
                     $("#tax_rate_tr_cgst").val(e.percent/2);
                     //SGST
                     if(gst_disabled_for_party == 0){
                        $("#bill_sundry_amount_sgst").val(taxSundryArray['sgst']);
                     }
                     //$("#bill_sundry_amount_sgst").prop('readonly',true);
                     $("#tax_amt_sgst").html(e.percent/2+" %");
                     $("#tax_rate_tr_sgst").val(e.percent/2);
                     if(taxSundryArray['sgst']=="" || taxSundryArray['sgst']==undefined){                       
                        taxSundryArray['sgst'] = 0;
                     }
                     if(taxSundryArray['cgst']=="" || taxSundryArray['cgst']==undefined){
                        taxSundryArray['cgst'] = 0;
                     }
                     //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']);
                     if(gst_disabled_for_party == 0){
                        final_total = final_total + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']); 
                     }
                     

                  }else{
                     if(enter_gst_status==0 && item_taxable_amount!=0){
                        let sundry_amount = (taxable_amount_per_item*e.percent/2)/100;//New Changes By Ashish
                        sundry_amount = sundry_amount.toFixed(2);
                        enter_gst_status = 1;
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                     }else{
                        let sundry_amount = (taxable_amount_per_item*e.percent/2)/100;//New Changes By Ashish
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                     }
                     //CGST
                     let cgst_sundry_value = "";
                     if(bill_sundry_array.length>0){ //New Changes By Ashish
                        bill_sundry_array.forEach(function(e){ //New Changes By Ashish
                           if(e.nature_of_sundry=='CGST'){ 
                              cgst_sundry_value = e.id;
                           }
                        });
                     }
                     $(".add_more_bill_sundry_gst").click();
                     if(gst_disabled_for_party == 0){
                        $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['cgst']);
                     }
                     $("#bill_sundry_"+add_more_bill_sundry_up_count).val(cgst_sundry_value)
                     //$("#bill_sundry_amount_"+add_more_bill_sundry_up_count).prop('readonly',true);
                     $("#tax_amt_"+add_more_bill_sundry_up_count).html(e.percent/2+" %");
                     $("#tax_rate_tr_"+add_more_bill_sundry_up_count).val(e.percent/2);
                     //SGST
                     let sgst_sundry_value = "";
                     if(bill_sundry_array.length>0){ //New Changes By Ashish
                        bill_sundry_array.forEach(function(e){ //New Changes By Ashish
                           if(e.nature_of_sundry=='SGST'){ 
                              sgst_sundry_value = e.id;
                           }
                        });
                     }
                     $(".add_more_bill_sundry_gst").click();
                     if(gst_disabled_for_party == 0){
                        $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['sgst']);
                     }
                     $("#bill_sundry_"+add_more_bill_sundry_up_count).val(sgst_sundry_value)
                     //$("#bill_sundry_amount_"+add_more_bill_sundry_up_count).prop('readonly',true);
                     $("#tax_amt_"+add_more_bill_sundry_up_count).html(e.percent/2+" %");
                     $("#tax_rate_tr_"+add_more_bill_sundry_up_count).val(e.percent/2);
                     if(taxSundryArray['sgst']=="" || taxSundryArray['sgst']==undefined){                        
                        taxSundryArray['sgst'] = 0;
                     }
                     if(taxSundryArray['cgst']=="" || taxSundryArray['cgst']==undefined){
                        taxSundryArray['cgst'] = 0;
                     }
                     //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']);
                     if(gst_disabled_for_party == 0){
                        final_total = final_total + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']); 
                     }
                  }
                  index++;
               });
            }
         }else{
            var maxPercent = Math.max.apply(null, result.map(function(item) {
              return item.percent;
            }))    

            if(result.length>0){
               let index = 1;
               let item_total_amount = 0;
               let bill_sundry_total = 0;
               $(".extra_gst").remove();  
               result.forEach(function(e){ //New Changes By Ashish
                  item_total_amount = parseFloat(item_total_amount) + parseFloat(e.amount);//New Changes By Ashish
               });//New Changes By Ashish
               result.forEach(function(e,i){
                  
                  let item_taxable_amount = e.amount;   
                  if(i==0){ //New Changes By Ashish
                     if(billSundryArray.length>0){
                        billSundryArray.forEach(function(e){
                           if(e.nature_of_sundry=='OTHER'){
                              if(e.value>0){
                                 if(e.type=='additive'){
                                    bill_sundry_total = bill_sundry_total + parseFloat(e.value);
                                    final_total = final_total + parseFloat(e.value);
                                 }else if(e.type=='subtractive'){
                                    bill_sundry_total = bill_sundry_total - parseFloat(e.value);
                                    final_total = final_total - parseFloat(e.value);
                                 }
                              }
                           }else if(e.nature_of_sundry=='TCS'){
                              final_total = final_total + parseFloat(e.value);
                              on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(e.value);
                           }
                        });
                     }
                  }  //New Changes By Ashish
                  if(i==0){
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount) + parseFloat(bill_sundry_total);
                  }else{
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount);
                  }
                  
                  //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(total_item_taxable_amount);                 

                  let taxable_amount_per_item = (e.amount/item_total_amount)*(bill_sundry_total);//New Changes By Ashish
                  taxable_amount_per_item = parseFloat(e.amount) + parseFloat(taxable_amount_per_item);//New Changes By Ashish
                  if(index==1){
                     if(enter_gst_status==0 && item_taxable_amount!=0){
                        let sundry_amount = (taxable_amount_per_item*e.percent)/100; //New Changes By Ashish
                        
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                        enter_gst_status = 1;                        
                     }else{
                        let sundry_amount = (taxable_amount_per_item*e.percent)/100; //New Changes By Ashish
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                     }
                     if(gst_disabled_for_party == 0){
                        $("#bill_sundry_amount_igst").val(taxSundryArray['igst']);
                     }
                     //$("#bill_sundry_amount_igst").prop('readonly',true);
                     $("#tax_amt_igst").html(e.percent+" %");
                     $("#tax_rate_tr_igst").val(e.percent); 
                     if(taxSundryArray['igst']=="" || taxSundryArray['igst']==undefined){
                        taxSundryArray['igst'] = 0;
                     }
                     //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['igst']);
                     if(gst_disabled_for_party == 0){
                        final_total = final_total + parseFloat(taxSundryArray['igst']); 
                     }
                  }else{
                     if(enter_gst_status==0 && item_taxable_amount!=0){
                        let sundry_amount = (taxable_amount_per_item*e.percent)/100;//New Changes By Ashish
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                        enter_gst_status = 1;                        
                     }else{
                        let sundry_amount = (taxable_amount_per_item*e.percent)/100;//New Changes By Ashish
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                     }
                     
                     let sundry_value = "";
                     if(bill_sundry_array.length>0){ //New Changes By Ashish
                        bill_sundry_array.forEach(function(e){ //New Changes By Ashish
                           if(e.nature_of_sundry=='IGST'){
                              sundry_value = e.id;
                           }
                        });
                     }
                     $(".add_more_bill_sundry_gst").click();
                     if(gst_disabled_for_party == 0){
                        $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['igst']);
                     }
                     
                     $("#bill_sundry_"+add_more_bill_sundry_up_count).val(sundry_value);
                     $("#tax_amt_"+add_more_bill_sundry_up_count).html(e.percent+" %");
                     $("#tax_rate_tr_"+add_more_bill_sundry_up_count).val(e.percent);
                     if(taxSundryArray['igst']=="" || taxSundryArray['igst']==undefined){
                        taxSundryArray['igst'] = 0;
                     }
                     //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['igst']);
                     if(gst_disabled_for_party == 0){
                        final_total = final_total + parseFloat(taxSundryArray['igst']); 
                     }
                  }                  
                  index++;
               });
            } 
         }
         $('#total_taxable_amounts').val(total_item_taxable_amount.toFixed(2));
         let gstamount = 0;
         $(".bill_sundry_tax_type").each(function(){
            let id = $(this).attr('data-id');
            let sundry_percent = $('option:selected', this).attr('data-sundry_percent');
            let sundry_percent_date = $('option:selected', this).attr('data-sundry_percent_date');
            let nature_of_sundry = $('option:selected', this).attr('data-nature_of_sundry');
            let bill_date = $("#date").val();
            let adjust_sale_amt = $('option:selected', this).attr('data-adjust_sale_amt');
            let effect_gst_calculation = $('option:selected', this).attr('data-effect_gst_calculation');
            let type = $('option:selected', this).attr('data-type');
            if(sundry_percent!=undefined && sundry_percent_date!=undefined && sundry_percent!='' && sundry_percent_date!=''){
               if(new Date(sundry_percent_date) <= new Date(bill_date)){
                  $("#tax_amt_"+id).html(sundry_percent+" %");
                  $("#tax_rate_tr_"+id).val(sundry_percent);
                  // let tcs_amount = (on_tcs_amount*sundry_percent)/100;
                  // tcs_amount = tcs_amount.toFixed(2);
                  // $("#bill_sundry_amount_"+id).val(tcs_amount);
                  // final_total = final_total + parseFloat(tcs_amount);
               }
            }else{
               if(new Date(sundry_percent_date) <= new Date(bill_date)){
                  if($("#bill_sundry_amount_"+id).val()!=""){
                     if(type=="additive"){
                        //final_total = final_total + parseFloat($("#bill_sundry_amount_"+id).val());
                     }else if(type=="subtractive"){
                        //final_total = final_total - parseFloat($("#bill_sundry_amount_"+id).val());
                     }
                  }
               }
            } 
            
            if($("#bill_sundry_amount_"+id).val()!='' && (nature_of_sundry=='CGST' || nature_of_sundry=='SGST' || nature_of_sundry=='IGST') && nature_of_sundry!='ROUNDED OFF (+)' && nature_of_sundry!='ROUNDED OFF (-)'){
               if(type=="additive"){
                  gstamount = parseFloat(gstamount) + parseFloat($("#bill_sundry_amount_"+id).val());
               }else{
                  gstamount = parseFloat(gstamount) - parseFloat($("#bill_sundry_amount_"+id).val());
               }
            }
         }); 
         if(gst_disabled_for_party == 1){
            gstamount = 0;
         }
        // console.log(gstamount);
         if (isNaN(final_total)) {
            final_total = 0;
         }
         final_total = Math.round(final_total);
         var formattedNumber = final_total.toLocaleString('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
         });

         

         $("#bill_sundry_amt").html(formattedNumber);
         $("#total_amounts").val(final_total);
         
         let roundoff = parseFloat(final_total) - parseFloat($("#total_taxable_amounts").val()) - parseFloat(gstamount) - on_tcs_amount;
         roundoff = roundoff.toFixed(2);
         $("#billtr_round_plus").hide();
         $("#billtr_round_minus").hide();
         $("#bill_sundry_amount_round_minus").val('');
         $("#bill_sundry_amount_round_plus").val('');
         if(parseFloat(roundoff)<0){
            $("#bill_sundry_amount_round_minus").val(Math.abs(roundoff));
            $("#bill_sundry_amount_round_minus").attr('readonly',true); 
            $("#billtr_round_minus").show();           
         }else if(parseFloat(roundoff)>0){
            $("#bill_sundry_amount_round_plus").val(Math.abs(roundoff));
            $("#bill_sundry_amount_round_plus").attr('readonly',true); 
            $("#billtr_round_plus").show(); 
         }
         return;         
      }
      $("#party_id").trigger('change');
      // Calculate amount on input change
      $(document).on('input', '.price',function(){
         let id = $(this).attr('data-id');
         if($(this).val()==""){
            $("#quantity_tr_"+id).focus();
         }
         calculateAmount();
      });
      $(document).on('input', '.quantity',function(){
         let id = $(this).attr('data-id');
         if($(this).val()==""){
            $("#item_id_"+id).focus();
         }
         calculateAmount();
      });
      $(document).on('input', '.amount',function(){
         let id = $(this).attr('data-id');
         let qty = $("#quantity_tr_"+id).val();
         let price = $("#price_tr_"+id).val();
         if(qty!=0 || qty!=0 || price!=0 || price!=0){
            // alert("Not Allowed")
            // $(this).val('');
            // retutn;
         }
         if($(this).val()==""){
            $("#price_tr_"+id).focus();
         }
         calculateAmount('A');
      });
      $(document).on('change', '.bill_sundry_tax_type',function(){
         let id = $(this).attr('data-id');
         if($(this).val()==""){
            $("#bill_sundry_amount_"+id).val('');
         }else{
            $("#bill_sundry_amount_"+id).attr('readonly',false);
         }
         let sequence = $('option:selected', this).attr('data-sequence');
         let sundry_percent = $('option:selected', this).attr('data-sundry_percent');
         let sundry_percent_date = $('option:selected', this).attr('data-sundry_percent_date');
         let bill_date = $("#date").val();
         if(sundry_percent!=undefined && sundry_percent_date!=undefined){
            if(new Date(sundry_percent_date) <= new Date(bill_date)){
               $("#tax_amt_"+id).html(sundry_percent+" %");
               $("#tax_rate_tr_"+id).val(sundry_percent);
            }
         }         
         $("#billtr_"+id).attr('data-sequence',sequence);
         $("#bill_sundry_amount_"+id).addClass('sundry_amt_'+$(this).val());
         calculateAmount();
      });      
      $(document).on('input', '.bill_amt',function(){
         if($(this).val()==""){
            $("#bill_sundry_"+$(this).attr('data-id')).focus();
         }
         calculateAmount($("#bill_sundry_"+$(this).attr('data-id')).val());
      });
      $("#purchaseBtn").click(function(){
         if(confirm("Are you sure to submit?")==true){            
            $("#purchaseForm").validate({
               ignore: [], 
               rules: {
                  series_no: "required",
                  voucher_no: "required",
                  party: "required",
                  material_center: "required",
                  "goods_discription[]": "required",
                  "qty[]" : "required",
                  "price[]" : "required",
                  "amount[]" : "required",
               },
               messages: {
                  series_no: "Please select series no",
                  voucher_no: "Please enter voucher no",
                  party: "Please select party",
                  material_center: "Please select material center",
                  "goods_discription[]" : "Please select item",
                  "qty[]" : "Please enter quantity",
                  "price[]" : "Please enter price",
                  "amount[]" : "Please enter amount",                
               }
            });
            let item_validate = 1;let item_count = 0;
            $(".item_id").each(function(){
               let id = $(this).attr('data-id');
               if($(this).val()=="" || $("#quantity_tr_"+id).val()=="" || $("#price_tr_"+id).val()=="" || $("#amount_tr_"+id).val()==""){
                  item_validate = 0;
               }
               item_count++;
            });
            if(item_validate==0 && item_count>1){
               alert("Please Enter Item Required Fields.");
               return false;
            }
         }else{
            return false;
         }         
      });
      if(mat_series==1){
         $("#series_no").change();
      }
   });
   
   function call_fun(data) {
      if($('#goods_discription_'+data).val()==""){
         $("#quantity_"+data).val('');
         $("#price_"+data).val('');
         $("#amount_"+data).val('');
         $("#quantity_"+data).keyup();
         $("#price_"+data).keyup();
         $("#amount_"+data).keyup();
      }
      var party_id = $('#party_id').val();
      if(party_id.length > 0){
         calculateAmount();
      }else{
         alert("Select Party Name First.");
         $('#unit_' + data).val('');
         $('#units_' + data).val('');      
         $('#goods_discription_'+data).val("");
      }
   }
   $(document).on("click", ".remove_sundry", function() {
      let id = $(this).attr('data-id');
      $("#billtr_" + id).remove();
      var max_val = $("#max_sale_sundry").val();
      max_val--;
      $("#max_sale_sundry").val(max_val);
      calculateAmount();
   });
   
   function getAccountDeatils(e){
      var account_id = $(e).val();
      $.ajax({
         url: '{{url("get/accounts/details")}}',
         async: false,
         type: 'POST',
         dataType: 'JSON',
         data: {
            _token: '<?php echo csrf_token() ?>',
            account_id: account_id
         },
         success: function(data) {
            $("input[name='shipping_address']").val(data.data.accounts.address);
            $("input[name='shipping_pincode']").val(data.data.accounts.pin_code);
            $("input[name='shipping_gst']").val(data.data.accounts.gstin);
            $("input[name='shipping_pan']").val(data.data.accounts.pan);
            $("input[name='shipping_state']").val(data.data.accounts.sname);
         }
      });
   }
   $(document).on("click", ".remove_sundry_up", function () {
  const id = $(this).data("id");
  $("#billtr_" + id).remove();

  // Filter valid sundry rows with numeric IDs
  let validSundryRows = $(".sundry_tr").filter(function () {
    let select = $(this).find("select.bill_sundry_tax_type");
    let idMatch = select.attr("id")?.match(/^bill_sundry_(\d+)$/);
    return idMatch !== null;
  });

  // Reassign icons after row removal
  validSundryRows.each(function (index) {
    let select = $(this).find("select.bill_sundry_tax_type");
    let match = select.attr("id").match(/^bill_sundry_(\d+)$/);
    let dataId = match[1];
    let $lastCell = $(this).find("td:last");

    // Define icons
    let removeIcon = `
      <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" tabindex="0"class="bi bi-file-minus-fill remove_sundry_up" data-id="${dataId}" viewBox="0 0 16 16">
        <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
      </svg>`;

    let addIcon = `
      <svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"tabindex="0" class="bg-primary rounded-circle add_more_bill_sundry_up" data-id="${dataId}" viewBox="0 0 24 24">
        <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
      </svg>`;

    // Clear icon first
    $lastCell.html("");

    // Only one row left → show Add icon
    if (validSundryRows.length === 1) {
      $lastCell.html(addIcon);
    }
    // First row → no icon
    else if (dataId === "1") {
      $lastCell.html("");
    }
    // Last row → Remove + Add
    else if (index === validSundryRows.length - 1) {
      $lastCell.html(removeIcon + addIcon);
    }
    // Middle rows → Remove only
    else {
      $lastCell.html(removeIcon);
    }
  });
  calculateAmount();
});

   $( ".select2-single, .select2-multiple" ).select2();


   //Ashish Javascript   
   function add_more_bill_sundry_up() {
      let empty_status = 0;
      $(".bill_sundry_tax_type").each(function(){
         if($(this).attr('data-id')!="cgst" && $(this).attr('data-id')!="sgst" && $(this).attr('data-id')!="igst" && $(this).attr('data-id')!="round_plus" && $(this).attr('data-id')!="round_minus"){
            if($(this).val()=="" || $("#bill_sundry_amount_"+$(this).attr('data-id')).val()==""){
               empty_status = 1;
            }
         }
         
      });
      if(empty_status==1){
         alert("Please enter sundry required fields");
         return;
      }
      add_more_bill_sundry_up_count++;
      var $curRow = $("#billtr_cgst").closest('tr');
      var optionElements = "<option value=''>Select</option>";
      <?php
      foreach ($billsundry as $value){ 
         if($value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';<?php 
         }
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent  bill_sundry_tax_type form-select w-100 select2-single"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align: right;" ></td><td class="w-min-50" style="display:flex" > </td></tr>';
      $curRow.before(newRow);
      
  // Clear all icon cells
  $(".sundry_tr td:last-child").html("");

// Filter only valid sundry rows with numeric IDs
let validSundryRows = $(".sundry_tr").filter(function () {
  let select = $(this).find("select.bill_sundry_tax_type");
  let idMatch = select.attr("id")?.match(/^bill_sundry_(\d+)$/);
  return idMatch !== null;
});

// Add icons to valid sundry rows
validSundryRows.each(function (index) {
  let select = $(this).find("select.bill_sundry_tax_type");
  let match = select.attr("id").match(/^bill_sundry_(\d+)$/);
  let dataId = match[1];
  let $lastCell = $(this).find("td:last");

  let removeIcon = `
    <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" tabindex="0" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="${dataId}" viewBox="0 0 16 16">
      <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
    </svg>`;

  let addIcon = `
    <svg style="color: green; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" tabindex="0" class="bg-primary rounded-circle add_more_bill_sundry_up" data-id="${add_more_bill_sundry_up_count}" viewBox="0 0 24 24">
      <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
    </svg>`;

  // First row: no icons
  if (dataId === "1") {
    $lastCell.html("");
  }
  // Last row: remove + add
  else if (index === validSundryRows.length - 1) {
    $lastCell.html(removeIcon + addIcon);
  }
  // Middle rows: only remove
  else {
    $lastCell.html(removeIcon);
  }
});
$( ".select2-single, .select2-multiple" ).select2(); 
   };   
   $(".add_more_bill_sundry_down").click(function() {
      add_more_bill_sundry_up_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = "<option value=''>Select</option>";
      <?php
      foreach ($billsundry as $value) { 
         if($value->effect_gst_calculation==0){?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';
            <?php 
         }
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type form-select w-100"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align: right;" ></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
   });
   $(".add_more_bill_sundry_gst").click(function() {
      add_more_bill_sundry_up_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = "";
      <?php
      foreach ($billsundry as $value) { 
         if($value->nature_of_sundry=='CGST' || $value->nature_of_sundry=='SGST' || $value->nature_of_sundry=='IGST'){?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';
            <?php 
         }
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr extra_gst"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type form-select w-100"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" step="any" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align: right;" ></td><td class="w-min-50"></td></tr>';
      $curRow.before(newRow);
   });
   $('#party').keydown(function(e) {
      if (e.keyCode === 8) {
         if($(this).val()==""){
         $("#voucher_no").focus();
         }         
      }
   });
   $('#material_center').keydown(function(e) {
      if (e.keyCode === 8) {
         $("#party").focus();         
      }
   }); 
   $('#party').keyup(function(){
      var query = $(this).val();
      if(query != ''){
         $('#party_id').val('');
         var _token = '<?php echo csrf_token(); ?>';
         $.ajax({
            url:"{{ url('get-party-list') }}",
            method:"POST",
            data:{query:query, _token:_token},
            success:function(data){
               $('#partyList').fadeIn();  
               $('#partyList').html(data);
            }
         });
      }
   });
   $(document).on('change', '#party_id', function(){
    if($(this).val()==""){
         return;
      }
    let selected = $('option:selected', this);
    let partyId = $(this).val();
    let gstin = selected.attr('data-gstin');
    let allowWithoutGst = selected.attr('data-allow_without_gst');
    let stateCode = selected.attr('data-state_code');
    let group = selected.data('group'); 
    if(partyGSTData[partyId]){
      gstin = partyGSTData[partyId].gstin;
      address = partyGSTData[partyId].address;
      stateCode = partyGSTData[partyId].state_code;
    }
    gst_disabled_for_party = 0;
    if(noGSTGroups.includes(group)){

         gst_disabled_for_party = 1;

         disableGSTCalculation();

         $("#bill_sundry_amount_cgst").val('');
         $("#bill_sundry_amount_sgst").val('');
         $("#bill_sundry_amount_igst").val('');

         $("#tax_amt_cgst").html('');
         $("#tax_amt_sgst").html('');
         $("#tax_amt_igst").html('');

         $("#tax_rate_tr_cgst").val(0);
         $("#tax_rate_tr_sgst").val(0);
         $("#tax_rate_tr_igst").val(0);

      }else{
    // 🔴 CASE 1: No GST & not already allowed
    if((!gstin || gstin.trim() === "") && allowWithoutGst != 1){

        let confirmBox = confirm(
            "This party is unauthorized.\n\nDo you want to continue without GST?"
        );

        if(confirmBox){

            // ✅ YES clicked
            gst_disabled_for_party = 1;

            disableGSTCalculation();

            // Save allow_without_gst = 1 in DB
            $.ajax({
                url: "{{ route('account.allow.without.gst') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    account_id: selected.val()
                },
                success: function(){
                    selected.attr('data-allow_without_gst',1);
                }
            });

        }else{

    // ❌ NO clicked → OPEN GST MODAL

    $("#gst_modal_account_id").val(selected.val());
    $("#gst_modal_account_name").text(selected.text().trim());

    $("#gstin").val('');
    $("#address").val('');
    $("#pincode").val('');
    $("#pan").val('');
    $("#state").val('').trigger('change');

    $("#gstAccountModal").modal('show');

    return;
}

    }
  
    // 🟢 Already allowed without GST
    if(allowWithoutGst == 1 && (!gstin || gstin.trim() === "")){
        gst_disabled_for_party = 1;
        disableGSTCalculation();
    }

    // 🟢 Normal GST case
    if(gstin && gstin.trim() !== ""){
        gst_disabled_for_party = 0;

        // if(stateCode == merchant_gstin.substring(0,2)){
        //     $("#purchase_type").val('LOCAL');
        // }else{
        //     $("#purchase_type").val('CENTER');
        // }
    }
     }
    updatePurchaseType(stateCode);
    // Update Address Section
    $("#partyaddress").html('');
    $("#partyaddress").html(
        "GSTIN : "+ (gstin ? gstin : "N/A") +
        "<br>Address : "+ selected.attr('data-address')
    );

    calculateAmount();
});
   $('body').on('keydown', 'input, select', function(e){
      if (e.key === "Enter") {
         var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
         focusable = form.find('input,a,select,button,textarea').filter(':visible');
         next = focusable.eq(focusable.index(this)+1);
         if (next.length) {
            next.focus();
         } else {
            form.submit();
         }
         return false;
      }
   });
   $(document).on('change','.item_id', function(){
      let id = $(this).attr('data-id');
      $('#unit_tr_'+id).val($('option:selected', this).attr('data-val'));
      $('#unit_tr_'+id).attr('data-parameterized_stock_status',$('option:selected', this).attr('data-parameterized_stock_status'));
      $('#unit_tr_'+id).attr('data-group_id',$('option:selected', this).attr('data-group_id'));
      $('#unit_tr_'+id).attr('data-config_status',$('option:selected', this).attr('data-config_status'));
      $('#units_tr_'+id).val($('option:selected', this).attr('data-unit_id'));
      $('#config_status_'+id).val($('option:selected', this).attr('data-config_status'));
      call_fun('tr_'+id);
      //getItemGstRate($(this).val(),id);
   });
   var paremeter_table_add_more_data = "";
   $(document).on('click',".unit",function(){
      let parameter_qty = $("#quantity_tr_"+$(this).attr('data-id')).val()+" "+$(this).val();
      let parameter_name = $("#goods_discription_tr_"+$(this).attr('data-id')).val();
      let item_qty = $("#quantity_tr_"+$(this).attr('data-id')).val();
      $("#parameter_item").html(parameter_name);
      $("#parameter_qty").html(parameter_qty);
      $("#parameter_modal_qty").val($("#quantity_tr_"+$(this).attr('data-id')).val());
      let uname = $(this).val();
      
      let config_status = $(this).attr('data-config_status');
      let parameterized_stock_status = $(this).attr('data-parameterized_stock_status');
      let group_id = $(this).attr('data-group_id');
      let id = $(this).attr('data-id');
      let item_id = $("#item_id_"+id).val();
      if(parameterized_stock_status==null || parameterized_stock_status==0 || parameterized_stock_status==""){
         return;
      }     
      
      if ($.inArray(id, parameter_assign_item_arr) !== -1) {
         $("#parameter_modal").modal('toggle');
         return;
      }
      paremeter_table_add_more_data = "";
      $.ajax({
         url: '{{url("get-parameter-data")}}',
         async: false,
         type: 'POST',
         dataType: 'JSON',
         data: {
            _token: '<?php echo csrf_token() ?>',
            config_status: config_status,
            parameterized_stock_status : parameterized_stock_status,
            group_id : group_id
         },
         success: function(res){
            let data = res.parameters;
            if((data.parameterized_stock_status!=undefined && data.parameterized_stock_status==1) || data.parameterized_status!=undefined && data.parameterized_status==1){
               let html = "<table class='table table-bordered'><thead><tr>";
               if(data.parameters.length>0){
                  data.parameters.forEach(function(e){
                     let alt_name = "";let alternative_qty_status = 0;
                     if(e.alternative_unit==1 && data.alternative_qty==1){
                        alt_name = " (ALT QTY)";
                        alternative_qty_status = 1;
                     }
                     html+='<td>'+e.paremeter_name+''+alt_name+'<input type="hidden" name="parameter_column[]" class="parameter_column" value="'+e.id+'" data-alternative_qty="'+alternative_qty_status+'"></td>';
                  });
               }
               html+='<td>Qty ('+uname+')<input type="hidden" name="parameter_column[]" class="parameter_column" value="QTY_COL"></td>'; 
               html+='<td></td>';
               html+='</tr></thead><tbody>';
               html+='<tr class="tr_param" data-id="param_index">';
               paremeter_table_add_more_data+='<tr id="param_tr_param_index" class="tr_param" data-id="param_index">';
               if(data.parameters.length>0){
                  let i = 1;
                  data.parameters.forEach(function(e){
                     if(e.parameter_type=="OPEN"){
                        let id_val = "";
                        if(e.alternative_unit=="1"){
                           id_val = "alternative_unit_id_param_index";
                        }else if(e.conversion_factor=="1"){
                           id_val = "conversion_factor_id_param_index";
                        }
                        html+='<td><input id="'+id_val+'" type="text" name="parameter_column_value_'+e.id+'" class="form-control param_col param_col_param_index parameter_column_value_'+e.id+'" style="height: 52px;" placeholder="'+e.paremeter_name+'" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+e.conversion_factor+'" data-id="param_index" data-parameter_id="'+e.id+'"></td>';
                        paremeter_table_add_more_data+='<td><input id="'+id_val+'" type="text" name="parameter_column_value_'+e.id+'" class="form-control param_col param_col_param_index parameter_column_value_'+e.id+'" style="height: 52px;" placeholder="'+e.paremeter_name+'" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+e.conversion_factor+'" data-id="param_index" data-parameter_id="'+e.id+'"></td>';
                     }else{
                        let predefined_list = "";
                        if(e.predefined_value.length>0){
                           predefined_list+='<select class="form-control param_col_param_index name="parameter_column_value_'+e.id+'" parameter_column_value_'+e.id+'" style="height: 52px;" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+data.conversion_factor+'" data-id="param_index"><option value="">Select</option>';
                           e.predefined_value.forEach(function(e1){
                              predefined_list+='<option value="'+e1.predefined_value+'">'+e1.predefined_value+'</option>';
                           });
                           predefined_list+='</select>';
                        }
                        html+='<td>'+predefined_list+'</td>';
                        paremeter_table_add_more_data+='<td>'+predefined_list+'</td>';
                     }
                     i++;                
                  });                  
                  html+='<td><input type="text" class="form-control parameter_column_value_QTY_COL" name="parameter_column_value_QTY_COL" id="parameter_column_value_qty_param_index" style="height: 52px;" placeholder="QTY" value="'+item_qty+'" data-id="param_index" data-alternative_qty="'+data.alternative_qty+'"></td>';
                  paremeter_table_add_more_data+='<td><input type="text" class="form-control parameter_column_value_QTY_COL" name="parameter_column_value_QTY_COL" id="parameter_column_value_qty_param_index" style="height: 52px;" placeholder="QTY" data-id="param_index" data-alternative_qty="'+data.alternative_qty+'"></td>';
                  html+='<td></td>';
                  paremeter_table_add_more_data+='<td></td>';
               }
               html+='</tr>';
               paremeter_table_add_more_data+='<tr>';
               html+='<tr class="parameters_table" style="display:none"><td colspan="3"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_new_row" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></td></tr>';
               html+='</tbody></table>';
               $(".parameter_body").html(html);
               $("#parameter_modal_id").val(id);
               $("#parameter_modal").modal('toggle');
            }
         }
      });
   });
   let param_index = 1;
   $(document).on('click','.add_new_row',function(){
      param_index++;
      let res = paremeter_table_add_more_data.replace(/\param_index/g,param_index);
      $(".parameters_table").before(res);
   });
   $(document).on('click','.remove_add_row',function(){      
      let id = $(this).attr('data-id');
      $("#param_tr_"+id).remove();
   });
   $(document).on('click','.parameter_save_btn',function(){   
      let data_arr = [];
      // $(".parameter_column").each(function(){
      //    let parameter_column = $(this).val();
      //    let data_value_arr = [];
        
      //    let alternative_qty = $(this).attr('data-alternative_qty');
      //    $(".parameter_column_value_"+parameter_column).each(function(){            
      //       data_value_arr.push($(this).val());
      //    });
      //    data_arr.push({'column_id':parameter_column,'alternative_qty':alternative_qty,'column_value':data_value_arr});
        
      // });
      
      $(".tr_param").each(function(){
         let id = $(this).attr('data-id');
         let data_value_arr = [];
         $(".param_col_"+id).each(function(){
            data_value_arr.push({'id':$(this).attr('data-parameter_id'),'value':$(this).val(),'alternative_unit':$(this).attr('data-alternative_unit'),'alternative_qty':$(this).attr('data-alternative_qty')})
         });
         data_arr.push(data_value_arr);
      });
      parameter_assign_item_arr.push($("#parameter_modal_id").val());
      $("#item_parameters_"+$("#parameter_modal_id").val()).val(JSON.stringify(data_arr));

      $("#quantity_tr_"+$("#parameter_modal_id").val()).attr('readonly',true);
      $("#parameter_modal").modal('toggle');
   });
   $(document).on('keyup','.param_col',function(){
      let id = $(this).attr('data-id');
      if($(this).attr('data-alternative_qty')==1){
         let item_total_qty = $("#parameter_modal_qty").val();
         if(item_total_qty==""){
            item_total_qty = 0;
         }
         if($(this).attr('data-alternative_unit')==1){
            let unit_val = $(this).val();
            let conversion_val = $("#conversion_factor_id_"+id).val();
            if(unit_val==""){
               unit_val = 1;
            }            
            if(conversion_val==""){
               conversion_val = 1;
            } 
            let qty = parseFloat(unit_val)*parseFloat(conversion_val);
            $("#parameter_column_value_qty_"+id).val(qty);
         }else  if($(this).attr('data-conversion_factor')==1){
            let conversion_val = $(this).val();
            let unit_val = $("#alternative_unit_id_"+id).val();
            if(unit_val==""){
               unit_val = 1;
            }            
            if(conversion_val==""){
               conversion_val = 1;
            } 
            let qty = parseFloat(conversion_val)*parseFloat(unit_val);
            $("#parameter_column_value_qty_"+id).val(qty);
         }else {
            return;
         }
         let qty1 = 0;
         $(".parameter_column_value_QTY_COL").each(function(){
            if($(this).val()!=""){
               qty1 = parseFloat(qty1) + parseFloat($(this).val());
            }else{
               $("#param_tr_"+$(this).attr('data-id')).remove();
            }
         });
         
         if(parseFloat(qty1)<parseFloat(item_total_qty)){
            //$(".add_new_row").click();
            $(".add_new_row").trigger('click');
         }else if(parseFloat(qty1)>parseFloat(item_total_qty)){
            $(this).val('');
            $("#parameter_column_value_qty_"+id).val('');
            alert("Quntity should be equal to item quantity")
         }
         $(".parameter_save_btn").hide();
         
         if(parseFloat(qty1)==parseFloat(item_total_qty)){
            $(".parameter_save_btn").show();
         }
         $("#item_parameters_"+$("#parameter_modal_id").val()).val("");
         $("#quantity_tr_"+$("#parameter_modal_id").val()).attr('readonly',false);
         
      }else{
         
      }
   });   
   $(document).on('keyup','.parameter_column_value_QTY_COL',function(){
      let qty = 0;
      let item_total_qty = $("#parameter_modal_qty").val();
      if(item_total_qty==""){
         item_total_qty = 0;
      }
      //parameter_column_value_qty_param_index
      if($(this).attr('data-alternative_qty')==0){         
         $(".parameter_column_value_QTY_COL").each(function(){
            if($(this).val()!=""){
               qty = parseFloat(qty) + parseFloat($(this).val());
            }else{
               $("#param_tr_"+$(this).attr('data-id')).remove();
            }
         });
         if(parseFloat(qty)<parseFloat(item_total_qty)){
            $(".add_new_row").click();
         }else if(parseFloat(qty)>parseFloat(item_total_qty)){
            $(this).val('');
            alert("Quntity should be equal to item quantity")
         }else{
             $(".parameter_save_btn").show();
         }

        
      }
   });
   
   $("#series_no").change(function(){
      let series = $(this).val();      
      $("#material_center").val($('option:selected', this).attr('data-mat_center'));
      merchant_gstin = $('option:selected', this).attr('data-gst_no');
       $("#merchant_gst").val(merchant_gstin);
      if($("#party_id").val()!=""){
         if($('#party_id option:selected').attr('data-state_code')==merchant_gstin.substring(0,2)){  
            $("#purchase_type").val('LOCAL');
         }else{
            $("#purchase_type").val('CENTER');
         }
      }
      calculateAmount();
          
   });
   
     
   $(".transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   });
   $(".save_transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   }); 

   
$(document).ready(function() {
  // Properly initialize Select2 with search enabled
  $('#party_id').select2({
    placeholder: "Select Account",
    allowClear: true,
    width: '100%' // Ensure dropdown matches Bootstrap styling
  });

  // Move focus to next field after selecting an option
  $('#party_id').on('select2:select', function (e) {
    // Move focus to the next field
    $('#material_center').focus();
  });

  // Handle the case when the user clears the selection
  $('#party_id').on('select2:unselect', function (e) {
    $('#material_center').focus(); // Move focus to the next field
  });

  // Handle the case when the user selects the same value again
  $('#party_id').on('select2:close', function (e) {
    // Check if the dropdown is closed and the same value is selected
    const selectedValue = $(this).val(); // Get the currently selected value
    const previousValue = $(this).data('previousValue'); // Get the previous value

    // If the same value is selected, move focus to the next field
    if (selectedValue === previousValue) {
      $('#material_center').focus();
    }

    // Update the previous value
    $(this).data('previousValue', selectedValue);
  });
});

$(document).ready(function() {
  // Initialize Select2 for all item_id_# fields
  $('[id^="item_id_"]').each(function() {
    $(this).select2({
      placeholder: "Select Item",
      allowClear: true,
      width: '100%'
    });
  });

  // When an item is selected
  $(document).on('select2:select', '[id^="item_id_"]', function(e) {
    const currentId = $(this).attr('id');
    const match = currentId.match(/item_id_(\d+)/);
    if (match) {
      const num = match[1];
      $('#quantity_tr_' + num).focus();
    }
  });

  // When selection is cleared
  $(document).on('select2:unselect', '[id^="item_id_"]', function(e) {
    const currentId = $(this).attr('id');
    const match = currentId.match(/item_id_(\d+)/);
    if (match) {
      const num = match[1];
      $('#quantity_tr_' + num).focus();
    }
  });

  // Handle re-selecting the same value
  $(document).on('select2:close', '[id^="item_id_"]', function(e) {
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');
    if (selectedValue === previousValue) {
      const currentId = $(this).attr('id');
      const match = currentId.match(/item_id_(\d+)/);
      if (match) {
        const num = match[1];
        $('#quantity_tr_' + num).focus();
      }
    }
    // Update previous value
    $(this).data('previousValue', selectedValue);
  });
});


$(document).on("keydown", ".amount", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();

    let $current = $(this);
    let id = parseInt($current.data("id")); // Current input ID
    let $currentRow = $("#tr_" + id);
    let lastRowId = $(".goods_items").last().data("id");

    if (id === 1) {
      // If id is 1, focus on Add More button
      $currentRow.find(".add_more_wrapper").focus();
    } else {
      let $removeButton = $currentRow.find(".remove");

      if ($removeButton.length > 0) {
        // If Remove button exists, focus it
        $removeButton.focus();
      } else {
        // Else, move to next goods description input
        let nextId = id + 1;
        let $nextInput = $("#goods_discription_tr_" + nextId);

        if ($nextInput.length > 0) {
          $nextInput.focus();
        } else {
          console.warn("Next description field not found for ID: " + nextId);
        }
      }
    }
  }
});

$(document).on("keydown", ".remove", function (event) {
 

    let id = $(this).data("id");
    let lastRowId = $(".item_id").last().data("id");
if(id==lastRowId){
   if ((event.key === "Tab" && !event.shiftKey)) {
      event.preventDefault();
   $("#tr_" + id).find(".add_more_wrapper").focus();
}
    
  }else{}
});


// Pressing Enter on the add button triggers row addition
$(document).on("keydown", ".add_more_wrapper", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();
    addMoreItem();
  }
});
// Pressing Enter on Remove button deletes the row
$(document).on("keydown", ".remove", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();
    $(this).trigger("click");
  }
});


// Clicking the add button (mouse or keyboard)
$(document).on("click", ".remove", function () {
  removeItem();
});


// Clicking the add button (mouse or keyboard)
$(document).on("click", ".add_more_wrapper", function () {
  addMoreItem();
});



$(document).on("keydown", ".add_more_bill_sundry_up", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();
    add_more_bill_sundry_up();
  }
});
// Pressing Enter on Remove button deletes the row
$(document).on("keydown", ".remove_sundry_up", function (event) {
  if (event.key === "Enter") {
    event.preventDefault();
    $(this).trigger("click");
  }
});
$(document).on("click", ".add_more_bill_sundry_up", function () {
   add_more_bill_sundry_up();
});
$(document).on("keydown", ".bill_sundry_amount", function (event) {
  if ((event.key === "Enter" || event.key === "Tab") && !event.shiftKey) {
    event.preventDefault();

    let id = $(this).data("id");
    let $currentRow = $("#billtr_" + id);
    let $actionIcon = $currentRow.find("td:last svg");

    // Check if the icon is 'add' or 'remove' and move focus or trigger click
    if ($actionIcon.hasClass("add_more_bill_sundry_up")) {
      $actionIcon.focus(); // or .trigger("click")
    } else if ($actionIcon.hasClass("remove_sundry_up")) {
      $actionIcon.focus(); // or .trigger("click")
    }
  }
});
$(document).ready(function() {
// Safely apply Select2 only to new bill_sundry_<number> elements not already initialized
$('select.bill_sundry_tax_type').each(function () {
  const id = $(this).attr('id');

  // Match only if id ends with a number
  if (/^bill_sundry_\d+$/.test(id) && !$(this).hasClass('select2-hidden-accessible')) {
    $(this).select2({
      placeholder: "Select Item",
      allowClear: true,
      width: '100%'
    });
  }
});

  // When an item is selected
  $(document).on('select2:select', '[id^="bill_sundry_"]', function(e) {
    const currentId = $(this).attr('id');
    const match = currentId.match(/bill_sundry_(\d+)/);
    if (match) {
      const num = match[1];
      $('#bill_sundry_amount_' + num).focus();
    }
  });

  // When selection is cleared
  $(document).on('select2:unselect', '[id^="bill_sundry_"]', function(e) {
    const currentId = $(this).attr('id');
    const match = currentId.match(/bill_sundry_(\d+)/);
    if (match) {
      const num = match[1];
      $('#bill_sundry_amount_' + num).focus();
    }
  });

  // Handle re-selecting the same value
  $(document).on('select2:close', '[id^="bill_sundry_"]', function(e) {
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');
    if (selectedValue === previousValue) {
      const currentId = $(this).attr('id');
      const match = currentId.match(/item_id_(\d+)/);
      if (match) {
        const num = match[1];
        $('#bill_sundry_amount_' + num).focus();
      }
    }
    // Update previous value
    $(this).data('previousValue', selectedValue);
  });
});



$(document).ready(function() {
  
  //   // When clicking purchaseBtn
  //   $("#purchaseBtn").on('click', function(event) {
  //     event.preventDefault(); // Stop default submit behavior
  //     $(this).closest('form').submit(); // Manually submit the form
  //   });
  
    // When pressing Enter on purchaseBtn
    $("#purchaseBtn").on('keydown', function(event) {
      if (event.key === "Enter") {
        event.preventDefault(); // prevent default behavior
        $(this).click(); // trigger click event (which submits)
      }
    });
  
  });




  $(document).ready(function() {
     let isDuplicateVoucher = false;

   function checkDuplicateVoucher(callback = null) {
        let voucher_no = $('#voucher_no').val();
        let party_id = $('#party_id').val();
        let financial_year = '{{ Session::get("default_fy") }}'; // or your session variable

        if(voucher_no !== '' && party_id !== '') {
            $.ajax({
                url: '{{ route("check.duplicate.voucher") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    voucher_no: voucher_no,
                    party_id: party_id,
                    financial_year: financial_year
                },
                success: function(response) {
                    if(response.exists) {
                     if(response.voucher_no!=""){
                        alert('Voucher number "' + voucher_no + '" already exists for this party in this financial year. With Slip No - "' + response.voucher_no+'"');
                     }else{
                        alert('Voucher number "' + voucher_no + '" already exists for this party in this financial year.');
                     }
                        $('#voucher_no').val('');
                        $('#voucher_no').focus();
                        isDuplicateVoucher = true;
                        return false;
                    } else {
                        isDuplicateVoucher = false;
                    }
                    if(callback) callback();
                }
            });
        } else {
            isDuplicateVoucher = false;
            if(callback) callback();
        }
    }

    // Trigger AJAX when voucher or party changes
    $('#voucher_no, #party_id').on('change', function() {
        checkDuplicateVoucher();
    });
    
    // Prevent form submission if duplicate exists
    
});

$('#date').on('change', function () {
    var bill_date = $(this).val();
    var group_id  = "{{ $groupId }}"; // pass if needed

    $.ajax({
        
        url: "",
        type: "GET",
        data: { bill_date: bill_date, group_id: group_id },
        success: function (response) {
            // Convert response into a map for quick lookup
            var itemMap = {};
            $.each(response, function (index, item) {
                itemMap[item.id] = item;
            });

            // Update all existing item dropdowns without removing selected value
            $('.item_id').each(function () {
                var select = $(this);
                var selectedId = select.val(); // keep current selection

                if (selectedId && itemMap[selectedId]) {
                    // Update GST and other attributes
                    var item = itemMap[selectedId];
                    var option = select.find('option[value="' + selectedId + '"]');

                    option.attr('data-unit_id', item.u_name)
                          .attr('data-percent', item.gst_rate)
                          .attr('data-val', item.unit)
                          .attr('data-parameterized_stock_status', item.parameterized_stock_status)
                          .attr('data-config_status', item.config_status)
                          .attr('data-group_id', item.group_id);

                    // 🔥 Trigger change so dependent logic (GST calc, etc.) re-runs
                    select.trigger('change');
                }
            });
        }
    });
});

$(document).ready(function () {

    @if(!empty($startItems))
  
        let incomingItems = @json($startItems);

        // Clear all existing rows except first
        $("#tr_1").find("input").val("");  
        $("#item_id_1").val("").trigger("change");

        let rowIndex = 1;

        incomingItems.forEach((itm, index) => {
            
            if (index === 0) {
                // Fill first row
                fillRow(rowIndex, itm);
            } else {
                // Trigger Add Row event
                $(".add_more_wrapper").last().click();
                rowIndex++;
                fillRow(rowIndex, itm);
            }
        });
    @endif

    @if(!empty($spare_part_id))
      $('#spare_part_id').val('{{$spare_part_id}}');
     @endif


    // FUNCTION TO FILL A ROW
    function fillRow(i, data) {

        // Select item
        let itemSelect = $("#item_id_" + i);
        itemSelect.val(data.item_id).trigger("change");

        // Quantity
        $("#quantity_tr_" + i).val(data.quantity);

             // unit
        $("#unit_tr_" + i).val(data.unit);

        // Price
        $("#price_tr_" + i).val(data.price);

        // Calculate amount
        let amt = (parseFloat(data.quantity) * parseFloat(data.price)).toFixed(2);
        $("#amount_tr_" + i).val(amt);
calculateAmount();
      
    }

});

document.getElementById('purchaseBtn').addEventListener('click', function (e) {
    const formCompanyId   = document.querySelector('[name="form_company_id"]').value;
    const formCompanyName = document.querySelector('[name="form_company_name"]').value;
    const sessionCompanyId = localStorage.getItem('active_company_id');
    if (sessionCompanyId && sessionCompanyId !== formCompanyId) {
        const msg =
         `This Purchase belongs to "${formCompanyName}"

         You have switched company in another tab.

         Saving will STORE under "${formCompanyName}"

         Do you want to continue?`;

        if (!confirm(msg)) {
            e.preventDefault();
            return false;
        }
    }
});
$(document).on('select2:open', function () {

  let select = $('.select2-container--open').prev('select');

  // ✅ STORE ROW ID IF ITEM DROPDOWN
  if (select.hasClass('item_id')) {
      activeItemRowId = select.attr('data-id');
  }

  $(document).on('keydown.select2Shortcut', function (e) {

    if (e.ctrlKey && e.key.toLowerCase() === 'a') {
      e.preventDefault();
      
      let modalId = select.data('modal');

      if (modalId) {
        $('#' + modalId).modal('show');
      }
    }
  });
});


$(document).on('select2:close', function () {
  $(document).off('keydown.select2Shortcut');
});
$('#modal_account_name').on('keyup', function () {
    $('#modal_print_name').val($(this).val());
});
$('#modal_under_group_type').val('group');
$('#modal_account_name').on('change', function () {

    let account_name = $(this).val();
    if (!account_name) return;

    $.ajax({
        url: '{{ url("check-account-name") }}',
        type: 'POST',
        dataType: 'JSON',
        data: {
            _token: '{{ csrf_token() }}',
            account_name: account_name,
            company_id: "{{ Session::get('user_company_id') }}"
        },
        success: function (data) {
            if (data == 1) {
                alert('Account Name Already Exists.');
                $('#modal_account_name').val('').focus();
            }
        }
    });
});
$('#modal_gstin').on('change', function () {

    let gstin = $(this).val().trim();
    if (!gstin || gstin.length < 2) return;

    $.ajax({
        url: '{{ url("check-gstin") }}',
        type: 'POST',
        dataType: 'JSON',
        data: {
            _token: '{{ csrf_token() }}',
            gstin: gstin
        },
        success: function (data) {

            if (data.status != 1) {
                alert(data.message);
                $('#modal_gstin').val('');
                return;
            }

            $('#modal_pan').val(gstin.substring(2, 12));
            $('#modal_address').val(data.address.toUpperCase());
            $('#modal_pincode').val(data.pinCode);

            let stateCode = gstin.substring(0, 2);

            let stateOption = $('#modal_state option').filter(function () {
                return $(this).text().trim().startsWith(stateCode + ' ');
            }).val();

            if (stateOption) {
                $('#modal_state')
                    .val(stateOption)
                    .trigger('change'); 

                $('#modal_state_hidden').val(stateOption);
            }
        }
    });
});

    $("#saveGstModal").click(function () {
        let accountId = $("#gst_modal_account_id").val();
        let gstin = $("#gstin").val();
    
        if (!gstin || gstin.length !== 15) {
            alert("Please enter a valid GST number");
            return;
        }
        $.ajax({
            url: "{{ route('account.update.gst') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                account_id: accountId,
                gstin: gstin,
                state: $("#state_hidden").val(),
                address: $("#address").val(),
                pincode: $("#pincode").val(),
                pan: $("#pan").val()
            },
            success: function () {
    
                let opt = $("#vendor option[value='" + accountId + "']");
                opt.attr("data-gstin", gstin);
    
                ignoreVendorChange = true;
                $("#vendor").val(accountId);
                $("#gstAccountModal").modal("hide");
    
                gstCalculation();
            }
        });
    });
    $("#cancelGstModal").click(function () {
        $("#gstAccountModal").modal("hide");
    });
    $('#gstAccountModal').on('shown.bs.modal', function () {
        $('#state').select2({
            dropdownParent: $('#gstAccountModal'),
            width: '100%'
        });
    });
    function syncStateValue() {
        $("#state_hidden").val($("#state").val());
    }
    $("#state").on("change", function () {
        syncStateValue();
    });
    $("#gstin").on("blur", function () {
        let gstin = $(this).val().trim();
        if (gstin === "") return;
    
        $.ajax({
            url: '{{ url("check-gstin-exists") }}',
            type: 'POST',
            dataType: 'JSON',
            data: {
                _token: '{{ csrf_token() }}',
                gstin: gstin,
                account_id: $("#gst_modal_account_id").val()
            },
            success: function (res) {
                if (res.exists === true) {
                    alert("This GST Number already exists.");
                    $("#gstin").val("").focus();
                    $("#pan").val("");
                    $("#address").val("");
                    $("#pincode").val("");
                    $("#state").val("").trigger('change');
                    $("#gstin").data("duplicate", true);
                } else {
                    $("#gstin").data("duplicate", false);
                }
            }
        });
    });
    $("#gstin").on("change", function () {
        if ($(this).data("duplicate") === true) return;
        let gstin = $(this).val().trim();
        if (gstin === "") return;
        $("#pan").val("");
        $("#address").val("");
        $("#pincode").val("");
        $("#state").val("").trigger('change');
        $.ajax({
            url: '{{ url("check-gstin") }}',
            type: 'POST',
            dataType: 'JSON',
            data: {
                _token: '{{ csrf_token() }}',
                gstin: gstin
            },
            success: function (data) {
    
                if (data && data.status == 1) {
    
                    let stateCode = gstin.substr(0, 2);
                    let matched = $('#state option[data-state_code="' + stateCode + '"]').val();
    
                    if (matched) {
                        $('#state').val(matched).trigger('change');
                        $('#state').on('select2:opening', function (e) {
                            e.preventDefault();
                        }).css('pointer-events', 'none');
                    }
    
                    $("#pan").val(gstin.substring(2, 12));
    
                    $("#address").val((data.address || "").toUpperCase());
                    $("#pincode").val(data.pinCode || "");
    
                    syncStateValue();
                } else {
                    alert(data.message || "Invalid GST Number");
                }
            }
        });
    });
    $('#gstAccountModal').on('hidden.bs.modal', function () {
        let accountId = $("#gst_modal_account_id").val();
        let opt = $("#vendor option[value='" + accountId + "']");
        if (opt.length && (!opt.attr("data-gstin") || opt.attr("data-gstin").trim() === "")) {
        ignoreVendorChange = true;
          $("#vendor").val(null).trigger("change.select2");
        }
    
        // Reset modal fields (clean state)
        $("#gst_modal_account_id").val("");
        $("#gst_modal_account_name").text("");
        $("#gstin, #pan, #address, #pincode").val("");
        $("#state").val("").trigger("change");
    });
$('#modal_state').on('change', function () {
    $('#modal_state_hidden').val($(this).val());
});
$('select[name="under_group"]').on('change', function () {
    $('#modal_under_group_type').val('group');
});

$('#saveAccountBtn').on('click', function () {

    $('#modal_under_group_type').val('group');

    let form = $('#accountForm');
    let btn = $(this);

    btn.prop('disabled', true);

    $.ajax({
        url: "{{ route('account.store') }}",
        type: "POST",
        data: form.serialize(),
        success: function (res) {

    alert(res.message || 'Account added successfully');

    if (!res.account || !res.account.id || !res.account.account_name) {
        console.error('Invalid response:', res);
        return;
    }

    let partySelect = document.getElementById('party_id');

    if (!partySelect) {
        console.error('#party_id not found');
        return;
    }

    // 🔥 Create option using native DOM
    let option = document.createElement("option");

option.value = res.account.id;
option.text  = res.account.account_name;
option.selected = true;

// 🔑 REQUIRED DATA ATTRIBUTES
option.setAttribute('data-gstin', res.account.gstin ?? '');
option.setAttribute('data-address', res.account.address ?? '');
option.setAttribute('data-state', res.account.state ?? '');
option.setAttribute('data-state_code', res.account.state_code ?? '');

partySelect.appendChild(option);

// refresh select2
$(partySelect).trigger('change');


    // 🔁 Refresh Select2 safely
    if ($(partySelect).hasClass("select2-hidden-accessible")) {
        $(partySelect).trigger("change");
    } else {
        $(partySelect).select2().trigger("change");
    }

    $('#accountModal').modal('hide');
    form[0].reset();
},

        error: function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                let msg = Object.values(xhr.responseJSON.errors)[0][0];
                alert(msg);
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                alert(xhr.responseJSON.message);
            } else {
                alert('Validation failed');
            }
        },
        complete: function () {
            btn.prop('disabled', false);
        }
    });
});

/* =========================
   ITEM MODAL LOGIC
========================= */

// Auto PRINT NAME
$('#modal_name').on('keyup', function () {
    $('#modal_p_name').val(this.value);
});

// PART-B toggle
$('#modal_partb').on('change', function () {
    $('.modal_partb_div, .modal_tcs_div').hide();
    $('#modal_tcs_applicable').prop('checked', false);

    if (this.checked) {
        $('.modal_partb_div').show();
    }
});

// TCS toggle
$('#modal_tcs_applicable').on('change', function () {
    $('.modal_tcs_div').toggle(this.checked);
});

// GST → Item type
$('#modal_gst_rate').on('change', function () {
    $('#modal_item_type').val(
        $(this).find(':selected').data('type') || ''
    );
});


/* =========================
   SELECT2 INIT (MODAL SAFE)
========================= */

$('#itemModal').on('shown.bs.modal', function () {

    $('#itemModal select.select2-single').each(function () {

        if ($(this).data('select2')) {
            $(this).select2('destroy');
        }

        $(this).select2({
            width: '100%',
            dropdownParent: $('#itemModal'),
            minimumResultsForSearch: 0
        });
    });
});

$('#saveItemBtn').on('click', function () {

    let btn = $(this);
    let form = $('#modalItemForm');

    btn.prop('disabled', true);

    $.ajax({
        url: "{{ route('account-manage-item.store') }}",
        type: "POST",
        data: form.serialize(),
        success: function (res) {

    if (!res.status || !res.item) {
        alert('Invalid response');
        return;
    }

    alert(res.message);

    // ✅ SAFETY CHECK
    if (!activeItemRowId) {
        alert('Item row not detected');
        return;
    }

    // 🎯 TARGET CORRECT ROW
    let itemSelect = $('#item_id_' + activeItemRowId);

    // 🔥 CREATE OPTION
    let option = document.createElement("option");

option.value = res.item.id;
option.text  = res.item.name;
option.selected = true;

// 🔥 REQUIRED DATA ATTRIBUTES
option.setAttribute('data-val', res.item.unit);          // UNIT TEXT
option.setAttribute('data-unit_id', res.item.u_name);    // UNIT ID
option.setAttribute('data-percent', res.item.gst_rate);
option.setAttribute('data-parameterized_stock_status', res.item.parameterized_stock_status ?? 0);
option.setAttribute('data-config_status', res.item.config_status ?? 0);
option.setAttribute('data-group_id', res.item.group_id ?? '');

itemSelect.append(option);
itemSelect.trigger('change');


    // ➕ APPEND & SELECT
    itemSelect.append(option).trigger('change');

    // 🔁 REFRESH SELECT2 (SAFE)
    if (itemSelect.hasClass('select2-hidden-accessible')) {
        itemSelect.trigger('change.select2');
    }

    // 👉 MOVE CURSOR TO QTY
    $('#quantity_tr_' + activeItemRowId).focus();

    // 🧹 CLEANUP
    $('#itemModal').modal('hide');
    $('#modalItemForm')[0].reset();
    activeItemRowId = null;
},
        error: function (xhr) {
            if (xhr.responseJSON?.errors) {
                alert(Object.values(xhr.responseJSON.errors)[0][0]);
            } else {
                alert('Failed to save item');
            }
        },
        complete: function () {
            btn.prop('disabled', false);
        }
    });
});
function disableGSTCalculation(){

    // Hide GST rows
    $("#billtr_cgst").hide();
    $("#billtr_sgst").hide();
    $("#billtr_igst").hide();
   $(".extra_gst").hide();
    // Reset GST values
    $("#bill_sundry_amount_cgst").val('');
    $("#bill_sundry_amount_sgst").val('');
    $("#bill_sundry_amount_igst").val('');

    $("#tax_rate_tr_cgst").val(0);
    $("#tax_rate_tr_sgst").val(0);
    $("#tax_rate_tr_igst").val(0);

    $("#tax_amt_cgst").html('');
    $("#tax_amt_sgst").html('');
    $("#tax_amt_igst").html('');
}
$(document).on('click', '#saveGstModal', function () {

    let account_id = $('#gst_modal_account_id').val();
    let gstin = $('#gstin').val();
    let address = $('#address').val();
    let pincode = $('#pincode').val();
    let state_code = $('#state option:selected').data('state_code');

    let fullAddress = address + ',' + pincode;

    partyGSTData[account_id] = {
        gstin: gstin,
        address: fullAddress,
        state_code: state_code
    };

    let option = $('#party_id option[value="' + account_id + '"]');

    option.attr('data-gstin', gstin);
    option.attr('data-address', fullAddress);
    option.attr('data-state_code', state_code);
    option.attr('data-allow_without_gst', 0);

    $("#partyaddress").html(
        "GSTIN : " + gstin + "<br>Address : " + fullAddress
    );

    gst_disabled_for_party = 0;
   updatePurchaseType(state_code);
    calculateAmount();

    $("#gstAccountModal").modal('hide');
});
function updatePurchaseType(stateCode){

    if(!stateCode || !merchant_gstin){
        $("#purchase_type").val('');
        return;
    }

    if(stateCode == merchant_gstin.substring(0,2)){
        $("#purchase_type").val('LOCAL');
    }else{
        $("#purchase_type").val('CENTER');
    }
}
function getItemGstRate(item_id,index){
   let date = $("#date").val();
   if(date==""){
      return;
   }
   var token = '<?php echo csrf_token(); ?>';
   $.ajax({
      url: "{{ route('get-item-gst-rate') }}",
      type: "POST",
      data : {'item_id':item_id,'txn_date':$("#date").val(),'_token':token},
      success : function(res) {
         if(res.status==true){
            let $select = $("#item_id_" + index);
            $select.find(':selected').attr('data-percent', res.gst_rate);
            calculateAmount();
         }
      }
   });
}
$("#date").on("change", function(){
   $(".item_id").each(function(){
      let item_id = $(this).val();
      let index = $(this).data("id");
      if(item_id){
         //getItemGstRate(item_id,index);
      }
   });   
});
</script>
@endsection