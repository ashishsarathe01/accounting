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
                        <a class="fw-bold font-heading font-12  text-decoration-none" href="#">Contra </a>
                     </li>
                  </ol>
               </nav>
            </div>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Contra</h5>
            <?php 
            $debit_html = '<option value="">Select</option>';            
            foreach ($party_list as $value) {
               $debit_html.='<option value="'.$value->id.'">'.$value->account_name.'</option>';
            } 
            
            ?>
            <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('contra.update') }}">
               @csrf
               <div class="row">
                  <input type="hidden" value="{{ $contra->id }}" id="contra_id" name="contra_id" />
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Date</label>
                     <input type="date" id="date" value="<?php echo $contra->date; ?>" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                     <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No." required value="{{$contra->voucher_no}}">
                  </div>
                        <div class="mb-2 col-md-2">
                     <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                     <select id="series_no" class="form-control" name="series_no">
                        <option value="">Select Series</option>
                        <?php
                        if(count($mat_series) > 0) {
                           foreach ($mat_series as $value) { ?>
                              <option value="<?php echo $value['branch_series']; ?>" @if($contra->series_no==$value['branch_series']) selected @endif><?php echo $value['branch_series']; ?></option>
                              <?php 
                           }
                        } ?>
                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Mode</label>
                     <select id="mode" class="form-control" name="mode">
                        <option value="">Select Mode</option>
                        <option value="0" @if($contra->mode==0) selected @endif>IMPS/NEFT/RTGS</option>
                        <option value="1" @if($contra->mode==1) selected @endif>CASH</option>                        
                        <option value="2" @if($contra->mode==2) selected @endif>CHEQUE</option>
                     </select>
                  </div>
                  <div class="mb-2 col-md-2">
                     <label for="name" class="form-label font-14 font-heading">Cheque No.</label>
                     <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Cheque No." @if($contra->mode!=2) readonly @endif value="{{$contra->cheque_no}}">
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
                     <?php
                     $i=1;
                     foreach($contra_detail as $value) { ?>
                        <tbody>
                           <tr class="font-14 font-heading bg-white" id="tr_<?php echo $i?>">
                              <td class="">
                                 <select class="form-control type" name="type[]" data-id="<?php echo $i?>" id="type_<?php echo $i?>">
                                    <option value="">Type</option>
                                    <option <?php echo $value->type == 'Credit' ? 'selected' : ''; ?> value="Credit">Credit</option>
                                    <option <?php echo $value->type == 'Debit' ? 'selected' : ''; ?> value="Debit">Debit</option>
                                 </select>
                              </td>
                              <td class="">
                                 <select class="form-select select2-single" id="account_<?php echo $i?>" name="account_name[]" required>
                                    <option value="">Select</option>
                                    <?php                                    
                                    foreach ($party_list as $val) {
                                       $sel = '';
                                       if($value->account_name == $val->id){
                                          $sel = 'selected';
                                       }?>
                                       <option <?php echo $sel ?> value="<?php echo $val->id; ?>"><?php echo $val->account_name; ?></option>
                                          <?php 
                                    }                                    
                                    ?>
                                 </select>
                              </td>
                              <td class="">
                                 <input type="number" name="debit[]" value="<?php echo $value->debit; ?>" class="form-control debit" data-id="<?php echo $i?>" id="debit_<?php echo $i?>" placeholder="Debit Amount" <?php if($value->type=="Credit"){ echo 'readonly'; }?> onkeyup="debitTotal();">
                              </td>
                              <td class="">
                                 <input type="number" name="credit[]" value="<?php echo $value->credit; ?>" class="form-control credit" data-id="<?php echo $i?>" id="credit_<?php echo $i?>" placeholder="Credit Amount" <?php if($value->type=="Debit"){ echo 'readonly'; }?> onkeyup="creditTotal();">
                              </td>
                              
                              <td class="">
                                 <input type="text" name="narration[]" value="<?php echo $value->narration; ?>" class="form-control narration" data-id="<?php echo $i?>" id="narration_<?php echo $i?>" placeholder="Enter Narration" value="">
                              </td>
                              <td>
                                 <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="<?php echo $i;?>" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg>
                                 
                              </td>
                           </tr>
                        </tbody>
                        <?php $i++; 
                     } ?>
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
                           <td colspan="6"><input type="text" class="form-control" placeholder="Enter Long Narration" name="long_narration" value="{{$contra->long_narration}}"></td>
                        </tr>
                     </div>
                  </table>
               </div>
               <div class=" d-flex">
                  <div class="ms-auto">
                     <a href="{{ route('contra.index') }}"><button type="button" class="btn btn-danger">QUIT</button></a>
                     <input type="button" value="UPDATE" class="btn btn-xs-primary submit_data">
                  </div>
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
         $("#account_"+id).html('<?php echo $debit_html;?>');
      }else if ($("#type_" + id).val() == "Debit") {
         $("#debit_" + id).prop('readonly', false);
         $("#credit_" + id).prop('readonly', true);
         let amount = credit_total - debit_total;
         if(amount>0){
            $("#debit_"+id).val(amount);
         }
         $("#account_"+id).html('<?php echo $debit_html;?>');
      }
      debitTotal();
      creditTotal();
   });

   var add_more_count = '<?php echo $i;?>';
   $(".add_more").click(function() {
        add_more_count++;
        var $curRow = $(this).closest('tr');
        var optionElements = "";
        newRow = '<tr id="tr_' + add_more_count + '"><td><select class="form-control type" name="type[]"  data-id="' + add_more_count + '" id="type_' + add_more_count + '"><option value="">Type</option><option value="Credit">Credit</option><option value="Debit">Debit</option></select></td><td><select class="form-control account select2-single" name="account_name[]" data-id="' + add_more_count + '" id="account_' + add_more_count + '">';
        newRow += optionElements;
        newRow += '</select></td><td><input type="number" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td><td><input type="number" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
   $("#mode").change(function(){
      $("#cheque_no").val('');
      $("#cheque_no").prop('readonly',true);
      if($(this).val()==2){
         $("#cheque_no").prop('readonly',false);
      }
   });
</script>
@endsection