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
                
                <div id="progressBox" style="display:none;">
                    <div style="background:#eee;">
                        <div id="progressBar" style="width:0%; background:green; color:white; text-align:center;">
                            0%
                        </div>
                    </div>
                    <small id="progressText"></small>
                </div>
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        List of Purchase Voucher   
                    </h5>
                        <!--<form id="roundoffForm">-->
                        <!--    @csrf-->
                        
                        <!--    <input type="date" name="from_date" required value="{{ request('from_date') }}">-->
                        <!--    <input type="date" name="to_date" required value="{{ request('to_date') }}">-->
                        
                        <!--    <button type="submit" class="btn btn-danger">-->
                        <!--        Fix Round-Off (Filtered)-->
                        <!--    </button>-->
                        <!--</form>-->
                    <form  action="{{ route('purchase.index') }}" method="GET">
                       @csrf
                       <div class="d-md-flex d-block">                  
                          <div class="calender-administrator my-2 my-md-0">
                             <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{!empty($from_date) ? date('Y-m-d', strtotime($from_date)) : ''}}">
                          </div>
                          <div class="calender-administrator ms-md-4">
                             <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{!empty($to_date) ? date('Y-m-d', strtotime($to_date)) : ''}}">
                          </div>
                          <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                       </div>
                    </form>
                    <div class="d-md-flex d-block"> 
                        <input type="text" id="search" class="form-control" placeholder="Search">
                        <button class="btn btn-info ms-2 export_csv">CSV</button>
                        <button class="btn btn-info ms-2  print_btn">PRINT</button>
                    </div>
                    @can('action-module',83)
                        <a href="{{ route('purchase.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                        </svg>
                    </a>
                    @endcan
                    
                </div>
               <div class="transaction-table bg-white table-view shadow-sm purchase_table">
                  <table class="table-striped table-bordered table m-0 shadow-sm" id="purchase_table">
                     <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                           <th>Date </th>
                           <th style="text-align: center;">Vch/Bill No.</th>
                           <th>Particular</th>
                           <th>Item Details</th>
                           <!-- <th style="text-align: right;">Quantity</th> -->
                           <th style="text-align: right;">Amount </th>
                           <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        $tot_amt = 0;
                        $qty = 0;setlocale(LC_MONETARY, 'en_IN');
                        foreach ($purchase as $value) { ?>
                           <tr class="font-14 font-heading bg-white">
                              <td class=""><?php echo date('d-m-Y',strtotime($value->date));?></td>
                              <td style="text-align: center;"><?php echo $value->voucher_no ?> @if($value->vehicle_voucher_no)  ({{$value->vehicle_voucher_no}}) @endif</td>
                              <td class=""><?php echo $value->account['account_name'] ?></td>
                              <td>
                                @php $qty_total = 0; @endphp
                                @foreach($value->purchaseDescription as $v)
                                    <strong>{{$v->item->name}} ({{$v->qty}} {{$v->units->name}})</strong><br>
                                    <table class="table table-bordered">
                                        @foreach($v->parameterColumnInfo as $k1=>$v1)
                                            @if($k1==0)
                                                <tr>
                                                    @if($v1->paremeter_name1!="")
                                                        <th>{{$v1->paremeter_name1}}</th>
                                                    @endif
                                                    @if($v1->paremeter_name2!="")
                                                        <th>{{$v1->paremeter_name2}}</th>
                                                    @endif
                                                    @if($v1->paremeter_name3!="")
                                                        <th>{{$v1->paremeter_name3}}</th>
                                                    @endif
                                                    @if($v1->paremeter_name4!="")
                                                        <th>{{$v1->paremeter_name4}}</th>
                                                    @endif
                                                    @if($v1->paremeter_name5!="")
                                                        <th>{{$v1->paremeter_name5}}</th>
                                                    @endif
                                                </tr>
                                            @endif
                                            <tr>
                                                @if($v1->parameter1_id!="" && $v1->parameter1_id!="0")
                                                    <td>
                                                        {{$v1->parameter1_value}}
                                                    </td>
                                                @endif
                                                @if($v1->parameter2_id!="" && $v1->parameter2_id!="0")
                                                    <td>
                                                        {{$v1->parameter2_value}}
                                                    </td>
                                                @endif
                                                @if($v1->parameter3_id!="" && $v1->parameter3_id!="0")
                                                    <td>
                                                        {{$v1->parameter3_value}}
                                                    </td>
                                                @endif
                                                @if($v1->parameter4_id!="" && $v1->parameter4_id!="0")
                                                    <td>
                                                        {{$v1->parameter4_value}}
                                                    </td>
                                                @endif
                                                @if($v1->parameter5_id!="" && $v1->parameter5_id!="0")
                                                    <td>
                                                        {{$v1->parameter5_value}}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </table>
                                    @php $qty_total = $qty_total + floatval($v->qty); @endphp
                                @endforeach
                              </td>
                              <!-- <td style="text-align: right;">{{$qty_total}}</td> -->
                              <td style="text-align: right;">
                                 <?php 
                                 echo $value->total;
                                 if(!empty($value->total)){
                                     $tot_amt = $tot_amt + $value->total; 
                                 }
                                 ?>
                              </td>
                              <td class="w-min-120  text-center">
                                 <?php 
                                 if(in_array(date('Y-m',strtotime($value->date)),$month_arr)){?>
                                    @can('action-module',57)
                                        <a href="{{ URL::to('purchase-edit/'.$value->id) }}">  <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="">
                                        </a>
                                    @endcan
                                    @can('action-module',58)
                                        <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->id;?>">
                                        <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                        </button>
                                    @endcan
                                    <?php 
                                 } ?>
                                 <a title="View Invoice" href="{{ URL::to('purchase-invoice/' . $value->id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
                              </td>
                           </tr>
                           <tr class="font-12 text-muted bg-light">
                            <td colspan="6" class="ps-4 py-1" style="text-align:left;">
                                
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
                           <td class="w-min-120"></td>
                           <td class="w-min-120"></td>
                           <td class="w-min-120 fw-bold font-heading" style="text-align: right;">
                              <?php 
                              echo $tot_amt;
                              ?></td>
                           <td class="w-min-120 "></td>
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
</div>
</section>
</div>
<!-- Modal ---for delete ---------------------------------------------------------------icon-->
<div class="modal fade" id="delete_purchase" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="" method="POST" action="{{ route('purchase.delete') }}">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body "> Do you really want to delete these records? this process
                        cannot be
                        undone. </p>
                </div>
                <input type="hidden" value="" id="purchase_id" name="purchase_id" />
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
         $("#delete_purchase").modal("hide");
      });
      $("#pan").change(function() {
         var inputvalues = $("#pan").val();
         var paninformat = new RegExp("^[A-Z]{5}[0-9]{4}[A-Z]{1}$");
         if (paninformat.test(inputvalues)) {
             return true;
         } else {
             alert('Please Enter Valid PAN Number');
             $("#pan").val('');
             $("#pan").focus();
         }
      });
      setTimeout(function() {
         if ($("#business_type").val() == 1) {
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
      $("#purchase_id").val(id);
      $("#delete_purchase").modal("show");
   });
   $("#search").keyup(function () {
      var value = this.value.toLowerCase().trim();
      $(".purchase_table tr").each(function (index) {
         if (!index) return;
         $(this).find("td").each(function () {
            var id = $(this).text().toLowerCase().trim();
            var not_found = (id.indexOf(value) == -1);
            $(this).closest('tr').toggle(!not_found);
            return not_found;
         });
      });
   });
   

// document.getElementById('roundoffForm').addEventListener('submit', function(e) {
//     e.preventDefault(); // ❗ STOP page reload

//     if (!confirm('Fix roundoff?')) return;

//     startTracking(); // start progress

//     let formData = new FormData(this);

//     fetch("{{ route('purchase.bulk.roundoff') }}", {
//         method: "POST",
//         headers: {
//             'X-CSRF-TOKEN': "{{ csrf_token() }}"
//         },
//         body: formData
//     })
//     .then(res => res.text())
//     .then(data => {
//         console.log("Completed");
//     });
// });

function startTracking() {

    let box = document.getElementById('progressBox');
    if (box) box.style.display = 'block'; // ✅ safe check

    let interval = setInterval(() => {

            fetch("{{ url('/purchase/roundoff-progress') }}")
.then(res => {
    if (!res.ok) throw new Error("Server error");
    return res.json();
})
.then(data => {

    if (!data || data.error) return; // ✅ prevent crash
             let percent = data.percent;
            let bar = document.getElementById('progressBar');
            let text = document.getElementById('progressText');

            if (bar) {
                bar.style.width = percent + '%';
                bar.innerText = percent + '%';
            }

            if (text) {
                text.innerText = data.done + " / " + data.total + " processed";
            }

            if (data.done >= data.total && data.total > 0) {
                clearInterval(interval);
                alert("Round-off update completed ✅");
            }
        });

    }, 1000);
}
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
        $("#purchase_table thead th").each(function (index) {
            if(index != 5){
                header.push($(this).text().trim());
            }
        });
        csv.push(header.join(","));

        $("#purchase_table tbody tr").each(function () {

            if($(this).hasClass("bg-light")){
                return;
            }

            let cols = [];
            let items = [];

            $(this).find("td").each(function (index) {

                if(index == 5) return; // skip action

                if(index == 3){
                    $(this).find("strong").each(function () {
                        items.push($(this).text().trim().replace(/,/g,''));
                    });
                } else {
                    let text = $(this).text().trim()
                        .replace(/\n/g, '')
                        .replace(/,/g, '');

                    cols.push(text);
                }

            });

            if(items.length > 0){
                let firstRow = [...cols];
                firstRow.splice(3, 0, items[0]); // insert item at correct position
                csv.push(firstRow.join(","));

                for(let i = 1; i < items.length; i++){
                    let emptyRow = ["", "", "", items[i], ""];
                    csv.push(emptyRow.join(","));
                }
            } else {
                csv.push(cols.join(","));
            }

        });

        let csvString = csv.join("\n");

        let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
        let url = window.URL.createObjectURL(blob);

        let a = document.createElement("a");
        a.href = url;
        a.download = "purchase_report.csv";
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
                <title>Purchase Report</title>
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
                List of Purchase Voucher
            </h2>

            <p style="text-align:center;">
                From: ${from_date} &nbsp;&nbsp; To: ${to_date}
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vch/Bill No</th>
                        <th>Particular</th>
                        <th>Item Details</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
        `;

        let total = 0;

        $("#purchase_table tbody tr").each(function () {

            if($(this).hasClass("bg-light")){
                return;
            }

            let tds = $(this).find("td");

            if($(tds[0]).text().trim().toLowerCase() === "total"){
                total = $(tds[4]).text().trim();
                return;
            }

            let date = $(tds[0]).text().trim();
            let vch  = $(tds[1]).text().trim();
            let party= $(tds[2]).text().trim();
            let amt  = $(tds[4]).text().trim();

            let items = [];

            $(tds[3]).find("strong").each(function () {
                items.push($(this).text().trim());
            });

            if(items.length > 0){

                tableHTML += `
                    <tr>
                        <td>${date}</td>
                        <td>${vch}</td>
                        <td>${party}</td>
                        <td>${items[0]}</td>
                        <td class="text-right">${amt}</td>
                    </tr>
                `;

                for(let i = 1; i < items.length; i++){
                    tableHTML += `
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>${items[i]}</td>
                            <td></td>
                        </tr>
                    `;
                }

            } else {

                tableHTML += `
                    <tr>
                        <td>${date}</td>
                        <td>${vch}</td>
                        <td>${party}</td>
                        <td></td>
                        <td class="text-right">${amt}</td>
                    </tr>
                `;
            }

        });

        tableHTML += `
            <tr>
                <td colspan="4" style="font-weight:bold;">TOTAL</td>
                <td class="text-right" style="font-weight:bold;">${total}</td>
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