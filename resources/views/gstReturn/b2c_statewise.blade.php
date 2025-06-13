@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">B2C Normal Sales (State-wise)</h3>
    <table class="table table-bordered table-striped">
        <thead>
        <tr style="background-color: #003366;">
            <th style="color: white;">State</th>
            <th style="color: white;">GST Rate</th>
            <th style="color: white;">Taxable Value</th>
            <th style="color: white;">CGST</th>
            <th style="color: white;">SGST</th>
            <th style="color: white;">IGST</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $row)
            <tr>
                <td>{{ $row['state'] }}</td>
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
