@extends('layouts.app')
@section('content')
@include('layouts.header')
<style type="text/css">
   .form-control {
      height: 52px !important;
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
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Receipt </a>
                     </li>
                  </ol>
               </nav>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Edit Receipt Voucher
            </h5>
            <?php 
            $credit_html = "<option value=''>Select</option>";            
            foreach ($party_list as $value) {
               $credit_html.="<option value='".$value->id."'>".$value->account_name."</option>";
            } 
            $debit_html = "<option value=''>Select</option>";            
            foreach ($debit_cash_accounts as $value) {
               $debit_html.="<option value='".$value->id."'>".$value->account_name."</option>";
            } 
            foreach ($debit_bank_accounts as $value) {
               $debit_html.="<option value='".$value->id."'>".$value->account_name."</option>";
            } 
            
            ?>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('receipt.update') }}">
               @csrf
               <div class="row">
                  <input type="hidden" value="{{ $receipt->id }}" id="receipt_id" name="receipt_id" />
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" value="<?php echo $receipt->date; ?>" min="{{ $fy_start_date }}" max="{{ $fy_end_date }}" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required>
                  </div>
                        <div class="mb-2 col-md-2">
                           <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                           <select id="series_no" class="form-control" name="series_no">
                              <option value="">Select Series</option>
                              <?php
                              if(count($mat_series) > 0) {
                                 foreach ($mat_series as $value) { ?>
                                    <option value="<?php echo $value->series; ?>"
                                       data-invoice_start_from="<?php echo $value->invoice_start_from ?? ''; ?>"
                                       data-invoice_prefix="<?php echo $value->invoice_prefix ?? ''; ?>"
                                       data-manual_enter_invoice_no="<?php echo $value->manual_enter_invoice_no ?? ''; ?>"
                                       @if($receipt->series_no==$value->series) selected @endif
                                    >
                                       <?php echo $value->series; ?>
                                    </option>
                                    <?php 
                                 }
                              } ?>
                           </select>
                        </div>
                        <div class="mb-2 col-md-2">
                           <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                           <input type="text" class="form-control" id="voucher_prefix"
                              name="voucher_prefix"
                              value="{{ $receipt->voucher_no_prefix ?? $receipt->voucher_no }}"
                              style="text-align:right;">
                           <input type="hidden" id="voucher_no" name="voucher_no" value="{{ $receipt->voucher_no }}">
                           <input type="hidden" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                        </div>
                        <div class="mb-2 col-md-2">
                           <label class="form-label font-14 font-heading">
                              Receipt Mode
                           </label>

                           <select id="receipt_mode" class="form-control">

                              <option value="">Select Receipt Account</option>

                              @foreach($debit_cash_accounts as $cash)
                                 <option value="{{ $cash->id }}"
                                       data-group="cash"
                                       {{ !empty($receipt_mode_account) && $receipt_mode_account->account_name == $cash->id ? 'selected' : '' }}>
                                    {{ $cash->account_name }}
                                 </option>
                              @endforeach

                              @foreach($debit_bank_accounts as $bank)
                                 <option value="{{ $bank->id }}"
                                       data-group="bank"
                                       {{ !empty($receipt_mode_account) && $receipt_mode_account->account_name == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->account_name }}
                                 </option>
                              @endforeach

                           </select>
                        </div>
                        <div class="mb-2 col-md-2">
                           <label class="form-label font-14 font-heading">
                              Receipt Type
                           </label>

                           <select id="mode"
                                 name="mode"
                                 class="form-control">

                              <option value="">Select Type</option>

                           </select>
                        </div>
                        <div class="mb-2 col-md-2">
                           <label for="name" class="form-label font-14 font-heading">Cheque No.</label>
                           <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Cheque No." @if($receipt->mode!=2) readonly @endif value="{{$receipt->cheque_no}}">
                        </div>
                    </div>

                    <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">
                        <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                           <thead>
                              <tr class=" font-12 text-body bg-light-pink ">
                                 <th style="width:55%;">Account</th>
                                 <th style="width:25%;">Amount</th>
                                 <th style="width:20%;">Action</th>
                              </tr>
                           </thead>
                           @php
                           $i = 1;
                           @endphp

                           <tbody>

                           @foreach($receipt_rows as $value)

                           <tr id="tr_{{ $i }}">

                              <td>
                                 <select class="form-select select2-single"
                                          id="account_{{ $i }}"
                                          data-id="{{ $i }}"
                                          name="account_name[]">

                                       <option value="">Select</option>

                                       @foreach($party_list as $party)

                                          <option value="{{ $party->id }}"
                                             {{ $value->account_name == $party->id ? 'selected' : '' }}>

                                             {{ $party->account_name }}

                                          </option>

                                       @endforeach

                                 </select>
                              </td>

                              <td>
                                 <input type="number"
                                          class="form-control amount"
                                          data-id="{{ $i }}"
                                          id="amount_{{ $i }}"
                                          value="{{ $value->credit }}"
                                          placeholder="Amount">
                              </td>

                              <td class="text-center align-middle">
                                 <div class="action-buttons"></div>
                              </td>

                           </tr>

                           @php $i++; @endphp

                           @endforeach

                           </tbody>
                            <div class="total">
                                <tr class="font-14 font-heading bg-white">
                                    <td class="fw-bold">Total</td>
                                    <td class="fw-bold" id="total_amount">0.00</td>
                                    <td></td>
                                </tr>
                                <tr class="font-14 font-heading bg-white">
                           <td colspan="6"><input type="text" class="form-control" placeholder="Enter Long Narration" name="long_narration" value="{{$receipt->long_narration}}"></td>
                        </tr>
                            </div>
                        </table>
                    </div>


                    <div class=" d-flex">

                        <div class="ms-auto">
                            <button type="button" onclick="redirectBack()" class="btn btn-danger">QUIT</button>
                            <input type="button" value="SUBMIT" class="btn btn-xs-primary submit_data">

                        </div>
                       

                    </div>
                    <input type="hidden" id="receipt_account" name="receipt_account" value="{{ !empty($receipt_mode_account) ? $receipt_mode_account->account_name : '' }}">
                </form>
            </div>
        </div>
</div>
</section>
</div>

</body>
@include('layouts.footer')
<script>
   const partyList = @json($party_list); // Debit accounts
   const cashAccounts = @json($debit_cash_accounts); // Credit - mode 1
   const bankAccounts = @json($debit_bank_accounts); // Credit - mode 0 or 2
function redirectBack(){
      let previousUrl = document.referrer; // Get Previous URL

      if(previousUrl == "{{ session('previous_url_receipt')  }}"){
         window.location.href = "https://www.meriaccounting.com/receipt"; // Fixed Redirect
      }else{
         history.back(); // Go Back to previous page
      }
   }
   
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

         total += parseFloat($(this).val()) || 0;

      });

      $('#total_amount').html(total.toFixed(2));

   }

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

                  @foreach($party_list as $value)
                     <option value="{{ $value->id }}">
                           {{ $value->account_name }}
                     </option>
                  @endforeach

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

   $(document).on('keyup change', '.amount', function(){

      calculateTotal();

   });
   function creditTotal() {
      let total_credit_amount = 0;
      $(".credit").each(function() {
         if ($(this).val() != '') {
            total_credit_amount = parseFloat(total_credit_amount) + parseFloat($(this).val());
         }
      });
      $("#total_credit").html(total_credit_amount);
   }
   $(document).on('change', '#receipt_mode', function(){

      let accountType = $('#receipt_mode option:selected').attr('data-group');

      $('#receipt_account').val($(this).val());

      let options = '<option value="">Select Type</option>';

      let defaultValue = '';

      if(accountType === 'bank'){

         options += '<option value="0">IMPS/NEFT/RTGS</option>';
         options += '<option value="2">CHEQUE</option>';

         defaultValue = '0';

      }
      else if(accountType === 'cash'){

         options += '<option value="1">CASH</option>';
         options += '<option value="2">CHEQUE</option>';

         defaultValue = '1';

      }

      $('#mode').html(options);
      $('#mode').val(defaultValue).trigger('change');

   });
   $("#mode").change(function(){
      $("#cheque_no").val('');
      $("#cheque_no").prop('readonly',true);
      if($(this).val()==2){
         $("#cheque_no").prop('readonly',false);
      }
   });
   $(document).ready(function() {
      $(".submit_data").click(function() {
         let from_date = "{{ $fy_start_date }}";

         let to_date = "{{ $fy_end_date }}";

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

         if($("#receipt_mode").val() == ""){
            alert("Please select Receipt Mode.");
            return false;
         }

         /* remove old generated fields */
         $('.generated-field').remove();

         /* Credit Entries (Party Accounts) */

         $('.amount').each(function(){

            let amount = parseFloat($(this).val()) || 0;

            $('<input>',{
               type:'hidden',
               name:'type[]',
               value:'Credit',
               class:'generated-field'
            }).appendTo('#frm');

            $('<input>',{
               type:'hidden',
               name:'credit[]',
               value:amount,
               class:'generated-field'
            }).appendTo('#frm');

            $('<input>',{
               type:'hidden',
               name:'debit[]',
               value:0,
               class:'generated-field'
            }).appendTo('#frm');

         });

         /* Debit Entry (Receipt Mode Account) */

         $('<input>',{
            type:'hidden',
            name:'account_name[]',
            value:$('#receipt_account').val(),
            class:'generated-field'
         }).appendTo('#frm');

         $('<input>',{
            type:'hidden',
            name:'type[]',
            value:'Debit',
            class:'generated-field'
         }).appendTo('#frm');

         $('<input>',{
            type:'hidden',
            name:'debit[]',
            value:totalAmount,
            class:'generated-field'
         }).appendTo('#frm');

         $('<input>',{
            type:'hidden',
            name:'credit[]',
            value:0,
            class:'generated-field'
         }).appendTo('#frm');

         $("#frm").submit();
      });
   });
   creditTotal();

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
$(document).ready(function () {

  $('.select2-single').select2({
    placeholder: "Select",
    allowClear: true,
    width: '100%'
  });

});
$(document).on('select2:select select2:unselect select2:close', 'select[id^="type_"]', function () {

    const id = $(this).data('id');
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');

    if (!previousValue || selectedValue === previousValue) {
        $("#account_" + id).focus();
    }

    $(this).data('previousValue', selectedValue);

});
$(document).on('select2:select select2:unselect select2:close', 'select[id^="account_"]', function () {

    const id = $(this).data('id');
    const selectedValue = $(this).val();
    const previousValue = $(this).data('previousValue');

    if (!previousValue || selectedValue === previousValue) {

        if(!$("#debit_"+id).prop("readonly")){
            $("#debit_"+id).focus();
        }else{
            $("#credit_"+id).focus();
        }

    }

    $(this).data('previousValue', selectedValue);

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
$(document).ready(function(){
   $("#date").focus();
});
$(document).ready(function(){

    let isEditPage = true;

    $('#series_no').change(function(){

        let selected = $(this).find(':selected');

        let prefix = selected.data('invoice_prefix') ?? '';
        let manual = selected.attr('data-manual_enter_invoice_no');

        if(!isEditPage){

            if(manual == '0'){ 
                let start = selected.data('invoice_start_from') ?? '';
                $('#voucher_prefix').val(prefix);
                $('#voucher_no').val(start); 
            }else{
                $('#voucher_prefix').val('');
                $('#voucher_no').val('');
            }

        }

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

        isEditPage = false;

    });
   $('#receipt_mode').trigger('change');
   refreshButtons();
   calculateTotal();
    $('#series_no').trigger('change');

});
</script>
@endsection