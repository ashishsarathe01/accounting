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
                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Item</li>
                    </ol>
                </nav>
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        List of Manage Item
                    </h5>
                    <a href="{{ route('account-manage-item.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                        </svg>
                    </a>
                </div>
                <div class="   bg-white table-view shadow-sm">
                    <table id="example" class="table-striped table m-0 shadow-sm">
                        <thead>
                        <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Unit </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">HSN Code </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">GST </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Opening Stock(Qty) </th>
                                <th class="w-min-120 border-none bg-light-pink text-body ">Opening Stock(Val) </th>
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
                                    <td class="w-min-120"><?php echo $value->u_name ?></td>
                                    <td class="w-min-120"><?php echo $value->hsn_code ?></td>
                                    <td class="w-min-120"><?php echo $value->gst_rate ?></td>
                                    <td class="w-min-120"><?php echo $value->opening_balance_qty ?></td>
                                    <td class="w-min-120"><?php echo $value->opening_balance ?></td>
                                    <td class="w-min-120"><?php echo $value->g_name ?></td>
                                    <td class="w-min-120">
                                        <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                                            <?php
                                            if ($value->status == 1)
                                                echo 'Enable';
                                            else
                                                echo 'Disable'; ?></span>
                                    </td>
                                    <td class="w-min-120 text-center">
                                        <a href="{{ URL::to('account-manage-item/' . $value->id . '/edit') }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                        <button type="button" class="border-0 bg-transparent delete_partner" data-id="<?php echo $value->id; ?>">
                                            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                        </button>
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
                <div class="modal-footer border-0 mx-auto p-0">
                    <button class="btn btn-border-body" data-bs-dismiss="modal">CANCEL</button>
                    <button class="ms-3 btn btn-red">DELETE</button>
                </div>
            </div>
        </div>
    </div>
</body>
@include('layouts.footer')
<script>
    $(document).ready(function() {

        $(".delete_partner").click(function() {
            var id = $(this).attr("data-id");
            $("#del_id").val(id);
            $("#delete_heading").modal("show");
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