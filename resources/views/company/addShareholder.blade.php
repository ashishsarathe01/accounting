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
                <?php if($business_type ==3){?>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius">
                    Shareholding Pattern in Case of Company
                </h5>
                <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-add-shareholder') }}">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="company_id" id="company_id" value="{{$company_id}}">
                        <input type="hidden" name="business_type" id="business_type" value="{{$business_type}}">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="shareholders_name" name="shareholders_name" placeholder="Enter name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Father’s Name</label>
                            <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Enter father’s name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" placeholder="Select date" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Address</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter address" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">PAN</label>
                            <input type="text" class="form-control" id="pan" name="pan" placeholder="Enter PAN" required />
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
                            <input type="text" class="form-control" id="no_of_share" name="no_of_share" placeholder="Select date" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="mobile_no" name="mobile_no" placeholder="Enter mobile number" required />
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
                            <input type="email" class="form-control" id="email_id" name="email_id" placeholder="Enter email ID" required />
                        </div>
                        <!--<div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">DIN</label>
                            <input type="text" class="form-control" id="name" placeholder="Enter DIN" />
                        </div>-->
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn  btn-small-primary mb-4">
                            SAVE
                        </button>
                    </div>
                    
                    <p class="font-14 fw-bold font-heading bg-white  ">Share holding pattern</p>
                    <div class="overflow-auto ">
                        <table class="table  mb-4 table-bordered">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink title-border-redius">
                                    <th class="w-120">Name</th>
                                    <th class="w-120">Father’s Name </th>
                                    <th class="w-120">No. of Shares</th>
                                    <th class="w-120">M.O.B No.</th>
                                    <th class="w-120">Email ID</th>
                                    <th class="w-120 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($shareholder_data as $value) { ?>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-120"><?php echo $value->shareholders_name; ?></td>
                                        <td class="w-120"><?php echo $value->father_name; ?></td>
                                        <td class="w-120"><?php echo $value->no_of_share; ?></td>
                                        <td class="w-120"><?php echo $value->mobile_no; ?></td>
                                        <td class="w-120"><?php echo $value->email_id; ?></td>
                                        <td class="text-center">
                                            <img src="public/assets/imgs/eye-icon.svg" class="px-1" alt="">
                                            <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                            <img src="public/assets/imgs/round-move-up.svg" alt="">
                                        </td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                        <p class="font-14 fw-bold font-heading bg-white  ">Share holding pattern</p>
                    <div class="overflow-auto ">
                        <table class="table  mb-4 table-bordered">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink title-border-redius">
                                    <th class="w-120">Name</th>
                                    <th class="w-120">Father’s Name </th>
                                    <th class="w-120">Share Percentage</th>
                                    <th class="w-120">M.O.B No.</th>
                                    <th class="w-120">Email ID</th>
                                    <th class="w-120 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($owner_data as $value) { ?>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-120"><?php echo $value->owner_name; ?></td>
                                        <td class="w-120"><?php echo $value->father_name; ?></td>
                                        <td class="w-120"><?php echo $value->share_percentage; ?></td>
                                        <td class="w-120"><?php echo $value->mobile_no; ?></td>
                                        <td class="w-120"><?php echo $value->email_id; ?></td>
                                        <td class="text-center">
                                            <img src="public/assets/imgs/eye-icon.svg" class="px-1" alt="">
                                            <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                            <img src="public/assets/imgs/round-move-up.svg" alt="">
                                        </td>
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                    <?php } ?>
                    <div id='transfer_entries' style='display:none'>
                        <p class="font-14 fw-bold font-heading bg-white  ">Show here Transfer Entries</p>
                        <div class="overflow-auto ">
                            <table class="table table-bordered mb-4">
                                <thead>
                                    <tr class="font-12 text-body bg-light-pink title-border-redius">
                                        <th class="w-120">Date of Transfer</th>
                                        <th class="w-120">Transfer From</th>
                                        <th class="w-120">No. of shares Transferred</th>
                                        <th class="w-120">Transfer To</th>
                                        <th class="w-120 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-120">11/03/2019</td>
                                        <td class="w-120">John Doe</td>
                                        <td class="w-120">153</td>
                                        <td class="w-120">Andrew Doe</td>
                                        <td class="text-center">
                                            <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                        </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-120">11/03/2019</td>
                                        <td class="w-120">John Doe</td>
                                        <td class="w-120">153</td>
                                        <td class="w-120">Andrew Doe</td>
                                        <td class="text-center">
                                            <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('add-owner') }}" class="btn btn-white mb-4">
                            Previous
                        </a>
                        <!--<input type="submit" value="NEXT" class="btn btn-heading mb-4">-->
                        <a href="{{ route('add-bank') }}" class="btn btn-heading mb-4">
                            NEXT
                        </a>
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