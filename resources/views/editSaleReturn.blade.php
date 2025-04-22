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
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if(session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success')}}
               </div>
            @endif
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Edit Return Sale
            </h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale-return-update') }}" id="saleReturnForm">
               @csrf
               <div class="row">
                  <input type="hidden" name="sale_return_edit_id" value="{{$sale_return->id}}">
                  
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">NATURE</label>
                     <select id="nature" name="nature" class="form-select" required onChange="sectionHideShow();">
                        @if($sale_return->sr_nature=="WITH GST")
                           <option value="WITH GST" @if($sale_return->sr_nature=="WITH GST") selected @endif>WITH GST</option>
                        @endif
                        @if($sale_return->sr_nature=="WITHOUT GST")
                           <option value="WITHOUT GST" @if($sale_return->sr_nature=="WITHOUT GST") selected @endif>WITHOUT GST</option>
                        @endif
                     </select>
                     <ul style="color: red;">
                        @error('date'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4 type_div" style="display:none">
                     <label for="name" class="form-label font-14 font-heading">TYPE</label>
                     <select id="type" name="type" class="form-select" onChange="sectionHideShow()">
                        @if($sale_return->sr_type=="WITH ITEM")
                           <option value="WITH ITEM" @if($sale_return->sr_type=="WITH ITEM") selected @endif>WITH ITEM</option>
                        @endif
                        @if($sale_return->sr_type=="WITHOUT ITEM")
                           <option value="WITHOUT ITEM" @if($sale_return->sr_type=="WITHOUT ITEM") selected @endif>WITHOUT ITEM</option>
                        @endif
                        @if($sale_return->sr_type=="RATE DIFFERENCE")
                           <option value="RATE DIFFERENCE" @if($sale_return->sr_type=="RATE DIFFERENCE") selected @endif>RATE DIFFERENCE</option>
                        @endif
                     </select>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" value="{{$sale_return->date}}" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     <ul style="color: red;">
                        @error('date'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Accounts</label>
                     <select class="form-select select2-single" id="party" name="party" required>
                        <option value="">Select</option>
                        <?php
                        foreach ($party_list as $value) { ?>
                           <option value="<?php echo $value->id; ?>" data-state_code="<?php echo $value->state_code; ?>" data-gstin="<?php echo $value->gstin; ?>" data-address="<?php echo $value->address.",".$value->pin_code; ?>" <?php if($sale_return->party==$value->id){ echo "selected";} ?>><?php echo $value->account_name; ?></option>
                           <?php 
                        } ?>
                     </select>
                     <p id="partyaddress" style="font-size: 9px;"></p>
                     <ul style="color: red;">
                       @error('party'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-3 col-md-3 voucher_no_div">
                     <label for="name" class="form-label font-14 font-heading">Sale Invoice No. </label>
                     <select class="form-select select2-single" id="voucher_no" name="voucher_no" required>
                        <option value="">Select</option>
                     </select>
                     <ul style="color: red;">
                       @error('voucher_no'){{$message}}@enderror                        
                     </ul>
                     <input type="hidden" name="sale_bill_id" id="sale_bill_id" value="{{$sale_return->sale_bill_id}}">
                  </div>
                  <div class="mb-1 col-md-1 voucher_no_div">
                     <br>
                     <a href="{{ URL::to('sale-invoice/')}}/{{$sale_return->invoice_no}}" title="View Invoice" target="__blank" id="invoice_id"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" style="margin-top: 20px;"></a>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" name="series_no" class="form-select" required>
                     <option value="{{$sale_return->series_no}}">{{$sale_return->series_no}}</option> 
                     </select>
                     <ul style="color: red;">
                       @error('series_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Material Center</label>
                     <select name="material_center" class="form-select" required>
                        <option value="{{$sale_return->material_center}}">{{$sale_return->material_center}}</option>
                     </select>
                     <ul style="color: red;">
                       @error('material_center'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="sale_return_no" class="form-label font-14 font-heading">Credit Note No.</label>
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" value="{{$sale_return->sr_prefix}}"  readonly style="text-align: right;">
                     <input type="hidden" id="sale_return_no" class="form-control" name="sale_return_no" value="{{$sale_return->sale_return_no}}">
                  </div>
               </div>
               <!-- With Gst With Item Section Start -->
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
                        @php $i = 1; $item_arr = [];$total_amount = 0;@endphp
                        @foreach($sale_return_description as $item)
                           @php $item_arr[$i] = $item->goods_discription;@endphp
                           <tr id="tr_@php $i; @endphp" class="font-14 font-heading bg-white">
                              <td class="w-min-50">@php echo $i; @endphp</td>
                              <td class="w-min-50">
                                 <select onchange="call_fun('tr_@php echo $i; @endphp');" class="border-0 goods_items form-select" id="goods_discription_tr_@php echo $i; @endphp" name="goods_discription[]" required data-id="@php echo $i; @endphp">
                                    <option value="">Select</option>
                                 </select>
                              </td>                              
                              <td class=" w-min-50">
                                 <input type="number" class="quantity w-100 form-control" id="quantity_tr_@php echo $i; @endphp" name="qty[]" value="{{$item->qty}}" style="text-align:right;"/>
                              </td>
                              <td class=" w-min-50">
                                 <input type="text" class="w-100 form-control" id="unit_tr_@php echo $i; @endphp" readonly style="text-align:center;" value="{{$item->s_name}}" />       
                                 <input type="hidden" class="units w-100" name="units[]" id="units_tr_@php echo $i; @endphp" value="{{$item->unit}}" />
                              </td>
                              <td class=" w-min-50">
                                 <input type="number" class="price form-control" id="price_tr_@php echo $i; @endphp" name="price[]" value="{{$item->price}}" style="text-align:right;"/>
                              </td>
                              <td class=" w-min-50">
                                 <input type="number" id="amount_tr_@php echo $i; @endphp" class="amount w-100 form-control" name="amount[]" value="{{$item->amount}}" style="text-align:right;">
                              </td>
                              <td>
                                 <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                              </td>
                           </tr>
                           @php $i++; $total_amount = $total_amount + $item->amount; @endphp
                        @endforeach
                     </tbody>
                     
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold">Total</td>
                           <td class="w-min-50 fw-bold">
                              <span id="totalSum" style="float:right;">{{$total_amount}}</span>
                              <input type="hidden" name="taxable_amt" id="total_taxable_amounts" value="{{$total_amount}}">
                           </td>
                        </tr>
                     </div>
                  </table>
               </div>
               <div class="row with_gst_with_item_section" style="display:none">
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
                              @php $index = 1;@endphp                           
                              @foreach($sale_return_sundry as $sundry)
                                 @if($sundry->nature_of_sundry!='CGST' && $sundry->nature_of_sundry!='SGST' && $sundry->nature_of_sundry!='IGST' && $sundry->nature_of_sundry!='ROUNDED OFF (+)' && $sundry->nature_of_sundry!='ROUNDED OFF (-)')
                                    <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                       <td class="w-min-50">
                                          <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
                                             <option value="">Select</option>
                                             <?php
                                             foreach ($billsundry as $value) { 
                                                if($value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
                                                   <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_@php echo $index;@endphp" id="sundry_option_<?php echo $value->id;?>_1" <?php if($sundry->bill_sundry==$value->id){ echo "selected";} ?> data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                                   <?php 
                                                }
                                             } ?>
                                          </select>
                                       </td>
                                       <td class="w-min-50 ">
                                          <span name="tax_amt[]" class="tax_amount" id="tax_amt_@php echo $index;@endphp"></span>
                                          <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_@php echo $index;@endphp">
                                       </td>
                                       <td class="w-min-50 ">
                                          <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_@php echo $index;@endphp" data-id="@php echo $index;@endphp" value="{{$sundry->amount}}" style="text-align: right;">
                                       </td>
                                       <td>
                                          <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="@php echo $index;@endphp" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg>
                                       </td>
                                    </tr>
                                    @php $index++;@endphp
                                 @endif
                              @endforeach
                              <div class="plus-icon">
                                 <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120 " colspan="5">
                                       <a style="float: right;cursor:pointer" class="add_more_bill_sundry_up"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
                                    </td>
                                 </tr>
                              </div>
                              <?php 
                              $return = array();$roundReturn = array();
                              foreach($sale_return_sundry as $val) {
                                 if($val['nature_of_sundry']=='CGST' || $val['nature_of_sundry']=='SGST' || $val['nature_of_sundry']=='IGST'){
                                    $return[$val['nature_of_sundry']][] = $val;
                                 }else if($val['nature_of_sundry']=='ROUNDED OFF (+)' || $val['nature_of_sundry']=='ROUNDED OFF (-)'){
                                    $roundReturn[$val['nature_of_sundry']][] = $val;
                                 }
                              }
                              $saleSundryArr = [];
                              if(isset($return['CGST'][0]['id'])){
                                 array_push($saleSundryArr,$return['CGST'][0]['id']);
                              }
                              if(isset($return['SGST'][0]['id'])){
                                 array_push($saleSundryArr,$return['SGST'][0]['id']);
                              }
                              if(isset($return['IGST'][0]['id'])){
                                 array_push($saleSundryArr,$return['IGST'][0]['id']);
                              }
                              ?>
                              <tr id="billtr_cgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($return['CGST'])){?> style="display:none" <?php } ?> >
                                 <td class="w-min-50">
                                    <select id="bill_sundry_cgst" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="cgst">
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='CGST'){?>
                                          <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_cgst" id="sundry_option_cgst" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name;?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_cgst">@if(isset($return['CGST'])){{$return['CGST'][0]['rate']}} %@endif</span>
                                    <input type="hidden" name="tax_rate[]" value="@if(isset($return['CGST'])){{$return['CGST'][0]['rate']}}@endif" id="tax_rate_tr_cgst"></td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_cgst" data-id="cgst" <?php if(isset($return['CGST'])){?> value="<?php echo $return['CGST'][0]['amount'];?>" <?php } ?> style="text-align: right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_sgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($return['SGST'])){?> style="display:none" <?php } ?>>
                                 <td class="w-min-50">
                                    <select id="bill_sundry_sgst" class="w-95-parsent  bill_sundry_tax_type  form-select" name="bill_sundry[]" data-id="sgst">
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
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_sgst">@if(isset($return['SGST'])){{$return['SGST'][0]['rate']}} %@endif</span>
                                    <input type="hidden" name="tax_rate[]" value="@if(isset($return['SGST'])){{$return['SGST'][0]['rate']}}@endif" id="tax_rate_tr_sgst">
                                 </td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_sgst" data-id="sgst" <?php if(isset($return['SGST'])){?> value="<?php echo $return['SGST'][0]['amount'];?>" <?php } ?> style="text-align: right;">
                                 </td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_igst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($return['IGST'])){?> style="display:none" <?php } ?>>
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
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_igst">@if(isset($return['IGST'])){{$return['IGST'][0]['rate']}} %@endif</span>
                                    <input type="hidden" name="tax_rate[]" value="@if(isset($return['IGST'])){{$return['IGST'][0]['rate']}} @endif" id="tax_rate_tr_igst"></td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_igst" data-id="igst" <?php if(isset($return['IGST'])){?> value="<?php echo $return['IGST'][0]['amount'];?>" <?php } ?> style="text-align: right;"></td>
                                 <td></td>
                              </tr>
                              <?php 
                              foreach($return as $key=>$value) {
                                 foreach ($value as $k1 => $v1) {
                                    if(!in_array($v1['id'],$saleSundryArr)){ ?>
                                       <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr extra_gst">
                                          <td class="w-min-50">
                                             <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
                                                <?php
                                                foreach ($billsundry as $value) { 
                                                   if($value->id==$v1['bill_sundry']){?>
                                                      <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_@php echo $index;@endphp" id="sundry_option_@php echo $index;@endphp" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                                      <?php 
                                                   }
                                                } ?>
                                             </select>
                                          </td>
                                          <td class="w-min-50 ">
                                             <span name="tax_amt[]" class="tax_amount" id="tax_amt_@php echo $index;@endphp">{{$v1['rate']}} %</span>
                                             <input type="hidden" name="tax_rate[]" value="{{$v1['rate']}}" id="tax_rate_tr_@php echo $index;@endphp"></td>
                                          <td class="w-min-50 ">
                                             <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_@php echo $index;@endphp" data-id="@php echo $index;@endphp" value="{{$v1['amount']}}" style="text-align: right;">
                                          </td>
                                          <td></td>
                                       </tr>
                                       <?php
                                       $index++;
                                    }
                                 }
                              }?>
                              <div class="plus-icon" >
                                 <tr class="font-14 font-heading bg-white" style="display: none;">
                                    <td class="w-min-120 " colspan="5" >
                                       <a class="add_more_bill_sundry_gst"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
                                    </td>
                                 </tr>
                              </div>
                              @foreach($sale_return_sundry as $sundry)
                                 @if($sundry->effect_gst_calculation==0 && $sundry->nature_of_sundry!='CGST' && $sundry->nature_of_sundry!='SGST' && $sundry->nature_of_sundry!='IGST' && $sundry->nature_of_sundry!='ROUNDED OFF (+)' && $sundry->nature_of_sundry!='ROUNDED OFF (-)')
                                    <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                       <td class="w-min-50">
                                          <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
                                             <option value="">Select</option>
                                             <?php
                                             foreach ($billsundry as $value) {
                                                if($value->effect_gst_calculation==0 && $value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
                                                   <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_@php echo $index;@endphp" id="sundry_option_<?php echo $value->id;?>_@php echo $index;@endphp" <?php if($value->id==$sundry->bill_sundry){ echo "selected";} ?> data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                                   <?php 
                                                }
                                             } ?>
                                          </select>
                                       </td>
                                       <td class="w-min-50 "><span name="tax_amt[]" class="tax_amount" id="tax_amt_@php echo $index;@endphp">{{$sundry->rate}} %</span>
                                       <input type="hidden" name="tax_rate[]" id="tax_rate_tr_@php echo $index;@endphp" value="{{$sundry->rate}}"></td>
                                       <td class="w-min-50 ">
                                          <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_@php echo $index;@endphp" data-id="@php echo $index;@endphp" value="{{$sundry->amount}}" style="text-align: right;">
                                       </td>
                                       <td>
                                          <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="@php echo $index;@endphp" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg>
                                       </td>
                                    </tr>
                                    @php $index++;@endphp
                                 @endif
                              @endforeach
                              
                              <tr id="billtr_round_plus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($roundReturn['ROUNDED OFF (+)'])){?> style="display:none" <?php } ?>>
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
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_round_plus"></span>
                                    <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_round_plus">
                                 </td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_plus" data-id="round_plus" <?php if(isset($roundReturn['ROUNDED OFF (+)'])){?> value="<?php echo $roundReturn['ROUNDED OFF (+)'][0]['amount'];?>" <?php } ?> style="text-align: right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_round_minus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($roundReturn['ROUNDED OFF (-)'])){?> style="display:none" <?php } ?>>
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
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_round_minus"></span>
                                    <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_round_minus">
                                 </td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_minus" data-id="round_minus" <?php if(isset($roundReturn['ROUNDED OFF (-)'])){?> value="<?php echo $roundReturn['ROUNDED OFF (-)'][0]['amount'];?>" <?php } ?> style="text-align: right;">
                                 </td>
                                 <td></td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-min-50 fw-bold">Total</td>
                                 <td class="w-min-50 fw-bold"></td>
                                 <td class="w-min-50 fw-bold">
                                    <span id="bill_sundry_amt" style="float:right ;">{{$sale_return->total}}</span>
                                    <input type="hidden" name="total" id="total_amounts" value="{{$sale_return->total}}">
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
                                          <input type="text" name="vehicle_no" class="form-control" placeholder="Vehicle No." value="{{$sale_return->vehicle_no}}"/>
                                       </div>
                                       <div class="mb-6 col-md-6">
                                          <label for="name" class="form-label font-14 font-heading">Transport Name</label>
                                          <input type="text" id="transport_name" name="transport_name" class="form-control" placeholder="Transport Name" />
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
                              <div>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="2" class="pl-40">Vehicle No.</td>
                                    <td colspan="3" class="">
                                       <input type="text" name="vehicle_no" placeholder="Vehicle No." value="" class="form-control"/>
                                    </td>
                                 </tr>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="2" class="pl-40">GR/RR No.</td>
                                    <td colspan="3" class="">
                                       <input type="text" name="gr_pr_no" placeholder="GR/RR No." value="{{$sale_return->gr_pr_no}}" class="form-control" >
                                    </td>
                                 </tr>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="2" class="pl-40">Transport Name</td>
                                    <td colspan="3" class="">
                                       <input type="text" name="transport_name" placeholder="Transport Name" value="{{$sale_return->transport_name}}" class="form-control"/>
                                    </td>
                                 </tr>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="2" class="pl-40">Station</td>
                                    <td colspan="3" class="">
                                       <input type="text" name="station" placeholder="Station" value="{{$sale_return->station}}" class="form-control" placeholder="Station"/>
                                    </td>
                                 </tr>
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
                        @php $add_more_count_withgst = 1; @endphp
                        @if(count($without_gst)>0)
                           @foreach($without_gst as $k=>$v)
                           <tr class="font-14 font-heading bg-white" id="withgst_tr_{{$k}}">
                              <td style="width:50%">
                                 <select class="form-control item select2-single" id="item_{{$k}}" data-index="{{$k}}" name="item[]" onchange="gstCalculation()" >
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                       <option value="{{$item->id}}" @if($v->account_name==$item->id) selected @endif>{{$item->account_name}}</option>
                                    @endforeach
                                 </select>
                              </td>
                              <td>
                                 <input type="number" class="form-control hsn" id="hsn_{{$k}}" name="hsn[]" placeholder="HSN/SAC" value="{{$v->hsn_code}}">
                              </td>
                              <td style="width:15%">
                                 <select class="form-select percentage" id="percentage_{{$k}}" data-index="{{$k}}" name="percentage[]" onchange="gstCalculation()">
                                    <option value="">GST(%)</option>
                                    <option value="5" @if($v->percentage==5) selected @endif>5%</option>
                                    <option value="12" @if($v->percentage==12) selected @endif>12%</option>
                                    <option value="18" @if($v->percentage==18) selected @endif>18%</option>
                                    <option value="28" @if($v->percentage==28) selected @endif>28%</option>
                                 </select>
                              </td>
                              <td>
                                 <input type="text" class="form-control amount" id="amount_{{$k}}" data-index="{{$k}}" name="without_item_amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()" value="{{$v->debit}}">
                              </td>
                              <td>
                                 <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_more_tr" data-id="{{$k}}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"></path></svg>
                           </tr>
                           @php $add_more_count_withgst = $k; @endphp
                           @endforeach
                        @endif
                        <tr>
                           <td colspan="5" style="text-align: right;">
                              <svg xmlns="http://www.w3.org/2000/svg" style="cursor:pointer;" class="bg-primary rounded-circle add_more_tr" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"></path></svg>
                           </td>
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
                              <input type="text" class="form-control" name="remark" placeholder="Enter Remark" value="{{$sale_return->remark}}">
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
                           <th class="w-min-120 border-none bg-light-pink text-body" style="width: 40%;">Account</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Amount</th>
                           <th class="w-min-120 border-none bg-light-pink text-body">Narration</th>
                        </tr>
                     </thead>
                     <tbody>
                     @php $add_more_countts = 1; @endphp
                     @if(count($without_gst)>0)
                        @foreach($without_gst as $k1=>$v)
                           <tr  id="tr_without_{{$k1}}" class="font-14 font-heading bg-white">
                              <td class="">
                                 <select class="form-select select2-single account" id="account_{{$k1}}" name="account_name[]" data-id="{{$k1}}">
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($all_account_list as $value) { ?>
                                       <option value="<?php echo $value->id; ?>" @if($v->account_name==$value->id) selected @endif><?php echo $value->account_name; ?></option>
                                       <?php 
                                    } ?>
                                 </select>
                              </td>
                              <td class="">
                                 <input type="number" name="debit[]" class="form-control debit" data-id="{{$k1}}" id="debit_{{$k1}}" placeholder="Debit Amount" onkeyup="debitTotal();" value="{{$v->debit}}">
                              </td>
                              <td class="">
                                 <input type="text" name="narration[]" class="form-control narration" data-id="{{$k1}}" id="narration_{{$k1}}" placeholder="Enter Narration" value="{{$v->narration}}">
                              </td>
                              <td>
                                 <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_without" data-id="{{$k1}}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg>
                              
                              </td>
                           </tr>
                           @php $add_more_countts = $k1; @endphp
                        @endforeach
                     @endif
                        <tr>
                           <td style="text-align:right" colspan="5">
                              <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more_without" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                           </td>
                        </tr>
                     </tbody>
                     
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 fw-bold">Total</td>
                           <td class="w-min-120 fw-bold" id="total_debit"></td>
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
                     <input type="submit" value="SAVE" class="btn btn-xs-primary " id="saleReturnBtn">
                     <a href="{{ route('sale-return.index') }}" class="btn  btn-black ">QUIT</a>
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
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Master">
                     F3
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
   var merchant_gstin = "{{$merchant_gst}}";
   var percent_arr = [];
   var item_count = '{{--$i}}';
   var add_more_count = item_count;
   var add_more_counts = 1;
   var add_more_bill_sundry_up_count = '<?php echo --$index;?>';
   
   $('body').on('keydown', 'input, select, textarea', function(e) {
      var self = $(this),
      form = self.parents('form:eq(0)'),
      focusable, next, prev;
      if(e.shiftKey) {
         if(e.keyCode == 13) {
            focusable = form.find('input,a,select,button,textarea').filter(':visible');
            prev = focusable.eq(focusable.index(this) - 1);
            if(prev.length) {
               prev.focus();
            }  
         }
      }else if (e.keyCode == 13) {
         focusable = form.find('input,a,select,button,textarea').filter(':visible');
         next = focusable.eq(focusable.index(this) + 1);
         if(next.length) {
            next.focus();
         }
         return false;
      }
   });   
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
      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50">' + add_more_count + '</td><td class=""><select onchange="call_fun(\'tr_' + add_more_count + '\');" id="goods_discription_tr_' + add_more_count + '" class="w-95-parsent form-select goods_items" name="goods_discription[]" required data-id="'+add_more_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50"><input type="number" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '" style="text-align:right"/></td><td class=" w-min-50"><input type="text" class="w-100 form-control" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class=" w-min-50"><input type="number" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '" style="text-align:right"/></td><td class=" w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]" id="amount_tr_' + add_more_count + '" style="text-align:right"/></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $("#max_sale_descrption").val(add_more_count);
      $("#example11").append(newRow);
   });
   $(document).on("click", ".remove", function() {
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      var max_val = $("#max_sale_descrption").val();
      max_val--;
      $("#max_sale_descrption").val(max_val);
      calculateAmount();
   });
   $('#party').change(function() {
      // Get the selected value
      var account_id = $(this).val();
      var sale_bill_id = '{{$sale_return->sale_bill_id}}';
      var invoice_no = '{{$sale_return->invoice_no}}';
      $.ajax({
         url: '{{url("get/invoice/details")}}',
         async: false,
         type: 'POST',
         dataType: 'JSON',
         data: {
            _token: '<?php echo csrf_token() ?>',
            account_id: account_id,
            sale_bill_id : sale_bill_id
         },
         success: function(data){
            var optionElements = '<option value="">Select</option>';
            $.each(data, function(key, val) {
               let selected = "";
               if(invoice_no==val.voucher_no){
                  selected = "selected";
               }
               optionElements += '<option value="' + val.voucher_no + '" data-id="'+val.id+'" '+selected+'>' + val.voucher_no_prefix + '</option>';
            });
            $("#voucher_no").html(optionElements);
            $('#voucher_no').change();
         }
      });
   });
   $('#voucher_no').change(function() {
      // Get the selected value
      $("#invoice_id").attr('href',"{{ URL::to('sale-invoice/')}}/"+$('option:selected', this).attr('data-id'));
      $("#sale_bill_id").val($('option:selected', this).attr('data-id'));
      var invoice_id = $(this).val();
      $.ajax({
         url: '{{url("get/saleitems/details")}}',
         async: false,
         type: 'POST',
         dataType: 'JSON',
         data: {
            _token: '<?php echo csrf_token() ?>',
            voucher_no: invoice_id
         },
         success: function(data){
            var optionElements = '<option value="">Select</option>';
            $.each(data, function(key, val) {
               optionElements += '<option unit_id="' + val.unit_id + '" data-val="' + val.unit + '" value="' + val.item_id + '" data-percent="' + val.gst_rate + '">' + val.items_name + '</option>';
            });
            $(".goods_items").html(optionElements);
            @php 
            forEach($item_arr as $k=>$v){ @endphp
               $("#goods_discription_tr_"+'@php echo $k; @endphp').val('@php echo $v; @endphp');
              @php 
            }
            @endphp            
         }
      });
   });
   // Function to calculate amount and update total sum
   function calculateAmount(key=null) {

      customer_gstin = $("#party option:selected").attr("data-state_code");              
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
            }else{
               amount = $(this).find('.amount').val();
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
         if($("#bill_sundry_amount_"+id).val()!='' && (nature_of_sundry=='CGST' || nature_of_sundry=='SGST' || nature_of_sundry=='IGST') && nature_of_sundry!='ROUNDED OFF (+)' && nature_of_sundry!='ROUNDED OFF (-)'){
            if(type=="additive"){
               gstamount = parseFloat(gstamount) + parseFloat($("#bill_sundry_amount_"+id).val());
            }else{
               gstamount = parseFloat(gstamount) - parseFloat($("#bill_sundry_amount_"+id).val());
            }
         }
      }); 
      final_total = Math.round(final_total);
      $("#bill_sundry_amt").html(final_total);
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
   $(document).ready(function(){  
      sectionHideShow();
      $('#party').change();   
      calculateAmount();
      gstCalculation();
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
      $("#saleReturnBtn").click(function(){
         if(confirm("Are you sure to submit?")==true){
            $("#saleReturnForm").validate({
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
      var party_id = $('#party').val();
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
   function getAccountDeatils(e) {
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
   $( ".select2-single, .select2-multiple" ).select2(  );
   //Ashish Javascript   
   $(".add_more_bill_sundry_up").click(function() {
      add_more_bill_sundry_up_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = "<option value=''>Select</option>";
      <?php
      foreach ($billsundry as $value){ 
         if($value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';<?php 
         }
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type form-select w-100"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align: right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type form-select w-100"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align: right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align: right;"></td><td class="w-min-50"></td></tr>';
      $curRow.before(newRow);
   });
   $(document).on("click", ".remove_sundry_up", function() {
      let id = $(this).attr('data-id');
      $("#billtr_" + id).remove();      
      calculateAmount();
   });
   $("#party").change(function(){
      $("#partyaddress").html('');
      if($(this).val()==""){
         $("#party-error").show();
         return;
      }
      $("#party-error").hide();
      $("#partyaddress").html("GSTIN : "+$('option:selected', this).attr('data-gstin')+"<br>Address : "+$('option:selected', this).attr('data-address'));
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
      if(nature=="WITH GST" && (type=="RATE DIFFERENCE" || type=="WITH ITEM" || type=="WITHOUT ITEM")){
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
   var add_more_count_withgst = {{$add_more_count_withgst}};
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
      $curRow.before(newRow);
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
   var add_more_count = {{$add_more_countts}};
   $(".add_more_without").click(function(){
      let empty_status = 0;
      $(".account").each(function(){
         if($(this).val()=="" || $("#debit_"+$(this).attr('data-id')).val()==""){
            empty_status = 1;
         }
      });
      if(empty_status==1){
         alert("Please enter required fields");
         return;
      }

      add_more_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = $('#account_1').html();
      newRow = '<tr id="tr_without_' + add_more_count + '">';
      newRow += '<td class=""><select class="form-select select2-single account" id="account_' + add_more_count + '" name="account_name[]" data-id="' + add_more_count + '"><option value="">Select</option><?php foreach ($all_account_list as $value) { ?><option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option><?php } ?></select></td><td><input type="number" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" onkeyup="debitTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_without" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
      $('.select2-single').select2();
   });
   $(document).on("click", ".remove_without", function() {
      let id = $(this).attr('data-id');
      $("#tr_without_" + id).remove();
      debitTotal();
      creditTotal();
   });
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
   $("#nature").change(function(){
      $("#series_no").change();
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
         $("#sale_return_no").val("");
         return;
      }
      let series = $(this).val();
      let invoice_prefix = $('option:selected', this).attr('data-invoice_prefix');
      let invoice_start_from = $('option:selected', this).attr('data-invoice_start_from');
      $("#material_center").val($('option:selected', this).attr('data-mat_center'));
      merchant_gstin = $('option:selected', this).attr('data-gst_no');
      if(nature=="WITHOUT GST"){
         if(invoice_prefix!=""){
            $("#voucher_prefix").val(invoice_prefix+"CRWT/{{Session::get('default_fy')}}/"+$('option:selected', this).attr('data-without_invoice_start_from'));
         }else{
            $("#voucher_prefix").val($('option:selected', this).attr('data-without_invoice_start_from'));
         }
         $("#sale_return_no").val($('option:selected', this).attr('data-without_invoice_start_from'));
      }else{
         if(invoice_prefix!=""){
            $("#voucher_prefix").val(invoice_prefix);
         }else{
            $("#voucher_prefix").val(invoice_start_from);
         }         
         $("#sale_return_no").val(invoice_start_from);
      }
      
      calculateAmount();
          
   });
</script>
@endsection