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
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if(session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success')}}
               </div>
            @endif            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Purchase Return/Debit Note</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('purchase-return.store') }}" id="purchaseReturnForm">
               @csrf
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="nature" class="form-label font-14 font-heading">NATURE</label>
                     <select id="nature" name="nature" class="form-select" required onChange="sectionHideShow();">
                        <option value="">Select</option>
                        <option value="WITH GST">WITH GST</option>
                        <option value="WITHOUT GST">WITHOUT GST</option>
                     </select>
                     <ul style="color: red;">
                        @error('date'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4 type_div" style="display:none">
                     <label for="type" class="form-label font-14 font-heading">TYPE</label>
                     <select id="type" name="type" class="form-select" onChange="sectionHideShow()">
                        <option value="">Select</option>
                        <option value="WITH ITEM">WITH ITEM</option>
                        <option value="WITHOUT ITEM">WITHOUT ITEM</option>
                        <option value="RATE DIFFERENCE">RATE DIFFERENCE</option>
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="date" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" value="{{$bill_date}}" placeholder="Select date" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     <ul style="color: red;">
                        @error('date'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-4 col-md-4 account_div">
                     <label for="party_id" class="form-label font-14 font-heading">Accounts</label>
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
                  <div class="mb-3 col-md-3 voucher_no_div" style="display:none">
                     <label for="voucher_no" class="form-label font-14 font-heading">Invoice No. *</label>
                     <select class="form-select select2-single" id="voucher_no" name="voucher_no">
                        <option value="">Select</option>
                     </select>                     
                     <input type="hidden" name="voucher_type" id="voucher_type">
                     <input type="hidden" name="purchase_bill_id" id="purchase_bill_id">
                  </div>
                  <div class="mb-3 col-md-3 other_invoice_div" style="display:none">
                     <label for="other_invoice_against" class="form-label font-14 font-heading">Invoice Against</label>
                     <select class="form-select" id="other_invoice_against" name="other_invoice_against">
                        <option value="">Select</option>
                        <option value="SALE">Sale</option>
                        <option value="PURCHASE">Purchase</option>
                     </select>  
                  </div>
                  <div class="mb-3 col-md-2 other_invoice_div" style="display:none">
                     <label for="other_invoice_no" class="form-label font-14 font-heading">Original Invoice No</label>
                     <input type="text" class="form-control" id="other_invoice_no" name="other_invoice_no" placeholder="Enter Invoice No.">
                  </div>
                  <div class="mb-3 col-md-2 other_invoice_div" style="display:none">
                     <label for="other_invoice_date" class="form-label font-14 font-heading">Original Invoice Date</label>
                     <input type="date" class="form-control" id="other_invoice_date" name="other_invoice_date">
                  </div>
                  <div class="mb-3 col-md-2 other_invoice_div" style="display:none">
                     <label for="other_invoice_date" class="form-label font-14 font-heading">Original Invoice Value</label>
                     <input type="number" class="form-control" id="original_invoice_value" name="other_invoice_value" placeholder="Including taxes">
                  </div>

                  <div class="mb-1 col-md-1 voucher_no_div" style="display:none">
                     <br>
                     <a href="" title="View Invoice" target="__blank" id="invoice_id"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" style="margin-top: 20px;"></a>
                  </div>
                  <div class="mb-4 col-md-3">
                     <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" name="series_no" class="form-select" required>
                        <option value="">Select</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->series; ?>" data-mat_center="<?php echo $value->mat_center;?>" data-gst_no="<?php echo $value->gst_no;?>" data-invoice_prefix="<?php echo $value->invoice_prefix;?>" data-invoice_prefix_wt="<?php echo $value->invoice_prefix_wt;?>"  data-invoice_start_from="<?php echo $value->invoice_start_from;?>" data-without_invoice_start_from="<?php echo $value->without_invoice_start_from;?>" data-manual_enter_invoice_no="<?php echo $value->manual_enter_invoice_no;?>" data-duplicate_voucher="<?php echo $value->duplicate_voucher;?>" data-blank_voucher="<?php echo $value->blank_voucher;?>"><?php echo $value->series; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <ul style="color: red;">
                       @error('series_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="material_center" class="form-label font-14 font-heading">Material Center</label>
                     <select name="material_center" id="material_center" class="form-select" required>
                        <option value="">Select</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->mat_center; ?>"><?php echo $value->mat_center; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <ul style="color: red;">
                       @error('material_center'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="voucher_prefix" class="form-label font-14 font-heading">Debit Note No.</label>
                        <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" value=""  readonly style="text-align: right;">
                        <input type="hidden" id="purchase_return_no" class="form-control" name="purchase_return_no"  readonly style="width: 30%;">
                        <input type="hidden" class="form-control" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                        <input type="hidden" class="form-control" id="merchant_gst" name="merchant_gst">
                                         
                  </div>
               </div>
               <div class=" transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4 with_gst_with_item_section" style="display:none">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="width: 36%;">Description of Goods
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
                           <td class="w-min-50">
                              <select onchange="call_fun('tr_1');" class="border-0  goods_items form-select" id="goods_discription_tr_1" name="goods_discription[]"  data-id="1">
                                 <option value="">Description of Goods</option>
                                 @foreach($manageitems as $item)
                                    <option value="{{$item->id}}" unit_id="{{$item->u_name}}" data-val="{{$item->unit}}"  data-percent="{{$item->gst_rate}}">{{$item->name}}</option>
                                 @endforeach
                              </select>
                           </td>                           
                           <td class="w-min-50">
                              <input type="number" class="quantity w-100 form-control" id="quantity_tr_1" name="qty[]" placeholder="Quantity" style="text-align:right;" />
                           </td>
                           <td class="w-min-50">
                              <input type="text" class="w-100 form-control" id="unit_tr_1" readonly style="text-align:center;" />
                              <input type="hidden" class="units" name="units[]" id="units_tr_1" />
                           </td>
                           <td class="w-min-50">
                              <input type="number" class="price form-control" id="price_tr_1" name="price[]" placeholder="Price" style="text-align:right;" />
                           </td>
                           <td class="w-min-50">
                              <input type="number" id="amount_tr_1" class="amount w-100 form-control" name="amount[]" placeholder="Amount" style="text-align:right;" />
                           </td>
                           <td class="">
                              <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                           </td>
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
               <div class="row with_gst_with_item_section" style="display:none">
                  <div class="col-lg-5">
                     <div class="transaction-table transacton-extra-table bg-white table-view shadow-sm border-radius-8 mb-4">
                        <table id="transcton-sale11" class="table-striped table m-0 shadow-sm table-bordered">
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
                                          if($value->nature_of_sundry=='OTHER'){?>
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
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_1" data-id="1" readonly  style="text-align:right;">
                                 </td>
                                 <td>
                                    <svg xmlns="http://www.w3.org/2000/svg" style="cursor:pointer;"class="bg-primary rounded-circle add_more_bill_sundry_up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
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
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_cgst" data-id="cgst" readonly  style="text-align:right;"></td>
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
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_sgst" data-id="sgst" readonly  style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_igst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_igst" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="igst">
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
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_igst" data-id="igst" readonly  style="text-align:right;"></td>
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
                              <tr id="billtr_round_plus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_plus" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="round_plus">
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
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_plus" data-id="round_plus" readonly  style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_round_minus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_minus" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="round_minus">
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
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_minus" data-id="round_minus" readonly  style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-min-50 fw-bold">Total</td>
                                 <td class="w-min-50 fw-bold"></td>
                                 <td class="w-min-50 fw-bold">
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
                                             <label for="vehicle_no" class="form-label font-14 font-heading">Vehicle No.</label>
                                             <input type="text" name="vehicle_no" id="vehicle_no" class="form-control" placeholder="Vehicle No." />
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="transport_name" class="form-label font-14 font-heading">Transport Name</label>
                                             <input type="text" id="transport_name" name="transport_name" class="form-control" placeholder="Transport Name" />
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="gr_pr_no" class="form-label font-14 font-heading">GR/RR No.</label>
                                             <input type="text" id="gr_pr_no" name="gr_pr_no" class="form-control" placeholder="GR/RR No"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="station" class="form-label font-14 font-heading">Station</label>
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
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <!-- With Gst WithOut Item Section Start -->
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4 with_gst_without_item_section" style="display:none">
                  <table class="table-striped table m-0 shadow-sm table-bordered with_gst_section">
                     <tbody>
                        <tr class="font-14 font-heading bg-white">
                           <td style="width:50%">
                              <select class="form-control item select2-single" id="item_1" data-index="1" name="item[]" onchange="gstCalculation()" >
                                 <option value="">Select Item</option>
                                 @foreach($items as $item)
                                    <option value="{{$item->id}}">{{$item->account_name}}</option>
                                 @endforeach
                              </select>
                           </td>
                           <td>
                              <input type="number" class="form-control hsn" id="hsn_1" name="hsn[]" placeholder="HSN/SAC">
                           </td>
                           <td style="width:15%">
                              <select class="form-select percentage" id="percentage_1" data-index="1" name="percentage[]" onchange="gstCalculation()">
                                 <option value="">GST(%)</option>
                                 <option value="5">5%</option>
                                 <option value="12">12%</option>
                                 <option value="18">18%</option>
                                 <option value="28">28%</option>
                              </select>
                           </td>
                           <td>
                              <input type="text" class="form-control amount" id="amount_1" data-index="1" name="without_item_amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()">
                           </td>
                           <td>
                              <svg xmlns="http://www.w3.org/2000/svg" style="cursor:pointer;" class="bg-primary rounded-circle add_more_tr" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"></path></svg>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td></td>
                           <td style="text-align: right;">Net Amount</td>
                           <td>
                              <input type="text" class="form-control" id="net_amount" name="net_amount" placeholder="Net Amount" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white cgst_tr" style="display: none;">
                           <td></td>
                           <td></td>
                           <td style="text-align: right;">CGST</td>
                           <td>
                              <input type="text" class="form-control" id="cgst" name="cgst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white sgst_tr" style="display: none;">
                           <td></td>
                           <td></td>
                           <td style="text-align: right;">SGST</td>
                           <td>
                              <input type="text" class="form-control" id="sgst" name="sgst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white igst_tr" style="display: none;">
                           <td></td>
                           <td></td>
                           <td style="text-align: right;">IGST</td>
                           <td>
                              <input type="text" class="form-control" id="igst" name="igst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td></td>
                           <td style="text-align: right;">Total Amount</td>
                           <td>
                              <input type="text" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td></td>
                           <td style="text-align: right;">Remark</td>
                           <td>
                              <input type="text" class="form-control" name="remark" placeholder="Enter Remark">
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
                <!-- WithOut Gst Section Start -->
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4 without_gst_section" style="display:none">
                  <?php
                  $account_html = '<option value="">Select</option>';            
                  foreach ($all_account_list as $value) {
                     $account_html.='<option value="'.$value->id.'">'.$value->account_name.'</option>';
                  }?>
                  <table id="without_gst_section" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                          <th class="w-min-120 border-none bg-light-pink text-body" style="width: 10%;text-align:left">S. NO. </th> 
                           <th class="w-min-120 border-none bg-light-pink text-body" style="width: 60%;">Account</th>
                           <th class="w-min-120 border-none bg-light-pink text-body" style="width: 30%;">Amount</th>
                           
                        </tr>
                     </thead>
                     <tbody>
                        <tr class="font-14 font-heading bg-white" id="tr_without_1">
                           <td class="srn" id="srn_1" style="text-align:left;">1.</td>

                           <td class="">
                              
                              <select class="form-select select2-single account" id="account_1" name="account_name[]" data-id="1">
                                 <option value="">Select</option>
                                 <?php
                                 foreach ($all_account_list as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                    <?php 
                                 } ?>
                              </select>
                           </td>
                           <td class="">
                              <input type="number" name="debit[]" class="form-control debit" data-id="1" id="debit_1" placeholder="Debit Amount" onkeyup="debitTotal();">
                           </td>
                           
                           <td class="action-cell" style="display: flex;">
                           <svg xmlns="http://www.w3.org/2000/svg" 
                           class="bg-primary rounded-circle add_more_without"
                           data-id="1"
                            width="24" height="24"
                             viewBox="0 0 24 24"
                              fill="none" 
                              style="cursor: pointer;">
                              <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                           </svg>
                           </td>
                        </tr>
                        
                     </tbody>
                     
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 fw-bold">Total</td>
                           <td class="w-min-120 fw-bold" id="total_debit" name="total_debit"></td>
                           <td></td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td colspan="3"><input type="text" class="form-control" placeholder="Enter Long Narration" name="long_narration"></td>
                        </tr>
                     </div>
                  </table>
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <input type="submit" value="SAVE" class="btn btn-xs-primary" id="purchaseReturnBtn">
                     <a href="{{ route('purchase-return.index') }}" class="btn  btn-black ">QUIT</a>
                  </div>
                  <input type="hidden" clas="max_sale_descrption" name="max_sale_descrption" value="1" id="max_sale_descrption">
                  <input type="hidden" name="max_sale_sundry" id="max_sale_sundry" value="1" />
               </div>
            </form>
         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key w-100">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help">F1<span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Help</span>
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
   var bill_sundry_array = @json($billsundry);
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
   var customer_gstin = "";
   var merchant_gstin = "";
   var percent_arr = [];
   var add_more_count = 1;
   var add_more_counts = 1;
   var add_more_bill_sundry_up_count = 2;
   $(".add_more").click(function() {
      let empty_status = 0;
      $('.goods_items').each(function(){   
         let i = $(this).attr('data-id');
         if($(this).val()=="" || $("#amount_tr_"+i).val()==""){
            empty_status=1;            
         }                   
      });
      if(empty_status==1){
         alert("Please enter required fields");
         return;
      }
      add_more_count++;
      var optionElements = $('#goods_discription_tr_1').html();
      var tr_id = 'tr_' + add_more_count;
      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50">' + add_more_count + '</td><td class=""><select onchange="call_fun(\'tr_' + add_more_count + '\');" id="goods_discription_tr_' + add_more_count + '" class="border-0 w-95-parsent  goods_items form-select" name="goods_discription[]" required data-id="'+add_more_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class=""><input type="number" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '" placeholder="Quantity"  style="text-align:right;" /></td><td class="w-min-50"><input type="text" class="w-100 form-control form-control" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class=" w-min-50"><input type="number" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '" placeholder="Price"  style="text-align:right;" /></td><td class=" w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]" id="amount_tr_' + add_more_count + '" placeholder="Amount"  style="text-align:right;"/></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $("#max_sale_descrption").val(add_more_count);
      $("#example11").append(newRow);
      $("#goods_discription_tr_"+add_more_count).select2();
   });
   $(document).on("click", ".remove", function() {
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      var max_val = $("#max_sale_descrption").val();
      max_val--;
      $("#max_sale_descrption").val(max_val);
      calculateAmount();
   });
   $('#voucher_no').change(function() {
      // Get the selected value
      $("#invoice_id").show();
      $(".other_invoice_div").hide();
      $("#other_invoice_no").val('');
      $("#other_invoice_date").val('');
      $("#other_invoice_against").attr('required', false);
      if($(this).val()=='OTHER'){
         $("#voucher_type").val('OTHER');
         $("#invoice_id").hide();
         $(".other_invoice_div").show();
         $("#other_invoice_against").attr('required', true);
         return;
      }
      let voucher_type = $('option:selected', this).attr('data-voucher_type');
      $(".extra_taxes_row").remove();
      $("#bill_sundry_tr_1").val('');
      $("#bill_sundry_amount_1").val('');
      $("#tax_rate_tr_1").val('');
      if(voucher_type=="PURCHASE"){
         $("#invoice_id").attr('href',"{{ URL::to('purchase-invoice/')}}/"+$('option:selected', this).attr('data-id'));
      }else if(voucher_type=="SALE"){
         $("#invoice_id").attr('href',"{{ URL::to('sale-invoice/')}}/"+$('option:selected', this).attr('data-id'));
      }
      
      $("#purchase_bill_id").val($('option:selected', this).attr('data-id'));
      $("#voucher_type").val(voucher_type);
      $("#series_no").val($('option:selected', this).attr('data-series_no'));
      $("#material_center").val($('option:selected', this).attr('data-material_center'));
      $("#voucher_prefix").val($('option:selected', this).attr('data-series_no')+"/{{Session::get('default_fy')}}/DR");
      var invoice_id = $(this).val();
      let series_no = $('option:selected', this).attr('data-series_no');
      
      $.ajax({
         url: '{{url("get/purchaseitems/details")}}',
         async: false,
         type: 'POST',
         dataType: 'JSON',
         data: {
            _token: '<?php echo csrf_token() ?>',
            voucher_no: invoice_id,
            voucher_type: voucher_type,
            id:$('option:selected', this).attr('data-id')
         },
         success: function(data) {
            var optionElements = '<option value="">Select</option>';
            $.each(data, function(key, val) {
               optionElements += '<option unit_id="' + val.unit_id + '" data-val="' + val.unit + '" value="' + val.item_id + '" data-percent="' + val.gst_rate + '">' + val.items_name + '</option>';
            });
            $("#goods_discription_tr_1").html(optionElements);
            $("#series_no").change();
         }
      });
   });
  $(document).ready(function() {
      
      $('#party_id').select2({
         placeholder: "Select Account",
         allowClear: true,
         width: '100%' // Ensure dropdown matches Bootstrap styling
      });

      // Move focus to next field after selecting an option
      $('#party_id').on('select2:select', function (e) {
         $('#voucher_no').focus();
      });
      // Properly initialize Select2 with search enabled
      $('#voucher_no').select2({
         placeholder: "Select Account",
         allowClear: true,
         width: '100%' // Ensure dropdown matches Bootstrap styling
      });

      // Move focus to next field after selecting an option
      $('#voucher_no').on('select2:select', function (e) {
         $('#series_no').focus();
      });      
      // Function to calculate amount and update total sum
      
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
         $('.goods_items').each(function(){   
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
                           }                           
                        });
                     }
                  } //New Changes By Ashish
                  
                  if(i==0){
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount) + parseFloat(bill_sundry_total);
                  }else{
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount);
                  }
                  on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(total_item_taxable_amount);
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
                     $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['cgst']);
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
                           }                           
                        });
                     }
                  }  //New Changes By Ashish
                  if(i==0){
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount) + parseFloat(bill_sundry_total);
                  }else{
                     total_item_taxable_amount = total_item_taxable_amount + parseFloat(item_taxable_amount);
                  }
                  
                  on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(total_item_taxable_amount);                 

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
                     $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['igst']);
                     
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
           $("#bill_sundry_amount_"+id).addClass('sundry_amt_'+$(this).val());
           calculateAmount();
        });
        
        $(document).on('input', '.bill_amt',function(){
           calculateAmount($("#bill_sundry_tr_"+$(this).attr('data-id')).val());
        });
         $("#purchaseReturnBtn").click(function(){
            let nature = $("#nature").val();
            let type = $("#type").val();
            if(confirm("Are you sure to submit?")==true){
               if(nature=="WITHOUT GST"){
                  $("#purchaseReturnForm").validate({
                     ignore: [], 
                     rules: {
                        series_no: "required",
                        material_center: "required",
                        "account_name[]": "required",
                        "without_type[]" : "required",
                     },
                     messages: {
                        series_no: "Please select series no",
                        material_center: "Please select material center",
                        "account_name[]" : "Please Select Account",
                        "without_type[]" : "Please Select Type"               
                     }
                  });
               }else if((nature=="WITH GST" && type=="WITH ITEM") || nature=="RATE DIFFERENCE"){
                  $("#purchaseReturnForm").validate({
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
               }else if((nature=="WITH GST" && type=="WITHOUT ITEM")){
                  $("#purchaseReturnForm").validate({
                     ignore: [], 
                     rules: {
                        series_no: "required",
                        party: "required",
                        material_center: "required",
                        total_amount: "required",
                        "item[]": "required",
                        "percentage[]" : "required",
                        "without_item_amount[]" : "required",
                     },
                     messages: {
                        series_no: "Please select series no",
                        party: "Please select party",
                        material_center: "Please select material center",
                        total_amount: "Total Amount Required",
                        "item[]" : "Please select item",
                        "percentage[]" : "Please enter percentage",
                        "without_item_amount[]" : "Please enter amount",                
                     }
                  });
               }else{
                  
                  $("#saleReturnForm").validate({
                     ignore: [], 
                     rules: {
                        nature: "required",
                     },
                     messages: {
                        nature: "Please select nature",              
                     }
                  });
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
      var selectedOptionData = $('#goods_discription_' + data + ' option:selected').data('val');
      var item_units_id = $('#goods_discription_' + data + ' option:selected').attr('unit_id');
      var itemId = $('#goods_discription_' + data + ' option:selected').val();
      var party_id = $('#party_id').val();
      if (party_id.length > 0) {
         $('#unit_' + data).val(selectedOptionData);
         $('#units_' + data).val(item_units_id);
         calculateAmount();
      }else{
         alert("Select Party Name First.");
         $('#unit_' + data).val(selectedOptionData);
         $('#units_' + data).val(item_units_id);      
         $('#goods_discription_'+data).select2("val","");
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
   //$( ".select2-single, .select2-multiple" ).select2(  );
   $(document).on("click", ".remove_sundry_up", function() {
      let id = $(this).attr('data-id');
      $("#billtr_" + id).remove();      
      calculateAmount();
   });   
   //$( ".select2-single, .select2-multiple" ).select2();
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
         if($value->nature_of_sundry=='OTHER'){?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';<?php 
         }
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100 form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
   });   
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
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100 form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;"></td><td class="w-min-50"></td></tr>';
      $curRow.before(newRow);
   });
   
   $("#voucher_no").change(function(){
      if($(this).val()==""){
         $("#voucher_no-error").show();
         return;
      }
      $("#voucher_no-error").hide();
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
   $(".goods_items").change(function(){
      let id = $(this).attr('data-id');
      if($(this).val()==""){
         $("#goods_discription_tr_"+id+"-error").show();
      }else{
         $("#goods_discription_tr_"+id+"-error").hide();
      }
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
      $("#invoice_id").show();
      $(".other_invoice_div").hide();
      $("#other_invoice_no").val('');
      $("#other_invoice_date").val('');
      $("#other_invoice_against").attr('required', false);
      $("#partyaddress").html('');
      $("#partyaddress").html("GSTIN : "+$('option:selected', this).attr('data-gstin')+"<br>Address : "+$('option:selected', this).attr('data-address'));     
      var account_id = $("#party_id").val();
      $("#voucher_no").html("<option value=''>Select</option>");
      $.ajax({
         url: '{{url("get/purchaseinvoice/details")}}',
         async: false,
         type: 'POST',
         dataType: 'JSON',
         data: {
             _token: '<?php echo csrf_token() ?>',
            account_id: account_id
         },
         success: function(data) {
            var optionElements = '';
            $.each(data, function(key, val) {
               //  var val = JSON.parse(val);
               let voc_no = "";
               if(val.voucher_type=="PURCHASE"){
                  voc_no = val.voucher_no;
               }else{
                  voc_no = val.voucher_no_prefix;
               }
               optionElements += '<option value="' + voc_no + '" data-id="'+val.id+'" data-series_no="'+val.series_no+'" data-material_center="'+val.material_center+'" data-voucher_type="'+val.voucher_type+'">'+ voc_no + '</option>';
            });
            optionElements += '<option value="OTHER">OTHER</option>';
            $("#voucher_no").append(optionElements);
         }
      });
      calculateAmount();
   });
   $("#series_no").change(function(){
      let nature = $("#nature").val();
      if(nature==""){
         alert("Please Select Nature");
         $("#series_no").val('');
         return;
      }
      if($("#series_no").val()==""){
         $("#material_center").val("");
         $("#voucher_prefix").val("");
         $("#purchase_return_no").val("");
         return;
      }
      $("#voucher_prefix").prop('readonly',true);
      $("#purchase_return_no").attr('required',true);
      let series = $(this).val();
      let invoice_prefix = $('option:selected', this).attr('data-invoice_prefix');
      let invoice_prefix_wt = $('option:selected', this).attr('data-invoice_prefix_wt');
      let manual_enter_invoice_no = $('option:selected', this).attr('data-manual_enter_invoice_no');
      $("#manual_enter_invoice_no").val(manual_enter_invoice_no);
      let invoice_start_from = $('option:selected', this).attr('data-invoice_start_from');
      $("#material_center").val($('option:selected', this).attr('data-mat_center'));
      merchant_gstin = $('option:selected', this).attr('data-gst_no');
      $("#merchant_gst").val(merchant_gstin);
      if(nature=="WITHOUT GST"){
         if(manual_enter_invoice_no==0){
            if(invoice_prefix_wt!=""){
               $("#voucher_prefix").val(invoice_prefix_wt);
            }else{
               $("#voucher_prefix").val($('option:selected', this).attr('data-without_invoice_start_from'));
            }
            $("#purchase_return_no").val($('option:selected', this).attr('data-without_invoice_start_from'));
         }else{
            $("#purchase_return_no").attr('required',false);
            $("#voucher_prefix").val("");
            $("#voucher_prefix").prop('readonly',false);
         }         
      }else{
         if(manual_enter_invoice_no==0){
            if(invoice_prefix!=""){
               $("#voucher_prefix").val(invoice_prefix);
            }else{
               $("#voucher_prefix").val(invoice_start_from);
            }         
            $("#purchase_return_no").val(invoice_start_from);
         }else{
            $("#purchase_return_no").attr('required',false);
            $("#voucher_prefix").val("");
            $("#voucher_prefix").prop('readonly',false);
         }
      }      
      calculateAmount();          
   });
   $("#nature").change(function(){
      $("#series_no").change();
   });
   function sectionHideShow(){
      $(".with_gst_with_item_section").hide();
      $(".with_gst_without_item_section").hide();
      $(".without_gst_section").hide();
      $(".account_div").show();
      $(".type_div").hide();
      $(".voucher_no_div").show();
      $('#voucher_no').select2();
      let nature = $("#nature").val();
      let type = $("#type").val();
      if(nature=="WITHOUT GST"){
         $(".without_gst_section").show();
         $(".account").select2();
      }
      if(nature=="WITH GST"){
         $(".type_div").show();
      }
      if(nature=="WITHOUT GST"){
         $(".voucher_no_div").hide();
      }
      if(type=="RATE DIFFERENCE" || type=="WITH ITEM"){
         $(".voucher_no_div").show();
         $('#voucher_no').select2();
      }
      if(nature=="WITH GST" && (type=="WITH ITEM" || type=="RATE DIFFERENCE")){
         $(".with_gst_with_item_section").show();
         $(".item").select2();
      }
      if((nature=="WITH GST" && type=="WITHOUT ITEM")){
         $(".with_gst_without_item_section").show();
         $(".item").select2();
      }
   }
   $(".transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   });
   $(".save_transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   });
   function gstCalculation(){
      let vendor_gstin = $("#party_id option:selected").attr("data-state_code");
      let company_gstin = merchant_gstin.substr(0,2);
      let net_total = 0;
      let total_cgst = 0;
      let total_sgst = 0;
      let total_igst = 0;
      $(".item").each(function(){
         if($(this).val()!=""){
            let id = $(this).attr('data-index');
            let percentage = $("#percentage_"+id).val();
            let amount = $("#amount_"+id).val();
            if(percentage!="" && amount!=""){
               let IGST = amount*percentage/100;
               let CGST = amount*(percentage/2)/100;
               let SGST = CGST;               
               IGST = IGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               CGST = CGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               SGST = SGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0]; 
               total_cgst = parseFloat(total_cgst) + parseFloat(CGST);
               total_sgst = parseFloat(total_sgst) + parseFloat(SGST);
               total_igst = parseFloat(total_igst) + parseFloat(IGST);
               net_total = parseFloat(net_total) + parseFloat(amount);
            }                        
         }
      });
      $("#cgst").val("");
      $("#sgst").val("");
      $("#igst").val("");
      if(vendor_gstin==company_gstin){
         $(".cgst_tr").show();
         $(".sgst_tr").show();
         $(".igst_tr").hide();          
         $("#cgst").val(total_cgst.toFixed(2));
         $("#sgst").val(total_sgst.toFixed(2)); 
      }else{
         $("#igst").val(total_igst.toFixed(2));
         $(".cgst_tr").hide();
         $(".sgst_tr").hide();
         $(".igst_tr").show();
      }
      $("#net_amount").val(net_total);
      let tamount = parseFloat(net_total) + parseFloat(total_igst);
      $("#total_amount").val(Math.round(tamount));
   }
   var add_more_count_withgst = 1;
   $(".add_more_tr").click(function(){
      let empty_status = 0;
      $(".item").each(function(){
         if($(this).val()=="" || $("#amount_"+$(this).attr('data-index')).val()=="" || $("#percentage_"+$(this).attr('data-index')).val()=="" || $("#hsn_"+$(this).attr('data-index')).val()==""){
            empty_status = 1;
         }
      });
      if(empty_status==1){
         alert("Please enter required fields");
         return;
      }
      add_more_count_withgst++;
      var $curRow = $(this).closest('tr');
      let newRow = '<tr id="withgst_tr_'+add_more_count_withgst+'" class="font-14 font-heading bg-white"><td style="width:50%"><select class="form-control item" id="item_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="item[]" onchange="gstCalculation()"><option value="">Select Item</option>@foreach($items as $item)<option value="{{$item->id}}">{{$item->account_name}}</option>@endforeach </select></td><td><input type="number" class="form-control hsn" id="hsn_1" name="hsn[]" placeholder="HSN/SAC"></td><td><select class="form-select percentage" id="percentage_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="percentage[]" onchange="gstCalculation()"><option value="">GST(%)</option><option value="5">5%</option><option value="12">12%</option><option value="18">18%</option><option value="28">28%</option></select></td><td><input type="text" class="form-control amount" id="amount_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="without_item_amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_more_tr" data-id="'+add_more_count_withgst+'" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"></path></svg></td></tr>';
      $curRow.after(newRow);
      //$("#item_"+add_more_count_withgst).select2();
      $(".item").select2();
   });
   $(document).on("click", ".remove_more_tr", function() {
      let id = $(this).attr('data-id');
      $("#withgst_tr_" + id).remove();
      gstCalculation();
   });
   function onTypeChange(id){
      $("#debit_" + id).val('');
      $("#credit_" + id).val('');
      let debit_total = 0;
      $(".debit").each(function(){
         if($(this).val()!=""){
            debit_total = parseFloat(debit_total) + parseFloat($(this).val());
         }
      });
      let credit_total = 0;
      $(".credit").each(function(){
         if($(this).val()!=""){
            credit_total = parseFloat(credit_total) + parseFloat($(this).val());
         }
      });
      if($("#type_" + id).val() == "Credit"){
         $("#debit_" + id).prop('readonly', true);
         $("#credit_" + id).prop('readonly', false);
         let amount = debit_total - credit_total;
         if(amount>0){
            $("#credit_"+id).val(amount.toFixed(2));
         }
         $("#account_"+id).html('<?php echo $account_html;?>');
      }else if ($("#type_" + id).val() == "Debit"){
         $("#debit_" + id).prop('readonly', false);
         $("#credit_" + id).prop('readonly', true);
         let amount = credit_total - debit_total;
         if(amount>0){
            $("#debit_"+id).val(amount.toFixed(2));
         }
         $("#account_"+id).html('<?php echo $account_html;?>');
      }
      $("#account_"+id).html('<?php echo $account_html;?>');
      debitTotal();
      creditTotal();
   }   
   add_more_count3 = $(".account").length; // Start count based on existing rows

$(document).on("click", ".add_more_without", function () {
    let empty_status = 0;

    $(".account").each(function () {
        let id = $(this).attr("data-id");
        if ($(this).val() == "" || $("#debit_" + id).val() == "") {
            empty_status = 1;
        }
    });

    if (empty_status == 1) {
        alert("Please enter required fields");
        return;
    }

    add_more_count3++;
    var $curRow = $(this).closest('tr');
    let tr_id = "tr_without_" + add_more_count3;
    let optionElements = $("#account_1").html();

    let newRow = `
    <tr id="${tr_id}">
        <td class="srn" id="srn_${add_more_count3}">${add_more_count3}</td>
        <td>
            <select class="form-control account select2-single" name="account_name[]" data-id="${add_more_count3}" id="account_${add_more_count3}">
                ${optionElements}
            </select>
        </td>
        <td>
            <input type="number" name="debit[]" class="form-control debit" id="debit_${add_more_count3}" data-id="${add_more_count3}" placeholder="Debit Amount" onkeyup="debitTotal();">
        </td>
        <td class="action-cell" style="display: flex;"></td>
    </tr>`;

    $curRow.after(newRow);
    $(".select2-single").select2();

    refreshActionIcons();
});

  $(document).on("click", ".remove_without", function () {
    let id = $(this).data("id");
    $("#tr_without_" + id).remove();

    refreshActionIcons();
    debitTotal();
});
function refreshActionIcons() {
    let totalRows = $(".account").length;

    // Reindex SRNs
    $(".account").each(function (index) {
        let rowId = $(this).attr("data-id");
        $("#srn_" + rowId).html(index + 1 + ".");
    });

    // Clear all action columns
    $(".action-cell").html("");

    // Apply action icons based on row positions
    $(".account").each(function (index) {
        let rowId = $(this).attr("data-id");
        let $actionCell = $("#tr_without_" + rowId + " .action-cell");

        let removeIcon = `
            <svg style="color: red; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                class="bi bi-file-minus-fill remove_without" data-id="${rowId}" viewBox="0 0 16 16">
                <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
            </svg>`;

        let addIcon = `
            <svg style="color: green; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="currentColor"
                class="bg-primary rounded-circle add_more_without" data-id="${rowId}">
                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
            </svg>`;

        if (totalRows === 1) {
            // Only 1 row → show Add only
            $actionCell.html(addIcon);
        } else if (index === totalRows - 1) {
            // Last row → Remove + Add
            $actionCell.html(removeIcon + addIcon);
        } else {
            // All other rows (including first) → Remove only
            $actionCell.html(removeIcon);
        }
    });
}


   function debitTotal() {
      let total_debit_amount = 0;
      $(".debit").each(function() {
         if($(this).val() != '') {
            total_debit_amount = parseFloat(total_debit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_debit").html(total_debit_amount.toFixed(2));
   }
   function creditTotal() {
      let total_credit_amount = 0;
      $(".credit").each(function() {
         if ($(this).val() != '') {
            total_credit_amount = parseFloat(total_credit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_credit").html(total_credit_amount.toFixed(2));
   }
   $("#mode").change(function(){
      $("#cheque_no").val('');
      $("#cheque_no").prop('readonly',true);
      if($(this).val()==2){
         $("#cheque_no").prop('readonly',false);
      }
   });
   $(document).on("change",".debit",function(){
      debitTotal();
      creditTotal();
   });
   $(document).on("change",".credit",function(){
      debitTotal();
      creditTotal();
   });
</script>
@endsection