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
                <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
                    <nav aria-label="breadcrumb meri-breadcrumb ">
                        <ol class="breadcrumb meri-breadcrumb m-0  ">
                            <li class="breadcrumb-item">
                                <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                                <a class="fw-bold font-heading font-12  text-decoration-none" href="#">
                                    Purchase </a>
                            </li>

                        </ol>
                    </nav>
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
                </div>

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        List of Add Purchase Return Voucher
                    </h5>
                    <form  action="{{ route('purchase-return.index') }}" method="GET">
                       @csrf
                       <div class="d-md-flex d-block">                  
                          <div class="calender-administrator my-2 my-md-0">
                             <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{date('Y-m-d',strtotime($from_date))}}">
                          </div>
                          <div class="calender-administrator ms-md-4">
                             <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{date('Y-m-d',strtotime($to_date))}}">
                          </div>
                          <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                       </div>
                    </form>
                    <div class="d-md-flex d-block"> 
                       <input type="text" id="search" class="form-control" placeholder="Search">
                    </div>
                    <div class="d-md-flex d-block"> 
                       <a href="{{ route('debit-note-without-item.create') }}" class="btn btn-xs-primary">ADD WITHOUT ITEM<svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                       <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" /></svg> </a>
                    </div>
                    <a href="{{ route('purchase-return.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                        </svg>
                    </a>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm">
                    <table class="table-striped table m-0 shadow-sm purchase_return_table">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Purchase Return No </th>
                                <!--<th class="w-min-120 border-none bg-light-pink text-body ">Particulars </th>-->
                                <th class="w-min-120 border-none bg-light-pink text-body ">Party Name
                                </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Amount </th>
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
                                    <td class="w-min-120 " style="text-align: center;"><?php echo $value->series_no."/".$value->financial_year."/DR".$value->purchase_return_no ?></td>
                                    <td class="w-min-120 "><?php echo $value->account_name ?></td>
                                    <td class="w-min-120 " style="text-align: right;"> 
                                       <?php 
                                       if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                          echo $value->total;
                                       }else{
                                          echo money_format('%!i', $value->total);
                                       }
                                       $tot_amt = $tot_amt + $value->total; ?></td>
                                    <td class="w-min-120  text-center">
                                       <?php 
                                       if(in_array(date('Y-m',strtotime($value->date)),$month_arr)){?>
                                             <a href="{{ URL::to('purchase-return-edit/'.$value->purchases_id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                             <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->purchases_id;?>">
                                            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                             </button>
                                          <?php 
                                       } ?>
                                        <a href="{{ URL::to('purchase-return-invoice/' . $value->purchases_id) }}" target="__blank"><img src="{{ URL::asset('public/assets/imgs/eye-icon.svg')}}" class="px-1" alt=""></a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 fw-bold font-heading">TOTAL</td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120 fw-bold font-heading" style="text-align: right;">
                                    <?php 
                                    if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                       echo $tot_amt;
                                    }else{
                                       echo money_format('%!i', $tot_amt);
                                    } 
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
</script>
@endsection