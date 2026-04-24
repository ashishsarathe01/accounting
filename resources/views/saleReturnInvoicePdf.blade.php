<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
body {
    font-family: DejaVu Sans;
    font-size: 11px;
}

table {
    width:100%;
    border-collapse: collapse;
}

th,td {
    border:1px solid #000;
    padding:4px;
}

.text-right { text-align:right; }
.text-center { text-align:center; }
.text-left { text-align:left; }

.header-table td {
    border:none;
}

.small { font-size:10px; }

</style>
</head>
<body>

<!-- HEADER -->
<table class="header-table">
<tr>
<td style="width:50%;">
<strong>GSTIN: {{ $seller_info->gst_no ?? '' }}</strong>
</td>

<td style="width:50%;" class="text-right">
<strong>PAN: {{ substr($seller_info->gst_no ?? '',2,10) }}</strong>
</td>
</tr>

<tr>
<td colspan="2" class="text-center">
<h3 style="margin:2px 0;">CREDIT NOTE</h3>
<h2 style="margin:2px 0;">{{ $company_data->company_name }}</h2>
<p class="small">
{{ $seller_info->address ?? '' }} {{ $seller_info->pincode ?? '' }}
</p>
<p class="small">
Phone: {{ $company_data->mobile_no }}
Email: {{ $company_data->email_id }}
</p>
</td>
</tr>
</table>

<hr>

<!-- BILLING + DETAILS -->
<table>
<tr>
<td style="width:50%; vertical-align:top;">
<strong>Billed To:</strong><br>
{{ $sale_return->billing_name }}<br>
{{ $sale_return->party_address }}<br>
GSTIN: {{ $sale_return->billing_gst }}
</td>

<td style="width:50%; vertical-align:top;">
Cr. Note No : {{ $sale_return->sr_prefix }}<br>
Cr. Note Date : {{ date('d-m-Y',strtotime($sale_return->date)) }}<br>
Org Inv No : {{ $sale_return->invoice_no }}<br>
Org Inv Date : {{ date('d-m-Y',strtotime($sale_return->original_invoice_date)) }}<br>
Transport : {{ $sale_return->transport_name }}<br>
Vehicle No : {{ $sale_return->vehicle_no }}<br>
Station : {{ $sale_return->station }}<br>
GR/RR No : {{ $sale_return->gr_pr_no }}
</td>
</tr>
</table>

<br>

<!-- ITEMS TABLE -->
<table>
<thead>
<tr>
<th width="5%">S.No</th>
<th width="25%">Description</th>
<th width="10%">HSN</th>
<th width="10%">Qty</th>
<th width="10%">Unit</th>
<th width="15%">Price</th>
<th width="15%">Amount</th>
</tr>
</thead>

<tbody>
@php $i=1; $item_total=0; @endphp
@foreach($items_detail as $item)
<tr>
<td class="text-center">{{ $i++ }}</td>
<td>{{ $item->items_name }}</td>
<td class="text-center">{{ $item->hsn_code }}</td>
<td class="text-right">{{ $item->qty }}</td>
<td class="text-center">{{ $item->unit }}</td>
<td class="text-right">{{ $item->price }}</td>
<td class="text-right">{{ formatIndianNumber($item->amount) }}</td>
</tr>
@php $item_total += $item->amount; @endphp
@endforeach

<tr>
<td colspan="6" class="text-right"><strong>Total</strong></td>
<td class="text-right"><strong>{{ formatIndianNumber($item_total) }}</strong></td>
</tr>

@foreach($sale_sundry as $sundry)
<tr>
<td colspan="6" class="text-right">
Add : {{ $sundry->name }}
@if($sundry->rate!=0) ({{ $sundry->rate }}%) @endif
</td>
<td class="text-right">{{ formatIndianNumber($sundry->amount) }}</td>
</tr>
@endforeach

<tr>
<td colspan="6" class="text-right"><strong>Grand Total</strong></td>
<td class="text-right">
<strong>{{ formatIndianNumber($sale_return->total) }}</strong>
</td>
</tr>

</tbody>
</table>

<br>

<!-- GST BREAKUP -->
@foreach($gst_detail as $val)
<table>
<tr>
<td width="20%">Tax Rate</td>
<td width="20%">{{ $val->rate }}%</td>

<td width="20%">Taxable</td>
<td width="20%">{{ formatIndianNumber($val->taxable_amount) }}</td>

@if(substr($company_data->gst,0,2)==substr($sale_return->billing_gst,0,2))
<td width="20%">CGST+SGST</td>
<td width="20%">{{ formatIndianNumber($val->amount*2) }}</td>
@else
<td width="20%">IGST</td>
<td width="20%">{{ formatIndianNumber($val->amount) }}</td>
@endif

</tr>
</table>
@endforeach

<br>

<strong>Narration:</strong> {{ $sale_return->narration }}

<br><br>

<!-- SIGNATURE -->
<table>
<tr>
<td width="50%">
@if($configuration && $configuration->term_status==1)
<strong>Terms & Conditions</strong><br>
@foreach($configuration->terms as $t)
<small>{{ $t->term }}</small><br>
@endforeach
@endif
</td>

<td width="50%" class="text-right">
<br><br>
For {{ $company_data->company_name }}<br><br>
Authorised Signatory
</td>
</tr>
</table>

</body>
</html>
