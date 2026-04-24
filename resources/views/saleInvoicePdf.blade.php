<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Bill</title>
    <style>
        body{
            margin: 0;
            padding: 0;
            font-size: small;
            color: #333;
            font-family: "Inter", sans-serif !important;
            overflow-x: hidden;
            font-weight: 400;
            line-height: 1.5;
            background-color: #fff;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }
        .dataTables_filter{
            float:right;
        }
        .data-table{
            font-size: 15px
        }
        .data-table tbody tr { line-height: 10px !important; }
        table{
            width:100%;
            border-spacing: 0;
            border:1px solid #dadada;
        }
        table tr th, table tr td{
            border:1px solid #000000;
            margin: 0;
            padding: 2px 5px;
        }
        hr{
            border:1px solid #000000;
        }
        .text-right{
            text-align: right;
        }
        .text-left{
            text-align: left;
        }
        p{
            margin:5px 0px; 
        }
        h1, h2, h3, h4, h5, h6{
            margin: 5px 0px;
        }
        .mar_lft10{
            margin-left: 15px;
        }
        span{
            display: inline-block;
        }
        p{
            margin:0px;
            margin-bottom:0rem !important;
        }
        .width25{
            width:35%;
        }
        .lft_mar15{
            margin-left:15px;
        }
        .bil_logo{
            width: 120px;
            height: 90px;
            overflow: hidden;
            position: absolute;
            margin-top: 20px;
            margin-left: 4px;
        }
        .bil_logo img{
            max-width:100%;
        }
        .wrap-text {
            display: inline-block;
            max-width: 55%;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            vertical-align: top;
        }
    </style>
</head>
<body>
    {{-- BILL CODE..... --}}
    <table>
        <tbody>
            {{-- ================= HEADER ================= --}}
            <tr>
                <th colspan="8" style="padding:0;">
                    @php
                        $companyName = $company_data->company_name;
                        $fontSize = strlen($companyName) > 30 ? '15px' : '20px';
                        if($configuration && $configuration->company_name_font_size!=""){
                           
                        }
                        $logoBase64 = null;
                        if (!empty($configuration->company_logo)) {
                            $logoPath = public_path('images/' . $configuration->company_logo);
                            if (file_exists($logoPath)) {
                                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                                $data = file_get_contents($logoPath);
                                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            }
                        }
                        $signBase64 = null;
                        if (!empty($configuration->signature)) {
                            $signPath = public_path('images/' . $configuration->signature);
                            if (file_exists($signPath)) {
                                $type = pathinfo($signPath, PATHINFO_EXTENSION);
                                $data = file_get_contents($signPath);
                                $signBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            }
                        }
                    @endphp
                    <div style="min-height:170px; width:100%;">
                        <table width="100%" style="border:none; border-collapse:collapse;">
                            <tr>
                                <!-- LEFT GST -->
                                <td width="33%" style="text-align:left; vertical-align:top; border:none;">
                                    <strong>GSTIN: {{ $seller_info->gst_no }}</strong>
                                </td>
                                <div style="position:absolute; top:5px; left:0; width:100%; text-align:center;">
                                    @if($configuration && !empty($configuration->invoice_header_text))
                                    <strong style="font-size:13px; font-weight:700; letter-spacing:1px;">
                                            {{ $configuration->invoice_header_text }}
                                    </strong>
                                    @endif
                                </div>
                                <!-- CENTER TAX INVOICE -->
                                <!-- RIGHT PAN -->
                                <td width="33%" style="text-align:right; vertical-align:top; border:none;">
                                    <strong>PAN: {{ substr($seller_info->gst_no, 2, 10) }}</strong><br>
                                    <span style="font-size:11px;">O/D/T</span>
                                </td>
                            </tr>
                        </table>
                        <div style="clear:both;"></div>
                        {{-- LOGO LEFT (like web) --}}
                        <table width="100%" style="border:none; border-collapse:collapse; margin-top:2px;">
                            <tr>
                                <td width="20%" style="border:none; text-align:left; vertical-align:top;">
                                    @if($configuration && $configuration->company_logo_status==1 
                                        && $configuration->logo_position_left==1 
                                        && $logoBase64)
                                    <img src="{{ $logoBase64 }}" style="max-width:120px; height:80px;">
                                    @endif
                                </td>
                                <td width="60%" style="border:none; text-align:center;">
                                    <p style="margin:0;"><u>TAX INVOICE</u></p>
                                    <p style="margin:0; font-size:{{ $fontSize }}; font-weight:bold;color: {{ $configuration->company_name_color ?? 'black' }};">
                                        {{ $companyName }}
                                    </p>
                                    <p style="margin:0;">
                                        <small style="font-size:12px;color: {{ $configuration->address_color ?? 'black' }};">
                                            {{ $seller_info->address }}
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
                                    @if($configuration && $configuration->company_logo_status==1 && $configuration->logo_position_right==1 && $logoBase64)
                                        <img src="{{ $logoBase64 }}" style="max-width:120px; height:80px;">
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <div style="clear:both;"></div>
                    </div>
                </th>
            </tr>
            {{-- ================= E-INVOICE ================= --}}
            @if($sale_detail->e_invoice_status == 1 && !empty($einvoice_data))
                <tr>
                    <td colspan="8" style="min-height:110px; vertical-align:top;">
                        <div style="float:right;width:90px;height:90px;margin-left:10px;">
                            @if($qrBase64)
                                <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width:90px; height:90px; display:block;">
                            @endif
                        </div>
                        {{-- TEXT --}}
                        <p style="margin-top:0;"><strong>IRN No :</strong> {{ $einvoice_data->Irn }}</p>
                        <p><strong>Ack No :</strong> {{ $einvoice_data->AckNo }}</p>
                        <p><strong>Ack Date :</strong> {{ $einvoice_data->AckDt }}</p>
                        {{-- CLEAR FLOAT (IMPORTANT) --}}
                        <div style="clear:both;"></div>
                    </td>
                </tr>
            @endif
            {{-- ================= INVOICE INFO ================= --}}
            <tr>
                <td colspan="8" style="padding:0;">
                    <table style="width:100%; border-collapse:collapse; table-layout:fixed; border:none;">
                        <tr>
                            <td style="width:50%; vertical-align:top; padding:8px; border-right:1px solid #000; border-top:none; border-bottom:none; border-left:none;">
                                <p>
                                    <strong>
                                        <span class="width25">Invoice No.</span> :
                                        {{ $sale_detail->voucher_no_prefix }}
                                    </strong>
                                </p>
                                <p><span class="width25">Date of Invoice</span> :
                                    {{ date('d-m-Y',strtotime($sale_detail->date)) }}
                                </p>
                                <p><span class="width25">Place of Supply</span> :
                                    {{ $sale_detail->sname }}
                                </p>
                                <p><span class="width25">Reverse Charge</span> :
                                    {{ $sale_detail->reverse_charge }}
                                </p>
                                <p><span class="width25">GR/RR No.</span> :
                                    {{ $sale_detail->gr_pr_no }}
                                </p>
                            </td>
                            <td style="width:50%; vertical-align:top; padding:8px; border:none;">
                                <p><span class="width25">Transport</span> :
                                    {{ $sale_detail->transport_name }}
                                </p>
                                <p><span class="width25">Vehicle No.</span> :
                                    {{ $sale_detail->vehicle_no }}
                                </p>
                                <p><span class="width25">Station</span> :
                                    {{ $sale_detail->station }}
                                </p>
                                <p><span class="width25">E-Way Bill No.</span> :
                                    <?php
                                    if($sale_detail->e_waybill_status==1 && $sale_detail->eway_bill_response){
                                        $ewaybill_data = json_decode($sale_detail->eway_bill_response);
                                        echo $ewaybill_data->ewayBillNo ?? '';
                                    }?>
                                </p>
                                @if($configuration && $configuration->purchase_order_status == 1)
                                    <p>
                                        <span class="width25">PO No.</span> : {{ $sale_detail->po_no }}
                                    </p>
                                    <p>
                                        <span class="width25">PO Date</span> : {{ $sale_detail->po_date ? date('d-m-Y', strtotime($sale_detail->po_date)) : '' }}
                                    </p>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td style="width:50%; vertical-align:top; padding:8px; border-right:1px solid #000; border-top:1px solid #000; border-left:none; border-bottom:none;">
                                <strong>Billed To :</strong><br>
                                <strong>{{ $sale_detail->billing_name }}</strong><br>
                                {{ $sale_detail->billing_address }}<br>
                                <strong>GSTIN : {{ $sale_detail->billing_gst }}</strong><br>
                                PAN : {{ $sale_detail->billing_pan }}
                            </td>
                            <td style="width:50%; vertical-align:top; padding:8px; border-top:1px solid #000; border-left:none; border-right:none; border-bottom:none;">
                                <strong>Shipped To :</strong><br>
                                <strong>{{ $sale_detail->shipp_name ?? $sale_detail->billing_name }}</strong><br>
                                {{ $sale_detail->shipping_address ?? $sale_detail->billing_address }}<br>
                                <strong>GSTIN : {{ $sale_detail->shipping_gst ?? $sale_detail->billing_gst }}</strong><br>
                                PAN : {{ $sale_detail->shipping_pan ?? $sale_detail->billing_pan }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            {{-- ================= ITEMS ================= --}}
            <tr>
                <th style="width:2%;padding: 0px 3px;">S. No.</th>
                <th colspan="2" style="text-align:left; width:30%;">Description of Goods</th>
                <th style="text-align:center; width:3%;">HSN/SAC Code</th> <!-- Centered SAC Code --> 
                <th style="text-align:right; width:11%;">Qty.</th>
                <th style="text-align:center; width:2%;">Unit</th>
                <th style="text-align:right; width:12%;">Price</th>
                <th style="text-align:right; width:15%;">Amount (₹)</th>
            </tr>
            @php $i = 1; $item_total = 0; $qty_total = 0;@endphp
            @foreach($items_detail as $item)
                <tr>
                    <td style="text-align:center;">{{ $i }}</td>
                    <td colspan="2" style="text-align:left;">
                        <strong>{{ $item->p_name }}</strong>
                        @if($configuration && $configuration->show_item_name == 1)
                        <span style="font-size:10px; color:#555; margin-left:4px;">
                            ({{ $item->name }})
                        </span>
                        @endif
                        @if(isset($item->lines) && count($item->lines) > 0)
                            @foreach($item->lines as $line)
                                
                                <small style="display:block; font-size:10px; font-style: italic; color:#555; margin-left:10px;">
                                    {{ $line->line_text }}
                                </small>
                            @endforeach
                        @endif
                    </td>
                    <td style="text-align:center;">{{ $item->hsn_code }}</td>
                    <td style="text-align:right;">{{ $item->qty }}</td>
                    <td style="text-align:center;">{{ $item->unit }}</td>
                    <td style="text-align:right;">{{ $item->price }}</td>
                    <td style="text-align:right;">{{ formatIndianNumber($item->amount) }}</td>
                </tr>
                @php $i++; $item_total += $item->amount; $qty_total = $qty_total + $item->qty;@endphp
            @endforeach
            @php                       
                foreach($sale_sundry as $sundry){
                    if($sundry->nature_of_sundry=="OTHER"){
                        $i++;
                    }
                }
                if($sale_detail->e_invoice_status==0){
                    $tRows = 10 - $i; 
                }else{
                    $tRows = 5 - $i; 
                }                         
                while($tRows>=0){                     
                    $tRows--; 
                    echo '<tr>
                        <td style="height:15px;">&nbsp;</td>
                        <td colspan="2">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>';
                }
            @endphp 
            {{-- ================= TOTAL ================= --}}
            <tr>
                <td colspan="3" style="border-bottom:0; border-right:0"></td>
                <td style="border-bottom:0; border-left:0; border-right:0"><strong>Total</strong></td>
                <td  style="border-bottom:0; border-left:0; border-right:0"> {{$qty_total}}</td>
                <td  style="border-bottom:0;border-left:0; border-right:0"></td>
                
                <td style="border-bottom:0; border-left:0"><strong></strong></td>
                <td style="text-align:right; border-bottom:0;" class="text-right"><strong>{{ formatIndianNumber($item_total) }}</strong></td>
            </tr>
            {{-- ================= SUNDRY ================= --}}
            <tr>
                <td style="border-right:0; border-top:0;" colspan="2"></td>
                <td colspan="4" style="border-left:0; border-right:0; border-top:0;">
                    @php
                        $addTypes = ['CGST', 'SGST', 'IGST','TCS', 'ROUNDED OFF (+)'];
                        $lessTypes = ['ROUNDED OFF (-)'];
                    @endphp                            
                    @foreach($sale_sundry as $sundry)
                        @php
                            $billsundry = \App\Models\BillSundrys::find($sundry->bill_sundry);  
                        @endphp                            
                        @if($sundry->nature_of_sundry === 'OTHER')
                            @if($sundry->bill_sundry_type === 'additive')
                                <p>Add : {{ $sundry->name }}</p>
                            @elseif($sundry->bill_sundry_type === 'subtractive')
                                <p>Less : {{ $sundry->name }}</p>
                            @endif
                        @elseif(in_array($sundry->nature_of_sundry, $addTypes))
                            <p>Add : {{ $sundry->name }}</p>
                        @elseif(in_array($sundry->nature_of_sundry, $lessTypes))
                            <p>Less : {{ $sundry->name }}</p>
                        @endif
                    @endforeach
                </td>
                <td style="border-left:0; border-top:0;">
                    <!-- <p style="white-space: nowrap;">&nbsp;</p> -->
                    @foreach($sale_sundry as $sundry)
                    <p>@if($sundry->rate!=0) {{$sundry->rate}} % @else &nbsp; @endif</p>
                    @endforeach
                </td>
                <td style="text-align:right; border-top:0;">
                    @foreach($sale_sundry as $sundry)
                    <p>{{formatIndianNumber($sundry->amount)}}</p>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right; border-right: 0; border-bottom: 0;">
                    <p><strong style="font-family: Inter, DejaVu Sans, sans-serif;">Grand Total &#x20B9;</strong></p>
                </td> 
                <td  style="text-align:right; border-right: 0; border-bottom: 0;border-left: 0;">
                    <p><strong></strong></p>
                </td> 
                <td  style="text-align:right; border-right: 0; border-bottom: 0;border-left: 0;">
                    <p><strong></strong></p>
                </td> 
                <td  style="text-align:right; border-right: 0; border-bottom: 0;border-left: 0;">
                    <p><strong></strong></p>
                </td> 
                <td style="text-align:right">
                    <p><strong>{{formatIndianNumber($sale_detail->total)}}</strong></p>
                </td>
            </tr>
            {{-- ================= AMOUNT IN WORDS ================= --}}
            <tr>
                <td colspan="8" style="border-top:0; border-bottom:0;">                        
                    @foreach($gst_detail as $val)
                        <span><u><small>Tax Rate</small></u><br>
                            <small>{{$val->rate}}%</small>
                        </span>
                        <span class="mar_lft10"><u><small>Taxable Amount</small></u><br>
                            <small>{{formatIndianNumber($val->taxable_amount)}}</small>
                        </span>
                        @if(Str::limit($seller_info->gst_no,2,'')==Str::limit($sale_detail->billing_gst,2,''))
                            <span class="mar_lft10"><u><small>CGST</small></u><br>
                                <small>{{formatIndianNumber($val->amount)}}</small>
                            </span>
                            <span class="mar_lft10"><u><small>SGST</small></u><br>
                                <small>{{formatIndianNumber($val->amount)}}</small>
                            </span>
                        @else
                            <span class="mar_lft10"><u><small>IGST</small></u><br>
                                <small>{{formatIndianNumber($val->amount)}}</small>
                            </span>
                        @endif                        
                        <span class="mar_lft10"><u><small>Total Tax</small></u><br>
                            @if(Str::limit($seller_info->gst_no,2,'')==Str::limit($sale_detail->billing_gst,2,''))
                                <small>{{formatIndianNumber($val->amount+$val->amount)}}</small>
                            @else
                                <small>{{formatIndianNumber($val->amount)}}</small>
                            @endif
                        </span><br>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border-top:0;">
                    <strong>
                        <?php
                            $number = (float) $sale_detail->total;
                            $no = floor($number);
                            $point = round(($number - $no) * 100);
                            $words = [
                                '0' => '', '1' => 'one', '2' => 'two', '3' => 'three',
                                '4' => 'four', '5' => 'five', '6' => 'six',
                                '7' => 'seven', '8' => 'eight', '9' => 'nine',
                                '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
                                '13' => 'thirteen', '14' => 'fourteen',
                                '15' => 'fifteen', '16' => 'sixteen',
                                '17' => 'seventeen', '18' => 'eighteen',
                                '19' => 'nineteen', '20' => 'twenty',
                                '30' => 'thirty', '40' => 'forty',
                                '50' => 'fifty', '60' => 'sixty',
                                '70' => 'seventy', '80' => 'eighty', '90' => 'ninety'
                            ];
                            $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];
                            $str = [];
                            $i = 0;
                            while ($no > 0) {
                                $divider = ($i == 2) ? 10 : 100;
                                $numberPart = $no % $divider;
                                $no = floor($no / $divider);
                                $i += ($divider == 10) ? 1 : 2;

                                if ($numberPart) {
                                    $counter = count($str);
                                    $hundred = ($counter == 1 && !empty($str[0])) ? ' and ' : '';
                                    $str[] = ($numberPart < 21)
                                        ? $words[$numberPart] . ' ' . $digits[$counter] . $hundred
                                        : $words[floor($numberPart / 10) * 10] . ' ' .
                                        $words[$numberPart % 10] . ' ' .
                                        $digits[$counter] . $hundred;
                                } else {
                                    $str[] = null;
                                }
                            }
                            $result = implode('', array_reverse($str));
                            echo ucfirst(trim($result)) . ' Rupees';
                            if ($point > 0) {
                                echo ' and ' . $words[floor($point / 10) * 10] . ' ' .
                                    $words[$point % 10] . ' Paise';
                            }
                            echo ' only';
                        ?>
                    </strong>
                </td>
            </tr>
            {{-- ================= BANK ================= --}}
            @if($bank_detail)
                <tr>
                    <td colspan="8">
                        @if($configuration && $configuration->banks)
                            <p style="margin:4px 0;">
                                <strong>Bank Details :</strong>
                                <strong>ACCOUNT NAME -</strong> {{ $configuration->banks->name }} <br>
                                <strong>ACCOUNT NO:</strong> {{ $configuration->banks->account_no }},
                                <strong>IFSC CODE:</strong> {{ $configuration->banks->ifsc }},
                                <strong>BANK NAME:</strong> {{ $configuration->banks->bank_name }},
                                {{ $configuration->banks->branch }}
                            </p>
                        @endif
                    </td>
                </tr>
            @endif
            {{-- ================= SIGN ================= --}}
            <tr>
                <td colspan="4" style="vertical-align: top; padding: 5px; ">
                    @if($configuration && $configuration->term_status==1 && $configuration->terms && count($configuration->terms)>0)
                        <p style="margin: 0;"><small><b>Terms &amp; Conditions</b></small></p>
                        <p style="margin: 0;"><small>E.&amp; O.E.</small></p>
                        @php $i = 1; @endphp
                        @foreach($configuration->terms as $k => $t)
                              <p style="margin: 0; line-height: 1;"><small>{{$i}}. {{$t->term}}</small></p>
                              @php $i++; @endphp
                        @endforeach
                    @endif
                </td>
                <td style="width:50%;"  colspan="4" class="text-right">
                    For {{ $company_data->company_name }}<br><br>
                    @if($signBase64)
                        <img src="{{ $signBase64 }}" height="60">
                    @endif
                    <br><strong>Authorised Signatory</strong>
                </td>
            </tr>
        </tbody>
    </table>
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
            }
            $item_total = 0;
        @endphp
        <div class="page-break"></div>
        <style>
            .eway-pdf-wrapper {
                font-family: Arial, sans-serif;
                font-size: 13px;
                line-height: 1.4;
                color: #000;
                padding: 10px;
                border: 1px solid #000;
            }
            .eway-section {
                border-bottom: 1px solid #000;
                padding-bottom: 8px;
                margin-bottom: 8px;
            }
            .eway-section:last-child {
                border-bottom: none;
            }
            .eway-label {
                font-weight: 600;
            }
            .eway-title {
                text-align: center;
                font-size: 16px;
                font-weight: 600;
                margin-bottom: 8px;
            }
            .eway-header-grid {
                display: table;
                width: 100%;
            }
            .eway-header-left {
                display: table-cell;
                width: 70%;
                vertical-align: top;
            }
            .eway-header-right {
                display: table-cell;
                width: 30%;
                text-align: right;
                vertical-align: top;
            }
            .eway-grid-3 {
                display: table;
                width: 100%;
                margin-bottom: 2px;
            }
            .eway-col-3 {
                display: table-cell;
                width: 33.33%;
            }
            .eway-grid-2 {
                display: table;
                width: 100%;
                margin-bottom: 6px;
            }
            .eway-col-2 {
                display: table-cell;
                width: 50%;
                vertical-align: top;
            }
            .eway-table {
                width: 100%;
                border-collapse: collapse;
                border: 1px solid #000;
                margin: 4px 0;
            }
            .eway-table th,
            .eway-table td {
                border: 1px solid #000;
                padding: 4px;
                text-align: left;
            }
            .eway-table th {
                background-color: #f0f0f0;
                font-weight: 600;
            }
            .eway-table .text-right {
                text-align: right;
            }
            .eway-table .text-center {
                text-align: center;
            }
        </style>
        <div class="eway-pdf-wrapper">        
            <div class="eway-section">
                <div class="eway-title">e-Way Bill</div>            
                <div class="eway-header-grid">
                    <div class="eway-header-left">
                        <div><span class="eway-label">Doc No : </span>Tax Invoice - {{ $sale_detail->voucher_no_prefix }}</div>
                        <div><span class="eway-label">Date : </span>{{ date('d-m-Y', strtotime($sale_detail->date)) }}</div>
                        <div><span class="eway-label">IRN : </span>{{ $Irn }}</div>
                        <div><span class="eway-label">Ack No : </span>{{ $AckNo }}</div>
                        <div><span class="eway-label">Ack Date : </span>{{ $AckDt }}</div>
                    </div>
                    <div class="eway-header-right">
                        @if(!empty($qrBase64))
                            <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width:90px; height:90px;">
                        @endif
                    </div>
                </div>
            </div>
            <div class="eway-section">
                <div class="eway-label" style="margin-bottom: 4px;">1. e-Way Bill Details</div>            
                <div class="eway-grid-3">
                    <div class="eway-col-3"><span class="eway-label">e-Way Bill No : </span>{{ $ewaybill_no }}</div>
                    <div class="eway-col-3"><span class="eway-label">Mode:</span> Road</div>
                    <div class="eway-col-3"><span class="eway-label">Generated Date : </span>{{ date('d-m-Y h:i a', strtotime($ewayBillDate)) }}</div>
                </div>            
                <div class="eway-grid-3">
                    <div class="eway-col-3"><span class="eway-label">Generated By : </span>{{ $sale_detail->merchant_gst }}</div>
                    <div class="eway-col-3"><span class="eway-label">Approx Distance : </span>{{ $sale_detail->e_waybill_distance }} KM</div>
                    <div class="eway-col-3"><span class="eway-label">Valid Upto : </span>{{ date('d-m-Y h:i a', strtotime($validUpto)) }}</div>
                </div>            
                <div class="eway-grid-3">
                    <div class="eway-col-3"><span class="eway-label">Supply Type : </span>Outward Supply</div>
                    <div class="eway-col-3"><span class="eway-label">Transaction Type : </span>Regular</div>
                    <div class="eway-col-3"></div>
                </div>
            </div>
            <div class="eway-section">
                <div class="eway-label" style="margin-bottom: 4px;">2. Address Details</div>            
                <div class="eway-grid-2">
                    <div class="eway-col-2">
                        <div class="eway-label">From</div>
                        {{ $company_data->company_name }}<br>
                        GSTIN: {{ $seller_info->gst_no }}<br>
                        {{ $seller_info->sname }}
                    </div>
                    <div class="eway-col-2">
                        <div class="eway-label">To</div>
                        {{ $sale_detail->billing_name }}<br>
                        GSTIN: {{ $sale_detail->billing_gst }}<br>
                        {{ $sale_detail->billing_address }}
                    </div>
                </div>
                <div class="eway-grid-2" style="margin-top: 6px;">
                    <div class="eway-col-2">
                        <div class="eway-label">Dispatch From</div>
                        {{ $seller_info->address }}<br>
                    </div>
                    <div class="eway-col-2">
                        <div class="eway-label">Ship To</div>
                        {{ $sale_detail->shipping_address ?? $sale_detail->billing_address }}
                    </div>
                </div>
            </div>
            <div class="eway-section">
                <div class="eway-label" style="margin-bottom: 4px;">3. Goods Details</div>            
                <table class="eway-table">
                    <thead>
                        <tr>
                            <th>HSN Code</th>
                            <th>Product Name & Description</th>
                            <th class="text-right">Quantity</th>
                            <th class="text-right">Taxable Amt</th>
                            <th class="text-center">
                                Tax Rate (
                                @foreach($sale_sundry as $sundry)
                                    @if($sundry->nature_of_sundry === 'CGST')
                                        C+S
                                    @elseif($sundry->nature_of_sundry === 'IGST')
                                        I
                                    @endif
                                @endforeach
                                )
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items_detail as $item)
                            <tr>
                                {{-- 1. HSN --}}
                                <td>{{ $item->hsn_code }}</td>
                                {{-- 2. Product Name & Description --}}
                                <td>
                                    <strong>{{ $item->p_name }}</strong>
                                    <span style="font-size:10px; color:#555;">
                                        ({{ $item->name }})
                                    </span>
                                </td>
                                {{-- 3. Quantity --}}
                                <td class="text-right">
                                    {{ $item->qty }} {{ $item->unit }}
                                </td>
                                {{-- 4. Taxable Amount --}}
                                <td class="text-right">
                                    {{ formatIndianNumber($item->amount) }}
                                </td>
                                {{-- 5. Tax Rate --}}
                                <td class="text-center">
                                    @php
                                        $rates = $sale_sundry->where('rate', '!=', 0)
                                                            ->pluck('rate')
                                                            ->toArray();
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
            <div class="eway-section">
                {{-- Row 1 --}}
                <div class="eway-grid-3">
                    {{-- Total Taxable --}}
                    <div class="eway-col-3">
                        <span class="eway-label">Total Taxable Amt :</span><br>
                        {{ formatIndianNumber($item_total) }}
                    </div>
                    {{-- Tax Amounts (Stacked Properly) --}}
                    <div class="eway-col-3">
                        @foreach($sale_sundry as $sundry)
                            @if($sundry->nature_of_sundry === 'CGST')
                                <div>
                                    <span class="eway-label">CGST Amt :</span>
                                    {{ formatIndianNumber($sundry->amount) }}
                                </div>
                                <div>
                                    <span class="eway-label">SGST Amt :</span>
                                    {{ formatIndianNumber($sundry->amount) }}
                                </div>
                            @endif
                            @if($sundry->nature_of_sundry === 'IGST')
                                <div>
                                    <span class="eway-label">IGST Amt :</span>
                                    {{ formatIndianNumber($sundry->amount) }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                    {{-- Empty Column For Balance --}}
                    <div class="eway-col-3"></div>
                </div>
                {{-- Row 2 --}}
                <div class="eway-grid-3" style="margin-top:6px;">
                    <div class="eway-col-3">
                        <span class="eway-label">Other Amt :</span>
                        @foreach($sale_sundry as $sundry)
                            @if($sundry->nature_of_sundry === 'ROUNDED OFF (+)' || $sundry->nature_of_sundry === 'ROUNDED OFF (-)')
                                @if($sundry->bill_sundry_type === 'additive')
                                    (+){{ formatIndianNumber($sundry->amount) }}
                                @elseif($sundry->bill_sundry_type === 'subtractive')
                                    (-){{ formatIndianNumber($sundry->amount) }}
                                @endif
                            @endif
                        @endforeach
                    </div>
                    <div class="eway-col-3"></div>
                    <div class="eway-col-3" style="text-align:right;">
                        <span class="eway-label">Total Inv Amt :</span>
                        <strong>{{ formatIndianNumber($sale_detail->total) }}</strong>
                    </div>
                </div>
            </div>
            <div class="eway-section">
                <div class="eway-label" style="margin-bottom: 4px;">4. Transportation Details</div>            
                <div class="eway-grid-2">
                    <div class="eway-col-2"><span class="eway-label">Transporter ID:</span></div>
                    <div class="eway-col-2"><span class="eway-label">Doc No : </span>{{ $sale_detail->gr_pr_no }}</div>
                </div>            
                <div class="eway-grid-2">
                    <div class="eway-col-2"><span class="eway-label">Name : </span>{{ $sale_detail->transport_name }}</div>
                    <div class="eway-col-2"><span class="eway-label">Date : </span>{{ date('d-m-Y', strtotime($sale_detail->date)) }}</div>
                </div>
            </div>
            <div>
                <div class="eway-label" style="margin-bottom: 4px;">5. Vehicle Details</div>            
                <div class="eway-grid-3">
                    <div class="eway-col-3"><span class="eway-label">Vehicle No : </span>{{ $sale_detail->vehicle_no }}</div>
                    <div class="eway-col-3"><span class="eway-label">From : </span>UNA BATHRI</div>
                    <div class="eway-col-3"><span class="eway-label">CEWB No : </span></div>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
