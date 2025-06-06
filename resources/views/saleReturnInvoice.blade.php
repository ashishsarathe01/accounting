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
      border:1px solid #dadada;
      margin: 0;
      padding: 2px 15px;
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
      width: 50px;
      height: 92px;
      overflow: hidden;
      position: absolute;
      margin-top: 26px;
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
   @page { size: auto;  margin: 0mm; }
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center noprint">
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Sale Return</a>
                     </li>
                  </ol>
               </nav>   
               <div class="d-md-flex d-block ">
                  <div class="calender-administrator my-2 my-md-0  w-min-230">
                     <a href="{{ route('sale-return.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     <button class="btn btn-info" onclick="window.print()">Print</button>
                  </div>
               </div>            
            </div>
            <table>
               <tbody>
                  <tr>
                     <th colspan="8">
                        <div style="width:auto; float:left; text-align:left;"><h4 style="margin-top:0; margin-bottom: 0px;"> GSTIN : {{$company_data->gst}}</h4></div>
                        <div class="bil_logo">
                           <img src="https://www.kraftpaperz.com/images/logo.png" alt="kraftpaperz">
                        </div>
                        <div style="width:auto; float:right; text-align:right;"><small>O/D/T</small></div>
                        <div style="clear:both"></div>
                        <p style="margin-top:0;" class="text-center">(Input TAX Credit is available to a taxable person against this copy)</p>
                        <p style="margin-top:0;" class="text-center"><u>SALE RETURN </u></p>
                        <h1 style="margin:0px" class="text-center">{{$company_data->company_name}}</h1>
                        <p class="text-center"><small style="font-size: 13px;">{{$company_data->address}},{{$company_data->sname}},{{$company_data->pin_code}}</small></p>
                     </th>
                  </tr> 
                     <!-- <tr>
                        <td colspan="8">               
                           <img src="" style="float: right;width: 90px;height: 90px;position: relative;">
                           <p>IRN NO. : 2c8e0befc2f68623add91ef6512a392220a4b362e580cdedd0a58afce9f968eb</p>
                           <p>ACK.NO. : 172414735996048</p>
                           <p>ACK.DATE : 2024-04-04 16:13:00</p>
                        </td>
                     </tr> -->                                                    
                     <tr>
                        <td colspan="4">
                           <p><span class="width25">Party Details :  </span></p>
                           <p><span class="width25">{{$sale_return->billing_name}} </span></p>
                           <p>{{$sale_return->billing_address}},{{$sale_return->sname}}</p>
                           <p>{{$sale_return->billing_pincode}}</p>
                           <p>&nbsp;</p>
                           <p>&nbsp;</p>
                           <p>GSTIN / UIN : {{$sale_return->billing_gst}}</p>
                        </td>
                        <td colspan="4">
                           <p><span class="width25">Cr. Note No </span>: <span class="lft_mar15">{{$sale_return->sr_series_no}}/{{$sale_return->sr_financial_year}}/CR{{$sale_return->sale_return_no}}</span> </p>
                           <p><span class="width25">Cr. Note Date </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($sale_return->date))}}</span> </p>
                           <p><span class="width25">Org. Inv. No. </span>: <span class="lft_mar15">{{$sale_return->series_no}}/{{$sale_return->financial_year}}/{{$sale_return->voucher_no}}</span> </p>
                           <p><span class="width25">Org. Inv. Date </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($sale_return->sale_date))}}</span> </p>
                           <p><span class="width25">Transport </span>: <span class="lft_mar15">{{$sale_return->transport_name}}</span> </p>
                           <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{$sale_return->vehicle_no}}</span> </p>
                           <p><span class="width25">Station </span>: <span class="lft_mar15">{{$sale_return->station}}</span> </p>
                           <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{$sale_return->gr_pr_no}}</span> </p>
                        </td>
                     </tr>
                     <tr>
                        <th style="width:5%;">S.N.</th>
                        <th colspan="2" style="text-align:left;">Description of Goods</th>
                        <th style="text-align:left; width:10%;">HSN/SAC Code</th>
                        <th style="text-align:left">Qty.</th>
                        <th style="text-align:left">Unit</th>
                        <th style="text-align:right">Price</th>
                        <th style="text-align:right">Amount(₹)</th>
                     </tr>
                     @php $i=1;$item_total = 0; @endphp
                     @foreach($items_detail as $item)
                        <tr>
                           <td style="text-align:left">{{$i}}</td>
                           <td colspan="2" style="text-align:left">{{$item->items_name}}</td>
                           <td style="text-align:left">{{$item->hsn_code}}</td>
                           <td style="text-align:right">{{$item->qty}}</td>
                           <td style="text-align:left">{{$item->unit}}</td>
                           <td style="text-align:right;">{{$item->price}}</td>
                           <td style="text-align:right;">{{number_format($item->amount,2)}}</td>
                        </tr>
                        @php $i++;$item_total = $item_total + $item->amount; @endphp
                     @endforeach                     
                     <tr style="height: 23px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     <tr style="height: 23px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     <tr style="height: 23px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     <tr style="height: 23px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     <tr>
                        <td colspan="6" style="border-bottom:0; border-right:0"></td>
                        <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                        <td style="text-align:right; border-bottom:0;">{{number_format($item_total,2)}} </td>
                     </tr>
                     <tr>
                        <td style="border-right:0; border-top:0;" colspan="2"></td>
                        <td colspan="4" style="border-left:0; border-right:0; border-top:0;">
                           @foreach($sale_sundry as $sundry)
                           <p>Add : {{$sundry->name}} </p>
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
                           <p>{{number_format($sundry->amount,2)}}</p>
                           @endforeach
                        </td>
                     </tr>                                
                     <tr>
                        <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0">
                           <p><strong>Grand Total ₹</strong></p>
                        </td>                                     
                        <td style="text-align:right">
                           <p><strong>{{number_format($sale_return->total,2)}}</strong></p>
                        </td>
                     </tr>
                     <tr>
                     <td colspan="8" style="border-top:0; border-bottom:0;">
                        @foreach($gst_detail as $val)
                           <span><u><small>Tax Rate</small></u><br>
                              <small>{{$val->rate}}%</small>
                           </span>
                           <span class="mar_lft10"><u><small>Taxable Amount</small></u><br>
                              <small>{{number_format($val->taxable_amount,2)}}</small>
                           </span>
                           @if(Str::limit($company_data->gst,2,'')==Str::limit($sale_return->billing_gst,2,''))
                              <span class="mar_lft10"><u><small>CGST</small></u><br>
                                 <small>{{number_format($val->amount,2)}}</small>
                              </span>
                              <span class="mar_lft10"><u><small>SGST</small></u><br>
                                 <small>{{number_format($val->amount,2)}}</small>
                              </span>
                           @else
                              <span class="mar_lft10"><u><small>IGST</small></u><br>
                                 <small>{{number_format($val->amount,2)}}</small>
                              </span>
                           @endif                        
                           <span class="mar_lft10"><u><small>Total Tax</small></u><br>
                              @if(Str::limit($company_data->gst,2,'')==Str::limit($sale_return->billing_gst,2,''))
                                 <small>{{number_format($val->amount+$val->amount,2)}}</small>
                              @else
                                 <small>{{number_format($val->amount,2)}}</small>
                              @endif
                           </span><br>
                        @endforeach
                     </td>
                  </tr>
                  <tr>
                     <td colspan="8" style="border-top:0">
                        <strong>
                           <?php
                           $number = $sale_return->total;
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
                  <tr>
                     <td colspan="4">
                        <p><small>Terms &amp; Conditions</small></p>
                        <p><small>E.&amp; O.E. </small></p>
                        <p><small>1. Goods once sold will not be taken back. </small></p>
                     </td>
                     <td colspan="4">
                        <p><small>Receiver's Signature :</small></p>
                        <hr>
                        <p style="text-align:right"><strong>for {{$company_data->company_name}}</strong></p><br>
                        <br>
                        <p style="text-align:right"><strong>Authorised Signatory</strong></p>
                     </td>
                  </tr>
               </tbody>
            </table>
            <br>
            <div style="text-align: center;" class="noprint">
               @if($einvoice_status==1)
                  <a href="#" class="btn btn-border border-secondary">GENERATE E-INVOICE</a>
               @endif
            </div>
         </div>                     
      </div>
   </section>
</div>
@include('layouts.footer')