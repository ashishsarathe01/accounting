@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif         
            @php
               $status = request()->get('status');
            @endphp   
            {{-- =================== PENDING SALES ORDER =================== --}}
            @if($status==0 || $status=="")
                @can('action-module',126)
                  <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                     <h5 class="transaction-table-title m-0 py-2">Pending Sales Order</h5>
                     @can('action-module',122)
                     <a href="{{ route('sale-order.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none">
                           <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white"/>
                        </svg>
                     </a>
                     @endcan
                     
                     <!--<a class="btn btn-info" href="{{ route('set-sale-order') }}"style="margin-left: 5px;">Set Sale Order </a>-->
                     <div class="d-md-flex d-block">
                        <select class="form-select serach_by_status">
                           <option value="0" @if($status==0 || $status=="") selected @endif>Pending</option>
                           <option value="3" @if($status==3) selected @endif>In Process</option>
                           <option value="4" @if($status==4) selected @endif>Ready To Dispatch</option>
                           <option value="1" @if($status==1) selected @endif>Completed</option>
                           <option value="2" @if($status==2) selected @endif>Cancelled</option>
                        </select>
                     </div>
                     <div class="d-md-flex d-block">
                        <input type="text" id="search_pending" class="form-control" placeholder="Search Pending">
                     </div>

                     
                  </div>
                  <div class="transaction-table bg-white table-view shadow-sm mb-5">
                     <table class="table-striped table m-0 shadow-sm sale_table_pending">
                        <thead>
                           <tr class="font-12 text-body bg-light-pink">
                              <th style="width: 9%;">Date</th>
                              <th>Sale Order No.</th>
                              <th>Purchase Order No.</th>
                              <th>Purchase Order Date</th>
                              <th>Bill To</th>
                              <th>Shipp To</th>
                              <th style="text-align: right;">Freight</th>
                              <th style="text-align: right;">Created By</th>
                              <th class="w-min-120 text-center">Action</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($pendingOrders as $value)
                              <tr>
                                 <td class="date_column">{{ date('d-m-Y', strtotime($value->created_at)) }}</td>
                                 <td>{{ $value->sale_order_no }}</td>
                                 <td>{{ $value->purchase_order_no }}</td>
                                 <td>@if($value->purchase_order_date) {{ date('d-m-Y', strtotime($value->purchase_order_date)) }} @endif</td>
                                 <td>{{ $value->billTo->account_name }}</td>
                                 <td>{{ $value->shippTo->account_name }}</td>
                                 <td style="text-align: right;">{{ $value->freight == 1 ? 'YES' : 'NO' }}</td>
                                 <td style="text-align: right;">{{ $value->createdByUser->name ?? '' }}</td>
                                 <td style="text-align: center;">
                                    @can('action-module',123)
                                    <a href="{{ URL::to('sale-order/'.$value->id.'/edit') }}"><img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1" alt=""></a>
                                    @endcan
                                    @can('action-module',124)
                                    <button type="button" class="border-0 bg-transparent delete"   data-id="<?php echo $value->id;?>"><img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt=""></button>
                                    @endcan
                                    <a href="{{ route('sale-order.show', $value->id) }}"><img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt=""></a>
                                    @can('action-module',125)
                                    {{-- <a href="{{ route('sale-order-start', $value->id) }}"><img src="{{ asset('public/assets/imgs/start.svg') }}" class="px-1 start" alt="" style="width: 30px;cursor:pointer;"></a> --}}
                                    <img src="{{ asset('public/assets/imgs/start.svg') }}" class="px-1 start_status" data-id="{{$value->id}}" alt="" style="width: 30px;cursor:pointer;">
                                    @endcan
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mt-4">
               <h5 class="transaction-table-title m-0 py-2">Pending Sales Order Summary</h5>

               <div class="d-md-flex d-block">
                  <input type="text" id="search_summary" class="form-control" placeholder="Search Summary">
               </div>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm mb-5">
               <table class="table-striped table m-0 shadow-sm sale_table_summary">
                  <thead>
                     <tr>
                        <th style="width:5%">S No</th>
                        <th>Item</th>
                        <th style="text-align:right;">Total KG</th>
                        <th style="text-align:right;">Total Reel</th>
                     </tr>
                  </thead>
                  <tbody>
                      @php $total_summary_qty = 0; $total_summary_reel = 0; @endphp
                     @forelse($summary as $index => $row)
                     <tr class="summary-row" data-item-id="{{ $row->item_id }}" style="cursor:pointer;">
                        <td>{{ $index + 1 }}</td>
                        <td class="text-primary fw-bold">{{ $row->item_name }}</td>
                        <td style="text-align:right;">
                           {{ formatIndianNumber(round($row->total_kg_raw / 100) * 100, 2) }}
                           @php $total_summary_qty = $total_summary_qty + round($row->total_kg_raw / 100) * 100; @endphp
                        </td>
                        <td style="text-align:right;">
                           {{ round($row->total_reel_raw) }}
                           @php $total_summary_reel = $total_summary_reel + round($row->total_reel_raw); @endphp
                        </td>
                     </tr>
                     
                     @empty
                        <tr>
                              <td colspan="4" class="text-center">No Data Found</td>
                        </tr>
                     @endforelse
                     <tr>
                         <th></th>
                         <th style="text-align:right;">Total</th>
                         <th style="text-align:right;"> @php echo formatIndianNumber($total_summary_qty); @endphp</th>
                         <th style="text-align:right;"> @php echo $total_summary_reel; @endphp</th>
                     </tr>
                  </tbody>
               </table>
            </div>
                  
                @endcan
               @endif
               
            @if($status==1)
               @can('action-module',127)
                  {{-- =================== COMPLETED SALES ORDER =================== --}}
                  <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                     <h5 class="transaction-table-title m-0 py-2">Completed Sales Order</h5>

                     <form action="{{ route('sale-order.index') }}" method="GET">
                        @csrf
                        <div class="d-md-flex d-block">
                           <div class="calender-administrator my-2 my-md-0">
                              <input type="date" id="from_date_completed" class="form-control calender-bg-icon calender-placeholder" name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                           </div>
                           <div class="calender-administrator ms-md-4">
                              <input type="date" id="to_date_completed" class="form-control calender-bg-icon calender-placeholder" name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                           </div>
                           <input type="hidden" name="status" value="{{$status}}">
                           <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                        </div>
                     </form>
                     <div class="d-md-flex d-block">
                        <select class="form-select serach_by_status">
                           <option value="0" @if($status==0 || $status=="") selected @endif>Pending</option>
                           <option value="3" @if($status==3) selected @endif>In Process</option>
                           <option value="4" @if($status==4) selected @endif>Ready To Dispatch</option>
                           <option value="1" @if($status==1) selected @endif>Completed</option>
                           <option value="2" @if($status==2) selected @endif>Cancelled</option>
                        </select>
                     </div>
                     <div class="d-md-flex d-block">
                        <input type="text" id="search_completed" class="form-control" placeholder="Search Completed">
                     </div>
                  </div>
                  <div class="transaction-table bg-white table-view shadow-sm">
                     <table class="table-striped table m-0 shadow-sm sale_table_completed">
                        <thead>
                           <tr class="font-12 text-body bg-light-pink">
                              <th style="width: 9%;">Date</th>
                              <th>Sale Order No.</th>
                              <th style="width: 12%;">Sale Invoice No.</th>
                              <th>Purchase Order No.</th>
                              <th>Purchase Order Date</th>
                              <th>Bill To</th>
                              <th>Shipp To</th>
                              <th style="text-align: right;">Freight</th>
                              <th style="text-align: right;">Completed By</th>
                              <th class="w-min-120 text-center">Action</th>
                           </tr>
                        </thead>
                        <tbody>
                           @foreach ($completedOrders as $value)
                              <tr>
                                 <td class="date_column">{{ date('d-m-Y', strtotime($value->sale->date)) }}</td>
                                 <td>{{ $value->sale_order_no }}</td>
                                 <td>{{ $value->sale->voucher_no_prefix }}</td>
                                 <td>{{ $value->purchase_order_no }}</td>
                                 <td>@if($value->purchase_order_date) {{ date('d-m-Y', strtotime($value->purchase_order_date)) }} @endif</td>
                                 <td>{{ $value->billTo->account_name }}</td>
                                 <td>{{ $value->shippTo->account_name }}</td>
                                 <td style="text-align: right;">{{ $value->freight == 1 ? 'YES' : 'NO' }}</td>
                                 <td style="text-align: right;">{{ $value->updatedByUser->name ?? '' }}</td>
                                 <td style="text-align: center;">
                                    @if($value->sale->e_invoice_status==0)
                                       <a href="{{ URL::to('sale-order/'.$value->id.'/edit') }}?sale_id={{$value->sale->id}}"><img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1" alt=""></a>
                                    @endif
                                    <a href="{{ url('sale-invoice/' . $value->sale->id) }}?source=sale" target="_blank">
                                       <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="View Invoice">
                                    </a>
                                 </td>
                              </tr>
                           @endforeach
                        </tbody>
                     </table>
                  </div>
                  @endcan
               @endif
            @if($status==2)
               @can('action-module',128)
               {{-- =================== CANCELLED SALES ORDER =================== --}}
               <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                  <h5 class="transaction-table-title m-0 py-2">Cancelled Sales Order</h5>

                  <form action="{{ route('sale-order.index') }}" method="GET">
                     @csrf
                     <div class="d-md-flex d-block">
                        <div class="calender-administrator my-2 my-md-0">
                           <input type="date" id="from_date_completed" class="form-control calender-bg-icon calender-placeholder" name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                        </div>
                        <div class="calender-administrator ms-md-4">
                           <input type="date" id="to_date_completed" class="form-control calender-bg-icon calender-placeholder" name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                        </div>
                        <input type="hidden" name="status" value="{{$status}}">
                        <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                     </div>
                  </form>
                  <div class="d-md-flex d-block">
                     <select class="form-select serach_by_status">
                         <option value="0" @if($status==0 || $status=="") selected @endif>Pending</option>
                         <option value="3" @if($status==3) selected @endif>In Process</option>
                        <option value="4" @if($status==4) selected @endif>Ready To Dispatch</option>
                        <option value="1" @if($status==1) selected @endif>Completed</option>
                        <option value="2" @if($status==2) selected @endif>Cancelled</option>
                     </select>
                  </div>
                  <div class="d-md-flex d-block">
                     <input type="text" id="search_completed" class="form-control" placeholder="Search Cancelled">
                  </div>
               </div>
               <div class="transaction-table bg-white table-view shadow-sm">
                  <table class="table-striped table m-0 shadow-sm sale_table_completed">
                     <thead>
                        <tr class="font-12 text-body bg-light-pink">
                           <th style="width: 9%;">Date</th>
                           <th>Sale Order No.</th>
                           <th>Purchase Order No.</th>
                           <th>Purchase Order Date</th>
                           <th>Bill To</th>
                           <th>Shipp To</th>
                           <th style="text-align: right;">Freight</th>
                           <th style="text-align: right;">Cancelled By</th>
                           <th class="w-min-120 text-center" style="width:13%">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($cancelledOrders as $value)
                           <tr>
                              <td class="date_column">{{ date('d-m-Y', strtotime($value->created_at)) }}</td>
                              <td>{{ $value->sale_order_no }}</td>
                              <td>{{ $value->purchase_order_no }}</td>
                              <td>@if($value->purchase_order_date) {{ date('d-m-Y', strtotime($value->purchase_order_date)) }} @endif</td>
                              <td>{{ $value->billTo->account_name }}</td>
                              <td>{{ $value->shippTo->account_name }}</td>
                              <td style="text-align: right;">{{ $value->freight == 1 ? 'YES' : 'NO' }}</td>
                              <td style="text-align: right;">{{ $value->updatedByUser->name ?? '' }}</td>
                              <td style="text-align: center;">
                              <a href="{{ route('sale-order.show', $value->id) }}"><img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt=""></a>
                                 @can('action-module',129)
                              <img src="{{ asset('public/assets/imgs/return-button.svg') }}" class="px-1 convert_in_pending" alt="" style="width: 20%;cursor:pointer" title="Convert In Pending" data-id="{{$value->id}}">
                              @endcan
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
               @endcan
            @endif
            @if($status==4)
               @can('action-module',128)
               {{-- =================== READYTODISPATCH SALES ORDER =================== --}}
               <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                  <h5 class="transaction-table-title m-0 py-2">Ready To Dispatch Order</h5>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block"></div>
                  <div class="d-md-flex d-block">
                     <select class="form-select serach_by_status" style="    width: 170px;">
                         <option value="0" @if($status==0 || $status=="") selected @endif>Pending</option>
                         <option value="3" @if($status==3) selected @endif>In Process</option>
                         <option value="4" @if($status==4) selected @endif>Ready To Dispatch</option>
                        <option value="1" @if($status==1) selected @endif>Completed</option>
                        <option value="2" @if($status==2) selected @endif>Cancelled</option>
                     </select>
                  </div>
                  <div class="d-md-flex d-block">
                     <input type="text" id="search_completed" class="form-control" placeholder="Search Ready To Dispatch">
                  </div>
               </div>
               <div class="transaction-table bg-white table-view shadow-sm">
                  <table class="table-striped table m-0 shadow-sm sale_table_completed">
                     <thead>
                        <tr class="font-12 text-body bg-light-pink">
                           <th style="width: 9%;">Date</th>
                           <th>Sale Order No.</th>
                           <th>Purchase Order No.</th>
                           <th>Purchase Order Date</th>
                           <th>Bill To</th>
                           <th>Shipp To</th>
                           <th style="text-align: right;">Freight</th>
                           <th style="text-align: right;">Cancelled By</th>
                           <th class="w-min-120 text-center" style="width:13%">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        @foreach ($readyToDispatchOrders as $value)
                           <tr>
                              <td class="date_column">{{ date('d-m-Y', strtotime($value->created_at)) }}</td>
                              <td>{{ $value->sale_order_no }}</td>
                              <td>{{ $value->purchase_order_no }}</td>
                              <td>@if($value->purchase_order_date) {{ date('d-m-Y', strtotime($value->purchase_order_date)) }} @endif</td>
                              <td>{{ $value->billTo->account_name }}</td>
                              <td>{{ $value->shippTo->account_name }}</td>
                              <td style="text-align: right;">{{ $value->freight == 1 ? 'YES' : 'NO' }}</td>
                              <td style="text-align: right;">{{ $value->updatedByUser->name ?? '' }}</td>
                              <td style="text-align: center;">
                              <a href="{{ route('sale-order.show', $value->id) }}"><img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt=""></a>
                              
                                 <img src="{{ asset('public/assets/imgs/return-button.svg') }}" class="px-1 convert_in_set_quantity" alt="" style="width: 20%;cursor:pointer" title="Convert In Set Quantity" data-id="{{$value->id}}">
                              
                              @can('action-module',125)
                                 <a href="{{ route('sale-order-start', $value->id) }}"><img src="{{ asset('public/assets/imgs/start.svg') }}" class="px-1 start" alt="" style="width: 30px;cursor:pointer;"></a>
                              @endcan
                              </td>
                           </tr>
                        @endforeach
                     </tbody>
                  </table>
               </div>
               @endcan
            @endif
         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key ">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 ">
                  F1
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
         </div>
      </div>
   </section>
</div>
<!-- Modal ---for delete ---------------------------------------------------------------icon-->
<div class="modal fade" id="delete_sale" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="{{ route('sale-order.delete') }}">
            @csrf
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone. </p>
            </div>
            <input type="hidden" value="" id="sale_id" name="sale_id" />
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal fade" id="summaryDetailModal" tabindex="-1">
   <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content border-radius-8">

         <!-- Header -->
         <div class="modal-header bg-light-pink">
            <h5 class="modal-title fw-bold" id="modalItemTitle">Item Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <!-- Body -->
         <div class="modal-body">

            <div class="table-responsive">
               <table class="table table-bordered table-striped m-0">
                  <thead class="bg-light">
                     <tr>
                        <th>Date</th>
                        <th>SO No</th>
                        <th>PO No</th>
                        <th>Bill To</th>
                        <th class="text-end">KG</th>
                        <th class="text-end">Reel</th>
                     </tr>
                  </thead>

                  <tbody id="summaryDetailBody">
                     <!-- Data will come here -->
                     <tr>
                        <td colspan="6" class="text-center">Click on item to load data</td>
                     </tr>
                  </tbody>

                  <tfoot>
                     <tr class="fw-bold bg-light">
                        <td colspan="4" class="text-end">Total</td>
                        <td class="text-end" id="modalTotalKg">0.00</td>
                        <td class="text-end" id="modalTotalReel">0</td>
                     </tr>
                  </tfoot>
               </table>
            </div>

         </div>

      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
      

      $(".cancel").click(function() {
         $("#delete_sale").modal("hide");
      });
      $("#pan").change(function() {
         var inputvalues = $("#pan").val();
         var paninformat = new RegExp("^[A-Z]{5}[0-9]{4}[A-Z]{1}$");
         if(paninformat.test(inputvalues)) {
            return true;
         }else {
            alert('Please Enter Valid PAN Number');
            $("#pan").val('');
            $("#pan").focus();
         }
      });
      setTimeout(function() {
         if($("#business_type").val() == 1) {
            $("#dateofjoing_section").hide();
            $("#din_sectioon").hide();
            $("#share_per_div").show();
            var html = '<option value="proprietor">Proprietor</option>';
            $("#designation").html('<option value="proprietor">Proprietor</option><option value="authorised_signatory">Authorised Signatory</option>');
         }else if ($("#business_type").val() == 2) {
            $("#dateofjoing_section").show();
            $("#din_sectioon").hide();
            $("#share_per_div").show();
            $("#designation").html('<option value="partner">Partner</option><option value="authorised_signatory">Authorised Signatory</option>');
         }else {
            $("#dateofjoing_section").show();
            $("#din_sectioon").show();
            $("#share_per_div").hide();
            $("#designation").html('<option value="director">Director</option><option value="authorised_signatory">Authorised Signatory</option>');
         }
      }, 1000);
   });
   $(document).on('click','.delete',function(){
      var id = $(this).attr("data-id");
      $("#sale_id").val(id);
      $("#delete_sale").modal("show");
   });
    
    $("#search_pending").on("keyup", function () {
        var value = $(this).val().toLowerCase().trim();
        $(".sale_table_pending tbody tr").each(function () {
            var text = $(this).text().toLowerCase().trim();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });

    
    $("#search_completed").on("keyup", function () {
        var value = $(this).val().toLowerCase().trim();
        $(".sale_table_completed tbody tr").each(function () {
            var text = $(this).text().toLowerCase().trim();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });

    
    // Parse date safely (handles different formats)
    function parseDateString(s) {
        if (!s) return null;
        s = s.trim().split(' ')[0].replace(/\//g, '-');
        var parts = s.split('-');
        if (parts.length === 3 && parts[0].length === 4) return new Date(s + "T00:00:00"); // yyyy-mm-dd
        if (parts.length === 3 && parts[2].length === 4) return new Date(parts[2] + "-" + parts[1] + "-" + parts[0] + "T00:00:00"); // dd-mm-yyyy
        var d = new Date(s);
        return isNaN(d.getTime()) ? null : d;
    }

    // Filter any table by date range
    function filterTableByDate(tableSelector, fromVal, toVal) {
        var fromDate = parseDateString(fromVal);
        var toDate = parseDateString(toVal);

        $(tableSelector + " tbody tr").each(function() {
            var dateText = $(this).find(".date_column").text().trim();
            var rowDate = parseDateString(dateText);

            if (!rowDate) {
                $(this).show();
                return;
            }

            // Apply filtering rules
            if (fromDate && toDate) {
                $(this).toggle(rowDate >= fromDate && rowDate <= toDate);
            } else if (fromDate) {
                $(this).toggle(rowDate >= fromDate);
            } else if (toDate) {
                $(this).toggle(rowDate <= toDate);
            } else {
                $(this).show();
            }
        });
    }

    // Pending Orders Date Filter
    $("#from_date, #to_date").on("change", function(e) {
        e.stopPropagation(); 
        var fromP = $("#from_date").val();
        var toP = $("#to_date").val();
        filterTableByDate(".sale_table_pending", fromP, toP);
    });

    // Completed Orders Date Filter
    $("#from_date_completed, #to_date_completed").on("change", function(e) {
        e.stopPropagation();
        var fromC = $("#from_date_completed").val();
        var toC = $("#to_date_completed").val();
        filterTableByDate(".sale_table_completed", fromC, toC);
    });

    $(".serach_by_status").change(function(){
      var status = $(this).val();

   if(status == 3){
      window.location = "{{url('set-sale-order')}}";
   }else{
      window.location = "{{url('sale-order')}}?status="+status;
   }
    });
   $(".convert_in_pending").click(function(){
      if(confirm("Are you sure to convert in pending?")==true){
         let id = $(this).attr('data-id');
         $.ajax({
            url : "{{url('sale-order-convert-in-pending')}}",
            method : "post",
            data : {'id':id,'_token': $('meta[name="csrf-token"]').attr('content')},
            success : function(res){
               if(res!=""){
                  let obj = JSON.parse(res);
                  if(obj.status==true){
                     alert("Updated Successfully.");
                     location.reload();
                  }else{
                     alert("Something Went Wrong.");
                  }
               }else{
                  alert("Something Went Wrong.");
               }

            }
         })
      }
   });
   $(".convert_in_set_quantity").click(function(){
         let id = $(this).attr('data-id');
         if(!confirm("Are you sure want to Back To Set Quantity this Sale Order?")){
            return;
         }
         $.ajax({
            url:"{{route('sale-order.back-to-set-quantity')}}",
            type:"post",
            data:{_token:"{{csrf_token()}}",id:id},
            success:function(res){
               if(res.status==1){
                  alert("Sale Order is Back to Set Quantity Successfully.");
                  location.reload();
               }else{
                  alert(res.msg);
               }
            }
         });
      });
   $(".start_status").click(function(){
      if(confirm("Are you sure to start this sale order?")==true){
         let id = $(this).attr('data-id');
         $.ajax({
            url : "{{url('sale-order-start-process')}}",
            method : "post",
            data : {'id':id,'_token': $('meta[name="csrf-token"]').attr('content')},
            success : function(res){
               if(res!=""){
                  
                  if(res.status==true){
                     alert("Started Successfully.");
                     window.location.href = "{{url('set-sale-order')}}/";
                  }else{
                     alert("Something Went Wrong.");
                  }
               }else{
                  alert("Something Went Wrong.");
               }

            }
         })
      }
   });
   $(document).on('click', '.summary-row', function () {

    let itemId = $(this).data('item-id');
    let itemName = $(this).find('td:nth-child(2)').text();

    $('#modalItemTitle').text(itemName + ' Details');

    $('#summaryDetailBody').html(`
        <tr>
            <td colspan="6" class="text-center">Loading...</td>
        </tr>
    `);

    $('#modalTotalKg').text('0.00');
    $('#modalTotalReel').text('0');

    $('#summaryDetailModal').modal('show');

    // 🔥 AJAX CALL
    $.ajax({
    url: "{{ route('summary.item.details') }}",
    type: "GET",
    data: { item_id: itemId },

    success: function (res) {

    let rows = '';
    let totalKg = 0;
    let totalReel = 0;

    if (res.length === 0) {
        rows = `<tr><td colspan="6" class="text-center">No Data Found</td></tr>`;
    } else {

        res.forEach(function (row) {

            let kg = parseFloat(row.kg) || 0;
            let reel = parseFloat(row.reel) || 0;
            
            totalKg += kg;
            totalReel += reel;

            let reelRounded = Math.round(reel);

            rows += `
                <tr>
                    <td>${row.date}</td>
                    <td>${row.so_no}</td>
                    <td>${row.po_no ?? '-'}</td>
                    <td>${row.bill_to}</td>
                    <td class="text-end">${kg.toFixed(2)}</td>
                    <td class="text-end">${reelRounded}</td>
                </tr>
            `;
        });
    }

    let finalKg = Math.round(totalKg / 100) * 100;
    let finalReel = Math.round(totalReel);

    $('#summaryDetailBody').html(rows);
    $('#modalTotalKg').text(finalKg.toFixed(2));
    $('#modalTotalReel').text(finalReel);
},

    error: function (err) {
        console.log(err); 

        $('#summaryDetailBody').html(`
            <tr>
                <td colspan="6" class="text-danger text-center">Error loading data</td>
            </tr>
        `);
    }
});
});
</script>
@endsection