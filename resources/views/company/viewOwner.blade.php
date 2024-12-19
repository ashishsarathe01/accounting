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
                    <ol class="breadcrumb meri-breadcrumb m-0 py-4 px-2  ">
                        <li class="breadcrumb-item">
                            <a class="font-12 text-body text-decoration-none" href="#">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="fw-bold font-heading font-12  text-decoration-none" href="#">View
                                Owner</a>
                        </li>
                    </ol>
                </nav>
                <div class=" title-border-redius shadow-sm position-relative  border-divider shadow-sm d-flex justify-content-between py-3  px-4 align-items-center bg-plum-viloet">
                    <h5 class="table-title-bottom-line m-0">Owner/Director’s Details Info
                    </h5>
                </div>
                <!-- company info - ------------------------------------------------>
                <form class="bg-white px-4 py-3  shadow-sm border-divider rounded-bottom-8">
                    <div class="row">
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0"><?php echo $owner_data->owner_name; ?></p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Father’s Name</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $owner_data->father_name;}}
                        </div>
                        <div class=" col-md-4">
                            <label class=" font-14 ">Date of Birth</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ date("d-m-Y", strtotime($owner_data->date_of_birth));}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Address</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $owner_data->address;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">PAN</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $owner_data->pan;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Designation</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $owner_data->designation;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label class=" font-14 ">Date of Joining</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ date("d-m-Y", strtotime($owner_data->date_of_joining));}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Mobile Number</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $owner_data->mobile_no;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Email ID</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $owner_data->email_id;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">DIN</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $owner_data->din;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label class=" font-14 ">Authorized Signatory</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0"><?php
                                 if($owner_data->authorized_signatory == 1){
                                    echo 'Yes';
                                 } else {
                                    echo 'No';
                                 }?></p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
</body>


</html>
@endsection