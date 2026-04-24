<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top:20px; }
        th, td { border: 1px solid black; padding: 8px; font-size: 13px; }
        th { background: #eee; }
        h3,h4 { margin: 0; padding: 0; }
    </style>
</head>

<body>

<h3>{{ Session::get('company_name') }}</h3>
<h4 class="mt-2 text-center font-weight-bold">
    {{ $acc->account_name }} Overdue Bills Report
</h4>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Bill No</th>
            <th>Overdue Amount</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($allocated as $b)
        <tr>
            <td>
    {{ strtotime($b['date']) 
        ? \Carbon\Carbon::parse($b['date'])->format('d-m-Y') 
        : 'Opening Balance' 
    }}
</td>
            <td>{{ $b['bill_no'] }}</td>
            <td>{{ formatIndianNumber($b['overdue'],2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
