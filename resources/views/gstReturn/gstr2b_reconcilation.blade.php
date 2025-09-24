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

</div>
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
            @php
            $date = DateTime::createFromFormat("Y-m", $month);
            @endphp
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Reconciliation for the month of  {{$date->format("F")}} </h5>
            <table class="table table-ordered bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm gst_table">
                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th style='text-align:right'>Amount</th>
                    </tr>
                </thead>
                <tbody style="font-size: 15px;">
                    <tr style="cursor: pointer" class="reconciliation_details" data-type="only_in_portal" data-month="{{$month}}" data-gstin="{{$gstin}}">
                        <td>Input On Portal</td>
                        <td style='text-align:right'>{{formatIndianNumber($total_val_on_portal_but_not_in_book)}}</td>
                    </tr>
                    <tr style="cursor: pointer" class="reconciliation_details" data-type="only_in_book" data-month="{{$month}}" data-gstin="{{$gstin}}">
                        <td>Only In books</td>
                        <td style='text-align:right'>{{formatIndianNumber($total_val_only_in_book)}}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <th style='text-align:right'>{{formatIndianNumber($total_val_on_portal_but_not_in_book+$total_val_only_in_book)}}</th>
                    </tr>
                </tbody>
            </table>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Aggregate Reconciliation from April upto date</h5>
            <table class="table table-ordered bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm gst_table">
                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th style='text-align:right'>Amount</th>
                    </tr>
                </thead>
                <tbody style="font-size: 15px;">
                    <tr style="cursor: pointer" class="reconciliation_details" data-type="only_in_portal_all" data-month="{{$month}}" data-gstin="{{$gstin}}">
                        <td>Input On Portal</td>
                        <td style='text-align:right'>{{formatIndianNumber($total_val_on_portal_but_not_in_book1)}}</td>
                    </tr>
                    <tr style="cursor: pointer" class="reconciliation_details" data-type="only_in_book_all" data-month="{{$month}}" data-gstin="{{$gstin}}">
                        <td>Only In books</td>
                        <td style='text-align:right'>{{formatIndianNumber($total_book_value_only_in_book)}}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <th style='text-align:right'>{{formatIndianNumber($total_val_on_portal_but_not_in_book1+$total_book_value_only_in_book)}}</th>
                    </tr>
                </tbody>
            </table>
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
<div class="modal fade" id="reconciliationModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title reconcilation-modal-title">Reconcilation </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Invoice No.</th>
                        <th>Invoice Date</th>
                        <th style="text-align: right">Portal Amount</th>
                        <th style="text-align: right">Book Amount</th>
                        <th style="text-align: right">Taxable Amount</th>
                        <th style="text-align: right">IGST Amount</th>
                        <th style="text-align: right">CGST Amount</th>
                        <th style="text-align: right">SGST Amount</th>
                        <th style="text-align: right">CESS Amount</th>
                    </tr>
                </thead>
                <tbody id="reconciliation_table_body">
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
    $(document).ready(function(){
        $(".reconciliation_details").click(function(){
            var type = $(this).data('type');
            var month = $(this).data('month');
            var gstin = $(this).data('gstin');
            $("#cover-spin").show();
            $.ajax({
                url: "{{ url('gstr2b-reconciliation-detail') }}",
                method: 'POST',
                data: {'type': type,'month':month,'gstin':gstin},
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Add this if CSRF token is needed
                },
                success: function (response) {
                    let res = JSON.parse(response);
                    let tbody = "";
                    if(type=="only_in_portal" || type=="only_in_portal_all"){
                        tbody = res.only_in_portal;
                    }else if(type=="only_in_book" || type=="only_in_book_all"){
                        tbody = res.only_in_book;
                    }                    
                    $(".reconcilation-modal-title").html(type=="only_in_portal"?"Input On Portal":"Only In Books");
                    $("#reconciliation_table_body").html(tbody);
                    //reconciliation_table_body
                    $("#reconciliationModal").modal('show');
                    $("#cover-spin").hide();
                },
                error: function() {
                    alert('An error occurred while fetching the data.');
                }
            });
           
            
        });      
    });
</script>
@endsection