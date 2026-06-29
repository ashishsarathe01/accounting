@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
    
    .border-all {
        border: 1px solid #000;
    }
    .border-bottom {
        border-bottom: 1px solid #000;
    }
    .label {
        font-weight: 600;
    }
    .small-text {
        font-size: 13px;
    }
    .gov-main{
        width:100%;

        border:1px solid #000;

        margin:0 auto;

        padding:0;
        font-family:Arial, Helvetica, sans-serif;
        font-size:12px;
        color:#000;
        background:#fff;
    }

    .gov-main table{
        width:100%;
        border-collapse:collapse;
    }

    .gov-main td,
    .gov-main th{
        border:1px solid #000;
        padding:5px 6px;
        vertical-align:top;
    }

    .gov-title{
        text-align:center;
        font-size:18px;
        font-weight:bold;
        padding:8px;
    }

    .gov-part{
        font-weight:bold;
        padding:6px;
        background:#fff;
    }

    .gov-label{
        width:100px;
        font-weight:400;      
        white-space:nowrap;
    }
    .gov-value{
        font-weight:700;
    }

    .gov-center{
        text-align:center;
    }

    .gov-small{
        font-size:10px;
    }
    #detailedView, #detailedView * {
        box-sizing: border-box;
    }
    .importantRule { 
        display: none !important;  /* Force hide anything with this class */
    }
    @page {
    size: A4;
    margin: 5mm 5mm 5mm 5mm; 
    }
    @media print{

        html,
        body{
            margin:0 !important;
            padding:0 !important;
            width:100%;
            height:auto !important;
            background:#fff !important;
        }

        .vh-100{
            height:auto !important;
            min-height:auto !important;
        }

        .container,
        .container-fluid,
        .row,
        .col-md-12,
        .col-lg-9,
        .bg-mint,
        .list-of-view-company,
        .list-of-view-company-section{

            margin:0 !important;
            padding:0 !important;
            width:100% !important;
            max-width:100% !important;
            min-height:auto !important;
            height:auto !important;
            background:#fff !important;
            border:none !important;
        }

        table{
            border-collapse:collapse !important;
            page-break-inside:auto !important;
        }

        thead{
            display:table-header-group !important;
        }

        tbody{
            display:table-row-group !important;
        }

        tfoot{
            display:table-footer-group !important;
        }

        tr{
            page-break-inside:avoid !important;
            page-break-after:auto !important;
        }

        td,
        th{
            page-break-inside:avoid !important;
        }

        .goods-table{
            border:1px solid #000 !important;
        }

        .goods-table tr{
            page-break-inside:avoid !important;
        }

        .goods-table td,
        .goods-table th{
            border:1px solid #000 !important;
        }
        .header-section,
        .sidebar,
        .leftnav,
        nav,
        .navbar,
        footer{
            display:none !important;
        }

        body.print-simple #simpleView{
            display:block !important;
        }

        body.print-simple #detailedView{
            display:none !important;
        }

        body.print-detailed #simpleView{
            display:none !important;
        }

        body.print-detailed #detailedView{
        display:block !important;

        }
    }
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                <div class="container-fluid border-all p-2">

                    <div class="d-flex justify-content-between align-items-center mb-3 header-section">
                        <button class="btn btn-info" onclick="printpage();">Print</button>

                        <div>
                            <label class="mb-0 font-weight-bold">
                                <input type="checkbox" id="detailed_view">
                                Detailed View
                            </label>
                        </div>
                    </div>

                    <div id="simpleView">
                        @php
                            $qrContent = "";
                            $Irn = "";

                            if ($sale->e_invoice_status == 1 && !empty($sale->einvoice_response)) {
                                $einvoice_data = json_decode($sale->einvoice_response);

                                $qrContent = $einvoice_data->SignedQRCode ?? "";
                                $Irn = $einvoice_data->Irn ?? "";
                            }

                            if ($sale->e_waybill_status == 1 && !empty($sale->eway_bill_response)) {
                                $qrContent = $sale->eway_bill_response;
                            }

                            $ewaybill_no = "";
                            $ewayBillDate = "";
                            $validUpto = "";
                            $formatted = "";

                            if (!empty($ewayBillDate)) {
                                try {
                                    $formatted = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ewayBillDate)
                                        ->format('d/m/Y h:i A');
                                } catch (\Exception $e) {
                                    try {
                                        $formatted = \Carbon\Carbon::createFromFormat('d/m/Y h:i:s A', $ewayBillDate)
                                            ->format('d/m/Y h:i A');
                                    } catch (\Exception $e) {
                                        $formatted = $ewayBillDate;
                                    }
                                }
                            }

                            if ($sale->e_waybill_status == 1 && !empty($sale->eway_bill_response)) {
                                $ewaybill_data = json_decode($sale->eway_bill_response);

                                $ewaybill_no = $ewaybill_data->ewayBillNo ?? "";
                                $ewayBillDate = $ewaybill_data->ewayBillDate ?? "";
                                $validUpto = $ewaybill_data->validUpto ?? "";
                            }

                            $hsnCodes = collect($items_detail)
                                ->pluck('hsn_code')
                                ->filter()
                                ->unique()
                                ->values();

                            $displayHSN = '';

                            if ($hsnCodes->count() == 1) {
                                $displayHSN = $hsnCodes->first();
                            } elseif ($hsnCodes->count() > 1) {
                                $firstItem = $items_detail->first();

                                $displayHSN = $hsnCodes->first();

                                if (!empty($firstItem->items_name)) {
                                    $displayHSN .= ' - ' . $firstItem->items_name;
                                }

                                $displayHSN .= ' (+' . ($hsnCodes->count() - 1) . ')';
                            }
                        @endphp

                        <div class="gov-main">
                            <table style="width:100%;border-collapse:collapse;">

                                <tr>
                                    <td colspan="2" class="gov-center" style="border-bottom:none;">
                                        <div class="gov-title">
                                            e-Way Bill
                                        </div>

                                        @if(!empty($qrContent))
                                            {!! QrCode::size(90)->generate($qrContent) !!}
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        E-Way Bill No:
                                    </td>
                                    <td class="gov-value">
                                        {{ $ewaybill_no }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        E-Way Bill Date:
                                    </td>
                                    <td class="gov-value">
                                        @php
                                            try {
                                                $formattedDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $ewayBillDate)
                                                    ->format('d/m/Y h:i A');
                                            } catch (\Exception $e) {
                                                try {
                                                    $formattedDate = \Carbon\Carbon::createFromFormat('d/m/Y h:i:s A', $ewayBillDate)
                                                        ->format('d/m/Y h:i A');
                                                } catch (\Exception $e) {
                                                    $formattedDate = $ewayBillDate;
                                                }
                                            }
                                        @endphp
                                        {{ $formattedDate }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Generated By:
                                    </td>
                                    <td class="gov-value">
                                        {{ $sale->merchant_gst }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Valid From:
                                    </td>
                                    <td class="gov-value">
                                        {{ $formattedDate }}
                                        @if(!empty($sale->e_waybill_distance))
                                            [{{ $sale->e_waybill_distance }} Kms]
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Valid Until:
                                    </td>
                                    <td class="gov-value">
                                        @if(!empty($validUpto))
                                            {{ \Carbon\Carbon::parse($validUpto)->format('d/m/Y') }}
                                        @endif
                                    </td>
                                </tr>

                                @if(!empty($Irn))
                                    <tr>
                                        <td class="gov-label">
                                            IRN:
                                        </td>
                                        <td class="gov-value" style="font-size:10px;word-break:break-all;">
                                            {{ $Irn }}
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                    <td class="gov-label">
                                        Portal:
                                    </td>
                                    <td class="gov-value">
                                        1
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" class="gov-part">
                                        Part - A
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        GSTIN of Supplier
                                    </td>
                                    <td class="gov-value">
                                        {{ $seller_info->gst_no }}
                                        -
                                        {{ $company_data->company_name }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Place of Dispatch
                                    </td>
                                    <td class="gov-value">
                                        {{ $seller_info->sname }},
                                        {{ $seller_info->sname }}-{{ $company_data->pin_code }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        GSTIN of Recipient
                                    </td>
                                    <td class="gov-value">
                                        {{ $sale->billing_gst }}
                                        -
                                        {{ $sale->billing_name }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Place of Delivery
                                    </td>
                                    <td class="gov-value">
                                        @if($sale->shipping_name)
                                            {{ $sale->shipping_state_name }},
                                            {{ $sale->shipping_state_name }}-{{ $sale->shipping_pincode }}
                                        @else
                                            {{ $sale->billing_state_name }},
                                            {{ $sale->billing_state_name }}-{{ $sale->billing_pincode }}
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Document No.
                                    </td>
                                    <td class="gov-value">
                                        {{ $sale->voucher_no_prefix }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Document Date
                                    </td>
                                    <td class="gov-value">
                                        {{ date('d/m/Y', strtotime($sale->date)) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Transaction Type
                                    </td>
                                    <td class="gov-value">
                                        -
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Value of Goods
                                    </td>
                                    <td class="gov-value">
                                        {{ formatIndianNumber($sale->total) }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        HSN Code
                                    </td>
                                    <td class="gov-value">
                                        {{ $displayHSN }}
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Reason for Transportation
                                    </td>
                                    <td class="gov-value">
                                        Outward - Supply
                                    </td>
                                </tr>

                                <tr>
                                    <td class="gov-label">
                                        Transporter
                                    </td>
                                    <td class="gov-value">
                                        -
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" style="padding:6px; border-left:1px solid #000; border-right:1px solid #000;">
                                        <table style="
                                            width:100%;
                                            border-collapse:collapse;
                                            border:1px solid #000;
                                            font-size:12px;
                                        ">

                                            <tr>
                                                <td colspan="8"
                                                    style="
                                                        font-weight:bold;
                                                        border:1px solid #000;
                                                        padding:4px;
                                                    ">
                                                    Part - B
                                                </td>
                                            </tr>

                                            <tr style="
                                                font-weight:600;
                                                text-align:center;
                                            ">
                                                <td style="width:7%; border:1px solid #000;">
                                                    Mode
                                                </td>

                                                <td style="width:28%; border:1px solid #000;">
                                                    Vehicle / Trans<br>
                                                    Doc No & Dt.
                                                </td>

                                                <td style="width:10%; border:1px solid #000;">
                                                    From
                                                </td>

                                                <td style="width:18%; border:1px solid #000;">
                                                    Entered Date
                                                </td>

                                                <td style="width:17%; border:1px solid #000;">
                                                    Entered By
                                                </td>

                                                <td style="width:10%; border:1px solid #000;">
                                                    CEWB No.<br>(if any)
                                                </td>

                                                <td style="width:10%; border:1px solid #000;">
                                                    Multi Veh.Info<br>(if any)
                                                </td>

                                                <td style="width:5%; border:1px solid #000;">
                                                    Portal
                                                </td>
                                            </tr>

                                            <tr style="
                                                text-align:center;
                                                font-weight:bold;
                                            ">
                                                <td style="
                                                    border:1px solid #000;
                                                    padding:3px;
                                                ">
                                                    -
                                                </td>

                                                <td style="border:1px solid #000; text-align:center;">
                                                    {{ $sale->vehicle_no }}

                                                    @if(!empty($sale->gr_pr_no))
                                                        &nbsp;&amp;&nbsp;{{ $sale->gr_pr_no }}&nbsp;&amp;&nbsp;
                                                    @endif

                                                    <br>
                                                    {{ date('d/m/Y', strtotime($sale->date)) }}
                                                </td>

                                                <td style="
                                                    border:1px solid #000;
                                                    padding:3px;
                                                ">
                                                    {{ $seller_info->sname }}
                                                </td>

                                                <td style="
                                                    border:1px solid #000;
                                                    padding:3px;
                                                ">
                                                    @if(!empty($ewayBillDate))
                                                        {{ $ewayBillDate }}
                                                    @endif
                                                </td>

                                                <td style="
                                                    border:1px solid #000;
                                                    padding:3px;
                                                ">
                                                    {{ $sale->merchant_gst }}
                                                </td>

                                                <td style="
                                                    border:1px solid #000;
                                                    padding:3px;
                                                ">
                                                    -
                                                </td>

                                                <td style="
                                                    border:1px solid #000;
                                                    padding:3px;
                                                ">
                                                    -
                                                </td>

                                                <td style="
                                                    border:1px solid #000;
                                                    padding:3px;
                                                ">
                                                    1
                                                </td>
                                            </tr>

                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2" style="padding:12px 0;text-align:center;">
                                        <svg id="barcode"></svg>

                                        <div style="
                                            margin-top:4px;
                                            font-size:10px;
                                            font-weight:bold;
                                            letter-spacing:1px;
                                        ">
                                            {{ $ewaybill_no }}
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="2"
                                        style="
                                            font-size:10px;
                                            border-top:1px solid #000;
                                            padding:6px;
                                        ">
                                        <strong>Note:</strong>
                                        Any discrepancy in the e-Way Bill should be reported to the
                                        concerned tax authority within 24 hours.
                                    </td>
                                </tr>

                            </table>
                        </div>
                    </div>

                    <div id="detailedView" style="display:none;">
                        <!-- Header -->
                        @php
                            $qrContent = "";
                            $Irn = "";
                            $AckNo = "";
                            $AckDt = "";

                            if ($sale->e_invoice_status == 1 && !empty($sale->einvoice_response)) {
                                $einvoice_data = json_decode($sale->einvoice_response);

                                $qrContent = $einvoice_data->SignedQRCode ?? "";
                                $Irn = $einvoice_data->Irn ?? "";
                                $AckNo = $einvoice_data->AckNo ?? "";
                                $AckDt = $einvoice_data->AckDt ?? "";
                            }

                            if ($sale->e_waybill_status == 1 && !empty($sale->eway_bill_response)) {
                                $qrContent = $sale->eway_bill_response;
                            }
                        @endphp

                        <table style="width:100%;border-collapse:collapse;border:1px solid #000;font-size:11px;">
                            <tr>
                                <td style="width:82%;padding:6px;border-bottom:1px solid #000;">
                                    <h4 style="margin:0;font-weight:bold;">
                                        e-Way Bill
                                    </h4>
                                </td>

                                <td style="width:18%;text-align:center;border-left:1px solid #000;">
                                    @if(!empty($qrContent))
                                        {!! QrCode::size(80)->generate($qrContent) !!}
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <div style="border:1px solid #000;border-top:none;font-size:11px;">

                            <div style="
                                padding:4px;
                                font-weight:bold;
                                border-bottom:1px solid #000;
                                background:#fafafa;
                            ">
                                1. E-WAY BILL DETAILS
                            </div>

                            <table style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="width:27%;padding:4px;">
                                        eWay Bill No:
                                        <strong>{{ $ewaybill_no }}</strong>
                                    </td>

                                    <td style="width:38%;padding:4px;">
                                        Generated Date:
                                        <strong>
                                            @if(!empty($ewayBillDate))
                                                {{ \Carbon\Carbon::parse($ewayBillDate)->format('d/m/Y h:i A') }}
                                            @endif
                                        </strong>
                                    </td>

                                    <td style="padding:4px;">
                                        Generated By:
                                        <strong>{{ $sale->merchant_gst }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:4px;">
                                        Mode:
                                        <strong>Road</strong>
                                    </td>

                                    <td style="padding:4px;">
                                        Approx Distance:
                                        <strong>{{ $sale->e_waybill_distance }} Km</strong>
                                    </td>

                                    <td style="padding:4px;">
                                        Valid Upto:
                                        <strong>
                                            {{ \Carbon\Carbon::parse($validUpto)->format('d/m/Y') }}
                                        </strong>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:4px;">
                                        Type:
                                        <strong>Outward Supply</strong>
                                    </td>

                                    <td style="padding:4px;">
                                        Document Details:
                                        <strong>
                                            {{ $sale->voucher_no_prefix }}
                                            -
                                            {{ date('d/m/Y', strtotime($sale->date)) }}
                                        </strong>
                                    </td>

                                    <td style="padding:4px;">
                                        Transaction Type:
                                        <strong>-</strong>
                                        &nbsp;&nbsp;&nbsp;
                                        Portal:
                                        <strong>1</strong>
                                    </td>
                                </tr>

                                @if(!empty($Irn))
                                    <tr>
                                        <td colspan="3" style="padding:4px;">
                                            <span style="font-weight:normal;">IRN:</span>
                                            <strong style="
                                                display:inline;
                                                font-size:10px;
                                                margin-left:5px;
                                                word-break:break-all;
                                            ">
                                                {{ $Irn }}
                                            </strong>
                                        </td>
                                    </tr>
                                @endif
                            </table>

                        </div>

                        <!-- 2. Address Details -->
                        <div style="border:1px solid #000;border-top:none;font-size:11px;">

                            <div style="padding:4px;font-weight:bold;border-bottom:1px solid #000;background:#fafafa;">
                                2. Address Details
                            </div>

                            <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                                <tr>
                                    <!-- FROM -->
                                    <td style="width:50%;vertical-align:top;border-right:1px solid #000;padding:4px;">
                                        <div style="font-weight:bold;border-bottom:1px solid #ddd;margin-bottom:3px;">
                                            From
                                        </div>

                                        <div>
                                            GSTIN :
                                            <strong>{{ $seller_info->gst_no }}</strong>
                                        </div>

                                        <div style="margin-top:4px;">
                                            {{ strtoupper($company_data->company_name) }}
                                        </div>

                                        <div style="margin-top:4px;">
                                            {{ strtoupper($seller_info->address) }}
                                        </div>

                                        <div>
                                            {{ strtoupper($seller_info->sname) }} - {{ $seller_info->pincode }}
                                        </div>

                                        <hr style="margin:6px 0;">

                                        <div style="font-weight:bold;">
                                            Dispatch From :
                                        </div>

                                        <div>
                                            {{ strtoupper($seller_info->address) }}
                                        </div>

                                        <div>
                                            {{ strtoupper($seller_info->sname) }} - {{ $seller_info->pincode }}
                                        </div>
                                    </td>

                                    <!-- TO -->
                                    <td style="width:50%;vertical-align:top;padding:4px;">
                                        <div style="font-weight:bold;border-bottom:1px solid #ddd;margin-bottom:3px;">
                                            To
                                        </div>

                                        <div>
                                            GSTIN :
                                            <strong>{{ $sale->billing_gst }}</strong>
                                        </div>

                                        <div style="margin-top:4px;">
                                            {{ strtoupper($sale->billing_name) }}
                                        </div>

                                        <div style="margin-top:4px;">
                                            {{ strtoupper($sale->billing_address) }}
                                        </div>

                                        <div>
                                            {{ strtoupper($sale->billing_state_name) }} - {{ $sale->billing_pincode }}
                                        </div>

                                        <hr style="margin:6px 0;">

                                        <div style="font-weight:bold;">
                                            Ship To :
                                        </div>

                                        @if($sale->shipping_name)
                                            <div>
                                                {{ strtoupper($sale->shipping_address) }}
                                            </div>

                                            <div>
                                                {{ strtoupper($sale->shipping_state_name) }} - {{ $sale->shipping_pincode }}
                                            </div>
                                        @else
                                            <div>
                                                {{ strtoupper($sale->billing_address) }}
                                            </div>

                                            <div>
                                                {{ strtoupper($sale->billing_state_name) }} - {{ $sale->billing_pincode }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                        </div>

                        <!-- ==================== 3. GOODS DETAILS ==================== -->
                        <div style="border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;border-top:none;font-size:11px;">

                            <div style="
                                padding:4px;
                                font-weight:bold;
                                border-bottom:1px solid #000;
                                background:#fafafa;
                            ">
                                3. Goods Details
                            </div>

                            <table style="width:100%;border-collapse:collapse;table-layout:fixed;">
                                <thead>
                                    <tr>
                                        <th style="width:14%;border:1px solid #000;padding:4px;text-align:center;">
                                            HSN Code
                                        </th>

                                        <th style="width:40%;border:1px solid #000;padding:4px;text-align:center;">
                                            Product Name &amp; Description
                                        </th>

                                        <th style="width:14%;border:1px solid #000;padding:4px;text-align:center;">
                                            Quantity
                                        </th>

                                        <th style="width:17%;border:1px solid #000;padding:4px;text-align:center;">
                                            Taxable Amount
                                        </th>

                                        <th style="width:15%;border:1px solid #000;padding:4px;text-align:center;">
                                            Tax Rate
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $item_total = 0;
                                    @endphp

                                    @foreach($items_detail as $item)
                                        <tr>
                                            <td style="border:1px solid #000;padding:4px;text-align:left;vertical-align:middle;">
                                                {{ $item->hsn_code }}
                                            </td>

                                            <td style="border:1px solid #000;padding:4px;vertical-align:middle;">
                                                {{ strtoupper($item->items_name) }}
                                            </td>

                                            <td style="border:1px solid #000;padding:4px;text-align:right;vertical-align:middle;">
                                                {{ formatIndianNumber($item->qty) }}
                                                {{ $item->unit }}
                                            </td>

                                            <td style="border:1px solid #000;padding:4px;text-align:right;vertical-align:middle;">
                                                {{ formatIndianNumber($item->amount) }}
                                            </td>

                                            <td style="border:1px solid #000;padding:4px;text-align:center;vertical-align:middle;">
                                                @php
                                                    $rates = $sale_sundry->where('rate', '!=', 0)->pluck('rate')->toArray();
                                                @endphp
                                                {{ implode(' + ', $rates) }}
                                            </td>
                                        </tr>

                                        @php
                                            $item_total += $item->amount;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>

                        </div>

                        <!-- ==================== TOTALS ==================== -->
                        @php
                            $cgst = 0;
                            $sgst = 0;
                            $igst = 0;
                            $other = 0;

                            foreach ($sale_sundry as $sundry) {
                                if ($sundry->nature_of_sundry == "CGST") {
                                    $cgst += $sundry->amount;
                                    $sgst += $sundry->amount;
                                } elseif ($sundry->nature_of_sundry == "IGST") {
                                    $igst += $sundry->amount;
                                } elseif (
                                    $sundry->nature_of_sundry == "ROUNDED OFF (+)" ||
                                    $sundry->nature_of_sundry == "ROUNDED OFF (-)"
                                ) {
                                    if ($sundry->bill_sundry_type == "additive") {
                                        $other += $sundry->amount;
                                    } else {
                                        $other -= $sundry->amount;
                                    }
                                }
                            }
                        @endphp

                        <div style="border:1px solid #000;border-top:none;font-size:11px;">
                            <table style="width:100%;border-collapse:collapse;">
                                <tr style="background:#fafafa;">
                                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold;">
                                        Total Taxable Amount
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold;">
                                        CGST
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold;">
                                        SGST
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold;">
                                        IGST
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold;">
                                        Other
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:center;font-weight:bold;">
                                        Total Invoice Amount
                                    </td>
                                </tr>

                                <tr>
                                    <td style="border:1px solid #000;padding:4px;text-align:right;">
                                        {{ formatIndianNumber($item_total) }}
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:right;">
                                        {{ formatIndianNumber($cgst) }}
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:right;">
                                        {{ formatIndianNumber($sgst) }}
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:right;">
                                        {{ formatIndianNumber($igst) }}
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:right;">
                                        @if($other > 0)
                                            (+){{ formatIndianNumber($other) }}
                                        @elseif($other < 0)
                                            (-){{ formatIndianNumber(abs($other)) }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td style="border:1px solid #000;padding:4px;text-align:right;font-weight:bold;">
                                        {{ formatIndianNumber($sale->total) }}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- ==================== 4. TRANSPORTATION DETAILS ==================== -->
                        <div style="border:1px solid #000;border-top:none;font-size:11px;">

                            <div style="
                                padding:4px;
                                font-weight:bold;
                                border-bottom:1px solid #000;
                                background:#fafafa;
                            ">
                                4. Transportation Details
                            </div>

                            <table style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="width:50%;padding:8px 10px;border-right:1px solid #000;">
                                        Transporter ID &amp; Name :
                                        <strong>
                                            -
                                        </strong>
                                    </td>

                                    <td style="width:50%;padding:8px 10px;">
                                        Transporter Doc. No &amp; Date :
                                        <strong>
                                            {{ $sale->gr_pr_no ?: '-' }}

                                            @if(!empty($sale->gr_pr_date))
                                                &amp; {{ date('d/m/Y', strtotime($sale->gr_pr_date)) }}
                                            @else
                                                &amp; {{ date('d/m/Y', strtotime($sale->date)) }}
                                            @endif
                                        </strong>
                                    </td>
                                </tr>
                            </table>

                        </div>

                        <!-- ==================== 5. VEHICLE DETAILS ==================== -->
                        <div style="border:1px solid #000;border-top:none;font-size:11px;">

                            <div style="
                                padding:4px;
                                font-weight:bold;
                                border-bottom:1px solid #000;
                                background:#fafafa;
                            ">
                                5. Vehicle Details
                            </div>

                            <div style="padding:8px;">
                                <table style="width:100%;border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th style="border:1px solid #000;padding:6px;width:6%;text-align:left;">
                                                Mode
                                            </th>

                                            <th style="border:1px solid #000;padding:6px;width:28%;text-align:left;">
                                                Vehicle / Trans<br>
                                                Doc No &amp; Dt.
                                            </th>

                                            <th style="border:1px solid #000;padding:6px;width:8%;text-align:left;">
                                                From
                                            </th>

                                            <th style="border:1px solid #000;padding:6px;width:17%;text-align:left;">
                                                Entered Date
                                            </th>

                                            <th style="border:1px solid #000;padding:6px;width:17%;text-align:left;">
                                                Entered By
                                            </th>

                                            <th style="border:1px solid #000;padding:6px;width:10%;text-align:left;">
                                                CEWB No.<br>(If any)
                                            </th>

                                            <th style="border:1px solid #000;padding:6px;width:12%;text-align:left;">
                                                Multi Veh.Info<br>(If any)
                                            </th>

                                            <th style="border:1px solid #000;padding:6px;width:5%;text-align:left;">
                                                Portal
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td style="border:1px solid #999;padding:6px;">
                                                -
                                            </td>

                                            <td style="border:1px solid #999;padding:6px;">
                                                {{ strtoupper($sale->vehicle_no ?: '-') }}
                                                <strong>&amp;</strong>
                                                {{ $sale->gr_pr_no ?: '-' }}
                                                <strong>&amp;</strong>
                                                {{ date('d/m/Y', strtotime($sale->date)) }}
                                            </td>

                                            <td style="border:1px solid #999;padding:6px;">
                                                {{ strtoupper($seller_info->sname) }}
                                            </td>

                                            <td style="border:1px solid #999;padding:6px;">
                                                @if(!empty($ewayBillDate))
                                                    {{ \Carbon\Carbon::parse($ewayBillDate)->format('d/m/Y h:i A') }}
                                                @else
                                                    {{ date('d/m/Y h:i A', strtotime($sale->date)) }}
                                                @endif
                                            </td>

                                            <td style="border:1px solid #999;padding:6px;">
                                                {{ $sale->merchant_gst }}
                                            </td>

                                            <td style="border:1px solid #999;padding:6px;">
                                                -
                                            </td>

                                            <td style="border:1px solid #999;padding:6px;">
                                                -
                                            </td>

                                            <td style="border:1px solid #999;padding:6px;">
                                                1
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>

                        <!-- ==================== BARCODE ==================== -->
                        <div style="
                            border:1px solid #000;
                            border-top:none;
                            text-align:center;
                            padding:12px 0;
                            font-size:11px;
                        ">
                            <svg id="barcodeDetailed"></svg>

                            <div style="
                                margin-top:4px;
                                font-size:10px;
                                font-weight:bold;
                                letter-spacing:1px;
                            ">
                                {{ $ewaybill_no }}
                            </div>
                        </div>

                        <div style="
                            border:1px solid #000;
                            border-top:none;
                            padding:6px;
                            font-size:10px;
                        ">
                            <strong>Note:</strong>
                            Any discrepancy in the e-Way Bill should be reported to the concerned tax authority within 24 hours.
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')
<script>
    function printpage(){
        $('.header-section').hide();
        $('.sidebar').hide();
        $('body').removeClass('print-simple print-detailed');
        if($('#detailed_view').is(':checked')){
            $('body').addClass('print-detailed');
        }else{
            $('body').addClass('print-simple');
        }
        window.print();
        $('body').removeClass('print-simple print-detailed');
        $('.header-section').show();
        $('.sidebar').show();
    }
    $(document).ready(function(){

    $('#simpleView').show();

    $('#detailedView').hide();

    $('#detailed_view').change(function(){

        if($(this).is(':checked')){

            $('#simpleView').hide();

            $('#detailedView').show();

        }else{

            $('#simpleView').show();

            $('#detailedView').hide();

        }

    });

});
</script>
@endsection

