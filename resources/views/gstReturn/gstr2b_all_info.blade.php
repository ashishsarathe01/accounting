@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
   .text-ellipsis {
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
   }
   .w-min-50 {
      min-width: 50px;
   }
   .dataTables_filter,
   .dataTables_info,
   .dataTables_length,
   .dataTables_paginate {
      display: none;
   }
   .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 29px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
      height: 30px !important;
   }
   .select2-container .select2-selection--single{
      height: 30px !important;
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
      height: 28px;
   }
   .form-select {
      height: 34px;
   }
   input[type=number]::-webkit-inner-spin-button, 
   input[type=number]::-webkit-outer-spin-button { 
       -webkit-appearance: none;
       -moz-appearance: none;
       appearance: none;
       margin: 0; 
   }
</style>
<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">GSTR2B Details ({{$ctin}}) - {{$account_name}}</h5>
            <ul class="nav nav-fill nav-tabs" role="tablist">
               <li class="nav-item" role="presentation">
                  <a class="nav-link active" id="fill-tab-0" data-bs-toggle="tab" href="#fill-tabpanel-0" role="tab" aria-controls="fill-tabpanel-0" aria-selected="true"><h5 class="px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius" style="text-align: center;">B2B</h5></a>
               </li>
               <li class="nav-item" role="presentation">
                  <a class="nav-link" id="fill-tab-1" data-bs-toggle="tab" href="#fill-tabpanel-1" role="tab" aria-controls="fill-tabpanel-1" aria-selected="false"><h5 class="px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius" style="text-align: center;">B2BA</h5></a>
               </li>
            </ul>
            <div class="tab-content pt-5" id="tab-content">
                <div class="tab-pane active" id="fill-tabpanel-0" role="tabpanel" aria-labelledby="fill-tab-0">
                    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm" style="text-align: center;">B2B-Invoices</h5>
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped table-hover">
                            <thead>
                             <tr>
                                <th></th>
                                <th>Invoice No.</th>
                                <th>Invoice Date</th>
                                <th style="text-align: right">Invoice Value</th>
                                <th style="text-align: right">Book Value</th>
                                <th style="text-align: right">Taxable Value</th>
                                <th style="text-align: right">IGST</th>
                                <th style="text-align: right">CGST</th>
                                <th style="text-align: right">SGST</th>
                                <th style="text-align: right">Cess</th>
                                <th>Action</th>
                             </tr>
                            </thead>
                            <tbody>{!!$b2b_invoices!!}</tbody>
                         </table>
                    </div>
                    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm" style="text-align: center;">B2B-Debit notes</h5>
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped table-hover">
                            <thead>
                             <tr>
                                <th></th>
                                <th>Invoice No.</th>
                                <th>Invoice Date</th>
                                <th style="text-align: right">Invoice Value</th>
                                <th style="text-align: right">Book Value</th>
                                <th style="text-align: right">Taxable Value</th>
                                <th style="text-align: right">IGST</th>
                                <th style="text-align: right">CGST</th>
                                <th style="text-align: right">SGST</th>
                                <th style="text-align: right">Cess</th>
                                <th>Action</th>
                             </tr>
                            </thead>
                            <tbody>{!!$b2b_debit_note!!}</tbody>
                         </table>
                    </div>
                    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm" style="text-align: center;">B2B-Credit Notes</h5>
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped table-hover">
                            <thead>
                             <tr>
                                <th></th>
                                <th>Invoice No.</th>
                                <th>Invoice Date</th>
                                <th style="text-align: right">Invoice Value</th>
                                <th style="text-align: right">Book Value</th>
                                <th style="text-align: right">Taxable Value</th>
                                <th style="text-align: right">IGST</th>
                                <th style="text-align: right">CGST</th>
                                <th style="text-align: right">SGST</th>
                                <th style="text-align: right">Cess</th>
                                <th>Action</th>
                             </tr>
                            </thead>
                            <tbody>{!!$b2b_credit_note!!}</tbody>
                         </table>
                    </div>
               </div>
               <div class="tab-pane" id="fill-tabpanel-1" role="tabpanel" aria-labelledby="fill-tab-1">
                    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm" style="text-align: center;">B2BA-Invoices</h5>
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped table-hover">
                            <thead>
                             <tr>
                                <th></th>
                                <th>Invoice No.</th>
                                <th>Invoice Date</th>
                                <th style="text-align: right">Invoice Value</th>
                                <th style="text-align: right">Book Value</th>
                                <th style="text-align: right">Taxable Value</th>
                                <th style="text-align: right">IGST</th>
                                <th style="text-align: right">CGST</th>
                                <th style="text-align: right">SGST</th>
                                <th style="text-align: right">Cess</th>
                                <th>Action</th>
                             </tr>
                            </thead>
                            <tbody>{!!$b2ba_invoices!!}</tbody>
                         </table>
                    </div>
                    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm" style="text-align: center;">B2BA-Debit notes</h5>
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped table-hover">
                            <thead>
                             <tr>
                                <th></th>
                                <th>Invoice No.</th>
                                <th>Invoice Date</th>
                                <th style="text-align: right">Invoice Value</th>
                                <th style="text-align: right">Book Value</th>
                                <th style="text-align: right">Taxable Value</th>
                                <th style="text-align: right">IGST</th>
                                <th style="text-align: right">CGST</th>
                                <th style="text-align: right">SGST</th>
                                <th style="text-align: right">Cess</th>
                                <th>Action</th>
                             </tr>
                            </thead>
                            <tbody>{!!$b2ba_debit_note!!}</tbody>
                         </table>
                    </div>
                    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm" style="text-align: center;">B2BA-Credit Notes</h5>
                    <div class="table-responsive">
                         <table class="table table-bordered table-striped table-hover">
                            <thead>
                             <tr>
                                <th></th>
                                <th>Invoice No.</th>
                                <th>Invoice Date</th>
                                <th style="text-align: right">Invoice Value</th>
                                <th style="text-align: right">Book Value</th>
                                <th style="text-align: right">Taxable Value</th>
                                <th style="text-align: right">IGST</th>
                                <th style="text-align: right">CGST</th>
                                <th style="text-align: right">SGST</th>
                                <th style="text-align: right">Cess</th>
                                <th>Action</th>
                             </tr>
                            </thead>
                            <tbody>{!!$b2ba_credit_note!!}</tbody>
                         </table>
                    </div>                                 
               </div>
            </div>             
         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key w-100">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Help">F1
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Help</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Account">
                  <span class="border-bottom-black">F1</span><span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Account</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Item">
                  <span class="border-bottom-black">F2</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Item</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Master">F3
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Master</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Voucher">
                  <span class="border-bottom-black">F3</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Voucher</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Payment">
                  <span class="border-bottom-black">F5</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Payment</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Receipt">
                  <span class="border-bottom-black">F6</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Receipt</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Journal">
                  <span class="border-bottom-black">F7</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Journal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Sales">
                  <span class="border-bottom-black">F8</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Sales</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Add Purchase">
                  <span class="border-bottom-black">F9</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Add Purchase</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Balance Sheet">
                  <span class="border-bottom-black">B</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Balance Sheet</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Trial Balance">
                  <span class="border-bottom-black">T</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Trial Balance</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Stock Status">
                  <span class="border-bottom-black">S</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Stock Status</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Acc. Ledger">
                  <span class="border-bottom-black">L</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Acc. Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Summary">
                  <span class="border-bottom-black">I</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Item Ledger">
                  <span class="border-bottom-black">D</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Item Ledger</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Summary">
                  <span class="border-bottom-black">G</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Summary</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Switch User">
                  <span class="border-bottom-black">U</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Switch User</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center " data-bs-toggle="tooltip" data-bs-placement="bottom" title="Configuration">
                  <span class="border-bottom-black">F</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Configuration</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Lock Program">
                  <span class="border-bottom-black">K</span>
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Lock Program</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Training Videos">
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">Training Videos</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-2 d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="bottom" title="GST Portal">
                  <span class="ps-1 fw-normal text-body d-inline-block text-ellipsis">GST Portal</span>
               </button>
               <button class="p-2 transaction-shortcut-btn mb-4 text-ellipsis d-inline-block" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Search Menu">Search Menu
               </button>
            </div>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <p><h5 class="modal-title">OTP Verification</h5></p>
            <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="form-group">
               <input type="text" class="form-control" id="otp" placeholder="Enter OTP">
               <input type="hidden" id="fgstin">
            </div>
         </div>
         <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-border-body close" data-bs-dismiss="modal">CANCEL</button>
            <button type="button" class="ms-3 btn btn-red verify_otp">SUBMIT</button>
         </div>
      </div>
   </div>
</div>
<div class="modal fade" id="remarkModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="remarkModalLabel">Enter Remark</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="remarkForm">
          <input type="hidden" name="invoice" id="invoice">
          <input type="hidden" name="date" id="date">
          <input type="hidden" name="total_amount" id="total_amount">
          <input type="hidden" name="taxable_amount" id="taxable_amount">
          <input type="hidden" name="igst" id="igst">
          <input type="hidden" name="cgst" id="cgst">
          <input type="hidden" name="sgst" id="sgst">
          <input type="hidden" name="cess" id="cess">
          <input type="hidden" name="irn" id="irn">
          <input type="hidden" name="type" id="type">
          <input type="hidden" name="gstin" id="gstin" value="{{ $gstin }}">
          <input type="hidden" name="ctin" id="ctin" value="{{ $ctin }}">
          <input type="hidden" name="gstr2b_month" id="gstr2b_month" value="{{ $month }}">
          <div class="mb-3">
            <label for="remark" class="form-label">Remark</label>
            <textarea name="remark" id="remark" class="form-control" required></textarea>
          </div>
          <button type="submit" class="btn btn-danger">Reject</button>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="linkCDNRModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="linkModalLabel"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
         <table class="table table-bordered table-striped table-hover">
            <thead>
               <tr>
                  <th></th>
                  <th>Invoice No.</th>
                  <th>Invoice Date</th>
                  <th style="text-align: right">Series</th>
                  <th style="text-align: right">Amount</th>
               </tr>
            </thead>
            <tbody id="cdnr_table_body">
               <!-- Content will be populated via AJAX -->
            </tbody>
         </table>
         
            <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-border-body close" data-bs-dismiss="modal">CANCEL</button>
            <button type="button" class="ms-3 btn btn-red link_btn_action">SUBMIT</button>
         </div>
      </div>
    </div>
  </div>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).on('click','.check_action',function(){
      let id = $(this).data('key');
      let type = $(this).data('type');
      if($(this).is(':checked')){
         $("#"+type+''+id).hide();
      }else{
         $("#"+type+''+id).show();
      }
   });
   $(document).on('click','.reject_btn',function(){
      $('#invoice').val($(this).data('invoice'));
      $('#date').val($(this).data('date'));
      $('#total_amount').val($(this).data('total_amount'));
      $('#taxable_amount').val($(this).data('taxable_amount'));
      $('#igst').val($(this).data('igst'));
      $('#cgst').val($(this).data('cgst'));
      $('#sgst').val($(this).data('sgst'));
      $('#cess').val($(this).data('cess'));
      $('#irn').val($(this).data('irn'));
      $('#type').val($(this).data('type'));
      $('#remarkModal').modal('show');
   });
   $('#remarkForm').on('submit', function (e) {
      e.preventDefault();
      $.ajax({
         url: "{{ route('reject-gstr2b-entry') }}", // Replace with your actual route
         method: 'POST',
         data: $(this).serialize(),
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add this if CSRF token is needed
         },
         success: function (response) {
            let res = JSON.parse(response);
            if(res.status==true) {
               alert('Invoice rejected successfully.');
               $('#remarkModal').modal('hide'); 
               location.reload(); // Reload the page to reflect changes
            } else {
               alert('Failed to reject invoice');
            }
            
            //alert('Invoice rejected successfully.');
            //location.reload(); // Reload the page to reflect changes
         },
         error: function (xhr) {
            alert('An error occurred while rejecting the invoice.');
         }
      });
   });
   $(document).on('click','.link_btn',function(){
      let type = $(this).data('type');
      let invoice_no = $(this).data('invoice_no');
      let action_type = $(this).data('action_type');
      $.ajax({
         url: "{{ route('get-unlinked-cdnr') }}", // Replace with your actual route
         method: 'POST',
         data: {'type': type, 'gstin': '{{ $gstin }}', 'ctin': '{{ $ctin }}', 'month': '{{ $month }}','action_type':action_type, 'invoice_no': invoice_no },
         headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add this if CSRF token is needed
         },
         success: function (response) {
            if(response){
               let res = JSON.parse(response);
               if(res.status==true) {
                  if(type=="credit_note"){
                     $("#linkModalLabel").html('Link Debit Note');
                     $("#cdnr_table_body").html('');
                     $.each(res.debit_note, function(index, value) {
                        $checked = '';
                        if(value.gstr2b_invoice_id!=""){
                           $checked = 'checked';
                        }
                        $("#cdnr_table_body").append('<tr><td><input type="checkbox" class="link_check" data-id="'+value.id+'" '+$checked+'></td><td>'+value.sr_prefix+'</td><td>'+value.date+'</td><td style="text-align: right">'+value.series_no+'</td><td style="text-align: right">'+value.total+'</td></tr>');
                     });
                     if(res.debit_note.length==0){
                        $("#cdnr_table_body").append('<tr><td colspan="5" class="text-center">No Debit Notes available to link.</td></tr>');
                     }
                     $(".link_btn_action").data('type', 'debit_note');
                     $(".link_btn_action").data('invoice_no',invoice_no);
                     $('#linkCDNRModal').modal('show');
                  }else if(type=="debit_note"){
                     $("#linkModalLabel").html('Link Credit Note');
                     $("#cdnr_table_body").html('');
                     $.each(res.credit_note, function(index, value) {
                        $checked = '';
                        if(value.gstr2b_invoice_id!=""){
                           $checked = 'checked';
                        }
                        $("#cdnr_table_body").append('<tr><td><input type="checkbox" class="link_check" data-id="'+value.id+'" '+$checked+'></td><td>'+value.sr_prefix+'</td><td>'+value.date+'</td><td style="text-align: right">'+value.series_no+'</td><td style="text-align: right">'+value.total+'</td></tr>');
                     });
                     if(res.credit_note.length==0){
                        $("#cdnr_table_body").append('<tr><td colspan="5" class="text-center">No Credit Notes available to link.</td></tr>');
                     }
                     $(".link_btn_action").data('type', 'credit_note');
                     $(".link_btn_action").data('invoice_no',invoice_no);
                     $('#linkCDNRModal').modal('show');
                  }
                  // $('#otpModal').modal('show');
               }
            }else{
               alert('Something Went Wrong.');
            }
            
         },
         error: function (xhr) {
            alert('An error occurred while rejecting the invoice.');
         }
      });
   });
   link_btn_action = function() {
      let type = $('.link_btn_action').data('type');
      let invoice_no = $('.link_btn_action').data('invoice_no');
      let selected_ids = [];
      $('.link_check:checked').each(function() {
         selected_ids.push($(this).data('id'));
      });
      if(selected_ids.length==0) {
         alert('Please select at least one entry to link.');
         return;
      }
         $.ajax({
            url: "{{ route('link-cdnr') }}", // Replace with your actual route
            method: 'POST',
            data: {'type': type, 'ids': selected_ids, 'gstin': '{{ $gstin }}', 'ctin': '{{ $ctin }}', 'month': '{{ $month }}', 'invoice_no': invoice_no },
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add this if CSRF token is needed
            },
            success: function (response) {
               let res = JSON.parse(response);
               if(res.status==true) {
                  alert('linked successfully.');
                  $('#linkCDNRModal').modal('hide'); 
                  location.reload(); // Reload the page to reflect changes
               } else {
                  alert('Failed to link CDNR');
               }
            },
            error: function (xhr) {
               alert('An error occurred while linking the CDNR.');
            }
         });      
   };
   $(document).on('click','.link_btn_action',function(){
      link_btn_action();
   });
   $(document).on('click','.accept',function(){
      let id = $(this).data('id');
      if(confirm('Are you sure you want to accept this entry?')) {
         $.ajax({
            url: "{{ route('accept-gstr2b-entry') }}", // Replace with your actual route
            method: 'POST',
            data: {'id': id},
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add this if CSRF token is needed
            },
            success: function (response) {
               let res = JSON.parse(response);
               if(res.status==true) {
                  alert('Entry accepted successfully.');
                  location.reload(); // Reload the page to reflect changes
               } else {
                  alert('Failed to accept entry');
               }
            },
            error: function (xhr) {
               alert('An error occurred while accepting the entry.');
            }
         });
      }
   });

</script>
@endsection