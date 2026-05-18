@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">


@if(session('success'))

<div class="alert alert-success mt-3">
    {{ session('success') }}
</div>

@endif


<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3">

<div class="d-flex align-items-center justify-content-between">

    <h5 class="transaction-table-title m-0">

        Box Calculator Configuration

    </h5>

</div>

</div>


<div class="bg-white shadow-sm p-4">

<form method="POST"
      action="{{ route('box-calculator.configuration.save') }}">

@csrf


<div class="row">


<div class="col-md-4 mb-3">

    <label>
        Conversion Cost Type
    </label>

    <select name="conversion_type"
            class="form-select">

        <option value="kg"
        {{ isset($config)
        && $config->conversion_type == 'kg'
        ? 'selected' : '' }}>

            Per KG

        </option>

        <option value="percent"
        {{ isset($config)
        && $config->conversion_type == 'percent'
        ? 'selected' : '' }}>

            Percentage

        </option>

    </select>

</div>



<div class="col-md-4 mb-3">

    <label>
        Conversion Cost
    </label>

    <input type="number"
           step="0.01"
           name="conversion_cost"
           class="form-control"

           value="{{
           $config->conversion_cost ?? 0
           }}">

</div>



<div class="col-md-4 mb-3">

    <label>
        Default Flute Factor
    </label>

    <input type="number"
           step="0.01"
           name="flute_factor"
           class="form-control"

           value="{{
           $config->flute_factor ?? 1.50
           }}">

</div>



<div class="col-md-4 mb-3">

    <label>
        GST %
    </label>

    <input type="number"
           step="0.01"
           name="gst_percent"
           class="form-control"

           value="{{
           $config->gst_percent ?? 0
           }}">

</div>



<div class="col-md-4 mb-3">

    <label>
        Joint Allowance
    </label>

    <input type="number"
           step="0.01"
           name="joint_allowance"
           class="form-control"

           value="{{
           $config->joint_allowance ?? 0
           }}">

</div>



<div class="col-md-4 mb-3">

    <label>
        Cutting Margin
    </label>

    <input type="number"
           step="0.01"
           name="cutting_margin"
           class="form-control"

           value="{{
           $config->cutting_margin ?? 0
           }}">

</div>


</div>



<div class="mt-4">

    <button type="submit"
            class="btn btn-primary">

        Save Configuration

    </button>

</div>


</form>

</div>

</div>

</div>

</section>

</div>

@include('layouts.footer')

@endsection