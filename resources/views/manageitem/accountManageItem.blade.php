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
                 @if ($errors->any())
             <div class="alert alert-danger d-flex align-items-center" style="height: 48px;">
                @foreach ($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
                @endforeach
            </div>
            @endif
            @if(session('success'))
            <div class="alert alert-success" style="height: 48px; display: flex; align-items: center;">
                <p class="mb-0">{{ session('success') }}</p>
            </div>
            @endif

                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        List of Manage Item
                    </h5>
                    <form class="" method="Get" action="{{ route('account-manage-item.index') }}" id="filter_form">
                        @csrf
                        <div class="modal-body text-center p-0">
                        <select class="form-select form-select-lg" name="filter" id="filter" aria-label="form-select-lg example">
                                <option value="">Filter</option>
                                <option value="Enable" @if(isset($_GET['filter']) && $_GET['filter']=="Enable") selected @endif>Enable</option>
                                <option value="Disable" @if(isset($_GET['filter']) && $_GET['filter']=="Disable") selected @endif>Disable</option>
                                <option value="InComplete" @if(isset($_GET['filter']) && $_GET['filter']=="InComplete") selected @endif>InComplete</option>
                            </select>
                        </div>
                    </form>
                    @can('action-module',79)
                        <a href="{{ route('account-manage-item.create') }}" class="btn btn-xs-primary">
                            ADD
                            <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                            </svg>
                        </a>
                    @endcan
                    
                </div>
                <div class="   bg-white table-view shadow-sm">
                    <table id="example" class="table-striped table m-0 shadow-sm">
                        <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Unit </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">HSN Code </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">GST </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Opening Stock</th>                                
                                <th class="w-min-120 border-none bg-light-pink text-body ">Group </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Staus </th>
                                <th class="w-min-120 border-none bg-light-pink text-body text-center"> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($manageitems as $value) { ?>
                                <tr class="font-14 font-heading bg-white">
                                    <td class="w-min-120"><?php echo $value->name ?></td>
                                    <td class="w-min-120"><?php echo $value->unit_name ?></td>
                                    <td class="w-min-120"><?php echo $value->hsn_code ?></td>
                                    <td class="w-min-120"><?php echo $value->gst_rate ?></td>
                                    <td class="w-min-120">
                                        @if(count($value->series_open)>0)
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Series</th>
                                                        <th>Amount</th>
                                                        <th>Weight</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($value->series_open as $k1=>$v1)
                                                        <tr>
                                                            <td>{{$v1->series}}</td>
                                                            <td style="text-align:right;">{{$v1->opening_amount}}</td>
                                                            <td style="text-align:right;">{{$v1->opening_quantity}}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </td>                                    
                                    <td class="w-min-120"><?php echo $value->group_name ?></td>
                                    <td class="w-min-120">
                                        <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                                            <?php
                                            if ($value->status == 1)
                                                echo 'Enable';
                                            else
                                                echo 'Disable'; ?></span>
                                    </td>
                                    <td class="w-min-120 text-center">
                                        @can('action-module',51)
                                            <a href="{{ URL::to('account-manage-item/' . $value->id . '/edit') }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                        @endcan
                                        @can('action-module',52)
                                            @if($value->item_delete_btn_view==1)
                                                <button type="button" class="border-0 bg-transparent delete_partner" data-id="<?php echo $value->id; ?>">
                                                    <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                                </button>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>
</section>
</div>
   <!-- Modal ---for delete ---------------------------------------------------------------icon-->
   <div class="modal fade" id="delete_heading" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog w-360  modal-dialog-centered  ">
            <div class="modal-content p-4 border-divider border-radius-8">
                <div class="modal-header border-0 p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="" method="POST" action="{{ route('account-manage-item.delete') }}">
                    @csrf
                   <div class="modal-body text-center p-0">
                       <button class="border-0 bg-transparent">
                           <img class="delete-icon mb-3 d-block mx-auto" src="assets/imgs/administrator-delete-icon.svg"
                               alt="">
                       </button>
                       <h5 class="mb-3 fw-normal">Delete this record</h5>
                       <p class="font-14 text-body "> Do you really want to delete these records? this process
                           cannot be
                           undone. </p>
                   </div>
                   <input type="hidden" value="" id="heading_id" name="heading_id" />
                   <div class="modal-footer border-0 mx-auto p-0">
                       <button class="btn btn-border-body" data-bs-dismiss="modal">CANCEL</button>
                       <button class="ms-3 btn btn-red">DELETE</button>
                   </div>
                </form>
            </div>
        </div>
    </div>
</body>
@include('layouts.footer')
<script>
    $(document).ready(function() {

        $(".delete_partner").click(function() {
            var id = $(this).attr("data-id");
            $("#heading_id").val(id);
            $("#delete_heading").modal("show");
        });
        $("#filter").change(function(){
         $("#filter_form").submit();
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
</script>
@endsection