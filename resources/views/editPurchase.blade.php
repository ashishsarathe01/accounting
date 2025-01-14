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
<?php
$item_list = '<option value="">Select</option>';
foreach ($manageitems as $value) {
   $item_list.='<option unit_id="'.$value->u_name.'" data-val="'.$value->unit.'" data-percent="'.$value->gst_rate.'" value="'.$value->id.'">'.$value->name.'</option>';
} ?>
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
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Purchase</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Purchase Voucher</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('purchase.update')}}" id="purchaseForm">
               @csrf
               <div class="row">
                  <input type="hidden" name="purchase_edit_id" value="{{$purchase->id}}">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" name="series_no" class="form-select" required>
                        <option value="">Select</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value['branch_series'];?>" <?php if($value['branch_series']==$purchase->series_no){ echo "selected";}?>><?php echo $value['branch_series']; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <ul style="color: red;">
                       @error('series_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" value="{{$purchase->date}}" placeholder="Select date" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     <ul style="color: red;">
                       @error('date'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                     <input type="text" class="form-control" name="voucher_no" placeholder="Enter Invoice No." value="{{$purchase->voucher_no}}">
                     <ul style="color: red;">
                       @error('voucher_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Party</label>
                     <select class="form-select select2-single" id="party" name="party" required>
                        <option value="">Select </option>
                        <?php
                        foreach ($party_list as $value) { ?>
                           <option value="<?php echo $value->id; ?>" data-gstin="<?php echo $value->gstin; ?>" data-address="<?php echo $value->address.",".$value->pin_code; ?>" data-state_code="<?php echo $value->state_code; ?>" <?php if($value->id==$purchase->party){ echo "selected";}?>><?php echo $value->account_name; ?></option>
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
                     <select name="material_center" class="form-select" required>
                        <option value="">Select</option>
                        <?php
                        if (count($mat_center) > 0) {
                           foreach ($mat_center as $value) { ?>
                              <option value="<?php echo $value['branch_matcenter']; ?>" <?php if($value['branch_matcenter']==$purchase->material_center){ echo "selected";}?>><?php echo $value['branch_matcenter']; ?></option>
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
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-50 border-none bg-light-pink text-body ">Description of Goods
                           </th>                           
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Qty</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: center;">Unit</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Amount</th>
                           <th class="w-min-50 border-none bg-light-pink text-body "></th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $i=1; $total = 0; @endphp
                        @foreach($PurchaseDescription as $item)
                           <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                              <td class="w-min-50" id="srn_{{$i}}">{{$i}}</td>
                              <td class="">
                                 <select onchange="call_fun('tr_{{$i}}');" class="border-0 goods_items select2-single" id="goods_discription_tr_{{$i}}" name="goods_discription[]" required data-id="{{$i}}">
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($manageitems as $value) { ?>
                                       <option unit_id="<?php echo $value->u_name; ?>" data-val="<?php echo $value->unit; ?>" data-percent="<?php echo $value->gst_rate; ?>" value="<?php echo $value->id; ?>" <?php if($value->id==$item->goods_discription){ echo "selected";} ?>><?php echo $value->name; ?></option>
                                       <?php 
                                    } ?>
                                 </select>
                              </td>                              
                              <td class=" w-min-50">
                                 <input type="number" class="quantity form-control w-100" id="quantity_tr_{{$i}}" name="qty[]" value="{{$item->qty}}" style="text-align:right"/>
                              </td>
                              <td class=" w-min-50">
                                 <input type="text" class="w-100 form-control" id="unit_tr_{{$i}}" readonly style="text-align:center;" value="{{$item->s_name}}" />
                                 <input type="hidden" class="units" name="units[]" id="units_tr_{{$i}}" value="{{$item->unit}}" />
                              </td>
                              <td class=" w-min-50">
                                 <input type="number" class="price w-100 form-control" id="price_tr_{{$i}}" name="price[]" value="{{$item->price}}" style="text-align:right"/></td>
                              <td class=" w-min-50">
                                 <input type="number" id="amount_tr_{{$i}}" class="amount w-100 form-control" name="amount[]"   value="{{$item->amount}}" style="text-align:right" />
                              </td>
                              <td class="">
                                 <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="{{$i}}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg>                                
                              </td>
                           </tr>
                           @php $i++; $total = $total + $item->amount; @endphp
                        @endforeach
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class=" " colspan="7">
                              <a class="add_more">
                                 <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                 </svg>
                              </a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-50 fw-bold"></td>
                           <td class="fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold">Total</td>
                           <td class="w-min-50 fw-bold">
                              <span id="totalSum" style="float:right;">{{$total}}</span>
                              <input type="hidden" name="taxable_amt" id="total_taxable_amounts" value="{{$total}}">
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
                              @php $index = 1;@endphp                           
                              @foreach($PurchaseSundry as $sundry)
                                 @if($sundry->effect_gst_calculation==1)
                                    <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                       <td class="w-min-50">
                                          <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
                                             <option value="">Select</option>
                                             <?php
                                             foreach ($billsundry as $value) { 
                                                if($value->effect_gst_calculation==1){?>
                                                   <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" class="sundry_option_@php echo $index;@endphp" id="sundry_option_<?php echo $value->id;?>_1" <?php if($sundry->bill_sundry==$value->id){ echo "selected";} ?> data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
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
                                          <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_@php echo $index;@endphp" data-id="@php echo $index;@endphp" value="{{$sundry->amount}}" style="text-align:right;">
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
                                       <a class="add_more_bill_sundry_up"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
                                    </td>
                                 </tr>
                              </div>
                              
                              <?php 
                              $return = array();$roundReturn = array();
                              foreach($PurchaseSundry as $val) {
                                 if($val['nature_of_sundry']=="CGST" || $val['nature_of_sundry']=="SGST" || $val['nature_of_sundry']=="IGST"){
                                    $return[$val['nature_of_sundry']][] = $val;
                                 }else if($val['nature_of_sundry']=="ROUNDED OFF (+)" || $val['nature_of_sundry']=="ROUNDED OFF (-)"){
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
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_cgst" data-id="cgst" <?php if(isset($return['CGST'])){?> value="{{$return['CGST'][0]['amount']}}" <?php } ?> style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_sgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($return['SGST'])){?> style="display:none" <?php } ?>>
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
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_sgst">@if(isset($return['SGST'])){{$return['SGST'][0]['rate']}} %@endif</span>
                                    <input type="hidden" name="tax_rate[]" value="@if(isset($return['SGST'])){{$return['SGST'][0]['rate']}}@endif" id="tax_rate_tr_sgst">
                                 </td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_sgst" data-id="sgst" <?php if(isset($return['SGST'])){?> value="<?php echo $return['SGST'][0]['amount'];?>" <?php } ?> style="text-align:right;">
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
                                    <input type="hidden" name="tax_rate[]" value="@if(isset($return['IGST'])){{$return['IGST'][0]['rate']}}@endif" id="tax_rate_tr_igst"></td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_igst" data-id="igst" <?php if(isset($return['IGST'])){?> value="<?php echo $return['IGST'][0]['amount'];?>" <?php } ?> style="text-align:right;"></td>
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
                                                      <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_@php echo $index;@endphp" id="sundry_option_@php echo $index;@endphp" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
                                                      <?php 
                                                   }
                                                } ?>
                                             </select>
                                          </td>
                                          <td class="w-min-50 ">
                                             <span name="tax_amt[]" class="tax_amount" id="tax_amt_@php echo $index;@endphp">{{$v1['rate']}} %</span>
                                             <input type="hidden" name="tax_rate[]" value="{{$v1['rate']}}" id="tax_rate_tr_@php echo $index;@endphp"></td>
                                          <td class="w-min-50 ">
                                             <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_@php echo $index;@endphp" data-id="@php echo $index;@endphp" value="{{$v1['amount']}}" style="text-align:right;">
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
                              @foreach($PurchaseSundry as $sundry)
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
                                       <input type="hidden" name="tax_rate[]"  id="tax_rate_tr_@php echo $index;@endphp" value="{{$sundry->rate}}"></td>
                                       <td class="w-min-50 ">
                                          <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_@php echo $index;@endphp" data-id="@php echo $index;@endphp" value="{{$sundry->amount}}" style="text-align:right;">
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
                                       <a class="add_more_bill_sundry_down"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
                                    </td>
                                 </tr>
                              </div>
                              <tr id="billtr_round_plus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($roundReturn["ROUNDED OFF (+)"])){?> style="display:none" <?php } ?>>
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_plus" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="round_plus">
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
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_plus" data-id="round_plus" <?php if(isset($roundReturn["ROUNDED OFF (+)"])){?> value="<?php echo $roundReturn["ROUNDED OFF (+)"][0]['amount'];?>" <?php } ?> style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_round_minus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($roundReturn['ROUNDED OFF (-)'])){?> style="display:none" <?php } ?>>
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_minus" class="w-95-parsent  bill_sundry_tax_type  form-select" name="bill_sundry[]" data-id="round_minus">
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
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_minus" data-id="round_minus" <?php if(isset($roundReturn["ROUNDED OFF (-)"])){?> value="<?php echo $roundReturn["ROUNDED OFF (-)"][0]['amount'];?>" <?php } ?> style="text-align:right;">
                                 </td>
                                 <td></td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-min-50 fw-bold">Total</td>
                                 <td class="w-min-50 fw-bold"></td>
                                 <td class="w-min-50 fw-bold">
                                    <span id="bill_sundry_amt" style="float:right ;">{{$purchase->total}}</span>
                                    <input type="hidden" name="total" id="total_amounts" value="{{$purchase->total}}">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                        <table id="transcton-sale3" class="table-striped table m-0 shadow-sm table-bordered">
                           <tbody>
                              <div>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="2" class="pl-40">Self Vehicle</td>
                                    <td colspan="3">
                                       <select class="w-95-parsent form-select" id="self_vehicle" name="self_vehicle">
                                          <option value="">Select</option>
                                          <option value="Yes" <?php if($purchase->self_vehicle=="Yes"){ echo "selected";}?>>Yes</option>
                                          <option value="No" <?php if($purchase->self_vehicle=="No"){ echo "selected";}?>>No</option>
                                       </select>
                                    </td>
                                 </tr>
                                 <tr class="font-14 font-heading bg-white">
                                    <td colspan="2" class="pl-40">Vehicle No.</td>
                                    <td colspan="3" class="">
                                       <input type="text" name="vehicle_no" value="{{$purchase->vehicle_no}}" placeholder="Vehicle No." class="form-control"/>
                                    </td>
                                 </tr>
                              </div>
                              <div class="other-details">
                                 <tr>
                                    <td colspan="2" class="pl-40">
                                       <div class="custom-checkbox me-4 " id="toggleButton">
                                          <input type="checkbox" class="custom-checkbox-input" name="flexRadioDefault3" id="flexRadioDefault3">
                                          <label for="flexRadioDefault3" class="custom-checkbox-label">Other Detail</label>
                                       </div>
                                    </td>
                                    <td class="" colspan="3"></td>
                                 </tr>
                                 <div class="other-details-show-details">
                                    <tr class="font-14 font-heading bg-white other-details-show other-details-show">
                                       <td colspan="2" class="pl-40">Transport Name</td>
                                       <td colspan="3" class="">
                                          <input type="text" name="transport_name" value="{{$purchase->transport_name}}" placeholder="Transport Name" class="form-control"/>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">Reverse Charge</td>
                                       <td colspan="3">
                                          <select class="w-95-parsent form-select" id="reverse_charge" name="reverse_charge">
                                             <option value="">Select</option>
                                             <option value="Yes" <?php if($purchase->reverse_charge=="Yes"){ echo "selected";}?>>Yes</option>
                                             <option value="No" <?php if($purchase->reverse_charge=="No"){ echo "selected";}?>>No</option>
                                          </select>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">GR/RR No.</td>
                                       <td colspan="3" class="">
                                          <input type="text" name="gr_pr_no" value="{{$purchase->gr_pr_no}}" class="form-control" placeholder="GR/RR No."/>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">Station</td>
                                       <td colspan="3" class="">
                                          <input type="text" name="station" value="{{$purchase->station}}" placeholder="Station" class="form-control"/>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">Shipping Name</td>
                                       <td colspan="3">
                                          <select class="w-95-parsent form-select" id="shipping_name" name="shipping_name" onchange="getAccountDeatils(this)">
                                             <option value="">Select</option>
                                             <?php
                                             foreach ($party_list as $value) { ?>
                                                <option value="<?php echo $value->id; ?>" <?php if($purchase->shipping_name==$value->id){ echo "selected";}?>><?php echo $value->account_name; ?></option>
                                                <?php 
                                             } ?>
                                          </select>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">Shipping Address</td>
                                       <td colspan="3" class="">
                                          <input type="text" name="shipping_address" value="{{$purchase->shipping_address}}" class="form-control" placeholder="Shipping Address"/>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">Shipping Pincode</td>
                                       <td colspan="3" class="">
                                          <input type="text" name="shipping_pincode" value="{{$purchase->shipping_pincode}}" placeholder="Shipping Pincode" class="form-control"/>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">Shipping GST</td>
                                       <td colspan="3" class="">
                                          <input type="text" name="shipping_gst" value="{{$purchase->shipping_gst}}" class="form-control" placeholder="Shipping GST"/>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">Shipping PAN</td>
                                       <td colspan="3" class="">
                                          <input type="text" name="shipping_pan" value="{{$purchase->shipping_pan}}" class="form-control" placeholder="Shipping PAN"/>
                                          <input type="hidden" name="shipping_state" value="{{$purchase->shipping_state}}"/>
                                       </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white other-details-show">
                                       <td colspan="2" class="pl-40">E-Way Bill No</td>
                                       <td colspan="3" class=""><input type="text" name="ewaybill_no" value="{{$purchase->ewaybill_no}}" class="form-control" placeholder="E-Way Bill No"/>
                                       </td>
                                    </tr>
                                 </div>
                              </div>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <input type="submit" value="SAVE" class="btn btn-xs-primary" id="purchaseBtn">
                     <a href="{{ route('purchase.index') }}" class="btn  btn-black ">QUIT</a>
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
   var merchant_gstin = "{{$GstSettings->gst_no}}";
   var percent_arr = [];
   var add_more_count = '<?php echo --$i;?>';

   var add_more_counts = 1;
   var add_more_bill_sundry_up_count = '<?php echo --$index;?>';
   $(".add_more").click(function() {
      let empty_status = 0;
      $('.goods_items').each(function(){   
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
      var optionElements = '<?php echo $item_list;?>';
      var tr_id = 'tr_' + add_more_count;
      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50" id="srn_'+add_more_count+'">' + srn + '</td><td class=""><select onchange="call_fun(\'tr_' + add_more_count + '\');" id="goods_discription_tr_' + add_more_count + '" class="border-0 w-95-parsent  goods_items" name="goods_discription[]" required data-id="'+add_more_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class=""><input type="number" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '"/ style="text-align:right"></td><td class=" w-min-50"><input type="text" class="w-100 form-control" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class=" w-min-50"><input type="number" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '"/ style="text-align:right"></td><td class=" w-min-50 form-control"><input type="number" class="amount w-100" name="amount[]" id="amount_tr_' + add_more_count + '"  / style="text-align:right"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="'+add_more_count+'" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $("#max_sale_descrption").val(add_more_count);
      $("#example11").append(newRow);
      $("#goods_discription_tr_"+add_more_count).select2();

      let k = 1;
      $('.goods_items').each(function(){   
         let i = $(this).attr('data-id');
         $("#srn_"+i).html(k);  
         k++;           
      });
   });
   $(".add_more_bill_sundry_up").click(function() {
      add_more_bill_sundry_up_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = "<option value=''>Select</option>";
      <?php
      foreach ($billsundry as $value){ 
         if($value->effect_gst_calculation==1){?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';<?php 
         }
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100 form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent  bill_sundry_tax_type form-select w-100"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" style="text-align:right;"></td><td class="w-min-50"></td></tr>';
      $curRow.before(newRow);
   });
   $(document).on("click", ".remove", function() {
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      var max_val = $("#max_sale_descrption").val();
      max_val--;
      $("#max_sale_descrption").val(max_val);
      calculateAmount();
   });
   
   $(".add_more_bill_sundry").click(function() {
      add_more_counts++;
      var optionElements = "<option value=''>Select</option>";
      <?php
      foreach ($billsundry as $value) { ?>
         optionElements += '<option value="<?php echo $value->id; ?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_counts+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_counts+'" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';
         <?php 
      } ?>
      newRow = '<tr id="billtr_' + add_more_counts + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50 ">' + add_more_counts + '</td><td class="w-min-50"><select class="bill_sundry_tax_type  form-select"  id="bill_sundry_' + add_more_counts + '" name="bill_sundry[]" data-id="'+add_more_counts+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-120 "><span name="tax_amt[]" id="tax_amt_' + add_more_counts + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_counts + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount' + add_more_counts + '" name="bill_sundry_amount[]" data-id="'+add_more_counts+'" style="text-align:right;"/></td><td><button data-id="' + add_more_counts + '" class="btn btn-danger remove_sundry" id="remove_sundry_'+add_more_counts+'">Remove</button></td></tr>';
      $("#transcton-sale").append(newRow);
      $("#max_sale_sundry").val(add_more_counts);
   });
   // Function to calculate amount and update total sum
   function calculateAmount(key=null) {
      customer_gstin = $("#party option:selected").attr("data-state_code");   
      $(".extra_gst").remove();           
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
                              if(e.type=='additive' && e.effect_gst_calculation=='1'){
                                 item_taxable_amount = item_taxable_amount + parseFloat(e.value);
                                 final_total = final_total + parseFloat(e.value);
                              }else if(e.type=='subtractive' && e.effect_gst_calculation=='1'){
                                 item_taxable_amount = item_taxable_amount - parseFloat(e.value);
                                 final_total = final_total - parseFloat(e.value);
                              }else if(e.effect_gst_calculation=='0' && e.type=='additive'){
                                 final_total = final_total + parseFloat(e.value);
                              }else if(e.effect_gst_calculation=='0' && e.type=='subtractive'){
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
                              if(e.type=='additive' && e.effect_gst_calculation=='1'){
                                 item_taxable_amount = item_taxable_amount + parseFloat(e.value);
                                 final_total = final_total + parseFloat(e.value);
                              }else if(e.type=='subtractive' && e.effect_gst_calculation=='1'){
                                 item_taxable_amount = item_taxable_amount - parseFloat(e.value);
                                 final_total = final_total - parseFloat(e.value);
                              }else if(e.effect_gst_calculation=='0' && e.type=='additive'){
                                 final_total = final_total + parseFloat(e.value);
                              }else if(e.effect_gst_calculation=='0' && e.type=='subtractive'){
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
                  let sundry_value = "";
                  if(billSundryArray.length>0){
                     billSundryArray.forEach(function(e){
                        if(e.nature_of_sundry=='IGST'){ 
                           sundry_value = e.id;
                        }
                     });
                  }
                  $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['igst']);
                  //$("#bill_sundry_amount_"+add_more_bill_sundry_up_count).prop('readonly',true);
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
         if($('option:selected', this).attr('data-effect_gst_calculation')=="0" && $("#bill_sundry_amount_"+id).val()!='' && nature_of_sundry!='ROUNDED OFF (+)' && nature_of_sundry!='ROUNDED OFF (-)'){
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
   }
   $(document).ready(function() {
      
      calculateAmount();
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
      if(party_id.length > 0){
         $('#unit_' + data).val(selectedOptionData);
         $('#units_' + data).val(item_units_id);
         calculateAmount();
      }else{
         alert("Select Party Name First.");
         $('#goods_discription_' + data + '').val("");
      }
   }
   $(document).on("click", ".remove_sundry", function(){
      let id = $(this).attr('data-id');
      $("#bill_sundry_amount_" + id).val('');
      calculateAmount();
   });
   $( ".select2-single, .select2-multiple" ).select2();
   function getAccountDeatils(e){
      $("input[name='shipping_address']").val('');
      $("input[name='shipping_pincode']").val('');
      $("input[name='shipping_gst']").val('');
      $("input[name='shipping_pan']").val('');
      $("input[name='shipping_state']").val('');
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
   $(document).on("click", ".remove_sundry_up", function() {
      let id = $(this).attr('data-id');
      $("#billtr_" + id).remove();      
      calculateAmount();
   });
   $("#party").change(function(){
      $("#partyaddress").html('');
      if($(this).val()==""){
         $("#party-error").show()
         return;
      }
      $("#party-error").hide()
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
   $(".goods_items").change(function(){
      let id = $(this).attr('data-id');
      if($(this).val()==""){
         $("#goods_discription_tr_"+id+"-error").show();
      }else{
         $("#goods_discription_tr_"+id+"-error").hide();
      }
   });
</script>
@endsection