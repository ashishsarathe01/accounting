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
<?php
$item_list = '<option value="">Select</option>';
foreach($manageitems as $value) {
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
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Sales Voucher</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale.update') }}" id="saleForm">
               @csrf
               <div class="row">
                  <input type="hidden" name="sale_edit_id" value="{{$sale->id}}">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading" >Series No.</label>
                     <select id="series_no" name="series_no" class="form-select"autofocus required>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { 
                              if($value->series==$sale->series_no){
                                 ?>
                                 <option value="<?php echo $value->series; ?>" <?php if($value->series==$sale->series_no){ echo "selected";} ?> data-mat_center="<?php echo $value->mat_center;?>" data-gst_no="<?php echo $value->gst_no;?>" data-invoice_start_from="<?php echo $value->invoice_start_from;?>" data-invoice_prefix="<?php echo $value->invoice_prefix;?>"><?php echo $value->series; ?></option>
                                 <?php
                              }
                           }
                        } ?>
                     </select>
                     <ul style="color: red;">
                       @error('series_no'){{$message}}@enderror                        
                     </ul> 
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" value="{{$sale->date}}" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                     <ul style="color: red;">
                       @error('date'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" value="{{$sale->voucher_no_prefix}}" readonly style="text-align: right;">
                     <input type="hidden" class="form-control" name="voucher_no" id="voucher_no" placeholder="" value="{{$sale->voucher_no}}"/>
                     <ul style="color: red;">
                       @error('voucher_no'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">SALE TYPE</label>
                     <input type="text" class="form-control" id="sale_type" name="sale_type" placeholder="SALE TYPE" readonly>
                  </div>
                  <div class="mb-4 col-md-5">
                     <label for="name" class="form-label font-14 font-heading">Party</label>
                     <select class="form-select select2-single" id="party" name="party" required>
                        <option value="">Select</option>
                        <?php
                        foreach ($party_list as $value) { ?>
                           <option value="<?php echo $value->id; ?>" data-gstin="<?php echo $value->gstin; ?>" data-address="<?php echo $value->address.",".$value->pin_code; ?>" data-state_code="<?php echo $value->state_code; ?>" data-other_address='<?php echo $value->otherAddress; ?>' <?php if($value->id==$sale->party){ echo "selected";}?>><?php echo $value->account_name; ?></option>
                           <?php 
                        } ?>
                     </select>
                     <p id="partyaddress" style="font-size: 9px;"></p>
                     <ul style="color: red;">
                       @error('party'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-4 col-md-5 address_div" style="display: none;">
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
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { 
                              if($value->mat_center==$sale->material_center){
                              ?>
                              <option value="<?php echo $value->mat_center; ?>" <?php if($value->mat_center==$sale->material_center){ echo "selected";}?>><?php echo $value->mat_center; ?></option>
                              <?php 
                              }
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
                        @php $i=1; $total = 0; $itemcount = count($SaleDescription);@endphp
                        @foreach($SaleDescription as $item)
                           <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                              <td class="w-min-50" id="srn_{{$i}}">{{$i}}</td>
                              <td class="w-min-50">
                                 <select onchange="call_fun('tr_{{$i}}');" class="form-control border-0 goods_items select2-single" id="goods_discription_tr_{{$i}}" name="goods_discription[]" required data-id="{{$i}}">
                                    <option value="">Select</option>
                                    <?php
                                    foreach($manageitems as $value) { ?>
                                       <option unit_id="<?php echo $value->u_name; ?>" data-val="<?php echo $value->unit; ?>" data-percent="<?php echo $value->gst_rate; ?>" value="<?php echo $value->id; ?>" <?php if($value->id==$item->goods_discription){ echo "selected";} ?>><?php echo $value->name; ?></option>
                                       <?php 
                                    } ?>
                                 </select>
                              </td>                              
                              <td class="w-min-50">
                                 <input type="number" class="quantity w-100 form-control" id="quantity_tr_{{$i}}" name="qty[]" value="{{$item->qty}}"data-id="{{$i}}"  placeholder="Quantity"  style="text-align:right;" >
                              </td>
                              <td class="w-min-50">
                                 <input type="text" class="w-100 form-control" id="unit_tr_{{$i}}" readonly style="text-align:center;"data-id="{{$i}}"  value="{{$item->s_name}}" />
                                 <input type="hidden" class="units w-100" name="units[]" id="units_tr_{{$i}}" data-id="{{$i}}"  value="{{$item->unit}}">
                              </td>
                              <td class="w-min-50">
                                 <input type="number" class="price form-control" id="price_tr_{{$i}}" name="price[]" value="{{$item->price}}"data-id="{{$i}}"  placeholder="Price"  style="text-align:right;">
                              </td>
                              <td class="">
                                 <input type="number" id="amount_tr_{{$i}}" class="amount w-100 form-control" name="amount[]" value="{{$item->amount}}" placeholder="Amount" data-id="{{$i}}"  style="text-align:right;">
                              </td>
                              <td class="" style="display:flex">
    {{-- Show remove icon for all rows except the first --}}
    @if($i != "1")
        <svg style="color: red; cursor: pointer; margin-left: 10px;" 
        tabindex="0"
             xmlns="http://www.w3.org/2000/svg" 
             width="24" height="24" 
             fill="currentColor" 
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

                           </tr>
                           @php $i++; $total = $total + $item->amount; @endphp
                        @endforeach
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="" colspan="7">
                              <a class="">
                                 
                              </a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold"></td>
                           <td class="w-min-50 fw-bold">Total</td>
                           <td class="w-min-50 fw-bold">
                              <span id="totalSum" style="float: right;">{{$total}}</span>
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
                              @php $index = 1; $count_sundry = 0;
                              @endphp                           
                              @foreach($SaleSundry as $sundry)
                                 @if($sundry->nature_of_sundry!='CGST' && $sundry->nature_of_sundry!='SGST' && $sundry->nature_of_sundry!='IGST' && $sundry->nature_of_sundry!='ROUNDED OFF (+)' && $sundry->nature_of_sundry!='ROUNDED OFF (-)')

                                 @php $count_sundry++ @endphp 
                                    <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                       <td class="w-min-50">
                                          <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
                                             <option value="">Select</option>
                                             <?php
                                             foreach ($billsundry as $value) { 
                                                if($value->nature_of_sundry!='CGST' && $value->nature_of_sundry!='SGST' && $value->nature_of_sundry!='IGST' && $value->nature_of_sundry!='ROUNDED OFF (+)' && $value->nature_of_sundry!='ROUNDED OFF (-)'){?>
                                                   <option value="<?php echo $value->id;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_@php echo $index;@endphp" id="sundry_option_<?php echo $value->id;?>_1" <?php if($sundry->bill_sundry==$value->id){ echo "selected";} ?> data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>
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
                                       
                                       <td >
                                       @if($index != "1")
                                       <svg style="color: red; cursor: pointer; margin-left: 10px;" 
             xmlns="http://www.w3.org/2000/svg" 
             tabindex="0"
             width="24" height="24" 
             fill="currentColor" 
             class="bi bi-file-minus-fill remove_sundry_up" 
             data-id="@php echo $index;@endphp" 
             viewBox="0 0 16 16">
            <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
        </svg>
    @endif


    <!-- {{-- Show add icon only on the last row --}}
    @if($index == $count_sundry) -->
        <!-- <span class="add_btn_class" id="add_btn_id_{{ $i }}">
            <svg xmlns="http://www.w3.org/2000/svg" 
            tabindex="0"
            data-id="@php echo $index;@endphp"
                 class="bg-primary rounded-circle add_more_bill_sundry_up" 
                 width="24" height="24" 
                 viewBox="0 0 24 24" 
                 fill="none" 
                 style="cursor: pointer;">
                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
            </svg>
        </span>  -->
    <!-- @endif -->
                                          <!-- <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="@php echo $index;@endphp" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg> -->

                                          <span class="add_sundry_btn_class"  id="add_sundry_btn_id_@php echo $index;@endphp" data-id="@php echo $index;@endphp">
                                          </span>
                                       </td>
                                    </tr>
                                    @php $index++;@endphp
                                 @endif
                              @endforeach  
                              @php 
                                 if($count_sundry==0){@endphp
                                    <tr class="font-14 font-heading bg-white bill_taxes_row">
                                       <td class="w-min-50" colspan="4">
                                          <span class="add_sundry_btn_class" id="add_sundry_btn_id_default" data-id="default">
                                             <svg style="cursor:pointer;float:right;" xmlns="http://www.w3.org/2000/svg" tabindex="0" class="bg-primary rounded-circle add_more_bill_sundry_up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"></path></svg>
                                          </span>
                                       </td>                                    
                                    </tr>
                                    @php 
                                 }
                              @endphp 
                              <?php 

                              $return = array();$roundReturn = array();
                              foreach($SaleSundry as $val) {
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
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_cgst">@if(isset($return['CGST'])){{$return['CGST'][0]['rate']}} %@endif</span>
                                    <input type="hidden" name="tax_rate[]" value="@if(isset($return['CGST'])){{$return['CGST'][0]['rate']}}@endif" id="tax_rate_tr_cgst"></td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_cgst" data-id="cgst" <?php if(isset($return['CGST'])){?> value="<?php echo $return['CGST'][0]['amount'];?>" <?php } ?> style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_sgst" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($return['SGST'])){?> style="display:none" <?php } ?>>
                                 <td class="w-min-50">
                                    <select id="bill_sundry_sgst" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="sgst">
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
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_igst">@if(isset($return['IGST'])){{$return['IGST'][0]['rate']}} %@endif</span>
                                    <input type="hidden" name="tax_rate[]" value="@if(isset($return['IGST'])){{$return['IGST'][0]['rate']}}@endif" id="tax_rate_tr_igst"></td>
                                 <td class="w-min-50 "><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_igst" data-id="igst" <?php if(isset($return['IGST'])){?> value="<?php echo $return['IGST'][0]['amount'];?>" <?php } ?> style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <?php 
                              foreach($return as $key=>$value) {
                                 foreach ($value as $k1 => $v1) {
                                    if(!in_array($v1['id'],$saleSundryArr)){ ?>
                                       <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr extra_gst">
                                          <td class="w-min-50">
                                             <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
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
                              
                              <!-- <div class="plus-icon">
                                 <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120 " colspan="5">
                                       <a class="add_more_bill_sundry_down"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                       <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
                                    </td>
                                 </tr>
                              </div> -->
                              <tr id="billtr_round_plus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($roundReturn["ROUNDED OFF (+)"])){?> style="display:none" <?php } ?>>
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
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_plus" data-id="round_plus" <?php if(isset($roundReturn["ROUNDED OFF (+)"])){?> value="<?php echo $roundReturn["ROUNDED OFF (+)"][0]['amount'];?>" <?php } ?> style="text-align:right;"></td>
                                 <td></td>
                              </tr>
                              <tr id="billtr_round_minus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php if(!isset($roundReturn["ROUNDED OFF (-)"])){?> style="display:none" <?php }?>>
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
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_minus" data-id="round_minus" <?php if(isset($roundReturn["ROUNDED OFF (-)"])){?> value="<?php echo $roundReturn["ROUNDED OFF (-)"][0]['amount'];?>" <?php } ?> style="text-align:right;">
                                 </td>
                                 <td></td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td class="w-min-50 fw-bold">Total</td>
                                 <td class="w-min-50 fw-bold"></td>
                                 <td class="w-min-50 fw-bold">
                                    <span id="bill_sundry_amt" style="float:right ;">{{$sale->total}}</span>
                                    <input type="hidden" name="total" id="total_amounts" value="{{$sale->total}}">
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
                                             <input type="text" name="vehicle_no" class="form-control" placeholder="Vehicle No." value="{{$sale->vehicle_no}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Transport Name</label>
                                             <input type="text" id="transport_name" name="transport_name" class="form-control" placeholder="Transport Name" value="{{$sale->transport_name}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Reverse Charge</label>
                                             <select class="w-95-parsent form-select" id="reverse_charge" id="reverse_charge" name="reverse_charge">
                                                <option value="">Select</option>
                                                <option value="Yes" <?php if($sale->reverse_charge=="Yes"){ echo "selected";}?>>Yes</option>
                                                <option value="No" <?php if($sale->reverse_charge=="No"){ echo "selected";}?>>No</option>
                                             </select>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">GR/RR No.</label>
                                             <input type="text" id="gr_pr_no" name="gr_pr_no" class="form-control" placeholder="GR/RR No" value="{{$sale->gr_pr_no}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Station</label>
                                             <input type="text" id="station" name="station" class="form-control" placeholder="Station" value="{{$sale->station}}"/>
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
                                                   <option value="<?php echo $value->id; ?>" <?php if($value->id==$sale->shipping_name){ echo "selected";}?>><?php echo $value->account_name; ?></option>
                                                   <?php 
                                                } ?>
                                             </select>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping Address</label>
                                             <input type="text" id="shipping_address" name="shipping_address" style="width: 95%;" class="form-control" placeholder="Shipping Address" value="{{$sale->shipping_address}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping Pincode</label>
                                             <input type="text" id="shipping_pincode" name="shipping_pincode" class="form-control" placeholder="Shipping Pincode" value="{{$sale->shipping_pincode}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping GST</label>
                                             <input type="text" id="shipping_gst" name="shipping_gst" class="form-control" placeholder="Shipping GST" value="{{$sale->shipping_gst}}"/>
                                          </div>
                                          <div class="mb-6 col-md-6">
                                             <label for="name" class="form-label font-14 font-heading">Shipping PAN</label>
                                             <input type="text" id="shipping_pan" name="shipping_pan" class="form-control" placeholder="Shipping PAN" value="{{$sale->shipping_pan}}"/>
                                          <input type="hidden" name="shipping_state" value="{{$sale->shipping_state}}"/>
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
</body>
@include('layouts.footer')
<script>
   var selected_series = "";
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
   var customer_gstin = "";
   var merchant_gstin = "";
   var percent_arr = [];
   var add_more_count = '<?php echo --$i;?>';
   var add_more_bill_sundry_up_count = '<?php echo --$index;?>';
   var address_id = "{{$sale->address_id}}";
   var page_load = 0;
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
      newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50" id="srn_'+add_more_count+'">' + srn + '</td><td class=""><select onchange="call_fun(\'tr_' + add_more_count + '\');" id="goods_discription_tr_' + add_more_count + '" class="border-0 w-95-parsent  goods_items" name="goods_discription[]" required data-id="'+add_more_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50"><input type="number" data-id="'+add_more_count+'" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '"/ style="text-align:right"></td><td class=" w-min-50"><input type="text" class="w-100 form-control"data-id="'+add_more_count+'" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class=" w-min-50"><input type="number" class="price w-100 form-control" data-id="'+add_more_count+'" name="price[]" id="price_tr_' + add_more_count + '" style="text-align:right"/></td><td class=" w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]"data-id="'+ add_more_count +'" id="amount_tr_' + add_more_count + '"  style="text-align:right"/></td><td class="w-min-50" style="display:flex"></td></tr>';
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
      let removeIcon = '<svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" tabindex="0" class="bi bi-file-minus-fill remove" data-id="' + dataId + '" viewBox="0 0 16 16">' +
         '<path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>' +
         '</svg>';

      let addIcon = '<svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"tabindex="0" class="bg-primary rounded-circle add_more_wrapper" data-id="' + dataId + '" >' +
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
      // $("#add_btn_id_"+add_more_count).html('<svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>');

   };
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
         ?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" class="sundry_option_'+add_more_bill_sundry_up_count+'" id="sundry_option_<?php echo $value->id;?>_'+add_more_bill_sundry_up_count+'" data-sequence="<?php echo $value->sequence;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-sundry_percent_date="<?php echo $value->sundry_percent_date;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>"><?php echo $value->name; ?></option>';
            <?php 
         
      } ?>
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100 form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" style="text-align:right;"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
      newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr extra_gst"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100 form-select" id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
      newRow += optionElements;
      newRow += '</select></td><td class="w-min-50 "><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50 "><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" style="text-align:right;"></td><td class="w-min-50"></td></tr>';
      $curRow.before(newRow);
   });
   function calculateAmount (key=null) {
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
                  //$(".add_more_bill_sundry_gst").click();
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
                  $(".add_more_bill_sundry_gst").click();
                  $("#bill_sundry_amount_"+add_more_bill_sundry_up_count).val(taxSundryArray['igst']);
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
   $(document).ready(function() {
      let si = "";
      $('.add_sundry_btn_class').each(function(){  
         si = $(this).attr('data-id');
      });
      let last_index = si;
      $("#add_sundry_btn_id_"+last_index).html('<svg style="cursor:pointer;float:right" xmlns="http://www.w3.org/2000/svg" tabindex="0" class="bg-primary rounded-circle add_more_bill_sundry_up" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"></path></svg>'); 
      $("#party").change();
      selected_series = "{{$sale->series_no}}";
      calculateAmount();
      // Function to calculate amount and update total sum
      $("#series_no").change();
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
         $('#goods_discription_' + data + '').val("");
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
   $( ".select2-single, .select2-multiple" ).select2(  );
   $("#party").change(function(){
      if($('option:selected', this).attr('data-state_code')==merchant_gstin.substring(0,2)){  
         $("#sale_type").val('LOCAL');
      }else{
         $("#sale_type").val('CENTER');
      }
      $("#partyaddress").html('');
      if($(this).val()==""){
         $("#party-error").show()
         return;
      }
      $("#party-error").hide()
      $("#partyaddress").html("GSTIN : "+$('option:selected', this).attr('data-gstin')+"<br>Address : "+$('option:selected', this).attr('data-address'));
      let other_address = JSON.parse($('option:selected', this).attr('data-other_address'));
      $(".address_div").hide();
      $("#address").html('');
      if(other_address!=null && other_address.length>0){
         address_html = "<option value=''>Select Other Address</option>";
         let selecte_status = 0;
         other_address.forEach(function(e){
            let selected = "";
            if(address_id==e.id){
               selected = "selected";
               selecte_status = 1;
               $("#partyaddress").html("GSTIN : "+$("#party  option:selected").attr('data-gstin')+"<br>Address : "+e.address+","+e.pincode);
            }
            address_html += "<option value='"+e.id+"' "+selected+" data-address='"+e.address+"' data-pincode='"+e.pincode+"'>"+e.address+" ("+e.pincode+")</option>";
         });
         if(address_id!="" && selecte_status==0){
            address_html += "<option value='"+address_id+"' selected data-address='{{$sale->billing_address}}' data-pincode='{{$sale->billing_pincode}}'>{{$sale->billing_address}}</option>";
            $("#partyaddress").html("GSTIN : "+$("#party  option:selected").attr('data-gstin')+"<br>Address : {{$sale->billing_address}}");
         }
         $("#address").html(address_html);
         $(".address_div").show();
      }
      calculateAmount();
   });
   $("#address").change(function(){
      if($(this).val()!=""){
         let address = $('option:selected', this).attr('data-address');
         let pincode = $('option:selected', this).attr('data-pincode');
         $("#partyaddress").html("GSTIN : "+$("#party  option:selected").attr('data-gstin')+"<br>Address : "+address+","+pincode);
      }else{
         $("#partyaddress").html("GSTIN : "+$("#party  option:selected").attr('data-gstin')+"<br>Address : "+$('option:selected', '#party').attr('data-address'));
      }
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
   $("#series_no").change(function(){
      let series = $(this).val();      
      merchant_gstin = $('option:selected', this).attr('data-gst_no');
      let invoice_prefix = $('option:selected', this).attr('data-invoice_prefix');
      if(selected_series!=series){
         if(invoice_prefix!=""){
            //$("#voucher_prefix").val(invoice_prefix+"/{{Session::get('default_fy')}}/"+$('option:selected', this).attr('data-invoice_start_from'));
         }else{
            //$("#voucher_prefix").val($('option:selected', this).attr('data-invoice_start_from'));
         }
         //$("#voucher_no").val($('option:selected', this).attr('data-invoice_start_from'));
      }else{
         //$("#voucher_prefix").val('{{$sale->voucher_no_prefix}}{{$sale->voucher_no}}');
         //$("#voucher_no").val('{{$sale->voucher_no}}');
      }
      $("#material_center").val($('option:selected', this).attr('data-mat_center'));
      calculateAmount();
      if($("#party").val()!=""){
         
         if($('#party option:selected').attr('data-state_code')==merchant_gstin.substring(0,2)){  
            $("#sale_type").val('LOCAL');
         }else{
            $("#sale_type").val('CENTER');
         }
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
  $('#party').select2({
    placeholder: "Select Account",
    allowClear: true,
    width: '100%' // Ensure dropdown matches Bootstrap styling
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


// $(document).ready(function() {
//   // Initialize Select2 for all goods_items_# fields
//   $('[id^="goods_discription_tr_"]').each(function() {
//     $(this).select2({
//       placeholder: "Select Item",
//       allowClear: true,
//       width: '100%'
//     });
//   });

//   // When an item is selected
//   $(document).on('select2:select', '[id^="goods_discription_tr_"]', function(e) {
//     const currentId = $(this).attr('id');
//     const match = currentId.match(/goods_discription_tr_(\d+)/);
//     if (match) {
//       const num = match[1];
//       $('#quantity_tr_' + num).focus();
//     }
//   });

//   // When selection is cleared
//   $(document).on('select2:unselect', '[id^="goods_discription_tr_"]', function(e) {
//     const currentId = $(this).attr('id');
//     const match = currentId.match(/goods_discription_tr_(\d+)/);
//     if (match) {
//       const num = match[1];
//       $('#quantity_tr_' + num).focus();
//     }
//   });

//   // Handle re-selecting the same value
//   $(document).on('select2:close', '[id^="goods_discription_tr_"]', function(e) {
//     const selectedValue = $(this).val();
//     const previousValue = $(this).data('previousValue');
//     if (selectedValue === previousValue) {
//       const currentId = $(this).attr('id');
//       const match = currentId.match(/goods_discription_tr_(\d+)/);
//       if (match) {
//         const num = match[1];
//         $('#quantity_tr_' + num).focus();
//       }
//     }
//     // Update previous value
//     $(this).data('previousValue', selectedValue);
//   });
// });
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
  $(document).on('select2:close', '[id^="bill_sundry_"]', function(e) {
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
</script>
@endsection