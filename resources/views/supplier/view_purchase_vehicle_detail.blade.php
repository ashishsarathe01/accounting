@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
    input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
 .unchange_dropdown{
      pointer-events: none !important;
      touch-action: none !important;
   }
   .carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(0,0,0,0.5); /* semi-transparent black */
    border-radius: 50%;
    background-size: 100%, 100%;
}
#imageCarousel img {
    width: 100%;           /* fit the column width */
    height: 200px;         /* fixed height */
    object-fit: contain;   /* keep aspect ratio, no cropping */
    background: #f8f9fa;   /* light gray background so white images are visible */
    border-radius: 8px;
}
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">Pending</h5>               
               <a href="{{route('add-purchase-info')}}"><button class="btn btn-primary btn-sm d-flex align-items-center" >ADD</button></a>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table m-0 shadow-sm payment_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Gross Weight</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                    @foreach($pending_report as $key => $value)
                        <tr>
                            <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                            <td>{{$value->vehicle_no}}</td>
                            <td>{{$value->group_name}}</td>
                            <td>{{$value->account_name}}</td>
                            <td>{{$value->gross_weight}}</td>
                            <td class="w-min-120 text-center">
                                <a href="{{ URL::to('edit-purchase-info/' . $value->id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->id; ?>">
                                    <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                </button>
                                <img src="{{ URL::asset('public/assets/imgs/start.svg')}}" class="px-1 start" alt="" style="width: 30px;cursor:pointer;" id="start_btn_{{$value->id}}"  data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="0" data-vehicle_no="{{$value->vehicle_no}}" data-entry_date="{{$value->entry_date}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>">
                            </td>
                        </tr>
                    @endforeach
                     
                  </tbody>
               </table>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">In Process</h5>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table m-0 shadow-sm payment_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Gross Weight</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                    @foreach($in_process_report as $key => $value)
                        <tr>
                            <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                            <td>{{$value->vehicle_no}}</td>
                            <td>{{$value->group_name}}</td>
                            <td>{{$value->account_name}}</td>
                            <td>{{$value->gross_weight}}</td>
                            <td class="w-min-120 text-center">
                                 <button class="btn btn-info upload_image" data-id="{{$value->id}}">Click</button>
                                 <img src="{{ URL::asset('public/assets/imgs/start.svg')}}" class="px-1 start" alt="" style="width: 30px;cursor:pointer;" id="start_btn_{{$value->id}}" data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="1" data-vehicle_no="{{$value->vehicle_no}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>" data-entry_date="{{$value->entry_date}}">
                            </td>
                        </tr>
                    @endforeach
                     
                  </tbody>
               </table>
            </div>
            
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">Pending For Approval</h5>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table m-0 shadow-sm payment_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Gross Weight</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                    @foreach($pending_for_approval_report as $key => $value)
                        <tr>
                            <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                            <td>{{$value->vehicle_no}}</td>
                            <td>{{$value->group_name}}</td>
                            <td>{{$value->account_name}}</td>
                            <td>{{$value->gross_weight}}</td>
                            <td class="w-min-120 text-center">
                                <button class="btn btn-info start" data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="2" data-vehicle_no="{{$value->vehicle_no}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>" data-entry_date="{{$value->entry_date}}">View</button>
                            </td>
                        </tr>
                    @endforeach
                     
                  </tbody>
               </table>
            </div>
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">Approved</h5>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table m-0 shadow-sm payment_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Vehicle No.</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Group</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Account Name </th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Gross Weight</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                    @foreach($approved_report as $key => $value)
                        <tr>
                            <td>@if($value->entry_date) {{date('d-m-Y',strtotime($value->entry_date))}} @endif</td>
                            <td>{{$value->vehicle_no}}</td>
                            <td>{{$value->group_name}}</td>
                            <td>{{$value->account_name}}</td>
                            <td>{{$value->gross_weight}}</td>
                            <td class="w-min-120 text-center">
                                <button class="btn btn-info start" data-gross_weight="{{$value->gross_weight}}" data-account_id="{{$value->account_id}}" data-id="<?php echo $value->id; ?>" data-group_id="<?php echo $value->group_id; ?>" data-map_purchase_id="<?php echo $value->map_purchase_id; ?>" data-price="<?php echo $value->price; ?>" data-purchase_amount="<?php echo $value->purchase_amount; ?>" data-purchase_date="<?php echo $value->purchase_date; ?>" data-purchase_voucher_no="<?php echo $value->purchase_voucher_no; ?>" data-status="3" data-vehicle_no="{{$value->vehicle_no}}" data-purchase_qty="<?php echo $value->purchase_qty; ?>" data-entry_date="{{$value->entry_date}}">View</button>
                            </td>
                        </tr>
                    @endforeach                     
                  </tbody>
               </table>
            </div>
         </div>
         <!-- <div class="col-lg-1 d-flex justify-content-center">
            <div class="shortcut-key ">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 ">F1
                  <span class="ps-1 fw-normal text-body">Help</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F1</span>
                  <span class="ps-1 fw-normal text-body">Add Account</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F2</span>
                  <span class="ps-1 fw-normal text-body">Add Item</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  F3
                  <span class="ps-1 fw-normal text-body">Add Master</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F3</span>
                  <span class="ps-1 fw-normal text-body">Add Voucher</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F5</span>
                  <span class="ps-1 fw-normal text-body">Add Payment</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F6</span>
                  <span class="ps-1 fw-normal text-body">Add Receipt</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F7</span>
                  <span class="ps-1 fw-normal text-body">Add Journal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F8</span>
                  <span class="ps-1 fw-normal text-body">Add Sales</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 ">
                  <span class="border-bottom-black">F9</span>
                  <span class="ps-1 fw-normal text-body">Add Purchase</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">B</span>
                  <span class="ps-1 fw-normal text-body">Balance Sheet</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">T</span>
                  <span class="ps-1 fw-normal text-body">Trial Balance</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">S</span>
                  <span class="ps-1 fw-normal text-body">Stock Status</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">L</span>
                  <span class="ps-1 fw-normal text-body">Acc. Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">I</span>
                  <span class="ps-1 fw-normal text-body">Item Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">D</span>
                  <span class="ps-1 fw-normal text-body">Item Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">G</span>
                  <span class="ps-1 fw-normal text-body">GST Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">U</span>
                  <span class="ps-1 fw-normal text-body">Switch User</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">F</span>
                  <span class="ps-1 fw-normal text-body">Configuration</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="border-bottom-black">K</span>
                  <span class="ps-1 fw-normal text-body">Lock Program</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="ps-1 fw-normal text-body">Training Videos</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 ">
                  <span class="ps-1 fw-normal text-body">GST Portal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 ">
                  Search Menu
               </button>
            </div>
         </div> -->
      </div>
   </section>
</div>
<div class="modal fade" id="delete_subhead" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form  method="POST" action="{{route('delete-purchase-info')}}">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}"
                            alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body "> Do you really want to delete these records? this process
                        cannot be
                        undone. </p>
                </div>
                <input type="hidden" value="" id="delete_id" name="delete_id" />
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button  type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="report_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Report</h5>
            <br>
            <div class="row">
                <div class="mb-3 col-md-3">
                    <label for="account_id" class="form-label font-14 font-heading">Account Name</label>
                    <select id="account_id" class="form-select unchange_dropdown">
                        @foreach($accounts as $key => $value)
                            <option value="{{$value->id}}">{{$value->account_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 col-md-3">
                    <label for="entry_date" class="form-label font-14 font-heading">Date</label>
                    <input type="date" id="entry_date" class="form-control" readonly/>
                </div>
                <div class="mb-3 col-md-3">
                    <label for="item_group" class="form-label font-14 font-heading">Item Group</label>
                    <select id="group_id" class="form-select unchange_dropdown">
                        @foreach($item_groups as $key => $value)
                            <option value="{{$value->id}}">{{$value->group_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 col-md-3">
                    <label for="vehicle_no" class="form-label font-14 font-heading">Vehicle No.</label>
                    <input type="text" id="vehicle_no" class="form-control" readonly/>
                </div>
                <div class="mb-3 col-md-3">
                    <label for="tare_weight" class="form-label font-14 font-heading">Gross Weight</label>
                    <input type="text" id="gross_weight" class="form-control" readonly/>
                </div> 
                <div class="mb-3 col-md-3">
                    <label for="tare_weight" class="form-label font-14 font-heading">Tare Weight</label>
                    <input type="number" step="any" min="1" id="tare_weight" class="form-control" placeholder="Tare Weight"/>
                </div> 
                <div class="mb-3 col-md-3">
                    <label for="voucher_no" class="form-label font-14 font-heading">Slip Number</label>
                    <input type="text" id="voucher_no" class="form-control" placeholder="Slip Number"/>
                </div> 
                <div class="mb-3 col-md-3">
                    <label for="location" class="form-label font-14 font-heading">Area</label>
                    <select id="location" class="form-select">
                        <option value="">Select Area</option>
                        @foreach($locations as $loc)
                            <option value="{{$loc->id}}">{{$loc->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 col-md-3">
                    <a href="" id="bill_url"><button class="btn btn-info" style="margin-top: 28px;">Add Bill</button></a>
                </div>
                <div class="mb-12 col-md-12"></div>
                <div class="mb-3 col-md-3 purchase_div">
                    <label for="purchase_invoice_no" class="form-label font-14 font-heading">Purchase Invoice No.</label>
                    <input type="text" id="purchase_invoice_no" class="form-control" readonly/>
                </div>
                <div class="mb-3 col-md-3 purchase_div">
                    <label for="purchase_invoice_date" class="form-label font-14 font-heading">Purchase Invoice Date</label>
                    <input type="text" id="purchase_invoice_date" class="form-control" readonly/>
                </div>
                <div class="mb-3 col-md-3 purchase_div">
                    <label for="purchase_invoice_qty" class="form-label font-14 font-heading">Purchase Invoice Qty</label>
                    <input type="text" id="purchase_invoice_qty" class="form-control" readonly/>
                </div>
                <div class="mb-3 col-md-3 purchase_div">
                    <label for="purchase_invoice_amount" class="form-label font-14 font-heading">Purchase Invoice Amount</label>
                    <input type="text" id="purchase_invoice_amount" class="form-control" readonly/>
                </div>
                <br>
                <div class="mb-12 col-md-12">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Head</th>
                                <th id="net_weight_view" style="text-align: right"></th>
                                <input type="hidden" id="net_weight">
                                <input type="hidden" id="row_id">
                                <th style="text-align: right">Bill Rate</th>
                                <th style="text-align: right">Contract Rate</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody id="report_body">
                            @foreach($heads as $key => $value)
                                <tr class="head">
                                    <td><input type="text" class="form-control" value="{{$value->name}}" readonly></td>
                                    <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_{{$value->id}}" style="text-align: right" data-id="{{$value->id}}"></td>
                                    <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_{{$value->id}}" style="text-align: right" data-id="{{$value->id}}"></td>
                                    <td><input type="text" class="form-control contract_rate" id="contract_rate_{{$value->id}}" style="text-align: right" readonly data-id="{{$value->id}}"></td>
                                    <td><input type="text" class="form-control difference_amount" id="difference_amount_{{$value->id}}" data-id="{{$value->id}}" style="text-align: right" readonly></td>
                                </tr>
                            @endforeach
                            <tr id="fuel_row" style="display: none">
                                <td><input type="text" class="form-control" value="Fuel" readonly></td>
                                <td><input type="text" class="form-control calculate" placeholder="Enter Qty" id="qty_fuel" style="text-align: right" data-id="fuel"></td>
                                <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_fuel" style="text-align: right" data-id="fuel"></td>
                                <td><input type="text" class="form-control" id="contract_rate_fuel" style="text-align: right" readonly></td>
                                <td><input type="text" class="form-control" id="difference_amount_fuel" data-id="fuel" style="text-align: right" readonly></td>
                            </tr>
                            <tr id="cut_row">
                                <td><input type="text" class="form-control" value="Cut" readonly></td>
                                <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_cut" style="text-align: right" data-id="cut"></td>
                                <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_cut" style="text-align: right" data-id="cut"></td>
                                <td><input type="text" class="form-control" id="contract_rate_cut" style="text-align: right" readonly></td>
                                <td><input type="text" class="form-control difference_amount" id="difference_amount_cut" data-id="cut" style="text-align: right" readonly></td>
                            </tr>
                            <tr id="short_weight_row">
                                <td><input type="text" class="form-control" value="Short Weight" readonly></td>
                                <td><input type="text" class="form-control calculate" readonly id="qty_short_weight" style="text-align: right" data-id="short_weight"></td>
                                <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_short_weight" style="text-align: right" data-id="short_weight"></td>
                                <td><input type="text" class="form-control" id="contract_rate_short_weight" style="text-align: right" readonly></td>
                                <td><input type="text" class="form-control difference_amount" id="difference_amount_short_weight" data-id="short_weight" style="text-align: right" readonly></td>
                            </tr>
                            <tr >
                                <td></td>
                                <td></td>
                                <td></td>
                                <th style="text-align: right">Difference</th>
                                <th><input type="text" class="form-control" id="difference_total_amount" style="text-align: right" readonly></th>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-4">
                     <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner image_div">
                           
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                           <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                           <span class="carousel-control-next-icon"></span>
                        </button>
                     </div>
                  </div>
            </div>
            <br>
            <div class="text-start">
                <button type="button" class="btn  btn-xs-primary save_location">
                    SAVE
                </button>
                <button class="btn btn-success approve" style="display: none">Approve</button>
                {{-- <button class="btn btn-success approve" style="display: none">Edit</button> --}}
                        {{-- <button class="btn btn-danger reject" data-id="">Reject</button> --}}
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="imageUploadModal" tabindex="-1" aria-labelledby="imageUploadLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3 border-radius-8">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="imageUploadLabel">Upload Images</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        {{-- Upload Form --}}
        <form action="{{ route('upload-purchase-image') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div id="image-inputs">
            <div class="mb-3 d-flex align-items-center">
              <input type="file" name="images[]" class="form-control me-2 image-input" accept="image/*" required>
              <button type="button" class="btn btn-sm btn-primary add-more">+</button>
            </div>
          </div>
          <input type="hidden" name="image_purchase_id" id="image_purchase_id">
          {{-- Live Preview Section --}}
          <div id="preview" class="row g-3"></div>
          <div class="text-end mt-3">
            <button type="submit" class="btn btn-success">Upload</button>
          </div>
        </form>

        {{-- Display Uploaded Images --}}
        @if(session('images'))
          <div class="mt-4">
            <p class="text-success">Images uploaded successfully!</p>
            <div class="row g-3">
              @foreach(session('images') as $img)
                <div class="col-md-4">
                  <img src="{{ asset('storage/' . $img) }}" class="img-fluid rounded border">
                </div>
              @endforeach
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
</body>
@include('layouts.footer')
<script>
    let open_id = "{{ request('id') }}";
    var modal_open_status = 0;
    $(document).ready(function(){
        if(open_id!=""){
            $("#start_btn_"+open_id).trigger('click');
        }
    })
    $(".delete").click(function() {
        var id = $(this).attr("data-id");
        $("#delete_id").val(id);
        $("#delete_subhead").modal("show");
    });
    $(".cancel").click(function() {
        $("#delete_subhead").modal("hide");
    });
    $(".start").click(function(){
        
        $("#imageCarousel").hide();
       
        $(".approve").hide();
        let id = $(this).attr("data-id");
        let account_id = $(this).attr('data-account_id');
        let group_id = $(this).attr('data-group_id');
        let gross_weight = $(this).attr('data-gross_weight');
        let map_purchase_id = $(this).attr('data-map_purchase_id');
        let purchase_voucher_no = $(this).attr('data-purchase_voucher_no');
        let purchase_date = $(this).attr('data-purchase_date');
        let purchase_amount = $(this).attr('data-purchase_amount');
        let purchase_qty = $(this).attr('data-purchase_qty');
        let price = $(this).attr('data-price');
        let status = $(this).attr('data-status');
        let vehicle_no = $(this).attr('data-vehicle_no');
        let entry_date = $(this).attr('data-entry_date');
        $(".qty").attr('readonly',false);
        if(status==3){
            $(".qty").attr('readonly',true);
        }
        $(".approve").attr('data-id',id);        
        $("#account_id").val(account_id);
        $("#account_id").attr('data-id',id);  
        $("#entry_date").attr('data-id',id);
        $("#row_id").val(id);
        $("#gross_weight").val(gross_weight);
        $("#vehicle_no").val(vehicle_no);
        $("#group_id").val(group_id);
        $("#entry_date").val(entry_date);
        $(".purchase_div").hide();

        if((status==0 || status==1) && purchase_amount=="" && modal_open_status==0){
            $("#voucher_no").val('');
            $(".qty").val('');
            $("#qty_short_weight").val('');
            $(".contract_rate").val('');
            $("#contract_rate_short_weight").val('');
            $(".difference_amount").val('');
            $("#difference_amount_short_weight").val('');
            $("#difference_total_amount").val('');
            $("#tare_weight").val('');
        }else if(modal_open_status==1){
            $(".contract_rate").val('');
        }
        $("#entry_date").attr('readonly',true);
        $("#vehicle_no").attr('readonly',true);
        $("#group_id").addClass('unchange_dropdown');
        $("#account_id").addClass('unchange_dropdown');
        if((status==0 || status==1) && purchase_amount==""){
            $("#entry_date").attr('readonly',false);
            $("#vehicle_no").attr('readonly',false);
            $("#group_id").removeClass('unchange_dropdown');
            $("#account_id").removeClass('unchange_dropdown');
        }
        if(map_purchase_id!=""){
            $("#bill_url").hide();
            $(".bill_rate").val(price);
            $("#purchase_invoice_no").val(purchase_voucher_no);
            $("#purchase_invoice_date").val(purchase_date);
            $("#purchase_invoice_amount").val(purchase_amount);
            $("#purchase_invoice_qty").val(purchase_qty);
            $("#bill_url").attr('href','');
            $(".purchase_div").show();
        }else{
            let bill_url = "{{url('purchase/create?row_id=row_id_value&account_id=account_id_value&group_id=group_id_value')}}";
            bill_url = bill_url.replace('row_id_value',id);
            bill_url = bill_url.replace('account_id_value',account_id);
            bill_url = bill_url.replace('group_id_value',group_id);
            bill_url = bill_url.replace(/&amp;/g, "&");
            $("#bill_url").attr('href',bill_url);
            $("#bill_url").show();
        }
        $.ajax({
            url : "{{url('get-location-by-supplier')}}",
            method : "POST",
            data: {
                _token: '<?php echo csrf_token() ?>',
                account_id : account_id
            },
            success:function(res){
                
                location_list = "<option value=''>Select Area</option>";
                if(res.location.length>0){
                    location_arr = res.location;
                    res.location.forEach(function(e){
                        location_list+="<option value="+e.id+">"+e.name+"</option>";
                    });
                }
                $("#location").html(location_list);
                //Get Store All Data
                $.ajax({
                    url:"{{url('view-complete-purchase-info/')}}/"+id,
                    type:"POST",
                    data:{_token:'{{csrf_token()}}'},
                    success:function(res){
                        if(res!=""){
                            let obj = JSON.parse(res);
                            if(obj.purchase==null){
                                if(modal_open_status==0){
                           
                            $("#report_modal").modal('toggle');
                        }else if(modal_open_status==1){
                            modal_open_status = 0;
                        }
                                return;
                            }
                            let head_data_arr = [];
                            $("#difference_total_amount").val(obj.purchase.difference_total_amount);
                            $("#voucher_no").val(obj.purchase.voucher_no);
                            if(modal_open_status==0){                                
                                $("#location").val(obj.purchase.location);
                            }                           
                            $("#tare_weight").val(obj.purchase.tare_weight);
                            let gross_weight = $("#gross_weight").val();
                            let tare_weight = $("#tare_weight").val();
                            let net_weight = parseFloat(gross_weight) - parseFloat(tare_weight);
                            
                            $("#net_weight").val(net_weight);
                            $("#net_weight_view").html("Net Weight : "+net_weight);
                            obj.reports.forEach(element => {
                                head_data_arr[element.head_id] = element;
                            });
                            let purchase_image = "";
                            $(".image_div").html(purchase_image);
                            if(obj.purchase.image_1!="" && obj.purchase.image_1!=null){
                                let img_url = "{{ asset("public/image_name") }}";
                                img_url = img_url.replace("image_name", obj.purchase.image_1); 
                                img_url = img_url.replace("images_names", obj.purchase.image_1); 
                                //purchase_image+=img_url;
                                purchase_image+='<div class="carousel-item active"><a href="'+img_url+'" target="_blank"><img src="'+img_url+'" class="d-block w-100 rounded"></a></div>';
                            }
                            if(obj.purchase.image_2!="" && obj.purchase.image_2!=null){
                                let img_url = "{{ asset("public/image_name") }}";
                                img_url = img_url.replace("image_name", obj.purchase.image_2); 
                                img_url = img_url.replace("images_names", obj.purchase.image_2); 
                                //purchase_image+=img_url;
                                purchase_image+='<div class="carousel-item"><a href="'+img_url+'" target="_blank"><img src="'+img_url+'" class="d-block w-100 rounded"></a></div>';
                            }
                            if(obj.purchase.image_3!="" && obj.purchase.image_3!=null){
                                let img_url = "{{ asset("public/image_name") }}";
                                img_url = img_url.replace("image_name", obj.purchase.image_3); 
                                img_url = img_url.replace("images_names", obj.purchase.image_3); 
                                //purchase_image+=img_url;
                                purchase_image+='<div class="carousel-item"><a href="'+img_url+'" target="_blank"><img src="'+img_url+'" class="d-block w-100 rounded"></a></div>';
                            }
                            $(".image_div").html(purchase_image);
                            $("#imageCarousel").show();
                            if(purchase_image!=""){
                                $(".approve").show();
                            }
                            
                            $(".qty").each(function(){
                                let id = $(this).attr('data-id');
                                if(head_data_arr[id]){
                                    $("#qty_"+id).val(head_data_arr[id].head_qty);
                                    if(status!=1){
                                        $("#bill_rate_"+id).val(head_data_arr[id].head_bill_rate);
                                    }
                                    if(modal_open_status==0){
                                        $("#contract_rate_"+id).val(head_data_arr[id].head_contract_rate);
                                        $("#difference_amount_"+id).val(head_data_arr[id].head_difference_amount);
                                    }
                                    
                                    
                                }
                            });
                            let short_weight_id = "short_weight";
                            if(head_data_arr[short_weight_id]){
                                $("#qty_"+short_weight_id).val(head_data_arr[short_weight_id].head_qty);
                                if(status!=1){
                                    $("#bill_rate_"+short_weight_id).val(head_data_arr[short_weight_id].head_bill_rate);
                                } 
                                
                                $("#contract_rate_"+short_weight_id).val(head_data_arr[short_weight_id].head_contract_rate);
                                $("#difference_amount_"+short_weight_id).val(head_data_arr[short_weight_id].head_difference_amount);
                            }
                            $(".calculate").each(function(){
                                $(this).keyup();
                            });

                            if(status==3){
                                $(".save_location").hide();
                            }
                            // $("#bill_url").hide();                            
                            if(modal_open_status==0){
                            
                                $("#report_modal").modal('toggle');
                            }else if(modal_open_status==1){
                                modal_open_status = 0;
                            }
                        }
                        
                    }
                });
               
                
            }
        });
    });
    $("#location").change(function(){
        var loc_id = $(this).val();
        var account_id = $("#account_id").val();
        if(loc_id != ''){
            $.ajax({
                url:"{{url('get-supplier-rate-by-location')}}",
                type:"POST",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "location": loc_id,
                    "account_id": account_id,
                    "date":$("#entry_date").val(),
                },
                success:function(res){
                    if(res == null){
                        $(".contract_rate").each(function(){
                            if(rate_arr[$(this).attr('data-id')]){
                                $(this).val('');
                            }
                        });
                        return;
                    }
                    if(res!=""){
                        if(res.length>0){
                            let rate_arr = [];
                            res.forEach(function(e){
                                rate_arr[e.head_id] = e.head_rate;
                            });
                            $(".contract_rate").each(function(){
                                if(rate_arr[$(this).attr('data-id')]){
                                    $(this).val(rate_arr[$(this).attr('data-id')]);
                                }
                            });
                        }
                    }                        
                    $("#contract_rate_cut").val(0);
                    $("#contract_rate_short_weight").val(0);

                    $(".calculate").each(function(){
                        $(this).keyup();
                    });
                }
            });
        }
    });
    $(".calculate").keyup(function(){
        let short_weight = 0;
        let qty_weight = 0;
        let net_weight = $("#net_weight").val();
        if(net_weight==""){
            net_weight = 0;
        }
        let purchase_invoice_qty = $("#purchase_invoice_qty").val();
        if(purchase_invoice_qty==""){
            purchase_invoice_qty = 0;
        }
        $(".qty").each(function(){
            if($(this).val()!=""){
                qty_weight = parseFloat(qty_weight) + parseFloat($(this).val());
            }
        })
        short_weight = parseFloat(purchase_invoice_qty) - parseFloat(net_weight);
        $("#qty_short_weight").val(short_weight);
        let bill_rate_short_weight = $("#bill_rate_short_weight").val();
        if(bill_rate_short_weight==undefined || bill_rate_short_weight==""){
            bill_rate_short_weight = 0;
        }
        $("#difference_amount_short_weight").val(parseFloat(short_weight)*parseFloat(bill_rate_short_weight));
        
        var id = $(this).data('id');
        var qty = $("#qty_"+id).val();
        var bill_rate = $("#bill_rate_"+id).val();
        var contract_rate = $("#contract_rate_"+id).val();
        if(qty == ''){
            qty = 0;
        }
        if(bill_rate == ''){
            bill_rate = 0;
        }
        if(contract_rate == ''){
            contract_rate = 0;
        }
        let diff_rate = bill_rate - contract_rate;
        var difference_amount = parseFloat(qty) * parseFloat(diff_rate);
        $("#difference_amount_"+id).val(difference_amount.toFixed(2));
        calculateTotalDifference();
    });
    function calculateTotalDifference(){
        var total = 0;
        $(".difference_amount").each(function(){
            var val = $(this).val();
            var id = $(this).attr('data-id');
            if(val == ''){
                val = 0;
            }
            total = parseFloat(total) + parseFloat(val);
        });
        $("#difference_total_amount").val(total.toFixed(2));
    }
    $("#tare_weight").keyup(function(){
        $("#net_weight").val('');
        $("#net_weight_view").html("");
        let gross_weight = $("#gross_weight").val();
        let tare_weight = $("#tare_weight").val();
        let net_weight = parseFloat(gross_weight) - parseFloat(tare_weight);
        if(net_weight<0){
            alert("Please Enter Valid Tare Weight");
            return;
        }
        $("#net_weight").val(net_weight);
        $("#net_weight_view").html("Net Weight : "+net_weight);
        $(".calculate").each(function(){
            $(this).keyup();
        });
    });
    $(".save_location").click(function(){
        var id = $("#row_id").val();
        var location_id = $("#location").val();
        if(location_id == ''){
            alert("Please select area");
            return;
        }
        var voucher_no = $("#voucher_no").val();
        if(voucher_no == ''){
            alert("Please enter Slip Number");
            return;
        }
        var purchase_id = $("#row_id").val();
        if(purchase_id == ''){
            alert("Purchase id not found");
            return;
        }
        let tare_weight = $("#tare_weight").val();
        if(tare_weight == ''){
            alert("Please enter tare weight");
            return;
        }
        let qty_total = 0;
        $(".qty").each(function(){
            if($(this).val()!=""){
                qty_total = parseFloat(qty_total) + parseFloat($(this).val());
            }
        });
        let net_weight = $("#net_weight").val();
        if(parseFloat(qty_total)!=parseFloat(net_weight)){
            alert("Total head quantity must be equal to net quantity.")
            return;
        }
        let arr = [];
        $(".bill_rate").each(function(){
            arr.push({'id':$(this).attr('data-id'),'contract_rate':$("#contract_rate_"+$(this).attr('data-id')).val(),'bill_rate':$(this).val(),'qty':$("#qty_"+$(this).attr('data-id')).val(),'difference_amount':$("#difference_amount_"+$(this).attr('data-id')).val()});
        });
        let account_id = $("#account_id").val();
        let entry_date = $("#entry_date").val();
        let group_id = $("#group_id").val();
        let vehicle_no = $("#vehicle_no").val();
        var data = {
            "voucher_no": voucher_no,
            "location": location_id,
            "purchase_id": purchase_id,
            "tare_weight": tare_weight,
            "account_id": account_id,
            "entry_date": entry_date,
            "group_id": group_id,
            "vehicle_no": vehicle_no,
            "data":JSON.stringify(arr),
            "difference_total_amount": $("#difference_total_amount").val(),
            "_token": "{{ csrf_token() }}"
        };
        $.ajax({
            url:"{{url('store-supplier-purchase-report')}}",
            type:"POST",
            data:data,
            success:function(res){
                response = JSON.parse(res);
                if(response.status == true){
                    alert(response.message);
                    window.location = "manage-purchase-info";
                }else{
                    alert(response.message);
                    
                }
            }
        });
    });
    $(".upload_image").click(function(){
        let id = $(this).attr('data-id');
        $("#image_purchase_id").val(id)
        $("#imageUploadModal").modal('toggle');
    });
    var image_count  = 1;
    document.addEventListener("DOMContentLoaded", function() {
        const imageInputs = document.getElementById("image-inputs");
        const preview = document.getElementById("preview");
        // Add & Remove file inputs
        imageInputs.addEventListener("click", function(e) {
            if (e.target.classList.contains("add-more")) {
                if(image_count==3){
                    alert('Upload Max 3 Image')
                    return;
                }
                image_count++;
                let newInput = `
                <div class="mb-3 d-flex align-items-center">
                <input type="file" name="images[]" class="form-control me-2 image-input" accept="image/*" required>
                <button type="button" class="btn btn-sm btn-danger remove">-</button>
                </div>`;
                e.target.closest(".mb-3").insertAdjacentHTML("afterend", newInput);
            }
            if (e.target.classList.contains("remove")) {
                image_count--;
                e.target.closest(".mb-3").remove();
                refreshPreview(); // refresh preview after removing
            }
        });
        // Live preview for selected images
        imageInputs.addEventListener("change", function(e) {
            if (e.target.classList.contains("image-input")) {
                refreshPreview();
            }
        });
        function refreshPreview() {
            preview.innerHTML = ""; // clear old previews
            const files = document.querySelectorAll(".image-input");
            files.forEach(input => {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        let col = document.createElement("div");
                        col.className = "col-md-3";
                        col.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded border">`;
                        preview.appendChild(col);
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            });
        }
    });
    $(".approve").click(function(){
        if(confirm("Are you sure to approve purchase ?")==true){
            let id = $(this).attr('data-id');
            $.ajax({
                url:"{{url('approve-purchase-report')}}",
                type:"POST",
                data:{
                    "_token": "{{ csrf_token() }}",
                    "purchase_id": id,
                },
                success:function(res){
                    if(res!=''){
                        let obj = JSON.parse(res);
                        if(obj.status==1){
                            alert(obj.message);
                            location.reload();
                        }else{
                            alert("Something went wrong");
                        }
                    }
                }
            });
        }
    });
    $(".qty").keyup(function(){
        let qty_total = 0;
        $(".save_location").show();
        $(".qty").each(function(){
            if($(this).val()!=""){
                qty_total = parseFloat(qty_total) + parseFloat($(this).val());
            }
        });
        let net_weight = $("#net_weight").val();
        if(parseFloat(qty_total)!=parseFloat(net_weight)){
            $(".save_location").hide();
        }
    })
    $("#account_id").change(function(){
        modal_open_status = 1;
        $("#start_btn_"+$(this).attr('data-id')).attr('data-account_id',$(this).val());
       $("#start_btn_"+$(this).attr('data-id')).click();
    });
    $("#entry_date").change(function(){
        modal_open_status = 1;
        $("#start_btn_"+$(this).attr('data-id')).attr('data-entry_date',$(this).val());
        $("#start_btn_"+$(this).attr('data-id')).click();
    });
    
</script>
@endsection