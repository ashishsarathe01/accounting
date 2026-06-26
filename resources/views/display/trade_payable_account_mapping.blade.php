@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

<h5 class="master-table-title m-0 py-2">
Trade Payable Account Mapping
</h5>

</div>

<form method="POST"
action="{{ route('trade.payable.account.mapping.save') }}">

@csrf

<div class="card shadow-sm">

<div class="card-body p-0">

<table class="table table-bordered mb-0">

<thead>

<tr>
    <th width="20%">Group</th>
    <th width="50%">Account</th>
    <th width="30%">Trade Payable Type</th>
</tr>

</thead>

<tbody>

@foreach($accounts as $account)

<tr>

<td>
    {{ $account->group_name }}
</td>

<td>
    {{ $account->account_name }}
</td>

<td>

<select
class="form-select select2-single"
name="trade_type[{{ $account->id }}]"
>

<option value="">
    Select
</option>

<option
value="A"
@if(
    ($existingMappings[$account->id] ?? '')
    == 'A'
)
selected
@endif
>
(A) Micro enterprises and small enterprises
</option>

<option
value="B"
@if(
    ($existingMappings[$account->id] ?? '')
    == 'B'
)
selected
@endif
>
(B) Others
</option>

</select>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

<div class="mt-3 text-end">

<button
type="submit"
class="btn btn-primary"
>
Save Mapping
</button>

</div>

</form>

</div>

</div>

</section>
</div>

@include('layouts.footer')
<script>
$(document).ready(function(){

    $('.select2-single').select2({
        width: '100%',
        placeholder: 'Select',
        allowClear: true
    });

});
</script>
@endsection