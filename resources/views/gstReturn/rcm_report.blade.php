@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="col-md-10">

<div class="card mt-3 shadow-sm">
<div class="card-header bg-primary text-white">
    <h5  styel="color:white;"class="mb-0">RCM Report – {{ $month }}</h5>
</div>

<div class="card-body">

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(empty($rcmData))
    <div class="alert alert-warning">No RCM entries found for selected month.</div>
@else

<form method="POST"  action="{{ $exist ? route('rcm.update', $exist->journal_id) : route('rcm.store') }}">
@csrf

<div class="table-responsive">
<table class="table table-bordered table-striped">

<thead class="table-light">
<tr>
    <th width="40">
        <input type="checkbox" id="select_all">
    </th>
    <th>Date</th>
    <th>Account</th>
    <th class="text-end">Amount</th>
    <th class="text-end">CGST</th>
    <th class="text-end">SGST</th>
    <th class="text-end">IGST</th>
</tr>
</thead>

<tbody>
@foreach($rcmData as $row)
<tr>
    <td>
        <input type="checkbox"
            class="row-check"
            data-amount="{{ $row['amount'] }}"
            data-cgst="{{ $row['cgst'] }}"
            data-sgst="{{ $row['sgst'] }}"
            data-igst="{{ $row['igst'] }}">
    </td>
    <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
    <td>{{ $row['account'] }}</td>
    <td class="text-end">{{ number_format($row['amount'],2) }}</td>
    <td class="text-end">{{ number_format($row['cgst'],2) }}</td>
    <td class="text-end">{{ number_format($row['sgst'],2) }}</td>
    <td class="text-end">{{ number_format($row['igst'],2) }}</td>
</tr>
@endforeach
</tbody>

<tfoot class="fw-bold table-secondary">
<tr>
    <td colspan="3" class="text-end">TOTAL (Selected)</td>
    <td class="text-end" id="total_amt_text">0.00</td>
    <td class="text-end" id="total_cgst_text">0.00</td>
    <td class="text-end" id="total_sgst_text">0.00</td>
    <td class="text-end" id="total_igst_text">0.00</td>
</tr>
</tfoot>

</table>
</div>

{{-- Hidden Fields --}}
<input type="hidden" name="month" value="{{ $month }}">
<input type="hidden" name="total_amt" id="total_amt">
<input type="hidden" name="cgst" id="cgst">
<input type="hidden" name="sgst" id="sgst">
<input type="hidden" name="igst" id="igst">
<input type="hidden" name="gstNo" value="{{ $gstNo }}">
@if($exist)
    @method('PUT')
    <input type="hidden" name="journal_id" value="{{ $exist->journal_id }}">
@endif

<div class="text-end mt-3">
 <button type="submit"
            class="btn btn-success"
            onclick="return document.getElementById('total_amt').value > 0;">
        <i class="fa fa-save"></i>
        {{ $exist ? 'Update RCM report and Journal for RCM' : 'Save & pass Journal for RCM' }}
    </button>
</div>

</form>
@endif

</div>
</div>

</div>
</div>
</section>
</div>

@include('layouts.footer')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const selectAll = document.getElementById('select_all');
    const checkboxes = document.querySelectorAll('.row-check');

    function calculateTotals() {
        let totalAmt = 0, cgst = 0, sgst = 0, igst = 0;

        checkboxes.forEach(cb => {
            if (cb.checked) {
                totalAmt += parseFloat(cb.dataset.amount);
                cgst += parseFloat(cb.dataset.cgst);
                sgst += parseFloat(cb.dataset.sgst);
                igst += parseFloat(cb.dataset.igst);
            }
        });

        document.getElementById('total_amt_text').innerText = totalAmt.toFixed(2);
        document.getElementById('total_cgst_text').innerText = cgst.toFixed(2);
        document.getElementById('total_sgst_text').innerText = sgst.toFixed(2);
        document.getElementById('total_igst_text').innerText = igst.toFixed(2);

        document.getElementById('total_amt').value = totalAmt.toFixed(2);
        document.getElementById('cgst').value = cgst.toFixed(2);
        document.getElementById('sgst').value = sgst.toFixed(2);
        document.getElementById('igst').value = igst.toFixed(2);
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            calculateTotals();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            if (selectAll) {
                selectAll.checked = [...checkboxes].every(c => c.checked);
            }
            calculateTotals();
        });
    });
});
</script>

@endsection
