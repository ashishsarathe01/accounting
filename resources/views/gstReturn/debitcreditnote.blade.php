@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
<div class="container col-md-10 justify-content-between py-4 px-2 align-items-center" style="background-color: #F6F7FF;">
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
                <th style="color: white;">PLACE OF SUPPLY</th>
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
            @php 
                $total_note_value = 0;
                $total_taxable_value = 0;
                $total_igst = 0;
                $total_cgst = 0;
                $total_sgst = 0;
            @endphp

            @foreach($grouped as $row)
                @php
                    if ($row['note_type'] === 'C') {
                        $total_note_value    -= $row['total'];
                        $total_taxable_value -= $row['taxable_value'];
                        $total_igst          -= $row['igst'];
                        $total_cgst          -= $row['cgst'];
                        $total_sgst          -= $row['sgst'];
                    } else {
                        $total_note_value    += $row['total'];
                        $total_taxable_value += $row['taxable_value'];
                        $total_igst          += $row['igst'];
                        $total_cgst          += $row['cgst'];
                        $total_sgst          += $row['sgst'];
                    }
                @endphp
                <tr>
                    <td>{{ $row['billing_gst'] }}</td>
                    <td>{{ $row['name'] }}</td>
                    <td>{{ $row['sr_prefix'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($row['note_date'])->format('d-m-Y') }}</td>
                    <td>{{ number_format($row['total'], 2) }}</td>
                    <td>{{ $row['note_type'] }}</td>
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

            <tr style="font-weight: bold; background-color: #003366;">
                <td style="color: white;" colspan="4" class="text-end">TOTAL</td>
                <td style="color: white;" >{{ number_format($total_note_value, 2) }}</td>
                <td colspan="5"></td>
                <td style="color: white;">{{ number_format($total_taxable_value, 2) }}</td>
                <td style="color: white;">{{ number_format($total_cgst, 2) }}</td>
                <td style="color: white;">{{ number_format($total_sgst, 2) }}</td>
                <td style="color: white;">{{ number_format($total_igst, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>

</div>
   </section>
</div>
</body>

@include('layouts.footer')

@endsection
