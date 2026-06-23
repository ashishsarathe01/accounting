@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3">

        <div class="d-flex align-items-center justify-content-between">

            <h5 class="transaction-table-title m-0">
                GST Return Compliance
            </h5>

            <a href="{{ route('gst-return-compliance.create') }}"
               class="btn btn-xs-primary text-nowrap">
                ADD COMPLIANCE
            </a>

        </div>

    </div>

    <div class="bg-white table-view shadow-sm" style="overflow-x:auto;">

        <table class="table table-bordered table-striped m-0">

            <thead>

                <tr class="bg-light-pink text-body">

                    <th>GST Number</th>

                    <th>Month</th>

                    <th>Return Type</th>

                    <th>ARN Number</th>

                    <th>Lock Status</th>

                    <th class="text-center">
                        Action
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse($compliances as $row)

                    <tr>

                        <td>
                            {{ $row->gst_number }}
                        </td>

                        <td>
                            {{ date('M Y', strtotime($row->month_year . '-01')) }}
                        </td>

                        <td>
                            {{ $row->return_type }}
                        </td>

                        <td>
                            {{ $row->arn_number }}
                        </td>

                        <td>

                            @if($row->is_locked == 1)

                                <span class="badge bg-danger">
                                    Locked
                                </span>

                            @else

                                <span class="badge bg-success">
                                    Unlocked
                                </span>

                            @endif

                        </td>

                        <td class="text-center">

                            <a href="{{ route('gst-return-compliance.edit',$row->id) }}">

                                <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}"
                                     class="px-1">

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6"
                            class="text-center">

                            No compliance records found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</div>

</section>

</div>

@endsection