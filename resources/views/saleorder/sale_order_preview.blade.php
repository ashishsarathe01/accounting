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
            <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
                
                
                <a href="{{ url('sale/create') . '?' . request()->getQueryString() }}" style="text-align: center">
                    <button class="btn btn-primary noprint" >Next</button>
                </a><br><br>
                @php $total_qunatity = 0;$to_pay_freight_amount = 0; @endphp
                @foreach($item_arr as $item)
                    @php $total_qunatity += $item['total_weight']; @endphp
                @endforeach
                
                @if (request('vehicle_info_type')=="to_pay")
                    @foreach($item_arr as $item)
                        @php 
                            $item_price = $item['price'];
                            if(request('vehicle_info_type')=="to_pay" && request('to_pay_other_charges')!=""){
                                $other_charges = request('to_pay_other_charges') / $total_qunatity;
                                $other_charges = round($other_charges, 2);
                                $item_price = request('to_pay_freight') + $other_charges  ;
                            }else{
                                $item_price = request('to_pay_freight');
                            }
                            $to_pay_freight_amount = $to_pay_freight_amount + ($item['total_weight'] * $item_price);
                        @endphp
                    @endforeach
                    Freight : {{request('vehicle_info')}}<br>
                    To Pay Price : {{request('to_pay_freight')}} <br>
                    To Pay Other Charges : {{request('to_pay_other_charges')}} <br>
                    To Pay Freight Amount : {{$to_pay_freight_amount}}
                @endif
                @if (request('vehicle_info_type')=="vehicle")
                    {{request('vehicle_transporter')}}<br>
                    Vehicle Freight : {{request('vehicle_freight')}} <br>
                @endif
                @if (request('vehicle_info_type')=="transporter")
                    
                    {{request('vehicle_transporter')}}<br>
                    Transporter Freight : {{request('transporter_freight')}} <br>
                    Transporter Other Charges : {{request('transporter_other_charges')}} <br>
                    Transporter Paid Amount : {{round(($total_qunatity*request('transporter_freight'))+request('transporter_other_charges'))}} <br>
                @endif
                <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;">
                    <tbody>
                        <tr>
                            <th colspan="8" style="padding: 0;">
                                <div style="min-height: 120px; position: relative;">
                                    <div style="width:auto; float:left; text-align:left;">
                                        <strong style="margin:0;">GSTIN: {{ $bill_to->gst_no }}</strong>
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
                                        <strong style="margin:0;">PAN: {{ substr($bill_to->gst_no, 2, 10) }}</strong><br>
                                        <small>O/D/T</small>
                                    </div>
                                    <div style="clear:both;"></div>
                                    <div style="text-align:center; line-height:1; margin:0; padding:0;">
                                        <p style="margin:0;"><u>TAX INVOICE</u></p>
                                        <p style="margin:0; font-size: {{ $fontSize }}; font-weight: bold;">
                                            {{ $companyName }}
                                        </p>
                                        <p style="margin:0;">
                                            <small style="font-size: 12px; display:inline-block; max-width:50%; word-break:break-word;">
                                                {{ $bill_to->address }}
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
                                <p><span class="width25">Invoice No. </span>:  <span class="lft_mar15" style="font-weight:800"></span></p>
                                <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{date('d-m-Y')}}</span></p>
                                <p><span class="width25">Place of Supply </span>: <span class="lft_mar15"></span></p>
                                <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15"></span></p>
                                <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15"></span></p>
                                <!-- <p>&nbsp;</p> -->
                            </td>
                            <td colspan="4">
                                <p><span class="width25">Transport </span>: <span class="lft_mar15 wrap-text"></span> </p>
                                <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15"></span> </p>
                                <p><span class="width25">Station </span>: <span class="lft_mar15"></span> </p>
                                <p><span class="width25">E-Way Bill No. </span>: <span class="lft_mar15">
                                    
                                </span> </p>
                                <p>&nbsp;</p>
                                <!-- <p>&nbsp;</p> -->
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                                <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                                <strong>Billed to :</strong>
                                </p>
                                <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                                    <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">
                                    {{$bill_to->account_name}}<br>
                                    {{$bill_to->address}}
                                    </p>
                                </div>
                                <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                                <p style="margin: 2px 0 0 0;font-weight:800">
                                    GSTIN/UIN : {{$bill_to->gstin}} 
                                    <span style="float: right;">PAN:{{$bill_to->pan}}</span>
                                </p>
                                </div>
                            </td>
                            <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">
                                <p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
                                <strong>Shipped to :</strong>
                                </p>                    
                                <div style="padding-top: 16px; margin-left:10px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
                                <p style="margin: 2px 0 0 0; line-height: 13px;font-weight:800;">
                                    @if($shipp_to->account_name)
                                        {{$shipp_to->account_name}}<br>
                                        {{$shipp_to->address}}
                                    @else
                                        {{$bill_to->account_name}}<br>
                                        {{$bill_to->address}}
                                    @endif
                                </p>
                                </div>
                                <div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
                                <p style="margin: 2px 0 0 0;font-weight:800">
                                    @if($shipp_to->account_name)
                                        GSTIN/UIN : {{$shipp_to->gstin}} 
                                        <span style="float: right;">PAN:{{$shipp_to->pan}}</span>
                                    @else
                                        GSTIN/UIN : {{$bill_to->gstin}} 
                                        <span style="float: right;">PAN:{{$bill_to->pan}}</span>
                                    @endif
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
                        @php 
                            $i=1;$item_total = 0;$qty_total = 0; $grand_total = 0;
                        @endphp                        
                        @foreach($item_arr as $item)
                            @php 
                                $item_price = $item['price'];
                                if(request('vehicle_info_type')=="to_pay" && request('to_pay_other_charges')!=""){
                                    $other_charges = request('to_pay_other_charges') / $total_qunatity;
                                    $other_charges = round($other_charges, 2);                                    
                                    $item_price = $item_price - $other_charges - request('to_pay_freight');
                                }else{
                                    $item_price = $item_price - request('to_pay_freight');
                                }
                            @endphp
                            <tr>
                                <td style="text-align:center;">{{$i}}</td>
                                <td colspan="2" style="text-align:left;">
                                    <strong>{{ $item['item_print_name'] }}</strong>
                                    <span style="font-size:9px; color:#777; margin-left:4px;">
                                        ({{ $item['item_name'] }})
                                    </span>
                                </td>
                                <td style="text-align:center;">{{$item['hsn_code']}}</td>
                                <td style="text-align:right">{{$item['total_weight']}}</td>
                                <td style="text-align:center">{{$item['unit_name']}}</td>
                                <td style="text-align:right;">{{$item_price}}</td>
                                <td style="text-align:right;">{{formatIndianNumber($item['total_weight']*$item_price)}}</td>
                            </tr>
                            @php $i++;$item_total = $item_total + $item['total_weight']*$item_price; $qty_total = $qty_total + $item['total_weight']; $grand_total = $grand_total + $item['total_weight']*$item_price; 
                                
                            @endphp
                        @endforeach
                        <tr>
                            <td colspan="4" style="border-bottom:0; border-right:0"></td>
                            <td style="border-bottom:0; border-left:0;border-right:0;text-align: right;"><strong>{{$qty_total}}</strong></td>
                            <td style="border-bottom:0; border-left:0;border-right:0"><strong></strong></td>
                            <td style="border-bottom:0; border-left:0"><strong>Total</strong></td>
                            <td style="text-align:right; border-bottom:0;">{{formatIndianNumber($item_total)}} </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0;">
                                <p><strong>Grand Total ₹</strong></p>
                            </td> 
                            
                            <td style="text-align:right">
                                <p><strong class="invoice-total">{{formatIndianNumber($grand_total)}}</strong></p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style="border-top:0; border-bottom:0;">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style="border-top:0">
                                <strong>
                                <?php
                                $number = $grand_total;
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
        </div>
    </section>
</div>
@include('layouts.footer')
<script> 

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

   
  
   function printpage(){
      $('.header-section').addClass('importantRule');
      $('.sidebar').addClass('importantRule');
      window.print();
      $('.header-section').removeClass('importantRule');
      $('.sidebar').removeClass('importantRule');
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

</script>
@endsection
