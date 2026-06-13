@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

@if (session('error'))
<div class="alert alert-danger mt-3">
    {{ session('error') }}
</div>
@endif

@if (session('success'))
<div class="alert alert-success mt-3">
    {{ session('success') }}
</div>
@endif

<nav>
    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
        <li class="breadcrumb-item">Dashboard</li>

        <img
            src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}"
            class="px-1"
            alt=""
        >

        <li class="breadcrumb-item fw-bold font-heading">
            ITC Ledger
        </li>
    </ol>
</nav>

<div class="card border-0 shadow-sm rounded-4 mb-4">

<div class="card-header border-0 text-white rounded-top-4"
style="background: linear-gradient(135deg,#0d6efd,#084298); padding:18px 25px;">

<div class="d-flex justify-content-between align-items-center">
<div>
    <h4 class="mb-1 fw-bold">ITC Ledger</h4>
    <small class="opacity-75">GST ITC Ledger Details</small>
</div>
</div>

</div>

<div class="card-body p-4">

<div class="rounded-4 p-4 mb-4"
style="background:#f8fbff;border:1px solid #dce8ff;">

<form method="GET" action="{{ route('itc.ledger') }}" id="itc-ledger-form">
    <div class="row align-items-end">
        <div class="col-md-4 mb-3">
            <label class="fw-bold mb-2">
                Select GST Number
            </label>
            <select
                name="gst_no"
                id="gst_no"
                class="form-control form-select select2-single"
                required
            >
                <option value="">
                    Select GSTIN
                </option>
                @foreach($gst as $g)
                    <option
                        value="{{ $g->gst_no }}"
                        {{ old('gst_no', $selected_gst ?? '') == $g->gst_no ? 'selected' : '' }}
                    >
                        {{ $g->gst_no }}
                    </option>
                @endforeach

            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="fw-bold mb-2">
                From Date
            </label>
            <input
                type="date"
                name="from_date"
                id="from_date"
                class="form-control"
                value="{{ old('from_date', $from_date ?? '') }}"
                required
            >
        </div>
        <div class="col-md-3 mb-3">
            <label class="fw-bold mb-2">
                To Date
            </label>
            <input
                type="date"
                name="to_date"
                id="to_date"
                class="form-control"
                value="{{ old('to_date', $to_date ?? '') }}"
                required
            >
        </div>
        <div class="col-md-2 mb-3">
            {{-- <button
                type="button"
                id="submit"
                class="btn btn-primary w-100 rounded-pill"
            >
                Search
            </button> --}}
            <button
                type="submit"
                id="real-submit" class="btn btn-primary w-100 rounded-pill"
                
            >Search</button>
        </div>
</div>

</form>

</div>

{{-- OTP MODAL --}}
<div class="modal fade" id="otpModal" tabindex="-1">

<div class="modal-dialog modal-dialog-centered">

<div class="modal-content p-4 border-divider border-radius-8">

<div class="modal-header border-0 p-0">

<h5 class="modal-title">
    OTP Verification
</h5>

<button
    type="button"
    class="btn-close"
    data-bs-dismiss="modal"
></button>

</div>

<div class="modal-body">

<div class="form-group">

<input
    type="text"
    class="form-control"
    id="otp"
    placeholder="Enter OTP"
>

<input type="hidden" id="fgstin">

</div>

</div>

<div class="modal-footer border-0 mx-auto p-0">

<button
    type="button"
    class="btn btn-border-body"
    data-bs-dismiss="modal"
>
    CANCEL
</button>

<button
    type="button"
    class="ms-3 btn btn-danger verify_otp"
>
    SUBMIT
</button>

</div>

</div>

</div>

</div>

{{-- TABLE --}}
@if(isset($ledger['itcLdgDtls']))

@php

$details = $ledger['itcLdgDtls'];

$opening = $details['op_bal'] ?? [];
$closing = $details['cl_bal'] ?? [];
$transactions = $details['tr'] ?? [];

@endphp

<div class="table-responsive rounded-4 border">

<table
class="table table-hover align-middle mb-0"
style="white-space: nowrap; min-width: 1800px;"
>

<thead
style="background:#0d6efd;color:#fff;"
>

<tr>

<th class="text-center py-3">Date</th>
<th class="text-center py-3">Description</th>
<th class="text-center py-3">Type</th>
<th class="text-center py-3">Reference No.</th>
<th class="text-center py-3">Return Period</th>
<th class="text-center py-3">IGST Amount</th>
<th class="text-center py-3">CGST Amount</th>
<th class="text-center py-3">SGST Amount</th>
<th class="text-center py-3">CESS Amount</th>
<th class="text-center py-3">Total Transaction</th>
<th class="text-center py-3">IGST Balance</th>
<th class="text-center py-3">CGST Balance</th>
<th class="text-center py-3">SGST Balance</th>
<th class="text-center py-3">CESS Balance</th>
<th class="text-center py-3">Total Balance</th>

</tr>

</thead>

<tbody>

<tr style="background:#e8f2ff;font-weight:600;">

<td>{{ $details['fr_dt'] ?? '' }}</td>

<td>Opening Balance</td>

<td colspan="8" class="text-center">--</td>

<td>{{ number_format($opening['igstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($opening['cgstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($opening['sgstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($opening['cessTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($opening['tot_rng_bal'] ?? 0, 2) }}</td>

</tr>

@forelse($transactions as $tr)

<tr>

<td>{{ $tr['dt'] ?? '' }}</td>

<td>{{ $tr['desc'] ?? '' }}</td>

<td class="text-center">

@if(($tr['tr_typ'] ?? '') == "Cr")

<span class="badge bg-success px-3 py-2">
    Credit
</span>

@else

<span class="badge bg-danger px-3 py-2">
    Debit
</span>

@endif

</td>

<td>{{ $tr['ref_no'] ?? '' }}</td>

<td>{{ $tr['ret_period'] ?? '' }}</td>

<td>{{ number_format($tr['igstTaxAmt'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['cgstTaxAmt'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['sgstTaxAmt'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['cessTaxAmt'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['tot_tr_amt'] ?? 0, 2) }}</td>

<td>{{ number_format($tr['igstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['cgstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['sgstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['cessTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($tr['tot_rng_bal'] ?? 0, 2) }}</td>

</tr>

@empty

<tr>
<td colspan="15" class="text-center py-4">
    No Transaction Found
</td>
</tr>

@endforelse

<tr style="background:#d9ffe7;font-weight:700;">

<td>{{ $details['to_dt'] ?? '' }}</td>

<td>Closing Balance</td>

<td colspan="8" class="text-center">--</td>

<td>{{ number_format($closing['igstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($closing['cgstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($closing['sgstTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($closing['cessTaxBal'] ?? 0, 2) }}</td>
<td>{{ number_format($closing['tot_rng_bal'] ?? 0, 2) }}</td>

</tr>

</tbody>

</table>

</div>

@endif

</div>
</div>
</div>
</div>
</section>
</div>

@include('layouts.footer')

<script>

$(document).ready(function () {
    let session_error = @json(session('error'));
    let merchant_gst = @json(session('merchant_gst'));
    let from_date = @json(session('from_date'));
    let to_date = @json(session('to_date'));
    $(".select2-single").select2({
        width: '100%'
    });
    if(session_error=="Please Generate Token." && merchant_gst!=null && merchant_gst!=''){
        $('#fgstin').val(merchant_gst);
        $('#gst_no').val(merchant_gst).trigger('change');
        $('#otpModal').modal('show');
        if(from_date!=null){
            $("#from_date").val(from_date);
        }
        if(to_date!=null){
            $("#to_date").val(to_date);
        }
    }
    $('#submit').on('click', function () {

        let gst_no = $('#gst_no').val();
        let from_date = $('#from_date').val();
        let to_date = $('#to_date').val();
        let type = "itcLedger";
        console.log(type);
        if (!gst_no || !from_date || !to_date) {

            alert("Please fill all fields");
            return;
        }

        let from = new Date(from_date);
        let to = new Date(to_date);

        let months =
            (to.getFullYear() - from.getFullYear()) * 12 +
            (to.getMonth() - from.getMonth());

        if (months > 12) {

            alert("From date to date should not have more than 12 month gap");
            return;
        }

        $.ajax({

            url: "{{ route('gstr1-detail') }}",

            type: 'POST',

            data: {

                _token: '{{ csrf_token() }}',
                series: gst_no,
                from_date: from_date,
                to_date: to_date,
                 type : type

            },

            success: function(res) {

                if (
                    res.status === true &&
                    res.message === 'TOKEN-VALID'
                ) {

                    $('#real-submit').click();

                } else if (
                    res.status === true &&
                    res.message === 'TOKEN-OTP'
                ) {

                    $('#fgstin').val(gst_no);

                    $('#otpModal').modal('show');

                } else {

                    alert(res.message || 'Token Error');

                }

            },

            error: function() {

                alert('Something went wrong while validating token');

            }

        });

    });

    $('.verify_otp').on('click', function () {

        let otp = $('#otp').val();
        let fgstin = $('#fgstin').val();

        if (!otp) {

            alert("Please enter OTP");
            return;
        }

        $.ajax({

            url: "{{ route('verify-gst-token-otp') }}",

            method: 'POST',

            data: {

                _token: '{{ csrf_token() }}',
                otp: otp,
                gstin: fgstin

            },

            success: function(res) {

                if(res!=""){

                    let obj = JSON.parse(res);

                    if(obj.status==true){

                        $('#otpModal').modal('hide');

                        $('#real-submit').click();

                    }else{

                        alert(obj.message);

                    }

                }else{

                    alert("Something Went Wrong.Please Try Again.");

                }

            },

            error: function() {

                alert("OTP Verification Failed");

            }

        });

    });

});

</script>

@endsection