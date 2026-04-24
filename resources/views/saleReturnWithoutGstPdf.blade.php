<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body { font-family: DejaVu Sans; font-size: 12px; }
table { width:100%; border-collapse: collapse; }
th,td { border:1px solid #000; padding:6px; }
.text-right { text-align:right; }
.text-center { text-align:center; }
</style>
</head>
<body>

<h3 style="text-align:center;">CREDIT NOTE</h3>

<strong>{{ $company_data->company_name }}</strong><br>
GSTIN: {{ $seller_info->gst_no }}<br>
{{ $seller_info->address }}, {{ $seller_info->pincode }}

<hr>

<strong>Party Details:</strong><br>
{{ $sale_return->account_name }}<br>
{{ $sale_return->address }}, {{ $sale_return->sname }}<br>
{{ $sale_return->pin_code }}<br>
GSTIN: {{ $sale_return->gstin }}

<hr>

<table>
<thead>
<tr>
<th>#</th>
<th>Account</th>
<th>Amount (₹)</th>
</tr>
</thead>
<tbody>

@php $i=1; $total=0; @endphp
@foreach($items as $item)
<tr>
<td>{{ $i++ }}</td>
<td>{{ $item->account_name }}</td>
<td class="text-right">{{ number_format($item->debit,2) }}</td>
</tr>
@php $total += $item->debit; @endphp
@endforeach

<tr>
<td colspan="2" class="text-right"><strong>Total</strong></td>
<td class="text-right"><strong>{{ number_format($total,2) }}</strong></td>
</tr>

</tbody>
</table>

<br>

<h4 style="text-align:right;">
Grand Total: ₹ {{ number_format($sale_return->total,2) }}
</h4>

</body>
</html>
