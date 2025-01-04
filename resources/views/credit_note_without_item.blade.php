@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style type="text/css">
   .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 49px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
      height: 50px !important;
   }
   .select2-container .select2-selection--single{
      height: 46px !important;
   }
   .select2-container{
      width: 300 px !important;
   }
   .select2-container--default .select2-selection--single{
      border-radius: 12px !important;
   }
   .selection{
      font-size: 14px;
   }
   .form-control {
      height: 52px;
   }
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
               <nav aria-label="breadcrumb meri-breadcrumb">
                  <ol class="breadcrumb meri-breadcrumb m-0 ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Credit Note</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Credit Note</h5>
           
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('credit-note-without-item.store') }}">
               @csrf
               <div class="row">
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}" value="{{$date}}">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                     <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No.">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" class="form-control" name="series_no" required>
                        <option value="">Select Series</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value['branch_series']; ?>" <?php if(count($mat_series)==1) { echo "selected";} ?>><?php echo $value['branch_series']; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                  </div>
                  
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  
                  <table class="table-striped table m-0 shadow-sm table-bordered with_gst_section">
                     <tbody>
                        <tr class="font-14 font-heading bg-white">
                           <td>
                              <select class="form-control" id="vendor" name="vendor" onchange="gstCalculation()" style="width: 598.611px;">
                                 <option value="">Select Vendor</option>
                                 @foreach($vendors as $vendor)
                                    <option value="{{$vendor->id}}" data-gstin="{{$vendor->gstin}}">{{$vendor->account_name}}</option>
                                 @endforeach
                              </select>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td>
                              <select class="form-control item" id="item_1" data-index="1" name="item[]" onchange="gstCalculation()" style="width: 598.611px;">
                                 <option value="">Select Item</option>
                                 @foreach($items as $item)
                                    <option value="{{$item->id}}">{{$item->account_name}}</option>
                                 @endforeach
                              </select>
                           </td>
                           <td>
                              <select class="form-control percentage" id="percentage_1" data-index="1" name="percentage[]" onchange="gstCalculation()">
                                 <option value="">Select GST(%)</option>
                                 <option value="5">5%</option>
                                 <option value="12">12%</option>
                                 <option value="18">18%</option>
                                 <option value="28">28%</option>
                              </select>
                           </td>
                           <td>
                              <input type="text" class="form-control amount" id="amount_1" data-index="1" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()">
                           </td>
                           <td><button type="button" class="btn btn-info add_more_tr">Add</button></td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td style="text-align: right;">Net Amount</td>
                           <td>
                              <input type="text" class="form-control" id="net_amount" name="net_amount" placeholder="Net Amount" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white cgst_tr" style="display: none;">
                           <td></td>
                           <td style="text-align: right;">CGST</td>
                           <td>
                              <input type="text" class="form-control" id="cgst" name="cgst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white sgst_tr" style="display: none;">
                           <td></td>
                           <td style="text-align: right;">SGST</td>
                           <td>
                              <input type="text" class="form-control" id="sgst" name="sgst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white igst_tr" style="display: none;">
                           <td></td>
                           <td style="text-align: right;">IGST</td>
                           <td>
                              <input type="text" class="form-control" id="igst" name="igst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td style="text-align: right;">Total Amount</td>
                           <td>
                              <input type="text" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td style="text-align: right;">Remark</td>
                           <td>
                              <input type="text" class="form-control" name="remark" placeholder="Enter Remark">
                           </td>
                        </tr>
                     </tbody>
                  </table>
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <a href="{{ route('journal.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     <input type="submit" value="SUBMIT" class="btn btn-xs-primary submit_data">
                  </div>
                  <input type="hidden" clas="max_sale_descrption" name="max_sale_descrption" value="1" id="max_sale_descrption">
                  <input type="hidden" name="max_sale_sundry" id="max_sale_sundry" value="1" />
               </div>
            </form>
         </div>
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>
   var company_gst = "{{$company_gst}}";   
   $(document).ready(function(){
      $('.select2-single').select2();
      $(".submit_data").click(function() {
         let error = false;         
         if($("#vendor").val() == '' && $("#series_no").val() == "" && $("#date").val() == '' && $("#item_1").val() == '' && $("#percentage_1").val() == '' && $("#amount_1").val() == '') {
            error = true;
         }       
         if(error) {
            alert("Please fill in all required fields.");
            return false;
         }         
         $("#frm").submit();
      });
   });
   function gstCalculation(){
      let vendor_gstin = $("#vendor option:selected").attr("data-gstin").substr(0,2);
      let company_gstin = company_gst.substr(0,2);
      let net_total = 0;
      let total_cgst = 0;
      let total_sgst = 0;
      let total_igst = 0;
      $(".item").each(function(){
         if($(this).val()!=""){
            let id = $(this).attr('data-index');
            let percentage = $("#percentage_"+id).val();
            let amount = $("#amount_"+id).val();
            if(percentage!="" && amount!=""){
               let IGST = amount*percentage/100;
               let CGST = amount*(percentage/2)/100;
               let SGST = CGST;               
               IGST = IGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               CGST = CGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               SGST = SGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0]; 
               total_cgst = parseFloat(total_cgst) + parseFloat(CGST);
               total_sgst = parseFloat(total_sgst) + parseFloat(SGST);
               total_igst = parseFloat(total_igst) + parseFloat(IGST);
               net_total = parseFloat(net_total) + parseFloat(amount);
            }                        
         }
      });
      $("#cgst").val("");
      $("#sgst").val("");
      $("#igst").val("");
      if(vendor_gstin==company_gstin){
         $(".cgst_tr").show();
         $(".sgst_tr").show();
         $(".igst_tr").hide();          
         $("#cgst").val(total_cgst);
         $("#sgst").val(total_sgst); 
      }else{
         $("#igst").val(total_igst);
         $(".cgst_tr").hide();
         $(".sgst_tr").hide();
         $(".igst_tr").show();
      }
      $("#net_amount").val(net_total);
      let tamount = parseFloat(net_total) + parseFloat(total_igst);
      $("#total_amount").val(Math.round(tamount));
   }
   var add_more_count_withgst = 1;
   $(".add_more_tr").click(function(){
      add_more_count_withgst++;
      var $curRow = $(this).closest('tr');
      let newRow = '<tr id="withgst_tr_'+add_more_count_withgst+'" class="font-14 font-heading bg-white"><td><select class="form-control item" id="item_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="item[]" onchange="gstCalculation()" style="width: 598.611px;"><option value="">Select Item</option>@foreach($items as $item)<option value="{{$item->id}}">{{$item->account_name}}</option>@endforeach </select></td><td><select class="form-control percentage" id="percentage_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="percentage[]" onchange="gstCalculation()"><option value="">Select GST(%)</option><option value="5">5%</option><option value="12">12%</option><option value="18">18%</option><option value="28">28%</option></select></td><td><input type="text" class="form-control amount" id="amount_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()"></td><td><button type="button" class="btn btn-danger remove_more_tr" data-id="'+add_more_count_withgst+'">Remove</button></td></tr>';
      $curRow.after(newRow);
      $("#item_"+add_more_count_withgst).select2();
   });
   $(document).on("click", ".remove_more_tr", function() {
      let id = $(this).attr('data-id');
      $("#withgst_tr_" + id).remove();
      gstCalculation();
   });
</script>
@endsection