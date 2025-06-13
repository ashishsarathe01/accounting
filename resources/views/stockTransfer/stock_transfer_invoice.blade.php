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
   @page { 
   size: A4;        /* Always A4 size page (210mm x 297mm) */
   margin: 5mm;     /* Outer margin around content */
}

.importantRule { 
   display: none !important;  /* Force hide anything with this class */
}

p {
   margin: 0.5px !important;  /* Almost zero vertical space between paragraphs */
}
</style>

<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
               <div class="d-md-flex justify-content-between py-4 px-2 align-items-center header-section">
               <div class="d-md-flex d-block noprint"> 
                  <div class="calender-administrator my-2 my-md-0  w-min-230 noprint">
                       <button type="button" class="btn btn-danger" onclick="redirectBack()">QUIT</button>
                     <button class="btn btn-info" onclick="printpage();">Print</button>
                      <?php 
                    if ( in_array(date('Y-m', strtotime($stock_transfer->transfer_date)), $month_arr) && $stock_transfer->e_waybill_status == 0 ) {?>
                        <a href="{{ URL::to('stock-transfer/'.$stock_transfer->id.'/edit') }}" class="btn btn-primary text-white">
                           <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg') }}" alt="Edit" style="width: 16px; height: 16px; vertical-align: middle; filter: brightness(0) invert(1);">
                           Edit
                        </a><?php 
                     } ?>
                  </div>
               </div>            
         </div><br>
         <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;">
            <tbody>
               <tr>
                  <th colspan="8" style="padding: 0;">
                     <div style="min-height: 120px; position: relative;">
                        <div style="width:auto; float:left; text-align:left;">
                           <strong style="margin:0;">GSTIN: {{ $from_series_info->gst_no }}</strong>
                        </div>
                        @php
                           $companyName = $company_data->company_name;
                           $fontSize = strlen($companyName) > 30 ? '18px' : '24px';
                        @endphp
                        <div class="bil_logo" style="float: left; margin-left: 10px;">
                           @if($configuration && $configuration->company_logo_status==1 && !empty($configuration->company_logo))
                              <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}" alt="My Logo" style="max-height: 100%; max-width: 100%; object-fit: contain; display: block;">
                           @endif
                        </div>
                        <div style="width:auto; float:right; text-align:right;">
                           <strong style="margin:0;">PAN: {{ substr($from_series_info->gst_no, 2, 10) }}</strong><br>
                           <small>O/D/T</small>
                        </div>
                        <div style="clear:both;"></div>
                        <div style="text-align:center; line-height:1; margin:0; padding:0;">
                           <p style="margin:0;"><u>DELIVERY CHALLAN</u></p>
                           <p style="margin:0; font-size: {{ $fontSize }}; font-weight: bold;">{{ $companyName }}</p>
                           <p style="margin:0;">
                              <small style="font-size: 12px; display:inline-block; max-width:50%; word-break:break-word;">
                                 {{ $from_series_info->address }}
                              </small>
                           </p>
                           <p style="margin:0;">
                              <small style="font-size: 12px;">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small>
                           </p>
                        </div>
                     </div>
                  </th>
                  </tr>                                                                      
                  <tr>
                     <td colspan="4">
                           <p><span class="width25">Delivery Challan No. </span>:  <span class="lft_mar15">{{$stock_transfer->voucher_no_prefix}}</span></p>
                           <p><span class="width25">Date of D.C. </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($stock_transfer->transfer_date))}}</span></p>
                           <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">{{$stock_transfer->sname}}</span></p>
                           <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15"></span></p>
                           <p>&nbsp;</p>
                     </td>
                     <td colspan="4">
                           <p><span class="width25">Transport </span>: <span class="lft_mar15"></span> </p>
                           <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15"></span> </p>
                           <p><span class="width25">Station </span>: <span class="lft_mar15"></span> </p>
                           <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15">
                           </span> </p>
                           <p>&nbsp;</p>
                        
                     </td>
                  </tr>
                  <tr>
                     <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                        <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                           <strong>Transfer from :</strong>
                        </p>
                        <p style="height:3px;"></p>
                        <div style="padding-top: 16px; margin-left:5px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin: 2px 0 0 0; line-height: 13px;">
                              {{$companyName}}<br>
                              {{$from_series_info->address }}, {{$from_series_info->sname}}, {{$from_series_info->pincode}}
                           </p>
                        </div>
                        <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                           <p style="margin: 2px 0 0 0;">
                              GSTIN/UIN:{{$from_series_info->gst_no}} 
                              <span style="float: right;">PAN:{{substr($from_series_info->gst_no, 2, 10)}}</span>
                           </p>
                        </div>
                     </td>
                     <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                        <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                        <strong>Transfer to :</strong>
                        </p>
                        <p style="height:3px;"></p>
                        <div style="padding-top: 16px; margin-left:5px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin: 2px 0 0 0; line-height: 13px;">
                              {{$companyName}}<br>
                              {{$to_series_info->address}}, {{$to_series_info->sname}}, {{$to_series_info->pincode}}
                           </p>
                        </div>
                        <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                           <p style="margin: 2px 0 0 0;">
                                 GSTIN/UIN:{{$to_series_info->gst_no}} 
                              <span style="float: right;">PAN:{{substr($to_series_info->gst_no, 2, 10)}}</span>
                           </p>
                        </div>
                     </td>
                  </tr>
                  <tr>
                     <th style="width:2%;padding: 0px 3px;">S. No.</th>
                     <th colspan="2" style="text-align:left; width:30%;">Description of Goods</th>
                     <th style="text-align:center; width:3%;">HSN/SAC Code</th> <!-- Centered SAC Code --> 
                     <th style="text-align:right; width:11%;">Qty.</th>
                     <th style="text-align:center; width:2%;">Unit</th>
                     <th style="text-align:right; width:12%;">Price</th>
                     <th style="text-align:right; width:15%;">Amount (₹)</th>
                  </tr>
                  @php $i=1;$item_total = 0; @endphp
                  @foreach($items_detail as $item)
                     <tr>
                        <td style="text-align:center;">{{$i}}</td>
                        <td colspan="2" style="text-align:left">{{$item->items_name}}</td>
                        <td style="text-align:center;">{{$item->hsn_code}}</td>
                        <td style="text-align:right">{{$item->qty}}</td>
                        <td style="text-align:center">{{$item->unit}}</td>
                        <td style="text-align:right;">{{$item->price}}</td>
                        <td style="text-align:right;">{{number_format($item->amount,2)}}</td>
                     </tr>
                     @php $i++;$item_total = $item_total + $item->amount; @endphp
                  @endforeach
                  @php                       
                  foreach($sundry as $sundry1){
                     if($sundry1->nature_of_sundry=="OTHER"){
                        $i++;
                     }
                  }
                  if($stock_transfer->e_invoice_status==0){
                     $tRows = 10 - $i; 
                  }else{
                     $tRows = 7 - $i; 
                  }                         
                  while($tRows>=0){
                     @endphp  
                        <tr style="height: 23px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     @php 
                     $tRows--; 
                  }
                  @endphp                      
                  <tr>
                     <td colspan="6" style="border-bottom:0; border-right:0"></td>
                     <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                     <td style="text-align:right; border-bottom:0;">{{number_format($item_total,2)}} </td>
                  </tr>
                  <tr>
                     <td style="border-right:0; border-top:0;" colspan="2"></td>
                     <td colspan="4" style="border-left:0; border-right:0; border-top:0;">
                           @php
                           $addTypes = ['CGST', 'SGST', 'IGST', 'ROUNDED OFF (+)'];
                           $lessTypes = ['ROUNDED OFF (-)'];
                            
                           
                        @endphp
                        @foreach($sundry as $sundrys)
                           @php
                           
                               
                           @endphp
                           @if($sundrys->nature_of_sundry === 'OTHER')
                              @if($sundrys->bill_sundry_type === 'additive')
                                 <p>Add : {{ $sundrys->name }}</p>
                              @elseif($sundrys->bill_sundry_type === 'subtractive')
                                 <p>Less : {{ $sundrys->name }}</p>
                              @endif
                           @elseif(in_array($sundrys->nature_of_sundry, $addTypes))
                              <p>Add : {{ $sundrys->name }}</p>
                           @elseif(in_array($sundrys->nature_of_sundry, $lessTypes))
                              <p>Less : {{ $sundrys->name }}</p>
                           @endif
                        @endforeach                           
                     </td>
                     <td style="border-left:0; border-top:0;">
                        @foreach($sundry as $row)
                        @php
                              
                           @endphp
                           <p>@if($row->rate!=0) {{$row->rate}} % @else &nbsp; @endif</p>
                        @endforeach
                     </td>
                     <td style="text-align:right; border-top:0;">
                        @foreach($sundry as $row)
                         @php
                             
                           @endphp
                        <p>{{number_format($row->amount,2)}}</p>
                        @endforeach
                     </td>
                  </tr>                                
                  <tr>
                     <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0">
                        <p><strong>Grand Total ₹</strong></p>
                     </td>                                     
                     <td style="text-align:right">
                        <p><strong>{{number_format($stock_transfer->grand_total,2)}}</strong></p>
                     </td>
                  </tr>
                  <tr>
                     <td colspan="8" style="border-top:0; border-bottom:0;">
                        <strong>Stock Transfer</strong> 
                     </td>
               </tr>
               <tr>
                  <td colspan="8" style="border-top:0">
                     <strong>
                        <?php
                        $number = $stock_transfer->grand_total;
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
                     <hr style="margin:0; padding:0;">
                     <p style="text-align:right; padding:0; margin:0;"><strong>for {{$company_data->company_name}}</strong></p>
                     @if($configuration && !empty($configuration->signature))
                        <p style="text-align:right; margin:0; padding:0;">
                           <img src="{{ URL::asset('public/images')}}/{{$configuration->signature}}" style="width: 145px; height:70px;">
                        </p>
                        @else
                           <p style="text-align:right; margin:0; padding:0;width: 145px; height:70px;"></p>
                     @endif
                     <p style="text-align:right; margin:0; padding:0;"><strong>Authorised Signatory</strong></p>
                  </td>
               </tr>
            </tbody>
         </table>                        
      </div> 
   </section>
</div>
@include('layouts.footer')
<script> 

function redirectBack() {
    const previousUrl = document.referrer;
   const sessionPreviousUrl = "{{ session('previous_url_stock_transfer') }}";
    const sessionPreviousSaleEditUrl = "{{ session('previous_url_stock_transfer_edit') }}";

    // If referrer matches session URLs → redirect to /sale
    if (previousUrl === sessionPreviousUrl || previousUrl === sessionPreviousSaleEditUrl) {
        window.location.href = "{{ url('stock-transfer') }}";
    } else {
        // Try going back in history
        if (window.history.length > 1) {
            history.back();
        } else {
            // No history → redirect to /stock-transfer.index
            window.location.href = "{{ url('stock-transfer') }}";
        }
    }
}

   function printpage(){
      $('.header-section').addClass('importantRule');
      window.print();
      $('.header-section').removeClass('importantRule');
   }
</script>
@endsection