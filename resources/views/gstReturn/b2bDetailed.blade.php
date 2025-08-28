@extends('layouts.app')
@section('content')
@include('layouts.header')
<style type="text/css">
.select2-container--default .select2-selection--single {
    height: 48px !important; /* Increase this value as needed */
    padding: 8px 12px;
    font-size: 16px;
    border: 1px solid #ced4da; /* Match Bootstrap border */
    border-radius: 0.375rem;    /* Match Bootstrap rounded corners */
}

/* Vertically center the text inside */
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 30px !important;
    padding-top: 5px;
}

/* Adjust dropdown arrow alignment */
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 48px !important;
    top: 0px !important;
}
   /* 👇 Hover effect on table rows */
.clickable-row:hover td {
    background-color: #cce5ff !important; /* Light blue */
    cursor: pointer;
}
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
   @include('layouts.leftnav')
   

   <!-- Add a column for the main content -->
   <div class="col-md-10 col-sm-12 px-4 bg-mint">
      <div class="container-fluid">
         <!-- Header section -->
         <div class="d-flex justify-content-between align-items-center px-3 py-2 mb-4 mt-4"
              style="background-color: #1ac6c6;
                     border-top-left-radius: 0.375rem;
                     border-top-right-radius: 0.375rem;">
            <h2 class="mb-0" style="color:white; font-size:18px;">B2B Detailed Summary</h2>
                  <form method="POST" action="{{ route('gstr1.send') }}">
                  @csrf
                  <input type="hidden" name="merchant_gst" value="{{ $merchant_gst }}">
                  <input type="hidden" name="company_id" value="{{ $company_id }}">
                  <input type="hidden" name="from_date" value="{{ $from_date }}">
                  <input type="hidden" name="to_date" value="{{ $to_date }}">
                  <button class="btn btn-primary align-left" type="submit">Send to GST Portal</button>
               </form>
            <form id="quitForm" method="POST" action="{{ url('gstr1') }}">
    @csrf
     <input type="hidden" name="series" value="{{ $merchant_gst }}">
            <input type="hidden" name="from_date" value="{{ $from_date }}">
            <input type="hidden" name="to_date" value="{{ $to_date }}">
    <button type="submit" class="btn btn-danger">QUIT</button>
</form>


         </div>

         <!-- Filter Form -->
         <form method="GET" action="{{ route('gst.b2b.detailed.billwise') }}" class="row g-3 mb-3">
            <!-- Hidden Inputs -->
            <input type="hidden" name="merchant_gst" value="{{ request('merchant_gst') }}">
            <input type="hidden" name="company_id" value="{{ request('company_id') }}">
            <input type="hidden" name="from_date" value="{{ request('from_date') }}">
            <input type="hidden" name="to_date" value="{{ request('to_date') }}">

            <!-- Form Fields -->
            <div class="col-md-3">
               <label class="form-label">Recipient Name</label>
               <select name="name" class="form-select select2-single">
                  <option value="">-- Select Recipient Name --</option>
                  @foreach($accountDropdown->whereNotNull('gstin')->where('gstin', '!=', '')->unique('gstin') as $account)
                     <option value="{{ $account->account_name }}" {{ request('name') == $account->account_name ? 'selected' : '' }}>
                        {{ $account->account_name }}
                     </option>
                  @endforeach
               </select>
            </div>

            <div class="col-md-3">
               <label class="form-label">Recipient GSTIN</label>
               <select name="billing_gst" class="form-select select2-single">
                  <option value="">-- Select GSTIN --</option>
                  @foreach($accountDropdown->whereNotNull('gstin')->where('gstin', '!=', '')->unique('gstin') as $account)
                     <option value="{{ $account->gstin }}" {{ request('billing_gst') == $account->gstin ? 'selected' : '' }}>
                        {{ $account->gstin }}
                     </option>
                  @endforeach
               </select>
            </div>

            <div class="col-md-3">
               <label class="form-label">Invoice Date</label>
               <input type="date" name="invoice_date" class="form-control" value="{{ request('invoice_date') }}">
            </div>

            <div class="col-md-3">
               <label class="form-label">Tax Rate</label>
               <select name="rate" class="form-select select2-single">
                  <option value="">-- Select Rate --</option>
                  <option value="5" {{ request('rate') == '5' ? 'selected' : '' }}>5%</option>
                  <option value="12" {{ request('rate') == '12' ? 'selected' : '' }}>12%</option>
                  <option value="18" {{ request('rate') == '18' ? 'selected' : '' }}>18%</option>
                  <option value="28" {{ request('rate') == '28' ? 'selected' : '' }}>28%</option>
               </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
               <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>

            <div class="col-md-2 d-flex align-items-end">
               <a href="{{ route('gst.b2b.detailed.billwise', [
                  'merchant_gst' => request('merchant_gst'),
                  'company_id' => request('company_id'),
                  'from_date' => request('from_date'),
                  'to_date' => request('to_date'),
               ]) }}" class="btn btn-secondary w-100">
                  Remove Filters
               </a>
            </div>
         </form>

         <!-- Table -->
         <div class="table-responsive">
            <table class="table table-bordered table-striped">
               <thead>
                  <tr style="background-color: #003366;">
                     <th style="color: white;">GSTIN/UIN OF RECIPIENT</th>
                     <th style="color: white;">RECIPIENT NAME</th>
                     <th style="color: white;">INVOICE NUMBER</th>
                     <th style="color: white;">INVOICE DATE</th>
                     <th style="color: white;">INVOICE VALUE</th>
                     <th style="color: white;">PLACE OF SUPPY</th>
                     <th style="color: white;">REVERSE CHARGE</th>
                     <th style="color: white;">Applicable % Tax Rate</th>
                     <th style="color: white;">Invoice Type</th>
                     <th style="color: white;">E-commerce GSTIN</th>
                     <th style="color: white;">Rate</th>
                     <th style="color: white;">Taxable Value</th>
                     <th style="color: white;">CGST</th>
                     <th style="color: white;">SGST</th>
                     <th style="color: white;">IGST</th>
                  </tr>
               </thead>
               <tbody>
                  @php
                     $total_taxable_value = 0;
                     $total_igst = 0;
                     $total_cgst = 0;
                     $total_sgst = 0;
                     $total_invoice_value = 0;
                  @endphp

                  @if(count($grouped) > 0)
                     @foreach($grouped as $row)
                        @php
                           $total_taxable_value += $row['taxable_value'];
                           $total_igst += $row['igst'];
                           $total_cgst += $row['cgst'];
                           $total_sgst += $row['sgst'];
                           $total_invoice_value = $total_taxable_value + $total_igst + $total_cgst + $total_sgst;
                        @endphp
                        <tr class="clickable-row" data-href="{{ url('sale-invoice/' . $row['sales_id']) }}">
                           <td>{{ $row['billing_gst'] }}</td>
                           <td>{{ $row['name'] }}</td>
                           <td>{{ $row['voucher_no_prefix'] }}</td>
                           <td>{{ \Carbon\Carbon::parse($row['invoice_date'])->format('d-m-Y') }}</td>
                           <td>{{ formatIndianNumber($row['total']) }}</td>
                           <td>{{ $row['POS'] }}</td>
                           <td style="text-align:center;">N</td>
                           <td>..</td>
                           <td>Regular</td>
                           <td>..</td>
                           <td>{{ $row['rate'] }}%</td>
                           <td>{{ formatIndianNumber($row['taxable_value']) }}</td>
                           <td>{{ formatIndianNumber($row['cgst']) }}</td>
                           <td>{{ formatIndianNumber($row['sgst']) }}</td>
                           <td>{{ formatIndianNumber($row['igst']) }}</td>
                        </tr>
                     @endforeach
                     <tr style="background-color: #003366;">
                        <th colspan="4" style="text-align:center;color: white;">Total Invoice Value</th>
                        <th style="color: white;">{{ formatIndianNumber($total_invoice_value) }}</th>
                        <th colspan="6" style="text-align:center; color: white;">Total</th>
                        <th style="color: white;">{{ formatIndianNumber($total_taxable_value) }}</th>
                        <th style="color: white;">{{ formatIndianNumber($total_cgst) }}</th>
                        <th style="color: white;">{{ formatIndianNumber($total_sgst) }}</th>
                        <th style="color: white;">{{ formatIndianNumber($total_igst) }}</th>
                     </tr>

                

                  @else
                     <tr>
                        <td colspan="15" class="text-center">No B2B invoices found for the selected period.</td>
                     </tr>
                  @endif
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
</div>
</div>
   </section>
</div>

@include('layouts.footer')
<script>
$(document).ready(function() {
    $('.select2-single').select2({
        width: '100%' // Optional: ensures full width
    });
});

 $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });


    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            event.preventDefault(); // Prevent default ESC behavior
            document.getElementById('quitForm').submit();
        }
    });
</script>
@endsection

