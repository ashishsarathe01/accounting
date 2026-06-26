@extends('layouts.app')
@section('content')
@include('layouts.header')
@php
$source = request()->get('source');
$return_url = request()->get('return_url');
@endphp
<style type="text/css">
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
   .screen-layout{
      display:block;
   }
   .print-layout{
      display:none;
   }
   @media print{
      .noprint{
         display:none;
      }
      .screen-layout{
         display:none !important;
      }
      .print-layout{
         display:block !important;
      }
   }
   @media print {
      table {
         width: 100% !important;
      }
      .header-section {
         display: none !important;
      }
      .sidebar {
         display: none !important;
      }
   }
   @page { size: auto; margin: 0mm; }
   .importantRule { 
      display: none !important;
   }
   p {
      margin: 0.5px !important;
   }
   .invoice-total{
      font-size:16px;
      font-weight:800;
      margin:0;
      white-space:nowrap;
   }
   .wrap-text {
      display: inline-block;
      max-width: 55%;
      word-wrap: break-word;
      word-break: break-word;
      white-space: normal;
      vertical-align: top;
   }
   @media print {
      table {
         page-break-after: auto;
      }
      .print-wrapper {
         display: block;
         page-break-after: always;
         break-after: page;
         box-sizing: border-box;
         min-height: 1084px;
         overflow: visible;
         position: relative;
      }
      .print-wrapper:last-child {
         page-break-after: auto;
         break-after: auto;
      }
      .print-wrapper > table.invoice-copy {
         width: 100%;
         table-layout: auto;
      }
      .print-content-spacer td{
         border-top:none !important;
         border-bottom:none !important;
         padding:0 !important;
      }
      .invoice-copy {
         width: 100%;
      }
      .print-financial-block {
         page-break-inside: avoid !important;
         break-inside: avoid !important;
      }
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
      }
      .print-compact-row td,
      .print-compact-row th {
         padding-top: 1px !important;
         padding-bottom: 1px !important;
         line-height: 1.15 !important;
      }
      .print-compact-row p {
         margin: 0 !important;
         line-height: 1.15 !important;
      }
      .print-summary-page .invoice-company-header {
         height: 110px !important;
         min-height: 110px !important;
         max-height: 110px !important;
      }
   }
   @page {
      size: A4 portrait;
      margin: 5mm;
   }
   .no-border td {
      border-top: none;
      border-bottom: none;
      border-left: 1px solid #000;
      border-right: 1px solid #000;
   }
   .invoice-company-header{
      height:130px;
      min-height:130px;
      max-height:130px;
      overflow:hidden;
      position:relative;
   }
   .invoice-logo-left,
   .invoice-logo-right{
      position:absolute;
      top:50px;
      width:90px;
      height:70px;
      overflow:hidden;
   }
   .invoice-logo-left{
      left:10px;
   }
   .invoice-logo-right{
      right:10px;
   }
   .invoice-logo-left img,
   .invoice-logo-right img{
      width:100%;
      height:100%;
      object-fit:contain;
   }
   .gst-summary-table{
      width:45% !important;
      display:inline-table !important;
   }
   .gst-summary-table td,
   .gst-summary-table th{
      border:none !important;
   }

.item-spacer td{
    border-top:none !important;
    border-bottom:none !important;
}
</style>

@php

$_previewVoucherPrefix = 'ABC/001';
$_previewPan = strlen($seller_info->gst_no ?? '') >= 12 ? substr($seller_info->gst_no, 2, 10) : 'XXXXXXXXXX';
$_previewBillingAddress = $seller_info->address ?? '';
$_previewBillingName = $company_data->company_name ?? 'Sample Party';

// ── Sample IRN (always shown in preview so the IRN block is visible) ──
$_previewIrn     = '2a694803c9b77900bccfbcbd0052aff3519521f1e649877fca5c22f3e4d9692a';
$_previewAckNo   = '123456789987456';
$_previewAckDt   = date('d/m/Y');
$_previewQrCode  = $_previewIrn; // QR encodes the signed QR / IRN string
$showIrn = ($einvoice_status ?? 0) == 1;
$sale_detail = (object)[
   'id'                 => 0,
   'voucher_no_prefix'  => $_previewVoucherPrefix,
   'date'               => date('Y-m-d'),
   'sname'              => 'DELHI',
   'reverse_charge'     => '',
   'gr_pr_no'           => '',
   'transport_name'     => 'SAMPLE TRANSPORT CO.',
   'vehicle_no'         => 'XX00X0000',
   'station'            => '',
   'e_waybill_status'   => 0,
   'eway_bill_response' => null,
   'e_invoice_status' => $showIrn ? 1 : 0,
   'einvoice_response' => $showIrn
      ? json_encode((object)[
         'Irn'          => $_previewIrn,
         'AckNo'        => $_previewAckNo,
         'AckDt'        => $_previewAckDt,
         'SignedQRCode' => $_previewQrCode,
      ])
      : null,
   'billing_name'       => $_previewBillingName,
   'billing_address'    => $_previewBillingAddress,
   'billing_gst'        => $seller_info->gst_no ?? 'XXXXXXXXXXXX',
   'billing_pan'        => $_previewPan,
   'shipping_name'      => null,
   'shipp_name'         => null,
   'shipping_address'   => null,
   'shipping_gst'       => null,
   'shipping_pan'       => null,
   'total'              => 1,   // placeholder — recalculated below
   'po_no'              => '',
   'po_date'            => null,
   'approved_status'    => 1,
];

$items_detail = collect([
   (object)[
      'p_name'              => 'ITEM A',
      'name'                => 'Item',
      'hsn_code'            => '12345678',
      'qty'                 => 1961.00,
      'unit'                => 'XX',
      'price'               => 34.10,
      'amount'              => 66870.10,
      'sale_description_id' => 1,
      'lines'               => [],
   ],
   (object)[
      'p_name'              => 'ITEM B',
      'name'                => 'Item',
      'hsn_code'            => '12345678',
      'qty'                 => 850.00,
      'unit'                => 'XX',
      'price'               => 42.00,
      'amount'              => 35700.00,
      'sale_description_id' => 2,
      'lines'               => [],
   ],
   (object)[
      'p_name'              => 'ITEM C',
      'name'                => 'Item',
      'hsn_code'            => '12345678',
      'qty'                 => 500.00,
      'unit'                => 'XX',
      'price'               => 28.50,
      'amount'              => 14250.00,
      'sale_description_id' => 3,
      'lines'               => [],
   ],
]);

$_previewItemTotal = $items_detail->sum('amount');  // 116820.10
$_previewFreight   = 700.00;
$_previewTaxable   = $_previewItemTotal + $_previewFreight;  // 117520.10
$_previewCGST      = round($_previewTaxable * 0.09, 2);      // 10576.81
$_previewSGST      = round($_previewTaxable * 0.09, 2);      // 10576.81
$_previewRoundOff  = 0.28;
$_previewGrandTotal = $_previewItemTotal + $_previewFreight + $_previewCGST + $_previewSGST + $_previewRoundOff;

// Patch total back into sale_detail
$sale_detail->total = $_previewGrandTotal;

$sale_sundry = collect([
   (object)['name' => 'Freight & Forwarding Charges', 'nature_of_sundry' => 'OTHER', 'bill_sundry_type' => 'additive', 'amount' => $_previewFreight,  'rate' => 0],
   (object)['name' => 'CGST',                         'nature_of_sundry' => 'CGST',  'bill_sundry_type' => 'additive', 'amount' => $_previewCGST,     'rate' => 9],
   (object)['name' => 'SGST',                         'nature_of_sundry' => 'SGST',  'bill_sundry_type' => 'additive', 'amount' => $_previewSGST,     'rate' => 9],
   (object)['name' => 'Rounded Off (+)',               'nature_of_sundry' => 'OTHER', 'bill_sundry_type' => 'additive', 'amount' => $_previewRoundOff, 'rate' => 0],
]);

$gst_detail = collect([
   (object)['rate' => 18, 'taxable_amount' => $_previewTaxable, 'amount' => $_previewCGST],
]);

$bank_detail = ($configuration && $configuration->bank_detail_status == 1) ? true : false;

$month_arr = [];

$totalCGST_pre = 0;
$totalSGST_pre = 0;
$totalIGST_pre = 0;
$displaySundries_pre = [];

foreach($sale_sundry as $s){
   $nat = strtoupper($s->nature_of_sundry);
   if($nat === 'CGST')      { $totalCGST_pre += $s->amount; }
   elseif($nat === 'SGST')  { $totalSGST_pre += $s->amount; }
   elseif($nat === 'IGST')  { $totalIGST_pre += $s->amount; }
   else                     { $displaySundries_pre[] = $s; }
}

$PX_LINE          = 21;
$PX_PAGE          = 1084;
$PX_HEADER_NO_IRN = 420;
$PX_HEADER_IRN    = 504;
$PX_BF_ROW        = 21;
$PX_CF_ROW        = 21;

$printHasIrn = $showIrn;

$px_header_actual = $printHasIrn
    ? $PX_HEADER_IRN
    : $PX_HEADER_NO_IRN;

$PX_TERM_LINE    = 12;
$PX_TERM_HEADER  = 24;
$PX_SIGNATURE_MIN = 80;
$PX_FOOTER_PAD   = 8;

$termCount = 0;
if($configuration && $configuration->term_status == 1 && $configuration->terms){
   $termCount = count($configuration->terms);
}
$effectiveTerms   = max(9, $termCount);
$px_terms_content = $PX_TERM_HEADER + ($effectiveTerms * $PX_TERM_LINE);
$PX_FOOTER        = max($px_terms_content, $PX_SIGNATURE_MIN) + $PX_FOOTER_PAD;

$sundryRows_pre = count($displaySundries_pre);
if($totalCGST_pre > 0) $sundryRows_pre++;
if($totalSGST_pre > 0) $sundryRows_pre++;
if($totalIGST_pre > 0) $sundryRows_pre++;
$sundryRows_pre = max(1, $sundryRows_pre);

$bankRow_pre = ($configuration && $configuration->bank_detail_status == 1 && $bank_detail) ? 1 : 0;
$gstRows_pre = count($gst_detail);

$PX_FINANCIAL =
     $PX_LINE
   + ($sundryRows_pre  * $PX_LINE)
   + $PX_LINE
   + ((2 + $gstRows_pre + 1) * 14)
   + $PX_LINE
   + ($bankRow_pre * $PX_LINE);

$fnItemPx = function($item) use ($PX_LINE){
   $sub = 0;
   if(isset($item->lines) && (is_array($item->lines) || is_countable($item->lines))){
      $sub = count($item->lines);
   }
   return (1 + $sub) * $PX_LINE;
};

$items_arr  = $items_detail->values()->all();
$totalItems = count($items_arr);

$printPages  = [];
$idx         = 0;
$runningQty  = 0;
$runningAmt  = 0;

$middleArea      = $PX_PAGE - $px_header_actual - $PX_CF_ROW - $PX_FOOTER;
$finalAreaFirst  = $PX_PAGE - $px_header_actual - $PX_FINANCIAL - $PX_FOOTER;
$finalAreaOther  = $PX_PAGE - $px_header_actual - $PX_BF_ROW - $PX_FINANCIAL - $PX_FOOTER;

while($idx < $totalItems){
   $isFirst   = count($printPages) === 0;
   $remaining = array_slice($items_arr, $idx);

   $remainPx = 0;
   foreach($remaining as $r){ $remainPx += $fnItemPx($r); }

   $finalArea = $isFirst ? $finalAreaFirst : $finalAreaOther;

   if($remainPx <= $finalArea){
      $sumQty = 0; $sumAmt = 0;
      foreach($remaining as $r){ $sumQty += $r->qty ?? 0; $sumAmt += $r->amount ?? 0; }
      $printPages[] = [
         'items'           => $remaining,
         'bf_qty'          => $isFirst ? 0 : $runningQty,
         'bf_amount'       => $isFirst ? 0 : $runningAmt,
         'cf_qty'          => $runningQty + $sumQty,
         'cf_amount'       => $runningAmt + $sumAmt,
         'show_final_block'=> true,
         'is_first'        => $isFirst,
      ];
      break;
   }

   $packItems = []; $packPx = 0;
   foreach($remaining as $r){
      $rPx = $fnItemPx($r);
      if(!empty($packItems) && ($packPx + $rPx) > $middleArea){ break; }
      $packItems[] = $r;
      $packPx += $rPx;
   }
   if(empty($packItems) && !empty($remaining)){
      $packItems[] = $remaining[0];
      $packPx = $fnItemPx($remaining[0]);
   }
   $packQty = 0; $packAmt = 0;
   foreach($packItems as $pi){ $packQty += $pi->qty ?? 0; $packAmt += $pi->amount ?? 0; }

   $printPages[] = [
      'items'            => $packItems,
      'bf_qty'           => $isFirst ? 0 : $runningQty,
      'bf_amount'        => $isFirst ? 0 : $runningAmt,
      'cf_qty'           => $runningQty + $packQty,
      'cf_amount'        => $runningAmt + $packAmt,
      'show_final_block' => false,
      'is_first'         => $isFirst,
   ];
   $runningQty += $packQty;
   $runningAmt += $packAmt;
   $idx += count($packItems);
}

if($totalItems === 0){
   $printPages[] = [
      'items'            => [],
      'bf_qty'           => 0,
      'bf_amount'        => 0,
      'cf_qty'           => 0,
      'cf_amount'        => 0,
      'show_final_block' => true,
      'is_first'         => true,
   ];
}

$lastIdx = count($printPages) - 1;
if($lastIdx >= 0 && empty($printPages[$lastIdx]['show_final_block'])){
   $lp = $printPages[$lastIdx];
   $printPages[] = [
      'items'            => [],
      'bf_qty'           => $lp['cf_qty'],
      'bf_amount'        => $lp['cf_amount'],
      'cf_qty'           => $lp['cf_qty'],
      'cf_amount'        => $lp['cf_amount'],
      'show_final_block' => true,
      'is_first'         => false,
   ];
}

for($pi = 0; $pi < count($printPages) - 1; $pi++){
   while(!empty($printPages[$pi + 1]['items'])){
      $moved   = $printPages[$pi + 1]['items'][0];
      $movedPx = $fnItemPx($moved);
      $currentUsed = 0;
      foreach($printPages[$pi]['items'] as $it){ $currentUsed += $fnItemPx($it); }
      if(!$printPages[$pi]['is_first']){ $currentUsed += $PX_BF_ROW; }
      $currentLimit = $printPages[$pi]['show_final_block']
         ? ($printPages[$pi]['is_first'] ? $finalAreaFirst : $finalAreaOther)
         : $middleArea;
      if(($currentUsed + $movedPx) > $currentLimit){ break; }
      array_shift($printPages[$pi + 1]['items']);
      $printPages[$pi]['items'][] = $moved;
      $mQty = $moved->qty ?? 0; $mAmt = $moved->amount ?? 0;
      $printPages[$pi]['cf_qty']    += $mQty;
      $printPages[$pi]['cf_amount'] += $mAmt;
      $printPages[$pi + 1]['bf_qty']    = $printPages[$pi]['cf_qty'];
      $printPages[$pi + 1]['bf_amount'] = $printPages[$pi]['cf_amount'];
      $printPages[$pi + 1]['cf_qty']    -= $mQty;
      $printPages[$pi + 1]['cf_amount'] -= $mAmt;
   }
}
@endphp

<div class="list-of-view-company">
<div class="screen-layout">
<section class="list-of-view-company-section container-fluid">
   <div class="row vh-100">
      @include('layouts.leftnav')
      <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

         <div class="d-md-flex justify-content-between py-4 px-2 align-items-center header-section">
            <div class="d-md-flex d-block noprint">
               <div class="calender-administrator my-2 my-md-0 w-min-230 noprint">
                  <button class="btn btn-info" onclick="printpage();">Print Bill</button>
               </div>
            </div>
         </div>
         <br>

         {{-- ── SCREEN TABLE (matches actual invoice screen table exactly) ── --}}
         <table style="font-family:'Source Sans Pro',sans-serif;letter-spacing:0.05em;color:#404040;font-size:12px;font-weight:500;padding:10px;" class="invoice-copy invoice-copy-screen">
            <tbody>

               {{-- ── HEADER ROW ── --}}
               <tr>
                  <th colspan="8" style="padding:0;">
                     <div class="invoice-company-header">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                           <div style="flex:1;text-align:left;margin-left:5px;">
                              <strong style="color:{{ $configuration->address_color ?? 'black' }};">
                                 GSTIN: {{ $seller_info->gst_no }}
                              </strong>
                           </div>
                           <div style="flex:1;text-align:center;">
                              @if($configuration && !empty($configuration->invoice_header_text))
                                 <strong style="font-size:13px;font-weight:700;letter-spacing:1px;color:{{ $configuration->address_color ?? 'black' }};">
                                    {{ $configuration->invoice_header_text }}
                                 </strong>
                              @endif
                           </div>
                           <div style="flex:1;text-align:right;margin-top:7px;margin-right:5px;">
                              <strong style="color:{{ $configuration->address_color ?? 'black' }};">
                                 PAN: {{ substr($seller_info->gst_no, 2, 10) }}
                              </strong><br>
                              <small style="color:{{ $configuration->address_color ?? 'black' }};">O/D/T</small>
                           </div>
                        </div>

                        @php
                           $companyName = $company_data->company_name;
                           $fontSize    = strlen($companyName) > 30 ? '18px' : '24px';
                           if($configuration && $configuration->company_name_font_size != ""){
                              $fontSize = $configuration->company_name_font_size;
                           }
                        @endphp

                        {{-- LEFT LOGO --}}
                        @if($configuration && $configuration->company_logo_status == 1
                           && $configuration->logo_position_left == 1
                           && !empty($configuration->company_logo))
                           <div class="invoice-logo-left">
                              <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                           </div>
                        @endif

                        {{-- RIGHT LOGO --}}
                        @if($configuration && $configuration->company_logo_status == 1
                           && $configuration->logo_position_right == 1
                           && !empty($configuration->company_logo))
                           <div class="invoice-logo-right">
                              <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                           </div>
                        @endif

                        <div style="clear:both;"></div>

                        <div style="text-align:center;line-height:1;margin:0;padding:0;">
                           <p style="margin:0;color:{{ $configuration->address_color ?? 'black' }};"><u>TAX INVOICE</u></p>
                           <p style="margin:0;font-size:{{ $fontSize }};font-weight:bold;color:{{ $configuration->company_name_color ?? 'black' }};">
                              {{ $companyName }}
                           </p>
                           <p style="margin:0;">
                              <small style="font-size:12px;display:inline-block;max-width:50%;word-break:break-word;color:{{ $configuration->address_color ?? 'black' }};">
                                 {{ $seller_info->address }}
                              </small>
                           </p>
                           <p style="margin:0;">
                              <small style="font-size:12px;color:{{ $configuration->address_color ?? 'black' }};">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small>
                           </p>
                        </div>
                     </div>
                  </th>
               </tr>

               @if($showIrn)

                  @php
                     $previewEinvoiceData = json_decode($sale_detail->einvoice_response);
                  @endphp

                  <tr>
                     <td colspan="8">
                           <span style="float:right;width:90px;height:90px;position:relative;">
                              {!! QrCode::size(90)->generate($previewEinvoiceData->SignedQRCode) !!}
                           </span>

                           <p>IRN NO. : {{ $previewEinvoiceData->Irn }}</p>
                           <p>ACK.NO. : {{ $previewEinvoiceData->AckNo }}</p>
                           <p>ACK.DATE : {{ $previewEinvoiceData->AckDt }}</p>
                     </td>
                  </tr>

               @endif

               {{-- ── INVOICE META ── --}}
               <tr>
                  <td colspan="4">
                     <p><span class="width25">Invoice No. </span>: <span class="lft_mar15" style="font-weight:800">{{ $sale_detail->voucher_no_prefix }}</span></p>
                     <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{ date('d-m-Y', strtotime($sale_detail->date)) }}</span></p>
                     <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">{{ $sale_detail->sname }}</span></p>
                     <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15">{{ $sale_detail->reverse_charge }}</span></p>
                     <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{ $sale_detail->gr_pr_no }}</span></p>
                  </td>
                  <td colspan="4">
                     <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text">{{ $sale_detail->transport_name }}</span></p>
                     <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{ $sale_detail->vehicle_no }}</span></p>
                     <p><span class="width25">Station </span>: <span class="lft_mar15">{{ $sale_detail->station }}</span></p>
                     <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15"></span></p>
                     <p>&nbsp;</p>
                     @if($configuration && $configuration->purchase_order_status == 1)
                        <p><span class="width25">PO No. </span>: <span class="lft_mar15">{{ $sale_detail->po_no }}</span></p>
                        <p><span class="width25">PO Date </span>: <span class="lft_mar15">{{ $sale_detail->po_date ? date('d-m-Y', strtotime($sale_detail->po_date)) : '' }}</span></p>
                     @endif
                  </td>
               </tr>

               {{-- ── BILLED TO / SHIPPED TO ── --}}
               <tr>
                  <td colspan="4" style="position:relative;vertical-align:top;padding:0;">
                     <p style="margin:0;position:absolute;top:0;left:5px;font-style:italic;"><strong>Billed to :</strong></p>
                     <div style="padding-top:16px;margin-left:10px;margin-right:5px;padding-bottom:30px;max-height:80px;overflow:hidden;">
                        <p style="margin:2px 0 0 0;line-height:13px;font-weight:800;">{{ $sale_detail->billing_name }}</p>
                        <p style="margin:2px 0 0 0;line-height:13px;">{{ $sale_detail->billing_address }}</p>
                     </div>
                     <div style="position:absolute;bottom:0;left:5px;right:4px;">
                        <p style="margin:2px 0 0 0;font-weight:800">
                           GSTIN/UIN : {{ $sale_detail->billing_gst }}
                           <span style="float:right;">PAN:{{ $sale_detail->billing_pan }}</span>
                        </p>
                     </div>
                  </td>
                  <td colspan="4" style="position:relative;vertical-align:top;padding:0;height:120px;">
                     <p style="margin:0;position:absolute;top:0;left:5px;font-style:italic;"><strong>Shipped to :</strong></p>
                     <div style="padding-top:16px;margin-left:10px;margin-right:5px;padding-bottom:30px;max-height:80px;overflow:hidden;">
                        <p style="margin:2px 0 0 0;line-height:13px;font-weight:800;">
                           @if($sale_detail->shipping_name) {{ $sale_detail->shipp_name }} @else {{ $sale_detail->billing_name }} @endif
                        </p>
                        <p style="margin:2px 0 0 0;line-height:13px;">
                           @if($sale_detail->shipping_name) {{ $sale_detail->shipping_address }} @else {{ $sale_detail->billing_address }} @endif
                        </p>
                     </div>
                     <div style="position:absolute;bottom:0;left:5px;right:4px;">
                        <p style="margin:2px 0 0 0;font-weight:800">
                           @if($sale_detail->shipping_name)
                              GSTIN/UIN : {{ $sale_detail->shipping_gst }}
                              <span style="float:right;">PAN:{{ $sale_detail->shipping_pan }}</span>
                           @else
                              GSTIN/UIN : {{ $sale_detail->billing_gst }}
                              <span style="float:right;">PAN:{{ $sale_detail->billing_pan }}</span>
                           @endif
                        </p>
                     </div>
                  </td>
               </tr>

               {{-- ── ITEM TABLE HEADER ── --}}
               <tr>
                  <th style="width:2%;padding:0px 3px;">S. No.</th>
                  <th colspan="2" style="text-align:left;width:30%;">Description of Goods</th>
                  <th style="text-align:center;width:3%;">HSN/SAC Code</th>
                  <th style="text-align:right;width:11%;">Qty.</th>
                  <th style="text-align:center;width:2%;">Unit</th>
                  <th style="text-align:right;width:12%;">Price</th>
                  <th style="text-align:right;width:15%;">Amount (₹)</th>
               </tr>

               {{-- ── ITEM ROWS ── --}}
               @php $i = 1; $displayLineCount = 0; $item_total = 0; $qty_total = 0; @endphp
               @foreach($items_detail as $item)
                  <tr class="{{ ($configuration && $configuration->lines_in_item_status == 0) ? 'no-border' : '' }}">
                     <td style="text-align:center;">{{ $i }}</td>
                     <td colspan="2" style="text-align:left;">
                        <strong>{{ $item->p_name }}</strong>
                        @if($configuration && $configuration->show_item_name == 1)
                           <span style="font-size:9px;color:#777;margin-left:4px;">({{ $item->name }})</span>
                        @endif
                        @if(isset($item->lines) && count($item->lines) > 0)
                           @foreach($item->lines as $line)
                              <small style="display:block;font-size:10px;font-style:italic;color:#555;margin-left:10px;">{{ $line->line_text }}</small>
                           @endforeach
                        @endif
                     </td>
                     <td style="text-align:center;">{{ $item->hsn_code }}</td>
                     <td style="text-align:right">{{ $item->qty }}</td>
                     <td style="text-align:center">{{ $item->unit }}</td>
                     <td style="text-align:right;">{{ $item->price }}</td>
                     <td style="text-align:right;white-space:nowrap;">{{ formatIndianNumber($item->amount) }}</td>
                  </tr>
                  @php
                     $i++;
                     $displayLineCount++;
                     if(isset($item->lines) && count($item->lines) > 0){ $displayLineCount += count($item->lines); }
                     $item_total += $item->amount;
                     $qty_total  += $item->qty;
                  @endphp
               @endforeach

               {{-- ── BLANK FILLER ROWS ── --}}
               @php
                  $minimumLines = 8;
                  $tRows = $minimumLines - $displayLineCount;
               @endphp

               @while($tRows > 0)

               @if($configuration && $configuration->lines_in_item_status == 0)

                  {{-- Lines in Item = NO --}}
                  <tr style="height:21px;" class="no-border">
                     <td></td>
                     <td colspan="2"></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                  </tr>

               @else

                  {{-- Lines in Item = YES --}}
                  <tr style="height:21px;">
                  <td style="border-top:none;border-bottom:none;"></td>
                  <td colspan="2" style="border-top:none;border-bottom:none;"></td>
                  <td style="border-top:none;border-bottom:none;"></td>
                  <td style="border-top:none;border-bottom:none;"></td>
                  <td style="border-top:none;border-bottom:none;"></td>
                  <td style="border-top:none;border-bottom:none;"></td>
                  <td style="border-top:none;border-bottom:none;"></td>
               </tr>

               @endif

               @php
                  $tRows--;
               @endphp

               @endwhile

               {{-- ── TOTALS ROW ── --}}
               <tr>
                  <td colspan="4" style="border-bottom:0;border-right:0"></td>
                  <td style="border-bottom:0;border-left:0;border-right:0;text-align:right;"><strong>{{ $qty_total }}</strong></td>
                  <td style="border-bottom:0;border-left:0;border-right:0"><strong></strong></td>
                  <td style="border-bottom:0;border-left:0"><strong>Total</strong></td>
                  <td style="text-align:right;border-bottom:0;">{{ formatIndianNumber($item_total) }}</td>
               </tr>

               @php
                  $totalCGST = 0; $totalSGST = 0; $totalIGST = 0;
                  $displaySundries = [];
                  foreach($sale_sundry as $printSundry){
                     $nat = strtoupper($printSundry->nature_of_sundry);
                     if($nat == 'CGST')     { $totalCGST += $printSundry->amount; }
                     elseif($nat == 'SGST') { $totalSGST += $printSundry->amount; }
                     elseif($nat == 'IGST') { $totalIGST += $printSundry->amount; }
                     else                   { $displaySundries[] = $printSundry; }
                  }
               @endphp
               <tr>
                  <td style="border-right:0;border-top:0;" colspan="2"></td>
                  <td colspan="4" style="border-left:0;border-right:0;border-top:0;">
                     @foreach($displaySundries as $ds)
                        @if(stripos($ds->name, 'round') === false)
                           <p>{{ $ds->bill_sundry_type == 'additive' ? 'Add' : 'Less' }} : {{ $ds->name }}</p>
                        @endif
                     @endforeach
                     @if($totalCGST > 0)<p>Add : CGST</p>@endif
                     @if($totalSGST > 0)<p>Add : SGST</p>@endif
                     @if($totalIGST > 0)<p>Add : IGST</p>@endif
                     @foreach($displaySundries as $ds)
                        @if(stripos($ds->name, 'round') !== false)
                           <p>{{ $ds->bill_sundry_type == 'additive' ? 'Add' : 'Less' }} : {{ $ds->name }}</p>
                        @endif
                     @endforeach
                  </td>
                  <td style="border-left:0;border-top:0;">
                     @foreach($displaySundries as $ds)
                        @if(stripos($ds->name, 'round') === false)<p>&nbsp;</p>@endif
                     @endforeach
                     @if($totalCGST > 0)<p>&nbsp;</p>@endif
                     @if($totalSGST > 0)<p>&nbsp;</p>@endif
                     @if($totalIGST > 0)<p>&nbsp;</p>@endif
                     @foreach($displaySundries as $ds)
                        @if(stripos($ds->name, 'round') !== false)<p>&nbsp;</p>@endif
                     @endforeach
                  </td>
                  <td style="text-align:right;border-top:0;">
                     @foreach($displaySundries as $ds)
                        @if(stripos($ds->name, 'round') === false)<p>{{ formatIndianNumber($ds->amount) }}</p>@endif
                     @endforeach
                     @if($totalCGST > 0)<p>{{ formatIndianNumber($totalCGST) }}</p>@endif
                     @if($totalSGST > 0)<p>{{ formatIndianNumber($totalSGST) }}</p>@endif
                     @if($totalIGST > 0)<p>{{ formatIndianNumber($totalIGST) }}</p>@endif
                     @foreach($displaySundries as $ds)
                        @if(stripos($ds->name, 'round') !== false)<p>{{ formatIndianNumber($ds->amount) }}</p>@endif
                     @endforeach
                  </td>
               </tr>

               {{-- ── GRAND TOTAL ── --}}
               <tr>
                  <td colspan="7" style="text-align:right;border-right:0;border-bottom:0;">
                     <p><strong>Grand Total ₹</strong></p>
                  </td>
                  <td style="text-align:right">
                     <p><strong class="invoice-total">{{ formatIndianNumber($sale_detail->total) }}</strong></p>
                  </td>
               </tr>

               {{-- ── GST SUMMARY TABLE ── --}}
               <tr>
                  <td colspan="8" style="border-top:0;border-bottom:0;padding:2px 4px;">
                     <table class="gst-summary-table" style="width:45% !important;border:none;border-collapse:collapse;font-size:10px;display:inline-table;">
                        <tr>
                           <td style="border:none;padding:1px;font-weight:bold;">Tax Rate</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">Taxable Amt.</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">CGST</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">SGST</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">Total Tax</td>
                        </tr>
                        @php $totalTaxable = 0; $totalCGSTSum = 0; $totalSGSTSum = 0; @endphp
                        @foreach($gst_detail as $gv)
                           @php $totalTaxable += $gv->taxable_amount; $totalCGSTSum += $gv->amount; $totalSGSTSum += $gv->amount; @endphp
                           <tr>
                              <td style="border:none;padding:1px;">{{ $gv->rate }}%</td>
                              <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->taxable_amount) }}</td>
                              <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->amount) }}</td>
                              <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->amount) }}</td>
                              <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->amount * 2) }}</td>
                           </tr>
                        @endforeach
                        <tr><td colspan="5" style="padding:0;border:none;"><hr style="margin:2px 0;border:none;border-top:1px solid #000;"></td></tr>
                        <tr>
                           <td style="border:none;padding:1px;font-weight:bold;">Total</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalTaxable) }}</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalCGSTSum) }}</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalSGSTSum) }}</td>
                           <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalCGSTSum + $totalSGSTSum) }}</td>
                        </tr>
                     </table>
                  </td>
               </tr>

               {{-- ── AMOUNT IN WORDS ── --}}
               <tr>
                  <td colspan="8" style="border-top:0">
                     <strong>
                        <?php
                        $number = $sale_detail->total;
                        $no = floor($number);
                        $point = round($number - $no, 2) * 100;
                        $hundred = null;
                        $digits_1 = strlen($no);
                        $i = 0;
                        $str = [];
                        $words = ['0'=>'','1'=>'one','2'=>'two','3'=>'three','4'=>'four','5'=>'five','6'=>'six','7'=>'seven','8'=>'eight','9'=>'nine','10'=>'ten','11'=>'eleven','12'=>'twelve','13'=>'thirteen','14'=>'fourteen','15'=>'fifteen','16'=>'sixteen','17'=>'seventeen','18'=>'eighteen','19'=>'nineteen','20'=>'twenty','30'=>'thirty','40'=>'forty','50'=>'fifty','60'=>'sixty','70'=>'seventy','80'=>'eighty','90'=>'ninety'];
                        $digits = ['','hundred','thousand','lakh','crore'];
                        while($i < $digits_1){
                           $divider = ($i == 2) ? 10 : 100;
                           $number  = floor($no % $divider);
                           $no      = floor($no / $divider);
                           $i += ($divider == 10) ? 1 : 2;
                           if($number){
                              $plural  = (($counter = count($str)) && $number > 9) ? 's' : null;
                              $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                              $str[]   = ($number < 21) ? $words[$number]." ".$digits[$counter].$plural." ".$hundred : $words[floor($number/10)*10]." ".$words[$number%10]." ".$digits[$counter].$plural." ".$hundred;
                           } else { $str[] = null; }
                        }
                        $str    = array_reverse($str);
                        $result = implode('', $str);
                        echo ucfirst($result)."Rupees  only";
                        ?>
                     </strong>
                  </td>
               </tr>

               {{-- ── BANK DETAILS (respects configuration) ── --}}
               @if($configuration && $configuration->bank_detail_status == 1 && $bank_detail)
                  <tr>
                     <td colspan="8">
                        @if($configuration && $configuration->banks)
                           <p>
                              <strong>Bank Details : </strong> <strong>ACCOUNT NAME-</strong>{{ $configuration->banks->name }} <br>
                              <strong>ACCOUNT NO:</strong>{{ $configuration->banks->account_no }} ,<strong>IFSC CODE:</strong>{{ $configuration->banks->ifsc }} ,<strong>BANK NAME:</strong>{{ $configuration->banks->bank_name }},{{ $configuration->banks->branch }}
                           </p>
                        @endif
                     </td>
                  </tr>
               @endif

               {{-- ── TERMS & SIGNATURE (respects configuration) ── --}}
               <tr>
                  <td colspan="4" style="vertical-align:top;padding:5px;">
                     @if($configuration && $configuration->term_status == 1 && $configuration->terms && count($configuration->terms) > 0)
                        <p style="margin:0;"><small><b>Terms &amp; Conditions</b></small></p>
                        <p style="margin:0;"><small>E.&amp; O.E.</small></p>
                        @php $ti = 1; @endphp
                        @foreach($configuration->terms as $t)
                           <p style="margin:0;line-height:1;"><small>{{ $ti }}. {{ $t->term }}</small></p>
                           @php $ti++; @endphp
                        @endforeach
                     @endif
                  </td>
                  <td colspan="4">
                     <p style="height:40px;margin:0;padding:0;"><small>Receiver's Signature :</small></p>
                     <p style="text-align:right;padding:0;margin:0;"><strong>for {{ $company_data->company_name }}</strong></p>
                     @if($configuration && $configuration->signature_status == 1 && !empty($configuration->signature))
                        <p style="text-align:right;margin:0;padding:0;">
                           <img src="{{ URL::asset('public/images') }}/{{ $configuration->signature }}" style="max-width:145px;max-height:120px;object-fit:contain;">
                        </p>
                     @else
                        <p style="text-align:right;margin:0;padding:0;width:145px;height:70px;"></p>
                     @endif
                     <p style="text-align:right;margin:0;padding:0;"><strong>Authorised Signatory</strong></p>
                  </td>
               </tr>

            </tbody>
         </table>
         <span class="append_invoice"></span>
         <br>
      </div>
   </div>
</section>
</div>
</div>

<div class="print-layout">
@php $printSerial = 1; @endphp
@foreach($printPages as $printPageIndex => $printPage)
   @php
      $printIsFirstPage    = ($printPageIndex === 0);
      $printShowFinalBlock = !empty($printPage['show_final_block']);
      $printHasItems       = count($printPage['items']) > 0;
      $printShowBfRow      = (!$printIsFirstPage);

      $usedPx = $px_header_actual;
      if($printShowBfRow)      $usedPx += $PX_BF_ROW;
      foreach($printPage['items'] as $pii) $usedPx += $fnItemPx($pii);
      if($printShowFinalBlock) $usedPx += $PX_FINANCIAL;
      else                     $usedPx += $PX_CF_ROW;
      $usedPx += $PX_FOOTER;

      $printSpacerPx = max(0, $PX_PAGE - $usedPx);
   @endphp

   <div class="print-wrapper{{ ($printShowFinalBlock && !$printHasItems) ? ' print-summary-page' : '' }}">
   <table style="font-family:'Source Sans Pro',sans-serif;letter-spacing:0.05em;color:#404040;font-size:12px;font-weight:500;padding:10px;width:100%;height:100%;border-collapse:collapse;" class="invoice-copy">
      <tbody>

         {{-- HEADER --}}
         <tr>
            <th colspan="8" style="padding:0;">
               <div class="invoice-company-header">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
                     <div style="flex:1;text-align:left;margin-left:5px;">
                        <strong style="color:{{ $configuration->address_color ?? 'black' }};">GSTIN: {{ $seller_info->gst_no }}</strong>
                     </div>
                     <div style="flex:1;text-align:center;">
                        @if($configuration && !empty($configuration->invoice_header_text))
                           <strong style="font-size:13px;font-weight:700;letter-spacing:1px;color:{{ $configuration->address_color ?? 'black' }};">{{ $configuration->invoice_header_text }}</strong>
                        @endif
                     </div>
                     <div style="flex:1;text-align:right;margin-top:7px;margin-right:5px;">
                        <strong style="color:{{ $configuration->address_color ?? 'black' }};">PAN: {{ substr($seller_info->gst_no, 2, 10) }}</strong><br>
                        <small style="color:{{ $configuration->address_color ?? 'black' }};">O/D/T</small>
                     </div>
                  </div>
                  @php
                     $printCompanyName = $company_data->company_name;
                     $printFontSize    = strlen($printCompanyName) > 30 ? '18px' : '24px';
                     if($configuration && $configuration->company_name_font_size != ""){ $printFontSize = $configuration->company_name_font_size; }
                  @endphp
                  @if($configuration && $configuration->company_logo_status==1 && $configuration->logo_position_left==1 && !empty($configuration->company_logo))
                     <div class="invoice-logo-left"><img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"></div>
                  @endif
                  @if($configuration && $configuration->company_logo_status==1 && $configuration->logo_position_right==1 && !empty($configuration->company_logo))
                     <div class="invoice-logo-right"><img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"></div>
                  @endif
                  <div style="clear:both;"></div>
                  <div style="text-align:center;line-height:1;margin:0;padding:0;">
                     <p style="margin:0;color:{{ $configuration->address_color ?? 'black' }};"><u>TAX INVOICE</u></p>
                     <p style="margin:0;font-size:{{ $printFontSize }};font-weight:bold;color:{{ $configuration->company_name_color ?? 'black' }};">{{ $printCompanyName }}</p>
                     <p style="margin:0;"><small style="font-size:12px;display:inline-block;max-width:50%;word-break:break-word;color:{{ $configuration->address_color ?? 'black' }};">{{ $seller_info->address }}</small></p>
                     <p style="margin:0;"><small style="font-size:12px;color:{{ $configuration->address_color ?? 'black' }};">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small></p>
                  </div>
               </div>
            </th>
         </tr>

         @if($showIrn)

            @php
               $printEinvoiceData = json_decode($sale_detail->einvoice_response);
            @endphp

            @if($printEinvoiceData && isset($printEinvoiceData->SignedQRCode))
            <tr>
               <td colspan="8">
                     <span style="float:right;width:90px;height:90px;position:relative;">
                        {!! QrCode::size(90)->generate($printEinvoiceData->SignedQRCode) !!}
                     </span>

                     <p>IRN NO. : {{ $printEinvoiceData->Irn }}</p>
                     <p>ACK.NO. : {{ $printEinvoiceData->AckNo }}</p>
                     <p>ACK.DATE : {{ $printEinvoiceData->AckDt }}</p>
               </td>
            </tr>
            @endif

         @endif

         {{-- META --}}
         <tr>
            <td colspan="4">
               <p><span class="width25">Invoice No. </span>: <span class="lft_mar15" style="font-weight:800">{{ $sale_detail->voucher_no_prefix }}</span></p>
               <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{ date('d-m-Y', strtotime($sale_detail->date)) }}</span></p>
               <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">{{ $sale_detail->sname }}</span></p>
               <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15">{{ $sale_detail->reverse_charge }}</span></p>
               <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{ $sale_detail->gr_pr_no }}</span></p>
            </td>
            <td colspan="4">
               <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text">{{ $sale_detail->transport_name }}</span></p>
               <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{ $sale_detail->vehicle_no }}</span></p>
               <p><span class="width25">Station </span>: <span class="lft_mar15">{{ $sale_detail->station }}</span></p>
               <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15"></span></p>
               <p>&nbsp;</p>
               @if($configuration && $configuration->purchase_order_status == 1)
                  <p><span class="width25">PO No. </span>: <span class="lft_mar15">{{ $sale_detail->po_no }}</span></p>
                  <p><span class="width25">PO Date </span>: <span class="lft_mar15">{{ $sale_detail->po_date ? date('d-m-Y', strtotime($sale_detail->po_date)) : '' }}</span></p>
               @endif
            </td>
         </tr>

         {{-- BILLED / SHIPPED --}}
         <tr>
            <td colspan="4" style="position:relative;vertical-align:top;padding:0;">
               <p style="margin:0;position:absolute;top:0;left:5px;font-style:italic;"><strong>Billed to :</strong></p>
               <div style="padding-top:16px;margin-left:10px;margin-right:5px;padding-bottom:30px;max-height:80px;overflow:hidden;">
                  <p style="margin:2px 0 0 0;line-height:13px;font-weight:800;">{{ $sale_detail->billing_name }}</p>
                  <p style="margin:2px 0 0 0;line-height:13px;">{{ $sale_detail->billing_address }}</p>
               </div>
               <div style="position:absolute;bottom:0;left:5px;right:4px;">
                  <p style="margin:2px 0 0 0;font-weight:800">GSTIN/UIN : {{ $sale_detail->billing_gst }} <span style="float:right;">PAN:{{ $sale_detail->billing_pan }}</span></p>
               </div>
            </td>
            <td colspan="4" style="position:relative;vertical-align:top;padding:0;height:120px;">
               <p style="margin:0;position:absolute;top:0;left:5px;font-style:italic;"><strong>Shipped to :</strong></p>
               <div style="padding-top:16px;margin-left:10px;margin-right:5px;padding-bottom:30px;max-height:80px;overflow:hidden;">
                  <p style="margin:2px 0 0 0;line-height:13px;font-weight:800;">
                     @if($sale_detail->shipping_name) {{ $sale_detail->shipp_name }} @else {{ $sale_detail->billing_name }} @endif
                  </p>
                  <p style="margin:2px 0 0 0;line-height:13px;">
                     @if($sale_detail->shipping_name) {{ $sale_detail->shipping_address }} @else {{ $sale_detail->billing_address }} @endif
                  </p>
               </div>
               <div style="position:absolute;bottom:0;left:5px;right:4px;">
                  <p style="margin:2px 0 0 0;font-weight:800">
                     @if($sale_detail->shipping_name)
                        GSTIN/UIN : {{ $sale_detail->shipping_gst }} <span style="float:right;">PAN:{{ $sale_detail->shipping_pan }}</span>
                     @else
                        GSTIN/UIN : {{ $sale_detail->billing_gst }} <span style="float:right;">PAN:{{ $sale_detail->billing_pan }}</span>
                     @endif
                  </p>
               </div>
            </td>
         </tr>

         {{-- COLUMN HEADERS --}}
         <tr>
            <th style="width:2%;padding:0px 3px;">S. No.</th>
            <th colspan="2" style="text-align:left;width:30%;">Description of Goods</th>
            <th style="text-align:center;width:3%;">HSN/SAC Code</th>
            <th style="text-align:right;width:11%;">Qty.</th>
            <th style="text-align:center;width:2%;">Unit</th>
            <th style="text-align:right;width:12%;">Price</th>
            <th style="text-align:right;width:15%;">Amount (₹)</th>
         </tr>

         {{-- B/F ROW --}}
         @if($printShowBfRow)
            <tr class="print-compact-row" style="font-weight:700;">
               <td colspan="4" style="text-align:right;padding:1px 5px;">B/F (Brought Forward)</td>
               <td style="text-align:right;padding:1px 5px;">{{ $printPage['bf_qty'] }}</td>
               <td></td><td></td>
               <td style="text-align:right;padding:1px 5px;">{{ formatIndianNumber($printPage['bf_amount']) }}</td>
            </tr>
         @endif

         {{-- ITEM ROWS --}}
         @foreach($printPage['items'] as $printItem)
            <tr class="print-item-row {{ ($configuration && $configuration->lines_in_item_status == 0) ? 'no-border' : '' }}">
               <td style="text-align:center;">{{ $printSerial }}</td>
               <td colspan="2" style="text-align:left;">
                  <strong>{{ $printItem->p_name }}</strong>
                  @if($configuration && $configuration->show_item_name == 1)
                     <span style="font-size:9px;color:#777;margin-left:4px;">({{ $printItem->name }})</span>
                  @endif
                  @if(isset($printItem->lines) && count($printItem->lines) > 0)
                     @foreach($printItem->lines as $pl)
                        <small style="display:block;font-size:10px;font-style:italic;color:#555;margin-left:10px;">{{ $pl->line_text }}</small>
                     @endforeach
                  @endif
               </td>
               <td style="text-align:center;">{{ $printItem->hsn_code }}</td>
               <td style="text-align:right">{{ $printItem->qty }}</td>
               <td style="text-align:center">{{ $printItem->unit }}</td>
               <td style="text-align:right;">{{ $printItem->price }}</td>
               <td style="text-align:right;white-space:nowrap;">{{ formatIndianNumber($printItem->amount) }}</td>
            </tr>
            @php $printSerial++; @endphp
         @endforeach

         {{-- SPACER (non-final pages) --}}
         @if(!$printShowFinalBlock)
            <tr class="print-content-spacer">
               <td style="height:{{ $printSpacerPx }}px;padding:0;">&nbsp;</td>
               <td colspan="2" style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td><td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td><td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
            </tr>
         @endif

         {{-- C/F ROW --}}
         @if(!$printShowFinalBlock)
            <tr class="print-compact-row" style="font-weight:700;">
               <td colspan="4" style="text-align:right;padding:1px 5px;">Carry Forward</td>
               <td style="text-align:right;padding:1px 5px;">{{ $printPage['cf_qty'] }}</td>
               <td></td><td></td>
               <td style="text-align:right;padding:1px 5px;">{{ formatIndianNumber($printPage['cf_amount']) }}</td>
            </tr>
         @endif

      </tbody>

      @if($printShowFinalBlock)
      <tbody>

      @if(($configuration->lines_in_item_status ?? 1) == 0)

      <tr class="print-content-spacer">
         <td style="height:{{ $printSpacerPx }}px;padding:0;">&nbsp;</td>
         <td colspan="2" style="padding:0;">&nbsp;</td>
         <td style="padding:0;">&nbsp;</td>
         <td style="padding:0;">&nbsp;</td>
         <td style="padding:0;">&nbsp;</td>
         <td style="padding:0;">&nbsp;</td>
         <td style="padding:0;">&nbsp;</td>
      </tr>

      @else

      <tr>
         <td style="height:{{ $printSpacerPx }}px;border-top:none;border-bottom:none;"></td>
         <td colspan="2" style="border-top:none;border-bottom:none;"></td>
         <td style="border-top:none;border-bottom:none;"></td>
         <td style="border-top:none;border-bottom:none;"></td>
         <td style="border-top:none;border-bottom:none;"></td>
         <td style="border-top:none;border-bottom:none;"></td>
         <td style="border-top:none;border-bottom:none;"></td>
      </tr>

      @endif

      </tbody>
      @endif

      {{-- FINAL FINANCIAL BLOCK --}}
      @if($printShowFinalBlock)
      <tbody class="print-financial-block print-indivisible-block">

         {{-- TOTAL ROW --}}
         <tr class="print-compact-row">
            <td colspan="4" style="border-bottom:0;border-right:0;padding:1px 5px;"></td>
            <td style="border-bottom:0;border-left:0;border-right:0;text-align:right;padding:1px 5px;"><strong>{{ $printPage['cf_qty'] }}</strong></td>
            <td style="border-bottom:0;border-left:0;border-right:0;padding:1px 5px;"><strong></strong></td>
            <td style="border-bottom:0;border-left:0;padding:1px 5px;"><strong>Total</strong></td>
            <td style="text-align:right;border-bottom:0;padding:1px 5px;">{{ formatIndianNumber($printPage['cf_amount']) }}</td>
         </tr>

         {{-- SUNDRY --}}
         <tr class="print-compact-row">
            <td style="border-right:0;border-top:0;" colspan="2"></td>
            <td colspan="4" style="border-left:0;border-right:0;border-top:0;">
               @foreach($displaySundries_pre as $ds)
                  @if(stripos($ds->name,'round') === false)
                     <p>{{ $ds->bill_sundry_type == 'additive' ? 'Add' : 'Less' }} : {{ $ds->name }}</p>
                  @endif
               @endforeach
               @if($totalCGST_pre > 0)<p>Add : CGST</p>@endif
               @if($totalSGST_pre > 0)<p>Add : SGST</p>@endif
               @if($totalIGST_pre > 0)<p>Add : IGST</p>@endif
               @foreach($displaySundries_pre as $ds)
                  @if(stripos($ds->name,'round') !== false)
                     <p>{{ $ds->bill_sundry_type == 'additive' ? 'Add' : 'Less' }} : {{ $ds->name }}</p>
                  @endif
               @endforeach
            </td>
            <td style="border-left:0;border-top:0;">
               @foreach($displaySundries_pre as $ds)
                  @if(stripos($ds->name,'round') === false)<p>&nbsp;</p>@endif
               @endforeach
               @if($totalCGST_pre > 0)<p>&nbsp;</p>@endif
               @if($totalSGST_pre > 0)<p>&nbsp;</p>@endif
               @if($totalIGST_pre > 0)<p>&nbsp;</p>@endif
               @foreach($displaySundries_pre as $ds)
                  @if(stripos($ds->name,'round') !== false)<p>&nbsp;</p>@endif
               @endforeach
            </td>
            <td style="text-align:right;border-top:0;">
               @foreach($displaySundries_pre as $ds)
                  @if(stripos($ds->name,'round') === false)<p>{{ formatIndianNumber($ds->amount) }}</p>@endif
               @endforeach
               @if($totalCGST_pre > 0)<p>{{ formatIndianNumber($totalCGST_pre) }}</p>@endif
               @if($totalSGST_pre > 0)<p>{{ formatIndianNumber($totalSGST_pre) }}</p>@endif
               @if($totalIGST_pre > 0)<p>{{ formatIndianNumber($totalIGST_pre) }}</p>@endif
               @foreach($displaySundries_pre as $ds)
                  @if(stripos($ds->name,'round') !== false)<p>{{ formatIndianNumber($ds->amount) }}</p>@endif
               @endforeach
            </td>
         </tr>

         {{-- GRAND TOTAL --}}
         <tr class="print-compact-row">
            <td colspan="7" style="text-align:right;border-right:0;border-bottom:0;padding:1px 5px;">
               <p style="margin:0;"><strong>Grand Total ₹</strong></p>
            </td>
            <td style="text-align:right;padding:1px 5px;white-space:nowrap;">
               <p style="margin:0;"><strong class="invoice-total">{{ formatIndianNumber($sale_detail->total) }}</strong></p>
            </td>
         </tr>

         {{-- GST SUMMARY --}}
         <tr class="print-compact-row">
            <td colspan="8" style="border-top:0;border-bottom:0;padding:1px 4px;">
               <table class="gst-summary-table" style="width:45% !important;border:none;border-collapse:collapse;font-size:10px;display:inline-table;">
                  <tr>
                     <td style="border:none;padding:1px;font-weight:bold;">Tax Rate</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">Taxable Amt.</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">CGST</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">SGST</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">Total Tax</td>
                  </tr>
                  @php $totalTaxablePr = 0; $totalCGSTPr = 0; $totalSGSTPr = 0; @endphp
                  @foreach($gst_detail as $gv)
                     @php $totalTaxablePr += $gv->taxable_amount; $totalCGSTPr += $gv->amount; $totalSGSTPr += $gv->amount; @endphp
                     <tr>
                        <td style="border:none;padding:1px;">{{ $gv->rate }}%</td>
                        <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->taxable_amount) }}</td>
                        <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->amount) }}</td>
                        <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->amount) }}</td>
                        <td style="border:none;padding:1px;text-align:right;">{{ formatIndianNumber($gv->amount * 2) }}</td>
                     </tr>
                  @endforeach
                  <tr><td colspan="5" style="padding:0;border:none;"><hr style="margin:2px 0;border:none;border-top:1px solid #000;"></td></tr>
                  <tr>
                     <td style="border:none;padding:1px;font-weight:bold;">Total</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalTaxablePr) }}</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalCGSTPr) }}</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalSGSTPr) }}</td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">{{ formatIndianNumber($totalCGSTPr + $totalSGSTPr) }}</td>
                  </tr>
               </table>
            </td>
         </tr>

         {{-- AMOUNT IN WORDS --}}
         <tr class="print-compact-row">
            <td colspan="8" style="border-top:0;padding:1px 5px;">
               <strong>
                  <?php
                  $pNum = $sale_detail->total;
                  $pNo  = floor($pNum);
                  $pPt  = round($pNum - $pNo, 2) * 100;
                  $pH   = null; $pD1 = strlen($pNo); $pI = 0; $pStr = [];
                  $pW   = ['0'=>'','1'=>'one','2'=>'two','3'=>'three','4'=>'four','5'=>'five','6'=>'six','7'=>'seven','8'=>'eight','9'=>'nine','10'=>'ten','11'=>'eleven','12'=>'twelve','13'=>'thirteen','14'=>'fourteen','15'=>'fifteen','16'=>'sixteen','17'=>'seventeen','18'=>'eighteen','19'=>'nineteen','20'=>'twenty','30'=>'thirty','40'=>'forty','50'=>'fifty','60'=>'sixty','70'=>'seventy','80'=>'eighty','90'=>'ninety'];
                  $pDig = ['','hundred','thousand','lakh','crore'];
                  while($pI < $pD1){
                     $pDiv = ($pI==2)?10:100; $pNum=floor($pNo%$pDiv); $pNo=floor($pNo/$pDiv); $pI+=($pDiv==10)?1:2;
                     if($pNum){ $pPl=(($pCnt=count($pStr))&&$pNum>9)?'s':null; $pH=($pCnt==1&&$pStr[0])?' and ':null; $pStr[]=($pNum<21)?$pW[$pNum].' '.$pDig[$pCnt].$pPl.' '.$pH:$pW[floor($pNum/10)*10].' '.$pW[$pNum%10].' '.$pDig[$pCnt].$pPl.' '.$pH; }
                     else { $pStr[]=null; }
                  }
                  $pStr=array_reverse($pStr); echo ucfirst(implode('',$pStr)).'Rupees  only';
                  ?>
               </strong>
            </td>
         </tr>

         {{-- BANK DETAILS --}}
         @if($configuration && $configuration->bank_detail_status == 1 && $bank_detail)
            <tr class="print-compact-row">
               <td colspan="8" style="padding:1px 5px;">
                  @if($configuration && $configuration->banks)
                     <p>
                        <strong>Bank Details : </strong> <strong>ACCOUNT NAME-</strong>{{ $configuration->banks->name }} <br>
                        <strong>ACCOUNT NO:</strong>{{ $configuration->banks->account_no }} ,<strong>IFSC CODE:</strong>{{ $configuration->banks->ifsc }} ,<strong>BANK NAME:</strong>{{ $configuration->banks->bank_name }},{{ $configuration->banks->branch }}
                     </p>
                  @endif
               </td>
            </tr>
         @endif

      </tbody>
      @endif

      {{-- PAGE FOOTER (terms + signature) --}}
      <tbody class="print-page-footer">
         <tr>
            <td colspan="4" style="vertical-align:top;padding:2px 5px;height:{{ $PX_FOOTER }}px;min-height:{{ $PX_FOOTER }}px;">
               @if($configuration && $configuration->term_status==1 && $configuration->terms && count($configuration->terms)>0)
                  <p style="margin:0;line-height:1.1;"><small><b>Terms &amp; Conditions</b></small></p>
                  <p style="margin:0;line-height:1.1;"><small>E.&amp; O.E.</small></p>
                  @php $ptn = 1; @endphp
                  @foreach($configuration->terms as $pt)
                     <p style="margin:0;line-height:1.1;font-size:9px;">{{ $ptn }}. {{ $pt->term }}</p>
                     @php $ptn++; @endphp
                  @endforeach
                  <p style="margin:0;line-height:1.1;font-size:9px;">&nbsp;</p>
               @endif
            </td>
            <td colspan="4" style="vertical-align:top;padding:2px 5px;height:{{ $PX_FOOTER }}px;min-height:{{ $PX_FOOTER }}px;position:relative;">
               <div style="height:{{ $PX_FOOTER - 10 }}px;position:relative;">
                  <p style="margin:0;line-height:1.1;"><small>Receiver's Signature :</small></p>
                  <div style="height:20px;"></div>
                  <p style="text-align:right;margin:0;font-weight:bold;">for {{ $company_data->company_name }}</p>
                  @if($configuration && $configuration->signature_status == 1 && !empty($configuration->signature))
                     <div style="position:absolute;top:50%;right:2px;width:170px;text-align:right;height:150px;margin-left:-75px;margin-top:-75px;text-align:center;z-index:10;">
                        <img src="{{ URL::asset('public/images') }}/{{ $configuration->signature }}" style="width:150px;height:150px;object-fit:contain;">
                     </div>
                  @endif
                  <p style="position:absolute;right:0;bottom:0;margin:0;font-weight:bold;">Authorised Signatory</p>
               </div>
            </td>
         </tr>
      </tbody>

   </table>
   </div>
@endforeach
</div>

@include('layouts.footer')
<script>
   let configuration = @json($configuration->no_of_bill_copy ?? 1);
   if(!configuration){ configuration = 1; }

   function printpage(){
      const billCopies = Math.max(1, parseInt(configuration, 10) || 1);
      const $printLayout = $('.print-layout');
      if(!$printLayout.length){ window.print(); return; }
      const $originalWrappers = $printLayout.children('.print-wrapper').detach();
      for(let copy = 0; copy < billCopies; copy++){
         $originalWrappers.each(function(){ $printLayout.append($(this).clone()); });
      }
      setTimeout(function(){
         window.print();
         setTimeout(function(){
            $printLayout.empty();
            $printLayout.append($originalWrappers);
         }, 500);
      }, 200);
   }
</script>
@endsection