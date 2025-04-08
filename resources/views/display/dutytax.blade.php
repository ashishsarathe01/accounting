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
                <div class="d-md-flex justify-content-between py-4 px-2 align-items-center">
                    <nav aria-label="breadcrumb meri-breadcrumb ">
                        <ol class="breadcrumb meri-breadcrumb m-0  ">
                            <li class="breadcrumb-item">
                                <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                                <a class="font-heading font-12  text-decoration-none" href="./display-balance-sheet.html">
                                    Balance Sheet</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                                <a class=" font-heading font-12  text-decoration-none" href="./display-liabilities.html">
                                    Current Liabilities</a>
                            </li>
                            <li class="breadcrumb-item p-0">
                                <a class="fw-bold font-heading font-12  text-decoration-none" href="#">
                                    Duties & Taxes</a>
                            </li>

                        </ol>
                    </nav>
                    <div class="d-md-flex d-block">
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
                    </div>
                </div>
                
                <div
                    class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="master-table-title m-0 py-2">
                        Duties & Taxes
                    </h5>
                </div>
                <div class="display-sale-month overflow-auto  bg-white table-view shadow-sm">
                    <table id="" class="table-striped table m-0 shadow-sm ">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Account </th>
                                <th class="w-min-120 border-none bg-light-pink text-body "></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120"><a class="text-decoration-none text-primary" href="{{ route('displaycgst.index') }}">CGST</a> </td>
                                <td class="w-min-120 text-end">12,00,000 </td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">SGST</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">IGST</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">GST Payable</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">Income Tax</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">TCS On Sale</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">TCS On Purchase</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">TDS On Rent</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">Esic</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">TDS On Commision</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">TDS On Salary</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">Esic (Employee)</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">TDS</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
                            <tr class=" font-14 font-heading bg-white">
                                <td class="w-min-120">PF</td>
                                <td class="w-min-120 text-end">12,00,000</td>
                            </tr>
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
            <form class="" method="POST" action="{{ route('account-heading.delete') }}">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="assets/imgs/administrator-delete-icon.svg" alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body "> Do you really want to delete these records? this process
                        cannot be
                        undone. </p>
                </div>
                <input type="hidden" value="" id="heading_id" name="heading_id" />
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

        $(".delete").click(function() {
            var id = $(this).attr("data-id");
            $("#heading_id").val(id);
            $("#delete_heading").modal("show");
        });

        $(".cancel").click(function() {

            $("#delete_heading").modal("hide");
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