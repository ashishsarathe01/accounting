@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
    .check-icon {
        width: 22px;  /* Adjust this value to match '0' size visually */
        height: 22px;
    }

    .count-value {
        font-size: 18px; /* Match this to your dashboard font size */
        font-weight: 600;
    }

    .gst-dashboard-count {
        font-size: 18px;
        font-weight: 600;
    }

    .gst-dashboard-card {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid grey; /* light gray border */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08); /* soft subtle shadow */
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        background-color: #fff;
    }

    .gst-dashboard-card:hover {
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15); /* on hover: deeper */
    }

    .gst-dashboard-header {
        background-color: #1e4486;
        padding: 10px;
        min-height: 60px;
    }

    .gst-dashboard-title {
        font-weight: 700;
        font-size: 15px;
        color: #fff;
        text-align: center;
    }

    .gst-dashboard-body {
        background: #F6F7FF; /* subtle light bluish background */
        min-height: 70px;
        border: 1.5px solid grey;
    }

    .gst-dashboard-count {
        font-size: 22px;
        font-weight: 700;
        color: #28a745;
    }

    /* Optional card spacing improvement on larger screens */
    @media (min-width: 1200px) {
        .col-lg-3 {
            flex: 0 0 auto;
            width: 23%;
            margin-right: 1.5%;
        }

        .col-lg-3:nth-child(4n) {
            margin-right: 0;
        }
    }

    .gstr-header {
        background-color: #1ac6c6;
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }

    .gstr-header .btn {
        background-color: #1e4486;
        color: #fff;
        border: none;
    }

    .gstr-header .btn:hover {
        background-color: #16325c;
    }

    .form-check-label {
        color: #333;
    }

    .bg-primary {
        background-color: #1e4486 !important;
    }

    .container {
        max-width: 1320px;
    }

    /* Toggle buttons styles */
    .view-toggle {
        display: flex;
        justify-content: center;
        margin-bottom: 20px;
    }

    .toggle-btn {
        padding: 8px 20px;
        background-color: #e9ecef;
        border: 1px solid #ced4da;
        cursor: pointer;
        transition: all 0.3s;
        font-weight: 600;
    }

    .toggle-btn:first-child {
        border-top-left-radius: 5px;
        border-bottom-left-radius: 5px;
        border-right: none;
    }

    .toggle-btn:last-child {
        border-top-right-radius: 5px;
        border-bottom-right-radius: 5px;
        border-left: none;
    }

    .toggle-btn.active {
        background-color: #1e4486;
        color: white;
        border-color: #1e4486;
    }

    .view-content.active {
        display: block;
    }

    .clickable-row:hover td {
        background-color: #cce5ff !important; /* Light blue */
        cursor: pointer;
    }

     tr.no-border {
        border-bottom: none !important;
    }
    
    .summary-box{
                border:1px solid #c7c7c7;
                margin-bottom:20px;
                background:#fff;
             }
        
        .summary-header{
            background:#1fb5ad;
            color:#fff;
            font-weight:bold;
            padding:8px 12px;
            font-size:15px;
        }
        
        .summary-table{
            width:100%;
            border-collapse:collapse;
        }
        
        .summary-table th{
            background:#f2f2f2;
            border:1px solid #d5d5d5;
            padding:8px;
            font-size:13px;
            text-align:center;
        }
        
        .summary-table td{
            border:1px solid #d5d5d5;
            padding:8px;
            font-size:13px;
        }
        
        .summary-total{
            background:#f9f9f9;
            font-weight:bold;
        }
        
        .summary-sub-row{
            background:#fcfcfc;
        }
        
        .summary-sub-row td:first-child{
            padding-left:30px;
        }
            
</style>
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            @php
                $cards = [
                    ['title' => '4A, 4B, 6B, 6C - B2B, SEZ, DE Invoices', 'count' => $saleCountB2B, 'route' => route('gst.b2b.detailed.billwise', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '5 - B2C (Large) Invoices','count' => $b2cLargeCount, 'route' => route('gst.b2c.large.detailed', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '6A - Exports Invoices', 'route' => '#'],
                    ['title' => '7 - B2C (Others)', 'count' => $totalRows_small, 'route' => route('gst.b2c.normal.statewise', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '8A, 8B, 8C, 8D - Nil Rated Supplies', 'route' => route('nilratedreginter', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '9B - Credit / Debit Notes (Registered)','count' => $totalNotes , 'route' => route('debitNote', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '9B - Credit / Debit Notes (Unregistered)', 'route' => route('debitNoteUnreg', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '11A(1), 11A(2) - Tax Liability (Advances Received)', 'route' => '#'],
                    ['title' => '11B(1), 11B(2) - Adjustment of Advances', 'route' => '#'],
                    ['title' => '12 - HSN-wise summary of outward supplies','count'=>$hsnWiseSummaryCount,'route' => route('hsnSummary', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '13 - Documents Issued','count' => $docCount, 'route' => route('docIssued', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
                    ['title' => '14 - Supplies made through ECO', 'route' => '#'],
                    ['title' => '15 - Supplies U/s 9(5)', 'route' => '#'],
                ];
            @endphp
            <!-- Main content column -->
            <div class="col-md-10 col-sm-12 px-4">
                <div class="container-fluid">
                    <ul class="nav nav-fill nav-tabs" role="tablist">
                         <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="fill-tab-1" data-bs-toggle="tab" href="#fill-tabpanel-1" role="tab" aria-controls="fill-tabpanel-1" aria-selected="false">GSTR-1 Books</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link " id="fill-tab-0" data-bs-toggle="tab" href="#fill-tabpanel-0" role="tab" aria-controls="fill-tabpanel-0" aria-selected="true"> GSTR-1 Matching with Portal</a>
                        </li>
                       
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="fill-tab-2" data-bs-toggle="tab" href="#fill-tabpanel-2" role="tab" aria-controls="fill-tabpanel-2" aria-selected="false">Generated Summary </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="fill-tab-3" data-bs-toggle="tab" href="#fill-tabpanel-3" role="tab" aria-controls="fill-tabpanel-2" aria-selected="false">Filing</a>
                        </li>
                    </ul>
                    <!-- Header section -->
                    <div class="container mt-4">
                        <div class="tab-content mt-4">
                            @php  use Carbon\Carbon; @endphp
                            <div class="tab-pane mb-4 active" id="fill-tabpanel-1" role="tabpanel" aria-labelledby="fill-tab-1">
                                <div class="card mb-3 shadow-sm border-0">
                                    <div class="card-body p-0">
                                        <!-- Header Banner -->
                                        <div class="gstr-header d-flex justify-content-between align-items-center px-3 py-2">
                                            <h5 class="mb-0 text-white fw-bold">GSTR-1 - Details of outward supplies of goods or services</h5>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-primary">E-INVOICE ADVISORY</button>
                                                <button class="btn btn-sm btn-primary">
                                                    HELP <i class="fas fa-question-circle ms-1"></i>
                                                </button>
                                                <button class="btn btn-sm btn-primary">
                                                    <i class="fas fa-sync-alt"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- White Info Row -->
                                        <div class="bg-white px-3 py-3">
                                            <div class="row g-2">
                                                <div class="col-md-4"><strong>GSTIN -</strong> {{$merchant_gst}}</div>
                                                <div class="col-md-4"><strong>Legal Name -</strong> {{$comp_details->legal_name}}</div>
                                                <div class="col-md-4"><strong>Trade Name -</strong>  {{$comp_details->company_name}}</div>
                                                <div class="col-md-4"><strong>FY -</strong> 20{{$fy}}</div>
                                                @php
                                                    
                                                    $from = Carbon::parse($from_date);
                                                    $to = Carbon::parse($to_date);
                                                @endphp
                                                <div class="col-md-4">
                                                    <strong>Tax Period -</strong>
                                                    @if($from->format('F Y') === $to->format('F Y'))
                                                        {{ $from->format('F Y') }}
                                                    @else
                                                        {{ $from->format('F Y') }} to {{ $to->format('F Y') }}
                                                    @endif
                                                </div>
                                                <div class="col-md-4"><strong>Status -</strong> Not Filed</div>
                                                <div class="col-md-4 text-danger">
                                                    <i class="fas fa-circle text-danger me-1" style="font-size: 8px;"></i> * Indicates Mandatory Fields
                                                </div>
                                                @php
                                                    $from = Carbon::parse($from_date);
                                                    $dueDate = $from->copy()->addMonth()->day(11); // 11th of next month
                                                @endphp
                                                <div class="col-md-4">
                                                    <strong>Due Date -</strong> {{ $dueDate->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Toggle buttons -->
                                <div class="bg-white py-2 px-3 border mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="fileNil">
                                        <label class="form-check-label fw-semibold" for="fileNil">File Nil GSTR-1</label>
                                    </div>
                                </div>

                                <!-- First view (Detailed) -->
                                <div id="view1" class="view-content active">
                                    <div class="bg-primary text-white px-3 py-2 fw-bold rounded-top">
                                        ADD RECORD DETAILS
                                    </div>
                                    <!-- Cards -->
                                    <div class="row bg-white py-3 px-2 rounded-bottom justify-content-start">
                                        @foreach ($cards as $card)
                                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                                <a href="{{ $card['route'] }}" class="text-decoration-none">
                                                    <div class="card gst-dashboard-card shadow-sm border-0 h-100 text-center">
                                                        <div class="gst-dashboard-header d-flex align-items-center justify-content-center">
                                                            <h6 class="gst-dashboard-title m-0">{{ $card['title'] }}</h6>
                                                        </div>
                                                        <div class="gst-dashboard-body d-flex flex-column justify-content-center align-items-center py-3">
                                                            <div class="gst-dashboard-count d-flex align-items-center">
                                                                <img src="//static.gst.gov.in/uiassets/images/processed.png" class="check-icon me-2" alt="check icon">
                                                                <span class="count-value">{{ $card['count'] ?? '0' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                    <strong id="show_to">Gross Turnover : {{formatIndianNumber($turnover_amount)}}  (FY : @php echo $fy; @endphp)</strong>
                                    <button class="btn btn-info add_turnover" type="button" >@empty($turnover_amount) Add Gross Turnover (@php echo $fy; @endphp) @else Update Gross Turnover  @endempty</button>
                                    
                                
                                <br><br>
                                <form method="POST" action="{{ route('gstr1.send') }}" id="gst_portal_form">
                                    @csrf
                                
                                    <input type="hidden" name="merchant_gst" id="merchant_gst" value="{{ $merchant_gst }}">
                                    <input type="hidden" name="company_id" value="{{ $company_id }}">
                                    <input type="hidden" name="from_date" value="{{ $from_date }}">
                                    <input type="hidden" name="to_date" value="{{ $to_date }}">
                                    <input type="hidden" name="download_json_status" id="download_json_status" value="0">
                                    <div class="d-flex justify-content-end gap-2 mt-3 mb-4">
                                
                                        <button class="btn btn-primary" type="submit">
                                            Send to GST Portal
                                        </button>
                                        <button type="button" class="btn btn-success" id="download_json">
                                                Download Json
                                            </button>
                                        @if($einvoice_status != 1)
                                            <button type="button" class="btn btn-danger" id="resetGstr1Btn">
                                                Reset GSTR-1
                                            </button>
                                        @endif
                                
                                        <button type="button" class="btn btn-success" id="generateSummaryBtn">
                                            Generate Summary
                                        </button>
                                
                                    </div>
                                </form>
                                
                            </div>
                            <div class="tab-pane " id="fill-tabpanel-0" role="tabpanel" aria-labelledby="fill-tab-0">
                                <div id="view2" class="view-content" style="height:100vh;">
                                    <div class="bg-primary text-white px-3 py-2 mb-3 fw-bold rounded-top">
                                        GSTR-1 Portal
                                            @php
                                                   
                                                    $from = Carbon::parse($from_date);
                                                    $to = Carbon::parse($to_date);
                                                @endphp
                                                <div class="">
                                                    @if($from->format('F Y') === $to->format('F Y'))
                                                        {{ $from->format('F Y') }}
                                                    @else
                                                        {{ $from->format('F Y') }} to {{ $to->format('F Y') }}
                                                    @endif
                                                </div>
                                    </div>
                                    <!-- B2B Summary -->
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom:10px;">
                                        <h4 style="margin: 0;">B2B</h4>
                                        <label style="margin-left: 15px; cursor: pointer; display: flex; align-items: center; gap: 5px;"><input type="checkbox" id="detailedCheckbox" onchange="togglePartySummary()"/>detailed</label>
                                    </div>
                                    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
                                        <thead>
                                            <tr style="border:1px solid black; background-color:rgb(65, 205, 230); color:white; cursor: pointer;">
                                                <th style="border:1px solid black; font-size:15px;">GSTIN</th>
                                                <th style="border:1px solid black; font-size:15px;">Party Name</th>
                                                <th style="border:1px solid black; font-size:15px;">Portal Total (₹)</th>
                                                <th style="border:1px solid black; font-size:15px;">Books Total (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $total_api_value_inv = 0;
                                                $total_db_value_inv = 0;
                                                $anyMismatch = false;
                                            @endphp
                                            <!-- Summary rows (hidden initially) -->
                                            <tbody id="party-summary" style="display: none;">
                                                @foreach($invoiceSummaries as $index => $summary)
                                                    @php 
                                                        $total_api_value_inv += $summary['total_value'];
                                                        $total_db_value_inv += $summary['db_value'];
                                                        if (!$summary['match']) $anyMismatch = true;
                                                    @endphp
                                                    <tr class="clickable-row" onclick="toggleDetails({{ $index }})" style="color: {{ $summary['match'] ? 'green' : 'red' }};">
                                                        <td style="border:1px solid black; font-size:15px;">{{ $summary['gstin'] }}</td>
                                                        <td style="border:1px solid black; font-size:15px;">{{ $summary['ctin'] }}</td>
                                                        <td style="border:1px solid black; font-size:15px;">{{ formatIndianNumber($summary['total_value'], 2) }}</td>
                                                        <td style="border:1px solid black; font-size:15px;">{{ formatIndianNumber($summary['db_value'], 2) }}</td>
                                                    </tr>
                                                    <!-- Expandable Invoice Details -->
                                                    <tr id="details-{{ $index }}" style="display: none;">
                                                        <td colspan="4">
                                                            <div style="padding: 10px; background-color: #cce5ff;">
                                                                <strong>Matched Invoices</strong>
                                                                <table border="1" width="100%" cellpadding="5">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Invoice No</th>
                                                                            <th>Portal Value</th>
                                                                            <th>Books Value</th>
                                                                            <th>Match</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse($summary['matched_invoices'] as $inv)
                                                                            <tr style="color: {{ $inv['match'] ? 'green' : 'red' }}">
                                                                                <td style="border:1px solid black">{{ $inv['invoice_no'] }}</td>
                                                                                <td style="border:1px solid black">{{ formatIndianNumber($inv['api_value'], 2) }}</td>
                                                                                <td style="border:1px solid black">{{ formatIndianNumber($inv['db_value'], 2) }}</td>
                                                                                <td style="border:1px solid black">{{ $inv['match'] ? '✔️' : '❌' }}</td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr><td colspan="4">No matching invoices.</td></tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                                <br>
                                                                <strong>Only on Portal</strong>
                                                                <table border="1" width="100%" cellpadding="5">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Invoice No</th>
                                                                            <th>API Value</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse($summary['only_in_api'] as $inv)
                                                                            <tr style="color: red;">
                                                                                <td>{{ $inv['invoice_no'] }}</td>
                                                                                <td>{{ formatIndianNumber($inv['api_value'], 2) }}</td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr><td colspan="2">None</td></tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table><br>
                                                                <strong>Only in Books</strong>
                                                                <table border="1" width="100%" cellpadding="5">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Invoice No</th>
                                                                            <th>DB Value</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse($summary['only_in_books'] as $inv)
                                                                            <tr style="color: red;">
                                                                                <td>{{ $inv['invoice_no'] }}</td>
                                                                                <td>{{ formatIndianNumber($inv['db_value'], 2) }}</td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr><td colspan="2">None</td></tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach 
                                            </tbody>
                                            <!-- Total Summary Row -->
                                            <tr style="color:{{ $anyMismatch ? 'red' : 'green' }};">
                                                <td colspan="2" style="border:1px solid black; text-align:center; font-weight: bold;">Total</td>
                                                <td style="border:1px solid black; font-weight: bold;">{{ formatIndianNumber($total_api_value_inv, 2) }}</td>
                                                <td style="border:1px solid black; font-weight: bold;">{{ formatIndianNumber($total_db_value_inv, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!-- ✅ Credit Note Registered Section -->
                                    <div style="margin-bottom:10px;display: flex; align-items: center; gap: 10px; margin-top:30px;">
                                        <h4 style="margin-bottom: 0;">Credit Note Registered</h4>
                                        <label style="margin-left: 15px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                            <input type="checkbox" id="creditNoteDetailedCheckbox" onchange="toggleCreditNoteSummary()" />
                                            Detailed
                                        </label>
                                    </div>
                                    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
                                        <thead>
                                            <tr style="background-color: rgb(65, 205, 230); color:white;">
                                                <th style="border:1px solid black; font-size:15px;">GSTIN</th>
                                                <th style="border:1px solid black;">Party Name</th>
                                                <th style="border:1px solid black;">Portal Total (₹)</th>
                                                <th style="border:1px solid black;">Books Total (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $grand_api_total = 0;
                                                    $grand_books_total = 0;
                                                    $anyMismatchCr = false;
                                            @endphp
                                                <tbody id="credit-note-summary" style="display: none;">
                                            @foreach ($creditNoteSummaries as $index => $group)
                                                @php $grand_api_total += $group['total_value'];
                                                    $grand_books_total += $group['db_value'];
                                                    if (!$group['match']) $anyMismatchCr = true;
                                                @endphp

                                                <tr onclick="toggleCreditNoteDetails('cdnr{{ $index }}')" style="cursor:pointer; color: {{ $group['match'] ? 'green' : 'red' }};">
                                                    <td style="border:1px solid black;">{{ $group['gstin'] }}</td>
                                                    <td style="border:1px solid black;">{{ $group['ctin'] }}</td>
                                                    <td style="border:1px solid black;">{{ formatIndianNumber($group['total_value'], 2) }}</td>
                                                    <td style="border:1px solid black;">{{ formatIndianNumber($group['db_value'], 2) }}</td>
                                                </tr>

                                                {{-- Expandable row --}}
                                                <tr id="details-cdnr{{ $index }}" style="display: none;">
                                                    <td colspan="3">
                                                        <div style="padding: 10px; background-color: #cce5ff;">
                                                            <strong>Matched Credit Notes</strong>
                                                            <table border="1" width="100%" cellpadding="5">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Note No</th>
                                                                        <th>API Value</th>
                                                                        <th>Books Value</th>
                                                                        <th>Match</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($group['matched_invoices'] as $note)
                                                                        <tr style="color: {{ $note['match'] ? 'green' : 'red' }}">
                                                                            <td style="border:1px solid black">{{ $note['invoice_no'] }}</td>
                                                                            <td style="border:1px solid black">{{ formatIndianNumber($note['api_value'], 2) }}</td>
                                                                            <td style="border:1px solid black">{{ formatIndianNumber($note['db_value'], 2) }}</td>
                                                                            <td style="border:1px solid black">{{ $note['match'] ? '✔️' : '❌' }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr><td colspan="4">No matched credit notes.</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>

                                                            <br>

                                                            <strong>Only on Portal</strong>
                                                            <table border="1" width="100%" cellpadding="5">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Note No</th>
                                                                        <th>Total Value</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($group['only_in_api'] as $note)
                                                                        <tr style="color: red;">
                                                                            <td>{{ $note['invoice_no'] }}</td>
                                                                            <td>{{ formatIndianNumber($note['api_value'], 2) }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr><td colspan="2">None</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>

                                                            <br>

                                                            <strong>Only in Books</strong>
                                                            <table border="1" width="100%" cellpadding="5">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Note No</th>
                                                                        <th>Total Value</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($group['only_in_books'] as $note)
                                                                        <tr style="color: red;">
                                                                            <td>{{ $note['invoice_no'] }}</td>
                                                                            <td>{{ formatIndianNumber($note['db_value'], 2) }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr><td colspan="2">None</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>

                                            {{-- Grand Total Row --}}
                                            <tr style="color:{{ $anyMismatchCr ? 'red' : 'green' }};">
                                                <td colspan=2 style="text-align:center; font-weight: bold; border:1px solid black;">Grand Total</td>
                                                <td style="font-weight: bold; border:1px solid black;">{{ formatIndianNumber($grand_api_total, 2) }}</td>
                                                <td style="font-weight: bold; border:1px solid black;">{{ formatIndianNumber($grand_books_total, 2) }}</td> 
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div style="margin-bottom:10px; display: flex; align-items: center; gap: 10px; margin-top:30px;">
                                        <h4 style="margin-bottom: 0;">Debit Note Registered</h4>
                                        <label style="margin-left: 15px; cursor: pointer; display: flex; align-items: center; gap: 5px;">
                                            <input type="checkbox" id="debitNoteDetailedCheckbox" onchange="toggleDebitNoteSummary()" />
                                            Detailed
                                        </label>
                                    </div>
                                    <table border="1" cellpadding="8" cellspacing="0" style="width:100%;">
                                        <thead>
                                            <tr style="background-color: rgb(65, 205, 230); color:white;">
                                                <th style="border:1px solid black; font-size:15px;">GSTIN</th>
                                                <th style="border:1px solid black;">Party Name</th>
                                                <th style="border:1px solid black;">Portal Total (₹)</th>
                                                <th style="border:1px solid black;">Books Total (₹)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $grand_api_total = 0;
                                                $grand_books_total = 0;
                                                 $anyMismatchDr = false;
                                            @endphp

                                            <tbody id="debit-note-summary" style="display: none;">
                                            @foreach ($debitNoteSummaries as $index => $group)
                                                @php
                                                    $grand_api_total += $group['total_value'];
                                                    $grand_books_total += $group['db_value'];
                                                    if (!$group['match']) $anyMismatchDr = true;
                                                @endphp

                                                <tr onclick="toggleDebitNoteDetails('dnr{{ $index }}')" style="cursor:pointer; color: {{ $group['match'] ? 'green' : 'red' }};">
                                                    <td style="border:1px solid black;">{{ $group['gstin'] }}</td>
                                                    <td style="border:1px solid black;">{{ $group['ctin'] }}</td>
                                                    <td style="border:1px solid black;">{{ formatIndianNumber($group['total_value'], 2) }}</td>
                                                    <td style="border:1px solid black;">{{ formatIndianNumber($group['db_value'], 2) }}</td>
                                                </tr>

                                                {{-- Expandable Row --}}
                                                <tr id="details-dnr{{ $index }}" style="display: none;">
                                                    <td colspan="4">
                                                        <div style="padding: 10px; background-color: #cce5ff;">
                                                            <strong>Matched Debit Notes</strong>
                                                            <table border="1" width="100%" cellpadding="5">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Note No</th>
                                                                        <th>API Value</th>
                                                                        <th>Books Value</th>
                                                                        <th>Match</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($group['matched_invoices'] as $note)
                                                                        <tr style="color: {{ $note['match'] ? 'green' : 'red' }}">
                                                                            <td style="border:1px solid black;">{{ $note['invoice_no'] }}</td>
                                                                            <td style="border:1px solid black;">{{ formatIndianNumber($note['api_value'], 2) }}</td>
                                                                            <td style="border:1px solid black;">{{ formatIndianNumber($note['db_value'], 2) }}</td>
                                                                            <td style="border:1px solid black;">{{ $note['match'] ? '✔️' : '❌' }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr><td colspan="4">No matched debit notes.</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>

                                                            <br>

                                                            <strong>Only on Portal</strong>
                                                            <table border="1" width="100%" cellpadding="5">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Note No</th>
                                                                        <th>Total Value</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($group['only_in_api'] as $note)
                                                                        <tr style="color: red;">
                                                                            <td>{{ $note['invoice_no'] }}</td>
                                                                            <td>{{ formatIndianNumber($note['api_value'], 2) }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr><td colspan="2">None</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>

                                                            <br>

                                                            <strong>Only in Books</strong>
                                                            <table border="1" width="100%" cellpadding="5">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Note No</th>
                                                                        <th>Total Value</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($group['only_in_books'] as $note)
                                                                        <tr style="color: red;">
                                                                            <td>{{ $note['invoice_no'] }}</td>
                                                                            <td>{{ formatIndianNumber($note['db_value'], 2) }}</td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr><td colspan="2">None</td></tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>

                                            {{-- Grand Total Row --}}
                                            <tr style="color: {{ $anyMismatchDr ? 'red' : 'green' }};">
                                                <td colspan="2" style="text-align:center; font-weight: bold; border:1px solid black;">Grand Total</td>
                                                <td style="font-weight: bold; border:1px solid black;">{{ formatIndianNumber($grand_api_total, 2) }}</td>
                                                <td style="font-weight: bold; border:1px solid black;">{{ formatIndianNumber($grand_books_total, 2) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- second view -->
                            
                            <!-- view third -->
                            <div class="tab-pane" id="fill-tabpanel-2" role="tabpanel" aria-labelledby="fill-tab-2">
                                <div id="view2" class="view-content" style="height:100vh;">
                                    <!-- Section Header -->
                                    <div class="bg-primary text-white px-3 py-2 fw-bold rounded-top">
                                        GSTR-1 Summary 
                                    </div>

                                    <!-- Summary content will go here -->
                                     <div class="row bg-white py-3 px-2 rounded-bottom justify-content-center">
                                      <div id="gstr1-summary-container"></div>
                                    
                                    <div class="d-flex justify-content-end mt-4 mb-3">
                                        <button type="button"
                                                class="btn btn-primary px-4 py-2"
                                                id="proceedToFilingBtn"
                                                style="display:none;">
                                            Proceed To File
                                        </button>
                                    </div>
                                    
                                        </div>
                                </div>
                                </div>
                            <!-- third view (Summary) -->
                            <div class="tab-pane" id="fill-tabpanel-3" role="tabpanel" aria-labelledby="fill-tab-3">
                        
                            <div id="view2" class="view-content" style="height:100vh;">
                        
                                <!-- Filing Header -->
                                <div class="bg-info text-white px-3 py-3 fw-bold rounded-top"
                                     style="font-size:22px; background:#1fb5ad !important;">
                                    Returns Filing for GST GSTR1
                                </div>
                        
                                <!-- Filing Box -->
                                <div class="bg-white p-4 border border-top-0 rounded-bottom">
                        
                                    <!-- Declaration -->
                                    <div class="mb-4">
                        
                                        <div class="form-check d-flex align-items-start">
                        
                                            <input class="form-check-input mt-1"
                                                   type="checkbox"
                                                   id="declarationCheck"
                                                   style="width:22px;height:22px;">
                        
                                            <label class="form-check-label ms-3"
                                                   for="declarationCheck"
                                                   style="font-size:18px; line-height:30px;">
                        
                                                I hereby solemnly affirm and declare that the information
                                                given herein above is true and correct to the best of
                                                my/our knowledge and belief and nothing has been concealed therefrom.
                        
                                            </label>
                        
                                        </div>
                        
                                    </div>
                        
                                    <!-- Authorised Signatory -->
                                    <div class="row mb-4">
                        
                                        <div class="col-md-6">

                                            <label class="fw-bold mb-2">
                                                Authorised Signatory <span class="text-danger">*</span>
                                            </label>
                                        
                                            <select class="form-select form-select-lg"
                                                    id="authorisedSignatory"
                                                    name="authorised_signatory_pan">
                                        
                                                {{--
                                                @if(count($authorizedSignatories) > 1)
                                                    <option value="">Select Authorised Signatory</option>
                                                @endif
                                        
                                                @foreach($authorizedSignatories as $signatory)
                                                    <option value="{{ $signatory['pan'] }}"
                                                        {{ count($authorizedSignatories) == 1 ? 'selected' : '' }}>
                                                        {{ $signatory['name'] }}
                                                    </option>
                                                @endforeach
                                        --}}
                                            </select>
                                        
                                        </div>
                        
                                    </div>
                        
                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-end gap-3 mt-5">
                        
                                        <button type="button"
                                                class="btn btn-light border px-5 py-2 fw-bold"
                                                id="backToSummaryBtn">
                        
                                            BACK
                        
                                        </button>
                        
                        
                                        <button type="button"
                                                class="btn btn-primary px-5 py-2 fw-bold"
                                                id="fileWithEvcBtn">
                        
                                            FILE WITH EVC
                        
                                        </button>
                        
                                    </div>
                        
                                    <!-- DSC Steps -->
                                    <div class="mt-5 p-4"
                                         style="background:#f5f5f5;border-radius:10px;">
                        
                                        <h4 class="fw-bold text-primary mb-3">
                                            DSC Usage Steps:
                                        </h4>
                        
                                        <ul style="font-size:16px; line-height:32px;">
                        
                                            <li>Run the emsigner as Administrator.</li>
                        
                                            <li>
                                                Open the portal, fill the appropriate details and
                                                update/register DSC.
                                            </li>
                        
                                            <li>
                                                Open a separate browser tab and type:
                                                https://127.0.0.1:1585
                                            </li>
                        
                                            <li>Click on Advanced.</li>
                        
                                            <li>Proceed to 127.0.0.1 (unsafe).</li>
                        
                                            <li>
                                                Come back to GST portal and refresh the page.
                                            </li>
                        
                                        </ul>
                        
                                    </div>
                        
                                </div>
                        
                            </div>
                        
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="turnover_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
               <h5 class="modal-title">Turnover</h5>
               <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="turnover_amount" class="form-label">Turnover Amount</label>
                  <input type="number" step="0.01" name="turnovers_amount" id="turnovers_amount" class="form-control" required placeholder="Enter Turnover Amount" value="@if($turnover_amount){{$turnover_amount}}@endif">
               </div>
            </div>            
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body close" data-bs-dismiss="modal">CANCEL</button>
               <button type="button" class="ms-3 btn btn-red save_turnover">SUBMIT</button>
            </div>
      </div>
   </div>
</div>
<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <p><h5 class="modal-title">OTP Verification</h5></p>
            <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="form-group">
               <input type="text" class="form-control" id="otp" placeholder="Enter OTP">
               <input type="hidden" id="fgstin">
            </div>
         </div>
         <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-border-body close" data-bs-dismiss="modal">CANCEL</button>
            <button type="button" class="ms-3 btn btn-red verify_otp">SUBMIT</button>
         </div>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')

<script>

    let currentlyOpenRow = null;
    let currentlyClickedRow = null;

    // =========================================
    // COMMON FUNCTIONS
    // =========================================

    function closeOpenedRow() {

        if (currentlyOpenRow) {
            currentlyOpenRow.style.display = 'none';
            currentlyOpenRow = null;
        }

        if (currentlyClickedRow) {
            currentlyClickedRow.classList.remove('no-border');
            currentlyClickedRow = null;
        }
    }

    function toggleRowDetails(index, type = '') {

        let rowId = type ? `details-${type}${index}` : `details-${index}`;

        let onclickSelector = type
            ? `tr[onclick="toggle${type}Details('${type}${index}')"]`
            : `tr[onclick="toggleDetails(${index})"]`;

        const newRow = document.getElementById(rowId);
        const clickedRow = document.querySelector(onclickSelector);

        if (currentlyOpenRow && currentlyOpenRow !== newRow) {
            closeOpenedRow();
        }

        if (newRow && clickedRow) {

            const isHidden = newRow.style.display === 'none';

            newRow.style.display = isHidden ? 'table-row' : 'none';

            clickedRow.classList.toggle('no-border', isHidden);

            currentlyOpenRow = isHidden ? newRow : null;
            currentlyClickedRow = isHidden ? clickedRow : null;
        }
    }

    function toggleSummary(summaryId, checkboxId) {

        const summaryBody = document.getElementById(summaryId);
        const checkbox = document.getElementById(checkboxId);

        if (!summaryBody || !checkbox) return;

        if (checkbox.checked) {

            summaryBody.style.display = '';

        } else {

            summaryBody.style.display = 'none';

            closeOpenedRow();
        }
    }

    // =========================================
    // DETAIL TOGGLES
    // =========================================

    function toggleDetails(index) {
        toggleRowDetails(index);
    }

    function toggleCreditNoteDetails(index) {
        toggleRowDetails(index.replace('cdnr', ''), 'cdnr');
    }

    function toggleDebitNoteDetails(index) {
        toggleRowDetails(index.replace('dnr', ''), 'dnr');
    }

    // =========================================
    // SUMMARY TOGGLES
    // =========================================

    function togglePartySummary() {
        toggleSummary('party-summary', 'detailedCheckbox');
    }

    function toggleCreditNoteSummary() {
        toggleSummary('credit-note-summary', 'creditNoteDetailedCheckbox');
    }

    function toggleDebitNoteSummary() {
        toggleSummary('debit-note-summary', 'debitNoteDetailedCheckbox');
    }

    // =========================================
    // TURNOVER
    // =========================================

    var turnover_amount = @json($turnover_amount);

    $(".add_turnover").click(function () {

        $("#turnover_modal").modal('toggle');
    });

    $(".save_turnover").click(function () {

        let amount = $("#turnovers_amount").val();

        if (amount == "") {
            alert("Please Enter Turnover Amount");
            return;
        }

        $.ajax({

            url: '{{url("store-turnover")}}',
            async: false,
            type: 'POST',
            dataType: 'JSON',

            data: {
                _token: '<?php echo csrf_token() ?>',
                amount: amount,
                merchant_gst: '<?php echo $merchant_gst; ?>',
                company_id: '<?php echo $company_id; ?>',
                fy: '<?php echo $fy; ?>',
            },

            success: function (data) {

                if (data.status == true) {

                    alert(data.message);

                    $("#show_to").html(
                        'Gross Turnover : ' +
                        amount +
                        ' (FY : @php echo $fy; @endphp)'
                    );

                    turnover_amount = amount;

                    $("#turnover_modal").modal('toggle');

                } else {

                    alert("Something Went Wrong.");
                }
            }
        });
    });

    // =========================================
    // SEND TO GST PORTAL
    // =========================================

    $('#gst_portal_form').on('submit', function (e) {

        e.preventDefault();

        if (turnover_amount == "") {

            alert("Please Add Gross Turnover");

            return;
        }

        let form = $(this);

        $.ajax({

            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),

            success: function (response) {
                $("#download_json_status").val(0);
                if (response != "") {
                    
                    let obj = JSON.parse(response);
                    
                    if (obj.status === true && obj.message === 'TOKEN-OTP') {
                        $('#fgstin').val($("#merchant_gst").val());
                        $('#otpModal').modal('show');

                    }else if(obj.status === true && obj.message === 'Json File'){
                        let dataStr = JSON.stringify(obj.data, null, 2);
                        let blob = new Blob([dataStr], {
                            type: 'application/json'
                        });

                        let url = window.URL.createObjectURL(blob);

                        let a = document.createElement('a');
                        a.href = url;
                        a.download = 'gstr1.json';
                        document.body.appendChild(a);
                        a.click();

                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);

                    }else if (obj.status == true) {

                        alert(obj.message);

                    } else {

                        alert(obj.message || "Something went wrong.");
                    }

                } else {

                    alert("Something went wrong.");
                }
            },

            error: function (xhr) {

                console.log(xhr.responseText);

                alert("Something went wrong.");
            }
        });
    });

    // =========================================
    // FORMAT NUMBER
    // =========================================

    function formatIndianNumber(x) {

        x = parseFloat(x || 0).toFixed(2);

        let parts = x.split(".");

        let lastThree = parts[0].substring(parts[0].length - 3);

        let otherNumbers = parts[0].substring(0, parts[0].length - 3);

        if (otherNumbers != '') {
            lastThree = ',' + lastThree;
        }

        let res = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ",");

        return res + lastThree + "." + parts[1];
    }

    // =========================================
    // GENERATE SUMMARY
    // =========================================

    $('#generateSummaryBtn').click(function () {

        let merchant_gst = $('input[name="merchant_gst"]').val();
        let company_id = $('input[name="company_id"]').val();
        let from_date = $('input[name="from_date"]').val();
        let to_date = $('input[name="to_date"]').val();

        let summaryKey =
            `gstr1_summary_${merchant_gst}_${from_date}_${to_date}`;

        $('#generateSummaryBtn')
            .prop('disabled', true)
            .html('Loading...');

        $.ajax({

            url: "{{ route('gstr1.summary') }}",
            type: "GET",

            data: {
                merchant_gst,
                company_id,
                from_date,
                to_date
            },

            success: function (response) {

                $('#generateSummaryBtn')
                    .prop('disabled', false)
                    .html('Generate Summary');

                if (!response.status) {

                    alert(response.message);

                    return;
                }

                let sections = response.data || [];

                let html = '';

                let sectionMap = {

                    'B2B_4A': '4A - Taxable outward supplies made to registered persons (other than reverse charge)',
                    'B2B_4B': '4B - Supplies attracting reverse charge',
                    'B2CL': '5A - B2CL (Large)',
                    'B2CS': '7 - B2CS',
                    'EXP': '6A - Exports',
                    'CDNR': '9B - Credit / Debit Notes (Registered)',
                    'CDNUR': '9B - Credit / Debit Notes (Unregistered)',
                    'HSN': '12 - HSN Summary',
                    'DOC_ISSUE': '13 - Documents Issued'
                };

                sections.forEach(function (item) {

                    if (!sectionMap[item.sec_nm]) {
                        return;
                    }

                    html += `
                    <div class="summary-box">

                        <div class="summary-header">
                            ${sectionMap[item.sec_nm]}
                        </div>

                        <table class="summary-table">

                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>No. of Records</th>
                                    <th>Value (₹)</th>
                                    <th>Taxable Value (₹)</th>
                                    <th>IGST (₹)</th>
                                    <th>CGST (₹)</th>
                                    <th>SGST (₹)</th>
                                    <th>Cess (₹)</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr class="summary-total">
                                    <td>Total</td>
                                    <td class="text-end">${item.ttl_rec ?? 0}</td>
                                    <td class="text-end">${formatIndianNumber(item.ttl_val)}</td>
                                    <td class="text-end">${formatIndianNumber(item.ttl_tax)}</td>
                                    <td class="text-end">${formatIndianNumber(item.ttl_igst)}</td>
                                    <td class="text-end">${formatIndianNumber(item.ttl_cgst)}</td>
                                    <td class="text-end">${formatIndianNumber(item.ttl_sgst)}</td>
                                    <td class="text-end">${formatIndianNumber(item.ttl_cess)}</td>
                                </tr>
                    `;

                    if (item.sub_sections) {

                        item.sub_sections.forEach(function (sub) {

                            html += `
                                <tr class="summary-sub-row">
                                    <td>${sub.sec_nm ?? sub.typ}</td>
                                    <td class="text-end">${sub.ttl_rec ?? 0}</td>
                                    <td class="text-end">${formatIndianNumber(sub.ttl_val)}</td>
                                    <td class="text-end">${formatIndianNumber(sub.ttl_tax)}</td>
                                    <td class="text-end">${formatIndianNumber(sub.ttl_igst)}</td>
                                    <td class="text-end">${formatIndianNumber(sub.ttl_cgst)}</td>
                                    <td class="text-end">${formatIndianNumber(sub.ttl_sgst)}</td>
                                    <td class="text-end">${formatIndianNumber(sub.ttl_cess)}</td>
                                </tr>
                            `;
                        });
                    }

                    html += `
                            </tbody>

                        </table>

                    </div>
                    `;
                });

                $('#gstr1-summary-container').html(html);
                // Show Proceed To File button
                $('#proceedToFilingBtn').show();

                localStorage.setItem(summaryKey, html);

                $('#fill-tab-2').tab('show');
            },

            error: function () {

                $('#generateSummaryBtn')
                    .prop('disabled', false)
                    .html('Generate Summary');

                alert('Unable to fetch summary');
            }
        });
    });

    // =========================================
    // RESET GSTR1
    // =========================================

    $('#resetGstr1Btn').click(function () {

        if (!confirm('Are you sure you want to reset GSTR-1 data from GST Portal?')) {
            return;
        }

        let merchant_gst = $('input[name="merchant_gst"]').val();
        let company_id = $('input[name="company_id"]').val();
        let from_date = $('input[name="from_date"]').val();
        let to_date = $('input[name="to_date"]').val();

        let summaryKey =
            `gstr1_summary_${merchant_gst}_${from_date}_${to_date}`;

        $('#resetGstr1Btn')
            .prop('disabled', true)
            .text('Resetting...');

        $.ajax({

            url: "{{ route('gstr1.reset') }}",
            type: "POST",

            data: {
                _token: "{{ csrf_token() }}",
                merchant_gst,
                company_id,
                from_date,
                to_date
            },

            success: function (response) {

                $('#resetGstr1Btn')
                    .prop('disabled', false)
                    .text('Reset GSTR-1');

                console.log(response);

                if (response.status == true) {

                    localStorage.removeItem(summaryKey);

                    $('#gstr1-summary-container').html('');
                    // Hide Proceed To File button after reset
                    $('#proceedToFilingBtn').hide();

                    alert(response.message);

                } else {

                    alert(response.message);
                }
            },

            error: function (xhr) {

                $('#resetGstr1Btn')
                    .prop('disabled', false)
                    .text('Reset GSTR-1');

                console.log(xhr);

                alert('Something went wrong');
            }
        });
    });

    // =========================================
    // LOAD SAVED SUMMARY
    // =========================================

    $(document).ready(function () {

        let merchant_gst = $('input[name="merchant_gst"]').val();
        let from_date = $('input[name="from_date"]').val();
        let to_date = $('input[name="to_date"]').val();

        let summaryKey =
            `gstr1_summary_${merchant_gst}_${from_date}_${to_date}`;

        let savedSummary = localStorage.getItem(summaryKey);

        if (savedSummary) {

            $('#gstr1-summary-container').html(savedSummary);
            $('#proceedToFilingBtn').show();
            $('#fill-tab-2').tab('show');
        }
    });
    
    
    // =========================================
// PROCEED TO FILING
// =========================================

$(document).on('click', '#proceedToFilingBtn', function () {

    // Open Filing Tab
    $('#fill-tab-3').tab('show');

    // Smooth scroll top
    $('html, body').animate({
        scrollTop: 0
    }, 300);
});
// OTP verification submit
    $('.verify_otp').on('click', function() {
        let otp = $('#otp').val();
        let fgstin = $('#fgstin').val();

        if (!otp) {
            alert("Please enter OTP");
            return;
        }

        $.ajax({
            url: "{{route('verify-gst-token-otp')}}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                otp: otp,
                gstin: fgstin
            },
            success: function(res) {
            
                if(res!=""){
                  let obj = JSON.parse(res);
                  if(obj.status==true){
                         $('#gst_portal_form').submit();
                        $('#otpModal').modal('hide');
                  }else{
                     alert(obj.message);
                  }
               }else{
                  alert("Something Went Wrong.Please Try Again.");
               }
            },
            error: function() {
                alert("Error verifying OTP");
            }
        });
    });

    $("#download_json").click(function(){
        $("#download_json_status").val(1);
        $('#gst_portal_form').submit();
    });
</script>

@endsection