@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style type="text/css">
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
                    <nav aria-label="breadcrumb meri-breadcrumb ">
                        <ol class="breadcrumb meri-breadcrumb m-0  ">
                            <li class="breadcrumb-item">
                                <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                            <a class="fw-bold font-heading font-12  text-decoration-none" href="#">
                            Contra</a>
                            </li>
                        </ol>
                    </nav>
                </div>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Add Contra Voucher
                </h5>
                <?php 
                $account_html = '<option value="">Select</option>';            
                foreach ($party_list as $value) {
                   $account_html.='<option value="'.$value->id.'">'.$value->account_name.'</option>';
                }
                ?>
                <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('contra.store') }}">
                    @csrf
                    <div class="row">

                        <div class="mb-2 col-md-2">
                            <label for="name" class="form-label font-14 font-heading">Date</label>
                            <input type="date" id="date" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required value="{{$date}}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}">
                        </div>
                        <div class="mb-2 col-md-2">
                           <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                           <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No.">
                        </div>
                              <div class="mb-2 col-md-2">
                           <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                           <select id="series_no" class="form-control" name="series_no">
                              <option value="">Select Series</option>
                              <?php
                              if(count($mat_series) > 0) {
                                 foreach ($mat_series as $value) { ?>
                                    <option value="<?php echo $value['branch_series']; ?>"><?php echo $value['branch_series']; ?></option>
                                    <?php 
                                 }
                              } ?>
                           </select>
                        </div>
                        <div class="mb-2 col-md-2">
                           <label for="name" class="form-label font-14 font-heading">Mode</label>
                           <select id="mode" class="form-control" name="mode">
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
                                        <select class="form-control type" name="type[]" data-id="1" id="type_1">
                                            <option value="">Type</option>
                                            <option value="Credit" selected>Credit</option>
                                            <option value="Debit">Debit</option>
                                        </select>
                                    </td>
                                    <td class="">
                                        <select class="form-select" id="account_1" name="account_name[]" required>
                                            <option value="">Select</option>
                                            <?php
                                            foreach ($party_list as $value) { ?>
                                                <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class="">
                                        <input type="text" name="debit[]" class="form-control debit" data-id="1" id="debit_1" placeholder="Debit Amount" readonly onkeyup="debitTotal();">
                                    </td>
                                    <td class="">
                                        <input type="text" name="credit[]" class="form-control credit" data-id="1" id="credit_1" placeholder="Credit Amount" onkeyup="creditTotal();">
                                    </td>
                                    
                                    <td class="">
                                        <input type="text" name="narration[]" class="form-control narration" data-id="1" id="narration_1" placeholder="Enter Narration" value="">
                                    </td>
                                </tr>
                                <tr class="font-14 font-heading bg-white">
                                    <td class="">
                                        <select class="form-control type" name="type[]" data-id="2" id="type_2">
                                            <option value="">Type</option>
                                            <option value="Credit">Credit</option>
                                            <option value="Debit" selected>Debit</option>
                                        </select>
                                    </td>
                                    <td class="">
                                        <select class="form-select" id="account_2" name="account_name[]" required>
                                            <option value="">Select</option>
                                            <?php
                                            foreach ($party_list as $value) { ?>
                                                <option value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td class="">
                                        <input type="text" name="debit[]" class="form-control debit" data-id="2" id="debit_2" placeholder="Debit Amount" onkeyup="debitTotal();">
                                    </td>
                                    <td class="">
                                        <input type="text" name="credit[]" class="form-control credit" data-id="2" id="credit_2" placeholder="Credit Amount" readonly onkeyup="creditTotal();">
                                    </td>
                                    
                                    <td class="">
                                        <input type="text" name="narration[]" class="form-control narration" data-id="2" id="narration_2" placeholder="Enter Narration" value="">
                                    </td>
                                </tr>
                            </tbody>
                            <div class="plus-icon">
                                <tr class="font-14 font-heading bg-white">
                                    <!-- icon 3 tr ma joi aavi nathi rahyo -->
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
                           <a href="{{ route('contra.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                            <input type="button" value="SUBMIT" class="btn btn-xs-primary submit_data">

                        </div>

                    </div>
                </form>
            </div>
            <!-- <div class="col-lg-1 d-flex justify-content-center">
                <div class="shortcut-key ">
                    <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
                    <button class="p-2 transaction-shortcut-btn my-2 ">
                        F1
                        <span class="ps-1 fw-normal text-body">Help</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F1</span>
                        <span class="ps-1 fw-normal text-body">Add Account</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F2</span>
                        <span class="ps-1 fw-normal text-body">Add Item</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        F3
                        <span class="ps-1 fw-normal text-body">Add Master</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F3</span>
                        <span class="ps-1 fw-normal text-body">Add Voucher</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F5</span>
                        <span class="ps-1 fw-normal text-body">Add Payment</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F6</span>
                        <span class="ps-1 fw-normal text-body">Add Receipt</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F7</span>
                        <span class="ps-1 fw-normal text-body">Add Journal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F8</span>
                        <span class="ps-1 fw-normal text-body">Add Sales</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 ">
                        <span class="border-bottom-black">F9</span>
                        <span class="ps-1 fw-normal text-body">Add Purchase</span>
                    </button>

                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">B</span>
                        <span class="ps-1 fw-normal text-body">Balance Sheet</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">T</span>
                        <span class="ps-1 fw-normal text-body">Trial Balance</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">S</span>
                        <span class="ps-1 fw-normal text-body">Stock Status</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">L</span>
                        <span class="ps-1 fw-normal text-body">Acc. Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">I</span>
                        <span class="ps-1 fw-normal text-body">Item Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">D</span>
                        <span class="ps-1 fw-normal text-body">Item Ledger</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">G</span>
                        <span class="ps-1 fw-normal text-body">GST Summary</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">U</span>
                        <span class="ps-1 fw-normal text-body">Switch User</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">F</span>
                        <span class="ps-1 fw-normal text-body">Configuration</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="border-bottom-black">K</span>
                        <span class="ps-1 fw-normal text-body">Lock Program</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="ps-1 fw-normal text-body">Training Videos</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-2 ">
                        <span class="ps-1 fw-normal text-body">GST Portal</span>
                    </button>
                    <button class="p-2 transaction-shortcut-btn mb-4 ">
                        Search Menu
                    </button>
                </div>
            </div> -->
        </div>
</div>
</section>
</div>

</body>
@include('layouts.footer')
<script>
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
      if($("#type_" + id).val() == "Credit"){
         $("#debit_" + id).prop('readonly', true);
         $("#credit_" + id).prop('readonly', false);
         let amount = debit_total - credit_total;
         if(amount>0){
            $("#credit_"+id).val(amount);
         }
         $("#account_"+id).html('<?php echo $account_html;?>');
      }else if ($("#type_" + id).val() == "Debit") {
         $("#debit_" + id).prop('readonly', false);
         $("#credit_" + id).prop('readonly', true);
         let amount = credit_total - debit_total;
         if(amount>0){
            $("#debit_"+id).val(amount);
         }
         $("#account_"+id).html('<?php echo $account_html;?>');
      }       
      debitTotal();
      creditTotal();
   });
   var add_more_count = 2;
   $(".add_more").click(function() {
        add_more_count++;
        var $curRow = $(this).closest('tr');
        var optionElements = $('#account_1').html();
        newRow = '<tr id="tr_' + add_more_count + '"><td><select class="form-control type" name="type[]"  data-id="' + add_more_count + '" id="type_' + add_more_count + '"><option value="">Type</option><option value="Credit">Credit</option><option value="Debit">Debit</option></select></td><td><select class="form-control account select2" name="account_name[]" data-id="' + add_more_count + '" id="account_' + add_more_count + '">';
        newRow += optionElements;
        newRow += '</select></td><td><input type="text" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td><td><input type="text" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
        $curRow.before(newRow);
    });

    $(document).on("click", ".remove", function() {
        let id = $(this).attr('data-id');
        $("#tr_" + id).remove();
        debitTotal();
        creditTotal();
    });
    $(document).ready(function() {
       $(".submit_data").click(function() {
          var form_data = [];
          let dr = 0;
          let cr = 0;
          let ids = 0;
          let error = false;
          $(".type").each(function() {
             let id = $(this).attr('data-id');
             if($(this).val() != '' && $("#account_" + id).val() != "" && $("#date").val()!='' && $("#mode").val()!='') {
                if($(this).val() == "Credit" && $("#credit_" + id).val() != ""  && $("#account_" + id).val() != "") {
                   form_data.push({
                      "type": "Credit",
                      "credit": $("#credit_" + id).val(),
                      "debit": 0,
                      "user_id": $("#account_" + id).val(),
                      "remark": $("#narration_" + id).val()
                   });
                   cr = parseFloat(cr) + parseFloat($("#credit_" + id).val());
                }else if($(this).val() == "Debit" && $("#debit_" + id).val() != ""  && $("#account_" + id).val() != "") {
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

    $("#mode").change(function(){
       $("#cheque_no").val('');
       $("#cheque_no").prop('readonly',true);
       if($(this).val()==2){
          $("#cheque_no").prop('readonly',false);
       }
    });
   $(document).on("change",".debit",function(){
      let id = $(this).attr('data-id');
      let ind = parseInt(id)+1;
      $("#type_"+ind).val('Credit');
      $("#type_"+ind).change();
      $("#credit_"+ind).prop("disabled",false);
      $("#credit_"+ind).val($(this).val());
      debitTotal();
      creditTotal();
   });
   $(document).on("change",".credit",function(){
      let id = $(this).attr('data-id');
      let ind = parseInt(id)+1;
      $("#type_"+ind).val('Debit');
      $("#type_"+ind).change();
      $("#debit_"+ind).prop("disabled",false);
      $("#debit_"+ind).val($(this).val());
      debitTotal();
      creditTotal();
   });
</script>
@endsection