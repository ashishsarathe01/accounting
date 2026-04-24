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
      height: 49px !important;
   }
   .select2-container .select2-selection--single{
      height: 49px !important;
   }
   .select2-container{
      width: 300px !important;
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
#claim_gst{
height:52px;
border-radius:12px;
}
.icon-btn{
    width:32px;
    height:32px;
    border-radius:50%;
    border:none;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    font-weight:bold;
    cursor:pointer;
}

.add-btn{
    background:#0d6efd;
    color:white;
}

.remove-btn{
    background:#dc3545;
    color:white;
}

.icon-btn:hover{
    transform:scale(1.05);
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
               <input type="hidden" name="items" value="{{ request('items') }}">
               <input type="hidden" name="data" value='{{ request("data") }}'>
               <input type="hidden" name="purchase_report_id" value="{{ request('purchase_report_id') }}">
               <input type="hidden" name="spare_part_id" value="{{ request('spare_part_id') }}">
               <input type="hidden" name="vehicle_entry_id" value="{{ request('vehicle_entry_id') }}">
               <input type="hidden" name="close_purchase" value="{{ request('close_purchase') }}">
               <div class="row">
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}" value="{{$date}}">
                  </div>
                  
                  <div class="mb-2 col-md-2">
                     <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" class="form-control" name="series_no" required>
                        <option value="">Select Series</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->series; ?>"
                                 data-invoice_start_from="<?php echo $value->invoice_start_from ?? ''; ?>"
                                 data-invoice_prefix="<?php echo $value->invoice_prefix ?? ''; ?>"
                                 data-manual_enter_invoice_no="<?php echo $value->manual_enter_invoice_no ?? ''; ?>"
                                 data-gst="<?php echo $value->gst_no ?? ''; ?>"
                              >
                                 <?php echo $value->series; ?>
                              </option>
                              <?php 
                           }
                        } ?>
                     </select>
                     <input type="hidden" name="merchant_gst" id="merchant_gst">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" style="text-align:right;">
                     <input type="hidden" id="voucher_no" name="voucher_no">
                     <input type="hidden" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                  </div>
                  <div class="mb-2 col-md-2">
                  <label class="form-label font-14 font-heading">Claim GST</label>
                  <select class="form-select claim_gst_dropdown" name="flexRadioDefault" id="claim_gst"
                     {{ isset($is_purchase_journal) && $is_purchase_journal ? 'disabled' : '' }}>
                     <option value="YES">Yes</option>
                     <option value="NO" {{ isset($is_purchase_journal) && $is_purchase_journal ? 'selected' : '' }}>No</option>
                  </select>

                  @if(isset($is_purchase_journal) && $is_purchase_journal)
                     <input type="hidden" name="flexRadioDefault" value="NO">
                  @endif
                  </div>
                  <div class="mb-2 col-md-2 with_gst_section" style="display:none;">
                     <label for="series_no" class="form-label font-14 font-heading">Invoice No.</label>
                     <input type="text"
                     class="form-control"
                     id="invoice_no"
                     name="invoice_no"
                     placeholder="Enter Invoice No."
                     value="{{ $prefill_invoice_no ?? '' }}">
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
                              <select class="form-select select2-single" id="account_1" data-id="1" name="account_name[]" required>
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
                              <select class="form-select select2-single" id="account_2" data-id="2" name="account_name[]" required>
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
                              <a class="add_more" tabindex="0"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;" tabindex="0"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
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
                              <select class="form-control" id="vendor" name="vendor" onchange="checkGSTVendor()" style="width: 598.611px;">
                                 <option value="">Select Vendor</option>
                                 @foreach($vendors as $vendor)
                                    <option value="{{$vendor->id}}" data-gstin="{{$vendor->gstin}}">{{ $vendor->account_name }} 
                                    @if(!$vendor->gstin) (No GST) @endif</option>
                                 @endforeach
                              </select>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td>
                              <select class="form-control item select2-single" id="item_1" data-index="1" name="item[]" onchange="gstCalculation()" style="width: 598.611px;">
                                 <option value="">Select Item</option>
                                 @foreach($items as $item)
                                    <option value="{{$item->id}}">{{$item->account_name}}</option>
                                 @endforeach
                              </select>
                           </td>
                           <td>
                              <select class="form-control percentage" id="percentage_1" data-index="1" name="percentage[]" onchange="gstCalculation()">
                                 <option value="">Select GST(%)</option>
                                 <option value="0">0%</option>
                                 <option value="5">5%</option>
                                 <option value="12">12%</option>
                                 <option value="18">18%</option>
                                 <option value="28">28%</option>
                              </select>
                           </td>
                           <td>
                              <input type="number" class="form-control amount" id="amount_1" data-index="1" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()">
                           </td>
                           <td><button type="button" class="btn btn-info add_more_tr">+</button></td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td style="text-align: right;vertical-align:middle;">Net Amount</td>
                           <td>
                              <input type="text" class="form-control" id="net_amount" name="net_amount" placeholder="Net Amount" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white cgst_tr" style="display: none;">
                           <td></td>
                           <td style="text-align: right;vertical-align:middle;vertical-align:middle;">CGST</td>
                           <td>
                              <input type="text" class="form-control" id="cgst" name="cgst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white sgst_tr" style="display: none;">
                           <td></td>
                           <td style="text-align: right;vertical-align:middle;">SGST</td>
                           <td>
                              <input type="text" class="form-control" id="sgst" name="sgst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white igst_tr" style="display: none;">
                           <td></td>
                           <td style="text-align: right;vertical-align:middle;vertical-align:middle;">IGST</td>
                           <td>
                              <input type="text" class="form-control" id="igst" name="igst" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white roundoff_tr">
                                 <td></td>
                                 <td style="text-align: right;">Round Off</td>
                                 <td>
                                    <input type="text"
                                          class="form-control"
                                          id="round_off"
                                          name="round_off"
                                          readonly
                                          >
                                 </td>
                              </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td></td>
                           <td style="text-align: right;vertical-align:middle;">Total Amount</td>
                           <td>
                              <input type="text" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" readonly>
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td colspan="3";>
                                    <input type="text" class="form-control" name="remark" placeholder="Enter Remark" >
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
<div class="modal fade" id="gstAccountModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          Add GST for <span id="gst_modal_account_name"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="gst_modal_account_id">

        <!-- GST NO -->
        <div class="mb-3">
          <label class="form-label">GST No</label>
          <input type="text" class="form-control" id="gstin" placeholder="GST No">
        </div>

        <!-- STATE -->
        <div class="mb-3">
          <label class="form-label">State</label>
          <select class="form-select select2-single" id="state">
            <option value="">Select State</option>
            @foreach($state_list as $state)
              <option value="{{ $state->id }}"
                data-state_code="{{ $state->state_code }}">
                {{ $state->state_code }} - {{ $state->name }}
              </option>
            @endforeach
          </select>
          <input type="hidden" id="state_hidden">
        </div>

        <!-- ADDRESS -->
        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea class="form-control" id="address" placeholder="Address"></textarea>
        </div>

        <!-- PINCODE -->
        <div class="mb-3">
          <label class="form-label">Pincode</label>
          <input type="number" class="form-control" id="pincode" placeholder="Pincode">
        </div>

        <!-- PAN -->
        <div class="mb-3">
          <label class="form-label">PAN</label>
          <input type="text" class="form-control" id="pan" readonly placeholder="PAN">
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelGstModal">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" id="saveGstModal">
          Save GST
        </button>
      </div>

    </div>
  </div>
</div>
</body>
@include('layouts.footer')
<script>
   let ignoreVendorChange = false;
   let isAutoFillingJournal = false;
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
      // if(debit_count>1){
      //    type_option = '<option value="Debit">Debit</option>';
      // }else if(credit_count>1){
      //    type_option = '<option value="Credit">Credit</option>';
      // }
      newRow = '<tr id="tr_' + add_more_count + '"><td><select class="form-control type" name="type[]" data-id="' + add_more_count + '" id="type_' + add_more_count + '" onchange="onTypeChange('+add_more_count+')"><option value="">Type</option>'+type_option+'</select></td><td><select class="form-control account select2-single" name="account_name[]" data-id="' + add_more_count + '" id="account_' + add_more_count + '">';
      newRow += optionElements;
      newRow += '</select></td><td><input type="number" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td><td><input type="text" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
      $curRow.before(newRow);
      $('.select2-single').select2();

      // focus new row type
      setTimeout(function(){
         $("#type_" + add_more_count).focus();
      },100);
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
      // -------- AUTO LOAD GST SECTION --------
      if ($("#claim_gst").val() === "YES") {

         $("#without_gst_section").hide();
         $(".with_gst_section").show();

         $("#vendor").attr('required', true);
         $("#item_1").attr('required', true);
         $("#percentage_1").attr('required', true);
         $("#invoice_no").attr('required', true);
         $("#amount_1").attr('required', true);

         $("#vendor").select2();
         $("#item_1").select2();
      }
      // -------- AUTO SELECT VENDOR --------
      const params = new URLSearchParams(window.location.search);
      const accountId = params.get('account_id');

      if (accountId) {
         $("#vendor").val(accountId).trigger("change");
      }
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
         let claim_gst_status = $("#claim_gst").val();
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
         // if(credit_count>1 && debit_count>1){
         //    alert("Not Allowed - You cannot enter multiple debits and credits simultaneously.");
         //    return false;
         // }
         dr = dr.toFixed(2);
         cr = cr.toFixed(2);
         console.log(cr+"**"+dr);
         if(parseFloat(cr) != parseFloat(dr) && claim_gst_status=="NO") {
            alert("Debit and credit amounts should be equal.");
            return false;
         }
        $("#frm").submit();
      });
      $("#claim_gst").change(function(){
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
            $("#invoice_no").attr('required',true);
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

      @if(isset($is_purchase_journal) && $is_purchase_journal)
         setTimeout(function(){

            isAutoFillingJournal = true; 

            let journalType = "{{ $journal_type }}";
            let amount = "{{ $journal_amount }}";
            let journalAccountId = "{{ $prefill_account_id }}";

            if(journalType === 'debit'){
               $("#type_1").val("Debit").change();
               $("#account_1").val(journalAccountId).trigger('change');
               $("#debit_1").val(amount);

               $("#type_2").val("Credit").change();
               $("#credit_2").val(amount);
            }else{
               $("#type_1").val("Credit").change();
               $("#account_1").val(journalAccountId).trigger('change');
               $("#credit_1").val(amount);

               $("#type_2").val("Debit").change();
               $("#debit_2").val(amount);
            }

            debitTotal();
            creditTotal();

            setTimeout(function(){
               isAutoFillingJournal = false; 
            },200);

         }, 300);

      @endif
   });
    function checkGSTVendor() {
        if (ignoreVendorChange) {
            ignoreVendorChange = false;
            return;
        }
        let select = document.getElementById('vendor');
        let opt = select.options[select.selectedIndex];
        if (!opt) return;
        let gst = opt.getAttribute('data-gstin');
        if (!gst || gst.trim() === "") {
            $("#gst_modal_account_id").val(opt.value);
            $("#gst_modal_account_name").text(opt.text.trim());
            $("#gstin").val("");
            $("#pan").val("");
            $("#address").val("");
            $("#pincode").val("");
            $("#state").val("").trigger("change");
            $("#state").off('select2:opening')
                      .css('pointer-events', 'auto');
            ignoreVendorChange = true;
             $("#vendor").val(null).trigger("change.select2");
            $("#gstAccountModal").modal("show");
            return;
        }
    
        gstCalculation();
    }

   function gstCalculation(){
      let vendor_gstin_full = $("#vendor option:selected").attr("data-gstin") || "";
      let company_gstin_full = company_gst || "";

      let vendor_gstin = vendor_gstin_full.length >= 2 ? vendor_gstin_full.substr(0,2) : "";
      let company_gstin = company_gstin_full.length >= 2 ? company_gstin_full.substr(0,2) : "";
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
               IGST = Math.round(IGST * 100) / 100;
                IGST = IGST.toFixed(2);
                IGST = IGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
                IGST = parseFloat(IGST);

               CGST = Math.round(CGST * 100) / 100;
               CGST = CGST.toFixed(2);
               CGST = CGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               CGST = parseFloat(CGST);

               SGST = Math.round(SGST * 100) / 100;
               SGST = SGST.toFixed(2);
               SGST = SGST.toString().match(/^-?\d+(?:\.\d{0,2})?/)[0];
               SGST = parseFloat(SGST);
 
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
          tamount = parseFloat(net_total || 0) 
            + parseFloat(total_cgst || 0) 
            + parseFloat(total_sgst || 0);
      }else{
         $("#igst").val(total_igst);
         $(".cgst_tr").hide();
         $(".sgst_tr").hide();
         $(".igst_tr").show();
         tamount = parseFloat(net_total || 0) 
            + parseFloat(total_igst || 0);
      }
      $("#net_amount").val(parseFloat(net_total || 0).toFixed(2));

// ✅ Total amount (rounded)
let rounded_total = Math.round(tamount);
$("#total_amount").val(rounded_total);

// ✅ Round off
let round_off = (rounded_total - tamount).toFixed(2);
$("#round_off").val(round_off);

// ✅ Show/hide roundoff row
if (parseFloat(round_off) === 0) {
    $(".roundoff_tr").hide();
} else {
    $(".roundoff_tr").show();
}
   }
   var add_more_count_withgst = 1;
   $(document).on("click",".add_more_tr",function(){

      add_more_count_withgst++;

      var $curRow = $(this).closest('tr');

      let newRow = '<tr id="withgst_tr_'+add_more_count_withgst+'" class="font-14 font-heading bg-white">'+
      '<td><select class="form-control item select2-single" id="item_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="item[]" onchange="gstCalculation()" style="width: 598.611px;">'+
      '<option value="">Select Item</option>@foreach($items as $item)<option value="{{$item->id}}">{{$item->account_name}}</option>@endforeach</select></td>'+

      '<td><select class="form-control percentage" id="percentage_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="percentage[]" onchange="gstCalculation()">'+
      '<option value="">Select GST(%)</option>'+
      '<option value="0">0%</option>'+
      '<option value="5">5%</option>'+
      '<option value="12">12%</option>'+
      '<option value="18">18%</option>'+
      '<option value="28">28%</option></select></td>'+

      '<td><input type="number" class="form-control amount" id="amount_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()"></td>'+

      '<td><button type="button" class="icon-btn remove-btn remove_more_tr" data-id="'+add_more_count_withgst+'">−</button></td>'+
      '</tr>';

      /* INSERT AFTER CURRENT ROW */
      $curRow.after(newRow);

      /* MOVE ADD BUTTON TO LAST ROW */
      $(".add_more_tr").remove();

      $("#withgst_tr_"+add_more_count_withgst).append(
         '<td><button type="button" class="icon-btn add-btn add_more_tr">+</button></td>'
      );

      $("#item_"+add_more_count_withgst).select2();

      setTimeout(function(){
         $("#item_"+add_more_count_withgst).select2('open');
      },100);

   });
   $(document).on("click", ".remove_more_tr", function() {

      let row = $(this).closest("tr");
      let table = row.closest("tbody");

      row.remove();

      /* remove any existing add buttons */
      table.find(".add_more_tr").remove();

      /* add add-button to last row */
      let lastRow = table.find("tr").has(".item").last();

      if(lastRow.length){
         lastRow.append('<td><button type="button" class="icon-btn add-btn add_more_tr">+</button></td>');
      }

      gstCalculation();

   });
    $("#saveGstModal").click(function () {
        let accountId = $("#gst_modal_account_id").val();
        let gstin = $("#gstin").val();
    
        if (!gstin || gstin.length !== 15) {
            alert("Please enter a valid GST number");
            return;
        }
        $.ajax({
            url: "{{ route('account.update.gst') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                account_id: accountId,
                gstin: gstin,
                state: $("#state_hidden").val(),
                address: $("#address").val(),
                pincode: $("#pincode").val(),
                pan: $("#pan").val()
            },
            success: function () {
    
                let opt = $("#vendor option[value='" + accountId + "']");
                opt.attr("data-gstin", gstin);
    
                ignoreVendorChange = true;
                $("#vendor").val(accountId);
                $("#gstAccountModal").modal("hide");
    
                gstCalculation();
            }
        });
    });
    $("#cancelGstModal").click(function () {
        $("#gstAccountModal").modal("hide");
    });
    $('#gstAccountModal').on('shown.bs.modal', function () {
        $('#state').select2({
            dropdownParent: $('#gstAccountModal'),
            width: '100%'
        });
    });
    function syncStateValue() {
        $("#state_hidden").val($("#state").val());
    }
    $("#state").on("change", function () {
        syncStateValue();
    });
    $("#gstin").on("blur", function () {
        let gstin = $(this).val().trim();
        if (gstin === "") return;
    
        $.ajax({
            url: '{{ url("check-gstin-exists") }}',
            type: 'POST',
            dataType: 'JSON',
            data: {
                _token: '{{ csrf_token() }}',
                gstin: gstin,
                account_id: $("#gst_modal_account_id").val()
            },
            success: function (res) {
                if (res.exists === true) {
                    alert("This GST Number already exists.");
                    $("#gstin").val("").focus();
                    $("#pan").val("");
                    $("#address").val("");
                    $("#pincode").val("");
                    $("#state").val("").trigger('change');
                    $("#gstin").data("duplicate", true);
                } else {
                    $("#gstin").data("duplicate", false);
                }
            }
        });
    });
    $("#gstin").on("change", function () {
        if ($(this).data("duplicate") === true) return;
        let gstin = $(this).val().trim();
        if (gstin === "") return;
        $("#pan").val("");
        $("#address").val("");
        $("#pincode").val("");
        $("#state").val("").trigger('change');
        $.ajax({
            url: '{{ url("check-gstin") }}',
            type: 'POST',
            dataType: 'JSON',
            data: {
                _token: '{{ csrf_token() }}',
                gstin: gstin
            },
            success: function (data) {
    
                if (data && data.status == 1) {
    
                    let stateCode = gstin.substr(0, 2);
                    let matched = $('#state option[data-state_code="' + stateCode + '"]').val();
    
                    if (matched) {
                        $('#state').val(matched).trigger('change');
                        $('#state').on('select2:opening', function (e) {
                            e.preventDefault();
                        }).css('pointer-events', 'none');
                    }
    
                    $("#pan").val(gstin.substring(2, 12));
    
                    $("#address").val((data.address || "").toUpperCase());
                    $("#pincode").val(data.pinCode || "");
    
                    syncStateValue();
                } else {
                    alert(data.message || "Invalid GST Number");
                }
            }
        });
    });
    $('#gstAccountModal').on('hidden.bs.modal', function () {
        let accountId = $("#gst_modal_account_id").val();
        let opt = $("#vendor option[value='" + accountId + "']");
        if (opt.length && (!opt.attr("data-gstin") || opt.attr("data-gstin").trim() === "")) {
        ignoreVendorChange = true;
          $("#vendor").val(null).trigger("change.select2");
        }
    
        // Reset modal fields (clean state)
        $("#gst_modal_account_id").val("");
        $("#gst_modal_account_name").text("");
        $("#gstin, #pan, #address, #pincode").val("");
        $("#state").val("").trigger("change");
    });

      $(document).on('keydown', '.add_more', function(event) {

      if (event.key === "Enter") {

         event.preventDefault();
         $(this).click();

      }

      });
      $(document).on('keydown', '.remove', function(event) {

      if (event.key === "Enter") {

         event.preventDefault();
         $(this).click();

      }

      });
      $(document).on("keydown", ".narration", function(e){

         if(e.key === "Tab" && !e.shiftKey){

            let id = $(this).data("id");
            let removeBtn = $("#tr_"+id).find(".remove");

            if(removeBtn.length){
                  e.preventDefault();
                  removeBtn.focus();
            }

         }

      });
      $(document).on("keydown", ".remove", function(e){

         if(e.key === "Tab" && !e.shiftKey){

            e.preventDefault();

            let id = $(this).data("id");
            let nextRow = $("#tr_" + (parseInt(id) + 1));

            if(nextRow.length){
                  nextRow.find(".type").focus();
            }else{
                  $(".add_more").focus();
            }

         }

      });
      $(document).on("keydown", "input, textarea", function(e) {

         if (e.key === "Enter") {

            if ($(e.target).closest(".add_more, .remove, .submit_data").length) {
                  return;
            }

            if ($(this).hasClass("select2-search__field")) {
                  return;
            }

            e.preventDefault();
            return false;
         }

      });
      $(document).on('change', 'select[id^="type_"]', function () {
      if(isAutoFillingJournal) return;
         const id = $(this).data('id');

         setTimeout(function(){
            $("#account_" + id).select2('open');
         },100);

      });
      $(document).on('select2:select', 'select[id^="account_"]', function () {

         const id = $(this).data('id');

         setTimeout(function(){

            if(!$("#debit_"+id).prop("readonly")){
                  $("#debit_"+id).focus();
            }else{
                  $("#credit_"+id).focus();
            }

         },100);

      });
      $(document).ready(function(){
         $("#date").focus();
      });
      $(document).on('select2:select', '#vendor', function () {

         setTimeout(function(){
            $("#item_1").select2('open');
         },100);

      });
      $(document).on('select2:select', '.item', function () {

         let id = $(this).data('index');

         setTimeout(function(){
            $("#percentage_" + id).focus();
         },100);

      });
      $(document).on('change', '.percentage', function () {

         let id = $(this).data('index');

         setTimeout(function(){
            $("#amount_" + id).focus();
         },100);

      });
      $(document).on('keydown', '.amount', function(e){

         if(e.key === "Tab" && !e.shiftKey){

            e.preventDefault();

            let row = $(this).closest("tr");

            let removeBtn = row.find(".remove_more_tr");
            let addBtn = row.find(".add_more_tr");

            if(removeBtn.length){
                  removeBtn.focus();
            }else if(addBtn.length){
                  addBtn.focus();
            }

         }

      });
      $(document).on('keydown', '.add_more_tr', function(event) {

      if (event.key === "Enter") {

         event.preventDefault();
         $(this).click();

      }

      });
      $(document).on('keydown', '.remove_more_tr', function(e){

         if(e.key === "Tab" && !e.shiftKey){

            e.preventDefault();

            let row = $(this).closest("tr");
            row.find(".add_more_tr").focus();

         }

      });
      $(document).on('keydown', '.remove_more_tr', function(e){

         if(e.key === "Enter"){

            e.preventDefault();
            $(this).click();

         }

      });
      $(document).on('keydown', '.add_more_tr', function(e){

         if(e.key === "Enter"){

            e.preventDefault();
            $(this).click();

         }

      });
      $(document).ready(function(){

         $('#series_no').change(function(){

         let selected = $(this).find(':selected');

         let prefix = selected.data('invoice_prefix') || '';
         let manual = selected.data('manual_enter_invoice_no');

         let start = selected.data('invoice_start_from') || '';
         $('#voucher_prefix').val(prefix);
         $('#voucher_no').val(start);
         $('#manual_enter_invoice_no').val(manual ?? '');

         if(manual === undefined || manual === null || manual === ''){
            $('#voucher_prefix').prop('readonly', false);
         }
         else if(manual == '1'){
            $('#voucher_prefix').prop('readonly', false);
         }
         else if(manual == '0'){
            $('#voucher_prefix').prop('readonly', true);
         }

      });

         $('#series_no').trigger('change');

      });
</script>
@endsection