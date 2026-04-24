@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
/* ===== A4 PAGE FIX ===== */
@page {
    size: A4;
    margin: 12mm;
}

body {
    font-family: 'Source Sans Pro', sans-serif;
    font-size: 12px;
    color: #333;
    background: #f5f5f5;
}

/* ===== CONTAINER ===== */
.print-container {
    background: #ffffff;
    padding: 12px;
    border: 1px solid #8d5a2b;
}

/* ===== HEADERS ===== */
.title {
    text-align: center;
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: #3e2723;
}

.subtitle {
    text-align: center;
    font-size: 14px;
    font-weight: 600;
    color: #8d5a2b;
}

.date-line {
    text-align: center;
    margin: 4px 0 10px;
    color: #555;
}

/* ===== ACCENT BARS ===== */
.section-bar {
    height: 7px;
    background: #8d5a2b;
    margin: 6px 0;
}

.dark-bar {
    height: 5px;
    background: #3e2723;
    margin: 6px 0;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #999;
    padding: 4px 6px;
    vertical-align: middle;
}

th {
    background: #f2f2f2;
    color: #3e2723;
    font-weight: 600;
}

tbody tr:nth-child(even) {
    background: #fafafa;
}

/* ===== TOTAL ROW ===== */
.total-row {
    background: #c6e6ff;
    font-weight: 700;
    color: #000;
}

/* ===== HELPERS ===== */
.text-center { text-align: center; }
.text-right { text-align: right; }

/* ===== SIGNATURE ===== */
.signature {
    text-align: right;
    font-weight: 600;
    color: #3e2723;
}

.signature-line {
    border-top: 1px solid #3e2723;
    margin-top: 32px;
}

/* ===== PRINT ===== */
@media print {
    .no-print {
        display: none !important;
    }

    header,
    nav,
    aside {
        display: none !important;
    }

    body {
        background: #fff;
        margin: 0;
    }

    .print-container {
        border: none;
        padding: 0;
    }
}

</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

<div class="print-container">

    {{-- HEADER --}}
    <div class="title">{{ strtoupper($company_data->company_name) }}</div>
    <div class="subtitle">Quotation</div>
    <div class="date-line">
        Date : {{ date('d-m-Y', strtotime($spare_part->created_at)) }}
    </div>
<table style="margin-bottom:12px;">
    <tr>
        <td width="15%"><strong>Department:</strong></td>
        <td width="20%">{{ $spare_part->department ?? '-' }}</td>

        <td width="15%"><strong>Purpose:</strong></td>
        <td width="20%">{{ $spare_part->purpose ?? '-' }}</td>

        <td width="15%"><strong>Department Head:</strong></td>
        <td width="15%">{{ $spare_part->department_head ?? '-' }}</td>
    </tr>
</table>


    <div class="section-bar"></div>

    {{-- ITEMS --}}
    <table>
    <thead>
        <tr>
            <th width="5%" class="text-center">Sr No</th>
            <th width="30%">Item Name</th>
            <th width="25%">Description</th>
            <th width="10%" class="text-center">Qty</th>
            <th width="10%" class="text-center">Unit</th>
            <th width="20%" class="text-center">Required Date</th>
        </tr>
    </thead>
    <tbody>
        @php $totalQty = 0; $sr = 1; @endphp
        @foreach($spare_part->items as $item)
            @php $totalQty += $item->quantity; @endphp
            <tr>
                <td class="text-center">{{ $sr++ }}</td>

                <td>{{ $item->item->name ?? '-' }}</td>

                {{-- Description = Narration --}}
                <td>{{ $item->narration ?? '-' }}</td>

                <td class="text-center">{{ $item->quantity }}</td>

                <td class="text-center">{{ $item->unit }}</td>

                <td class="text-center">
                    {{ $item->required_date ? date('d-m-Y', strtotime($item->required_date)) : '-' }}
                </td>
            </tr>
        @endforeach

        <tr class="total-row">
            <td colspan="3" class="text-right">TOTAL</td>
            <td class="text-center">{{ $totalQty }}</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>


    {{-- SIGNATURES --}}
<table style="margin-top:40px;">
    <tr>

        <td width="25%" class="text-center">
            <strong>Prepared By</strong><br><br>
            {{ optional(\App\Models\User::find($spare_part->created_by))->name ?? '-' }}
        </td>

        <td width="25%" class="text-center">
            <strong>Requirement By</strong><br><br>
            {{ $spare_part->requirement_by ?? '-' }}
        </td>

        <td width="25%" class="text-center">
            <strong>HOD</strong><br><br>
            {{ $spare_part->hod ?? '-' }}
        </td>

        <td width="25%" class="text-center">
            <strong>Approved for Quotation</strong><br><br>
            {{ $spare_part->approved_for_quotation ?? '-' }}
        </td>

    </tr>
</table>

@if($spare_part->supplierOffers->count() > 0)

<div class="dark-bar"></div>
<h4 style="margin-top:15px;">Supplier Quotations</h4>

@php
    $groupedOffers = $spare_part->supplierOffers->groupBy('account_id');
    $quotationNumber = 1;
@endphp

@foreach($groupedOffers as $accountId => $offers)

    <h5 style="margin-top:15px;">
        {{ $quotationNumber++ }}. {{ $offers->first()->account->account_name ?? '-' }}
    </h5>

    <table style="margin-bottom:15px;">
        <thead>
            <tr>
                <th width="35%">Item</th>
                <th width="15%" class="text-center">Unit</th>
                <th width="25%" class="text-center">Qty Offered</th>
                <th width="25%" class="text-center">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($offers as $offer)
                <tr>
                    <td>{{ $offer->item->name ?? '-' }}</td>
                    <td class="text-center">
                        {{ $offer->item->unit->name ?? '-' }}
                    </td>
                    <td class="text-center">
                        {{ number_format($offer->offered_quantity, 2) }}
                    </td>
                    <td class="text-center">
                        {{ number_format($offer->offered_price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endforeach

@endif




</div>

<div class="no-print mt-3">
    <a href="{{ route('spare-part.index') }}" class="btn btn-secondary">Back</a>
    <button onclick="window.print()" class="btn btn-primary">Print</button>
    <button onclick="downloadImage()" class="btn btn-success">Download</button>
</div>

</div>
</div>
</section>
</div>
@include('layouts.footer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<script>
function downloadImage() {

    const element = document.querySelector('.print-container');

    html2canvas(element, {
        scale: 2, // better quality
        useCORS: true
    }).then(canvas => {

        const link = document.createElement('a');
        link.download = 'Quotation_{{ $spare_part->id }}.png';
        link.href = canvas.toDataURL('image/png');
        link.click();

    });
}
</script>

@endsection
