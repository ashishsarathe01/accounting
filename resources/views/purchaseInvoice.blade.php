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
   .importantRule { display: none !important; }
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
                
               <div class="d-md-flex d-block noprint">
                  <div class="calender-administrator my-2 my-md-0  w-min-230 noprint">
                  <button class="btn btn-info" onclick="printpage();">Print</button>
                  </div>
               </div>            
            </div>
            <table>
               <tbody>
                <tr>
    <th colspan="8">
        <div style="width:auto; float:left; text-align:left;">
            <h4 style="margin: 0;">GSTIN : {{$party_detail->gstin}}</h4>
        </div>
        <div class="bil_logo">
            <!-- Logo or any image can be placed here -->
        </div>
        <div style="clear:both"></div>

        <!-- No vertical spacing -->
        <p class="text-center" style="margin: 0;"><u>PURCHASE INVOICE</u></p>

        <h1 class="text-center" style="margin: 0;">{{$party_detail->account_name}}</h1>

        <!-- Centered address with max width and zero margin -->
        <div style="max-width: 60%; margin: 0 auto;">
            <p class="text-center" style="margin: 0;">
                <small style="font-size: 13px;">
                    {{$party_detail->address}},{{$party_detail->sname}},{{$party_detail->pin_code}}
                </small>
            </p>
        </div>
    </th>
</tr>



                                                                         
                     <tr>
                        <td colspan="4">
                           <p><span class="width25">Invoice No. </span>:  <span class="lft_mar15">{{$sale_detail->voucher_no}}</span></p>
                           <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($sale_detail->date))}}</span></p>                           
                        </td>
                        <td colspan="4">                           
                           <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{$sale_detail->vehicle_no}}</span> </p>
                        </td>
                     </tr>
                     <tr>
                        <td colspan="4">
                           <i><p><strong>Billed to :</strong></p></i>
                           <p>{{$company_data->company_name}}<br>{{$company_data->address}},{{$company_data->sname}},{{$company_data->pincode}}</p>
                           <br>
                           <p>GSTIN / UIN : {{$company_data->gst}} </p>
                        </td>
                        <td colspan="4">
                           <i><p><strong>Shipped to :</strong></p></i>
                           @if($sale_detail->shipping_name)
                              <p>{{$sale_detail->shipp_name}}<br>{{$sale_detail->shipping_address}},{{$sale_detail->shipping_state}},{{$sale_detail->shipping_pincode}}</p>
                              <br>
                              <p>GSTIN / UIN : {{$sale_detail->shipping_gst}} </p>
                           @else
                              <p>{{$company_data->company_name}}<br>{{$company_data->address}},{{$company_data->sname}},{{$company_data->pincode}}</p>
                              <br>
                              <p>GSTIN / UIN : {{$company_data->gst}} </p>
                           @endif
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
                              @php
    $addTypes = ['CGST', 'SGST', 'IGST', 'ROUNDED OFF (+)'];
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
                           <p>{{number_format($sundry->amount,2)}}</p>
                           @endforeach
                        </td>
                     </tr>                                
                     <tr>
                        <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0">
                           <p><strong>Grand Total ₹</strong></p>
                        </td>                                     
                        <td style="text-align:right">
                           <p><strong>{{number_format($sale_detail->total,2)}}</strong></p>
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
                           @if(Str::limit($company_data->gst,2,'')==Str::limit($party_detail->gstin,2,''))
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
                              @if(Str::limit($company_data->gst,2,'')==Str::limit($party_detail->gstin,2,''))
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
               </tbody>
            </table>
         </div>                     
      </div>
   </section>
</div>
@include('layouts.footer')

<script>
   function printpage(){
      $('.header-section').addClass('importantRule');
      window.print();
      $('.header-section').removeClass('importantRule');
   }
</script>