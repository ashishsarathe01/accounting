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
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif            
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Stock Transfer</h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('stock-transfer.update',$stock_transfer->id) }}" id="saleForm">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Series No.</label>
                            <select id="series_no" name="series_no" class="form-select" required >
                                <option value="{{$stock_transfer->series_no}}">{{$stock_transfer->series_no}}</option>
                                
                            </select>
                            <input type="hidden" id="merchant_gst" name="merchant_gst" value="{{$stock_transfer->merchant_gst}}">
                            <ul style="color: red;">
                                @error('series_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Date</label>
                            <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date"  required value="{{$stock_transfer->transfer_date}}">
                            <ul style="color: red;">
                            @error('date'){{$message}}@enderror                        
                            </ul>
                        </div>                
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                            <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" readonly style="text-align: right;" placeholder="Voucher No" value="{{$stock_transfer->voucher_no_prefix}}">
                            <input type="hidden" class="form-control" id="voucher_no" name="voucher_no" value="{{$stock_transfer->voucher_no}}">
                            <input type="hidden" class="form-control" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                            <ul style="color: red;">
                            @error('voucher_no'){{$message}}@enderror                        
                            </ul>
                        </div>                        
                        <div class="mb-4 col-md-5">
                            <label for="name" class="form-label font-14 font-heading">FROM MATERIAL CENTRE</label><br>
                            <select class="form-select" name="material_center_from" id="material_center_from" required>
                                <option value="{{$stock_transfer->material_center_from}}">{{$stock_transfer->material_center_from}}</option>                                
                            </select>   
                            <ul style="color: red;">
                                @error('material_center_from'){{$message}}@enderror                        
                            </ul>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">TO MATERIAL CENTRE</label>
                            <select name="material_center_to" id="material_center_to" class="form-select" required>
                                <option value="{{$stock_transfer->material_center_to}}">{{$stock_transfer->material_center_to}}</option>                                
                            </select>
                            <ul style="color: red;">
                                @error('material_center_to'){{$message}}@enderror                        
                            </ul>
                        </div>
                        <input type="hidden" name="to_series" id="to_series">
                    </div>
                    <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                        <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                            <thead>
                                <tr class=" font-12 text-body bg-light-pink ">
                                    <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                                    <th class="w-min-50 border-none bg-light-pink text-body " style="width: 36%;">Description of Goods</th>                           
                                    <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Qty</th>
                                    <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: center;">Unit</th>
                                    <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                                    <th class="w-min-50 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Amount</th>
                                    <th class="w-min-50 border-none bg-light-pink text-body "></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $i=1; $total = 0; $itemcount = count($stock_transfer_desc);@endphp
                                @foreach($stock_transfer_desc as $item)
                                    <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                                        <td class="w-min-50" id="srn_{{$i}}">{{$i}}</td>
                                        <td class="w-min-50">
                                            <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_{{$i}}" data-id="{{$i}}">
                                                <option value="">Select Item</option>
                                                @foreach($item_list as $key => $value)
                                                    <option value="{{ $value->id }}" data-unit_id="{{$value->u_name}}"  data-unit_name="{{$value->unit}}"  data-available_item="{{$value->available_item}}" <?php if($value->id==$item->goods_discription){ echo "selected";} ?>>{{ $value->name }}</option>
                                                @endforeach
                                            </select>                  
                                        </td>                           
                                        <td class="w-min-50">
                                            <input type="number" class="quantity w-100 form-control" id="quantity_tr_{{$i}}" name="qty[]" placeholder="Quantity" style="text-align:right;" data-id="{{$i}}" value="{{$item->qty}}"/>
                                        </td>
                                        <td class="w-min-50">                              
                                            <input type="text" class="w-100 form-control unit" id="unit_tr_{{$i}}" readonly style="text-align:center;" data-id="{{$i}}" value="{{$item->s_name}}"/>
                                            <input type="hidden" class="units w-100" name="units[]" id="units_tr_{{$i}}" value="{{$item->unit}}"/>
                                        </td>
                                        <td class="w-min-50">
                                            <input type="number" class="price form-control" id="price_tr_{{$i}}" name="price[]" placeholder="Price" style="text-align:right;" data-id="{{$i}}" value="{{$item->price}}"/>
                                        </td>
                                        <td class="">
                                            <input type="number" id="amount_tr_{{$i}}" class="amount w-100 form-control" name="amount[]" placeholder="Amount"  style="text-align:right;" data-id="{{$i}}" value="{{$item->amount}}"/>
                                        </td>
                                        <td style="display:flex">
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
                            <div class="total">
                                <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-50 fw-bold"></td>
                                    <td class="w-min-50 fw-bold"></td>
                                    <td class="w-min-50 fw-bold"></td>
                                    <td class="w-min-50 fw-bold"></td>
                                    <td class="w-min-50 fw-bold">Total</td>
                                    <td class="w-min-50 fw-bold">
                                        <span id="totalSum" style="float: right;">{{$total}}</span>
                                        <input type="hidden" name="item_total" id="total_taxable_amounts" value="{{$total}}">
                                    </td>
                                </tr>
                            </div>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-lg-5"></div>
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
                                        @php $index = 1; $count_sundry = 0; @endphp
                                        @foreach($stock_transfer_sundry as $sundry)
                                            @if($sundry->nature_of_sundry=='OTHER')
                                                @php $count_sundry++ @endphp 
                                                <tr id="billtr_@php echo $index;@endphp" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
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
                                                            <svg style="color: red; cursor: pointer; margin-left: 10px;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="@php echo $index;@endphp" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg>
                                                        @endif
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
                                        
                                        
                                       
                                        
                                        <tr class="font-14 font-heading bg-white">
                                            <td class="w-min-50 fw-bold">Total</td>
                                            <td class="w-min-50 fw-bold"></td>
                                            <td class="w-min-50 fw-bold">
                                                <span id="bill_sundry_amt" style="float:right ;">{{$stock_transfer->grand_total}}</span>
                                                <input type="hidden" name="grand_total" id="total_amounts" value="{{$stock_transfer->grand_total}}">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table id="transcton-sale3" class="table-striped table m-0 shadow-sm table-bordered">
                                    <tbody>                              
                                        <div>
                                            <tr class="font-14 font-heading bg-white">
                                                <td colspan="4" class="pl-40"><button type="button" class="btn btn-info transport_info">Transport Info</button></td>
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
                                                        <label for="name" class="form-label font-14 font-heading">Transport Id</label>
                                                        <input type="text" id="transport_id" name="transport_id" class="form-control" placeholder="Transport Name" />
                                                    </div>
                                                    <div class="mb-6 col-md-6">
                                                        <label for="name" class="form-label font-14 font-heading">Other Details</label>
                                                        <input type="text" id="other_details" name="other_details" class="form-control" placeholder="Other Details"/>
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
                    <div class=" d-flex">                  
                        <div class="ms-auto">
                            <input type="submit" value="SAVE" class="btn btn-xs-primary" id="saveBtn">
                           <a href="{{ route('stock-transfer.index') }}" class="btn  btn-black ">QUIT</a> 
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

</body>
@include('layouts.footer')
<script>
    var add_more_count = '<?php echo --$i;?>';
    var add_more_bill_sundry_up_count = '<?php echo --$index;?>';
    let series_list = @json($series_list);
    $(document).ready(function() {
        $( ".select2-single, .select2-multiple" ).select2(); 
        $("#saveBtn").click(function(){
            if(confirm("Are you sure to submit?")==true){            
                $("#saleForm").validate({
                    ignore: [], 
                    rules: {
                        series_no: "required",
                        voucher_no: "required",
                        material_center_from: "required",
                        material_center_to: "required",
                        "goods_discription[]": "required",
                        "qty[]" : "required",
                        "price[]" : "required",
                        "amount[]" : "required",
                    },
                    messages: {
                        series_no: "Please select series no",
                        voucher_no: "Please enter voucher no",
                        material_center_from: "Please select material center",
                        material_center_to: "Please select material center",
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
    $(document).on('click','.add_more_wrapper',function(){   
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
        var tr_id = 'tr_' + add_more_count;
        newRow = '<tr id="tr_' + add_more_count + '" class="font-14 font-heading bg-white"><td class="w-min-50" id="srn_'+add_more_count+'">' + srn + '</td><td class=""><select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_'+add_more_count+'" data-id="'+add_more_count+'"><option value="">Select Item</option>@foreach($item_list as $item_list)<option value="{{$item_list->id}}" data-unit_id="{{$item_list->u_name}}" data-unit_name="{{$item_list->unit}}" data-available_item="{{$item_list->available_item}}">{{$item_list->name}}</option>@endforeach';
        newRow += optionElements;
        newRow += '</td><td class="w-min-50"><input type="number" class="quantity w-100 form-control" name="qty[]" id="quantity_tr_' + add_more_count + '" placeholder="Quantity" style="text-align:right" data-id="'+add_more_count+'" /></td><td class="w-min-50"><input type="text" class="w-100 form-control unit" id="unit_tr_'+add_more_count+'" readonly style="text-align:center;" data-id="'+add_more_count+'"/><input type="hidden" class="units w-100" name="units[]" id="units_tr_' + add_more_count + '"/></td><td class="w-min-50"><input type="number" class="price w-100 form-control" name="price[]" id="price_tr_' + add_more_count + '" placeholder="Price" style="text-align:right" data-id="'+add_more_count+'"/></td><td class="w-min-50"><input type="number" class="amount w-100 form-control" name="amount[]" id="amount_tr_' + add_more_count + '" placeholder="Amount" style="text-align:right" data-id="'+add_more_count+'"/></td><input type="hidden" name="item_parameters[]" id="item_parameters_'+add_more_count+'"><input type="hidden" name="config_status[]" id="config_status_'+add_more_count+'"><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';      $
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
        calculateAmount(); 
    });
    $(document).on('change', '.item_id', function(){
        $('#unit_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-unit_name'));
        $('#units_tr_'+$(this).attr('data-id')).val($('option:selected', this).attr('data-unit_id'));
        if($('#goods_discription_'+$(this).attr('data-id')).val()==""){
            $("#quantity_"+$(this).attr('data-id')).val('');
            $("#price_"+$(this).attr('data-id')).val('');
            $("#amount_"+$(this).attr('data-id')).val('');
            $("#quantity_"+$(this).attr('data-id')).keyup();
            $("#price_"+$(this).attr('data-id')).keyup();
            $("#amount_"+$(this).attr('data-id')).keyup();
        } 
    });
    $(document).on('input', '.price',function(){         
         calculateAmount();
    });
    $(document).on('input', '.quantity',function(){
        calculateAmount();
    });
    $(document).on('change', '.bill_sundry_tax_type',function(){
        let id = $(this).attr('data-id');
        if($(this).val()==""){
            $("#bill_sundry_amount_"+id).val('');
        }else{
            $("#bill_sundry_amount_"+id).attr('readonly',false);
        }      
        calculateAmount();  
    });
    function calculateAmount(){
        let total = 0;
        $('#example11 tbody tr').each(function() {         
            var price = $(this).find('.price').val();
            var quantity = $(this).find('.quantity').val();            
            var amount = (price && quantity) ? (price * quantity) : 0;
            if(amount!=0){
                $(this).find('.amount').val(parseFloat(amount).toFixed(2));
                $(this).find('.amount').keyup();
            }
            if(amount!=undefined){
               total += parseFloat(amount);
            }
        });
        $("#totalSum").html(total.toFixed(2));
        $("#total_taxable_amounts").val(total.toFixed(2));
        let bill_sundry_amount = 0;
        $(".bill_sundry_tax_type").each(function(){          
            let id = $(this).attr('data-id');
            let type = $('option:selected', this).attr('data-type');
            let adjust_sale_amt = $('option:selected', this).attr('data-adjust_sale_amt')
            if($("#bill_sundry_amount_"+id).val()!=''){
                if(type=="additive"){
                    bill_sundry_amount = parseFloat(bill_sundry_amount) + parseFloat($("#bill_sundry_amount_"+id).val());
                }else if(type=="subtractive"){
                    bill_sundry_amount = parseFloat(bill_sundry_amount) - parseFloat($("#bill_sundry_amount_"+id).val());
                }                
            }
        });
        let total_amounts = parseFloat(total) + parseFloat(bill_sundry_amount);
        total_amounts = total_amounts.toFixed(2)
        $("#bill_sundry_amt").html(total_amounts);
        $("#total_amounts").val(total_amounts)
    }
    $(".add_more_bill_sundry_up").click(function() {
        let empty_status = 0;
        
        $(".bill_sundry_tax_type").each(function(){     
            if($(this).attr('data-id')!='round_minus' && $(this).attr('data-id')!='round_plus' && $(this).attr('data-id')!='cgst' && $(this).attr('data-id')!='sgst' && $(this).attr('data-id')!='igst'){
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
        foreach ($billsundry as $value){ ?>
            optionElements += '<option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>"><?php echo $value->name; ?></option>';<?php          
        } ?>
        newRow = '<tr id="billtr_' + add_more_bill_sundry_up_count + '" class="font-14 font-heading bg-white extra_taxes_row sundry_tr"><td class="w-min-50"><select class="w-95-parsent bill_sundry_tax_type w-100 form-select"  id="bill_sundry_' + add_more_bill_sundry_up_count + '" name="bill_sundry[]" data-id="'+add_more_bill_sundry_up_count+'">';
        newRow += optionElements;
        newRow += '</select></td><td class="w-min-50"><span name="tax_amt[]" id="tax_amt_' + add_more_bill_sundry_up_count + '"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_' + add_more_bill_sundry_up_count + '"></td><td class="w-min-50"><input type="number" class="bill_amt w-100 form-control" id="bill_sundry_amount_' + add_more_bill_sundry_up_count + '" name="bill_sundry_amount[]" data-id="'+add_more_bill_sundry_up_count+'" readonly style="text-align:right;" placeholder="Amount"></td><td class="w-min-50"><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_sundry_up" data-id="' + add_more_bill_sundry_up_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
        $curRow.before(newRow);
    }); 
    $(document).on("click", ".remove_sundry_up", function() {
        let id = $(this).attr('data-id');
        $("#billtr_" + id).remove();
        calculateAmount();
    }); 
    $(document).on('input', '.bill_amt',function(){
        calculateAmount();
    });
    $(".transport_info").click(function(){
        $("#transport_info_modal").modal('toggle');
    });
    $(".save_transport_info").click(function(){
        $("#transport_info_modal").modal('toggle');
    });    
</script>
@endsection