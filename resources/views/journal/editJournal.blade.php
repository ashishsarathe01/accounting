@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<style type="text/css">
   .form-control {
      height: 52px;
   }
    .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 52px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
      height: 52px !important;
   }
   .select2-container .select2-selection--single{
      height: 52px !important;
   }
   .select2-container{
          width: 300 px !important;
   }
   .select2-container--default .select2-selection--single{
      border-radius: 12px !important;
   }
   /* Add / Remove Buttons UI */
.icon-btn{
    width:34px;
    height:34px;
    border:none;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    font-weight:600;
    cursor:pointer;
    transition:all .2s ease;
}

/* Add Button */
.add-btn{
    background:#0d6efd;
    color:#fff;
}

.add-btn:hover{
    background:#0b5ed7;
}

/* Remove Button */
.remove-btn{
    background:#ff4d4f;
    color:#fff;
}

.remove-btn:hover{
    background:#d9363e;
}

/* center buttons in table */
td .icon-btn{
    margin:auto;
}
@media print {
    table {
        /*width: 100% !important;*/
    }
    .header-section {
        display: none !important; /* hide buttons only */
    }
    .sidebar {
        display: none !important; /* hide buttons only */
    }
}
@page { size: auto;  margin: 0mm; }
</style>
<!-- list-view-company-section -->
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
               
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm ">
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
                            <input type="date" id="date" value="<?php echo $journal->date; ?>" min="{{ $fy_start_date }}" max="{{ $fy_end_date }}" class="form-control calender-bg-icon calender-placeholder" name="date" placeholder="Select date" required>
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
                                        data-gst="<?php echo $value->gst_no ?? ''; ?>"
                                        @if($journal->series_no==$value->series) selected @endif
                                        >
                                        <?php echo $value->series; ?>
                                    </option>
                                    <?php 
                                 }
                              } ?>
                           </select>
                           <input type="hidden" name="merchant_gst" id="merchant_gst" value="{{$journal->merchant_gst}}">
                        </div>
                        <div class="mb-2 col-md-2">
                           <label for="name" class="form-label font-14 font-heading">Voucher No.</label>
                          <input type="text" class="form-control" id="voucher_prefix"
                                name="voucher_prefix"
                                value="{{ $journal->voucher_no_prefix ?? $journal->voucher_no }}"
                                style="text-align:right;">
                            <input type="hidden" id="voucher_no" name="voucher_no" value="{{ $journal->voucher_no }}">
                            <input type="hidden" id="manual_enter_invoice_no" name="manual_enter_invoice_no">
                        </div>
                        <div class="mb-2 col-md-2">
                            <label class="form-label font-14 font-heading">Claim GST</label>
                            <select class="form-select claim_gst_dropdown" id="claim_gst" name="flexRadioDefault">
                            <option value="YES" {{ $journal->claim_gst_status == 'YES' ? 'selected' : '' }}>
                            Yes
                            </option>
                            <option value="NO" {{ $journal->claim_gst_status == 'NO' ? 'selected' : '' }}>
                            No
                            </option>
                            </select>
                        </div>
                        <div class="mb-2 col-md-2 with_gst_section" style="display:none;">
                           <label for="series_no" class="form-label font-14 font-heading">Invoice No.</label>
                           <input type="text" class="form-control" id="invoice_no" name="invoice_no" placeholder="Enter Invoice No." value="{{$journal->invoice_no}}">
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
                                        <td class="add_btn_cell_without"></td>
                                    </tr>
                                </tbody>
                            <?php $i++;
                            } ?>
                            <div class="plus-icon">
                                
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
                                    <select class="form-control" id="vendor" name="vendor" onchange="checkGSTVendor()" style="width: 598.611px;">
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
                                 if(!in_array($value->account_name,$sundry) && $value->debit!='' && $value->percentage!=''){
                                   
                                    ?>
                                    <tr id="withgst_tr_<?php echo $j;?>" class="font-14 font-heading bg-white">
                                       <td>
                                          <select class="form-control item select2-single" id="item_<?php echo $j;?>" data-index="<?php echo $j;?>" name="item[]" onchange="gstCalculation()" style="width: 598.611px;">
                                             <option value="">Select Item</option>
                                             @foreach($items as $item)
                                                <option value="{{$item->id}}" @if($value->account_name==$item->id) selected @endif>{{$item->account_name}}</option>
                                             @endforeach
                                          </select>
                                       </td>
                                       <td>
                                          <select class="form-control percentage" id="percentage_<?php echo $j;?>" data-index="<?php echo $j;?>" name="percentage[]" onchange="gstCalculation()">
                                             <option value="">Select GST(%)</option>
                                             <option value="0" @if($value->percentage==0) selected @endif >Nil</option>
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
                                        <button type="button" class="icon-btn remove-btn remove_more_tr" data-id="<?php echo $j;?>">
                                            &minus;
                                        </button>
                                        </td>

                                        <td class="add_btn_cell"></td>
                                    </tr>
                                    <?php 
                                 }
                                 $j++;
                              } ?>
                              
                              <tr class="font-14 font-heading bg-white">
                                 <td></td>
                                 <td style="text-align: right; vertical-align:middle;">Net Amount</td>
                                 <td>
                                    <input type="text" class="form-control" id="net_amount" name="net_amount" placeholder="Net Amount" readonly value="{{$journal->net_amount}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white cgst_tr" style="display: none;">
                                 <td></td>
                                 <td style="text-align: right; vertical-align:middle;">CGST</td>
                                 <td>
                                    <input type="text" class="form-control" id="cgst" name="cgst" readonly value="{{$journal->cgst}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white sgst_tr" style="display: none;">
                                 <td></td>
                                 <td style="text-align: right; vertical-align:middle;">SGST</td>
                                 <td>
                                    <input type="text" class="form-control" id="sgst" name="sgst" readonly value="{{$journal->sgst}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white igst_tr" style="display: none;">
                                 <td></td>
                                 <td style="text-align: right; vertical-align:middle;">IGST</td>
                                 <td>
                                    <input type="text" class="form-control" id="igst" name="igst" readonly value="{{$journal->igst}}">
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
                                          value="{{ 
                                             round(
                                             ($journal->total_amount ?? 0) -
                                             (
                                                ($journal->net_total ?? 0)
                                                + ($journal->cgst ?? 0)
                                                + ($journal->sgst ?? 0)
                                                + ($journal->igst ?? 0)
                                                + ($journal->tcs_amount ?? 0)
                                             ),
                                             2)
                                          }}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                                 <td></td>
                                 <td style="text-align: right; vertical-align:middle;">Total Amount</td>
                                 <td>
                                    <input type="text" class="form-control" id="total_amount" name="total_amount" placeholder="Total Amount" readonly value="{{$journal->total_amount}}">
                                 </td>
                              </tr>
                              <tr class="font-14 font-heading bg-white">
                               
                                 <td colspan="3";>
                                    <input type="text" class="form-control" name="remark" placeholder="Enter Remark" value="{{$journal->remark}}">
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                    </div>


                    <div class=" d-flex header-section">

                        <div class="ms-auto">
                           <button type="button" onclick="redirectBack()" class="btn btn-danger">QUIT</button>
                            <input type="button" value="SUBMIT" class="btn btn-xs-primary submit_data">

                        </div>
                        

                    </div>
                </form>
            </div>
            <div class="col-lg-1 d-flex justify-content-center header-section">
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

    function redirectBack(){
      let previousUrl = document.referrer; // Get Previous URL

      if(previousUrl == "{{ session('previous_url_journal')  }}"){
         window.location.href = "https://www.meriaccounting.com/journal"; // Fixed Redirect
      }else{
         history.back(); // Go Back to previous page
      }
    }
    let ignoreVendorChange = false;
    let originalVendorId = $("#vendor").val(); 
    let pageLoaded = false;
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
     $(document).on("click",".add_more",function(){
         add_more_count++;
         var $curRow = $(this).closest('tr');
         var optionElements = "";
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
        newRow = '<tr id="tr_' + add_more_count + '"><td><select class="form-control type" name="type[]"  data-id="' + add_more_count + '" id="type_' + add_more_count + '"><option value="">Type</option>'+type_option+'</select></td><td><select class="form-control account select2-single" name="account_name[]" data-id="' + add_more_count + '" id="account_' + add_more_count + '">';
        newRow += optionElements;
        newRow += '</select></td><td><input type="text" name="debit[]" class="form-control debit" data-id="' + add_more_count + '" id="debit_' + add_more_count + '" placeholder="Debit Amount" readonly onkeyup="debitTotal();"></td><td><input type="text" name="credit[]" class="form-control credit" data-id="' + add_more_count + '" id="credit_' + add_more_count + '" placeholder="Credit Amount" readonly onkeyup="creditTotal();"></td><td><input type="text" name="narration[]" class="form-control narration" data-id="' + add_more_count + '" id="narration_' + add_more_count + '" placeholder="Enter Narration"></td><td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove" data-id="' + add_more_count + '" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td><td class="add_btn_cell_without"></td></tr>';
        $curRow.before(newRow);

            $('.select2-single').select2();
            
            // focus new row type (same as ADD page)
            setTimeout(function(){
               $("#type_" + add_more_count).focus();
            },100);
            // ✅ FIX "+" ONLY FOR WITHOUT GST
        $("#without_gst_section .add_more").remove();

        $("#without_gst_section tr[id^='tr_']:last")
        .find(".add_btn_cell_without")
        .html('<button type="button" class="icon-btn add-btn add_more">+</button>');
    });

    $(document).on("click", ".remove", function() {
        let rows = $("#without_gst_section tr[id^='tr_']");

        // ✅ prevent deleting last row
        if(rows.length <= 1){
            alert("At least one row required");
            return;
        }
        let id = $(this).data('id');
        let currentRow = $("#tr_"+id);
        let nextRow = currentRow.next("tbody").find("tr");
        let prevRow = currentRow.prev("tbody").find("tr");
        currentRow.remove();
        debitTotal();
        creditTotal();
        // ✅ fix + button (ONLY WITHOUT GST)
        $("#without_gst_section .add_more").remove();

        $("#without_gst_section tr[id^='tr_']:last")
        .find(".add_btn_cell_without")
        .html('<button type="button" class="icon-btn add-btn add_more">+</button>');
        setTimeout(function(){
            if(nextRow.length){
                nextRow.find(".type").focus();
            }
            else if(prevRow.length){
                prevRow.find(".narration").focus();
            }
            else{
                $(".add_more").focus();
            }
        },100);
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
      if($("#claim_gst").val() == "YES"){
            $("#without_gst_section").hide();
            $(".with_gst_section").show();
        }else{
            $("#without_gst_section").show();
        }
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
         $("#invoice_no").attr('required',true);
         $("#percentage_1").attr('required',true);
         $("#amount_1").attr('required',true);
         $("#vendor").select2();
         $("#item_1").select2();
         gstCalculation();
      }
       debitTotal();
       creditTotal();
       $("#without_gst_section tr[id^='tr_']:last")
        .find(".add_btn_cell_without")
        .html('<button type="button" class="icon-btn add-btn add_more">+</button>');
       $(".add_btn_cell").last().html('<button type="button" class="icon-btn add-btn add_more_tr">+</button>');
       $(".submit_data").click(function() {
          var form_data = [];
          let dr = 0;
          let cr = 0;
          let ids = 0;
          let error = false;
         let claim_gst_status = $("#claim_gst").val();
          let debit_count = 0;let credit_count = 0;
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
                   cr = cr.toFixed(2);
                   credit_count++
                }else if($(this).val() == "Debit" && $("#debit_" + id).val() != "" && $("#account_" + id).val() != "") {
                   form_data.push({
                      "type": "Debit",
                      "credit": 0,
                      "debit": $("#debit_" + id).val(),
                      "user_id": $("#account_" + id).val(),
                      "remark": $("#narration_" + id).val()
                   });
                   dr = parseFloat(dr) + parseFloat($("#debit_" + id).val());
                   dr = dr.toFixed(2);
                   
                    debit_count++;
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
         //  if(credit_count>1 && debit_count>1){
         //    alert("Not Allowed - You cannot enter multiple debits and credits simultaneously.");
         //    return false;
         // }
         dr = parseFloat(dr || 0).toFixed(2);
         cr = parseFloat(cr || 0).toFixed(2);
    // dr = dr.toFixed(2);
    //      cr = cr.toFixed(2);
          if(parseFloat(cr)!= parseFloat(dr) && claim_gst_status=="NO") {
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
             $("#amount_1").attr('required',true);
             $("#vendor").select2();
             $("#item_1").select2();
             addDefaultGstRowIfNone();
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
      setTimeout(() => {
         pageLoaded = true;
      }, 0);
      setTimeout(function(){
            let lastRow = $("#without_gst_section tr[id^='tr_']").last();

            if(lastRow.length){
                lastRow.find(".add_btn_cell_without").html(
                    '<button type="button" class="icon-btn add-btn add_more">+</button>'
                );
            }

        },300);

        setTimeout(function(){

            let table = $(".with_gst_section tbody");

            let rows = table.find("tr[id^='withgst_tr_']");

            table.find(".add_more_tr").remove();

            if(rows.length === 1){


                rows.last().append(
                    '<td><button type="button" class="icon-btn add-btn add_more_tr">+</button></td>'
                );

            } else if(rows.length > 1){

                rows.last().append(
                    '<td><button type="button" class="icon-btn add-btn add_more_tr">+</button></td>'
                );

            }

        },200);
    });
    function addDefaultGstRowIfNone() {
       if ($(".with_gst_section tr[id^='withgst_tr_']").length === 0) {

            add_more_count_withgst++;

            let newRow =
            '<tr id="withgst_tr_'+add_more_count_withgst+'" class="font-14 font-heading bg-white">'+
            '<td><select class="form-control item select2-single" id="item_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="item[]" onchange="gstCalculation()">'+
            '<option value="">Select Item</option>@foreach($items as $item)<option value="{{$item->id}}">{{$item->account_name}}</option>@endforeach'+
            '</select></td>'+

            '<td><select class="form-control percentage" id="percentage_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="percentage[]" onchange="gstCalculation()">'+
            '<option value="">Select GST(%)</option>'+
            '<option value="0">0%</option>'+
            '<option value="5">5%</option>'+
            '<option value="12">12%</option>'+
            '<option value="18">18%</option>'+
            '<option value="28">28%</option>'+
            '</select></td>'+

            '<td><input type="text" class="form-control amount" id="amount_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="amount[]" onkeyup="gstCalculation()"></td>'+

            '<td><button type="button" class="icon-btn remove-btn remove_more_tr" data-id="'+add_more_count_withgst+'">&minus;</button></td>'+

            '<td><button type="button" class="icon-btn add-btn add_more_tr">+</button></td>'+

            '</tr>';

            $(".with_gst_section tbody tr:first").after(newRow);

            $("#item_"+add_more_count_withgst).select2();

        
        }
    }
    function gstCalculation(){
        if($("#vendor option:selected").attr("data-gstin")==undefined){
            return;
        }
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
            
                // ✅ ROUND ITEM FIRST
                let roundedAmount = parseFloat(amount).toFixed(2);
            
                // update input (auto UI fix)
                $("#amount_"+id).val(roundedAmount);
            
                // use rounded value everywhere
                let IGST = roundedAmount * percentage / 100;
                let CGST = roundedAmount * (percentage/2) / 100;
                let SGST = CGST;
            
                // round GST also
                IGST = parseFloat(IGST).toFixed(2);
                CGST = parseFloat(CGST).toFixed(2);
                SGST = parseFloat(SGST).toFixed(2);
            
                total_cgst += parseFloat(CGST);
                total_sgst += parseFloat(SGST);
                total_igst += parseFloat(IGST);
            
                // ✅ IMPORTANT: add rounded amount only
                net_total += parseFloat(roundedAmount);
            }                   
          }
       });
        $("#cgst").val("");
$("#sgst").val("");
$("#igst").val("");

let tamount = 0;

if (vendor_gstin == company_gstin) {
    // ✅ Intra-state
    $(".cgst_tr").show();
    $(".sgst_tr").show();
    $(".igst_tr").hide();

    $("#cgst").val(total_cgst);
    $("#sgst").val(total_sgst);

    tamount = parseFloat(net_total) + parseFloat(total_cgst) + parseFloat(total_sgst);

} else {
    // ✅ Inter-state
    $(".cgst_tr").hide();
    $(".sgst_tr").hide();
    $(".igst_tr").show();

    $("#igst").val(total_igst);

    tamount = parseFloat(net_total) + parseFloat(total_igst);
}

$("#net_amount").val(net_total.toFixed(2));

// ✅ Total amount
$("#total_amount").val(Math.round(tamount));

// ✅ Round off calculation
let calculated_total = tamount;
let rounded_total = Math.round(calculated_total);
let round_off = (rounded_total - calculated_total).toFixed(2);


$("#round_off").val(round_off);

// ✅ Show/hide round off row
if (parseFloat(round_off) === 0) {
    $(".roundoff_tr").hide();
} else {
    $(".roundoff_tr").show();
}
       

       
    }
    var add_more_count_withgst = "{{$j}}";

$(document).on("click",".add_more_tr",function(){

    add_more_count_withgst++;

    var $curRow = $(this).closest("tr");

    let newRow =
    '<tr id="withgst_tr_'+add_more_count_withgst+'" class="font-14 font-heading bg-white">'+
    '<td><select class="form-control item select2-single" id="item_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="item[]" onchange="gstCalculation()">'+
    '<option value="">Select Item</option>@foreach($items as $item)<option value="{{$item->id}}">{{$item->account_name}}</option>@endforeach'+
    '</select></td>'+

    '<td><select class="form-control percentage" id="percentage_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="percentage[]" onchange="gstCalculation()">'+
    '<option value="">Select GST(%)</option>'+
    '<option value="0">0%</option>'+
    '<option value="5">5%</option>'+
    '<option value="12">12%</option>'+
    '<option value="18">18%</option>'+
    '<option value="28">28%</option>'+
    '</select></td>'+

    '<td><input type="text" class="form-control amount" id="amount_'+add_more_count_withgst+'" data-index="'+add_more_count_withgst+'" name="amount[]" onkeyup="gstCalculation()"></td>'+

    '<td><button type="button" class="icon-btn remove-btn remove_more_tr" data-id="'+add_more_count_withgst+'">&minus;</button></td>'+
    '</tr>';

    $curRow.after(newRow);

    $(".add_more_tr").remove();

    $("#withgst_tr_"+add_more_count_withgst).append(
        '<td><button type="button" class="icon-btn add-btn add_more_tr">+</button></td>'
    );

    $("#item_"+add_more_count_withgst).select2();
    setTimeout(function(){
    $("#item_"+add_more_count_withgst).select2('open');
},100);
});

$(document).on("click",".remove_more_tr",function(){

    let row = $(this).closest("tr");
    let table = row.closest("tbody");

    row.remove();

    table.find(".add_more_tr").remove();

    let lastRow = table.find("tr").has(".item").last();

    if(lastRow.length){
        lastRow.append('<td><button type="button" class="icon-btn add-btn add_more_tr">+</button></td>');
    }

    gstCalculation();
});
    $(document).on("click", ".remove_more_tr", function() {
       let id = $(this).attr('data-id');
       $("#withgst_tr_" + id).remove();
       gstCalculation();
    });
    function checkGSTVendor() {
        if (ignoreVendorChange) {
            ignoreVendorChange = false;
            return;
        }
        if (!pageLoaded && !$("#gstAccountModal").hasClass("show")) return;
    
        let select = document.getElementById('vendor');
        let opt = select.options[select.selectedIndex];
        if (!opt) return;
    
        let gst = opt.getAttribute('data-gstin');
    
        if (!gst || gst.trim() === "") {
    
            $("#gst_modal_account_id").val(opt.value);
            $("#gst_modal_account_name").text(opt.text.trim());
    
            $("#gstin, #pan, #address, #pincode").val("");
            $("#state").val("").trigger("change");
    
            $("#state")
                .off("select2:opening")
                .css("pointer-events", "auto");
    
            ignoreVendorChange = true;
            $("#vendor").val(null).trigger("change.select2");
    
            $("#gstAccountModal").modal("show");
            return;
        }
        gstCalculation();
    }
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
    
                    // ðŸ‘‰ STATE
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
    
                    $("#state_hidden").val($("#state").val());
    
                } else {
                    alert(data.message || "Invalid GST Number");
                }
            }
        });
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
                $("#vendor").val(accountId).trigger("change.select2");
    
                $("#gstAccountModal").modal("hide");
    
                gstCalculation();
            }
        });
    });
    $('#gstAccountModal').on('hidden.bs.modal', function () {
        let accountId = $("#gst_modal_account_id").val();
        let opt = $("#vendor option[value='" + accountId + "']");
    
        if (opt.length && (!opt.attr("data-gstin") || opt.attr("data-gstin").trim() === "")) {
    
            ignoreVendorChange = true;
    
            if (originalVendorId) {
                $("#vendor").val(originalVendorId).trigger("change.select2");
            } else {
                $("#vendor").val(null).trigger("change.select2");
            }
        }
    
        $("#gst_modal_account_id").val("");
        $("#gst_modal_account_name").text("");
        $("#gstin, #pan, #address, #pincode").val("");
        $("#state").val("").trigger("change");
    });
    $('#gstAccountModal').on('shown.bs.modal', function () {
        $('#state').select2({
            dropdownParent: $('#gstAccountModal'),
            width: '100%'
        });
    });
    $("#cancelGstModal").click(function () {
       $("#gstAccountModal").modal("hide");
    });
    $(document).on('change', 'select[id^="type_"]', function () {

    const id = $(this).data('id');

    setTimeout(function(){
        $("#account_" + id).select2('open');
    },100);

});
$(document).on('select2:select', '#vendor', function () {

    setTimeout(function(){

        let firstItem = $(".item").first();

        if(firstItem.length){
            firstItem.next('.select2-container').find('.select2-selection').focus();
        }

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
   $("#date").focus();
   $("#vendor").select2({
    placeholder: "Select Vendor",
    width: '100%'
});
});

$(document).ready(function(){

    let isEditPage = true;

    $('#series_no').change(function(){

        let selected = $(this).find(':selected');

        let prefix = selected.data('invoice_prefix') || '';
        let manual = selected.data('manual_enter_invoice_no');

        if(!isEditPage){

            if(manual == '0'){ 
                let start = selected.data('invoice_start_from') || '';
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

    $('#series_no').trigger('change');

});
function printpage(){
       //$('.sidebar').addClass('importantRule'); // only sidebar hide
       window.print();
       //$('.sidebar').removeClass('importantRule');
    }
</script>
@endsection