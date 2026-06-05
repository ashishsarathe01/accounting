@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style type="text/css">
.select2-container--default .select2-selection--single {
  height: 50px !important;          /* increased for full text visibility */
  border: 1px solid #ced4da !important;
  border-radius: 0.4rem !important;    
  display: flex;
  align-items: center;
  padding: 0 0.75rem;               /* extra horizontal padding for text */
  font-size: 1rem;
  box-sizing: border-box;           /* ensures padding doesn't cut text */
}

/* Text inside */
.select2-container--default .select2-selection--single .select2-selection__rendered {
  color: #495057 !important;  
  line-height: 50px !important;    /* match container height for vertical centering */
  white-space: nowrap;              /* prevents wrapping inside the box */
  overflow: hidden;
  text-overflow: ellipsis;         /* shows ... if text is too long */
}

/* Arrow dropdown */
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 100% !important;
  display: flex;
  align-items: center;
  right: 8px;
}
.select2-selection__clear {
    display: none !important;
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
            $credit_html = "<option value=''>Select</option>";            
            foreach ($credit_cash_accounts as $value) {
               $credit_html.="<option value='".$value->id."'>".$value->account_name."</option>";
            } 
            foreach ($credit_bank_accounts as $value) {
               $credit_html.="<option value='".$value->id."'>".$value->account_name."</option>";
            }
            ?>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('payment.store') }}">
               @csrf
               <div class="row">
                  <div class="mb-2 col-md-2">
                     <label for="date" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" autofocus required value="{{$date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                  </div>
                  
                  <div class="mb-2 col-md-2">
                     <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" class="form-control select2-single" name="series_no">
                        <option value="">Select Series</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value->series; ?>"
                                 data-invoice_start_from="<?php echo $value->invoice_start_from ?? ''; ?>"
                                 data-invoice_prefix="<?php echo $value->invoice_prefix ?? ''; ?>"
                                 data-manual_enter_invoice_no="<?php echo $value->manual_enter_invoice_no ?? ''; ?>"
                              >
                                 <?php echo $value->series; ?>
                              </option>
                              <?php 
                           }
                        } ?>
                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="voucher_no" class="form-label font-14 font-heading">Voucher No.</label>
                     <input type="text" class="form-control" id="voucher_prefix" name="voucher_prefix" style="text-align:right;">
                     <input type="hidden" id="voucher_no" name="voucher_no">
                     <input type="hidden" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="payment_mode" class="form-label font-14 font-heading">
                        Payment Mode
                     </label>

                     <select id="payment_mode"
                           class="form-control select2-single">

                        <option value="">Select Payment Account</option>

                        @foreach($credit_cash_accounts as $cash)
                           <option value="{{ $cash->id }}" data-group="cash">
                              {{ $cash->account_name }}
                           </option>
                        @endforeach

                        @foreach($credit_bank_accounts as $bank)
                           <option value="{{ $bank->id }}" data-group="bank">
                              {{ $bank->account_name }}
                           </option>
                        @endforeach

                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="mode" class="form-label font-14 font-heading">
                        Payment Type
                     </label>

                     <select id="mode"
                           name="mode"
                           class="form-control select2-single">

                        <option value="">Select Type</option>

                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="cheque_no" class="form-label font-14 font-heading">Cheque No.</label>
                     <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Cheque No." readonly>
                  </div>
               </div>
               <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                  <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                     <thead>
                        <tr class="font-12 text-body bg-light-pink">
                           <th style="width:55%;">Account</th>
                           <th style="width:25%;">Amount</th>
                           <th style="width:20%;">Action</th>
                        </tr>
                     </thead>
                     <tbody>
                        <tr class="font-14 font-heading bg-white" id="tr_1">

                           <td>
                              <select class="form-select select2-single"
                                       id="account_1"
                                       data-id="1"
                                       name="account_name[]"
                                       required>

                                    <option value="">Select</option>

                                    <?php
                                    foreach ($party_list as $value) { ?>
                                       <option value="<?php echo $value->id; ?>">
                                          <?php echo $value->account_name; ?>
                                       </option>
                                    <?php } ?>

                              </select>
                           </td>

                           <td>
                              <input type="number"
                                    class="form-control amount"
                                    data-id="1"
                                    id="amount_1"
                                    placeholder="Amount">
                           </td>

                           <td class="text-center align-middle">
                              <div class="action-buttons"></div>
                           </td>

                        </tr>

                     </tbody>
                     <div class="total">
                        <tr class="font-14 font-heading bg-white">
                           <td class="fw-bold">Total</td>
                           <td class="fw-bold" id="total_amount">0.00</td>
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
               <input type="hidden" id="payment_account" name="payment_account">
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
         $("#account_"+id).html("<?php echo $credit_html;?>");
         $("#account_" + id).select2();
      }else if ($("#type_" + id).val() == "Debit") {
         $("#debit_" + id).prop('readonly', false);
         $("#credit_" + id).prop('readonly', true);
         let amount = credit_total - debit_total;
         if(amount>0){
            $("#debit_"+id).val(amount);
         }
         $("#account_"+id).html("<?php echo $debit_html;?>");
         $("#account_" + id).select2();
      }     
      debitTotal();
      creditTotal();
   });
   let add_more_count = 1;

   function refreshButtons() {

      let rows = $('tr[id^="tr_"]');

      rows.each(function(index){

         let rowId = $(this).attr('id').replace('tr_','');

         let html = '';

         if(rows.length === 1){

               html = `
                  <a class="add_more" tabindex="0">
                     <svg xmlns="http://www.w3.org/2000/svg"
                           class="bg-primary rounded-circle"
                           width="24"
                           height="24"
                           viewBox="0 0 24 24"
                           fill="none"
                           style="cursor:pointer;">

                           <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z"
                                 fill="white"/>

                     </svg>
                  </a>
               `;

         } else {

               html = `
                  <svg xmlns="http://www.w3.org/2000/svg"
                        width="28"
                        height="28"
                        fill="#dc3545"
                        class="bi bi-dash-circle-fill remove"
                        data-id="${rowId}"
                        viewBox="0 0 16 16"
                        style="cursor:pointer;">

                     <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0M4 8a.5.5 0 0 0 .5.5h7a.5.5 0 0 0 0-1h-7A.5.5 0 0 0 4 8"/>

                  </svg>
               `;

               if(index === rows.length - 1){

                  html += `
                     <a class="add_more ms-2" tabindex="0">

                           <svg xmlns="http://www.w3.org/2000/svg"
                              class="bg-primary rounded-circle"
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              style="cursor:pointer;">

                              <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z"
                                    fill="white"/>

                           </svg>

                     </a>
                  `;
               }
         }

         $(this).find('.action-buttons').html(html);

      });

   }
   function calculateTotal() {

      let total = 0;

      $('.amount').each(function(){

         let amt = parseFloat($(this).val()) || 0;

         total += amt;

      });

      $('#total_amount').html(total.toFixed(2));

   }
   $(document).on('keyup change', '.amount', function(){

      calculateTotal();

   });
   $(document).on("click", ".add_more", function () {

      add_more_count++;

      let newRow = `
      <tr id="tr_${add_more_count}">

         <td>
               <select class="form-select select2-single"
                     id="account_${add_more_count}"
                     data-id="${add_more_count}"
                     name="account_name[]"
                     required>

                  <option value="">Select</option>

                  <?php foreach ($party_list as $value) { ?>
                     <option value="<?php echo $value->id; ?>">
                           <?php echo $value->account_name; ?>
                     </option>
                  <?php } ?>

               </select>
         </td>

         <td>
               <input type="number"
                     class="form-control amount"
                     data-id="${add_more_count}"
                     id="amount_${add_more_count}"
                     placeholder="Amount">
         </td>

         <td class="text-center align-middle">
               <div class="action-buttons"></div>
         </td>

      </tr>
      `;

      $('tr[id^="tr_"]:last').after(newRow);

      $('.select2-single').select2();

      refreshButtons();

   });
   $(document).on("click", ".remove", function () {

      let id = $(this).data('id');

      $("#tr_" + id).remove();

      refreshButtons();

      calculateTotal();

   });
   $(document).ready(function() {
      $(".submit_data").click(function () {
        let date = $("#date").val();
        let from_date = "{{ Session::get('from_date') }}";

      let to_date = "{{ Session::get('to_date') }}";

      let selected_date = $("#date").val();

      if(
         selected_date < from_date
         ||
         selected_date > to_date
      ){
         alert(
            "Selected date is outside the current financial year."
         );

         $("#date").focus();

         return false;
      }
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

         let error = false;
         let totalAmount = 0;

         $('.amount').each(function(){

            let id = $(this).data('id');

            let account = $("#account_" + id).val();
            let amount  = $(this).val();

            if(account == "" || amount == "" || parseFloat(amount) <= 0){
               error = true;
            }

            totalAmount += parseFloat(amount || 0);

         });

         if(error){
            alert("Please fill all Account and Amount fields.");
            return false;
         }

         if(totalAmount <= 0){
            alert("Please enter at least one transaction.");
            return false;
         }

         if($("#payment_mode").val() == ""){
            alert("Please select Payment Mode.");
            return false;
         }

         $('.generated-field').remove();

         $('.amount').each(function(){

            let id = $(this).data('id');
            let amount = parseFloat($(this).val()) || 0;

            $('<input>',{
               type:'hidden',
               name:'type[]',
               value:'Debit',
               class:'generated-field'
            }).appendTo('#frm');

            $('<input>',{
               type:'hidden',
               name:'debit[]',
               value:amount,
               class:'generated-field'
            }).appendTo('#frm');

            $('<input>',{
               type:'hidden',
               name:'credit[]',
               value:0,
               class:'generated-field'
            }).appendTo('#frm');

         });

         $('<input>',{
            type:'hidden',
            name:'account_name[]',
            value:$('#payment_account').val(),
            class:'generated-field'
         }).appendTo('#frm');

         $('<input>',{
            type:'hidden',
            name:'type[]',
            value:'Credit',
            class:'generated-field'
         }).appendTo('#frm');

         $('<input>',{
            type:'hidden',
            name:'debit[]',
            value:0,
            class:'generated-field'
         }).appendTo('#frm');

         $('<input>',{
            type:'hidden',
            name:'credit[]',
            value:totalAmount,
            class:'generated-field'
         }).appendTo('#frm');

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
      $('#mode').change(function(){

      $("#cheque_no").val('');
      $("#cheque_no").prop('readonly', true);

      if($(this).val() == '2'){
         $("#cheque_no").prop('readonly', false);
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
$(document).ready(function() {
  // Properly initialize Select2 with search enabled
  $('#series_no').select2({
    placeholder: "Select Account",
    allowClear: true,
    width: '100%' // Ensure dropdown matches Bootstrap styling
  });

  // Move focus to next field after selecting an option
  $('#series_no').on('select2:select', function (e) {
    $('#mode').focus();
  });

  // Handle the case when the user clears the selection
  $('#series_no').on('select2:unselect', function (e) {
    $('#mode').focus();
  });

  // Handle the case when the user selects the same value again OR previous value not set
  $('#series_no').on('select2:close', function (e) {
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');

    // If same value OR no previous value, move focus
    if (!previousValue || selectedValue === previousValue) {
      $('#mode').focus();
    }

    // Update previous value
    $(this).data('previousValue', selectedValue);
  });
});
$(document).ready(function() {
  // Properly initialize Select2 with search enabled
  $('#mode').select2({
    placeholder: "Select Account",
    allowClear: true,
    width: '100%' // Ensure dropdown matches Bootstrap styling
  });

  // Move focus to next field after selecting an option
  $('#mode').on('select2:select', function (e) {
    $('#cheque_no').focus();
  });

  // Handle the case when the user clears the selection
  $('#mode').on('select2:unselect', function (e) {
    $('#cheque_no').focus();
  });

  // Handle the case when the user selects the same value again OR previous value not set
  $('#mode').on('select2:close', function (e) {
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');

    // If same value OR no previous value, move focus
    if (!previousValue || selectedValue === previousValue) {
      $('#cheque_no').focus();
    }

    // Update previous value
    $(this).data('previousValue', selectedValue);
  });
});
$(document).ready(function () {
  // Initialize all select2 dropdowns with the same class
  $('.select2-single').select2({
    placeholder: "Select",
    allowClear: true,
    width: '100%'
  });

  // When user selects an option in `type_x`, move focus to `account_x`
  $(document).on('select2:select select2:unselect select2:close', 'select[id^="type_"]', function (e) {
    const id = $(this).data('id'); // e.g. 1, 2, 3 ...
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');

    // If first time OR same value OR any selection, move focus
    if (!previousValue || selectedValue === previousValue) {
      $(`#account_${id}`).focus(); // focus on the corresponding account select
    }

    // Update previous value
    $(this).data('previousValue', selectedValue);
  });
});
$(document).ready(function () {
  // Initialize all select2 dropdowns with the same class
  $('.select2-single').select2({
    placeholder: "Select",
    allowClear: true,
    width: '100%'
  });

  // When user selects an option in `type_x`, move focus to `account_x`
  $(document).on('select2:select select2:unselect select2:close', 'select[id^="account_"]', function (e) {
    const id = $(this).data('id'); // e.g. 1, 2, 3 ...
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');

    // If first time OR same value OR any selection, move focus
    if (!previousValue || selectedValue === previousValue) {
      if(!$("#debit_"+id).prop("readonly")){
   $("#debit_"+id).focus();
}else{
   $("#credit_"+id).focus();
}
    }

    // Update previous value
    $(this).data('previousValue', selectedValue);
  });
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
    const submitBtn = document.querySelector('.submit_data');

// Function to change color
function setGreen() {
    submitBtn.style.backgroundColor = 'green';
}

// Function to reset color
function resetColor() {
    submitBtn.style.backgroundColor = ''; // original color
}

// Mouse hover
submitBtn.addEventListener('mouseenter', setGreen);
submitBtn.addEventListener('mouseleave', resetColor);

// Keyboard focus (tab)
submitBtn.addEventListener('focus', setGreen);
submitBtn.addEventListener('blur', resetColor);

submitBtn.addEventListener('blur', resetColor);
    $(".submit_data").on('keydown', function(event) {
      if (event.key === "Enter") {
        event.preventDefault(); // prevent default behavior
        $(this).click(); // trigger click event (which submits)
      }
    });
// Narration → focus remove button
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
// Remove → focus +
$(document).on("keydown", ".remove", function(e){

    if(e.key === "Tab" && !e.shiftKey){
        e.preventDefault();
        $(".add_more").focus();
    }

});
    // 🔒 Prevent Enter from moving to next field
$(document).on("keydown", "input, textarea", function(e) {

    if (e.key === "Enter") {

        // Allow Enter for action buttons
        if ($(e.target).closest(".add_more, .remove, .submit_data").length) {
            return;
        }

        // Allow Select2 search typing
        if ($(this).hasClass("select2-search__field")) {
            return;
        }

        e.preventDefault();
        return false;
    }

});
$(document).ready(function(){

    $('#series_no').change(function(){

        let selected = $(this).find(':selected');

        let prefix = selected.data('invoice_prefix') ?? '';
        let start = selected.data('invoice_start_from') ?? '';
        let manual = selected.attr('data-manual_enter_invoice_no');
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
   refreshButtons();
   calculateTotal();
    $('#series_no').trigger('change');

});
$('#payment_mode').change(function(){

    let accountId = $(this).val();

    $('#payment_account').val(accountId);

    let group = $(this).find(':selected').data('group');

    let html = '';

    if(group === 'bank'){

        html += '<option value="0">IMPS/NEFT/RTGS</option>';
        html += '<option value="2">CHEQUE</option>';

    }else if(group === 'cash'){

        html += '<option value="1">CASH</option>';
        html += '<option value="2">CHEQUE</option>';

    }

    $('#mode').html(html).trigger('change');

});
</script>
@endsection