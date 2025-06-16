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
               <nav aria-label="breadcrumb meri-breadcrumb ">
                  <ol class="breadcrumb meri-breadcrumb m-0  ">
                     <li class="breadcrumb-item">
                        <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                     </li>
                     <li class="breadcrumb-item p-0">
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Payment </a>
                     </li>
                  </ol>
               </nav>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Add Payment Voucher
            </h5>
            <?php 
            $debit_html = "<option value=''>Select</option>";            
            foreach ($party_list as $value) {
               $debit_html.="<option value='".$value->id."'>".$value->account_name.'</option>';
            } 
         
            ?>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('payment.store') }}">
               @csrf
               <div class="row">
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" autofocus required value="{{$date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                     <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No.">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" class="form-control select2-single" name="series_no">
                        <option value="">Select Series</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->series; ?>" <?php if(count($mat_series)==1) { echo "selected";} ?>><?php echo $value->series; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                  </div>
                  <div class="mb-2 col-md-2 ">
                     <label for="name" class="form-label font-14 font-heading">Mode</label>
                     <select id="mode" class="form-control select2-single" name="mode">
                        <option value="">Select Mode</option>
                        <option value="0">IMPS/NEFT/RTGS</option>
                        <option value="1">CASH</option>                        
                        <option value="2">CHEQUE</option>
                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Cheque No.</label>
                     <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Cheque No." readonly>
                  </div>
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th style="width:10%;"class="w-min-120 border-none bg-light-pink text-body ">Debit/Credit</th>
                           <th style="width:35%;"class="w-min-120 border-none bg-light-pink text-body ">Account</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Debit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Credit</th>                           
                           <th class="w-min-120 border-none bg-light-pink text-body ">Narration</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr class="font-14 font-heading bg-white">
                           <td class="">
                              <select class="form-control type select2-single" name="type[]" data-id="1" id="type_1">
                                 <option value="">Type</option>
                                 <option value="Credit">Credit</option>
                                 <option value="Debit" selected>Debit</option>
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
                              <input type="number" name="debit[]" class="form-control debit" data-id="1" id="debit_1" placeholder="Debit Amount" onkeyup="debitTotal();" >
                           </td>
                           <td class="">
                              <input type="number" name="credit[]" class="form-control credit" data-id="1" id="credit_1" placeholder="Credit Amount" readonly onkeyup="creditTotal();">
                           </td>                           
                           <td class="">
                              <input type="text" name="narration[]" class="form-control narration" data-id="1" id="narration_1" placeholder="Enter Narration" value="">
                           </td>
                        </tr>
                        <tr class="font-14 font-heading bg-white">
                           <td class="">
                              <select class="form-control type" name="type[]" data-id="2" id="type_2">
                                 <option value="">Type</option>
                                 <option value="Credit" selected>Credit</option>
                                 <option value="Debit">Debit</option>
                              </select>
                           </td>
                          <td class="">
    <select class="form-select select2-single account-dropdown" id="account_1" data-id="1" name="account_name[]" required>
        <option value="">Select</option>
        <!-- Cash Accounts -->
        @foreach($credit_cash_accounts as $cash)
            <option value="{{ $cash->id }}" class="account-option mode-cash" >
                {{ $cash->account_name }}
            </option>
        @endforeach

        <!-- Bank Accounts -->
        @foreach($credit_bank_accounts as $bank)
            <option value="{{ $bank->id }}" class="account-option mode-bank" >
                {{ $bank->account_name }}
            </option>
        @endforeach
    </select>
</td>

                           <td class="">
                              <input type="number" name="debit[]" class="form-control debit" data-id="2" id="debit_2" placeholder="Debit Amount" readonly onkeyup="debitTotal();">
                           </td>
                           <td class="">
                              <input type="number" name="credit[]" class="form-control credit" data-id="2" id="credit_2" placeholder="Credit Amount" onkeyup="creditTotal();">
                           </td>                           
                           <td class="">
                              <input type="text" name="narration[]" class="form-control narration" data-id="2" id="narration_2" placeholder="Enter Narration" value="">
                           </td>
                        </tr>
                     </tbody>
                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                 <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
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
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <a href="{{ route('payment.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     <input type="button" value="SUBMIT" class="btn btn-xs-primary submit_data">
                  </div>
                  
               </div>
            </form>
         </div>
         
      </div>
   </section>
</div>
</body>
@include('layouts.footer')
<script>

   const partyList = @json($party_list); // Debit accounts
const cashAccounts = @json($credit_cash_accounts); // Credit - mode 1
const bankAccounts = @json($credit_bank_accounts); // Credit - mode 0 or 2

  
   $(document).on("change", ".type", function() {
      let id = $(this).attr('data-id');
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
      if($("#type_" + id).val() == "Credit") {
         $("#debit_" + id).prop('readonly', true);
         $("#credit_" + id).prop('readonly', false);
         let amount = debit_total - credit_total;
         if(amount>0){
            $("#credit_"+id).val(amount);
         }
        
      }else if ($("#type_" + id).val() == "Debit") {
         $("#debit_" + id).prop('readonly', false);
         $("#credit_" + id).prop('readonly', true);
         let amount = credit_total - debit_total;
         if(amount>0){
            $("#debit_"+id).val(amount);
         }
         $("#account_"+id).html("<?php echo $debit_html;?>");
      }     
      debitTotal();
      creditTotal();
   });
   let add_more_count = 2;

$(".add_more").click(function () {
    add_more_count++;
    const $curRow = $(this).closest('tr');

    const newRow = `
    <tr id="tr_${add_more_count}">
        <td>
            <select class="form-control type" name="type[]" data-id="${add_more_count}" id="type_${add_more_count}">
                <option value="">Type</option>
                <option value="Credit">Credit</option>
                <option value="Debit">Debit</option>
            </select>
        </td>
        <td>
            <select class="form-control account-dropdown select2-single" name="account_name[]" data-id="${add_more_count}" id="account_${add_more_count}">
                <option value="">Select</option>
            </select>
        </td>
        <td><input type="number" name="debit[]" class="form-control debit" data-id="${add_more_count}" id="debit_${add_more_count}" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td>
        <td><input type="number" name="credit[]" class="form-control credit" data-id="${add_more_count}" id="credit_${add_more_count}" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td>
        <td><input type="text" name="narration[]" class="form-control narration" data-id="${add_more_count}" id="narration_${add_more_count}" placeholder="Enter Narration"></td>
        <td>
            <svg style="color: red; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="${add_more_count}" viewBox="0 0 16 16">
                <path d="M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
            </svg>
        </td>
    </tr>`;

    $curRow.before(newRow);
    $('.select2-single').select2();

    // Add change event handler for new Type dropdown
    $(`#type_${add_more_count}`).change(function () {
        const selectedType = $(this).val();
        const accountDropdown = $(`#account_${add_more_count}`);
        const currentMode = $('#mode').val(); // Get mode from mode dropdown

        accountDropdown.empty().append(`<option value="">Select</option>`);

        if (selectedType === 'Debit') {
            partyList.forEach(item => {
                accountDropdown.append(`<option value="${item.id}">${item.account_name}</option>`);
            });
        } else if (selectedType === 'Credit') {
            if (currentMode == 1) {
                cashAccounts.forEach(item => {
                    accountDropdown.append(`<option value="${item.id}">${item.account_name}</option>`);
                });
            } else if (currentMode == 0 || currentMode == 2) {
                bankAccounts.forEach(item => {
                    accountDropdown.append(`<option value="${item.id}">${item.account_name}</option>`);
                });
            }
        }

        accountDropdown.val('').trigger('change.select2');
    });
});

   $(document).on("click", ".remove", function() {
      let id = $(this).attr('data-id');
      $("#tr_" + id).remove();
      debitTotal();
      creditTotal();
   });
   $(document).ready(function() {
      $(".submit_data").click(function () {
        let date = $("#date").val();
        let mode = $("#mode").val();
        let series_no = $("#series_no").val(); // Added series_no validation

        // Field-wise alerts
        if (date === "") {
            alert("Please enter the Date.");
            return false;
        }

        if (mode === "") {
            alert("Please select a Mode.");
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
         $(".type").each(function() {
            let id = $(this).attr('data-id');
            if($(this).val() != '' && $("#account_" + id).val() != "" && $("#date").val()!='') {
               if($(this).val() == "Credit" && $("#credit_" + id).val() != "" && $("#account_" + id).val() != "") {
                  form_data.push({
                     "type": "Credit",
                     "credit": $("#credit_" + id).val(),
                     "debit": 0,
                     "user_id": $("#account_" + id).val(),
                     "remark": $("#narration_" + id).val()
                  });
                  cr = parseFloat(cr) + parseFloat($("#credit_" + id).val());
               }else if($(this).val() == "Debit" && $("#debit_" + id).val() != "" && $("#account_" + id).val() != "") {

                  form_data.push({
                     "type": "Debit",
                     "credit": 0,
                     "debit": $("#debit_" + id).val(),
                     "user_id": $("#account_" + id).val(),
                     "remark": $("#narration_" + id).val()
                  });
                  dr = parseFloat(dr) + parseFloat($("#debit_" + id).val());
               }
            }else{
               error = true; // Set error flag if any required field is empty
            }
         });
         if(error) {
            alert("Please fill in all required fields.");
            return false;
         }
         if(form_data.length == 0) {
            alert("Please enter at least one transaction.");
            return false;
         }
         if(cr != dr) {
            alert("Debit and credit amounts should be equal.");
            return false;
         }
         $("#frm").submit();
      });
   });
   function debitTotal() {
      let total_debit_amount = 0;
      $(".debit").each(function() {
         if($(this).val() != '') {
            total_debit_amount = parseFloat(total_debit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_debit").html(total_debit_amount);
   }
   function creditTotal() {
      let total_credit_amount = 0;
      $(".credit").each(function() {
         if ($(this).val() != '') {
            total_credit_amount = parseFloat(total_credit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_credit").html(total_credit_amount);
   }
   $( ".select2-single, .select2-multiple" ).select2(  );
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
 
   $(document).ready(function () {

    // Function to show/hide account options based on mode
    function updateAccountOptions(selectedMode) {
        // Hide all account options first
        $('.account-option').hide();

        if (selectedMode === "1") { // CASH
            $('.mode-cash').show();
        } else if (selectedMode === "0" || selectedMode === "2") { // BANK or CHEQUE
            $('.mode-bank').show();
        }

        // Reset all account dropdowns
        $('.account-dropdown').val('').trigger('change');
    }

    // On change of Mode dropdown
    $('#mode').change(function () {
        var modeValue = $(this).val();
        console.log("Mode dropdown changed to:", modeValue);
        updateAccountOptions(modeValue);
    });

});

$(document).ready(function () {
    let currentMode = $('#mode').val();

    function isOptionAllowed(data) {
        if (!data.id) return true; // For "Select" placeholder
        if (currentMode === "1") return $(data.element).hasClass('mode-cash');
        if (currentMode === "0" || currentMode === "2") return $(data.element).hasClass('mode-bank');
        return false;
    }

    $('.account-dropdown').select2({
        placeholder: "Select",
        templateResult: function (data) {
            return isOptionAllowed(data) ? data.text : null;
        },
        templateSelection: function (data) {
            return isOptionAllowed(data) ? data.text : '';
        }
    });

    $('#mode').on('change', function () {
        currentMode = $(this).val();
        $('.account-dropdown').val('').trigger('change.select2'); // Clear selection
        $('.account-dropdown').select2('close'); // Force close dropdown to refresh filtered list
    });
});


</script>
@endsection