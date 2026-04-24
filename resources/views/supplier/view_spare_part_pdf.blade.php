<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Spare Part Order - {{ $spare_part->id }}</title>
    <style>
        body {
            font-family: 'Source Sans Pro', sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }

        h2, h4 {
            text-align: center;
            margin: 5px 0;
        }

        p {
            margin: 5px 0;
        }

        .table-container {
            width: 100%;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 500px;
        }

        table th, table td {
            border: 1px solid #dadada;
            padding: 10px 12px;
            vertical-align: middle;
        }

        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .total-row {
            background-color: #BBDEFB;
            font-weight: bold;
        }

        .signatures {
            width: 100%;
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .signature-box {
            width: 30%;
            text-align: center;
            margin-bottom: 20px;
        }

        .signature-box hr {
            border-top: 1px solid #000;
            margin-top: 40px;
        }

        /* A4 sizing for PDF */
        @page {
            size: A4;
            margin: 20mm;
        }

    </style>
</head>
<body>

    <h2>{{ $company_data->company_name }}</h2>
    <h4>Spare Part Order</h4>
    <p style="text-align:center;">Date: {{ date('d-m-Y', strtotime($spare_part->created_at)) }}</p>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width:60%;">Item</th>
                    <th style="width:20%; text-align:center;">Unit</th>
                    <th style="width:20%; text-align:center;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @foreach($spare_part->items as $item)
                    <tr>
                        <td>{{ $item->item->name }}</td>
                        <td style="text-align:center;">{{ $item->unit }}</td>
                        <td style="text-align:center;">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align:right;">Grand Total</td>
                    <td style="text-align:center;">{{ $spare_part->items->sum('quantity') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Signatures --}}
    <div class="signatures">
        <div class="signature-box">
            <p>Prepared By</p>
            <hr>
        </div>
        <div class="signature-box">
            <p>Confirmed By</p>
            <hr>
        </div>
        <div class="signature-box">
            <p>Received By</p>
            <hr>
        </div>
    </div>

</body>
</html>
