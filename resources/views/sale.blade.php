@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
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
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">Sales Voucher</h5>
                <form  action="{{ route('sale.index') }}" method="GET">
                  @csrf
                  <div class="d-md-flex d-block">                  
                     <div class="calender-administrator my-2 my-md-0">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                     </div>
                     <div class="calender-administrator ms-md-4">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                     </div>
                     <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                     <button type="button" class="btn btn-info ms-2 export_csv">CSV</button>
                     <button type="button" class="btn btn-secondary ms-2 print_btn">PRINT</button>
                  </div>
               </form>
               <div class="d-md-flex d-block"> 
                  <input type="text" id="search" class="form-control" placeholder="Search">
               </div>
               @can('action-module',85)
                  <a href="{{ route('sale.create') }}" class="btn btn-xs-primary">
                  ADD
                  <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" /></svg>
               </a>
               @endcan
               
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table-bordered table m-0 shadow-sm sale_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="">Date </th>
                        <th class="">Vch/Bill No </th>
                        <th class="">Party Name</th>
                        <th style="text-align: right;">Amount </th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $tot_amt = 0;
                     $qty = 0;setlocale(LC_MONETARY, 'en_IN');
                     foreach ($sale as $value) { ?> 
                        <tr class="font-14 font-heading bg-white">
                           <td class=""><?php echo date('d-m-Y',strtotime($value->date)); ?></td>
                           <td style="text-align: center;"><?php echo $value->voucher_no_prefix ?></td>
                           <td class=""><?php echo $value->account_name ?></td>
                           <td style="text-align: right;">
                              <?php 

                              
                              if($value->total!="" && $value->total!=0 && $value->status!='2'){
                                  echo $value->total;
                                 $tot_amt = $tot_amt + floatval($value->total);

                              }else if($value->status=='2'){
                                  echo 0.00;
                              }
                              
                              ?>
                           </td>
                           <td class="w-min-120 text-center">
                              
                              <?php
                              if(in_array(date('Y-m',strtotime($value->date)),$month_arr)){                                 
                                    if($value->e_invoice_status==0 && $value->e_waybill_status==0 && $value->status=='1'){?>
                                       @can('action-module',61)
                                          @if($value->sale_order_id=="")
                                          <a href="{{ URL::to('edit-sale/'.$value->sales_id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                          @endif
                                       @endcan
                                       @can('action-module',62)

                                          @if(($value->max_voucher_no==$value->voucher_no && $value->manual_numbering_status=="NO") || ($value->manual_numbering_status=="YES" || $value->manual_numbering_status==""))
                                             <button type="button" class="border-0 bg-transparent delete"   data-id="<?php echo $value->sales_id;?>">
                                                <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                             </button>
                                          @endif
                                       @endcan
                                       @can('action-module',236)
                                       <button type="button" class="btn btn-link p-0 cancel-einvoice text-danger fw-bold" 
                                                   data-id="{{ $value->sales_id }}" title="Cancel Invoice"
                                                   style="font-size:20px; line-height:1; vertical-align:middle;">
                                             &times;
                                          </button>
                                          @endcan
                                       <?php 
                                    }
                              } 
                              if($value->status=='1'){?>
                                 <a title="View Invoice" href="{{ url('sale-invoice/' . $value->sales_id) }}?source=sale" target="_blank"><img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" class="px-1" alt="View Invoice"></a>
                                 <?php 
                              }
                              if($value->status=='2'){?>
                                 <h4 style="color:red">CANCELLED</h4>
                                 <?php 
                              }
                              ?>
                           </td>
                        </tr>
                        <tr class="font-12 text-muted bg-light">
                           <td colspan="5" class="ps-4 py-1" style="text-align:left;">
                              
                              <strong>Created By:</strong> 
                              {{ $value->created_by_name ?? '-' }}

                              &nbsp;&nbsp;|&nbsp;&nbsp;

                              <strong>Approved By:</strong> 
                              @if($value->approved_status == 1)
                                 {{ $value->approved_by_name ?? '-' }}
                                 <small>({{ date('d-m-Y H:i', strtotime($value->approved_at)) }})</small>
                              @else
                                 -
                              @endif

                           </td>
                        </tr>
                        <?php 
                     } ?>
                     <tr class="font-14 font-heading bg-white">
                        <td class="w-min-120 fw-bold font-heading">TOTAL</td>
                        <td class="w-min-120"></td>
                        <td class="w-min-120 "></td>
                        <td class="w-min-120 fw-bold font-heading" style="text-align: right;">
                           <?php 
                           echo $tot_amt;?>
                        </td>
                        <td class="w-min-120 text-center"></td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
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
   </section>
</div>
<!-- Modal ---for delete ---------------------------------------------------------------icon-->
<div class="modal fade" id="delete_sale" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="{{ route('sale.delete') }}">
            @csrf
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone. </p>
            </div>
            <input type="hidden" value="" id="sale_id" name="sale_id" />
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
<div class="modal fade" id="cancel_sale_modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered w-360">
    <div class="modal-content p-4 border-divider border-radius-8">
      <div class="modal-header border-0 p-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <span class="text-danger fw-bold mb-3 d-block mx-auto" 
              style="font-size:50px; line-height:1;">&times;</span>
        <h5 class="mb-3 fw-normal">Cancel this Invoice?</h5>
        <p class="font-14 text-body">This process cannot be undone. Are you sure?</p>
      </div>
      <input type="hidden" id="cancel_sale_id">
      <div class="modal-footer border-0 mx-auto p-0">
        <button type="button" class="btn btn-border-body cancel" data-bs-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-red" id="confirm_cancel">CONFIRM</button>
      </div>
    </div>
  </div>
</div>
</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
         $(document).on('click', '.cancel-einvoice', function() {
            var sale_id = $(this).data('id');
            $("#cancel_sale_id").val(sale_id);
            $("#cancel_sale_modal").modal('show');
         });
         $("#confirm_cancel").click(function() {
        var sale_id = $("#cancel_sale_id").val();

        $.ajax({
            url: "{{ route('sale.cancel') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: sale_id
            },
            success: function(response) {
                $("#cancel_sale_modal").modal('hide');
                if(response.success){
                    alert(response.message);
                    location.reload(); 
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr, status, error){
                $("#cancel_sale_modal").modal('hide');
                let msg = "Something went wrong!";
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                } else if(xhr.responseText){
                    msg = xhr.responseText; 
                }

                alert("Error: " + msg);
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    });
      $(".cancel").click(function() {
         $("#delete_sale").modal("hide");
      });
      $("#pan").change(function() {
         var inputvalues = $("#pan").val();
         var paninformat = new RegExp("^[A-Z]{5}[0-9]{4}[A-Z]{1}$");
         if(paninformat.test(inputvalues)) {
            return true;
         }else {
            alert('Please Enter Valid PAN Number');
            $("#pan").val('');
            $("#pan").focus();
         }
      });
      setTimeout(function() {
         if($("#business_type").val() == 1) {
            $("#dateofjoing_section").hide();
            $("#din_sectioon").hide();
            $("#share_per_div").show();
            var html = '<option value="proprietor">Proprietor</option>';
            $("#designation").html('<option value="proprietor">Proprietor</option><option value="authorised_signatory">Authorised Signatory</option>');
         }else if ($("#business_type").val() == 2) {
            $("#dateofjoing_section").show();
            $("#din_sectioon").hide();
            $("#share_per_div").show();
            $("#designation").html('<option value="partner">Partner</option><option value="authorised_signatory">Authorised Signatory</option>');
         }else {
            $("#dateofjoing_section").show();
            $("#din_sectioon").show();
            $("#share_per_div").hide();
            $("#designation").html('<option value="director">Director</option><option value="authorised_signatory">Authorised Signatory</option>');
         }
      }, 1000);
   });
   $(document).on('click','.delete',function(){
      var id = $(this).attr("data-id");
      $("#sale_id").val(id);
      $("#delete_sale").modal("show");
   });
   $("#search").keyup(function () {
      var value = this.value.toLowerCase().trim();
      $(".sale_table tr").each(function (index) {
         if (!index) return;
         $(this).find("td").each(function () {
            var id = $(this).text().toLowerCase().trim();
            var not_found = (id.indexOf(value) == -1);
            $(this).closest('tr').toggle(!not_found);
            return not_found;
         });
      });
   });
   $(".export_csv").click(function () {

      let csv = [];

      let from_date = $("input[name='from_date']").val();
      let to_date   = $("input[name='to_date']").val();

      // format (optional: dd-mm-yyyy)
      function formatDate(dateStr){
         if(!dateStr) return '';
         let parts = dateStr.split("-");
         return parts[2] + "-" + parts[1] + "-" + parts[0];
      }

      from_date = formatDate(from_date);
      to_date   = formatDate(to_date);

      csv.push("From Date: " + from_date);
      csv.push("To Date: " + to_date);
      csv.push(""); // empty line

      // HEADER (REMOVE ACTION, ADD STATUS)
      let header = [];
      $(".sale_table thead th").each(function (index) {
         if(index != 4){
               header.push($(this).text().trim());
         }
      });
      header.push("Status");
      csv.push(header.join(","));

      $(".sale_table tbody tr").each(function () {

         if($(this).hasClass("bg-light")){
               return;
         }

         let row = [];
         let isCancelled = false;

         $(this).find("td").each(function (index) {

               if(index == 4){
                  let actionText = $(this).text().toLowerCase();
                  if(actionText.includes("cancelled")){
                     isCancelled = true;
                  }
                  return;
               }

               let text = $(this).text().trim()
                  .replace(/\n/g, '')
                  .replace(/,/g, '');

               row.push(text);
         });

         row.push(isCancelled ? "Cancelled" : "");

         if(row.length > 1){
               csv.push(row.join(","));
         }
      });

      let csvString = csv.join("\n");

      let blob = new Blob([csvString], { type: "text/csv" });
      let url = window.URL.createObjectURL(blob);

      let a = document.createElement("a");
      a.setAttribute("hidden", "");
      a.setAttribute("href", url);
      a.setAttribute("download", "sales_report.csv");

      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
   });

   $(".print_btn").click(function () {

      let from_date = $("input[name='from_date']").val();
      let to_date   = $("input[name='to_date']").val();

      function formatDate(dateStr){
         if(!dateStr) return '';
         let parts = dateStr.split("-");
         return parts[2] + "-" + parts[1] + "-" + parts[0];
      }

      from_date = formatDate(from_date);
      to_date   = formatDate(to_date);

      let printWindow = window.open('', '', 'width=900,height=700');

      let tableHTML = `
         <html>
         <head>
               <title>Sales Report</title>
               <style>
                  body { font-family: Arial; font-size: 12px; }
                  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                  th, td { border: 1px solid #000; padding: 5px; text-align: left; }
                  th { background: #f2f2f2; }
                  .text-right { text-align: right; }
                  .center { text-align: center; }
               </style>
         </head>
         <body>
            <h2 style="text-align:center; margin:10px 0 5px 0; text-decoration: underline;">
               List of Sales Voucher
            </h2>

               <p style="text-align:center;">
                  From: ${from_date} &nbsp;&nbsp; To: ${to_date}
               </p>

               <table>
                  <thead>
                     <tr>
                           <th>Date</th>
                           <th>Vch/Bill No</th>
                           <th>Party Name</th>
                           <th class="text-right">Amount</th>
                           <th>Status</th>
                     </tr>
                  </thead>
                  <tbody>
      `;

      $(".sale_table tbody tr").each(function () {

         if($(this).hasClass("bg-light")){
               return;
         }

         let tds = $(this).find("td");

         let date = $(tds[0]).text().trim();
         let vch  = $(tds[1]).text().trim();
         let party= $(tds[2]).text().trim();
         let amt  = $(tds[3]).text().trim();

         let status = "";

         let actionText = $(tds[4]).text().toLowerCase();
         if(actionText.includes("cancelled")){
               status = "Cancelled";
         }

         tableHTML += `
               <tr>
                  <td>${date}</td>
                  <td>${vch}</td>
                  <td>${party}</td>
                  <td class="text-right">${amt}</td>
                  <td>${status}</td>
               </tr>
         `;
      });

      tableHTML += `
                  </tbody>
               </table>

         </body>
         </html>
      `;

      printWindow.document.write(tableHTML);
      printWindow.document.close();
      printWindow.print();
   });
</script>
@endsection