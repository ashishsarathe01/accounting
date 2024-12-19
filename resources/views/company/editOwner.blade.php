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
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb m-0 py-4 px-2  ">
                        <li class="breadcrumb-item">
                            <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="fw-bold font-heading font-12 text-decoration-none" href="#">Owner/Director’s Details</a>
                        </li>
                    </ol>
                </nav>
                <!-- Display validation errors -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <!-- director-details--------------------------------------------------------------------- -->
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius">
                    Owner/Director’s
                    Details
                </h5>
                <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-edit-owner.update') }}">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="owner_id" id="owner_id" value="{{$owner_data->id}}">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="owner_name" value="{{$owner_data->owner_name}}" name="owner_name" placeholder="Enter name" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Father’s Name</label>
                            <input type="text" class="form-control" id="father_name" value="{{$owner_data->father_name}}" name="father_name" placeholder="Enter father’s name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" value="{{$owner_data->date_of_birth}}" name="date_of_birth" placeholder="Select date" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Address</label>
                            <input type="text" class="form-control" id="address" value="{{$owner_data->address}}" name="address" placeholder="Enter address" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">PAN</label>
                            <input type="text" class="form-control" id="pan" value="{{$owner_data->pan}}" name="pan" placeholder="Enter PAN" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">Designation</label>
                            <select class="form-select" id="designation" name="designation" required>
                                <option value="">Select</option>
                                <option value="proprietor">Proprietor</option>
                                <option value="partner">Partner</option>
                                <option value="director">Director</option>
                                <option value="authorised_signatory">Authorised Signatory</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4" id="dateofjoing_section">
                            <label for="name" class="form-label font-14 font-heading">Date of Joining</label>
                            <input type="date" class="form-control" value="{{$owner_data->date_of_joining}}" id="date_of_joining" name="date_of_joining" placeholder="Select date" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" value="{{$owner_data->mobile_no}}" id="mobile_no" name="mobile_no" placeholder="Enter mobile number" />
                                <!-- <span class="position-absolute number-divider font-14">+91</span>
                                <button type="button"
                                    class="btn btn-link-primary border-0 font-12 position-absolute verify-button"
                                    data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    VERIFY
                                </button>-->
                            </div>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Email ID</label>
                            <input type="email" class="form-control" value="{{$owner_data->email_id}}" id="email_id" name="email_id" placeholder="Enter email ID" required />
                        </div>
                        <div class="mb-4 col-md-4" id="din_sectioon">
                            <label for="name" class="form-label font-14 font-heading">DIN</label>
                            <input type="text" class="form-control" value="{{$owner_data->din}}" id="din" name="din" placeholder="Enter DIN" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Authorized Signatory</label>
                            <select class="form-select form-select-lg mb-3" name="authorized_signatory" id="authorized_signatory" aria-label="form-select-lg example">
                                <option selected>Select </option>
                                <option <?php echo $owner_data->authorized_signatory == '1' ? 'selected' : ''; ?> value="1">Yes</option>
                                <option <?php echo $owner_data->authorized_signatory == '0' ? 'selected' : ''; ?> value="0">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4" id="share_per_div">
                            <label class="form-label font-14 font-heading">Share Percentage</label>
                            <input class="form-control form-select-lg mb-3" name="share_percentage" id="share_percentage" aria-label="form-select-lg example">
                        </div>
                    </div>
                    <div class="text-start">
                        <input type="submit" value="UPDATE" class="btn  btn-small-primary mb-4">
                        <!--<button  class="btn  btn-small-primary mb-4">
                            SAVE
                        </button>-->
                    </div>

                    <!--<div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('add-company') }}" class="btn btn-white mb-4">
                            Previous
                        </a>
                    <input type="submit" value="NEXT" class="btn btn-heading mb-4">
                        <a href="{{ route('add-shareholder') }}" class="btn btn-heading mb-4">
                            NEXT
                        </a>
                    </div>-->
                </form>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="resigningpopup" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content w-460">
            <div class="modal-header py-12">
                <h5 class="modal-title" id="exampleModalLabel">Resigned Partner/Director</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-delete-owner') }}">
                @csrf
                <input type="hidden" name="del_id" id="del_id" value="">
                <div class="modal-body p-4">
                    <div class=" position-relative">
                        <label for="name" class="form-label font-14 font-heading">Date of Resigning</label>
                        <input type="date" id="date_of_resigning" name="date_of_resigning" class="form-control" placeholder="Select date">
                        <!--<img class="position-absolute calendar-top-right-icon" src="public/assets/imgs/calendar.svg"
                        alt="">-->
                    </div>
                </div>
                <div class="modal-footer p-3">
                    <button type="submit" class="btn btn-red w-100">DELETE</button>
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
            $("#del_id").val(id);
            $("#resigningpopup").modal("show");
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