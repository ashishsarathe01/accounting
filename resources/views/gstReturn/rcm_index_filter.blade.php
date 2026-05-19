@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="container mt-4">

<div class="row justify-content-center">

<div class="col-md-8">

<div class="card shadow-sm">

<div class="card-header bg-primary text-white">

    <h5 class="mb-0">
        Generate RCM Report
    </h5>

</div>

<div class="card-body">

@if(session('success'))

    <div class="alert alert-success">
        {{ session('success') }}
    </div>

@endif

@if ($errors->any())

    <div class="alert alert-danger">

        <ul class="mb-0">

            @foreach ($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif

<form method="GET"
      action="{{ route('RcmReport') }}">

    @csrf

    <div class="row">

        <div class="col-md-6 mb-3">

            <label>
                GST Number
            </label>

            <select name="gst_no"
                    class="form-control"
                    required>

                <option value="">
                    Select GST Number
                </option>

                @foreach($gst as $g)

                    <option value="{{ $g->gst_no }}">

                        {{ $g->gst_no }}

                    </option>

                @endforeach

            </select>

        </div>

        <div class="col-md-6 mb-3">

            <label>
                Month
            </label>

            <input type="month"
                   name="month"
                   class="form-control"
                   required>

        </div>

    </div>

    <div class="text-end mt-3">

        <button type="submit"
                class="btn btn-success">

            <i class="fa fa-search"></i>

            Generate RCM Report

        </button>

    </div>

</form>

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