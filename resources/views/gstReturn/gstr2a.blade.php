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
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">GSTR2A</h5>
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
               <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm gst_head">
                  GSTR2A
                  <button class="btn btn-info reconciliation" style="float:right;">Reconciliation</button>
               </h5>
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
   var refresh = 0;
   $(document).ready(function(){
      $(".submit_btn").click(function(){
         refresh = 0;
         let month = $("#month").val();
         let gstin = $("#gstin").val();
         getGstData(month,gstin);
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
         $("#cover-spin").show();
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
                     getGstData(month,gstin)
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
      });
   });
   function getGstData(month,gstin){
      $("#cover-spin").show(); 
      $.ajax({
         url : "{{route('gstr2a-detail')}}",
         method : 'post',
         data : {
            _token : '{{ csrf_token() }}',
            month : month,
            gstin : gstin,
            refresh : refresh
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
                     //alert("OTP Verified Successfully");
                     if(refresh==1){
                        refresh = 0;
                     }
                     getGstData(month,gstin);
                  }else if(obj.message=="GSTR2A"){
                     $(".gst_head").html(`
                        GSTR2A - Last Created Date : ${obj.last_created_date}
                        <button type="button" class="btn btn-xs-primary new_data_btn">Refresh</button>
                        <button type="button" class="btn btn-info reconciliation" style="margin-left:10px;">Reconciliation</button>
                     `);
                     let html = "";
                     let total_b2b_portal = 0;
                     let total_b2b_books = 0;

                     let total_cdnr_portal = 0;
                     let total_cdnr_books = 0;

                     let total_diff = 0;
                    for (let key in obj.data) {
                        let row = obj.data[key];
                        let baseUrl = "{{ url('/gstr2a-all-info') }}";
                        let fullUrl = `${baseUrl}/${month}/${gstin}/${key}`;
                        html += `
                            <tr>
                                <td>
                                    <a href="${fullUrl}" style="color:#000 !important;">
                                       ${row.name} (${key})
                                    </a>
                                 </td>
                                 <td style="text-align:right; color:${row.all_matched ? 'green' : 'red'}">
                                    ${Number(row.b2b_portal).toLocaleString('en-IN',{minimumFractionDigits:2})}
                                 </td>
                                 <td style="text-align:right; color:${row.all_matched ? 'green' : 'red'}">
                                    ${Number(row.b2b_books).toLocaleString('en-IN',{minimumFractionDigits:2})}
                                 </td>

                                 <td style="text-align:right; color:${parseFloat(row.cdnr_portal) === parseFloat(row.cdnr_books) ? 'green' : 'red'};">
                                    ${Number(row.cdnr_portal).toLocaleString('en-IN',{minimumFractionDigits:2})}
                                 </td>
                                 <td style="text-align:right; color:${parseFloat(row.cdnr_portal) === parseFloat(row.cdnr_books) ? 'green' : 'red'};">
                                    ${Number(row.cdnr_books).toLocaleString('en-IN',{minimumFractionDigits:2})}
                                 </td>
                                <td style="text-align:right; color:${parseFloat(row.diff_amt) != 0 ? 'red' : 'black'}">
                                    ${Number(row.diff_amt).toLocaleString('en-IN',{minimumFractionDigits:2})}
                                </td>
                            </tr>
                        `;
                    
                        total_b2b_portal += parseFloat(row.b2b_portal);
                        total_b2b_books  += parseFloat(row.b2b_books);

                        total_cdnr_portal += parseFloat(row.cdnr_portal);
                        total_cdnr_books  += parseFloat(row.cdnr_books);
                        total_diff   += parseFloat(row.diff_amt);
                    }

                    html += `
                     <tr>
                        <th>Total</th>

                        <th style="text-align:right">${Number(total_b2b_portal).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                        <th style="text-align:right">${Number(total_b2b_books).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>

                        <th style="text-align:right">${Number(total_cdnr_portal).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                        <th style="text-align:right">${Number(total_cdnr_books).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>

                        <th style="text-align:right">${Number(total_diff).toLocaleString('en-IN',{minimumFractionDigits:2})}</th>
                     </tr>
                     `;

                     let finalHtml = html;

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

                                    </div>

                                 </div>
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
                           </td>
                        </tr>`;
                     }

                     $(".gst_table tbody").html(finalHtml);
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
   $(document).on('click','.new_data_btn',function(){
      if(confirm("Do you want to refresh data?")==true){
         let month = $("#month").val();
         let gstin = $("#gstin").val();
         refresh = 1;
         getGstData(month,gstin);
      }
   });
   $(document).on('click', '.reconciliation', function () {

      let month = $("#month").val();
      let gstin = $("#gstin").val();

      let url = "{{url('gstr2a-reconciliation-data')}}/month/gstin";
      url = url.replace('month', month);
      url = url.replace('gstin', gstin);

      window.location = url;
   });

   $(document).on('click', '.pending_print_btn', function () {

      let month = $("#month").val();
      let gstin = $("#gstin").val();

      let printWindow = window.open('', '', 'width=1200,height=700');

      let tableHTML = `
         <html>
         <head>

               <title>Pending Credit / Debit Notes</title>

               <style>

                  body{
                     font-family: Arial;
                     font-size: 12px;
                     padding:20px;
                  }

                  h2{
                     text-align:center;
                     margin-bottom:10px;
                  }

                  .info{
                     text-align:center;
                     margin-bottom:20px;
                     font-size:14px;
                  }

                  table{
                     width:100%;
                     border-collapse:collapse;
                  }

                  table th,
                  table td{
                     border:1px solid #000;
                     padding:6px;
                     font-size:12px;
                  }

                  table th{
                     background:#f2f2f2;
                  }

               </style>

         </head>

         <body>

               <h2>Pending Credit / Debit Notes (Unlinked)</h2>

               <div class="info">

                  <strong>Month:</strong>
                  ${month}

                  &nbsp;&nbsp;&nbsp;

                  <strong>GSTIN:</strong>
                  ${gstin}

               </div>

               ${$('.pending_notes_table').prop('outerHTML')}

         </body>

         </html>
      `;

      printWindow.document.write(tableHTML);

      printWindow.document.close();

      printWindow.focus();

      printWindow.print();

   });

   $(document).on('click', '.pending_excel_btn', function () {

      let month = $("#month").val();
      let gstin = $("#gstin").val();

      let table = `
         <table border="1">

               <tr>
                  <th colspan="11" style="font-size:18px;">
                     GSTR2A
                  </th>
               </tr>

               <tr>
                  <td colspan="11">
                     <strong>Month:</strong> ${month}
                     &nbsp;&nbsp;&nbsp;
                     <strong>GSTIN:</strong> ${gstin}
                  </td>
               </tr>

               <tr>
                  <th colspan="11" style="font-size:16px;">
                     Pending Credit / Debit Notes (Unlinked)
                  </th>
               </tr>

               ${$('.pending_notes_table').html()}

         </table>
      `;

      let blob = new Blob(
         ['\ufeff' + table],
         { type: 'application/vnd.ms-excel' }
      );

      let url = window.URL.createObjectURL(blob);

      let a = document.createElement("a");

      a.href = url;

      a.download = "pending_credit_debit_notes.xls";

      document.body.appendChild(a);

      a.click();

      document.body.removeChild(a);

   });

</script>
@endsection