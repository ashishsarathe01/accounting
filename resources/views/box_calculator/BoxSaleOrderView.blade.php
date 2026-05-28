@extends('layouts.app')

@section('content')

@include('layouts.header')

<style type="text/css">

.dataTables_filter{
    float:right;
}

.data-table{
    font-size:15px;
}

.data-table tbody tr{
    line-height:10px !important;
}

table{
    width:100%;
    border-spacing:0;
    border-collapse:collapse;
}

table tr th,
table tr td{
    border:1px solid #000000;
    margin:0;
    padding:2px 5px;
}

.text-right{
    text-align:right;
}

.text-left{
    text-align:left;
}

.text-center{
    text-align:center;
}

p{
    margin:0px;
    margin-bottom:0rem !important;
}

h1,h2,h3,h4,h5,h6{
    margin:5px 0px;
}

.width25{
    width:35%;
}

.lft_mar15{
    margin-left:15px;
}

.invoice-total{
    font-size:18px;
    font-weight:800;
    margin:0;
}

.wrap-text{
    display:inline-block;
    max-width:55%;
    word-wrap:break-word;
    word-break:break-word;
    white-space:normal;
    vertical-align:top;
}


/* PRINT CSS */
@media print{

    html,
    body{
        width:100%;
        margin:0 !important;
        padding:0 !important;
        background:#fff !important;
    }

    .noprint{
        display:none !important;
    }

    .header-section{
        display:none !important;
    }

    .sidebar{
        display:none !important;
    }

    .navbar{
        display:none !important;
    }

    .container-fluid{
        width:100% !important;
        padding:0 !important;
        margin:0 !important;
    }

    .row{
        margin:0 !important;
    }

    .col-lg-10,
    .col-md-12{
        width:100% !important;
        max-width:100% !important;
        flex:0 0 100% !important;
        padding:0 !important;
        margin:0 !important;
    }

    .bg-mint{
        background:#fff !important;
    }

    .print-area{
        width:96% !important;
        margin:auto !important;
    }

    table{
        width:100% !important;
        border-collapse:collapse !important;
    }

    table tr th,
    table tr td{
        padding:3px 5px !important;
    }

}


/* PAGE MARGIN */
@page{

    size:auto;

    margin:8mm 8mm;

}

</style>

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="d-md-flex justify-content-between py-4 px-2 align-items-center header-section noprint">

    <div class="calender-administrator my-2 my-md-0 noprint">

        <button type="button"
                class="btn btn-danger"
                onclick="window.close();">

            CLOSE

        </button>

        <button class="btn btn-info"
                onclick="window.print();">

            Print

        </button>

    </div>

</div>

<br>

<div class="print-area">

<table style="
    font-family:'Source Sans Pro', sans-serif;
    letter-spacing:0.05em;
    color:#404040;
    font-size:12px;
    font-weight:500;
    padding:10px;
">

<tbody>

{{-- COMPANY HEADER --}}
<tr>

    <th colspan="8"
        style="padding:0;">

        <div style="min-height:130px; position:relative;">

            <div style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                margin-bottom:5px;
            ">

                <div style="
                    flex:1;
                    text-align:left;
                    margin-left:5px;
                ">

                    <strong>

                        GSTIN:
                        {{ $seller_info->gst_no }}

                    </strong>

                </div>

                <div style="
                    flex:1;
                    text-align:center;
                ">

                    <strong style="
                        font-size:13px;
                        font-weight:700;
                        letter-spacing:1px;
                    ">

                        BOX SALE ORDER

                    </strong>

                </div>

                <div style="
                    flex:1;
                    text-align:right;
                    margin-right:5px;
                ">

                    <strong>

                        PAN:
                        {{ substr($seller_info->gst_no, 2, 10) }}

                    </strong>

                </div>

            </div>

            <div style="
                text-align:center;
                line-height:1;
                margin:0;
                padding:0;
            ">

                <p style="
                    margin:0;
                    font-size:24px;
                    font-weight:bold;
                ">

                    {{ $company_data->company_name }}

                </p>

                <p style="margin:0;">

                    <small style="
                        font-size:12px;
                        display:inline-block;
                        max-width:50%;
                        word-break:break-word;
                    ">

                        {{ $seller_info->address }}

                    </small>

                </p>

                <p style="margin:0;">

                    <small style="font-size:12px;">

                        Phone:
                        {{ $company_data->mobile_no }}

                        &nbsp;

                        Email:
                        {{ $company_data->email_id }}

                    </small>

                </p>

            </div>

        </div>

    </th>

</tr>

{{-- ORDER DETAILS --}}
<tr>

    {{-- PARTY DETAILS --}}
    <td colspan="3"
        style="
            width:50%;
            vertical-align:top;
            padding:0;
        ">

        <div style="
            padding:8px 12px;
            border-bottom:1px solid #000;
            font-style:italic;
            font-weight:700;
        ">

            Party Details :

        </div>

        <div style="
            padding:12px;
            min-height:130px;
        ">

            <p style="
                font-size:16px;
                font-weight:800;
                margin-bottom:12px !important;
            ">

                {{ $saleOrder->party_name ?? '-' }}

            </p>

            <p style="
                line-height:22px;
                font-size:13px;
                margin-bottom:15px !important;
            ">

                {{ $saleOrder->address ?? '' }}

                {{ $saleOrder->address2 ?? '' }}

                {{ $saleOrder->address3 ?? '' }}

            </p>

            <p style="
                font-size:13px;
                font-weight:700;
            ">

                GSTIN/UIN:
                {{ $saleOrder->gstin ?? '' }}

            </p>

        </div>

    </td>

    {{-- SALE ORDER DETAILS --}}
    <td colspan="4"
        style="
            width:50%;
            vertical-align:top;
            padding:0;
        ">

        <div style="
            padding:8px 12px;
            border-bottom:1px solid #000;
            font-style:italic;
            font-weight:700;
        ">

            Sale Order Details :

        </div>

        <div style="
            padding:12px;
            min-height:130px;
        ">

            <div style="
                display:flex;
                margin-bottom:12px;
            ">

                <div style="
                    width:45%;
                    font-weight:700;
                ">

                    Sale Order No.

                </div>

                <div style="width:5%;">

                    :

                </div>

                <div style="
                    width:50%;
                    font-weight:800;
                ">

                    {{ $saleOrder->sale_order_no }}

                </div>

            </div>

            <div style="
                display:flex;
                margin-bottom:12px;
            ">

                <div style="
                    width:45%;
                    font-weight:700;
                ">

                    Sale Order Date

                </div>

                <div style="width:5%;">

                    :

                </div>

                <div style="width:50%;">

                    {{ date('d-m-Y', strtotime($saleOrder->order_date)) }}

                </div>

            </div>

            <div style="
                display:flex;
                margin-bottom:12px;
            ">

                <div style="
                    width:45%;
                    font-weight:700;
                ">

                    PO Number

                </div>

                <div style="width:5%;">

                    :

                </div>

                <div style="width:50%;">

                    {{ $saleOrder->po_number }}

                </div>

            </div>

            <div style="
                display:flex;
                margin-bottom:12px;
            ">

                <div style="
                    width:45%;
                    font-weight:700;
                ">

                    PO Date

                </div>

                <div style="width:5%;">

                    :

                </div>

                <div style="width:50%;">

                    {{ date('d-m-Y', strtotime($saleOrder->po_date)) }}

                </div>

            </div>

        </div>

    </td>

</tr>

{{-- TABLE HEADER --}}
<tr>

    <th style="
        width:4%;
        text-align:center;
        vertical-align:middle;
    ">
        S.No.
    </th>

    <th style="
        width:14%;
        text-align:left;
        vertical-align:middle;
    ">
        Item
    </th>

    <th style="
        width:42%;
        text-align:left;
        vertical-align:middle;
    ">
        Description
    </th>

    <th style="
        width:10%;
        text-align:right;
        vertical-align:middle;
    ">
        Qty
    </th>

    <th style="
        width:10%;
        text-align:right;
        vertical-align:middle;
    ">
        Dispatched
    </th>

    <th style="
        width:10%;
        text-align:right;
        vertical-align:middle;
    ">
        Pending
    </th>

    <th style="
        width:10%;
        text-align:right;
        vertical-align:middle;
    ">
        Price
    </th>

</tr>

@php

$i = 1;

$totalQty = 0;
$totalDispatch = 0;
$totalPending = 0;

@endphp

@forelse($saleOrderItems as $row)

@php

$totalQty += $row->qty;
$totalDispatch += $row->dispatched_qty;
$totalPending += $row->pending_qty;

@endphp

<tr style="vertical-align:top;">

    {{-- SR NO --}}
    <td style="
        text-align:center;
        vertical-align:middle;
        padding:10px 5px;
    ">

        {{ $i }}

    </td>

    {{-- ITEM --}}
    <td style="
        vertical-align:middle;
        font-weight:600;
        padding:10px 8px;
    ">

        {{ $row->item_name }}

    </td>

    {{-- DESCRIPTION --}}
    <td style="
    white-space:normal;
    text-align:left;
    line-height:18px;
    padding:6px 8px;
    word-break:break-word;
    vertical-align:top;
">

    {!! nl2br(

    str_replace(

        ['Dimensions', 'Paper Specification'],

        [

            '<strong>Dimensions</strong>',

            '<strong>Paper Specification</strong>'

        ],

        preg_replace(

            "/(\r\n|\n|\r){2,}/",

            "<br>",

            trim($row->description)

        )

    )

) !!}

</td>

    {{-- QTY --}}
    <td style="
        text-align:right;
        vertical-align:middle;
        padding:10px 8px;
    ">

        {{ number_format($row->qty,2) }}

    </td>

    {{-- DISPATCHED --}}
    <td style="
        text-align:right;
        vertical-align:middle;
        padding:10px 8px;
    ">

        {{ number_format($row->dispatched_qty,2) }}

    </td>

    {{-- PENDING --}}
    <td style="
        text-align:right;
        vertical-align:middle;
        padding:10px 8px;
    ">

        {{ number_format($row->pending_qty,2) }}

    </td>

    {{-- PRICE --}}
    <td style="
        text-align:right;
        vertical-align:middle;
        padding:10px 8px;
    ">

        {{ number_format($row->price,2) }}

    </td>

</tr>

@php $i++; @endphp

@empty

<tr>

    <td colspan="7"
        style="
            text-align:center;
            padding:25px;
        ">

        No Items Found

    </td>

</tr>

@endforelse


{{-- TOTAL --}}
<tr>

    <td colspan="3"
        style="
            text-align:right;
            font-weight:bold;
            padding:10px;
        ">

        TOTAL

    </td>

    <td style="
        text-align:right;
        padding:10px;
    ">

        <strong>

            {{ number_format($totalQty,2) }}

        </strong>

    </td>

    <td style="
        text-align:right;
        padding:10px;
    ">

        <strong>

            {{ number_format($totalDispatch,2) }}

        </strong>

    </td>

    <td style="
        text-align:right;
        padding:10px;
    ">

        <strong>

            {{ number_format($totalPending,2) }}

        </strong>

    </td>

    <td></td>

</tr>


</tbody>

</table>

</div>

</div>

</div>

</section>

</div>

@include('layouts.footer')

@endsection