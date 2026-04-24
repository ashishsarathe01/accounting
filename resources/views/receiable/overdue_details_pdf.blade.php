<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top:20px; }
        th, td { border: 1px solid black; padding: 8px; font-size: 13px; }
        th { background: #eee; text-align: center; }
        td.text-center { text-align: center; }
        td.text-right { text-align: right; }
        h3, h4 { margin: 0; padding: 0; text-align: center; }
    </style>
</head>

<body>

<h3>{{ Session::get('company_name') }}</h3>
<h4>{{ $acc->account_name }} Overdue Bills Report</h4>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Bill No</th>
            <th style="text-align: right;" >Overdue Amount</th>
        </tr>
    </thead>

    <tbody>
        @php $total_overdue = 0; @endphp
        @foreach ($allocated as $b)
         @php $total_overdue += $b['overdue']; @endphp
            <tr>
                <td class="text-center">
                    {{ \Carbon\Carbon::parse($b['date'])->format('d-m-Y') }}
                </td>

                <td class="text-center">
                    {{ $b['bill_no'] }}
                </td>

                <td class="text-right">
                    {{ formatIndianNumber($b['overdue'], 2) }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tr>
            <td colspan="2" class="text-center fw-bold">Total</td>
            <td class="text-right fw-bold">
                {{ formatIndianNumber($total_overdue, 2) }}
            </td>
        </tr>
</table>

</body>
</html>
