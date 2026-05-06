@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- TITLE BAR --}}
<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3 d-flex justify-content-between align-items-center">

    <h5 class="transaction-table-title m-0">
        GSTR2A Reconciliation
    </h5>

    <div>
        <a href="{{ url()->previous() }}" class="btn btn-xs-secondary">
            Back
        </a>
    </div>
</div>

{{-- FILTER INFO --}}
<div class="bg-white px-3 py-2 border-bottom">
    <strong>Month:</strong> {{ date('F, Y', strtotime($month)) }} &nbsp;&nbsp;
    <strong>GSTIN:</strong> {{ $gstin }}
</div>

{{-- TABLE --}}
<div class="bg-white table-view shadow-sm p-3">

<table class="table table-bordered text-center">

    <thead style="background:#f1f1f1;">
        <tr>
            <th>PARTICULARS</th>
            <th>B2B-INVOICE</th>
            <th>B2B-CDNR</th>
        </tr>
    </thead>

    <tbody>

        <tr>
            <td class="text-start">AS PER PORTAL</td>
            <td>{{ number_format($summary['b2b_invoice']['portal'],2) }}</td>
            <td>{{ number_format($summary['b2b_cdnr']['portal'],2) }}</td>
        </tr>

        <tr>
            <td class="text-start">ONLY IN PORTAL</td>
            <td>{{ number_format($summary['b2b_invoice']['only_portal'],2) }}</td>
            <td>{{ number_format($summary['b2b_cdnr']['only_portal'],2) }}</td>
        </tr>

        <tr>
            <td class="text-start">ONLY IN BOOKS</td>
            <td>{{ number_format($summary['b2b_invoice']['only_books'],2) }}</td>
            <td>{{ number_format($summary['b2b_cdnr']['only_books'],2) }}</td>
        </tr>

        <tr>
            <td class="text-start">PREVIOUS</td>
            <td>{{ number_format($summary['b2b_invoice']['previous'],2) }}</td>
            <td>{{ number_format($summary['b2b_cdnr']['previous'],2) }}</td>
        </tr>

        <tr class="fw-bold">
            <td class="text-start">TOTAL</td>
            <td>
                {{ number_format(
                    ($summary['b2b_invoice']['portal'] ?? 0)
                    - ($summary['b2b_invoice']['only_portal'] ?? 0)
                    + ($summary['b2b_invoice']['only_books'] ?? 0),
                2) }}
            </td>

            <td>
                {{ number_format(
                    ($summary['b2b_cdnr']['portal'] ?? 0)
                    - ($summary['b2b_cdnr']['only_portal'] ?? 0)
                    + ($summary['b2b_cdnr']['only_books'] ?? 0),
                2) }}
            </td>
        </tr>

        <tr class="fw-bold">
            <td class="text-start">AS PER BOOKS</td>
            <td>{{ number_format($summary['b2b_invoice']['books'],2) }}</td>
            <td>{{ number_format($summary['b2b_cdnr']['books'],2) }}</td>
        </tr>

        <tr style="color:red; font-weight:bold;">
            <td class="text-start">DIFFERENCE</td>
            <td>
                {{ number_format(
                    (
                        ($summary['b2b_invoice']['portal'] ?? 0)
                        - ($summary['b2b_invoice']['only_portal'] ?? 0)
                        + ($summary['b2b_invoice']['only_books'] ?? 0)
                    )
                    - ($summary['b2b_invoice']['books'] ?? 0),
                2) }}
            </td>

            <td>
                {{ number_format(
                    (
                        ($summary['b2b_cdnr']['portal'] ?? 0)
                        - ($summary['b2b_cdnr']['only_portal'] ?? 0)
                        + ($summary['b2b_cdnr']['only_books'] ?? 0)
                    )
                    - ($summary['b2b_cdnr']['books'] ?? 0),
                2) }}
            </td>
        </tr>

    </tbody>

</table>

</div>

</div>
</div>
</section>
</div>

@endsection