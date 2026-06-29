<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
<head>
    <meta charset="UTF-8">
    <title>Sales Bill</title>

</head>
<body>
    {{-- BILL CODE..... --}}
    @php
    $PX_LINE            = 21;
    $PX_PAGE            = 1084;
    $PX_HEADER_NO_IRN   = 420;
    $PX_HEADER_IRN      = 504;
    $PX_BF_ROW          = 21;
    $PX_CF_ROW          = 21;

    $printHasIrn      = ($sale_detail->e_invoice_status == 1 && !empty($sale_detail->einvoice_response));
    $px_header_actual = $printHasIrn ? $PX_HEADER_IRN : $PX_HEADER_NO_IRN;

    $PX_TERM_LINE     = 12;
    $PX_TERM_HEADER    = 24;
    $PX_SIGNATURE_MIN  = 80;
    $PX_FOOTER_PAD    = 8;

    $termCount = 0;
    if ($configuration && $configuration->term_status == 1 && $configuration->terms) {
        $termCount = count($configuration->terms);
    }
    $effectiveTerms = max(9, $termCount);
    $px_terms_content = $PX_TERM_HEADER + ($effectiveTerms * $PX_TERM_LINE);
    $PX_FOOTER = max($px_terms_content, $PX_SIGNATURE_MIN) + $PX_FOOTER_PAD;

    $printTotalCGST = 0;
    $printTotalSGST = 0;
    $printTotalIGST = 0;
    $printDisplaySundries = [];

    foreach ($sale_sundry as $s) {
        $nat = strtoupper($s->nature_of_sundry);
        if ($nat === 'CGST') {
            $printTotalCGST += $s->amount;
        } elseif ($nat === 'SGST') {
            $printTotalSGST += $s->amount;
        } elseif ($nat === 'IGST') {
            $printTotalIGST += $s->amount;
        } else {
            $printDisplaySundries[] = $s;
        }
    }

    $sundryRows = count($printDisplaySundries);
    if ($printTotalCGST > 0) $sundryRows++;
    if ($printTotalSGST > 0) $sundryRows++;
    if ($printTotalIGST > 0) $sundryRows++;
    $sundryRows = max(1, $sundryRows);

    $bankRow = ($configuration && $configuration->bank_detail_status == 1 && $bank_detail) ? 1 : 0;
    $gstRows = count($gst_detail);

    $PX_FINANCIAL =
        $PX_LINE
        + ($sundryRows * $PX_LINE)
        + $PX_LINE
        + ((2 + $gstRows + 1) * 14)
        + $PX_LINE
        + ($bankRow * $PX_LINE);

    $fnItemPx = function ($item) use ($PX_LINE) {
        $sub = 0;
        if (isset($item->lines) && (is_array($item->lines) || is_countable($item->lines))) {
            $sub = count($item->lines);
        }
        return (1 + $sub) * $PX_LINE;
    };

    $items = $items_detail->values()->all();
    $totalItems = count($items);

    $printPages = [];
    $idx = 0;
    $runningQty = 0;
    $runningAmt = 0;

    $middleArea = $PX_PAGE - $px_header_actual - $PX_CF_ROW - $PX_FOOTER;
    $finalAreaFirst = $PX_PAGE - $px_header_actual - $PX_FINANCIAL - $PX_FOOTER;
    $finalAreaOther = $PX_PAGE - $px_header_actual - $PX_BF_ROW - $PX_FINANCIAL - $PX_FOOTER;

    while ($idx < $totalItems) {
        $isFirst = count($printPages) === 0;
        $remaining = array_slice($items, $idx);

        $remainPx = 0;
        foreach ($remaining as $r) {
            $remainPx += $fnItemPx($r);
        }

        $finalArea = $isFirst ? $finalAreaFirst : $finalAreaOther;

        if ($remainPx <= $finalArea) {
            $sumQty = 0;
            $sumAmt = 0;
            foreach ($remaining as $r) {
                $sumQty += $r->qty ?? 0;
                $sumAmt += $r->amount ?? 0;
            }

            $printPages[] = [
                'items' => $remaining,
                'bf_qty' => $isFirst ? 0 : $runningQty,
                'bf_amount' => $isFirst ? 0 : $runningAmt,
                'cf_qty' => $runningQty + $sumQty,
                'cf_amount' => $runningAmt + $sumAmt,
                'show_final_block' => true,
                'is_first' => $isFirst,
            ];
            break;
        }

        $packItems = [];
        $packPx = 0;

        foreach ($remaining as $r) {
            $rPx = $fnItemPx($r);

            if (!empty($packItems) && ($packPx + $rPx) > $middleArea) {
                break;
            }

            $packItems[] = $r;
            $packPx += $rPx;
        }

        if (empty($packItems) && !empty($remaining)) {
            $packItems[] = $remaining[0];
            $packPx = $fnItemPx($remaining[0]);
        }

        $packQty = 0;
        $packAmt = 0;
        foreach ($packItems as $pi) {
            $packQty += $pi->qty ?? 0;
            $packAmt += $pi->amount ?? 0;
        }

        $printPages[] = [
            'items' => $packItems,
            'bf_qty' => $isFirst ? 0 : $runningQty,
            'bf_amount' => $isFirst ? 0 : $runningAmt,
            'cf_qty' => $runningQty + $packQty,
            'cf_amount' => $runningAmt + $packAmt,
            'show_final_block' => false,
            'is_first' => $isFirst,
        ];

        $runningQty += $packQty;
        $runningAmt += $packAmt;
        $idx += count($packItems);
    }

    if ($totalItems === 0) {
        $printPages[] = [
            'items' => [],
            'bf_qty' => 0,
            'bf_amount' => 0,
            'cf_qty' => 0,
            'cf_amount' => 0,
            'show_final_block' => true,
            'is_first' => true,
        ];
    }

    $lastIdx = count($printPages) - 1;
    if ($lastIdx >= 0 && empty($printPages[$lastIdx]['show_final_block'])) {
        $lp = $printPages[$lastIdx];
        $printPages[] = [
            'items' => [],
            'bf_qty' => $lp['cf_qty'],
            'bf_amount' => $lp['cf_amount'],
            'cf_qty' => $lp['cf_qty'],
            'cf_amount' => $lp['cf_amount'],
            'show_final_block' => true,
            'is_first' => false,
        ];
    }

    for ($i = 0; $i < count($printPages) - 1; $i++) {
        while (!empty($printPages[$i + 1]['items'])) {
            $moved = $printPages[$i + 1]['items'][0];
            $movedPx = $fnItemPx($moved);

            $currentUsed = 0;
            foreach ($printPages[$i]['items'] as $it) {
                $currentUsed += $fnItemPx($it);
            }

            if (!$printPages[$i]['is_first']) {
                $currentUsed += $PX_BF_ROW;
            }

            $currentLimit = $printPages[$i]['show_final_block']
                ? ($printPages[$i]['is_first'] ? $finalAreaFirst : $finalAreaOther)
                : $middleArea;

            if (($currentUsed + $movedPx) > $currentLimit) {
                break;
            }

            array_shift($printPages[$i + 1]['items']);
            $printPages[$i]['items'][] = $moved;

            $mQty = $moved->qty ?? 0;
            $mAmt = $moved->amount ?? 0;

            $printPages[$i]['cf_qty'] += $mQty;
            $printPages[$i]['cf_amount'] += $mAmt;

            $printPages[$i + 1]['bf_qty'] = $printPages[$i]['cf_qty'];
            $printPages[$i + 1]['bf_amount'] = $printPages[$i]['cf_amount'];

            $printPages[$i + 1]['cf_qty'] -= $mQty;
            $printPages[$i + 1]['cf_amount'] -= $mAmt;
        }
    }
    @endphp

    <style>
        .invoice-pdf-wrap table{
    width:100%;
    border-spacing:0;
    border:1px solid #dadada;
    border-collapse:collapse;
}
.invoice-pdf-wrap table.invoice-items-table{
    table-layout:fixed;
}
        .invoice-pdf-wrap table tr th,
        .invoice-pdf-wrap table tr td{
            border:1px solid #000000;
            margin:0;
            padding:2px 5px;
            vertical-align:top;
        }
        .invoice-pdf-wrap p{
            margin:0.5px !important;
        }
        .invoice-pdf-wrap .width25{
            width:35%;
        }
        .invoice-pdf-wrap .lft_mar15{
            margin-left:15px;
        }
        .invoice-pdf-wrap .wrap-text{
            display:inline-block;
            max-width:55%;
            word-wrap:break-word;
            word-break:break-word;
            white-space:normal;
            vertical-align:top;
        }
        .invoice-total{
            font-size:16px;
            font-weight:800;
            margin:0;
            white-space:nowrap;
        }

        .gst-summary-table{
            width:45% !important;
            display:inline-table !important;
            border-collapse:collapse !important;
        }
        .gst-summary-table td,
        .gst-summary-table th{
            border:none !important;
        }
        .invoice-company-header{
            height:130px;
            min-height:130px;
            max-height:130px;
            overflow:hidden;
            position:relative;
        }
        .invoice-header-table{
            width:100%;
            border:none !important;
            border-collapse:collapse !important;
        }
        .invoice-header-table td{
            border:none !important;
            vertical-align:top;
        }
.invoice-logo-left,
.invoice-logo-right{
    position:absolute;
    top:55px;
    width:65px;
    height:50px;
    overflow:hidden;
}

.invoice-logo-left{
    left:15px;
}

.invoice-logo-right{
    right:15px;
}
.invoice-logo-left img,
.invoice-logo-right img{
    max-width:100%;
    max-height:100%;
    width:auto;
    height:auto;
}
        @page {
            size: A4;
            margin: 0.4in;
        }
        .pdf-footer-row,
        .pdf-financial-row,
        tr,
        td,
        th {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .print-page-footer{
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        }

        .print-page-footer tr{
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        }

        .print-page-footer td{
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        }
    </style>

    <div class="invoice-pdf-wrap">
        @php
        $printSerial = 1;
        @endphp
        @foreach ($printPages as $printPageIndex => $printPage)
        @php
            $printIsFirstPage    = ($printPageIndex === 0);
            $printShowFinalBlock = !empty($printPage['show_final_block']);
            $printHasItems       = count($printPage['items']) > 0;
            $printShowBfRow      = (!$printIsFirstPage); // show B/F on every non-first page

            // Recalculate used px for this page to find spacer
            $usedPx = $px_header_actual;
            if ($printShowBfRow)      $usedPx += $PX_BF_ROW;
            foreach ($printPage['items'] as $pi) $usedPx += $fnItemPx($pi);
            if ($printShowFinalBlock) $usedPx += $PX_FINANCIAL;
            else                      $usedPx += $PX_CF_ROW;
            $usedPx += $PX_FOOTER;

            $printSpacerPx = max(0, $PX_PAGE - $usedPx - 50);
        @endphp
        <div class="print-wrapper{{ ($printShowFinalBlock && !$printHasItems) ? ' print-summary-page' : '' }}">
        <table class="invoice-items-table" style="font-family:'Source Sans Pro', sans-serif; letter-spacing:0.05em; color:#404040; font-size:12px; font-weight:500;">
            <colgroup>
                <col style="width:5%">
                <col style="width:17%">
                <col style="width:11%">
                <col style="width:17%">
                <col style="width:13%">
                <col style="width:5%">
                <col style="width:14%">
                <col style="width:18%">
            </colgroup>
            <tbody>
                <tr>
                    <th colspan="8" style="padding:0;">
                    <div class="invoice-company-header">
                        <table class="invoice-header-table">
                            <tr>
                                <td style="width:33%; text-align:left; padding-left:5px;">
                                <strong style="color: {{ $configuration->address_color ?? 'black' }};">
                                    GSTIN: {{ $seller_info->gst_no }}
                                </strong>
                                </td>
                                <td style="width:34%; text-align:center;">
                                @if($configuration && !empty($configuration->invoice_header_text))
                                    <strong style="font-size:13px; font-weight:700; letter-spacing:1px; color: {{ $configuration->address_color ?? 'black' }};">
                                        {{ $configuration->invoice_header_text }}
                                    </strong>
                                @endif
                                </td>
                                <td style="width:33%; text-align:right; padding-right:5px;">
                                <strong style="color: {{ $configuration->address_color ?? 'black' }};">
                                    PAN: {{ substr($seller_info->gst_no, 2, 10) }}
                                </strong><br>
                                <small style="color: {{ $configuration->address_color ?? 'black' }};">O/D/T</small>
                                </td>
                            </tr>
                        </table>
                        @php
                            $printCompanyName = $company_data->company_name;
                            $printFontSize = strlen($printCompanyName) > 30 ? '18px' : '24px';
                            if ($configuration && $configuration->company_name_font_size != "") {
                                $printFontSize = $configuration->company_name_font_size;
                            }
                        @endphp
                        @if($configuration && $configuration->company_logo_status==1 && $configuration->logo_position_left==1 && !empty($configuration->company_logo))
                            <div class="invoice-logo-left">
                                <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                            </div>
                        @endif
                        @if($configuration && $configuration->company_logo_status==1 && $configuration->logo_position_right==1 && !empty($configuration->company_logo))
                            <div class="invoice-logo-right">
                                <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                            </div>
                        @endif
                        <div style="text-align:center; line-height:1; margin:0; padding:0;">
                            <p style="margin:0;color: {{ $configuration->address_color ?? 'black' }};"><u>TAX INVOICE</u></p>
                            <p style="margin:0; font-size: {{ $printFontSize }}; font-weight:bold; color: {{ $configuration->company_name_color ?? 'black' }};">
                                {{ $printCompanyName }}
                            </p>
                            <p style="margin:0;">
                                <small style="font-size:12px; display:inline-block; max-width:50%; word-break:break-word; color: {{ $configuration->address_color ?? 'black' }};">
                                {{ $seller_info->address }}
                                </small>
                            </p>
                            <p style="margin:0;">
                                <small style="font-size:12px; color: {{ $configuration->address_color ?? 'black' }};">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small>
                            </p>
                        </div>
                    </div>
                    </th>
                </tr>

                @if($sale_detail->e_invoice_status==1 && !empty($sale_detail->einvoice_response))
                    @php
                    $printEinvoiceData = json_decode($sale_detail->einvoice_response);
                    $printQrContent = $printEinvoiceData->SignedQRCode;
                    @endphp
                    <tr>
                    <td colspan="8" style="min-height:190px; vertical-align:top;">
                        <span style="float:right; width:70px; height:70px; position:relative;">
                            @if($qrBase64)
                                <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width:70px; height:70px; display:block;">
                            @endif
                           
                        </span>
                        <p>IRN NO. : {{ $printEinvoiceData->Irn }}</p>
                        <p>ACK.NO. : {{ $printEinvoiceData->AckNo }}</p>
                        <p>ACK.DATE : {{ $printEinvoiceData->AckDt }}</p>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>
                    </td>
                    </tr>
                @endif

                <tr>
            <td colspan="4">
               <p><span class="width25">Invoice No. </span>:  <span class="lft_mar15" style="font-weight:800">{{$sale_detail->voucher_no_prefix}}</span></p>
               <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($sale_detail->date))}}</span></p>
               <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">{{$sale_detail->sname}}</span></p>
               <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15">{{$sale_detail->reverse_charge}}</span></p>
               <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{$sale_detail->gr_pr_no}}</span></p>
            </td>
            <td colspan="4">
               <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text">{{$sale_detail->transport_name}}</span> </p>
               <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{$sale_detail->vehicle_no}}</span> </p>
               <p><span class="width25">Station </span>: <span class="lft_mar15">{{$sale_detail->station}}</span> </p>
               <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15">
                  <?php
                  if ($sale_detail->e_waybill_status==1 && $sale_detail->eway_bill_response && !empty($sale_detail->eway_bill_response)) {
                     $printEwaybillData = json_decode($sale_detail->eway_bill_response);
                     echo $printEwaybillData->ewayBillNo;
                  }
                  ?>
               </span> </p>
               <p>&nbsp;</p>
               @if($configuration && $configuration->purchase_order_status == 1)
                  <p><span class="width25">PO No. </span>: <span class="lft_mar15">{{ $sale_detail->po_no }}</span></p>
                  <p><span class="width25">PO Date </span>: <span class="lft_mar15">{{ $sale_detail->po_date ? date('d-m-Y', strtotime($sale_detail->po_date)) : '' }}</span></p>
               @endif
               @if($company_sale_type=="BOX")
                  <p><span class="width25">PO No. </span>: <span class="lft_mar15">{{ $box_po_numbers ?? '' }}</span></p>
                  <p><span class="width25">PO Date </span>: <span class="lft_mar15">{{ $box_po_dates ?? '' }}</span></p>
               @endif
            </td>
         </tr>
        <tr>
            <!-- BILL TO -->
            <td colspan="4"
                style="
                    position:relative;
                    vertical-align:top;
                    padding:0;
                    height:120px;
                ">

                <p style="
                    margin:0;
                    position:absolute;
                    top:0;
                    left:5px;
                    font-style:italic;
                ">
                    <strong>Billed to :</strong>
                </p>

                <div style="
                    padding-top:16px;
                    margin-left:10px;
                    margin-right:5px;
                    padding-bottom:30px;
                    max-height:80px;
                    overflow:hidden;
                ">
                    <p style="margin:2px 0 0 0; line-height:14px; font-weight:800;">
                        {{$sale_detail->billing_name}}
                    </p>

                    <p style="margin:2px 0 0 0; line-height:11px; font-size:10px;">
                        {{$sale_detail->billing_address}}
                    </p>
                </div>

                <div style="
                    position:absolute;
                    bottom:0;
                    left:5px;
                    right:4px;
                ">
                    <p style="margin:4px 0 0 0; font-weight:800; font-size:10px;">
                        GSTIN/UIN : {{$sale_detail->billing_gst}}
                        <span style="float:right;">PAN:{{$sale_detail->billing_pan}}</span>
                    </p>
                </div>

            </td>

            <!-- SHIP TO -->
            <td colspan="4"
                style="
                    position:relative;
                    vertical-align:top;
                    padding:0;
                    height:120px;
                ">

                <p style="
                    margin:0;
                    position:absolute;
                    top:0;
                    left:5px;
                    font-style:italic;
                ">
                    <strong>Shipped to :</strong>
                </p>

                <div style="
                    padding-top:16px;
                    margin-left:10px;
                    margin-right:5px;
                    padding-bottom:30px;
                    max-height:80px;
                    overflow:hidden;
                ">
                    <p style="margin:2px 0 0 0; line-height:14px; font-weight:800;">
                        @if($sale_detail->shipping_name)
                            {{$sale_detail->shipp_name}}
                        @else
                            {{$sale_detail->billing_name}}
                        @endif
                    </p>

                    <p style="margin:2px 0 0 0; line-height:11px; font-size:10px;">
                        @if($sale_detail->shipping_name)
                            {{$sale_detail->shipping_address}}
                        @else
                            {{$sale_detail->billing_address}}
                        @endif
                    </p>

                </div>

                <div style="
                    position:absolute;
                    bottom:0;
                    left:5px;
                    right:4px;
                ">
                    <p style="margin:4px 0 0 0; font-weight:800; font-size:10px;">
                        @if($sale_detail->shipping_name)
                            GSTIN/UIN : {{$sale_detail->shipping_gst}}
                            <span style="float:right;">PAN:{{$sale_detail->shipping_pan}}</span>
                        @else
                            GSTIN/UIN : {{$sale_detail->billing_gst}}
                            <span style="float:right;">PAN:{{$sale_detail->billing_pan}}</span>
                        @endif
                    </p>
                </div>

            </td>
        </tr>
          <tr>
            <th style="width:5%;padding: 0px 3px;">S. No.</th>
            <th colspan="2" style="text-align:left; width:28%;">Description of Goods</th>
            <th style="text-align:center; width:17%;">HSN/SAC Code</th>
            <th style="text-align:right; width:13%;">Qty.</th>
            <th style="text-align:center; width:5%;">Unit</th>
            <th style="text-align:right; width:14%;">Price</th>
            <th style="text-align:right; width:18%;">Amount (₹)</th>
         </tr>

                @if($printShowBfRow)
                    <tr style="font-weight:700;">
                    <td colspan="4" style="text-align:right;">B/F (Brought Forward)</td>
                    <td style="text-align:right;">{{$printPage['bf_qty']}}</td>
                    <td></td><td></td>
                    <td style="text-align:right;">{{formatIndianNumber($printPage['bf_amount'])}}</td>
                    </tr>
                @endif

                @foreach($printPage['items'] as $printItem)
                    <tr class="{{ ($configuration && $configuration->lines_in_item_status == 0) ? 'no-border' : '' }}">
                    <td style="text-align:center;">{{$printSerial}}</td>
                    <td colspan="2" style="text-align:left;">
                        <strong>{{ $printItem->p_name }}</strong>
                        @if($configuration && $configuration->show_item_name == 1)
                            <span style="font-size:9px; color:#777; margin-left:4px;">({{ $printItem->name }})</span>
                        @endif
                        @if(isset($printItem->lines) && count($printItem->lines) > 0)
                            @foreach($printItem->lines as $printLine)
                                <small style="display:block; font-size:10px; font-style:italic; color:#555; margin-left:10px;">
                                {{ $printLine->line_text }}
                                </small>
                            @endforeach
                        @endif
                    </td>
                    <td style="text-align:center;">{{$printItem->hsn_code}}</td>
                    <td style="text-align:right">{{$printItem->qty}}</td>
                    <td style="text-align:center">{{$printItem->unit}}</td>
                    <td style="text-align:right;">{{$printItem->price}}</td>
                    <td style="text-align:right; white-space:nowrap;">{{formatIndianNumber($printItem->amount)}}</td>
                    </tr>
                    @php $printSerial++; @endphp
                @endforeach

                @if(!$printShowFinalBlock)
                    <tr>
                    <td style="height:{{ $printSpacerPx }}px; padding:0;">&nbsp;</td>
                    <td colspan="2" style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    </tr>
                    <tr style="font-weight:700;">
                    <td colspan="4" style="text-align:right;">Carry Forward</td>
                    <td style="text-align:right;">{{$printPage['cf_qty']}}</td>
                    <td></td><td></td>
                    <td style="text-align:right;">{{formatIndianNumber($printPage['cf_amount'])}}</td>
                    </tr>
                @else
                    <tr>
                    <td style="height:{{ $printSpacerPx }}px; padding:0;">&nbsp;</td>
                    <td colspan="2" style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    <td style="padding:0;">&nbsp;</td>
                    </tr>

                    <tr>
                    <td colspan="4" style="border-bottom:0; border-right:0;"></td>
                    <td style="border-bottom:0; border-left:0; border-right:0; text-align:right;"><strong>{{$printPage['cf_qty']}}</strong></td>
                    <td style="border-bottom:0; border-left:0; border-right:0;"></td>
                    <td style="border-bottom:0; border-left:0;"><strong>Total</strong></td>
                    <td style="text-align:right; border-bottom:0;">{{formatIndianNumber($printPage['cf_amount'])}}</td>
                    </tr>

                    <tr>
                    <td style="border-right:0; border-top:0;" colspan="2"></td>
                    <td colspan="4" style="border-left:0; border-right:0; border-top:0;">
                        @foreach($printDisplaySundries as $printSundry)
                            @if(stripos($printSundry->name, 'round') === false)
                                @if($printSundry->bill_sundry_type == 'additive')
                                <p>Add : {{ $printSundry->name }}</p>
                                @else
                                <p>Less : {{ $printSundry->name }}</p>
                                @endif
                            @endif
                        @endforeach
                        @if($printTotalCGST > 0) <p>Add : CGST</p> @endif
                        @if($printTotalSGST > 0) <p>Add : SGST</p> @endif
                        @if($printTotalIGST > 0) <p>Add : IGST</p> @endif
                        @foreach($printDisplaySundries as $printSundry)
                            @if(stripos($printSundry->name, 'round') !== false)
                                @if($printSundry->bill_sundry_type == 'additive')
                                <p>Add : {{ $printSundry->name }}</p>
                                @else
                                <p>Less : {{ $printSundry->name }}</p>
                                @endif
                            @endif
                        @endforeach
                    </td>
                    <td style="border-left:0; border-top:0;">
                        @foreach($printDisplaySundries as $printSundry)
                            @if(stripos($printSundry->name, 'round') === false) <p>&nbsp;</p> @endif
                        @endforeach
                        @if($printTotalCGST > 0) <p>&nbsp;</p> @endif
                        @if($printTotalSGST > 0) <p>&nbsp;</p> @endif
                        @if($printTotalIGST > 0) <p>&nbsp;</p> @endif
                        @foreach($printDisplaySundries as $printSundry)
                            @if(stripos($printSundry->name, 'round') !== false) <p>&nbsp;</p> @endif
                        @endforeach
                    </td>
                    <td style="text-align:right; border-top:0;">
                        @foreach($printDisplaySundries as $printSundry)
                            @if(stripos($printSundry->name, 'round') === false)
                                <p>{{ formatIndianNumber($printSundry->amount) }}</p>
                            @endif
                        @endforeach
                        @if($printTotalCGST > 0) <p>{{ formatIndianNumber($printTotalCGST) }}</p> @endif
                        @if($printTotalSGST > 0) <p>{{ formatIndianNumber($printTotalSGST) }}</p> @endif
                        @if($printTotalIGST > 0) <p>{{ formatIndianNumber($printTotalIGST) }}</p> @endif
                        @foreach($printDisplaySundries as $printSundry)
                            @if(stripos($printSundry->name, 'round') !== false)
                                <p>{{ formatIndianNumber($printSundry->amount) }}</p>
                            @endif
                        @endforeach
                    </td>
                    </tr>
                    <tr>
                    <td colspan="7" style="text-align:right; border-right:0; border-bottom:0;">
                        <p><strong>Grand Total </strong></p>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <p><strong class="invoice-total">{{formatIndianNumber($sale_detail->total)}}</strong></p>
                    </td>
                    </tr>

                    <tr>
                    <td colspan="8" style="border-top:0; border-bottom:0; padding:2px 4px;">
                        <table class="gst-summary-table" style="width:45% !important; border:none; border-collapse:collapse; font-size:10px; display:inline-table;">
                            <tr>
                                <td style="border:none; padding:1px; font-weight:bold;">Tax Rate</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">Taxable Amt.</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">CGST</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">SGST</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">Total Tax</td>
                            </tr>
                            @php $totalTaxable = 0; $totalCGST = 0; $totalSGST = 0; @endphp
                            @foreach($gst_detail as $printVal)
                                @php
                                $totalTaxable += $printVal->taxable_amount;
                                $totalCGST += $printVal->amount;
                                $totalSGST += $printVal->amount;
                                @endphp
                                <tr>
                                <td style="border:none; padding:1px;">{{$printVal->rate}}%</td>
                                <td style="border:none; padding:1px; text-align:right;">{{formatIndianNumber($printVal->taxable_amount)}}</td>
                                <td style="border:none; padding:1px; text-align:right;">{{formatIndianNumber($printVal->amount)}}</td>
                                <td style="border:none; padding:1px; text-align:right;">{{formatIndianNumber($printVal->amount)}}</td>
                                <td style="border:none; padding:1px; text-align:right;">{{formatIndianNumber($printVal->amount * 2)}}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="5" style="padding:0; border:none;">
                                <hr style="margin:2px 0; border:none; border-top:1px solid #000;">
                                </td>
                            </tr>
                            <tr>
                                <td style="border:none; padding:1px; font-weight:bold;">Total</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">{{formatIndianNumber($totalTaxable)}}</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">{{formatIndianNumber($totalCGST)}}</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">{{formatIndianNumber($totalSGST)}}</td>
                                <td style="border:none; padding:1px; text-align:right; font-weight:bold;">{{formatIndianNumber($totalCGST + $totalSGST)}}</td>
                            </tr>
                        </table>
                    </td>
                    </tr>

                    <tr>
                    <td colspan="8" style="border-top:0;">
                        <strong>
                            @php
                                $printNumber = $sale_detail->total;
                                $printNo = floor($printNumber);
                                $printPoint = round($printNumber - $printNo, 2) * 100;
                                $printHundred = null;
                                $printDigits1 = strlen($printNo);
                                $printI = 0;
                                $printStr = array();
                                $printWords = array(
                                '0' => '', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four',
                                '5' => 'five', '6' => 'six', '7' => 'seven', '8' => 'eight', '9' => 'nine',
                                '10' => 'ten', '11' => 'eleven', '12' => 'twelve', '13' => 'thirteen',
                                '14' => 'fourteen', '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
                                '18' => 'eighteen', '19' => 'nineteen', '20' => 'twenty', '30' => 'thirty',
                                '40' => 'forty', '50' => 'fifty', '60' => 'sixty', '70' => 'seventy',
                                '80' => 'eighty', '90' => 'ninety'
                                );
                                $printDigits = array('', 'hundred', 'thousand', 'lakh', 'crore');
                                while ($printI < $printDigits1) {
                                $printDivider = ($printI == 2) ? 10 : 100;
                                $printNumber = floor($printNo % $printDivider);
                                $printNo = floor($printNo / $printDivider);
                                $printI += ($printDivider == 10) ? 1 : 2;
                                if ($printNumber) {
                                    $printPlural = (($printCounter = count($printStr)) && $printNumber > 9) ? 's' : null;
                                    $printHundred = ($printCounter == 1 && $printStr[0]) ? ' and ' : null;
                                    $printStr[] = ($printNumber < 21)
                                        ? $printWords[$printNumber] . " " . $printDigits[$printCounter] . $printPlural . " " . $printHundred
                                        : $printWords[floor($printNumber / 10) * 10] . " " . $printWords[$printNumber % 10] . " " . $printDigits[$printCounter] . $printPlural . " " . $printHundred;
                                } else {
                                    $printStr[] = null;
                                }
                                }
                                $printStr = array_reverse($printStr);
                                $printResult = implode('', $printStr);
                                echo ucfirst($printResult) . "Rupees  only";
                            @endphp
                        </strong>
                    </td>
                    </tr>

                    @if($configuration && $configuration->bank_detail_status == 1 && $bank_detail)
                    <tr>
                        <td colspan="8">
                            @if($configuration && $configuration->banks)
                                <p>
                                <strong>Bank Details : </strong> <strong>ACCOUNT NAME-</strong>{{$configuration->banks->name}}
                                <br><strong>ACCOUNT NO:</strong>{{$configuration->banks->account_no}} ,
                                <strong>IFSC CODE:</strong>{{$configuration->banks->ifsc}} ,
                                <strong>BANK NAME:</strong>{{$configuration->banks->bank_name}},{{$configuration->banks->branch}}
                                </p>
                            @endif
                        </td>
                    </tr>
                    @endif
                @endif
            </tbody>

            <tbody class="print-page-footer">
                    <tr>

                        <td colspan="4"
                            style="
                                vertical-align:top;
                                padding:2px 5px;
                                height:{{ $PX_FOOTER }}px;
                                min-height:{{ $PX_FOOTER }}px;
                            ">

                            @if($configuration && $configuration->term_status==1 && $configuration->terms && count($configuration->terms)>0)

                                <p style="margin:0;line-height:1.1;">
                                    <small><b>Terms &amp; Conditions</b></small>
                                </p>

                                <p style="margin:0;line-height:1.1;">
                                    <small>E.&amp; O.E.</small>
                                </p>

                                @php $printTermNo = 1; @endphp

                                @foreach($configuration->terms as $printTerm)
                                    <p style="margin:0;line-height:1.1;font-size:9px;">
                                        {{$printTermNo}}. {{$printTerm->term}}
                                    </p>
                                    @php $printTermNo++; @endphp
                                @endforeach
                                    <p style="margin:0;line-height:1.1;font-size:9px;">
                                        &nbsp;
                                    </p>
                            @endif

                        </td>
                        <td colspan="4"
                            style="
                            vertical-align:top;
                            padding:2px 5px;
                            height:{{ $PX_FOOTER }}px;
                            min-height:{{ $PX_FOOTER }}px;
                            position:relative;
                            ">

                            <div style="
                            height:{{ $PX_FOOTER - 10 }}px;
                            position:relative;
                            ">

                            <p style="
                                    margin:0;
                                    line-height:1.1;
                            ">
                                    <small>Receiver's Signature :</small>
                            </p>

                            <div style="height:20px;"></div>

                            <p style="
                                    text-align:right;
                                    margin:0;
                                    font-weight:bold;
                            ">
                                    for {{$company_data->company_name}}
                            </p>

                            @if(
                                $configuration &&
                                $configuration->signature_status == 1 &&
                                !empty($configuration->signature)
                            )

                                <div style="
                                    position:absolute;
                                    top:50%;
                                    right:2px;
                                    width:170px;
                                    text-align:right;
                                    height:150px;
                                    margin-left:-75px;
                                    margin-top:-75px;
                                    text-align:center;
                                    z-index:10;
                                ">
                                    <img
                                        src="{{ URL::asset('public/images')}}/{{$configuration->signature}}"
                                        style="
                                            width:150px;
                                            height:150px;
                                            object-fit:contain;
                                        "
                                    >
                                </div>

                            @endif

                            <p style="
                                    position:absolute;
                                    right:0;
                                    bottom:0;
                                    margin:0;
                                    font-weight:bold;
                            ">
                                    Authorised Signatory
                            </p>

                            </div>

                        </td>
                    </tr>
            </tbody>
        </table>
        </div>
    @endforeach
    </div>
    {{-- CHALLAN CODE.... --}}
    <?php
        $combinedItems = [];
        $grandTotal = 0;
        $totalReels = 0;
        $saleItemSizes = \DB::table('item_size_stocks')
                        ->join('manage_items', 'item_size_stocks.item_id', '=', 'manage_items.id')
                        ->join('sale_descriptions', 'item_size_stocks.sale_description_id', '=', 'sale_descriptions.id')
                        ->where('item_size_stocks.sale_id', $sale_detail->id)
                        ->select(
                            'item_size_stocks.sale_description_id', 
                            'manage_items.name as item_name',
                            'sale_descriptions.price',
                            'item_size_stocks.reel_no',
                            'item_size_stocks.size',
                            'item_size_stocks.weight',
                            'item_size_stocks.unit'
                        )
                        ->get();
        if(count($saleItemSizes)>0){
            foreach ($saleItemSizes as $size) {
                $rawSize = strtoupper(trim($size->size ?? '0X0'));
                $unit    = $size->unit ?? '';

                // split size like 12X120
                [$a, $b] = array_pad(explode('X', $rawSize), 2, 0);

                $combinedItems[] = [
                    'sale_description_id' => $size->sale_description_id,
                    'item_name' => $size->item_name ?? '-',
                    'price' => $size->price ?? 0,
                    'reel_no' => $size->reel_no ?? '-',
                    'size' => $rawSize . ' ' . $unit,
                    'size_a' => (float) $a,
                    'size_b' => (float) $b,
                    'weight' => (float) ($size->weight ?? 0),
                ];
            }
            $groupedItems = collect($combinedItems)
                            ->groupBy(function ($row) {
                            return $row['sale_description_id']; 
                            })
                            ->map(function ($rows) {
                            return $rows->sort(function ($a, $b) {
                            if ($a['size_b'] != $b['size_b']) {
                            return $a['size_b'] <=> $b['size_b'];
                            }
                            return $a['size_a'] <=> $b['size_a'];
                            })->values();
                            });
            $serialNo = 1;
        ?>
        <div class="page-break"></div>
            <div id="challanPrintSection" style="padding:15px; font-family:Arial, sans-serif; line-height:1.2;">
                <style>
                    @media print {
                        .container,
                        .container-fluid,
                        #challanPrintSection {
                            width: 100% !important;
                            max-width: 100% !important;
                        }
                    }
                    @media print {
                        body { margin: 0; }
                    }
                    .page-break {
                        page-break-before: always;
                        break-before: page;
                    }
                    .header-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 2px;
                        font-size: 13px;
                    }
                    h3 {
                        margin: 0;
                        padding: 0;
                        text-align: center; 
                    }
                    .two-column {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 12px;
                    }
                    .two-column .item-block {
                        width: 100%;
                    }
                    .item-block {
                        break-inside: avoid;
                        page-break-inside: avoid;
                        margin-bottom: 12px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 12px;
                        margin: 1px 0; 
                    }
                    th, td {
                        border: 1px solid #ccc;
                        padding: 1px 3px; 
                        text-align: left;
                    }
                    thead {
                        background: #e6e6e6;
                        font-weight: bold;
                    }

                    .total-row {
                        font-weight: bold;
                        background: #e8f5e9;
                    }
                    .final-totals {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 5px;
                        font-weight: bold;
                        font-size: 13px;
                    }
                </style>
                <div class="header-row">
                    <h3>Packaging Slip</h3>
                    <div>Date: {{ \Carbon\Carbon::parse($sale_detail->created_at)->format('d M Y') }}</div>
                </div>
                <hr style="margin:2px 0;">
                <div style="display:flex;justify-content:space-between;align-items:center;width:100%;gap:5px;">
                    <div style="flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        <strong>Party:</strong> {{ $saleOrder->billTo->account_name ?? ($party_detail->account_name ?? '-') }}
                    </div>
                    <div style="flex:0 0 auto;">
                        <strong>Vehicle No:</strong> {{ $sale_detail->vehicle_no ?? '' }}
                    </div>
                    <div style="flex:0 0 auto;">
                        <strong>Challan No:</strong> {{ $sale_detail->voucher_no_prefix ?? '' }}
                    </div>
                </div>
                <hr style="margin:2px 0;">
                @if($groupedItems->count() > 0)
                    <table style="width:100%; border:1px solid #000; border-collapse:collapse;">
                        {{-- ✅ IF ONLY ONE ITEM → SPLIT INTO 2 HALVES --}}
                        @if($groupedItems->count() == 1)
                            @php
                                $rows = $groupedItems->first();
                                $totalRows = $rows->count();
                                $half = ceil($totalRows / 2);
                                $chunks = $rows->chunk($half);

                                $firstRow = $rows->first();
                                $itemTotal = 0;
                            @endphp
                            <tr>
                                @foreach($chunks as $chunk)
                                    <td style="width:50%; vertical-align:top; padding:8px; border-right:1px solid #000;">
                                        <div class="item-block">
                                            <h4 style="margin:1px 0; font-size:13px;">
                                                Item: {{ $firstRow['item_name'] }}
                                            </h4>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th style="width:8%;">S No.</th>
                                                        <th style="width:25%;">Reel No</th>
                                                        <th style="width:45%;">Size</th>
                                                        <th style="width:22%;">Weight</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($chunk as $row)
                                                        @php
                                                            $itemTotal += $row['weight'];
                                                            $serialNo++;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $serialNo - 1 }}</td>
                                                            <td>{{ $row['reel_no'] }}</td>
                                                            <td>{{ $row['size'] }}</td>
                                                            <td>{{ number_format($row['weight'], 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                            {{-- TOTAL ROW FULL WIDTH --}}
                            <tr>
                                <td colspan="2" style="padding:8px;">
                                    <table style="width:100%;">
                                        <tr class="total-row">
                                            <td style="text-align:right; width:78%;">Total Weight</td>
                                            <td style="width:22%;">{{ number_format($itemTotal, 2) }}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            @php
                                $grandTotal += $itemTotal;
                                $totalReels = $serialNo - 1;
                            @endphp
                            {{-- ✅ MULTIPLE ITEMS → NORMAL 2 PER ROW --}}
                        @else
                            @foreach($groupedItems->chunk(2) as $chunk)
                                <tr>
                                    @foreach($chunk as $rows)
                                        @php
                                            $itemTotal = 0;
                                            $firstRow = $rows->first();
                                        @endphp

                                        <td style="width:50%; vertical-align:top; padding:8px; border-right:1px solid #000;">

                                            <div class="item-block">
                                                <h4 style="margin:1px 0; font-size:13px;">
                                                    Item: {{ $firstRow['item_name'] }}
                                                </h4>

                                                <table>
                                                    <thead>
                                                        <tr>
                                                            <th style="width:8%;">S No.</th>
                                                            <th style="width:25%;">Reel No</th>
                                                            <th style="width:45%;">Size</th>
                                                            <th style="width:22%;">Weight</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($rows as $row)
                                                            @php
                                                                $itemTotal += $row['weight'];
                                                                $serialNo++;
                                                            @endphp
                                                            <tr>
                                                                <td>{{ $serialNo - 1 }}</td>
                                                                <td>{{ $row['reel_no'] }}</td>
                                                                <td>{{ $row['size'] }}</td>
                                                                <td>{{ number_format($row['weight'], 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                        <tr class="total-row">
                                                            <td colspan="3" style="text-align:right;">Total Weight</td>
                                                            <td>{{ number_format($itemTotal, 2) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            @php
                                                $grandTotal += $itemTotal;
                                                $totalReels = $serialNo - 1;
                                            @endphp

                                        </td>
                                    @endforeach

                                    @if($chunk->count() == 1)
                                        <td style="width:50%;"></td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    <hr style="margin:3px 0;">
                    <div class="final-totals">
                        <div>Total Reels: {{ $totalReels }}</div>
                        <div>Grand Total Weight: {{ number_format($grandTotal, 2) }} Kg</div>
                    </div>
                @else
                    <p class="text-danger">No Sale or Sale Order item-size data found for this invoice.</p>
                @endif
            </div>

            <?php
        }else{ ?>
            <div id="challanPrintSection" style="padding:15px; font-family:Arial, sans-serif; line-height:1.2;">
                <style>
                    @media print {
                        .container,
                        .container-fluid,
                        #challanPrintSection {
                            width: 100% !important;
                            max-width: 100% !important;
                        }
                    }
                    @media print {
                        body { margin: 0; }
                    }
                    .page-break {
                        page-break-before: always;
                        break-before: page;
                    }
                    .header-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 2px;
                        font-size: 13px;
                    }
                    h3 {
                        margin: 0;
                        padding: 0;
                        text-align: center; 
                    }
                    .two-column {
                        display: grid;
                        grid-template-columns: repeat(2, 1fr);
                        gap: 12px;
                    }
                    .two-column .item-block {
                        width: 100%;
                    }
                    .item-block {
                        break-inside: avoid;
                        page-break-inside: avoid;
                        margin-bottom: 12px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 12px;
                        margin: 1px 0; 
                    }
                    th, td {
                        border: 1px solid #ccc;
                        padding: 1px 3px; 
                        text-align: left;
                    }
                    thead {
                        background: #e6e6e6;
                        font-weight: bold;
                    }

                    .total-row {
                        font-weight: bold;
                        background: #e8f5e9;
                    }
                    .final-totals {
                        display: flex;
                        justify-content: space-between;
                        margin-top: 5px;
                        font-weight: bold;
                        font-size: 13px;
                    }
                </style>
            </div>
            <?php            
        } 
    ?> 
    {{-- EWAY BILL CODE..... --}}
    @if($sale_detail->e_waybill_status == 1)
        @php
            $qrContent    = "";
            $Irn          = "";
            $AckNo        = "";
            $AckDt        = "";
            $ewaybill_no  = "";
            $ewayBillDate = "";
            $validUpto    = "";
            // e‑Invoice
            if ($sale_detail->e_invoice_status == 1 && $einvoice_data !== null) {
                if (is_string($einvoice_data)) {
                    $decoded = json_decode($einvoice_data);
                } else {
                    $decoded = $einvoice_data;
                }
                if ($decoded && isset($decoded->SignedQRCode)) {
                    $qrContent = $decoded->SignedQRCode;
                    $Irn   = $decoded->Irn ?? '';
                    $AckNo = $decoded->AckNo ?? '';
                    $rawAckDt = $decoded->AckDt ?? '';
                    $AckDt = date('d-m-Y H:i:s', strtotime($rawAckDt));
                }
            }
            // e‑Way Bill details
            if ($sale_detail->e_waybill_status == 1 && $sale_detail->eway_bill_response) {
                $way_raw = $sale_detail->eway_bill_response;

                $ewaybill_data = is_string($way_raw)
                    ? json_decode($way_raw)
                    : ($way_raw ?? null);

                $ewaybill_no  = $ewaybill_data->ewayBillNo  ?? '';
                $ewayBillDate = $ewaybill_data->ewayBillDate ?? '';
                $validUpto    = $ewaybill_data->validUpto    ?? '';

                $qrContent = $way_raw;
            }
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

                if (!empty($firstItem->p_name)) {
                    $displayHSN .= ' - ' . $firstItem->p_name;
                }

                $displayHSN .= ' (+' . ($hsnCodes->count() - 1) . ')';
            }
        @endphp
        <div class="page-break"></div>
        <style>
            .eway-simple-wrap table {
                width: 100%;
                border-collapse: collapse;
                font-family: Arial, sans-serif;
                font-size: 12px;
            }
            .eway-simple-wrap .gov-center {
                text-align: center;
            }
            .eway-simple-wrap .gov-title {
                font-size: 16px;
                font-weight: 700;
                margin-bottom: 4px;
            }
            .eway-simple-wrap .gov-label {
                width: 35%;
                font-weight: 600;
                border: 1px solid #000;
                padding: 4px 6px;
                vertical-align: top;
            }
            .eway-simple-wrap .gov-value {
                border: 1px solid #000;
                padding: 4px 6px;
                vertical-align: top;
            }
            .eway-simple-wrap .gov-part {
                background: #f0f0f0;
                font-weight: 700;
                text-align: center;
                border: 1px solid #000;
                padding: 4px 6px;
            }
            .eway-simple-wrap table > tr > td,
            .eway-simple-wrap table > tbody > tr > td {
                border: 1px solid #000;
            }
        </style>
        <div class="eway-simple-wrap">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td colspan="2" class="gov-center" style="border:1px solid #000;border-bottom:none;">
                        <div class="gov-title">
                            e-Way Bill
                        </div>

                        @if(!empty($qrBase64))
                            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width:150px;height:150px;">
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
                        {{ $formattedDate }}
                    </td>
                </tr>
                <tr>
                    <td class="gov-label">
                        Generated By:
                    </td>
                    <td class="gov-value">
                        {{ $sale_detail->merchant_gst }}
                    </td>
                </tr>
                <tr>
                    <td class="gov-label">
                        Valid From:
                    </td>
                    <td class="gov-value">
                        {{ $formattedDate }}
                        @if(!empty($sale_detail->e_waybill_distance))
                            [{{ $sale_detail->e_waybill_distance }} Kms]
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
                        {{ $sale_detail->billing_gst }}
                        -
                        {{ $sale_detail->billing_name }}
                    </td>
                </tr>
                <tr>
                    <td class="gov-label">
                        Place of Delivery
                    </td>
                    <td class="gov-value">
                        @if($sale_detail->shipping_name)
                            {{ $sale_detail->shipping_state_name }},
                            {{ $sale_detail->shipping_state_name }}-{{ $sale_detail->shipping_pincode }}
                        @else
                            {{ $sale_detail->billing_state_name }},
                            {{ $sale_detail->billing_state_name }}-{{ $sale_detail->billing_pincode }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="gov-label">
                        Document No.
                    </td>
                    <td class="gov-value">
                        {{ $sale_detail->voucher_no_prefix }}
                    </td>
                </tr>
                <tr>
                    <td class="gov-label">
                        Document Date
                    </td>
                    <td class="gov-value">
                        {{ date('d/m/Y', strtotime($sale_detail->date)) }}
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
                        {{ formatIndianNumber($sale_detail->total) }}
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
                    <td colspan="2" style="padding:6px;border:1px solid #000;">
                        <table style="width:100%;border-collapse:collapse;border:1px solid #000;font-size:12px;">
                            <tr>
                                <td colspan="8" class="gov-part">
                                    Part - B
                                </td>
                            </tr>
                            <tr style="font-weight:600;text-align:center;">
                                <td style="width:7%;border:1px solid #000;">
                                    Mode
                                </td>

                                <td style="width:28%;border:1px solid #000;">
                                    Vehicle / Trans<br>
                                    Doc No & Dt.
                                </td>

                                <td style="width:10%;border:1px solid #000;">
                                    From
                                </td>

                                <td style="width:18%;border:1px solid #000;">
                                    Entered Date
                                </td>

                                <td style="width:17%;border:1px solid #000;">
                                    Entered By
                                </td>

                                <td style="width:10%;border:1px solid #000;">
                                    CEWB No.<br>(if any)
                                </td>

                                <td style="width:10%;border:1px solid #000;">
                                    Multi Veh.Info<br>(if any)
                                </td>

                                <td style="width:5%;border:1px solid #000;">
                                    Portal
                                </td>
                            </tr>

                            <tr style="text-align:center;font-weight:bold;">
                                <td style="border:1px solid #000;padding:3px;">
                                    -
                                </td>

                                <td style="border:1px solid #000;text-align:center;">
                                    {{ $sale_detail->vehicle_no }}

                                    @if(!empty($sale_detail->gr_pr_no))
                                        &nbsp;&amp;&nbsp;{{ $sale_detail->gr_pr_no }}&nbsp;&amp;&nbsp;
                                    @endif

                                    <br>
                                    {{ date('d/m/Y', strtotime($sale_detail->date)) }}
                                </td>

                                <td style="border:1px solid #000;padding:3px;">
                                    {{ $seller_info->sname }}
                                </td>

                                <td style="border:1px solid #000;padding:3px;">
                                    @if(!empty($ewayBillDate))
                                        {{ $ewayBillDate }}
                                    @endif
                                </td>

                                <td style="border:1px solid #000;padding:3px;">
                                    {{ $sale_detail->merchant_gst }}
                                </td>

                                <td style="border:1px solid #000;padding:3px;">
                                    -
                                </td>

                                <td style="border:1px solid #000;padding:3px;">
                                    -
                                </td>

                                <td style="border:1px solid #000;padding:3px;">
                                    1
                                </td>
                            </tr>

                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="padding:12px 0;text-align:center;border:1px solid #000;">
                        <div style="margin-top:4px;font-size:10px;font-weight:bold;letter-spacing:1px;">
                            {{ $ewaybill_no }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="font-size:10px;border-top:1px solid #000;border-left:1px solid #000;border-right:1px solid #000;border-bottom:1px solid #000;padding:6px;">
                        <strong>Note:</strong>
                        Any discrepancy in the e-Way Bill should be reported to the
                        concerned tax authority within 24 hours.
                    </td>
                </tr>

            </table>
        </div>
    @endif
</body>
</html>
