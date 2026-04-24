<!DOCTYPE html>
<html>
<head>
<title>Sale Order View</title>
<style>
    body { font-size: 12px; font-family: 'Source Sans Pro', sans-serif; }
    table{ width:100%; border-spacing:0; border:1px solid #dadada; }
    th,td{ border:1px solid #dadada; padding:4px 8px; }
    .text-center{ text-align:center; }
    .text-right{ text-align:right; }
    .fw-bold{ font-weight:700; }

    @media print{
        body{ margin:0; }
        .noprint{ display:none; }
    }

    @page {
        size: A4;
        margin: 5mm;
    }
</style>
</head>
<body>

<h2 class="text-center">{{$company->company_name}}</h2>
<h4 class="text-center">Sale Order</h4>

<table>
<tr>
    <td colspan="4">
        <strong>ACCOUNT NAME:</strong> {{$saleOrder->billTo->account_name}}
    </td>
    <td colspan="4" class="text-right">
        <strong>Date:</strong> {{date('d-m-Y',strtotime($saleOrder->created_at))}}<br>
        <strong>Sale Order No:</strong> {{$saleOrder->sale_order_no}}
    </td>
</tr>

<tr>
    <td colspan="4">
        <strong>Bill To:</strong><br>
        {{$saleOrder->billTo->account_name}}<br>
        {{$saleOrder->billTo->address}}<br>
        GSTIN: {{$saleOrder->billTo->gstin}}
    </td>
    <td colspan="4">
        <strong>Ship To:</strong><br>
        {{$saleOrder->shippTo->account_name}}<br>
        {{$saleOrder->shippTo->address}}<br>
        GSTIN: {{$saleOrder->shippTo->gstin}}
    </td>
</tr>

@php $grandTotal = 0; @endphp

@foreach($saleOrder->items as $item)
<tr><td colspan="8" style="background:#e0e0e0;"></td></tr>

<tr>
    <th>Item</th><td>{{$item->item->name}}</td>
    <th>Rate</th><td>{{$item->price}}</td>
    <th>Billing Rate</th><td>{{$item->bill_price ?? $item->price}}</td>
    <td colspan="2"></td>
</tr>

<tr>
    <th>Unit</th><td>{{$item->unitMaster->s_name}}</td>
    <th>Freight</th><td>{{ $saleOrder->freight==1?'Yes':'No' }}</td>
    <td colspan="4"></td>
</tr>

<tr>
@foreach($item->gsms as $gsm)
    <th colspan="2" class="text-center">{{$gsm->gsm}} GSM</th>
@endforeach
</tr>

<tr>
@foreach($item->gsms as $gsm)
    <th>Size({{$item->sub_unit}})</th>
    <th>{{$item->unitMaster->s_name}}</th>
@endforeach
</tr>

@php $rows = $item->gsms->map(fn($x)=>$x->details->count())->max(); @endphp
@for($i=0;$i<$rows;$i++)
<tr>
@foreach($item->gsms as $gsm)
<td>{{$gsm->details[$i]->size ?? ''}}</td>
<td>{{$gsm->details[$i]->quantity ?? ''}}</td>
@endforeach
</tr>
@endfor

<tr class="fw-bold">
@foreach($item->gsms as $gsm)
@php $t = $gsm->details->sum('quantity'); $grandTotal+=$t; @endphp
<th>Total</th><th>{{$t}}</th>
@endforeach
</tr>

@endforeach

<tr style="background:#d9edf7;">
<th colspan="2">GRAND TOTAL</th>
<th colspan="6">{{$grandTotal}} {{$saleOrder->items->first()->unitMaster->s_name}}</th>
</tr>

@if($configuration && $configuration->terms)
<tr>
    <td colspan="8">
        <strong>Terms & Conditions:</strong><br>
        @foreach($configuration->terms as $k=>$t)
            {{$k+1}}. {{$t->term}}<br>
        @endforeach
    </td>
</tr>
@endif

<tr>
<td colspan="3">Prepared By: {{$saleOrder->orderCreatedBy->name}}</td>
<td colspan="3">Confirmed By:</td>
<td colspan="2">Received By:</td>
</tr>

</table>

</body>
</html>
