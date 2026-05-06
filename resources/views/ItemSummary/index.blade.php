@extends('layouts.app')

@section('content')

@include('layouts.header')
<style>
    @media print {

    body {
        margin: 0;
        padding: 0;
        font-size: 12px;
        background: #fff;
    }

    .header-section,
    .sidebar,
    form,
    button {
        display: none !important;
    }

    .col-lg-9 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }

    .table-responsive {
        overflow: visible !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
        font-size: 11px;
    }

    th, td {
        border: 1px solid #000 !important;
        padding: 6px !important;
        text-align: right;
    }

    th:first-child,
    td:first-child {
        text-align: left;
    }

    thead {
        display: table-header-group;
    }

    tfoot {
        display: table-footer-group;
    }

    tr {
        page-break-inside: avoid;
    }

    .text-success,
    .text-danger {
        color: #000 !important; /* colors don’t print well */
    }

}
    
    @page { size: auto;  margin: 0mm; }
    .importantRule { 
       display: none !important;  /* Force hide anything with this class */
    }
    .print-header {
    display: block;
}
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                <div class="table-title-bottom-line d-flex justify-content-between align-items-center
                            bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mt-3 header-section">
                    <h5 class="transaction-table-title m-0">
                        Item Summary (Groups)
                    </h5>
                    <button class="btn btn-info" onclick="printpage();">Print</button>
                </div>

                {{-- Date Filter --}}
                <form method="GET" class="row g-3 align-items-end mt-3">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">From Date</label>
                        <input type="date" name="from_date" value="{{ $from_date }}" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">To Date</label>
                        <input type="date" name="to_date" value="{{ $to_date }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            Apply
                        </button>
                    </div>

                </form>

                <div class="table-responsive mt-4 mb-4">
                    <div class="print-header text-center mb-3">
                        <h4>Item Summary Report</h4>
                        <p>From: {{ date('d-m-Y',strtotime($from_date)) }} To: {{ date('d-m-Y',strtotime($to_date)) }}</p>
                    </div>
                    <table class="table table-bordered table-hover table-sm align-middle bg-white shadow-sm">

                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Group Name</th>
                                <th class="text-end">Opening Qty</th>

                                <th class="text-end">Opening</th>
                                <th class="text-end">Debit Qty</th>

                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit Qty</th>

                                <th class="text-end">Credit</th>
                                <th class="text-end">Closing Qty</th>
                                <th class="text-end">Closing</th>
                            </tr>
                        </thead>

                        @php
                            $grandOpening = 0;
                            $grandDebit   = 0;
                            $grandCredit  = 0;
                            $grandClosing = 0;
                        @endphp

                        <tbody>
                            @forelse($groups as $group)

                                @php
                                    $grandOpening += $group->opening;
                                    $grandDebit   += $group->debit;
                                    $grandCredit  += $group->credit;
                                    $grandClosing += $group->closing;

                                    $openingType = $group->opening < 0 ? 'Cr' : 'Dr';
                                    $closingType = $group->closing < 0 ? 'Cr' : 'Dr';
                                @endphp

                                <tr class="fw-semibold">

                                    <td>
                                        <a href="{{ url('accounting/item-summary/group/'.$group->id.'?from_date='.$from_date.'&to_date='.$to_date) }}"
                                           class="text-decoration-none text-primary">
                                            {{ $group->group_name }}
                                        </a>
                                    </td>
                                    <td class="text-end">{{ formatIndianNumber($group->opening_qty, 2) }}</td>
                                    
                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($group->opening)) }} {{ $openingType }}
                                    </td>
                                    
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($group->debit_qty, 2) }}
                                    </td>
                                    
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($group->debit) }}
                                    </td>
                                    
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($group->credit_qty, 2) }}
                                    </td>
                                    
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($group->credit) }}
                                    </td>
                                    
                                    <td class="text-end">
                                        {{ formatIndianNumber($group->closing_qty, 2) }}
                                    </td>
                                    
                                    <td class="text-end fw-bold">
                                        {{ formatIndianNumber(abs($group->closing)) }} {{ $closingType }}
                                    </td>

                                </tr>

                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        No item groups found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        {{-- Grand Total Row --}}
                        @if(count($groups) > 0)

                            @php
                                $grandOpeningType = $grandOpening < 0 ? 'Cr' : 'Dr';
                                $grandClosingType = $grandClosing < 0 ? 'Cr' : 'Dr';
                            @endphp
                            @php
                                $grandOpeningQty = $groups->sum('opening_qty');
                                $grandDebitQty   = $groups->sum('debit_qty');
                                $grandCreditQty  = $groups->sum('credit_qty');
                                $grandClosingQty = $groups->sum('closing_qty');
                            @endphp

                           <tfoot class="table-light fw-bold">
                                <tr>
                                    <td class="text-end">TOTAL</td>
                                
                                    <td class="text-end">{{ formatIndianNumber($grandOpeningQty, 2) }}</td>
                                
                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($grandOpening)) }} {{ $grandOpeningType }}
                                    </td>
                                
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($grandDebitQty, 2) }}
                                    </td>
                                
                                    <td class="text-end text-success">
                                        {{ formatIndianNumber($grandDebit) }}
                                    </td>
                                
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($grandCreditQty, 2) }}
                                    </td>
                                
                                    <td class="text-end text-danger">
                                        {{ formatIndianNumber($grandCredit) }}
                                    </td>
                                
                                    <td class="text-end">
                                        {{ formatIndianNumber($grandClosingQty, 2) }}
                                    </td>
                                
                                    <td class="text-end">
                                        {{ formatIndianNumber(abs($grandClosing)) }} {{ $grandClosingType }}
                                    </td>
                                </tr>
                            </tfoot>

                        @endif

                    </table>
                </div>

            </div>
        </div>
    </section>
</div>
<script>
    function printpage(){
         window.print();
      }
</script>
@include('layouts.footer')

@endsection