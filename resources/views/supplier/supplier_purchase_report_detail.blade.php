@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<style>
select.no-arrow {
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    background-image: none !important;
    padding-right: 0 !important;
}
.table-scroll-wrapper {
    width: 100%;
    overflow-x: auto;   /* scroll only when needed */
}

/* Let browser decide column width naturally */
.payment_table {
    width: 100%;
    table-layout: auto;
}

.payment_table th,
.payment_table td {
    white-space: nowrap;
}
.modal-scroll-body {
    max-height: calc(100vh - 80px);
    overflow-y: auto;
    outline: none; /* removes focus outline */
}
/* ===== Make Table Header Sticky ===== */

.table-scroll-wrapper {
    max-height: 70vh;      /* adjust height if needed */
    overflow-y: auto;
    overflow-x: auto;
}

/* Sticky header */
#purchase_table thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;   /* important so text doesn't overlap */
    z-index: 5;
}
</style>
<!-- list-view-company-section -->
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
               <h5 class="transaction-table-title m-0 py-2">Purchase Report ({{$account_name}}) @isset($from_date)- ({{date('d-m-Y',strtotime($from_date))}} TO {{date('d-m-Y',strtotime($to_date))}}) @endisset</h5>
               <a href="{{ url()->previous() }}"><button class="btn btn-info" style="float:right">Back</button></a>
               <div class="d-md-flex d-block">
               </div>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
                @php
                    $sub_heads = $heads
                        ->where('group_id', $group_id)
                        ->sortBy('sequence');
                @endphp
                @php
                $detailColumns = [
                    'date'            => 'Invoice Date',
                    'invoice'         => 'Invoice No./Slip No.',
                    'area'            => 'Area',
                    'net_weight'      => 'Net Weight',
                    'cut_weight'      => 'Cut Weight',
                    'actual_weight'   => 'Actual Weight',
                ];

                foreach ($sub_heads as $head) {
                    $detailColumns['sub_head_'.$head->id] = $head->name;
                }

                $detailColumns += [
                    'invoice_amount'  => 'Invoice Amount',
                    'gst_amount'      => 'GST Amount',
                    'taxable_amount'  => 'Taxable Amount',
                    'actual_amount'   => 'Actual Amount',
                    'actual_with_gst' => 'Actual + GST',
                    'payment'         => 'Payment', 
                    'billing_rate'    => 'Billing Rate',
                    'contract_rate'   => 'Contract Rate',
                    'difference'      => 'Difference',
                ];

                $selectedDetailColumns = request()->input(
                    'columns',
                    array_keys($detailColumns)
                );
                @endphp
                <form method="GET" class="mb-3 noprint px-3">
                    <div class="row">
                        <label class="fw-bold mb-1">Show Columns</label>
                        @foreach($detailColumns as $key => $label)
                            <div class="col-md-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        name="columns[]"
                                        value="{{ $key }}"
                                        {{ in_array($key, $selectedDetailColumns) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary btn-sm mt-2">Apply</button>
                </form>
                <div class="table-scroll-wrapper">
                
               <table class="table-bordered table m-0 shadow-sm payment_table" id="purchase_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th ><input type="checkbox" class="all_check"> ALL</th>
                        @if(in_array('date',$selectedDetailColumns))
                        <th style="text-align:left;">Invoice Date</th>
                        @endif

                        @if(in_array('invoice',$selectedDetailColumns))
                        <th style="text-align:right;">Invoice No./Slip No.</th>
                        @endif

                        @if(in_array('area',$selectedDetailColumns))
                        <th style="text-align:left;">Area</th>
                        @endif

                        @if(in_array('net_weight',$selectedDetailColumns))
                        <th data-column="net_weight" style="text-align:right;">Net Weight</th>
                        @endif

                        @if(in_array('cut_weight',$selectedDetailColumns))
                        <th data-column="cut_weight" style="text-align:right;">Cut Weight</th>
                        @endif

                        @if(in_array('actual_weight',$selectedDetailColumns))
                        <th data-column="actual_weight" style="text-align:right;">Actual Weight</th>
                        @endif

                        @foreach($sub_heads as $head)
                            @if(in_array('sub_head_'.$head->id, $selectedDetailColumns))
                                <th data-column="sub_head_{{$head->id}}" class="text-end">{{ $head->name }}</th>
                            @endif
                        @endforeach

                        @if(in_array('invoice_amount',$selectedDetailColumns))
                        <th data-column="invoice_amount" style="text-align:right;">Invoice Amount</th>
                        @endif

                        @if(in_array('gst_amount',$selectedDetailColumns))
                        <th data-column="gst_amount" style="text-align:right;">GST Amount</th>
                        @endif

                        @if(in_array('taxable_amount',$selectedDetailColumns))
                        <th data-column="taxable_amount" style="text-align:right;">Taxable Amount</th>
                        @endif

                        @if(in_array('actual_amount',$selectedDetailColumns))
                        <th data-column="actual_amount" style="text-align:right;">Actual Amount</th>
                        @endif

                        @if(in_array('actual_with_gst',$selectedDetailColumns))
                        <th data-column="actual_with_gst" style="text-align:right;">Actual + GST</th>
                        @endif

                        @if(in_array('payment',$selectedDetailColumns))
                        <th data-column="payment" style="text-align:right;">Payment</th>
                        @endif

                        @if(in_array('billing_rate',$selectedDetailColumns))
                        <th data-column="billing_rate" style="text-align:right;">Billing Rate</th>
                        @endif

                        @if(in_array('contract_rate',$selectedDetailColumns))
                        <th data-column="contract_rate" style="text-align:right;">Contract Rate</th>
                        @endif

                        @if(in_array('difference',$selectedDetailColumns))
                        <th data-column="difference" style="text-align:right;">Difference</th>
                        @endif
                        
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:center;">Detail</th>
                    </tr>
                  </thead>
                  <tbody>
                       @php 
                       $total_invoice_amount=0;
                       $total_gst_amt=0;
                       $total_taxable_amt=0;
                       $total_voucher_amt=0;
                       $total_diff_amt=0;
                       $total_actual_with_gst = 0;
                       $total_payment = 0;
                       $date_net_weight = 0;
                        $date_cut_weight = 0;
                        $date_actual_weight = 0;
                        $headTotals = []; 
                       @endphp
                        @foreach($purchases as $key => $value)

                        @if(isset($value->is_payment_only) && $value->is_payment_only)
                        @php
                        $total_payment += $value->payment_amount;
                        @endphp
                        <tr style="background:#e8f7ff;">
                        <td>
                            <input type="checkbox" class="check_row payment_check"
                                data-amount="{{ $value->payment_amount }}">
                        </td>
                            @if(in_array('date',$selectedDetailColumns))
                                <td>{{ date('d-m-Y', strtotime($value->date)) }}</td>
                            @endif
                            @if(in_array('invoice',$selectedDetailColumns))
                                <td>Payment</td>
                            @endif
                            @if(in_array('area',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('net_weight',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('cut_weight',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('actual_weight',$selectedDetailColumns)) <td></td> @endif
                            @foreach($sub_heads as $head)
                                @if(in_array('sub_head_'.$head->id, $selectedDetailColumns))
                                    <td></td>
                                @endif
                            @endforeach
                            @if(in_array('invoice_amount',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('gst_amount',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('taxable_amount',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('actual_amount',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('actual_with_gst',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('payment',$selectedDetailColumns))
                                <td style="text-align:right; font-weight:bold;">
                                    {{ formatIndianNumber($value->payment_amount,2) }}
                                </td>
                            @endif
                            @if(in_array('billing_rate',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('contract_rate',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('difference',$selectedDetailColumns)) <td></td> @endif
                            <td></td>
                        </tr>
                        @continue
                        @endif
                        @php
                            $contract_rate = "";
                            $headMap = [];
                            foreach (($value->purchaseReport ?? []) as $rp) {
                                $headMap[$rp->head_id] = $rp;
                                if (!isset($headTotals[$rp->head_id])) {
                                $headTotals[$rp->head_id] = 0;
                            }
                            $headTotals[$rp->head_id] += $rp->head_qty ?? 0;
                                if(!empty($rp->head_qty) && $rp->head_qty!=0 && $rp->head_contract_rate!=0){
                                            $contract_rate.=$rp->head_contract_rate." , ";
                                        }
                            }
                        @endphp

                        @php
                           $report_total = 0;
                           foreach(($value->purchaseReport ?? []) as $key => $v){
                              if($v->head_qty!="" && $v->head_qty!=0){
                                 $report_total = $report_total + ($v->head_qty*$v->head_contract_rate);
                              }
                           }
                           $total_invoice_amount += $value->total;
                           $total_gst_amt += $value->gst_rate;
                           $total_taxable_amt += $value->taxable_amt;
                           $total_voucher_amt += $report_total;
                           $actual_with_gst = $report_total + $value->gst_rate;
                           $total_actual_with_gst += $actual_with_gst;
                           $total_diff_amt += $value->difference_total_amount;
                           if(isset($value->payment_amount)){
                                $total_payment += $value->payment_amount;
                            }
                            $cut = $headMap['cut']->head_qty ?? 0;
                            $actual_weight = $value->gross_weight - $value->tare_weight - $cut;
                            $date_net_weight = $date_net_weight + $value->gross_weight - $value->tare_weight;
                            $date_cut_weight  += $cut;
                            $date_actual_weight  += $actual_weight;
                           @endphp
                            <tr>
                                <td><input type="checkbox" class="check_row" data-id="{{$value->id}}" data-amount="{{$value->difference_total_amount}}"></td>
                                @if(in_array('date',$selectedDetailColumns))
                                <td>{{date('d-m-Y',strtotime($value->purchase_date ?? $value->date))}}</td>
                                @endif

                                @if(in_array('invoice',$selectedDetailColumns))
                                <td>{{$value->invoice_no}} / {{$value->voucher_no}}</td>
                                @endif

                                @if(in_array('area',$selectedDetailColumns))
                                <td>
                                    @if(isset($value->locationInfo)) 
                                        {{$value->locationInfo->name??''}} 
                                    @endif
                                </td>
                                @endif

                                @if(in_array('net_weight',$selectedDetailColumns))
                                <td>{{$value->gross_weight - $value->tare_weight}}</td>
                                @endif

                                @if(in_array('cut_weight',$selectedDetailColumns))
                                <td>{{$cut}}</td>
                                @endif

                                @if(in_array('actual_weight',$selectedDetailColumns))
                                <td>{{$actual_weight}}</td>
                                @endif
                                @foreach($sub_heads as $head)
                                    @if(in_array('sub_head_'.$head->id, $selectedDetailColumns))
                                        @php
                                            $qty  = $headMap[$head->id]->head_qty ?? 0;
                                            $rate = $headMap[$head->id]->head_contract_rate ?? 0;
                                        @endphp
                                        <td class="text-end">
                                            @if($qty > 0)
                                                {{ number_format($qty, 2) }} ({{ number_format($rate, 2) }})
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                                @if(in_array('invoice_amount',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{formatIndianNumber($value->total,2)}}
                                </td>
                                @endif

                                @if(in_array('gst_amount',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{formatIndianNumber($value->gst_rate,2)}}
                                </td>
                                @endif

                                @if(in_array('taxable_amount',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{formatIndianNumber($value->taxable_amt,2)}}
                                </td>
                                @endif

                                @if(in_array('actual_amount',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{formatIndianNumber($report_total,2)}}
                                </td>
                                @endif

                                @if(in_array('actual_with_gst',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{formatIndianNumber($actual_with_gst,2)}}
                                </td>
                                @endif
                                @if(in_array('payment',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    @if(isset($value->payment_amount) && $value->payment_amount > 0)
                                        {{ formatIndianNumber($value->payment_amount,2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @endif
                                @if(in_array('billing_rate',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{ str_replace([',', '[', ']'], [' , ', '', ''], $value->prices);}}
                                </td>
                                @endif

                                @if(in_array('contract_rate',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{ rtrim($contract_rate, ' ,')}}
                                </td>
                                @endif

                                @if(in_array('difference',$selectedDetailColumns))
                                <td style="text-align:right;">
                                    {{formatIndianNumber($value->difference_total_amount,2)}}
                                </td>
                                @endif
                                <td>
                                    @php 
                                       $view_html = "";

                                       // MAIN VALUES
                                       $mainAccountName = $account_name;

                                       $invoice_date    = date("d-m-Y", strtotime($value->date));
                                       $invoice_no      = $value->invoice_no;
                                       $voucher_no      = $value->voucher_no;
                                       $area            = $value->locationInfo->name ?? 'N/A';

                                       $invoice_amount  = $value->total;
                                        $taxable_amount  = $value->taxable_amt;
                                       $gst_amount      = $value->gst_rate;
                                       $voucher_amount  = 0;
                                       foreach(($value->purchaseReport ?? []) as $v){
                                          if($v->head_qty != "" && $v->head_qty != 0){
                                             $voucher_amount += ($v->head_qty * $v->head_contract_rate);
                                          }
                                       }

                                       $difference      = $value->difference_total_amount;

                                       $view_html .= '
                                          <div style="margin-bottom:10px; font-size:14px;">
                                             <table style="width:100%; border:none;">
                                                <tr>
                                                   <td><strong>Account Name:</strong> '.$mainAccountName.'</td>
                                                   <td><strong>Invoice Date:</strong> '.$invoice_date.'</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Invoice No. / Slip No.:</strong> '.$invoice_no.' / '.$voucher_no.'</td>
                                                   <td><strong>Area:</strong> '.$area.'</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Invoice Amount:</strong> '.$invoice_amount.'</td>
                                                   <td><strong>Voucher Amount:</strong> '.$voucher_amount.'</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Taxable Amount:</strong> '.$taxable_amount.'</td>
                                                   <td><strong>GST Amount:</strong> '.$gst_amount.'</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Difference:</strong> '.$difference.'</td>
                                                   <td></td>
                                                </tr>
                                             </table>
                                          </div>
                                       ';

                                       $view_html .= '
                                       <table class="table table-bordered">
                                          <thead>
                                             <tr>
                                                   <td>Head</td>
                                                   <td>Qty</td>
                                                   <td>Bill Rate</td>
                                                   <td>Contract Rate</td>
                                                   <td>Difference Amount</td>
                                             </tr>
                                          </thead>
                                          <tbody>
                                       ';

                                       foreach(($value->purchaseReport ?? []) as $v){
                                          if($v->head_qty != "" && $v->head_qty != 0){

                                             $headName = $v->headInfo->name ?? $v->head_id;

                                             $view_html .= '
                                                   <tr>
                                                      <td>'.$headName.'</td>
                                                      <td>'.$v->head_qty.'</td>
                                                      <td style="text-align:right;">'.$v->head_bill_rate.'</td>
                                                      <td style="text-align:right;">'.$v->head_contract_rate.'</td>
                                                      <td style="text-align:right;">'.$v->head_difference_amount.'</td>
                                                   </tr>
                                             ';
                                          }
                                       }
                                       $view_html .= '
                                          <tr>
                                             <th></th><th></th><th></th><th></th>
                                             <th style="text-align:right;">'.$difference.'</th>
                                          </tr>
                                          </tbody>
                                       </table>';
                                       @endphp
                                        <button
                                            class="btn btn-info start {{ $value->group_id == $waste_group_id ? 'wastekraft' : 'boilerfuel' }}"
                                            data-id="{{ $value->id }}"
                                            data-group_id="{{ $value->group_id }}"
                                            data-map_purchase_id="{{ $value->map_purchase_id }}"
                                            data-account_id="{{ $value->account_id }}"
                                            data-gross_weight="{{ $value->gross_weight }}"
                                            data-purchase_voucher_no="{{ $value->purchase_voucher_no }}"
                                            data-purchase_date="{{ $value->purchase_date }}"
                                            data-purchase_amount="{{ $value->purchase_amount }}"
                                            data-purchase_qty="{{ $value->purchase_qty }}"
                                            data-purchase_taxable_amount="{{ $value->purchase_taxable_amount }}"
                                            data-purchase_price='{{ $value->prices }}'
                                            data-status="3"
                                            data-vehicle_no="{{ $value->vehicle_no }}"
                                            data-entry_date="{{ $value->entry_date }}"
                                        >
                                            View
                                        </button>
                                </div>
                            </tr>
                        @endforeach
                        <tr>
                            <td></td>

                            @if(in_array('date',$selectedDetailColumns))
                            <td class="fw-bold text-center">Total</td>
                            @endif

                            @if(in_array('invoice',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('area',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('net_weight',$selectedDetailColumns)) <td class="text-end fw-bold">{{ number_format($date_net_weight,2) }}</td> @endif
                            @if(in_array('cut_weight',$selectedDetailColumns)) <td class="text-end fw-bold">{{ number_format($date_cut_weight,2) }}</td> @endif
                            @if(in_array('actual_weight',$selectedDetailColumns)) <td class="text-end fw-bold">{{ number_format($date_actual_weight,2) }}</td> @endif

                            @foreach($sub_heads as $head)
                            @if(in_array('sub_head_'.$head->id, $selectedDetailColumns))
                                    <td class="text-end fw-bold">{{ isset($headTotals[$head->id]) ? number_format($headTotals[$head->id],2) : '-' }}</td>
                                @endif
                            @endforeach

                            @if(in_array('invoice_amount',$selectedDetailColumns))
                            <td class="text-end fw-bold">{{ formatIndianNumber($total_invoice_amount,2) }}</td>
                            @endif

                            @if(in_array('gst_amount',$selectedDetailColumns))
                            <td class="text-end fw-bold">{{ formatIndianNumber($total_gst_amt,2) }}</td>
                            @endif

                            @if(in_array('taxable_amount',$selectedDetailColumns))
                            <td class="text-end fw-bold">{{ formatIndianNumber($total_taxable_amt,2) }}</td>
                            @endif

                            @if(in_array('actual_amount',$selectedDetailColumns))
                            <td class="text-end fw-bold">{{ formatIndianNumber($total_voucher_amt,2) }}</td>
                            @endif

                            @if(in_array('actual_with_gst',$selectedDetailColumns))
                            <td class="text-end fw-bold">
                                {{ formatIndianNumber($total_actual_with_gst,2) }}
                            </td>
                            @endif
                            @if(in_array('payment',$selectedDetailColumns))
                            <td class="text-end fw-bold">
                                {{ formatIndianNumber($total_payment,2) }}
                            </td>
                            @endif
                            @if(in_array('billing_rate',$selectedDetailColumns)) <td></td> @endif
                            @if(in_array('contract_rate',$selectedDetailColumns)) <td></td> @endif

                            @if(in_array('difference',$selectedDetailColumns))
                            <td class="text-end fw-bold">{{ formatIndianNumber($total_diff_amt,2) }}</td>
                            @endif

                            <td></td>
                        </tr>
                        <tr>
                           <td colspan="{{ 9 + $sub_heads->count() }}" style="text-align: center;">
                              <button class="btn btn-info action" data-action_account_id="{{$id}}">Action</button>
                              <button class="btn btn-secondary print_selected">Print Selected</button>
                              <button class="btn btn-secondary csv_selected">CSV Selected</button>
                           </td>
                        </tr>
                  </tbody>
               </table>
                </div>
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
<div class="modal fade" id="action_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered w-360">
        <div class="modal-content p-4 border-divider border-radius-8 shadow-sm">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h5 class="mb-4 fw-semibold">Choose an Action</h5>
                <div class="d-flex flex-column gap-3 text-start">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="debit_note" value="debit_note">
                        <label class="form-check-label fw-normal" for="debit_note">
                            Create Debit Note
                        </label>
                    </div>
                     <div class="form-check">
                     <input class="form-check-input" type="radio" name="action_type" id="credit_note" value="credit">
                     <label class="form-check-label fw-normal" for="credit_note">
                        Create Credit Note
                     </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="create_journal" value="journal">
                        <label class="form-check-label fw-normal" for="create_journal">
                            Create Journal
                        </label>
                    </div>
                    <!--<div class="form-check">-->
                    <!-- <input class="form-check-input" type="radio" name="action_type" id="cancel_receipt" value="cancel">-->
                    <!-- <label class="form-check-label fw-normal" for="cancel_receipt">-->
                    <!--    Cancel Receipt-->
                    <!-- </label>-->
                    <!--</div> -->
                </div>
            </div>
            <input type="hidden" value="" id="action_data" name="action_data" />
            <input type="hidden" value="" id="action_account_id" name="action_account_id" />
            <!-- Footer -->
            <div class="modal-footer border-0 p-0 mt-4 justify-content-center">
               <button type="button" class="btn btn-red px-4 perform_action">Submit</button>
            </div>
      </div>
   </div>
</div>
<div class="modal fade" id="view_detail_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 border-divider border-radius-8 shadow-sm">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h5 class="mb-4 fw-semibold">Details</h5>
                <div class="d-flex flex-column gap-3 text-start detail_div">
                    
                </div>
            </div>
      </div>
   </div>
</div>

</div>
</body>
<div class="modal fade" id="wastekraft_report_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content p-4 border-divider border-radius-8">

            <div class="modal-header border-0 p-0">
                
                <button type="button"
        class="btn btn-danger report_modal_close"
        data-bs-dismiss="modal"
        style="
            position:absolute;
            top:10px;
            right:10px;
            padding:8px 22px;
            cursor:pointer;
            display:inline-block;
            width:auto;
        ">
    Close
</button>


            </div>

            
            <div class="modal-body p-4 modal-scroll-body" tabindex="0">
                <div class="row">

                    <div class="col-md-7 left-section">
                        <!-- FORM FIELDS START -->
                    <div class="row">

                        <div class="mb-3 col-md-3">
                            <label for="account_id" class="form-label font-14 font-heading">Account Name</label>
                            <select id="account_id" class="form-select">
                                @foreach($accounts as $key => $value)
                                    <option value="{{$value->id}}">{{$value->account_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="entry_date" class="form-label font-14 font-heading">Date</label>
                            <input type="date" id="entry_date" class="form-control"/>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="group_id" class="form-label font-14 font-heading">Item Group</label>
                            <select id="group_id" class="form-select">
                                @foreach($item_groups as $key => $value)
                                    <option value="{{$value->id}}">{{$value->group_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="vehicle_no" class="form-label font-14 font-heading">Vehicle No.</label>
                            <input type="text" id="vehicle_no" class="form-control"/>
                        </div>

                        <div class="mb-3 col-md-3 short_weight_div">
                            <label for="tare_weight" class="form-label font-14 font-heading">Gross Weight</label>
                            <input type="text" id="gross_weight" class="form-control"/>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="tare_weight" class="form-label font-14 font-heading">Tare Weight</label>
                            <input type="number" step="any" min="1" id="tare_weight" class="form-control" placeholder="Tare Weight"/>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label for="voucher_no" class="form-label font-14 font-heading">Slip Number</label>
                            <input type="text" id="voucher_no" class="form-control" placeholder="Slip Number"/>
                        </div>

                        <div class="mb-3 col-md-3 area_div">
                            <label for="location" class="form-label font-14 font-heading">Area</label>
                            <select id="location" class="form-select">
                                <option value="">Select Area</option>
                                @foreach($locations as $loc)
                                    <option value="{{$loc->id}}">{{$loc->name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3 col-md-3 bill_url_div">
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

                    </div>
                    <!-- FORM FIELDS END -->

                    <!-- FIX: CLOSE THE ABOVE ROW BEFORE TABLE -->
                   

                    <div class="mb-12 col-md-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Head </th>
                                    <th id="net_weight_view" style="text-align: right;width: 18%;"></th>
                                    <input type="hidden" id="net_weight">
                                    <input type="hidden" id="row_id">
                                    <th style="text-align: right;width: 16%;">Bill Rate</th>
                                    <th style="text-align: right;width: 15%;">Contract Rate</th>
                                    <th style="text-align: right">Report Amount</th>
                                    <th style="width: 19%;">Difference Amount</th>
                                </tr>
                            </thead>

                            <tbody id="report_body">

                                @foreach($heads as $key => $value)
                                    @if($value->group_type=='WASTE KRAFT')
                                        <tr class="head waste_head">
                                            <td><input type="text" class="form-control" value="{{$value->name}}" readonly></td>
                                            <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_{{$value->id}}" style="text-align: right" data-id="{{$value->id}}"></td>
                                            <td>
                                                <select class="form-select calculate bill_rate" id="bill_rate_{{$value->id}}" data-id="{{$value->id}}"></select>
                                            </td>
                                            <td><input type="text" class="form-control contract_rate calculate" id="contract_rate_{{$value->id}}" style="text-align: right" readonly data-id="{{$value->id}}"></td>
                                            <td><input type="text" class="form-control report_amount" id="report_amount_{{$value->id}}" data-id="{{$value->id}}" style="text-align: right" readonly></td>
                                            <td><input type="text" class="form-control difference_amount" id="difference_amount_{{$value->id}}" data-id="{{$value->id}}" style="text-align: right" readonly></td>
                                        </tr>
                                    @endif
                                @endforeach

                                <tr id="cut_row">
                                    <td><input type="text" class="form-control" value="Cut" readonly></td>
                                    <td><input type="text" class="form-control calculate qty" placeholder="Enter Qty" id="qty_cut" style="text-align: right" data-id="cut"></td>
                                    <td><input type="text" class="form-control calculate bill_rate" readonly id="bill_rate_cut" style="text-align: left" data-id="cut"></td>
                                    <td><input type="text" class="form-control" id="contract_rate_cut" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control report_amount" id="report_amount_cut" data-id="cut" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control difference_amount" id="difference_amount_cut" data-id="cut" style="text-align: right" readonly></td>
                                </tr>

                                <tr id="short_weight_row">
                                    <td><input type="text" class="form-control" value="Short Weight" readonly></td>
                                    <td><input type="text" class="form-control calculate" readonly id="qty_short_weight" style="text-align: right" data-id="short_weight"></td>
                                    <td>
                                        <select class="form-select calculate bill_rate" id="bill_rate_short_weight" data-id="short_weight"></select>
                                    </td>
                                    <td><input type="text" class="form-control" id="contract_rate_short_weight" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control report_amount" id="report_amount_short_weight" data-id="short_weight" style="text-align: right" readonly></td>
                                    <td><input type="text" class="form-control difference_amount" id="difference_amount_short_weight" data-id="short_weight" style="text-align: right" readonly></td>
                                </tr>

                                <tr>
                                    <td></td><td></td><td></td>
                                    <th style="text-align: right"></th>
                                    <td><input type="text" class="form-control" id="report_amount_total" style="text-align: right" readonly></td>
                                    <th><input type="text" class="form-control" id="difference_total_amount" style="text-align: right" readonly></th>
                                </tr>

                                <tr>
                                    <th colspan="6" style="text-align: right">
                                        <span id="invoice_taxable_amount"></span> | 
                                        <span id="total_report_amount"></span>
                                    </th>
                                </tr>

                            </tbody>
                        </table>
                        <div class="text-start">
                            @can('view-module', 194)
                            <button type="button" class="btn btn-xs-primary save_location" style="padding: 2px 6px;
    font-size: 15px;
    line-height: 1.2;">SAVE</button>
    @endcan
                            @can('view-module', 197)
                            <button class="btn btn-success approve" style="display: none;    padding: 2px 6px;
    font-size: 15px;
    line-height: 1.2;">Approve</button>
    @endcan
                             @can('view-module', 198)
                            <a href="" id="edit_purchase_url"><button class="btn btn-success edit_purchase" style="display: none;    padding: 2px 6px;
    font-size: 15px;
    line-height: 1.2;">Edit Purchase</button></a>
    @endcan
                             @can('view-module', 199)
                            <button class="btn btn-warning revert_in_process" style="display: none;    padding: 2px 6px;
    font-size: 15px;
    line-height: 1.2;">Revert In Process</button>
    @endcan
                        </div>
                    </div>
                    </div>

                    <div class="col-md-5 right-section position-relative">  

                       <a id="wk_view_bill" class="btn btn-info view-bill-btn" style="display:none;">
                        View Bill
                    </a>
                    
                    
                        <div id="imageCarousel" class="carousel slide" style="display:none;margin-top:115px">
                            <div class="carousel-inner image_div"></div>
                    
                            <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                    
                            <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>



                </div>
            </div>

            {{-- <div class="modal-footer">
                <button class="btn btn-xs-primary save_location">SAVE</button>
                <button class="btn btn-success approve" style="display:none">Approve</button>
            </div> --}}
            
<div class="modal-footer">

                
            </div>
            <div class="row">

                <!-- LEFT SIDE -->
                <div class="col-md-6 left-scroll-area">

                    
                    
                </div>

                <!-- RIGHT SIDE -->
                <div class="col-md-6 left-scroll-area">
                    
                </div>

            </div>

            

        </div>
    </div>
</div>

<div class="modal fade" id="boilerfuel_report_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content p-4 border-divider border-radius-8">

            <!-- HEADER -->
            <div class="modal-header border-0 p-0">
               <button type="button"
        class="btn btn-danger report_modal_close"
        data-bs-dismiss="modal"
        style="position:absolute; top:10px; right:10px; display:block; padding:8px 20px; cursor:pointer;">
    Close
</button>

            </div>

            <!-- BODY -->
            <div class="modal-body p-4 modal-scroll-body" tabindex="0">
                <div class="row g-4">


                    <!-- LEFT SECTION -->
                    <div class="col-md-7 left-section">

                        <!-- FORM FIELDS -->
                        <div class="row g-4">


                            <div class="mb-3 col-md-3">
                                <label class="form-label">Account Name</label>
                                <select id="bf_account_id" class="form-select">
                                    @foreach($accounts as $value)
                                        <option value="{{$value->id}}">{{$value->account_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 col-md-3">
                                <label class="form-label">Date</label>
                                <input type="date" id="bf_entry_date" class="form-control"/>
                            </div>

                            <div class="mb-3 col-md-3">
                                <label class="form-label">Item Group</label>
                                <select id="bf_group_id" class="form-select">
                                    @foreach($item_groups as $value)
                                        <option value="{{$value->id}}">{{$value->group_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 col-md-3">
                                <label class="form-label">Vehicle No.</label>
                                <input type="text" id="bf_vehicle_no" class="form-control"/>
                            </div>

                            <div class="mb-3 col-md-3">
                                <label class="form-label">Gross Weight</label>
                                <input type="text" id="bf_gross_weight" class="form-control"/>
                            </div>

                            <div class="mb-3 col-md-3">
                                <label class="form-label">Tare Weight</label>
                                <input type="number" id="bf_tare_weight" class="form-control"/>
                            </div>

                            <div class="mb-3 col-md-3">
                                <label class="form-label">Slip Number</label>
                                <input type="text" id="bf_voucher_no" class="form-control"/>
                            </div>

                            <div class="mb-3 col-md-3 item_div">
                                <label class="form-label">Item</label>
                                <select id="bf_item" class="form-select">
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{$item->id}}">{{$item->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3 col-md-3">
                                <a href="" id="bf_bill_url">
                                    <button class="btn btn-info mt-4">Add Bill</button>
                                </a>
                            </div>

                            <!-- PURCHASE INFO -->
                            <div class="row purchase_div mt-2">
                                <div class="col-md-3">
                                    <label class="form-label">Purchase Invoice No.</label>
                                    <input type="text" id="bf_purchase_invoice_no"
                                        class="form-control bg-light" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Purchase Invoice Date</label>
                                    <input type="text" id="bf_purchase_invoice_date"
                                        class="form-control bg-light" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Purchase Invoice Qty</label>
                                    <input type="text" id="bf_purchase_invoice_qty"
                                        class="form-control bg-light" readonly>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Purchase Invoice Amount</label>
                                    <input type="text" id="bf_purchase_invoice_amount"
                                        class="form-control bg-light" readonly>
                                </div>
                            </div>


                        </div>

                        <!-- TABLE -->
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Head</th>
                                        <th id="bf_net_weight_view" class="text-end"></th>
                                        <input type="hidden" id="bf_net_weight">
                                        <input type="hidden" id="bf_row_id">
                                        <th class="text-end">Bill Rate</th>
                                        <th class="text-end">Contract Rate</th>
                                        <th class="text-end">Report Amount</th>
                                        <th class="text-end">Difference</th>
                                    </tr>
                                </thead>
                                <tbody id="bf_report_body">

                                    @foreach($heads as $value)
                                        @if($value->group_type=='BOILER FUEL')
                                        <tr class="fuel_head">
                                            <td><input class="form-control" value="{{$value->name}}" readonly></td>
                                            <td><input class="form-control qty calculate" id="bf_qty_{{$value->id}}" data-id="{{$value->id}}"></td>
                                            <td><input class="form-control bill_rate calculate" id="bf_bill_rate_{{$value->id}}" data-id="{{$value->id}}" readonly></td>
                                            <td><input class="form-control contract_rate calculate" id="bf_contract_rate_{{$value->id}}" readonly></td>
                                            <td><input class="form-control report_amount" id="bf_report_amount_{{$value->id}}" readonly></td>
                                            <td><input class="form-control difference_amount" id="bf_difference_amount_{{$value->id}}" readonly></td>
                                        </tr>
                                        @endif
                                    @endforeach

                                    <!-- CUT -->
                                    <tr id="cut_row">
                                        <td><input class="form-control" value="Cut" readonly></td>
                                        <td><input class="form-control qty calculate" id="bf_qty_cut" data-id="cut"></td>
                                        <td><input class="form-control bill_rate calculate" id="bf_bill_rate_cut" readonly></td>
                                        <td><input class="form-control" id="bf_contract_rate_cut" readonly></td>
                                        <td><input class="form-control report_amount" id="bf_report_amount_cut" readonly></td>
                                        <td><input class="form-control difference_amount" id="bf_difference_amount_cut" readonly></td>
                                    </tr>

                                    <!-- SHORT WEIGHT -->
                                    <tr id="short_weight_row">
                                        <td><input class="form-control" value="Short Weight" readonly></td>
                                        <td><input class="form-control" id="bf_qty_short_weight" readonly></td>
                                        <td><input class="form-control bill_rate" id="bf_bill_rate_short_weight" readonly></td>
                                        <td><input class="form-control" id="bf_contract_rate_short_weight" readonly></td>
                                        <td><input class="form-control report_amount" id="bf_report_amount_short_weight" readonly></td>
                                        <td><input class="form-control difference_amount" id="bf_difference_amount_short_weight" readonly></td>
                                    </tr>

                                    <tr>
                                        <td colspan="4"></td>
                                        <td><input class="form-control" id="bf_report_amount_total" readonly></td>
                                        <td><input class="form-control" id="bf_difference_total_amount" readonly></td>
                                    </tr>

                                    <tr>
                                        <th colspan="6" class="text-end">
                                            <span id="bf_invoice_taxable_amount"></span> |
                                            <span id="bf_total_report_amount"></span>
                                        </th>
                                    </tr>

                                </tbody>
                            </table>

                            <div class="text-start">
                                <button type="button" class="btn  btn-xs-primary bf_save_location">
                                    SAVE
                                </button>
                                <button class="btn btn-success bf_approve" style="display: none">Approve</button>
                                <a href="" id="edit_purchase_url"><button class="btn btn-success bf_edit_purchase" style="display: none">Edit Purchase</button></a>
                                        {{-- <button class="btn btn-danger reject" data-id="">Reject</button> --}}
                            </div>


                        </div>
                    </div>

                    <!-- RIGHT SECTION (IMAGES) -->
                    <div class="col-md-5 right-section">
                        <div id="bf_imageCarousel" class="carousel slide" style="display:none;margin-top:110px">
                            <div class="carousel-inner bf_image_div"></div>

                            <button class="carousel-control-prev" type="button" data-bs-target="#bf_imageCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#bf_imageCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@include('layouts.footer')
<script>
   $(".all_check").click(function(){
      $(".check_row").prop('checked',false);
      if($(this).prop('checked')==true){
         $(".check_row").prop('checked',true);
      }
   });
   $(".action").click(function(){
      let row_arr = [];
      $(".check_row").each(function(){
         if($(this).prop('checked')==true){
            row_arr.push({'id':$(this).attr('data-id'),'amount':$(this).attr('data-amount')});
         }
      });
      if(row_arr.length==0){
         alert("Please Select Entry");
         return;
      }
      $("#action_account_id").val($(this).attr('data-action_account_id'));
      $("#action_data").val(JSON.stringify(row_arr));
      $("#action_modal").modal('toggle');
   });
   $(".perform_action").click(function(){
      let action_data = $("#action_data").val();
      let action_account_id = $("#action_account_id").val();
      let selected_action = $('input[name="action_type"]:checked').val();
      if(!selected_action){
         alert("Choose an Action");
         return;
      }
      if(action_data==""){
         alert("Data Required");
         return;
      }
      if(action_account_id==""){
         alert("Account Id Required");
         return;
      }        
      if(selected_action=="debit_note"){
         window.location = "{{url('purchase-return/')}}/create?data="+action_data+"&account_id="+action_account_id
      }
      if(selected_action=="journal"){
        window.location = "{{url('journal/create')}}?data="+action_data+"&account_id="+action_account_id
      }
   });
   $(".view_detail").click(function(){
      let html = $(this).attr('data-html');
      $(".detail_div").html(html);
      $("#view_detail_modal").modal('toggle');
   });
   $(".print_selected").click(function () {

    let selectedRows = [];
    let selectedColumns = [];
    let columnTotals = [];

    // Columns where we DO NOT want totals
    let skipTotalColumns = ["Billing Rate", "Contract Rate"];

    // Get headers
    $('#purchase_table thead th')
        .not(':first-child')
        .not(':last-child')
        .each(function (index) {
            let headerText = $(this).text().trim();
            selectedColumns.push(headerText);

            // Initialize total only if not skipped
            if (!skipTotalColumns.includes(headerText)) {
                columnTotals[index] = 0;
            } else {
                columnTotals[index] = null; // mark as skip
            }
        });

    // Get selected rows
    $(".check_row:checked").each(function () {

        let row = $(this).closest("tr");
        let rowData = [];

        row.find("td")
            .not(':first-child')
            .not(':last-child')
            .each(function (index) {

                let text = $(this).text().trim();
                rowData.push(text);

                if (columnTotals[index] !== null) {
                    let number = text.replace(/,/g, '');
                    if (!isNaN(number) && number !== "") {
                        columnTotals[index] += parseFloat(number);
                    }
                }
            });

        selectedRows.push(rowData);
    });

    if (selectedRows.length === 0) {
        alert("Please select at least one row.");
        return;
    }

    let win = window.open("", "_blank");

    let html = `
    <html>
    <head>
    <style>
        body { font-family: Arial; }
        table { width:100%; border-collapse: collapse; }
        th,td { border:1px solid #000; padding:6px; font-size:12px; }
        th { background:#f2f2f2; }
        .total-row { font-weight:bold; background:#f9f9f9; }
    </style>
    </head>
    <body>
    <h3>Purchase Report - {{$account_name}}</h3>
    <table>
    <thead><tr>`;

    selectedColumns.forEach(col => {
        html += `<th>${col}</th>`;
    });

    html += `</tr></thead><tbody>`;

    // Add rows
    selectedRows.forEach(row => {
        html += "<tr>";
        row.forEach(col => {
            html += `<td>${col}</td>`;
        });
        html += "</tr>";
    });

    // Add TOTAL row
    html += `<tr class="total-row">`;
    columnTotals.forEach((total, index) => {

        if (index === 0) {
            html += `<td>Total</td>`;
        } else if (total !== null && total !== 0) {
            html += `<td>${total.toLocaleString('en-IN', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>`;
        } else {
            html += `<td></td>`;
        }
    });
    html += `</tr>`;

    html += "</tbody></table></body></html>";

    win.document.write(html);
    win.document.close();
    win.print();
});

var modal_open_status = 0;
var wasteModal = $("#wastekraft_report_modal");

$(document).on("click", ".start.wastekraft", function () {
        $("#cover-spin").show();
        wasteModal.find("#imageCarousel").hide();
        wasteModal.find(".approve").hide();
        wasteModal.find(".edit_purchase").hide();
        wasteModal.find(".contract_rate").attr('readonly',true);
        wasteModal.find(".waste_head").show();
        wasteModal.find(".fuel_head").hide();
        wasteModal.find(".item_div").hide();
        wasteModal.find(".area_div").show();

        let id = $(this).attr("data-id");
        let account_id = $(this).attr('data-account_id');
        let group_id = $(this).attr('data-group_id');
        let gross_weight = $(this).attr('data-gross_weight');
        let map_purchase_id = $(this).attr('data-map_purchase_id');
        let purchase_voucher_no = $(this).attr('data-purchase_voucher_no');
        let purchase_date = $(this).attr('data-purchase_date');
        let purchase_amount = $(this).attr('data-purchase_amount');
        let purchase_qty = $(this).attr('data-purchase_qty');
        let purchase_taxable_amount = $(this).attr('data-purchase_taxable_amount');
        let purchase_price = $(this).attr('data-purchase_price');
        let bill_price_options = "<option value=''>Bill Price</option>";
        let max_purchase_price = 0;
        let purchase_price_count = 1;
        if(purchase_price!=""){
            purchase_price = JSON.parse(purchase_price);
            max_purchase_price = Math.max(...purchase_price);
            purchase_price.forEach(function(e){
                bill_price_options+="<option value='"+e+"' data-qty_status='1'>"+e+"</option>";
            });
            purchase_price_count = purchase_price.length;
        }
        
        wasteModal.find(".bill_rate").html(bill_price_options);
        let price = "";
        if(purchase_taxable_amount!="" && purchase_qty!=""){
            price = purchase_taxable_amount/purchase_qty;
            price = price.toFixed(2);
        }
        wasteModal.find("#invoice_taxable_amount").html("Purchase Taxable Amount : "+purchase_taxable_amount);
        wasteModal.find("#difference_total_amount").css({ color: "black" });
        let status = $(this).attr('data-status');
        let vehicle_no = $(this).attr('data-vehicle_no');
        let entry_date = $(this).attr('data-entry_date');
        wasteModal.find(".save_location").attr('data-status', status);
        wasteModal.find(".qty").attr('readonly',false);
        if(status==2){
            wasteModal.find(".contract_rate").attr('readonly',false);
        }
        wasteModal.find(".approve").attr('data-id', id);        
        wasteModal.find("#account_id").val(account_id);
        wasteModal.find("#account_id").attr('data-id',id);  
        wasteModal.find("#entry_date").attr('data-id',id);
        wasteModal.find("#row_id").val(id);
        wasteModal.find("#gross_weight").val(gross_weight);
        wasteModal.find("#vehicle_no").val(vehicle_no);
        wasteModal.find("#group_id").val(group_id);
        wasteModal.find("#entry_date").val(entry_date);
        wasteModal.find(".purchase_div").hide();
        if((status==0 || status==1) && purchase_amount=="" && modal_open_status==0){
            wasteModal.find("#voucher_no").val('');
            wasteModal.find(".qty").val('');
            wasteModal.find("#qty_short_weight").val('');
            wasteModal.find(".contract_rate").val('');
            wasteModal.find("#contract_rate_short_weight").val('');
            wasteModal.find(".difference_amount").val('');
            wasteModal.find("#difference_amount_short_weight").val('');
            wasteModal.find("#difference_total_amount").val('');
            wasteModal.find("#tare_weight").val('');
        }else if(modal_open_status==1){
            wasteModal.find(".contract_rate").val('');
        }
        if((status==0 || status==1) && purchase_amount==""){
            wasteModal.find("#entry_date").attr('readonly',false);
            wasteModal.find("#vehicle_no").attr('readonly',false);
            wasteModal.find("#group_id").removeClass('unchange_dropdown');
            wasteModal.find("#account_id").removeClass('unchange_dropdown');
        }
        if(status==2 || status==3){
            let edit_purchase_url = "{{url('purchase-edit/map_id')}}?row_id=ids";
            edit_purchase_url = edit_purchase_url.replace('map_id',map_purchase_id);
            edit_purchase_url = edit_purchase_url.replace('ids',id);
            $("#edit_purchase_url").attr('href',edit_purchase_url);
            $(".edit_purchase").show();
            wasteModal.find(".revert_in_process").show();
             let view_purchase_url = "{{url('purchase-invoice/map_id')}}?row_id=ids";
            view_purchase_url = view_purchase_url.replace('map_id', map_purchase_id);
            view_purchase_url = view_purchase_url.replace('ids', id);
            
            $("#wk_view_bill").attr('href', view_purchase_url); // ▶ correct anchor
            $("#wk_view_bill").show();

        }
        if(map_purchase_id!="" && map_purchase_id!=0){
            wasteModal.find("#bill_url").hide();
            $("#purchase_invoice_no").val(purchase_voucher_no);
            $("#purchase_invoice_date").val(purchase_date);
            $("#purchase_invoice_amount").val(purchase_amount);
            $("#purchase_invoice_qty").val(purchase_qty);
            wasteModal.find("#bill_url").attr('href','');
            wasteModal.find(".purchase_div").show();
        }else{
            let bill_url = "{{url('purchase/create?row_id=row_id_value&account_id=account_id_value&group_id=group_id_value')}}";
            bill_url = bill_url.replace('row_id_value',id);
            bill_url = bill_url.replace('account_id_value',account_id);
            bill_url = bill_url.replace('group_id_value',group_id);
            bill_url = bill_url.replace(/&amp;/g, "&");
            wasteModal.find("#bill_url").show();
            wasteModal.find("#bill_url").attr('href', bill_url);
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
                wasteModal.find("#location").html(location_list);
                $.ajax({
                    url: "{{ url('view-complete-purchase-info') }}/" + id,
                    type:"POST",
                    data:{_token:'{{csrf_token()}}'},
                    success:function(res){
                        $("#cover-spin").hide();
                        if(res!=""){
                            let obj = JSON.parse(res);
                            
                            if(obj.purchase==null){
                                if(modal_open_status==0){
                                    wasteModal.modal('toggle');                               
                                }else if(modal_open_status==1){
                                    modal_open_status = 0;
                                }
                                return;
                            }
                            
                            let head_data_arr = [];
                            wasteModal.find("#difference_total_amount").val(obj.purchase.difference_total_amount);                            wasteModal.find("#voucher_no").val(obj.purchase.voucher_no);
                            if(modal_open_status==0){
                                wasteModal.find("#location").val(obj.purchase.location);
                            
                            }
                            wasteModal.find("#tare_weight").val(obj.purchase.tare_weight);
                            let gross_weight = wasteModal.find("#gross_weight").val();
                            let tare_weight = wasteModal.find("#tare_weight").val();
                            if(tare_weight=="" || tare_weight==null){
                                tare_weight = 0;
                            }
                            let net_weight = parseFloat(gross_weight) - parseFloat(tare_weight);
                            
                            wasteModal.find("#net_weight").val(net_weight);
                            wasteModal.find("#net_weight_view").html("Net Weight : "+net_weight);
                            
                            obj.reports.forEach(element => {
                                head_data_arr[element.head_id] = element;
                            });
                            let purchase_image = "";
                            wasteModal.find(".image_div").html(purchase_image);
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
                            wasteModal.find(".image_div").html(purchase_image);
                            wasteModal.find("#imageCarousel").show();
                            if(status==2){
                                wasteModal.find(".approve").show();
                            }
                            let report_amount_total = 0;
                            
                            wasteModal.find(".qty").each(function(){
                                let id = $(this).attr('data-id');
                                if(head_data_arr[id]){
                                    wasteModal.find("#qty_"+id).val(head_data_arr[id].head_qty);
                                    if(head_data_arr[id].head_qty!=0){
                                        if(purchase_price_count==1){
                                            $("#bill_rate_"+id).val(max_purchase_price);
                                        }else{
                                            let rate = parseFloat(head_data_arr[id].head_bill_rate).toString();
                                            $("#bill_rate_"+id).val(rate);
                                        }
                                    }else{
                                        $("#bill_rate_"+id).val(max_purchase_price);
                                    }
                                    if(modal_open_status==0){
                                        $("#contract_rate_"+id).val(head_data_arr[id].head_contract_rate);
                                        $("#report_amount_"+id).val(head_data_arr[id].head_contract_rate*head_data_arr[id].head_qty);
                                        $("#difference_amount_"+id).val(head_data_arr[id].head_difference_amount);
                                        report_amount_total = parseFloat(report_amount_total) + parseFloat(head_data_arr[id].head_contract_rate*head_data_arr[id].head_qty);
                                    }
                                }
                            });
                            let short_weight_id = "short_weight";
                            if(head_data_arr[short_weight_id]){
                                $("#qty_"+short_weight_id).val(head_data_arr[short_weight_id].head_qty);
                                //if(status!=1){
                                    let rate = parseFloat(head_data_arr[short_weight_id].head_bill_rate).toString();
                                    //$("#bill_rate_"+short_weight_id).val(rate);
                                    $("#bill_rate_"+short_weight_id).val(max_purchase_price);
                                    
                                //}
                                $("#contract_rate_"+short_weight_id).val(head_data_arr[short_weight_id].head_contract_rate);
                                $("#report_amount_"+short_weight_id).val(head_data_arr[short_weight_id].head_contract_rate*head_data_arr[short_weight_id].head_qty);
                                $("#difference_amount_"+short_weight_id).val(head_data_arr[short_weight_id].head_difference_amount);
                                report_amount_total = parseFloat(report_amount_total) + parseFloat(head_data_arr[short_weight_id].head_contract_rate*head_data_arr[short_weight_id].head_qty);
                            }
                            wasteModal.find("#bill_rate_cut").val(max_purchase_price);
                            wasteModal.find("#report_amount_total").val(report_amount_total);
                            let diff_cal_amount = parseFloat(purchase_taxable_amount) - parseFloat(report_amount_total);
                            let diff_cal_rate_amount = wasteModal.find("#difference_total_amount").val();
                            wasteModal.find("#total_report_amount").html("Total Report Amount : " + report_amount_total);
                            if(parseFloat(diff_cal_amount)!=parseFloat(diff_cal_rate_amount)){
                                wasteModal.find("#difference_total_amount").css({ color: "red" });
                            }
                            wasteModal.find(".calculate").each(function () {
                                $(this).keyup();
                            });

                            wasteModal.find("input").attr("readonly", true);
                            wasteModal.find("select").prop("disabled", true);

                            
                            wasteModal.find(".right_image_col").hide();
                            wasteModal.find(".left_content_col")
                                .removeClass("col-md-7")
                                .addClass("col-md-12");
                            wasteModal.find(".save_location").hide();
                            wasteModal.find(".edit_purchase").hide();
                            wasteModal.find(".approve").hide();
                            wasteModal.find("#bill_url").hide();

                            if(modal_open_status==0){
                                wasteModal.modal('toggle');                            
                            } else if(modal_open_status==1){
                                modal_open_status = 0;
                            }

                            
                        }
                        
                    }
                });
               
                
            }
        });
    });
    function printpage(){
      $('.header-section').addClass('importantRule');
      $('.sidebar').addClass('importantRule');
      window.print();
      $('.header-section').removeClass('importantRule');
      $('.sidebar').removeClass('importantRule');
   }
var fuelModal  = $("#boilerfuel_report_modal");

    $(document).on("click", ".start.boilerfuel", function () {
        $("#cover-spin").show();
        fuelModal.find("#bf_imageCarousel").hide();
        fuelModal.find(".bf_approve").hide();
        fuelModal.find(".bf_edit_purchase").val('');
        fuelModal.find(".bf_contract_rate").attr('readonly',true);        
        fuelModal.find(".waste_head").hide();
        fuelModal.find(".fuel_head").show();
        fuelModal.find(".item_div").show();
        fuelModal.find(".area_div").hide();

        let id = $(this).attr("data-id");
        let account_id = $(this).attr('data-account_id');
        fuelModal.find("#bf_account_id").val(account_id);
        let group_id = $(this).attr('data-group_id');
        fuelModal.find("#bf_account_id").val(account_id);
        let gross_weight = $(this).attr('data-gross_weight');
        let map_purchase_id = $(this).attr('data-map_purchase_id');
        let purchase_voucher_no = $(this).attr('data-purchase_voucher_no');
        let purchase_date = $(this).attr('data-purchase_date');
        let purchase_amount = $(this).attr('data-purchase_amount');
        let purchase_qty = $(this).attr('data-purchase_qty');
        let purchase_taxable_amount = $(this).attr('data-purchase_taxable_amount');
        // let price = $(this).attr('data-price');
       let price = 0;

        let taxable = parseFloat(purchase_taxable_amount);
        let qty     = parseFloat(purchase_qty);

        if (!isNaN(taxable) && !isNaN(qty) && qty > 0) {
            price = taxable / qty;
        }

        price = Number(price).toFixed(2);

        fuelModal.find("#bf_invoice_taxable_amount").html("Purchase Taxable Amount : "+purchase_taxable_amount);
        fuelModal.find("#bf_difference_total_amount").css({ color: "black" });
        let status = $(this).attr('data-status');
        let vehicle_no = $(this).attr('data-vehicle_no');
        let entry_date = $(this).attr('data-entry_date');
        fuelModal.find("[id^='bf_qty_']").attr('readonly',false);;
        if(status==2){
            fuelModal.find(".bf_contract_rate").attr('readonly',false);
        }
        fuelModal.find(".bf_approve").attr('data-id', id);        
        fuelModal.find("#bf_account_id").val(account_id);
        fuelModal.find("#bf_account_id").attr('data-id',id);  
        fuelModal.find("#bf_entry_date").attr('data-id',id);
        fuelModal.find("#bf_row_id").val(id);
        fuelModal.find("#bf_gross_weight").val(gross_weight);
        fuelModal.find("#bf_vehicle_no").val(vehicle_no);
        fuelModal.find("#bf_group_id").val(group_id);
        fuelModal.find("#bf_entry_date").val(entry_date);
        fuelModal.find(".purchase_div").hide();
        if((status==0 || status==1) && purchase_amount=="" && modal_open_status==0){
            fuelModal.find("#bf_voucher_no").val('');
            fuelModal.find("[id^='bf_qty_']").val('');
            fuelModal.find("#bf_qty_short_weight").val('');
            fuelModal.find(".bf_contract_rate").val('');
            fuelModal.find("#bf_contract_rate_short_weight").val('');
            fuelModal.find("[id^='bf_difference_amount_']").val('');
            fuelModal.find("#bf_difference_amount_short_weight").val('');
            fuelModal.find("#bf_difference_total_amount").val('');
            fuelModal.find("#bf_tare_weight").val('');
        }else if(modal_open_status==1){
            fuelModal.find(".bf_contract_rate").val('');
        }
        if((status==0 || status==1) && purchase_amount==""){
            fuelModal.find("#bf_entry_date").attr('readonly',false);
            fuelModal.find("#bf_vehicle_no").attr('readonly',false);
            fuelModal.find("#bf_group_id").removeClass('unchange_dropdown');
            fuelModal.find("#bf_account_id").removeClass('unchange_dropdown');
        }
        if(status==2 || status==3){
            let edit_purchase_url = "{{url('purchase-edit/map_id')}}?row_id=ids";
            edit_purchase_url = edit_purchase_url.replace('map_id',map_purchase_id);
            edit_purchase_url = edit_purchase_url.replace('ids',id);
            fuelModal.find("#bf_edit_purchase_url").attr('href',edit_purchase_url);
            $(".bf_edit_purchase").show();
        }
        
        $.ajax({
            url : "{{url('get-location-by-supplier')}}",
            method : "POST",
            data: {
                _token: '<?php echo csrf_token() ?>',
                account_id : account_id
            },
            success:function(res){
               
                //Get Store All Data
                $.ajax({
                    url:"{{url('view-complete-purchase-info/')}}/"+id,
                    type:"POST",
                    data:{_token:'{{csrf_token()}}'},
                    success:function(res){
                        $("#cover-spin").hide();
                        if(res!=""){
                            let obj = JSON.parse(res);

                            if (obj.purchase.contract_item_id) {
                                fuelModal.find("#bf_item").val(obj.purchase.contract_item_id);
                            }
                            
                            console.log(obj.purchase);
                            fuelModal.find("#bf_account_id").html(
                            `<option value="${obj.purchase.account_id}" selected>
                                ${obj.purchase.account_name}
                            </option>`
                            );

                            fuelModal.find("#bf_group_id").html(
                            `<option value="${obj.purchase.group_id}" selected>
                                ${obj.purchase.group_name}
                            </option>`
                            );

                            fuelModal.find("#bf_item").html(
                            `<option value="${obj.purchase.contract_item_id}" selected>
                                ${obj.purchase.item_name}
                            </option>`
                            );
                            fuelModal.find("input").attr("readonly", true);
                            fuelModal.find("select").prop("disabled", true);

                            // qty / rate safety (explicit)
                            fuelModal.find("[id^='bf_qty_']").attr("readonly", true);
                            fuelModal.find("[id^='bf_bill_rate_']").attr("readonly", true);
                            fuelModal.find("[id^='bf_contract_rate_']").attr("readonly", true);
                            fuelModal.find("[id^='bf_difference_amount_']").attr("readonly", true);

                            fuelModal.find(".bf_save_location").hide();
                            fuelModal.find(".bf_edit_purchase").hide();
                            fuelModal.find(".bf_approve").hide();
                            fuelModal.find("#bf_bill_url").hide();

                            fuelModal.find("#bf_imageCarousel").hide();

                            if(obj.purchase==null){
                                if(modal_open_status==0){
                                    fuelModal.modal('toggle');
                                }else if(modal_open_status==1){
                                    modal_open_status = 0;
                                }
                                return;
                            }
                            let head_data_arr = [];
                            fuelModal.find("#bf_difference_total_amount").val(obj.purchase.difference_total_amount);                            
                            fuelModal.find("#bf_voucher_no").val(obj.purchase.voucher_no);
                            if(modal_open_status==0){
                               
                                if(obj.purchase.contract_item_id!=""){
                                    fuelModal.find("#bf_item").val(obj.purchase.contract_item_id);
                                }                                
                            }
                            fuelModal.find("#bf_tare_weight").val(obj.purchase.tare_weight);
                            let gross_weight = fuelModal.find("#bf_gross_weight").val();
                            let tare_weight = fuelModal.find("#bf_tare_weight").val();
                            if(tare_weight=="" || tare_weight==null){
                                tare_weight = 0;
                            }
                            let net_weight = parseFloat(gross_weight) - parseFloat(tare_weight);
                            
                            fuelModal.find("#bf_net_weight").val(net_weight);
                            fuelModal.find("#bf_net_weight_view").html("Net Weight : "+net_weight);
                            
                            obj.reports.forEach(element => {
                                head_data_arr[element.head_id] = element;
                            });
                            let purchase_image = "";
                            fuelModal.find(".bf_image_div").html(purchase_image);
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
                            fuelModal.find(".bf_image_div").html(purchase_image);
                            fuelModal.find("#bf_imageCarousel").show();
                            if(status==2){
                                fuelModal.find(".bf_approve").show();
                            }
                            let report_amount_total = 0;
                            fuelModal.find("[id^='bf_qty_']").each(function(){
                                let id = $(this).attr('data-id');
                                if(head_data_arr[id]){
                                    fuelModal.find("#bf_qty_"+id).val(head_data_arr[id].head_qty);
                                    if(status!=1){
                                        fuelModal.find("#bf_bill_rate_"+id).val(price);
                                    }
                                    if(modal_open_status==0){
                                        fuelModal.find("#bf_contract_rate_"+id).val(head_data_arr[id].head_contract_rate);
                                        fuelModal.find("#bf_report_amount_"+id).val(head_data_arr[id].head_contract_rate*head_data_arr[id].head_qty);
                                        fuelModal.find("#bf_difference_amount_"+id).val(head_data_arr[id].head_difference_amount);
                                        report_amount_total = parseFloat(report_amount_total) + parseFloat(head_data_arr[id].head_contract_rate*head_data_arr[id].head_qty);
                                    }
                                }
                            });
                            let short_weight_id = "short_weight";
                            if(head_data_arr[short_weight_id]){
                                fuelModal.find("#bf_qty_"+short_weight_id).val(head_data_arr[short_weight_id].head_qty);
                                if(status!=1){
                                    fuelModal.find("#bf_bill_rate_"+short_weight_id).val(price);
                                }
                                fuelModal.find("#bf_contract_rate_"+short_weight_id).val(head_data_arr[short_weight_id].head_contract_rate);
                                fuelModal.find("#bf_report_amount_"+short_weight_id).val(head_data_arr[short_weight_id].head_contract_rate*head_data_arr[short_weight_id].head_qty);
                                fuelModal.find("#bf_difference_amount_"+short_weight_id).val(head_data_arr[short_weight_id].head_difference_amount);
                                report_amount_total = parseFloat(report_amount_total) + parseFloat(head_data_arr[short_weight_id].head_contract_rate*head_data_arr[short_weight_id].head_qty);
                            }
                            fuelModal.find("#bf_report_amount_total").val(report_amount_total);
                            let diff_cal_amount = parseFloat(purchase_taxable_amount) - parseFloat(report_amount_total);
                            let diff_cal_rate_amount = fuelModal.find("#bf_difference_total_amount").val();
                            fuelModal.find("#bf_total_report_amount").html("Total Report Amount : " + report_amount_total);
                            if(parseFloat(diff_cal_amount)!=parseFloat(diff_cal_rate_amount)){
                                fuelModal.find("#bf_difference_total_amount").css({ color: "red" });
                            }
                            fuelModal.find("[id^='bf_'].calculate").each(function () {
                                $(this).keyup();
                            });

                            if(status==3){
                                //$(".save_location").hide();
                            }
                            // modal.find("#bill_url").hide();
                            if(modal_open_status==0){
                                fuelModal.modal('toggle');
                            }else if(modal_open_status==1){
                                modal_open_status = 0;
                            }
                            if (typeof open_id !== "undefined" && open_id !== "") {
                                fuelModal.find(".bf_save_location").click();
                            }
                            if(map_purchase_id!="" && map_purchase_id!=0){
                                fuelModal.find("#bf_bill_url").hide();
                                fuelModal.find(".bf_bill_rate").val(price);
                                fuelModal.find("#bf_purchase_invoice_no").val(purchase_voucher_no);
                                fuelModal.find("#bf_purchase_invoice_date").val(purchase_date);
                                fuelModal.find("#bf_purchase_invoice_amount").val(purchase_amount);
                                fuelModal.find("#bf_purchase_invoice_qty").val(purchase_qty);
                                fuelModal.find("#bf_bill_url").attr('href','');
                                fuelModal.find(".purchase_div").show();
                            }else{
                                let bill_url = "{{url('purchase/create?row_id=row_id_value&account_id=account_id_value&group_id=group_id_value&quantity=quantity_value&price=price_value')}}";
                                let qty_index = 0;let qty = 0; let rate = 0;
                                fuelModal.find("[id^='bf_qty_']").each(function(){
                                    if(qty_index==0){
                                        let id = $(this).attr('data-id');
                                        qty = $(this).val();
                                        
                                        rate = $("#bf_contract_rate_"+id).val();
                                    }                
                                    qty_index++;

                                    
                                });
                                bill_url = bill_url.replace('row_id_value',id);
                                bill_url = bill_url.replace('account_id_value',account_id);
                                bill_url = bill_url.replace('group_id_value',group_id);
                                bill_url = bill_url.replace('quantity_value',qty);
                                bill_url = bill_url.replace('price_value',rate);
                                bill_url = bill_url.replace(/&amp;/g, "&");
                                fuelModal.find("#bf_bill_url").show();
                                fuelModal.find("#bf_bill_url").attr('href', bill_url);
                            }
                        }
                        
                    }
                });
               
                
            }
        });
    });
     document.getElementById('boilerfuel_report_modal')
        .addEventListener('shown.bs.modal', function () {
            const body = this.querySelector('.modal-scroll-body');
            if (body) body.focus();
        });
     document.getElementById('wastekraft_report_modal')
        .addEventListener('shown.bs.modal', function () {
            const body = this.querySelector('.modal-scroll-body');
            if (body) body.focus();
        });
        
    $('.csv_selected').on('click', function () {
    
        let csv = [];
    let header = [];

    // HEADER: skip first & last column
    $('#purchase_table thead tr th')
        .not(':first-child')
        .not(':last-child')
        .each(function () {
            header.push('"' + $(this).text().trim() + '"');
        });

    csv.push(header.join(','));

    // BODY: only checked rows
    $('#purchase_table tbody tr').each(function () {

        let checkbox = $(this).find('.check_row');

        if (checkbox.length && checkbox.is(':checked')) {

            let row = [];

            $(this).find('td')
                .not(':first-child')
                .not(':last-child')
                .each(function () {
                    let text = $(this).text().trim().replace(/\s+/g, ' ');
                    text = text.replace(/"/g, '""');
                    row.push('"' + text + '"');
                });

            csv.push(row.join(','));
        }
    });

    if (csv.length === 1) {
        alert('Please select at least one row');
        return;
    }

    downloadCSV(csv.join('\n'), 'purchase_report.csv');
    });

    // Download helper
    function downloadCSV(csv, filename) {
        let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        let link = document.createElement('a');
    
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.display = 'none';
    
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    $(document).on("click", ".revert_in_process", function () {

    if(confirm("Are you sure to revert purchase to In Process ?")){

        $("#cover-spin").show();

        let id = $("#row_id").val();

        $.ajax({
            url: "{{url('revert-in-process-purchase-report')}}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                row_id: id
            },
            success: function(res){

                $("#cover-spin").hide();

                if(res){
                    let obj = JSON.parse(res);

                    if(obj.status == 1){
                        alert(obj.message);
                        location.reload();
                    }else{
                        alert(obj.message);
                    }
                }else{
                    alert("Something went wrong");
                }
            }
        });
    }

});

// ===== COLUMN UNCHECK VALIDATION =====

const sensitiveColumns = [
    'net_weight',
    'cut_weight',
    'actual_weight',

    // 🔥 ALL SUBHEADS (KRAFT, DUPLEX, etc)
    ...$('input[name="columns[]"]').map(function () {
        return $(this).val();
    }).get().filter(v => v.startsWith('sub_head_')),

    // 🔥 ALL AMOUNT COLUMNS
    'invoice_amount',
    'gst_amount',
    'taxable_amount',
    'actual_amount',
    'actual_with_gst',
    'payment',
    'billing_rate',
    'contract_rate',
    'difference'
];

$(document).on('change', 'input[name="columns[]"]', function () {

    let column = $(this).val();
    let isChecked = $(this).prop('checked');

    if (!isChecked && (
        sensitiveColumns.includes(column) ||
        column.startsWith('sub_head_')
    )) {

        let colIndex = -1;

        // Find correct column index from header
        $('#purchase_table thead th').each(function (index) {
            if ($(this).data('column') === column) {
                colIndex = index;
            }
        });

        if (colIndex === -1) return;

        let hasValue = false;

        // Loop table rows
        $("#purchase_table tbody tr").each(function () {

            let cell = $(this).find("td").eq(colIndex);
            let text = cell.text().trim().replace(/,/g, '');

                if (text !== "" && text !== "-" && !isNaN(text) && parseFloat(text) !== 0) {
                    hasValue = true;
                    return false;
                }
        });

        if (hasValue) {
            let labelText = $('input[name="columns[]"][value="' + column + '"]')
                    .closest('.form-check')
                    .find('label')
                    .text()
                    .trim();

            let confirmAction = confirm(labelText + " has values. Do you want to hide it?");
                    if (!confirmAction) {
                        $(this).prop('checked', true); // revert
                    }
        }
    }
});
</script>
@endsection