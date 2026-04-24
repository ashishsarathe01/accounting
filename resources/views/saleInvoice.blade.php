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
   @media print{
      .noprint{
         display:none;
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
    font-size:18px;
    font-weight:800;
    margin:0;
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

</style>

<div class="list-of-view-company ">
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
            
            <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;">
               <tbody>
                  <tr>
                    <th colspan="8" style="padding: 0;">
                        <div style="min-height: 130px; position: relative;">
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
                                <div style="position:absolute; left:10px; top:45px;">
                                   <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"
                                         style="height:80px;">
                                </div>

                            @endif

                            {{-- RIGHT LOGO --}}
                            @if($configuration && $configuration->company_logo_status==1 
                               && $configuration->logo_position_right==1 
                               && !empty($configuration->company_logo))
                                <div style="position:absolute; right:10px; top:45px;">
                                   <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"
                                         style="height:80px;">
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
                     @php $i=1;$item_total = 0;$qty_total = 0; @endphp
                     @foreach($items_detail as $item)
                        <tr>
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
                           <td style="text-align:right;">{{formatIndianNumber($item->amount)}}</td>
                        </tr>
                        @php $i++;$item_total = $item_total + $item->amount; $qty_total = $qty_total + $item->qty;  @endphp
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
                           $tRows = 7 - $i; 
                        }                         
                        while($tRows>=0){
                            @endphp  
                                <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                            @php 
                            $tRows--; 
                        }
                     @endphp   
                     <tr>
                       <td colspan="8" style="height: {{ $tRows * 21 }}px; border:none;"></td>
                    </tr>
                     <tr>
                        <td colspan="4" style="border-bottom:0; border-right:0"></td>
                        <td style="border-bottom:0; border-left:0;border-right:0;text-align: right;"><strong>{{$qty_total}}</strong></td>
                        <td style="border-bottom:0; border-left:0;border-right:0"><strong></strong></td>
                        <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                        <td style="text-align:right; border-bottom:0;">{{formatIndianNumber($item_total)}} </td>
                     </tr>
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
                        <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0;">
                           <p><strong>Grand Total ₹</strong></p>
                        </td> 
                        <!--<td  style="text-align:right; border-right: 0; border-bottom: 0;border-left: 0;">-->
                        <!--   <p><strong>{{$qty_total}}</strong></p>-->
                        <!--</td> -->
                        <!--<td  style="text-align:right; border-right: 0; border-bottom: 0;border-left: 0;">-->
                        <!--   <p><strong></strong></p>-->
                        <!--</td> -->
                        <!--<td  style="text-align:right; border-right: 0; border-bottom: 0;border-left: 0;">-->
                        <!--   <p><strong></strong></p>-->
                        <!--</td> -->
                        <td style="text-align:right">
                           <p><strong class="invoice-total">{{formatIndianNumber($sale_detail->total)}}</strong></p>
                        </td>
                     </tr>
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
                  @if($bank_detail)
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

   @if($configuration && !empty($configuration->signature))
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
            <br>
           
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


@include('layouts.footer')
<script> 

const companyName = @json($company_data->company_name ?? '');
console.log(companyName);
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
   function printpage(){
       //$('.sidebar').addClass('importantRule'); // only sidebar hide
       window.print();
       //$('.sidebar').removeClass('importantRule');
    }

   $(document).on('click', '#printChallanBtn', function() {
      let printContents = document.getElementById('challanPrintSection').innerHTML;
      let newWindow = window.open('', '', 'width=900,height=700');
      newWindow.document.write('<html><head><title>Packaging Slip</title></head><body>');
      newWindow.document.write(printContents);
      newWindow.document.write('</body></html>');
      newWindow.document.close();
      newWindow.print();
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
