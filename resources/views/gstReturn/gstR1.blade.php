@extends('layouts.app')
@section('content')

    @php
        $cards = [
            ['title' => '4A, 4B, 6B, 6C - B2B, SEZ, DE Invoices', 'route' => route('gst.b2b.detailed.billwise', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '5 - B2C (Large) Invoices', 'route' => route('gst.b2c.large.detailed', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '6A - Exports Invoices', 'route' => '#'],
            ['title' => '7 - B2C (Others)', 'route' => route('gst.b2c.normal.statewise', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '8A, 8B, 8C, 8D - Nil Rated Supplies', 'route' => route('nilratedreginter', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '9B - Credit / Debit Notes (Registered)', 'route' => route('debitNote', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '9B - Credit / Debit Notes (Unregistered)', 'route' => route('debitNoteUnreg', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '11A(1), 11A(2) - Tax Liability (Advances Received)', 'route' => '#'],
            ['title' => '11B(1), 11B(2) - Adjustment of Advances', 'route' => '#'],
            ['title' => '12 - HSN-wise summary of outward supplies', 'route' => route('hsnSummary', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '13 - Documents Issued', 'route' => route('docIssued', compact('merchant_gst', 'company_id', 'from_date', 'to_date'))],
            ['title' => '14 - Supplies made through ECO', 'route' => '#'],
            ['title' => '15 - Supplies U/s 9(5)', 'route' => '#'],
        ];
    @endphp

<div class="container mt-4">
    <div class="card mb-3 shadow-sm border-0">
        <!-- Combined Header and Info in One Card -->
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
                    <div class="col-md-4"><strong>Legal Name -</strong> Gopal Dutt</div>
                    <div class="col-md-4"><strong>Trade Name -</strong>  {{$comp_details->company_name}}</div>
                    <div class="col-md-4"><strong>FY -</strong> 20{{$fy}}</div>
                    <div class="col-md-4"><strong>Tax Period -</strong> {{$from_date}} to {{$to_date}}</div>
                    <div class="col-md-4"><strong>Status -</strong> Not Filed</div>
                    <div class="col-md-4 text-danger">
                        <i class="fas fa-circle text-danger me-1" style="font-size: 8px;"></i> * Indicates Mandatory Fields
                    </div>
                    <div class="col-md-4"><strong>Due Date -</strong> 11/07/2025</div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Nil GSTR-1 -->
    <div class="bg-white py-2 px-3 border mb-3">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="fileNil">
            <label class="form-check-label fw-semibold" for="fileNil">File Nil GSTR-1</label>
        </div>
    </div>

    <!-- Section Header -->
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
    <img src="//static.gst.gov.in/uiassets/images/processed.png" 
         class="check-icon me-2" 
         alt="check icon" >
    <span class="count-value">0</span>
  </div>
</div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

@endsection


@section('styles')
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
</style>

@endsection





