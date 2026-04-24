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
               <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                  <h5 class="transaction-table-title m-0 py-2">Pending Sales Orders</h5>
                 

                  @can('action-module',85)
                     ADD
                     <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none">
                        <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white"/>
                     </svg>
                  </a>
                  @endcan
               </div>
               <div class="transaction-table bg-white table-view shadow-sm mb-5">
                  <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('save-sale-order-quantity')}}" id="saleOrderForm">
                     @csrf                     
                     <div class="row mb-3">
                        <div class="mb-3 col-md-3">
                           <label for="bill_to" class="form-label font-14 font-heading">Bill To</label>
                           <input type="text" class="form-control" readonly value="{{$sale_order->billTo->account_name}}">
                        </div>
                        <div class="mb-3 col-md-3">
                           <label for="bill_to" class="form-label font-14 font-heading">Date</label>
                           <input type="text" class="form-control" readonly value="{{ date('d-m-Y', strtotime($sale_order->created_at)) }}">
                        </div>
                        <div class="mb-3 col-md-3">
                           <label for="bill_to" class="form-label font-14 font-heading">Order No.</label>
                           <input type="text" class="form-control" readonly value="{{ $sale_order->sale_order_no }}">
                        </div>
                     </div>
                     <div id="items_container">
                        @foreach ($sale_order->items as $item)
                           <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                              <div class="row">
                                 <div class="col-md-3 mb-3">
                                    <label class="form-label font-14 font-heading">Item</label>
                                    <input type="text" class="form-control" value="{{ $item->item->name }}" readonly>
                                 </div>
                                 <div class="col-md-3 mb-3">
                                       <label for="unit_1" class="form-label font-14 font-heading">Unit</label>
                                       <input type="text" class="form-control" value="{{ $item->unitMaster->s_name }}" readonly>
                                 </div>
                                 <div class="mb-3 col-md-3">
                                       <label for="sub_unit_1" class="form-label font-14 font-heading">Sub Unit</label>
                                       <input type="text" class="form-control" value="{{$item->sub_unit}}" readonly>
                                 </div>
                              </div>
                              <!-- GSM / Sizes / Reels -->
                              @php 
                                 $grouped = [];
                                 foreach ($item->itemSize as $row) {
                                    $key = $row['item_id'] . 'X' . $row['size'];
                                    $grouped[$key][] = $row->toArray();
                                 }
                              @endphp
                              <div class="row" id="dynamic_gsm_1">
                                 @foreach ($item->gsms as $gsm)
                                    <div class="col-md-6 gsm-block" id="gsm_block_1_1">
                                       <table class="table table-bordered">
                                          <tr>
                                             <td style="width: 40%;">GSM</td>
                                             <td style="text-align: center">{{$gsm->gsm}}</td>
                                          </tr>
                                       </table>
                                       <table class="table table-bordered" id="table_1_1">
                                          <tr>
                                                <td width="40%">SIZES</td>
                                                <td width="20%">Order Quantity</td>
                                                <td width="20%">Stock Quantity</td>
                                                <td width="20%">Set Quantity</td>

                                          </tr>
                                          @php  $reelArr = [];$sizeArr = [];@endphp
                                          @foreach ($gsm->details as $detail)
                                             @php 
                                             array_push($reelArr,$detail->quantity);
                                             array_push($sizeArr,$detail->size);
                                             @endphp
                                          @endforeach
                                          @php
                                             $approx_qty = 0;
                                             for($i=0;$i<count($reelArr);$i++){
                                                if($reelArr[$i]!=""){
                                                   $approx_qty = $approx_qty + $reelArr[$i]/($sizeArr[$i]*15);
                                                }
                                             }
                                             $approx_qty = round($approx_qty);
                                          @endphp
                                          @foreach ($gsm->details as $detail)
                                             @php 
                                                if($item->unitMaster->s_name=="KG"){
                                                   $quantity = $approx_qty;
                                                }else{
                                                   $quantity = $detail->quantity;
                                                }
                                                $freeze_quantity = 0;
                                                if(isset($item->saleOrderItem) && count($item->saleOrderItem)>0){
                                                   foreach ($item->saleOrderItem as $key => $value) {
                                                      foreach ($value->gsms as $k1 => $v1) {
                                                         foreach ($v1->details as $k2 => $v2) {
                                                            if($v2->estimate_quantity!=="" && $v2->estimate_quantity!=null){
                                                               if($detail->size."X".$gsm->gsm==$v2->size."X".$v1->gsm){
                                                                  $freeze_quantity = $freeze_quantity + $v2->estimate_quantity;
                                                               }
                                                            }
                                                         }
                                                      }
                                                   }
                                                   $freeze_quantity = $freeze_quantity - $detail->estimate_quantity;
                                                }
                                             @endphp
                                             <tr>
                                                <td>
                                                   <input type="text"  class="form-control size size_1_1" value="{{ $detail->size }}X{{$gsm->gsm}}" readonly >
                                                   <input type="hidden" value="{{$detail->id}}" name="size_id[]">
                                                </td>
                                                <td>
                                                   <input type="text" class="form-control" name="order_size_quantity[]" value="{{$quantity}}" readonly>
                                                </td>
                                                <td>
                                                   <input type="text" class="form-control" name="stock_size_quantity[]" value="@php if(isset($grouped[$item->item_id."X".$detail->size."X".$gsm->gsm])){
                                                   echo count($grouped[$item->item_id."X".$detail->size."X".$gsm->gsm])-$freeze_quantity;}else{ echo 0;} @endphp" readonly>
                                                </td>
                                                <td>
                                                   <input type="text" class="form-control size_quantity" placeholder="Quantity" name="size_quantity[]" value="{{$detail->estimate_quantity}}" data-stock="@php if(isset($grouped[$item->item_id."X".$detail->size."X".$gsm->gsm])){
                                                   echo count($grouped[$item->item_id."X".$detail->size."X".$gsm->gsm])-$freeze_quantity;}else{ echo 0;} @endphp">
                                                </td>
                                             </tr>
                                          @endforeach
                                       </table>
                                    </div>
                                 @endforeach
                              </div>
                           </div>
                        @endforeach
                     </div>                    
                     <div class="d-flex">
                        <div class="ms-auto">
                            <input type="submit" value="SAVE" class="btn btn-primary">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">QUIT</a>
                        </div>
                    </div>
                  </form>
               </div>
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

</body>
@include('layouts.footer')
<script>
   $(document).ready(function(){
      $(".size_quantity").keyup(function(){
         let stock = $(this).attr('data-stock');
         let quantity = $(this).val();
         if(parseInt(quantity)>parseInt(stock)){
            $(this).val('');
            alert("Invalid Quantity");
         }
      });
   });
</script>
@endsection