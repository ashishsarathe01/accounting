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
                <!--<div class="d-md-flex justify-content-between py-4 px-2 align-items-center">-->
                   
                    <!--<div class="d-md-flex d-block">
                        <div class="calender-administrator my-2 my-md-0  w-min-230">
                            <input type="date" id="customDate"
                                class="form-control calender-bg-icon calender-placeholder" placeholder="From date"
                                required>
                        </div>
                        <div class="calender-administrator   w-min-230 ms-md-4">
                            <input type="date" id="customDate"
                                class="form-control calender-bg-icon calender-placeholder" placeholder="To date"
                                required>
                        </div>
                    </div>-->
                <!--</div>-->

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-3 flex-wrap">
                    <h5 class="transaction-table-title m-0 me-3">
                        Purchase Return/Debit Note
                    </h5>
                    <form action="{{ route('purchase-return.index') }}" method="GET" class="d-flex align-items-center flex-wrap gap-2 m-0">
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
                        <select name="sr_nature" class="form-select form-select-sm" style="width:140px;">
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
                        @can('action-module',77)
                        <a href="{{ route('purchase-return.create') }}" class="btn btn-xs-primary btn-sm d-flex align-items-center">
                            ADD
                            <svg class="ms-1" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none">
                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white"/>
                            </svg>
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm">
                    <table class="table-striped table-bordered table m-0 shadow-sm purchase_return_table" id="purchase_return_table">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Debit Note No </th>
                                <!--<th class="w-min-120 border-none bg-light-pink text-body ">Particulars </th>-->
                                <th class="w-min-120 border-none bg-light-pink text-body ">Party Name
                                </th>
                                <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;">Amount </th>
                                <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tot_amt = 0;
                            $qty = 0;setlocale(LC_MONETARY, 'en_IN');
                            foreach ($purchase as $value) { ?>
                                <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120 "><?php echo date('d-m-Y',strtotime($value->date)); ?></td>
                                    <td class="w-min-120 " ><?php echo $value->sr_prefix ?></td>
                                    <td class="w-min-120 "><?php echo $value->account_name ?></td>
                                    <td class="w-min-120 " style="text-align: right;"> 
                                       <?php 
                                       echo $value->total;
                                       $tot_amt = $tot_amt + $value->total; ?></td>
                                    <td class="w-min-120  text-center">
                                       <?php 
                                        if(in_array(date('Y-m',strtotime($value->date)),$month_arr) && $value->approved_status != 1 && $value->status == '1'){?>

                                            @can('action-module',47)
                                            @if($value->e_invoice_status==0)
                                                <a href="{{ URL::to('purchase-return-edit/'.$value->purchases_id) }}">
                                                    <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="">
                                                </a>
                                            @endif
                                            @endcan

                                            @can('action-module',48)
                                                @if(
                                                    ($value->max_voucher_no == $value->purchase_return_no && $value->manual_numbering_status == "NO") 
                                                    || ($value->manual_numbering_status == "YES" || $value->manual_numbering_status == "")
                                                )
                                                    <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->purchases_id;?>">
                                                        <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                                    </button>
                                                @endif
                                            @endcan
                                            @if($value->e_invoice_status==0)
                                                <button type="button" 
                                                    class="btn btn-link p-0 cancel-purchase-return text-danger fw-bold" 
                                                    data-id="{{ $value->purchases_id }}" 
                                                    title="Cancel Purchase Return"
                                                    style="font-size:20px;">
                                                    &times;
                                                </button>
                                            @endif
                                        <?php 
                                        } ?>
                                        @if($value->status=='2')
                                            <h4 style="color:red">CANCELLED</h4>
                                        @endif
                                        @if($value->status=='1')
                                       @if($value->sr_nature=="WITH GST" && ($value->sr_type=="WITH ITEM" || $value->sr_type=="RATE DIFFERENCE"))
                                            <a href="{{ URL::to('purchase-return-invoice/' . $value->purchases_id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
                                        @elseif($value->sr_nature=="WITH GST" && $value->sr_type=="WITHOUT ITEM")
                                            <a href="{{ URL::to('purchase-return-without-item-invoice/' . $value->purchases_id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
                                        @elseif($value->sr_nature=="WITHOUT GST")
                                            <a href="{{ URL::to('purchase-return-without-gst-invoice/' . $value->purchases_id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
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
                            <?php } ?>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 fw-bold font-heading">TOTAL</td>
                                <td></td>
                                <td></td>
                                <td class="w-min-120 fw-bold font-heading" style="text-align: right;">
                                    <?php 
                                    echo $tot_amt;
                                    ?>
                                 </td>
                                <td class="w-min-120 "></td>
                            </tr>

                        </tbody>
                    </table>

                    <!--<table id="example" class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Vch/Bill No </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Particulars </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Item Details
                                </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Qty.</th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Unit </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Price</th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Amount </th>
                                <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 ">10/11/2023</td>
                                <td class="w-min-120 ">D</td>
                                <td class="w-min-120 ">Industries</td>
                                <td class="w-min-120 ">Agro</td>
                                <td class="w-min-120 ">1</td>
                                <td class="w-min-120 ">Reel</td>
                                <td class="w-min-120 ">Price</td>
                                <td class="w-min-120 ">Amount</td>
                                <td class="w-min-120  text-center">
                                    <img src="../assets/imgs/edit-icon.svg" class="px-1" alt="">
                                    <button type="button" class="border-0 bg-transparent" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal">
                                        <img src="../assets/imgs/delete-icon.svg" class="px-1" alt="">
                                    </button>
                                </td>
                            </tr>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 ">10/11/2023</td>
                                <td class="w-min-120 ">D</td>
                                <td class="w-min-120 ">Industries</td>
                                <td class="w-min-120 ">Agro</td>
                                <td class="w-min-120 ">1</td>
                                <td class="w-min-120 ">Reel</td>
                                <td class="w-min-120 ">118.00</td>
                                <td class="w-min-120 ">65,000.00</td>
                                <td class="w-min-120  text-center">
                                    <img src="../assets/imgs/edit-icon.svg" class="px-1" alt="">
                                    <button type="button" class="border-0 bg-transparent" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal">
                                        <img src="../assets/imgs/delete-icon.svg" class="px-1" alt="">
                                    </button>
                                </td>
                            </tr>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 ">10/11/2023</td>
                                <td class="w-min-120 ">D</td>
                                <td class="w-min-120 ">Industries</td>
                                <td class="w-min-120 ">Agro</td>
                                <td class="w-min-120 ">1</td>
                                <td class="w-min-120 ">Reel</td>
                                <td class="w-min-120 ">118.00</td>
                                <td class="w-min-120 ">65,000.00</td>
                                <td class="w-min-120  text-center">
                                    <img src="../assets/imgs/edit-icon.svg" class="px-1" alt="">
                                    <button type="button" class="border-0 bg-transparent" data-bs-toggle="modal"
                                        data-bs-target="#exampleModal">
                                        <img src="../assets/imgs/delete-icon.svg" class="px-1" alt="">
                                    </button>
                                </td>
                            </tr>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 fw-bold font-heading">TOTAL</td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120 fw-bold font-heading">1</td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120 fw-bold font-heading">65,000.00</td>
                                <td class="w-min-120 text-center">

                                </td>
                            </tr>

                        </tbody>
                    </table>-->
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
<div class="modal fade" id="delete_purchase_return" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="" method="POST" action="{{ route('purchase-return.delete') }}">
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
                <input type="hidden" value="" id="purchase_return_id" name="purchase_return_id" />
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
<div class="modal fade" id="cancel_purchase_return_modal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered w-360">
    <div class="modal-content p-4 border-divider border-radius-8">
      <div class="modal-header border-0 p-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body text-center">
        <span class="text-danger fw-bold mb-3 d-block mx-auto" style="font-size:50px;">&times;</span>
        <h5 class="mb-3 fw-normal">Cancel this Purchase Return?</h5>
        <p class="font-14 text-body">This process cannot be undone. Are you sure?</p>
      </div>

      <input type="hidden" id="cancel_purchase_return_id">

      <div class="modal-footer border-0 mx-auto p-0">
        <button type="button" class="btn btn-border-body" data-bs-dismiss="modal">CANCEL</button>
        <button type="button" class="btn btn-red" id="confirm_cancel_purchase_return">CONFIRM</button>
      </div>
    </div>
  </div>
</div>
@include('layouts.footer')
<script>
   $(document).ready(function(){
      $(".cancel").click(function(){
         $("#delete_purchase_return").modal("hide");
      });
      $("#pan").change(function() {
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
      setTimeout(function(){
         if($("#business_type").val() == 1){
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
      $("#purchase_return_id").val(id);
      $("#delete_purchase_return").modal("show");
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

   // Open modal
    $(document).on('click', '.cancel-purchase-return', function() {
        var id = $(this).data('id');
        $("#cancel_purchase_return_id").val(id);
        $("#cancel_purchase_return_modal").modal('show');
    });
    
    // Confirm cancel
    $("#confirm_cancel_purchase_return").click(function() {
    
        var id = $("#cancel_purchase_return_id").val();
    
        $.ajax({
            url: "{{ url('cancel-purchase-return') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            success: function(response) {
                $("#cancel_purchase_return_modal").modal('hide');
    
                if(response.success){
                    alert(response.message);
                    location.reload();
                } else {
                    alert("Error: " + response.message);
                }
            },
            error: function(){
                $("#cancel_purchase_return_modal").modal('hide');
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
        $("#purchase_return_table thead th").each(function (index) {
            if(index != 4){
                header.push($(this).text().trim());
            }
        });
        header.push("Status");
        csv.push(header.join(","));

        $("#purchase_return_table tbody tr").each(function () {

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
        a.download = "debit_note_report.csv";
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
                <title>Debit Note Report</title>
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
                List of Purchase Return Voucher
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
                        <th>Debit Note No</th>
                        <th>Party Name</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
        `;

        let total = 0;

        $("#purchase_return_table tbody tr").each(function () {

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