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
   .importantRule { 
   display: none !important;  /* Force hide anything with this class */
}
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center noprint header-section">
                 
               <div class="d-md-flex d-block ">
                  <div class="calender-administrator my-2 my-md-0  w-min-230">
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
            <table style="font-family: 'Source Sans Pro', sans-serif;letter-spacing: 0.05em;color: #404040;font-size: 12px;font-weight: 500;padding: 10px;">
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
                     <!-- <tr>
                        <td colspan="8">               
                           <img src="" style="float: right;width: 90px;height: 90px;position: relative;">
                           <p>IRN NO. : 2c8e0befc2f68623add91ef6512a392220a4b362e580cdedd0a58afce9f968eb</p>
                           <p>ACK.NO. : 172414735996048</p>
                           <p>ACK.DATE : 2024-04-04 16:13:00</p>
                        </td>
                     </tr> -->                                                    
                     <tr>
                        <td colspan="4" style="width:50%;">
                           <p><span class="width25">Party Details :  </span></p>
                           <p><span class="width25">{{$purchase_return->billing_name}} </span></p>
                           <p>{{$purchase_return->billing_address}},{{$purchase_return->sname}}</p>
                           <p>{{$purchase_return->billing_pincode}}</p>
                           <p>&nbsp;</p>
                           <p>&nbsp;</p>
                           <p>GSTIN / UIN : {{$purchase_return->billing_gst}}</p>
                        </td>
                        <td colspan="4" style="width:50%;">
                           <p><span class="width25">Dr. Note No </span>: <span class="lft_mar15">{{$purchase_return->sr_prefix}}</span> </p>
                           <p><span class="width25">Dr. Note Date </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($purchase_return->date))}}</span> </p>
                           @if($purchase_return->voucher_type!="OTHER")
                              <p><span class="width25">Org. Inv. No. </span>: <span class="lft_mar15">{{$purchase_return->voucher_no}}</span> </p>
                              <p><span class="width25">Org. Inv. Date </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($purchase_return->purchase_date))}}</span> </p>
                           @elseif($purchase_return->voucher_type=="OTHER")
                              @if($purchase_return->other_invoice_no!="")
                                  <p><span class="width25">Org. Inv. No. </span>: <span class="lft_mar15">{{$purchase_return->other_invoice_no}}</span> </p>
                              @endif
                              @if($purchase_return->other_invoice_date!="")
                                 <p><span class="width25">Org. Inv. Date </span>: <span class="lft_mar15">{{date('d-m-Y',strtotime($purchase_return->other_invoice_date))}}</span> </p>
                              @endif
                              
                           @endif
                           
                           <p><span class="width25">Transport </span>: <span class="lft_mar15">{{$purchase_return->transport_name}}</span> </p>
                           <p><span class="width25">Vehicle No. </span>: <span class="lft_mar15">{{$purchase_return->vehicle_no}}</span> </p>
                           <p><span class="width25">Station </span>: <span class="lft_mar15">{{$purchase_return->station}}</span> </p>
                           <p><span class="width25">GR/RR No. </span>: <span class="lft_mar15">{{$purchase_return->gr_pr_no}}</span> </p>
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
                           @foreach($purchase_sundry as $sundry)
                           <p>Add : {{$sundry->name}} </p>
                           @endforeach                           
                        </td>
                        <td style="border-left:0; border-top:0;">
                           <!-- <p style="white-space: nowrap;">&nbsp;</p> -->
                           @foreach($purchase_sundry as $sundry)
                           <p>@if($sundry->rate!=0) {{$sundry->rate}} % @else &nbsp; @endif</p>
                           @endforeach
                        </td>
                        <td style="text-align:right; border-top:0;">
                           @foreach($purchase_sundry as $sundry)
                           <p>{{number_format($sundry->amount,2)}}</p>
                           @endforeach
                        </td>
                     </tr>                                
                     <tr>
                        <td colspan="7" style="text-align:right; border-right: 0; border-bottom: 0">
                           <p><strong>Grand Total ₹</strong></p>
                        </td>                                     
                        <td style="text-align:right">
                           <p><strong>{{number_format($purchase_return->total,2)}}</strong></p>
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
                           @if(Str::limit($company_data->gst,2,'')==Str::limit($purchase_return->billing_gst,2,''))
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
                              @if(Str::limit($company_data->gst,2,'')==Str::limit($purchase_return->billing_gst,2,''))
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
            <br>
            <div style="text-align: center;" class="noprint">
               @if($einvoice_status==1 && $purchase_return->e_invoice_status==0)
                  <button class="btn btn-border border-secondary generate_einvoice">GENERATE E-INVOICE</button>
               @endif
               @if(($einvoice_status==1 && $purchase_return->e_invoice_status==1 &&  $ewaybill_status==1 && $purchase_return->e_waybill_status==0) || ($einvoice_status==0 && $ewaybill_status==1 && $purchase_return->e_waybill_status==0))                  
                  <button type="button" class="btn btn-info generate_eway">GENERATE E-WAY BILL</button>                  
               @endif

               @if($einvoice_status==1 && $purchase_return->e_invoice_status==1)
                  <button type="button" class="btn btn-danger cancel_einvoice">CANCEL E-INVOICE</button>
               @endif

               @if($ewaybill_status==1 && $purchase_return->e_waybill_status==1)
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
                  <input type="text" class="form-control" id="vehicle_no" placeholder="Enter Vehicle Number" value="{{$purchase_return->vehicle_no}}">
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
   $(document).ready(function(){
      $(".generate_einvoice").click(function(){
         if(confirm("Are you confirm ?")==true){
            let id = "<?php echo $purchase_return->id ?>";
            $.ajax({
               url: '{{url("generate-debit-note-einvoice")}}',
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
      $(".generate_eway").click(function(){
         $("#ewayBillModal").modal('toggle');         
      });      
      $(".generate_eway_btn").click(function(){
            let id = "<?php echo $purchase_return->id; ?>";
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
               url: '{{url("generate-ewaybill-purchase-return")}}',
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
            let id = "<?php echo $purchase_return->id ?>";
            $.ajax({
               url: '{{url("cancel-einvoice-purchase-return")}}',
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
            let id = "<?php echo $purchase_return->id ?>";
            $.ajax({
               url: '{{url("cancel-ewaybill-purchase-return")}}',
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
         window.print();
         $('.header-section').removeClass('importantRule');
      }
</script>
@endsection