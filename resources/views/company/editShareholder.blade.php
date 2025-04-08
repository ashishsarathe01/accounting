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
                            <a class="fw-bold font-heading font-12 text-decoration-none" href="#">Add Company</a>
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
                    Shareholding Pattern in Case of Company
                </h5>
                <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-edit-shareholder.update') }}">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="shareholder_id" id="shareholder_id" value="{{$shareholder_data->id}}">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="shareholders_name" value="{{$shareholder_data->shareholders_name}}" name="shareholders_name" placeholder="Enter name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Father’s Name</label>
                            <input type="text" class="form-control" id="father_name" value="{{$shareholder_data->father_name}}"  name="father_name" placeholder="Enter father’s name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" value="{{$shareholder_data->date_of_birth}}" name="date_of_birth" placeholder="Select date" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Address</label>
                            <input type="text" class="form-control" id="address" value="{{$shareholder_data->address}}" name="address" placeholder="Enter address" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">PAN</label>
                            <input type="text" class="form-control" id="pan" name="pan" value="{{$shareholder_data->pan}}" placeholder="Enter PAN" required />
                        </div>
                        <!--<div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">Designation</label>
                            <select class="form-select" id="country" name="country" required>
                                <option value="select">Select</option>
                                <option value="option1">option1</option>
                                <option value="option2">option2</option>
                            </select>
                        </div>-->
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">No. of Share (Opening)</label>
                            <input type="text" class="form-control" id="no_of_share" name="no_of_share" value="{{$shareholder_data->no_of_share}}" placeholder="Select date" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="mobile_no" value="{{$shareholder_data->mobile_no}}" name="mobile_no" placeholder="Enter mobile number" required />
                                <!--<span class="position-absolute number-divider font-14">+91</span>
                                <button type="button"
                                    class="btn btn-link-primary border-0 font-12 position-absolute verify-button"
                                    data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    VERIFY
                                </button>-->
                            </div>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Email ID</label>
                            <input type="email" class="form-control" id="email_id" name="email_id" value="{{$shareholder_data->email_id}}" placeholder="Enter email ID" required />
                        </div>
                        <!--<div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">DIN</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter DIN" />
                        </div>-->
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn  btn-small-primary mb-4">
                            UPDATE
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>
</body>

@include('layouts.footer')
<script>
    $(document).ready(function() {

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
    });
</script>
@endsection