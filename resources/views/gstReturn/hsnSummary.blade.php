@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">Hsn-wise Summary</h3>
    <table class="table table-bordered table-striped">
        <thead>
        <tr style="background-color: #003366;">
            <th style="color: white;">S. no.</th>
            <th style="color: white;">Hsn Code</th>
            <th style="color: white;">Description</th>
            <th style="color: white;">UQC</th>
            <th style="color: white;">Total Quantity</th>
            <th style="color: white;">Rate of Tax</th>
            <th style="color: white;">Total Taxable Value</th>
            <th style="color: white;">CGST</th>
            <th style="color: white;">SGST</th>
            <th style="color: white;">IGST</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row['hsn'] }}</td>
                <td>..</td>
                <td>{{ $row['unit_code'] }} </td>
                <td>{{ $row['qty'] }}</td>
                <td>{{ $row['rate'] }}%</td>
                <td>{{ number_format($row['taxable_value'], 2) }}</td>
                <td>{{ number_format($row['cgst'], 2) }}</td>
                <td>{{ number_format($row['sgst'], 2) }}</td>
                <td>{{ number_format($row['igst'], 2) }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">No Data Available</td></tr>
        @endforelse
    </tbody>
</table>

</div>
@endsection