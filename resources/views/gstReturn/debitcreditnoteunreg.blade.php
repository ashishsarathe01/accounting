@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Credit/Debit Registered Detailed Summary</h2>
  
    <table class="table table-bordered table-striped">
        <thead>
            <tr style="background-color: #003366;">
                <th style="color: white;">UR Type</th>
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
              @if(count($grouped) > 0)
            @foreach($grouped as $row)
            <tr>
                <td> B2CL</td>  
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['sr_prefix'] }}</td>
                <td>{{ \Carbon\Carbon::parse($row['note_date'])->format('d-m-Y') }}</td>
                <td>{{ formatIndianNumber($row['total']) }}</td>
                <td> {{ $row['note_type'] }}</td>
                <td>{{ $row['POS'] }}</td>
                  <td>N</td>
                  <td>..</td>  
                <td>{{ $row['rate'] }}%</td>
                <td>{{ formatIndianNumber($row['taxable_value']) }}</td>
                <td>{{ formatIndianNumber($row['cgst']) }}</td>
                <td>{{ formatIndianNumber($row['sgst']) }}</td>
                <td>{{ formatIndianNumber($row['igst']) }}</td>
            </tr>
            @endforeach
                   @else
      <tr> 
    <td colspan="13" class="text-center">No B2B invoices found for the selected period.</td>
</tr>
    @endif
        </tbody>
    </table>
   
</div>
@endsection