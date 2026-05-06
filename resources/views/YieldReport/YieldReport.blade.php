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
                <button type="button" onclick="downloadPDF()" class="btn btn-sm btn-danger">
                    PDF
                </button>
            </div>

        </div>

    </form>
</div>

{{-- REPORT TABLE --}}
<div id="printArea" class="bg-white table-view shadow-sm">
    <div id="printHeader" style="display:none; text-align:center; margin-bottom:20px;">
        <h3 style="margin:0;">Yield Report</h3>
        <p style="margin:0; font-size:14px;">
            From Date : {{ \Carbon\Carbon::parse($from_date)->format('d-m-Y') }}
            &nbsp;&nbsp;&nbsp;
            To Date : {{ \Carbon\Carbon::parse($to_date)->format('d-m-Y') }}
        </p>
    </div>
<table class="table table-bordered m-0">

    <thead>
        <tr style="background-color:#d9edf7; color:#000;">
            <th style="width:30%">Particulars</th>
            <th style="width:20%; text-align:right;">Weight</th>
        </tr>
    </thead>

    <tbody>

        <tr id="productionRow">
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

        <tr style="background-color:#d0d0d0;">
            <td><strong>Total Production</strong></td>
            <td style="text-align:right;">
                <strong>{{ number_format($totalProduction ?? 0, 2) }}</strong>
            </td>
        </tr>
        <tr id="consumptionRow">
            <td colspan="2">
                <table class="table table-bordered mb-0">
                    <tbody>
                        @foreach($consumptionDetails as $row)
                        @php
                            $adjusted = ($row->total * $row->percent) / 100;
                        @endphp
                        <tr>
                            <td>
                                <strong>Less (-) Produced</strong> {{ $row->item_name }}
                            </td>

                            <td class="text-end">
                                {{ number_format($row->total, 2) }}
                            </td>

                            <td class="text-end">
                                {{ number_format($row->percent, 2) }}%
                            </td>

                            <td class="text-end">
                                {{ number_format($adjusted, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>

        <tr style="background-color:#d0d0d0;">
            <td><strong>Balance Production</strong></td>
            <td style="text-align:right;">
                <strong>{{ number_format($yieldLoss ?? 0, 2) }}</strong>
            </td>
        </tr>
        <tr id="wasteRow">
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

        <tr style="background-color:#d0d0d0;">
            <td><strong>Total Waste</strong></td>
            <td style="text-align:right;">
                <strong>{{ number_format($totalWaste ?? 0, 2) }}</strong>
            </td>
        </tr>

        <tr style="background-color:#d0d0d0;">
            <td><strong>Yield Recovery %</strong></td>
            <td style="text-align:right;">
                <strong>{{ number_format($yieldPercent, 2) }} %</strong>
            </td>
        </tr>


        <tr style="background-color:#d0d0d0;">
            <td><strong>Overall Yield Recovery %</strong></td>
            <td style="text-align:right;">
                <strong>
                    {{ $totalWaste > 0 ? number_format(($totalProduction / $totalWaste) * 100, 2) : 0 }} %
                </strong>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
function downloadPDF() {

    let element = document.getElementById('printArea');

    // SHOW HEADER FOR PDF
    document.getElementById('printHeader').style.display = 'block';

    let opt = {
        margin: 0.5,
        filename: 'Yield_Report.pdf',
        image: { type: 'jpeg', quality: 1 },
        html2canvas: {
            scale: 2
        },
        jsPDF: {
            unit: 'in',
            format: 'a4',
            orientation: 'portrait'
        }
    };

    html2pdf()
        .set(opt)
        .from(element)
        .save()
        .then(() => {

            // HIDE HEADER AGAIN AFTER DOWNLOAD
            document.getElementById('printHeader').style.display = 'none';

        });
}
</script>
@endsection