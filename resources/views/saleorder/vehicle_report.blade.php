@extends('layouts.app')
@section('content')

@include('layouts.header')
<style>
.select2-container .select2-selection--single {
    height: 38px !important;
    padding-top: 4px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 34px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 34px;
}

.d-flex.flex-nowrap {
    flex-wrap: nowrap !important;
}

.form-control-sm, .form-select-sm {
    height: 38px !important;
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">
        Vehicle Freight Report
    </h5>

    <div class="d-flex align-items-center gap-2 flex-nowrap">
        <form method="GET" class="d-flex align-items-center gap-2 m-0 p-0 flex-nowrap">
            <input type="date" name="from_date" class="form-control form-control-sm" 
                   value="{{ request('from_date') }}" style="width: 130px; height: 38px;">
            
            <span class="text-white fs-14 px-1">to</span>
            
            <input type="date" name="to_date" class="form-control form-control-sm" 
                   value="{{ request('to_date') }}" style="width: 130px; height: 38px;">
            
            <select name="vehicle_id" id="vehicle_id" class="form-select form-select-sm" 
                    style="width: 220px; height: 38px; min-width: 220px;">
                <option value="">All Vehicles</option>
                @foreach($vehicle_list as $v)
                <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                    {{ strtoupper($v->vehicle_no) }}
                </option>
                @endforeach
            </select>
            
            <button type="submit" class="btn btn-primary btn-sm px-3 py-1" style="height: 38px; white-space: nowrap;">
                Filter
            </button>
        </form>
        
        <input type="text" id="search" class="form-control form-control-sm" 
               placeholder="Search" style="width: 140px; height: 38px;">
    </div>
</form>
</div>


<div class="transaction-table bg-white table-view shadow-sm">

<table class="table-striped table m-0 shadow-sm vehicle_table">

<thead>
<tr class="font-12 text-body bg-light-pink">
<th>Sr No.</th>
<th>Vehicle No</th>
<th>Bill Date</th>
<th>Bill No</th>
<th>Party Name</th>
<th style="text-align:right;">Amount</th>
</tr>
</thead>

<tbody>

@php
$total_freight = 0;
@endphp

@foreach($vehicles as $key => $vehicle)

<tr class="font-14 font-heading bg-white">

<td>{{ $key + 1 }}</td>

<td>{{ $vehicle->vehicle_no }}</td>

<td>{{ date('d-m-Y',strtotime($vehicle->bill_date)) }}</td>

<td>{{ $vehicle->voucher_no_prefix }}</td>

<td>{{ $vehicle->party_name }}</td>

<td style="text-align:right;">
{{ number_format($vehicle->vehicle_freight_amount,2) }}
</td>
</tr>

@php
$total_freight += $vehicle->vehicle_freight_amount;
@endphp

@endforeach


<tr class="font-14 font-heading bg-white">

<td colspan="5" class="fw-bold text-end">TOTAL</td>

<td class="fw-bold" style="text-align:right;">
{{ number_format($total_freight,2) }}
</td>

</tr>

</tbody>

</table>

</div>

</div>




</div>
</section>
</div>


@include('layouts.footer')


<script>

$("#search").keyup(function () {

var value = this.value.toLowerCase().trim();

$(".vehicle_table tr").each(function (index) {

if (!index) return;

$(this).find("td").each(function () {

var id = $(this).text().toLowerCase().trim();

var not_found = (id.indexOf(value) == -1);

$(this).closest('tr').toggle(!not_found);

return not_found;

});

});

});
$(document).ready(function(){

$('#vehicle_id').select2({
placeholder: "Select Vehicle",
allowClear: true,
width: '200px'
});

});
</script>

@endsection