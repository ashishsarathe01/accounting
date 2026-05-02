@extends('layouts.app')
@section('content')

@include('layouts.header')
<style>
    .table td,
.table th {
    vertical-align: middle;
}

.date-col input {
    width: 130px !important;
    padding: 4px 6px !important;
}

.small-col input,
.small-col select {
    width: 90px !important;
    padding: 4px 6px !important;
}

.table td {
    padding: 6px !important;
}
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 mb-3">
        <h5 class="m-0">Duties Compliance</h5>
    </div>

    <div class="bg-white shadow-sm rounded p-3">

        <div class="table-responsive">
            <table class="table table-bordered text-center">

                <thead>
                    <tr>
                        <th rowspan="2">MONTH</th>

                        <th colspan="3">GSTR-1</th>
                        <th colspan="3">GSTR-3B</th>

                        <th colspan="2">TDS/TCS</th>
                        <th>ESIC</th>
                        <th>PF</th>
                    </tr>

                    <tr>
                        {{-- GST --}}
                        <th>STATUS</th>
                        <th>ARN Number</th>
                        <th>DATE OF FILING</th>

                        <th>STATUS</th>
                        <th>ARN Number</th>
                        <th>DATE OF FILING</th>

                        {{-- TDS --}}
                        <th>RETURN</th>
                        <th>CHALLAN</th>

                        {{-- ESIC --}}
                        <th>CHALLAN</th>

                        {{-- PF --}}
                        <th>CHALLAN</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($months as $month)
                @php
                    $monthKey = date('Y-m', strtotime($month['label'] ?? $month));
                @endphp

                <tr>
                    <td>{{ strtoupper($month['label'] ?? $month) }}</td>

                    {{-- GST --}}
                    @if($setting && $setting->gst)
                        {{-- GSTR-1 --}}
                        <td>
                            <select class="form-control">
                                <option value="">Select</option>
                                <option value="filed">Filed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control"></td>
                        <td class="date-col">
                            <input type="date" class="form-control">
                        </td>

                        {{-- GSTR-3B --}}
                        <td>
                            <select class="form-control">
                                <option value="">Select</option>
                                <option value="filed">Filed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </td>
                        <td><input type="text" class="form-control"></td>
                        <td class="date-col">
                            <input type="date" class="form-control">
                        </td>
                    @else
                        <td colspan="6">NA</td>
                    @endif

                    {{-- TDS --}}
                    <td class="small-col">
                        @if($setting && $setting->tds)
                            <input type="text" class="form-control">
                        @else
                            NA
                        @endif
                    </td>

                    <td class="small-col">
                        @if($setting && $setting->tds)
                            <input type="text" class="form-control">
                        @else
                            NA
                        @endif
                    </td>

                    {{-- ESIC--}}
                    <td class="small-col">
                        @if($setting && $setting->esic)
                            <input type="text" class="form-control">
                        @else
                            NA
                        @endif
                    </td>

                    {{-- PF --}}
                    <td class="small-col">
                        @if($setting && $setting->pf)
                            <input type="text" class="form-control">
                        @else
                            NA
                        @endif
                    </td>

                </tr>
                @endforeach

                </tbody>

            </table>
        </div>

    </div>

</div>
</div>
</section>
</div>

@endsection