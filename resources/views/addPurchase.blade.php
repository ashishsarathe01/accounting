@extends('layouts.app')
@section('content')
@include('layouts.header')
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
               <div class="row">
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
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" value="{{$bill_date}}" placeholder="Select date" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     <ul style="color: red;">
                       @error('date'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                     <input type="text" class="form-control" name="voucher_no" placeholder="Enter Invoice No.">
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
                     <select class="form-select select2-single" id="party_id" name="party_id" required>
                        <option value="">Select </option>
                        <?php
                        foreach ($party_list as $value) { ?>
                           <option value="<?php echo $value->id; ?>" data-gstin="<?php echo $value->gstin; ?>" data-address="<?php echo $value->address.",".$value->pin_code; ?>" data-state_code="{{$value->state_code}}"><?php echo $value->account_name; ?></option>
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
                              <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_1" data-id="1">
                                 <option value="">Select Item</option>
                                 @foreach($items as $item_list)
                                    <option value="{{$item_list->id}}" data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}"  data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>
                                 @endforeach
                              </select>
                           </td>                           
                           <td class=" w-min-50">
                              <input type="number" class="quantity w-100 form-control" id="quantity_tr_1" name="qty[]" placeholder="Quantity" style="text-align:right;"/>
                           </td>
                           <td class=" w-min-50">
                              <input type="text" class="w-100 form-control unit" id="unit_tr_1" readonly style="text-align:center;" data-id="1"/>
                              <input type="hidden" class="units" name="units[]" id="units_tr_1" />
                           </td>
                           <td class=" w-min-50">
                              <input type="number" class="price form-control" id="price_tr_1" name="price[]" data-id="1" placeholder="Price" style="text-align:right;" />
                           </td>
                           <td class=" w-min-50">
                              <input type="number" id="amount_tr_1" class="amount w-100 form-control" name="amount[]"  data-id="1" placeholder="Amount" style="text-align:right;" />
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
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_1" id="sundry_option_<?php echo $value->id;?>_1" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
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
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_1" data-id="1" readonly style="text-align: right;">
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
                                             <input type="text" name="vehicle_no" class="form-control" placeholder="Vehicle No." />
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Transport Name</label>
                                             <input type="text" id="transport_name" name="transport_name" class="form-control" placeholder="Transport Name" />
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
                                             <input type="text" id="station" name="station" class="form-control" placeholder="Station"/>
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
                           <button type="button" class="ms-3 btn btn-red parameter_save_btn">SUBMIT</button>
                        </div>
                     </div>
                  </div>
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

</body>
@include('layouts.footer')
<script>
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
   var customer_gstin = "";
   var merchant_gstin = "";
   var percent_arr = [];
   var add_more_count = 1;
   var add_more_counts = 1;
   var add_more_bill_sundry_up_count = 2;
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
      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50">' + add_more_count + '</td><td class=""><select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_' + add_more_count + '" data-id="' + add_more_count + '"><option value="">Select Item</option>@foreach($items as $item_list)<option value="{{$item_list->id}}" data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}"  data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>@endforeach</select>';
      //newRow += optionElements;
      newRow += '</td><td class=""><input type="number" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '" placeholder="Quantity" style="text-align:right;"  /></td><td class=" w-min-50"><input type="text" class="w-100 form-control unit" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;" data-id="'+add_more_count+'"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class=" w-min-50"><input type="number" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '" placeholder="Price" style="text-align:right;" /></td><td class=" w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]" id="amount_tr_' + add_more_count + '" data-id="' + add_more_count + '" placeholder="Amount" style="text-align:right;" /></td><td class="w-min-50" style="display:flex" ></td><input type="hidden" name="item_parameters[]" id="item_parameters_'+add_more_count+'"><input type="hidden" name="config_status[]" id="config_status_'+add_more_count+'"></tr>';
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
      let removeIcon = '<svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + dataId + '" viewBox="0 0 16 16">' +
         '<path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>' +
         '</svg>';

      let addIcon = '<svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="bg-primary rounded-circle add_more_wrapper" data-id="' + dataId + '" >' +
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
        <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="${rowId}" viewBox="0 0 16 16">
          <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
        </svg>`;

      let addIcon = `
        <svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="bg-primary rounded-circle add_more_wrapper" data-id="${rowId}">
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
   $(document).ready(function() {
      var mat_series = "<?php echo count($GstSettings);?>";
       
      // Function to calculate amount and update total sum
      window.calculateAmount = function(key=null) {
         customer_gstin = $('#party_id option:selected').attr('data-state_code');             
         if(customer_gstin==merchant_gstin.substring(0,2)){  
            $("#billtr_cgst").show();
            $("#billtr_sgst").show();
            $("#bill_sundry_amount_igst").val('');
            $("#billtr_igst").hide();
            $("#tax_rate_tr_igst").val(0);
            $("#tax_amt_igst").html('');
         }else{
            $("#billtr_igst").show();
            $("#billtr_cgst").hide();
            $("#billtr_sgst").hide();         
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
               if(amount!=0){
                  $(this).find('.amount').val(parseFloat(amount).toFixed(2));
                  $(this).find('.amount').keyup();
               } 
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
         percent_arr.reduce(function(res, value) {
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
         if(customer_gstin==merchant_gstin.substring(0,2)){            
            var maxPercent = Math.max.apply(null, result.map(function(item){
              return item.percent;
            }))           
            if(result.length>0){
               let index = 1;
               $(".extra_gst").remove();
               result.forEach(function(e){     
                  let item_taxable_amount = e.amount;   
                  if(e.percent==maxPercent){
                     if(billSundryArray.length>0){
                        billSundryArray.forEach(function(e){
                           if(e.nature_of_sundry!='CGST' && e.nature_of_sundry!='SGST' && e.nature_of_sundry!='IGST' && e.nature_of_sundry!='ROUNDED OFF (+)' && e.nature_of_sundry!='ROUNDED OFF (-)'){ 
                              if(e.value>0){
                                 if(e.type=='additive'){
                                    item_taxable_amount = item_taxable_amount + parseFloat(e.value);
                                    final_total = final_total + parseFloat(e.value);
                                 }else if(e.type=='subtractive'){
                                    item_taxable_amount = item_taxable_amount - parseFloat(e.value);
                                    final_total = final_total - parseFloat(e.value);
                                 }
                              }
                           }                           
                        });
                     }
                  }
                  total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount);
                  on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(total_item_taxable_amount);
                  if(index==1){
                     if(enter_gst_status==0 && item_taxable_amount!=0 && auto_gst_calculation==1){
                        let sundry_amount = (item_taxable_amount*e.percent/2)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                        enter_gst_status = 1;                        
                     }else{
                        let sundry_amount = (item_taxable_amount*e.percent/2)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                     }
                     //CGST
                     $("#bill_sundry_amount_cgst").val(taxSundryArray['cgst']);
                     //$("#bill_sundry_amount_cgst").prop('readonly',true);
                     $("#tax_amt_cgst").html(e.percent/2+" %");
                     $("#tax_rate_tr_cgst").val(e.percent/2);
                     //SGST
                     $("#bill_sundry_amount_sgst").val(taxSundryArray['sgst']);
                     //$("#bill_sundry_amount_sgst").prop('readonly',true);
                     $("#tax_amt_sgst").html(e.percent/2+" %");
                     $("#tax_rate_tr_sgst").val(e.percent/2);
                     if(taxSundryArray['sgst']=="" || taxSundryArray['sgst']==undefined){                       
                        taxSundryArray['sgst'] = 0;
                     }
                     if(taxSundryArray['cgst']=="" || taxSundryArray['cgst']==undefined){
                        taxSundryArray['cgst'] = 0;
                     }
                     on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']);
                     final_total = final_total + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']); 

                  }else{
                     if(enter_gst_status==0 && item_taxable_amount!=0){
                        let sundry_amount = (item_taxable_amount*e.percent/2)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        enter_gst_status = 1;
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                     }else{
                        let sundry_amount = (item_taxable_amount*e.percent/2)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['cgst'] = sundry_amount;
                        taxSundryArray['sgst'] = sundry_amount;
                     }
                     //CGST
                     let cgst_sundry_value = "";
                     if(billSundryArray.length>0){
                        billSundryArray.forEach(function(e){
                           if(e.nature_of_sundry=='CGST'){ 
                              cgst_sundry_value = e.id;
                           }
                        });
                     }
                     $(".add_more_bill_sundry_gst").click();
                     $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['cgst']);
                     $("#bill_sundry_"+add_more_bill_sundry_up_count).val(cgst_sundry_value)
                     //$("#bill_sundry_amount_"+add_more_bill_sundry_up_count).prop('readonly',true);
                     $("#tax_amt_"+add_more_bill_sundry_up_count).html(e.percent/2+" %");
                     $("#tax_rate_tr_"+add_more_bill_sundry_up_count).val(e.percent/2);
                     //SGST
                     let sgst_sundry_value = "";
                     if(billSundryArray.length>0){
                        billSundryArray.forEach(function(e){
                           if(e.nature_of_sundry=='SGST'){ 
                              sgst_sundry_value = e.id;
                           }
                        });
                     }
                     $(".add_more_bill_sundry_gst").click();
                     $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['sgst']);
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
                     on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']);
                     final_total = final_total + parseFloat(taxSundryArray['cgst']) + parseFloat(taxSundryArray['sgst']); 
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
               $(".extra_gst").remove();            
               result.forEach(function(e){     
                  let item_taxable_amount = e.amount;   
                  if(e.percent==maxPercent){
                     if(billSundryArray.length>0){
                        billSundryArray.forEach(function(e){
                           if(e.nature_of_sundry!='CGST' && e.nature_of_sundry!='SGST' && e.nature_of_sundry!='IGST' && e.nature_of_sundry!='ROUNDED OFF (+)' && e.nature_of_sundry!='ROUNDED OFF (-)'){ 
                              if(e.value>0){
                                 if(e.type=='additive'){
                                    item_taxable_amount = item_taxable_amount + parseFloat(e.value);
                                    final_total = final_total + parseFloat(e.value);
                                 }else if(e.type=='subtractive'){
                                    item_taxable_amount = item_taxable_amount - parseFloat(e.value);
                                    final_total = final_total - parseFloat(e.value);
                                 }
                              }
                           }                           
                        });
                     }
                  }                   
                  total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount);
                  on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(total_item_taxable_amount);
                  if(index==1){
                     if(enter_gst_status==0 && item_taxable_amount!=0){
                        let sundry_amount = (item_taxable_amount*e.percent)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                        enter_gst_status = 1;                        
                     }else{
                        let sundry_amount = (item_taxable_amount*e.percent)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                     }
                     $("#bill_sundry_amount_igst").val(taxSundryArray['igst']);
                     //$("#bill_sundry_amount_igst").prop('readonly',true);
                     $("#tax_amt_igst").html(e.percent+" %");
                     $("#tax_rate_tr_igst").val(e.percent); 
                     if(taxSundryArray['igst']=="" || taxSundryArray['igst']==undefined){
                        taxSundryArray['igst'] = 0;
                     }
                     on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['igst']);
                     final_total = final_total + parseFloat(taxSundryArray['igst']); 
                  }else{
                      $(".add_more_bill_sundry_gst").click();
                     if(enter_gst_status==0 && item_taxable_amount!=0){
                        let sundry_amount = (item_taxable_amount*e.percent)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                        enter_gst_status = 1;                        
                     }else{
                        let sundry_amount = (item_taxable_amount*e.percent)/100;
                        sundry_amount = sundry_amount.toFixed(2);
                        taxSundryArray['igst'] = sundry_amount;
                     }
                     $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['igst']);
                     let sundry_value = "";
                     if(billSundryArray.length>0){
                        billSundryArray.forEach(function(e){
                           if(e.nature_of_sundry=='IGST'){ 
                              sundry_value = e.id;
                           }
                        });
                     }
                     $("#bill_sundry_"+add_more_bill_sundry_up_count).val(sundry_value)
                     $("#tax_amt_"+add_more_bill_sundry_up_count).html(e.percent+" %");
                     $("#tax_rate_tr_"+add_more_bill_sundry_up_count).val(e.percent);
                     if(taxSundryArray['igst']=="" || taxSundryArray['igst']==undefined){
                        taxSundryArray['igst'] = 0;
                     }
                     on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['igst']);
                     final_total = final_total + parseFloat(taxSundryArray['igst']); 
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
            let bill_date = $("#date").val();
            let nature_of_sundry = $('option:selected', this).attr('data-nature_of_sundry');
            let adjust_sale_amt = $('option:selected', this).attr('data-adjust_sale_amt');
            let effect_gst_calculation = $('option:selected', this).attr('data-effect_gst_calculation');
            let type = $('option:selected', this).attr('data-type');
            if(sundry_percent!=undefined && sundry_percent_date!=undefined && sundry_percent!='' && sundry_percent_date!=''){
               if(new Date(sundry_percent_date) <= new Date(bill_date)){
                  $("#tax_amt_"+id).html(sundry_percent+" %");
                  $("#tax_rate_tr_"+id).val(sundry_percent);
                  let tcs_amount = (on_tcs_amount*sundry_percent)/100;
                  tcs_amount = tcs_amount.toFixed(2);
                  $("#bill_sundry_amount_"+id).val(tcs_amount);
                  final_total = final_total + parseFloat(tcs_amount);
               }
            }else{
               if(new Date(sundry_percent_date) <= new Date(bill_date) && effect_gst_calculation=="0"){
                  if($("#bill_sundry_amount_"+id).val()!=""){
                     if(type=="additive"){
                        //final_total = final_total + parseFloat($("#bill_sundry_amount_"+id).val());
                     }else if(type=="subtractive"){
                        //final_total = final_total - parseFloat($("#bill_sundry_amount_"+id).val());
                     }
                  }
               }
            }
            if($("#bill_sundry_amount_"+id).val()!='' && (nature_of_sundry=='CGST' || nature_of_sundry=='SGST' || nature_of_sundry=='IGST') && $("#bill_sundry_amount_"+id).val()!='' && nature_of_sundry!='ROUNDED OFF (+)' && nature_of_sundry!='ROUNDED OFF (-)'){
               if(type=="additive"){
                  gstamount = parseFloat(gstamount) + parseFloat($("#bill_sundry_amount_"+id).val());
               }else{
                  gstamount = parseFloat(gstamount) - parseFloat($("#bill_sundry_amount_"+id).val());
               }
            }
         }); 
         final_total = Math.round(final_total);
         var formattedNumber = final_total.toLocaleString('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
         });
         $("#bill_sundry_amt").html(formattedNumber);
         $("#total_amounts").val(final_total);
         
         let roundoff = parseFloat(final_total) - parseFloat($("#total_taxable_amounts").val()) - parseFloat(gstamount);         
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
      if(mat_series==1){
         $("#series_no").change();
      }
      // Calculate amount on input change
      $(document).on('input', '.price',function(){
         calculateAmount();
      });
      $(document).on('input', '.quantity',function(){
         calculateAmount();
      });
      $(document).on('input', '.amount',function(){
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
         $("#bill_sundry_amount"+id).addClass('sundry_amt_'+$(this).val());
         calculateAmount();
      });
      $(document).on('keyup', '.goods_items', function(){
      let id = $(this).attr('data-id');
      var query = $(this).val();
      if(query != ''){
         
         var _token = '<?php echo csrf_token(); ?>';
         $.ajax({
            url:"{{ url('get-item-list') }}",
            method:"POST",
            data:{query:query, _token:_token,id:id},
            success:function(data){
               $('#itemList_'+id).fadeIn();  
               $('#itemList_'+id).html(data);
            }
         });
      }
   });
      $(document).on('input', '.bill_amt',function(){
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
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align: right;" ></td><td class="w-min-50"></td></tr>';
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
      if($('option:selected', this).attr('data-state_code')==merchant_gstin.substring(0,2)){  
         $("#purchase_type").val('LOCAL');
      }else{
         $("#purchase_type").val('CENTER');
      }
      $("#partyaddress").html('');
      $("#partyaddress").html("GSTIN : "+$('option:selected', this).attr('data-gstin')+"<br>Address : "+$('option:selected', this).attr('data-address')); 
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
      paremeter_table_add_more_data = "";
      let config_status = $(this).attr('data-config_status');
      let parameterized_stock_status = $(this).attr('data-parameterized_stock_status');
      let group_id = $(this).attr('data-group_id');
      let id = $(this).attr('data-id');
      let item_id = $("#item_id_"+id).val();
      if(parameterized_stock_status==null || parameterized_stock_status==0 || parameterized_stock_status==""){
         return;
      }     
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
         success: function(data){
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
               html+='<tr>';
               paremeter_table_add_more_data+='<tr id="param_tr_param_index">';
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
                        html+='<td><input id="'+id_val+'" type="text" name="parameter_column_value_'+e.id+'" class="form-control param_col param_col_param_index parameter_column_value_'+e.id+'" style="height: 52px;" placeholder="'+e.paremeter_name+'" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+e.conversion_factor+'" data-id="param_index" ></td>';
                        paremeter_table_add_more_data+='<td><input id="'+id_val+'" type="text" name="parameter_column_value_'+e.id+'" class="form-control param_col param_col_param_index parameter_column_value_'+e.id+'" style="height: 52px;" placeholder="'+e.paremeter_name+'" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+e.conversion_factor+'" data-id="param_index"></td>';
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
      $(".parameter_column").each(function(){
         let parameter_column = $(this).val();
         let data_value_arr = [];
        
         let alternative_qty = $(this).attr('data-alternative_qty');
         $(".parameter_column_value_"+parameter_column).each(function(){            
            data_value_arr.push($(this).val());
         });
         data_arr.push({'column_id':parameter_column,'alternative_qty':alternative_qty,'column_value':data_value_arr});         
      });
      console.log(data_arr);
      $("#item_parameters_"+$("#parameter_modal_id").val()).val(JSON.stringify(data_arr));
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
            $(".add_new_row").click();
         }else if(parseFloat(qty1)>parseFloat(item_total_qty)){
            $(this).val('');
            $("#parameter_column_value_qty_"+id).val('');
            alert("Quntity should be equal to item quantity")
         }
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
         }
      }
   });
   $(".parameter_save_btn").click(function(){
      let row_id = $("#parameter_modal_id").val();
      let parameter_arr = [];
      $(".parameter_column").each(function(){
         let id = $(this).val();
         let col_val_arr = [];
         $(".parameter_column_value_"+id).each(function(){
            if($(this).val()!=""){
               col_val_arr.push($(this).val());
            }            
         });
         parameter_arr[row_id] = col_val_arr;
      });
      $("#item_parameters_"+row_id).val(parameter_arr);
      $("#parameter_modal").modal('toggle');
   });
   $("#series_no").change(function(){
      let series = $(this).val();      
      $("#material_center").val($('option:selected', this).attr('data-mat_center'));
      merchant_gstin = $('option:selected', this).attr('data-gst_no');
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


  

// document.addEventListener("DOMContentLoaded", function () {
//   const amountInput = document.getElementById("amount_tr_1");
//   const addBtn = document.getElementById("select_item_add_btn");

//   // 1. Tab or Enter from input to the add button (SVG)
//   amountInput.addEventListener("keydown", function (event) {
//   console.log("Key pressed:", event.key); // Debugging line
//   if (event.key === "Tab" && !event.shiftKey || event.key==="Enter") {
//     event.preventDefault(); // Prevent default behavior
//     addBtn.focus(); // Move focus to SVG
//     console.log("Focus moved to button"); // Debugging line
//   }
//   else if (event.key === "Enter") {
//     event.preventDefault(); // Prevent default behavior
//     addBtn.focus(); // Move focus to SVG
//     console.log("Focus moved to button"); // Debugging line
//   }
// });

//   // 2. Pressing Enter on the button triggers click
//   addBtn.addEventListener("keydown", function (event) {
//     if (event.key === "Enter") {
//       event.preventDefault();
//       addMoreItem(); // Your custom function
//     }
//   });

//   // 3. Click on the button (mouse or keyboard)
//   addBtn.addEventListener("click", function () {
//     addMoreItem(); // Your logic to add row/item
//   });
// });


$(document).on("keydown", ".amount", function (event) {
  if ((event.key === "Tab" && !event.shiftKey) || event.key === "Enter") {
    event.preventDefault();

    let id = $(this).data("id");
    let lastRowId = $(".item_id").last().data("id");
if(id=="1"){
   $("#tr_" + id).find(".add_more_wrapper").focus();
}
    else {
      // Go to Remove (-) button
      $("#tr_" + id).find(".remove").focus();
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
</script>
@endsection