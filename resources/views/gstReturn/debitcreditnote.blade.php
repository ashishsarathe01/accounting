@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Credit/Debit Registered Detailed Summary</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr style="background-color: #003366;">
                <th style="color: white;">GSTIN/UIN OF RECIPIENT</th>
                <th style="color: white;">RECEIVER NAME</th>
                <th style="color: white;">NOTE NUMBER</th>
                <th style="color: white;">NOTE DATE</th>
                <th style="color: white;">NOTE VALUE</th>
                <th style="color: white;">NOTE TYPE</th>
                <th style="color: white;">PLACE OF SUPPY</th>
                <th style="color: white;">REVERSE CHARGE</th>
                <th style="color: white;">Applicable % Tax Rate</th>
                <th style="color: white;">Rate</th>
                <th style="color: white;">Taxable Value</th>
                <th style="color: white;">CGST</th>
                <th style="color: white;">SGST</th>
                <th style="color: white;">IGST</th>
            </tr>
        </thead>
        <tbody>
            @foreach($grouped as $row)
            <tr>
                <td>{{ $row['billing_gst'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['sr_prefix'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['note_date'])->format('d-m-Y') }}</td>
                <td>{{ number_format($row['total'], 2) }}</td>
                <td> {{ $row['note_type'] }}</td>
                <td>{{ $row['POS'] }}</td>
                  <td>N</td>
                  <td>..</td>  
                <td>{{ $row['rate'] }}%</td>
                <td>{{ number_format($row['taxable_value'], 2) }}</td>
                <td>{{ number_format($row['cgst'], 2) }}</td>
                <td>{{ number_format($row['sgst'], 2) }}</td>
                <td>{{ number_format($row['igst'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection