@php
    $current_route = Route::currentRouteName();
@endphp

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
.filter-control {
    height: 42px !important;
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 10px;
}
#supplier {
    min-width: 150px;
}

.filter-viewby {
    min-width: 170px;
    padding-right: 28px;   
}
.filter-btn {
    height: 42px !important;
    padding: 0 26px;
    font-size: 14px;
    border-radius: 20px;
}
@media print{
      .noprint{
         display:none;
      }
   }
   @page { size: auto;  margin: 0mm; }
   @page { /* Always A4 size page (210mm x 297mm) */
   margin: 5mm;     /* Outer margin around content */
}

.importantRule { 
   display: none !important;  /* Force hide anything with this class */
}
.table-scroll-wrapper {
    max-height: 600px;   /* adjust height */
    overflow-y: auto;
    overflow-x: auto;
}

/* Let browser auto-fit columns */
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
.select2-container .select2-selection--single {
    height: 45px;              /* increase height */
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 45px;         /* center text vertically */
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 45px;
}

.select2-container {
    min-width: 220px;   /* adjust as needed */
}

@media (max-width: 768px) {
    .select2-container {
        width: 100% !important;
    }
}
/* Make table header sticky */
#purchase_table thead th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    z-index: 10;
}

/* If your main header is also sticky, increase top */
.table-title-bottom-line + .transaction-table 
#purchase_table thead th {
    top: 60px; /* adjust if needed */
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
                    <h5 class="transaction-table-title m-0 py-2">Purchase Report</h5>
                    <div class="d-md-flex d-block header-section"> 
                        <div class="calender-administrator my-2 my-md-0">
                            <input type="date" id="from_date" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : date('Y-m-01') }}">
                        </div>
                        <div class="calender-administrator ms-md-4">
                            <input type="date" id="to_date" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : date('Y-m-t')}}">
                        </div>
                        <div class="ms-md-4" style="min-width:240px">
                            <select class="form-select form-select-sm filter-control select2-single" id="supplier">
                            <option value="all">All Supplier</option>
                            @foreach($accounts as $loc)
                                <option value="{{ $loc->id }}">{{ $loc->account_name }}</option>
                            @endforeach
                        </select>
                        </div>
                        <select id="view_by" class="form-select form-select-sm ms-md-4 filter-control filter-viewby">
                            <option value="party" {{ request()->view_by == 'party' ? 'selected' : '' }}>View by Party</option>
                            <option value="date"  {{ request()->view_by == 'date' ? 'selected' : '' }}>View by Date</option>
                        </select>
                        <button class="btn btn-primary btn-sm d-flex align-items-center ms-md-4 search_btn">Submit</button>
                        <button class="btn btn-info" onclick="printpage();">Print</button>
                        <button class="btn btn-info print_selected" >CSV</button>
                    </div>
                </div>
                <div id="print-area">
                    <div class="transaction-table bg-white table-view shadow-sm">
                        @if(isset($view_by) && $view_by == 'party')
                            <table class="table-bordered table m-0 shadow-sm payment_table" id="payment_table">
                                <thead>
                                    <tr class="font-12 text-body bg-light-pink">
                                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:left;">Account Name</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Invoice Amount</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Gst Amount</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Taxable Amount</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Actual Amount</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Difference Amount</th>
                                        <th style="text-align:right;">Payment</th> 
                                        <th class="w-min-120 border-none bg-light-pink text-body header-section">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $invoice_total = 0;
                                        $actual_total  = 0;
                                        $difference_total = 0;
                                        $total_gst = 0;
                                        $total_taxable_amt = 0;
                                        $total_payment = 0;
                                    @endphp
                                    @foreach($purchases as $key => $value)
                                        @php
                                            $actual_sum = $value->actual_amount;
                                            $invoice_total     += $value->total_sum;
                                            $actual_total      += $actual_sum;
                                            $difference_total  += $value->difference_sum;
                                            $total_payment += $value->payment ?? 0;
                                            $total_gst         += $value->gst_amt ?? 0;
                                            $total_taxable_amt += $value->taxable_amt ?? 0;
                                        @endphp
                                        <tr>
                                            <td style="text-align:left;">{{ $value->accountInfo->account_name }}</td>
                                            <td style="text-align:right;">{{ formatIndianNumber($value->total_sum,2) }}</td>
                                            <td style="text-align:right;">{{ formatIndianNumber($value->gst_amt ?? 0,2) }}</td>
                                            <td style="text-align:right;">{{ formatIndianNumber($value->taxable_amt ?? 0,2) }}</td>
                                            <td style="text-align:right;">{{ formatIndianNumber($actual_sum,2) }}</td>
                                            <td style="text-align:right;">{{ formatIndianNumber($value->difference_sum,2) }}</td>
                                            <td style="text-align:right;">
                                                {{ formatIndianNumber($value->payment ?? 0,2) }}
                                            </td>
                                            <td class="header-section">
                                                @if(request()->is('wastekraft-purchase-report*'))
                                                    <a href="{{ route('wastekraft-view-detail', [
                                                            $value->account_id,
                                                            $from_date ?? 'all',
                                                            $to_date ?? 'all',
                                                            $group_id
                                                    ]) }}" class="btn btn-info">View</a>
                                                @endif
                                                @if(request()->is('boilerfuel-purchase-report*'))
                                                    <a href="{{ route('boilerfuel-view-detail', [
                                                            $value->account_id,
                                                            $from_date ?? 'all',
                                                            $to_date ?? 'all',
                                                            $group_id
                                                    ]) }}" class="btn btn-info">View</a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th>Total</th>
                                        <th style="text-align:right;">{{ formatIndianNumber($invoice_total,2) }}</th>
                                        <th style="text-align:right;">{{ formatIndianNumber($total_gst,2) }}</th>
                                        <th style="text-align:right;">{{ formatIndianNumber($total_taxable_amt,2) }}</th>
                                        <th style="text-align:right;">{{ formatIndianNumber($actual_total,2) }}</th>
                                        <th style="text-align:right;">{{ formatIndianNumber($difference_total,2) }}</th>
                                        <th style="text-align:right;">
                                                {{ formatIndianNumber($total_payment,2) }}
                                            </th>
                                        <th class="header-section"></th>
                                    </tr>
                                </tbody>
                            </table>
                        @endif
                        @if(isset($view_by) && $view_by == 'date')
                            @php
                                $sub_heads = collect();
                                if (request()->is('wastekraft-purchase-report*')) {
                                    $current_group_id = $waste_group_id;
                                } elseif (request()->is('boilerfuel-purchase-report*')) {
                                    $current_group_id = $group_id;
                                } else {
                                    $current_group_id = null;
                                }
                                if ($current_group_id) {
                                    $sub_heads = DB::table('supplier_sub_heads')
                                        ->select('id','name')
                                        ->where('group_id', $current_group_id)
                                        ->where('status', '1')
                                        ->orderBy('sequence')
                                        ->get();
                                }
                                $dateColumns = [
                                    'date'            => 'Date',
                                    'account'         => 'Account Name',
                                    'invoice'         => 'Invoice No./Slip No.',
                                    'area'            => 'Area',
                                    'net_weight'      => 'Net Weight',
                                    'cut_weight'      => 'Cut Weight',
                                    'actual_weight'   => 'Actual Weight',
                                ];

                                foreach ($sub_heads as $head) {
                                    $dateColumns['sub_head_'.$head->id] = $head->name;
                                }
                                $dateColumns += [
                                    'invoice_amount'  => 'Invoice Amount',
                                    'gst_amount'      => 'Gst Amount',
                                    'taxable_amount'  => 'Taxable Amount',
                                    'actual_amount'   => 'Actual Amount',
                                    'actual_with_gst' => 'Actual + Gst',
                                    'payment'         => 'Payment',
                                    'billing_rate'    => 'Billing Rate',
                                    'contract_rate'   => 'Contract Rate',
                                    'difference'      => 'Difference Amount',
                                ];
                                $selectedDateColumns = request()->input(
                                    'columns',
                                    array_keys($dateColumns)
                                );
                            @endphp
                            <form method="GET" class="mb-3 noprint">
                                <input type="hidden" name="view_by" value="date">
                                <div class="row px-3">
                                    <label class="fw-bold mb-1">Show Columns</label>
                                    @foreach($dateColumns as $key => $label)
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    name="columns[]"
                                                    value="{{ $key }}"
                                                    {{ in_array($key, $selectedDateColumns) ? 'checked' : '' }}
                                                >
                                                <label class="form-check-label">{{ $label }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="px-3 mt-2">
                                    <button class="btn btn-primary btn-sm">Apply</button>
                                </div>
                            </form>
                            <div class="table-scroll-wrapper">
                                <table class="table-bordered table m-0 shadow-sm payment_table" id="purchase_table">
                                    <thead>
                                        <tr class="font-12 text-body bg-light-pink">
                                            @if(in_array('date',$selectedDateColumns))
                                                <th>Date</th>
                                            @endif

                                            @if(in_array('account',$selectedDateColumns))
                                                <th>Account Name</th>
                                            @endif

                                            @if(in_array('invoice',$selectedDateColumns))
                                                <th style="text-align:right;">Invoice No./Slip No.</th>
                                            @endif

                                            @if(in_array('area',$selectedDateColumns))
                                                <th>Area</th>
                                            @endif

                                            @if(in_array('net_weight',$selectedDateColumns))
                                                <th data-column="net_weight" style="text-align:right;">Net Weight</th>
                                            @endif

                                            @if(in_array('cut_weight',$selectedDateColumns))
                                                <th data-column="cut_weight" style="text-align:right;">Cut Weight</th>
                                            @endif

                                            @if(in_array('actual_weight',$selectedDateColumns))
                                                <th data-column="actual_weight" style="text-align:right;">Actual Weight</th>
                                            @endif
                                            @foreach($sub_heads as $head)
                                                @if(in_array('sub_head_'.$head->id, $selectedDateColumns))
                                                    <th data-column="sub_head_{{$head->id}}" style="text-align:right;">
                                                        {{ $head->name }}
                                                    </th>
                                                @endif
                                            @endforeach
                                            @if(in_array('invoice_amount',$selectedDateColumns))
                                                <th data-column="invoice_amount" style="text-align:right;">Invoice Amount</th>
                                            @endif

                                            @if(in_array('gst_amount',$selectedDateColumns))
                                                <th data-column="gst_amount" style="text-align:right;">Gst Amount</th>
                                            @endif

                                            @if(in_array('taxable_amount',$selectedDateColumns))
                                                <th data-column="taxable_amount" style="text-align:right;">Taxable Amount</th>
                                            @endif

                                            @if(in_array('actual_amount',$selectedDateColumns))
                                                <th data-column="actual_amount" style="text-align:right;">Actual Amount</th>
                                            @endif

                                            @if(in_array('actual_with_gst',$selectedDateColumns))
                                                <th data-column="actual_with_gst" style="text-align:right;">Actual + Gst</th>
                                            @endif
                                            @if(in_array('payment',$selectedDateColumns))
                                                <th style="text-align:right;">Payment</th>
                                            @endif
                                            @if(in_array('billing_rate',$selectedDateColumns))
                                                <th data-column="billing_rate" style="text-align:right;">Billing Rate</th>
                                            @endif

                                            @if(in_array('contract_rate',$selectedDateColumns))
                                                <th data-column="contract_rate" style="text-align:right;">Contract Rate</th>
                                            @endif

                                            @if(in_array('difference',$selectedDateColumns))
                                                <th data-column="difference" style="text-align:right;">Difference Amount</th>
                                            @endif
                                            <th class="header-section">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $grand_net_weight = 0;
                                            $grand_cut_weight = 0;
                                            $grand_actual_weight = 0;
                                            $grand_invoice_amount = 0;
                                            $grand_gst_amount = 0;
                                            $grand_taxable_amount = 0;
                                            $grand_actual_amount = 0;
                                            $grand_actual_gst_amount = 0;
                                            $grand_payment_amount = 0;
                                            $grand_difference_amount = 0;
                                            $grand_head_totals = [];
                                        @endphp
                                        @foreach($purchases_details_by_date as $key => $rows)
                                            @php
                                                $date_net_weight = 0;
                                                $date_cut_weight = 0;
                                                $date_actual_weight = 0;
                                                $date_invoice_amount = 0;
                                                $date_gst_amount = 0;
                                                $date_taxable_amount = 0;
                                                $date_actual_amount = 0;
                                                $date_actual_gst_amount = 0;
                                                $date_payment_amount = 0;
                                                $date_difference_amount = 0;
                                                $date_head_totals = [];
                                            @endphp
                                            @foreach($rows as $k => $row)
                                                @php 
                                                    $headMap = []; $actual_amount = 0; $contract_rate = "";
                                                    foreach ($row->purchaseReport as $rp) {
                                                        $headMap[$rp->head_id] = $rp;
                                                        if (is_numeric($rp->head_id) && $rp->total_head_qty > 0) {
                                                            $actual_amount += ($rp->total_head_qty * $rp->head_contract_rate);
                                                        }
                                                        if(!empty($rp->total_head_qty) && $rp->total_head_qty!=0 && $rp->head_contract_rate!=0){
                                                            $contract_rate.=$rp->head_contract_rate." , ";
                                                        }
                                                    }
                                                    $cut = $headMap['cut']->total_head_qty ?? 0;
                                                    $actual_weight = $row->gross_weight - $row->tare_weight - $cut;
                                                    //Date Wise Total
                                                    $date_net_weight += $row->gross_weight - $row->tare_weight;
                                                    $date_cut_weight += $headMap['cut']->total_head_qty ?? 0;
                                                    $date_actual_weight += $actual_weight;
                                                    $date_invoice_amount += $row->purchase_total_amt;
                                                    $date_gst_amount += $row->purchase_gst;
                                                    $date_taxable_amount += $row->purchase_taxable_amt;
                                                    $date_actual_amount += $actual_amount;
                                                    $date_actual_gst_amount += $actual_amount + $row->purchase_gst;
                                                    $date_payment_amount += $row->payment_amount;
                                                    $date_difference_amount += $row->difference_total_amount;

                                                    //Grand Wise Total
                                                    $grand_net_weight += $row->gross_weight - $row->tare_weight;
                                                    $grand_cut_weight += $headMap['cut']->total_head_qty ?? 0;
                                                    $grand_actual_weight += $actual_weight;
                                                    $grand_invoice_amount += $row->purchase_total_amt;
                                                    $grand_gst_amount += $row->purchase_gst;
                                                    $grand_taxable_amount += $row->purchase_taxable_amt;
                                                    $grand_actual_amount += $actual_amount;
                                                    $grand_actual_gst_amount += $actual_amount + $row->purchase_gst;
                                                    $grand_payment_amount += $row->payment_amount;
                                                    $grand_difference_amount += $row->difference_total_amount;
                                                @endphp
                                                <tr>
                                                    @if(in_array('date',$selectedDateColumns))
                                                    <td>{{ date('d-m-Y', strtotime($row->entry_date)) }}</td>
                                                    @endif

                                                    @if(in_array('account',$selectedDateColumns))
                                                    <td>{{ $row->account_name ?? '-' }}</td>
                                                    @endif

                                                    @if(in_array('invoice',$selectedDateColumns))
                                                    <td style="text-align:right;@if($row->purchase_voucher_no=="")background:red;@endif">
                                                        {{ $row->purchase_voucher_no }} / {{ $row->voucher_no }}
                                                    </td>
                                                    @endif
                                                    @if(in_array('area',$selectedDateColumns))
                                                    <td>{{ $row->locationInfo->name ?? '-' }}</td>
                                                    @endif

                                                    @if(in_array('net_weight',$selectedDateColumns))
                                                    <td style="text-align:right;">{{ $row->gross_weight - $row->tare_weight }}</td>
                                                    @endif

                                                    @if(in_array('cut_weight',$selectedDateColumns))
                                                    <td style="text-align:right;">{{ $headMap['cut']->total_head_qty ?? 0 }}</td>
                                                    @endif

                                                    @if(in_array('actual_weight',$selectedDateColumns))
                                                    <td style="text-align:right;">{{ $actual_weight }}</td>
                                                    @endif

                                                    @foreach($sub_heads as $head)
                                                        @php 
                                                            $qty = $headMap[$head->id]->total_head_qty ?? 0;
                                                            if(!isset($date_head_totals[$head->id])){
                                                                $date_head_totals[$head->id] = 0;
                                                            }
                                                            $date_head_totals[$head->id] += $qty;
                                                            if(!isset($grand_head_totals[$head->id])){
                                                                $grand_head_totals[$head->id] = 0;
                                                            }
                                                            $grand_head_totals[$head->id] += $qty;
                                                        @endphp
                                                        @if(in_array('sub_head_'.$head->id, $selectedDateColumns))
                                                            <td style="text-align:right;">
                                                                {{ $headMap[$head->id]->total_head_qty ?? '-' }}
                                                            </td>
                                                        @endif
                                                    @endforeach

                                                    @if(in_array('invoice_amount',$selectedDateColumns))
                                                    <td style="text-align:right;">{{$row->purchase_total_amt}}</td>
                                                    @endif

                                                    @if(in_array('gst_amount',$selectedDateColumns))
                                                    <td style="text-align:right;">{{$row->purchase_gst}}</td>
                                                    @endif

                                                    @if(in_array('taxable_amount',$selectedDateColumns))
                                                    <td style="text-align:right;">{{$row->purchase_taxable_amt}}</td>
                                                    @endif

                                                    @if(in_array('actual_amount',$selectedDateColumns))
                                                    <td style="text-align:right;">{{$actual_amount}}</td>
                                                    @endif

                                                    @if(in_array('actual_with_gst',$selectedDateColumns))
                                                        <td style="text-align:right;">{{$actual_amount + $row->purchase_gst}}</td>
                                                    @endif
                                                    @if(in_array('payment',$selectedDateColumns))
                                                        <td style="text-align:right;">
                                                            {{ formatIndianNumber($row->payment_amount,2) }}
                                                        </td>
                                                    @endif
                                                    @if(in_array('billing_rate',$selectedDateColumns))
                                                    <td style="text-align:right;">{{$row->prices}}</td>
                                                    @endif

                                                    @if(in_array('contract_rate',$selectedDateColumns))
                                                    <td style="text-align:right;">{{ rtrim($contract_rate,' ,') }}</td>
                                                    @endif

                                                    @if(in_array('difference',$selectedDateColumns))
                                                    <td style="text-align:right;">{{ formatIndianNumber($row->difference_total_amount,2) }}</td>
                                                    @endif
                                                    <td class="header-section">
                                                    
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="bg-light fw-bold">
                                                @if(in_array('date',$selectedDateColumns))
                                                <td colspan="1">Total ({{ date('d-m-Y', strtotime($key)) }})</td>
                                                @endif

                                                @if(in_array('account',$selectedDateColumns))
                                                <td>-</td>
                                                @endif

                                                @if(in_array('invoice',$selectedDateColumns))
                                                <td>-</td>
                                                @endif

                                                @if(in_array('area',$selectedDateColumns))
                                                <td>-</td>
                                                @endif

                                                @if(in_array('net_weight',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_net_weight,2) }}</td>
                                                @endif

                                                @if(in_array('cut_weight',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_cut_weight,2) }}</td>
                                                @endif

                                                @if(in_array('actual_weight',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_actual_weight,2) }}</td>
                                                @endif

                                                @foreach($sub_heads as $head)
                                                    @if(in_array('sub_head_'.$head->id, $selectedDateColumns))
                                                        <td>{{formatIndianNumber($date_head_totals[$head->id] ?? 0,2)}}</td>
                                                    @endif
                                                @endforeach

                                                @if(in_array('invoice_amount',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_invoice_amount,2) }}</td>
                                                @endif

                                                @if(in_array('gst_amount',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_gst_amount,2) }}</td>
                                                @endif

                                                @if(in_array('taxable_amount',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_taxable_amount,2) }}</td>
                                                @endif

                                                @if(in_array('actual_amount',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_actual_amount,2) }}</td>
                                                @endif

                                                @if(in_array('actual_with_gst',$selectedDateColumns))
                                                    <td style="text-align:right;">{{ formatIndianNumber($date_actual_gst_amount,2) }}</td>
                                                @endif
                                                @if(in_array('payment',$selectedDateColumns))
                                                    <td style="text-align:right;">
                                                        {{ formatIndianNumber($date_payment_amount ?? 0,2) }}
                                                    </td>
                                                @endif
                                                @if(in_array('billing_rate',$selectedDateColumns))
                                                <td>-</td>
                                                @endif

                                                @if(in_array('contract_rate',$selectedDateColumns))
                                                <td>-</td>
                                                @endif

                                                @if(in_array('difference',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($date_difference_amount,2) }}</td>
                                                @endif
                                                <td class="header-section"></td>
                                            </tr>
                                            <tr class="bg-light fw-bold">
                                                <td colspan="{{ 10 + $sub_heads->count() + 1 }}" colspan="{{ 10 + $sub_heads->count() + 1 }}">
                                                    <div style="
                                                        display:flex;
                                                        justify-content: space-between;
                                                        width:100%;
                                                        font-weight:bold;">
                                                        <span>Daily Summary ({{ date('d-m-Y', strtotime($key)) }})</span>
                                                        <span>Total Report Amount: {{ formatIndianNumber($date_actual_amount, 2) }}</span>
                                                        <span>Total Net Weight: {{ number_format($date_net_weight, 2) }}</span>
                                                        <span>Total Average Rate: 
                                                            @php
                                                                $date_avg_purchase_rate = ($date_net_weight > 0)
                                                                    ? ($date_actual_amount / $date_net_weight)
                                                                    : 0;
                                                            @endphp
                                                            {{ number_format($date_avg_purchase_rate, 2) }}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-light fw-bold">
                                            <td colspan="{{ 10 + $sub_heads->count() + 1 }}">
                                                <div style="
                                                    display:flex;
                                                    justify-content: space-between;
                                                    width:100%;
                                                    font-weight:bold;
                                                ">
                                                    <span>
                                                        Overall Summary ({{ date('d-m-Y', strtotime($from_date)) }}
                                                        to
                                                        {{ date('d-m-Y', strtotime($to_date)) }})
                                                    </span>

                                                    <span>Total Report Amount: {{ formatIndianNumber($grand_actual_amount,2) }}</span>

                                                    <span>Total Net Weight: {{ number_format($grand_net_weight,2) }}</span>

                                                    <span>Total Average Rate: 
                                                        @php
                                                            $overall_avg_rate = ($grand_net_weight > 0)
                                                                ? ($grand_actual_amount / $grand_net_weight)
                                                                : 0;
                                                        @endphp
                                                        {{ number_format($overall_avg_rate,2) }}</span>

                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="bg-light fw-bold">
                                            @if(in_array('date',$selectedDateColumns))
                                            <td colspan="1">Total ({{ date('d-m-Y', strtotime($key)) }})</td>
                                            @endif

                                            @if(in_array('account',$selectedDateColumns))
                                            <td>-</td>
                                            @endif

                                            @if(in_array('invoice',$selectedDateColumns))
                                            <td>-</td>
                                            @endif

                                            @if(in_array('area',$selectedDateColumns))
                                            <td>-</td>
                                            @endif

                                            @if(in_array('net_weight',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_net_weight,2) }}</td>
                                            @endif

                                            @if(in_array('cut_weight',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_cut_weight,2) }}</td>
                                            @endif

                                            @if(in_array('actual_weight',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_actual_weight,2) }}</td>
                                            @endif

                                            @foreach($sub_heads as $head)
                                                @if(in_array('sub_head_'.$head->id, $selectedDateColumns))
                                                    <td>{{formatIndianNumber($grand_head_totals[$head->id] ?? 0,2)}}</td>
                                                @endif
                                            @endforeach

                                            @if(in_array('invoice_amount',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_invoice_amount,2) }}</td>
                                            @endif

                                            @if(in_array('gst_amount',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_gst_amount,2) }}</td>
                                            @endif

                                            @if(in_array('taxable_amount',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_taxable_amount,2) }}</td>
                                            @endif

                                            @if(in_array('actual_amount',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_actual_amount,2) }}</td>
                                            @endif

                                            @if(in_array('actual_with_gst',$selectedDateColumns))
                                                <td style="text-align:right;">{{ formatIndianNumber($grand_actual_gst_amount,2) }}</td>
                                            @endif
                                            @if(in_array('payment',$selectedDateColumns))
                                                <td style="text-align:right;">
                                                    {{ formatIndianNumber($grand_payment_amount ?? 0,2) }}
                                                </td>
                                            @endif
                                            @if(in_array('billing_rate',$selectedDateColumns))
                                            <td>-</td>
                                            @endif

                                            @if(in_array('contract_rate',$selectedDateColumns))
                                            <td>-</td>
                                            @endif

                                            @if(in_array('difference',$selectedDateColumns))
                                            <td style="text-align:right;">{{ formatIndianNumber($grand_difference_amount,2) }}</td>
                                            @endif
                                            <td class="header-section"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <h5 class="transaction-table-title m-0 py-2">Details</h5>
                    </div>
                    <div class="transaction-table bg-white table-view shadow-sm">
                        <table class="table-bordered table m-0 shadow-sm payment_table">
                            <thead>
                                <tr class=" font-12 text-body bg-light-pink">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Head</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Quantity</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $qty_total = 0; $amount_total = 0; @endphp
                                @foreach($purchases_details as $key => $value)
                                    @php
                                        if($value['total_qty']==0){
                                            continue;
                                        }
                                        $qty_total = $qty_total + $value['total_qty']; 
                                        $amount_total = $amount_total + $value['total_amount']; 
                                    @endphp
                                    <tr>
                                        <td>@if($value['head_name']) {{ $value['head_name'] }} @else {{ $value['head_id'] }} @endif</td>
                                        <td style="text-align:right;">
                                            <a href="javascript:void(0)" class="qty-breakup" data-head="{{ $value['head_id'] }}">
                                                {{ formatIndianNumber($value['total_qty']) }}
                                            </a>
                                        </td>
                                        <td style="text-align:right;">{{formatIndianNumber($value['total_amount'])}}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th>Total</th>
                                    <th style="text-align:right;">{{formatIndianNumber($qty_total)}}</th>
                                    <th style="text-align:right;">{{formatIndianNumber($amount_total)}}</th>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

</body>

@include('layouts.footer')
<script>
    function normalizeHead(val) {
        return (val ?? '')
            .toString()
            .toLowerCase()
            .replace(/\s+/g, '_'); 
    }
    $( ".select2-single" ).select2({
        //width: '100%',
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            // Normalize: remove dots + spaces, lowercase everything
            function normalize(str) {
                return (str || '')
                    .toLowerCase()
                    .replace(/[.\s]/g, ''); // remove '.' and spaces
            }
            var term = normalize(params.term);
            var text = normalize(data.text);
            if (text.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });
    $(".search_btn").click(function(){
        let supplier = $("#supplier").val();
        let from_date = $("#from_date").val();
        let to_date = $("#to_date").val();
        let view_by = $("#view_by").val();
        let url = window.location.href;
        if(url.includes("wastekraft-purchase-report1")){
            window.location = "{{ url('wastekraft-purchase-report1') }}/" + supplier + "/" + from_date + "/" + to_date + "?view_by=" + view_by;
        }else if(url.includes("boilerfuel-purchase-report")){
            window.location = "{{ url('boilerfuel-purchase-report1') }}/" + supplier + "/" + from_date + "/" + to_date + "?view_by=" + view_by;
        }else{
            window.location = "{{ url('manage-supplier-purchase-report1') }}/" + supplier + "/" + from_date + "/" + to_date + "?view_by=" + view_by;
        }
    });
    function printpage() {
        let printArea = document.getElementById('print-area').cloneNode(true);

        printArea.querySelectorAll('.header-section, .btn, button, .noprint').forEach(el => el.remove());

        printArea.querySelectorAll('*').forEach(el => {
            el.style.overflow = 'visible';
            el.style.maxHeight = 'none';
            el.style.height = 'auto';
        });

        let heading = document.createElement('div');
        heading.innerHTML = `
            <div style="margin-bottom:20px;">
                <div style="text-align:center; font-size:22px; font-weight:700;">
                    Purchase Report
                </div>
            </div>
        `;

        let win = window.open('', '', 'width=1200,height=800');

        win.document.write(`
            <html>
            <head>
                <title>Purchase Report</title>

                <!-- Bootstrap -->
                <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

                <!-- Your actual UI CSS -->
                <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
                <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

                <style>
                    body {
                        background: #fff;
                        margin: 10mm;
                        font-family: Arial, sans-serif;
                    }

                    /* TABLE STRUCTURE */
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 18px;
                    }

                    thead th {
                        background: #f0f0f0;
                        border: 1px solid #888;
                        font-weight: 600;
                        text-align: center;
                    }

                    tbody td,
                    tbody th {
                        border: 1px solid #bbb;
                    }

                    th, td {
                        padding: 7px 9px;
                        font-size: 12px;
                        vertical-align: middle;
                    }

                    /* TOTAL ROW */
                    tr.bg-light,
                    tr.fw-bold {
                        background: #eaeaea !important;
                        font-weight: 700;
                    }

                    /* SECTION TITLE BAR */
                    .table-title-bottom-line {
                        background: #e6e6e6 !important;
                        border: 1px solid #999;
                        margin: 20px 0 10px;
                        padding: 6px 10px;
                        font-weight: 700;
                    }

                    /* allow scroll tables to expand */
                    .table-scroll-wrapper {
                        overflow: visible !important;
                    }

                    thead {
                        display: table-header-group;
                    }

                    tr {
                        page-break-inside: avoid;
                    }

                    @page {
                        
                        margin: 10mm;
                    }
                </style>


            </head>
            <body></body>
            </html>
        `);

        win.document.body.appendChild(heading);
        win.document.body.appendChild(printArea);

        win.document.close();

        win.onload = function () {
            win.focus();
            win.print();
            win.close();
        };
    }
</script>
@endsection