@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">GST B2B Detailed Summary</h2>
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
             @if(count($grouped) > 0)
            @foreach($grouped as $row)
            <tr>
                <td>{{ $row['billing_gst'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['voucher_no_prefix'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['invoice_date'])->format('d-m-Y') }}</td>
                <td>{{ number_format($row['total'], 2) }}</td>
                <td>{{ $row['POS'] }}</td>
                <td>{{ $row['reverse_charge'] }}</td>
                <td>..</td>
                <td>Regular</td>
                <td>..</td>
                <td>{{ $row['rate'] }}%</td>
                <td>{{ number_format($row['taxable_value'], 2) }}</td>
                <td>{{ number_format($row['cgst'], 2) }}</td>
                <td>{{ number_format($row['sgst'], 2) }}</td>
                <td>{{ number_format($row['igst'], 2) }}</td>
            </tr>
            @endforeach
             @else
      <tr>
           
    <td colspan="15" class="text-center">No B2B invoices found for the selected period.</td>
</tr>

        

    @endif
        </tbody>
    </table>
    
</div>
@endsection


