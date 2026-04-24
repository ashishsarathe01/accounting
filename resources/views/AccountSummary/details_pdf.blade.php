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

        .group {
            font-weight: bold;
        }

        .group {
            font-weight: bold;
        }

        .main-group td {
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
        <div>
            From Date : {{ date('d-m-Y',strtotime($from)) }}
            To Date : {{ date('d-m-Y',strtotime($to)) }}
        </div>
    </div>

    <br>

    {{-- Title --}}
    <div class="text-center title">
        Account Summary : {{ $title ?? '' }}
    </div>
    @php
        $openingDr = 0;
        $openingCr = 0;

        $totalDebit = 0;
        $totalCredit = 0;

        $closingDr = 0;
        $closingCr = 0;

        foreach($rows as $r){

            if(($r['level'] ?? 1) != 1) continue;

            // CLEAN VALUES
            $opening = floatval(str_replace([',','Dr','Cr',"\n"], '', $r['opening']));
            $debit   = floatval(str_replace([',',"\n"], '', $r['debit']));
            $credit  = floatval(str_replace([',',"\n"], '', $r['credit']));
            $closing = floatval(str_replace([',','Dr','Cr',"\n"], '', $r['closing']));

            // OPENING
            if(str_contains($r['opening'], 'Dr')){
                $openingDr += $opening;
            } else {
                $openingCr += $opening;
            }

            // DEBIT / CREDIT
            $totalDebit  += $debit;
            $totalCredit += $credit;

            // CLOSING
            if(str_contains($r['closing'], 'Dr')){
                $closingDr += $closing;
            } else {
                $closingCr += $closing;
            }
        }

        $netOpening = $openingDr - $openingCr;
        $netOpeningType = $netOpening >= 0 ? 'Dr' : 'Cr';

        $netClosing = $closingDr - $closingCr;
        $netClosingType = $netClosing >= 0 ? 'Dr' : 'Cr';
    @endphp
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
                <tr class="{{ ($row['type'] === 'GROUP' && ($row['level'] ?? 1) == 1) ? 'main-group' : '' }}">
                    {{-- INDENTATION BASED ON LEVEL --}}
                    <td style="padding-left: {{ ($row['level'] ?? 1) * 20 }}px;"
                        class="{{ $row['type'] === 'GROUP' ? 'group' : '' }}">

                        {{ $row['name'] }}
                    </td>

                    <td class="text-center">
                        {{ $row['type'] }}
                    </td>

                    <td class="text-end">
                        {{ $row['opening'] }}
                    </td>

                    <td class="text-end">
                        {{ $row['debit'] }}
                    </td>

                    <td class="text-end">
                        {{ $row['credit'] }}
                    </td>

                    <td class="text-end">
                        {{ $row['closing'] }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f2f2f2;">
                <td class="text-end">TOTAL</td>
                <td></td>

                <td class="text-end">
                    {{ number_format(abs($netOpening), 2) }} {{ $netOpeningType }}
                </td>

                <td class="text-end">
                    {{ number_format($totalDebit, 2) }}
                </td>

                <td class="text-end">
                    {{ number_format($totalCredit, 2) }}
                </td>

                <td class="text-end">
                    {{ number_format(abs($netClosing), 2) }} {{ $netClosingType }}
                </td>
            </tr>
        </tfoot>
    </table>

</body>
</html>