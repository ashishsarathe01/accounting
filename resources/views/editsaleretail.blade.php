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
$item_list = '<option value="">Select Item</option>';
foreach($manageitems as $value) {
    $item_list .= '<option value="'.$value->id.'"
        data-unit_id="'.$value->u_name.'"
        data-val="'.$value->unit.'"
        data-percent="'.$value->gst_rate.'"
        data-itemid="'.$value->id.'"
        data-available_item="'.$value->available_item.'"
        data-parameterized_stock_status="'.$value->parameterized_stock_status.'"
        data-config_status="'.$value->config_status.'"
        data-group_id="'.$value->group_id.'">
            '.$value->name.'
        </option>';
}
?>
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
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Sales Voucher</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale.update') }}" id="saleForm">
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
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" value="{{$sale->voucher_no_prefix}}"  style="text-align: right;" readonly>
                     <input type="hidden" class="form-control" name="voucher_no" id="voucher_no" placeholder="" value="{{$sale->voucher_no}}"/>
                     <ul style="color: red;">
                       @error('voucher_no'){{$message}}@enderror                        
                     </ul>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">SALE TYPE</label>
                     <input type="text" class="form-control" id="sale_type" name="sale_type" placeholder="SALE TYPE" readonly>
                  </div>
                  @if(isset($config) && $config->purchase_order_status == 1)
                    <div class="mb-3 col-md-3">
                       <label class="form-label font-14 font-heading">Purchase Order No</label>
                       <input type="text"
                          name="po_no"
                          class="form-control"
                          value="{{ $sale->po_no ?? '' }}"
                          placeholder="Enter PO Number">
                    </div>
                    
                    <div class="mb-3 col-md-3">
                       <label class="form-label font-14 font-heading">Purchase Order Date</label>
                       <input type="date"
                          name="po_date"
                          class="form-control"
                          value="{{ $sale->po_date ?? '' }}">
                    </div>
                @endif
                  <div class="mb-4 col-md-5">
                     <label for="name" class="form-label font-14 font-heading">Party</label>
                     <select class="form-select select2-single" id="party" name="party" required data-modal="accountModal">
                        <option value="">Select</option>
                        <?php
                        foreach ($party_list as $value) { ?>
                           <option value="<?php echo $value->id; ?>" data-gstin="<?php echo $value->gstin; ?>" data-address="<?php echo $value->address.",".$value->pin_code; ?>" data-state_code="<?php echo $value->state_code; ?>" data-other_address='<?php echo $value->otherAddress; ?>' <?php if(!empty($bill_to_id)){ if($value->id==$bill_to_id){ echo "selected";} }else{ if($value->id==$sale->party){ echo "selected";} } ?> data-under_group="{{$value->under_group}}"><?php echo $value->account_name; ?></option>
                           <?php 
                        } ?>
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
                           <th class="w-min-50 border-none bg-light-pink text-body" style="width: 36%;">
                              @if($config && $config->show_description == 1)
                                 Description of Goods + Description
                              @else
                                 Description of Goods
                              @endif
                           </th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Qty</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: center;">Unit</th>
                           <th>Price (With GST)</th>
                            <th>Profit</th>

                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Amount</th>
                           <th class="w-min-50 border-none bg-light-pink text-body "></th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $i=1; $total = 0; $itemcount = count($SaleDescription);@endphp
                        @if(count($sale_order_items)>0)
                           @foreach ($sale_order_items as $sale_order_item)
                              <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                                 <td class="w-min-50" id="srn_{{$i}}">{{$i}}</td>
                                 <td class="w-min-50">
                                    <select onchange="call_fun('{{$i}}');" class="form-control border-0 goods_items select2-single" id="goods_discription_tr_{{$i}}" name="goods_discription[]" required data-id="{{$i}}" data-modal="itemModal">
                                       <option value="">Select</option>
                                       <?php
                                       foreach($manageitems as $value) { ?>
                                          <option data-unit_id="<?php echo $value->u_name; ?>" data-itemid="<?php echo $value->id; ?>" data-val="<?php echo $value->unit; ?>" data-percent="<?php echo $value->gst_rate; ?>" value="<?php echo $value->id; ?>" <?php if($value->id==$sale_order_item['item_id']){ echo "selected";} ?>><?php echo $value->name; ?></option>
                                          <?php 
                                       } ?>
                                    </select>
                                    @if($config && $config->show_description == 1)
                                    <div class="description-wrapper mt-1" data-row="{{ $i-1 }}">
                                       <div class="d-flex mb-1">
                                          <input type="text"
                                             name="description_lines[{{ $i-1 }}][]"
                                             class="form-control description-input"
                                             placeholder="Enter description line">

                                       </div>
                                    </div>
                                    @endif
                                 </td>                              
                                 <td class="w-min-50">
                                    <input type="number" class="quantity w-100 form-control" id="quantity_tr_{{$i}}" name="qty[]" value="{{$sale_order_item['total_weight']}}"data-id="{{$i}}"  placeholder="Quantity"  style="text-align:right;" >
                                 </td>
                                 <td class="w-min-50">
                                    <input type="text" class="w-100 form-control" id="unit_tr_{{$i}}" readonly style="text-align:center;"data-id="{{$i}}"  value="" />
                                    <input type="hidden" class="units w-100" name="units[]" id="units_tr_{{$i}}" data-id="{{$i}}"  value="">
                                 </td>
                                 <td class="w-min-50">
                                    <input type="number" class="price form-control" id="price_tr_{{$i}}" name="price[]" value="{{$sale_order_item['price']}}"data-id="{{$i}}"  placeholder="Price"  style="text-align:right;" data-price="{{$sale_order_item['price']}}">
                                 </td>
                                 <td class="">
                                    <input type="number" id="amount_tr_{{$i}}" class="amount w-100 form-control" name="amount[]" value="" placeholder="Amount" data-id="{{$i}}"  style="text-align:right;">
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
                              @php $i++;  @endphp
                           @endforeach
                        @else
                           @foreach($SaleDescription as $item)
                              <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                                 <td class="w-min-50" id="srn_{{$i}}">{{$i}}</td>
                                 <td class="w-min-50">
                                    <div class="d-flex align-items-center gap-2">
                                       <select class="form-control goods_items select2-single"
                                             name="goods_discription[]"
                                             id="goods_discription_tr_{{$i}}"
                                             data-id="{{$i}}"
                                             onchange="call_fun({{$i}}); handleItemChange({{$i}});"
                                             required data-modal="itemModal"> 

                                          <option value="">Select Item</option>
                                          @foreach($manageitems as $value)
                                             <option value="{{ $value->id }}"
                                                      data-unit_id="{{ $value->u_name }}"
                                                      data-val="{{ $value->unit }}"
                                                      data-percent="{{ $value->gst_rate }}"
                                                      data-itemid="{{ $value->id }}"
                                                      data-available_item="{{ $value->available_item }}"
                                                      data-parameterized_stock_status="{{ $value->parameterized_stock_status }}"
                                                      data-config_status="{{ $value->config_status }}"
                                                      data-group_id="{{ $value->group_id }}"
                                                      @if($value->id == $item->goods_discription) selected @endif>
                                                   {{ $value->name }}
                                             </option>
                                          @endforeach
                                       </select>
                                       <button type="button"class="btn btn-outline-secondary p-1 px-2 editItemDetailsBtn"data-row="{{$i}}"title="Configure item">⚙️</button>
                                    </div>
                                     @if($config && $config->show_description == 1)
                                    <div class="description-wrapper mt-1" data-row="{{ $i-1 }}">

                                       @if(isset($item->lines) && count($item->lines) > 0)

                                          @foreach($item->lines as $lineIndex => $line)
                                          <div class="d-flex mb-1">
                                             <input type="text"
                                                name="description_lines[{{ $i-1 }}][]"
                                                value="{{ $line->line_text }}"
                                                class="form-control description-input"
                                                placeholder="Enter description line">

                                          </div>
                                          @endforeach

                                       @else

                                          <div class="d-flex mb-1">
                                             <input type="text"
                                                name="description_lines[{{ $i-1 }}][]"
                                                class="form-control description-input"
                                                placeholder="Enter description line">

                                             <button type="button" class="btn btn-success add-desc ms-1">+</button>
                                             <button type="button" class="btn btn-danger remove-desc ms-1">-</button>
                                          </div>

                                       @endif

                                    </div>
                                    @endif
                                    <input type="hidden"id="item_size_info_{{$i}}"name="item_size_info[]"value='@json($item->selected_sizes ?? [])'data-id="{{$i}}">
                                 </td>                              
                                 <td class="w-min-50">
                                    <input type="number" class="quantity w-100 form-control" id="quantity_tr_{{$i}}" name="qty[]" value="{{$item->qty}}"data-id="{{$i}}"  placeholder="Quantity"  style="text-align:right;" >
                                 </td>
                                 <td class="w-min-50">
                                    <input type="text" class="w-100 form-control" id="unit_tr_{{$i}}" readonly style="text-align:center;"data-id="{{$i}}"  value="{{$item->s_name}}" />
                                    <input type="hidden" class="units w-100" name="units[]" id="units_tr_{{$i}}" data-id="{{$i}}"  value="{{$item->unit}}">
                                 </td>
                                 <!-- Price with GST -->
                                <td>
                                    <input type="number" 
                                        class="price_with_gst form-control" 
                                        name = "pricewithgst[]"
                                        id="price_with_gst_tr_{{$i}}" 
                                        data-id="{{$i}}" 
                                        value="{{$item->pricewithgst}}"
                                        style="text-align:right;" />
                                </td>

                                <!-- Hidden cost -->
                                <input type="hidden" id="cost_tr_{{$i}}" />

                                <!-- Profit -->
                                <td>
                                    <input type="number" 
                                        class="profit form-control" 
                                        id="profit_tr_{{$i}}" 
                                        name = "profit[]"
                                        value="{{$item->profit}}"
                                        readonly 
                                        style="text-align:right;" />
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
                        @endif
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
                                 @if($sundry->nature_of_sundry=='OTHER')

                                 @php $count_sundry++ @endphp 
                                    <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" >
                                       <td class="w-min-50">
                                          <select id="bill_sundry_@php echo $index;@endphp" class="w-95-parsent  bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="@php echo $index;@endphp">
                                             <option value="">Select</option>
                                             <?php
                                             foreach ($billsundry as $value) { 
                                                if($value->nature_of_sundry=='OTHER'){?>
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
                                 }else if($val['nature_of_sundry']=="ROUNDED OFF (+)" || $val['nature_of_sundry']=="ROUNDED OFF (-)" || $val['nature_of_sundry']=="TCS"){
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
                              <tr id="billtr_tcs" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" <?php $tcs_status = 1; if(!isset($roundReturn["TCS"])){ $tcs_status = 0;?>  style="display:none" <?php } ?>>
                                 <td class="w-min-50">
                                   
                                    <select id="bill_sundry_tcs" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="tcs">
                                        <option value="">Select</option>
                                       <?php
                                       foreach ($billsundry as $value) { 
                                          if($value->nature_of_sundry=='TCS'){?>
                                             <option value="<?php echo $value->id;?>" data-sundry_percent="<?php echo $value->sundry_percent;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" class="sundry_option_tcs" id="sundry_option_tcs" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>" data-tcs_status="<?php echo $tcs_status;?>"><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50 ">
                                    <span name="tax_amt[]" class="tax_amount" id="tax_amt_tcs"></span>
                                    <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_tcs">
                                 </td>
                                 <td class="w-min-50 ">
                                    <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_tcs" data-id="tcs" <?php if(isset($roundReturn["TCS"])){?> value="<?php echo $roundReturn["TCS"][0]['amount'];?>" <?php } ?> style="text-align:right;" readonly></td>
                                 <td></td>
                              </tr>
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
               <div class="mb-3">
   <label class="form-label fw-bold">Narration</label>
   <input 
      type="text"
      id="narration"
      name="narration"
      class="form-control"
      value="{{ $sale->narration ?? '' }}"
      placeholder="Enter narration for the entry...">
</div>
               <div class=" d-flex">
                  
                  <div class="ms-auto">
                     <input type="submit" value="SAVE" class="btn btn-xs-primary" id="saveBtn">
                     <a href="{{ url()->previous() }}" class="btn btn-black">QUIT</a>
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
<!-- Modal -->
<div class="modal fade" id="sizeModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-3">
         <div class="modal-header">
            <h5 class="modal-title">Size List</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>

         <div class="modal-body">
            <div class="table-responsive">
               <table class="table table-bordered table-striped mb-0 item_size_table">
                  <thead>
                     <tr>
                        <th style="width: 42%;">Size</th>
                        <th>Weight</th>
                        <th>Reel No.</th>
                     </tr>
                  </thead>
                  <tbody id="item_size_tbody">
                  </tbody>
               </table>

               <div class="mt-2 text-end">
                  <strong>Total Weight: <span id="total_weight">0</span></strong>
               </div>
            </div>
         </div>

         <div class="modal-footer">
            <input type="hidden" id="item_size_row_id">
            <input type="hidden" id="sale_id" value="{{ $sale->id ?? '' }}">
            <button type="button" class="btn btn-info item_size_btn">Submit</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
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
   var selected_series = "";
   var enter_gst_status = 0;
   var auto_gst_calculation = 0;
    var bill_to_id = "{{$bill_to_id}}";
   var shipp_to_id = "{{$shipp_to_id}}";
   var customer_gstin = "";
   var merchant_gstin = "";
   var percent_arr = [];
   var add_more_count = '<?php echo --$i;?>';
   var add_more_bill_sundry_up_count = '<?php echo --$index;?>';
   var address_id = "{{$sale->address_id}}";
   var page_load = 0;
   var production_module_status = "<?php echo $production_module_status; ?>";
   var vehicle_info_type = "{{$vehicle_info_type}}";
   var to_pay_freight = "{{$to_pay_freight}}";
   var to_pay_other_charges = "{{$to_pay_other_charges}}";
   var cash_group_ids = @json($cash_group_ids);
   function addMoreItem() {

   
      let empty_status = 0;

$('.goods_items').each(function () {
    let i = $(this).attr('data-id');
    if ($(this).val() === "" || $("#quantity_tr_" + i).val() === "" || $("#price_tr_" + i).val() === "") {
        empty_status = 1;
    }
});

if (empty_status === 1) {
    alert("Please enter required fields");
    return;
}

let srn = $("#srn_" + add_more_count).html();
srn++;

add_more_count++;

let optionElements = `<?php echo $item_list; ?>`;

// Build new row
let newRow = `
<tr id="tr_${add_more_count}" class="font-14 font-heading bg-white">

<td class="w-min-50" id="srn_${add_more_count}">${srn}</td>

<td class="w-min-50">
    <div class="d-flex align-items-center gap-2">

        <select class="form-control goods_items select2-single"
                name="goods_discription[]"
                id="goods_discription_tr_${add_more_count}"
                data-id="${add_more_count}"
                onchange="call_fun('${add_more_count}'); handleItemChange(${add_more_count});"
                required data-modal="itemModal">
            <option value="">Select Item</option>
            ${optionElements}
        </select>

        <button type="button"
                class="btn btn-outline-secondary p-1 px-2 editItemDetailsBtn d-none"
                data-row="${add_more_count}">
            ⚙️
        </button>

    </div>
    @if($config && $config->show_description == 1)
<div class="description-wrapper mt-1" data-row="${add_more_count-1}">
      <div class="d-flex mb-1">
         <input type="text" 
               name="description_lines[${add_more_count-1}][]" 
               class="form-control description-input"
               placeholder="Enter description line">

         <button type="button" class="btn btn-success add-desc ms-1">+</button>
         <button type="button" class="btn btn-danger remove-desc ms-1">-</button>
      </div>
   </div>
       @endif
    <input type="hidden"
           id="item_size_info_${add_more_count}"
           name="item_size_info[]"
           data-id="${add_more_count}">
</td>

<td class="w-min-50">
    <input type="number" class="quantity w-100 form-control"
           id="quantity_tr_${add_more_count}"
           name="qty[]"
           data-id="${add_more_count}"
           style="text-align:right;">
</td>

<td class="w-min-50">
    <input type="text" class="w-100 form-control"
           id="unit_tr_${add_more_count}"
           readonly>
    <input type="hidden"
           class="units"
           id="units_tr_${add_more_count}"
           name="units[]">
</td>

<td>
    <input type="number" 
        class="price_with_gst form-control" 
        name = "pricewithgst[]"
        id="price_with_gst_tr_${add_more_count}" 
        data-id="${add_more_count}" 
        style="text-align:right;" />
</td>

<!-- Hidden cost -->
<input type="hidden"  data-id="${add_more_count}"  id="cost_tr_${add_more_count}" />

<!-- Profit -->
<td>
    <input type="number" 
        class="profit form-control" 
        id="profit_tr_${add_more_count}" 
        name = "profit[]"
         data-id="${add_more_count}" 
        readonly 
        style="text-align:right;" />
</td>

<td class="w-min-50">
    <input type="number" class="price w-100 form-control"
           id="price_tr_${add_more_count}"
           name="price[]"
           data-id="${add_more_count}"
           style="text-align:right;">
</td>

<td class="w-min-50">
    <input type="number" class="amount w-100 form-control"
           id="amount_tr_${add_more_count}"
           name="amount[]"
           data-id="${add_more_count}"
           style="text-align:right;">
</td>

<td class="w-min-50" style="display:flex"></td>

</tr>
`;
$("#example11").find("tr[id^='tr_']").last().after(newRow);

$("#goods_discription_tr_" + add_more_count).select2();

let k = 1;
$(".goods_items").each(function () {
    let id = $(this).attr("data-id");
    $("#srn_" + id).html(k);
    k++;
});

  
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

      let itemId = $("#goods_discription_tr_" + id).val();

      let arr = [];
      try { arr = JSON.parse($("#item_size_info_" + id).val() || "[]"); } catch(e){}

      if (usedSizesByItem[itemId]) {
         let removeIds = arr.map(o => String(o.id));
         usedSizesByItem[itemId] = usedSizesByItem[itemId].filter(
            sid => !removeIds.includes(String(sid))
         );
      }

      $("#tr_" + id).remove();

      disableGloballyUsedSizes(itemId);


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
     
      customer_gstin = $('#party option:selected').attr('data-state_code'); 
      let under_group = $('#party option:selected').attr('data-under_group');
     
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
         let final_tcs_amount = 0;
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
               $(".extra_gst").remove();
               let index = 1;
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
         // if(tcs_amoumt!="" && tcs_amoumt!=0){
         //    alert()
         //    final_total = parseFloat(final_total) + parseFloat((final_total*0.1)/100);
         // }
         // console.log(final_total)
         
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
            if(nature_of_sundry=="TCS" && $('option:selected', this).attr('data-tcs_status')==1){
               
                  $("#tax_amt_"+id).html(sundry_percent+" %");
                  $("#tax_rate_tr_"+id).val(sundry_percent);
                  let tcs_amount = (on_tcs_amount*sundry_percent)/100;
                  final_tcs_amount = tcs_amount;
                  tcs_amount = tcs_amount.toFixed(2);
                  $("#bill_sundry_amount_"+id).val(tcs_amount);
                  
                  final_total = final_total + parseFloat(tcs_amount);
               
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
         let roundoff = parseFloat(final_total) - parseFloat($("#total_taxable_amounts").val()) - parseFloat(gstamount) - parseFloat(final_tcs_amount);
            
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
         $(".goods_items").each(function(){
            $(this).change();
         })
         if(bill_to_id!=shipp_to_id){
            $("#shipping_name").val(shipp_to_id);
            $("#shipping_name").change();
         }
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
      $("#date").trigger('change');
   });
   function call_fun(data){
      if($('#goods_discription_'+data).val()==""){
         $("#quantity_tr_"+data).val('');
         $("#price_tr_"+data).val('');
         $("#amount_tr_"+data).val('');
         $("#quantity_tr_"+data).keyup();
         $("#price_tr_"+data).keyup();
         $("#amount_tr_"+data).keyup();
      }
     
      var selectedOptionData = $('#goods_discription_tr_' + data + ' option:selected').data('val');
      var item_units_id = $('#goods_discription_tr_' + data + ' option:selected').attr('data-unit_id');
      var itemId = $('#goods_discription_tr_' + data + ' option:selected').val();
      var party_id = $('#party').val();
      if (party_id.length > 0) {
          
         $('#unit_tr_' + data).val(selectedOptionData);
         $('#units_tr_' + data).val(item_units_id);
         calculateAmount();
      }else{
         alert("Select Party Name First.");
         $('#goods_discription_tr_' + data + '').val("");
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
         address_html = "<option value=''>"+$('option:selected', this).attr('data-address')+"</option>";
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
            let billing_address = `{{ e($sale->billing_address) }}`;
            let billing_pincode = `{{ e($sale->billing_pincode) }}`;
            let party_gstin = $("#party option:selected").attr('data-gstin');
            address_html += "<option value='"+address_id+"' selected data-address='"+billing_address+"' data-pincode='"+billing_pincode+"'>"+billing_address+"</option>";
            $("#partyaddress").html("GSTIN : "+party_gstin+"<br>Address : "+billing_address);
         }
         $("#address").html(address_html);
         $(".address_div").show();
      }
      calculateAmount();
   });
    $("#voucher_prefix").keyup(function(){
      $("#voucher_no").val($(this).val());
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
      //getItemGstRate($(this).val(),$(this).attr('data-id'));
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
   $(".goods_items").data("loaded", true);

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



  
  //   // When clicking purchaseBtn
  //   $("#purchaseBtn").on('click', function(event) {
  //     event.preventDefault(); // Stop default submit behavior
  //     $(this).closest('form').submit(); // Manually submit the form
  //   });
  
    // When pressing Enter on purchaseBtn
   


  function addEmptyRow() {
    const nextIndex = $("#item_size_tbody tr").length + 1;

    let availableOptions = [];
    $("#item_size_tbody tr:first .item_size option").each(function () {
        if ($(this).val() !== "") {
            availableOptions.push({
                id: $(this).val(),
                weight: $(this).data("weight"),
                reel_no: $(this).data("reel_no"),
                size: $(this).text().split("|")[0].trim()
            });
        }
    });

    $("#item_size_tbody").append(generateRow(nextIndex, availableOptions));

    $(".item_size").select2({
        dropdownParent: $('#sizeModal'),
        width: '100%'
    });

    const rowId = $("#item_size_row_id").val();
    const itemId = $("#goods_discription_tr_" + rowId).val();
    disableAlreadySelectedSizes(itemId);
}

// Calculate total weight
function calculateTotalWeight() {
    let total = 0;
    $(".item_weight").each(function () {
        const val = parseFloat($(this).val());
        if (!isNaN(val)) total += val;
    });
    $("#total_weight").text(total.toFixed(2));
}

$(document).on('click', '.item_size_btn', function () {
    const rowId = $("#item_size_row_id").val();
    let selectedData = [];
    let totalWeight = 0;

    $("#item_size_tbody tr").each(function () {
        const sizeId = $(this).find(".item_size").val();
        const weight = parseFloat($(this).find(".item_weight").val()) || 0;
        const reelNo = $(this).find(".item_reel_no").val();

        if (sizeId) {
            selectedData.push({ id: sizeId, weight: weight, reel_no: reelNo });
            totalWeight += weight;
        }
    });

    $("#item_size_info_" + rowId).val(JSON.stringify(selectedData));

    $("#quantity_tr_" + rowId).val(totalWeight.toFixed(2)).attr('readonly', true);

    const itemId = $("#goods_discription_tr_" + rowId).val();
$("#item_size_info_" + rowId).val(JSON.stringify(selectedData));

rebuildUsedSizesForItem(itemId);

disableGloballyUsedSizes(itemId);

    $("#sizeModal").modal('hide');
});


//  Remove row
$(document).on("click", ".remove-row", function () {

    const rowId = $("#item_size_row_id").val();
    const itemId = $("#goods_discription_tr_" + rowId).val();

    $(this).closest("tr").remove();
    calculateTotalWeight();

    let updated = [];
    $("#item_size_tbody tr").each(function () {
        const sizeId = $(this).find(".item_size").val();
        const weight = $(this).find(".item_weight").val();
        const reel   = $(this).find(".item_reel_no").val();

        if (sizeId) {
            updated.push({ id: sizeId, weight: weight, reel_no: reel });
        }
    });

    $("#item_size_info_" + rowId).val(JSON.stringify(updated));

    rebuildUsedSizesForItem(itemId);

    disableGloballyUsedSizes(itemId);
});
  //  Disable already selected sizes for same item
function disableAlreadySelectedSizes(itemId) {
    if (!itemId || !selectedSizesByItem[itemId]) return;
    const usedSizes = selectedSizesByItem[itemId];

    $(".item_size").each(function () {
        const val = $(this).val();
        $(this).find("option").each(function () {
            if ($(this).val() !== val && usedSizes.includes($(this).val())) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    });
}

function handleItemChange(rowId) {

    console.log("Checking row:", rowId);

    let ddl = $("#goods_discription_tr_" + rowId);
    let itemId = ddl.val();
    let btn = $("#tr_" + rowId + " .editItemDetailsBtn");

    if (!itemId || itemId === "") {
        console.log("No item selected.");
        btn.addClass("d-none");
        return;
    }

    let paramStatus = ddl.find(":selected").data("parameterized_stock_status");

    console.log("Row:", rowId, "Item:", itemId, "ParamStatus:", paramStatus);

    if(production_module_status==1 && bill_to_id==""){
        console.log("Parameterized → Show Gear + Open Modal");

        btn.removeClass("d-none");

        let saleId = $("#sale_id").val();
        openSizeModal(itemId, rowId, saleId);   // 🔥 Auto-open modal

    } else {
        console.log("NOT parameterized → Hide Gear");
        btn.addClass("d-none");
    }
    
}
// Disable globally used sizes for this item everywhere
function disableGloballyUsedSizes(itemId) {

   if (!itemId || !usedSizesByItem[itemId]) return;

   const used = usedSizesByItem[itemId];

   $(".item_size").each(function () {
      const currentVal = $(this).val();

      $(this).find("option").each(function () {
         let optVal = $(this).val();

         if (optVal !== currentVal && used.includes(optVal)) {
               $(this).prop("disabled", true);
         } else {
               $(this).prop("disabled", false);
         }
      });
   });
   }
   $(document).on('select2:open', function () {

    let select = $('.select2-container--open').prev('select');

    if (select.hasClass('goods_items')) {
        activeItemRowId = select.data('id');
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

// 🔥 REQUIRED DATA ATTRIBUTES (THIS FIXES undefined)
option.setAttribute('data-gstin', res.account.gstin || '');
option.setAttribute('data-state_code', res.account.state_code || '');
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
// SAFETY
if (!activeItemRowId) {
    alert('Item row not detected');
    return;
}

// 🎯 TARGET EDIT SALE ROW
let itemSelect = $('#goods_discription_tr_' + activeItemRowId);

// CREATE OPTION
let option = document.createElement("option");
option.value = res.item.id;
option.text  = res.item.name;
option.selected = true;

// REQUIRED DATA
option.setAttribute('data-val', res.item.unit);
option.setAttribute('data-unit_id', res.item.u_name);
option.setAttribute('data-percent', res.item.gst_rate);
option.setAttribute('data-parameterized_stock_status', res.item.parameterized_stock_status ?? 0);
option.setAttribute('data-config_status', res.item.config_status ?? 0);
option.setAttribute('data-group_id', res.item.group_id ?? '');

// APPEND + SELECT
itemSelect.append(option).trigger('change');

// FOCUS QTY
$('#quantity_tr_' + activeItemRowId).focus();

// CLEANUP
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
      console.log(other_charges)
      $(".price").each(function(){
         let val = parseFloat($(this).attr("data-price")) || 0;
         console.log(val+"--"+other_charges+"--"+to_pay_freight);
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









function getGstPercent(rowId) {
    return parseFloat(
        $("#goods_discription_tr_" + rowId + " option:selected").data('percent')
    ) || 0;
}

function updatePriceFromGST(rowId) {

    let priceWithGst = parseFloat($("#price_with_gst_tr_" + rowId).val()) || 0;
    let gstPercent = getGstPercent(rowId);

    if (priceWithGst > 0) {

        let priceWithoutGst = priceWithGst / (1 + (gstPercent / 100));

        $("#price_tr_" + rowId).val(priceWithoutGst.toFixed(2)).trigger('input');
    }
}

function calculateProfit(rowId) {

    let gstPercent = getGstPercent(rowId);
    let withGst = parseFloat($("#price_with_gst_tr_" + rowId).val()) || 0;
    let cost = parseFloat($("#cost_tr_" + rowId).val()) || 0;
console.log(cost);
    if (!withGst || !cost) {
        $("#profit_tr_" + rowId).val('');
        return;
    }

    let withoutGst = withGst / (1 + gstPercent / 100);
    let profitBeforeGst = withoutGst - cost;

    let gstSale = withGst - withoutGst;
    let gstCost = cost * gstPercent / 100;

    let gstDiff = gstSale - gstCost;

    let finalProfit = profitBeforeGst - gstDiff;

    $("#price_tr_" + rowId).val(withoutGst.toFixed(2));
    $("#profit_tr_" + rowId).val(finalProfit.toFixed(2));
}

$(document).on('change', '.goods_items', function () {

    let rowId = $(this).data('id');

    // reset
    $("#cost_tr_" + rowId).val('');
    $("#profit_tr_" + rowId).val('');

    let itemId = $(this).val();
    let date = $("#date").val();
    let series = $("#series_no").val();

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
                $("#profit_tr_" + rowId).val('');
            }

            updatePriceFromGST(rowId);
            calculateProfit(rowId);
        }
    });


    

});

function updatePrice(rowId) {

    if (!isPageLoaded) return; // ❌ STOP on page load

    let itemId = $("#goods_discription_tr_" + rowId).val();
    let date   = $("#date").val();

    if (!itemId || !date) return;

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

                // ✅ Set price with GST
                $("#price_with_gst_tr_" + rowId).val(res.price_with_gst);

                // ✅ Recalculate
                updatePriceFromGST(rowId);

            } else {
                $("#price_with_gst_tr_" + rowId).val('');
                alert("No rate found for selected date");
            }
        }
    });
}

let isPageLoaded = false;

$(document).ready(function () {
    setTimeout(() => {
        isPageLoaded = true; // after page fully ready
    }, 300); // small delay
});

// Item change
$(document).on("change", ".goods_items", function () {
    let rowId = $(this).data("id");
    updatePrice(rowId);
});

// Date change
$("#date").on("change", function () {
    $(".goods_items").each(function () {
        let rowId = $(this).data("id");
        updatePrice(rowId);
    });
});
$(document).on('input', '.price_with_gst', function () {

    let rowId = $(this).data('id');

    updatePriceFromGST(rowId);
    calculateProfit(rowId);
});
$(document).on('change', '#series_no, #date', function () {

    $('.goods_items').each(function () {

        let rowId = $(this).data('id');

        if ($(this).val()) {
            $(this).trigger('change');
        }
    });
});







</script>
@endsection