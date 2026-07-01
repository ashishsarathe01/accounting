@extends('layouts.app')

@section('content')

@include('layouts.header')
<style>

@media print {

    body * {
        visibility: hidden;
    }

    #printArea,
    #printArea * {
        visibility: visible;
    }

    #printArea {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        overflow: visible !important;
        box-shadow: none !important;
    }

    #printHeader {
        display: block !important;
        text-align: center;
        margin-bottom: 20px;
    }

    .table-title-bottom-line,
    form,
    nav,
    header,
    footer,
    .btn,
    .alert {
        display: none !important;
    }

    table {
        width: 100% !important;
        border-collapse: collapse;
    }

    table th,
    table td {
        border: 1px solid #000 !important;
    }
}

</style>
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

<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3 d-flex justify-content-between align-items-center">

    <h5 class="transaction-table-title m-0">
        Consumption Report
    </h5>
    <a href="{{route('consumption-report-settings')}}"><button class="btn btn-info">Setting</button></a>
</div>

<form method="GET" action="{{ route('consumption-report') }}">

    <div class="card mt-3">

        <div class="card-body">

            <div class="row">

                <div class="col-md-3">
                    <label>From Date</label>

                    <input
                        type="date"
                        name="from_date"
                        class="form-control"
                        value="{{ $from_date }}"
                    >
                </div>

                <div class="col-md-3">
                    <label>To Date</label>

                    <input
                        type="date"
                        name="to_date"
                        class="form-control"
                        value="{{ $to_date }}"
                    >
                </div>

                <div class="col-md-4 align-self-end">

                    <button
                        type="submit"
                        class="btn btn-primary">
                        Search
                    </button>

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="window.print();">
                        Print
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger"
                        onclick="downloadPDF();">
                        PDF
                    </button>

                </div>

            </div>

        </div>

    </div>

</form>

<div id="printArea" class="bg-white table-view shadow-sm mt-3" style="overflow-x:auto;">

    <div id="printHeader" style="display:none; text-align:center; margin-bottom:20px;">

        <h3 style="margin-bottom:10px;">
            Consumption Report
        </h3>

        <strong>From :</strong>
        {{ date('d-m-Y', strtotime($from_date)) }}

        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

        <strong>To :</strong>
        {{ date('d-m-Y', strtotime($to_date)) }}

    </div>
<table class="table table-bordered table-striped mb-3">

    <thead>
        <tr>
            <th width="5%">S.No.</th>
            <th>Total Production</th>
            <th style="text-align:right;">Weight</th>
        </tr>
    </thead>

    <tbody>

        {{-- Summary Row --}}
        <tr
            style="font-weight:bold; cursor:pointer; background:#f8f9fa;"
            onclick="toggleProductionDetails()"
        >
            <td colspan="2">
                <span id="productionIcon">▶</span>
                Total Production
            </td>

            <td style="text-align:right;">
                {{ number_format($totalProduction, 2) }}
            </td>
        </tr>

    </tbody>

    <tbody id="productionDetailsBody" style="display:none;">

        @foreach($productionDetails as $key => $row)

            <tr>

                <td>
                    {{ $key + 1 }}
                </td>

                <td>
                    {{ $row->item_name }}
                </td>

                <td style="text-align:right;">
                    {{ number_format($row->total, 2) }}
                </td>

            </tr>

        @endforeach

    </tbody>

</table>
    <table class="table table-bordered table-striped m-0">

        <thead>

            <tr>

                <th width="5%">
                    S.No.
                </th>

                <th>
                    Consumed Item
                </th>

                <th style="text-align:right;">
                    Qty
                </th>

                <th style="text-align:right;">
                    Amount
                </th>

                <th style="text-align:right;">
                    Avg Price
                </th>

                <th style="text-align:right;">
                    Generated Qty
                </th>

                <th style="text-align:right;">
                    Cost Per KG
                </th>
                <th style="text-align:right;">
                    Overall Cost
                </th>
                <th style="text-align:right;">
                    Consumed Per Ton
                </th>

            </tr>

        </thead>

        <tbody>

            @php

                $totalQty = 0;
                $totalAmount = 0;
                $totalGeneratedQty = 0;
                $totalPerKg = 0;
                $totalConsumedPerTon = 0;
                $totalOverAllCost = 0;
            @endphp

            @php $sr = 1; @endphp

            @forelse($groupedReport as $group)

                @php

                    $groupAvgPrice = $group['qty'] > 0
                        ? ($group['amount'] / $group['qty'])
                        : 0;

                    $groupPerKg = 0; $groupOverAllCost = 0;
                    $groupConsumedPerTon = 0;

                    foreach($group['items'] as $tmpItem){

                        $groupPerKg += $tmpItem['per_kg'];
                        if($tmpItem['amount']!=""){
                            $groupOverAllCost +=$tmpItem['amount']/$totalProduction;
                        }
                        
                        $groupConsumedPerTon += (
                            $tmpItem['generated_qty'] > 0
                                ? (($tmpItem['qty'] / $tmpItem['generated_qty']) * 1000)
                                : 0
                        );
                    }

                    $totalQty += $group['qty'];
                    $totalAmount += $group['amount'];
                    $totalGeneratedQty += $group['generated_qty'];
                    $totalPerKg += $groupPerKg;
                    $totalConsumedPerTon += $groupConsumedPerTon;
                    $totalOverAllCost += $groupOverAllCost;
                @endphp

                {{-- GROUP ROW --}}
                <tr
                    class="consumption-group-row bg-light fw-bold"
                    data-group-id="{{ $group['group_id'] }}"
                    data-expanded="0"
                    style="cursor:pointer;"
                >

                    <td>{{ $sr++ }}</td>

                    <td>
                        ▶ {{ $group['group_name'] }}
                    </td>

                    <td style="text-align:right;">
                        {{ number_format($group['qty'], 2) }}
                    </td>

                    <td style="text-align:right;">
                        {{ number_format($group['amount'], 2) }}
                    </td>

                    <td style="text-align:right;">
                        {{ number_format($groupAvgPrice, 2) }}
                    </td>

                    <td style="text-align:right;"></td>

                    <td style="text-align:right;">
                        {{ number_format($groupPerKg, 2) }}
                    </td>
                    <td style="text-align:right;">
                        {{ number_format($groupOverAllCost, 2) }}
                    </td>
                    <td style="text-align:right;">
                        {{ number_format($groupConsumedPerTon, 2) }}
                    </td>

                </tr>

                {{-- ITEM ROWS --}}
                @foreach($group['items'] as $item)

                    @php
                        $consumedPerTon = $item['generated_qty'] > 0
                            ? (($item['qty'] / $item['generated_qty']) * 1000)
                            : 0;
                    @endphp

                    <tr
                        class="consumption-item-row d-none"
                        data-group-id="{{ $group['group_id'] }}"
                    >

                        <td></td>

                        <td class="ps-4 text-muted">
                            {{ $item['item_name'] }}
                        </td>

                        <td style="text-align:right;">
                            {{ number_format($item['qty'], 2) }}
                        </td>

                        <td style="text-align:right;">
                            {{ number_format($item['amount'], 2) }}
                        </td>

                        <td style="text-align:right;">
                            {{ number_format($item['avg_price'], 2) }}
                        </td>

                        <td style="text-align:right;">
                            {{ number_format($item['generated_qty'], 2) }}
                        </td>

                        <td style="text-align:right;">
                            {{ number_format($item['per_kg'], 2) }}
                        </td>
                        <td style="text-align:right;">
                            @if($item['amount']!="")
                                {{ number_format($item['amount']/$totalProduction, 2) }}
                            @endif
                        </td>
                        <td style="text-align:right;">
                            {{ number_format($consumedPerTon, 2) }}
                        </td>

                    </tr>

                @endforeach

            @empty

                <tr>
                    <td colspan="8" class="text-center">
                        No Records Found
                    </td>
                </tr>

            @endforelse

        </tbody>

        @if(count($groupedReport) > 0)

        <tfoot>

            <tr style="font-weight:bold;">

                <td colspan="2">
                    Total
                </td>

                <td style="text-align:right;">
                    {{ number_format($totalQty, 2) }}
                </td>

                <td style="text-align:right;">
                    {{ number_format($totalAmount, 2) }}
                </td>

                <td></td>

                <td style="text-align:right;">
                    {{ number_format($totalGeneratedQty, 2) }}
                </td>

                <td style="text-align:right;">
                    {{ number_format($totalPerKg, 2) }}
                </td>
                <td style="text-align:right;">
                    {{ number_format($totalOverAllCost, 2) }}
                </td>
                <td style="text-align:right;">
                    {{ number_format($totalConsumedPerTon, 2) }}
                </td>

            </tr>

        </tfoot>

        @endif

    </table>

</div>

</div>

</div>

</section>

</div>
@include('layouts.footer')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>

function downloadPDF() {

    let element = document.getElementById('printArea');

    let header = document.getElementById('printHeader');

        header.style.display = 'block';

    let opt = {
        margin: 0.5,
        filename: 'Consumption_Report.pdf',
        image: {
            type: 'jpeg',
            quality: 1
        },
        html2canvas: {
            scale: 2
        },
        jsPDF: {
            unit: 'in',
            format: 'a4',
            orientation: 'landscape'
        }
    };

    html2pdf()
        .set(opt)
        .from(element)
        .save()
        .then(() => {

            header.style.display = 'none';

        });
}
function toggleProductionDetails()
{
    let details = document.getElementById('productionDetailsBody');
    let icon = document.getElementById('productionIcon');

    if(details.style.display === 'none')
    {
        details.style.display = '';
        icon.innerHTML = '▼';
    }
    else
    {
        details.style.display = 'none';
        icon.innerHTML = '▶';
    }
}
$(document).on('click', '.consumption-group-row', function () {

    let $groupRow = $(this);

    let gid = $groupRow.data('group-id');

    let expanded = $groupRow.data('expanded') || 0;

    let $items = $('.consumption-item-row[data-group-id="' + gid + '"]');

    if (expanded == 0) {

        $items.removeClass('d-none');

        $groupRow.data('expanded', 1);

    } else {

        $items.addClass('d-none');

        $groupRow.data('expanded', 0);
    }
});
</script>
@endsection