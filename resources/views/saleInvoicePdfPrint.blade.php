<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
   .data-table{ font-size: 15px }
   table{ width:100%; border-spacing:0; border:1px solid #dadada; }
   table tr th, table tr td{ border:1px solid #000000; margin:0; padding:2px 5px; }
   hr{ border:1px solid #000000; }
   .text-right{ text-align:right; } .text-left{ text-align:left; }
   p{ margin:0.5px !important; }
   h1,h2,h3,h4,h5,h6{ margin:5px 0px; }
   .mar_lft10{ margin-left:15px; }
   span{ display:inline-block; }
   .width25{ width:35%; }
   .lft_mar15{ margin-left:15px; }
   .wrap-text{ display:inline-block; max-width:55%; word-wrap:break-word; word-break:break-word; white-space:normal; vertical-align:top; }
   .print-runtime-page{ display:flex; flex-direction:column; box-sizing:border-box; width:200mm; height:287mm; overflow:hidden; }
   .print-page-header,.print-page-items,.print-page-financial,.print-page-footer-table{
      width:100%; border-collapse:collapse; table-layout:fixed; flex:0 0 auto;
      font-family:'Source Sans Pro', sans-serif; letter-spacing:.05em; color:#404040; font-size:12px; font-weight:500; border:none !important;
   }
   .print-page-header{ margin-bottom:-1px; }
   .print-page-items{ margin-top:0; }
   .print-page-financial{ margin-top:-1px; }
   .print-page-footer-table{ margin-top:-1px; }
   .print-page-footer-table tr:first-child td, .print-page-footer-table tr:first-child th{ border-top:1px solid #000 !important; }
   .print-page-footer-holder{ flex:0 0 auto; min-height:42mm; }
   .print-page-footer-table{ min-height:42mm; }
   .print-runtime-page .invoice-company-header{ height:auto; min-height:0; max-height:none; overflow:visible; }
   .print-runtime-page .print-compact-row td, .print-runtime-page .print-compact-row th{ padding-top:1px !important; padding-bottom:1px !important; line-height:1.15 !important; }
   .print-runtime-page .print-compact-row p{ margin:0 !important; line-height:1.15 !important; }
   .print-source-wrapper{ display:none !important; }
   .invoice-total{ font-size:16px; font-weight:800; margin:0; white-space:nowrap; }
   .invoice-company-header{
        position:relative;
        height:145px;
        min-height:145px;
        max-height:145px;
        overflow:hidden;
    }
   .invoice-logo-left,
    .invoice-logo-right{
        position:absolute;
        top:28px;
        width:85px;
        height:65px;
        z-index:1;
    }
    .invoice-company-header > div:nth-child(5){
        position:relative;
        z-index:2;
    }
   .invoice-logo-left{ left:10px; } .invoice-logo-right{ right:10px; }
   .invoice-logo-left img,.invoice-logo-right img{ width:100%; height:100%; object-fit:contain; }
   .gst-summary-table{ width:45% !important; display:inline-table !important; }
   .gst-summary-table td,.gst-summary-table th{ border:none !important; }
   .no-border td{ border-top:none; border-bottom:none; border-left:1px solid #000; border-right:1px solid #000; }
   @page { size:A4 portrait; margin:5mm; }
   .print-wrapper{
    width:198mm;
    margin:0 auto;
    box-sizing:border-box;

      page-break-after:always; break-after:page; box-sizing:border-box; overflow:hidden; position:relative;
   }
   .print-wrapper:last-child{ page-break-after:auto; break-after:auto; }
   .page-break{ page-break-before:always; break-before:page; }
   @media print {
   .print-page-spacer {
      flex:1 1 auto;
      min-height:0;
   }
   .print-page-spacer td {
      border-top:0 !important;
   }
   .print-page-footer-holder {
      min-height:38mm;
   }
   .print-page-footer-table {
      height:38mm;
      min-height:38mm;
      max-height:38mm;
      overflow:hidden;
   }
   .print-financial-block,
   .print-financial-block tr {
      page-break-inside: avoid !important;
      break-inside: avoid !important;
   }
   .print-item-row {
      page-break-inside: avoid !important;
      break-inside: avoid !important;
   }
   .print-indivisible-block {
      page-break-inside: avoid !important;
      break-inside: avoid !important;
   }
   .print-page-footer {
      page-break-inside: avoid !important;
      break-inside: avoid !important;
      height:38mm !important;
   }
}
</style>
</head>
<body>

@php
   $PX_FOOTER = 145;
   $printTotalCGST = 0; $printTotalSGST = 0; $printTotalIGST = 0;
   $printDisplaySundries = [];
   foreach ($sale_sundry as $s) {
      $nat = strtoupper($s->nature_of_sundry);
      if ($nat === 'CGST') { $printTotalCGST += $s->amount; }
      elseif ($nat === 'SGST') { $printTotalSGST += $s->amount; }
      elseif ($nat === 'IGST') { $printTotalIGST += $s->amount; }
      else { $printDisplaySundries[] = $s; }
   }
   $items = $items_detail->values()->all();
   $printItemQty = collect($items)->sum(fn($i) => (float)($i->qty ?? 0));
   $printItemAmount = collect($items)->sum(fn($i) => (float)($i->amount ?? 0));
   $printPages = [[
      'items' => $items, 'bf_qty' => 0, 'bf_amount' => 0,
      'cf_qty' => $printItemQty, 'cf_amount' => $printItemAmount,
      'show_final_block' => true, 'is_first' => true,
   ]];
   $totalIGST = $printTotalIGST; 
@endphp

<div class="print-layout">
@php $printSerial = 1; @endphp
@foreach ($printPages as $printPageIndex => $printPage)
   @php
      $printIsFirstPage = ($printPageIndex === 0);
      $printShowFinalBlock = !empty($printPage['show_final_block']);
      $printShowBfRow = (!$printIsFirstPage);
      $printSpacerPx = 0;
   @endphp
   <div class="print-wrapper print-source-wrapper">
   <table style="font-family:'Source Sans Pro', sans-serif;letter-spacing:0.05em;color:#404040;font-size:12px;font-weight:500;padding:10px;width:100%;height:100%;border-collapse:collapse;" class="invoice-copy">
      <tbody class="print-source-header">
         <tr>
            <th colspan="8" style="padding:0;">
               <div class="invoice-company-header">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                     <div style="flex:1; text-align:left;margin-left:5px;">
                        <strong style="color: {{ $configuration->address_color ?? 'black' }};">GSTIN: {{ $seller_info->gst_no }}</strong>
                     </div>
                     <div style="flex:1; text-align:center;">
                        @if($configuration && !empty($configuration->invoice_header_text))
                           <strong style="font-size:13px; font-weight:700; letter-spacing:1px; color: {{ $configuration->address_color ?? 'black' }};">{{ $configuration->invoice_header_text }}</strong>
                        @endif
                     </div>
                     <div style="flex:1; text-align:right;margin-top:7px;margin-right:5px;">
                        <strong style="color: {{ $configuration->address_color ?? 'black' }};">PAN: {{ substr($seller_info->gst_no, 2, 10) }}</strong><br>
                        <small style="color: {{ $configuration->address_color ?? 'black' }};">O/D/T</small>
                     </div>
                  </div>
                  @php
                     $printCompanyName = $company_data->company_name;
                     $printFontSize = strlen($printCompanyName) > 30 ? '18px' : '24px';
                     if ($configuration && $configuration->company_name_font_size != "") { $printFontSize = $configuration->company_name_font_size; }
                  @endphp
                  @if($configuration && $configuration->company_logo_status==1 && $configuration->logo_position_left==1 && !empty($configuration->company_logo))
                     <div class="invoice-logo-left"><img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"></div>
                  @endif
                  @if($configuration && $configuration->company_logo_status==1 && $configuration->logo_position_right==1 && !empty($configuration->company_logo))
                     <div class="invoice-logo-right"><img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"></div>
                  @endif
                  <div style="clear:both;"></div>
                  <div style="text-align:center; line-height:1; margin:0; padding:0;">
                     <p style="margin:0;color: {{ $configuration->address_color ?? 'black' }};"><u>TAX INVOICE</u></p>
                     <p style="margin:0; font-size: {{ $printFontSize }}; font-weight:bold; color: {{ $configuration->company_name_color ?? 'black' }};">{{ $printCompanyName }}</p>
                     <p style="margin:0;"><small style="font-size:12px; display:inline-block; max-width:50%; word-break:break-word;color: {{ $configuration->address_color ?? 'black' }};">{{ $seller_info->address }}</small></p>
                     <p style="margin:0;"><small style="font-size:12px; color: {{ $configuration->address_color ?? 'black' }};">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small></p>
                  </div>
               </div>
            </th>
         </tr>
         @if($sale_detail->e_invoice_status==1 && !empty($einvoice_data))
            <tr>
               <td colspan="8">
                  @if(!empty($qrBase64))
                     <span style="float:right;width:90px;height:90px;position:relative;"><img src="data:image/svg+xml;base64,{{ $qrBase64 }}" style="width:90px;height:90px;"></span>
                  @endif
                  <p>IRN NO. : {{ $einvoice_data->Irn ?? '' }}</p>
                  <p>ACK.NO. : {{ $einvoice_data->AckNo ?? '' }}</p>
                  <p>ACK.DATE : {{ $einvoice_data->AckDt ?? '' }}</p>
               </td>
            </tr>
         @endif
         <tr>
            <td colspan="4">
               <p><span class="width25">Invoice No. </span>: <span class="lft_mar15" style="font-weight:800">{{$sale_detail->voucher_no_prefix}}</span></p>
               <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($sale_detail->date))}}</span></p>
               <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">{{$sale_detail->sname}}</span></p>
               <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15">{{$sale_detail->reverse_charge}}</span></p>
               <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{$sale_detail->gr_pr_no}}</span></p>
            </td>
            <td colspan="4">
               <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text">{{$sale_detail->transport_name}}</span></p>
               <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{$sale_detail->vehicle_no}}</span></p>
               <p><span class="width25">Station </span>: <span class="lft_mar15">{{$sale_detail->station}}</span></p>
               <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15">
                  @if($sale_detail->e_waybill_status==1 && $sale_detail->eway_bill_response)
                     @php $ewaybill_data = json_decode($sale_detail->eway_bill_response); @endphp
                     {{ $ewaybill_data->ewayBillNo ?? '' }}
                  @endif
               </span></p>
               <p>&nbsp;</p>
               @if($configuration && $configuration->purchase_order_status == 1)
                  <p><span class="width25">PO No. </span>: <span class="lft_mar15">{{ $sale_detail->po_no }}</span></p>
                  <p><span class="width25">PO Date </span>: <span class="lft_mar15">{{ $sale_detail->po_date ? date('d-m-Y', strtotime($sale_detail->po_date)) : '' }}</span></p>
               @endif
            </td>
         </tr>
         <tr>
            <td colspan="4" style="position:relative; vertical-align:top; padding:0;">
               <p style="margin:0; position:absolute; top:0; left:5px; font-style:italic;"><strong>Billed to :</strong></p>
               <div style="padding-top:16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                  <p style="margin:2px 0 0 0; line-height:13px;font-weight:800;">{{$sale_detail->billing_name}}</p>
                  <p style="margin:2px 0 0 0; line-height:13px;">{{$sale_detail->billing_address}}</p>
               </div>
               <div style="position:absolute; bottom:0; left:5px; right:4px;">
                  <p style="margin:2px 0 0 0;font-weight:800">GSTIN/UIN : {{$sale_detail->billing_gst}} <span style="float:right;">PAN:{{$sale_detail->billing_pan}}</span></p>
               </div>
            </td>
            <td colspan="4" style="position:relative; vertical-align:top; padding:0; height:120px;">
               <p style="margin:0; position:absolute; top:0; left:5px; font-style:italic;"><strong>Shipped to :</strong></p>
               <div style="padding-top:16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                  <p style="margin:2px 0 0 0; line-height:13px;font-weight:800;">{{ $sale_detail->shipping_name ? $sale_detail->shipp_name : $sale_detail->billing_name }}</p>
                  <p style="margin:2px 0 0 0; line-height:13px;">{{ $sale_detail->shipping_name ? $sale_detail->shipping_address : $sale_detail->billing_address }}</p>
               </div>
               <div style="position:absolute; bottom:0; left:5px; right:4px;">
                  <p style="margin:2px 0 0 0;font-weight:800">
                     GSTIN/UIN : {{ $sale_detail->shipping_name ? $sale_detail->shipping_gst : $sale_detail->billing_gst }}
                     <span style="float:right;">PAN:{{ $sale_detail->shipping_name ? $sale_detail->shipping_pan : $sale_detail->billing_pan }}</span>
                  </p>
               </div>
            </td>
         </tr>
         <tr>
            <th style="width:2%;padding:0px 3px;">S. No.</th>
            <th colspan="2" style="text-align:left; width:30%;">Description of Goods</th>
            <th style="text-align:center; width:3%;">HSN/SAC Code</th>
            <th style="text-align:right; width:11%;">Qty.</th>
            <th style="text-align:center; width:2%;">Unit</th>
            <th style="text-align:right; width:12%;">Price</th>
            <th style="text-align:right; width:15%;">Amount (₹)</th>
         </tr>
         @if($printShowBfRow)
            <tr class="print-compact-row" style="font-weight:700;">
               <td colspan="4" style="text-align:right; padding:1px 5px;">B/F (Brought Forward)</td>
               <td style="text-align:right; padding:1px 5px;">{{$printPage['bf_qty']}}</td>
               <td></td><td></td>
               <td style="text-align:right; padding:1px 5px;">{{formatIndianNumber($printPage['bf_amount'])}}</td>
            </tr>
         @endif
      </tbody>
      <tbody class="print-source-items">
         @foreach($printPage['items'] as $printItem)
            <tr class="print-item-row {{ ($configuration && $configuration->lines_in_item_status == 0) ? 'no-border' : '' }}"
                data-qty="{{ (float)($printItem->qty ?? 0) }}" data-amount="{{ (float)($printItem->amount ?? 0) }}">
               <td style="text-align:center;">{{$printSerial}}</td>
               <td colspan="2" style="text-align:left;">
                  <strong>{{ $printItem->p_name }}</strong>
                  @if($configuration && $configuration->show_item_name == 1)
                     <span style="font-size:9px; color:#777; margin-left:4px;">({{ $printItem->name }})</span>
                  @endif
                  @if(isset($printItem->lines) && count($printItem->lines) > 0)
                     @foreach($printItem->lines as $printLine)
                        <small style="display:block; font-size:10px; font-style:italic; color:#555; margin-left:10px;">{{ $printLine->line_text }}</small>
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
      </tbody>
      <tbody>
         <tr class="print-content-spacer">
            <td style="height:{{ $printSpacerPx }}px;padding:0;">&nbsp;</td>
            <td colspan="2" style="padding:0;">&nbsp;</td>
            <td style="padding:0;">&nbsp;</td><td style="padding:0;">&nbsp;</td>
            <td style="padding:0;">&nbsp;</td><td style="padding:0;">&nbsp;</td><td style="padding:0;">&nbsp;</td>
         </tr>
      </tbody>
      <tbody class="print-financial-block print-indivisible-block">
         <tr class="print-compact-row">
            <td colspan="4" style="border-bottom:0; border-right:0; padding:1px 5px;"></td>
            <td style="border-bottom:0; border-left:0;border-right:0;text-align:right; padding:1px 5px;"><strong>{{$printPage['cf_qty']}}</strong></td>
            <td style="border-bottom:0; border-left:0;border-right:0; padding:1px 5px;"><strong></strong></td>
            <td style="border-bottom:0; border-left:0; padding:1px 5px;"><strong>Total</strong></td>
            <td style="text-align:right; border-bottom:0; padding:1px 5px;">{{formatIndianNumber($printPage['cf_amount'])}}</td>
         </tr>
         <tr class="print-compact-row">
            <td style="border-right:0; border-top:0;" colspan="2"></td>
            <td colspan="4" style="border-left:0; border-right:0; border-top:0;">
               @foreach($printDisplaySundries as $printSundry)
                  @if(stripos($printSundry->name, 'round') === false)
                     @if($printSundry->bill_sundry_type == 'additive') <p>Add : {{ $printSundry->name }}</p> @else <p>Less : {{ $printSundry->name }}</p> @endif
                  @endif
               @endforeach
               @if($printTotalCGST > 0) <p>Add : CGST</p> @endif
               @if($printTotalSGST > 0) <p>Add : SGST</p> @endif
               @if($printTotalIGST > 0) <p>Add : IGST</p> @endif
               @foreach($printDisplaySundries as $printSundry)
                  @if(stripos($printSundry->name, 'round') !== false)
                     @if($printSundry->bill_sundry_type == 'additive') <p>Add : {{ $printSundry->name }}</p> @else <p>Less : {{ $printSundry->name }}</p> @endif
                  @endif
               @endforeach
            </td>
            <td style="border-left:0; border-top:0;">
               @foreach($printDisplaySundries as $s2) <p>&nbsp;</p> @endforeach
               @if($printTotalCGST > 0) <p>&nbsp;</p> @endif
               @if($printTotalSGST > 0) <p>&nbsp;</p> @endif
               @if($printTotalIGST > 0) <p>&nbsp;</p> @endif
            </td>
            <td style="text-align:right; border-top:0;">
               @foreach($printDisplaySundries as $printSundry)
                  @if(stripos($printSundry->name, 'round') === false) <p>{{ formatIndianNumber($printSundry->amount) }}</p> @endif
               @endforeach
               @if($printTotalCGST > 0) <p>{{ formatIndianNumber($printTotalCGST) }}</p> @endif
               @if($printTotalSGST > 0) <p>{{ formatIndianNumber($printTotalSGST) }}</p> @endif
               @if($printTotalIGST > 0) <p>{{ formatIndianNumber($printTotalIGST) }}</p> @endif
               @foreach($printDisplaySundries as $printSundry)
                  @if(stripos($printSundry->name, 'round') !== false) <p>{{ formatIndianNumber($printSundry->amount) }}</p> @endif
               @endforeach
            </td>
         </tr>
         <tr class="print-compact-row">
            <td colspan="7" style="text-align:right; border-right:0; border-bottom:0; padding:1px 5px;"><p style="margin:0;"><strong>Grand Total ₹</strong></p></td>
            <td style="text-align:right; padding:1px 5px; white-space:nowrap;"><p style="margin:0;"><strong class="invoice-total">{{formatIndianNumber($sale_detail->total)}}</strong></p></td>
         </tr>
         <tr class="print-compact-row">
            <td colspan="8" style="border-top:0;border-bottom:0;padding:2px 4px;">
               <table class="gst-summary-table" style="width:45% !important;border:none;border-collapse:collapse;font-size:10px;display:inline-table;">
                  <tr>
                     <td style="border:none;padding:1px;font-weight:bold;">Tax Rate</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">Taxable Amt.</td>
                     @if($totalIGST > 0)
                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">IGST</td>
                     @else
                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">CGST</td>
                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">SGST</td>
                     @endif
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">Total Tax</td>
                  </tr>
                  @php $totalTaxable=0; $totalCGSTAmt=0; $totalSGSTAmt=0; $totalIGSTAmt=0; @endphp
                  @foreach($gst_detail as $printVal)
                     @php
                        $totalTaxable += $printVal->taxable_amount;
                        if($totalIGST > 0){ $totalIGSTAmt += $printVal->amount; }
                        else { $totalCGSTAmt += $printVal->amount; $totalSGSTAmt += $printVal->amount; }
                     @endphp
                     <tr>
                        <td style="border:none;padding:1px;">{{$printVal->rate}}%</td>
                        <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($printVal->taxable_amount) }}</td>
                        @if($totalIGST > 0)
                           <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($printVal->amount) }}</td>
                        @else
                           <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($printVal->amount) }}</td>
                           <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($printVal->amount) }}</td>
                        @endif
                        <td style="border:none;padding:1px;text-align:right;">
                           {{ $totalIGST > 0 ? formatIndianNumber($printVal->amount) : formatIndianNumber($printVal->amount * 2) }}
                        </td>
                     </tr>
                  @endforeach
                  <tr><td colspan="5" style="padding:0;border:none;"><hr style="margin:2px 0;border:none;border-top:1px solid #000;"></td></tr>
                  <tr>
                     <td style="border:none;padding:1px;font-weight:bold;">Total</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalTaxable) }}</td>
                     @if($totalIGST > 0)
                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalIGSTAmt) }}</td>
                     @else
                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalCGSTAmt) }}</td>
                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalSGSTAmt) }}</td>
                     @endif
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                        {{ $totalIGST > 0 ? formatIndianNumber($totalIGSTAmt) : formatIndianNumber($totalCGSTAmt + $totalSGSTAmt) }}
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
        <tr class="print-compact-row">
                     <td colspan="8" style="border-top:0">
                        <strong>
                           <?php
                           $number = $sale_detail->total;
                           $no = floor($number);
                           $point = round($number - $no, 2) * 100;
                           $hundred = null;
                           $digits_1 = strlen($no);
                           $i = 0;
                           $str = array();
                           $words = array(
                               '0' => '', '1' => 'one', '2' => 'two',
                               '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
                               '7' => 'seven', '8' => 'eight', '9' => 'nine',
                               '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
                               '13' => 'thirteen', '14' => 'fourteen',
                               '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
                               '18' => 'eighteen', '19' => 'nineteen', '20' => 'twenty',
                               '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
                               '60' => 'sixty', '70' => 'seventy',
                               '80' => 'eighty', '90' => 'ninety'
                           );
                           $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
                           while ($i < $digits_1) {
                               $divider = ($i == 2) ? 10 : 100;
                               $number = floor($no % $divider);
                               $no = floor($no / $divider);
                               $i += ($divider == 10) ? 1 : 2;
                               if ($number) {
                                   $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                                   $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                                   $str[] = ($number < 21) ? $words[$number] .
                                       " " . $digits[$counter] . $plural . " " . $hundred
                                       :
                                       $words[floor($number / 10) * 10]
                                       . " " . $words[$number % 10] . " "
                                       . $digits[$counter] . $plural . " " . $hundred;
                               } else $str[] = null;
                           }
                           $str = array_reverse($str);
                           $result = implode('', $str);
                           $points = ($point) ?
                               "." . $words[$point / 10] . " " .
                               $words[$point = $point % 10] : '';
                           echo ucfirst($result) . "Rupees  only";
                           ?>
                        </strong>
                     </td>
                  </tr>
         @if($configuration && $configuration->bank_detail_status == 1 && $bank_detail)
            <tr class="print-compact-row">
               <td colspan="8" style="padding:1px 5px;">
                  @if($configuration && $configuration->banks)
                     <p><strong>Bank Details : </strong> <strong>ACCOUNT NAME-</strong>{{$configuration->banks->name}} <br><strong>ACCOUNT NO:</strong>{{$configuration->banks->account_no}} ,<strong>IFSC CODE:</strong>{{$configuration->banks->ifsc}} ,<strong>BANK NAME:</strong>{{$configuration->banks->bank_name}},{{$configuration->banks->branch}}</p>
                  @endif
               </td>
            </tr>
         @endif
      </tbody>
      <tbody class="print-page-footer">
         <tr>
            <td colspan="4" style="vertical-align:top; padding:2px 5px; height:{{ $PX_FOOTER }}px; min-height:{{ $PX_FOOTER }}px;">
               @if($configuration && $configuration->term_status==1 && $configuration->terms && count($configuration->terms)>0)
                  <p style="margin:0;line-height:1.1;"><small><b>Terms &amp; Conditions</b></small></p>
                  <p style="margin:0;line-height:1.1;"><small>E.&amp; O.E.</small></p>
                  @php $printTermNo = 1; @endphp
                  @foreach($configuration->terms as $printTerm)
                     <p style="margin:0;line-height:1.1;font-size:9px;">{{$printTermNo}}. {{$printTerm->term}}</p>
                     @php $printTermNo++; @endphp
                  @endforeach
               @endif
            </td>
            <td colspan="4" style="vertical-align:top; padding:2px 5px; height:{{ $PX_FOOTER }}px; min-height:{{ $PX_FOOTER }}px; position:relative;">
               <div style="height:{{ $PX_FOOTER - 10 }}px; position:relative;">
                  <p style="margin:0; line-height:1.1;"><small>Receiver's Signature :</small></p>
                  <div style="height:20px;"></div>
                  <p style="text-align:right; margin:0; font-weight:bold;">for {{$company_data->company_name}}</p>
                  @if($configuration && $configuration->signature_status == 1 && !empty($configuration->signature))
                     <div style="position:absolute; top:50%; right:2px; width:170px; text-align:center; height:150px; margin-left:-75px; margin-top:-75px; z-index:10;">
                        <img src="{{ URL::asset('public/images')}}/{{$configuration->signature}}" style="width:150px; height:150px; object-fit:contain;">
                     </div>
                  @endif
                  <p style="position:absolute; right:0; bottom:0; margin:0; font-weight:bold;">Authorised Signatory</p>
               </div>
            </td>
         </tr>
      </tbody>
   </table>
   </div>
@endforeach
</div>

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
                if (!empty($validUpto)) {
                    try {
                        // Format: 2026-07-01 23:59:00
                        $formattedValidUpto = \Carbon\Carbon::createFromFormat(
                            'Y-m-d H:i:s',
                            $validUpto
                        )->format('d/m/Y h:i A');
                
                    } catch (\Exception $e) {
                
                        try {
                            // Format: 29/06/2026 11:59:00 PM
                            $formattedValidUpto = \Carbon\Carbon::createFromFormat(
                                'd/m/Y h:i:s A',
                                $validUpto
                            )->format('d/m/Y h:i A');
                
                        } catch (\Exception $e) {
                            $formattedValidUpto = $validUpto;
                        }
                    }
                }

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
                            {{ $formattedValidUpto }}
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
@include('layouts.footer')
<script>
function invoiceNumber(value) {
   return new Intl.NumberFormat('en-IN', { minimumFractionDigits:0, maximumFractionDigits:2 }).format(Number(value) || 0);
}
function invoiceForwardRow(label, qty, amount) {
   const row = document.createElement('tr');
   row.className = 'print-compact-row print-forward-row';
   row.style.fontWeight = '700';
   row.innerHTML = `
      <td colspan="4" style="text-align:right;padding:1px 5px;">${label}</td>
      <td style="text-align:right;padding:1px 5px;">${invoiceNumber(qty)}</td>
      <td></td><td></td>
      <td style="text-align:right;padding:1px 5px;">${invoiceNumber(amount)}</td>
   `;
   return row;
}
function buildInvoicePrintPages(copyCount = 1) {
   const layout = document.querySelector('.print-layout');
   const source = layout ? layout.querySelector('.print-source-wrapper') : null;
   if (!layout || !source) return false;
   const sourceHeader = source.querySelector('.print-source-header');
   const sourceItems = Array.from(source.querySelectorAll('.print-source-items > .print-item-row'));
   const sourceFinancial = source.querySelector('.print-financial-block');
   const sourceFooter = source.querySelector('.print-page-footer');
   if (!sourceHeader || !sourceFinancial || !sourceFooter) return false;
   layout.querySelectorAll('.print-runtime-page').forEach(p => p.remove());
   layout.classList.add('is-building');

   const COLGROUP_HTML =
      '<colgroup><col style="width:4%"><col style="width:21%"><col style="width:19%">' +
      '<col style="width:9%"><col style="width:11%"><col style="width:7%">' +
      '<col style="width:11%"><col style="width:18%"></colgroup>';

   const makeTable = (className, tbody) => {
      const table = document.createElement('table');
      table.className = className;
      table.insertAdjacentHTML('afterbegin', COLGROUP_HTML);
      table.appendChild(tbody);
      return table;
   };
   const makePage = (isFirst, broughtQty, broughtAmount) => {
      const page = document.createElement('div');
      page.className = 'print-wrapper print-runtime-page';
      const headerTable = makeTable('invoice-copy print-page-header', sourceHeader.cloneNode(true));
      const itemBody = document.createElement('tbody');
      const itemTable = makeTable('invoice-copy print-page-items', itemBody);
      if (!isFirst) itemBody.appendChild(invoiceForwardRow('B/F (Brought Forward)', broughtQty, broughtAmount));
      const spacerBody = document.createElement('tbody');
      const spacerRow = document.createElement('tr');
      for (let c = 0; c < 7; c++) {
         const td = document.createElement('td');
         td.innerHTML = '&nbsp;';
         if (c === 1) td.colSpan = 2;
         spacerRow.appendChild(td);
      }
      spacerBody.appendChild(spacerRow);
      const spacer = makeTable('invoice-copy print-page-items print-page-spacer', spacerBody);
      const footerHolder = document.createElement('div');
      footerHolder.className = 'print-page-footer-holder';
      footerHolder.appendChild(makeTable('invoice-copy print-page-footer-table', sourceFooter.cloneNode(true)));
      page.append(headerTable, itemTable, spacer, footerHolder);
      layout.appendChild(page);
      return { page, itemBody, itemTable, spacer, footerHolder, itemCount: 0 };
   };
   const usedHeight = ps => {
      const financial = ps.page.querySelector('.print-page-financial');
      return ps.page.querySelector('.print-page-header').offsetHeight + ps.itemTable.offsetHeight +
         (financial ? financial.offsetHeight : 0) + ps.footerHolder.offsetHeight;
   };
   const fits = ps => usedHeight(ps) <= ps.page.clientHeight + 1;
   const addFinancial = ps => {
      const financialTable = makeTable('invoice-copy print-page-financial', sourceFinancial.cloneNode(true));
      ps.page.insertBefore(financialTable, ps.footerHolder);
      return financialTable;
   };
   let current = makePage(true, 0, 0);
   let runningQty = 0, runningAmount = 0;
   sourceItems.forEach(sourceRow => {
      let placed = false;
      while (!placed) {
         const itemRow = sourceRow.cloneNode(true);
         const qty = Number(sourceRow.dataset.qty) || 0;
         const amount = Number(sourceRow.dataset.amount) || 0;
         current.itemBody.appendChild(itemRow);
         const testForward = invoiceForwardRow('Carry Forward', runningQty + qty, runningAmount + amount);
         current.itemBody.appendChild(testForward);
         const candidateFits = fits(current);
         testForward.remove();
         if (candidateFits || current.itemCount === 0) {
            runningQty += qty; runningAmount += amount; current.itemCount++; placed = true; return;
         }
         itemRow.remove();
         current.itemBody.appendChild(invoiceForwardRow('Carry Forward', runningQty, runningAmount));
         current = makePage(false, runningQty, runningAmount);
      }
   });
   const financialTable = addFinancial(current);
   if (!fits(current) && sourceItems.length > 0) {
      financialTable.remove();
      current.itemBody.appendChild(invoiceForwardRow('Carry Forward', runningQty, runningAmount));
      current = makePage(false, runningQty, runningAmount);
      addFinancial(current);
   }
   const originalPages = Array.from(layout.querySelectorAll('.print-runtime-page'));
   for (let copy = 1; copy < Math.max(1, copyCount); copy++) {
      originalPages.forEach(page => layout.appendChild(page.cloneNode(true)));
   }
   layout.classList.remove('is-building');
   return true;
}

document.addEventListener('DOMContentLoaded', async function () {
   const billCopies = {{ (int) ($configuration->no_of_bill_copy ?? 1) }};
   if (document.fonts && document.fonts.ready) { await document.fonts.ready; }
   const pendingImages = Array.from(document.querySelectorAll('.print-source-wrapper img'))
      .filter(img => !img.complete)
      .map(img => new Promise(res => {
         img.addEventListener('load', res, { once: true });
         img.addEventListener('error', res, { once: true });
      }));
   await Promise.all(pendingImages);
   buildInvoicePrintPages(billCopies || 1);

   const marker = document.createElement('div');
   marker.id = 'pdf-ready';
   document.body.appendChild(marker);
});
</script>
</body>
</html>