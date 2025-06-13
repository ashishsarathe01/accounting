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
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Stock Transfer</h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('stock-transfer.store') }}" id="saleForm">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Series No.</label>
                            <select id="series_no" name="series_no" class="form-select" required >
                                <option value="">Select</option>
                                @foreach($series_list as $key => $value)
                                    <option value="{{$value->series}}" data-invoice_start_from="{{$value->invoice_start_from}}" data-invoice_prefix="{{$value->invoice_prefix}}" data-manual_enter_invoice_no="{{ $value->manual_enter_invoice_no}}" data-duplicate_voucher="{{$value->duplicate_voucher}}" data-blank_voucher="{{$value->blank_voucher}}" data-mat_center="{{$value->mat_center}}" data-gst_no="{{$value->gst_no}}" @if(count($series_list)==1) selected  @endif>{{ $value->series }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="merchant_gst" name="merchant_gst">
                            <ul style="color: red;">
                                @error('series_no'){{$message}}@enderror                        
                            </ul> 
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Date</label>
                            <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" value="{{date('Y-m-d')}}" required>
                            <ul style="color: red;">
                            @error('date'){{$message}}@enderror                        
                            </ul>
                        </div>                
                        <div class="mb-3 col-md-3">
                            <label for="name" class="form-label font-14 font-heading">Voucher No. *</label>
                            <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" readonly style="text-align: right;" placeholder="Voucher No">
                            <input type="hidden" class="form-control" id="voucher_no" name="voucher_no">
                            <input type="hidden" class="form-control" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                            <ul style="color: red;">
                            @error('voucher_no'){{$message}}@enderror                        
                            </ul>
                        </div>                        
                        <div class="mb-4 col-md-5">
                            <label for="name" class="form-label font-14 font-heading">FROM MATERIAL CENTRE</label><br>
                            <select class="form-select" name="material_center_from" id="material_center_from" required>
                                <option value="">Select</option>                                
                            </select>   
                            <ul style="color: red;">
                                @error('material_center_from'){{$message}}@enderror                        
                            </ul>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">TO MATERIAL CENTRE</label>
                            <select name="material_center_to" id="material_center_to" class="form-select" required>
                                <option value="">Select</option>                                
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
                                <tr id="tr_1" class="font-14 font-heading bg-white">
                                    <td class="w-min-50" id="srn_1">1</td>
                                    <td class="w-min-50">
                                        <select class="form-control item_id select2-single" name="goods_discription[]" id="item_id_1" data-id="1">
                                            <option value="">Select Item</option>
                                            @foreach($item_list as $key => $value)
                                                <option value="{{ $value->id }}" data-unit_id="{{$value->u_name}}"  data-unit_name="{{$value->unit}}"  data-available_item="{{$value->available_item}}">{{ $value->name }}</option>
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more_item" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                        <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                        </svg>
                                    </td>
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
                                        <input type="hidden" name="item_total" id="total_taxable_amounts" value="0">
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
                                        <tr id="billtr_1" class="font-14 font-heading bg-white bill_taxes_row sundry_tr">
                                            <td class="w-min-50">
                                                <select id="bill_sundry_1" class="w-95-parsent bill_sundry_tax_type form-select" name="bill_sundry[]" data-id="1">
                                                <option value="">Select</option>
                                                @foreach($billsundry as $key => $value)
                                                       @if($value->nature_of_sundry == 'OTHER')
                                                    <option value="{{ $value->id }}" data-type="{{$value->bill_sundry_type}}" data-adjust_sale_amt="{{$value->adjust_sale_amt}}">{{ $value->name }}</option>   
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="w-min-50">
                                                <span name="tax_amt[]" class="tax_amount" id="tax_amt_1"></span>
                                                <input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_1">
                                            </td>
                                            <td class="w-min-50">
                                                <input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_1" data-id="1" readonly style="text-align:right;" placeholder="Amount" />
                                            </td>
                                            <td>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more_bill_sundry" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor:pointer"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg>
                                            </td>
                                        </tr>

                                         <tr id="billtr_round_plus" class="font-14 font-heading bg-white bill_taxes_row sundry_tr" style="display:none">
                                 <td class="w-min-50">
                                    <select id="bill_sundry_round_plus" class="w-95-parsent bill_sundry_tax_type  form-select" name="bill_sundry[]" data-id="round_plus">
                                       <?php
                                       
                                       foreach ($billsundry as $key => $value) { 
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
                                       foreach ($billsundry as $key => $value) { 
                                          if($value->nature_of_sundry=='ROUNDED OFF (-)'){?>
                                             <option value="<?php echo $value->id;?>" data-type="<?php echo $value->bill_sundry_type;?>" data-adjust_sale_amt="<?php echo $value->adjust_sale_amt;?>" data-effect_gst_calculation="<?php echo $value->effect_gst_calculation;?>" data-sequence="<?php echo $value->sequence;?>" data-nature_of_sundry="<?php echo $value->nature_of_sundry;?>" class="sundry_option_round_minus" id="sundry_option_round_minus" selected><?php echo $value->name; ?></option>
                                             <?php 
                                          }
                                       } ?>
                                    </select>
                                 </td>
                                 <td class="w-min-50"><span name="tax_amt[]" class="tax_amount" id="tax_amt_round_minus"></span><input type="hidden" name="tax_rate[]" value="0" id="tax_rate_tr_round_minus"></td>
                                 <td class="w-min-50"><input class="bill_amt w-100 form-control" type="number" name="bill_sundry_amount[]" id="bill_sundry_amount_round_minus" data-id="round_minus" readonly style="text-align:right;"></td>
                                 <td></td>
                              </tr>



                                        <tr class="font-14 font-heading bg-white" id="append_tr">
                                            <td class="w-min-50 fw-bold">Total</td>
                                            <td class="w-min-50 fw-bold"></td>
                                            <td class="w-min-50 fw-bold">
                                                <span id="bill_sundry_amt" style="float:right ;"></span>
                                                <input type="hidden" name="grand_total" id="total_amounts" value="0">
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
    var add_more_count = 1;
    var add_more_bill_sundry_up_count = 1;
    let series_list = @json($series_list);
    $(document).ready(function() {
        $("#series_no").change();
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
        $("#voucher_prefix").keyup(function(){
            $("#voucher_no").val($(this).val());
        });
        $("#series_no").change(function(){
            $("#voucher_prefix").prop('readonly',true);
            $("#voucher_no").attr('required',true);
            let series = $(this).val();
            let invoice_prefix = $('option:selected', this).attr('data-invoice_prefix');
            let mat_center = $('option:selected', this).attr('data-mat_center');
            let gst_no = $('option:selected', this).attr('data-gst_no');
            let manual_enter_invoice_no = $('option:selected', this).attr('data-manual_enter_invoice_no');
            if($(this).val()==""){
                invoice_prefix = "";
                mat_center = "";
                gst_no = "";
                manual_enter_invoice_no = "";
            }
            
            $("#material_center_from").html('<option value="'+mat_center+'">'+mat_center+'</option>');
            
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
            let option  = '<option value="">Select</option>';
            $.each(series_list, function(index, value) {
                if(gst_no==value.gst_no && series!=value.series){
                    option += '<option value="'+value.mat_center+'" data-series="'+value.series+'">'+value.mat_center+'</option>';
                }
            }); 
            $("#merchant_gst").val(gst_no);           
            $("#material_center_to").html(option);
            calculateAmount();                    
        });
    });
    $("#material_center_to").change(function(){
        $("#to_series").val($('option:selected', this).attr('data-series'));
    });
    $(document).on('click','.add_more_item',function(){   
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
        
        let round_off_total_amount = Math.round(total_amounts);
         let roundoff = parseFloat(round_off_total_amount) - parseFloat(total_amounts);     
            
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
         total_amounts1 = parseFloat(total_amounts) + parseFloat(roundoff) ;
         
          total_amounts = total_amounts1.toFixed(2)
         $("#bill_sundry_amt").html(total_amounts);
        $("#total_amounts").val(total_amounts)
    }
       

       
    $(".add_more_bill_sundry").click(function() {
        let empty_status = 0;
        $(".bill_sundry_tax_type").each(function(){            
            if($(this).val()=="" || $("#bill_sundry_amount_"+$(this).attr('data-id')).val()==""){
               empty_status = 1;
            }                
        });
        if(empty_status==1){
            alert("Please enter sundry required fields");
            return;
        }
        add_more_bill_sundry_up_count++;
        var $curRow = $("#append_tr").closest('tr');
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