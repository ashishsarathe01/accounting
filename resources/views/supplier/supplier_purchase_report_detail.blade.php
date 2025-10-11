@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="transaction-table-title m-0 py-2">Purchase Report ({{$account_name}}) @isset($from_date)- ({{date('d-m-Y',strtotime($from_date))}} TO {{date('d-m-Y',strtotime($to_date))}}) @endisset</h5>
               <a href="{{route('manage-supplier-purchase-report')}}"><button class="btn btn-info" style="float:right">Back</button></a>
               <div class="d-md-flex d-block">
               </div>
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table class="table-bordered table m-0 shadow-sm payment_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th ><input type="checkbox" class="all_check"> ALL</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Invoice Date </th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Invoice No.</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Invoice Amount</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Voucher Number</th>
                        <th class="w-min-120 border-none bg-light-pink text-body">Area</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:right;">Difference</th>
                        <th class="w-min-120 border-none bg-light-pink text-body" style="text-align:center;">Detail</th>
                     </tr>
                  </thead>
                  <tbody>
                        @foreach($purchases as $key => $value)
                            <tr>
                                <td><input type="checkbox" class="check_row" data-id="{{$value->id}}" data-amount="{{$value->difference_total_amount}}"></td>
                                <td>{{date('d-m-Y',strtotime($value->date))}}</td>
                                <td>{{$value->invoice_no}}</td>
                                <td style="text-align:right;">{{$value->total}}</td>
                                <td>{{$value->voucher_no}}</td>
                                <td>@if(isset($value->locationInfo)) {{$value->locationInfo->name}} @endif</td>
                                <td style="text-align:right;">{{$value->difference_total_amount}}</td>
                                <td>
                                    @php 
                                    $view_html = "";                                    
                                    $view_html.='<table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <td>Head</td>
                                                <td>Qty</td>
                                                <td>Bill Rate</td>
                                                <td>Contract Rate</td>
                                                <td>Difference Amount</td>
                                            </tr>
                                        </thead>
                                        <tbody>';                                          
                                          foreach($value->purchaseReport as $key => $v){
                                             if($v->head_qty!="" && $v->head_qty!=0){
                                                $view_html.='<tr><td>';
                                                   if(isset($v->headInfo->name)){
                                                         $view_html.=$v->headInfo->name;
                                                   }else{
                                                      $view_html.=$v->head_id;
                                                   }
                                                   $view_html.='</td>';
                                                   $view_html.='<td>'.$v->head_qty.'</td>';
                                                   $view_html.='<td style="text-align:right;">'.$v->head_bill_rate.'</td>';
                                                   $view_html.='<td style="text-align:right;">'.$v->head_contract_rate.'</td>';
                                                   $view_html.='<td style="text-align:right;">'.$v->head_difference_amount.'</td>';
                                                   $view_html.='</tr>';
                                             }
                                          }
                                          $view_html.='<tr><th></th><th></th><th></th><th></th><th style="text-align:right;">'.$value->difference_total_amount.'</th></tr>';
                                        $view_html.='</tbody>
                                    </table>';
                                    @endphp
                                    <button class="btn btn-info view_detail" data-html="{{$view_html}}">View</button>
                                </div>
                            </tr>
                        @endforeach
                        <tr>
                           <td colspan="8" style="text-align: center;">
                              <button class="btn btn-info action" data-action_account_id="{{$id}}">Action</button>
                              <button class="btn btn-secondary print_selected">Print Selected</button>
                           </td>
                        </tr>
                  </tbody>
               </table>
            </div>
         </div>
         <!-- <div class="col-lg-1 d-flex justify-content-center">
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
         </div> -->
      </div>
   </section>
</div>
<div class="modal fade" id="action_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered w-360">
        <div class="modal-content p-4 border-divider border-radius-8 shadow-sm">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h5 class="mb-4 fw-semibold">Choose an Action</h5>
                <div class="d-flex flex-column gap-3 text-start">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="action_type" id="debit_note" value="debit_note">
                        <label class="form-check-label fw-normal" for="debit_note">
                            Create Debit Note
                        </label>
                    </div>
                    {{-- <div class="form-check">
                     <input class="form-check-input" type="radio" name="action_type" id="credit_note" value="credit">
                     <label class="form-check-label fw-normal" for="credit_note">
                        Create Credit Note
                     </label>
                    </div>
                    <div class="form-check">
                     <input class="form-check-input" type="radio" name="action_type" id="cancel_receipt" value="cancel">
                     <label class="form-check-label fw-normal" for="cancel_receipt">
                        Cancel Receipt
                     </label>
                    </div> --}}
                </div>
            </div>
            <input type="hidden" value="" id="action_data" name="action_data" />
            <input type="hidden" value="" id="action_account_id" name="action_account_id" />
            <!-- Footer -->
            <div class="modal-footer border-0 p-0 mt-4 justify-content-center">
               <button type="button" class="btn btn-red px-4 perform_action">Submit</button>
            </div>
      </div>
   </div>
</div>
<div class="modal fade" id="view_detail_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 border-divider border-radius-8 shadow-sm">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <h5 class="mb-4 fw-semibold">Details</h5>
                <div class="d-flex flex-column gap-3 text-start detail_div">
                    
                </div>
            </div>
      </div>
   </div>
</div>

</div>
</body>
@include('layouts.footer')
<script>
   $(".all_check").click(function(){
      $(".check_row").prop('checked',false);
      if($(this).prop('checked')==true){
         $(".check_row").prop('checked',true);
      }
   });
   $(".action").click(function(){
      let row_arr = [];
      $(".check_row").each(function(){
         if($(this).prop('checked')==true){
            row_arr.push({'id':$(this).attr('data-id'),'amount':$(this).attr('data-amount')});
         }
      });
      if(row_arr.length==0){
         alert("Please Select Entry");
         return;
      }
      $("#action_account_id").val($(this).attr('data-action_account_id'));
      $("#action_data").val(JSON.stringify(row_arr));
      $("#action_modal").modal('toggle');
   });
   $(".perform_action").click(function(){
      let action_data = $("#action_data").val();
      let action_account_id = $("#action_account_id").val();
      let selected_action = $('input[name="action_type"]:checked').val();
      if(!selected_action){
         alert("Choose an Action");
         return;
      }
      if(action_data==""){
         alert("Data Required");
         return;
      }
      if(action_account_id==""){
         alert("Account Id Required");
         return;
      }        
      if(selected_action=="debit_note"){
         window.location = "{{url('purchase-return/')}}/create?data="+action_data+"&account_id="+action_account_id
      }
   });
   $(".view_detail").click(function(){
      let html = $(this).attr('data-html');
      $(".detail_div").html(html);
      $("#view_detail_modal").modal('toggle');
   });
   $(".print_selected").click(function () {
      var selectedRows = [];
      let account_name = "{{$account_name}}";
      $(".check_row:checked").each(function () {
         var row = $(this).closest("tr");
         var rowData = {
            date: row.find("td:eq(1)").text().trim(),
            invoice_no: row.find("td:eq(2)").text().trim(),
            invoice_amount: row.find("td:eq(3)").text().trim(),
            voucher_no: row.find("td:eq(4)").text().trim(),
            area: row.find("td:eq(5)").text().trim(),
            difference: row.find("td:eq(6)").text().trim()
         };
         selectedRows.push(rowData);
      });
      if (selectedRows.length === 0) {
         alert("Please select at least one row to print.");
         return;
      }
      // Build print HTML
      var printWindow = window.open("", "_blank");
      var html = `
         <html>
         <head>
               <title>Purchase Report</title>
               <style>
                  @page { size: auto;  margin: 0mm; }
                  body { font-family: Arial, sans-serif; margin: 20px; }
                  table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                  th, td { border: 1px solid #333; padding: 8px; text-align: left; }
                  th { background-color: #f2f2f2; }
                  h2 { text-align: center; }
               </style>
         </head>
         <body>
               <h2>Purchase Report - ${account_name}</h2>
               <table>
                  <thead>
                     <tr>
                           <th>Invoice Date</th>
                           <th>Invoice No.</th>
                           <th style="text-align:right;">Invoice Amount</th>
                           <th>Voucher Number</th>
                           <th>Area</th>
                           <th style="text-align:right;">Difference</th>
                     </tr>
                  </thead>
                  <tbody>`;
      let total = 0;
      selectedRows.forEach(function (row) {
         total = parseFloat(total) + parseFloat(row.difference);
         html += `
               <tr>
                  <td>${row.date}</td>
                  <td>${row.invoice_no}</td>
                  <td style="text-align:right;">${row.invoice_amount}</td>
                  <td>${row.voucher_no}</td>
                  <td>${row.area}</td>
                  <td style="text-align:right;">${row.difference}</td>
               </tr>`;
      });
      html += `<tr>
                  <td></td>
                  <td></td>
                  <td style="text-align:right;"></td>
                  <td></td>
                  <th>Total</th>
                  <th style="text-align:right;">${total}</th>
               </tr>
               </tbody>
            </table>
      </body>
      </html>`;
      printWindow.document.write(html);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
   });
</script>
@endsection