@extends('layouts.app')

@section('content')
@include('layouts.header')

<style>
    td,
    th {
        font-size: 18px;
    }
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-10 col-sm-12 px-4">
                <div class="container-fluid">

                    <div class="card mt-4">
                        <div class="card-header bg-info text-white">
                            <strong>6.1 Payment of Tax</strong>
                        </div>

                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <strong>Ledger Balance</strong>
                                </h5>

                                <button type="button" class="btn btn-primary btn-sm">
                                    Refresh
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light text-center">
                                        <tr>
                                            <th style="width:30%;">Particulars</th>
                                            <th>IGST</th>
                                            <th>CGST</th>
                                            <th>SGST</th>
                                            <th>CESS</th>
                                            <th>TOTAL</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong>Credit Ledger Balance</strong>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <strong>Cash Ledger Balance</strong>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-5">
                                <h5 class="mb-3">
                                    <strong>Payment Details</strong>
                                </h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-center" style="table-layout:fixed;width:100%;">
                                        <thead>
                                            <tr class="table-light">
                                                <th rowspan="2" style="width:12%;vertical-align:middle;">Particulars</th>

                                                <th colspan="2">Liability</th>

                                                <th rowspan="2" style="width:6%;vertical-align:middle;">IGST</th>
                                                <th rowspan="2" style="width:6%;vertical-align:middle;">CGST</th>
                                                <th rowspan="2" style="width:6%;vertical-align:middle;">SGST</th>

                                                <th rowspan="2" style="width:13%;vertical-align:middle;">Cash In RCM</th>

                                                <th rowspan="2" style="width:16%;vertical-align:middle;">Other Than RCM</th>

                                                <th rowspan="2" style="width:18%;vertical-align:middle;">Balance To Be Paid</th>
                                            </tr>

                                            <tr class="table-light">
                                                <th style="width:8%;">RCM</th>
                                                <th style="width:16%;">Other Than RCM</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <tr>
                                                <td class="text-start">
                                                    <strong>IGST</strong>
                                                </td>

                                                <td></td>
                                                <td></td>

                                                <td></td>
                                                <td></td>
                                                <td></td>

                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>

                                            <tr>
                                                <td class="text-start">
                                                    <strong>CGST</strong>
                                                </td>

                                                <td></td>
                                                <td></td>

                                                <td></td>
                                                <td></td>
                                                <td></td>

                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>

                                            <tr>
                                                <td class="text-start">
                                                    <strong>SGST</strong>
                                                </td>

                                                <td></td>
                                                <td></td>

                                                <td></td>
                                                <td></td>
                                                <td></td>

                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <button type="button" class="btn btn-primary px-4 py-2">
                                        SAVE
                                    </button>

                                    <button type="button" class="btn btn-primary px-4 py-2">
                                        CREATE CHALLAN
                                    </button>

                                    <button type="button" class="btn btn-primary px-4 py-2">
                                        PREVIEW
                                    </button>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-3">
                                    <button type="button" class="btn btn-primary px-5 py-2">
                                        OFFSET
                                    </button>

                                    <button type="button" class="btn btn-primary px-5 py-2">
                                        FILE
                                    </button>
                                </div>
                            </div>    
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@endsection