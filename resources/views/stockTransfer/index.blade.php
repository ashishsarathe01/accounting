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
               <div class="alert alert-danger" role="alert"> {{session('error')}} </div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2 ">
                  List of Stock Transfer
               </h5>
                <form  action="{{ route('stock-transfer.index') }}" method="GET">
                  @csrf
                  <div class="d-md-flex d-block">                  
                     <div class="calender-administrator my-2 my-md-0">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                     </div>
                     <div class="calender-administrator ms-md-4">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                     </div>
                     <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                     <button class="btn btn-info ms-2 export_csv">CSV</button>
                     <button class="btn btn-secondary ms-2 print_btn">PRINT</button>
                  </div>
               </form>
               @can('action-module',87)
                  <a href="{{ route('stock-transfer.create') }}" class="btn btn-xs-primary">ADD
                     <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                     </svg>
                  </a>
               @endcan
               
            </div>
            <div class="transaction-table bg-white table-view shadow-sm">
               <table id="stock_transfer_table" class="table-striped table m-0 shadow-sm">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="w-min-120 border-none bg-light-pink text-body">Date</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Voucher No. </th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">From</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">To</th>
                        <th class="w-min-120 border-none bg-light-pink text-body ">Amount</th>
                        <th class="w-min-120 border-none bg-light-pink text-body text-center"> Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach ($stock_transfers as $stockTransfer)   
                     <tr class="font-12 text-body">
                        <td>
                           {{date('d-m-Y',strtotime($stockTransfer->transfer_date))}}

                           <div style="font-size:11px; color:#6c757d; margin-top:4px;">
                                 <strong>Created By:</strong> {{ $stockTransfer->created_by_name ?? '-' }}
                                 <br>
                                 <strong>Approved By:</strong> 
                                 @if($stockTransfer->approved_status == 1)
                                    {{ $stockTransfer->approved_by_name ?? '-' }}
                                    <small>({{ date('d-m-Y H:i', strtotime($stockTransfer->approved_at)) }})</small>
                                 @else
                                    -
                                 @endif
                           </div>
                        </td>

                        <td>{{$stockTransfer->voucher_no_prefix}}</td>

                        <td>{{$stockTransfer->material_center_from}}</td>

                        <td>{{$stockTransfer->material_center_to}}</td>

                        <td>{{$stockTransfer->grand_total}}</td>

                        <td>
                           @if($stockTransfer->e_waybill_status==0 && $stockTransfer->approved_status != 1)
                                 @can('action-module',65)
                                    <a href="{{ URL::to('stock-transfer/'.$stockTransfer->id.'/edit') }}">
                                       <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="">
                                    </a>
                                 @endcan
                                 @can('action-module',66)
                                    <button type="button" class="border-0 bg-transparent delete_entry" data-id="<?php echo $stockTransfer->id;?>">
                                       <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                    </button>
                                 @endcan
                           @endif

                           <a href="{{ URL::to('stock-transfer',$stockTransfer->id) }}" target="__blank">
                                 <img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt="">
                           </a>
                        </td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="delete_stock_transfer" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form method="POST" action="{{ route('stock-transfer.destroy',1) }}" id="delete_stock_transfer_form">
            @csrf
            @method('DELETE')
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone. </p>
            </div>
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>
         </form>
      </div>
   </div>
</div>                       
</body>
@include('layouts.footer')
<script>
   $(document).ready(function() {
      $(".cancel").click(function() {
         $("#delete_stock_transfer").modal("hide");
      });        
   });
   $(document).on('click','.delete_entry',function(){
      var id = $(this).attr("data-id");
      let url = '{{ route('stock-transfer.destroy', ':id') }}';
      url = url.replace(':id', id);
      
      $('#delete_stock_transfer_form').attr('action',url);      
      $("#delete_stock_transfer").modal("show");
   });

   $(".export_csv").click(function () {

      let csv = [];

      let from_date = $("input[name='from_date']").val();
      let to_date   = $("input[name='to_date']").val();

      function formatDate(dateStr){
         if(!dateStr) return '';
         let parts = dateStr.split("-");
         return parts[2] + "-" + parts[1] + "-" + parts[0];
      }

      csv.push("From Date: " + formatDate(from_date));
      csv.push("To Date: " + formatDate(to_date));
      csv.push("");

      let header = [];
      $("#stock_transfer_table thead th").each(function (index) {
         if(index != 5){
               header.push($(this).text().trim());
         }
      });
      csv.push(header.join(","));

      $("#stock_transfer_table tbody tr").each(function () {

         let row = [];

         $(this).find("td").each(function (index) {

               if(index == 5) return;

               let text = "";

               if(index == 0){
                  text = $(this).clone()   
                     .children("div")     
                     .remove()
                     .end()
                     .text()
                     .trim();
               } else {
                  text = $(this).text().trim();
               }

               text = text.replace(/\n/g, '').replace(/,/g, '');

               row.push(text);
         });

         if(row.length > 1){
               csv.push(row.join(","));
         }
      });

      let csvString = csv.join("\n");

      let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
      let url = window.URL.createObjectURL(blob);

      let a = document.createElement("a");
      a.href = url;
      a.download = "stock_transfer_report.csv";
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
   });

   $(".print_btn").click(function () {

      let rows = [];

      let from_date = $("input[name='from_date']").val();
      let to_date   = $("input[name='to_date']").val();

      function formatDate(dateStr){
         if(!dateStr) return '';
         let parts = dateStr.split("-");
         return parts[2] + "-" + parts[1] + "-" + parts[0];
      }

      let from = formatDate(from_date);
      let to   = formatDate(to_date);

      let header = [];
      $("#stock_transfer_table thead th").each(function (index) {
         if(index != 5){
               header.push($(this).text().trim());
         }
      });
      rows.push(header);

      $("#stock_transfer_table tbody tr").each(function () {

         let row = [];

         $(this).find("td").each(function (index) {

               if(index == 5) return;

               let text = "";

               if(index == 0){
                  text = $(this).clone()
                     .children("div")
                     .remove()
                     .end()
                     .text()
                     .trim();
               } else {
                  text = $(this).text().trim();
               }

               row.push(text);
         });

         if(row.length > 1){
               rows.push(row);
         }
      });

      let printWindow = window.open('', '', 'width=900,height=700');

      let html = `
         <html>
         <head>
               <title>Stock Transfer</title>
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
               List of Stock Transfer
         </h2>

         <p style="text-align:center;">
               From: ${from} &nbsp;&nbsp; To: ${to}
         </p>

         <table>
               <thead>
                  <tr>
      `;

      header.forEach(col => {
         html += `<th>${col}</th>`;
      });

      html += `</tr></thead><tbody>`;

      for(let i = 1; i < rows.length; i++){

         let row = rows[i];

         html += `<tr>`;

         row.forEach((cell, index) => {

               let align = (index == 4) ? 'text-right' : '';

               html += `<td class="${align}">${cell}</td>`;
         });

         html += `</tr>`;
      }

      html += `
               </tbody>
         </table>
         </body>
         </html>
      `;

      printWindow.document.write(html);
      printWindow.document.close();
      printWindow.print();
   });
</script>
@endsection