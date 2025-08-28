@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
   @include('layouts.leftnav')

   <!-- Add a column for the main content -->
   <div class="col-md-10 col-sm-12 px-4 bg-mint">
      <div class="container-fluid">
         <!-- Header section -->
<div class="container">
    <div class="d-flex justify-content-between align-items-center px-2 py-0.1 mb-4 mt-4" style=" background-color: #1ac6c6;
      border-top-left-radius: 0.375rem;
      border-top-right-radius: 0.375rem; ">
    <h2 class="mb-4" style="color:white; font-size:18px; "> GST B2C Large Invoice - Detailed Summary</h2>
     </div>
   

   
    <table class="table table-bordered table-striped">
        <thead style="background-color: #003366;">
            <tr>
                <th style="color: white;">Invoice Number</th>
                <th style="color: white;">Invoice Date</th>
                <th style="color: white;">Invoice Value</th>
                <th style="color: white;">Place of Supply</th>
                <th style="color: white;">Applicable % Tax Rate</th>
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

              @if(count($data) > 0)
            @foreach($data as $row)
                    @php
                        $total_taxable_value += $row['taxable_value'];
                        $total_igst += $row['igst'];
                        $total_cgst += $row['cgst'];
                        $total_sgst += $row['sgst'];
                        $total_invoice_value = $total_taxable_value + $total_igst + $total_cgst + $total_sgst ;
                    @endphp
             <tr class="clickable-row" data-href="{{ url('sale-invoice/' . $row['sales_id']) }}">
                <td>{{ $row['voucher_no_prefix'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                <td>{{ formatIndianNumber($row['total'], 2) }}</td>
                <td>{{ $row['POS'] }}</td>
                <td>{{ $row['rate'] }}%</td>
                <td>{{ formatIndianNumber($row['taxable_value'], 2) }}</td>
                <td>{{ formatIndianNumber($row['cgst'], 2) }}</td>
                <td>{{ formatIndianNumber($row['sgst'], 2) }}</td>
                <td>{{ formatIndianNumber($row['igst'], 2) }}</td>
            </tr>
            @endforeach
             <tr style="background-color:rgb(58, 95, 150);">
                    <th colspan="2"  style="text-align:center;color: white;">Total Invoice Value</th>
                    <th style="color: white;">{{ formatIndianNumber($total_invoice_value) }}</th>
                    <th colspan="2"  style="text-align:center; color: white;">Total</th>
                    <th style="color: white;" >{{ formatIndianNumber($total_taxable_value) }}</th>
                    <th style="color: white;" >{{ formatIndianNumber($total_cgst) }}</th>
                    <th style="color: white;">{{ formatIndianNumber($total_sgst) }}</th>
                    <th style="color: white;">{{ formatIndianNumber($total_igst) }}</th>
                </tr>
                @else
                <tr>
                    <td colspan="15" class="text-center">No B2C Large invoices found for the selected period.</td>
                </tr>
            @endif
        </tbody>
    </table>
   
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
</script>
@endsection
@section('styles')
<style>
       /* 👇 Hover effect on table rows */
.clickable-row:hover {
    background-color: #cce5ff !important; /* Light blue */
    cursor: pointer;
}
</style>
@endsection

