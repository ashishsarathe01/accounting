@extends('layouts.app')
@section('content')
@include('layouts.header')
<style type="text/css">
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
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Payment Voucher</h5>
            <?php 
            
            ?>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('payment.update') }}">
               @csrf
               <div class="row">
                  <input type="hidden" value="{{ $payment->id }}" id="payment_id" name="payment_id" />
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" value="<?php echo $payment->date; ?>" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                     <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No." required value="{{$payment->voucher_no}}">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" class="form-control" name="series_no">
                        <option value="">Select Series</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->series; ?>" @if($payment->series_no==$value->series) selected @endif><?php echo $value->series; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Mode</label>
                     <select id="mode" class="form-control" name="mode">
                        <option value="">Select Mode</option>
                        <option value="0" @if($payment->mode==0) selected @endif>IMPS/NEFT/RTGS</option>
                        <option value="1" @if($payment->mode==1) selected @endif>CASH</option>                        
                        <option value="2" @if($payment->mode==2) selected @endif>CHEQUE</option>
                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Cheque No.</label>
                     <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Cheque No." @if($payment->mode!=2) readonly @endif value="{{$payment->cheque_no}}">
                  </div>
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th class="w-min-120 border-none bg-light-pink text-body ">Debit/Credit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Account</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Debit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Credit</th>
                           <th class="w-min-120 border-none bg-light-pink text-body ">Narration</th>
                        </tr>
                     </thead>
                     @php $i = 1; @endphp

@foreach($payment_detail as $value)
    <tbody>
        <tr class="font-14 font-heading bg-white" id="tr_{{ $i }}">
            <td>
                <select class="form-control type" name="type[]" data-id="{{ $i }}" id="type_{{ $i }}">
                    <option value="">Type</option>
                    <option value="Credit" {{ $value->type == 'Credit' ? 'selected' : '' }}>Credit</option>
                    <option value="Debit" {{ $value->type == 'Debit' ? 'selected' : '' }}>Debit</option>
                </select>
            </td>

            <td>
                <select class="form-select select2-single account-dropdown" id="account_{{ $i }}" name="account_name[]" required>
                    <option value="">Select</option>

                    @if($value->type == "Debit")
                        @foreach($party_list as $val)
                        
                            <option value="{{ $val->id }}"  class="account-option mode-bank mode-cash" {{ $value->account_name == $val->id ? 'selected' : '' }}>
                                {{ $val->account_name }}
                            </option>
                        @endforeach
                    @elseif($value->type == "Credit")
                        @foreach($credit_cash_accounts as $cash)
                            <option value="{{ $cash->id }}" class="account-option mode-cash" {{ $value->account_name == $cash->id ? 'selected' : '' }}>
                                {{ $cash->account_name }}
                            </option>
                        @endforeach

                        @foreach($credit_bank_accounts as $bank)
                            <option value="{{ $bank->id }}" class="account-option mode-bank" {{ $value->account_name == $bank->id ? 'selected' : '' }}>
                                {{ $bank->account_name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </td>

            <td>
                <input type="number" name="debit[]" value="{{ $value->debit }}" class="form-control debit" data-id="{{ $i }}" id="debit_{{ $i }}" placeholder="Debit Amount" {{ $value->type == "Credit" ? 'readonly' : '' }} onkeyup="debitTotal();">
            </td>

            <td>
                <input type="number" name="credit[]" value="{{ $value->credit }}" class="form-control credit" data-id="{{ $i }}" id="credit_{{ $i }}" placeholder="Credit Amount" {{ $value->type == "Debit" ? 'readonly' : '' }} onkeyup="creditTotal();">
            </td>

            <td>
                <input type="text" name="narration[]" value="{{ $value->narration }}" class="form-control narration" data-id="{{ $i }}" id="narration_{{ $i }}" placeholder="Enter Narration">
            </td>

            <td>
                <svg style="color: red; cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="{{ $i }}" viewBox="0 0 16 16">
                    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                </svg>
            </td>
        </tr>
    </tbody>

    @php $i++; @endphp
@endforeach

                     <div class="plus-icon">
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 " colspan="7">
                              <a class="add_more"><svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                 <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" /></svg></a>
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
                           <td colspan="6"><input type="text" class="form-control" placeholder="Enter Long Narration" name="long_narration" value="{{$payment->long_narration}}"></td>
                        </tr>
                     </div>
                  </table>
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <button type="button" onclick="redirectBack()" class="btn btn-danger">QUIT</button>
                     <input type="button" value="UPDATE" class="btn btn-xs-primary submit_data">
                  </div>
                  <input type="hidden" clas="max_sale_descrption" name="max_sale_descrption" value="1" id="max_sale_descrption">
                  <input type="hidden" name="max_sale_sundry" id="max_sale_sundry" value="1" />
               </div>
            </form>
         </div>
        </div>
</div>
</section>
</div>

</body>
@include('layouts.footer')
<script>
   const partyList = @json($party_list); // For Debit
    const cashAccounts = @json($credit_cash_accounts); // mode = 1
    const bankAccounts = @json($credit_bank_accounts); // mode = 0 or 2

     function redirectBack(){
      let previousUrl = document.referrer; // Get Previous URL

      if(previousUrl == "{{ session('previous_url_payment')  }}"){
         window.location.href = "https://www.meriaccounting.com/payment"; // Fixed Redirect
      }else{
         history.back(); // Go Back to previous page
      }
   }

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
       
      }
      debitTotal();
      creditTotal();
   });

let add_more_count = '{{ $i }}';

$(".add_more").click(function () {
    add_more_count++;

    const $curRow = $(this).closest('tr');
   let type_option = '<option value="Credit">Credit</option><option value="Debit">Debit</option>';
    $(".type").each(function(){
      if(this.value == 'Credit') {
         type_option = '<option value="Debit">Debit</option>';
      }
    });
    const newRow = `
    <tr id="tr_${add_more_count}">
        <td>
            <select class="form-control type" name="type[]" data-id="${add_more_count}" id="type_${add_more_count}">
                <option value="">Type</option>${type_option}
            </select>
        </td>
        <td>
            <select class="form-select select2-single account-dropdown" name="account_name[]" data-id="${add_more_count}" id="account_${add_more_count}">
                <option value="">Select</option>
            </select>
        </td>
        <td><input type="number" name="debit[]" class="form-control debit" data-id="${add_more_count}" id="debit_${add_more_count}" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td>
        <td><input type="number" name="credit[]" class="form-control credit" data-id="${add_more_count}" id="credit_${add_more_count}" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td>
        <td><input type="text" name="narration[]" class="form-control narration" data-id="${add_more_count}" id="narration_${add_more_count}" placeholder="Enter Narration"></td>
        <td>
            <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="${add_more_count}" viewBox="0 0 16 16">
                <path d="M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
            </svg>
        </td>
    </tr>`;

    $curRow.before(newRow);
    $('.select2-single').select2();

    // Attach change handler to the new Type select
    $(`#type_${add_more_count}`).change(function () {
        const selectedType = $(this).val();
        const accountDropdown = $(`#account_${add_more_count}`);
        const currentMode = $('#mode').val();
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
   $(".select2-single").select2();
   $(document).ready(function() {
      debitTotal();
      creditTotal();
      $(".submit_data").click(function() {
         var form_data = [];
         let dr = 0;
         let cr = 0;
         let ids = 0;
         let error = false;
         let credit_count = 0;
         $(".type").each(function() {
            let id = $(this).attr('data-id');
            if($(this).val() != '' && $("#account_" + id).val() != "" && $("#date").val()!='' && $("#mode").val()!='') {
               if($(this).val() == "Credit" && $("#credit_" + id).val() != "" && $("#account_" + id).val() != "") {
                  
                  form_data.push({
                     "type": "Credit",
                     "credit": $("#credit_" + id).val(),
                     "debit": 0,
                     "user_id": $("#account_" + id).val(),
                     "remark": $("#narration_" + id).val(),
                    
                  });
                  cr = parseFloat(cr) + parseFloat($("#credit_" + id).val());
                  credit_count++;
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
        
         if(credit_count >1) {
            alert("Only 1 Bank/Cash Account(Credit) Allowed.");
            return false;
         }
         if(cr != dr) {
            alert("Debit and credit amounts should be equal.");
            return false;
         }         
         $("#frm").submit();
      });
   });
   $("#mode").change(function(){
      $("#cheque_no").val('');
      $("#cheque_no").prop('readonly',true);
      if($(this).val()==2){
         $("#cheque_no").prop('readonly',false);
      }
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