@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">GST B2C Large Invoice - Detailed Summary</h2>

    @if(count($data) > 0)
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
            @foreach($data as $row)
            <tr>
                <td>{{ $row['voucher_no_prefix'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                <td>{{ number_format($row['total'], 2) }}</td>
                <td>{{ $row['POS'] }}</td>
                <td>{{ $row['rate'] }}%</td>
                <td>{{ number_format($row['taxable_value'], 2) }}</td>
                <td>{{ number_format($row['cgst'], 2) }}</td>
                <td>{{ number_format($row['sgst'], 2) }}</td>
                <td>{{ number_format($row['igst'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
        <div class="alert alert-info">
            No B2C Large invoices found for the selected period.
        </div>
    @endif
</div>
@endsection

