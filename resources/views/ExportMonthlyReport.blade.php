@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

    <section class="list-of-view-company-section container-fluid">

        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <div class="d-flex justify-content-between align-items-center py-4 px-2">

                    <h4 class="m-0">
                        Export Monthly Report
                    </h4>

                </div>


                <div class="bg-white shadow-sm p-4 rounded">

                    <form method="GET"
                          action="{{ route('export.monthly.report.download') }}">

                        <div class="row align-items-end">

                            <div class="col-md-3">

                                <label class="form-label fw-bold">
                                    Select Month
                                </label>

                                <input type="month"
                                       name="month"
                                       class="form-control"
                                       required>

                            </div>


<div class="col-md-3">

    <button type="button"
            class="btn btn-dark w-100"
            id="openStockReportModal">

        Download Complete Report

    </button>

</div>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </section>

</div>
<!-- STOCK REPORT MODAL -->

<div class="modal fade"
     id="stockReportModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    Download Complete Report

                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">

                </button>

            </div>


            <form method="POST"
                id="previewReportForm"
                action="{{ route('export.monthly.report.preview') }}"
                target="_blank">
                @csrf

                <div class="modal-body">

    {{-- BANK --}}

    <div class="mb-3">

        <label class="form-label fw-bold">

            Bank

        </label>

        @php

            $banks = DB::table('banks')

                ->where(
                    'company_id',
                    Session::get('user_company_id')
                )

                ->where(
                    'delete',
                    '0'
                )

                ->where(
                    'status',
                    '1'
                )

                ->orderBy('bank_name')

                ->get();

        @endphp

        <select name="bank_id"
                class="form-control select2-single"
                required>

            <option value="">

                Select Bank

            </option>

            @foreach($banks as $bank)

                <option value="{{ $bank->id }}">

                    {{ $bank->bank_name }}
                    (
                    {{ $bank->account_no }}
                    )

                </option>

            @endforeach

        </select>

    </div>


    {{-- REPORT TYPE --}}

    <div class="mb-3">

        <label class="form-label fw-bold">

            Report Type

        </label>

        <select name="stock_type"
                class="form-control"
                required>

            <option value="group">

                Group Wise

            </option>

            <option value="item">

                Item Wise

            </option>

        </select>

    </div>


    <input type="hidden"
           name="report_type"
           value="complete">


    <input type="hidden"
           id="stock_month"
           name="month">

</div>


                <div class="modal-footer">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        Close

                    </button>

                    <button type="submit"
                            class="btn btn-dark">
                        Preview
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>
@include('layouts.footer')
<script>

    $('#openStockReportModal').click(function(){

        let monthField =
            $('input[name="month"]');

        if(monthField.val() == '')
        {
            monthField.focus();

            return false;
        }

        $('#stock_month')
            .val(monthField.val());

        $('#stockReportModal')
            .modal('show');

    });

    $('#previewReportForm').submit(function(){

        let month = $('#stock_month').val();
        let bank  = $('select[name="bank_id"]').val();
        let type  = $('select[name="stock_type"]').val();

        if(!month || !bank || !type)
        {
            alert('Please fill all fields.');

            return false;
        }

        // form submits normally, opens preview in new tab
        return true;

    });

</script>
@endsection