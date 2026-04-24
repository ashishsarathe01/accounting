@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>

/* ================= GLOBAL TABLE ================= */
td, th {
    font-size: 14px !important;
    vertical-align: middle !important;
}

.table {
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
}

.table thead {
    background: linear-gradient(90deg,#5f6df5,#8f94fb);
    color: #fff;
    font-weight: 600;
}

.table thead th {
    border: none !important;
    padding: 12px;
}

.table tbody td {
    padding: 10px 12px;
}

/* Alternate rows */
.table-striped tbody tr:nth-child(even) {
    background: #f9fbff;
}

/* Hover effect */
.table tbody tr:hover {
    background: #eef4ff !important;
    transition: 0.2s ease;
}

/* Overdue amount style */
.overdue-amount {
    text-align: right;
    font-weight: 700;
    color: #dc3545;
}

/* Total row */
tfoot tr {
    background: #f1f5ff !important;
    font-size: 15px;
}

tfoot td {
    padding: 12px !important;
}

/* Action button */
.btn-view {
    background: linear-gradient(90deg,#007bff,#4da3ff);
    color: #fff;
    border-radius: 6px;
    padding: 4px 10px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    transition: 0.2s ease;
}

.btn-view:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

/* Page header style */
.report-header {
    background: linear-gradient(90deg,#5f6df5,#8f94fb);
    color: #fff;
    border-radius: 12px;
    padding: 12px 20px;
    margin-bottom: 20px;
}

/* Print fix */
@media print {
    .header,
    .leftnav,
    .sidebar,
    .footer,
    .noprint {
        display: none !important;
    }

    body {
        background: #fff !important;
    }

    .col-lg-10 {
        width: 100% !important;
        max-width: 100% !important;
    }
}

@page { 
   margin: 8mm;
}
.table-title-bottom-line {
    margin-bottom: 18px;
}
@media print {

    body * {
        visibility: hidden;
    }

    .print-area,
    .print-area * {
        visibility: visible;
    }

    .print-area {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .noprint {
        display: none !important;
    }
}
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint ">

    <h3>{{ Session::get('company_name') }}</h3>
    <div class="print-area">
<div class="table-title-bottom-line position-relative d-flex justify-content-between
            align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h4 class="mt-2 text-center font-weight-bold">
    {{ $acc->account_name }} Overdue Bills Report
</h4>
</div>

    <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Bill No</th>
            <th style="text-align:right;">Overdue Amount</th>
            <th class="noprint text-center">Action</th>
        </tr>
    </thead>

    <tbody>
        @php $total_overdue = 0; @endphp

        @foreach ($allocated as $b)
        @php $total_overdue += $b['overdue']; @endphp

        <tr>
            <td>
                {{ strtotime($b['date']) ? \Carbon\Carbon::parse($b['date'])->format('d-m-Y') : 'Opening Balance' }}
            </td>

            <td>
                {{ $b['bill_no'] }}
                @if($b['remaining_part'] > 0)
                    <span class="text-muted">
                        ({{ formatIndianNumber($b['total'],2) }})
                    </span>
                @endif
            </td>

            <td class="overdue-amount">
                ₹ {{ formatIndianNumber($b['overdue'],2) }}
            </td>

            <td class="noprint text-center">
                @if(empty($b['id']))
                    <a href="{{ url('accountledger-filter') }}?party={{ $account_id }}&from_date={{ $oDate }}&to_date={{ $today }}"
                       class="btn-view">
                        View
                    </a>
                @else
                    <a href="{{ url('sale-invoice/' . $b['id']) }}"
                       class="btn-view">
                        View
                    </a>
                @endif
            </td>
        </tr>

        @endforeach
    </tbody>

    <tfoot>
        <tr>
            <td colspan="2" class="text-center fw-bold">TOTAL</td>
            <td class="text-end fw-bold text-danger">
                ₹ {{ formatIndianNumber($total_overdue, 2) }}
            </td>
            <td></td>
        </tr>
    </tfoot>
</table>

    <div class="text-center mt-4">
        <button onclick="window.print()" class="btn btn-warning noprint">Print</button>
      <a href="{{ route('overdue.pdf.download', $acc->id) }}?date={{ $today }}" class="btn btn-success noprint">Download PDF</a>
    </div>
</div>
</div>
 </div> <!-- END row -->
    </section>
</div>
@include('layouts.footer')
@endsection
