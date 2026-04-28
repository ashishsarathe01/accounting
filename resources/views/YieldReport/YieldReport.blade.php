@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- ALERT --}}
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- TITLE + FILTER --}}
<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3">

    <form method="GET" action="{{ route('yield-report.report') }}">

        <div class="d-flex align-items-center justify-content-between flex-nowrap">

            <h5 class="transaction-table-title m-0 me-3 text-nowrap">
                Yield Report
            </h5>

            <div class="d-flex align-items-center flex-nowrap" style="gap:8px;">

                <input type="date"
                       name="from_date"
                       value="{{ $from_date }}"
                       class="form-control form-control-sm"
                       style="width:140px;">

                <input type="date"
                       name="to_date"
                       value="{{ $to_date }}"
                       class="form-control form-control-sm"
                       style="width:140px;">

                <button type="submit" class="btn btn-sm btn-primary">
                    Filter
                </button>
                <a href="{{ route('yield-report.index') }}">
                    <button type="button" class="btn btn-sm btn-primary">
                        Setting
                    </button>
                </a>
            </div>

        </div>

    </form>
</div>

{{-- REPORT TABLE --}}
<div class="bg-white table-view shadow-sm" style="overflow-x:auto;">

<table class="table table-bordered table-striped m-0">

    <thead>
        <tr class="bg-light-pink text-body">
            <th style="width:30%">Particulars</th>
            <th style="width:20%; text-align:right;">Weight</th>
        </tr>
    </thead>

    <tbody>

        <tr>
            <td><strong>Total Production</strong></td>
            <td style="text-align:right;">
                <a href="javascript:void(0)" onclick="openProductionModal()">
                    {{ number_format($totalProduction ?? 0, 2) }}
                </a>
            </td>
        </tr>

        <tr>
            <td><strong>Consumed Weight</strong></td>
            <td style="text-align:right;">
                <a href="javascript:void(0)" onclick="openConsumptionModal()">
                    {{ number_format($totalAdjustedConsumption ?? 0, 2) }}
                </a>
            </td>
        </tr>

        <tr>
            <td><strong>Yield Loss</strong></td>
            <td style="text-align:right;">
                <a href="javascript:void(0)" onclick="openYieldLossModal()">
                    {{ number_format($yieldLoss ?? 0, 2) }}
                </a>
            </td>
        </tr>

        <tr>
            <td><strong>Total Waste</strong></td>
            <td style="text-align:right;">
                <a href="javascript:void(0)" onclick="openWasteModal()">
                    {{ number_format($totalWaste ?? 0, 2) }}
                </a>
            </td>
        </tr>

        <tr class="bg-light">
            <td><strong>Yield Loss %</strong></td>
            <td style="text-align:right;">
                <a href="javascript:void(0)" onclick="openYieldPercentModal()">
                    {{ number_format($yieldPercent ?? 0, 2) }} %
                </a>
            </td>
        </tr>

    </tbody>

</table>

</div>

</div>
</div>
</section>
</div>
<div class="modal fade" id="productionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Production Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align:right;">Weight</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($productionDetails as $row)
                        <tr>
                            <td>{{ $row->item_name }}</td>
                            <td style="text-align:right;">
                                {{ number_format($row->total, 2) }}
                            </td>
                        </tr>
                        @endforeach

                        <tr class="bg-light">
                            <td><strong>Total</strong></td>
                            <td style="text-align:right;">
                                <strong>{{ number_format($totalProduction, 2) }}</strong>
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="consumptionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Consumption Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align:right;">Total Weight</th>
                            <th style="text-align:right;">%</th>
                            <th style="text-align:right;">Adjusted</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($consumptionDetails as $row)
                        @php
                            $adjusted = ($row->total * $row->percent) / 100;
                        @endphp
                        <tr>
                            <td>{{ $row->item_name }}</td>
                            <td style="text-align:right;">
                                {{ number_format($row->total, 2) }}
                            </td>
                            <td style="text-align:right;">
                                {{ number_format($row->percent, 2) }}%
                            </td>
                            <td style="text-align:right;">
                                {{ number_format($adjusted, 2) }}
                            </td>
                        </tr>
                        @endforeach

                        <tr class="bg-light">
                            <td><strong>Total</strong></td>
                            <td></td>
                            <td></td>
                            <td style="text-align:right;">
                                <strong>{{ number_format($totalAdjustedConsumption, 2) }}</strong>
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="yieldLossModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Yield Loss Calculation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered">

                    <tr>
                        <td>Total Production</td>
                        <td style="text-align:right;">
                            {{ number_format($totalProduction, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td>Consumed Weight</td>
                        <td style="text-align:right;">
                            {{ number_format($totalAdjustedConsumption, 2) }}
                        </td>
                    </tr>

                    <tr class="bg-light">
                        <td><strong>Yield Loss</strong></td>
                        <td style="text-align:right;">
                            <strong>{{ number_format($yieldLoss, 2) }}</strong>
                        </td>
                    </tr>

                </table>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="wasteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Waste Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th style="text-align:right;">Waste Weight</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($wasteDetails as $row)
                        <tr>
                            <td>{{ $row->item_name }}</td>
                            <td style="text-align:right;">
                                {{ number_format($row->total, 2) }}
                            </td>
                        </tr>
                        @endforeach

                        <tr class="bg-light">
                            <td><strong>Total</strong></td>
                            <td style="text-align:right;">
                                <strong>{{ number_format($totalWaste, 2) }}</strong>
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="yieldPercentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Yield Loss % Calculation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <table class="table table-bordered">

                    <tr>
                        <td>Yield Loss</td>
                        <td style="text-align:right;">
                            {{ number_format($yieldLoss, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td>Total Waste</td>
                        <td style="text-align:right;">
                            {{ number_format($totalWaste, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td>Formula</td>
                        <td style="text-align:right;">
                            (Yield Loss ÷ Waste) × 100
                        </td>
                    </tr>

                    <tr class="bg-light">
                        <td><strong>Yield Loss %</strong></td>
                        <td style="text-align:right;">
                            <strong>{{ number_format($yieldPercent, 2) }} %</strong>
                        </td>
                    </tr>

                </table>

            </div>

        </div>
    </div>
</div>
@include('layouts.footer')
<script>
function openProductionModal() {
    $('#productionModal').modal('show');
}
function openConsumptionModal() {
    $('#consumptionModal').modal('show');
}
function openYieldLossModal() {
    $('#yieldLossModal').modal('show');
}
function openWasteModal() {
    $('#wasteModal').modal('show');
}
function openYieldPercentModal() {
    $('#yieldPercentModal').modal('show');
}
</script>
@endsection