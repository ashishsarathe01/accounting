@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<style>
   .btn-remove-complete {
    background: none;
    border: 1px solid #dc3545;
    color: #dc3545;
    font-size: 12px;
    padding: 4px 10px;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 6px;
}

.btn-remove-complete:hover {
    background: #dc3545;
    color: #fff;
}

.saved-row {
    background: #fff;
}

.saved-gsm {
    font-weight: 600;
    margin-top: 6px;
}

.saved-combo {
    padding-left: 24px;
    font-size: 14px;
    line-height: 1.6;
}

.saved-remove {
    padding-left: 24px;
    color: #c00;
    cursor: pointer;
    font-size: 13px;
}

.saved-separator td {
    border-bottom: 1px solid #ddd;
}

.print-btn {
    font-size: 13px;
}
</style>
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
            @php
               $savedDeckleItems = [];

               foreach ($pendingOrders as $value) {
                  foreach ($value->items as $item) {
                     foreach ($item->gsms as $gsm) {
                        foreach ($gsm->details as $detail) {
                           if (isset($detail->set_sale_status) && $detail->set_sale_status == 1) {

                              $savedDeckleItems[$value->id][$item->id]['sale'] = $value;
                              $savedDeckleItems[$value->id][$item->id]['item'] = $item;
                              $savedDeckleItems[$value->id][$item->id]['sizes'][] = [
                                 'size' => $detail->size,
                                 'gsm'  => $gsm->gsm
                              ];
                           }
                        }
                     }
                  }
               }
            @endphp

            @php
               $pendingList = [];
               $savedList   = [];
               
               foreach ($pendingOrders as $value) {

                  $pendingCount = 0;

                  foreach ($value->items as $item) {
                     foreach ($item->gsms as $gsm) {
                        foreach ($gsm->details as $detail) {
                           if(!isset($detail->set_sale_status) || $detail->set_sale_status == 0){
                              $pendingCount++;
                           }
                        }
                     }
                  }

                  if ($pendingCount > 0) {
                     //$pendingList[] = $value;
                  } else {
                     $savedList[] = $value;
                  }
                  $pendingList[] = $value;
               }
               
            @endphp
               <div class="table-title-bottom-line position-relative d-flex align-items-center gap-3
                            bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

                            <h5 class="transaction-table-title m-0 py-2">
                                Pending Sales Orders
                            </h5>
                        
                            <select class="form-select sale-order-status w-auto p-2">
                                <option value="">select</option>
                                <option value="0">Pending</option>
                                <option value="4">Ready To Dispatch</option>
                                <option value="1">Completed</option>
                                <option value="2">Cancelled</option>
                            </select>
                        
                </div>
               <div class="transaction-table bg-white table-view shadow-sm mb-5">
                  <table class="table-striped table m-0 shadow-sm sale_table_pending">
                     <thead>
                        <tr class="font-12 text-body bg-light-pink">
                           <td>#</td>
                           <th style="width: 9%;">Date</th>
                           <th>Sale Order No.</th>
                           <th>Bill To</th>
                           <th>Order Detail</th>
                           <th class="w-min-120 text-center">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        
                        @foreach ($pendingList as $value)

                           {{-- 🔍 Count pending sizes for this sale order --}}
                           @php 
                              $pendingCount = 0; $pendingCount1 = 0;
                              foreach ($value->items as $item) {
                                    foreach ($item->gsms as $gsm) {
                                       foreach ($gsm->details as $detail) {
                                          if($detail->set_sale_status == 1){
                                                $pendingCount++;
                                          }
                                          if($detail->set_sale_status == 0){
                                                $pendingCount1++;
                                          }
                                       }
                                    }
                              }
                           @endphp
                           {{-- 🚫 Skip row if all sizes are already saved --}}
                           @if($pendingCount == 0)
                              {{-- @continue --}}
                           @endif

                           <tr>
                              <td><input type="checkbox" class="check_option" value="{{ $value->id }}" @if($pendingCount1==0) disabled @endif></td>
                              <td class="date_column">{{ date('d-m-Y', strtotime($value->created_at)) }}</td>
                              <td>{{ $value->sale_order_no }}</td>
                              <td>{{ $value->billTo->account_name ?? '-' }}</td>

                              <td>
                           @php $estimate_quantity_status = 0; @endphp

                           @foreach ($value->items as $item)

                              {{-- Count pending sizes for this item --}}
                              @php 
                                    $itemPending = 0;
                                    foreach ($item->gsms as $gsm) {
                                       foreach ($gsm->details as $detail) {
                                          if(!isset($detail->set_sale_status) || $detail->set_sale_status == 0){
                                                $itemPending++;
                                          }
                                       }
                                    }
                              @endphp

                           @php
                              $isItemCompleted = ($itemPending == 0);
                           @endphp
                              <div class="mb-2"
                                 style="{{ $isItemCompleted ? 'background:#f1f8f4;padding:6px;border-radius:4px;' : '' }}">
                                 {{ $item->sub_unit }}
                              <br>
                                 <strong>
                                    {{ $item->item->name }}
                                    @if($isItemCompleted)
                                          <span style=" background:#e6f4ea; color:#137333; font-size:12px; padding:2px 6px; border-radius:4px;margin-left:6px; "> Completed </span> @endif </strong><br>

                                    @foreach ($item->gsms as $gsm)
                                       @foreach ($gsm->details as $detail)

                                          {{-- mark estimate qty --}}
                                          @if($detail->estimate_quantity!="")
                                                @php $estimate_quantity_status = 1; @endphp
                                          @endif

                                          {{-- show only pending sizes --}}
                                          <span class="d-block {{ (isset($detail->set_sale_status) && $detail->set_sale_status == 1) ? 'text-muted' : '' }}">
                                             Size: <strong>
                                                @if($item->sub_unit!="INCH")
                                                   @php 
                                                      if($item->sub_unit=="CM"){
                                                         $length_inch = round($detail->size/2.54,2);
                                                         echo $detail->size." CM (".$length_inch." INCH)X".$gsm->gsm;
                                                      }
                                                      if($item->sub_unit=="MM"){
                                                         $length_inch = round($detail->size/25.4,2);
                                                         echo $detail->size." MM (".$length_inch." INCH)X".$gsm->gsm;
                                                      }
                                                   @endphp
                                                @else
                                                   {{ $detail->size }}X{{ $gsm->gsm }}
                                                @endif
                                             </strong>
                                             — Qty: <strong>{{ $detail->quantity }} {{ $item->unitMaster->s_name }}</strong>
                                          </span>
                                       @endforeach
                                    @endforeach
                              </div>
                           @endforeach
                        </td>
                           <td>
                                 <a href="{{ route('set-sale-order-quantity', $value->id) }}">
                                    <button class="btn btn-info" style="padding: 2px 6px;font-size: 15px;line-height: 1;">Set Quantity</button>
                                    @if($estimate_quantity_status == 1)
                                       <i class="fa-solid fa-check" style="color: green;font-size: 20px;"></i>
                                    @endif
                                 </a>

                                 @if($estimate_quantity_status == 1)
                                    <br><br>
                                    <button class="btn btn-success ready_to_disptach" data="{{ $value->id }}" style="padding: 2px 6px;font-size: 15px;line-height: 1;">
                                       Ready To Disptach
                                    </button>
                                 @endif
                                    @if($pendingCount==0)
                                 <br><br>
                                 <button class="btn btn-warning back_to_pending" data="{{ $value->id }}" style="padding: 2px 6px;font-size: 15px;line-height: 1;">
                                    Back To Pending
                                 </button>
                                 @endif
                           </td>

                        </tr>

                     @endforeach

                        <tr>
                           <td colspan="6" style="text-align: center"><button class="btn btn-success set_deckle">Set Deckle</button></td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div class="table-title-bottom-line bg-plum-viloet px-4 py-2">
                  <h5 class="transaction-table-title m-0">Saved Sale Orders</h5>
               </div>

               <div class="transaction-table bg-white shadow-sm mb-5">
               <table class="table table-striped m-0">
                  <thead>
                     <tr>
                        <th>Date</th>
                        <th>Sale Order(s)</th>
                        <th>Bill To(s)</th>
                        <th>Item</th>
                        <th>GSM</th>
                        <th>Combination</th>
                     </tr>
                  </thead>
                  @php
                  $groupedRows = collect($savedDeckleGroups)->groupBy(function ($row) {
                     return $row['date'].'|'.$row['sale_orders'].'|'.$row['bill_tos'].'|'.$row['item_name'];
                  });
                  @endphp
                  <tbody>
                     @foreach($groupedRows as $rows)
                        {{-- HEADER ROW --}}
                        <tr class="saved-row">
                           <td>{{ $rows->first()['date'] }}</td>
                           <td>{{ $rows->first()['sale_orders'] }}</td>
                           <td>{{ $rows->first()['bill_tos'] }}</td>
                           <td><strong>{{ $rows->first()['item_name'] }}</strong></td>
                           <td colspan="2"></td>
                        </tr>
                        {{-- GSM + COMBINATIONS --}}
                        @foreach($rows as $row)
                           <tr>
                              <td colspan="4"></td>
                              <td colspan="2">
                                 <div class="saved-gsm">GSM {{ $row['gsm'] }}</div>
                                 @php $total_size = 0; $total_manual_size = 0;@endphp
                                 @foreach($row['filler'] as $combo)
                                    @if (count($combo)>0)
                                       @php $total_manual_size = $total_manual_size + count($combo); @endphp
                                    @endif
                                 @endforeach
                                 @foreach($row['combinations'] as $combo)
                                    @php $total_size = $total_size + count(explode(' + ', $combo)); @endphp
                                    <div class="saved-combo"> {{ $loop->iteration }}.)  {{ $combo }}</div>
                                 @endforeach
                                 <strong>Total Sizes: {{ $total_size }}</strong><br>
                                 <strong>Total Manual Sizes: {{ $total_manual_size }}</strong><br>
                                 <button class="btn-remove-complete remove-complete-gsm"
                                    data-item="{{ $row['item_id'] }}"
                                    data-gsm="{{ $row['gsm'] }}"
                                    data-so='@json($row['sale_order_ids'])'>
                                    Remove Combination
                                 </button>
                              </td>
                           </tr>
                        @endforeach
                        {{-- PRINT ROW --}}
                        <tr>
                           <td colspan="4"></td>
                           <td colspan="2">
                              <button class="btn btn-sm btn-outline-secondary print-btn print-saved-group" data-group='@json($rows)'>Print</button>
                           </td>
                        </tr>
                        <tr class="saved-separator"><td colspan="6"></td></tr>
                     @endforeach
                  </tbody>
               </table>
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
<!-- Modal: Select Item -->
<div class="modal fade" id="selectItemModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h5 class="modal-title">Select Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
      <div id="prioritySaleOrders" class="mb-3">
            <h6 class="fw-bold">Selected Sale Orders (Priority Order)</h6>
            <table class="table table-sm table-bordered mb-2">
               <thead class="table-light">
                  <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Sale Order</th>
                        <th>Bill To</th>
                  </tr>
               </thead>
               <tbody id="prioritySaleOrdersBody">
                  <!-- dynamically filled -->
               </tbody>
            </table>
      </div>

      <label class="mt-2">Select Item</label>
      <select id="deckle_item_id" class="form-select">
            <option value="">-- Select Item --</option>
      </select>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" id="submitDeckleItem">Continue</button>
      </div>
    </div>
  </div>
</div>

@include('layouts.footer')
<script>
     let checkedOrder = [];
$(".set_deckle").click(function () {

    if (checkedOrder.length === 0) {
        alert("Please Select Sale Order");
        return;
    }

    $.ajax({
        url: "{{ route('sale-order.get-items') }}",
        type: "POST",
        data: {
            sale_orders: checkedOrder, // 🔥 priority preserved
            _token: "{{ csrf_token() }}"
        },
        success: function (res) {

            // ---------- Populate ITEM dropdown ----------
            $("#deckle_item_id").empty()
                .append('<option value="">-- Select Item --</option>');

            res.items.forEach(function (it) {
                $("#deckle_item_id").append(
                    `<option value="${it.item_id}">${it.item_name}</option>`
                );
            });

            // ---------- Populate PRIORITY SALE ORDERS ----------
            let tbody = $("#prioritySaleOrdersBody");
            tbody.empty();

            res.sale_orders.forEach(function (so, index) {
                tbody.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${so.date}</td>
                        <td>${so.sale_order_no}</td>
                        <td>${so.bill_to}</td>
                    </tr>
                `);
            });

            new bootstrap.Modal(
                document.getElementById('selectItemModal')
            ).show();
        },
        error: function () {
            alert("Unable to load data, try again.");
        }
    });
});

   $("#submitDeckleItem").click(function(){

    let itemId = $("#deckle_item_id").val();

    if(!itemId){
        alert("Please select an item");
        return;
    }

    let saleOrders = [];
    $(".check_option:checked").each(function(){
        saleOrders = checkedOrder;
    });

    let url = "{{ url('set-sale-order-deckle') }}" +
              "?sale_order=" + JSON.stringify(saleOrders) +
              "&item_id=" + itemId;

    window.location.href = url;
});
$(document).on('click','.ready_to_disptach',function(){   
   let sale_order_id = $(this).attr('data');
   if(!confirm("Are you sure want to Ready to Dispatch this Sale Order?")){
      return;
   }
   $.ajax({
      url:"{{route('sale-order.ready-to-dispatch')}}",
      type:"post",
      data:{_token:"{{csrf_token()}}",sale_order_id:sale_order_id},
      success:function(res){
         if(res.status==1){
            alert("Sale Order is Ready to Dispatch");
            window.location = "sale-order-start/"+sale_order_id;
         }else{
            alert(res.msg);
         }
      }
   });
});
$(document).on('click','.back_to_pending',function(){
   let id = $(this).attr('data');
   if(!confirm("Are you sure want to Back To Pending this Sale Order?")){
      return;
   }
   $.ajax({
      url:"{{route('sale-order.back-to-pending')}}",
      type:"post",
      data:{_token:"{{csrf_token()}}",id:id},
      success:function(res){
         if(res.status==1){
            alert("Sale Order is Back to Pending Successfully.");
            window.location = "sale-order-start/"+id;
            location.reload();
         }else{
            alert(res.msg);
         }
      }
   });
});
$(document).on('change', '.sale-order-status', function () {
        let status = $(this).val();
        window.location.href = "{{ route('sale-order.index') }}?status=" + status;
    });
   

    $(document).on('change', '.check_option', function () {
        let id = $(this).val();

        if (this.checked) {
            // add if not already present
            if (!checkedOrder.includes(id)) {
                checkedOrder.push(id);
            }
        } else {
            // remove if unchecked
            checkedOrder = checkedOrder.filter(v => v !== id);
        }

        console.log('Priority order:', checkedOrder);
    });

$(document).on('click', '.remove-complete-gsm', function () {

    if (!confirm("Remove complete combination for this GSM?")) return;

    let saleOrderIds = JSON.parse($(this).attr('data-so'));
    let itemId = $(this).data('item');
    let gsm = $(this).data('gsm');

    $.ajax({
        url: "{{ route('deckle.remove-complete') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            sale_order_ids: saleOrderIds,
            item_id: itemId,
            gsm: gsm
        },
        success: function (res) {
            if (res.status) {
                location.reload();
            } else {
                alert(res.msg || "Unable to remove combination");
            }
        },
        error: function () {
            alert("Server error while removing combination");
        }
    });
});

$(document).on('click', '.print-saved-group', function () {

    let rows = $(this).data('group');
    if (!rows || rows.length === 0) {
        alert('Nothing to print');
        return;
    }

    let saleOrders = rows[0].sale_orders || '';
    let billTos    = rows[0].bill_tos || '';

    let html = `
    <html>
    <head>
        <title>Deckle Size Combinations</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 13px;
                padding: 25px;
                color: #000;
            }

            h2 {
                text-align: center;
                margin-bottom: 12px;
                letter-spacing: 0.5px;
            }

            .meta {
                margin-bottom: 12px;
                font-size: 12.5px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th, td {
                border: 1px solid #000;
                padding: 8px;
                vertical-align: top;
            }

            th {
                background: #f5f5f5;
                font-weight: bold;
                text-align: left;
            }

            .item {
                width: 20%;
                font-weight: bold;
            }

            .gsm {
                width: 10%;
                text-align: center;
                font-weight: bold;
            }

            .combo-cell {
                width: 70%;
                padding: 0;
            }

            .combo-line {
                padding: 6px 8px;
                border-bottom: 1px solid #000;
            }

            .combo-line:last-child {
                border-bottom: none;
            }
        </style>
    </head>
    <body>

        <h2>DECKLE SIZE COMBINATIONS</h2>

        <div class="meta">
            <div><strong>Sale Order(s):</strong> ${saleOrders}</div>
            <div><strong>Bill To(s):</strong> ${billTos}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>GSM</th>
                    <th>Deckle Size Combinations</th>
                </tr>
            </thead>
            <tbody>`;
               let total_size = 0; let total_manual_size = 0;
      rows.forEach(row => {
        html += `
            <tr>
                <td class="item">${row.item_name}</td>
                <td class="gsm">${row.gsm}</td>
                <td class="combo-cell">`;
                  row.filler.forEach((combo, index) => {
                     total_manual_size += combo.length; 
                  });
                  row.combinations.forEach((combo, index) => {
                     total_size += combo.split(' + ').length; 
                     html += `<div class="combo-line">${index + 1}.) ${combo}</div>`;
                  });
         html += `
                  </td>
               </tr>
         `;
      });
      html += `
            <tr>
                <td></td>
                <td></td>
                <td> <strong>Total Sizes: ${total_size}</strong><br>
                  <strong>Total Manual Sizes: ${total_manual_size}</strong></td>
               </tr>`;
    html += `
            </tbody>
        </table>

    </body>
    </html>
    `;

    let win = window.open('', '_blank', 'width=900,height=650');
    win.document.open();
    win.document.write(html);
    win.document.close();
    win.focus();
    win.print();
    win.close();
});
</script>
@endsection