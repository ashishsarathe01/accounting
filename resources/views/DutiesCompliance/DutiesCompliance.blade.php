@extends('layouts.app')

@section('content')

@include('layouts.header')

<style>
    body {
        background: #f4f7fb;
    }

    .compliance-wrapper {
        background: #f4f7fb;
        min-height: 100vh;
    }

    .compliance-card {
        background: #fff;
        border-radius: 18px;
        border: 1px solid #e9edf5;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .page-header {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: 18px;
        padding: 22px 28px;
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(79, 70, 229, 0.20);
    }

    .page-header h4 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.4px;
    }

    .page-subtitle {
        opacity: 0.9;
        font-size: 14px;
        margin-top: 4px;
    }

    .filter-card {
        background: #ffffff;
        border: 1px solid #edf1f7;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .filter-label {
        font-size: 13px;
        font-weight: 700;
        color: #344054;
        margin-bottom: 8px;
        display: block;
    }

    .modern-select {
        height: 46px;
        border-radius: 12px;
        border: 1px solid #d0d5dd;
        box-shadow: none !important;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .modern-select:focus {
        border-color: #7c3aed;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.10) !important;
    }

    .filter-btn {
        height: 46px;
        border-radius: 12px;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border: none;
        font-weight: 600;
        padding: 0 24px;
        width: 100%;
        transition: 0.3s ease;
    }

    .filter-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(79, 70, 229, 0.20);
    }

    .table-wrapper {
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid #edf1f7;
    }

    .modern-table {
        margin-bottom: 0;
    }

    .modern-table thead tr:first-child {
        background: #f3f4f6;
    }

    .modern-table thead tr:first-child th {
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        font-weight: 700;
        color: #111827;
        padding: 16px 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .modern-table thead tr:nth-child(2) {
        background: #fafafa;
    }

    .modern-table thead tr:nth-child(2) th {
        font-size: 12px;
        font-weight: 700;
        color: #667085;
        padding: 14px 12px;
        border-bottom: 1px solid #e5e7eb;
        text-transform: uppercase;
    }

    .modern-table td {
        padding: 16px 12px !important;
        vertical-align: middle;
        font-size: 14px;
        border-color: #eef2f7;
    }

    .modern-table tbody tr {
        transition: 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background: #f9fafb;
    }

    .month-cell {
        font-weight: 700;
        color: #111827;
        letter-spacing: 0.4px;
        min-width: 150px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        min-width: 90px;
    }

    .status-filed {
        background: #ecfdf3;
        color: #027a48;
    }

    .status-pending {
        background: #fff7ed;
        color: #c2410c;
    }

    .status-default {
        background: #f2f4f7;
        color: #667085;
    }

    .arn-text {
        font-family: monospace;
        font-size: 13px;
        color: #344054;
        font-weight: 600;
    }

    .date-text {
        color: #475467;
        font-weight: 500;
    }

    .alert-success {
        border-radius: 12px;
        border: none;
        background: #ecfdf3;
        color: #027a48;
        font-weight: 600;
    }

    @media(max-width: 768px) {

        .page-header {
            padding: 18px;
        }

        .filter-card {
            padding: 18px;
        }

        .filter-btn {
            margin-top: 10px;
        }
    }
    /* =========================
   FILTER SECTION FIX
========================= */

.filter-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 26px;
    margin-bottom: 24px;
}

.filter-label {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    display: block;
    letter-spacing: 0.2px;
}

/* PROFESSIONAL INPUTS */
.modern-select {
    width: 100% !important;
    height: 48px !important;
    border-radius: 10px !important;
    border: 1px solid #d1d5db !important;
    background: #fff !important;

    font-size: 14px !important;
    font-weight: 500 !important;
    color: #111827 !important;

    padding: 0 16px !important;

    box-shadow: none !important;
    transition: all 0.2s ease;
}

/* REMOVE CHILDISH LOOK */
.modern-select,
.filter-btn,
.modern-table,
.page-header,
body {
    font-family: Inter, "Segoe UI", sans-serif !important;
}

.modern-select:focus {
    border-color: #5b5bd6 !important;
    box-shadow: 0 0 0 3px rgba(91, 91, 214, 0.10) !important;
}

/* FIX DROPDOWN TEXT VISIBILITY */
.modern-select option {
    color: #111827;
    font-size: 14px;
    font-weight: 500;
}

/* BUTTON */
.filter-btn {
    height: 48px;
    border-radius: 10px;
    background: #5b5bd6;
    border: none;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.2px;
    padding: 0 24px;
    transition: 0.2s ease;
}

.filter-btn:hover {
    background: #4c4cc7;
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(91, 91, 214, 0.18);
}

/* TABLE FONT IMPROVEMENT */
.modern-table thead th {
    font-size: 12px !important;
    font-weight: 700 !important;
    color: #6b7280 !important;
    letter-spacing: 0.5px;
}

.modern-table td {
    font-size: 14px !important;
    font-weight: 500;
    color: #111827;
}

/* MONTH FONT */
.month-cell {
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.3px;
    color: #111827;
}

/* ARN */
.arn-text {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
    font-family: "SFMono-Regular", Consolas, monospace;
}

/* HEADER IMPROVEMENT */
.page-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    border-radius: 16px;
    padding: 22px 28px;
}

.page-header h4 {
    font-size: 30px;
    font-weight: 700;
    letter-spacing: -0.3px;
}

.page-subtitle {
    font-size: 14px;
    font-weight: 400;
    opacity: 0.92;
}

/* RESPONSIVE FIX */
@media(max-width: 768px) {

    .modern-select {
        margin-bottom: 14px;
    }

    .filter-btn {
        width: 100%;
    }
}
</style>

<div class="list-of-view-company compliance-wrapper">
    <section class="list-of-view-company-section container-fluid">

        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 py-4">

                {{-- Success Message --}}
                @if (session('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Page Header --}}
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h4>Duties Compliance</h4>
                            <div class="page-subtitle">
                                Monitor GSTR-1 and GSTR-3B filing compliance status
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Card --}}
                <div class="compliance-card p-4">

                    {{-- Filters --}}
                    <div class="filter-card">

                        <form method="GET">

                            <div class="row align-items-end">

                                <div class="col-lg-5 col-md-6 mb-3">
                                    <label class="filter-label">
                                        Select GST Number
                                    </label>

                                    <select name="gst_no" class="form-control modern-select">

                                        @foreach($gstList as $gst)
                                            <option value="{{ $gst }}"
                                                {{ $selectedGst == $gst ? 'selected' : '' }}>
                                                {{ $gst }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                               <div class="col-lg-3 col-md-4 mb-3">
                                    <label class="filter-label">
                                        Financial Year
                                    </label>

                                    <select name="financial_year" class="form-control modern-select">

                                        @foreach($fyOptions as $fy)
                                            <option value="{{ $fy }}"
                                                {{ $financial_year == $fy ? 'selected' : '' }}>
                                                {{ $fy }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-4 mb-3">
                                    <button class="btn btn-primary filter-btn">
                                        Apply Filter
                                    </button>
                                </div>

                            </div>

                        </form>

                    </div>

                    {{-- Table --}}
                    <div class="table-wrapper">

                        <div class="table-responsive">

                            <table class="table modern-table text-center align-middle">

                                <thead>

                                    <tr>
                                        <th rowspan="2">Month</th>

                                        <th colspan="3">GSTR-1</th>

                                        <th colspan="3">GSTR-3B</th>
                                    </tr>

                                    <tr>

                                        <th>Status</th>
                                        <th>ARN Number</th>
                                        <th>Date of Filing</th>

                                        <th>Status</th>
                                        <th>ARN Number</th>
                                        <th>Date of Filing</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach($filteredMonths as $month)

                                        @php

                                            $monthLabel = date(
                                                'F Y',
                                                strtotime($month . '-01')
                                            );

                                            $gstr1 = $apiData[$month]['GSTR1'] ?? null;

                                            $gstr3b = $apiData[$month]['GSTR3B'] ?? null;

                                            $gstr1Status = strtolower($gstr1['status'] ?? '');

                                            $gstr3bStatus = strtolower($gstr3b['status'] ?? '');

                                        @endphp

                                        <tr>

                                            <td class="month-cell">
                                                {{ strtoupper($monthLabel) }}
                                            </td>

                                            {{-- GSTR1 --}}
                                            <td>
                                                <span class="status-badge
                                                    {{ str_contains($gstr1Status, 'filed') ? 'status-filed' : (str_contains($gstr1Status, 'pending') ? 'status-pending' : 'status-default') }}">
                                                    {{ $gstr1['status'] ?? '-' }}
                                                </span>
                                            </td>

                                            <td class="arn-text">
                                                {{ $gstr1['arn'] ?? '-' }}
                                            </td>

                                            <td class="date-text">
                                                {{ $gstr1['date'] ?? '-' }}
                                            </td>

                                            {{-- GSTR3B --}}
                                            <td>
                                                <span class="status-badge
                                                    {{ str_contains($gstr3bStatus, 'filed') ? 'status-filed' : (str_contains($gstr3bStatus, 'pending') ? 'status-pending' : 'status-default') }}">
                                                    {{ $gstr3b['status'] ?? '-' }}
                                                </span>
                                            </td>

                                            <td class="arn-text">
                                                {{ $gstr3b['arn'] ?? '-' }}
                                            </td>

                                            <td class="date-text">
                                                {{ $gstr3b['date'] ?? '-' }}
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>
</div>

@endsection