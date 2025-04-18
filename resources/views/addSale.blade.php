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
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Sales Voucher</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale.store') }}" id="saleForm">
               @csrf
               <div class="row">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" name="series_no" class="form-select" required >
                        <option value="">Select</option>
                        <?php
                        if(count($GstSettings) > 0) {
                           foreach ($GstSettings as $value){ ?>
                              <option value="<?php echo $value->series;?>" data-mat_center="<?php echo $value->mat_center;?>" data-gst_no="<?php echo $value->gst_no;?>" data-invoice_start_from="<?php echo $value->invoice_start_from;?>" data-invoice_prefix="<?php echo $value->invoice_prefix;?>" data-manual_enter_invoice_no="<?php echo $value->manual_enter_invoice_no;?>" data-duplicate_voucher="<?php echo $value->duplicate_voucher;?>" data-blank_voucher="<?php echo $value->blank_voucher;?>" data-last_bill_date="<?php echo $value->last_bill_date;?>" <?php if(count($GstSettings)==1) { echo "selected";} ?>><?php echo $value->series; ?></option>
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
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" value="{{$bill_date}}" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     <ul style="color: red;">
                       @error('date'){{$message}}@enderror                        
                     </ul>
                  </div>                
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                        <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" value="" readonly style="text-align: right;" placeholder="Voucher No">
                        <input type="hidden" class="form-control" id="voucher_no" name="voucher_no">
                        <input type="hidden" class="form-control" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                        <input type="hidden" class="form-control" id="merchant_gst" name="merchant_gst">
                     <ul style="color: red;">
                       @error('voucher_no'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">SALE TYPE</label>
                     <input type="text" class="form-control" id="sale_type" name="sale_type" placeholder="SALE TYPE" readonly>
                  </div>
                  <div class="mb-4 col-md-5">
                     <label for="name" class="form-label font-14 font-heading">Party</label><br>
                     <select class="form-select select2-single" name="party_id" id="party_id">
                        <option value="">Select Account</option>
                        @foreach($party_list as $party)
                           <option value="{{$party->id}}" data-state_code="{{$party->state_code}}" data-gstin="{{$party->gstin}}" data-id="{{$party->id}}" data-address="{{$party->address}}, {{$party->pin_code}}">{{$party->account_name}}</option>
                        @endforeach
                     </select>          
                     <p id="partyaddress" style="font-size: 9px;"></p>
                     <ul style="color: red;">
                       @error('party'){{$message}}@enderror                        
                     </ul>
                  </div>
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
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="    width: 36%;">Description of Goods
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
                           <td class="w-min-50" id="srn_1">1</td>
                           <td class="w-min-50">
                              <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_1" data-id="1">
                                 <option value="">Select Item</option>
                                 @foreach($item as $item_list)
                                    <option value="{{$item_list->id}}" data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}" data-available_item="{{$item_list->available_item}}" data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>
                                 @endforeach
                              </select>                  
                           </td>                           
                           <td class="w-min-50">
                              <input type="number" class="quantity w-100 form-control" id="quantity_tr_1" name="qty[]" placeholder="Quantity" style="text-align:right;" data-id="1"/>
                           </td>
                           <td class="w-min-50">                              
                              <input type="text" class="w-100 form-control unit" id="unit_tr_1" readonly style="text-align:center;" data-id="1"/>
                              <input type="hidden" class="units w-100" name="units[]" id="units_tr_1" />
                           </td>
                           <td class="w-min-50">
                              <input type="number" class="price form-control" id="price_tr_1" name="price[]" placeholder="Price" style="text-align:right;" data-id="1"/>
                           </td>
                           <td class="">
                              <input type="number" id="amount_tr_1" class="amount w-100 form-control" name="amount[]" placeholder="Amount"  style="text-align:right;" data-id="1"/>
                           </td>
                           <td class="">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                 <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                 </svg></td>
                           <input type="hidden" name="item_parameters[]" id="item_parameters_1">
                           <input type="hidden" name="config_status[]" id="config_status_1">
                        </tr>
                     </tbody>
                     
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold">Total</td>
                           <td class="w-min-50 fw-bold">
                              <span id="totalSum" style="float: right;"></span>
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
                                 <th class="w-min-50 border-none bg-light-pink text-body">Tax Rate </th>
                                 <th class="w-min-50 border-none bg-light-pink text-body ">Taxable Amt.</th>
                                 <th class="w-min-50 border-none bg-light-pink text-body ">Tax </th>
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
                                       foreach($billsundry as $value) {
                                          if($value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_1" id="sundry_option_<?php echo $value->id;?>_1" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_1"></span>
                                    <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_1">
                                 </td>
                                 <td class="w-min-50">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_1" data-id="1" readonly style="text-align:right;">
                                 </td>
                                 <td><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more_bill_sundry_up" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor:pointer">
                                 <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></td>
                              </tr>
                              
                              <tr id="billtr_cgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_cgst" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="cgst">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='CGST'){?>
                                          <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_cgst" id="sundry_option_cgst" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50"><span name="tax_amt[]" class="tax_amount" id="tax_amt_cgst"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_cgst"></td>
                                 <td class="w-min-50"><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_cgst" data-id="cgst" readonly style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_sgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_sgst" class="w-95-parsent bill_sundry_tax_type  form-select" name="bill_sundry[]" data-id="sgst">
                                       <?php
                                       foreach($billsundry as $value){ 
                                          if($value->nature_of_sundry=='SGST'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_sgst" id="sundry_option_sgst" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50"><span name="tax_amt[]" class="tax_amount" id="tax_amt_sgst"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_sgst"></td>
                                 <td class="w-min-50"><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_sgst" data-id="sgst" readonly  style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_igst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_igst" class="w-95-parsent bill_sundry_tax_type  form-select" name="bill_sundry[]" data-id="igst">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='IGST'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_igst" id="sundry_option_igst" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50"><span name="tax_amt[]" class="tax_amount" id="tax_amt_igst"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_igst"></td>
                                 <td class="w-min-50"><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_igst" data-id="igst" readonly  style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <div class="plus-icon" >
                                 <tr class="font-14 font-heading bg-white" style="display: none;">
                                    <td class="w-min-120 " colspan="5" >
                                       <button type="button" class="btn btn-primary btn-xs"><a class="add_more_bill_sundry_gst"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a></button>
                                    </td>
                                 </tr>
                              </div>
                              <!-- <tr id="billtr_2" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_2" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="2">
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
                                 <td class="w-min-50"><span name="tax_amt[]" class="tax_amount" id="tax_amt_2"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_2"></td>
                                 <td class="w-min-50"><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_2" data-id="2" readonly  style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <div class="plus-icon">
                                 <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120 " colspan="5">
                                       <button type="button" class="btn btn-primary btn-xs"><a class="add_more_bill_sundry_down"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a></button>
                                    </td>
                                 </tr>
                              </div> -->
                              <tr id="billtr_round_plus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_plus" class="w-95-parsent bill_sundry_tax_type  form-select" name="bill_sundry[]" data-id="round_plus">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='ROUNDED OFF (+)'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_round_plus" id="sundry_option_round_plus" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50"><span name="tax_amt[]" class="tax_amount" id="tax_amt_round_plus"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_round_plus"></td>
                                 <td class="w-min-50"><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_plus" data-id="round_plus" readonly style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_round_minus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_minus" class="w-95-parsent bill_sundry_tax_type  form-select" name="bill_sundry[]" data-id="round_minus">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='ROUNDED OFF (-)'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>" class="sundry_option_round_minus" id="sundry_option_round_minus"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50"><span name="tax_amt[]" class="tax_amount" id="tax_amt_round_minus"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_round_minus"></td>
                                 <td class="w-min-50"><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_minus" data-id="round_minus" readonly style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-min-50 fw-bold">Total</td>
                                 <td class="w-min-50 fw-bold"></td>
                                 <td class="w-min-50 fw-bold">
                                    <span id="bill_sundry_amt" style="float:right ;"></span>
                                    <input type="hidden" name="total" id="total_amounts" value="0">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                        <table id="transcton-sale3" class="table-striped table m-0 shadow-sm table-bordered">
                           <tbody>                              
                              <div>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="4" class="pl-40"><button type="button" class="btn btn-info transport_info">Transport Info</button><button type="button" class="btn btn-info shipping_info" style="float: right;">Shipping Info</button></td>
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
                                          <button type="button" class="btn  btn-xs-primary save_transport_info">
                                                SAVE
                                          </button>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="modal fade" id="shipping_info_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                 <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content p-4 border-divider border-radius-8">
                                       <div class="modal-header border-0 p-0">
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                       </div>
                                       <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                                       Shipping Info
                                       </h5>
                                       <br>
                                       <div class="row">
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping Name</label>
                                             <select class="w-95-parsent form-select" id="shipping_name" id="shipping_name" name="shipping_name" onchange="getAccountDeatils(this)">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($party_list as $value) { ?>
                                                   <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                                   <?php 
                                                } ?>
                                             </select>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping Address</label>
                                             <input type="text" id="shipping_address" name="shipping_address" style="width: 95%;" class="form-control" placeholder="Shipping Address"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping Pincode</label>
                                             <input type="text" id="shipping_pincode" name="shipping_pincode" class="form-control" placeholder="Shipping Pincode"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping GST</label>
                                             <input type="text" id="shipping_gst" name="shipping_gst" class="form-control" placeholder="Shipping GST"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping PAN</label>
                                             <input type="text" id="shipping_pan" name="shipping_pan" class="form-control" placeholder="Shipping PAN"/>
                                          <input type="hidden" name="shipping_state" />
                                          </div>
                                       </div>
                                       <br>
                                       <div class="text-start">
                                          <button type="button" class="btn  btn-xs-primary save_shipping_info">
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
               <div class=" d-flex">
                  
                  <div class="ms-auto">
                     <input type="submit" value="SAVE" class="btn btn-xs-primary" id="saveBtn">
                     <a href="{{ route('sale.index') }}" class="btn  btn-black ">QUIT</a>
                  </div>
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
                  <span class="border-bottom-black">F1</span><span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Account</span>
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
               <button class="p-2 transaction-shortcut-btn mb-4 text-ellipsis d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search Menu">Search Menu
               </button>
            </div>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="parameter_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <p><h5 class="modal-title">Parameterized Stock Details (Sale Voucher)</h5></p>
            <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div style="font-size:20px">Item : <span id="parameter_item"></span></div>
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
</body>
@include('layouts.footer')
<script>
   var mat_series = "<?php echo count($GstSettings);?>";
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
   var customer_gstin = "";
   var merchant_gstin = "";
   var percent_arr = [];
   var add_more_count = 1;
   var add_more_counts = 1;
   var page_load = 0;
   var add_more_bill_sundry_up_count = 2;
   $(".add_more").click(function(){
      let empty_status = 0;
      $('.item_id').each(function(){   
         let i = $(this).attr('data-id');
         if($(this).val()=="" || $("#quantity_tr_"+i).val()=="" || $("#price_tr_"+i).val()==""){
            empty_status=1;            
         }                   
      });
      if(empty_status==1){
         alert("Please enter required fields");
         return;
      }
      let srn = $("#srn_"+add_more_count).html();
      srn++
      add_more_count++;
      var optionElements = $('#goods_discription_tr_1').html();
      //var selectHTML = $('#goods_discription').prop('outerHTML');
      var tr_id = 'tr_' + add_more_count;

      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50" id="srn_'+add_more_count+'">' + srn + '</td><td class=""><select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_'+add_more_count+'" data-id="'+add_more_count+'"><option value="">Select Item</option>@foreach($item as $item_list)<option value="{{$item_list->id}}" data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}" data-available_item="{{$item_list->available_item}}" data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>@endforeach';
      newRow += optionElements;
      newRow += '</td><td class="w-min-50"><input type="number" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '" placeholder="Quantity" style="text-align:right" data-id="'+add_more_count+'" /></td><td class="w-min-50"><input type="text" class="w-100 form-control unit" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;" data-id="'+add_more_count+'"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class="w-min-50"><input type="number" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '" placeholder="Price" style="text-align:right" data-id="'+add_more_count+'"/></td><td class="w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]" id="amount_tr_' + add_more_count + '" placeholder="Amount" style="text-align:right" data-id="'+add_more_count+'"/></td><input type="hidden" name="item_parameters[]" id="item_parameters_'+add_more_count+'"><input type="hidden" name="config_status[]" id="config_status_'+add_more_count+'"><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $("#max_sale_descrption").val(add_more_count);
      $("#example11").append(newRow);
      let k = 1;
      $('.item_id').each(function(){   
         let i = $(this).attr('data-id');
         $("#srn_"+i).html(k);  
         k++;           
      });
      $( ".select2-single, .select2-multiple" ).select2();
   });
   $(document).on("click", ".remove", function() {
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      var max_val = $("#max_sale_descrption").val();
      max_val--;
      $("#max_sale_descrption").val(max_val);
      calculateAmount();
   });
   $(document).ready(function(){
      // Properly initialize Select2 with search enabled
      $('#party_id').select2({
         placeholder: "Select Account",
         allowClear: true,
         width: '100%' // Ensure dropdown matches Bootstrap styling
      });
      // Move focus to next field after selecting an option
      $('#party_id').on('select2:select', function (e) {
         $('#material_center').focus();
      });
      
      // Function to calculate amount and update total sum
      window.calculateAmount = function(key=null) {         
         customer_gstin = $('#party_id option:selected').attr('data-state_code'); 
         if(customer_gstin==undefined){
            return;
         }            
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
         $('#example11 tbody tr').each(function() {         
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
                  console.log(item_taxable_amount);
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
                  console.log(item_taxable_amount);                 
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
                     $("#bill_sundry_"+add_more_bill_sundry_up_count).val(sundry_value);
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
            let nature_of_sundry = $('option:selected', this).attr('data-nature_of_sundry');
            let bill_date = $("#date").val();
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
         final_total = Math.round(final_total);
         var formattedNumber = final_total.toLocaleString('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
         });
         $("#bill_sundry_amt").html(formattedNumber);
         $("#total_amounts").val(final_total);         
         let roundoff = parseFloat(final_total) - parseFloat($("#total_taxable_amounts").val()) - parseFloat(gstamount);     
         console.log(parseFloat(final_total)+"-"+parseFloat($("#total_taxable_amounts").val())+"-"+ parseFloat(gstamount));    
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
      $("#saveBtn").click(function(){
         if(confirm("Are you sure to submit?")==true){            
            $("#saleForm").validate({
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
         }else{
            return false;
         }         
      });
      if(mat_series==1){
         $("#series_no").change();
      }
   });
   function call_fun(data){
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
   $(document).on("click", ".remove_sundry", function() {
      let id = $(this).attr('data-id');
      $("#billtr_" + id).remove();
      var max_val = $("#max_sale_sundry").val();
      max_val--;
      $("#max_sale_sundry").val(max_val);
      calculateAmount();
   });
   $(document).on("click", ".remove_sundry_up", function() {
      let id = $(this).attr('data-id');
      $("#billtr_" + id).remove();      
      calculateAmount();
   });   
   $( ".select2-single, .select2-multiple" ).select2();
   //Ashish Javascript   
   $(".add_more_bill_sundry_up").click(function() {
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
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';<?php 
         }
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100 form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50"><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50"><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
   });   
   $(".add_more_bill_sundry_down").click(function() {
      add_more_bill_sundry_up_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = "<option value=''>Select</option>";
      <?php
      foreach ($billsundry as $value) { 
         //if($value->effect_gst_calculation==0){?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>"><?php echo $value->name; ?></option>';
            <?php 
         //}
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100  form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50"><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50"><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr extra_gst"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100  form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50"><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50"><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;"></td><td class="w-min-50"></td></tr>';
      $curRow.before(newRow);
   });
   $('body').on('keydown', 'input, select', function(e){      
      if (e.key === "Enter") {
         var self = $(this), form = self.parents('form:eq(0)'), focusable, next;
         focusable = form.find('input,select,button,textarea').filter(':visible');
         next = focusable.eq(focusable.index(this)+1);
         if (next.length) {
            next.focus();
         } else {
            form.submit();
         }
         return false;
      }
   });
   $(".goods_items").change(function(){
      let id = $(this).attr('data-id');
      if($(this).val()==""){
         $("#goods_discription_tr_"+id+"-error").show();
      }else{
         $("#goods_discription_tr_"+id+"-error").hide();
      }
   });
   $('#date').keydown(function(e) {
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#series_no").focus();
         }
      }
   });
   $("#voucher_prefix").keyup(function(){
      $("#voucher_no").val($(this).val());
   });
   $("#series_no").change(function(){
      $("#voucher_prefix").prop('readonly',true);
      $("#voucher_no").attr('required',true);
      
      let series = $(this).val();
      let invoice_prefix = $('option:selected', this).attr('data-invoice_prefix');
      let last_bill_date = $('option:selected', this).attr('data-last_bill_date');
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
      $("#material_center").val($('option:selected', this).attr('data-mat_center'));
      merchant_gstin = $('option:selected', this).attr('data-gst_no');
      $("#merchant_gst").val(merchant_gstin);
      if($("#party_id").val()!=""){
         if($('#party_id option:selected').attr('data-state_code')==merchant_gstin.substring(0,2)){  
            $("#sale_type").val('LOCAL');
         }else{
            $("#sale_type").val('CENTER');
         }
      }
      $("#date").attr('min',last_bill_date)
      
      calculateAmount();
            
   });
   $('#voucher_no').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#date").focus();
         }
      }
   });
   $('#party_id').keydown(function(e) {
      if (e.keyCode === 8) {
         if($(this).val()==""){
         $("#voucher_no").focus();
         }         
      }
   });
   $('#material_center').keydown(function(e) {
      if (e.keyCode === 8) {
         $("#party_id").focus();         
      }
   });   
   $(document).on('keydown','.bill_sundry_tax_type',function(e){    
      if (e.keyCode === 8) {
         let id = $(this).attr('data-id');  
         
         if(id==1){
            $(".amount").focus();
         }else if(id==3){
            $("#bill_sundry_amount_1").focus();
         }else{
            id--;

            $("#bill_sundry_amount_"+id).focus();
         }    
      }
   });
   $('#ewaybill_no').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#shipping_pan").focus();
         }
      }
   });
   $('#shipping_pan').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#shipping_gst").focus();
         }
      }
   });
   $('#shipping_gst').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#shipping_pincode").focus();
         }
      }
   });
   $('#shipping_pincode').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#shipping_address").focus();
         }
      }
   });
   $('#shipping_address').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#shipping_name").focus();
         }
      }
   });
   $('#shipping_name').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#station").focus();
         }
      }
   });
   $('#station').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#gr_pr_no").focus();
         }
      }
   });
   $('#gr_pr_no').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#reverse_charge").focus();
         }
      }
   });
   $('#reverse_charge').keydown(function(e) {      
      if (e.keyCode === 8) {
         if($(this).val()==""){
            $("#transport_name").focus();
         }
      }
   });   
   $(document).on('change', '#party_id', function(){
      if($('option:selected', this).attr('data-state_code')==merchant_gstin.substring(0,2)){  
         $("#sale_type").val('LOCAL');
      }else{
         $("#sale_type").val('CENTER');
      }
      $("#partyaddress").html('');   
      $("#partyaddress").html("GSTIN : "+$('option:selected', this).attr('data-gstin')+"<br>Address : "+$('option:selected', this).attr('data-address'));
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
   $(document).on('change', '.item_id', function(){
      $('#unit_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-val'));
      $('#units_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-unit_id'));
      $('#unit_tr_'+$(this).attr('data-id')).attr('data-parameterized_stock_status',$('option:selected', this).attr('data-parameterized_stock_status'));
      $('#unit_tr_'+$(this).attr('data-id')).attr('data-group_id',$('option:selected', this).attr('data-group_id'));
      $('#unit_tr_'+$(this).attr('data-id')).attr('data-config_status',$('option:selected', this).attr('data-config_status'));
      call_fun('tr_'+$(this).attr('data-id'));

      $('#item_id_'+$(this).attr('data-id')).select2({
         placeholder: "Select Item",
         allowClear: true,
         width: '100%' // Ensure dropdown matches Bootstrap styling
      });
      // Move focus to next field after selecting an option
      $('#item_id_'+$(this).attr('data-id')).on('select2:select', function (e) {
         $('#quantity_tr_'+$(this).attr('data-id')).focus();
      });
   });
   $(document).on('change', '.quantity',function(){
      let id = $(this).attr("data-id");
      let item_id = $("#item_id_"+id).val();
      let available_weight = $("#item_id_"+id).attr('data-available_item');
      let asssign_weight = 0;
      $(".quantity").each(function(){
         if($("#item_id_"+$(this).attr('data-id')).val()==item_id){
            asssign_weight = parseFloat(asssign_weight) + parseFloat($(this).val());
         }
      });
      if(asssign_weight>available_weight){
         alert("Please Check Quantity Greater Than Available Quantity");
      }
   });
   var modal_item_arr = [];
   $(document).on('click',".unit",function(){
      let parameter_qty = $("#quantity_tr_"+$(this).attr('data-id')).val()+" "+$(this).val();
      let parameter_name = $("#item_id_"+$(this).attr('data-id')).val();
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
         url: '{{url("get-item-parameter")}}',
         async: false,
         type: 'POST',
         dataType: 'JSON',
         data: {
            _token: '<?php echo csrf_token() ?>',
            config_status: config_status,
            parameterized_stock_status : parameterized_stock_status,
            group_id : group_id,
            item_id: item_id
         },
         success: function(data){  
            if(data.data.head.length==0 || data.data.data.length==0){
               return;
            }
            let html = "<table class='table table-bordered'><thead><tr>";
            data.data.head.forEach(function(e,i){
               if(e.parameter_col_id!=0){
                  let style = "";
                  if(i==0){
                     style = "style='width: 45%;'";
                  }
                  html+='<th '+style+'>'+e.parameter_column_name.paremeter_name+'</th>'; 
               }                        
            });
            html+='<th>Qty</th>';
            html+='</tr></thead><tbody>';            
            data.data.data.forEach(function(e,i){
               let v = ""; let option_value = "";
               let size_id = 0;let reel_id = 0;let update_id = 0;let weight_id = 0;
               let size = "";let reel = "";let weight = "";
               e.forEach(function(v1,k1){
                  if(k1==0){
                     option_value = v1.value;
                  }
                  v+=v1.name+" - "+v1.value+" | ";
                  let alternative_unit = v1.alternative_unit;
                  let conversion_factor = v1.conversion_factor;
                  if(alternative_unit==0 && conversion_factor==0){
                     size_id = v1.id;
                     size = v1.value;
                  }else if(alternative_unit==1){
                     reel_id = v1.id;
                     reel = v1.value;
                  }else if(conversion_factor==1){
                     weight_id = v1.id;
                     weight = v1.value;
                  }                  
                  if(v1.alternative_unit==1){
                     update_id = v1.id;
                  }
               });

               modal_item_arr.push({"size":size,"size_id":size_id,"reel":reel,"reel_id":reel_id,"update_id":update_id,"weight":weight,"weight_id":weight_id,'text':v});
               
            });            
            let index = 1;
            while(index<10){
               html+='<tr>';
               data.data.head.forEach(function(e,i){
                  if(e.parameter_col_id!=0){
                     let alternative_unit = e.parameter_column_name.alternative_unit;
                     let conversion_factor = e.parameter_column_name.conversion_factor;
                     if(alternative_unit==0 && conversion_factor==0){                        
                        html+='<th><input type="text" data-id="'+index+'" id="param_item_size_'+index+'" class="form-control input-lg param_item_size" ><div id="item_list_'+index+'" ></div></th>';
                     }else if(alternative_unit==1){
                        html+='<th><input type="text" class="form-control param_item_reel" id="param_item_reel_'+index+'" data-id="'+index+'"  ></th>'; 
                     }else if(conversion_factor==1){
                        html+='<th><input type="text" class="form-control" id="param_item_weight_'+index+'" data-id="'+index+'"  readonly></th>'; 
                     }else{
                        html+='<th><input type="text" class="form-control"  data-id="'+index+'"  readonly></th>'; 
                     }                
                  }                   
               });
               html+='<th><input type="text" class="form-control param_item_qty" id="param_item_qty_'+index+'" data-id="'+index+'"  readonly></th>'; 
               index++;               
               html+='</tr>';
            }  
            html+='<tr>';
            data.data.head.forEach(function(e,i){
               if(e.parameter_col_id!=0){
                  html+='<th></th>'; 
               }                   
            });   
            html+='<th><input type="text" class="form-control"  readonly id="total_weight"></th>';
            html+='</tr>';    
            html+='</tbody></table>';
            $(".parameter_body").html(html);
            $("#parameter_modal_id").val(id);            
            $("#parameter_modal").modal('toggle');
         }
      });
   });
   // $(document).on('change','.param_item_size',function(){
   //    let id = $(this).attr('data-id');
   //    $("#param_item_weight_"+id).val($('option:selected', this).attr('data-weight'));
   //    $("#param_item_reel_"+id).val($('option:selected', this).attr('data-reel'));
   //    calculateWeight(id);
   // });
     
   var content = "";var selected_item_arr = [];
   $(document).on('keyup','.param_item_size',function(){
      let search = $(this).val();
      let id = $(this).attr('data-id');
      content = '<ul class="dropdown-menu" style="display:block; position:relative">';
      let i = 1;
      if(search==""){         
         if($(this).attr('data-selected_id')){
            const index = selected_item_arr.indexOf(parseInt($(this).attr('data-selected_id')));
            if (index > -1) { // only splice array when item is found
               selected_item_arr.splice(index, 1); // 2nd parameter means remove one item only
            }
            
         }
         $("#param_item_weight_"+id).val('');
         $("#param_item_reel_"+id).val('');
         calculateWeight(id);
      }
      modal_item_arr.forEach(function(e){
         if(jQuery.inArray(i, selected_item_arr) == -1){
            if(e.size.trim()==search.trim()){
               content+='<li class="modal_item_li" data-selected='+i+' data-id="'+id+'" data-size="'+e.size+'" data-size_id="'+e.size_id+'" data-reel="'+e.reel+'" data-reel_id="'+e.reel_id+'" data-update_id="'+e.update_id+'" data-weight="'+e.weight+'" data-weight_id="'+e.weight_id+'" data-value="'+e.size+'"><a href=" javascript:void(0)"><table class="table table-bordered"><tr><td>'+e.size+'</td><td>'+e.weight+'</td><td>'+e.reel+'</td></tr></table></a></li>';
            }
         }         
         i++;
      });
      content+='</ul>';
      $('#item_list_'+id).fadeIn();  
      $('#item_list_'+id).html(content);
      
   });
   $(document).on('click', '.modal_item_li', function(){  
      let item_index_id = $(this).attr('data-id');
      selected_item_arr.push(parseInt($(this).attr('data-selected')));
      $('#param_item_size_'+item_index_id).val($(this).attr('data-size'));
      $('#param_item_size_'+item_index_id).attr('data-reel',$(this).attr('data-reel'));
      $('#param_item_size_'+item_index_id).attr('data-size_id',$(this).attr('data-size_id'));
      $('#param_item_size_'+item_index_id).attr('data-reel_id',$(this).attr('data-reel_id'));
      $('#param_item_size_'+item_index_id).attr('data-weight_id',$(this).attr('data-weight_id'));
      $('#param_item_size_'+item_index_id).attr('data-update_id',$(this).attr('data-update_id'));
      $('#param_item_size_'+item_index_id).attr('data-selected_id',parseInt($(this).attr('data-selected')));
      $('#item_list_'+item_index_id).fadeOut();
      $("#param_item_weight_"+item_index_id).val($(this).attr('data-weight'));
      $("#param_item_reel_"+item_index_id).val($(this).attr('data-reel'));
      calculateWeight(item_index_id);
   });
   function calculateWeight(index){
      let param_item_reel = $("#param_item_reel_"+index).val();
      let param_item_item = $("#param_item_weight_"+index).val();
      if(param_item_reel==""){
         param_item_reel = 0;
      }
      if(param_item_item==""){
         param_item_item = 0;
      }
      let t = parseFloat(param_item_reel)*parseFloat(param_item_item);
      if(t==0){
         t = "";
      }
      $("#param_item_qty_"+index).val(t);
      //Total calculation
      let total = 0;
      $(".param_item_qty").each(function(){
         if($(this).val()!=""){
            total = parseFloat(total) + parseFloat($(this).val());
         }
      });
      $("#total_weight").val(total);
   }
   $(document).on('keyup','.param_item_reel',function(){
      let actual_val = $("#param_item_size_"+$(this).attr('data-id')).attr('data-reel');
      let updated_val = $(this).val();
      if(actual_val<updated_val){
         alert("Invalid Quantity");
         $(this).val('');
      }
      calculateWeight($(this).attr('data-id'));
   });
   $(".parameter_save_btn").click(function(){
      let arr = [];
      $(".param_item_size").each(function(){
         let id = $(this).attr('data-id');
         if($(this).val()!="" && $("#param_item_reel_"+id).val()!="" && $("#param_item_weight_"+id).val()!=""){
            let sale_info = [];
            sale_info.push({'value':$(this).val(),"id":$(this).attr('data-size_id')});
            sale_info.push({'value':$("#param_item_reel_"+id).val(),"id":$(this).attr('data-reel_id')});
            sale_info.push({'value':$("#param_item_weight_"+id).val(),"id":$(this).attr('data-weight_id')});
            arr.push({"reel":$("#param_item_reel_"+id).val(),"id":$(this).attr('data-update_id'),"sale_info":sale_info});
         }         
      });
      $("#item_parameters_"+$("#parameter_modal_id").val()).val(JSON.stringify(arr));
      $("#quantity_tr_"+$("#parameter_modal_id").val()).val($("#total_weight").val());
      $("#parameter_modal").modal('toggle');
   });
   $(".transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   });
   $(".save_transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   });   
   $(".shipping_info").click(function(){
      $("#shipping_info_modal").modal('toggle');
   });
   $(".save_shipping_info").click(function(){
      $("#shipping_info_modal").modal('toggle');
   }); 
</script>
@endsection