@extends('layouts.app')

@section('content')
<div class="container">
    <h3 class="mb-4">B2C Normal Sales (State-wise)</h3>
   
    <table class="table table-bordered">
    <thead>
        <tr>
            <th>State</th>
            <th>GST Rate</th>
            <th>Taxable Value</th>
            <th>CGST</th>
            <th>SGST</th>
            <th>IGST</th>
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
