@extends('layouts.app')
@section('content')
@include('layouts.header')
@section('title', 'Edit Purchase')
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
   $item_list.='<option unit_id="'.$value->u_name.'" data-val="'.$value->unit.'" data-percent="'.$value->gst_rate.'" value="'.$value->id.'" data-parameterized_stock_status="'.$value->parameterized_stock_status.'" data-config_status="'.$value->config_status.'" data-group_id="'.$value->group_id.'">'.$value->name.'</option>';
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
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Purchase Voucher</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('purchase.update')}}" id="purchaseForm">
               @csrf
               <div class="row">
                  <input type="hidden" name="rowId" value="{{$rowId}}">
                  <input type="hidden" name="purchase_edit_id" value="{{$purchase->id}}">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" name="series_no" class="form-select" autofocus required>
                        <option value="">Select</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->series; ?>" <?php if($value->series==$purchase->series_no){ echo "selected";} ?> data-mat_center="<?php echo $value->mat_center;?>" data-gst_no="<?php echo $value->gst_no;?>" data-invoice_start_from="<?php echo $value->invoice_start_from;?>"><?php echo $value->series; ?></option>
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
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" value="{{$purchase->date}}" placeholder="Select date" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     <ul style="color: red;">
                       @error('date'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  @if($stockEntryEnabled)
                     <div class="col-md-3">
                        <label class="form-label">
                           Stock Entry Date <span class="text-danger">*</span>
                        </label>
                        <input
                           type="date"
                           name="stock_entry_date"
                           class="form-control"
                           value="{{ old('stock_entry_date', $stock_entry_date) }}"
                           required
                        >
                     </div>
                  @endif
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                     <input type="text" class="form-control" id="voucher_no" name="voucher_no" placeholder="Enter Invoice No." value="{{$purchase->voucher_no}}">
                     <ul style="color: red;">
                       @error('voucher_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">PURCHASE TYPE</label>
                     <input type="text" class="form-control" id="purchase_type" name="purchase_type" placeholder="PURCHASE TYPE" readonly>
                  </div>
                  <div class="mb-6 col-md-6">
                     <label for="name" class="form-label font-14 font-heading">Party</label>
                     <select class="form-select select2-single" id="party" name="party" required data-modal="accountModal">
                        <option value="">Select </option>
                        <?php
                        foreach ($party_list as $value) { ?>
                           <option value="<?php echo $value->id; ?>" data-gstin="<?php echo $value->gstin; ?>" data-address="<?php echo $value->address.",".$value->pin_code; ?>" data-state_code="<?php echo $value->state_code; ?>" data-group="<?php echo $value->under_group ?? ''; ?>" <?php if($value->id==$purchase->party){ echo "selected";}?>><?php echo $value->account_name; ?></option>
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
                     <select name="material_center" id="material_center" class="form-select" required>
                        <option value="">Select</option>
                        <?php
                        if (count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->mat_center; ?>" <?php if($value->mat_center==$purchase->material_center){ echo "selected";}?>><?php echo $value->mat_center; ?></option>
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
                       
                        @php $i=1; $total = 0; $itemcount = count($PurchaseDescription);@endphp
                        @foreach($PurchaseDescription as $item)
                           <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                              <td class="w-min-50" id="srn_{{$i}}">{{$i}}</td>
                              <td class="">
                                 <select onchange="call_fun('tr_{{$i}}');" class="border-0 form-control goods_items select2-single" id="goods_discription_tr_{{$i}}" name="goods_discription[]" required data-id="{{$i}}" data-modal="itemModal">
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($manageitems as $value) { ?>
                                       <option value="{{$value->id}}" unit_id="<?php echo $value->u_name; ?>" data-val="<?php echo $value->unit; ?>" data-percent="<?php echo $value->gst_rate; ?>" value="<?php echo $value->id; ?>" <?php if($value->id==$item->goods_discription){ echo "selected";} ?> data-parameterized_stock_status="{{$value->parameterized_stock_status}}" data-config_status="{{$value->config_status}}" data-group_id="{{$value->group_id}}"><?php echo $value->name; ?></option>
                                       <?php 
                                    } ?>
                                 </select>
                              </td>                              
                              <td class=" w-min-50">
                                 <input type="number" class="quantity form-control w-100" id="quantity_tr_{{$i}}" name="qty[]" value="{{$item->qty}}" style="text-align:right" @if($stock_status==0) readonly @endif/>
                              </td>
                              <td class=" w-min-50">
                                 <input type="text" class="w-100 form-control @if($stock_status==1) unit @endif " id="unit_tr_{{$i}}" readonly style="text-align:center;" value="{{$item->s_name}}"  data-id="{{$i}}" data-row_id="{{$item->id}}"/>
                                 <input type="hidden" class="units" name="units[]" id="units_tr_{{$i}}" value="{{$item->unit}}" />
                              </td>
                              <td class=" w-min-50">
                                 <input type="number" class="price w-100 form-control" id="price_tr_{{$i}}" name="price[]" value="{{$item->price}}" style="text-align:right"/></td>
                              <td class=" w-min-50">
                                 <input type="number" id="amount_tr_{{$i}}" class="amount w-100 form-control" name="amount[]" data-id="{{$i}}"   value="{{$item->amount}}" style="text-align:right" />
                              </td>
                              <td class="" style="display:flex">
                              {{-- Show remove icon for all rows except the first --}}
                              @if($i != "1")
                                 <svg style="color: red; cursor: pointer; margin-left: 10px;" 
                                       xmlns="http://www.w3.org/2000/svg" 
                                       width="24" height="24" 
                                       fill="currentColor" 
                                       tabindex="0"
                                       class="bi bi-file-minus-fill remove" 
                                       data-id="{{ $i }}" 
                                       viewBox="0 0 16 16">
                                       <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                                 </svg>
                              @endif

                              {{-- Show add icon only on the last row --}}
                              @if($itemcount == $i)
                                 <span class="add_btn_class" id="add_btn_id_{{ $i }}">
                                       <svg xmlns="http://www.w3.org/2000/svg" 
                                       tabindex="0"
                                       data-id="{{$i}}"
                                          class="bg-primary rounded-circle add_more_wrapper" 
                                          width="24" height="24" 
                                          viewBox="0 0 24 24" 
                                          fill="none" 
                                          style="cursor: pointer;">
                                          <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                       </svg>
                                 </span>
                              @endif
                              </td>
                              @php 
                              $final = [];                                
                              if(count($item->parameterColumnInfo)>0){
                                 foreach ($item->parameterColumnInfo as $pkey => $pvalue) {   
                                    $param_arr = [];                              
                                    if($pvalue['parameter1_id']!="" && $pvalue['parameter1_id']!=0){
                                       array_push($param_arr,array("id"=>$pvalue['parameter1_id'],"value"=>$pvalue['parameter1_value'],"alternative_unit"=>$pvalue['parameter1_alternative_unit'],"alternative_qty"=>1));
                                    }
                                    if($pvalue['parameter2_id']!="" && $pvalue['parameter2_id']!=0){
                                       array_push($param_arr,array('id'=>$pvalue['parameter2_id'],"value"=>$pvalue['parameter2_value'],"alternative_unit"=>$pvalue['parameter2_alternative_unit'],"alternative_qty"=>1));
                                    }
                                    if($pvalue['parameter3_id']!="" && $pvalue['parameter3_id']!=0){
                                       array_push($param_arr,array('id'=>$pvalue['parameter3_id'],"value"=>$pvalue['parameter3_value'],"alternative_unit"=>$pvalue['parameter3_alternative_unit'],"alternative_qty"=>1));
                                    }
                                    if($pvalue['parameter4_id']!="" && $pvalue['parameter4_id']!=0){
                                       array_push($param_arr,array('id'=>$pvalue['parameter4_id'],"value"=>$pvalue['parameter4_value'],"alternative_unit"=>$pvalue['parameter4_alternative_unit'],"alternative_qty"=>1));
                                    }
                                    if($pvalue['parameter5_id']!="" && $pvalue['parameter5_id']!=0){
                                       array_push($param_arr,array('id'=>$pvalue['parameter5_id'],"value"=>$pvalue['parameter5_value'],"alternative_unit"=>$pvalue['parameter5_alternative_unit'],"alternative_qty"=>1));
                                    }        
                                    array_push($final,$param_arr);
                                                             
                                 }                                 
                              }
                              @endphp
                              <input type="hidden" name="item_parameters[]" id="item_parameters_tr_{{ $i }}" value="{{json_encode($final)}}">
                              <input type="hidden" name="config_status[]" id="config_status_tr_{{ $i }}">
                           </tr>
                           @php $i++; $total = $total + $item->amount; @endphp
                        @endforeach
                     </tbody>
                     
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
                              @php $index = 1;$count_sundry = 0;@endphp                           
                              @foreach($PurchaseSundry as $sundry)
                                 @if($sundry->nature_of_sundry!='CGST' && $sundry->nature_of_sundry!='SGST' && $sundry->nature_of_sundry!='IGST' && $sundry->nature_of_sundry!='ROUNDED OFF (+)' && $sundry->nature_of_sundry!='ROUNDED OFF (-)')
                                    @php $count_sundry++ @endphp
                                    <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                       <td class="w-min-50">
                                          <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
                                             <option value="">Select</option>
                                             <?php
                                             foreach ($billsundry as $value) { 
                                                if($value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
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
                                          @if($index != "1")
                                                <svg style="color: red; cursor: pointer; margin-left: 10px;" 
                                                   xmlns="http://www.w3.org/2000/svg" 
                                                   width="24" height="24" 
                                                   fill="currentColor" 
                                                   class="bi bi-file-minus-fill remove_sundry_up" 
                                                   data-id="@php echo $index;@endphp" 
                                                   viewBox="0 0 16 16">
                                                   <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                                             </svg>
                                          @endif
                                          <span class="add_sundry_btn_class" id="add_sundry_btn_id_@php echo $index;@endphp" data-id="@php echo $index;@endphp"></span>
                                       </td>
                                    </tr>
                                    @php $index++;@endphp
                                 @endif
                              @endforeach  
                              @php 
                                 if($count_sundry==0){ @endphp
                                    <tr class="font-14 font-heading bg-white bill_taxes_row">
                                       <td class="w-min-50" colspan="4">
                                          <span class="add_sundry_btn_class" id="add_sundry_btn_id_default" data-id="default">
                                             <svg style="cursor:pointer;float:right;" xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more_bill_sundry_up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"></path></svg>
                                          </span>
                                       </td>                                    
                                    </tr>
                                    @php 
                                 }
                              @endphp                            
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
                                    if(!in_array($v1['id'],$saleSundryArr)){ 
                                       
                                       ?>
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
                                    <td colspan="4" class="pl-40"><button type="button" class="btn btn-info transport_info" style="float:right">Transport Info</button></td>
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
                                             <input type="text" name="vehicle_no" class="form-control" placeholder="Vehicle No." value="{{$purchase->vehicle_no}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Transport Name</label>
                                             <input type="text" id="transport_name" name="transport_name" class="form-control" placeholder="Transport Name" value="{{$purchase->transport_name}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Reverse Charge</label>
                                             <select class="w-95-parsent form-select" id="reverse_charge" id="reverse_charge" name="reverse_charge">
                                                <option value="">Select</option>
                                                <option value="Yes" <?php if($purchase->reverse_charge=="Yes"){ echo "selected";}?>>Yes</option>
                                                <option value="No" <?php if($purchase->reverse_charge=="No"){ echo "selected";}?>>No</option>
                                             </select>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">GR/RR No.</label>
                                             <input type="text" id="gr_pr_no" name="gr_pr_no" class="form-control" placeholder="GR/RR No" value="{{$purchase->gr_pr_no}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Station</label>
                                             <input type="text" id="station" name="station" class="form-control" placeholder="Station" value="{{$purchase->station}}"/>
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
                      value="{{ $purchase->narration ?? '' }}"
                      placeholder="Enter narration for the entry...">
                </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <input type="submit" value="SAVE" class="btn btn-xs-primary purchaseBtn" id="purchaseBtn">
                     <button type="button" onclick="redirectBack()" class="btn btn-danger">QUIT</button>
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
   var bill_sundry_array = @json($billsundry);
   var parameter_assign_item_arr = [];
   var pageLoaded = 1;
   function redirectBack(){
      let previousUrl = document.referrer; // Get Previous URL
      if(previousUrl == "{{ session('previous_url_purchase')  }}"){
         window.location.href = "https://www.meriaccounting.com/purchase"; // Fixed Redirect
      }else{
         history.back(); // Go Back to previous page
      }
   }
   var selected_series = "";
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
   var customer_gstin = "";
   var merchant_gstin = "";
   var gstApplicable = {{ $gstApplicable ? 'true' : 'false' }};
   var initialGstApplicable = gstApplicable;
   var gst_disabled_for_party = 0;
   var percent_arr = [];
   var partyGSTData = {};
   var add_more_count = '<?php echo --$i;?>';
   var add_more_counts = 1;
   var add_more_bill_sundry_up_count = '<?php echo --$index;?>';
   var noGSTGroups = @json($no_gst_group_ids);
   function addMoreItem() {   
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
      //var selectHTML = $('#goods_discription').prop('outerHTML');
      var tr_id = 'tr_' + add_more_count;
      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50" id="srn_'+add_more_count+'">' + srn + '</td><td class=""><select onchange="call_fun(\'tr_' + add_more_count + '\');" id="goods_discription_tr_' + add_more_count + '" class="border-0 w-95-parsent  goods_items" name="goods_discription[]" required data-id="'+add_more_count+'" data-modal="itemModal">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50"><input type="number" data-id="'+add_more_count+'" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '"/ style="text-align:right"></td><td class=" w-min-50"><input type="text" class="w-100 form-control unit"data-id="'+add_more_count+'" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class=" w-min-50"><input type="number" class="price w-100 form-control" data-id="'+add_more_count+'" name="price[]" id="price_tr_' + add_more_count + '" style="text-align:right"/></td><td class=" w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]"data-id="'+ add_more_count +'" id="amount_tr_' + add_more_count + '"  style="text-align:right"/></td><td class="w-min-50" style="display:flex"></td><input type="hidden" name="item_parameters[]" id="item_parameters_tr_' + add_more_count + '"><input type="hidden" name="config_status[]" id="config_status_tr_' + add_more_count + '"></tr>';
      $("#max_sale_descrption").val(add_more_count);
      $("#example11").append(newRow);
      $("#goods_discription_tr_"+add_more_count).select2();
      let k = 1;
      $(".add_btn_class").html('');
      $('.goods_items').each(function(){   
         let i = $(this).attr('data-id');
         $("#srn_"+i).html(k);  
         k++;           
      });
      // Reset all icon cells
      $(".goods_items").each(function () {
         let dataId = $(this).attr("data-id");
         $("#tr_" + dataId + " td:last").html('');
      });
      let totalRows = $(".goods_items").length;
      $(".goods_items").each(function (index) {
         let dataId = $(this).attr("data-id");
         let removeIcon = '<svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" tabindex="0" class="bi bi-file-minus-fill remove" data-id="' + dataId + '" viewBox="0 0 16 16">' + '<path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>' +
         '</svg>';
         let addIcon = '<svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"tabindex="0" class="bg-primary rounded-circle add_more_wrapper" data-id="' + dataId + '" >' + '<path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>' + '</svg>';
         if (dataId == "1") {
            // Clear the icon from the last <td> of the first row
            $("#tr_" + dataId + " td:last").html('');
         }else if (index < totalRows - 1) {
            $("#tr_" + dataId + " td:last").html(removeIcon);
         } else {
            $("#tr_" + dataId + " td:last").html(removeIcon + addIcon);
         }
      });
      $(".select2-single").select2();
   }
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
      function removeItem() {
   $(document).on("click", ".remove", function () {
      let id = $(this).attr("data-id");
      $("#tr_" + id).remove();

      // Re-index SRNs
      let k = 1;
      $(".goods_items").each(function () {
         let i = $(this).attr("data-id");
         $("#srn_" + i).html(k);
         k++;
      });

      // Update max counter
      let max_val = $("#max_sale_descrption").val();
      $("#max_sale_descrption").val(--max_val);

      let totalRows = $(".goods_items").length;

      // Loop through all remaining item rows to reassign icons
      $(".goods_items").each(function (index) {
         let rowId = $(this).attr("data-id");
         let $iconCell = $("#tr_" + rowId + " td:last");

         let removeIcon = `
         <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="${rowId}" viewBox="0 0 16 16">
            <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
         </svg>`;

         let addIcon = `
         <svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg"tabindex="0" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="bg-primary rounded-circle add_more_wrapper" data-id="${rowId}">
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
      function calculateAmount (key=null) {      
         customer_gstin = $('#party option:selected').attr('data-state_code');
         let bill_date = $("#date").val();
   
         if(customer_gstin==undefined){
            return;
         }       
         if(gstApplicable && customer_gstin==merchant_gstin.substring(0,2)){
            $("#billtr_cgst").show();
            $("#billtr_sgst").show();
            $("#bill_sundry_amount_igst").val('');
            $("#billtr_igst").hide();
            $("#tax_rate_tr_igst").val(0);
            $("#tax_amt_igst").html('');
         }else if(gstApplicable){
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
         
         if(!gstApplicable){
            $(".extra_gst").remove();

            $("#billtr_cgst").hide();
            $("#billtr_sgst").hide();
            $("#billtr_igst").hide();

            $("#bill_sundry_amount_cgst").val('');
            $("#bill_sundry_amount_sgst").val('');
            $("#bill_sundry_amount_igst").val('');

            $("#tax_rate_tr_cgst").val(0);
            $("#tax_rate_tr_sgst").val(0);
            $("#tax_rate_tr_igst").val(0);
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
            if(key=="A" || pageLoaded==1){
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
                     if(gstApplicable){
                        $("#bill_sundry_amount_cgst").val(taxSundryArray['cgst']);
                     }
                     //$("#bill_sundry_amount_cgst").prop('readonly',true);
                     $("#tax_amt_cgst").html(e.percent/2+" %");
                     $("#tax_rate_tr_cgst").val(e.percent/2);
                     //SGST
                     if(gstApplicable){
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
                     if(gstApplicable){
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
                     if(gstApplicable){
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
                     if(gstApplicable){
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
                     if(gstApplicable){
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
               $(".extra_gst").remove();
               let item_total_amount = 0;
               let bill_sundry_total = 0;
               
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
                     
                     if(gstApplicable){
                         console.log("0000kk");
                        $("#bill_sundry_amount_igst").val(taxSundryArray['igst']);
                     }
                     //$("#bill_sundry_amount_igst").prop('readonly',true);
                     $("#tax_amt_igst").html(e.percent+" %");
                     $("#tax_rate_tr_igst").val(e.percent); 
                     if(taxSundryArray['igst']=="" || taxSundryArray['igst']==undefined){
                        taxSundryArray['igst'] = 0;
                     }
                     //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['igst']);
                     if(gstApplicable){
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
                     if(gstApplicable){
                        $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['igst']);
                     }
                     
                     $("#bill_sundry_"+add_more_bill_sundry_up_count).val(sundry_value);
                     $("#tax_amt_"+add_more_bill_sundry_up_count).html(e.percent+" %");
                     $("#tax_rate_tr_"+add_more_bill_sundry_up_count).val(e.percent);
                     if(taxSundryArray['igst']=="" || taxSundryArray['igst']==undefined){
                        taxSundryArray['igst'] = 0;
                     }
                     //on_tcs_amount = parseFloat(on_tcs_amount) + parseFloat(taxSundryArray['igst']);
                     if(gstApplicable){
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
         if(!gstApplicable){
            gstamount = 0;
         }
         final_total = Math.round(final_total);
         var formattedNumber = final_total.toLocaleString('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2
         });
         $("#bill_sundry_amt").html(formattedNumber);
         $("#total_amounts").val(final_total);                 
         let roundoff = parseFloat(final_total) - parseFloat($("#total_taxable_amounts").val()) - parseFloat(gstamount)  - on_tcs_amount;
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
      $(document).ready(function() {
         let si = "";
         $('.add_sundry_btn_class').each(function(){  
            si = $(this).attr('data-id');
         });
         let last_index = si;
         $("#add_sundry_btn_id_"+last_index).html('<svg style="cursor:pointer;float:right" xmlns="http://www.w3.org/2000/svg" tabindex="0" class="bg-primary rounded-circle add_more_bill_sundry_up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"></path></svg>'); 
         $("#party").change();
         selected_series = "{{$purchase->series_no}}";
         $(".goods_items").each(function(){
            call_fun('tr_'+$(this).attr('data-id'));
         });
         
         
         
         calculateAmount();
         // Function to calculate amount and update total sum
         $("#series_no").change();
         // Calculate amount on input change
         $(document).on('input', '.price',function(){
            
            pageLoaded = 0;
            calculateAmount();
         });
         $(document).on('input', '.quantity',function(){
           
            pageLoaded = 0;
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
               $(".goods_items").each(function(){
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
         $("#date").change();
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
            //new code for parameter
            console.log();
            $('#unit_'+data).attr('data-parameterized_stock_status',$('#goods_discription_'+data+' option:selected').attr('data-parameterized_stock_status'));
            $('#unit_'+data).attr('data-group_id',$('#goods_discription_'+data+' option:selected').attr('data-group_id'));
            $('#unit_'+data).attr('data-config_status',$('#goods_discription_'+data+' option:selected').attr('data-config_status'));
           
            $('#config_status_'+data).val($('#goods_discription_'+data+' option:selected').attr('data-config_status'));
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
   $("#party").change(function(){
      let selected = $('option:selected', this);
      let partyId = $(this).val();

      if(partyId==""){
         $("#party-error").show();
         return;
      }

      $("#party-error").hide();

      // Reset GST state back to server defaults (we'll disable again below if required)
      gstApplicable = initialGstApplicable;
      gst_disabled_for_party = 0;

      let gstin = selected.attr('data-gstin');
      let address = selected.attr('data-address');
      let allowWithoutGst = selected.attr('data-allow_without_gst');
      let stateCode = selected.attr('data-state_code');
      let group = selected.data('group');

      if(partyGSTData[partyId]){
         gstin = partyGSTData[partyId].gstin;
         address = partyGSTData[partyId].address;
         stateCode = partyGSTData[partyId].state_code;
      }

      if(noGSTGroups.includes(group)){
         gst_disabled_for_party = 1;
         disableGSTCalculation();

         $("#bill_sundry_amount_cgst").val('');
         $("#bill_sundry_amount_sgst").val('');
         $("#bill_sundry_amount_igst").val('');
         $("#tax_amt_cgst").html('');
         $("#tax_amt_sgst").html('');
         $("#tax_amt_igst").html('');
      }else{
         if((!gstin || gstin.trim() === "") && allowWithoutGst != 1){
            let confirmBox = confirm(
               "This party is unauthorized.\n\nDo you want to continue without GST?"
            );

            if(confirmBox){
               gst_disabled_for_party = 1;
               disableGSTCalculation();

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
               // Open GST modal to enter GST details
               $("#gst_modal_account_id").val(selected.val());
               $("#gst_modal_account_name").text(selected.text().trim());

               $("#gstin").val('');
               $("#pan").val('');
               $("#address").val('');
               $("#pincode").val('');
               $("#state").val('').trigger('change');

               $("#gstAccountModal").modal('show');
               return;
            }
         }

         if(allowWithoutGst == 1 && (!gstin || gstin.trim() === "")){
            gst_disabled_for_party = 1;
            disableGSTCalculation();
         }
         console.log(gstin)
         if(gstin && gstin.trim() !== ""){
            gst_disabled_for_party = 0;
            gstApplicable = true;
         }
      }

      updatePurchaseType(stateCode);

      $("#partyaddress").html('');
      if(address){
         $("#partyaddress").html(
            "GSTIN : "+ (gstin ? gstin : "N/A") +
            "<br>Address : "+ address
         );
      }

      calculateAmount();
   });

   function disableGSTCalculation(){
      gstApplicable = false;

      // Hide GST rows
      $("#billtr_cgst").hide();
      $("#billtr_sgst").hide();
      $("#billtr_igst").hide();

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

   function updatePurchaseType(stateCode){
      if(!stateCode){
         $("#purchase_type").val('');
         return;
      }

      if(stateCode == (merchant_gstin || '').substring(0,2)){
         $("#purchase_type").val('LOCAL');
      }else{
         $("#purchase_type").val('CENTER');
      }
   }

   // GST modal Save/Cancel (same behavior as Add Purchase)
   $(document).on('click', '#saveGstModal', function () {
      let account_id = $('#gst_modal_account_id').val();
      let gstin = ($('#gstin').val() || '').trim();
      let address = ($('#address').val() || '').trim();
      let pincode = $('#pincode').val();

      if (!gstin || gstin.length !== 15) {
         alert("Please enter a valid GST number");
         return;
      }

      let pan = gstin.substring(2, 12);
      $("#pan").val(pan);

      let state_code = $('#state option:selected').data('state_code');
      let state_id = $('#state_hidden').val() || $('#state option:selected').val();
      let fullAddress = address + ',' + pincode;

      $.ajax({
         url: "{{ route('account.update.gst') }}",
         type: "POST",
         data: {
            _token: "{{ csrf_token() }}",
            account_id: account_id,
            gstin: gstin,
            state: state_id,
            address: address,
            pincode: pincode,
            pan: pan
         },
         success: function () {
            partyGSTData[account_id] = {
               gstin: gstin,
               address: fullAddress,
               state_code: state_code
            };

            let option = $('#party option[value="' + account_id + '"]');
            if (option.length) {
               option.attr('data-gstin', gstin);
               option.attr('data-address', fullAddress);
               option.attr('data-state_code', state_code);
               option.attr('data-allow_without_gst', 0);
            }

            $("#partyaddress").html(
               "GSTIN : " + gstin + "<br>Address : " + fullAddress
            );

            gst_disabled_for_party = 0;
            gstApplicable = initialGstApplicable;
            updatePurchaseType(state_code);
            calculateAmount();

            $("#gstAccountModal").modal('hide');
         }
      });
   });

   $(document).on('click', '#cancelGstModal', function () {
      $("#gstAccountModal").modal('hide');
   });

   $('#gstAccountModal').on('shown.bs.modal', function () {
      $('#state').select2({
         dropdownParent: $('#gstAccountModal'),
         width: '100%'
      });
      // Keep hidden state id in sync (used while saving GST)
      $('#state_hidden').val($('#state').val());
   });
   $('#state').on('change', function () {
      $('#state_hidden').val($(this).val());
   });

   // Auto-fill modal fields from GSTIN (same as Add Purchase)
   $("#gstin").on("change", function () {
      let gstin = ($(this).val() || '').trim();
      if (gstin === "") return;

      // Clear dependent fields; they'll be re-filled by check-gstin response.
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
               }

               $("#pan").val(gstin.substring(2, 12));
               $("#address").val((data.address || "").toUpperCase());
               $("#pincode").val(data.pinCode || "");
            } else {
               alert(data.message || "Invalid GST Number");
            }
         }
      });
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
      //getItemGstRate($(this).val(),id);
   });
   $("#series_no").change(function(){
      let series = $(this).val();      
      merchant_gstin = $('option:selected', this).attr('data-gst_no');
      $("#material_center").val($('option:selected', this).attr('data-mat_center'));
      calculateAmount();
      if($("#party").val()!=""){
         if($('#party option:selected').attr('data-state_code')==merchant_gstin.substring(0,2)){  
            $("#purchase_type").val('LOCAL');
         }else{
            $("#purchase_type").val('CENTER');
         }
      } 
   });
   $(".transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   });
   $(".save_transport_info").click(function(){
      $("#transport_info_modal").modal('toggle');
   });
   $(document).ready(function() {
      // Initialize Select2
      $('#party').select2({
         placeholder: "Select Account",
         allowClear: true,
         width: '100%'
      });
      // ✅ Set initial previous value if there's a selected value (on page load)
      const initialVal = $('#party').val();
      $('#party').data('previousValue', initialVal);
      // 🔁 On dropdown open, capture the current value
      $('#party').on('select2:open', function () {
         const currentValue = $(this).val();
         $(this).data('previousValue', currentValue);
      });
      // 🎯 Focus next field on selection
      $('#party').on('select2:select', function (e) {
         $('#material_center').focus();
      });
      // 🎯 Focus next field on unselect
      $('#party').on('select2:unselect', function (e) {
         $('#material_center').focus();
      });
      // 💡 On dropdown close, check if value changed or stayed the same
      $('#party').on('select2:close', function (e) {
         const selectedValue = $(this).val();
         const previousValue = $(this).data('previousValue');
         if (selectedValue === previousValue) {
            $('#material_center').focus();
         }
         // Update previous value
         $(this).data('previousValue', selectedValue);
      });
   });
   $(document).ready(function () {
      // Utility function to check if element is in viewport
      function isInViewport(element) {
         const rect = element[0].getBoundingClientRect();
         return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
         );
      }
      // Initialize Select2 for all relevant fields
      $('[id^="goods_discription_tr_"]').each(function () {
         $(this).select2({
            placeholder: "Select Item",
            allowClear: true,
            width: '100%'
         });
         // Store initial value as previousValue for comparison later
         const initialVal = $(this).val();
         $(this).data('previousValue', initialVal);
      });
      function focusQuantityField(num) {
         const input = $('#quantity_tr_' + num);
         if (!input.is(':focus') && !isInViewport(input)) {
            input[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
         }
         if (!input.is(':focus')) {
            input.focus();
         }
      }
      // On item selected
      $(document).on('select2:select', '[id^="goods_discription_tr_"]', function () {
         const currentId = $(this).attr('id');
         const match = currentId.match(/goods_discription_tr_(\d+)/);
         if (match) {
            focusQuantityField(match[1]);
         }
      });
      // On selection cleared
      $(document).on('select2:unselect', '[id^="goods_discription_tr_"]', function () {
         const currentId = $(this).attr('id');
         const match = currentId.match(/goods_discription_tr_(\d+)/);
         if (match) {
            focusQuantityField(match[1]);
         }
      });
      // On Select2 closed (handles re-selecting the same value)
      $(document).on('select2:close', '[id^="goods_discription_tr_"]', function () {
         const selectedValue = $(this).val();
         const previousValue = $(this).data('previousValue');
         if (selectedValue === previousValue) {
            const currentId = $(this).attr('id');
            const match = currentId.match(/goods_discription_tr_(\d+)/);
            if (match) {
               focusQuantityField(match[1]);
            }
         }
         // Update previous value
         $(this).data('previousValue', selectedValue);
      });
      // OPTIONAL: if you want to apply this logic to #party too:
      const partyInitial = $('#party').val();
      $('#party').data('previousValue', partyInitial);
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
      let lastRowId = $(".goods_items").last().data("id");
      if (event.key === "Tab" && !event.shiftKey) {
         event.preventDefault();
         if (id == lastRowId) {
            // Focus the add button on the last row
            $("#tr_" + id).find(".add_more_wrapper").focus();
         } else {
            // Focus the first focusable input/select in the next row
            let nextId = parseInt(id) + 1;
            let nextRowFirstInput = $("#tr_" + nextId).find("td").eq(1).find("select, input").first();
            if (nextRowFirstInput.length) {
               nextRowFirstInput.focus();
            }
         }
      }
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
      $(document).on('select2:close', '[id^="bill_sundry_"]', function(e){
         const selectedValue = $(this).val();
         const previousValue = $(this).data('previousValue');
         if (selectedValue === previousValue) {
            const currentId = $(this).attr('id');
            const match = currentId.match(/goods_items_(\d+)/);
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
      // When pressing Enter on purchaseBtn
      $("#purchaseBtn").on('keydown', function(event) {
         if (event.key === "Enter") {
            event.preventDefault(); // prevent default behavior
            $(this).click(); // trigger click event (which submits)
         }
      });
      var paremeter_table_add_more_data = "";
      let param_index = 0;
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
         let row_id = $(this).attr('data-row_id');
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
               group_id : group_id,
               action : 'edit_purchase',
               id : '{{$purchase->id}}',
               row_id : row_id
            },
            success: function(res){
               let data = res.parameters;
               let edit_data = [];
               if(res.edit_purchase_data.purchase_description.length>0 && res.edit_purchase_data.purchase_description[0].parameter_column_info.length>0){
                  edit_data = res.edit_purchase_data.purchase_description[0].parameter_column_info;
               }else{
                  edit_data.push({"parameter1_id":""});
               }
               if((data.parameterized_stock_status!=undefined && data.parameterized_stock_status==1) || data.parameterized_status!=undefined && data.parameterized_status==1){
                  let html = "<table class='table table-bordered'><thead>";
                  html+= "<tr>";
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
                  html+='</tr>';

                  html+='</thead><tbody>';
                  let param_id = "";
                  edit_data.forEach(function(ele,ele_index){
                     if(ele_index==0){
                        param_id = "param_index";
                     }else{
                        param_index++
                        param_id = param_index;
                     }
                     html+='<tr class="tr_param" data-id="'+param_id+'">';
                     paremeter_table_add_more_data+='<tr id="param_tr_'+param_id+'" class="tr_param" data-id="'+param_id+'">';
                     if(data.parameters.length>0){
                        let i = 1;
                        data.parameters.forEach(function(e){
                           if(e.parameter_type=="OPEN"){                              
                              let id_val = "";
                              if(e.alternative_unit=="1"){
                                 id_val = "alternative_unit_id_"+param_id;
                              }else if(e.conversion_factor=="1"){
                                 id_val = "conversion_factor_id_"+param_id;
                              }
                              let ele_value = "";
                              if(ele.parameter1_id==e.id){
                                 ele_value = ele.parameter1_value;
                              }else if(ele.parameter2_id==e.id){
                                 ele_value = ele.parameter2_value;
                              }else if(ele.parameter3_id==e.id){
                                 ele_value = ele.parameter3_value;
                              }else if(ele.parameter4_id==e.id){
                                 ele_value = ele.parameter4_value;
                              }else if(ele.parameter5_id==e.id){
                                 ele_value = ele.parameter5_value;
                              }
                              html+='<td><input id="'+id_val+'" value="'+ele_value+'" type="text" name="parameter_column_value_'+e.id+'" class="form-control param_col param_col_'+param_id+' parameter_column_value_'+e.id+'" style="height: 52px;" placeholder="'+e.paremeter_name+'" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+e.conversion_factor+'" data-id="'+param_id+'" data-parameter_id="'+e.id+'"></td>';
                              paremeter_table_add_more_data+='<td><input id="'+id_val+'" type="text" name="parameter_column_value_'+e.id+'" class="form-control param_col param_col_'+param_id+' parameter_column_value_'+e.id+'" style="height: 52px;" placeholder="'+e.paremeter_name+'" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+e.conversion_factor+'" data-id="'+param_id+'" data-parameter_id="'+e.id+'"></td>';
                           }else{
                              let predefined_list = "";
                              if(e.predefined_value.length>0){
                                 predefined_list+='<select class="form-control param_col_'+param_id+' name="parameter_column_value_'+e.id+'" parameter_column_value_'+e.id+'" style="height: 52px;" data-alternative_unit="'+e.alternative_unit+'" data-alternative_qty="'+data.alternative_qty+'" data-conversion_factor="'+data.conversion_factor+'" data-id="'+param_id+'"><option value="">Select</option>';
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
                        html+='<td><input type="text" class="form-control parameter_column_value_QTY_COL" name="parameter_column_value_QTY_COL" id="parameter_column_value_qty_'+param_id+'" style="height: 52px;" placeholder="QTY" value="" data-id="'+param_id+'" data-alternative_qty="'+data.alternative_qty+'"></td>';
                        paremeter_table_add_more_data+='<td><input type="text" class="form-control parameter_column_value_QTY_COL" name="parameter_column_value_QTY_COL" id="parameter_column_value_qty_'+param_id+'" style="height: 52px;" placeholder="QTY" data-id="'+param_id+'" data-alternative_qty="'+data.alternative_qty+'"></td>';
                        html+='<td></td>';
                        paremeter_table_add_more_data+='<td></td>';
                     }
                     html+='</tr>';
                  });
                  paremeter_table_add_more_data+='<tr>';
                  html+='<tr class="parameters_table" style="display:none"><td colspan="3"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_new_row" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></td></tr>';
                  html+='</tbody></table>';
                  $(".parameter_body").html(html);
                  $("#parameter_modal_id").val(id);
                  $("#parameter_modal").modal('toggle');
                  $(".param_col").each(function() {
                     $(this).trigger("keyup");
                  });
               }
            }
         });
      });
      
      $(document).on('click','.add_new_row',function(){
         param_index++;
         let res = paremeter_table_add_more_data.replace(/\param_index/g,param_index);
         $(".parameters_table").before(res);
      });
      $(document).on('click','.remove_add_row',function(){      
         let id = $(this).attr('data-id');
         $("#param_tr_"+id).remove();
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
   });
   $(document).on('click','.parameter_save_btn',function(){
      let data_arr = [];
      $(".tr_param").each(function(){
         let id = $(this).attr('data-id');
         let data_value_arr = [];
         $(".param_col_"+id).each(function(){
            data_value_arr.push({'id':$(this).attr('data-parameter_id'),'value':$(this).val(),'alternative_unit':$(this).attr('data-alternative_unit'),'alternative_qty':$(this).attr('data-alternative_qty')})
         });
         data_arr.push(data_value_arr);
      });
      parameter_assign_item_arr.push($("#parameter_modal_id").val());      
      $("#item_parameters_tr_"+$("#parameter_modal_id").val()).val(JSON.stringify(data_arr));
      $("#quantity_tr_"+$("#parameter_modal_id").val()).attr('readonly',true);
      $("#parameter_modal").modal('toggle');
   });



     $(document).ready(function() {
    let isDuplicateVoucher = false;

    function checkDuplicateVoucher(callback = null) {
      return true; 
        let voucher_no = $('#voucher_no').val();
        let party_id = $('#party').val();
        let financial_year = '{{ Session::get("default_fy") }}';
        let purchase_id = '{{ $purchase->id ?? "" }}'; // pass id in edit mode

        if(voucher_no !== '' && party_id !== '') {
            $.ajax({
               
                url: '',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    voucher_no: voucher_no,
                    party_id: party_id,
                    financial_year: financial_year,
                    purchase_id: purchase_id // send current purchase id
                },
                success: function(response) {
                    if(response.exists) {
                        alert('Voucher number "' + voucher_no + '" already exists for this party in this financial year.');
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

    // Run on change
    $('#voucher_no, #party').on('change', function() {
        checkDuplicateVoucher();
    });

    // Also run once on page load (for edit mode check)
    checkDuplicateVoucher();
});


$('#date').on('change', function () {
    var bill_date = $(this).val();
    var group_id  = "{{ $groupId }}"; // pass if needed
    return;

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
            $('.goods_items').each(function ()  {
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

$(document).on('select2:open', function () {

  let select = $('.select2-container--open').prev('select');

  // ✅ STORE ROW ID IF ITEM DROPDOWN
  if (select.hasClass('goods_items')) {
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

    let partySelect = document.getElementById('party');

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
    let itemSelect = $('#goods_discription_tr_' + activeItemRowId);

    // 🔥 CREATE OPTION
    let option = document.createElement("option");

      option.value = res.item.id;
      option.text  = res.item.name;
      option.selected = true;

      // 🔥 REQUIRED DATA ATTRIBUTES
      option.setAttribute('data-val', res.item.unit);          // UNIT TEXT
      option.setAttribute('unit_id', res.item.u_name);    // UNIT ID
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
            let $select = $("#goods_discription_tr_" + index);
            $select.find(':selected').attr('data-percent', res.gst_rate);
            calculateAmount();
         }
      }
   });
}
$("#date").on("change", function(){
   $(".goods_items").each(function(){
      let item_id = $(this).val();
      let index = $(this).data("id");
      if(item_id){
         //getItemGstRate(item_id,index);
      }
   });   
});
</script>
@endsection