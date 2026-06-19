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
<div class="list-of-view-company ">
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
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">GSTR2B</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm">
               <div class="row">
                  <div class="mb-3 col-md-3">
                     <label for="month" class="form-label font-14 font-heading">Month</label>
                     <input type="month" class="form-control" name="month" id="month" required value="{{date('Y-m', strtotime('-1 month'))}}"/>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="gstin" class="form-label font-14 font-heading">GSTIN</label>
                     <select class="form-select" id="gstin">
                        @foreach ($gst as $value)
                           <option value="{{$value->gst_no}}">{{$value->gst_no}}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3 col-md-3">
                     <button type="button" class="btn btn-xs-primary submit_btn" style="margin-top: 20px;">SUBMIT</button>
                  </div>
               </div>
            </form>
            <div id="gst_div" style="display: none">
               <div class="col-md-12 col-sm-12 px-0">
                  <div class="container-fluid table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                     <ul class="nav nav-fill nav-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                           <a class="nav-link active" id="fill-tab-0" data-bs-toggle="tab" href="#fill-tabpanel-0" role="tab" aria-controls="fill-tabpanel-0" aria-selected="true">
                              GSTR-2B
                           </a>
                        </li>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link" id="fill-tab-1" data-bs-toggle="tab" href="#fill-tabpanel-1" role="tab" aria-controls="fill-tabpanel-1" aria-selected="false">
                              GSTR-2B RECONCILIATION
                           </a>
                        </li>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link " id="fill-tab-2" data-bs-toggle="tab" href="#fill-tabpanel-2" role="tab" aria-controls="fill-tabpanel-2" aria-selected="true">
                              GSTR-2B BOOK
                           </a>
                        </li>
                     </ul>
                     <div class="w-100 mt-0">
                        <div class="tab-content mt-2">
                           <!-- View Tab -->
                           <div class="tab-pane active" id="fill-tabpanel-0" role="tabpanel" aria-labelledby="fill-tab-0">
                              <table class="table table-ordered bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm gst_table">
                                 <thead>
                                    <tr>
                                       <th rowspan="2">Account Name</th>
                                       <th colspan="2" style="text-align:center">B2B-INVOICE</th>
                                       <th colspan="2" style="text-align:center">B2B-CDNR</th>
                                       <th rowspan="2" style="text-align:right">Difference</th>
                                    </tr>
                                    <tr>
                                       <th style="text-align:right">Portal</th>
                                       <th style="text-align:right">Books</th>
                                       <th style="text-align:right">Portal</th>
                                       <th style="text-align:right">Books</th>
                                    </tr>
                                 </thead>
                                 <tbody style="font-size: 15px;">
                                    
                                 </tbody>
                              </table>
                           </div>
                           <div class="tab-pane" id="fill-tabpanel-1" role="tabpanel" aria-labelledby="fill-tab-1">
                                 <table class="table table-bordered bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm">
                                    <thead>
                                       <tr>
                                          <th style='text-align:left'>Particular</th>
                                          <th style='text-align:right'>Amount</th>
                                          <th style='text-align:right'>IGST</th>
                                          <th style='text-align:right'>CGST</th>
                                          <th style='text-align:right'>SGST</th>
                                       </tr>
                                    </thead>
                                    <tbody style="font-size: 15px;">
                                       <tr>
                                          <td style='text-align:left'>GST Portal</td>
                                          <td style='text-align:right' id="portal_invoice_amount"></td>
                                          <td style='text-align:right' id="portal_igst_amount"></td>
                                          <td style='text-align:right' id="portal_cgst_amount"></td>
                                          <td style='text-align:right' id="portal_sgst_amount"></td>
                                       </tr>
                                       <tr><th colspan="4">Previous Month</th></tr>
                                       <tr>
                                          <td style='text-align:left'>Previous Month Invoice (Portal)</td>
                                          <td style='text-align:right' id="previous_month_invoice_amount"></td>
                                          <td style='text-align:right' id="previous_month_invoice_igst_amount"></td>
                                          <td style='text-align:right' id="previous_month_invoice_cgst_amount"></td>
                                          <td style='text-align:right' id="previous_month_invoice_sgst_amount"></td>
                                       </tr>
                                       <tr>
                                          <td style='text-align:left'>Previous Debit Note (Portal)</td>
                                          <td style='text-align:right' id="previous_month_debit_note_amount"></td>
                                          <td style='text-align:right' id="previous_month_debit_note_igst_amount"></td>
                                          <td style='text-align:right' id="previous_month_debit_note_cgst_amount"></td>
                                          <td style='text-align:right' id="previous_month_debit_note_sgst_amount"></td>
                                       </tr>
                                       <tr>
                                          <td style='text-align:left'>Previous Credit Note (Portal)</td>
                                          <td style='text-align:right' id="previous_month_credit_note_amount"></td>
                                          <td style='text-align:right' id="previous_month_credit_note_igst_amount"></td>
                                          <td style='text-align:right' id="previous_month_credit_note_cgst_amount"></td>
                                          <td style='text-align:right' id="previous_month_credit_note_sgst_amount"></td>
                                       </tr>
                                       <tr><th colspan="4">Only In Book</th></tr>
                                       <tr>
                                          <td style='text-align:left'>Invoice (Only In Book)</td>
                                          <td style='text-align:right;cursor:pointer;color:#0000FF' data-detail="" data-journal-detail="" class="purchase_only_on_book_detail" id="only_on_book_purchase_amount"></td>
                                          <td style='text-align:right' id="only_on_book_purchase_igst_amount"></td>
                                          <td style='text-align:right' id="only_on_book_purchase_cgst_amount"></td>
                                          <td style='text-align:right' id="only_on_book_purchase_sgst_amount"></td>
                                       </tr>
                                       <tr>
                                          <td style='text-align:left'>Debit Note (Only In Book)</td>
                                          <td style='text-align:right' id="only_on_book_debit_note_amount"></td>
                                          <td style='text-align:right' id="only_on_book_debit_note_igst_amount"></td>
                                          <td style='text-align:right' id="only_on_book_debit_note_cgst_amount"></td>
                                          <td style='text-align:right' id="only_on_book_debit_note_sgst_amount"></td>
                                       </tr>
                                       <tr>
                                          <td style='text-align:left'>Credit Note (Only In Book)</td>
                                          <td style='text-align:right' id="only_on_book_credit_note_amount"></td>
                                          <td style='text-align:right' id="only_on_book_credit_note_igst_amount"></td>
                                          <td style='text-align:right' id="only_on_book_credit_note_cgst_amount"></td>
                                          <td style='text-align:right' id="only_on_book_credit_note_sgst_amount"></td>
                                       </tr>
                                       <tr><th colspan="4">Only On Portal</th></tr>
                                       <tr>
                                          <td style='text-align:left'>Invoice (Only On Portal)</td>
                                          <td style='text-align:right' id="only_on_portal_purchase_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_purchase_igst_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_purchase_cgst_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_purchase_sgst_amount"></td>
                                       </tr>
                                       <tr>
                                          <td style='text-align:left'>Debit Note (Only On Portal)</td>
                                          <td style='text-align:right' id="only_on_portal_debit_note_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_debit_note_igst_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_debit_note_cgst_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_debit_note_sgst_amount"></td>
                                       </tr>
                                       <tr>
                                          <td style='text-align:left'>Credit Note (Only On Portal)</td>
                                          <td style='text-align:right' id="only_on_portal_credit_note_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_credit_note_igst_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_credit_note_cgst_amount"></td>
                                          <td style='text-align:right' id="only_on_portal_credit_note_sgst_amount"></td>
                                       </tr>
                                       <tr>
                                          <th style='text-align:left'>Book Total</th>
                                          <th style='text-align:right' id="portal_after_above_affect_invoice">
                                          </th>
                                          <th style='text-align:right' id="portal_after_above_affect_igst">
                                          </th>
                                          <th style='text-align:right' id="portal_after_above_affect_cgst">
                                          </th>
                                          <th style='text-align:right' id="portal_after_above_affect_sgst">
                                          </th>
                                       </tr>
                                       {{-- <tr>
                                          <th style='text-align:left'>Book Total</th>
                                          <th style='text-align:right' id="book_total_invoice">
                                          </th>
                                          <th style='text-align:right' id="book_total_igst">
                                          </th>
                                          <th style='text-align:right' id="book_total_cgst">
                                          </th>
                                          <th style='text-align:right' id="book_total_sgst">
                                          </th>
                                       </tr> --}}
                                    </tbody>
                                 </table>
                           </div>
                           <div class="tab-pane" id="fill-tabpanel-2" role="tabpanel" aria-labelledby="fill-tab-2">
                              <div class="table-responsive">
                                 <table class="table table-bordered gstr2b_book">
                                    <thead>
                                       <tr>
                                          <th>S.No.</th>
                                          <th>GSTIN</th>
                                          <th>Party Name</th>
                                          <th>Invoice No.</th>
                                          <th>Invoice Date</th>
                                          <th>Invoice Type</th>
                                          <th>Invoice Value</th>
                                          <th>Taxable Value</th>
                                          <th>IGST</th>
                                          <th>CGST</th>
                                          <th>SGST</th>
                                       </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot></tfoot>
                                 </table>
                              </div>
                           </div>
                        </div> <!-- tab content -->
                     </div> 
                  </div> 
               </div>
               {{-- <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">GSTR2B 
                  <button class="btn btn-info reconciliation">Reconciliation</button>
                  
                  
               </h5>  --}}
               
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
<div class="modal fade" id="unlinkInvoiceModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
     <div class="modal-content">
       <div class="modal-header">
         <h5 class="modal-title">Unlinked  Invoices</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
       </div>
 
       <div class="modal-body">
         <table class="table table-bordered">
           <thead>
             <tr>
               <th>Voucher No</th>
               <th>Date</th>
               <th style="text-align: right">Total</th>
             </tr>
           </thead>
           <tbody id="unlinkInvoiceBody"></tbody>
         </table>
       </div>
     </div>
   </div>
 </div>
<div class="modal fade" id="purchase_only_on_book_detailModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Purchase Only On Book Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Invoice No.</th>
                        <th>Invoice Date</th>
                        <th style="text-align: right">Amount</th>
                    </tr>
                </thead>
                <tbody id="purchase_only_on_book_body">
                  <!-- Content will be populated via AJAX -->
                </tbody>
            </table> 
         </div>
      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script>
   var reconciliation_click = 0;
   var book_click = 0;
   $(document).ready(function(){
     
         let month = $("#month").val();
         let gstin = $("#gstin").val();
         //getGSTR2BData(month,gstin);
      
      
       $(".submit_btn").click(function(){
         let month = $("#month").val();
         let gstin = $("#gstin").val();
         getGSTR2BData(month,gstin);
      });
      $(".verify_otp").click(function(){
         let otp = $("#otp").val();
         let fgstin = $("#fgstin").val();
         let month = $("#month").val();
         let gstin = $("#gstin").val();
         if(otp==""){
            alert("Please Enter Otp");
            return;
         }
         $.ajax({
            url : "{{route('verify-gst-token-otp')}}",
            method : 'post',
            data : {
               _token : '{{ csrf_token() }}',
               otp : otp,
               gstin : fgstin
            },
            success : function(res){
               if(res!=""){
                  let obj = JSON.parse(res);
                  if(obj.status==true){
                     getGSTR2BData(month,gstin)
                  }else{
                     alert(obj.message);
                  }
               }else{
                  alert("Something Went Wrong.Please Try Again.");
               }
            }
         });
      });
      $(".reconciliation").click(function(){
         let month = $("#month").val();
         let gstin = $("#gstin").val();
         let url = "{{url('gstr2b-reconciliation-data')}}/month/gstin";
         url = url.replace('month',month);
         url = url.replace('gstin',gstin);
         window.location = url;
         
      });
   });
   function getGSTR2BData(month,gstin){
      $(".verify_gstr2b").hide();
      $("#verify_detail").html('');
      $("#cover-spin").show();
      $.ajax({
         url : "{{route('gstr2b-detail')}}",
         method : 'post',
         data : {
            _token : '{{ csrf_token() }}',
            month : month,
            gstin : gstin
         },
         success : function(res){
            if(res!=""){
               let obj = JSON.parse(res);
               if(obj.status==true){
                  if(obj.message=="TOKEN-OTP"){
                     $("#fgstin").val(gstin);
                     $("#otpModal").modal('toggle');
                     $("#cover-spin").hide();
                  }else if(obj.message=="SUCCESS"){
                     // $("#otpModal").modal('toggle');
                     alert("OTP Verified Successfully");
                     getGSTR2BData(month,gstin);
                     $("#cover-spin").hide();
                  }else if(obj.message=="GSTR2B"){
                     $(".gst_head").html('GSTR2B');
                     reconciliation_click = 0;
                     book_click = 0;
                     let html = "";let total_amount = 0;let total_book_amount = 0;
                     let total_b2b_portal = 0;
                     let total_b2b_books = 0;

                     let total_cdnr_portal = 0;
                     let total_cdnr_books = 0;
                     let total_diff = 0;
                     obj.data.forEach(element => {
                        let baseUrl = "{{ url('/gstr2b-all-info') }}";
                        let fullUrl = `${baseUrl}/${month}/${gstin}/${element.ctin}`;
                        let diffColor = '';
                        if(parseFloat(element.diff_amt) != 0){
                           diffColor = 'style="color:red;"';
                        }
                        
                        let b2bMatchColor = (
                           parseFloat(element.b2b_portal) === parseFloat(element.b2b_books)
                        ) ? 'style="color:green;font-weight:bold;"' : '';
                        
                        let cdnrMatchColor = (
                           parseFloat(element.cdnr_portal) === parseFloat(element.cdnr_books)
                        ) ? 'style="color:green;font-weight:bold;"' : '';
                        html += "<tr style='cursor:pointer;'>"+
                                "<td><a href='"+fullUrl+"'>"+element.trdnm+" ("+element.ctin+")</a></td>"+
                                
                                "<td style='text-align:right'><a "+b2bMatchColor+" href='"+fullUrl+"'>"+
                                Number(element.b2b_portal).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                                })+"</a></td>"+
                                
                                "<td style='text-align:right'><a "+b2bMatchColor+" href='"+fullUrl+"'>"+
                                Number(element.b2b_books).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                                })+"</a></td>"+
                                
                                "<td style='text-align:right'><a "+cdnrMatchColor+" href='"+fullUrl+"'>"+
                                Number(element.cdnr_portal).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                                })+"</a></td>"+
                                
                                "<td style='text-align:right'><a "+cdnrMatchColor+" href='"+fullUrl+"'>"+
                                Number(element.cdnr_books).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                                })+"</a></td>"+
                                
                                "<td style='text-align:right'><a "+diffColor+" href='"+fullUrl+"'>"+
                                Number(element.diff_amt).toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                                })+"</a></td></tr>";
                           total_b2b_portal += parseFloat(element.b2b_portal);
                           total_b2b_books  += parseFloat(element.b2b_books);

                           total_cdnr_portal += parseFloat(element.cdnr_portal);
                           total_cdnr_books  += parseFloat(element.cdnr_books);
                           total_amount += parseFloat(element.amount);
                           total_book_amount += parseFloat(element.book_value);
                           total_diff += parseFloat(element.diff_amt);
                     });
                     html+=`<tr>
                        <th>Total</th>
                        <th style='text-align:right'>${Number(total_b2b_portal).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                        <th style='text-align:right'>${Number(total_b2b_books).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                        <th style='text-align:right'>${Number(total_cdnr_portal).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                        <th style='text-align:right'>${Number(total_cdnr_books).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                        <th style='text-align:right'>${Number(total_diff).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                        </tr>`;
                     let finalHtml = html;

                        


                     if(obj.itcBookData){
                        let igst_amt_book = obj?.itcBookData?.IGST ?? 0;
                        let igst_amt_portal = obj?.itcApiData?.itc_elg?.itc_net?.iamt ?? 0;

                        let cgst_amt_book = obj?.itcBookData?.CGST ?? 0;
                        let cgst_amt_portal = obj?.itcApiData?.itc_elg?.itc_net?.camt ?? 0;

                        let sgst_amt_book = obj?.itcBookData?.SGST ?? 0;
                        let sgst_amt_portal = obj?.itcApiData?.itc_elg?.itc_net?.samt ?? 0;

                        finalHtml += `
                        <tr>
                           <td colspan="6">
                                 <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px;">

                                    <h6 style="margin:0;">
                                       Tax Summary
                                    </h6>
                                    
                                    <span id="verify_detail" style="float: right;color:green"></span>
                                    <button class="btn btn-info verify_gstr2b" style="display:none;float: right;">Verify</button>
                                 </div><br>
                                 <table class="table table-bordered ">
                                    <tr class="section-header">
                                       <th>4. Eligible ITC</th>
                                       <th>Books</th>
                                       <th class="d-flex justify-content-between align-items-center">
                                       <span>Portal</span> </th>
                                        <th>Difference</th>
                                    </tr>
                                    <tr>
                                       <td>Integrated Tax</td>
                                       <td>₹ ${formatIndianNumber(obj?.itcBookData?.IGST ?? 0)}</td>
                                       <td>₹ ${formatIndianNumber(obj?.itcApiData?.itc_elg?.itc_net?.iamt ?? 0)}</td>
                                       <td>₹ ${formatIndianNumber(Math.abs(igst_amt_book -igst_amt_portal))}</td>
                                    </tr>
                                    <tr>
                                       <td>Central Tax</td>
                                       <td>₹ ${formatIndianNumber(obj?.itcBookData?.CGST ?? 0)}</td>
                                       <td>₹ ${formatIndianNumber(obj?.itcApiData?.itc_elg?.itc_net?.camt ?? 0)}</td>
                                       <td>₹ ${formatIndianNumber(Math.abs(cgst_amt_book - cgst_amt_portal))}</td>
                                    </tr>
                                    <tr>
                                       <td>State/UT Tax</td>
                                       <td>₹ ${formatIndianNumber(obj?.itcBookData?.SGST ?? 0)}</td>
                                       <td>₹ ${formatIndianNumber(obj?.itcApiData?.itc_elg?.itc_net?.samt ?? 0)}</td>
                                       <td>₹ ${formatIndianNumber(Math.abs(sgst_amt_book - sgst_amt_portal))}</td>
                                    </tr>
                                    <tr>
                                       <td>CESS</td>
                                       <td>₹ 0.00</td>
                                       <td>₹ ${formatIndianNumber(obj?.itcApiData?.itc_elg?.itc_net?.csamt ?? 0)}</td>
                                       <td>₹ 0.00</td>
                                    </tr>
                                 </table>`;

                     }
                     if(obj.pending_notes && obj.pending_notes.length){
                        finalHtml += `
                        <tr>
                           <td colspan="6">
                                 <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px;">

                                    <h6 style="margin:0;">
                                       Pending Credit / Debit Notes (Unlinked)
                                    </h6>

                                    <div style="display:flex; align-items:center; gap:12px; font-size:20px;">

                                       <span class="pending_print_btn"
                                          title="Print"
                                          style="cursor:pointer;">
                                          🖨️
                                       </span>

                                       <span class="pending_excel_btn"
                                          title="Export Excel"
                                          style="cursor:pointer;">
                                          📥
                                       </span>
                                       <span class="pending_toggle_btn" title="Show/Hide Table" style="cursor:pointer;">
                                          👁️
                                       </span>
                                    </div>

                                 </div>
                                 <div class="pending_table_wrapper" style="display:none;">
                                 <table class="table table-bordered pending_notes_table">
                                    <thead>
                                       <tr>
                                             <th>Sr No</th>
                                             <th>Party</th>
                                             <th>Type</th>
                                             <th>Invoice No</th>
                                             <th>Date</th>
                                             <th>Book Value</th>
                                             <th>Taxable</th>
                                             <th>IGST</th>
                                             <th>CGST</th>
                                             <th>SGST</th>
                                             <th>Cess</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                        `;

                        obj.pending_notes.forEach(r => {
                           finalHtml += `
                           <tr>
                                 <td>${r.sr_no}</td>
                                 <td>${r.party}</td>
                                 <td>${r.type}</td>
                                 <td>${r.invoice_no}</td>
                                 <td>${r.date}</td>
                                 <td style="text-align:right">${Number(r.book_value).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.taxable).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.igst).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.cgst).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.sgst).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.cess).toLocaleString('en-IN')}</td>
                           </tr>`;
                        });

                        finalHtml += `
                                    </tbody>
                                 </table>
                                 </div>
                           </td>
                        </tr>`;
                     }
                     if(obj.pending_invoice && obj.pending_invoice.length){
                        finalHtml += `
                        <tr>
                           <td colspan="6">
                                 <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px;">

                                    <h6 style="margin:0;">
                                       Pending Invoices (Unlinked)
                                    </h6>

                                    <div style="display:flex; align-items:center; gap:12px; font-size:20px;">

                                       <span class="pending_invoice_print_btn"
                                          title="Print"
                                          style="cursor:pointer;">
                                          🖨️
                                       </span>

                                       <span class="pending_invoice_excel_btn"
                                          title="Export Excel"
                                          style="cursor:pointer;">
                                          📥
                                       </span>
                                       <span class="pending_invoice_toggle_btn" title="Show/Hide Table" style="cursor:pointer;">
                                          👁️
                                       </span>
                                    </div>

                                 </div>
                                 <div class="pending_invoice_table_wrapper" style="display:none;">
                                 <table class="table table-bordered pending_invoice_table">
                                    <thead>
                                       <tr>
                                             <th>Sr No</th>
                                             <th>Party</th>
                                             <th>Type</th>
                                             <th>Invoice No</th>
                                             <th>Date</th>
                                             <th>Book Value</th>
                                             <th>Taxable</th>
                                             <th>IGST</th>
                                             <th>CGST</th>
                                             <th>SGST</th>
                                             <th>Cess</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                        `;

                        obj.pending_invoice.forEach(r => {
                           finalHtml += `
                           <tr>
                                 <td>${r.sr_no}</td>
                                 <td>${r.party}</td>
                                 <td>${r.type}</td>
                                 <td>${r.invoice_no}</td>
                                 <td>${r.date}</td>
                                 <td style="text-align:right">${Number(r.book_value).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.taxable).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.igst).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.cgst).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.sgst).toLocaleString('en-IN')}</td>
                                 <td style="text-align:right">${Number(r.cess).toLocaleString('en-IN')}</td>
                           </tr>`;
                        });

                        finalHtml += `
                                    </tbody>
                                 </table>
                                 </div>
                           </td>
                        </tr>`;
                     }
                     $(".gst_table tbody").html(finalHtml);
                     if(obj.verify_status==0){
                         $(".verify_gstr2b").show();
                     }else if(obj.verify_status==1){
                         $("#verify_detail").html("Verified By : "+obj.verify_by+", Verify Date : "+obj.verify_date);
                     }
                     $("#gst_div").show();
                     $("#cover-spin").hide();
                  }
               }else{
                  alert(obj.message);
                  $("#cover-spin").hide();
               }
            }else{
               alert("Something Went Wrong.Please Try Again.");
               $("#cover-spin").hide();
            }
         }
      });
   }
   
   $(document).on('click','.verify_gstr2b',function(){
      if(confirm("Are you sure to verify?")==true){
         let month = $("#month").val();
         let gstin = $("#gstin").val();
         if(gstin=="" || month==""){
               alert("All Fields Required.");
               return;
         }
         $("#cover-spin").show();
         $.ajax({
               url : "{{route('verify-gst2b')}}",
               method : 'post',
               data : {
                  _token : '{{ csrf_token() }}',
                  month : month,
                  gstin : gstin
               },
               success : function(res){
                  if(res!=""){
                     let obj = JSON.parse(res);
                     if(obj.status==true){
                        alert("Verified Successfully.");
                        getGSTR2BData(month,gstin);
                     }else{
                           alert("Something Went Wrong.");
                     }
                     
                  }else{
                        alert("Something Went Wrong.");
                  }
                  $("#cover-spin").hide();
               }
               
         });
      }
   });
   $(document).on('click','#fill-tab-1',function(){
      let month = $("#month").val();
      let gstin = $("#gstin").val();
      let url = "{{url('gstr2b-reconciliation-data')}}/month/gstin";
      url = url.replace('month',month);
      url = url.replace('gstin',gstin);
      if(reconciliation_click==1){
         return;
      }
      $("#cover-spin").show();
      $.ajax({
            url : url,
            method : 'GET',
            data : {
               _token : '{{ csrf_token() }}'
            },
            success : function(res){
               if(res!=""){
                  let obj = JSON.parse(res);
                  reconciliation_click = 1;
                  
                  $("#portal_invoice_amount").html(formatIndianNumber(obj.portal_invoice_amount));
                  $("#portal_igst_amount").html(formatIndianNumber(obj.portal_igst_amount));
                  $("#portal_cgst_amount").html(formatIndianNumber(obj.portal_cgst_amount));
                  $("#portal_sgst_amount").html(formatIndianNumber(obj.portal_sgst_amount));
                  $("#previous_month_invoice_amount").html(formatIndianNumber(parseFloat(obj.previous_month_invoice_amount) + parseFloat(obj.previous_month_journal_amount)));
                  $("#previous_month_invoice_igst_amount").html(parseFloat(obj.previous_month_invoice_igst_amount) + parseFloat(obj.previous_month_journal_igst_amount));
                  $("#previous_month_invoice_cgst_amount").html(formatIndianNumber(parseFloat(obj.previous_month_invoice_cgst_amount) + parseFloat(obj.previous_month_journal_cgst_amount)));
                  $("#previous_month_invoice_sgst_amount").html(formatIndianNumber(parseFloat(obj.previous_month_invoice_sgst_amount) + parseFloat(obj.previous_month_journal_sgst_amount)));
                  $("#previous_month_debit_note_amount").html(formatIndianNumber(obj.previous_month_debit_note_amount));
                  $("#previous_month_debit_note_igst_amount").html(formatIndianNumber(obj.previous_month_debit_note_igst_amount));
                  $("#previous_month_debit_note_cgst_amount").html(formatIndianNumber(obj.previous_month_debit_note_cgst_amount));
                  $("#previous_month_debit_note_sgst_amount").html(formatIndianNumber(obj.previous_month_debit_note_sgst_amount));
                  $("#previous_month_credit_note_amount").html(formatIndianNumber(obj.previous_month_credit_note_amount));
                  $("#previous_month_credit_note_igst_amount").html(formatIndianNumber(obj.previous_month_credit_note_igst_amount));
                  $("#previous_month_credit_note_cgst_amount").html(formatIndianNumber(obj.previous_month_credit_note_cgst_amount));
                  $("#previous_month_credit_note_sgst_amount").html(formatIndianNumber(obj.previous_month_credit_note_sgst_amount));
                  $("#only_on_book_purchase_amount").html(formatIndianNumber(obj.only_on_book_purchase_amount));
                  $("#only_on_book_purchase_igst_amount").html(formatIndianNumber(obj.only_on_book_purchase_igst_amount));
                  $("#only_on_book_purchase_cgst_amount").html(formatIndianNumber(obj.only_on_book_purchase_cgst_amount));
                  $("#only_on_book_purchase_sgst_amount").html(formatIndianNumber(obj.only_on_book_purchase_sgst_amount));
                  $("#only_on_book_debit_note_amount").html(formatIndianNumber(obj.only_on_book_debit_note_amount));
                  $("#only_on_book_debit_note_igst_amount").html(formatIndianNumber(obj.only_on_book_debit_note_igst_amount));
                  $("#only_on_book_debit_note_cgst_amount").html(formatIndianNumber(obj.only_on_book_debit_note_cgst_amount));
                  $("#only_on_book_debit_note_sgst_amount").html(formatIndianNumber(obj.only_on_book_debit_note_sgst_amount));
                  $("#only_on_book_credit_note_amount").html(formatIndianNumber(obj.only_on_book_credit_note_amount));
                  $("#only_on_book_credit_note_igst_amount").html(formatIndianNumber(obj.only_on_book_credit_note_igst_amount));
                  $("#only_on_book_credit_note_cgst_amount").html(formatIndianNumber(obj.only_on_book_credit_note_cgst_amount));
                  $("#only_on_book_credit_note_sgst_amount").html(formatIndianNumber(obj.only_on_book_credit_note_sgst_amount));
                  $("#only_on_portal_purchase_amount").html(formatIndianNumber(obj.only_on_portal_purchase_amount));
                  $("#only_on_portal_purchase_igst_amount").html(formatIndianNumber(obj.only_on_portal_purchase_igst_amount));
                  $("#only_on_portal_purchase_cgst_amount").html(formatIndianNumber(obj.only_on_portal_purchase_cgst_amount));
                  $("#only_on_portal_purchase_sgst_amount").html(formatIndianNumber(obj.only_on_portal_purchase_sgst_amount));
                  $("#only_on_portal_debit_note_amount").html(formatIndianNumber(obj.only_on_portal_debit_note_amount));
                  $("#only_on_portal_debit_note_igst_amount").html(formatIndianNumber(obj.only_on_portal_debit_note_igst_amount));
                  $("#only_on_portal_debit_note_cgst_amount").html(formatIndianNumber(obj.only_on_portal_debit_note_cgst_amount));
                  $("#only_on_portal_debit_note_sgst_amount").html(formatIndianNumber(obj.only_on_portal_debit_note_sgst_amount));
                  $("#only_on_portal_credit_note_amount").html(formatIndianNumber(obj.only_on_portal_credit_note_amount));
                  $("#only_on_portal_credit_note_igst_amount").html(formatIndianNumber(obj.only_on_portal_credit_note_igst_amount));
                  $("#only_on_portal_credit_note_cgst_amount").html(formatIndianNumber(obj.only_on_portal_credit_note_cgst_amount));
                  $("#only_on_portal_credit_note_sgst_amount").html(formatIndianNumber(obj.only_on_portal_credit_note_sgst_amount));
                  $("#book_total_invoice").html(formatIndianNumber(obj.book_total_invoice));
                  $("#book_total_igst").html(formatIndianNumber(obj.book_total_igst));
                  $("#book_total_cgst").html(formatIndianNumber(obj.book_total_cgst));
                  $("#book_total_sgst").html(formatIndianNumber(obj.book_total_sgst));
                  $("#only_on_book_purchase_amount").attr('data-detail',JSON.stringify(obj.purchase_only_on_book_detail));
                  $("#only_on_book_purchase_amount").attr('data-journal-detail',JSON.stringify(obj.journal_only_on_book_detail));
                  let portal_after_above_affect_invoice = parseFloat(obj.portal_invoice_amount)
                           - parseFloat(obj.previous_month_invoice_amount)
                           - parseFloat(obj.previous_month_debit_note_amount)
                           + parseFloat(obj.previous_month_credit_note_amount) 
                           + parseFloat(obj.only_on_book_purchase_amount)
                           - parseFloat(obj.only_on_book_debit_note_amount)
                           + parseFloat(obj.only_on_book_credit_note_amount)
                           - parseFloat(obj.only_on_portal_purchase_amount)
                           - parseFloat(obj.only_on_portal_debit_note_amount)
                           + parseFloat(obj.only_on_portal_credit_note_amount);
                  $("#portal_after_above_affect_invoice").html(formatIndianNumber(portal_after_above_affect_invoice));
                  let portal_after_above_affect_igst = parseFloat(obj.portal_igst_amount)
                           - parseFloat(obj.previous_month_invoice_igst_amount)
                           + parseFloat(obj.previous_month_journal_igst_amount)
                           - parseFloat(obj.previous_month_debit_note_igst_amount)
                           + parseFloat(obj.previous_month_credit_note_igst_amount)
                           + parseFloat(obj.only_on_book_purchase_igst_amount)
                           - parseFloat(obj.only_on_book_debit_note_igst_amount)
                           + parseFloat(obj.only_on_book_credit_note_igst_amount)
                           - parseFloat(obj.only_on_portal_purchase_igst_amount)
                           - parseFloat(obj.only_on_portal_debit_note_igst_amount)
                           + parseFloat(obj.only_on_portal_credit_note_igst_amount);
                  $("#portal_after_above_affect_igst").html(formatIndianNumber(portal_after_above_affect_igst));
                  let portal_after_above_affect_cgst = parseFloat(obj.portal_cgst_amount)
                           - parseFloat(obj.previous_month_invoice_cgst_amount)
                           + parseFloat(obj.previous_month_journal_cgst_amount)
                           - parseFloat(obj.previous_month_debit_note_cgst_amount)
                           + parseFloat(obj.previous_month_credit_note_cgst_amount)
                           + parseFloat(obj.only_on_book_purchase_cgst_amount)
                           - parseFloat(obj.only_on_book_debit_note_cgst_amount)
                           + parseFloat(obj.only_on_book_credit_note_cgst_amount)
                           - parseFloat(obj.only_on_portal_purchase_cgst_amount)
                           - parseFloat(obj.only_on_portal_debit_note_cgst_amount)
                           + parseFloat(obj.only_on_portal_credit_note_cgst_amount);
                  $("#portal_after_above_affect_cgst").html(formatIndianNumber(portal_after_above_affect_cgst));
                  let portal_after_above_affect_sgst = parseFloat(obj.portal_sgst_amount)
                           - parseFloat(obj.previous_month_invoice_sgst_amount) 
                           + parseFloat(obj.previous_month_journal_sgst_amount)
                           - parseFloat(obj.previous_month_debit_note_sgst_amount)
                           + parseFloat(obj.previous_month_credit_note_sgst_amount)
                           + parseFloat(obj.only_on_book_purchase_sgst_amount)
                           - parseFloat(obj.only_on_book_debit_note_sgst_amount)
                           + parseFloat(obj.only_on_book_credit_note_sgst_amount)
                           - parseFloat(obj.only_on_portal_purchase_sgst_amount)
                           - parseFloat(obj.only_on_portal_debit_note_sgst_amount)
                           + parseFloat(obj.only_on_portal_credit_note_sgst_amount);
                  $("#portal_after_above_affect_sgst").html(formatIndianNumber(portal_after_above_affect_sgst));

               }else{
                     alert("Something Went Wrong.");
               }
               $("#cover-spin").hide();
            }
            
      });
   });
   $(".purchase_only_on_book_detail").click(function(){
      let details = $(this).data('detail');
      let journal_details = $(this).data('journal-detail');
      details = details.concat(journal_details);
      let tbody = "";
      const formatDate = (dateStr) => {
         if (!dateStr) return '';
         let d = new Date(dateStr);
         let dd = String(d.getDate()).padStart(2, '0');
         let mm = String(d.getMonth() + 1).padStart(2, '0');
         let yyyy = d.getFullYear();
         return `${dd}-${mm}-${yyyy}`;
      };
      const formatAmount = (amount) => {
         return Number(amount).toLocaleString('en-IN', {
               minimumFractionDigits: 2,
               maximumFractionDigits: 2
         });
      };
      details.forEach(function(item){
         let account_name = item.account_name;
         if(item.claim_gst_status){
            account_name += " (JOURNAL)";
         }
         tbody += `<tr>
            <td>${account_name} </td>
            <td>${item.voucher_no}</td>
            <td>${formatDate(item.date)}</td>
            <td style="text-align: right;">${formatAmount(item.amount)}</td>
         </tr>`;
      });

      tbody += `<tr>
         <th colspan="3" style="text-align: right;">Total</th>
         <th style="text-align: right;">${formatAmount(
            details.reduce((sum, item) => sum + Number(item.amount), 0)
      )}</th>
      </tr>`;
      $("#purchase_only_on_book_body").html(tbody);
      $("#purchase_only_on_book_detailModal").modal('show');
   });
   function formatIndianNumber(num) {
      return new Intl.NumberFormat('en-IN', {
         minimumFractionDigits: 2,
         maximumFractionDigits: 2
      }).format(num || 0);
   }
   $(document).on('click','#fill-tab-2',function(){
      let month = $("#month").val();
      let gstin = $("#gstin").val();
      let url = "{{url('gstr3b/view/itcdetails')}}";
      let [year, mon] = month.split("-");
      // First date
      let from_date = `${year}-${mon}-01`;
      if(book_click==1){
         return;
      }
      // Last date
      let lastDateObj = new Date(year, mon, 0); 
      // JS months are 0-based, so passing mon gives last day of selected month

      let to_date = `${year}-${mon}-${String(lastDateObj.getDate()).padStart(2, '0')}`;
      $("#cover-spin").show();
      $.ajax({
            url : url,
            method : 'GET',
            data : {
               _token : '{{ csrf_token() }}',
               series : gstin,
               source : 'GSTR2B',
               from_date : from_date,
               to_date : to_date
            },
            success : function(res){
               if(res!=""){
                  book_click = 1;
                  let obj = JSON.parse(res);
                  let body_data = ""; let tfoot_data = "";
                  let sr = 1;
                  let invoiceTotal = 0;
                  let taxableTotal = 0;
                  let igstTotal = 0;
                  let cgstTotal = 0;
                  let sgstTotal = 0;
                  if(obj.data && obj.data.length>0){
                     obj.data.forEach(function(e){
                        let inv_url = '#';
                        if(e.voucher_source == 'purchase'){
                           inv_url = 'purchase-edit/'+e.voucher_id;
                        }else if(e.voucher_source == 'purchase_return'){
                           inv_url = 'purchase-return-edit/'+e.voucher_id;
                        }
                        else if(e.voucher_source == 'sale_return'){
                           inv_url = 'sale-return-edit/'+e.voucher_id;
                        }
                        else if(e.voucher_source == 'journal'){
                           inv_url = 'journal/'+e.voucher_id+'/edit';
                        }

                        if(e.invoice_type == 'Purchase Debit Note')
                        {
                           invoiceTotal -= parseFloat(e.invoice_value);
                           taxableTotal -= parseFloat(e.taxable_value);
                           igstTotal -= parseFloat(e.igst);
                           cgstTotal -= parseFloat(e.cgst);
                           sgstTotal -= parseFloat(e.sgst);
                        }
                        else
                        {
                           invoiceTotal += parseFloat(e.invoice_value);
                           taxableTotal += parseFloat(e.taxable_value);
                           igstTotal += parseFloat(e.igst);
                           cgstTotal += parseFloat(e.cgst);
                           sgstTotal += parseFloat(e.sgst);
                        }
                        body_data+=`<tr>
                              <td>${sr++}</td>
                              <td>${e.gstin ?? e.account_gst}</td>
                              <td>${e.party_name}</td>
                              <td>
                                 <a href="${inv_url}" target="_blank">
                                       ${e.invoice_no ?? '-'}
                                 </a>
                              </td>
                              <td>
                                 ${e.invoice_date}
                              </td>
                              <td>${e.invoice_type}</td>
                              <td class="text-right">
                                 ${formatIndianNumber(e.invoice_value)}
                              </td>
                              <td class="text-right">
                                 ${formatIndianNumber(e.taxable_value)}
                              </td>
                              <td class="text-right">
                                 ${formatIndianNumber(e.igst)}
                              </td>
                              <td class="text-right">
                                 ${formatIndianNumber(e.cgst)}
                              </td>
                              <td class="text-right">
                                 ${formatIndianNumber(e.sgst)}
                              </td>
                           </tr>`;
                     });
                  }
                  tfoot_data = `
                  <tr style="font-weight:bold;background:#f8f9fa;">
                     <td colspan="6" style="text-align:right;font-weight:bold;">
                        Total
                     </td>
                     <td class="text-right">
                        ${formatIndianNumber(invoiceTotal)}
                     </td>
                     <td class="text-right">
                        ${formatIndianNumber(taxableTotal)}
                     </td>
                     <td class="text-right">
                        ${formatIndianNumber(igstTotal)}
                     </td>
                     <td class="text-right">
                        ${formatIndianNumber(cgstTotal)}
                     </td>
                     <td class="text-right">
                        ${formatIndianNumber(sgstTotal)}
                     </td>
                  </tr>`;
                  $(".gstr2b_book tbody").html(body_data);
                  $(".gstr2b_book tfoot").html(tfoot_data);
               }else{
                  alert("Something Went Wrong.");
               }
               $("#cover-spin").hide();
            }
      });
   });
   $(document).on("click", ".pending_toggle_btn", function () {
      let wrapper = $(this)
         .closest("td")
         .find(".pending_table_wrapper");

      wrapper.slideToggle(200);

      // Optional icon change
      if ($(this).text() === "👁️") {
         $(this).text("🙈");
      } else {
         $(this).text("👁️");
      }
   });
   $(document).on("click", ".pending_invoice_toggle_btn", function () {
      let wrapper = $(this)
         .closest("td")
         .find(".pending_invoice_table_wrapper");

      wrapper.slideToggle(200);

      // Optional icon change
      if ($(this).text() === "👁️") {
         $(this).text("🙈");
      } else {
         $(this).text("👁️");
      }
   });
   $(document).on('click', '.pending_print_btn', function () {
    let tableHtml = $(this)
        .closest('td')
        .find('.pending_notes_table')[0].outerHTML;

    let w = window.open('', '', 'width=1200,height=700');
    w.document.write(`
        <html>
        <head>
            <title>Pending Notes</title>
            <style>
                table{border-collapse:collapse;width:100%;}
                th,td{border:1px solid #000;padding:6px;text-align:left;}
            </style>
        </head>
        <body>${tableHtml}</body>
        </html>
    `);
    w.document.close();
    w.print();
});
$(document).on('click', '.pending_invoice_print_btn', function () {
    let tableHtml = $(this)
        .closest('td')
        .find('.pending_invoice_table')[0].outerHTML;

    let w = window.open('', '', 'width=1200,height=700');
    w.document.write(`
        <html>
        <head>
            <title>Pending Invoice</title>
            <style>
                table{border-collapse:collapse;width:100%;}
                th,td{border:1px solid #000;padding:6px;text-align:left;}
            </style>
        </head>
        <body>${tableHtml}</body>
        </html>
    `);
    w.document.close();
    w.print();
});

function downloadTableAsCSV(tableSelector, filename) {
    let csv = [];
    let rows = document.querySelectorAll(tableSelector + " tr");

    rows.forEach(row => {
        let cols = row.querySelectorAll("td, th");
        let rowData = [];

        cols.forEach(col => {
            rowData.push('"' + col.innerText.replace(/"/g, '""') + '"');
        });

        csv.push(rowData.join(","));
    });

    let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
    let downloadLink = document.createElement("a");

    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";

    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
$(document).on('click', '.pending_excel_btn', function () {
    downloadTableAsCSV('.pending_notes_table', 'pending_notes.csv');
});
$(document).on('click', '.pending_invoice_excel_btn', function () {
    downloadTableAsCSV('.pending_invoice_table', 'pending_invoice.csv');
});
</script>
@endsection