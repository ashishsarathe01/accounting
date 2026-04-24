<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans; font-size: 11px; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background: #f2f2f2;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    {{-- Company --}}
    <div class="text-center">
        <div class="title">{{ $company->company_name ?? '' }}</div>
        <div>{{ $company->address ?? '' }}</div>
        <div>CIN: {{ $company->cin ?? '' }}</div>
    </div>

    <br>

    {{-- Title --}}
    <div class="text-center title">
        Account Summary : {{ $account->account_name }}
    </div>


    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Opening</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Closing</th>
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $row)
                <tr>
                    <td>{{ $row[0] }}</td>
                    <td class="text-end">{{ $row[1] }}</td>
                    <td class="text-end">{{ $row[2] }}</td>
                    <td class="text-end">{{ $row[3] }}</td>
                    <td class="text-end">{{ $row[4] }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <th class="text-end">TOTAL</th>
                <th class="text-end">{{ number_format($totalOpening,2) }}</th>
                <th class="text-end">{{ number_format($totalDebit,2) }}</th>
                <th class="text-end">{{ number_format($totalCredit,2) }}</th>
                <th class="text-end">{{ number_format($totalClosing,2) }}</th>
            </tr>
        </tfoot>
    </table>

</body>
</html>