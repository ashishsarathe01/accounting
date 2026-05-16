<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Debit Note</title>
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
                                    <p style="margin:0;"><u>DEBIT NOTE</u></p>
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

            {{-- ================= INVOICE INFO SECTION ================= --}}
            {{-- LEFT = Party Details | RIGHT = Debit Note details (from purchase return blade) --}}
            <tr>
                <td colspan="8" style="padding:0;">
                    <table style="width:100%; border-collapse:collapse; table-layout:fixed; border:none;">
                        <tr>
                            {{-- LEFT: Party Details — from purchase return blade --}}
                            <td style="width:50%; vertical-align:top; padding:10px; border-right:1px solid #000; height:150px;">
                                <p style="margin:0;">
                                    <strong>Party Details :</strong>
                                </p>
                                <p style="margin:4px 0 0 0;">
                                    <strong>{{ $purchase_return->billing_name }}</strong>
                                </p>
                                <p style="margin:2px 0 0 0; line-height:1.4;">
                                    {{ $purchase_return->party_address }}
                                </p>
                                <p style="margin:10px 0 0 0;">
                                    GSTIN / UIN : {{ $purchase_return->billing_gst }}
                                </p>
                            </td>

                            {{-- RIGHT: Debit Note Details — from purchase return blade --}}
                            <td style="width:50%; vertical-align:top; padding:10px; height:150px;">
                                <table style="width:100%; border:none; border-collapse:collapse;">
                                    <tr>
                                        <td style="border:none; width:40%;">Dr. Note No</td>
                                        <td style="border:none; width:5%;">:</td>
                                        <td style="border:none;">
                                            <strong>{{ $purchase_return->sr_prefix }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">Dr. Note Date</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            {{ date('d-m-Y', strtotime($purchase_return->date)) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">Org. Inv. No.</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            {{ $purchase_return->invoice_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">Org. Inv. Date</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            @if($purchase_return->original_invoice_date != '')
                                                {{ date('d-m-Y', strtotime($purchase_return->original_invoice_date)) }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">Transport</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            <span class="wrap-text">{{ $purchase_return->transport_name }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">Vehicle No.</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            {{ $purchase_return->vehicle_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">Station</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            {{ $purchase_return->station }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="border:none;">GR/RR No.</td>
                                        <td style="border:none;">:</td>
                                        <td style="border:none;">
                                            {{ $purchase_return->gr_pr_no }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            {{-- ================= ITEMS ================= --}}
            <tr>
                <th style="width:2%; padding:0px 3px;">S. No.</th>
                <th colspan="2" style="text-align:left; width:30%;">Description of Goods</th>
                <th style="text-align:left; width:10%;">HSN/SAC Code</th>
                <th style="text-align:right; width:11%;">Qty.</th>
                <th style="text-align:left; width:5%;">Unit</th>
                <th style="text-align:right; width:12%;">Price</th>
                <th style="text-align:right; width:15%;">Amount (&#x20B9;)</th>
            </tr>

            @php $i = 1; $item_total = 0; $qty_total = 0; @endphp
            @foreach($items_detail as $item)
                <tr>
                    <td style="text-align:left;">{{ $i }}</td>
                    <td colspan="2" style="text-align:left;">{{ $item->items_name }}</td>
                    <td style="text-align:left;">{{ $item->hsn_code }}</td>
                    <td style="text-align:right;">{{ $item->qty }}</td>
                    <td style="text-align:left;">{{ $item->unit }}</td>
                    <td style="text-align:right;">{{ $item->price }}</td>
                    <td style="text-align:right;">{{ formatIndianNumber($item->amount) }}</td>
                </tr>
                @php $i++; $item_total += $item->amount; $qty_total += $item->qty; @endphp
            @endforeach


            {{-- ================= TOTAL ROW ================= --}}
            <tr>
                <td colspan="6" style="border-bottom:0; border-right:0;"></td>
                <td style="border-bottom:0; border-left:0;"><strong>Total</strong></td>
                <td style="text-align:right; border-bottom:0;">{{ formatIndianNumber($item_total) }}</td>
            </tr>

            {{-- ================= SUNDRY (purchase return blade logic — plain "Add:" for all) ================= --}}
            <tr>
                <td style="border-right:0; border-top:0;" colspan="2"></td>
                <td colspan="4" style="border-left:0; border-right:0; border-top:0;">
                    @foreach($purchase_sundry as $sundry)
                        <p>Add : {{ $sundry->name }}</p>
                    @endforeach
                </td>
                <td style="border-left:0; border-top:0;">
                    @foreach($purchase_sundry as $sundry)
                        <p>@if($sundry->rate != 0){{ $sundry->rate }} %@else&nbsp;@endif</p>
                    @endforeach
                </td>
                <td style="text-align:right; border-top:0;">
                    @foreach($purchase_sundry as $sundry)
                        <p>{{ formatIndianNumber($sundry->amount) }}</p>
                    @endforeach
                </td>
            </tr>

            {{-- ================= GRAND TOTAL ================= --}}
            <tr>
                <td colspan="7" style="text-align:right; border-right:0; border-bottom:0;">
                    <p><strong style="font-family: DejaVu Sans, sans-serif;">Grand Total &#x20B9;</strong></p>
                </td>
                <td style="text-align:right;">
                    <p><strong>{{ formatIndianNumber($purchase_return->total) }}</strong></p>
                </td>
            </tr>

            {{-- ================= GST BREAKUP (purchase return blade logic: $company_data->gst) ================= --}}
            <tr>
                <td colspan="8" style="border-top:0; border-bottom:0;">
                    @foreach($gst_detail as $val)
                        <span><u><small>Tax Rate</small></u><br>
                            <small>{{ $val->rate }}%</small>
                        </span>
                        <span class="mar_lft10"><u><small>Taxable Amount</small></u><br>
                            <small>{{ formatIndianNumber($val->taxable_amount) }}</small>
                        </span>
                        @if(Str::limit($company_data->gst, 2, '') == Str::limit($purchase_return->billing_gst, 2, ''))
                            <span class="mar_lft10"><u><small>CGST</small></u><br>
                                <small>{{ formatIndianNumber($val->amount) }}</small>
                            </span>
                            <span class="mar_lft10"><u><small>SGST</small></u><br>
                                <small>{{ formatIndianNumber($val->amount) }}</small>
                            </span>
                        @else
                            <span class="mar_lft10"><u><small>IGST</small></u><br>
                                <small>{{ formatIndianNumber($val->amount) }}</small>
                            </span>
                        @endif
                        <span class="mar_lft10"><u><small>Total Tax</small></u><br>
                            @if(Str::limit($company_data->gst, 2, '') == Str::limit($purchase_return->billing_gst, 2, ''))
                                <small>{{ formatIndianNumber($val->amount + $val->amount) }}</small>
                            @else
                                <small>{{ formatIndianNumber($val->amount) }}</small>
                            @endif
                        </span><br>
                    @endforeach
                </td>
            </tr>

            {{-- ================= AMOUNT IN WORDS (purchase return blade logic) ================= --}}
            <tr>
                <td colspan="8" style="border-top:0;">
                    <strong>
                        <?php
                            $number   = $purchase_return->total;
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
                        <p style="text-align:right; margin:0; padding:0; width:145px; height:70px;"></p>
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