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
                     <button class="btn btn-info" onclick="printpage();">Print Bill</button>
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
                     <tr>
                        <td colspan="8">    
                        <span style="float: right;width: 90px;height: 90px;position: relative;">
                            @php $qrContent = "2a694803c9b77900bccfbcbd0052aff3519521f1e649877fca5c22f3e4d9692a"; @endphp
                            {!! QrCode::size(90)->generate($qrContent) !!}
                           </span> 
                           <p>IRN NO. : 2a694803c9b77900bccfbcbd0052aff3519521f1e649877fca5c22f3e4d9692a</p>
                           <p>ACK.NO. : 132626529872069</p>
                           <p>ACK.DATE : {{date('d-m-Y')}}</p>
                        </td>
                     </tr>                                                  
                     <tr>
                        <td colspan="4">
                           <p><span class="width25">Invoice No. </span>:  <span class="lft_mar15" style="font-weight:800">KP/26-27/001</span></p>
                           <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{date('d-m-Y')}}</span></p>
                           <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">DELHI</span></p>
                           <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15"></span></p>
                           <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15"></span></p>
                           <!-- <p>&nbsp;</p> -->
                        </td>
                        <td colspan="4">
                           <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text">DELHI GOODS CARRIER</span> </p>
                           <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">DL01D575</span> </p>
                           <p><span class="width25">Station </span>: <span class="lft_mar15"></span> </p>
                           <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15">64847383</span> </p>
                           <p>&nbsp;</p>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="4" style="position: relative; vertical-align: top; padding: 0;">

                        <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                           <strong>Billed to :</strong>
                        </p>

                        <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                           <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">
                              ABC Enterprise
                           </p>
                           <p style="margin: 2px 0 0 0; line-height: 13px;">
                              GALI NO5, MATIALA EXTENSION,UTTAM NAGAR,Delhi
                           </p>
                        </div>

                        <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                           <p style="margin: 2px 0 0 0;font-weight:800">
                              GSTIN/UIN : 07CAUPS4081B1ZE 
                              <span style="float: right;">PAN:CAUPS4081B</span>
                           </p>
                        </div>
                    </td>

                    <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">

                    <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                       <strong>Shipped to :</strong>
                    </p>
                    
                    <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                       <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">
                          ABC Enterprise
                       </p>
                       <p style="margin: 2px 0 0 0; line-height: 13px;">
                          GALI NO5, MATIALA EXTENSION,UTTAM NAGAR,Delhi
                       </p>
                    </div>

                     <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                        <p style="margin: 2px 0 0 0;font-weight:800">
                              GSTIN/UIN : 07CAUPS4081B1ZE
                              <span style="float: right;">PAN:CAUPS4081B</span>
                           
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
                        <tr>
                           <td style="text-align:center;">1</td>
                           <td colspan="2" style="text-align:left;">
                                <strong>KRAFT PAPER (0-150 GSM)</strong>
                            </td>
                           <td style="text-align:center;">48043100</td>
                           <td style="text-align:right">1961.00</td>
                           <td style="text-align:center">KG</td>
                           <td style="text-align:right;">34.1</td>
                           <td style="text-align:right;">{{formatIndianNumber(66870.10)}}</td>
                        </tr>
                        <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                        <tr style="height: 21px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     <tr>
                        <td colspan="4" style="border-bottom:0; border-right:0"></td>
                        <td style="border-bottom:0; border-left:0;border-right:0;text-align: right;"><strong>1961</strong></td>
                        <td style="border-bottom:0; border-left:0;border-right:0"><strong></strong></td>
                        <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                        <td style="text-align:right; border-bottom:0;">{{formatIndianNumber(66870.10)}} </td>
                     </tr>
                     <tr>
                        <td style="border-right:0; border-top:0;" colspan="2"></td>
                        <td colspan="4" style="border-left:0; border-right:0; border-top:0;">
                            <p>Add : Freight & Forwarding Charges</p>
                            <p>Add : CGST</p>
                            <p>Add : SGST</p>
                            <p>Add : Rounded Off (+)</p>
                        </td>
                        <td style="border-left:0; border-top:0;">
                            <p></p>
                            <p> 9 % </p>
                            <p> 9 % </p>
                            <p></p>
                        </td>
                        <td style="text-align:right; border-top:0;">
                           <p>700.00</p>
                           <p>6,081.31</p>
                           <p>6,081.31</p>
                           <p>0.28</p>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0;">
                           <p><strong>Grand Total ₹</strong></p>
                        </td> 
                       
                        <td style="text-align:right">
                           <p><strong class="invoice-total">{{formatIndianNumber(79733.00)}}</strong></p>
                        </td>
                     </tr>
                     <tr>
                     <td colspan="8" style="border-top:0; border-bottom:0;">
                        
                       
                           <span><u><small>Tax Rate</small></u><br>
                              <small>18%</small>
                           </span>
                           <span class="mar_lft10"><u><small>Taxable Amount</small></u><br>
                              <small>{{formatIndianNumber(67570.1)}}</small>
                           </span>
                           
                              <span class="mar_lft10"><u><small>CGST</small></u><br>
                                 <small>6,081.31</small>
                              </span>
                              <span class="mar_lft10"><u><small>SGST</small></u><br>
                                 <small>6,081.31</small>
                              </span>
                                                 
                           <span class="mar_lft10"><u><small>Total Tax</small></u><br>
                              
                                 <small>{{formatIndianNumber(12162.62)}}</small>
                           </span><br>
                        
                     </td>
                  </tr>
                  <tr>
                     <td colspan="8" style="border-top:0">
                        <strong>
                           <?php
                           $number = 12345;
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
           
            
         </div>                     
      </div>
   </section>
</div>

<!-- Hidden Challan Print Section -->



@include('layouts.footer')
<script> 



   
   
   function printpage(){
       //$('.sidebar').addClass('importantRule'); // only sidebar hide
       window.print();
       //$('.sidebar').removeClass('importantRule');
    }

   

</script>
@endsection
