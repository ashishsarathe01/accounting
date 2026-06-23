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

    

    /*.container-fluid,*/
    /*.row,*/
    /*.col-md-12,*/
    /*.col-lg-10 {*/
    /*    width: 100% !important;*/
    /*    margin: 0 !important;*/
    /*    padding: 0 !important;*/
    /*}*/

    table {
        width: 100% !important;
    }

    .header-section {
        display: none !important; /* hide buttons only */
    }
    .sidebar {
        display: none !important; /* hide buttons only */
    }
}
@page { size: auto;  margin: 0mm; }

.importantRule { 
   display: none !important;  /* Force hide anything with this class */
}

p {
   margin: 0.5px !important;  /* Almost zero vertical space between paragraphs */
}
.invoice-total{
    font-size:16px;
    font-weight:800;
    margin:0;
    white-space:nowrap;
}

/* @page {
    size: A4;
    margin: 5mm;
}

.invoice-box {
    width: 205mm;
    min-height: 293mm;
    padding: 5mm;
    margin: auto;
    border: 1px solid #eee;
    box-shadow: 0 0 5px rgba(0,0,0,0.1);
    box-sizing: border-box;
    background: white;
}

body {
    background: #ccc; /* optional gray background for contrast */
    /* display: flex;
    justify-content: center; */
   /* } */
 
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
      min-height: 1084px;   /* was height */
      overflow: visible;    /* was hidden */
      position: relative;
   }

   .print-wrapper:last-child {
      page-break-after: auto;
      break-after: auto;
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
      top:50px;          /* was 35px */
      width:90px;
      height:70px;       /* was 80px */
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
</style>

<div class="list-of-view-company ">
   <div class="screen-layout">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center header-section">
               <div class="d-md-flex d-block noprint">
                  <div class="calender-administrator my-2 my-md-0  w-min-230 noprint">
                      <button type="button" class="btn btn-danger" onclick="window.location='{{ url()->previous() }}'">QUIT</button>
                     <button class="btn btn-info" onclick="printpage();">Print Bill</button>
                     <button id="printChallanBtn" class="btn btn-primary">Print Challan</button>
                        <a href="{{ URL::to('sale-invoice/pdf/'.$sale_detail->id) }}"><button class="btn btn-primary">PDF(Bill @if($production_module_status==1)+ Challan @endif)</button>
                     </a>
                            <form action="{{ route('sale.emailInvoice', $sale_detail->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    Email Invoice
                                </button>
                            </form> 
                     @if($sale_detail->e_waybill_status == 1)
                        <a href="{{ URL::to('sale-invoice/ewaybill/'.$sale_detail->id) }}"><button class="btn btn-primary">Print E-Way Bill</button>
                        </a>
                     @endif
                     <?php 
                     //&& is_null($sale_detail->sale_order_id)
                    if ( in_array(date('Y-m', strtotime($sale_detail->date)), $month_arr) && $sale_detail->e_invoice_status == 0 && $sale_detail->e_waybill_status == 0) {?>
                        <a href="{{ URL::to('edit-sale/'.$sale_detail->id) }}" class="btn btn-primary text-white">
                           <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg') }}" alt="Edit" style="width: 16px; height: 16px; vertical-align: middle; filter: brightness(0) invert(1);">
                           Edit
                        </a>
                        <?php } ?>
                     <a href="{{ route('sale.create') }}"><button class="btn btn-primary">Add Sale</button></a>
                    @if($source == 'approve' && $sale_detail->approved_status != 1)
                        <button class="btn btn-success" id="approveSale">
                            Approve
                        </button>
                    @endif
                  </div>
               </div>            
            </div>  
            <br>          
            
            <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;" class="invoice-copy invoice-copy-screen">
               <tbody>
                  <tr>
                    <th colspan="8" style="padding: 0;">
                        <div class="invoice-company-header">
                           <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                              <!-- LEFT -->
                              <div style="flex:1; text-align:left;margin-left: 5px;">
                                 <strong style="color: {{ $configuration->address_color ?? 'black' }};">
                                       GSTIN: {{ $seller_info->gst_no }}
                                 </strong>
                              </div>

                              <!-- CENTER -->
                              <div style="flex:1; text-align:center;">
                                 @if($configuration && !empty($configuration->invoice_header_text))
                                       <strong style="font-size:13px; font-weight:700; letter-spacing:1px; color: {{ $configuration->address_color ?? 'black' }};">
                                          {{ $configuration->invoice_header_text }}
                                       </strong>
                                 @endif
                              </div>

                              <!-- RIGHT -->
                              <div style="flex:1; text-align:right;margin-top: 7px;margin-right: 5px;">
                                 <strong style="color: {{ $configuration->address_color ?? 'black' }};">
                                    PAN: {{ substr($seller_info->gst_no, 2, 10) }}
                                 </strong><br>
                                 <small style="color: {{ $configuration->address_color ?? 'black' }};">
                                    O/D/T
                                 </small>
                              </div>
                              
                           </div>
                           
                            @php
                                $companyName = $company_data->company_name;
                                $fontSize = strlen($companyName) > 30 ? '18px' : '24px';
                                if($configuration && $configuration->company_name_font_size!=""){
                                   $fontSize = $configuration->company_name_font_size;
                                }
                            @endphp

                            {{-- LEFT LOGO --}}
                            @if($configuration && $configuration->company_logo_status==1 
                               && $configuration->logo_position_left==1 
                               && !empty($configuration->company_logo))
                                 <div class="invoice-logo-left">
                                    <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                                 </div>

                            @endif

                            {{-- RIGHT LOGO --}}
                            @if($configuration && $configuration->company_logo_status==1 
                               && $configuration->logo_position_right==1 
                               && !empty($configuration->company_logo))
                                 <div class="invoice-logo-right">
                                    <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                                 </div>
                            @endif

                           
                            <div style="clear:both;"></div>
                
                            <div style="text-align:center; line-height:1; margin:0; padding:0;">
                                <p style="margin:0;color: {{ $configuration->address_color ?? 'black' }};"><u>TAX INVOICE</u></p>
                                <p style="margin:0; font-size: {{ $fontSize }}; font-weight: bold; color: {{ $configuration->company_name_color ?? 'black' }};">
                                    {{ $companyName }}
                                </p>
                                <p style="margin:0;">
                                    <small style="font-size: 12px; display:inline-block; max-width:50%; word-break:break-word;color: {{ $configuration->address_color ?? 'black' }};">
                                        {{ $seller_info->address }}
                                    </small>
                                </p>
                                <p style="margin:0;">
                                    <small style="font-size: 12px; color: {{ $configuration->address_color ?? 'black' }};">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small>
                                </p>
                            </div>
                        </div>
                    </th>
                </tr>
              @if($sale_detail->e_invoice_status==1 && !empty($sale_detail->einvoice_response))
                  <?php 
                     $einvoice_data = json_decode($sale_detail->einvoice_response);
                     //$data = $einvoice_data->SignedQRCode;
                     $qrContent = $einvoice_data->SignedQRCode;
                     ?>
                     <tr>
                        <td colspan="8">    
                        <span style="float: right;width: 90px;height: 90px;position: relative;">
                            {!! QrCode::size(90)->generate($qrContent) !!}
                           </span> 
                           <!-- <img src="{{ URL::asset('public/images')}}/qrcode.png" style="float: right;width: 90px;height: 90px;position: relative;"> -->
                           <p>IRN NO. : <?php echo $einvoice_data->Irn;?></p>
                           <p>ACK.NO. : <?php echo $einvoice_data->AckNo;?></p>
                           <p>ACK.DATE : <?php echo $einvoice_data->AckDt;?></p>
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
                           <!-- <p>&nbsp;</p> -->
                        </td>
                        <td colspan="4">
                           <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text">{{$sale_detail->transport_name}}</span> </p>
                           <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{$sale_detail->vehicle_no}}</span> </p>
                           <p><span class="width25">Station </span>: <span class="lft_mar15">{{$sale_detail->station}}</span> </p>
                           <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15">
                              <?php
                              if($sale_detail->e_waybill_status==1 && $sale_detail->eway_bill_response && !empty($sale_detail->eway_bill_response)){
                                 $ewaybill_data = json_decode($sale_detail->eway_bill_response);
                                 echo $ewaybill_no = $ewaybill_data->ewayBillNo;
                              }?>
                           </span> </p>
                           <p>&nbsp;</p>
                           @if($configuration && $configuration->purchase_order_status == 1)
                           <p>
                              <span class="width25">PO No. </span>: 
                              <span class="lft_mar15">{{ $sale_detail->po_no }}</span>
                           </p>

                           <p>
                              <span class="width25">PO Date </span>: 
                              <span class="lft_mar15">
                                 {{ $sale_detail->po_date ? date('d-m-Y', strtotime($sale_detail->po_date)) : '' }}
                              </span>
                           </p>
                           @endif
                          
                           <!-- <p>&nbsp;</p> -->
                        </td>
                     </tr>
                     <tr>
                        <td colspan="4" style="position: relative; vertical-align: top; padding: 0;">

                        <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                           <strong>Billed to :</strong>
                        </p>

                        <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">
                              {{$sale_detail->billing_name}}
                           </p>
                           <p style="margin: 2px 0 0 0; line-height: 13px;">
                              {{$sale_detail->billing_address}}
                           </p>
                        </div>

                        <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                           <p style="margin: 2px 0 0 0;font-weight:800">
                              GSTIN/UIN : {{$sale_detail->billing_gst}} 
                              <span style="float: right;">PAN:{{$sale_detail->billing_pan}}</span>
                           </p>
                        </div>
                    </td>

                    <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">

                    <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                       <strong>Shipped to :</strong>
                    </p>
                    
                    <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                       <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">
                          @if($sale_detail->shipping_name)
                             {{$sale_detail->shipp_name}}
                          @else
                             {{$sale_detail->billing_name}}
                          @endif
                       </p>
                       <p style="margin: 2px 0 0 0; line-height: 13px;">
                          @if($sale_detail->shipping_name)
                             {{$sale_detail->shipping_address}}
                          @else
                             {{$sale_detail->billing_address}}
                          @endif
                       </p>
                    </div>

                     <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                        <p style="margin: 2px 0 0 0;font-weight:800">
                           @if($sale_detail->shipping_name)
                              GSTIN/UIN : {{$sale_detail->shipping_gst}} 
                              <span style="float: right;">PAN:{{$sale_detail->shipping_pan}}</span>
                           @else
                              GSTIN/UIN : {{$sale_detail->billing_gst}} 
                              <span style="float: right;">PAN:{{$sale_detail->billing_pan}}</span>
                           @endif
                        </p>
                     </div>

                     </td>

                     </tr>
                     <tr>
                        <th style="width:2%;padding: 0px 3px;">S.   No.</th>
                        <th colspan="2" style="text-align:left; width:30%;">Description of Goods</th>
                        <th style="text-align:center; width:3%;">HSN/SAC Code</th> <!-- Centered SAC Code --> 
                        <th style="text-align:right; width:11%;">Qty.</th>
                        <th style="text-align:center; width:2%;">Unit</th>
                        <th style="text-align:right; width:12%;">Price</th>
                        <th style="text-align:right; width:15%;">Amount (₹)</th>
                     </tr>
                     @php $i=1;$displayLineCount = 0;$item_total = 0;$qty_total = 0; @endphp
                     @foreach($items_detail as $item)
                        <tr class="{{ ($configuration && $configuration->lines_in_item_status == 0) ? 'no-border' : '' }}">
                           <td style="text-align:center;">{{$i}}</td>
                           <td colspan="2" style="text-align:left;">
                                <strong>{{ $item->p_name }}</strong>
                                @if($configuration && $configuration->show_item_name == 1)
                                <span style="font-size:9px; color:#777; margin-left:4px;">
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


                           <td style="text-align:center;">{{$item->hsn_code}}</td>
                           <td style="text-align:right">{{$item->qty}}</td>
                           <td style="text-align:center">{{$item->unit}}</td>
                           <td style="text-align:right;">{{$item->price}}</td>
                           <td style="text-align:right; white-space:nowrap;">
                              {{formatIndianNumber($item->amount)}}
                           </td>
                        </tr>
                        @php
                           $i++;
                           $displayLineCount++;
                           if(isset($item->lines) && count($item->lines) > 0){
                              $displayLineCount += count($item->lines);
                           }
                           $item_total += $item->amount;
                           $qty_total += $item->qty;
                        @endphp
                     @endforeach
                     @php
                         foreach($sale_sundry as $sundry){
                            if($sundry->nature_of_sundry=="OTHER"){
                               $i++;
                            }
                         }
                        if($sale_detail->e_invoice_status==0){
                           $tRows = 7 - $i; 
                        }else{
                           $tRows = 7 - $i; 
                        }
                        $minimumLines = 8;
                        $tRows = $minimumLines - $displayLineCount;
                        while($tRows > 0){
                            @endphp  
                                <tr style="height: 21px;" class="{{ ($configuration && $configuration->lines_in_item_status == 0) ? 'no-border' : '' }}"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                            @php 
                            $tRows--; 
                        }
                     @endphp   
                     
                     <tr>
                        <td colspan="4" style="border-bottom:0; border-right:0"></td>
                        <td style="border-bottom:0; border-left:0;border-right:0;text-align: right;"><strong>{{$qty_total}}</strong></td>
                        <td style="border-bottom:0; border-left:0;border-right:0"><strong></strong></td>
                        <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                        <td style="text-align:right; border-bottom:0;">{{formatIndianNumber($item_total)}} </td>
                     </tr>
                     @php

            $totalCGST = 0;
            $totalSGST = 0;
            $totalIGST = 0;

            $displaySundries = [];

            foreach($sale_sundry as $printSundry){

               if(strtoupper($printSundry->nature_of_sundry) == 'CGST'){
                  $totalCGST += $printSundry->amount;
               }
               elseif(strtoupper($printSundry->nature_of_sundry) == 'SGST'){
                  $totalSGST += $printSundry->amount;
               }
               elseif(strtoupper($printSundry->nature_of_sundry) == 'IGST'){
                  $totalIGST += $printSundry->amount;
               }
               else{
                  $displaySundries[] = $printSundry;
               }
            }

            @endphp

            <tr>

               <td style="border-right:0; border-top:0;" colspan="2"></td>

               <td colspan="4" style="border-left:0; border-right:0; border-top:0;">

                  {{-- Other sundries first --}}
                  @foreach($displaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') === false)
                           @if($printSundry->bill_sundry_type == 'additive')
                              <p>Add : {{ $printSundry->name }}</p>
                           @else
                              <p>Less : {{ $printSundry->name }}</p>
                           @endif
                        @endif
                  @endforeach

                  @if($totalCGST > 0)
                        <p>Add : CGST</p>
                  @endif

                  @if($totalSGST > 0)
                        <p>Add : SGST</p>
                  @endif

                  @if($totalIGST > 0)
                        <p>Add : IGST</p>
                  @endif

                  {{-- Rounded Off last --}}
                  @foreach($displaySundries as $printSundry)
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

                  @foreach($displaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') === false)
                           <p>&nbsp;</p>
                        @endif
                  @endforeach

                  @if($totalCGST > 0)
                        <p>&nbsp;</p>
                  @endif

                  @if($totalSGST > 0)
                        <p>&nbsp;</p>
                  @endif

                  @if($totalIGST > 0)
                        <p>&nbsp;</p>
                  @endif

                  @foreach($displaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') !== false)
                           <p>&nbsp;</p>
                        @endif
                  @endforeach

               </td>

               <td style="text-align:right; border-top:0;">

                  @foreach($displaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') === false)
                           <p>{{ formatIndianNumber($printSundry->amount) }}</p>
                        @endif
                  @endforeach

                  @if($totalCGST > 0)
                        <p>{{ formatIndianNumber($totalCGST) }}</p>
                  @endif

                  @if($totalSGST > 0)
                        <p>{{ formatIndianNumber($totalSGST) }}</p>
                  @endif

                  @if($totalIGST > 0)
                        <p>{{ formatIndianNumber($totalIGST) }}</p>
                  @endif

                  @foreach($displaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') !== false)
                           <p>{{ formatIndianNumber($printSundry->amount) }}</p>
                        @endif
                  @endforeach

               </td>

            </tr>
 
            <tr>
               <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0;">
                  <p><strong>Grand Total ₹</strong></p>
               </td>
               <td style="text-align:right">
                  <p><strong class="invoice-total">{{formatIndianNumber($sale_detail->total)}}</strong></p>
               </td>
            </tr>
 
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

                     @php
                        $totalTaxable = 0;
                        $totalCGST = 0;
                        $totalSGST = 0;
                     @endphp

                     @foreach($gst_detail as $printVal)

                        @php
                           $totalTaxable += $printVal->taxable_amount;
                           $totalCGST += $printVal->amount;
                           $totalSGST += $printVal->amount;
                        @endphp

                        <tr>
                           <td style="border:none;padding:1px;">
                              {{$printVal->rate}}%
                           </td>

                           <td style="border:none;padding:1px;text-align:right;">
                              {{formatIndianNumber($printVal->taxable_amount)}}
                           </td>

                           <td style="border:none;padding:1px;text-align:right;">
                              {{formatIndianNumber($printVal->amount)}}
                           </td>

                           <td style="border:none;padding:1px;text-align:right;">
                              {{formatIndianNumber($printVal->amount)}}
                           </td>

                           <td style="border:none;padding:1px;text-align:right;">
                              {{formatIndianNumber($printVal->amount * 2)}}
                           </td>
                        </tr>

                     @endforeach

                     <tr>
                        <td colspan="5" style="padding:0;border:none;">
                           <hr style="margin:2px 0;border:none;border-top:1px solid #000;">
                        </td>
                     </tr>

                     <tr>

                        <td style="border:none;padding:1px;font-weight:bold;">
                           Total
                        </td>

                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                           {{formatIndianNumber($totalTaxable)}}
                        </td>

                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                           {{formatIndianNumber($totalCGST)}}
                        </td>

                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                           {{formatIndianNumber($totalSGST)}}
                        </td>

                        <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                           {{formatIndianNumber($totalCGST + $totalSGST)}}
                        </td>

                     </tr>

                  </table>

               </td>
            </tr>
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
                  @if(
    $configuration &&
    $configuration->bank_detail_status == 1 &&
    $bank_detail
)
                     <tr>
                        <td colspan="8">   
                           @if($configuration && $configuration->banks)                     
                              <p>
                                 <strong>Bank Details : </strong> <strong>ACCOUNT NAME-</strong>{{$configuration->banks->name}} <br><strong>ACCOUNT NO:</strong>{{$configuration->banks->account_no}} ,<strong>IFSC CODE:</strong>{{$configuration->banks->ifsc}} ,<strong>BANK NAME:</strong>{{$configuration->banks->bank_name}},{{$configuration->banks->branch}} 
                              </p>
                           @endif
                        </td>
                     </tr>
                  @endif
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

                     <td colspan="4">
                        <p style="height:40px; margin:0; padding:0;"><small>Receiver's Signature :</small></p>
                        <p style="text-align:right; padding:0; margin:0;"><strong>for {{$company_data->company_name}}</strong></p>

                        @if(
    $configuration &&
    $configuration->signature_status == 1 &&
    !empty($configuration->signature)
)
                           <p style="text-align:right; margin:0; padding:0;">
                              <img src="{{ URL::asset('public/images')}}/{{$configuration->signature}}" style="max-width:145px; max-height:120px; object-fit:contain;">
                           </p>
                           @else
                           <p style="text-align:right; margin:0; padding:0;width: 145px; height:70px;">
                              </p>
                        @endif

                        <p style="text-align:right; margin:0; padding:0;"><strong>Authorised Signatory</strong></p>
                     </td>

                  </tr>
               </tbody>
            </table>
            
            <span class="append_invoice"></span>
            
           
            @if(request('source') == 'sale')
            <div style="text-align: center;" class="noprint">
               <p id="jsonhtml"></p>
               @if($einvoice_status==1 && $sale_detail->e_invoice_status==0)
                  <button type="button" class="btn btn-info generate_einvoice">GENERATE E-INVOICE</button>
               @endif               
               @if(($einvoice_status==1 && $sale_detail->e_invoice_status==1 &&  $ewaybill_status==1 && $sale_detail->e_waybill_status==0) || ($einvoice_status==0 && $ewaybill_status==1 && $sale_detail->e_waybill_status==0))                  
                  <button type="button" class="btn btn-info generate_eway">GENERATE E-WAY BILL</button>                  
               @endif
                @can('view-module', 238)
               @if($einvoice_status==1 && $sale_detail->e_invoice_status==1)
                  <button type="button" class="btn btn-danger cancel_einvoice">CANCEL E-INVOICE</button>
               @endif
                @endcan
                @can('view-module', 237)
               @if($ewaybill_status==1 && $sale_detail->e_waybill_status==1)
                  <button type="button" class="btn btn-danger cancel_eway" style="margin-left: 100px;">CANCEL E-WAY BILL</button>                  
               @endif
               @endcan
            </div>  
            @endif
         </div>                     
      </div>
   </section>
</div>
</div>
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

         /* If all remaining items fit with final block, place them and finish */
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
   <div class="print-layout">
   @php
      $printSerial = 1;
   @endphp
   @foreach ($printPages as $printPageIndex => $printPage)
      @php
      $printIsFirstPage    = ($printPageIndex === 0);
      $printShowFinalBlock = !empty($printPage['show_final_block']);
      $printHasItems       = count($printPage['items']) > 0;
      $printShowBfRow      = (!$printIsFirstPage); // show B/F on every non-first page

      $usedPx = $px_header_actual;
      if ($printShowBfRow)      $usedPx += $PX_BF_ROW;
      foreach ($printPage['items'] as $pi) $usedPx += $fnItemPx($pi);
      if ($printShowFinalBlock) $usedPx += $PX_FINANCIAL;
      else                      $usedPx += $PX_CF_ROW;
      $usedPx += $PX_FOOTER;

      $printSpacerPx = max(0, $PX_PAGE - $usedPx);
   @endphp
   <div class="print-wrapper{{ ($printShowFinalBlock && !$printHasItems) ? ' print-summary-page' : '' }}">
   <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;width:100%;height:100%;border-collapse:collapse;" class="invoice-copy">
      <tbody>
          <tr>
            <th colspan="8" style="padding: 0;">
               <div class="invoice-company-header">
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                     <div style="flex:1; text-align:left;margin-left: 5px;">
                        <strong style="color: {{ $configuration->address_color ?? 'black' }};">
                           GSTIN: {{ $seller_info->gst_no }}
                        </strong>
                     </div>
                     <div style="flex:1; text-align:center;">
                        @if($configuration && !empty($configuration->invoice_header_text))
                           <strong style="font-size:13px; font-weight:700; letter-spacing:1px; color: {{ $configuration->address_color ?? 'black' }};">
                              {{ $configuration->invoice_header_text }}
                           </strong>
                        @endif
                     </div>
                     <div style="flex:1; text-align:right;margin-top: 7px;margin-right: 5px;">
                        <strong style="color: {{ $configuration->address_color ?? 'black' }};">
                           PAN: {{ substr($seller_info->gst_no, 2, 10) }}
                        </strong><br>
                        <small style="color: {{ $configuration->address_color ?? 'black' }};">
                           O/D/T
                        </small>
                     </div>
                  </div>
                  @php
                     $printCompanyName = $company_data->company_name;
                     $printFontSize = strlen($printCompanyName) > 30 ? '18px' : '24px';
                     if ($configuration && $configuration->company_name_font_size != "") {
                        $printFontSize = $configuration->company_name_font_size;
                     }
                  @endphp
                  @if($configuration && $configuration->company_logo_status==1
                     && $configuration->logo_position_left==1
                     && !empty($configuration->company_logo))
                     <div class="invoice-logo-left">
                        <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                     </div>
                  @endif
                  @if($configuration && $configuration->company_logo_status==1
                     && $configuration->logo_position_right==1
                     && !empty($configuration->company_logo))
                     <div class="invoice-logo-right">
                        <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}">
                     </div>
                  @endif
                  <div style="clear:both;"></div>
                  <div style="text-align:center; line-height:1; margin:0; padding:0;">
                     <p style="margin:0;color: {{ $configuration->address_color ?? 'black' }};"><u>TAX INVOICE</u></p>
                     <p style="margin:0; font-size: {{ $printFontSize }}; font-weight: bold; color: {{ $configuration->company_name_color ?? 'black' }};">
                        {{ $printCompanyName }}
                     </p>
                     <p style="margin:0;">
                        <small style="font-size: 12px; display:inline-block; max-width:50%; word-break:break-word;color: {{ $configuration->address_color ?? 'black' }};">
                           {{ $seller_info->address }}
                        </small>
                     </p>
                     <p style="margin:0;">
                        <small style="font-size: 12px; color: {{ $configuration->address_color ?? 'black' }};">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small>
                     </p>
                  </div>
               </div>
            </th>
         </tr>
          @if($sale_detail->e_invoice_status==1 && !empty($sale_detail->einvoice_response))
            <?php
               $printEinvoiceData = json_decode($sale_detail->einvoice_response);
               $printQrContent = $printEinvoiceData->SignedQRCode;
            ?>
            <tr>
               <td colspan="8">
                  <span style="float: right;width: 90px;height: 90px;position: relative;">
                     {!! QrCode::size(90)->generate($printQrContent) !!}
                  </span>
                  <p>IRN NO. : <?php echo $printEinvoiceData->Irn; ?></p>
                  <p>ACK.NO. : <?php echo $printEinvoiceData->AckNo; ?></p>
                  <p>ACK.DATE : <?php echo $printEinvoiceData->AckDt; ?></p>
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
            <td colspan="4" style="position: relative; vertical-align: top; padding: 0;">
               <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;"><strong>Billed to :</strong></p>
               <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                  <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">{{$sale_detail->billing_name}}</p>
                  <p style="margin: 2px 0 0 0; line-height: 13px;">{{$sale_detail->billing_address}}</p>
               </div>
               <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                  <p style="margin: 2px 0 0 0;font-weight:800">
                     GSTIN/UIN : {{$sale_detail->billing_gst}}
                     <span style="float: right;">PAN:{{$sale_detail->billing_pan}}</span>
                  </p>
               </div>
            </td>
            <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
               <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;"><strong>Shipped to :</strong></p>
               <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                  <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">
                     @if($sale_detail->shipping_name)
                        {{$sale_detail->shipp_name}}
                     @else
                        {{$sale_detail->billing_name}}
                     @endif
                  </p>
                  <p style="margin: 2px 0 0 0; line-height: 13px;">
                     @if($sale_detail->shipping_name)
                        {{$sale_detail->shipping_address}}
                     @else
                        {{$sale_detail->billing_address}}
                     @endif
                  </p>
               </div>
               <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                  <p style="margin: 2px 0 0 0;font-weight:800">
                     @if($sale_detail->shipping_name)
                        GSTIN/UIN : {{$sale_detail->shipping_gst}}
                        <span style="float: right;">PAN:{{$sale_detail->shipping_pan}}</span>
                     @else
                        GSTIN/UIN : {{$sale_detail->billing_gst}}
                        <span style="float: right;">PAN:{{$sale_detail->billing_pan}}</span>
                     @endif
                  </p>
               </div>
            </td>
         </tr>
          <tr>
            <th style="width:2%;padding: 0px 3px;">S. No.</th>
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
               <td></td>
               <td></td>
               <td style="text-align:right; padding:1px 5px;">{{formatIndianNumber($printPage['bf_amount'])}}</td>
            </tr>
         @endif
          @foreach($printPage['items'] as $printItem)
            <tr class="print-item-row {{ ($configuration && $configuration->lines_in_item_status == 0) ? 'no-border' : '' }}">
               <td style="text-align:center;">{{$printSerial}}</td>
               <td colspan="2" style="text-align:left;">
                  <strong>{{ $printItem->p_name }}</strong>
                  @if($configuration && $configuration->show_item_name == 1)
                     <span style="font-size:9px; color:#777; margin-left:4px;">
                        ({{ $printItem->name }})
                     </span>
                  @endif
                  @if(isset($printItem->lines) && count($printItem->lines) > 0)
                     @foreach($printItem->lines as $printLine)
                        <small style="display:block; font-size:10px; font-style: italic; color:#555; margin-left:10px;">
                           {{ $printLine->line_text }}
                        </small>
                     @endforeach
                  @endif
               </td>
               <td style="text-align:center;">{{$printItem->hsn_code}}</td>
               <td style="text-align:right">{{$printItem->qty}}</td>
               <td style="text-align:center">{{$printItem->unit}}</td>
               <td style="text-align:right;">{{$printItem->price}}</td>
               <td style="text-align:right; white-space:nowrap;">
                  {{formatIndianNumber($printItem->amount)}}
               </td>
            </tr>
            @php $printSerial++; @endphp
         @endforeach
         
        @if(!$printShowFinalBlock)
            <tr class="print-content-spacer">
               <td style="height:{{ $printSpacerPx }}px;padding:0;">&nbsp;</td>
               <td colspan="2" style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
            </tr>
         @endif
         @if(!$printShowFinalBlock)
            <tr class="print-compact-row" style="font-weight:700;">
               <td colspan="4" style="text-align:right; padding:1px 5px;">Carry Forward</td>
               <td style="text-align:right; padding:1px 5px;">{{$printPage['cf_qty']}}</td>
               <td></td>
               <td></td>
               <td style="text-align:right; padding:1px 5px;">{{formatIndianNumber($printPage['cf_amount'])}}</td>
            </tr>
         @endif
      </tbody>
      @if($printShowFinalBlock)
         <tbody>
            <tr class="print-content-spacer">
               <td style="height:{{ $printSpacerPx }}px;padding:0;">&nbsp;</td>
               <td colspan="2" style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
               <td style="padding:0;">&nbsp;</td>
            </tr>
         </tbody>
      @endif
      @if($printShowFinalBlock)
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
                           @if($printSundry->bill_sundry_type == 'additive')
                              <p>Add : {{ $printSundry->name }}</p>
                           @else
                              <p>Less : {{ $printSundry->name }}</p>
                           @endif
                        @endif
                  @endforeach

                  @if($printTotalCGST > 0)
                        <p>Add : CGST</p>
                  @endif

                  @if($printTotalSGST > 0)
                        <p>Add : SGST</p>
                  @endif

                  @if($printTotalIGST > 0)
                        <p>Add : IGST</p>
                  @endif

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
                        @if(stripos($printSundry->name, 'round') === false)
                           <p>&nbsp;</p>
                        @endif
                  @endforeach

                  @if($printTotalCGST > 0)
                        <p>&nbsp;</p>
                  @endif

                  @if($printTotalSGST > 0)
                        <p>&nbsp;</p>
                  @endif

                  @if($printTotalIGST > 0)
                        <p>&nbsp;</p>
                  @endif

                  @foreach($printDisplaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') !== false)
                           <p>&nbsp;</p>
                        @endif
                  @endforeach

               </td>

               <td style="text-align:right; border-top:0;">

                  @foreach($printDisplaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') === false)
                           <p>{{ formatIndianNumber($printSundry->amount) }}</p>
                        @endif
                  @endforeach

                  @if($printTotalCGST > 0)
                        <p>{{ formatIndianNumber($printTotalCGST) }}</p>
                  @endif

                  @if($printTotalSGST > 0)
                        <p>{{ formatIndianNumber($printTotalSGST) }}</p>
                  @endif

                  @if($printTotalIGST > 0)
                        <p>{{ formatIndianNumber($printTotalIGST) }}</p>
                  @endif

                  @foreach($printDisplaySundries as $printSundry)
                        @if(stripos($printSundry->name, 'round') !== false)
                           <p>{{ formatIndianNumber($printSundry->amount) }}</p>
                        @endif
                  @endforeach

               </td>
            </tr>
            <tr class="print-compact-row">
               <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0; padding:1px 5px;">
                  <p style="margin:0;"><strong>Grand Total ₹</strong></p>
               </td>
               <td style="text-align:right; padding:1px 5px; white-space:nowrap;">
                  <p style="margin:0;"><strong class="invoice-total">{{formatIndianNumber($sale_detail->total)}}</strong></p>
               </td>
            </tr>
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
                  @php
                     $totalTaxable = 0;
                     $totalCGST = 0;
                     $totalSGST = 0;
                  @endphp
                  @foreach($gst_detail as $printVal)
                     @php
                        $totalTaxable += $printVal->taxable_amount;
                        $totalCGST += $printVal->amount;
                        $totalSGST += $printVal->amount;
                     @endphp
                     <tr>
                        <td style="border:none;padding:1px;">
                           {{$printVal->rate}}%
                        </td>
                        <td style="border:none;padding:1px;text-align:right;">
                           {{formatIndianNumber($printVal->taxable_amount)}}
                        </td>
                        <td style="border:none;padding:1px;text-align:right;">
                           {{formatIndianNumber($printVal->amount)}}
                        </td>
                        <td style="border:none;padding:1px;text-align:right;">
                           {{formatIndianNumber($printVal->amount)}}
                        </td>
                        <td style="border:none;padding:1px;text-align:right;">
                           {{formatIndianNumber($printVal->amount * 2)}}
                        </td>
                     </tr>
                  @endforeach
                  <tr>
                     <td colspan="5" style="padding:0;border:none;">
                        <hr style="margin:2px 0;border:none;border-top:1px solid #000;">
                     </td>
                  </tr>
                  <tr>
                     <td style="border:none;padding:1px;font-weight:bold;">
                        Total
                     </td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                        {{formatIndianNumber($totalTaxable)}}
                     </td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                        {{formatIndianNumber($totalCGST)}}
                     </td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                        {{formatIndianNumber($totalSGST)}}
                     </td>
                     <td style="border:none;padding:1px;text-align:right;font-weight:bold;">
                        {{formatIndianNumber($totalCGST + $totalSGST)}}
                     </td>
                  </tr>
               </table>
            </td>
         </tr>
            <tr class="print-compact-row">
               <td colspan="8" style="border-top:0; padding:1px 5px;">
                  <strong>
                     <?php
                     $printNumber = $sale_detail->total;
                     $printNo = floor($printNumber);
                     $printPoint = round($printNumber - $printNo, 2) * 100;
                     $printHundred = null;
                     $printDigits1 = strlen($printNo);
                     $printI = 0;
                     $printStr = array();
                     $printWords = array(
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
                     $printDigits = array('', 'hundred', 'thousand', 'lakh', 'crore');
                     while ($printI < $printDigits1) {
                        $printDivider = ($printI == 2) ? 10 : 100;
                        $printNumber = floor($printNo % $printDivider);
                        $printNo = floor($printNo / $printDivider);
                        $printI += ($printDivider == 10) ? 1 : 2;
                        if ($printNumber) {
                           $printPlural = (($printCounter = count($printStr)) && $printNumber > 9) ? 's' : null;
                           $printHundred = ($printCounter == 1 && $printStr[0]) ? ' and ' : null;
                           $printStr[] = ($printNumber < 21) ? $printWords[$printNumber] .
                              " " . $printDigits[$printCounter] . $printPlural . " " . $printHundred
                              :
                              $printWords[floor($printNumber / 10) * 10]
                              . " " . $printWords[$printNumber % 10] . " "
                              . $printDigits[$printCounter] . $printPlural . " " . $printHundred;
                        } else {
                           $printStr[] = null;
                        }
                     }
                     $printStr = array_reverse($printStr);
                     $printResult = implode('', $printStr);
                     echo ucfirst($printResult) . "Rupees  only";
                     ?>
                  </strong>
               </td>
            </tr>
            @if(
               $configuration &&
               $configuration->bank_detail_status == 1 &&
               $bank_detail
            )
               <tr class="print-compact-row">
                  <td colspan="8" style="padding:1px 5px;">
                     @if($configuration && $configuration->banks)
                        <p>
                           <strong>Bank Details : </strong> <strong>ACCOUNT NAME-</strong>{{$configuration->banks->name}} <br><strong>ACCOUNT NO:</strong>{{$configuration->banks->account_no}} ,<strong>IFSC CODE:</strong>{{$configuration->banks->ifsc}} ,<strong>BANK NAME:</strong>{{$configuration->banks->bank_name}},{{$configuration->banks->branch}}
                        </p>
                     @endif
                  </td>
               </tr>
            @endif
      </tbody>
      @endif
      
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
<div class="modal fade" id="ewayBillModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <h4 class="modal-title"><span id="modal_parameter_name"></span>Eway Details</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body text-center p-0">
            <div class="row">
               <div class="mb-12 col-md-12">
                  <label for="vehicle_no" class="form-label font-14 font-heading">Vehicle Number</label>
                  <input type="text" class="form-control" id="vehicle_no" placeholder="Enter Vehicle Number" value="{{$sale_detail->vehicle_no}}">
               </div>
               <div class="mb-12 col-md-12">
                  <label for="distance" class="form-label font-14 font-heading">Distance</label>
                  <input type="text" class="form-control" id="distance" placeholder="Enter Distance" value="{{$eway_bill_distance}}">
               </div>
               <div class="mb-12 col-md-12">
                  <label for="transporter_id" class="form-label font-14 font-heading">Transporter Id (Optional)</label>
                  <input type="text" class="form-control" id="transporter_id" placeholder="Enter Transporter Id">
               </div>
            </div>
         </div>
         <br></br>
         <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-danger cancel">CANCEL</button>
            <button type="button" class="ms-3 btn btn-info generate_eway_btn">Generate</button>
         </div>
      </div>
   </div>
</div>
<!-- Hidden Challan Print Section -->
<div id="challanPrintSection" style="display:none; padding:15px; font-family:Arial, sans-serif; line-height:1.2;">
    <style>
        @media print {
            body { margin: 0; }
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
            grid-template-columns: 1fr 1fr;
            gap: 8px;
         }

         @media print {
            .two-column {
               grid-template-columns: 1fr 1fr;
            }
         }

        .item-block {
            break-inside: avoid;
            page-break-inside: avoid;
            margin-bottom: 2px;
            border: 1px solid #ccc;
            padding: 1px; 
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
            background: #f2f2f2;
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
        <div>Date: {{ \Carbon\Carbon::parse($sale_detail->date)->format('d M Y') }}</div>
    </div>
    <hr style="margin:2px 0;">

    <div class="header-row" style="margin-bottom:2px;">
        <div><strong>Party:</strong> {{ $saleOrder->billTo->account_name ?? ($party_detail->print_name ?? '-') }}</div>
        <div><strong>Vehicle No:</strong> {{ $sale_detail->vehicle_no ?? '' }}</div>
        <div><strong>Challan No:</strong> {{ $sale_detail->voucher_no_prefix ?? '' }}</div>
    </div>
    <hr style="margin:2px 0;">

    @php 
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
        if($sale_detail->id==64906){
        //echo "<pre>";
        //print_r($saleItemSizes->toArray());
        }
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
    @endphp

      @if($groupedItems->count() > 0)
         <div class="two-column">
               @foreach($groupedItems as $groupKey => $rows)
            @php
            
               $itemTotal = 0;
               $firstRow = $rows->first();
            @endphp
                  <div class="item-block">
                     <h4 style="margin:1px 0; font-size:13px;">
                           Item: {{ $firstRow['item_name'] }}
                     </h4>
                     <table>
                           <thead>
                              <tr>
                                 <th style="width:8%;">S No.</th>
                                 <th style="width:25%;">Reel No</th>
                                 <th style="width:45%;">Size (with Unit)</th>
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
                    $totalReels = $serialNo - 1; // last serial number = total reels
                @endphp
            @endforeach
        </div>

        <hr style="margin:3px 0;">
        <div class="final-totals">
            <div>Total Reels: {{ $totalReels }}</div>
            <div>Grand Total Weight: {{ number_format($grandTotal, 2) }} Kg</div>
        </div>
    @else
        <p class="text-danger">No Sale or Sale Order item-size data found for this invoice.</p>
    @endif
</div>
@if($company_data->company_sale_type == 'TAAROBAAR')

<div id="taarobaarChallanPrintSection"
     style="display:none; padding:20px; font-family:Arial, sans-serif; color:#000;">

<style>

#taarobaarChallanPrintSection{
    width:100%;
    font-family:Arial, sans-serif;
    color:#000;
}

#taarobaarChallanPrintSection table{
    width:100%;
    border-collapse:collapse;
}

#taarobaarChallanPrintSection th,
#taarobaarChallanPrintSection td{
    border:1px solid #000;
    padding:6px;
    vertical-align:top;
    font-size:14px;
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

.font-bold{
    font-weight:bold;
}

</style>

<div style="border:1px solid #000; padding:0;">
    <div style="text-align:center;
                padding-top:10px;
                padding-bottom:10px;">
        <div style="font-size:24px;
                    font-weight:bold;">
            Estimate
        </div>
        <div style="font-size:28px;
                    font-weight:bold;
                    margin-top:5px;">
            {{ strtoupper($company_data->company_name) }}
        </div>
    </div>
    <table>
      <tr>
         <td style="width:65%; padding:3px 5px; vertical-align:top;">
               <span style="font-size:11px; font-weight:bold; display:block;">Party Details:</span>
               <span style="font-size:13px; font-weight:bold; display:block; line-height:1.3;">{{ strtoupper($sale_detail->billing_name) }}</span>
               <span style="font-size:11px; display:block; line-height:1.3;">{{ strtoupper($sale_detail->billing_address) }}</span>
         </td>
         <td style="width:35%; padding:3px 5px; vertical-align:top;">
               <table style="width:100%; border-collapse:collapse;">
                  <tr>
                     <td style="border:none; font-size:11px; font-weight:bold; padding:1px 0; white-space:nowrap;">Challan No.</td>
                     <td style="border:none; font-size:11px; padding:1px 0;">: {{ $sale_detail->voucher_no_prefix }}</td>
                  </tr>
                  <tr>
                     <td style="border:none; font-size:11px; font-weight:bold; padding:1px 0;">Dated</td>
                     <td style="border:none; font-size:11px; padding:1px 0;">: {{ date('d-m-Y', strtotime($sale_detail->date)) }}</td>
                  </tr>
                  <tr>
                     <td style="border:none; font-size:11px; font-weight:bold; padding:1px 0;">Vehicle No.</td>
                     <td style="border:none; font-size:11px; padding:1px 0;">: {{ $sale_detail->vehicle_no }}</td>
                  </tr>
               </table>
         </td>
      </tr>
   </table>
    <table>
        <thead>
            <tr>
                <th style="width:5%;">
                    S.N.
                </th>
                <th style="width:35%;">
                    Item Name
                </th>
                <th style="width:10%;">
                    Qty
                </th>
                <th style="width:15%;">
                    Weight
                </th>
                <th style="width:15%;">
                    Kgs
                </th>
                <th style="width:10%;">
                    Price
                </th>
                <th style="width:10%;">
                    Amount(₹)
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $i = 1;
                $qtyTotal = 0;
                $weightTotal = 0;
                $amountTotal = 0;
            @endphp
            @foreach($items_detail as $item)
                @php
                    $pieceWeights = DB::table('taarobar_sale_description_piece_weights')
                        ->where('sale_description_id', $item->sale_description_id)
                        ->pluck('weight');
                    if($item->dual_unit == 1){
                        $displayQty = $item->taarobaar_qty;
                        $displayTotalWeight = $item->qty;
                    }else{
                        $displayQty = $item->qty;
                        $displayTotalWeight = 0;
                    }
                    $qtyTotal += $displayQty;
                    $weightTotal += $displayTotalWeight;
                    $amountTotal += $item->amount;
                @endphp
                <tr>
                    <td class="text-center">
                        {{ $i++ }}
                    </td>
                    <td>
                        <strong>
                            {{ strtoupper($item->p_name) }}
                        </strong>
                    </td>
                    <td class="text-center">
                        @if($item->dual_unit == 1)
                            {{ number_format($displayQty,0) }}
                        @else
                            {{ number_format($displayQty,2) }}
                        @endif
                    </td>
                    <td class="text-right"
                        style="line-height:24px;">
                        @if(count($pieceWeights) > 0)
                            @foreach($pieceWeights as $pw)
                                {{ number_format($pw,2) }}
                                <br>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if($displayTotalWeight > 0)
                            {{ number_format($displayTotalWeight,2) }} KGS
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        {{ number_format($item->price,2) }}
                    </td>
                    <td class="text-right">
                        {{ number_format($item->amount,2) }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6"
                    class="text-right font-bold"
                    style="font-size:16px;">
                    Total
                </td>
                <td class="text-right font-bold"
                    style="font-size:16px;">
                    {{ number_format($amountTotal,2) }}
                </td>
            </tr>
            @foreach($sale_sundry as $sundry)
            <tr>
                <td colspan="6"
                    class="text-right"
                    style="border-top:none;
                           border-bottom:none;">
                    {{ $sundry->name }}
                    @if($sundry->rate > 0)
                        @ {{ $sundry->rate }}%
                    @endif
                </td>
                <td class="text-right"
                    style="border-top:none;
                           border-bottom:none;">
                    {{ number_format($sundry->amount,2) }}
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="4"
                    class="text-right font-bold"
                    style="font-size:20px;">
                    Grand Total ₹
                </td>
                <td class="text-center font-bold"
                    style="font-size:18px;">
                    {{ number_format($weightTotal,2) }}
                </td>
                <td>
                </td>
                <td class="text-right font-bold"
                    style="font-size:20px;">
                    {{ number_format($sale_detail->total,2) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
<br>
<div style="font-size:16px;">
    <span class="bold">
        Amount in Words :
    </span>
    <?php
    function numberToWords($number) {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '', '1' => 'one', '2' => 'two',
            '3' => 'three', '4' => 'four', '5' => 'five',
            '6' => 'six', '7' => 'seven', '8' => 'eight',
            '9' => 'nine', '10' => 'ten', '11' => 'eleven',
            '12' => 'twelve', '13' => 'thirteen',
            '14' => 'fourteen', '15' => 'fifteen',
            '16' => 'sixteen', '17' => 'seventeen',
            '18' => 'eighteen', '19' => 'nineteen',
            '20' => 'twenty', '30' => 'thirty',
            '40' => 'forty', '50' => 'fifty',
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
                $str[] = ($number < 21)
                    ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                    : $words[floor($number / 10) * 10]
                        . " " . $words[$number % 10] . " "
                        . $digits[$counter] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        return ucfirst($result) . " Rupees Only";
    }
    echo numberToWords($sale_detail->total);
    ?>
</div>
</div>
@endif


@include('layouts.footer')
<script> 

const companyName = @json($company_data->company_name ?? '');
let configuration = @json($configuration->no_of_bill_copy);
if(configuration==null || configuration==undefined || configuration==""){
   configuration = 0;
}
    function redirectBack() {
    const previousUrl = document.referrer;
    const sessionPreviousUrl = "{{ session('previous_url') }}";
    const sessionPreviousSaleEditUrl = "{{ session('previous_url_saleEdit') }}";
    
    

    // If referrer matches session URLs → redirect to /sale
    if (previousUrl === sessionPreviousUrl || previousUrl === sessionPreviousSaleEditUrl) {
        window.location.href = "{{ url('sale') }}";
    } else {
        // Try going back in history
        if (window.history.length > 1) {
            history.back();
        } else {
            // No history → redirect to /sale
            window.location.href = "{{ url('sale') }}";
        }
    }
}

   
   $(document).ready(function(){
      $(".generate_einvoice").click(function(){
         if(confirm("Are you confirm ?")==true){
            let id = "<?php echo $sale_detail->id ?>";
            $.ajax({
               url: '{{url("generate-einvoice")}}',
               async: false,
               type: 'POST',
               dataType: 'JSON',
               data: {
                  _token: '<?php echo csrf_token() ?>',
                  id: id
               },
               success: function(res){ 
                  if(res.success==true){
                     alert(res.message)
                     location.reload();
                     //$("#jsonhtml").html("<pre>" + JSON.stringify(res.data) + "</pre>");
                  }else{
                     alert(res.message)
                  }                  
               }
            });
         }
      });
      $(".generate_eway").click(function(){
         $("#ewayBillModal").modal('toggle');         
      });      
      $(".generate_eway_btn").click(function(){
         let id = "<?php echo $sale_detail->id; ?>";
         let vehicle_no = $("#vehicle_no").val();
         let distance = $("#distance").val();
         let transporter_id = $("#transporter_id").val();
         if(vehicle_no==""){
            alert("Please Enter Vehicle No.");
            return;
         }
         if(distance==""){
            alert("Please Enter Distance");
            return;
         }
         if(confirm("Are you confirm ?")==true){            
            $.ajax({
               url: '{{url("generate-ewaybill")}}',
               async: false,
               type: 'POST',
               dataType: 'JSON',
               data: {
                  _token: '<?php echo csrf_token() ?>',
                  id: id,
                  vehicle_number : vehicle_no,
                  distance : distance,
                  transporter_id : transporter_id
               },
               success: function(res){                  
                  if(res.success==true){
                     alert(res.message)
                     location.reload();
                     //$("#jsonhtml").html("<pre>" + JSON.stringify(res.data) + "</pre>");
                  }else{
                      console.log(res);
                      
                     alert(res.message)
                  } 
               }
            });
         }
      });
      $(".cancel_einvoice").click(function(){
         if(confirm("Are you confirm cancel einvoice?")==true){
            let id = "<?php echo $sale_detail->id ?>";
            $.ajax({
               url: '{{url("cancel-einvoice")}}',
               async: false,
               type: 'POST',
               dataType: 'JSON',
               data: {
                  _token: '<?php echo csrf_token() ?>',
                  id: id
               },
               success: function(res){ 
                  if(res.success==true){
                     alert(res.message)
                     location.reload();
                  }else{
                     alert(res.message)
                  }                  
               }
            });
         }
      });
      $(".cancel_eway").click(function(){
         if(confirm("Are you confirm eway bill?")==true){
            let id = "<?php echo $sale_detail->id ?>";
            $.ajax({
               url: '{{url("cancel-ewaybill")}}',
               async: false,
               type: 'POST',
               dataType: 'JSON',
               data: {
                  _token: '<?php echo csrf_token() ?>',
                  id: id
               },
               success: function(res){ 
                  if(res.success==true){
                     alert(res.message)
                     location.reload();
                  }else{
                     alert(res.message)
                  }                  
               }
            });
         }
      });
   });
  function printpage() {
    const billCopies = Math.max(1, parseInt(configuration, 10) || 1);
    const $printLayout = $('.print-layout');

    if (!$printLayout.length) {
        window.print();
        return;
    }

    const $originalWrappers = $printLayout.children('.print-wrapper').detach();

    for (let copy = 0; copy < billCopies; copy++) {
        $originalWrappers.each(function () {
            $printLayout.append($(this).clone());
        });
    }

    setTimeout(function () {
        window.print();

        setTimeout(function () {
            $printLayout.empty();
            $printLayout.append($originalWrappers);
        }, 500);
    }, 200);
}

   $(document).on('click', '#printChallanBtn', function () {
      let companyId = "{{ Session::get('user_company_id') }}";
      let company_sale_type = "{{ $company_data->company_sale_type }}";
      
      let sectionId = 'challanPrintSection';
      let pageTitle = 'Packaging Slip';
      if (company_sale_type == "TAAROBAAR") {
         sectionId = 'taarobaarChallanPrintSection';
         pageTitle = 'Estimate';
      }
      
      let printContents = document.getElementById(sectionId).innerHTML;
      let printWindow = window.open('', '', 'height=900,width=1200');
      
     
      if (company_sale_type == "TAAROBAAR") {
         printWindow.document.write(`
            <html>
            <head>
                  <title>${pageTitle}</title>
                  <style>
                     * {
                        box-sizing: border-box;
                        margin: 0;
                        padding: 0;
                     }
                     body {
                        font-family: Arial, sans-serif;
                        color: #000;
                        background: #fff;
                     }
                     table {
                        width: 100%;
                        border-collapse: collapse;
                     }
                     th, td {
                        border: 1px solid #000;
                        padding: 6px 8px;
                        vertical-align: top;
                     }
                     thead {
                        background: #f2f2f2;
                     }
                     .text-center { text-align: center; }
                     .text-right  { text-align: right;  }
                     .font-bold   { font-weight: bold;   }
                     .total-row {
                        font-weight: bold;
                        background: #e8f5e9;
                     }
                     @media print and (orientation: portrait) {

                        @page {
                           margin: 6mm 8mm;
                        }

                        html, body {
                           height: 100%;
                           overflow: hidden;
                        }

                        body {
                           padding: 0;
                        }

                        .challan-wrapper {
                           display: flex;
                           flex-direction: column;
                           width: 100%;
                           height: 99vh;
                           gap: 3mm;
                        }

                        .challan-copy {
                           flex: 1 1 0;
                           min-height: 0;
                           overflow: hidden;
                           border: 1px solid #000;
                           padding: 4px 6px;
                           page-break-inside: avoid;
                           break-inside: avoid;
                        }

                        .challan-divider {
                           display: block;
                           height: 0;
                           border-top: 1px dashed #aaa;
                           margin: 0;
                           flex-shrink: 0;
                        }

                        /* Compress everything to fit both halves */
                        table { font-size: 10px; }
                        th, td { padding: 2px 4px; }
                        h3 { font-size: 12px; margin: 0; }
                        h4 { font-size: 11px; margin: 0 0 1px 0; }
                        p, div { font-size: 10px; line-height: 1.2; }
                        .header-row { margin-bottom: 1px; font-size: 10px; }
                        .final-totals { font-size: 10px; margin-top: 2px; }
                        hr { margin: 1px 0; }

                        /* Compress party + challan header row */
                        .header-row > div { padding: 0; }

                        /* Hide D/L row entirely */
                        .dl-row { display: none !important; }

                        /* Tighten the two-column item grid */
                        .two-column {
                           display: grid;
                           grid-template-columns: 1fr 1fr;
                           gap: 3px;
                        }

                        .item-block {
                           padding: 1px 2px;
                           margin-bottom: 1px;
                        }
                     }
                     @media print and (orientation: landscape) {
                        @page {
                              margin: 10mm 12mm;
                        }
                        body {
                              padding: 0;
                        }
                        .challan-wrapper {
                              display: flex;
                              flex-direction: row;
                              gap: 0;
                              width: 100%;
                              height: 100%;
                              align-items: flex-start;
                        }
                        .challan-copy {
                              flex: 1 1 0;
                              min-width: 0;
                              border: 1px solid #000;
                              padding: 8px;
                              page-break-inside: avoid;
                              break-inside: avoid;
                        }
                        .challan-divider {
                              width: 10mm;
                              flex-shrink: 0;
                              display: block;
                              /* vertical dotted cut-line between the two copies */
                              border-left: 1px dashed #aaa;
                              align-self: stretch;
                              margin: 0 2mm;
                        }
                        table { font-size: 11px; }
                        th, td { padding: 4px 5px; }
                     }
                     @media screen {
                        body { padding: 10px; background: #eee; }
                        .challan-wrapper {
                           display: flex;
                           flex-direction: row;
                           gap: 10px;
                           flex-wrap: wrap;
                        }
                        .challan-copy {
                           flex: 1 1 360px;
                           background: #fff;
                           border: 1px solid #000;
                           padding: 10px;
                           min-width: 280px;
                        }
                        .challan-divider {
                           width: 1px;
                           background: #ccc;
                           align-self: stretch;
                        }
                        .dl-row { display: none !important; }
                     }
                  </style>
            </head>
            <body>
                  <div class="challan-wrapper">
                     <div class="challan-copy">
                        ${printContents}
                     </div>
                     <div class="challan-divider"></div>
                     <div class="challan-copy">
                        ${printContents}
                     </div>
                  </div>
            </body>
            </html>
         `);
         printWindow.document.close();
         printWindow.focus();
         setTimeout(function () {
            printWindow.print();
         }, 600);
      }else{
         printWindow.document.write('<html><head><title>Packaging Slip</title></head><body>');
         printWindow.document.write(printContents);
         printWindow.document.write('</body></html>');
         printWindow.document.close();
         printWindow.print();
      }
   });
   
   function openGmailWithInvoice(id, email, voucherNo) {

    // Step 1: Download PDF
    window.open('/sale-invoice/pdf/' + id, '_blank');

    // Step 2: Open Gmail compose
    let gmailUrl = "https://mail.google.com/mail/?view=cm&fs=1"
        + "&to=" + encodeURIComponent(email)
        + "&su=" + encodeURIComponent(companyName + " - Invoice " + voucherNo)
        + "&body=" + encodeURIComponent("Dear Sir/Madam,\n\nPlease find attached invoice.\n\nRegards,\n" + companyName);

    window.open(gmailUrl, '_blank');
}
$(document).on('click','#approveSale',function(){

    if(confirm("Approve this Sale Invoice?")){

        let id = "{{ $sale_detail->id }}";
        let returnUrl = @json($return_url);

        $.ajax({

             url: "{{ route('transaction.approve') }}",
            type: "POST",

            data:{
                _token:"{{ csrf_token() }}",
                id:id,
                module:'sale'
            },

            success:function(res){
                if(res.status){
                        alert("Transaction Approved ✅");
                        if(returnUrl){
                        window.location.href = returnUrl;
                    }else{
                        window.location.href = "{{ url('transaction-report') }}";
                    }
                    } else {
                        alert("Something went wrong.");
                    }
                

            }

        });

    }

});
</script>
@endsection
