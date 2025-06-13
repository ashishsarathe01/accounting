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
                

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        List of Purchase Voucher
                    </h5>
                    <form  action="{{ route('purchase.index') }}" method="GET">
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
                  <table class="table-striped table m-0 shadow-sm">
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
                              <td style="text-align: center;"><?php echo $value->voucher_no ?></td>
                              <td class=""><?php echo $value->account['account_name'] ?></td>
                              <td>
                                @php $qty_total = 0; @endphp
                                @foreach($value->purchaseDescription as $v)
                                    <strong>{{$v->item->name}} ({{$v->qty}} {{$v->units->name}})</strong><br>
                                    <table class="table table-bordered">
                                        @foreach($v->parameterColumnInfo as $v1)
                                            @if($v1->parameterColumnName)
                                                <tr>
                                                    <td>
                                                        {{$v1->parameterColumnName->paremeter_name}}
                                                    </td>
                                                    @foreach($v1->parameterColumnValues as $v2)
                                                        <td>
                                                            {{$v2->column_value}}
                                                        </td>
                                                    @endforeach
                                                </tr> 
                                            @endif
                                            @if($v1->parameter_col_id==0)
                                                <td>Qty.</td>
                                                @foreach($v1->parameterColumnValues as $v2)
                                                    <td>
                                                        {{$v2->column_value}}
                                                    </td>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </table>
                                    @php $qty_total = $qty_total + $v->qty; @endphp
                                    
                                @endforeach
                              </td>
                              <!-- <td style="text-align: right;">{{$qty_total}}</td> -->
                              <td style="text-align: right;">
                                 <?php 
                                 echo $value->total;
                                 
                                 $tot_amt = $tot_amt + $value->total; ?>
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
</script>
@endsection