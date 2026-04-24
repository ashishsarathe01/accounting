@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style type="text/css">
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
   <?php
      $items_list = '<option value="">Select Item</option>';
      foreach($items as $item){
         $items_list.='<option value="'.$item->id.'" data-unit_id="'.$item->u_name.'"  data-unit_name="'.$item->unit.'">'.$item->name.'</option>';
      }
   ?>
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
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Stock Journal</h5>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('update-stock-journal') }}">
               @csrf
               <div class="row">
                  <input type="hidden" name="edit_id" value="{{$journal->id}}">
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Series</label>
                     <select id="series_no" name="series_no" class="form-select" required >
                        @foreach($series_list as $key => $value)
                           @if($value->series==$journal->series_no)
                              <option value="{{$value->series}}"  >{{ $value->series }}</option>
                           @endif
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required value="{{$journal->jdate}}" min="{{ $fy_start_date }}" max="{{ $fy_end_date }}">
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Stock Journal No.</label>
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" placeholder="" readonly style="text-align: right;" placeholder="Voucher No" value="{{$journal->voucher_no_prefix}}">
                     <input type="hidden" class="form-control" id="voucher_no" name="voucher_no" value="{{$journal->voucher_no}}">
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="name" class="form-label font-14 font-heading">Material Center</label>
                     <select class="form-select" name="material_center" id="material_center" required>
                        @foreach($series_list as $key => $value)
                           @if($value->mat_center==$journal->material_center)
                              <option value="{{ $value->mat_center }}" @if(count($series_list)==1) selected  @endif>{{ $value->mat_center }}</option>
                           @endif
                        @endforeach
                     </select> 
                  </div>                  
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr><th colspan="7" style="text-align:center">ITEMS CONSUMED</th></tr>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="width: 36%;">DESCRIPTION OF GOODS</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">QUANTITY</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: center;">UNIT</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right">Amount</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $i=1; @endphp
                        @foreach($journal_details as $value)

                           @if($value->consume_item != '')
                           
                              <tr id="tr_{{$i}}" class="font-14 font-heading bg-white">
                                    <td class="w-min-50" id="consume_srn_{{$i}}">{{$i}}</td>
                                    <td class="w-min-50">                                       
                                       <div class="d-flex align-items-center">
                                          @php $isSelectedProductionEnabled = 0;@endphp
                                          <select class="form-control consume_item select2-single" name="consume_item[]" data-id="{{$i}}" id="consume_item_{{$i}}">
                                                <option value="">Select Item</option>
                                                @foreach($items as $item)
                                                   @php
                                                      $isProductionEnabled = $production_module_status && in_array($item->id,$itemIds) ? 1 : 0;
                                                      if($production_module_status && $item->id == $value->consume_item){
                                                         $isSelectedProductionEnabled =1;
                                                      }
                                                   @endphp
                                                   <option value="{{$item->id}}" 
                                                      data-unit_id="{{$item->u_name}}" 
                                                      data-unit_name="{{$item->unit}}" 
                                                      data-production_status="{{$isProductionEnabled}}" 
                                                      @if($item->id == $value->consume_item) selected @endif>
                                                      {{$item->name}}
                                                   </option>
                                                @endforeach
                                          </select>                                          
                                          @if($isSelectedProductionEnabled == 1)
                                             <button type="button" class="btn btn-outline-secondary p-1 px-2 configure-size-btn ms-1" 
                                                data-id="{{ $i }}" 
                                                title="Configure Item ⚙️">⚙️</button>
                                          @endif

                                          <input type="hidden" name="item_size_info[]" id="item_size_info_{{$i}}" value="{{$value->item_size_info ?? ''}}">
                                       </div>
                                    </td>
                                    <td>
                                       <input type="number" name="consume_weight[]" class="form-control consume_weight" data-id="{{$i}}" id="consume_weight_{{$i}}" placeholder="Weight" value="{{$value->consume_weight}}" style="text-align:right">
                                    </td>
                                    <td>
                                       <input type="text" class="w-100 form-control consume_unit" id="consume_unit_tr_{{$i}}" readonly style="text-align:center;" data-id="{{$i}}" name="consume_unit_name[]" value="{{$value->consume_item_unit_name}}">
                                       <input type="hidden" class="consume_units w-100" name="consume_units[]" id="consume_units_tr_{{$i}}" value="{{$value->consume_item_unit}}">
                                    </td>
                                    <td>
                                       <input type="number" name="consume_price[]" class="form-control consume_price" data-id="{{$i}}" id="consume_price_{{$i}}" placeholder="Price" value="{{$value->consume_price}}" style="text-align:right">
                                    </td>
                                    <td>
                                       <input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="{{$i}}" id="consume_amount_{{$i}}" placeholder="Amount" readonly value="{{$value->consume_amount}}" style="text-align:right">
                                    </td>
                                    <td>
                                       <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="{{$i}}" viewBox="0 0 16 16">
                                          <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                                       </svg>
                                    </td>
                              </tr>
                              @php $i++; @endphp
                           @endif
                        @endforeach

                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;float: right;">
                              <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                              </svg></a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold"></td>
                           <td class="fw-bold" style="text-align:right">Total</td>
                           <td class="fw-bold" id="consume_weight_total" style="text-align:right">0</td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold" id="consume_amount_total" style="text-align:right">0</td>
                           <td class="fw-bold"></td>
                        </tr>
                     </div>
                  </table>
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                     <tr><th colspan="7" style="text-align:center">ITEMS GENERATED</th></tr>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="width: 36%;">DESCRIPTION OF GOODS</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">QUANTITY</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: center;">UNIT</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                           <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right">Amount</th>
                           <th></th>
                        </tr>
                     </thead>
                     <tbody>
                        @php $j=1; @endphp
                        
                        @foreach($journal_details as $value)
                        
                           @if($value->new_item!='')
                           
                              <tr id="tr1_{{$j}}" class="font-14 font-heading bg-white">
                              <td class="w-min-50" id="generated_srn_{{$j}}">{{$j}}</td>
                                 <td class="d-flex gap-1">
                                    @php $isSelectedProductionEnabledGen = 0;@endphp
                                    <select class="form-control generated_item select2-single" name="generated_item[]" data-id="{{$j}}" id="generated_item_{{$j}}" data-detail_id="{{$value->id}}">
                                       <option value="">Item</option>      
                                       @foreach($items as $item)
                                       @php
                                          
                                             $isProductionEnabledGen = $production_module_status && in_array($item->id,$itemIds) ? 1 : 0;
                                             if($production_module_status && $item->id == $value->new_item){
                                                         $isSelectedProductionEnabledGen =1;
                                                      }
                                          @endphp
                                          
                                       
                                          <option value="{{$item->id}}" data-unit_id="{{$item->u_name}}"  data-unit_name="{{$item->unit}}" @if($item->id==$value->new_item) selected @endif data-production_status="{{$isProductionEnabledGen}}">{{$item->name}}</option>
                                       @endforeach
                                    </select>
                                 @if($isSelectedProductionEnabledGen == 1)
                                     <button type="button" class="btn btn-outline-secondary p-1 px-2 configure-size-btn-gen ms-1" 
                                          data-id="{{$j}}" data-detail_id="{{$value->id}}" 
                                          title="Configure Item ⚙️">⚙️</button>
                                          @endif
                                    <input type="hidden" name="item_size_info_gen[]" id="item_size_info_gen_{{$j}}" value="{{$value->item_size_info_gen ?? ''}}">
                                 </td>
                                 <td class="">
                                    <input type="number" name="generated_weight[]" class="form-control generated_weight" data-id="{{$j}}" id="generated_weight_{{$j}}" placeholder="Weight" value="{{$value->new_weight}}" style="text-align:right">
                                 </td>
                                 <td class="w-min-50">                              
                                    <input type="text" class="w-100 form-control generated_unit" id="generated_unit_tr_{{$j}}" readonly style="text-align:center;" data-id="{{$j}}" name="generated_unit_name[]" value="{{$value->new_item_unit_name}}"/>
                                    <input type="hidden" class="generated_units w-100" name="generated_units[]" id="generated_units_tr_{{$j}}" value="{{$value->new_item_unit}}"/>
                                 </td>
                                 <td class="">
                                    <input type="number" name="generated_price[]" class="form-control generated_price" data-id="{{$j}}" id="generated_price_{{$j}}" placeholder="Price" value="{{$value->new_price}}" style="text-align:right">
                                 </td>
                                 <td class="">
                                    <input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="{{$j}}" id="generated_amount_{{$j}}" placeholder="Amount" readonly value="{{$value->new_amount}}" style="text-align:right">
                                 </td>                           
                                 <td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove1" data-id="{{$j}}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td>
                              </tr>
                              @php $j++; @endphp
                           @endif
                           
                        @endforeach
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more1"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;float:right">
                              <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                              </svg></a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold"></td>
                           <td class="fw-bold" style="text-align:right">Total</td>
                           <td class="fw-bold" id="consume_weight_total1" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold"></td>
                           <td class="fw-bold" id="consume_amount_total1" style="text-align: right;">0</td>
                           <td class="fw-bold"></td>
                        </tr>
                     </div>
                  </table>                  
               </div>
               <input type="hidden" id="reel_data_json" name="reel_data_json" value="[]">

               <div class="mb-3 col-md-12">
                  <label for="name" class="form-label font-14 font-heading">Narration</label>
                  <input type="text" id="narration" class="form-control" name="narration" placeholder="Narration" value="{{$journal->narration}}">
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <a href="{{ route('stock-journal') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     @if($journal->consumption_entry_status==0)
                     <input type="button" value="SUBMIT" class="btn btn-xs-primary savebtn">
                     @endif
                  </div>
               </div>
            </form>
         </div>
         <div class="col-lg-1 d-flex justify-content-center">
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
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            
            <div class="table-responsive">
               <table class="table table-bordered table-striped mb-0 item_size_table">
                  <thead>
                     <tr>
                        <th style="width: 42%;">Size</th>
                        <th>Reel No.</th>
                        <th>Weight</th>
                        <th>Unit</th>
                         <th>Action</th>
                     </tr>
                  </thead>
                  <tbody id="size_rows">

                  </tbody>
                  <div class="mt-2 text-end">
                     <strong>Total Weight: <span id="total_weight">0</span></strong>
                  </div>
              </table>
            </div>
         </div>
         <div class="modal-footer">
            <input type="hidden" id="item_size_row_id">
            <button class="btn btn-info item_size_btn">Submit</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>

<!-- Modal Gen -->
<div class="modal fade" id="sizeModalGen" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content p-3">
         <div class="modal-header">
            <h5 class="modal-title">Size List</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            
            <div class="table-responsive">
               <table class="table table-bordered table-striped mb-0 item_size_table_gen">
                  <thead>
                     <tr>
                        <th style="width: 42%;">Size</th>
                        <th>Reel No.</th>
                        <th>Weight</th>
                        <th>Unit</th>
                         <th>Action</th>
                     </tr>
                  </thead>
                  <tbody id="size_rows_gen">

                  </tbody>
                  <div class="mt-2 text-end">
                     <strong>Total Weight: <span id="total_weight_gen">0</span></strong>
                  </div>
              </table>
            </div>
         </div>
         <div class="modal-footer">
            <input type="hidden" id="item_size_row_gen_id">
            <button class="btn btn-info item_size_gen_btn">Submit</button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
   let productionModuleStatus = {{ $production_module_status }};
   // Track original item per row (EDIT only)
   let originalConsumeItem = {};
   let originalGenerateItem = {};

   // Track rows where item is changed
   let changedConsumeRows = {};
   let changedGenerateRows = {};
   let acceptedConsumeItem = {};
   let acceptedGenerateItem = {};
   let isNewConsumeRow = {};
   let isNewGenerateRow = {};
                                                   

    let productionItems = @json($production_items);
    productionItems = productionItems.map(obj => parseInt(obj.item_id));

   let availableReels = @json($availableReels);
    let consumedReels = @json($consumed_reels);
    let generatedReels = @json($generated_reels);
    var itemsOptions = {!! json_encode($items->map(function($item) use ($production_items) {
        return ['id' => $item->id,'name' => $item->name,'unit_id' => $item->u_name,'unit_name' => $item->unit,'production_status' => $production_items->contains('item_id', $item->id) ? 1 : 0];})) !!};
   $(document).ready(function() {
      $( ".select2-single, .select2-multiple" ).select2(); 
      calculateAmount(1);
      calculateAmountNew(1);
   });   
   var add_more_count = {{$i}} - 1;
   $(".add_more").click(function() {      
      add_more_count++;
      isNewConsumeRow[add_more_count] = true;
      let srn = add_more_count;

      var $curRow = $(this).closest('tr');

      var newRow = `
      <tr id="tr_${add_more_count}" class="font-14 font-heading bg-white">
         <td class="w-min-50" id="consume_srn_${add_more_count}">${srn}</td>
         <td>
               <div class="d-flex align-items-center">
                  <select class="form-control consume_item select2-single" name="consume_item[]" data-id="${add_more_count}" id="consume_item_${add_more_count}">
                     ${generateOptions()}
                  </select>
                  
                  <button type="button" class="btn btn-outline-secondary p-1 px-2 configure-size-btn ms-1 " data-id="${add_more_count}" title="Configure Item ⚙️">⚙️</button>
                  <input type="hidden" name="item_size_info[]" id="item_size_info_${add_more_count}">
               </div>
         </td>
         <td><input type="number" name="consume_weight[]" class="form-control consume_weight" data-id="${add_more_count}" id="consume_weight_${add_more_count}" placeholder="Weight" style="text-align:right"></td>
         <td><input type="text" class="w-100 form-control consume_unit" id="consume_unit_tr_${add_more_count}" readonly style="text-align:center;" data-id="${add_more_count}" name="consume_unit_name[]"></td>
         <td><input type="number" name="consume_price[]" class="form-control consume_price" data-id="${add_more_count}" id="consume_price_${add_more_count}" placeholder="Price" style="text-align:right"></td>
         <td><input type="text" name="consume_amount[]" class="form-control consume_amount" data-id="${add_more_count}" id="consume_amount_${add_more_count}" placeholder="Amount" readonly style="text-align:right"></td>
         <td>
               <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="${add_more_count}" viewBox="0 0 16 16">
                  <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
               </svg>
         </td>
      </tr>`;
      $curRow.before(newRow);
      let $newSelect = $("#consume_item_" + add_more_count);
      $newSelect.val("").trigger("change");
      $newSelect.select2();
      refreshConsumeItemOptionsEdit();
   });

   var add_more_count1 = '<?php echo $j;?>';
   add_more_count1--;
   $(".add_more1").click(function() {
      let srn = $("#generated_srn_"+add_more_count1).html();
      srn++
      add_more_count1++;
      isNewGenerateRow[add_more_count1] = true;
      acceptedGenerateItem[add_more_count1] = null;
      var $curRow = $(this).closest('tr');
      var optionElements = '<?php echo $items_list;?>';
      newRow = '<tr id="tr1_'+add_more_count1+'" class="font-14 font-heading bg-white"><td class="w-min-50" id="generated_srn_'+add_more_count1+'">'+srn+'</td><td class="d-flex gap-1"><select class="form-select generated_item select2-single" name="generated_item[]" data-id="'+add_more_count1+'" id="generated_item_'+add_more_count1+'">'+optionElements+'</select>  <button type="button" class="btn btn-outline-secondary p-1 px-2 configure-size-btn-gen ms-1" data-id="'+add_more_count1+'" title="Configure Item ⚙️">⚙️</button><input type="hidden" name="item_size_info_gen[]" id="item_size_info_gen_'+add_more_count1+'" value="{{$value->item_size_info_gen ?? ''}}"></td><td class=""><input type="number" name="generated_weight[]" class="form-control generated_weight" data-id="'+add_more_count1+'" id="generated_weight_'+add_more_count1+'" placeholder="Weight" style="text-align:right"></td><td class="w-min-50"><input type="text" class="w-100 form-control generated_unit" id="generated_unit_tr_'+add_more_count1+'" readonly="" style="text-align:center;" data-id="'+add_more_count1+'" name="generated_unit_name[]"><input type="hidden" class="generated_units w-100" name="generated_units[]" id="generated_units_tr_'+add_more_count1+'" ></td><td class=""><input type="number" name="generated_price[]" class="form-control generated_price" data-id="'+add_more_count1+'" id="generated_price_'+add_more_count1+'" placeholder="Price" style="text-align:right"></td><td class=""><input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="'+add_more_count1+'" id="generated_amount_'+add_more_count1+'" placeholder="Amount" readonly style="text-align:right"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove1" data-id="' + add_more_count1+ '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
      let $newSelect = $("#generated_item_" + add_more_count1);
      $newSelect.val("").trigger("change");
      $("#tr1_" + add_more_count1 + " .configure-size-btn-gen").hide();
   });
   $(document).on("click", ".remove", function(){
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      calculateAmount(1)
   });
   $(document).on("click", ".remove1", function(){
      let id = $(this).attr('data-id');
      $("#tr1_" + id).remove();
      calculateAmountNew(1)
   });
   $(".savebtn").click(function(){
      let date = $("#date").val();
      let item_count = 0;
      $(".consume_item").each(function(){
         let id = $(this).attr('data-id');
         let consume_item = $(this).val();
         let consume_weight = $("#consume_weight_"+id).val();
         let new_item = $("#new_item_"+id).val();
         let new_weight = $("#new_weight_"+id).val();
         if(consume_item!="" && consume_weight!="" && new_item!="" && new_weight!=""){
            item_count++;
         }
      });
      if(item_count==0){
         alert("Please item and weight.");
         return false;
      }
      $("#frm").submit();
   });
   $(document).on('keyup','.consume_weight',function(){
      let id = $(this).attr('data-id');
      calculateAmount(id);
   });
   $(document).on('keyup','.consume_price',function(){
      let id = $(this).attr('data-id');
      calculateAmount(id);
   });
   $(document).on('keyup','.generated_weight',function(){
      let id = $(this).attr('data-id');
      calculateAmountNew(id);
   });
   $(document).on('keyup','.generated_price',function(){
      let id = $(this).attr('data-id');
      calculateAmountNew(id);
   });
   function calculateAmount(id){
      let consume_price = $("#consume_price_"+id).val();
      let consume_weight = $("#consume_weight_"+id).val();
      let cweight = 0;
      $(".consume_weight").each(function(){
         if($(this).val()!=''){
            cweight = parseFloat(cweight) + parseFloat($(this).val());
         }
      });
      $("#consume_weight_total").html(cweight);
      if(consume_price=="" || consume_weight==""){
         return;
      }
      let amount = parseFloat(consume_price)*parseFloat(consume_weight);
      $("#consume_amount_"+id).val(amount.toFixed(2));
      let camount = 0;
      $(".consume_amount").each(function(){
         if($(this).val()!=''){
            camount = parseFloat(camount) + parseFloat($(this).val());
         }
      });
      let finalAmount = (parseFloat(camount) || 0).toFixed(2);
      $("#consume_amount_total").html(finalAmount);
   } 
   function calculateAmountNew(id){
      let generated_price = $("#generated_price_"+id).val();
      let generated_weight = $("#generated_weight_"+id).val();
      let nweight = 0;
      $(".generated_weight").each(function(){
         if($(this).val()!=''){
            nweight = parseFloat(nweight) + parseFloat($(this).val());
         }
      });
      $("#consume_weight_total1").html(nweight);
      if(generated_price=="" || generated_weight==""){
         return;
      }
      let amount = parseFloat(generated_price)*parseFloat(generated_weight);
      $("#generated_amount_"+id).val(amount.toFixed(2));

      let namount = 0;
      $(".generated_amount").each(function(){
         if($(this).val()!=''){
            namount = parseFloat(namount) + parseFloat($(this).val());
         }
      });
      let finalAmount = (parseFloat(namount) || 0).toFixed(2);
       $("#consume_amount_total1").html(finalAmount);

   }
   $("#voucher_prefix").keyup(function(){
      $("#voucher_no").val($(this).val());
   });
   $(document).on('change', '.consume_item', function () {

    let rowId = $(this).data("id");
    let newItemId = $(this).val();

    // unit update
    $('#consume_unit_tr_' + rowId).val($(this).find(':selected').data('unit_name'));
    $('#consume_units_tr_' + rowId).val($(this).find(':selected').data('unit_id'));

    let isProduction = $(this).find(':selected').data('production_status') == 1;
    let gearBtn = $('#tr_' + rowId + ' .configure-size-btn');

    // same item → do nothing
    // NEW ROW → behave like Add Stock Journal
      if (isNewConsumeRow[rowId]) {

      // if still empty → do NOTHING
      if (!newItemId) {
         gearBtn.hide();
         return;
      }

      $('#item_size_info_' + rowId).val('');
      $('#consume_weight_' + rowId).val('');
      $('#consume_price_' + rowId).val('');
      $('#consume_amount_' + rowId).val('');

      if (isProduction && productionModuleStatus === 1) {
         gearBtn.show();
         gearBtn.trigger('click'); // open modal only AFTER item selection
      } else {
         gearBtn.hide();
      }

      return;
      }


      // EXISTING ROW → old edit logic
      if (newItemId === acceptedConsumeItem[rowId]) {
         isProduction ? gearBtn.show() : gearBtn.hide();
         return;
      }


    // item changed → reset row
    $('#item_size_info_' + rowId).val('');
    $('#consume_weight_' + rowId).val('');
    $('#consume_price_' + rowId).val('');
    $('#consume_amount_' + rowId).val('');

    // show & auto-open modal if parameterized
    if (isProduction && productionModuleStatus === 1) {
        gearBtn.show();
        gearBtn.trigger('click');
    } else {
        gearBtn.hide();
    }

    calculateAmount(rowId);
    refreshConsumeItemOptionsEdit();
   });


   $(document).on('change', '.generated_item', function () {

    let rowId = $(this).data("id");
    let newItemId = $(this).val();

    // update unit
    $('#generated_unit_tr_' + rowId).val($(this).find(':selected').data('unit_name') || '');
    $('#generated_units_tr_' + rowId).val($(this).find(':selected').data('unit_id') || '');

    let isProduction = isGeneratedItemParameterized(newItemId);
    let gearBtn = $('#tr1_' + rowId + ' .configure-size-btn-gen');

    /* ===============================
       NEW ROW (Add Row behaviour)
       =============================== */
    if (isNewGenerateRow[rowId] === true) {

        // nothing selected → hide gear
        if (!newItemId) {
            gearBtn.hide();
            return;
        }

        // reset row data
        $('#item_size_info_gen_' + rowId).val('');
        $('#generated_weight_' + rowId).val('');
        $('#generated_price_' + rowId).val('');
        $('#generated_amount_' + rowId).val('');

        if (isProduction && productionModuleStatus === 1) {
            gearBtn.show();

            // ✅ ALWAYS open modal safely
            openGeneratedModal(rowId);
        } else {
            gearBtn.hide();
        }

        return;
    }

    /* ===============================
       EXISTING ROW (Edit behaviour)
       =============================== */
    if (acceptedGenerateItem[rowId] && newItemId === acceptedGenerateItem[rowId]) {

        if (isProduction && productionModuleStatus === 1) {
            gearBtn.show();
        } else {
            gearBtn.hide();
        }

        return;
    }

    /* ===============================
       ITEM CHANGED
       =============================== */
    $('#item_size_info_gen_' + rowId).val('');
    $('#generated_weight_' + rowId).val('');
    $('#generated_price_' + rowId).val('');
    $('#generated_amount_' + rowId).val('');

    if (isProduction && productionModuleStatus === 1) {
        gearBtn.show();
        openGeneratedModal(rowId);
    } else {
        gearBtn.hide();
    }

    calculateAmountNew(rowId);
});

   $(document).on('click', '.configure-size-btn', function () {

    let rowId = $(this).data("id");
    let itemId = $("#consume_item_" + rowId).val();
    
      if (!(productionModuleStatus === 1 && productionItems.includes(parseInt(itemId)))) {
         return; // ❌ Skip because condition not satisfied
      }

    if (!itemId) {
        alert("Select item first!");
        return;
    }

    // Filter available reels for this item
    let itemAvailable = availableReels.filter(r => r.item_id == itemId);

      // Load reels saved earlier (from hidden input)
      let savedJson = "";
      if ($("#consume_item_" + rowId).val() === acceptedConsumeItem[rowId]) {
         savedJson = $("#item_size_info_" + rowId).val();
      }


      let savedReels = [];

      if (savedJson) {
         try {
            savedReels = JSON.parse(savedJson);
         } catch (e) {
            savedReels = [];
         }
      }

      // Load consumed reels from DB
      let dbReels = consumedReels.filter(r => r.item_id == itemId);

      // Merge both lists
      let itemConsumed = [...dbReels];

      // Add saved reels ONLY if not already included
      savedReels.forEach(sr => {
         if (!itemConsumed.some(r => r.id == sr.id)) {
            itemConsumed.push({
                  id: sr.id,
                  reel_no: sr.reel_no,
                  weight: sr.weight,
                  size: "",    // optional, fetched from availableReels below
                  unit: ""     // optional
            });
         }
      });

      // If still empty → add blank row
      if (itemConsumed.length == 0) {
         itemConsumed = [{ id: "", size: "", weight: "", reel_no: "" }];
      }


      let totalWeight = 0;
      let rowsHTML = '';

      itemConsumed.forEach(function(c, index){

         let match = itemAvailable.find(a => a.id == c.id);

         // 2️⃣ FIX — IF FOUND, FILL MISSING VALUES
         if (match) {
            c.size  = c.size  || match.size;
            c.unit  = c.unit  || match.unit;
            c.weight = c.weight || match.weight;
            c.reel_no = c.reel_no || match.reel_no;
         }
            
         let optionsHTML = "";
         let optionsUnit = "";

         

      if (c.size) {  
         optionsHTML = `<option value="${c.id}">${c.size} (Reel ${c.reel_no}) - ${c.weight}kg</option>`;
         optionsUnit = `<option value="${c.unit}">${c.unit}</option>`
      } else {
         optionsHTML = `<option value="">Select Reel</option>`;
         optionsUnit =`<option value="">Select Unit</option>
                                       <option value="INCH">INCH</option>
                                       <option value="CM">CM</option>
                                       <option value="MM">MM</option>`
      }


    itemAvailable.forEach(function(a){
        optionsHTML += `
            <option value="${a.id}" 
                    data-weight="${a.weight}" 
                    data-reel="${a.reel_no}"
                    data-unit ="${a.unit}"
                    ${a.id == c.id ? 'selected' : ''}>
                ${a.size} (Reel ${a.reel_no}) - ${a.weight}kg
            </option>`;
    });

    rowsHTML += `
        <tr class="size-row">
            <td>
                <select class="form-select size-select">
                    ${optionsHTML}
                </select>
            </td>
           
            <td><input type="text" class="form-control size-reel" value="${c.reel_no}" readonly></td>
             <td><input type="text" class="form-control size-weight" value="${c.weight}" readonly></td>
              <td>
                              <select class="form-select me-2 size-unit" disabled>
                                 ${optionsUnit}
                              </select>
                           </td>
            <td class="d-flex gap-1">
               <button type="button" class="btn btn-primary add_new_reel" data-id="${rowId}" data-item-id="${itemId}" id="addNewReelBtn"> + </button> <button type="button" class="btn btn-danger remove-reel">−</button>

            </td>
        </tr>`;
        totalWeight += Number(c.weight || 0);
   });
   
    $("#size_rows").html(rowsHTML);
    $("#total_weight").text(totalWeight.toFixed(2));

    $("#item_size_row_id").val(rowId);
    $("#sizeModal").modal("show");
});

$(document).on("change", ".size-select", function () {
          refreshReelDropdowns();

    let selected = $(this).find("option:selected");
    let row = $(this).closest("tr");

    let weight = selected.data("weight") || "";
    let reel = selected.data("reel") || "";
    let unit   = selected.data("unit") || ""; 

    row.find(".size-weight").val(weight);
    row.find(".size-reel").val(reel);
    row.find(".size-unit").val(unit).trigger("change");


    updateTotalWeight();
});


$(document).on("click",".add_new_reel", function(){
    let rowId = $(this).data("id");
    let optionsHTML = `<option value="">Select Reel</option>`;
    let optionsUnit = `<option value="">Select Unit</option>
                                `;

    let itemId = $(this).data("item-id");

    
let itemAvailable = availableReels.filter(r => r.item_id == itemId);

    itemAvailable.forEach(function(a){
        optionsHTML += `
            <option value="${a.id}" 
                    data-weight="${a.weight}" 
                    data-reel="${a.reel_no}"
                    data-unit ="${a.unit}">
                ${a.size} (Reel ${a.reel_no}) - ${a.weight}kg
            </option>`;
             optionsUnit +=`<option value="${a.unit}" readonly>${a.unit}</option>`;

    });

    let newRow = `
        <tr class="size-row">
            <td><select class="form-select size-select">${optionsHTML}</select></td>
            <td><input type="text" class="form-control size-reel" readonly></td>
             <td><input type="text" class="form-control size-weight" readonly></td>
              <td>
                              <select class="form-select me-2 size-unit" disabled>
                                 ${optionsUnit}
                              </select>
                           </td>

            <td class="d-flex gap-1"><button type="button" 
                class="btn btn-primary add_new_reel"
                data-id="${rowId}"
                data-item-id="${itemId}">
            +
        </button>
         <button type="button" class="btn btn-danger remove-reel">-</button></td>
        </tr>`;

    $("#size_rows").append(newRow);
    refreshReelDropdowns();
});

$(document).on("click", ".remove-reel", function () {

    let totalRows = $(".size-row").length;

    if (totalRows <= 1) {
        alert("You must keep at least one reel row.");
        return;
    }

    $(this).closest("tr").remove();

    refreshReelDropdowns();  // re-enable options

    updateTotalWeight();    // recalc weight
});



 function updateTotalWeight() {
    let modal = $("#sizeModal");
    let total = 0;

    modal.find(".size-weight").each(function () {
        total += parseFloat($(this).val() || 0);
    });

    modal.find("#total_weight").text(total.toFixed(2));
}




function generateOptions() {
    let html = `<option value="">Select Item</option>`;
    itemsOptions.forEach(function(item) {
        html += `<option value="${item.id}" 
                        data-unit_id="${item.unit_id}" 
                        data-unit_name="${item.unit_name}" 
                        data-production_status="${item.production_status}">
                        ${item.name}
                 </option>`;
    });
    return html;
}


$(document).on("click", ".item_size_btn", function () {


    let modal = $("#sizeModal");     // LIMIT to this modal only
    let total = 0;

    modal.find(".size-weight").each(function () {
        total += parseFloat($(this).val() || 0);
    });

    modal.find("#total_weight").text(total.toFixed(2));
 let totalWeight = modal.find("#total_weight").text().trim();     // remove spaces


    let rowId = $("#item_size_row_id").val(); 
    // row in main table
    let reels = [];

    $("#size_rows .size-row").each(function () {
        let selected = $(this).find(".size-select option:selected");

        let reelId = selected.val();
       
        let weight = $(this).find(".size-weight").val() || "";
        let reelNo = $(this).find(".size-reel").val() || "";

        // Skip blank rows
        if (reelId !== "" && weight !== "") {
            reels.push({
                id: reelId,
                weight: weight,
                reel_no: reelNo
            });
        }
    });

    // Save as JSON inside hidden input of this main row
    $("#item_size_info_" + rowId).val(JSON.stringify(reels));
     $("#consume_weight_" + rowId).val(totalWeight);
     isNewConsumeRow[rowId] = false;                 // mark row as existing
    acceptedConsumeItem[rowId] = $("#consume_item_" + rowId).val();
     calculateAmount(rowId);

    $("#sizeModal").modal("hide");
});
// Recalculate when user types in size (if that affects something) OR directly edits weight
$(document).on("input", ".size_gen, .size-weight", function () {
    // find the modal containing the changed input (works for any modal)
    let modal = $(this).closest(".modal");
    updateTotalWeightGen(modal);
});

// When you open the modal (or fill rows) call this to ensure total is correct
// Example: after you populate rows you already call $("#sizeModalGen").modal("show");
// call updateTotalWeightGen($('#sizeModalGen')) right after filling the tbody.
$(document).on("shown.bs.modal", "#sizeModalGen", function () {
    updateTotalWeightGen($(this));
});

/**
 * updateTotalWeightGen(modal)
 * - modal: optional jQuery modal element. If omitted, will default to #sizeModalGen.
 */
function updateTotalWeightGen(modal) {
    modal = modal && modal.length ? modal : $("#sizeModalGen");

    // debug: modal exists?
    if (!modal || !modal.length) {
        console.warn("updateTotalWeightGen: modal not found");
        return;
    }

    let total = 0;

    modal.find(".size-weight").each(function () {
        // use Number and guard against NaN
        let v = Number($(this).val());
        if (!isNaN(v)) total += v;
    });

    // write total (ensure target exists)
    let $display = modal.find("#total_weight_gen");
    if ($display && $display.length) {
        $display.text(total.toFixed(2));
    } else {
        console.warn("updateTotalWeightGen: #total_weight_gen not found inside modal", modal);
    }

    // debug output (remove when done)
    console.log("updateTotalWeightGen: total =", total, "modal id =", modal.attr("id"));
}

// When you append a new row programmatically, trigger recalculation immediately
$(document).on("click", ".add_new_reel_gen", function (e) {
    // your existing code appends row — after append call:
    // $("#size_rows_gen").append(newRow);  <-- your code
    // then recalc:
    let modal = $(this).closest(".modal");
    // small timeout to ensure appended element is in DOM
    setTimeout(() => updateTotalWeightGen(modal), 0);
});




$(document).on('click', '.configure-size-btn-gen', function () {

    let rowId = $(this).data("id");
    let itemId = $("#generated_item_" + rowId).val();
    let detail_id = $(this).data("detail_id");
    if (!isGeneratedItemParameterized(itemId)) {
      return;
   }


    if (!itemId) {
        alert("Select item first!");
        return;
    }
    
    // 1️⃣ FETCH REELS FROM DATABASE
    let dbReels = generatedReels.filter(r => r.sj_generated_detail_id == detail_id);
      
    // 2️⃣ FETCH SAVED REELS FROM HIDDEN INPUT (IF EXIST)
    let savedJson = "";
   if ($("#generated_item_" + rowId).val() === acceptedGenerateItem[rowId]) {
      savedJson = $("#item_size_info_gen_" + rowId).val();
   }

    let savedReels = [];

    if (savedJson) {
        try { savedReels = JSON.parse(savedJson); } catch (e) {}
    }
    
    // 3️⃣ MERGE LOGIC
    // SAVED reels override DB reels
    let finalReels = [];

    if (savedReels.length > 0) {
      
        // USER ALREADY SAVED SOMETHING → USE THAT
        finalReels = savedReels;
    } else if (dbReels.length > 0) {
        // USE DATABASE REELS
        finalReels = dbReels.map(r => ({         
            size: r.size || "",
            reel_no: r.reel_no || "",
            weight: r.weight || "",
            unit: r.unit || "",
            status: r.status || "0"
        }));
        
    } else {
        // IF NOTHING EXISTS → GIVE 1 BLANK ROW
        finalReels = [{ size: "", reel_no: "", weight: "", unit: "",status: "" }];
    }

    // 4️⃣ FILL MODAL TABLE
    let totalWeight = 0;
    let rowsHTML = "";
    console.log(finalReels)
    finalReels.forEach((c, index) => {
        let unitOptions = `
            <option value="">Select Unit</option>
            <option value="INCH" ${c.unit == "INCH" ? "selected" : ""}>INCH</option>
            <option value="CM" ${c.unit == "CM" ? "selected" : ""}>CM</option>
            <option value="MM" ${c.unit == "MM" ? "selected" : ""}>MM</option>
        `;
         let readonlyAttr = "";
         let disableAttr = "";
         if(c.status==0){
            console.log("ff");
            readonlyAttr = "readonly";
            disableAttr = "disabled";
         }
        rowsHTML += `
        <tr class="size-row">
            <td><input type="text" class="form-control size size_gen" value="${c.size}" ${disableAttr}></td>
            <td><input type="text" class="form-control size-reel" value="${c.reel_no}" ${disableAttr}></td>
            <td><input type="text" class="form-control size-weight" value="${c.weight}" ${disableAttr}></td>
            <td><select class="form-select size-unit" ${disableAttr}>${unitOptions}</select></td>
            <td class="d-flex gap-1" ${disableAttr}>
               <button type="button" class="btn btn-primary  add_new_reel_gen"
                  data-id="${rowId}" data-item-id="${itemId}"> + </button>

               <button type="button" class="btn btn-danger  remove-reel-gen" ${disableAttr}> − </button>
            </td>

        </tr>
        `;
        totalWeight += Number(c.weight || 0);
    });

    $("#size_rows_gen").html(rowsHTML);
    $("#total_weight_gen").text(totalWeight.toFixed(2));

    $("#item_size_row_gen_id").val(rowId);
    $("#sizeModalGen").modal("show");
});




$(document).on("click",".add_new_reel_gen", function(){

    let rowId = $(this).data("id");
    let itemId = $(this).data("item-id");

    let newRow = `
        <tr class="size-row">
            <td><input type="text" class="form-control size size_gen"></td>
            <td><input type="text" class="form-control size-reel"></td>
            <td><input type="text" class="form-control size-weight"></td>
            <td>
                <select class="form-select size-unit">
                    <option value="">Select Unit</option>
                    <option value="INCH">INCH</option>
                    <option value="CM">CM</option>
                    <option value="MM">MM</option>
                </select>
            </td>
           <td class="d-flex gap-1">
               <button type="button" class="btn btn-primary  add_new_reel_gen"
                  data-id="${rowId}" data-item-id="${itemId}"> + </button>

               <button type="button" class="btn btn-danger  remove-reel-gen"> − </button>
            </td>

        </tr>
    `;

    $("#size_rows_gen").append(newRow);
});


$(document).on("click", ".item_size_gen_btn", function () {
   let modal = $("#sizeModalGen");   // LIMIT to this modal only
   let total = 0;
   let hasError = false;
   modal.find(".size-weight").each(function () {
      total += parseFloat($(this).val() || 0);
   });
   modal.find("#total_weight_gen").text(total.toFixed(2));
   let totalWeight = modal.find("#total_weight_gen").text().trim();
   let rowId = $("#item_size_row_gen_id").val();    
   // remove spaces
   let reels = [];
   $("#size_rows_gen .size-row").each(function () {
      let size = $(this).find(".size").val().trim();
      let reelNo = $(this).find(".size-reel").val().trim();
      let weight = $(this).find(".size-weight").val().trim();
      let unit = $(this).find(".size-unit").val().trim();
      // Skip empty blank rows
      if (size === "" || reelNo === "" || weight === "" || unit === "") {
         alert("Please fill Size, Reel No, Weight and Unit for all rows.");
         // highlight row with missing data
         $(this).css("background", "#ffe5e5");
         hasError = true;
         return false;  // stop loop
      }
      
      reels.push({
         size: size,
         reel_no: reelNo,
         weight: weight,
         unit: unit,
         status: $(this).find(".size").prop('disabled')==true ? "0" : "1"
      });
      
   });
   if (hasError) return;
   $("#item_size_info_gen_" + rowId).val(JSON.stringify(reels));
   $("#generated_weight_" + rowId).val(totalWeight);
   isNewGenerateRow[rowId] = false;
   acceptedGenerateItem[rowId] = $("#generated_item_" + rowId).val();
   $("#sizeModalGen").modal("hide");
   calculateAmountNew(rowId);
});

function getSelectedReels() {
    let selected = [];
    $(".size-select").each(function () {
        let val = $(this).val();
        if (val) {
            selected.push(val);
        }
    });
    return selected;
}

function refreshReelDropdowns() {
    let usedReels = getSelectedReels();

    $(".size-select").each(function () {
        let currentSelect = this;
        let currentValue = $(this).val(); // do not disable own selected reel

        $(this).find("option").each(function () {
            let val = $(this).attr("value");

            if (!val) return; // skip "Select Reel"

            if (usedReels.includes(val) && val !== currentValue) {
                $(this).prop("disabled", true).hide();
            } else {
                $(this).prop("disabled", false).show();
            }
        });
    });
}


$(document).on("click", ".remove-reel-gen", function () {

    let modal = $(this).closest(".modal");
    let rows = modal.find(".size-row");

    if (rows.length <= 1) {
        alert("At least one reel row is required.");
        return;
    }

    $(this).closest("tr").remove();

    // Recalculate total after deletion
    updateTotalWeightGen(modal);
});


   function populateItemSizeInfo() {
      $(".consume_item").each(function() {
         let rowId = $(this).data("id");
         let itemId = $(this).val();

         if (!itemId) return; // Skip if no item selected
         
         if($(this).find(':selected').data('production_status')==0) return;
        // Filter available reels for this item
         let itemAvailable = availableReels.filter(r => r.item_id == itemId);

        // Load consumed reels from DB
         let dbReels = consumedReels.filter(r => r.item_id == itemId);

        // Merge both lists
         let reels = [...dbReels];

        // Add default values if none exists
        if (reels.length === 0 && itemAvailable.length > 0) {
            reels.push({
                id: itemAvailable[0].id,
                reel_no: itemAvailable[0].reel_no,
                weight: itemAvailable[0].weight,
                size: itemAvailable[0].size,
                unit: itemAvailable[0].unit
            });
        }

        // Save as JSON in hidden input
        $("#item_size_info_" + rowId).val(JSON.stringify(reels));

        // Update total weight
        let totalWeight = reels.reduce((sum, r) => sum + Number(r.weight || 0), 0);
        $("#consume_weight_" + rowId).val(totalWeight.toFixed(2));

        // Optional: calculate amount if you have a function
        calculateAmount(rowId);
    });
}
var consumption_entry_status = @json($journal->consumption_entry_status);
$(document).ready(function() {
   if(productionModuleStatus==1){
      populateItemSizeInfo();
      if(consumption_entry_status==0){
         populateGeneratedItemSizeInfo();
      }
      
   }
   
});
// Capture original items on EDIT load
$(".consume_item").each(function () {
    let rowId = $(this).data("id");
    originalConsumeItem[rowId] = $(this).val();
    acceptedConsumeItem[rowId] = $(this).val();
});

$(".generated_item").each(function () {
    let rowId = $(this).data("id");
    originalGenerateItem[rowId] = $(this).val();
    acceptedGenerateItem[rowId] = $(this).val();
});

function populateGeneratedItemSizeInfo() {
    $(".generated_item").each(function() {
        let rowId = $(this).data("id");
        let detail_id = $(this).data("detail_id");
        let itemId = $(this).val();

        if (!itemId) return; // skip if no item selected
        if($(this).find(':selected').data('production_status')==0) return;
        // 1️⃣ Fetch reels for this item
        let dbReels = generatedReels.filter(r => r.sj_generated_detail_id == detail_id);

        // 2️⃣ Merge with saved JSON if exists
        let savedJson = $("#item_size_info_gen_" + rowId).val();
        let savedReels = [];

        if (savedJson) {
            try { savedReels = JSON.parse(savedJson); } catch(e){ savedReels = []; }
        }

        // 3️⃣ Final reels logic
        let finalReels = [];
        if (savedReels.length > 0) {
            finalReels = savedReels;
        } else if (dbReels.length > 0) {
            finalReels = dbReels.map(r => ({
                size: r.size || "",
                reel_no: r.reel_no || "",
                weight: r.weight || "",
                unit: r.unit || "",
                status: r.status || "0"
            }));
        } else {
            // At least one blank row
            finalReels = [{ size: "", reel_no: "", weight: "", unit: "",status: "" }];
        }

        // 4️⃣ Save JSON in hidden input
        $("#item_size_info_gen_" + rowId).val(JSON.stringify(finalReels));
        let hasSoldReel = finalReels.some(r => r.status == 0 || r.status == "0");

         if(hasSoldReel){
            $("#tr1_" + rowId + " .remove1").hide();
         }
        // 5️⃣ Calculate total weight
        let totalWeight = finalReels.reduce((sum, r) => sum + Number(r.weight || 0), 0);
        $("#generated_weight_" + rowId).val(totalWeight.toFixed(2));

        // 6️⃣ Optional: calculate amount if you have a function
        calculateAmountNew(rowId);
    });
}
function openGeneratedModal(rowId) {
    let itemId = $("#generated_item_" + rowId).val();
    if (!itemId) return;

    // set active row
    $("#item_size_row_gen_id").val(rowId);

    // directly invoke the existing click handler logic
    $(".configure-size-btn-gen[data-id='" + rowId + "']").trigger("click");
}
function isGeneratedItemParameterized(itemId) {
    if (!itemId) return false;

    let item = itemsOptions.find(i => i.id == itemId);
    return item && item.production_status == 1;
}

function refreshConsumeItemOptionsEdit() {
    let selected = [];

    $(".consume_item").each(function () {
        let v = $(this).val();
        if (v) selected.push(v);
    });

    $(".consume_item").each(function () {
        let current = $(this).val();

        $(this).find("option").each(function () {
            let opt = $(this).val();
            if (!opt) return;

            if (selected.includes(opt) && opt !== current) {
                $(this).prop("disabled", true);
            } else {
                $(this).prop("disabled", false);
            }
        });
    });

    $(".consume_item").trigger("change.select2");
}
</script>
@endsection