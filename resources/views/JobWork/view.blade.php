@extends('layouts.app')
@section('content')
@include('layouts.header')
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

<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center header-section noprint">
               <div class="calender-administrator my-2 my-md-0 noprint">
                  @if($type == 'raw')
                     <button type="button" class="btn btn-danger"
                           onclick="window.location='{{ route('jobwork.out.raw') }}'">QUIT</button>
                  @else
                     <button type="button" class="btn btn-danger"
                           onclick="window.location='{{ route('jobwork.out.finished') }}'">QUIT</button>
                  @endif
                  <button class="btn btn-info" onclick="window.print();">Print</button>
               </div>
            </div>
            <br>
            <div class="print-area">
            <table style="font-family: 'Source Sans Pro', sans-serif; letter-spacing: 0.05em; color: #404040; font-size: 12px; font-weight: 500; padding: 10px;">
               <tbody>
                  <tr>
                     <th colspan="8" style="padding: 0;">
                        <div style="min-height: 130px; position: relative;">

                           {{-- Top row: GST | Header Text | PAN --}}
                           <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                              <div style="flex:1; text-align:left; margin-left:5px;">
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

                              <div style="flex:1; text-align:right; margin-top:7px; margin-right:5px;">
                                 <strong style="color: {{ $configuration->address_color ?? 'black' }};">
                                    PAN: {{ substr($seller_info->gst_no, 2, 10) }}
                                 </strong><br>
                                 <small style="color: {{ $configuration->address_color ?? 'black' }};">O/D/T</small>
                              </div>
                           </div>

                           @php
                              $companyName = $company_data->company_name;
                              $fontSize = strlen($companyName) > 30 ? '18px' : '24px';
                              if($configuration && $configuration->company_name_font_size != ""){
                                 $fontSize = $configuration->company_name_font_size;
                              }
                           @endphp

                           @if($configuration && $configuration->company_logo_status == 1
                              && $configuration->logo_position_left == 1
                              && !empty($configuration->company_logo))
                              <div style="position:absolute; left:10px; top:45px;">
                                 <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"
                                      style="height:80px;">
                              </div>
                           @endif

                           @if($configuration && $configuration->company_logo_status == 1
                              && $configuration->logo_position_right == 1
                              && !empty($configuration->company_logo))
                              <div style="position:absolute; right:10px; top:45px;">
                                 <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}"
                                      style="height:80px;">
                              </div>
                           @endif

                           <div style="clear:both;"></div>

                           <div style="text-align:center; line-height:1; margin:0; padding:0;">
                              <p style="margin:0; color: {{ $configuration->address_color ?? 'black' }};"><u>Delivery Challan</u></p>
                              <p style="margin:0; color: {{ $configuration->address_color ?? 'black' }};">Only For Job Work</p>
                              <p style="margin:0; font-size: {{ $fontSize }}; font-weight: bold; color: {{ $configuration->company_name_color ?? 'black' }};">
                                 {{ $companyName }}
                              </p>
                              <p style="margin:0;">
                                 <small style="font-size:12px; display:inline-block; max-width:50%; word-break:break-word; color: {{ $configuration->address_color ?? 'black' }};">
                                    {{ $seller_info->address }}
                                 </small>
                              </p>
                              <p style="margin:0;">
                                 <small style="font-size:12px; color: {{ $configuration->address_color ?? 'black' }};">
                                    Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}
                                 </small>
                              </p>
                           </div>
                        </div>
                     </th>
                  </tr>

                  <tr>
                     <td colspan="4">
                        <p><span class="width25">Delivery No. </span>: <span class="lft_mar15" style="font-weight:800">{{ $jobwork->voucher_no_prefix ?? $jobwork->voucher_no ?? '' }}</span></p>
                        <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{ date('d-m-Y', strtotime($jobwork->date)) }}</span></p>
                        <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">{{ $jobwork->party->state_code ?? '' }}</span></p>
                        <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15">{{ $jobwork->reverse_charge ?? 'No' }}</span></p>
                        <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{ $jobwork->gr_rr_no ?? '' }}</span></p>
                     </td>
                     <td colspan="4">
                        <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text">{{ $jobwork->transport_name ?? '' }}</span></p>
                        <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{ $jobwork->vehicle_no ?? '' }}</span></p>
                        <p><span class="width25">Station </span>: <span class="lft_mar15">{{ $jobwork->station ?? '' }}</span></p>
                        <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15">-</span></p>
                        <p>&nbsp;</p>
                        {{-- PURCHASE ORDER — same guard as sale invoice --}}
                        @if($configuration && $configuration->purchase_order_status == 1)
                        <p>
                           <span class="width25">PO No. </span>:
                           <span class="lft_mar15">{{ $jobwork->po_no ?? '' }}</span>
                        </p>
                        <p>
                           <span class="width25">PO Date </span>:
                           <span class="lft_mar15">
                              {{ !empty($jobwork->po_date) ? date('d-m-Y', strtotime($jobwork->po_date)) : '' }}
                           </span>
                        </p>
                        @endif
                     </td>
                  </tr>

                  <tr>
                     <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                        <p style="margin:0; position:absolute; top:0; left:5px; font-style:italic;"><strong>Billed to :</strong></p>
                        <div style="padding-top:16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin:2px 0 0 0; line-height:13px; font-weight:800;">
                              {{ $jobwork->party->account_name ?? '-' }}
                           </p>
                           <p style="margin:2px 0 0 0; line-height:13px;">
                              {{ $jobwork->party->address ?? '' }} {{ $jobwork->party->address2 ?? '' }} {{ $jobwork->party->address3 ?? '' }}
                           </p>
                        </div>
                        <div style="position:absolute; bottom:0; left:5px; right:4px;">
                           <p style="margin:2px 0 0 0; font-weight:800;">
                              GSTIN/UIN: {{ $jobwork->party->gstin ?? '' }}
                              <span style="float:right;">PAN:{{ $jobwork->party->pan ?? substr($jobwork->party->gstin ?? '', 2, 10) }}</span>
                           </p>
                        </div>
                     </td>

                     <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                        @php
                           $useShipping = $type == 'finished' && !empty($jobwork->job_work_in_id);
                           $hasShipping = $useShipping && (
                              !empty(trim($jobwork->shipping_name)) ||
                              !empty(trim($jobwork->shipping_address)) ||
                              !empty(trim($jobwork->shipping_gst))
                           );
                           $shippingAccount = null;
                           if($hasShipping && is_numeric($jobwork->shipping_name)){
                              $shippingAccount = \App\Models\Accounts::find($jobwork->shipping_name);
                           }
                        @endphp

                        <p style="margin:0; position:absolute; top:0; left:5px; font-style:italic;"><strong>Shipped to :</strong></p>
                        <div style="padding-top:16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin:2px 0 0 0; line-height:13px; font-weight:800;">
                              @if($hasShipping && $shippingAccount)
                                 {{ $shippingAccount->account_name ?? '-' }}<br>
                                 {{ $shippingAccount->address ?? '' }} {{ $shippingAccount->address2 ?? '' }} {{ $shippingAccount->address3 ?? '' }}
                              @elseif($hasShipping)
                                 {{ $jobwork->shipping_name ?? '-' }}<br>
                                 {{ $jobwork->shipping_address ?? '' }}
                              @else
                                 {{ $jobwork->party->account_name ?? '-' }}<br>
                                 {{ $jobwork->party->address ?? '' }} {{ $jobwork->party->address2 ?? '' }} {{ $jobwork->party->address3 ?? '' }}
                              @endif
                           </p>
                        </div>
                        <div style="position:absolute; bottom:0; left:5px; right:4px;">
                           <p style="margin:2px 0 0 0; font-weight:800;">
                              @if($hasShipping && $shippingAccount)
                                 GSTIN/UIN: {{ $shippingAccount->gstin ?? '' }}
                                 <span style="float:right;">PAN:{{ $shippingAccount->pan ?? substr($shippingAccount->gstin ?? '', 2, 10) }}</span>
                              @elseif($hasShipping)
                                 GSTIN/UIN: {{ $jobwork->shipping_gst ?? '' }}
                                 <span style="float:right;">PAN:{{ substr($jobwork->shipping_gst ?? '', 2, 10) }}</span>
                              @else
                                 GSTIN/UIN: {{ $jobwork->party->gstin ?? '' }}
                                 <span style="float:right;">PAN:{{ $jobwork->party->pan ?? substr($jobwork->party->gstin ?? '', 2, 10) }}</span>
                              @endif
                           </p>
                        </div>
                     </td>
                  </tr>

                  <tr>
                     <th style="width:2%; padding:0px 3px;">S. No.</th>
                     <th colspan="2" style="text-align:left; width:30%;">Description of Goods</th>
                     <th style="text-align:center; width:3%;">HSN/SAC Code</th>
                     <th style="text-align:right; width:11%;">Qty.</th>
                     <th style="text-align:center; width:2%;">Unit</th>
                     <th style="text-align:right; width:12%;">Price</th>
                     <th style="text-align:right; width:15%;">Amount (₹)</th>
                  </tr>

                  @php $i = 1; $item_total = 0; $qty_total = 0; @endphp

                  @forelse($jobwork->descriptions ?? [] as $desc)
                  @php
                     $item_total += floatval($desc->amount ?? 0);
                     $qty_total  += floatval($desc->qty ?? 0);
                  @endphp

                  <tr>
                     <td style="text-align:center;">{{ $i }}</td>

                     <td colspan="2" style="text-align:left;">
                        <strong>{{ $desc->item->p_name ?? '' }}</strong>

                        @if($configuration && $configuration->show_item_name == 1 && !empty($desc->item))
                           <span style="font-size:9px; color:#777; margin-left:4px;">
                              ({{ $desc->item->name }})
                           </span>
                        @endif

                        @if(isset($desc->lines) && count($desc->lines) > 0)
                           @foreach($desc->lines as $line)
                              <small style="display:block; font-size:10px; font-style:italic; color:#555; margin-left:10px;">
                                 {{ $line->line_text }}
                              </small>
                           @endforeach
                        @endif
                     </td>

                     <td style="text-align:center;">{{ $desc->item->hsn_code ?? $desc->hsn_code }}</td>
                     <td style="text-align:right;">{{ $desc->qty ?? 0 }}</td>
                     <td style="text-align:center;">{{ $desc->unit }}</td>
                     <td style="text-align:right;">{{ number_format($desc->price ?? 0, 2) }}</td>
                     <td style="text-align:right;">{{ number_format($desc->amount ?? 0, 2) }}</td>
                  </tr>

                  @php $i++; @endphp
                  @empty
                  <tr><td colspan="8" style="text-align:center; padding:20px;">No items found</td></tr>
                  @endforelse

                  @php $tRows = max(0, 10 - ($i - 1)); @endphp
                  @while($tRows >= 0)
                     <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     @php $tRows--; @endphp
                  @endwhile
                  <tr>
                     <td colspan="4" style="border-bottom:0; border-right:0"></td>
                     <td style="border-bottom:0; border-left:0; border-right:0; text-align:right;"><strong>{{ $qty_total }}</strong></td>
                     <td style="border-bottom:0; border-left:0; border-right:0"></td>
                     <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                     <td style="text-align:right; border-bottom:0;">{{ number_format($item_total, 2) }}</td>
                  </tr>

                  <tr>
                     <td colspan="7" style="text-align:right; border-right:0; border-bottom:0;">
                        <p><strong>Grand Total ₹</strong></p>
                     </td>
                     <td style="text-align:right;">
                        <p><strong class="invoice-total">{{ number_format($item_total, 2) }}</strong></p>
                     </td>
                  </tr>

                  <tr>
                     <td colspan="8" style="border-top:0;">
                        <strong>
                           <?php
                           $number = $item_total;
                           $no = floor($number);
                           $point = round($number - $no, 2) * 100;
                           $hundred = null;
                           $digits_1 = strlen($no);
                           $iw = 0;
                           $str = array();
                           $words = array(
                              '0' => '', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four',
                              '5' => 'five', '6' => 'six', '7' => 'seven', '8' => 'eight', '9' => 'nine',
                              '10' => 'ten', '11' => 'eleven', '12' => 'twelve', '13' => 'thirteen',
                              '14' => 'fourteen', '15' => 'fifteen', '16' => 'sixteen',
                              '17' => 'seventeen', '18' => 'eighteen', '19' => 'nineteen',
                              '20' => 'twenty', '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
                              '60' => 'sixty', '70' => 'seventy', '80' => 'eighty', '90' => 'ninety'
                           );
                           $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
                           while ($iw < $digits_1) {
                              $divider = ($iw == 2) ? 10 : 100;
                              $number = floor($no % $divider);
                              $no = floor($no / $divider);
                              $iw += ($divider == 10) ? 1 : 2;
                              if ($number) {
                                 $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                                 $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                                 $str[] = ($number < 21) ? $words[$number]." ".$digits[$counter].$plural." ".$hundred
                                    : $words[floor($number/10)*10]." ".$words[$number%10]." ".$digits[$counter].$plural." ".$hundred;
                              } else $str[] = null;
                           }
                           $str = array_reverse($str);
                           $result = implode('', $str);
                           echo ucfirst($result) . "Rupees only";
                           ?>
                        </strong>
                     </td>
                  </tr>
                  @if($configuration && $configuration->bank_detail_status == 1 && $configuration->bank)
                  <tr>
                     <td colspan="8">
                        <p>
                           <strong>Bank Details :</strong>
                           <strong>ACCOUNT NAME-</strong>{{ $configuration->bank->name }}<br>
                           <strong>ACCOUNT NO:</strong>{{ $configuration->bank->account_no }} ,
                           <strong>IFSC CODE:</strong>{{ $configuration->bank->ifsc }} ,
                           <strong>BANK NAME:</strong>{{ $configuration->bank->bank_name }},
                           {{ $configuration->bank->branch }}
                        </p>
                     </td>
                  </tr>
                  @endif

                  <tr>
                     <td colspan="4" style="vertical-align: top; padding: 5px;">
                        @if($configuration && $configuration->term_status == 1 && $configuration->terms && count($configuration->terms) > 0)
                           <p style="margin:0;"><small><b>Terms &amp; Conditions</b></small></p>
                           <p style="margin:0;"><small>E.&amp; O.E.</small></p>
                           @php $ti = 1; @endphp
                           @foreach($configuration->terms as $t)
                              <p style="margin:0; line-height:1;"><small>{{ $ti }}. {{ $t->term }}</small></p>
                              @php $ti++; @endphp
                           @endforeach
                        @endif
                     </td>
                     <td colspan="4">
                        <p style="height:40px; margin:0; padding:0;"><small>Receiver's Signature :</small></p>
                        <p style="text-align:right; padding:0; margin:0;"><strong>for {{ $company_data->company_name }}</strong></p>
                        @if($configuration && $configuration->signature_status == 1 && !empty($configuration->signature))
                           <p style="text-align:right; margin:0; padding:0;">
                              <img src="{{ URL::asset('public/images') }}/{{ $configuration->signature }}"
                                   style="max-width:145px; max-height:120px; object-fit:contain;">
                           </p>
                        @else
                           <p style="text-align:right; margin:0; padding:0; width:145px; height:70px;"></p>
                        @endif
                        <p style="text-align:right; margin:0; padding:0;"><strong>Authorised Signatory</strong></p>
                     </td>
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