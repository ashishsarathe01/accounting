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
 

</style>

<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center header-section">
               <div class="d-md-flex d-block noprint">
                  <div class="calender-administrator my-2 my-md-0  w-min-230 noprint">
                     <button type="button" class="btn btn-danger" onclick="window.location='{{ url()->previous() }}'">QUIT</button>
                     <button class="btn btn-info" onclick="printpage();">Print</button>
                     <?php 
                    if ( in_array(date('Y-m', strtotime($sale_detail->date)), $month_arr) && $sale_detail->e_invoice_status == 0 && $sale_detail->e_waybill_status == 0) {?>
                        <a href="{{ URL::to('edit-sale/'.$sale_detail->id) }}" class="btn btn-primary text-white">
                           <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg') }}" alt="Edit" style="width: 16px; height: 16px; vertical-align: middle; filter: brightness(0) invert(1);">
                           Edit
                        </a><?php 
                     } ?>
                     <a href="{{ route('sale.create') }}"><button class="btn btn-primary">Add Sale</button></a>
                  </div>
               </div>            
            </div>  
            <br>          
            
            <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;">
               <tbody>
                  <tr>
    <th colspan="8" style="padding: 0;">
        <div style="min-height: 120px; position: relative;">
            <div style="width:auto; float:left; text-align:left;">
                <strong style="margin:0;">GSTIN: {{ $seller_info->gst_no }}</strong>
            </div>

            @php
                $companyName = $company_data->company_name;
                $fontSize = strlen($companyName) > 30 ? '18px' : '24px';
                
            @endphp

            <div class="bil_logo" style="float: left; margin-left: 10px;">
                @if($configuration && $configuration->company_logo_status==1 && !empty($configuration->company_logo))
                    <!--<img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}" -->
                    <!--     alt="My Logo" -->
                    <!--     style="object-fit: contain;">-->
                        
    <img src="{{ URL::asset('public/images') }}/{{ $configuration->company_logo }}" 
         alt="My Logo" 
         style="max-height: 100%; max-width: 100%; object-fit: contain; display: block;">

                @endif
            </div>

            <div style="width:auto; float:right; text-align:right;">
                <strong style="margin:0;">PAN: {{ substr($seller_info->gst_no, 2, 10) }}</strong><br>
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
                        {{ $seller_info->address }}
                    </small>
                </p>
                <p style="margin:0;">
                    <small style="font-size: 12px;">Phone: {{ $company_data->mobile_no }} &nbsp; Email: {{ $company_data->email_id }}</small>
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
                           <p><span class="width25">Invoice No. </span>:  <span class="lft_mar15">{{$sale_detail->voucher_no_prefix}}</span></p>
                           <p><span class="width25">Date of Invoice </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($sale_detail->date))}}</span></p>
                           <p><span class="width25">Place of Supply </span>: <span class="lft_mar15">{{$sale_detail->sname}}</span></p>
                           <p><span class="width25">Reverse Charge </span>: <span class="lft_mar15">{{$sale_detail->reverse_charge}}</span></p>
                           <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{$sale_detail->gr_pr_no}}</span></p>
                           <!-- <p>&nbsp;</p> -->
                        </td>
                        <td colspan="4">
                           <p><span class="width25">Transport </span>: <span class="lft_mar15">{{$sale_detail->transport_name}}</span> </p>
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
                           <!-- <p>&nbsp;</p> -->
                        </td>
                     </tr>
                     <tr>
                     <td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">

<p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
   <strong>Billed to :</strong>
</p>

<div style="padding-top: 16px; margin-left:5px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
   <p style="margin: 2px 0 0 0; line-height: 13px;">
      {{$sale_detail->billing_name}}<br>
      {{$sale_detail->billing_address}}
   </p>
</div>

<div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
   <p style="margin: 2px 0 0 0;">
      GSTIN/UIN:{{$sale_detail->billing_gst}} 
      <span style="float: right;">PAN:{{$sale_detail->billing_pan}}</span>
   </p>
</div>

</td>

<td colspan="4" style="position: relative; vertical-align: top; padding: 0; height:120px;">

<p style="margin: 0; position: absolute; top: 0; left: 5px; font-style: italic;">
   <strong>Shipped to :</strong>
</p>

<div style="padding-top: 16px; margin-left:5px; margin-right:5px; padding-bottom:30px; max-height:80px; overflow:hidden;">
   <p style="margin: 2px 0 0 0; line-height: 13px;">
      @if($sale_detail->shipping_name)
         {{$sale_detail->shipp_name}}<br>
         {{$sale_detail->shipping_address}}
      @else
         {{$sale_detail->billing_name}}<br>
         {{$sale_detail->billing_address}}
      @endif
   </p>
</div>

<div style="position: absolute; bottom: 0; left: 5px; right: 4px;">
   <p style="margin: 2px 0 0 0;">
      @if($sale_detail->shipping_name)
         GSTIN/UIN:{{$sale_detail->shipping_gst}} 
         <span style="float: right;">PAN:{{$sale_detail->shipping_pan}}</span>
      @else
         GSTIN/UIN:{{$sale_detail->billing_gst}} 
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
                     @php $i=1;$item_total = 0; @endphp
                     @foreach($items_detail as $item)
                        <tr>
                           <td style="text-align:center;">{{$i}}</td>
                           <td colspan="2" style="text-align:left">{{$item->items_name}}</td>
                           <td style="text-align:center;">{{$item->hsn_code}}</td>
                           <td style="text-align:right">{{$item->qty}}</td>
                           <td style="text-align:center">{{$item->unit}}</td>
                           <td style="text-align:right;">{{$item->price}}</td>
                           <td style="text-align:right;">{{formatIndianNumber($item->amount)}}</td>
                        </tr>
                        @php $i++;$item_total = $item_total + $item->amount; @endphp
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
                        <tr style="height: 23px;"><td></td><td colspan="2"></td><td></td><td></td><td></td><td></td><td></td></tr>
                     @php 
                        $tRows--; 
                        }
                     @endphp                      
                     <tr>
                        <td colspan="6" style="border-bottom:0; border-right:0"></td>
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
                        <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0">
                           <p><strong>Grand Total ₹</strong></p>
                        </td>                                     
                        <td style="text-align:right">
                           <p><strong>{{formatIndianNumber($sale_detail->total)}}</strong></p>
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
            <br>
           
            
            <div style="text-align: center;" class="noprint">
               <p id="jsonhtml"></p>
               @if($einvoice_status==1 && $sale_detail->e_invoice_status==0)
                  <button type="button" class="btn btn-info generate_einvoice">GENERATE E-INVOICE</button>
               @endif               
               @if(($einvoice_status==1 && $sale_detail->e_invoice_status==1 &&  $ewaybill_status==1 && $sale_detail->e_waybill_status==0) || ($einvoice_status==0 && $ewaybill_status==1 && $sale_detail->e_waybill_status==0))                  
                  <button type="button" class="btn btn-info generate_eway">GENERATE E-WAY BILL</button>                  
               @endif

               @if($einvoice_status==1 && $sale_detail->e_invoice_status==1)
                  <button type="button" class="btn btn-danger cancel_einvoice">CANCEL E-INVOICE</button>
               @endif

               @if($ewaybill_status==1 && $sale_detail->e_waybill_status==1)
                  <button type="button" class="btn btn-danger cancel_eway" style="margin-left: 100px;">CANCEL E-WAY BILL</button>                  
               @endif
            </div>             
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
                  <label for="no_of_parameter" class="form-label font-14 font-heading">Vehicle Number</label>
                  <input type="text" class="form-control" id="vehicle_no" placeholder="Enter Vehicle Number" value="{{$sale_detail->vehicle_no}}">
               </div>
               <div class="mb-12 col-md-12">
                  <label for="no_of_parameter" class="form-label font-14 font-heading">Distance</label>
                  <input type="text" class="form-control" id="distance" placeholder="Enter Distance">
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
                  distance : distance
               },
               success: function(data){                  
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
      $('.header-section').addClass('importantRule');
      $('.sidebar').addClass('importantRule');
      window.print();
      $('.header-section').removeClass('importantRule');
      $('.sidebar').removeClass('importantRule');
   }
</script>
@endsection
