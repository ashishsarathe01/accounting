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
                <div class="alert alert-danger" role="alert"> {{session('error')}}
                </div>
                @endif
                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                

                <div
                    class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        List of Contra Voucher
                    </h5>
                    <form  action="{{ route('contra.index') }}" method="GET">
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
                       <button class="btn btn-secondary ms-2 print_btn">PRINT</button>
                    </div>
                    @can('action-module',75)
                        <a href="{{ route('contra.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 20 20" fill="none">
                            <path
                                d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                fill="white" />
                        </svg>
                    </a>
                    @endcan
                    
                </div>
                <div class="transaction-table bg-white table-view shadow-sm" style="    overflow: scroll;">
                    <table class="table-striped table m-0 shadow-sm contra_table" id="contra_table">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Vch/Bill No </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Account </th>
                                <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Debit(Rs.)
                                </th>
                                <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Credit(Rs.)</th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Mode</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Series </th>
                                <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php
                            $tot_dbt = 0;
                            $tot_crt = 0;
                            $prev_con_id = null;
                            setlocale(LC_MONETARY, 'en_IN');



                            foreach ($contra as $key => $value) {
                                $is_last = !isset($contra[$key+1]) || $contra[$key+1]->con_id != $value->con_id;
                            $value->debit = trim($value->debit);
                                $value->credit = trim($value->credit);
                            ?>
                            <tr class="font-14 font-heading bg-white"><td>
                                <!-- Show date only if con_id is different -->
                                <?php if ($value->con_id != $prev_con_id) {
                                echo date("d-m-Y", strtotime($value->date));
                            }
                            ?></td>
                                <td class="w-min-120">
                                    <?php 
                                    echo ($value->con_id != $prev_con_id) ? $value->voucher_no : "&nbsp;";
                                    ?>
                                </td>
                                <!-- Account Name -->
                                <td class="w-min-120 "><?php echo $value->acc_name ?></td>
                                <!-- Debit -->
                                <td class="w-min-120 " style="text-align: right;"><?php 
                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                        echo $value->debit;
                                    } else {
                                        if(!empty($value->debit)){
                                            echo formatIndianNumber(str_replace(",","",$value->debit));
                                        } else {
                                            echo $value->debit;
                                        }
                                    }
                                    if(!empty($value->debit)){
                                        $tot_dbt += str_replace(",","",$value->debit);
                                    }
                                ?></td>

                                <!-- Credit -->
                                <td class="w-min-120 " style="text-align: right;"><?php 
                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                        echo $value->credit;
                                    } else {
                                        if(!empty($value->credit)){
                                            echo formatIndianNumber(str_replace(",","",$value->credit));
                                        } else {
                                            echo $value->credit;
                                        }
                                    }
                                    if(!empty($value->credit)){
                                        $tot_crt+= str_replace(",","",$value->credit);
                                    }
                                ?></td>

                                <!-- Mode (shown once per con_id) -->
                                <td class="w-min-120 ">
                                    <?php 
                                    if ($value->con_id != $prev_con_id) {
                                        if($value->m == '1'){
                                            echo 'Cash';
                                        } else if($value->m == '0'){
                                            echo 'IMPS/NEFT/RTGS'; 
                                        } else if($value->m == '2'){
                                            echo 'CHEQUE';
                                        } else {
                                            echo 'IMPS/NEFT/RTGS'; 
                                        }
                                    } else {
                                        echo "&nbsp;";
                                    }
                                    ?>
                                </td>

                                <!-- Series Number (shown once per con_id) -->
                                <td class="w-min-120 ">
                                    <?php 
                                    echo ($value->con_id != $prev_con_id) ? $value->series_no : "&nbsp;";
                                    ?>
                                </td>

                                <!-- Action (shown once per con_id) -->
                                <td class="w-min-120 text-center">
                                    <?php 
                                    if ($value->con_id != $prev_con_id && in_array(date('Y-m',strtotime($value->date)),$month_arr) && $value->approved_status != 1) {
                                    ?>
                                        @can('action-module',45)
                                            <a href="{{ URL::to('contra/' . $value->con_id . '/edit') }}">
                                                <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="">
                                            </a>
                                        @endcan
                                        @can('action-module',46)
                                            <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->con_id;?>">
                                                <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                            </button>
                                        @endcan
                                    <?php 
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php if($is_last){ ?>
                            <tr class="font-12 text-muted bg-light">
                            <td colspan="8" class="ps-4 py-1" style="text-align:left;">
                                
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
                            <?php } ?>
                            <?php 
                                $prev_con_id = $value->con_id; // Update for next iteration
                            } 
                            ?>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 fw-bold font-heading">TOTAL</td>
                                <td></td>
                                <td></td>
                                <td class="w-min-120 fw-bold font-heading" style="text-align: right;"><?php echo $tot_dbt;?></td>
                                <td class="w-min-120 fw-bold font-heading" style="text-align: right;"><?php echo $tot_crt;?></td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120 "></td>
                                <td class="w-min-120 "></td>
                                </td>
                            </tr>
                          
                        </tbody>
                        <!--<tbody>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 ">10/11/2023</td>
                                <td class="w-min-120 ">D</td>
                                <td class="w-min-120 ">Industries</td>
                                <td class="w-min-120 ">Agro</td>
                                <td class="w-min-120 ">1</td>
                                <td class="w-min-120  text-center">
                                    <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="">
                                    <button type="button" class="border-0 bg-transparent" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal">
                                        <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                    </button>
                                </td>
                            </tr>
                        
                          
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 fw-bold font-heading">TOTAL</td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120"></td>

                                <td class="w-min-120 fw-bold font-heading">65,000.00</td>
                                <td class="w-min-120 text-center">

                                </td>
                            </tr>

                        </tbody>-->
                    </table>
                </div>
            </div>
            <!-- <div class="col-lg-1 d-flex justify-content-center">
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
            </div> -->
        </div>
</section>
</div>
   <!-- Modal ---for delete ---------------------------------------------------------------icon-->
   <div class="modal fade" id="contraDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog w-360  modal-dialog-centered  ">
         <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="" method="POST" action="{{ route('contra.delete') }}">
               @csrf
               <div class="modal-body text-center p-0">
                  <button class="border-0 bg-transparent">
                     <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                  </button>
                  <h5 class="mb-3 fw-normal">Delete this record</h5>
                  <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone. </p>
               </div>
               <input type="hidden" value="" id="contra_id" name="contra_id" />
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
           
            $("#contraDeleteModal").modal("hide");
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

            } else if ($("#business_type").val() == 2) {
                $("#dateofjoing_section").show();
                $("#din_sectioon").hide();
                $("#share_per_div").show();
                $("#designation").html('<option value="partner">Partner</option><option value="authorised_signatory">Authorised Signatory</option>');
            } else {
                $("#dateofjoing_section").show();
                $("#din_sectioon").show();
                $("#share_per_div").hide();
                $("#designation").html('<option value="director">Director</option><option value="authorised_signatory">Authorised Signatory</option>');
            }
        }, 1000);
    });
   $(document).on('click','.delete',function(){
      var id = $(this).attr("data-id");
      $("#contra_id").val(id);
      $("#contraDeleteModal").modal("show");
   });
   $("#search").keyup(function () {
      var value = this.value.toLowerCase().trim();
      $(".contra_table tr").each(function (index) {
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
        $("#contra_table thead th").each(function (index) {
            if(index != 7){
                header.push($(this).text().trim());
            }
        });
        csv.push(header.join(","));

        $("#contra_table tbody tr").each(function () {

            if($(this).hasClass("bg-light")){
                return;
            }

            let row = [];
            let isEmptyRow = true;

            $(this).find("td").each(function (index) {

                if(index == 7) return; // skip action

                let text = $(this).text().trim()
                    .replace(/\n/g, '')
                    .replace(/,/g, '');

                if(text !== "" && text !== "\u00a0"){
                    isEmptyRow = false;
                }

                row.push(text);
            });

            if(!isEmptyRow){
                csv.push(row.join(","));
            }
        });

        let csvString = csv.join("\n");

        let blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
        let url = window.URL.createObjectURL(blob);

        let a = document.createElement("a");
        a.href = url;
        a.download = "contra_report.csv";
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
                <title>Contra Report</title>
                <style>
                    body { font-family: Arial; font-size: 12px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #000; padding: 5px; }
                    th { background: #f2f2f2; }
                    .text-right { text-align: right; }
                    .no-border td { border-top: none !important; }
                </style>
            </head>
            <body>

            <h2 style="text-align:center; text-decoration: underline;">
                List of Contra Voucher
            </h2>

            <p style="text-align:center;">
                From: ${from_date} &nbsp;&nbsp; To: ${to_date}
            </p>

            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vch/Bill No</th>
                        <th>Account</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Credit</th>
                        <th>Mode</th>
                        <th>Series</th>
                    </tr>
                </thead>
                <tbody>
        `;

        let totalDebit = 0;
        let totalCredit = 0;

        $("#contra_table tbody tr").each(function () {

            if($(this).hasClass("bg-light")){
                return;
            }

            let tds = $(this).find("td");

            if($(tds[0]).text().trim().toLowerCase() === "total"){
                totalDebit  = $(tds[3]).text().trim();
                totalCredit = $(tds[4]).text().trim();
                return;
            }

            let date   = $(tds[0]).text().trim();
            let vch    = $(tds[1]).text().trim();
            let acc    = $(tds[2]).text().trim();
            let debit  = $(tds[3]).text().trim();
            let credit = $(tds[4]).text().trim();
            let mode   = $(tds[5]).text().trim();
            let series = $(tds[6]).text().trim();

            let isSubRow = (date === "" && vch === "");

            tableHTML += `
                <tr class="${isSubRow ? 'no-border' : ''}">
                    <td>${date}</td>
                    <td>${vch}</td>
                    <td>${acc}</td>
                    <td class="text-right">${debit}</td>
                    <td class="text-right">${credit}</td>
                    <td>${mode}</td>
                    <td>${series}</td>
                </tr>
            `;
        });

        tableHTML += `
            <tr>
                <td style="font-weight:bold;">TOTAL</td>
                <td></td>
                <td></td>
                <td class="text-right" style="font-weight:bold;">${totalDebit}</td>
                <td class="text-right" style="font-weight:bold;">${totalCredit}</td>
                <td></td>
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