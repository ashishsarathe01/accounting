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
@php
   $to_pay_freight = "";
   $to_pay_other_charges = "";
   $vehicle_info_type = request('vehicle_info_type');
@endphp
@if(request('vehicle_info_type')=="to_pay")
   @php
      $to_pay_freight = request('to_pay_freight');
      $to_pay_other_charges = request('to_pay_other_charges');
   @endphp
@elseif (request('vehicle_info_type')=="vehicle")

@elseif (request('vehicle_info_type')=="transporter")

@endif
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
                  <input type="hidden" name="vehicle_info_type" value="{{request('vehicle_info_type')}}">
                  <input type="hidden" name="vehicle_info" value="{{request('vehicle_info')}}">
                  <input type="hidden" name="vehicle_freight" value="{{request('vehicle_freight')}}">
                  <input type="hidden" name="transporter_freight" value="{{request('transporter_freight')}}">
                  <input type="hidden" name="transporter_other_charges" value="{{request('transporter_other_charges')}}">
                  <input type="hidden" name="to_pay_other_charges" value="{{request('to_pay_other_charges')}}">
                  <input type="hidden" name="to_pay_freight" value="{{request('to_pay_freight')}}">
                  
                  <input type="hidden" name="sale_order_id" value="{{$sale_order_id}}">
                  <input type="hidden" name="new_order" value="{{$new_order}}">
                  <input type="hidden" name="sale_enter_data" value="@if($sale_enter_data){{$sale_enter_data}}@endif">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" name="series_no" class="form-select" required autofocus>
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
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" value="{{$bill_date}}" required min="{{ $fy_start_date }}" max="{{ $fy_end_date }}">
                     <ul style="color: red;">
                       @error('date'){{$message}}@enderror                        
                     </ul>
                  </div>                
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                        <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" value=""  style="text-align: right;" placeholder="Voucher No">
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
                  @if($config && $config->purchase_order_status == 1)
                    <div class="mb-3 col-md-3">
                        <label class="form-label font-14 font-heading">PURCHASE ORDER NO</label>
                        <input type="text" name="po_no" class="form-control" placeholder="Enter PO Number">
                    </div>
                    
                    <div class="mb-3 col-md-3">
                        <label class="form-label font-14 font-heading">PURCHASE ORDER DATE</label>
                        <input type="date" name="po_date" class="form-control">
                    </div>
                    @endif
                  <div class="mb-4 col-md-5">
                     <label for="name" class="form-label font-14 font-heading">Party</label><br>
                     <select class="form-select select2-single" name="party_id" id="party_id" data-modal="accountModal">
                        <option value="">Select Account</option>
                        @foreach($party_list as $party)
                           <option value="{{$party->id}}" @if($bill_to_id==$party->id) selected @endif data-state_code="{{$party->state_code}}" data-gstin="{{$party->gstin}}" data-id="{{$party->id}}" data-address="{{$party->address}}, {{$party->pin_code}}" data-other_address="{{$party->otherAddress}}" data-under_group="{{$party->under_group}}">{{$party->account_name}}</option>
                        @endforeach
                     </select>          
                     <p id="partyaddress" style="font-size: 9px;"></p>
                     <ul style="color: red;">
                       @error('party'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-4 col-md-7 address_div" style="display: none;">
                     <label for="name" class="form-label font-14 font-heading">Address</label><br>
                     <select class="form-select" name="address" id="address">
                     </select>
                     <ul style="color: red;">
                       @error('address'){{$message}}@enderror                        
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
                           <th class="w-min-50 border-none bg-light-pink text-body" style="width: 36%;">
                              @if($config && $config->show_description == 1)
                                 Description of Goods + Description
                              @else
                                 Description of Goods
                              @endif
                           </th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Qty</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: center;">Unit</th>
                           <th style="text-align:right;padding-right: 24px;">Price (With GST)</th>
                           <th style="text-align:right;">Profit</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Amount</th>
                           <th class="w-min-50 border-none bg-light-pink text-body "></th>
                        </tr>
                     </thead>
                     <tbody>
                       
                        @php $add_more_count = 1; @endphp
                        @if(count($sale_order_items)>0)
                           @foreach ($sale_order_items as $sale_order_item)
                              <tr id="tr_{{$add_more_count}}" class="font-14 font-heading bg-white">
                                 <td class="w-min-50" id="srn_{{$add_more_count}}">{{$add_more_count}}</td>
                                 <td class="w-min-50">
                                    <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_{{$add_more_count}}" data-id="{{$add_more_count}}" data-modal="itemModal">
                                       <option value="">Select Item</option>
                                       @foreach($item as $item_list)
                                          <option value="{{$item_list->id}}" @if($item_list->id==$sale_order_item['item_id']) selected @endif data-unit_id="{{$item_list->u_name}}" data-percent="{{$item_list->gst_rate}}" data-val="{{$item_list->unit}}" data-id="{{$item_list->id}}" data-itemid="{{$item_list->id}}" data-available_item="{{$item_list->available_item}}" data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" data-config_status="{{$item_list->config_status}}" data-group_id="{{$item_list->group_id}}">{{$item_list->name}}</option>
                                       @endforeach
                                    </select>
                                    @if($config && $config->show_description == 1) 
                                       
                                    <div class="description-wrapper mt-1" data-row="{{ $add_more_count-1 }}">
                                    <div class="d-flex mb-1">
                                       <input type="text" 
                                                name="description_lines[{{ $add_more_count-1 }}][]"
                                                class="form-control description-input" 
                                                placeholder="Enter description line">
                                    </div>
                                 </div>
                                    @endif
                                 </td>                           
                                 <td class="w-min-50">
                                    <input type="number" class="quantity w-100 form-control" id="quantity_tr_{{$add_more_count}}" name="qty[]" placeholder="Quantity" style="text-align:right;" data-id="{{$add_more_count}}" value="{{$sale_order_item['total_weight']}}" />
                                 </td>
                                 <td class="w-min-50">                              
                                    <input type="text" class="w-100 form-control unit" id="unit_tr_{{$add_more_count}}" readonly style="text-align:center;" data-id="{{$add_more_count}}"/>
                                    <input type="hidden" class="units w-100" name="units[]" id="units_tr_{{$add_more_count}}" />
                                 </td>
                                 <td class="w-min-50">
                                                    <input type="number" 
                                                        class="price_with_gst form-control" 
                                                        id="price_with_gst_tr_{{$add_more_count}}" 
                                                        placeholder="Price With GST"
                                                        style="text-align:right;" 
                                                        data-id="{{$add_more_count}}" />
                                                    </td>
                                 <td class="w-min-50">
                                    <input type="number" class="price form-control" id="price_tr_{{$add_more_count}}" name="price[]" placeholder="Price" style="text-align:right;" data-id="{{$add_more_count}}" value="{{$sale_order_item['price']}}" data-price="{{$sale_order_item['price']}}"/>
                                 </td>
                                 <td class=""><input type="number" id="amount_tr_{{$add_more_count}}" class="amount w-100 form-control" name="amount[]" placeholder="Amount"  style="text-align:right;" data-id="{{$add_more_count}}"/></td>
                                 <td class="" style="display:flex">
                                    <svg xmlns="http://www.w3.org/2000/svg" data-id="{{$add_more_count}}"class="bg-primary rounded-circle add_more_wrapper" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                                 </td>
                                 <input type="hidden" name="item_parameters[]" id="item_parameters_{{$add_more_count}}">
                                 <input type="hidden" name="config_status[]" id="config_status_{{$add_more_count}}">
                              </tr>
                              @php $add_more_count++; @endphp
                           @endforeach
                           
                        @else
                           <tr id="tr_1" class="font-14 font-heading bg-white">
                              <td class="w-min-50" id="srn_1">1</td>
                              <td class="w-min-50">
                              <div class="d-flex align-items-center gap-2">
                                    <!-- Item select -->
                                    <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_1" data-id="1" style="flex:1" data-modal="itemModal">
                                       <option value="">Select Item</option>
                                       @foreach($item as $item_list)
                                          <option value="{{$item_list->id}}" 
                                                   data-unit_id="{{$item_list->u_name}}" 
                                                   data-percent="{{$item_list->gst_rate}}" 
                                                   data-val="{{$item_list->unit}}" 
                                                   data-id="{{$item_list->id}}" 
                                                   data-itemid="{{$item_list->id}}" 
                                                   data-available_item="{{$item_list->available_item}}" 
                                                   data-parameterized_stock_status="{{$item_list->parameterized_stock_status}}" 
                                                   data-config_status="{{$item_list->config_status}}" 
                                                   data-group_id="{{$item_list->group_id}}">
                                                {{$item_list->name}}
                                          </option>
                                       @endforeach
                                    </select>
                                   
                                    <!-- ⚙️ Button -->
                                    <button type="button" 
                                          class="btn btn-outline-secondary p-1 px-2 editItemDetailsBtn" 
                                          data-row="tr_1" 
                                          title="Configure item">
                                       ⚙️
                                    </button>
                              </div>
                              @if($config && $config->show_description == 1)
                                <div class="description-wrapper mt-1" data-row="{{ $add_more_count-1 }}">
                                    <div class="d-flex mb-1">
                                       <input type="text" 
                                                name="description_lines[{{ $add_more_count-1 }}][]"
                                                class="form-control description-input" 
                                                placeholder="Enter description line">
                                    </div>
                                 </div>
                               @endif
                                 <input type="hidden" id="item_size_info_1" value="" name="item_size_info[]" data-id="1">
                              </td>
                              <td class="w-min-50">
                                 <input type="number" class="quantity w-100 form-control" id="quantity_tr_1" name="qty[]" placeholder="Quantity" style="text-align:right;" data-id="1"/>
                              </td>
                              <td class="w-min-50">                              
                                 <input type="text" class="w-100 form-control unit" id="unit_tr_1" readonly style="text-align:center;" data-id="1"/>
                                 <input type="hidden" class="units w-100" name="units[]" id="units_tr_1" />
                              </td>
                               <td class="w-min-50">
                                                    <input type="number" 
                                                        class="price_with_gst form-control" 
                                                        id="price_with_gst_tr_1" 
                                                         name="pricewithgst[]"
                                                        placeholder="Price With GST"
                                                        style="text-align:right;" 
                                                        data-id="1" />
                                                    </td>
                                                    <input type="hidden" id="cost_tr_1" />

                                                    <td>
                                                         <input type="number" 
                                                             name="profit[]"
                                                            class="profit form-control" 
                                                            id="profit_tr_1" 
                                                            readonly 
                                                            style="text-align:right;" />
                                                      </td>
                              <td class="w-min-50">
                                 <input type="number" class="price form-control" id="price_tr_1" name="price[]" placeholder="Price" style="text-align:right;" data-id="1"/>
                              </td>
                              <td class=""><input type="number" id="amount_tr_1" class="amount w-100 form-control" name="amount[]" placeholder="Amount"  style="text-align:right;" data-id="1"/></td>
                              <td class="" style="display:flex">
                                 <svg xmlns="http://www.w3.org/2000/svg" data-id="1"class="bg-primary rounded-circle add_more_wrapper" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;" tabindex="0" role="button"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                              </td>
                              <input type="hidden" name="item_parameters[]" id="item_parameters_1">
                              <input type="hidden" name="config_status[]" id="config_status_1">
                           </tr>
                        @endif
                        
                        
                     </tbody>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
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
                                    <select id="bill_sundry_1" class="w-95-parsent bill_sundry_tax_type form-select select2-single" name="bill_sundry[]" data-id="1" >
                                       <option value="">Select</option>
                                       <?php
                                       foreach($billsundry as $value) {
                                          if($value->nature_of_sundry=='OTHER'){?>
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
                                 <td><svg xmlns="http://www.w3.org/2000/svg" tabindex="0" data-id="1" class="bg-primary rounded-circle add_more_bill_sundry_up" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor:pointer">
                                 <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></td>
                              </tr>
                              
                              <tr id="billtr_cgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_cgst" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="cgst">
                                       <?php
                                    $cgst_found = false;

                                    foreach ($billsundry as $value) { 
                                       if ($value->nature_of_sundry == 'CGST') {
                                          $cgst_found = true;
                                          ?>
                                          <option value="<?php echo $value->id;?>" 
                                                data-type="<?php echo $value->bill_sundry_type;?>"
                                                data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>"
                                                data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>"
                                                data-sequence="<?php echo $value->sequence;?>"
                                                class="sundry_option_cgst" 
                                                id="sundry_option_cgst"
                                                data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>">
                                             <?php echo $value->name; ?>
                                          </option>
                                          <?php
                                          break;
                                       }
                                    }
                                    if (!$cgst_found) {
                                       ?>
                                       <option value="" 
                                             data-type="" 
                                             data-adjust_sale_amt="" 
                                             data-effect_gst_calculation="" 
                                             data-sequence="" 
                                             class="sundry_option_cgst" 
                                             id="sundry_option_cgst" 
                                             data-nature_of_sundry="">
                                       </option>
                                    <?php } ?>
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
                                       $sgst_found = false; // Flag to track SGST

                                       foreach($billsundry as $value){ 
                                          if($value->nature_of_sundry == 'SGST'){
                                             $sgst_found = true;
                                             ?>
                                             <option value="<?php echo $value->id;?>" 
                                                   data-type="<?php echo $value->bill_sundry_type;?>"
                                                   data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>"
                                                   data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>"
                                                   data-sequence="<?php echo $value->sequence;?>"
                                                   class="sundry_option_sgst" 
                                                   id="sundry_option_sgst"
                                                   data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>">
                                                <?php echo $value->name; ?>
                                             </option>
                                             <?php 
                                             break; // SGST mila to loop yahin break
                                          }
                                       }
                                       // SGST nahi mila to default empty option
                                       if (!$sgst_found) { ?>
                                          <option value="" 
                                                data-type="" 
                                                data-adjust_sale_amt="" 
                                                data-effect_gst_calculation="" 
                                                data-sequence="" 
                                                class="sundry_option_sgst" 
                                                id="sundry_option_sgst" 
                                                data-nature_of_sundry="">
                                          </option>
                                       <?php } ?>
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
                                    $igst_found = false; // Track whether IGST found or not

                                    foreach ($billsundry as $value) { 
                                       if ($value->nature_of_sundry == 'IGST') {
                                          $igst_found = true;
                                          ?>
                                          <option value="<?php echo $value->id;?>" 
                                                data-type="<?php echo $value->bill_sundry_type;?>"
                                                data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>"
                                                data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>"
                                                data-sequence="<?php echo $value->sequence;?>"
                                                class="sundry_option_igst" 
                                                id="sundry_option_igst"
                                                data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>">
                                             <?php echo $value->name; ?>
                                          </option>
                                          <?php
                                          break; // Stop loop after first IGST found
                                       }
                                    }

                                    // If no IGST was found, print default option (like your else)
                                    if (!$igst_found) {
                                       ?>
                                       <option value="" 
                                             data-type="" 
                                             data-adjust_sale_amt="" 
                                             data-effect_gst_calculation="" 
                                             data-sequence="" 
                                             class="sundry_option_igst" 
                                             id="sundry_option_igst" 
                                             data-nature_of_sundry="">
                                       </option>
                                    <?php } ?>
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


</body>
@include('layouts.footer')
<script>
   let activeItemRowId = null;
   var bill_sundry_array = @json($billsundry);//New Changes By Ashish
   var mat_series = "<?php echo count($GstSettings);?>";
   var bill_to_id = "{{$bill_to_id}}";
   var shipp_to_id = "{{$shipp_to_id}}";
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
   var customer_gstin = "";
   var merchant_gstin = "";
   var percent_arr = [];
   var add_more_count = {{$add_more_count}};
   var page_load = 0;
   var add_more_bill_sundry_up_count = 2;
   var production_module_status = "<?php echo $production_module_status; ?>";
   var bill_to_address_id = "{{$bill_to_address_id}}";
   var shipp_to_address_id = "{{$shipp_to_address_id}}";
   var shipp_to_other_address = "{{$shipp_to_other_address}}";
   var shipp_to_other_pincode = "{{$shipp_to_other_pincode}}";
   var vehicle_info_type = "{{$vehicle_info_type}}";
   var to_pay_freight = "{{$to_pay_freight}}";
   var to_pay_other_charges = "{{$to_pay_other_charges}}";
   var cash_group_ids = @json($cash_group_ids);
   function addMoreItem(){
      let empty_status = 0;
      $('.item_id').each(function () {
         let i = $(this).attr('data-id');
         if ($(this).val() == "" || $("#quantity_tr_" + i).val() == "" || $("#price_tr_" + i).val() == "") {
            empty_status = 1;
         }
      });
      if (empty_status == 1) {
         alert("Please enter required fields");
         return;
      }
      let srn = $("#srn_" + add_more_count).html();
      srn++;
      add_more_count++;
      let optionElements = $('#goods_discription_tr_1').html();
      let tr_id = 'tr_' + add_more_count;
      let newRow = '<tr id="' + tr_id + '" class="font-14 font-heading bg-white">' +
         '<td class="w-min-50" id="srn_' + add_more_count + '">' + srn + '</td>' +
         '<td class="w-min-50">' +
            '<div class="d-flex align-items-center gap-2">' +

               '<select class="form-control item_id select2-single" ' +
                  'name="goods_discription[]" ' +
                  'id="item_id_' + add_more_count + '" ' +
                  'data-id="' + add_more_count + '" style="flex:1" data-modal="itemModal">' +
                     '<option value="">Select Item</option>' +
                     '@foreach($item as $item_list)' +
                        '<option value="{{ $item_list->id }}" ' +
                           'data-unit_id="{{ $item_list->u_name }}" ' +
                           'data-percent="{{ $item_list->gst_rate }}" ' +
                           'data-val="{{ $item_list->unit }}" ' +
                           'data-available_item="{{ $item_list->available_item }}" ' +
                           'data-parameterized_stock_status="{{ $item_list->parameterized_stock_status }}" ' +
                           'data-config_status="{{ $item_list->config_status }}" ' +
                           'data-group_id="{{ $item_list->group_id }}">' +
                              '{{ $item_list->name }}' +
                        '</option>' +
                     '@endforeach' +
               '</select>' +

               '<button type="button" class="btn btn-outline-secondary p-1 px-2 editItemDetailsBtn d-none" ' +
                  'data-row="tr_' + add_more_count + '" title="Configure item">⚙️</button>' +

            '</div>' +
            @if($config && $config->show_description == 1)
            '<div class="description-wrapper mt-1" data-row="' + (add_more_count-1) + '">' +

               '<div class="d-flex mb-1">' +
                  '<input type="text" ' +
                        'name="description_lines[' + (add_more_count-1) + '][]" ' +
                        'class="form-control description-input" ' +
                        'placeholder="Enter description line">' +

                  '<button type="button" class="btn btn-success add-desc ms-1">+</button>' +
                  '<button type="button" class="btn btn-danger remove-desc ms-1">-</button>' +
               '</div>' +

            '</div>' +
            @endif
            '<input type="hidden" id="item_size_info_' + add_more_count + '" ' +
                  'name="item_size_info[]" value="" data-id="' + add_more_count + '">' +
         '</td>' +

         '<td class="w-min-50"><input type="number" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '" required placeholder="Quantity" style="text-align:right" data-id="' + add_more_count + '" /></td>' +
         '<td class="w-min-50"><input type="text" class="w-100 form-control unit" id="unit_tr_' + add_more_count + '" readonly style="text-align:center;" data-id="' + add_more_count + '"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td>' +
         '<td class="w-min-50">' +
'<input type="number" class="price_with_gst w-100 form-control" ' +
'name="pricewithgst[]" id="price_with_gst_tr_' + add_more_count + '" ' +
'placeholder="Price With GST" style="text-align:right" data-id="' + add_more_count + '" />' +
'</td>' +
'<input type="hidden" id="cost_tr_' + add_more_count + '" />' +

'<td>' +
    '<input type="number" name="profit[]"  class="profit form-control" ' +
    'id="profit_tr_' + add_more_count + '" readonly style="text-align:right;" />' +
'</td>' +

         '<td class="w-min-50"><input type="number" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '" required placeholder="Price" style="text-align:right" data-id="' + add_more_count + '"/></td>' +
         '<td class="w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]" id="amount_tr_' + add_more_count + '" required placeholder="Amount" style="text-align:right" data-id="' + add_more_count + '"/></td>' +
         '<input type="hidden" name="item_parameters[]" id="item_parameters_' + add_more_count + '">' +
         '<input type="hidden" name="config_status[]" id="config_status_' + add_more_count + '">' +
         '<td class="w-min-50 action-cell" style="display: flex;"></td>' +
         '</tr>';
      $("#example11").append(newRow);
      $("#max_sale_descrption").val(add_more_count);
      // Re-index serial numbers
      let k = 1;
      $('.item_id').each(function () {
         let i = $(this).attr('data-id');
         $("#srn_" + i).html(k);
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
      $(".select2-single").select2();
   }


function removeItem() {

  $(document).on("click", ".remove", function () {

    let id = $(this).attr("data-id");
    let itemId = $("#item_id_" + id).val();
    let savedSizes = $("#item_size_info_" + id).val();

    //  RELEASE SIZES FOR THIS ITEM

    if (savedSizes && itemId) {
      try {
        let sizeObjs = JSON.parse(savedSizes); // [{id,weight,reel}]
        let sizeIds = sizeObjs.map(s => s.id); // extract size IDs

        if (selectedSizesByItem[itemId]) {
          // Remove only these size IDs from saved list
          selectedSizesByItem[itemId] =
            selectedSizesByItem[itemId].filter(x => !sizeIds.includes(x));

          // If empty → delete the entry
          if (selectedSizesByItem[itemId].length === 0) {
            delete selectedSizesByItem[itemId];
          }
        }
      } catch (e) {
        console.log("Error releasing sizes:", e);
      }
    }
     
    $("#tr_" + id).remove();

    //  RE-INDEX SRN NUMBERS
    let k = 1;
    $(".item_id").each(function () {
      let i = $(this).attr("data-id");
      $("#srn_" + i).html(k);
      k++;
    });

    let max_val = $("#max_sale_descrption").val();
    $("#max_sale_descrption").val(--max_val);
    let totalRows = $(".item_id").length;

    $(".item_id").each(function (index) {
      let rowId = $(this).attr("data-id");
      let $iconCell = $("#tr_" + rowId + " td:last");

      let removeIcon = `
        <svg style="color: red; cursor: pointer; margin-right: 8px;" 
             xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" 
             fill="currentColor" class="bi bi-file-minus-fill remove" 
             data-id="${rowId}" viewBox="0 0 16 16">
          <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
        </svg>`;

      let addIcon = `
        <svg style="color: green;cursor: pointer;" 
             xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" 
             viewBox="0 0 24 24" fill="currentColor" 
             class="bg-primary rounded-circle add_more_wrapper" data-id="${rowId}">
          <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
        </svg>`;

      $iconCell.html("");

      if (totalRows === 1) {
        $iconCell.html(addIcon);
      } 
      else if (index === 0) {
        $iconCell.html("");
      } 
      else if (index === totalRows - 1) {
        $iconCell.html(removeIcon + addIcon);
      } 
      else {
        $iconCell.html(removeIcon);
      }
    });

    calculateAmount();
  });
}

//   $(document).on('select2:select', '.item_id', function (e) {
//     let rowId = $(this).data('id');
//     let selected = $(this).find(':selected');
//     let unit = selected.data('unit_id');

//     $('#unit_tr_' + rowId).val(unit);
//     $('#units_tr_' + rowId).val(unit);
// });

function updateUnit(rowId, selectedOption) {

    let unitName = selectedOption.data('val');       // for display
    let unitId   = selectedOption.data('unit_id');   // for DB

    // show name to user
    $('#unit_tr_' + rowId).val(unitName);

    // store ID in hidden input
    $('#units_tr_' + rowId).val(unitId);

    // extra attributes (unchanged)
    $('#unit_tr_' + rowId).attr('data-parameterized_stock_status', selectedOption.data('parameterized_stock_status'));
    $('#unit_tr_' + rowId).attr('data-group_id', selectedOption.data('group_id'));
    $('#unit_tr_' + rowId).attr('data-config_status', selectedOption.data('config_status'));
}


let isProgrammaticChange = false;
$(document).on('change', '.item_id', function () {
    if (isProgrammaticChange) return;

    var party_id = $('#party_id').val();
    if (party_id.length == 0) {         
        alert("Select Party Name First.");
        isProgrammaticChange = true;
        $(this).val(null).trigger('change');
        isProgrammaticChange = false;
        return;
    }

    let rowId = $(this).data("id");
    let selected = $(this).find(':selected');

    // ✅ Call function
    updateUnit(rowId, selected);

    call_fun('tr_' + rowId);

    if (selected.data('parameterized_stock_status') == 1) {
        $('#unit_tr_' + rowId).css({ cursor: 'pointer' });
    }
});

   $(document).ready(function(){
     
      // Function to calculate amount and update total sum
      
      window.calculateAmount = function(key=null) {
         customer_gstin = $('#party_id option:selected').attr('data-state_code');
         let under_group = $('#party_id option:selected').attr('data-under_group'); 
         
         if(cash_group_ids.includes(Number(under_group))){
            customer_gstin = merchant_gstin.substring(0,2);
         } 
         if(customer_gstin==undefined || customer_gstin==""){
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
               if(price==0 && quantity==0){
                     amount = $(this).find('.amount').val();
                  }
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
      if(bill_to_id!=""){
         $("#party_id").change();
         $(".item_id").each(function(){
            $(this).change();
         })
         if(bill_to_id!=shipp_to_id){
            $("#shipping_name").val(shipp_to_id);
            $("#shipping_name").change();
            if(shipp_to_other_address!=""){
               $("#shipping_address").val(shipp_to_other_address);
            }
            if(shipp_to_other_pincode!=""){
               $("#shipping_pincode").val(shipp_to_other_pincode);
            }
         }else if(shipp_to_address_id!=bill_to_address_id){
            $("#shipping_name").val(shipp_to_id);
            $("#shipping_name").change();
            if(shipp_to_other_address!=""){
               $("#shipping_address").val(shipp_to_other_address);
            }
            if(shipp_to_other_pincode!=""){
               $("#shipping_pincode").val(shipp_to_other_pincode);
            }
         }
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
         calculateToPayAmount();
      });
      $(document).on('input', '.amount',function(){
         let id = $(this).attr('data-id');
         let qty = $("#quantity_tr_"+id).val();
         let price = $("#price_tr_"+id).val();
         if(qty!=0 || qty!=0 || price!=0 || price!=0){
            alert("Not Allowed")
            $(this).val('');
            return;
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

  // Check for empty values
  $(".bill_sundry_tax_type").each(function () {
    let dataId = $(this).attr("data-id");
    if (
      dataId !== "cgst" &&
      dataId !== "sgst" &&
      dataId !== "igst" &&
      dataId !== "round_plus" &&
      dataId !== "round_minus"
    ) {
      if (
        $(this).val() === "" ||
        $("#bill_sundry_amount_" + dataId).val() === ""
      ) {
        empty_status = 1;
      }
    }
  });

  if (empty_status === 1) {
    alert("Please enter sundry required fields");
    return;
  }

  add_more_bill_sundry_up_count++;

  // Build dropdown options from PHP
  let optionElements = "<option value=''>Select</option>";
  <?php
  foreach ($billsundry as $value) {
    if (
      $value->nature_of_sundry != "CGST" &&
      $value->nature_of_sundry != "SGST" &&
      $value->nature_of_sundry != "IGST" &&
      $value->nature_of_sundry != "ROUNDED OFF (+)" &&
      $value->nature_of_sundry != "ROUNDED OFF (-)"
    ) {
      ?>
      optionElements += `<option value="<?php echo $value->id; ?>"
        data-type="<?php echo $value->bill_sundry_type; ?>"
        data-sundry_percent="<?php echo $value->sundry_percent; ?>"
        data-sundry_percent_date="<?php echo $value->sundry_percent_date; ?>"
        data-adjust_sale_amt="<?php echo $value->adjust_sale_amt; ?>"
        data-effect_gst_calculation="<?php echo $value->effect_gst_calculation; ?>"
        data-nature_of_sundry="<?php echo $value->nature_of_sundry; ?>"
        class="sundry_option_${add_more_bill_sundry_up_count}"
        id="sundry_option_<?php echo $value->id; ?>_${add_more_bill_sundry_up_count}"
        data-sequence="<?php echo $value->sequence; ?>"
      ><?php echo $value->name; ?></option>`;
      <?php
    }
  }
  ?>

  // New row HTML
  let newRow = `
    <tr id="billtr_${add_more_bill_sundry_up_count}" class="font-14 font-heading bg-white extra_taxes_row sundry_tr">
      <td class="w-min-50">
        <select class="w-95-parsent bill_sundry_tax_type w-100 form-select select2-single" id="bill_sundry_${add_more_bill_sundry_up_count}" name="bill_sundry[]" data-id="${add_more_bill_sundry_up_count}">
          ${optionElements}
        </select>
      </td>
      <td class="w-min-50">
        <span name="tax_amt[]" id="tax_amt_${add_more_bill_sundry_up_count}"></span>
        <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_${add_more_bill_sundry_up_count}">
      </td>
      <td class="w-min-50">
        <input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_${add_more_bill_sundry_up_count}" name="bill_sundry_amount[]" data-id="${add_more_bill_sundry_up_count}" readonly style="text-align:right;">
      </td>
      <td class="w-min-50"></td>
    </tr>
  `;

  // Insert new row before CGST row
  $("#billtr_cgst").before(newRow);

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
}


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
      newRow += '</select></td><td class="w-min-50"><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50"><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16"tabindex="0" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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

      let otherAddressAttr = $('option:selected', this).attr('data-other_address');

      let other_address = [];

      if (otherAddressAttr && otherAddressAttr !== "undefined") {
         try {
            other_address = JSON.parse(otherAddressAttr);
         } catch (e) {
            console.warn("Invalid other_address JSON", otherAddressAttr);
            other_address = [];
         }
      }
      

      $(".address_div").hide();
      $("#address").html('');
      $("#address").attr('required',false);
      if(other_address!=null && other_address.length>0){
         let address_html = "<option value=''>Select Address</option>";
         address_html+= "<option value=''>"+$('option:selected', this).attr('data-address')+"</option>";
         other_address.forEach(function(e){
            address_html += "<option value='"+e.id+"' data-address='"+e.address+"' data-pincode='"+e.pincode+"' data-location='"+e.location+"'>"+e.address+" ("+e.pincode+")</option>";
         });
         $("#address").html(address_html);
         if(bill_to_address_id!=""){
            $("#address").val(bill_to_address_id);
            $("#address").change();
         }
         $(".address_div").show();
         $("#address").attr('required',false);
      }
      calculateAmount(); 
   }); 
   $("#address").change(function(){
      if($(this).val()!=""){
         let address = $('option:selected', this).attr('data-address');
         let pincode = $('option:selected', this).attr('data-pincode');
         let location = $('option:selected', this).attr('data-location');
         $("#partyaddress").html("GSTIN : "+$("#party_id  option:selected").attr('data-gstin')+"<br>Address : "+address+","+pincode);
      }else{
         $("#partyaddress").html("GSTIN : "+$("#party_id  option:selected").attr('data-gstin')+"<br>Address : "+$('option:selected', '#party_id').attr('data-address'));
      }
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
// $(document).on("keydown", ".bill_sundry_amount", function (event) {
//   if ((event.key === "Tab" && !event.shiftKey) || event.key === "Enter") {
//     event.preventDefault();

//     let id = $(this).data("id").toString();
//     let $rows = $(".bill_sundry_tax_type");
//     let lastId = $rows.last().data("id").toString();

//     if (id === "1" || id === lastId) {
//       // Focus Add icon if first or last row
//       $("#billtr_" + id).find(".add_more_bill_sundry_up").focus();
//     } else {
//       // Otherwise, focus Remove icon
//       $("#billtr_" + id).find(".remove_sundry_up").focus();
//     }
//   }
// });

$(document).ready(function() {
  
  //   // When clicking purchaseBtn
  //   $("#purchaseBtn").on('click', function(event) {
  //     event.preventDefault(); // Stop default submit behavior
  //     $(this).closest('form').submit(); // Manually submit the form
  //   });
  
    // When pressing Enter on purchaseBtn
    $("#saveBtn").on('keydown', function(event) {
      if (event.key === "Enter") {
        event.preventDefault(); // prevent default behavior
        $(this).click(); // trigger click event (which submits)
      }
    });
  
  });
let selectedSizesByItem = {}; 

$(document).on('change', '.item_size', function () {
    let selectedValue = $(this).val();

    // block duplicates in current modal
    let duplicate = false;
    $('.item_size').not(this).each(function () {
        if ($(this).val() == selectedValue && selectedValue !== '') {
            duplicate = true;
        }
    });

    if (duplicate) {
        alert("This size is already selected. Choose another one.");
        $(this).val('').trigger('change');
        return;
    }

    if ($(this).val() == "") {
        return;
    }

    let index = parseInt($(this).attr('data-index'));
    let nextIndex = index + 1;

    let weight = $(this).find(':selected').data('weight');
    let reel_no = $(this).find(':selected').data('reel_no');

    $("#item_weight_" + index).val(weight);
    $("#item_reel_no_" + index).val(reel_no);

    updateTotalWeight();

    // prevent cloning duplicate row if next exists
    if ($("#size_tr_" + nextIndex).length > 0) return;

    // Clone first row
    let cloneRow = $('#size_tr_1').clone();
    cloneRow.find('.select2-container').remove();

    let originalSelect = $('#size_tr_1').find('.select2-single').clone();
    cloneRow.find('.select2-single').replaceWith(originalSelect);

    cloneRow.attr('id', 'size_tr_' + nextIndex);

    cloneRow.find('.item_size')
        .attr('data-index', nextIndex)
        .val('');

    cloneRow.find('.item_weight')
        .attr('id', 'item_weight_' + nextIndex)
        .val('');

    cloneRow.find('.item_reel_no')
        .attr('id', 'item_reel_no_' + nextIndex)
        .val('');

    $('#size_tr_' + index).after(cloneRow);

    // Reinitialize select2
    cloneRow.find('.select2-single').select2({
        dropdownParent: $('#sizeModal'),
        width: '100%'
    });

    // Disable already used sizes for this item
    disableAlreadySelectedSizes();
});
















function checkVoucherDuplicate() {
        let voucherNo = $('#voucher_prefix').val().trim();
        if (voucherNo === '') return;

        $.ajax({
            url: '{{ route("check.voucher.no") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                voucher_no_prefix: voucherNo
            },
            success: function(response) {
                if (response.exists) {
                    alert('⚠️ This voucher number already exists! Please generate or assign another.');
                    $('#voucher_prefix').val('');
                    $('#voucher_no').val('');
                }
            },
            error: function() {
                alert('Error checking voucher number.');
            }
        });
    }

    // Automatically check when voucher number is set by script
    let observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === "attributes" && mutation.attributeName === "value") {
                checkVoucherDuplicate();
            }
        });
    });

    observer.observe(document.getElementById('voucher_prefix'), { attributes: true });

    // Also check if manually changed
    $('#voucher_prefix').on('change', checkVoucherDuplicate);
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

            // 🔥 REQUIRED DATA ATTRIBUTES (THIS FIXES undefined)
            option.setAttribute('data-state_code', res.account.state_code || '');
            option.setAttribute('data-gstin', res.account.gstin || '');
            option.setAttribute('data-id', res.account.id || '');
            option.setAttribute('data-address', res.account.address || '');
            option.setAttribute(
               'data-other_address',
               JSON.stringify(res.account.other_address || [])
            );
            partySelect.appendChild(option);
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

   

calculateToPayAmount();
function calculateToPayAmount(){
   if(vehicle_info_type!="to_pay"){
      return;
   }
   let other_charges = 0;
   if(to_pay_other_charges!=""){
      let total_qunatity = 0;
      $(".quantity").each(function(){
         let val = parseFloat($(this).val()) || 0;
         total_qunatity += val;
      });
      
      let other_charges = parseFloat(to_pay_other_charges) / parseFloat(total_qunatity);      
      other_charges = other_charges.toFixed(2);
      $(".price").each(function(){
         let val = parseFloat($(this).attr("data-price")) || 0;
         
         val = val - parseFloat(other_charges) - (parseFloat(to_pay_freight) || 0);
         
         $(this).val(val.toFixed(2));
      });
   }else{
      $(".price").each(function(){
         let val = parseFloat($(this).attr("data-price")) || 0;
         val = val - parseFloat(other_charges) - (parseFloat(to_pay_freight) || 0);
         $(this).val(val.toFixed(2));
      });
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
$(document).on('click', '.add-desc', function () {
    let wrapper = $(this).closest('.description-wrapper');
    let rowIndex = wrapper.data('row');

    let newLine = `
        <div class="d-flex mb-1">
            <input type="text" 
                   name="description_lines[${rowIndex}][]" 
                   class="form-control description-input"
                   placeholder="Enter description line">
        </div>
    `;

    wrapper.append(newLine);

    updateDescButtons(wrapper); 
});
$(document).on('click', '.remove-desc', function () {
    let wrapper = $(this).closest('.description-wrapper');

    $(this).closest('.d-flex').remove();

    updateDescButtons(wrapper); 
});
function updateDescButtons(wrapper) {
    let rows = wrapper.find('.d-flex');

    rows.each(function (index) {

        // remove old buttons
        $(this).find('.add-desc').remove();
        $(this).find('.remove-desc').remove();

        if (rows.length === 1) {
            $(this).append('<button type="button" class="btn btn-success add-desc ms-1">+</button>');
        } 
        else if (index === rows.length - 1) {
            $(this).append('<button type="button" class="btn btn-danger remove-desc ms-1">-</button>');
            $(this).append('<button type="button" class="btn btn-success add-desc ms-1">+</button>');
        } 
        else {
            $(this).append('<button type="button" class="btn btn-danger remove-desc ms-1">-</button>');
        }
    });
}
$(document).ready(function () {
    $('.description-wrapper').each(function () {
        updateDescButtons($(this));
    });
});

function checkPartyItemPrice(rowId) {

    let party_id = $('#party_id').val();
    let item_id  = $('#item_id_' + rowId).val();

    if (!party_id || !item_id) return;

    $.ajax({
        url: '/get-party-item-price',
        type: 'GET',
        data: {
            party_id: party_id,
            item_id: item_id
        },
        success: function (res) {

            let priceInput = $('#price_tr_' + rowId);

            if (res.status) {
                priceInput.val(res.price);
                priceInput.prop('readonly', true);   // 🔒 LOCK
                priceInput.addClass('bg-light');     // UI feel
            } else {
                priceInput.prop('readonly', false);  // 🔓 UNLOCK
                priceInput.removeClass('bg-light');
            }
        }
    });
}

$(document).on('change', '.item_id', function () {

    let rowId = $(this).data('id');
    checkPartyItemPrice(rowId);

});
$('#party_id').on('change', function () {

    $('.item_id').each(function () {
        let rowId = $(this).data('id');
        checkPartyItemPrice(rowId);
    });

});

$(document).ready(function () {

    setTimeout(function () {
        $('.item_id').each(function () {
            let rowId = $(this).data('id');
            checkPartyItemPrice(rowId);
        });
    }, 500);

});

function updatePriceFromGST(rowId) {

    let priceWithGst = parseFloat($("#price_with_gst_tr_" + rowId).val()) || 0;

    let gstPercent = parseFloat(
        $("#item_id_" + rowId + " option:selected").data('percent')
    ) || 0;

    if (priceWithGst > 0 && gstPercent >= 0) {

        let priceWithoutGst = priceWithGst / (1 + (gstPercent / 100));

        $("#price_tr_" + rowId).val(priceWithoutGst.toFixed(2));

        $("#price_tr_" + rowId).trigger('input');
    }
}

$(document).on('input', '.price_with_gst', function () {
    let rowId = $(this).data('id');
    updatePriceFromGST(rowId);
});

$(document).on('change', '.item_id', function () {
    let rowId = $(this).data('id');
    updatePriceFromGST(rowId);
});


function calculateProfit(rowId) {

    let gstPercent = parseFloat($("#item_id_" + rowId + " option:selected").data('percent')) || 0;
    let withGst = parseFloat($("#price_with_gst_tr_" + rowId).val()) || 0;
    let cost = parseFloat($("#cost_tr_" + rowId).val()) || 0;

    // If required values missing → CLEAR profit
    if (!withGst || gstPercent === 0) {
        $("#profit_tr_" + rowId).val('');
        return;
    }

    // Step 1: without GST
    let withoutGst = withGst / (1 + gstPercent / 100);

    // Step 2: profit before gst
    let profitBeforeGst = withoutGst - cost;

    // Step 3: GST on sale
    let gstSale = withGst - withoutGst;

    // Step 4: GST on cost
    let gstCost = cost * gstPercent / 100;

    // Step 5: net gst impact
    let gstDiff = gstSale - gstCost;

    // Step 6: final profit
    let finalProfit = profitBeforeGst - gstDiff;

    $("#price_tr_" + rowId).val(withoutGst.toFixed(2));
    $("#profit_tr_" + rowId).val(finalProfit.toFixed(2));
}

$(document).on('change', '.item_id', function () {

    let rowId = $(this).data('id');

    // 🔥 reset instantly (prevents flicker of old profit)
    $("#cost_tr_" + rowId).val('');
    $("#profit_tr_" + rowId).val('');
    let itemId = $(this).val();
    let date = $("#date").val();
    let series = $("#series_no").val();

    console.log("Row:", rowId, "Item:", itemId);

    if (!itemId || !date || !series) return;

    $.ajax({
        url: "{{ url('/get-item-cost') }}",
        method: 'GET',
        data: {
            item_id: itemId,
            date: date,
            series: series
        },
       success: function (res) {

    let cost = parseFloat(res.cost) || 0;

    $("#cost_tr_" + rowId).val(cost);

    if (!cost) {
        $("#profit_tr_" + rowId).val(''); // 🔥 clear old profit
    }

    calculateProfit(rowId);
},
        error: function (err) {
            console.log("AJAX ERROR:", err);
        }
    });
});

$(document).on('change', '#series_no', function () {
    $('.item_id').each(function () {
        let rowId = $(this).data('id');

        if ($(this).val()) {
            $(this).trigger('change');
        }
    });
});

$(document).on('change', '#date', function () {
    $('.item_id').each(function () {
        let rowId = $(this).data('id');

        if ($(this).val()) {
            $(this).trigger('change');
        }
    });
});

$(document).on('input', '.price_with_gst', function () {
    let rowId = $(this).data('id');

    // existing logic
    let priceWithGst = parseFloat($(this).val()) || 0;
    let gstPercent = parseFloat($("#item_id_" + rowId + " option:selected").data('percent')) || 0;

    let priceWithoutGst = priceWithGst / (1 + gstPercent / 100);
    $("#price_tr_" + rowId).val(priceWithoutGst.toFixed(2)).trigger('input');

    // 🔥 profit calc
    calculateProfit(rowId);
});

$(document).on('keyup change', '.price_with_gst', function () {
    let rowId = $(this).data('id');
    calculateProfit(rowId);
});

$(document).on('change', '.item_id', function () {

    let rowId = $(this).data('id');

    // your ajax...

    setTimeout(() => {
        calculateProfit(rowId);
    }, 200);
});


$(document).on('change', '.item_id', function(){

    let rowId = $(this).data('id');
    let itemId = $(this).val();
    let date = $('#date').val();

    if(!itemId || !date){
        return;
    }

    $.ajax({
        url: "{{ route('get.item.rate.by.date') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            item_id: itemId,
            date: date
        },
        success: function(res){

            if(res.status){

                // 🔥 Set price with GST
                $("#price_with_gst_tr_" + rowId).val(res.price_with_gst);

                // 🔥 Trigger calculation
                updatePriceFromGST(rowId);

            } else {
                alert("No rate found for selected date");
                $("#price_with_gst_tr_" + rowId).val('');
            }
        }
    });

});
$('#date').on('change', function(){

    $('.item_id').each(function(){
        $(this).trigger('change');
    });

});
</script>
@endsection