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
Profit & Loss Group Mapping
</h5>

</div>

<form method="POST"
action="{{ route('profitloss.group.mapping.save') }}">

@csrf

<div class="card shadow-sm">

<div class="card-body p-0">

<table class="table table-bordered mb-0">

<thead>

<tr>
    <th width="50%">Account Group</th>
    <th width="50%">Profit & Loss Mapping</th>
</tr>

</thead>

<tbody>

@foreach($groups as $group)

<tr>

<td>
    {{$group->name}}
</td>

<td>

<select
class="form-select"
name="mapping[{{$group->unique_key}}]"
>

<option value="">
    Select
</option>

@foreach($mappingOptions as $option)

<option
value="{{$option}}"
@if(
    isset($mappings[$group->unique_key])
    &&
    $mappings[$group->unique_key] == $option
)
selected
@endif
>
{{$option}}
</option>

@endforeach

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

@endsection