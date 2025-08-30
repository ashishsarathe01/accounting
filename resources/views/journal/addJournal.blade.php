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
   input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    margin: 0; 
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
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Journal Voucher</a>
                     </li>
                  </ol>
               </nav>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Journal Voucher</h5>
            <?php 
            $account_html = "<option value=''>Select</option>";            
            foreach ($party_list as $value) {
               $account_html.="<option value='".$value->id."'>$value->account_name</option>";
            }?>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('journal.store') }}">
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
                              <option value="<?php echo $value->series; ?>" data-gst="<?php echo $value->gst_no;?>" <?php if(count($mat_series)==1) { echo "selected";} ?>><?php echo $value->series; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <input type="hidden" name="merchant_gst" id="merchant_gst">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label class="form-label font-14 font-heading">Claim GST</label>
                     <div class="custom-radio me-4 d-flex">
                        <input type="radio" class="custom-radio-input journal-hide claim_gst_radio" name="flexRadioDefault" id="flexRadioDefault1" value="YES">
                        <label for="flexRadioDefault1" class="custom-radio-label me-4 ps-4 ">Yes</label>
                        <input type="radio" class="custom-radio-input journal-show claim_gst_radio" name="flexRadioDefault" id="flexRadioDefault2" checked value="NO">
                        <label for="flexRadioDefault2" class="custom-radio-label ps-4 ">No</label>
                     </div>
                  </div>
                  <div class="mb-2 col-md-2 with_gst_section" style="display:none;">
                     <label for="series_no" class="form-label font-14 font-heading">Invoice No.</label>
                     <input type="text" class="form-control" name="invoice_no" placeholder="Enter Invoice No.">
                  </div>
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="without_gst_section" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-120 border-none bg-light-pink text-body ">Debit/Credit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Account</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Debit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Credit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Narration</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr class="font-14 font-heading bg-white">
                           <td class="">
                              <select class="form-control type" name="type[]" data-id="1" id="type_1" onchange="onTypeChange(1)">
                                 <option value="">Type</option>
                                 <option value="Credit" selected>Credit</option>
                                 <option value="Debit">Debit</option>
                              </select>
                           </td>
                           <td class="">
                              <select class="form-select select2-single" id="account_1" name="account_name[]" required>
                                 <option value="">Select</option>
                                 <?php
                                 foreach ($party_list as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                    <?php 
                                 } ?>
                              </select>
                           </td>
                           <td class="">
                              <input type="number" name="debit[]" class="form-control debit" data-id="1" id="debit_1" placeholder="Debit Amount" readonly onkeyup="debitTotal();">
                           </td>
                           <td class="">
                              <input type="number" name="credit[]" class="form-control credit" data-id="1" id="credit_1" placeholder="Credit Amount" onkeyup="creditTotal();">
                           </td>
                           <td class="">
                              <input type="text" name="narration[]" class="form-control narration" data-id="1" id="narration_1" placeholder="Enter Narration" value="">
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td class="">
                              <select class="form-control type" name="type[]" data-id="2" id="type_2" onchange="onTypeChange(2)">
                                 <option value="">Type</option>
                                 <option value="Credit">Credit</option>
                                 <option value="Debit" selected>Debit</option>
                              </select>
                           </td>
                           <td class="">
                              <select class="form-select select2-single" id="account_2" name="account_name[]" required>
                                 <option value="">Select</option>
                                 <?php
                                 foreach ($party_list as $value) { ?>
                                    <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                    <?php 
                                 } ?>
                              </select>
                           </td>
                           <td class="">
                              <input type="number" name="debit[]" class="form-control debit" data-id="2" id="debit_2" placeholder="Debit Amount" onkeyup="debitTotal();">
                           </td>
                           <td class="">
                              <input type="number" name="credit[]" class="form-control credit" data-id="2" id="credit_2" placeholder="Credit Amount" readonly onkeyup="creditTotal();">
                           </td>
                           <td class="">
                              <input type="text" name="narration[]" class="form-control narration" data-id="2" id="narration_2" placeholder="Enter Narration" value="">
                           </td>
                        </tr>
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                              </svg></a>
                           </td>
                        </tr>
                     </div>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td class="w-min-120 fw-bold">Total</td>
                           <td class="w-min-120 fw-bold" id="total_debit"></td>
                           <td class="w-min-120 fw-bold" id="total_credit"></td>
                           <td></td>
                           <td></td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td colspan="6"><input type="text" class="form-control" placeholder="Enter Long Narration" name="long_narration"></td>
                        </tr>
                     </div>
                  </table>
                  <table class="table-striped table m-0 shadow-sm table-bordered with_gst_section" style="display: none;">
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
                              <input type="number" class="form-control amount" id="amount_1" data-index="1" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()">
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
   function onTypeChange(id){
      $("#debit_" + id).val('');
      $("#credit_" + id).val('');
      let debit_total = 0;
      $(".debit").each(function(){
         if($(this).val()!=""){
            debit_total = parseFloat(debit_total) + parseFloat($(this).val());
         }
      });
      let credit_total = 0;
      $(".credit").each(function(){
         if($(this).val()!=""){
            credit_total = parseFloat(credit_total) + parseFloat($(this).val());
         }
      });
      if($("#type_" + id).val() == "Credit"){
         $("#debit_" + id).prop('readonly', true);
         $("#credit_" + id).prop('readonly', false);
         let amount = debit_total - credit_total;
         if(amount>0){
            $("#credit_"+id).val(amount.toFixed(2));
         }
         $("#account_"+id).html("<?php echo $account_html;?>");
      }else if ($("#type_" + id).val() == "Debit"){
         $("#debit_" + id).prop('readonly', false);
         $("#credit_" + id).prop('readonly', true);
         let amount = credit_total - debit_total;
         if(amount>0){
            $("#debit_"+id).val(amount.toFixed(2));
         }
         $("#account_"+id).html("<?php echo $account_html;?>");
      }
      $("#account_"+id).html("<?php echo $account_html;?>");
      debitTotal();
      creditTotal();
   }
   
   var add_more_count = 2;
   $(".add_more").click(function(){
      add_more_count++;
      var $curRow = $(this).closest('tr');
      var optionElements = $('#account_1').html();
      let type_option = '<option value="Credit">Credit</option><option value="Debit">Debit</option>';
      let debit_count = 0; let credit_count = 0;
      $(".type").each(function(){
         if(this.value == 'Credit') {
            credit_count++;            
         }else if(this.value == 'Debit'){
            debit_count++;
         }
      });
      if(debit_count>1){
         type_option = '<option value="Debit">Debit</option>';
      }else if(credit_count>1){
         type_option = '<option value="Credit">Credit</option>';
      }
      newRow = '<tr id="tr_' + add_more_count + '"><td><select class="form-control type" name="type[]" data-id="' + add_more_count + '" id="type_' + add_more_count + '" onchange="onTypeChange('+add_more_count+')"><option value="">Type</option>'+type_option+'</select></td><td><select class="form-control account select2-single" name="account_name[]" data-id="' + add_more_count + '" id="account_' + add_more_count + '">';
      newRow += optionElements;
      newRow += '</select></td><td><input type="number" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td><td><input type="text" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
      $('.select2-single').select2();
   });
   $(document).on("click", ".remove", function() {
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      debitTotal();
      creditTotal();
   });
   function debitTotal() {
      let total_debit_amount = 0;
      $(".debit").each(function() {
         if($(this).val() != '') {
            total_debit_amount = parseFloat(total_debit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_debit").html(total_debit_amount.toFixed(2));
   }
   function creditTotal() {
      let total_credit_amount = 0;
      $(".credit").each(function() {
         if ($(this).val() != '') {
            total_credit_amount = parseFloat(total_credit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_credit").html(total_credit_amount.toFixed(2));
   }
   $("#mode").change(function(){
      $("#cheque_no").val('');
      $("#cheque_no").prop('readonly',true);
      if($(this).val()==2){
         $("#cheque_no").prop('readonly',false);
      }
   });
   $(document).on("change",".debit",function(){
      // let id = $(this).attr('data-id');
      // let ind = parseInt(id)+1;
      // $("#type_"+ind).val('Credit');
      // $("#type_"+ind).change();
      // $("#credit_"+ind).prop("disabled",false);
      // $("#credit_"+ind).val($(this).val());
      debitTotal();
      creditTotal();
   });
   $(document).on("change",".credit",function(){
      // let id = $(this).attr('data-id');
      // let ind = parseInt(id)+1;
      // $("#type_"+ind).val('Debit');
      // $("#type_"+ind).change();
      // $("#debit_"+ind).prop("disabled",false);
      // $("#debit_"+ind).val($(this).val());
      debitTotal();
      creditTotal();
   });
   $(document).ready(function(){
      $('.select2-single').select2();
      $(".submit_data").click(function() {            
         let date = $("#date").val();
         let series_no = $("#series_no").val(); // Added series_no validation
         // Field-wise alerts
         if (date === "") {
               alert("Please enter the Date.");
               return false;
         }
         if (series_no === "") {
               alert("Please enter the Series Name/Number.");
               return false;
         }
         var form_data = [];
         let dr = 0;
         let cr = 0;
         let ids = 0;
         let error = false;
         let claim_gst_status = document.querySelector('input[name="flexRadioDefault"]:checked').value;
         let debit_count = 0;let credit_count = 0;
         if(claim_gst_status=="NO"){
            $(".type").each(function(){
               let id = $(this).attr('data-id');
               if($(this).val() != '' && $("#account_" + id).val() != "" && $("#date").val()!='') {
                  if($(this).val() == "Credit" && $("#credit_" + id).val() != ""  && $("#account_" + id).val() != ""){
                     form_data.push({
                        "type": "Credit",
                        "credit": $("#credit_" + id).val(),
                        "debit": 0,
                        "user_id": $("#account_" + id).val(),
                        "remark": $("#narration_" + id).val()
                     });
                     cr = parseFloat(cr) + parseFloat($("#credit_" + id).val());
                     credit_count++
                  }else if($(this).val()=="Debit" && $("#debit_" + id).val()!= ""  && $("#account_" + id).val()!= ""){
                     form_data.push({
                        "type": "Debit",
                        "credit": 0,
                        "debit": $("#debit_" + id).val(),
                        "user_id": $("#account_" + id).val(),
                        "remark": $("#narration_" + id).val()
                     });
                     dr = parseFloat(dr) + parseFloat($("#debit_" + id).val());
                     debit_count++;
                  }
               }else{
                  error = true; // Set error flag if any required field is empty
               }
            }); 
         }else{
            if($("#vendor").val() == '' && $("#series_no").val() == "" && $("#date").val() == '' && $("#item_1").val() == '' && $("#percentage_1").val() == '' && $("#amount_1").val() == '') {
               error = true;
            }
         }
                  
         if(error) {
            alert("Please fill in all required fields.");
            return false;
         }
         if(form_data.length == 0 && claim_gst_status=="NO") {
            alert("Please enter at least one transaction.");
            return false;
         }
         if(credit_count>1 && debit_count>1){
            alert("Not Allowed - You cannot enter multiple debits and credits simultaneously.");
            return false;
         }
         if(cr != dr && claim_gst_status=="NO") {
            alert("Debit and credit amounts should be equal.");
            return false;
         }
        $("#frm").submit();
      });
      $(".claim_gst_radio").click(function(){
         $("#without_gst_section").hide();
         $(".with_gst_section").hide();
         $("#vendor").attr('required',false);
         $("#item_1").attr('required',false);
         $("#percentage_1").attr('required',false);
         $("#amount_1").attr('required',false);
         $("#account_1").attr('required',false);
         $("#account_2").attr('required',false);
         if($(this).val()=="YES"){
            $(".with_gst_section").show();
            $("#vendor").attr('required',true);
            $("#item_1").attr('required',true);
            $("#percentage_1").attr('required',true);
            $("#amount_1").attr('required',true);
            $("#vendor").select2();
            $("#item_1").select2();
         }else{
            $("#account_1").attr('required',true);
            $("#account_2").attr('required',true);
            $("#without_gst_section").show();
         }
      });
      $("#series_no").change(function(){
         let gst_no = $("#series_no option:selected").attr("data-gst");
         $("#merchant_gst").val(gst_no);
         if(gst_no){
            company_gst = gst_no;
            gstCalculation();
         }
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
      let newRow = '<tr id="withgst_tr_'+add_more_count_withgst+'" class="font-14 font-heading bg-white"><td><select class="form-control item" id="item_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="item[]" onchange="gstCalculation()" style="width: 598.611px;"><option value="">Select Item</option>@foreach($items as $item)<option value="{{$item->id}}">{{$item->account_name}}</option>@endforeach </select></td><td><select class="form-control percentage" id="percentage_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="percentage[]" onchange="gstCalculation()"><option value="">Select GST(%)</option><option value="5">5%</option><option value="12">12%</option><option value="18">18%</option><option value="28">28%</option></select></td><td><input type="number" class="form-control amount" id="amount_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()"></td><td><button type="button" class="btn btn-danger remove_more_tr" data-id="'+add_more_count_withgst+'">Remove</button></td></tr>';
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