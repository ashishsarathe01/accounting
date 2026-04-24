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
            {{-- Single table: Party OR Date based on $view_by --}}
            <div id="print-area">

            <div class="transaction-table bg-white table-view shadow-sm">
               {{-- PARTY AGGREGATED VIEW (unchanged) --}}
               @if(isset($view_by) && $view_by == 'party')
                  <table class="table-bordered table m-0 shadow-sm payment_table" id="payment_table">
                        <thead>
                           <tr class="font-12 text-body bg-light-pink">
                              <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:left;">Account Name</th>
                              <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Invoice Amount</th>
                              <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Gst Amount</th>
                              <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Taxable Amount</th>
                              <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Actual Amount</th>
                              {{-- <th style="text-align:right;">Contract Rate</th> --}}

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
                                    $actual_sum = 0;
                                    // For weighted average contract rate
                                    $contract_weighted_total = 0;  // sum(qty × rate)
                                    $contract_qty_total      = 0;  // sum(qty)

                                    $details_for_account = $purchases_details->where('account_id', $value->account_id);
                                    
                                    foreach ($details_for_account as $detail) {
                                       foreach ($detail->purchaseReport as $v) {

                                          // Only consider positive qty
                                          if (!empty($v->head_qty) && $v->head_qty > 0) {

                                                // Actual amount (your existing logic)
                                                $actual_sum += ($v->head_qty * $v->head_contract_rate);

                                                // Weighted contract calc
                                                $contract_weighted_total += ($v->head_qty * $v->head_contract_rate);
                                                $contract_qty_total      += $v->head_qty;
                                          }
                                       }
                                    }

                                    $contract_rate = ($contract_qty_total > 0)
                                       ? ($contract_weighted_total / $contract_qty_total)
                                       : 0;


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
                                    {{-- <td style="text-align:right;">{{ number_format($contract_rate, 2) }}</td> --}}
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
                              {{-- <th style="text-align:right;">-</th> <!-- Contract Rate total --> --}}

                              <th style="text-align:right;">{{ formatIndianNumber($difference_total,2) }}</th>
                              <th style="text-align:right;">
                                    {{ formatIndianNumber($total_payment,2) }}
                                </th>
                              <th class="header-section"></th>
                           </tr>
                        </tbody>
                  </table>
               @endif
               @php
                if (request()->is('wastekraft-purchase-report*')) {
                    $current_group_id = $waste_group_id;
                } elseif (request()->is('boilerfuel-purchase-report*')) {
                    $current_group_id = $group_id;
                } else {
                    $current_group_id = null;
                }

                $sub_heads = collect();

                if ($current_group_id) {
                    $sub_heads = DB::table('supplier_sub_heads')
                        ->where('group_id', $current_group_id)
                        ->where('status', 1)
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
               {{-- DATE VIEW: single table with correct GST + Taxable + Actual --}}
                  @if(isset($view_by) && $view_by == 'date')

                     @php
                        $purchase_gst = DB::table('purchase_sundries')
                              ->join('bill_sundrys', 'purchase_sundries.bill_sundry', '=', 'bill_sundrys.id')
                              ->whereIn('nature_of_sundry', ['CGST','SGST','IGST'])
                              ->select('purchase_id', DB::raw('SUM(amount) as gst'))
                              ->groupBy('purchase_id')
                              ->pluck('gst','purchase_id'); 
                     @endphp
                    
                    @if($view_by == 'date')
                    <form method="GET" class="mb-3 noprint">

                        {{-- preserve existing params --}}
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
                    @endif

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
                           $inv_total = 0;
                           $gst_total = 0;
                           $tax_total = 0;
                           $act_total = 0;
                           $diff_total = 0;
                           $actual_with_gst_total = 0;
                           $total_payment = 0;
                           $grand_head_totals = [];
                           $overall_net_weight = 0;
                           $overall_cut_weight = 0;        
                           $overall_actual_weight = 0;
                           // Group rows by date
                           $grouped = $purchases_details
                                            ->sortBy([
                                                ['entry_date', 'asc'],
                                                ['voucher_no', 'asc']
                                            ])
                                            ->groupBy(fn($r) => $r->entry_date);
                        @endphp

                       @foreach($grouped as $date => $rows)
                            @php
                                $date_inv  = 0;
                                $date_gst  = 0;
                                $date_tax  = 0;
                                $date_payment = 0;
                                $date_act  = 0;
                                $date_diff = 0;
                                $date_actual_with_gst = 0;
                                $date_net_weight = 0;
                                $date_cut_weight = 0;
                                $date_actual_weight = 0;
                                $date_head_totals = [];
                            @endphp


                            @php
                                $date_weighted = 0;
                                $date_qty      = 0;
                                $date_total_actual = 0;
                                $date_total_netwt  = 0;
                            @endphp

                            @foreach($rows as $row)
                            
                                @php
                                    $contract_rate = "";
                                    // Map purchase report rows by head_id
                                    $headMap = [];
                                    foreach ($row->purchaseReport as $rp) {
                                        $headMap[$rp->head_id] = $rp;
                                        if(!empty($rp->head_qty) && $rp->head_qty!=0 && $rp->head_contract_rate!=0){
                                            $contract_rate.=$rp->head_contract_rate." , ";
                                        }
                                    }
                                    foreach($sub_heads as $head){
                                        $qty = $headMap[$head->id]->head_qty ?? 0;
                                        if(!isset($date_head_totals[$head->id])){
                                            $date_head_totals[$head->id] = 0;
                                        }
                                        $date_head_totals[$head->id] += $qty;
                                        if(!isset($grand_head_totals[$head->id])){
                                            $grand_head_totals[$head->id] = 0;
                                        }
                                        $grand_head_totals[$head->id] += $qty;
                                    }
                                @endphp
                                                
                                @php
                                    $p  = $row->purchaseInfo;
                                    

                                    $invoice = $p->total ?? 0;
                                     $gst     = 0;
                                    if(isset($p->id)){
                                        $gst     = $purchase_gst[$p->id] ?? 0;
                                    }
                                    
                                    $taxable = $invoice - $gst;

                                    // -------------------------------------------------------
                                    // CORRECTED ACTUAL (IGNORE cut, short_weight, fuel)
                                    // -------------------------------------------------------
                                    $actual = 0;
                                    foreach ($row->purchaseReport as $rp) {
                                        if (is_numeric($rp->head_id) && $rp->head_qty > 0) {
                                            $actual += ($rp->head_qty * $rp->head_contract_rate);
                                        }
                                    }

                                    // -------------------------------------------------------
                                    // CONTRACT RATE = head_id = 1 only
                                    // -------------------------------------------------------
                                    $head1 = $row->purchaseReport->where('sequence', 1)->first();
                                    

                                    // Update date totals only if head1 exists
                                    if ($head1 && $head1->head_qty > 0) {
                                        $date_weighted += ($head1->head_qty * $head1->head_contract_rate);
                                        $date_qty      += $head1->head_qty;
                                    }

                                    // -------------------------------------------------------
                                    // AVG PURCHASE RATE = Actual / Net Weight
                                    // Net Weight = sum of only numeric heads with qty > 0
                                    // -------------------------------------------------------
                                    $net_weight = 0;
                                    foreach ($row->purchaseReport as $rp) {
                                        if (is_numeric($rp->head_id) && $rp->head_qty > 0) {
                                            $net_weight += $rp->head_qty;
                                        }
                                    }

                                    $real_net_weight = $row->gross_weight - $row->tare_weight;
                                    $overall_net_weight += $real_net_weight;
                                    $avg_purchase_rate = ($real_net_weight > 0)
                                        ? ($actual / $real_net_weight)
                                        : 0;


                                    // -------------------------------------------------------
                                    // TOTAL SUMMARY ACCUMULATORS
                                    // -------------------------------------------------------
                                    // Add to daily totals
                                    $date_total_actual += $actual;
                                    $date_total_netwt  += $real_net_weight;

                                    $actual_with_gst = $actual + $gst;  
                                    $inv_total += $invoice;
                                    $gst_total += $gst;
                                    $tax_total += $taxable;
                                    $act_total += $actual;
                                    $total_payment += $row->payment_amount ?? 0;
                                    $actual_with_gst_total += $actual_with_gst;   
                                    $diff_total += ($row->difference_total_amount ?? 0);
                                @endphp
                                @php
                                    $date_inv  += $invoice;
                                    $date_gst  += $gst;
                                    $date_payment += $row->payment_amount ?? 0;
                                    $date_tax  += $taxable;
                                    $date_act  += $actual;
                                    $date_actual_with_gst += $actual_with_gst;
                                    $date_diff += ($row->difference_total_amount ?? 0);
                                    $cut = $headMap['cut']->head_qty ?? 0;
                                    $actual_weight = $row->gross_weight - $row->tare_weight - $cut;
                                    $overall_cut_weight += $cut;
                                    $overall_actual_weight += $actual_weight;
                                    $date_net_weight = $date_net_weight + $row->gross_weight - $row->tare_weight;
                                    $date_cut_weight  += $cut;
                                    $date_actual_weight  += $actual_weight;
                                @endphp


                                 <tr>

                                    @if(in_array('date',$selectedDateColumns))
                                    <td>{{ date('d-m-Y', strtotime($row->entry_date)) }}</td>
                                    @endif

                                    @if(in_array('account',$selectedDateColumns))
                                    <td>{{ $row->accountInfo->account_name ?? '-' }}</td>
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
                                    <td style="text-align:right;">{{ $headMap['cut']->head_qty ?? 0 }}</td>
                                    @endif

                                    @if(in_array('actual_weight',$selectedDateColumns))
                                    <td style="text-align:right;">{{ $actual_weight }}</td>
                                    @endif

                                    @foreach($sub_heads as $head)
                                    @if(in_array('sub_head_'.$head->id, $selectedDateColumns))
                                    <td style="text-align:right;">
                                        {{ $headMap[$head->id]->head_qty ?? '-' }}
                                    </td>
                                    @endif
                                    @endforeach

                                    @if(in_array('invoice_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($invoice,2) }}</td>
                                    @endif

                                    @if(in_array('gst_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($gst,2) }}</td>
                                    @endif

                                    @if(in_array('taxable_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($taxable,2) }}</td>
                                    @endif

                                    @if(in_array('actual_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($actual,2) }}</td>
                                    @endif

                                    @if(in_array('actual_with_gst',$selectedDateColumns))
                                        <td style="text-align:right;">{{ formatIndianNumber($actual_with_gst,2) }}</td>
                                    @endif

                                    @if(in_array('payment',$selectedDateColumns))
                                        <td style="text-align:right;">
                                            @if(!empty($row->payment_amount))
                                                {{ formatIndianNumber($row->payment_amount,2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endif

                                    @if(in_array('billing_rate',$selectedDateColumns))
                                    <td style="text-align:right;">{{ $row->prices }}</td>
                                    @endif

                                    @if(in_array('contract_rate',$selectedDateColumns))
                                    <td style="text-align:right;">{{ rtrim($contract_rate,' ,') }}</td>
                                    @endif

                                    @if(in_array('difference',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($row->difference_total_amount,2) }}</td>
                                    @endif

                                    <td class="header-section">
                                    <button
                                        class="btn btn-info start {{ $row->group_id == $waste_group_id ? 'wastekraft' : 'boilerfuel' }}"
                                        data-id="{{ $row->id }}"
                                        data-group_id="{{ $row->group_id }}"
                                        data-map_purchase_id="{{ $row->map_purchase_id }}"
                                        data-account_id="{{ $row->account_id }}"
                                        data-gross_weight="{{ $row->gross_weight }}"
                                        data-purchase_voucher_no="{{ $row->purchase_voucher_no }}"
                                        data-purchase_date="{{ $row->purchase_date }}"
                                        data-purchase_amount="{{ $row->purchase_amount }}"
                                        data-purchase_qty="{{ $row->purchase_qty }}"
                                        data-purchase_taxable_amount="{{ $row->purchase_taxable_amount }}"
                                        @if($row->group_id == $waste_group_id)
                                            data-purchase_price='{{ $row->prices }}'
                                        @endif
                                        data-status="3"
                                        data-vehicle_no="{{ $row->vehicle_no }}"
                                        data-entry_date="{{ $row->entry_date }}">
                                        View
                                    </button>
                                    </td>
                                </tr>


                              @endforeach

                              @php
                                 $date_avg_purchase_rate = ($date_total_netwt > 0)
                                    ? ($date_total_actual / $date_total_netwt)
                                    : 0;
                              @endphp
                                <tr class="bg-light fw-bold">

                                    @if(in_array('date',$selectedDateColumns))
                                    <td colspan="1">Total ({{ date('d-m-Y', strtotime($date)) }})</td>
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
                                    <td>{{ formatIndianNumber($date_head_totals[$head->id] ?? 0,2) }}</td>
                                    @endif
                                    @endforeach

                                    @if(in_array('invoice_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($date_inv,2) }}</td>
                                    @endif

                                    @if(in_array('gst_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($date_gst,2) }}</td>
                                    @endif

                                    @if(in_array('taxable_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($date_tax,2) }}</td>
                                    @endif

                                    @if(in_array('actual_amount',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($date_act,2) }}</td>
                                    @endif

                                    @if(in_array('actual_with_gst',$selectedDateColumns))
                                        <td style="text-align:right;">{{ formatIndianNumber($date_actual_with_gst,2) }}</td>
                                    @endif
                                    @if(in_array('payment',$selectedDateColumns))
                                        <td style="text-align:right;">
                                            {{ formatIndianNumber($date_payment ?? 0,2) }}
                                        </td>
                                    @endif
                                    @if(in_array('billing_rate',$selectedDateColumns))
                                    <td>-</td>
                                    @endif

                                    @if(in_array('contract_rate',$selectedDateColumns))
                                    <td>-</td>
                                    @endif

                                    @if(in_array('difference',$selectedDateColumns))
                                    <td style="text-align:right;">{{ formatIndianNumber($date_diff,2) }}</td>
                                    @endif

                                    <td class="header-section"></td>

                                </tr>



                              <tr class="bg-light fw-bold">
                                    <td colspan="{{ 10 + $sub_heads->count() + 1 }}" colspan="{{ 10 + $sub_heads->count() + 1 }}">
                                        <div style="
                                            display:flex;
                                            justify-content: space-between;
                                            width:100%;
                                            font-weight:bold;
                                        ">
                                            <span>Daily Summary ({{ date('d-m-Y', strtotime($date)) }})</span>
                                            <span>Total Report Amount: {{ formatIndianNumber($date_total_actual, 2) }}</span>
                                            <span>Total Net Weight: {{ number_format($date_total_netwt, 2) }}</span>
                                            <span>Total Average Rate: {{ number_format($date_avg_purchase_rate, 2) }}</span>
                                        </div>
                                    </td>
                                </tr>
                        @endforeach
                        @php
                            $overall_avg_rate = ($overall_net_weight > 0)
                                ? ($act_total / $overall_net_weight)
                                : 0;
                        @endphp

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

                                    <span>Total Report Amount: {{ formatIndianNumber($act_total,2) }}</span>

                                    <span>Total Net Weight: {{ number_format($overall_net_weight,2) }}</span>

                                    <span>Total Average Rate: {{ number_format($overall_avg_rate,2) }}</span>

                                </div>
                            </td>
                        </tr>
                        <tr class="bg-light fw-bold">

                            @if(in_array('date',$selectedDateColumns))
                            <td>Total</td>
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
                            <td style="text-align:right;">{{ formatIndianNumber($overall_net_weight,2) }}</td>
                            @endif

                            @if(in_array('cut_weight',$selectedDateColumns))
                            <td style="text-align:right;">{{ formatIndianNumber($overall_cut_weight,2) }}</td>
                            @endif

                            @if(in_array('actual_weight',$selectedDateColumns))
                            <td style="text-align:right;">{{ formatIndianNumber($overall_actual_weight,2) }}</td>
                            @endif

                            @foreach($sub_heads as $head)
                            @if(in_array('sub_head_'.$head->id, $selectedDateColumns))
                            <td>{{ formatIndianNumber($grand_head_totals[$head->id] ?? 0,2) }}</td>
                            @endif
                            @endforeach

                            @if(in_array('invoice_amount',$selectedDateColumns))
                            <td style="text-align:right;">{{ formatIndianNumber($inv_total,2) }}</td>
                            @endif

                            @if(in_array('gst_amount',$selectedDateColumns))
                            <td style="text-align:right;">{{ formatIndianNumber($gst_total,2) }}</td>
                            @endif

                            @if(in_array('taxable_amount',$selectedDateColumns))
                            <td style="text-align:right;">{{ formatIndianNumber($tax_total,2) }}</td>
                            @endif

                            @if(in_array('actual_amount',$selectedDateColumns))
                            <td style="text-align:right;">{{ formatIndianNumber($act_total,2) }}</td>
                            @endif

                            @if(in_array('actual_with_gst',$selectedDateColumns))
                                <td style="text-align:right;">{{ formatIndianNumber($actual_with_gst_total,2) }}</td>
                            @endif
                            @if(in_array('payment',$selectedDateColumns))
                                <td style="text-align:right;">
                                    {{ formatIndianNumber($total_payment,2) }}
                                </td>
                            @endif
                            @if(in_array('billing_rate',$selectedDateColumns))
                            <td>-</td>
                            @endif

                            @if(in_array('contract_rate',$selectedDateColumns))
                            <td>-</td>
                            @endif

                            @if(in_array('difference',$selectedDateColumns))
                            <td style="text-align:right;">{{ formatIndianNumber($diff_total,2) }}</td>
                            @endif

                            <td class="header-section"></td>

                        </tr>
                        

                        </tbody>
                     </table>
                    </div>               
                  {{-- If there were no rows at all --}}
                  @if($purchases_details->isEmpty())
                        <div class="alert alert-info mt-3">No purchase records found for selected date range.</div>
                  @endif
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
                     @php $reportArr = []; @endphp
                     @foreach($purchases_details as $key => $value)
                        @foreach($value->purchaseReport as $k1 => $v1)
                           @if($v1->head_qty!="" && $v1->head_qty!=0)
                              @isset($v1->headInfo->name)
                                 @php 
                                    $headName = $v1->headInfo->name;
                                 @endphp
                              @else
                                 @php 
                                    $headName = $v1->head_id;
                                 @endphp
                              @endisset
                              @php 
                                 if (!isset($reportArr[$headName])) {
                                    $reportArr[$headName] = [
                                       'qty' => 0,
                                       'amount' => 0,
                                    ];
                                 }
                                 $reportArr[$headName]['qty'] += $v1->head_qty;
                                 $reportArr[$headName]['amount'] += $v1->head_difference_amount; // assuming `rate` exists
                               @endphp
                           @endif                          
                        @endforeach
                     @endforeach
                     @php  $qty_total=0;$amount_total=0; @endphp
                      
                     @foreach($reportArr as $key => $value)
                     @php  $qty_total=$qty_total+$value['qty'];$amount_total=$amount_total+$value['amount']; @endphp
                        <tr>
                           <td>{{$key}}</td>
                           <td style="text-align:right;">
                                <a href="javascript:void(0)"
                                class="qty-breakup"
                                data-head="{{ $key }}">
                                    {{ number_format($value['qty'],2) }}
                                </a>
                            </td>

                           <td style="text-align:right;">{{$value['amount']}}</td>
                        </tr>
                     @endforeach
                     <tr>
                           <th>Total</th>
                           <th style="text-align:right;">{{$qty_total}}</th>
                           <th style="text-align:right;">{{$amount_total}}</th>
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

</body>
<div class="modal fade" id="wastekraft_report_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content p-4 border-divider border-radius-8">

            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close report_modal_close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                    <div class="mb-12 col-md-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Head</th>
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
                                    <th colspan="6" style="text-align: right" colspan="6">
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

                    <div class="col-md-5 right-section">                       
                        <div id="imageCarousel" class="carousel slide"  style="display: none;margin-top:115px">
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

            <div class="row">
                <div class="col-md-6 left-scroll-area">
                </div>
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
                <button type="button" class="btn-close report_modal_close" data-bs-dismiss="modal"></button>
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
                                        <td colspan="4" colspan="4"></td>
                                        <td><input class="form-control" id="bf_report_amount_total" readonly></td>
                                        <td><input class="form-control" id="bf_difference_total_amount" readonly></td>
                                    </tr>

                                    <tr>
                                        <th colspan="6" class="text-end" colspan="6">
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
<div class="modal fade" id="qtyBreakupModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="qtyBreakupTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Date</th>
                            <th>Account Name</th>
                            <th>Invoice No.</th>
                            <th style="text-align:right;">Quantity</th>
                        </tr>
                    </thead>
                    <tbody id="qtyBreakupBody"></tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="3" style="text-align:right;" colspan="3">Total</td>
                            <td style="text-align:right;" id="qtyBreakupTotal"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>

@include('layouts.footer')
<script>
    const purchaseDetails = @json($purchases_details);
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

         if(url.includes("wastekraft-purchase-report")){
            window.location = "{{ url('wastekraft-purchase-report') }}/" + supplier + "/" + from_date + "/" + to_date + "?view_by=" + view_by;
         }
         else if(url.includes("boilerfuel-purchase-report")){
            window.location = "{{ url('boilerfuel-purchase-report') }}/" + supplier + "/" + from_date + "/" + to_date + "?view_by=" + view_by;
         }
         else{
            window.location = "{{ url('manage-supplier-purchase-report') }}/" + supplier + "/" + from_date + "/" + to_date + "?view_by=" + view_by;
         }
      });



    $(".view").click(function(){
         let id = $(this).data('id');
         $.ajax({
            url:"{{url('view-complete-purchase-info/')}}/"+id,
            type:"POST",
            data:{_token:'{{csrf_token()}}'},
            success:function(res){
               if(res!=""){
                  let obj = JSON.parse(res);
                  $("#voucher_no").val(obj.reports.voucher_no);
                  $("#location").val(obj.reports.location_name);

                  $("#kraft_i_qty").val(obj.reports.kraft_i_qty);
                  $("#kraft_i_bill_rate").val(obj.reports.kraft_i_bill_rate);
                  $("#kraft_i_contract_rate").val(obj.reports.kraft_i_contract_rate);
                  $("#kraft_i_difference_amount").val(obj.reports.kraft_i_difference_amount);

                  $("#kraft_ii_qty").val(obj.reports.kraft_ii_qty);
                  $("#kraft_ii_bill_rate").val(obj.reports.kraft_ii_bill_rate);
                  $("#kraft_ii_contract_rate").val(obj.reports.kraft_ii_contract_rate);
                  $("#kraft_ii_difference_amount").val(obj.reports.kraft_ii_difference_amount);

                  $("#duplex_qty").val(obj.reports.duplex_qty);
                  $("#duplex_bill_rate").val(obj.reports.duplex_bill_rate);
                  $("#duplex_contract_rate").val(obj.reports.duplex_contract_rate);
                  $("#duplex_difference_amount").val(obj.reports.duplex_difference_amount);

                  $("#poor_qty").val(obj.reports.poor_qty);
                  $("#poor_bill_rate").val(obj.reports.poor_bill_rate);
                  $("#poor_contract_rate").val(obj.reports.poor_contract_rate);
                  $("#poor_difference_amount").val(obj.reports.poor_difference_amount);

                  $("#cut_qty").val(obj.reports.cut_qty);
                  $("#cut_bill_rate").val(obj.reports.cut_bill_rate);
                  $("#cut_contract_rate").val(obj.reports.cut_contract_rate);
                  $("#cut_difference_amount").val(obj.reports.cut_difference_amount);

                  $("#other_qty").val(obj.reports.other_qty);
                  $("#other_bill_rate").val(obj.reports.other_bill_rate);
                  $("#other_contract_rate").val(obj.reports.other_contract_rate);
                  $("#other_difference_amount").val(obj.reports.other_difference_amount);

                  $("#difference_total_amount").val(obj.reports.difference_total_amount);
                  if(obj.reports.other_check==1){
                     $("#other_check").prop('checked', true);
                  }
               }
               
               $("#report_modal").modal('show');
            }
         });
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
    function formatDateDMY(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split('-'); // YYYY-MM-DD
        if (parts.length !== 3) return dateStr;
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    $(document).on("click", ".qty-breakup", function () {

        let headName = $(this).data("head");

        let clickedHead = normalizeHead(headName);

        $("#qtyBreakupTitle").text(headName + " – Quantity Breakup");
        $("#qtyBreakupBody").html("");
        $("#qtyBreakupTotal").text("0.00");

        let totalQty = 0;

        purchaseDetails.forEach(row => {

            if (!row.purchase_report) return;

            row.purchase_report.forEach(rp => {

                let rpHeadRaw = rp.head_info ? rp.head_info.name : rp.head_id;
                let rpHead = normalizeHead(rpHeadRaw);

                if (rpHead === clickedHead && rp.head_qty != 0) {

                    let qty = parseFloat(rp.head_qty);

                    totalQty += qty;

                    $("#qtyBreakupBody").append(`
                        <tr>
                            <td>${formatDateDMY(row.entry_date)}</td>
                            <td>${row.account_info?.account_name ?? '-'}</td>
                            <td style="text-align:right;">
                                ${row.purchase_voucher_no ?? ''} / ${row.voucher_no ?? ''}
                            </td>
                            <td style="text-align:right;">${qty.toFixed(2)}</td>
                        </tr>
                    `);
                }
            });
        });

        $("#qtyBreakupTotal").text(totalQty.toFixed(2));

        $("#qtyBreakupModal").modal("show");
    });
    
        $('.print_selected').on('click', function () {
            let view_by = $("#view_by").val();
            let table_id = "purchase_table";
            if(view_by=='party'){
                table_id = "payment_table";
            }
            let csv = [];
            let colCount = 0;

            // ===============================
            // HEADER ROW (VISIBLE COLUMNS)
            // ===============================
            let headerRow = [];
            $('#'+table_id+' thead th').each(function () {
                if (!$(this).hasClass('header-section')) {
                    headerRow.push('"' + $(this).text().trim() + '"');
                    colCount++;
                }
            });
            csv.push(headerRow.join(','));

            $('#'+table_id+' tbody tr').each(function () {

                let $tr = $(this);

                if ($tr.text().includes('Daily Summary')) {
                    return;
                }

                let row = [];
                let filled = 0;

                $tr.children('td').each(function () {

                    if ($(this).hasClass('header-section')) return;

                    let colspan = parseInt($(this).attr('colspan')) || 1;
                    let text = $(this).text().trim().replace(/\s+/g, ' ');
                    text = text.replace(/"/g, '""');

                    for (let i = 0; i < colspan; i++) {
                        row.push('"' + text + '"');
                        filled++;
                    }
                });

                while (filled < colCount) {
                    row.push('""');
                    filled++;
                }

                csv.push(row.join(','));
               
                if ($tr.text().trim().match(/^Total\s*\(/)) {
                    let blank = Array(colCount).fill('""').join(',');
                    csv.push(blank);
                }
            });


            downloadCSV(csv.join('\n'), 'purchase_report.csv');
        });

        
        // Download helper
        function downloadCSV(csv, filename) {
            let blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        
            let link = document.createElement('a');
            let url = URL.createObjectURL(blob);
        
            link.setAttribute('href', url);
            link.setAttribute('download', filename);
            link.style.visibility = 'hidden';
        
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


const sensitiveColumns = [
    'net_weight',
    'cut_weight',
    'actual_weight',
    'invoice_amount',
    'gst_amount',
    'taxable_amount',
    'actual_amount',
    'actual_with_gst',
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

        $('#purchase_table thead th').each(function (index) {
            if ($(this).data('column') === column) {
                colIndex = index;
            }
        });

        if (colIndex === -1) return;

        let hasValue = false;

        $("#purchase_table tbody tr").each(function () {

            let cell = $(this).find("td").eq(colIndex);
            let text = cell.text().trim();

            text = text.replace(/[\[\]]/g, '');

            let values = text.split(',');

            let hasNonZero = false;

            values.forEach(v => {
                let num = parseFloat(v.trim());
                if (!isNaN(num) && num !== 0) {
                    hasNonZero = true;
                }
            });

            if (hasNonZero) {
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
                $(this).prop('checked', true);
            }
        }
    }
});
</script>
@endsection