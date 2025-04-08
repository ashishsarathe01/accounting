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
                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Balance Sheet</li>
                    </ol>
                </nav>
                
                <div
                    class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="master-table-title m-0 py-2">
                        Balance Sheet
                    </h5>
                </div>
                <div class="row display-profit-loss  p-0 m-0 font-heading border-divider shadow-sm rounded-bottom-8">
                    <!-- for debit -->
                    <div class="col-md-6  font-14 p-0 border-bottom-divider">
                        <div class="row p-0 m-0">
                            <div class="col-md-12 fw-bold font-14 d-flex px-3 py-12 border-bottom-divider">
                                Liabilities (Rs.)
                                <span class="ms-auto">2,00,000</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                                Profit/Loss Adjusted
                                <span class="ms-auto">-40,000</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                                <a class="text-decoration-none text-primary fw-500" href="{{ route('currentliabilities.index') }}">Current Liabilities</a>  
                                <span class="ms-auto">1,20,000</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                                Loan Liability
                                <span class="ms-auto">-28,000</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                                Expenses (Indirect/Admn.)
                                <span class="ms-auto">1,50,000</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6  font-14 p-0 border-left-divider  border-bottom-divider">
                        <div class="row p-0 m-0">
                            <div class="col-md-12 fw-bold font-14 d-flex px-3 py-12 border-bottom-divider">
                                Assets (Rs.)
                                <span class="ms-auto">4,00,000</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                                Current Assets
                                <span class="ms-auto">3,00,000</span>
                            </div>
                            <div class="col-md-12 fw-500 font-14 d-flex px-3 py-12 border-bottom-divider">
                                Fixed Assets
                                <span class="ms-auto">00.00</span>
                            </div>
                        </div>
                    </div>
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
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button  type="submit" class="ms-3 btn btn-red">DELETE</button>
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