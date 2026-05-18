@extends('layouts.app')

@section('content')

@include('layouts.header')

<style>
@media print {
    body * {
        visibility: hidden;
    }

    #printableArea,
    #printableArea * {
        visibility: visible;
    }

    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .no-print {
        display: none !important;
    }

    .row.vh-100,
    .vh-100 {
        height: auto !important;
        min-height: unset !important;
    }

    body, html {
        height: auto !important;
    }
}
</style>

<div class="list-of-view-company">

    <section class="list-of-view-company-section container-fluid">

        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <div id="printableArea" class="bg-white p-4 shadow-sm border mb-4">

                    <div class="text-center mb-4">
                        <h3 class="mb-1 fw-bold text-uppercase">
                            {{ $company->company_name ?? '' }}
                        </h3>
                        <div>{{ $company->address ?? '' }}</div>
                        <div>{{ $company->mobile ?? '' }}</div>
                        <h4 class="mt-3 text-decoration-underline">QUOTATION</h4>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h5 class="mb-2">Quotation To,</h5>
                            <div class="fw-bold">{{ $quotation->party_name }}</div>
                            <div>{!! nl2br($quotation->address) !!}</div>
                            <div>{{ $quotation->mobile }}</div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div>
                                <strong>Date :</strong>
                                {{ date('d/m/Y', strtotime($quotation->quotation_date)) }}
                            </div>
                        </div>
                    </div>

                    <table class="table table-bordered align-top">
                        <thead>
                            <tr>
                                <th width="5%">Sr.No</th>
                                <th width="35%">Description</th>
                                <th width="40%">Paper Specification</th>
                                <th width="20%">Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>

                                <td>
                                    <div class="fw-bold mb-1">
                                        {{ $item->ply }} Box {{ $item->box_name }}
                                    </div>
                                    <div>
                                        <strong>Outer Dimensions :</strong>
                                        {{ $item->dimensions }}
                                    </div>
                                </td>

                                <td>
                                    <div>{!! nl2br(e($item->paper_specification)) !!}</div>
                                </td>

                                <td>
                                    <div>Rs {{ number_format($item->rate, 2) }}</div>
                                    <div class="mt-1">
                                        Tax @ {{ number_format($item->gst_percent, 2) }} %
                                    </div>
                                    <div>Rs {{ number_format($item->gst_amount, 2) }}</div>
                                    <hr class="my-1">
                                    <div class="fw-bold">
                                        Total : Rs {{ number_format($item->total_amount, 2) }}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <strong>Terms &amp; Conditions:</strong>
                            <br>
                            <br>
                            <br>
                            <br>
                            <br>
                            <div class="mt-2">
                                Auto generated copy, no signature required.
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="mt-4 fw-bold">
                                For {{ $company->company_name ?? '' }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 no-print">
                        <button class="btn btn-primary btn-sm" onclick="window.print()">
                            PRINT
                        </button>
                    </div>

                </div>

            </div>

        </div>

    </section>

</div>

@include('layouts.footer')

@endsection