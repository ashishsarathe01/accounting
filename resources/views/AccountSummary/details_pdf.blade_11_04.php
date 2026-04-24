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

        .section {
            background: #eee;
            font-weight: bold;
        }
    </style>
</head>

<body>

    {{-- Company --}}
    <div class="text-center">
        <div class="title">{{ $company->company_name ?? '' }}</div>
        <div>{{ $company->address ?? '' }}</div>
        <div>CIN : {{ $company->cin ?? '' }}</div>
        <div>From Date : {{ date('d-m-Y',strtotime($from)) }} To Date : {{ date('d-m-Y',strtotime($to)) }}</div>
    </div>

    <br>

    {{-- Title --}}
    <div class="text-center title">
        Account Summary : {{ $title }}
    </div>


    <table>
        <thead>
            <tr>
                <th>Account / Group</th>
                <th>Type</th>
                <th>Opening</th>
                <th>Debit</th>
                <th>Credit</th>
                <th>Closing</th>
            </tr>
        </thead>

        <tbody>
            @foreach($rows as $row)

                @if($row[0] === 'Accounts')
                    <tr class="section">
                        <td colspan="6">Accounts</td>
                    </tr>
                @else
                    <tr>
                        <td>{{ $row[0] }}</td>
                        <td class="text-center">{{ $row[1] }}</td>
                        <td class="text-end">{{ $row[2] }}</td>
                        <td class="text-end">{{ $row[3] }}</td>
                        <td class="text-end">{{ $row[4] }}</td>
                        <td class="text-end">{{ $row[5] }}</td>
                    </tr>
                @endif

            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">TOTAL</th>
                <th class="text-end">{{ number_format(abs($totalOpening),2) }}</th>
                <th class="text-end">{{ number_format($totalDebit,2) }}</th>
                <th class="text-end">{{ number_format($totalCredit,2) }}</th>
                <th class="text-end">{{ number_format(abs($totalClosing),2) }}</th>
            </tr>
        </tfoot>

    </table>

</body>
</html>