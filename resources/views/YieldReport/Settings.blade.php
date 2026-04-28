@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- SUCCESS --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- TITLE --}}
<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3 d-flex justify-content-between align-items-center">

    <h5 class="transaction-table-title m-0">
        Yield Report – List
    </h5>

    <a href="{{ route('yield-report.create') }}" class="btn btn-xs-primary">
        ADD
    </a>
    <a href="{{ route('yield-report.report') }}">
                    <button type="button" class="btn btn-sm btn-primary">
                        Report
                    </button>
                </a>
</div>

{{-- TABLE --}}
<div class="bg-white table-view shadow-sm" style="overflow-x:auto;">

<table class="table table-bordered table-striped m-0">

    <thead>
        <tr class="bg-light-pink text-body">
            <th>Item Name</th>
            <th>Type</th>
            <th>Recovery Status</th>
            <th>Recovery %</th>
            <th class="text-center">Action</th>
        </tr>
    </thead>

    <tbody>

    @php $group_arr = []; @endphp

    @foreach($data as $row)

        <tr>

            {{-- ITEM --}}
            <td>{{ $row->item_name }}</td>

            {{-- TYPE --}}
            <td>
                @if($row->type == 'material_required')
                    Material Required
                @else
                    Main Raw Material
                @endif
            </td>

            {{-- RECOVERY STATUS --}}
            <td>
                @if($row->type == 'material_required')
                    {{ $row->recovery_status ? 'Yes' : 'No' }}
                @else
                    -
                @endif
            </td>

            {{-- RECOVERY % --}}
            <td>
                @if($row->type == 'material_required' && $row->recovery_status == 1)
                    {{ $row->recovery_percent }} %
                @else
                    -
                @endif
            </td>

            <td class="text-center">

                @if(!in_array($row->report_id, $group_arr))

                    <a href="{{ route('yield-report.edit', $row->report_id) }}">
                        <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1">
                    </a>

                @endif

            </td>

        </tr>

        @php $group_arr[] = $row->report_id; @endphp

    @endforeach

    @if(count($data) == 0)
        <tr>
            <td colspan="5" class="text-center">No records found</td>
        </tr>
    @endif

    </tbody>

</table>

</div>

</div>
</div>
</section>
</div>

@include('layouts.footer')

@endsection