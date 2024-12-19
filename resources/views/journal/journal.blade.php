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
                <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
                    <nav aria-label="breadcrumb meri-breadcrumb ">
                        <ol class="breadcrumb meri-breadcrumb m-0  ">
                            <li class="breadcrumb-item">
                                <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                                <a class="fw-bold font-heading font-12  text-decoration-none" href="#">
                                Journal</a>
                            </li>

                        </ol>
                    </nav>
                    
                </div>

                <div
                    class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        List of Journal Voucher
                    </h5>
                    <form  action="{{ route('journal.index') }}" method="GET">
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
                    <a href="{{ route('journal.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                            viewBox="0 0 20 20" fill="none">
                            <path
                                d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                fill="white" />
                        </svg>
                    </a>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm">
                <table class="table-striped table m-0 shadow-sm journal_table">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Date </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Account Name </th>
                                <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Debit</th>
                                <th class="w-min-120 border-none bg-light-pink text-body " style="text-align:right;">Credit</th>  
                                <th class="w-min-120 border-none bg-light-pink text-body">Series </th>                              
                                <th class="w-min-120 border-none bg-light-pink text-body text-center">Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $tot_dbt = 0;
                            $tot_crt = 0;
                            $arr = [];
                            setlocale(LC_MONETARY, 'en_IN');
                            foreach ($journal as $value) {
                              
                             ?>
                                <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120 "><?php 
                                    if(!in_array($value->jon_id,$arr)){
                                       echo date("d-m-Y", strtotime($value->date));
                                    }
                                     ?></td>
                                    <td class="w-min-120 "><?php echo $value->acc_name ?></td>
                                    <td class="w-min-120 " style="text-align: right;"><?php 

                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                       echo $value->debit;
                                    }else{
                                       echo money_format('%!i', $value->debit);
                                    }
                                     $tot_dbt = $tot_dbt+$value->debit; ?></td>
                                    <td class="w-min-120 " style="text-align: right;"><?php 

                                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                                       echo $value->credit;
                                    }else{
                                       echo money_format('%!i',$value->credit);
                                    }
                                     $tot_crt = $tot_crt+$value->credit; ?></td>
                                    <td class="w-min-120 "><?php echo $value->series_no ?></td>
                                    <td class="w-min-120  text-center">
                                       <?php 
                                       if(!in_array($value->jon_id,$arr)){
                                       if(in_array(date('Y-m',strtotime($value->date)),$month_arr)){?>
                                          <a href="{{ URL::to('journal/' . $value->jon_id . '/edit') }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                          <button type="button" class="border-0 bg-transparent delete" data-id="<?php echo $value->jon_id; ?>">
                                               <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                          </button>
                                          <?php 
                                       }
                                       }?>
                                    </td>
                                 </tr>
                            <?php 
                            array_push($arr,$value->jon_id);
                         } ?>
                            <tr class="font-14 font-heading bg-white">
                                <td class="w-min-120 fw-bold font-heading">TOTAL</td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120 fw-bold font-heading" style="text-align: right;"><?php echo $tot_dbt;?></td>
                                <td class="w-min-120 fw-bold font-heading" style="text-align: right;"><?php echo $tot_crt;?></td>
                                <td class="w-min-120"></td>
                                <td class="w-min-120 "></td>
                            </tr>
                          
                        </tbody>
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
</div>
</section>
</div>
   <!-- Modal ---for delete ---------------------------------------------------------------icon-->
  <div class="modal fade" id="journalDeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
           <div class="modal-header border-0 p-0">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <form class="" method="POST" action="{{ route('journal.delete') }}">
              @csrf
              <div class="modal-body text-center p-0">
                 <button class="border-0 bg-transparent">
                    <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                 </button>
                 <h5 class="mb-3 fw-normal">Delete this record</h5>
                 <p class="font-14 text-body "> Do you really want to delete these records? this process cannot be undone. </p>
              </div>
              <input type="hidden" value="" id="journal_id" name="journal_id" />
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
           
            $("#journalDeleteModal").modal("hide");
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
      $("#journal_id").val(id);
      $("#journalDeleteModal").modal("show");
   });
   $("#search").keyup(function () {
      var value = this.value.toLowerCase().trim();
      $(".journal_table tr").each(function (index) {
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