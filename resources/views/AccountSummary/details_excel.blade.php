<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        table {
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
{{-- COMPANY DETAILS --}}
<table>
    <tr>
        <td colspan="6" class="text-center title">
            <strong>{{ $company->company_name ?? '' }}</strong>
        </td>
    </tr>
    <tr>
        <td colspan="6" class="text-center">
            {{ $company->address ?? '' }}
        </td>
    </tr>
    <tr>
        <td colspan="6" class="text-center">
            CIN : {{ $company->cin ?? '' }}
        </td>
    </tr>
    <tr>
        <td colspan="6" class="text-center">
            From Date : {{ $from }}
            &nbsp;&nbsp;&nbsp;
            To Date : {{ $to }}
        </td>
    </tr>

    {{-- EMPTY ROW --}}
    <tr>
        <td colspan="6"></td>
    </tr>

    {{-- TITLE --}}
    <tr>
        <td colspan="6" class="text-center title">
            <strong>Account Summary : {{ $title ?? '' }}</strong>
        </td>
    </tr>
</table>
<table>
    <tr style="font-weight: bold; background: #f2f2f2;">
        <th style="font-size: 11pt; font-weight: bold;">Account / Group</th>
        <th style="font-size: 11pt; font-weight: bold;">Type</th>
        <th style="font-size: 11pt; font-weight: bold;">Opening</th>
        <th style="font-size: 11pt; font-weight: bold;">Debit</th>
        <th style="font-size: 11pt; font-weight: bold;">Credit</th>
        <th style="font-size: 11pt; font-weight: bold;">Closing</th>
    </tr>
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
    @foreach($rows as $row)
        <tr>
            @php
                $level = $row['level'] ?? 1;
                $isMainGroup = ($row['type']=='GROUP' && $level==1);
                $isSubGroup = ($row['type']=='GROUP' && $level==2);
                $fontSize = $isMainGroup ? '11pt' : ($isSubGroup ? '10pt' : '10pt');
                $fontWeight = ($isMainGroup || $isSubGroup) ? 'bold' : 'normal';
            @endphp
            
            <td style="
                padding-left: {{ $level * 20 }}px;
                font-size: {{ $fontSize }};
                font-weight: {{ $fontWeight }};
            ">
                {{ $row['name'] }}
            </td>
            <td style="font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }};">{{ $row['type'] }}</td>
            <td style="font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }};">{{ $row['opening'] }}</td>
            <td style="font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }};">{{ $row['debit'] }}</td>
            <td style="font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }};">{{ $row['credit'] }}</td>
            <td style="font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }};">{{ $row['closing'] }}</td>
        </tr>
    @endforeach
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
</table>

</body>
</html>