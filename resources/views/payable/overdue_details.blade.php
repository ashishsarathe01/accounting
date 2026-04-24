@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
    /* ================= GLOBAL TABLE STYLING ================= */
    td,
    th {
        font-size: 14px !important;
        vertical-align: middle !important;
    }

    .table {
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
    }

    .table thead {
        background: linear-gradient(90deg, #5f6df5, #8f94fb);
        color: #fff;
        font-weight: 600;
    }

    .table thead th {
        border: none !important;
        padding: 8px 10px !important;
        font-size: 13px !important;
    }

    .table tbody td {
        padding: 6px 10px !important;
        font-size: 13px !important;
        line-height: 1.2 !important;
    }

    .table tr {
        height: 30px;
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

    /* Print fix */
    @media print {

        /* Hide header/sidenav/footer on print */
        .header,
        header,
        #header,
        .top-header,
        .noprint,
        .leftnav,
        .sidebar,
        .footer,
        footer,
        #footer,
        .list-of-view-company-section .row > :first-child
        {
            display: none !important;
            visibility: hidden !important;
        }

        /* Make main content full width */
        .col-lg-9,
        .bg-mint,
        .col-md-12 {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 10px !important;
        }

        body {
            background: #fff !important;
        }

        /* Optional: hide buttons only in print */
        .noprint {
            display: none !important;
        }
    }

    @page {
        size: A4;        /* Always A4 size page (210mm x 297mm) */
        margin: 5mm;     /* Outer margin around content */
    }

    /* Overdue amount text */
    .text-danger {
        font-weight: 700;
    }

    /* Buttons */
    .btn-warning {
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 6px;
        border: none;
    }

    .btn-success {
        font-size: 14px;
        padding: 8px 16px;
        border-radius: 6px;
    }

    /* Total row */
tfoot tr {
    background: #f1f5ff !important;
    font-size: 15px;
}

tfoot td {
    padding: 12px !important;
}
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <h3 class="mt-2">{{ Session::get('company_name') }}</h3>

                <h4 class="mt-3 text-center font-weight-bold">
                    {{ $acc->account_name }} Overdue Bills Report
                </h4>

                <table class="table table-bordered table-striped mt-4">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Bill No</th>
                            <th style="text-align: right;">Overdue Amount</th>
                            <th class="noprint">View</th>
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
                                            ( {{ formatIndianNumber($b['total'],2) }} )
                                        </span>
                                    @endif
                                </td>

                                <td class="overdue-amount">
                                    {{ formatIndianNumber($b['overdue'],2) }}
                                </td>

                                <td class="noprint">
                                    @if(empty($b['id']))
                                        <a href="{{ url('accountledger-filter') }}?party={{ $acc->id }}&from_date={{ $oDate }}&to_date={{ $today }}"
                                           class="btn btn-sm btn-primary">
                                            View
                                        </a>
                                    @else
                                        <a href="{{ url('purchase-invoice/' . $b['id']) }}"
                                           class="btn btn-sm btn-primary">
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
                    <button onclick="window.print()" class="btn btn-warning noprint me-2">
                        Print
                    </button>
                    <a href="{{ route('payable.overdue.pdf.download', $acc->id) }}?date={{ $today }}"
                       class="btn btn-success">
                        Download PDF
                    </a>
                </div>

            </div>
        </div> <!-- END row -->
    </section>
</div>

@include('layouts.footer')
@endsection
