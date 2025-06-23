@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Document Summary </h4>
    <p><strong>Period:</strong> {{ $from_date }} to {{ $to_date }}</p>
    <br>
   <h4 style="color:blue;">Invoices for Outward Supply</h4>
    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr style="background-color: #003366;">
                <th style="color: white;">No.</th>
                <th style="color: white;">Sr. No. (From)</th>
                <th style="color: white;">Sr. No. (To)</th>
                <th style="color: white;">Total Number</th>
                <th style="color: white;">Cancelled</th>
                <th style="color: white;">Net Issued</th>
            </tr>
        </thead>
        <tbody>
            @forelse($SalesdocumentSummary as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['from'] }}</td>
                    <td>{{ $row['to'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['cancelled'] }}</td>
                    <td>{{ $row['net_issued'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No records found for selected period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="container mt-4">
    <h4 style="color:blue;" class="mb-3">Debit Notes </h4>
   

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr style="background-color: #003366;">
                <th style="color: white;">No.</th>
                <th style="color: white;">Sr. No. (From)</th>
                <th style="color: white;">Sr. No. (To)</th>
                <th style="color: white;">Total Number</th>
                <th style="color: white;">Cancelled</th>
                <th style="color: white;">Net Issued</th>
            </tr>
        </thead>
        <tbody>
            @forelse($DebitNotedocumentSummary as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['from'] }}</td>
                    <td>{{ $row['to'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['cancelled'] }}</td>
                    <td>{{ $row['net_issued'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No debit notes found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>



<div class="container mt-4">
    <h4 style="color:blue;" class="mb-3">Credit Notes </h4>
   

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr style="background-color: #003366;">
                <th style="color: white;">No.</th>
                <th style="color: white;">Sr. No. (From)</th>
                <th style="color: white;">Sr. No. (To)</th>
                <th style="color: white;">Total Number</th>
                <th style="color: white;">Cancelled</th>
                <th style="color: white;">Net Issued</th>
            </tr>
        </thead>
        <tbody>
            @forelse($CreditNotedocumentSummary as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['from'] }}</td>
                    <td>{{ $row['to'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['cancelled'] }}</td>
                    <td>{{ $row['net_issued'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No debit notes found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>



<div class="container mt-4">
    <h4 style="color:blue;" class="mb-3">Payments </h4>
   

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr style="background-color: #003366;">
                <th style="color: white;">No.</th>
                <th style="color: white;">Sr. No. (From)</th>
                <th style="color: white;">Sr. No. (To)</th>
                <th style="color: white;">Total Number</th>
                <th style="color: white;">Cancelled</th>
                <th style="color: white;">Net Issued</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentsDocumentSummary as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['from'] }}</td>
                    <td>{{ $row['to'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['cancelled'] }}</td>
                    <td>{{ $row['net_issued'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No debit notes found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="container mt-4">
    <h4 style="color:blue;" class="mb-3">Receipts </h4>
   

    <table class="table table-bordered mt-3">
        <thead class="thead-dark">
            <tr style="background-color: #003366;">
                <th style="color: white;">No.</th>
                <th style="color: white;">Sr. No. (From)</th>
                <th style="color: white;">Sr. No. (To)</th>
                <th style="color: white;">Total Number</th>
                <th style="color: white;">Cancelled</th>
                <th style="color: white;">Net Issued</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receiptsDocumentSummary as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['from'] }}</td>
                    <td>{{ $row['to'] }}</td>
                    <td>{{ $row['total'] }}</td>
                    <td>{{ $row['cancelled'] }}</td>
                    <td>{{ $row['net_issued'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">No debit notes found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
