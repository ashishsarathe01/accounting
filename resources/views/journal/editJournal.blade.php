@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<style type="text/css">
   .form-control {
      height: 52px;
   }
</style>
<!-- list-view-company-section -->
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
                <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
                    <nav aria-label="breadcrumb meri-breadcrumb ">
                        <ol class="breadcrumb meri-breadcrumb m-0  ">
                            <li class="breadcrumb-item">
                                <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                                <a class="fw-bold font-heading font-12  text-decoration-none" href="#">
                                Journal </a>
                            </li>
                        </ol>
                    </nav>
                </div>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Edit Journal Voucher
                </h5>
                <?php 
                $account_html = '<option value="">Select</option>';            
                foreach ($party_list as $value) {
                   $account_html.='<option value="'.$value->id.'">'.$value->account_name.'</option>';
                } 
                
                ?>
                <form id="frm" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('journal.update') }}">
                    @csrf
                    <div class="row">
                        <input type="hidden" value="{{ $journal->id }}" id="journal_id" name="journal_id" />
                        <div class="mb-2 col-md-2">
                            <label for="name" class="form-label font-14 font-heading">Date</label>
                            <input type="date" id="date" value="<?php echo $journal->date; ?>" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required>
                        </div>
                        <div class="mb-2 col-md-2">
                           <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                           <input type="text" id="voucher_no" class="form-control" name="voucher_no" placeholder="Voucher No." required value="{{$journal->voucher_no}}">
                        </div>
                        <div class="mb-2 col-md-2">
                           <label for="series_no" class="form-label font-14 font-heading">Series No.</label>
                           <select id="series_no" class="form-control" name="series_no">
                              <option value="">Select Series</option>
                              <?php
                              if(count($mat_series) > 0) {
                                 foreach ($mat_series as $value) { ?>
                                    <option value="<?php echo $value->series; ?>" data-gst="<?php echo $value->gst_no;?>" @if($journal->series_no==$value->series) selected @endif><?php echo $value->series; ?></option>
                                    <?php 
                                 }
                              } ?>
                           </select>
                           <input type="hidden" name="merchant_gst" id="merchant_gst" value="{{$journal->merchant_gst}}">
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
                           <input type="text" class="form-control" name="invoice_no" placeholder="Enter Invoice No." value="{{$journal->invoice_no}}">
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
                            <?php
                            $i = 1;
                            foreach ($journal_detail as $value) { ?>
                                <tbody>
                                    <tr class="font-14 font-heading bg-white" id="tr_<?php echo $i?>">
                                        <td class="">
                                            <select class="form-control type" name="type[]" data-id="<?php echo $i ?>" id="type_<?php echo $i ?>">
                                                <option value="">Type</option>
                                                <option <?php echo $value->type == 'Credit' ? 'selected' : ''; ?> value="Credit">Credit</option>
                                                <option <?php echo $value->type == 'Debit' ? 'selected' : ''; ?> value="Debit">Debit</option>
                                            </select>
                                        </td>
                                        <td class="">
                                            <select class="form-select select2-single" id="account_<?php echo $i ?>" name="account_name[]" required>
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($party_list as $val) {
                                                    $sel = '';
                                                    if ($value->account_name == $val->id)
                                                        $sel = 'selected';
                                                ?>
                                                    <option <?php echo $sel ?> value="<?php echo $val->id; ?>"><?php echo $val->account_name; ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td class="">
                                            <input type="text" name="debit[]" value="<?php echo $value->debit; ?>" class="form-control debit" data-id="<?php echo $i ?>" id="debit_<?php echo $i ?>" placeholder="Debit Amount" <?php if($value->type=="Credit"){ echo 'readonly'; }?> onkeyup="debitTotal();">
                                        </td>
                                        <td class="">
                                            <input type="text" name="credit[]" value="<?php echo $value->credit; ?>" class="form-control credit" data-id="<?php echo $i ?>" id="credit_<?php echo $i ?>" placeholder="Credit Amount" <?php if($value->type=="Debit"){ echo 'readonly'; }?> onkeyup="creditTotal();">
                                        </td>
                                        <td class="">
                                            <input type="text" name="narration[]" value="<?php echo $value->narration; ?>" class="form-control narration" data-id="<?php echo $i ?>" id="narration_<?php echo $i ?>" placeholder="Enter Narration" value="">
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
                                  <td colspan="6"><input type="text" class="form-control" placeholder="Enter Long Narration" name="long_narration" value="{{$journal->long_narration}}"></td>
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
                                          <option value="{{$vendor->id}}" data-gstin="{{$vendor->gstin}}" @if($journal->vendor==$vendor->id) selected @endif>{{$vendor->account_name}}</option>
                                       @endforeach
                                    </select>
                                 </td>
                              </tr>
                              
                              <?php
                             
                              $j = 1;
                              // echo "<pre>";
                              //    print_r($sundry);
                              //    print_r($journal_detail->toArray());
                              foreach ($journal_detail as $value){
                                 if(!in_array($value->account_name,$sundry) && $value->debit!=''){
                                    echo "ashish";
                                    ?>
                                    <tr id="withgst_tr_<?php echo $j;?>" class="font-14 font-heading bg-white">
                                       <td>
                                          <select class="form-control item" id="item_<?php echo $j;?>" data-index="<?php echo $j;?>" name="item[]" onchange="gstCalculation()" style="width: 598.611px;">
                                             <option value="">Select Item</option>
                                             @foreach($items as $item)
                                                <option value="{{$item->id}}" @if($value->account_name==$item->id) selected @endif>{{$item->account_name}}</option>
                                             @endforeach
                                          </select>
                                       </td>
                                       <td>
                                          <select class="form-control percentage" id="percentage_<?php echo $j;?>" data-index="<?php echo $j;?>" name="percentage[]" onchange="gstCalculation()">
                                             <option value="">Select GST(%)</option>
                                             <option value="5"  @if($value->percentage==5) selected @endif>5%</option>
                                             <option value="12"  @if($value->percentage==12) selected @endif>12%</option>
                                             <option value="18"  @if($value->percentage==18) selected @endif>18%</option>
                                             <option value="28"  @if($value->percentage==28) selected @endif>28%</option>
                                          </select>
                                       </td>
                                       <td>
                                          <input type="text" class="form-control amount" id="amount_<?php echo $j;?>" data-index="<?php echo $j;?>" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()" value="{{$value->debit}}">
                                       </td>
                                       <td>
                                          <svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_more_tr" data-id="<?php echo $j;?>" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"></path></svg></td>
                                    </tr>
                                    <?php 
                                 }
                                 $j++;
                              } ?>
                              <tr class="font-14 font-heading bg-white">
                                 <td></td><td></td><td></td>
                                 <td style="float: right;" >
                                    <a class="add_more_tr">
                                       <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                          <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"></path>
                                       </svg>
                                    </a>
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td></td>
                                 <td style="text-align: right;">Net Amount</td>
                                 <td>
                                    <input type="text" class="form-control" id="net_amount" name="net_amount" placeholder="Net Amount" readonly value="{{$journal->net_amount}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white cgst_tr" style="display: none;">
                                 <td></td>
                                 <td style="text-align: right;">CGST</td>
                                 <td>
                                    <input type="text" class="form-control" id="cgst" name="cgst" readonly value="{{$journal->cgst}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white sgst_tr" style="display: none;">
                                 <td></td>
                                 <td style="text-align: right;">SGST</td>
                                 <td>
                                    <input type="text" class="form-control" id="sgst" name="sgst" readonly value="{{$journal->sgst}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white igst_tr" style="display: none;">
                                 <td></td>
                                 <td style="text-align: right;">IGST</td>
                                 <td>
                                    <input type="text" class="form-control" id="igst" name="igst" readonly value="{{$journal->igst}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td></td>
                                 <td style="text-align: right;">Total Amount</td>
                                 <td>
                                    <input type="text" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" readonly value="{{$journal->total_amount}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td></td>
                                 <td style="text-align: right;">Remark</td>
                                 <td>
                                    <input type="text" class="form-control" name="remark" placeholder="Enter Remark" value="{{$journal->remark}}">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                    </div>


                    <div class=" d-flex">

                        <div class="ms-auto">
                           <button type="button" onclick="redirectBack()" class="btn btn-danger">QUIT</button>
                            <input type="button" value="SUBMIT" class="btn btn-xs-primary submit_data">

                        </div>
                        

                    </div>
                </form>
            </div>
            <div class="col-lg-1 d-flex justify-content-center">
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
            </div>
        </div>
</div>
</section>
</div>

</body>
@include('layouts.footer')
<script>

    function redirectBack(){
      let previousUrl = document.referrer; // Get Previous URL

      if(previousUrl == "{{ session('previous_url_journal')  }}"){
         window.location.href = "https://www.meriaccounting.com/journal"; // Fixed Redirect
      }else{
         history.back(); // Go Back to previous page
      }
   }
   
   
   var company_gst = "{{$company_gst}}";
   var claim_gst_status = "{{$journal->claim_gst_status}}";
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
               $("#credit_"+id).val(amount.toFixed(2));
            }
        }else if ($("#type_" + id).val() == "Debit") {
            $("#debit_" + id).prop('readonly', false);
            $("#credit_" + id).prop('readonly', true);
            let amount = credit_total - debit_total;
            if(amount>0){
               $("#debit_"+id).val(amount.toFixed(2));
            }
        }
         $("#account_" + id).html(`{!! $account_html !!}`);
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
        newRow += '</select></td><td><input type="text" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td><td><input type="text" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td></tr>';
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
    $(".select2-single").select2();
    $(document).ready(function() {
      
      if(claim_gst_status=="YES"){
         
         $("#without_gst_section").hide();
         $(".with_gst_section").hide();
         $("#vendor").attr('required',false);
         $("#item_1").attr('required',false);
         $("#percentage_1").attr('required',false);
         $("#amount_1").attr('required',false);
         $("#account_1").attr('required',false);
         $("#account_2").attr('required',false);
         $("#flexRadioDefault1").prop('checked',true);
         $(".with_gst_section").show();
         $("#vendor").attr('required',true);
         $("#item_1").attr('required',true);
         $("#percentage_1").attr('required',true);
         $("#amount_1").attr('required',true);
         $("#vendor").select2();
         $("#item_1").select2();
         gstCalculation();
      }
       debitTotal();
       creditTotal();
       $(".submit_data").click(function() {
          var form_data = [];
          let dr = 0;
          let cr = 0;
          let ids = 0;
          let error = false;
          let claim_gst_status = document.querySelector('input[name="flexRadioDefault"]:checked').value;
          if(claim_gst_status=="NO"){
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
       }else{
            if($("#vendor").val() == '' && $("#series_no").val() == "" && $("#date").val() == '') {
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
      $("#series_no").change();
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
    var add_more_count_withgst = "{{$j}}";
    $(".add_more_tr").click(function(){
       add_more_count_withgst++;
       var $curRow = $(this).closest('tr');
       let newRow = '<tr id="withgst_tr_'+add_more_count_withgst+'" class="font-14 font-heading bg-white"><td><select class="form-control item" id="item_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="item[]" onchange="gstCalculation()" style="width: 598.611px;"><option value="">Select Item</option>@foreach($items as $item)<option value="{{$item->id}}">{{$item->account_name}}</option>@endforeach </select></td><td><select class="form-control percentage" id="percentage_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="percentage[]" onchange="gstCalculation()"><option value="">Select GST(%)</option><option value="5">5%</option><option value="12">12%</option><option value="18">18%</option><option value="28">28%</option></select></td><td><input type="text" class="form-control amount" id="amount_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="amount[]" placeholder="Enter Amount" onkeyup="gstCalculation()"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove_more_tr" data-id="'+add_more_count_withgst+'" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"></path></svg></td></tr>';
       $curRow.before(newRow);
       $("#item_"+add_more_count_withgst).select2();
    });
    $(document).on("click", ".remove_more_tr", function() {
       let id = $(this).attr('data-id');
       $("#withgst_tr_" + id).remove();
       gstCalculation();
    });
</script>
@endsection