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
      width: 120px;
      height: 87px;
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
                    <div class="d-md-flex d-block ">
                        <div class="calender-administrator my-2 my-md-0  w-min-230 noprint">
                            <a href="{{ route('purchase-return.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                            <button class="btn btn-info" onclick="printpage();">Print</button>
                             <?php 
                    if ( in_array(date('Y-m', strtotime($purchase_return->date)), $month_arr) && $purchase_return->e_invoice_status == 0 && $purchase_return->e_waybill_status == 0) {?>
                        <a href="{{ URL::to('purchase-return-edit/'.$purchase_return->id) }}" class="btn btn-primary text-white">
                           <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg') }}" alt="Edit" style="width: 16px; height: 16px; vertical-align: middle; filter: brightness(0) invert(1);">
                           Edit
                        </a><?php 
                     } ?>
                        </div>
                    </div>            
                </div>
                <table>
                    <tbody>
                        <tr>
                             <th colspan="8">
                        <div style="width:auto; float:left; text-align:left;">
                           <strong style="margin:0;">GSTIN: {{$seller_info->gst_no}}</strong>
                        </div>
                        <div class="bil_logo">
                           @if($configuration && $configuration->company_logo_status==1 && !empty($configuration->company_logo))
                              <img src="{{ URL::asset('public/images')}}/{{$configuration->company_logo}}" alt="My Logo">
                           @endif
                        </div>
                        <div style="width:auto; float:right; text-align:right;">
                           <strong style="margin:0;">PAN: {{substr($seller_info->gst_no, 2, 10)}}</strong><br>
                           <small>O/D/T</small>
                        </div>
                        <div style="clear:both;"></div>
                        <div style="text-align:center; line-height:1; margin:0; padding:0;">
                           <p style="margin:0;"><u>DEBIT NOTE</u></p>
                           <p style="margin:0; font-size: 24px; font-weight: bold;">{{$company_data->company_name}}</p>
                           <p style="margin:0;">
                              <small style="font-size: 12px; display:inline-block; max-width:50%; word-break:break-word;">
                                 {{$seller_info->address}},{{$seller_info->pincode}}
                              </small>
                           </p>
                           <p style="margin:0;">
                              <small style="font-size: 12px;">Phone: {{$company_data->mobile_no}} &nbsp; Email: {{$company_data->email_id}}</small>
                           </p>
                        </div>
                     </th>
                        </tr>                                          
                        <tr>
                            <td colspan="4" style="width:50%;">
                                <p><span class="width25">Party Details :  </span></p>
                                <p><span class="width25">{{$purchase_return->account_name}} </span></p>
                                <p>{{$purchase_return->address}},{{$purchase_return->sname}}</p>
                                <p>{{$purchase_return->pin_code}}</p>
                                <p>&nbsp;</p>
                                <p>GSTIN / UIN : {{$purchase_return->gstin}}</p>
                            </td>
                        <td colspan="4" style="width:50%;">
                           <p><span class="width25">Dr. Note No </span>: <span class="lft_mar15">{{$purchase_return->sr_prefix}}</span> </p>
                           <p><span class="width25">Dr. Note Date </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($purchase_return->date))}}</span> </p>
                           <p>&nbsp;</p>
                           <p>&nbsp;</p>
                           <p>&nbsp;</p>
                           <p>&nbsp;</p>
                           <p>&nbsp;</p>
                        </td>
                     </tr>
                     <tr>
                        <th colspan="1" style="width:5%;">S.N.</th>
                        <th colspan="4" style="text-align:left;">Description of Goods</th>
                        <th colspan="1" style="text-align:left; width:10%;">HSN/SAC Code</th>
                        <th colspan="2" style="text-align:right">Amount(₹)</th>
                     </tr>
                     @php $i=1;$item_total = 0; $tax_arr = [];@endphp
                     @foreach($items as $item)
                        <tr>
                           <td colspan="1"style="text-align:left">{{$i}}</td>
                           <td colspan="4" style="text-align:left">{{$item->account_name}}</td>
                           <td colspan="1"style="text-align:left">{{$item->hsn_code}}</td>
                           <td colspan="2"style="text-align:right;">{{number_format($item->debit,2)}}</td>
                        </tr>
                        @php $i++;$item_total = $item_total + $item->debit; array_push($tax_arr,array("percentage"=>$item->percentage,"amount"=>$item->debit)); @endphp
                     @endforeach 
                         <tr style="height: 23px;"><td colspan="1"></td><td colspan="4"></td><td colspan="1"></td><td colspan="2" ></td></tr>
                     <tr style="height: 23px;"><td colspan="1"></td><td colspan="4"></td><td colspan="1"></td><td colspan="2" ></td></tr>
                     <tr style="height: 23px;"><td colspan="1"></td><td colspan="4"></td><td colspan="1"></td><td colspan="2" ></td></tr>
                     <tr style="height: 23px;"><td colspan="1"></td><td colspan="4"></td><td colspan="1"></td><td colspan="2" ></td></tr>  
                     <tr>
                         <td colspan="3" style="border-bottom:0; border-right:0"></td>
                        <td colspan="3" style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                        <td colspan="2"style="text-align:right; border-bottom:0;">{{number_format($item_total,2)}} </td>
                     </tr>
                     <tr>
                              <td style="border-right:0; border-top:0;width:5%" colspan="1"></td>
                        <td colspan="4" style="border-left:0; border-right:0; border-top:0;text-align:right;">
                           @php 
                              
                              $return = array();
                              foreach($tax_arr as $val) {
                                 $return[$val['percentage']][] = $val;
                              } 
                           @endphp
                            @foreach($return as $k=>$item)
                                @if($purchase_return->tax_cgst!='' && $purchase_return->tax_sgst!='')
                                <p><strong>Add : </strong> CGST</p>
                                <p><strong>Add : </strong> SGST</p>
                                @elseif($purchase_return->tax_igst!='')
                                <p><strong>Add : </strong> IGST</p>
                                @endif
                            @endforeach                                             
                        </td>
                        <td colspan="1" style="border-left:0; border-top:0;text-align:center;">
                              @foreach($return as $k=>$item)
                                @if($purchase_return->tax_cgst!='' && $purchase_return->tax_sgst!='')
                                 <p>{{$k/2}}%</p>
                                 <p>{{$k/2}}%</p>
                                @elseif($purchase_return->tax_igst!='')
                                 <p>{{$k}}%</p>
                                @endif
                            @endforeach 
                        </td>
                        <td colspan="2"style="text-align:right; border-top:0;">
                              @foreach($return as $k=>$item)
                                 @php $taxable_amount = 0; @endphp
                                 @foreach($item as $amount)
                                    @php $taxable_amount = $taxable_amount + $amount['amount']; @endphp
                                 @endforeach
                                 
                                @if($purchase_return->tax_cgst!='' && $purchase_return->tax_sgst!='')
                                 <p>@php echo number_format(($taxable_amount*($k/2))/100,2) @endphp</p>
                                 <p>@php echo number_format(($taxable_amount*($k/2))/100,2) @endphp</p>
                                @elseif($purchase_return->tax_igst!='')
                                <p>@php echo number_format(($taxable_amount*$k)/100,2) @endphp</p>
                                @endif
                            @endforeach
                        </td>
                     </tr>
                     <tr>
                          <td colspan="6" style="text-align:right; border-right: 0; border-bottom: 0">
                           <p><strong>Grand Total ₹</strong></p>
                        </td>                                     
                        <td colspan="2" style="text-align:right">
                           <p><strong>{{number_format($purchase_return->total,2)}}</strong></p>
                        </td>
                     </tr>
                      <tr>
                           <td colspan="8" style="border-top: 1px solid #dadada;"><strong>Narration : </strong>{{$purchase_return->remark}}</td>
                  </tr>
                     <tr>
                        <td colspan="8" style="border-top:0">
                            <strong>
                            <?php
                            $number = $purchase_return->total;
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
      <p style="text-align:right; margin:0; padding:0;width: 145px; height:70px;">
          </p>
   @endif

   <p style="text-align:right; margin:0; padding:0;"><strong>Authorised Signatory</strong></p>
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