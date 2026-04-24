@extends('layouts.app')
@section('content')
@include('layouts.header')
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
            
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-3 flex-wrap">
               <h5 class="transaction-table-title m-0 me-3">
                  Sale Return/Credit Note
               </h5>
               <form action="{{ route('sale-return.index') }}" method="GET"
                     class="d-flex align-items-center flex-wrap gap-2 m-0">
                  @csrf
                  <input type="date" name="from_date"
                        class="form-control form-control-sm"
                        style="width:150px;"
                        value="{{ request('from_date') }}">
                  <input type="date" name="to_date"
                        class="form-control form-control-sm"
                        style="width:150px;"
                        value="{{ request('to_date') }}">
                     <button class="btn btn-info btn-sm">Search</button>
                  <select name="sr_nature" class="form-select form-select-sm"
                        style="width:140px;">
                        <option value="WITH GST" {{ request('sr_nature')=='WITH GST' ? 'selected' : '' }}>With GST</option>
                        <option value="WITHOUT GST" {{ request('sr_nature')=='WITHOUT GST' ? 'selected' : '' }}>Without GST</option>
                  </select>
               </form>
               <div class="d-flex align-items-center gap-2">
                  <input type="text" id="search"
                        class="form-control form-control-sm"
                        placeholder="Search"
                        style="width:100px;">
                        <button class="btn btn-info btn-sm export_csv">CSV</button>
                        <button class="btn btn-secondary btn-sm print_btn">PRINT</button>
                  @can('action-module',76)
                  <a href="{{ route('sale-return.create') }}"
                     class="btn btn-xs-primary btn-sm d-flex align-items-center">
                        ADD
                        <svg class="ms-1" xmlns="http://www.w3.org/2000/svg"
                           width="16" height="16" fill="none">
                           <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white"/>
                        </svg>
                  </a>
                  @endcan
               </div>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-striped table-bordered table m-0 shadow-sm sale_return_table" id="sale_return_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Sale Return No </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Party Name</th>
                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:center">Amount </th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     $tot_amt = 0;
                     $qty = 0;
                     setlocale(LC_MONETARY, 'en_IN');
                     foreach ($sale as $value) { 
                        //print_r($value);
                     ?>
                        <tr class="font-14 font-heading bg-white">
                           <td class="w-min-120 ">
                              <?php echo date('d-m-Y',strtotime($value->date)); ?>
                           </td>
                           <td class="w-min-120 " style="text-align: center;">
                              <?php echo $value->sr_prefix; ?>
                           </td>
                           <td class="w-min-120 "><?php echo $value->account_name ?></td>
                           
                           <td class="w-min-120 " style="text-align: right;">
                              <?php 
                              if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                 echo $value->total;
                              }else{
                                 echo $value->total;
                              }
                              $tot_amt = $tot_amt + $value->total; 
                              ?>
                           </td>
                           <td class="w-min-120  text-center">
                              <?php 
                              if(in_array(date('Y-m',strtotime($value->date)),$month_arr) && $value->approved_status != 1 && $value->status=='1'){?>
                                 
                                 @can('action-module',69)
                                    @if($value->e_invoice_status==0)
                                       <a href="{{ URL::to('sale-return-edit/'.$value->sales_returns_id) }}">
                                          <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="">
                                       </a>
                                    @endif
                                 @endcan

                                 @can('action-module',70)
                                    @if(($value->max_voucher_no==$value->sale_return_no && $value->manual_numbering_status=="NO") || ($value->manual_numbering_status=="YES" || $value->manual_numbering_status==""))
                                       @if($value->e_invoice_status==0)
                                          <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->sales_returns_id;?>">
                                             <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                          </button>
                                       @endif
                                    @endif
                                 @endcan
                                 @can('action-module',236)
                                 @if($value->e_invoice_status==0 && $value->status=='1')
                                    <button type="button" 
                                       class="btn btn-link p-0 cancel-sale-return text-danger fw-bold" 
                                       data-id="{{ $value->sales_returns_id }}" 
                                       title="Cancel Sale Return"
                                       style="font-size:20px; line-height:1; vertical-align:middle;">
                                       &times;
                                    </button>
                                 @endif
                                 @endcan
                              <?php } ?>
                                 @if($value->status=='2')
                                    <h4 style="color:red">CANCELLED</h4>
                                 @endif
                                 @if($value->status=='1')
                              @if($value->sr_nature=="WITH GST" && ($value->sr_type=="WITH ITEM" || $value->sr_type=="RATE DIFFERENCE"))
                                 <a href="{{ URL::to('sale-return-invoice/' . $value->sales_returns_id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
                              @elseif($value->sr_nature=="WITH GST" && $value->sr_type=="WITHOUT ITEM")
                                 <a href="{{ URL::to('sale-return-without-item-invoice/' . $value->sales_returns_id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
                              @elseif($value->sr_nature=="WITHOUT GST")
                                 <a href="{{ URL::to('sale-return-without-gst-invoice/' . $value->sales_returns_id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
                              @endif
                              @endif
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
                        <td></td>
                        <td></td>
                        
                        <td class="w-min-120 fw-bold font-heading" style="text-align: right;">
                           <?php echo $tot_amt; ?></td>
                        <td class="w-min-120 "></td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
         <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            <div class="shortcut-key ">
               <p class="font-14 fw-500 font-heading m-0">Shortcut Keys</p>
               <button class="p-2 transaction-shortcut-btn my-2 ">F1
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
<div class="modal fade" id="delete_sale_return" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="{{ route('sale-return.delete') }}">
            @csrf
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone. </p>
            </div>
            <input type="hidden" value="" id="sale_return_id" name="sale_return_id" />
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>
</body>
<div class="modal fade" id="cancel_sale_return_modal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered w-360">
    <div class="modal-content p-4 border-divider border-radius-8">
      <div class="modal-header border-0 p-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">
        <span class="text-danger fw-bold mb-3 d-block mx-auto" style="font-size:50px;">&times;</span>
        <h5 class="mb-3 fw-normal">Cancel this Sale Return?</h5>
        <p class="font-14 text-body">This process cannot be undone. Are you sure?</p>
      </div>

      <input type="hidden" id="cancel_sale_return_id">

      <div class="modal-footer border-0 mx-auto p-0">
        <button type="button" class="btn btn-border-body" data-bs-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-red" id="confirm_cancel_sale_return">CONFIRM</button>
      </div>
    </div>
  </div>
</div>
@include('layouts.footer')
<script>
   $(document).ready(function() {      
      $(".cancel").click(function() {
         $("#delete_sale_return").modal("hide");
      });
      $("#pan").change(function(){
         var inputvalues = $("#pan").val();
         var paninformat = new RegExp("^[A-Z]{5}[0-9]{4}[A-Z]{1}$");
         if(paninformat.test(inputvalues)) {
            return true;
         }else{
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
         }else if($("#business_type").val() == 2) {
            $("#dateofjoing_section").show();
            $("#din_sectioon").hide();
            $("#share_per_div").show();
            $("#designation").html('<option value="partner">Partner</option><option value="authorised_signatory">Authorised Signatory</option>');
         }else{
            $("#dateofjoing_section").show();
            $("#din_sectioon").show();
            $("#share_per_div").hide();
            $("#designation").html('<option value="director">Director</option><option value="authorised_signatory">Authorised Signatory</option>');
         }
      }, 1000);
   });
   $(document).on('click','.delete',function(){
      var id = $(this).attr("data-id");
      $("#sale_return_id").val(id);
      $("#delete_sale_return").modal("show");
   });
   $("#search").keyup(function () {
      var value = this.value.toLowerCase().trim();
      $(".sale_return_table tr").each(function (index) {
         if (!index) return;
         $(this).find("td").each(function () {
            var id = $(this).text().toLowerCase().trim();
            var not_found = (id.indexOf(value) == -1);
            $(this).closest('tr').toggle(!not_found);
            return not_found;
         });
      });
   });

   // Open modal
    $(document).on('click', '.cancel-sale-return', function() {
        var id = $(this).data('id');
        $("#cancel_sale_return_id").val(id);
        $("#cancel_sale_return_modal").modal('show');
    });
    
    // Confirm cancel
    $("#confirm_cancel_sale_return").click(function() {
    
        var id = $("#cancel_sale_return_id").val();
    
        $.ajax({
            url: "{{ url('cancel-sale-return') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(response) {
                $("#cancel_sale_return_modal").modal('hide');
    
                if(response.success){
                    alert(response.message);
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(xhr){
                $("#cancel_sale_return_modal").modal('hide');
                alert("Something went wrong");
            }
        });
    });
    $('select[name="sr_nature"]').change(function(){
        $(this).closest('form').submit();
    });
    $(".export_csv").click(function () {

      let csv = [];

      let from_date = $("input[name='from_date']").val();
      let to_date   = $("input[name='to_date']").val();
      let nature    = $("select[name='sr_nature']").val();

      function formatDate(dateStr){
         if(!dateStr) return '';
         let parts = dateStr.split("-");
         return parts[2] + "-" + parts[1] + "-" + parts[0];
      }

      csv.push("From Date: " + formatDate(from_date));
      csv.push("To Date: " + formatDate(to_date));
      csv.push("Nature: " + (nature || ""));
      csv.push("");

      let header = [];
      $("#sale_return_table thead th").each(function (index) {
         if(index != 4){
               header.push($(this).text().trim());
         }
      });
      header.push("Status");
      csv.push(header.join(","));

      $("#sale_return_table tbody tr").each(function () {

         if($(this).hasClass("bg-light")){
               return;
         }

         let row = [];
         let isCancelled = false;

         $(this).find("td").each(function (index) {

               if(index == 4){
                  let txt = $(this).text().toLowerCase();
                  if(txt.includes("cancelled")){
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

      let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
      let url = window.URL.createObjectURL(blob);

      let a = document.createElement("a");
      a.href = url;
      a.download = "credit_note_report.csv";
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
   });

   $(".print_btn").click(function () {

      let from_date = $("input[name='from_date']").val();
      let to_date   = $("input[name='to_date']").val();
      let nature    = $("select[name='sr_nature']").val();

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
               <title>Sales Return Report</title>
               <style>
                  body { font-family: Arial; font-size: 12px; }
                  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                  th, td { border: 1px solid #000; padding: 5px; }
                  th { background: #f2f2f2; }
                  .text-right { text-align: right; }
               </style>
         </head>
         <body>

         <h2 style="text-align:center; text-decoration: underline;">
               List of Sales Return Voucher
         </h2>

         <p style="text-align:center;">
               From: ${from_date} &nbsp;&nbsp; To: ${to_date}
         </p>

         <p style="text-align:center;">
               Nature: ${nature || ""}
         </p>

         <table>
               <thead>
                  <tr>
                     <th>Date</th>
                     <th>Sale Return No</th>
                     <th>Party Name</th>
                     <th class="text-right">Amount</th>
                     <th>Status</th>
                  </tr>
               </thead>
               <tbody>
      `;

      let total = 0;

      $("#sale_return_table tbody tr").each(function () {

         if($(this).hasClass("bg-light")){
               return;
         }

         let tds = $(this).find("td");

         if($(tds[0]).text().trim().toLowerCase() === "total"){
               total = $(tds[3]).text().trim();
               return;
         }

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
         <tr>
               <td colspan="3" style="font-weight:bold;">TOTAL</td>
               <td class="text-right" style="font-weight:bold;">${total}</td>
               <td></td>
         </tr>
      `;

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