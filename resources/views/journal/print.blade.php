<!DOCTYPE html>
<html>
<head>
    <title>Journal Voucher</title>
    <style>
        body {
            font-family: Arial;
            font-size: 14px;
            margin: 20px;
        }

        .voucher-box {
            border: 1px solid #000;
            padding: 10px;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }

        .sub-header {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: 1px solid #000;
        }

        th {
            text-align: left;
            padding: 5px;
        }

        td {
            padding: 5px;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 20px;
        }

        .signature {
            float: right;
            text-align: center;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>

<div class="voucher-box">

    <div class="header">
        JOURNAL VOUCHER
    </div>

    <div class="sub-header">
        <div>
            <b>Voucher No:</b> {{ $journal->voucher_no }}
        </div>
        <div>
            <b>Date:</b> {{ date('d-m-Y', strtotime($journal->date)) }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Particulars</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
            </tr>
        </thead>
        <tbody>
            @php $totalDebit = 0; $totalCredit = 0; @endphp

            @foreach($journal_detail as $row)
                <tr>
                    <td>{{ $row->account_name }}</td>
                    <td class="text-right">
                        {{ $row->type == 'Debit' ? number_format($row->debit,2) : '' }}
                    </td>
                    <td class="text-right">
                        {{ $row->type == 'Credit' ? number_format($row->credit,2) : '' }}
                    </td>
                </tr>

                @php
                    $totalDebit += $row->debit;
                    $totalCredit += $row->credit;
                @endphp
            @endforeach

            <tr>
                <th>Total</th>
                <th class="text-right">{{ number_format($totalDebit,2) }}</th>
                <th class="text-right">{{ number_format($totalCredit,2) }}</th>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><b>Narration:</b> {{ $journal->long_narration }}</p>

        <div class="signature">
            <br><br>
            Authorised Signatory
        </div>
    </div>

</div>

<script>
    window.onload = function() {
        window.print();
    }
</script>

</body>
</html>