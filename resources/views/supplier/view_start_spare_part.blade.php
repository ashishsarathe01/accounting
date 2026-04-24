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
    font-size: 12px; /* was 13px */
    color: #333;
}

/* ===== CONTAINER ===== */
.print-container {
    background: #fff;
    padding: 12px;          /* reduced */
    border: 1px solid #aaa; /* lighter border */
}

/* ===== HEADERS ===== */
h1,h2,h3,h4 {
    margin: 3px 0;
}

.title {
    text-align: center;
    font-size: 22px; /* reduced from 26 */
    font-weight: 700;
    letter-spacing: 0.8px;
}

.subtitle {
    text-align: center;
    font-size: 14px; /* reduced */
    font-weight: 600;
}

.date-line {
    text-align: center;
    margin: 4px 0 10px;
}

/* ===== TABLE ===== */
table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    border: 1px solid #999;
    padding: 4px 6px; /* tighter rows */
    vertical-align: middle;
}

th {
    background: #f2f2f2;
    font-weight: 600;
}

/* ===== SECTION BARS ===== */
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

/* ===== TOTAL ROW ===== */
.total-row {
    background: #c6e6ff;
    font-weight: 700;
}

/* ===== HELPERS ===== */
.text-center { text-align: center; }
.text-right { text-align: right; }

/* ===== BILL / SHIP ===== */
.bill-box td {
    height: 95px; /* reduced from 110 */
}

/* ===== TERMS ===== */
.terms p {
    margin: 3px 0;
}

/* ===== SIGNATURE ===== */
.signature {
    text-align: right;
    font-weight: 600;
}

.signature-line {
    border-top: 1px solid #000;
    margin-top: 32px; /* slightly tighter */
}

/* ===== FOOTER ===== */
.footer td {
    font-weight: 600;
}

/* ===== PRINT ===== */
@media print {
    .no-print { display: none !important; }
    body { background: #fff; }
}
@media print {

    /* Hide only app chrome (NOT parents of print-container) */
    header,
    nav,
    aside,
    .no-print {
        display: none !important;
    }

    /* Ensure printable content is visible */
    .print-container {
        display: block !important;
        position: relative;
        width: 100%;
        margin: 0;
        padding: 0;
        border: none;
    }

    body {
        margin: 0;
        background: #fff;
    }

    @page {
        size: A4;
        margin: 12mm;
    }

    table {
        page-break-inside: avoid;
    }

    tr {
        page-break-inside: avoid;
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
                    <div class="subtitle">Purchase Order</div>
                    <div class="date-line">
                        Date : {{ date('d-m-Y', strtotime($spare->created_at)) }}
                    </div>

                    <div class="section-bar"></div>

                    {{-- ================= SUPPLIER + PO DETAILS ================= --}}
                    <table class="bill-box" style="margin-top:10px;">
                        <tr>

                            {{-- SUPPLIER --}}
                            <td width="50%">
                                <strong>Supplier :</strong><br><br>
                                {{ $spare->account->account_name }}<br>
                                {{ $spare->account->address }}<br>
                                {{ $spare->account->stateMaster->name ?? '' }}
                                {{ $spare->account->pin_code }}<br>
                                <strong>GSTIN :</strong> {{ $spare->account->gstin }}<br>
                                <strong>Contact :</strong> {{ $spare->account->mobile ?? '-' }}
                            </td>

                            {{-- PO META --}}
                            <td width="50%" valign="top">
                                <strong>PO Number :</strong> {{ $spare->po_number ?? '-' }}<br>
                                <strong>PO Date :</strong>
                                {{ $spare->po_date ? \Carbon\Carbon::parse($spare->po_date)->format('d-m-Y') : '-' }}
                                <br>
                                <strong>Freight :</strong>
                                {{ $spare->freight == '1' ? 'Yes' : 'No' }}
                                <br>

                                @if(!empty($spare->po_narration))
                                    <strong>Narration :</strong><br>
                                    {{ $spare->po_narration }}
                                @endif
                            </td>

                        </tr>
                    </table>

                    <div class="dark-bar" style="margin:12px 0;"></div>

                    {{-- ================= BILL TO / SHIP TO ================= --}}
                    <table class="bill-box">
                        <tr>

                            {{-- BILL TO --}}
                            <td width="50%">
                                <strong>Bill To :</strong><br><br>

                                @if($spare->bill_to_company_id)
                                    {{-- COMPANY --}}
                                    {{ $company_data->company_name }}<br>
                                    {{ $company_data->address ?? '' }}<br>
                                    {{ $company_data->stateMaster->name ?? '' }}
                                    {{ $company_data->pin_code ?? '' }}<br><br>
                                    <strong>GSTIN :</strong> {{ $company_data->gst ?? '-' }}<br>
                                    <strong>Contact :</strong> {{ $company_data->mobile_no ?? '-' }}

                                @elseif($spare->bill_to_account_id && $spare->billTo)
                                    {{-- ACCOUNT --}}
                                    {{ $spare->billTo->account_name }}<br>
                                    {{ $spare->billTo->address ?? '' }}<br>
                                    {{ $spare->billTo->stateMaster->name ?? '' }}
                                    {{ $spare->billTo->pin_code ?? '' }}<br><br>
                                    <strong>GSTIN :</strong> {{ $spare->billTo->gstin ?? '-' }}<br>
                                    <strong>Contact :</strong> {{ $spare->billTo->mobile ?? '-' }}

                                @else
                                    -
                                @endif
                            </td>

                            {{-- SHIP TO --}}
                            <td width="50%">
                                <strong>Ship To :</strong><br><br>

                                @if($spare->ship_to_company_id)
                                    {{-- COMPANY --}}
                                    {{ $company_data->company_name }}<br>
                                    {{ $company_data->address ?? '' }}<br>
                                    {{ $company_data->stateMaster->name ?? '' }}
                                    {{ $company_data->pin_code ?? '' }}<br><br>
                                    <strong>GSTIN :</strong> {{ $company_data->gst ?? '-' }}<br>
                                    <strong>Contact :</strong> {{ $company_data->mobile_no ?? '-' }}

                                @elseif($spare->ship_to_account_id && $spare->shipTo)
                                    {{-- ACCOUNT --}}
                                    {{ $spare->shipTo->account_name }}<br>
                                    {{ $spare->shipTo->address ?? '' }}<br>
                                    {{ $spare->shipTo->stateMaster->name ?? '' }}
                                    {{ $spare->shipTo->pin_code ?? '' }}<br><br>
                                    <strong>GSTIN :</strong> {{ $spare->shipTo->gstin ?? '-' }}<br>
                                    <strong>Contact :</strong> {{ $spare->shipTo->mobile ?? '-' }}

                                @else
                                    -
                                @endif
                            </td>

                        </tr>
                    </table>

                    <div class="dark-bar" style="margin:12px 0;"></div>

                    {{-- ITEMS --}}
                    <table>
                        <thead>
                            <tr>
                                <th width="30%">Item / Quality</th>
                                <th width="15%" class="text-center">Unit</th>
                                <th width="15%" class="text-center">Quantity</th>
                                <th width="15%" class="text-center">Price</th>
                                <th width="25%">Narration</th>
                            </tr>
                        </thead>
                        <tbody>

                            @php $totalQty = 0; @endphp

                            @foreach($spare->items as $item)
                                @php $totalQty += $item->quantity; @endphp
                                <tr>
                                    <td>{{ $item->item->name }}</td>
                                    <td class="text-center">{{ $item->unit }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ number_format($item->price ?? 0, 2) }}</td>
                                    <td>{{ $item->narration ?? '-' }}</td>
                                </tr>
                            @endforeach

                            <tr class="total-row">
                                <td class="text-right">TOTAL</td>
                                <td></td>
                                <td class="text-center">{{ $totalQty }}</td>
                                <td></td>
                                <td></td>
                            </tr>

                        </tbody>
                    </table>

                    <div class="section-bar" style="margin:12px 0;"></div>

                    {{-- TERMS --}}
                    <table class="terms">
                        <tr>
                            <td width="60%">
                                <strong>Terms & Conditions</strong><br><br>
                                @if($terms->count())
                                    @foreach($terms as $index => $term)
                                        <p>{{ $index + 1 }}. {{ $term->term_text }}</p>
                                    @endforeach
                                @else
                                    <p>-</p>
                                @endif
                            </td>

                            <td width="40%">
                                <div class="signature">Checked By</div>
                                <div class="signature-line"></div>
                                <br>
                                <div class="signature">Authorised By</div>
                                <div class="signature-line"></div>
                            </td>
                        </tr>
                    </table>
                    
                    <table class="footer" style="margin-top:15px;">
                        <tr>
                            <td width="33%">Prepared By : {{ $spare->user->name ?? '-' }}</td>
                            <td width="33%">Requirement By : {{ $spare->requirement_by ?? '-' }}</td>
                            <td width="33%">Approved By : {{ $spare->approvedBy->name ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="no-print mt-3">
                    <a href="{{ route('spare-part.index') }}" class="btn btn-secondary">Back</a>
                    <button onclick="window.print()" class="btn btn-primary">Print</button>
                </div>

            </div>
        </div>
    </section>
</div>

@endsection
