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
               <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm gst_head"></h5>
               <table class="table table-ordered bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm gst_table">
                  <thead>
                     <tr>
                        <th>Account Name</th>
                        <th style='text-align:right'>Amount</th>
                     </tr>
                  </thead>
                  <tbody style="font-size: 15px;">
                     
                  </tbody>
               </table>
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
</body>
@include('layouts.footer')
<script>
   $(document).ready(function(){
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
   });
   function getGSTR2BData(month,gstin){
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
                  }else if(obj.message=="SUCCESS"){
                     // $("#otpModal").modal('toggle');
                     alert("OTP Verified Successfully");
                     getGSTR2BData(month,gstin);
                  }else if(obj.message=="GSTR2B"){
                     $(".gst_head").html('GSTR2B');
                     let html = "";let total_amount = 0;
                     obj.data.forEach(element => {
                        let baseUrl = "{{ url('/gstr2b-all-info') }}";
                        let fullUrl = `${baseUrl}/${month}/${gstin}/${element.ctin}`;
                        html+="<tr style='cursor:pointer'><td><a href='"+fullUrl+"'>"+element.trdnm+" ("+element.ctin+")</a></td><td style='text-align:right'><a href='"+fullUrl+"'>"+Number(element.amount).toLocaleString('en-IN', {
                                       minimumFractionDigits: 2,
                                       maximumFractionDigits: 2
                                       })+"</a></td></tr>";
                        total_amount += parseFloat(element.amount);
                     });
                     html+="<tr><th>Total</th><th style='text-align:right'>"+Number(total_amount).toLocaleString('en-IN', {
                           minimumFractionDigits: 2,
                           maximumFractionDigits: 2
                           })+"</th></tr>";
                     $(".gst_table tbody").html(html);
                     $("#gst_div").show();
                  }
               }else{
                  alert(obj.message);
               }
            }else{
               alert("Something Went Wrong.Please Try Again.");
            }
         }
      });
   }
</script>
@endsection