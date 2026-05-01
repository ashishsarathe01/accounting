@extends('layouts.app')
@section('content')

@include('layouts.header')
<style>
@media print {

    @page {
        margin: 15mm;
    }

    body * {
        visibility: hidden;
    }

    #printArea, #printArea * {
        visibility: visible;
    }

    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    #printHeader {
        display: block !important;
    }

    tr[style*="display: none"] {
        display: none !important;
    }

}
</style>
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
                <button type="button" onclick="printReport()" class="btn btn-sm btn-success">
                    Print
                </button>
            </div>

        </div>

    </form>
</div>

{{-- REPORT TABLE --}}
<div id="printArea" class="bg-white table-view shadow-sm">
    <div id="printHeader" style="display:none; text-align:center; margin-bottom:10px;">
        <h3 style="margin:0;">Yield Report</h3>
        <p style="margin:0;">
            From: {{ $from_date }} To: {{ $to_date }}
        </p>
    </div>
<table class="table table-bordered table-striped m-0">

    <thead>
        <tr class="bg-light-pink text-body">
            <th style="width:30%">Particulars</th>
            <th style="width:20%; text-align:right;">Weight</th>
        </tr>
    </thead>

    <tbody>

        <tr onclick="toggleRow('productionRow')" style="cursor:pointer;">
            <td><strong>Total Production</strong></td>
            <td style="text-align:right;">
                {{ number_format($totalProduction ?? 0, 2) }}
            </td>
        </tr>
        <tr id="productionRow" style="display:none;">
            <td colspan="2">
                <table class="table table-bordered mb-0">
                    @foreach($productionDetails as $row)
                    <tr>
                        <td>{{ $row->item_name }}</td>
                        <td style="text-align:right;">
                            {{ number_format($row->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        <tr onclick="toggleRow('consumptionRow')" style="cursor:pointer;">
            <td><strong>Consumed Weight</strong></td>
            <td style="text-align:right;">
                {{ number_format($totalAdjustedConsumption ?? 0, 2) }}
            </td>
        </tr>
        <tr id="consumptionRow" style="display:none;">
            <td colspan="2">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Total</th>
                            <th>%</th>
                            <th>Adjusted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($consumptionDetails as $row)
                        @php
                            $adjusted = ($row->total * $row->percent) / 100;
                        @endphp
                        <tr>
                            <td>{{ $row->item_name }}</td>
                            <td>{{ number_format($row->total, 2) }}</td>
                            <td>{{ number_format($row->percent, 2) }}%</td>
                            <td>{{ number_format($adjusted, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        <tr onclick="toggleRow('yieldLossRow')" style="cursor:pointer;">
            <td><strong>Yield Loss</strong></td>
            <td style="text-align:right;">
                {{ number_format($yieldLoss ?? 0, 2) }}
            </td>
        </tr>
        <tr id="yieldLossRow" style="display:none;">
            <td colspan="2">
                <table class="table table-bordered mb-0">
                    <tr>
                        <td>Total Production</td>
                        <td style="text-align:right;">{{ number_format($totalProduction, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Consumed</td>
                        <td style="text-align:right;">{{ number_format($totalAdjustedConsumption, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr onclick="toggleRow('wasteRow')" style="cursor:pointer;">
            <td><strong>Total Waste</strong></td>
            <td style="text-align:right;">
                    {{ number_format($totalWaste ?? 0, 2) }}
            </td>
        </tr>
        <tr id="wasteRow" style="display:none;">
            <td colspan="2">
                <table class="table table-bordered mb-0">
                    @foreach($wasteDetails as $row)
                    <tr>
                        <td>{{ $row->item_name }}</td>
                        <td style="text-align:right;">
                            {{ number_format($row->total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        <tr class="bg-light" onclick="toggleRow('yieldPercentRow')" style="cursor:pointer;">
            <td><strong>Yield Loss %</strong></td>
            <td style="text-align:right;">
                {{ number_format($yieldPercent ?? 0, 2) }} %
            </td>
            </td>
        </tr>
        <tr id="yieldPercentRow" style="display:none;" onclick="event.stopPropagation();">
            <td colspan="2">
                <table class="table table-bordered mb-0">

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

function toggleRow(id) {
    let row = document.getElementById(id);

    if (row.style.display === "none") {
        row.style.display = "table-row";
    } else {
        row.style.display = "none";
    }
}
function printReport() {
    window.print();
}
</script>
@endsection