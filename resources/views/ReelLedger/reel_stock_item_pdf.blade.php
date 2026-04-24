<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Available Reel Stock</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

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
            background: #efefef;
            font-weight: bold;
        }
         @page {
        margin-top: 70px;   /* top margin for header */
    }

    /* FIXED PAGE HEADER (repeats automatically on every page) */
    .page-header {
        position: fixed;
        top: -50px;            /* must be a negative top */
        left: 0;
        right: 0;
        height: 40px;
        text-align: center;
        font-size: 14px;
        font-weight: bold;
    }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>

<body>

<div class="page-header">
    Available Reel Stock : {{ $A_date }} <br>
    Item: {{ $ItemName }}
</div>

    

    {{-- If no reels --}}
    @if(empty($FinalList))
        <p style="color:red;">No Reels Found.</p>
    @else

    <table>
        <thead>
            <tr>
                <th width="40" class="text-center">S.No</th>
                <th width="100">Reel No</th>
                <th width="100">Size</th>
                <th width="120" class="text-right">Weight</th>
                <th width="60" class="text-center">Unit</th>
            </tr>
        </thead>

        <tbody>
            @foreach($FinalList as $row)
            <tr>
                <td class="text-center">{{ $row['sn'] }}</td>
                <td class="text-center">{{ $row['reel_no'] }}</td>
                <td class="text-center">{{ $row['size'] }}</td>
                <td class="text-right">{{ number_format($row['weight'], 2) }}</td>
                <td class="text-center">{{ $row['unit'] }}</td>
            </tr>
            @endforeach

            {{-- FOOTER TOTAL --}}
            <tr class="fw-bold">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ number_format($total_weight, 2) }}</td>
                <td class="text-center">{{ count($FinalList) }}</td>
            </tr>
        </tbody>
    </table>

    @endif

</body>
</html>
