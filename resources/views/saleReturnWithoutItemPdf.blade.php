<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Note</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-size: small;
            color: #333;
            font-family: "DejaVu Sans", Arial, sans-serif;
            overflow-x: hidden;
            font-weight: 400;
            line-height: 1.5;
            background-color: #fff;
        }
        table {
            width: 100%;
            border-spacing: 0;
            border: 1px solid #000000;
            border-collapse: collapse;
        }
        table tr th, table tr td {
            border: 1px solid #000000;
            margin: 0;
            padding: 2px 5px;
        }
        hr {
            border: 1px solid #000000;
        }
        .text-right {
            text-align: right;
        }
        .text-left {
            text-align: left;
        }
        p {
            margin: 0px;
            margin-bottom: 0rem;
        }
        h1, h2, h3, h4, h5, h6 {
            margin: 5px 0px;
        }
        .mar_lft10 {
            margin-left: 15px;
        }
        span {
            display: inline-block;
        }
        .width25 {
            width: 35%;
        }
        .lft_mar15 {
            margin-left: 15px;
        }
        .wrap-text {
            display: inline-block;
            max-width: 55%;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            vertical-align: top;
        }
        .data-table tbody tr {
            line-height: 10px;
        }
    </style>
</head>
<body>
    @php
        $logoBase64 = null;
        if ($configuration && $configuration->company_logo_status == 1 && !empty($configuration->company_logo)) {
            $logoPath = public_path('images/' . $configuration->company_logo);
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        $signBase64 = null;
        if ($configuration && !empty($configuration->signature)) {
            $signPath = public_path('images/' . $configuration->signature);
            if (file_exists($signPath)) {
                $type = pathinfo($signPath, PATHINFO_EXTENSION);
                $data = file_get_contents($signPath);
                $signBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }
        $companyName = $company_data->company_name;
        $fontSize = strlen($companyName) > 30 ? '15px' : '20px';
    @endphp

    <table>
        <tbody>

            {{-- ================= HEADER ================= --}}
            <tr>
                <th colspan="8" style="padding:0;">
                    <div style="min-height:170px; width:100%;">

                        {{-- TOP ROW: GSTIN left | header text center | PAN right --}}
                        <table width="100%" style="border:none; border-collapse:collapse;">
                            <tr>
                                <td width="33%" style="text-align:left; vertical-align:top; border:none;">
                                    <strong>GSTIN: {{ $seller_info->gst_no }}</strong>
                                </td>
                                <td width="34%" style="text-align:center; vertical-align:top; border:none;">
                                    @if($configuration && !empty($configuration->invoice_header_text))
                                        <strong style="font-size:13px; font-weight:700; letter-spacing:1px;">
                                            {{ $configuration->invoice_header_text }}
                                        </strong>
                                    @endif
                                </td>
                                <td width="33%" style="text-align:right; vertical-align:top; border:none;">
                                    <strong>PAN: {{ substr($seller_info->gst_no, 2, 10) }}</strong><br>
                                    <span style="font-size:11px;">O/D/T</span>
                                </td>
                            </tr>
                        </table>

                        <div style="clear:both;"></div>

                        {{-- LOGO + COMPANY NAME CENTER --}}
                        <table width="100%" style="border:none; border-collapse:collapse; margin-top:2px;">
                            <tr>
                                <td width="20%" style="border:none; text-align:left; vertical-align:top;">
                                    @if($logoBase64 && isset($configuration->logo_position_left) && $configuration->logo_position_left == 1)
                                        <img src="{{ $logoBase64 }}" style="max-width:120px; height:80px;">
                                    @endif
                                </td>
                                <td width="60%" style="border:none; text-align:center;">
                                    <p style="margin:0;"><u>CREDIT NOTE</u></p>
                                    <p style="margin:0; font-size:{{ $fontSize }}; font-weight:bold; color:{{ $configuration->company_name_color ?? 'black' }};">
                                        {{ $companyName }}
                                    </p>
                                    <p style="margin:0;">
                                        <small style="font-size:12px; color:{{ $configuration->address_color ?? 'black' }};">
                                            {{ $seller_info->address }}@if(!empty($seller_info->pincode)),{{ $seller_info->pincode }}@endif
                                        </small>
                                    </p>
                                    <p style="margin:0;">
                                        <small style="font-size:12px;">
                                            Phone: {{ $company_data->mobile_no }}
                                            &nbsp; Email: {{ $company_data->email_id }}
                                        </small>
                                    </p>
                                </td>
                                <td width="20%" style="border:none; text-align:right; vertical-align:top;">
                                    @if($logoBase64 && isset($configuration->logo_position_right) && $configuration->logo_position_right == 1)
                                        <img src="{{ $logoBase64 }}" style="max-width:120px; height:80px;">
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <div style="clear:both;"></div>
                    </div>
                </th>
            </tr>

            {{-- ================= E-INVOICE (Sale Return Without Item logic) ================= --}}
            @if($sale_return->e_invoice_status == 1 && !empty($sale_return->einvoice_response))
                @php
                    $einvoice_data = json_decode($sale_return->einvoice_response);
                    $qrContent     = $einvoice_data->SignedQRCode ?? '';
                    $qrBase64pdf   = null;
                    if (!empty($qrContent)) {
                        $svgContent  = (string) \QrCode::size(90)->generate($qrContent);
                        $qrBase64pdf = base64_encode($svgContent);
                    }
                @endphp
                <tr>
                    <td colspan="8" style="min-height:110px; vertical-align:top;">
                        <div style="float:right; width:90px; height:90px; margin-left:10px;">
                            @if(!empty($qrBase64pdf))
                                <img src="data:image/svg+xml;base64,{{ $qrBase64pdf }}"
                                     style="width:90px; height:90px; display:block;">
                            @endif
                        </div>
                        <p style="margin-top:0;"><strong>IRN No :</strong> {{ $einvoice_data->Irn ?? '' }}</p>
                        <p><strong>Ack No :</strong> {{ $einvoice_data->AckNo ?? '' }}</p>
                        <p><strong>Ack Date :</strong> {{ $einvoice_data->AckDt ?? '' }}</p>
                        <div style="clear:both;"></div>
                    </td>
                </tr>
            @endif

            {{-- ================= INVOICE INFO SECTION ================= --}}
            {{-- LEFT = Party Details (Sale Return Without Item) | RIGHT = Credit Note details --}}
            <tr>
                <td colspan="8" style="padding:0;">
                    <table style="width:100%; border-collapse:collapse; table-layout:fixed; border:none;">
                        <tr>
                            {{-- LEFT: Party Details — from sale return without item blade --}}
                            <td style="width:50%; vertical-align:top; padding:10px; border-right:1px solid #000; height:150px;">
                                <p style="margin:0;">
                                    <strong>Party Details :</strong>
                                </p>
                                <p style="margin:4px 0 0 0;">
                                    <strong>{{ $sale_return->account_name }}</strong>
                                </p>
                                <p style="margin:2px 0 0 0; line-height:1.4;">
                                    {{ $sale_return->address }},{{ $sale_return->sname }}<br>
                                    {{ $sale_return->pin_code }}
                                </p>
                                <p style="margin:10px 0 0 0;">
                                    GSTIN / UIN : {{ $sale_return->gstin }}
                                </p>
                            </td>

                            {{-- RIGHT: Credit Note Details — from sale return without item blade --}}
                            <td style="width:50%; vertical-align:top; padding:10px; height:150px;">
                                <table style="width:100%; border:none; border-collapse:collapse;">
                                    <tr>
                                        <td style="border:none; width:40%;">Cr. Note No</td>
                                        <td style="border:none; width:5%;">:</td>
                                        <td style="border:none;">
                                            <strong>{{ $sale_return->sr_prefix }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">Cr. Note Date</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            {{ date('d-m-Y', strtotime($sale_return->date)) }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- ================= ITEMS (without item — uses $items, not $items_detail) ================= --}}
            <tr>
                <th style="width:3%; padding:0px 3px;">S.N.</th>
                <th colspan="4" style="text-align:left;">Description of Goods</th>
                <th style="text-align:left; width:10%;">HSN/SAC Code</th>
                <th colspan="2" style="text-align:right; width:15%;">Amount (&#x20B9;)</th>
            </tr>

            @php $i = 1; $item_total = 0; $tax_arr = []; @endphp
            @foreach($items as $item)
                <tr>
                    <td style="text-align:left;">{{ $i }}</td>
                    <td colspan="4" style="text-align:left;">{{ $item->account_name }}</td>
                    <td style="text-align:left;">{{ $item->hsn_code }}</td>
                    <td colspan="2" style="text-align:right;">{{ formatIndianNumber($item->debit) }}</td>
                </tr>
                @php
                    $i++;
                    $item_total += $item->debit;
                    array_push($tax_arr, ['percentage' => $item->percentage, 'amount' => $item->debit]);
                @endphp
            @endforeach

            {{-- ================= TOTAL ROW ================= --}}
            <tr>
                <td colspan="3" style="border-bottom:0; border-right:0;"></td>
                <td colspan="3" style="border-bottom:0; border-left:0;"><strong>Total</strong></td>
                <td colspan="2" style="text-align:right; border-bottom:0;">{{ formatIndianNumber($item_total) }}</td>
            </tr>

            {{-- ================= TAX SUNDRY (Sale Return Without Item logic) ================= --}}
            @php
                $actual_total = $item_total;
                if ($sale_return->tax_igst) {
                    $actual_total += $sale_return->tax_igst;
                }
                if ($sale_return->tax_cgst) {
                    $actual_total += $sale_return->tax_cgst;
                }
                if ($sale_return->tax_sgst) {
                    $actual_total += $sale_return->tax_sgst;
                }
                $round_off = $sale_return->total - $actual_total;

                $return_tax = [];
                foreach ($tax_arr as $val) {
                    $return_tax[$val['percentage']][] = $val;
                }
            @endphp
            <tr>
                <td style="border-right:0; border-top:0;" colspan="1"></td>
                <td colspan="4" style="border-left:0; border-right:0; border-top:0; text-align:right;">
                    @foreach($return_tax as $k => $item)
                        @if($sale_return->tax_cgst != '' && $sale_return->tax_sgst != '')
                            <p><strong>Add : </strong> CGST</p>
                            <p><strong>Add : </strong> SGST</p>
                        @elseif($sale_return->tax_igst != '')
                            <p><strong>Add : </strong> IGST</p>
                        @endif
                    @endforeach
                </td>
                <td style="border-left:0; border-top:0; text-align:center;">
                    @foreach($return_tax as $k => $item)
                        @if($sale_return->tax_cgst != '' && $sale_return->tax_sgst != '')
                            <p>{{ $k / 2 }}%</p>
                            <p>{{ $k / 2 }}%</p>
                        @elseif($sale_return->tax_igst != '')
                            <p>{{ $k }}%</p>
                        @endif
                    @endforeach
                </td>
                <td colspan="2" style="text-align:right; border-top:0;">
                    @foreach($return_tax as $k => $taxItems)
                        @php $taxable_amount = 0; @endphp
                        @foreach($taxItems as $amount)
                            @php $taxable_amount += $amount['amount']; @endphp
                        @endforeach
                        @if($sale_return->tax_cgst != '' && $sale_return->tax_sgst != '')
                            <p>{{ formatIndianNumber(($taxable_amount * ($k / 2)) / 100) }}</p>
                            <p>{{ formatIndianNumber(($taxable_amount * ($k / 2)) / 100) }}</p>
                        @elseif($sale_return->tax_igst != '')
                            <p>{{ formatIndianNumber(($taxable_amount * $k) / 100) }}</p>
                        @endif
                    @endforeach
                </td>
            </tr>

            {{-- ================= ROUND OFF ================= --}}
            <tr>
                <td colspan="6" style="text-align:right;">
                    <p>
                        <strong>
                            @if($round_off > 0)
                                Round Off (+)
                            @else
                                Round Off (-)
                            @endif
                        </strong>
                    </p>
                </td>
                <td colspan="2" style="text-align:right;">
                    <p>{{ number_format(abs($round_off), 2) }}</p>
                </td>
            </tr>

            {{-- ================= GRAND TOTAL ================= --}}
            <tr>
                <td colspan="6" style="text-align:right; border-right:0; border-bottom:0;">
                    <p><strong style="font-family: DejaVu Sans, sans-serif;">Grand Total &#x20B9;</strong></p>
                </td>
                <td colspan="2" style="text-align:right;">
                    <p><strong>{{ formatIndianNumber($sale_return->total) }}</strong></p>
                </td>
            </tr>

            {{-- ================= AMOUNT IN WORDS (Sale Return Without Item logic) ================= --}}
            <tr>
                <td colspan="8" style="border-top:0;">
                    <strong>
                        <?php
                            $number   = $sale_return->total;
                            $no       = floor($number);
                            $point    = round($number - $no, 2) * 100;
                            $hundred  = null;
                            $digits_1 = strlen($no);
                            $i        = 0;
                            $str      = [];
                            $words = [
                                '0'  => '',         '1'  => 'one',      '2'  => 'two',
                                '3'  => 'three',    '4'  => 'four',     '5'  => 'five',
                                '6'  => 'six',      '7'  => 'seven',    '8'  => 'eight',
                                '9'  => 'nine',     '10' => 'ten',      '11' => 'eleven',
                                '12' => 'twelve',   '13' => 'thirteen', '14' => 'fourteen',
                                '15' => 'fifteen',  '16' => 'sixteen',  '17' => 'seventeen',
                                '18' => 'eighteen', '19' => 'nineteen', '20' => 'twenty',
                                '30' => 'thirty',   '40' => 'forty',    '50' => 'fifty',
                                '60' => 'sixty',    '70' => 'seventy',  '80' => 'eighty',
                                '90' => 'ninety'
                            ];
                            $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];
                            while ($i < $digits_1) {
                                $divider = ($i == 2) ? 10 : 100;
                                $number  = floor($no % $divider);
                                $no      = floor($no / $divider);
                                $i      += ($divider == 10) ? 1 : 2;
                                if ($number) {
                                    $plural  = (($counter = count($str)) && $number > 9) ? 's' : null;
                                    $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                                    $str[]   = ($number < 21)
                                        ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                                        : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10]
                                          . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
                                } else {
                                    $str[] = null;
                                }
                            }
                            $str    = array_reverse($str);
                            $result = implode('', $str);
                            echo ucfirst($result) . 'Rupees  only';
                        ?>
                    </strong>
                </td>
            </tr>

            {{-- ================= FOOTER: Terms left | Signature right ================= --}}
            <tr>
                <td colspan="4" style="vertical-align:top; padding:5px;">
                    @if($configuration && $configuration->term_status == 1 && $configuration->terms && count($configuration->terms) > 0)
                        <p style="margin:0;"><small><b>Terms &amp; Conditions</b></small></p>
                        <p style="margin:0;"><small>E.&amp; O.E.</small></p>
                        @php $i = 1; @endphp
                        @foreach($configuration->terms as $k => $t)
                            <p style="margin:0; line-height:1;"><small>{{ $i }}. {{ $t->term }}</small></p>
                            @php $i++; @endphp
                        @endforeach
                    @endif
                </td>
                <td colspan="4">
                    <p style="height:40px; margin:0; padding:0;"><small>Receiver's Signature :</small></p>
                    <hr style="margin:0; padding:0; border:none; height:1px; background-color:#000000;">
                    <p style="text-align:right; padding:0; margin:0;">
                        <strong>for {{ $company_data->company_name }}</strong>
                    </p>
                    @if($signBase64)
                        <p style="text-align:right; margin:0; padding:0;">
                            <img src="{{ $signBase64 }}" style="width:145px; height:70px;">
                        </p>
                    @else
                        <p style="width:145px; height:70px;"></p>
                    @endif
                    <p style="text-align:right; margin:0; padding:0;">
                        <strong>Authorised Signatory</strong>
                    </p>
                </td>
            </tr>

        </tbody>
    </table>
</body>
</html>