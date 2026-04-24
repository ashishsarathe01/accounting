@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
   /* Force table to fit container */
.stock_journal_table {
    table-layout: fixed;
    width: 100%;
}

/* Compact columns */
.col-date {
    width: 90px;
    white-space: nowrap;
}

.col-unit {
    width: 60px;
    white-space: nowrap;
}

.col-price,
.col-amount {
    width: 90px;
    white-space: nowrap;
    text-align: right;
}

/* Item details can wrap */
.col-item {
    width: 250px;
    /*width: auto;*/
    word-wrap: break-word;
}
/* Highlight first row of each production entry */
.first-row {
    background-color: #fff3cd !important; /* light highlight */
    font-weight: 600;
}

</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
           
            
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2">List of Stock Journal</h5>
               <form class="" id="frm" method="get" action="{{ route('stock-journal') }}">
                  @csrf
                  <div class="d-md-flex d-block">
                     <div class="calender-administrator my-2 my-md-0">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{!empty($from_date) ? date('Y-m-d', strtotime($from_date)) : ''}}">
                     </div>
                     <div class="calender-administrator ms-md-4">
                        <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{!empty($to_date) ? date('Y-m-d', strtotime($to_date)) : ''}}">
                     </div>
                     <div class="calender-administrator ms-md-2">
                        <button type="submit" class="btn btn-info next">Next</button>
                        <button type="button" class="btn btn-info ms-2 export_csv">CSV</button>
                        <button type="button" class="btn btn-secondary ms-2 print_btn">PRINT</button>
                     </div>
                  </div>
               </form>
                    @can('action-module',86)
                       <a href="{{ route('add-stock-journal') }}" class="btn btn-xs-primary">ADD
                           <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                              <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                           </svg>
                        </a>
                    @endcan
               
            </div>
            <div class="bg-white table-view shadow-sm">
               <table class="table-striped table m-0 table-bordered shadow-sm stock_journal_table" id="stock_journal_table">
                  <thead>
                     <tr class=" font-12 text-body bg-light-pink ">
                        <th class="col-date">Date</th>
                        <th class="col-date">Voucher No.</th>
                        <th class="col-item">Item Details</th>
                        <th style="text-align:right;">Qty. Generated</th>
                        <th class="col-unit">Unit</th>
                        <th class="col-price" style="text-align:right;">Price</th>
                        <th class="col-amount" style="text-align:right;">Amount</th>
                        <th style="text-align:right;">Qty. Consumed</th>
                        <th class="col-unit">Unit</th>
                        <th class="col-price" style="text-align:right;">Price</th>
                        <th class="col-amount" style="text-align:right;">Amount</th>
                        <th style="width: 93px;"> Action</th>
                     </tr>
                  </thead>
                  <tbody>
                    @php
                        $currentParent = null;
                    
                        // Per entry totals
                        $genQty = $genAmt = $conQty = $conAmt = 0;
                    
                        // Overall totals
                        $overallGenQty = $overallGenAmt = 0;
                        $overallConQty = $overallConAmt = 0;
                    @endphp
                    
                    @foreach($journals as $journal)
                    
                        @php
                            $isNewParent = $currentParent !== $journal->id;
                    
                            // Print previous entry total
                            if ($isNewParent && $currentParent !== null) {
                        @endphp
                            <tr class="bg-light fw-bold">
                                
                                <td colspan="3" class="text-end">Entry Total</td>
                                <td class="text-end">{{ formatIndianNumber($genQty) }}</td>
                                <td></td>
                                <td></td>
                                <td class="text-end">{{ formatIndianNumber($genAmt) }}</td>
                                <td class="text-end">{{ formatIndianNumber($conQty) }}</td>
                                <td></td>
                                <td></td>
                                <td class="text-end">{{ formatIndianNumber($conAmt) }}</td>
                                <td></td>
                            </tr>
                            <tr class="font-12 text-muted bg-light">
                              <td colspan="11" class="ps-4 py-1" style="text-align:left;">
                                 
                                 <strong>Created By:</strong> 
                                 {{ $journal->created_by_name ?? '-' }}

                                 &nbsp;&nbsp;|&nbsp;&nbsp;

                                 <strong>Approved By:</strong> 
                                 @if($journal->approved_status == 1)
                                    {{ $journal->approved_by_name ?? '-' }}
                                    <small>({{ date('d-m-Y H:i', strtotime($journal->approved_at)) }})</small>
                                 @else
                                    -
                                 @endif

                              </td>
                           </tr>
                        @php
                                // Reset per entry totals
                                $genQty = $genAmt = $conQty = $conAmt = 0;
                            }
                        @endphp
                    
                        {{-- Data Row --}}
                        <tr class="font-14 font-heading {{ $isNewParent ? 'first-row' : '' }}">
                            <td>{{ $isNewParent ? date('d-m-Y', strtotime($journal->journal_date)) : '' }}</td>
                            <td>{{ $isNewParent ? $journal->voucher_no_prefix : '' }}</td>
                            <td>{{ $journal->name != '' ? $journal->name : $journal->new_item }}</td>
                    
                            <td class="text-end">{{ $journal->new_weight }}</td>
                            <td>{{ $journal->new_item != '' ? $journal->new_unit : '' }}</td>
                            <td class="text-end">{{ $journal->new_price }}</td>
                            <td class="text-end">{{ $journal->new_amount }}</td>
                    
                            <td class="text-end">{{ $journal->consume_weight }}</td>
                            <td>{{ $journal->name != '' ? $journal->s_name : '' }}</td>
                            <td class="text-end">{{ $journal->consume_price }}</td>
                            <td class="text-end">{{ $journal->consume_amount }}</td>
                    
                            <td>
                                <?php 
                             
                              if(in_array(date('Y-m',strtotime($journal->journal_date)),$month_arr) && $isNewParent){                           
                                 
                                    if($journal->consumption_entry_status==0){?>                                       
                                       @can('action-module',63)
                                          <a href="{{ URL::to('edit-stock-journal/' . $journal->id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                       @endcan
                                       <?php 
                                    } ?>
                                    @if(!in_array($journal->id,$hideDeleteFor))
                                       @can('action-module',64)
                                          <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $journal->id;?>">
                                             <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                          </button>
                                       @endcan
                                    @endif
                                    <?php 
                                 
                              }?>
                            </td>
                        </tr>
                        
                        @php
                            // Add per entry totals
                            $genQty += $journal->new_weight;
                            $genAmt += $journal->new_amount;
                            $conQty += $journal->consume_weight;
                            $conAmt += $journal->consume_amount;
                    
                            // Add overall totals
                            $overallGenQty += $journal->new_weight;
                            $overallGenAmt += $journal->new_amount;
                            $overallConQty += $journal->consume_weight;
                            $overallConAmt += $journal->consume_amount;
                    
                            $currentParent = $journal->id;
                        @endphp
                    
                    @endforeach
                    
                    {{-- Last entry total --}}
                    @if($currentParent !== null)
                    <tr class="bg-light fw-bold">
                        <td colspan="3" class="text-end">Entry Total</td>
                        <td class="text-end">{{ formatIndianNumber($genQty) }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-end">{{ formatIndianNumber($genAmt) }}</td>
                        <td class="text-end">{{ formatIndianNumber($conQty) }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-end">{{ formatIndianNumber($conAmt) }}</td>
                        <td></td>
                    </tr>
                    @endif
                    
                    {{-- OVERALL TOTAL --}}
                    <tr class="bg-warning fw-bold">
                        <td colspan="3" class="text-end">Overall Total</td>
                        <td class="text-end">{{ formatIndianNumber($overallGenQty) }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-end">{{ formatIndianNumber($overallGenAmt) }}</td>
                        <td class="text-end">{{ formatIndianNumber($overallConQty) }}</td>
                        <td></td>
                        <td></td>
                        <td class="text-end">{{ formatIndianNumber($overallConAmt) }}</td>
                        <td></td>
                    </tr>
                    
                    </tbody>
               </table>
            </div>
         </div>
      </div>
   </section>
</div>
<div class="modal fade" id="delete_journal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form class="" method="POST" action="{{ route('delete-stock-journal') }}">
            @csrf
            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this record</h5>
               <p class="font-14 text-body "> Do you really want to delete these records? this processcannot be undone. </p>
            </div>
            <div class="modal-footer border-0 mx-auto p-0">
               <input type="hidden" name="del_id" id="del_id">
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
   $(".cancel").click(function() {
      $("#delete_journal").modal("hide");
   });
   $(document).on('click','.delete',function(){
      var id = $(this).attr("data-id");
      $("#del_id").val(id);
      $("#delete_journal").modal("show");
   });
   $("#search").keyup(function () {
      var value = this.value.toLowerCase().trim();
      $(".stock_journal_table tr").each(function (index) {
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

      function formatDate(dateStr){
         if(!dateStr) return '';
         let parts = dateStr.split("-");
         return parts[2] + "-" + parts[1] + "-" + parts[0];
      }

      csv.push("From Date: " + formatDate(from_date));
      csv.push("To Date: " + formatDate(to_date));
      csv.push("");

      let header = [];
      $("#stock_journal_table thead th").each(function (index) {
         if(index != 10){
               header.push($(this).text().trim());
         }
      });
      csv.push(header.join(","));

      $("#stock_journal_table tbody tr").each(function () {

         if($(this).hasClass("font-12") && $(this).hasClass("bg-light")){
               return;
         }

         let row = [];
         let isTotalRow = false;

         let tds = $(this).find("td");

         if(tds.length && $(tds[0]).attr("colspan") == "2"){

               let label = $(tds[0]).text().trim(); // Entry Total / Overall Total
               isTotalRow = true;

               row = ["", label]; 
               tds.each(function(index){
                  if(index == 0) return; 
                  if(index == 10) return;

                  let text = $(this).text().trim()
                     .replace(/\n/g, '')
                     .replace(/,/g, '');

                  row.push(text);
               });

         } else {

               tds.each(function(index){

                  if(index == 10) return;

                  let text = $(this).text().trim()
                     .replace(/\n/g, '')
                     .replace(/,/g, '');

                  row.push(text);
               });
         }

         if(row.length > 1){
               csv.push(row.join(","));
         }
      });

      let csvString = csv.join("\n");

      let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
      let url = window.URL.createObjectURL(blob);

      let a = document.createElement("a");
      a.href = url;
      a.download = "stock_journal_report.csv";
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
      $("#stock_journal_table thead th").each(function (index) {
         if(index != 10){
               header.push($(this).text().trim());
         }
      });
      rows.push(header);

      $("#stock_journal_table tbody tr").each(function () {

         if($(this).hasClass("font-12") && $(this).hasClass("bg-light")){
               return;
         }

         let row = [];
         let tds = $(this).find("td");

         if(tds.length && $(tds[0]).attr("colspan") == "2"){

               let label = $(tds[0]).text().trim();

               row = ["", label];

               tds.each(function(index){
                  if(index == 0) return;
                  if(index == 10) return;

                  let text = $(this).text().trim();
                  row.push(text);
               });

         } else {

               tds.each(function(index){

                  if(index == 10) return;

                  let text = $(this).text().trim();
                  row.push(text);
               });
         }

         if(row.length > 1){
               rows.push(row);
         }
      });

      let printWindow = window.open('', '', 'width=1000,height=700');

      let html = `
         <html>
         <head>
               <title>Stock Journal</title>
               <style>
                  body { font-family: Arial; font-size: 11px; }
                  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                  th, td { border: 1px solid #000; padding: 4px; }
                  th { background: #f2f2f2; }
                  .text-right { text-align: right; }
                  .no-border td { border-top: none !important; }
                  .bold { font-weight: bold; }
               </style>
         </head>
         <body>

         <h2 style="text-align:center; text-decoration: underline;">
               List of Stock Journal
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
         let isSubRow = (row[0] === "");
         let isTotal  = (row[1] && (row[1].includes("Total")));

         html += `<tr class="${isSubRow ? 'no-border' : ''} ${isTotal ? 'bold' : ''}">`;

         row.forEach((cell, index) => {

               let align = (index == 2 || index == 5 || index == 6 || index == 9)
                  ? 'text-right' : '';

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