<!DOCTYPE html>
<html lang="en">
@php
    $isPdf = $isPdf ?? false;
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report Preview - {{ date('F Y', strtotime($month)) }}</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            background: #f0f0f0;
            color: #000;
        }

        /* ===== TOP BAR ===== */

        .top-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #1a1a2e;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .top-bar .title {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .top-bar .subtitle {
            font-size: 11px;
            color: #aaa;
            margin-top: 2px;
        }

        .top-bar .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-excel {
            background: #1d6f42;
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-excel:hover {
            background: #155233;
        }

        .btn-pdf {
            background: #c0392b;
            color: #fff;
            border: none;
            padding: 9px 20px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-pdf:hover {
            background: #922b21;
        }

        /* ===== EDIT NOTICE ===== */

        .edit-notice {
            margin: 70px 24px 10px 24px;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 8px 16px;
            font-size: 12px;
            color: #856404;
        }

        /* ===== TABS ===== */

        .tabs-bar {
            position: sticky;
            top: 52px;
            z-index: 900;
            background: #fff;
            border-bottom: 2px solid #ddd;
            padding: 0 24px;
            display: flex;
            gap: 0;
            margin: 0 0 0 0;
        }

        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: none;
            font-size: 13px;
            font-weight: bold;
            color: #555;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            transition: all 0.15s;
        }

        .tab-btn.active {
            color: #1a1a2e;
            border-bottom: 3px solid #1a1a2e;
        }

        .tab-btn:hover {
            color: #1a1a2e;
        }

        /* ===== SHEET PANELS ===== */

        @if($isPdf)

        .sheet-panel {
            display:block !important;
            padding:0 !important;
            page-break-after:always;
        }

        .sheet-panel.active {
            display:block !important;
        }

        @else

        .sheet-panel {
            display:none;
            padding:16px 24px 40px 24px;
        }

        .sheet-panel.active {
            display:block;
        }

        @endif

        /* ===== REPORT PAGE ===== */

        .report-page {
            background:#fff;

            @if($isPdf)
                max-width:100%;
                margin:0;
                padding:20px;
                box-shadow:none;
            @else
                max-width:980px;
                margin:0 auto;
                padding:30px 36px;
                box-shadow:0 1px 6px rgba(0,0,0,0.12);
            @endif
        }

        /* ===== EDITABLE FIELD ===== */

        .editable {

            @if($isPdf)

            border:none !important;
            background:transparent !important;
            padding:0 !important;

            @else

            border:none;
            border-bottom:1.5px dashed #1976d2;
            background:#f0f7ff;
            padding:2px 4px;

            @endif

            font-family:Arial,sans-serif;
            font-size:13px;
            color:#000;
            outline:none;
            min-width:60px;
            width:100%;
        }

        @if(!$isPdf)

        .editable:focus {
            background:#dbeeff;
            border-bottom:2px solid #0d47a1;
        }

        @endif

        .editable.right {
            text-align: right;
        }

        /* ===== STATIC LABELS ===== */

        .static {
            color: #000;
        }

        /* ===== SECTION HEADERS ===== */

        .section-right {
            text-align: right;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .section-center-xl {
            text-align: center;
            font-weight: bold;
            font-size: 22px;
            padding: 8px 0 4px 0;
        }

        .section-center-lg {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            padding: 4px 0;
        }

        .section-center-md {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            padding: 4px 0 16px 0;
        }

        .section-title-lg {
            font-weight: bold;
            font-size: 18px;
            margin: 16px 0 8px 0;
        }

        .section-title-md {
            font-weight: bold;
            font-size: 16px;
            margin: 12px 0 8px 0;
        }

        /* ===== META INFO ===== */

        .meta-line {
            font-size: 13px;
            line-height: 22px;
            margin: 8px 0;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            margin: 10px 0 6px 0;
            font-size: 13px;
        }

        /* ===== REPORT TABLE ===== */

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin: 10px 0;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 5px 7px;
        }

        .report-table th {

            @if(!$isPdf)
            background:#f5f5f5;
            @endif

            font-weight:bold;
            text-align:center;
        }

        .report-table td.right,
        .report-table th.right {
            text-align: right;
        }

        .report-table td.center,
        .report-table th.center {
            text-align: center;
        }

        .report-table tr.total-row td,
        .report-table tr.total-row th {

            font-weight:bold;

            @if(!$isPdf)
            background:#f5f5f5;
            @endif
        }

        /* ===== NARROW TABLE ===== */

        .narrow-table {
            width: 65%;
        }

        /* ===== SALES / PURCHASE TABLE ===== */

        .sp-table {
            width: 55%;
            border-collapse: collapse;
            font-size: 13px;
            margin: 8px 0;
        }

        .sp-table td {
            padding: 5px 7px;
        }

        .sp-table td.right {
            text-align: right;
            font-weight: bold;
        }

        /* ===== CALC TABLE ===== */

        .calc-table {
            width: 70%;
            border-collapse: collapse;
            font-size: 14px;
            margin: 10px 0;
        }

        .calc-table th,
        .calc-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            font-weight: bold;
        }

        .calc-table th {

            text-align:center;

            @if(!$isPdf)
            background:#f5f5f5;
            @endif
        }

        .calc-table td.right {
            text-align: right;
        }

        /* ===== DECLARATION ===== */

        .declaration-list {
            margin: 10px 0;
        }

        .declaration-item {
            display: flex;
            gap: 10px;
            margin-bottom: 14px;
            line-height: 20px;
        }

        .declaration-num {
            min-width: 22px;
            font-weight: bold;
        }

        /* ===== SIGNATURE SECTION ===== */

        .signature-section {
            margin-top: 40px;
            text-align: right;
            font-size: 14px;
        }

        .signature-section .label {
            font-weight: bold;
            font-size: 14px;
        }

        /* ===== OFFICE USE ===== */

        .office-use {
            margin-top: 30px;
            line-height: 34px;
            font-size: 14px;
        }

        .office-use .heading {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 8px;
        }

        /* ===== NOTE LINE ===== */

        .note-line {
            font-size: 13px;
            margin: 4px 0;
        }

        /* ===== IN LACS HEADER ===== */

        .in-lacs-header {
            text-align: right;
            font-weight: bold;
            font-size: 13px;
            padding-right: 0;
        }

        /* ===== DIVIDER ===== */

        .divider {
            border: none;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }

        /* ===== HIDDEN INPUTS ===== */

        .d-none {
            display: none;
        }
        @if($isPdf)

        input {
            border:none !important;
            background:transparent !important;
            box-shadow:none !important;
            outline:none !important;
        }

        body {
            background:#fff !important;
        }

        @endif
    </style>

</head>
<body>

{{-- ============================================================
     MASTER FORM — wraps everything so both buttons can submit
     ============================================================ --}}

<form
    id="reportForm"
    method="POST"
    action="">

    @csrf

    {{-- Hidden meta fields --}}
    <input type="hidden" name="month"      value="{{ $month }}">
    <input type="hidden" name="bank_id"    value="{{ $bank->id }}">
    <input type="hidden" name="stock_type" value="{{ $stockType }}">

    {{-- ===== TOP BAR ===== --}}
@if(!$isPdf)
    <div class="top-bar">

        <div>
            <div class="title">
                Monthly Report Preview
            </div>
            <div class="subtitle">
                {{ $bank->bank_name }}
                &nbsp;|&nbsp;
                {{ date('F Y', strtotime($month)) }}
                &nbsp;|&nbsp;
                {{ ucfirst($stockType) }} Wise
            </div>
        </div>

        <div class="btn-group">

            <button
                type="button"
                class="btn-excel"
                onclick="submitForm('{{ route('export.monthly.report.download.excel') }}')">
                &#x2B07; Download Excel
            </button>

            <button
                type="button"
                class="btn-pdf"
                onclick="submitForm('{{ route('export.monthly.report.download.pdf') }}')">
                &#x2B07; Download PDF
            </button>

        </div>

    </div>
@endif
    {{-- ===== EDIT NOTICE ===== --}}
@if(!$isPdf)
    <div class="edit-notice">
        &#9998;&nbsp;
        <strong>Preview Mode:</strong>
        Blue dashed fields are editable.
        Static labels are not editable.
        Click any blue field to edit the value.
        Downloads will use your edited values.
    </div>
@endif
    {{-- ===== TABS ===== --}}
@if(!$isPdf)
    <div class="tabs-bar">

        <button
            type="button"
            class="tab-btn active"
            onclick="showTab('sheet1', this)">
            Sheet 1 — Stock Report
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showTab('sheet2', this)">
            Sheet 2 — Creditors
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showTab('sheet3', this)">
            Sheet 3 — Debtors
        </button>

        <button
            type="button"
            class="tab-btn"
            onclick="showTab('sheet4', this)">
            Sheet 4 — Calculation
        </button>

    </div>
@endif

    {{-- ============================================================
         SHEET 1 — STOCK REPORT
         ============================================================ --}}

    <div id="sheet1" class="sheet-panel active">
        <div class="report-page">

            {{-- PART A / ANNEXURE --}}
            <div class="section-right">PART - A</div>
            <div class="section-right">ANNEXURE</div>

            {{-- BANK NAME --}}
            <div class="section-center-xl">{{ $bank->bank_name }}</div>

            {{-- TITLE --}}
            <div class="section-center-lg">
                STOCK STATEMENT (REVISED PROFORMA)
            </div>

            <div class="section-center-md">
                (TO BE SUBMITTED BY THE BORROWER)
            </div>

            {{-- PERIODICITY --}}
            <div class="meta-line">
                <strong>Periodicity of submission of stock statement :</strong>
                Fortnightly / Monthly / quarterly / half yearly.
            </div>

            {{-- STATEMENT LINE --}}
            <div class="meta-line" style="margin-top:12px;">
                Statement as on
                <strong>{{ strtoupper(date('d-M-y', strtotime($lastDate))) }}</strong>
                belonging to M/s
                <strong>{{ $company->company_name }}</strong>
                {{ $company->address }}
                Hypothecated as security with
                <strong>{{ $bank->bank_name }}, {{ $bank->branch }}</strong>
            </div>

            {{-- ACCOUNT / FACILITY --}}
            <div class="meta-grid" style="margin-top:14px;">
                <div>
                    <strong>A/c No. :</strong>
                    {{ $bank->account_no }}
                </div>
                <div>
                    <strong>Facility</strong>
                </div>
                <div>Cash Credit</div>
            </div>

            {{-- LIMIT --}}
            <div class="meta-line" style="margin-top:6px;">
                <strong>Limit Rs. :</strong>

                <input
                    type="text"
                    class="editable right"
                    name="limit_amount"
                    id="limitAmount"
                    style="
                        width:200px;
                        display:inline-block;
                        margin-left:10px;
                    "
                    value="{{ $limitAmount ?? '' }}">
            </div>

            {{-- ===== STOCK TABLE ===== --}}

            <table class="report-table" style="margin-top:16px;">

                <thead>
                    <tr>
                        <th style="width:6%;">Sr No</th>
                        <th style="width:28%;">Particulars of Goods</th>
                        <th style="width:14%;">Where Lying</th>
                        <th style="width:14%;">Quantity In Kgs</th>
                        <th style="width:14%;">Rate</th>
                        <th style="width:14%;">Value</th>
                        <th style="width:10%;">Remarks</th>
                    </tr>
                </thead>

                <tbody id="stockTableBody">

                    @foreach($stockRows as $i => $row)
                    <tr>

                        <td class="center">
                            {{ $row['sr_no'] }}
                            <input
                                type="hidden"
                                name="stock_sr[]"
                                value="{{ $row['sr_no'] }}">
                        </td>

                        <td>
                            {{-- Editable: item/group name --}}
                            <input
                                type="text"
                                class="editable"
                                name="stock_name[]"
                                value="{{ $row['name'] }}">
                        </td>

                        <td class="center">
                            <span class="static">FACTORY</span>
                        </td>

                        <td>
                            {{-- Editable: qty --}}
                            <input
                                type="text"
                                class="editable right"
                                name="stock_qty[]"
                                value="{{ FormatIndianNumber($row['qty'], 2) }}">
                        </td>

                        <td>
                            {{-- Editable: rate --}}
                            <input
                                type="text"
                                class="editable right"
                                name="stock_rate[]"
                                value="{{ FormatIndianNumber($row['rate'], 2) }}">
                        </td>

                        <td>
                            {{-- Editable: value --}}
                            <input
                                type="text"
                                class="editable right stock-value"
                                name="stock_value[]"
                                value="{{ FormatIndianNumber($row['value'], 2) }}">
                        </td>

                        <td></td>

                    </tr>
                    @endforeach

                </tbody>

                <tfoot>
                    <tr class="total-row">
                        <td colspan="5" class="right">
                            <span class="static">TOTAL</span>
                        </td>
                        <td class="right">
                            {{-- Editable: grand total --}}
                            <input
                                type="text"
                                class="editable right"
                                name="stock_grand_total"
                                id="stockGrandTotal"
                                value="{{ FormatIndianNumber($grandTotal, 2) }}">
                        </td>
                        <td></td>
                    </tr>
                </tfoot>

            </table>

            {{-- FOOTER NOTES --}}
            <div class="note-line" style="margin-top:10px;">
                (Extra Sheet to be attached in case of Need)
            </div>
            <div class="note-line">
                PNB 938 revised (8/2009)
            </div>

            <hr class="divider">

            {{-- ===== PART B — DEBTORS ===== --}}

            <div class="section-title-lg">PART-B</div>

            <div class="section-title-md">
                Sundry Debtors (Receivables) @
            </div>

            <table class="report-table narrow-table">

                <thead>
                    <tr>
                        <th style="width:12%;">S.NO</th>
                        <th style="width:58%;">
                            List of Debtors as per Annexure
                        </th>
                        <th style="width:30%;">Amount (Rs.)</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td class="center">
                            <span class="static">I</span>
                        </td>
                        <td>
                            <span class="static">Upto 90 Days</span>
                        </td>
                        <td>
                            {{-- Editable --}}
                            <input
                                type="text"
                                class="editable right"
                                name="upto90_total"
                                id="upto90Total"
                                value="{{ FormatIndianNumber($upto90Total, 2) }}">
                        </td>
                    </tr>

                    <tr>
                        <td class="center">
                            <span class="static">II</span>
                        </td>
                        <td>
                            <span class="static">&gt;90 Days To 180 Days</span>
                        </td>
                        <td>
                            {{-- Editable --}}
                            <input
                                type="text"
                                class="editable right"
                                name="days91to180_total"
                                id="days91to180Total"
                                value="{{ FormatIndianNumber($days91to180Total, 2) }}">
                        </td>
                    </tr>

                    <tr>
                        <td class="center">
                            <span class="static">III</span>
                        </td>
                        <td>
                            <span class="static">&gt;180 Days</span>
                        </td>
                        <td>
                            {{-- Editable --}}
                            <input
                                type="text"
                                class="editable right"
                                name="moreThan180_total"
                                id="moreThan180Total"
                                value="{{ FormatIndianNumber($moreThan180Total, 2) }}">
                        </td>
                    </tr>

                </tbody>

                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="right">
                            <span class="static">TOTAL</span>
                        </td>
                        <td class="right">
                            <span id="debtorsTotalDisplay">
                                {{ FormatIndianNumber($debtorsGrandTotal, 2) }}
                            </span>
                            <input
                                type="hidden"
                                name="debtors_grand_total"
                                id="debtorsGrandTotalInput"
                                value="{{ FormatIndianNumber($debtorsGrandTotal, 2) }}">
                        </td>
                    </tr>
                </tfoot>

            </table>

            <div class="note-line" style="margin-top:8px;">
                @ Sundry debtors acceptable as per terms of sanction
            </div>
            <div class="note-line">
                $ Separate Annexure for i, ii and iii to be enclosed
            </div>

            <hr class="divider">

            {{-- ===== SALES DURING FINANCIAL YEAR ===== --}}

            <div style="display:flex; align-items:baseline; gap:20px; margin-top:10px;">
                <div style="font-weight:bold; font-size:14px;">
                    Sales during the financial year
                </div>
                <div style="font-weight:bold; margin-left:auto;">
                    In Lacs
                </div>
            </div>

            <table class="sp-table" style="margin-top:8px;">
                <tbody>
                    <tr>
                        <td style="width:6%;">1.</td>
                        <td style="width:60%;">
                            <span class="static">Sales upto last month</span>
                        </td>
                        <td class="right" style="width:34%;">
                            <input
                                type="text"
                                class="editable right"
                                name="sales_upto_last_month_lacs"
                                id="salesUptoLastMonth"
                                value="{{ FormatIndianNumber($salesUptoLastMonthLacs, 2) }}">
                        </td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>
                            <span class="static">Sales during the month</span>
                        </td>
                        <td class="right">
                            <input
                                type="text"
                                class="editable right"
                                name="sales_during_month_lacs"
                                id="salesDuringMonth"
                                value="{{ FormatIndianNumber($salesDuringMonthLacs, 2) }}">
                        </td>
                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>
                            <span class="static">Total Sales</span>
                        </td>
                        <td class="right">
                            <span id="totalSalesDisplay">
                                {{ FormatIndianNumber($totalSalesLacs, 2) }}
                            </span>
                            <input
                                type="hidden"
                                name="total_sales_lacs"
                                id="totalSalesInput"
                                value="{{ FormatIndianNumber($totalSalesLacs, 2) }}">
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr class="divider">

            {{-- ===== PART C — CREDITORS ===== --}}

            <div class="section-title-lg">PART - C</div>

            <div class="section-title-md">Sundry Creditors</div>

            <table class="report-table narrow-table">
                <thead>
                    <tr>
                        <th style="width:70%;"></th>
                        <th style="width:30%;">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <span class="static">
                                List of Creditors as per Annexure #
                            </span>
                        </td>
                        <td>
                            {{-- Editable --}}
                            <input
                                type="text"
                                class="editable right"
                                name="creditors_total"
                                id="creditorsTotal"
                                value="{{ FormatIndianNumber($creditorsTotal, 2) }}">
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="note-line" style="margin-top:6px;">
                # List of Creditors as per Annexure to be enclosed.
            </div>

            <hr class="divider">

            {{-- ===== PURCHASES DURING FINANCIAL YEAR ===== --}}

            <div style="display:flex; align-items:baseline; gap:20px; margin-top:10px;">
                <div style="font-weight:bold; font-size:14px;">
                    Purchase during the Financial Year
                </div>
                <div style="font-weight:bold; margin-left:auto;">
                    In Lacs
                </div>
            </div>

            <table class="sp-table" style="margin-top:8px;">
                <tbody>
                    <tr>
                        <td style="width:6%;">1.</td>
                        <td style="width:60%;">
                            <span class="static">Purchases upto last month</span>
                        </td>
                        <td class="right" style="width:34%;">
                            <input
                                type="text"
                                class="editable right"
                                name="purchase_upto_last_month_lacs"
                                id="purchaseUptoLastMonth"
                                value="{{ FormatIndianNumber($purchaseUptoLastMonthLacs, 2) }}">
                        </td>
                    </tr>
                    <tr>
                        <td>2.</td>
                        <td>
                            <span class="static">Purchases during the month</span>
                        </td>
                        <td class="right">
                            <input
                                type="text"
                                class="editable right"
                                name="purchase_during_month_lacs"
                                id="purchaseDuringMonth"
                                value="{{ FormatIndianNumber($purchaseDuringMonthLacs, 2) }}">
                        </td>
                    </tr>
                    <tr>
                        <td>3.</td>
                        <td>
                            <span class="static">Total Purchase</span>
                        </td>
                        <td class="right">
                            <span id="totalPurchaseDisplay">
                                {{ FormatIndianNumber($totalPurchaseLacs, 2) }}
                            </span>
                            <input
                                type="hidden"
                                name="total_purchase_lacs"
                                id="totalPurchaseInput"
                                value="{{ FormatIndianNumber($totalPurchaseLacs, 2) }}">
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr class="divider">

            {{-- ===== NOTES ===== --}}

            <div class="note-line">
                * Note: as per the periodicity of submission of stock statement
                in terms of sanction ( fortnightly / monthly / quarterly / half yearly )
            </div>
            <div class="note-line" style="font-style:italic; margin-top:6px;">
                (Extra sheet to be attached in case of need)
            </div>

            <hr class="divider">

            {{-- ===== DECLARATIONS ===== --}}

            <div class="declaration-list">

                @php
                    $declarations = [
                        'I/We declare and acknowledge that all the goods noted above stand hypothecated to the bank and the same are my/our own property and that I/We/am/are entitled to hypothecate them with the bank. They are unencumbered and are not subject to any other lien, claim or charge of any sort.',
                        'I/We certify that the quality and quantity of the stock are correct and in accordance with the entries in our record. The stock shown do not include damaged unsaleable / obsolete / old goods.',
                        'I/We certify that the valuation of stocks has been made as per mandatory Accounting Standard (AS-2) (i.e. cost price / Net Realisable Value whichever is lower) as prescribed by ICAI.',
                        'I/We certify that the above goods are adequately covered by insurance against fire and other necessary risks in terms of sanction. All premia on insurance policies have been paid and these are in force.',
                        'I/We certify that the amount of sundry debtors / sundry creditors and Sales / Purchase are correct and in accordance with the entries in our record.',
                        'In case the above contain any mis-statement (of which the bank is the sole judge) or there be any shortage of security, I/We shall render myself / ourselves liable to legal action.',
                    ];
                @endphp

                @foreach($declarations as $i => $text)
                <div class="declaration-item">
                    <div class="declaration-num">{{ $i+1 }})</div>
                    <div>{{ $text }}</div>
                </div>
                @endforeach

            </div>

            {{-- ===== SIGNATURE ===== --}}

            <div class="signature-section">
                <div class="label">BORROWER / AUTHORISED SIGNATORY</div>
                <div style="margin-top:36px;">For {{ $company->company_name }}</div>
                <div style="margin-top:28px;">Director</div>
            </div>

            {{-- ===== OFFICE USE ===== --}}

            <div class="office-use">
                <div class="heading">FOR OFFICE USE ONLY</div>
                <div>1. &nbsp; Limit _______________________</div>
                <div>2. &nbsp; Value of security (value of stock minus surplus sundry creditors, if any, to be deducted in terms of sanction) _______________________</div>
                <div>3. &nbsp; Margin (as per sanction) _______________________</div>
                <div>4. &nbsp; Drawing power (value of security as per above less margin) _______________________</div>
                <div>5. &nbsp; SRM updated on _________ Entered by (Name) __________________</div>
                <div style="margin-left:30px;">Verified by (Name) __________________</div>
                <div>6. &nbsp; Inspected on _________ by (Name) __________________</div>
                <div style="margin-left:30px;">Designation ___________________________</div>
                <div style="margin-left:30px; margin-top:30px;">(Signature) ___________________________</div>
            </div>

        </div>
    </div>


    {{-- ============================================================
         SHEET 2 — CREDITORS
         ============================================================ --}}

    <div id="sheet2" class="sheet-panel">
        <div class="report-page">

            <div class="section-center-xl" style="font-size:16px;">
                {{ $company->company_name }}
            </div>

            <div style="text-align:center; font-weight:bold; margin-bottom:16px;">
                Sundry Creditors Closing Balance As On
                {{ date('d-m-Y', strtotime($lastDate)) }}
            </div>

            <table class="report-table">

                <thead>
                    <tr>
                        <th style="width:8%;">Sr No</th>
                        <th style="width:62%;">Account Name</th>
                        <th style="width:30%;">Amount</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($creditorsRows as $i => $row)
                    <tr>

                        <td class="center">
                            {{ $row['sr_no'] }}
                        </td>

                        <td>
                            {{-- Editable: account name --}}
                            <input
                                type="text"
                                class="editable"
                                name="creditor_name[]"
                                value="{{ $row['account_name'] }}">
                        </td>

                        <td>
                            {{-- Editable: balance --}}
                            <input
                                type="text"
                                class="editable right creditor-balance"
                                name="creditor_balance[]"
                                value="{{ $row['closing_balance'] }}">
                        </td>

                    </tr>
                    @endforeach

                </tbody>

                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="right">
                            <span class="static">TOTAL</span>
                        </td>
                        <td class="right">
                            {{-- Editable: creditors total balance --}}
                            <input
                                type="text"
                                class="editable right"
                                name="creditors_total_balance"
                                id="creditorsTotalBalance"
                                value="{{ $creditorsTotalBalance }}">
                        </td>
                    </tr>
                </tfoot>

            </table>

        </div>
    </div>


    {{-- ============================================================
         SHEET 3 — DEBTORS
         ============================================================ --}}

    <div id="sheet3" class="sheet-panel">
        <div class="report-page">

            <div class="section-center-xl" style="font-size:16px;">
                {{ $company->company_name }}
            </div>

            <div style="text-align:center; font-weight:bold; margin-bottom:16px;">
                Sundry Debtors As On
                {{ date('d-m-Y', strtotime($lastDate)) }}
            </div>

            <table class="report-table">

                <thead>
                    <tr>
                        <th style="width:7%;">Sr No</th>
                        <th style="width:51%;">Particulars</th>
                        <th style="width:21%;">&lt;= 90 Days</th>
                        <th style="width:21%;">&gt;= 91 Days</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($debtorsRows as $i => $row)
                    <tr>

                        <td class="center">
                            {{ $row['sr_no'] }}
                        </td>

                        <td>
                            {{-- Editable: party name --}}
                            <input
                                type="text"
                                class="editable"
                                name="debtor_party[]"
                                value="{{ $row['party'] }}">
                        </td>

                        <td>
                            {{-- Editable: <= 90 days --}}
                            <input
                                type="text"
                                class="editable right debtor-less90"
                                name="debtor_less90[]"
                                value="{{ FormatIndianNumber($row['less90'], 2) }}">
                        </td>

                        <td>
                            {{-- Editable: >= 91 days --}}
                            <input
                                type="text"
                                class="editable right debtor-greater90"
                                name="debtor_greater90[]"
                                value="{{ FormatIndianNumber($row['greater90'], 2) }}">
                        </td>

                    </tr>
                    @endforeach

                </tbody>

                <tfoot>
                    <tr class="total-row">
                        <td colspan="2" class="right">
                            <span class="static">TOTAL</span>
                        </td>
                        <td class="right">
                            <span id="less90TotalDisplay">
                                {{ FormatIndianNumber($less90Total, 2) }}
                            </span>
                            <input
                                type="hidden"
                                name="less90_total"
                                id="less90TotalInput"
                                value="{{ FormatIndianNumber($less90Total, 2) }}">
                        </td>
                        <td class="right">
                            <span id="greater90TotalDisplay">
                                {{ FormatIndianNumber($greater90Total, 2) }}
                            </span>
                            <input
                                type="hidden"
                                name="greater90_total"
                                id="greater90TotalInput"
                                value="{{ FormatIndianNumber($greater90Total, 2) }}">
                        </td>
                    </tr>
                </tfoot>

            </table>

        </div>
    </div>


    {{-- ============================================================
         SHEET 4 — CALCULATION
         ============================================================ --}}

    <div id="sheet4" class="sheet-panel">
        <div class="report-page">

            <div class="section-center-xl" style="margin-bottom:20px;">
                FOR BANK
            </div>

            <table class="calc-table">

                <thead>
                    <tr>
                        <th style="width:25%;"></th>
                        <th style="width:25%;">Debtors</th>
                        <th style="width:25%;">Creditors</th>
                        <th style="width:25%;">Availability</th>
                    </tr>
                </thead>

                <tbody>

                    <tr>
                        <td><span class="static">Assesable</span></td>

                        <td class="right">
                            {{-- Editable: debtors upto 90 --}}
                            <input
                                type="text"
                                class="editable right"
                                name="calc_upto90"
                                id="calcUpto90"
                                value="{{ FormatIndianNumber($upto90Total, 2) }}">
                        </td>

                        <td class="right">
                            {{-- Editable: creditors total --}}
                            <input
                                type="text"
                                class="editable right"
                                name="calc_creditors"
                                id="calcCreditors"
                                value="{{ FormatIndianNumber($creditorsTotal, 2) }}">
                        </td>

                        <td class="right">
                            {{-- Editable: debtors availability --}}
                            <input
                                type="text"
                                class="editable right"
                                name="debtors_availability"
                                id="debtorsAvailability"
                                value="{{ FormatIndianNumber($debtorsAvailability, 2) }}">
                        </td>
                    </tr>

                    <tr>
                        <td><span class="static">Stock</span></td>

                        <td></td>

                        <td class="right">
                            {{-- Editable: stock total --}}
                            <input
                                type="text"
                                class="editable right"
                                name="stock_total"
                                id="stockTotal"
                                value="{{ FormatIndianNumber($stockTotal, 2) }}">
                        </td>

                        <td class="right">
                            {{-- Editable: stock availability --}}
                            <input
                                type="text"
                                class="editable right"
                                name="stock_availability"
                                id="stockAvailability"
                                value="{{ FormatIndianNumber($stockAvailability, 2) }}">
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" class="right">
                            <span class="static">D.P</span>
                        </td>
                        <td class="right">
                            <span id="dpDisplay">
                                {{ FormatIndianNumber($dp, 2) }}
                            </span>
                            <input
                                type="hidden"
                                name="dp"
                                id="dpInput"
                                value="{{ FormatIndianNumber($dp, 2) }}">
                        </td>
                    </tr>

                </tbody>

            </table>
            @if(!$isPdf)
            {{-- DP FORMULA NOTE --}}
            <div
                class="note-line"
                style="margin-top:14px; color:#555;">
                D.P = Debtors Availability + Stock Availability
                <br>
                Debtors Availability = (Debtors upto 90 − Creditors) × 70%
                <br>
                Stock Availability = Stock Total × 75%
            </div>
            @endif

        </div>
    </div>

</form>

@if(!$isPdf)

<script>

    function showTab(id, btn) {

        document.querySelectorAll('.sheet-panel')
            .forEach(p => p.classList.remove('active'));

        document.querySelectorAll('.tab-btn')
            .forEach(b => b.classList.remove('active'));

        document.getElementById(id).classList.add('active');

        btn.classList.add('active');
    }

    function submitForm(action) {

        document.getElementById('reportForm').action = action;

        document.getElementById('reportForm').submit();
    }

    function recalcDebtorsTotal() {

        var v1 = parseFloat(
            unformat(
                document.getElementById('upto90Total').value
            )
        ) || 0;

        var v2 = parseFloat(
            unformat(
                document.getElementById('days91to180Total').value
            )
        ) || 0;

        var v3 = parseFloat(
            unformat(
                document.getElementById('moreThan180Total').value
            )
        ) || 0;

        var total = v1 + v2 + v3;

        document.getElementById('debtorsTotalDisplay').textContent =
            formatNumber(total);

        document.getElementById('debtorsGrandTotalInput').value =
            formatNumber(total);
    }

    ['upto90Total', 'days91to180Total', 'moreThan180Total']
        .forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', recalcDebtorsTotal);
        });

    function recalcTotalSales() {

        var v1 = parseFloat(
            unformat(
                document.getElementById('salesUptoLastMonth').value
            )
        ) || 0;

        var v2 = parseFloat(
            unformat(
                document.getElementById('salesDuringMonth').value
            )
        ) || 0;

        var total = v1 + v2;

        document.getElementById('totalSalesDisplay').textContent =
            formatNumber(total);

        document.getElementById('totalSalesInput').value =
            formatNumber(total);
    }

    ['salesUptoLastMonth', 'salesDuringMonth']
        .forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', recalcTotalSales);
        });

    function recalcTotalPurchase() {

        var v1 = parseFloat(
            unformat(
                document.getElementById('purchaseUptoLastMonth').value
            )
        ) || 0;

        var v2 = parseFloat(
            unformat(
                document.getElementById('purchaseDuringMonth').value
            )
        ) || 0;

        var total = v1 + v2;

        document.getElementById('totalPurchaseDisplay').textContent =
            formatNumber(total);

        document.getElementById('totalPurchaseInput').value =
            formatNumber(total);
    }

    ['purchaseUptoLastMonth', 'purchaseDuringMonth']
        .forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', recalcTotalPurchase);
        });

    function recalcDP() {

        var da = parseFloat(
            unformat(
                document.getElementById('debtorsAvailability').value
            )
        ) || 0;

        var sa = parseFloat(
            unformat(
                document.getElementById('stockAvailability').value
            )
        ) || 0;

        var dp = da + sa;

        document.getElementById('dpDisplay').textContent =
            formatNumber(dp);

        document.getElementById('dpInput').value =
            formatNumber(dp);
    }

    ['debtorsAvailability', 'stockAvailability']
        .forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', recalcDP);
        });

    function recalcStockGrandTotal()
    {
        let total = 0;

        document
            .querySelectorAll('.stock-value')
            .forEach(function(el){

                total += parseFloat(
                    unformat(el.value)
                ) || 0;
            });

        document.getElementById(
            'stockGrandTotal'
        ).value = formatNumber(total);
    }

    document
        .querySelectorAll('.stock-value')
        .forEach(function(el){

            el.addEventListener(
                'input',
                recalcStockGrandTotal
            );
        });

    function recalcCreditorsTotal()
    {
        let total = 0;

        document
            .querySelectorAll('.creditor-balance')
            .forEach(function(el){

                total += parseFloat(
                    unformat(el.value)
                ) || 0;

            });

        document.getElementById(
            'creditorsTotalBalance'
        ).value = formatNumber(total);
    }

    document
        .querySelectorAll('.creditor-balance')
        .forEach(function(el){

            el.addEventListener(
                'input',
                recalcCreditorsTotal
            );

        });

    function recalcDebtorSheetTotals()
    {
        let less90Total = 0;
        let greater90Total = 0;

        document
            .querySelectorAll('.debtor-less90')
            .forEach(function(el){

                less90Total += parseFloat(
                    unformat(el.value)
                ) || 0;

            });

        document
            .querySelectorAll('.debtor-greater90')
            .forEach(function(el){

                greater90Total += parseFloat(
                    unformat(el.value)
                ) || 0;

            });

        document.getElementById(
            'less90TotalDisplay'
        ).textContent = formatNumber(
            less90Total
        );

        document.getElementById(
            'less90TotalInput'
        ).value = formatNumber(
            less90Total
        );

        document.getElementById(
            'greater90TotalDisplay'
        ).textContent = formatNumber(
            greater90Total
        );

        document.getElementById(
            'greater90TotalInput'
        ).value = formatNumber(
            greater90Total
        );
    }

    document
        .querySelectorAll(
            '.debtor-less90, .debtor-greater90'
        )
        .forEach(function(el){

            el.addEventListener(
                'input',
                recalcDebtorSheetTotals
            );

        });

    function unformat(val)
    {
        val = String(val).trim();

        if (
            val.startsWith('(') &&
            val.endsWith(')')
        ) {
            val =
                '-' +
                val.substring(
                    1,
                    val.length - 1
                );
        }

        return val.replace(/,/g, '');
    }

    // Format number with 2 decimal places
    function formatNumber(num) {
        return parseFloat(num).toFixed(2)
            .replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

</script>
@endif
</body>
</html>