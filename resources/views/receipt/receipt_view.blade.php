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
             <div class="alert alert-danger" role="alert"> {{session('error')}}
             </div>
             @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Import Receipt</h5>
            <!-- <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale-import-process') }}" multipart enctype="multipart/form-data"> -->
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" id="upload_form" multipart enctype="multipart/form-data">
               @csrf
               <div class="row">
                  <div class="mb-5 col-md-5">
                     <label for="name" class="form-label font-14 font-heading">Upload CSV File *</label> <strong>(<a href="{{ URL::asset('public/csv-template/MA - PaymentCSV .csv')}}">Download Template</a>)</strong>
                     <span style="display: inline-flex">
                        
                        <input type="file" class="form-control" id="csv_file" name="csv_file" required style="height: 30%;" accept=".csv">
                     </span>
                     <ul style="color: red;">
                       @error('csv_file'){{$message}}@enderror                        
                     </ul>
                  </div>
               </div> 
               <div class=" d-flex">                  
                  <div class="ms-auto">
                     <input type="submit" value="SAVE" class="btn btn-xs-primary" id="saveBtn">
                     <a href="{{ route('sale.index') }}" class="btn  btn-black ">QUIT</a>
                  </div>
               </div>
            </form>            
               <p style="font-size:20px;text-align:center;" class="res_log total_voucher"></p>
               <p style="font-size:20px;text-align:center;color:green" class="res_log insert_voucher"></p>
               <p style="font-size:20px;text-align:center;color:red" class="res_log failed_voucher"></p>
               <p style="font-size:20px;text-align:center;color:red" class="res_log error_msg">

               </p>
                              
            
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
<div class="modal fade" id="voucherModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog  modal-dialog-centered modal-lg">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <h4 class="modal-title">Duplicate Voucher List (<span id="duplicate_count"></span>)</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div><br><br>
         <div class="modal-body text-center p-0">
         <div class="modal-footer border-0 mx-auto p-0" style="display: block;">            
            <button type="button" class="btn btn-danger upload_duplicate_voucher_no">Ignore Duplicate Voucher</button>
            <button type="button" class="ms-3 btn btn-info upload_duplicate_voucher_yes">Override Duplicate Voucher</button>
         </div>
         <br><br>
            <div class="row duplicate_voucher_list">
               
            </div>
         </div>
         
      </div>
   </div>
</div>
</body>
@include('layouts.footer')
<script type="text/javascript">
   
   var duplicate_voucher_status = 0;
   $('form#upload_form').on('submit', function(e){
      e.preventDefault();
      var uploadFile =  $('#csv_file');
      var ext = $("input#csv_file").val().split(".").pop().toLowerCase();
      var file = $('input[name="csv_file"]').val();
      if($.inArray(ext, ["csv"]) === -1) {
         alert("Please upload a .csv file!");
         return false;
      }
      var csv = uploadFile[0].files;
      var formData = new FormData($(this)[0]);
      formData.append('uploaded_file', $("#csv_file")[0].files[0]);
      formData.append('lastModifed', csv[0].lastModified);
      formData.append('fileName', csv[0].name);
      formData.append('duplicate_voucher_status',duplicate_voucher_status);
      $("#cover-spin").show();
      $.ajax({
         url: '{{ URL::route("receipt-import-process") }}',
         type: 'POST',
         data: formData,
         async: true,
         cache: false,
         contentType: false,
         processData: false,
         success: function (returndata) {             
            let obj = JSON.parse(returndata);
            if(obj.status==false){
               if(obj.message=="Already Exists."){
                  let list = "";
                  $("#duplicate_count").html(obj.data.length);
                  obj.data.forEach(function(e){
                     list+= "<p>"+e+"</p>";
                  });                  
                  $(".duplicate_voucher_list").html(list);
                  $("#voucherModal").modal('toggle');
               }else{

               }
            }else if(obj.status==true){
               duplicate_voucher_status = 0;
               $('#csv_file').val('');
               $(".total_voucher").html("Total Voucher : "+obj.data.total_count);
               $(".insert_voucher").html("Insert Voucher : "+obj.data.success_count);
               $(".failed_voucher").html("Failed Voucher : "+obj.data.failed_count);
               let err = "";
               obj.data.error_message.forEach(function(e){
                  err+='<p>'+e[0]+'</p>';
               });
               $(".error_msg").html(err);
            }
            $("#cover-spin").hide();
         }
      });
   });

   $(".upload_duplicate_voucher_no").click(function(){
      duplicate_voucher_status = 1; //Not Override Duplicate Voucher
      $("#voucherModal").modal('toggle');
      $('#upload_form').submit();      
   });
   $(".upload_duplicate_voucher_yes").click(function(){
      
      duplicate_voucher_status = 2; //Override Duplicate Voucher
      $("#voucherModal").modal('toggle');
      $('#upload_form').submit();      
   });
 </script>
@endsection