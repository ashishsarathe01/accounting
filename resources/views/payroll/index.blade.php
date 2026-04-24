@extends('layouts.app')
@section('content')

@include('layouts.header')

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
<h5 class="transaction-table-title m-0 py-2">Payroll Heads</h5>

<div class="d-flex">
<input type="text" id="search" class="form-control me-3" placeholder="Search">

<a href="{{ route('payroll.create') }}" class="btn btn-xs-primary">
ADD
<svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
<path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white"/>
</svg>
</a>
</div>
</div>

<div class="transaction-table bg-white table-view shadow-sm">
<table class="table-striped table m-0 shadow-sm payroll_table">
<thead>
<tr class="font-12 text-body bg-light-pink">
<th>Name</th>
<th>Type</th>
<th>Income Type</th>
<th>Calculation</th>
<th class="text-center">Action</th>
</tr>
</thead>

<tbody>
    @php
$headNames = $headNames->toArray(); @endphp
@forelse($payroll_heads as $head)
<tr class="font-14 font-heading bg-white">
<td>{{ $head->name }}</td>
<td class="text-uppercase">{{ $head->type }}</td>
<td>{{ ucfirst($head->income_type) }}</td>
<td>

@if($head->calculation_type == 'percentage')

    {{ $head->percentage }} % of Basic

@elseif($head->calculation_type == 'custom_formula')

    @php
        $formulaHeads = $head->formula_heads ? json_decode($head->formula_heads, true) : [];
        foreach($formulaHeads as $k=>$h){
            $formulaHeads[$k] = $headNames[$h];
        }
    @endphp
    ({{ implode(' + ', $formulaHeads) }}) × {{ $head->percentage }}%

@else

    User Defined

@endif

</td>

<td class="text-center">

<a href="{{ route('payroll.edit', $head->id) }}">
<img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1">
</a>

<button type="button"
class="border-0 bg-transparent delete"
data-id="{{ $head->id }}">
<img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1">
</button>

</td>
</tr>
@empty
<tr>
<td colspan="5" class="text-center">No Payroll Heads Found</td>
</tr>
@endforelse

</tbody>
</table>
</div>

</div>
</div>
</section>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="delete_modal" tabindex="-1">
<div class="modal-dialog modal-dialog-centered w-360">
<div class="modal-content p-4 border-divider border-radius-8">
<div class="modal-header border-0 p-0">
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<form method="POST" action="{{ route('payroll.delete') }}">
@csrf
<div class="modal-body text-center">
<img class="delete-icon mb-3 d-block mx-auto"
src="{{ asset('public/assets/imgs/administrator-delete-icon.svg') }}">
<h5>Delete this record?</h5>
<p>This process cannot be undone.</p>
</div>

<input type="hidden" name="id" id="delete_id">

<div class="modal-footer border-0 mx-auto">
<button type="button" class="btn btn-border-body" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-red ms-3">Delete</button>
</div>

</form>
</div>
</div>
</div>
@include('layouts.footer')

<script>
$(document).on('click','.delete',function(){
$('#delete_id').val($(this).data('id'));
$('#delete_modal').modal('show');
});

$("#search").keyup(function () {
var value = this.value.toLowerCase().trim();
$(".payroll_table tbody tr").each(function () {
$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
});
});
</script>

@endsection