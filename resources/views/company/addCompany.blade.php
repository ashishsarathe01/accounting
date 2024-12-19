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
                <!-- company info--------------------------------------------------------------------- -->
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius">Company Info
                </h5>
                <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-add-company') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Business Structure</label>
                            <select name="business_type" id="business_type" class="form-select form-select-lg mb-3" aria-label="form-select-lg example" required>
                                <option value="">Select</option>
                                <option value="1">Proprietorship</option>
                                <option value="2">Partnership</option>
                                <option value="3">Company (PVT LTD)</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Firm’s Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" placeholder="Enter firm’s name" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">GST Applicable</label>
                            <select class="form-select" id="gst_applicable" name="gst_applicable" required>
                                <option value="">Select</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4" id="gst_filed">
                            <label for="name" class="form-label font-14 font-heading">GSTN (If Yes)</label>
                            <input type="text" class="form-control" id="gst" name="gst" placeholder="Enter GSTN" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">PAN</label>
                            <input type="text" class="form-control" id="pan" name="pan" placeholder="Enter PAN" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Date of Incorporation</label>
                            <input type="date" class="form-control" id="date_of_incorporation" name="date_of_incorporation" placeholder="Select date" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Address</label>
                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter address" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">State</label>
                            <select class="form-select" id="state" name="state" required>
                                <option value="">Select</option>
                                <?php
                                foreach ($state_list as $val) { ?>
                                <option value="<?= $val->id;?>"><?= $val->name;?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 font-heading">Country Name</label>
                            <select class="form-select" id="country_name" name="country_name" required>
                                <option value="">Select</option>
                                <option value="india">India</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Pin Code</label>
                            <input type="number" class="form-control" id="pin_code" name="pin_code" placeholder="Enter pin code" />
                        </div>
                        <div class="mb-4 col-md-4">
                           <label for="contact-number" class="form-label font-14 font-heading">Current FinancialYear</label>
                           <select class="form-select " id="current_finacial_year" name="current_finacial_year" required>
                              <option value="">Select</option>
                              <?php 
                              $y = 22;
                              while($y<=date('y')){
                                 $y1 = $y+1;
                                 ?>
                                 <option value="<?php echo $y."-".$y1;?>"><?php echo $y."-".$y1;?></option>
                                 <?php
                                 $y++;
                              }
                              ?>                              
                           </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Books start From</label>
                            <input type="date" class="form-control" id="books_start_from" name="books_start_from" placeholder="Enter books start from" />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Email ID</label>
                            <input type="email" class="form-control" id="email_id" name="email_id" placeholder="Enter email ID" required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                            <div class="position-relative">
                                <input type="text" min="10" max="10" class="form-control" id="mobile_no" name="mobile_no" placeholder="Enter mobile number" required />
                                <!--<span class="position-absolute number-divider font-14">+91</span>
                                <button type="button" class="btn btn-link-primary border-0 font-12 position-absolute verify-button" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    VERIFY
                                </button>-->
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <input type="submit" value="NEXT" class="btn btn-heading mb-4">
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')

<script type="text/javascript">
    $("#gst_applicable").on("keyup change", function(e) {
        if ($("#gst_applicable").val() == 1) {
            $("#gst_filed").show();
        } else {
            $("#gst_filed").hide();
        }
    });

    $("#gst").change(function() {
        var inputvalues = $("#gst").val();
        var gstinformat = new RegExp("^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9]{1}Z[a-zA-Z0-9]{1}$");
        if (gstinformat.test(inputvalues)) {
            var GstateCode = inputvalues.substr(0, 2); 
            //$('#state').val(GstateCode);
            $('#country_name').val('india');
            var GpanNum = inputvalues.substring(2, 12);
            $("#pan").val(GpanNum);
            var GEnd = inputvalues.substring(12,14);
            return true;
        } else {
            alert('Please Enter Valid GSTIN Number');
            $("#gst").val('');
            $("#gst").focus();
        }
    });

   /* $("#gst").change(function() {
        var gst = $("#gst").val();
        var url = "{{ route('check-gst') }}";
    $.ajax({
        url: url,
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        method: 'POST',
        data: { "gst": gst },
        //dataType: 'JSON',
        //contentType: false,
        //cache: false,
        //processData: false,
        success:function(response)
        {
            alert();
        },
        error: function(response) {
        }
    });
});  */
    $("#pin_code").change(function() {
        var inputvalues = $("#pin_code").val();
        var pincode = new RegExp("^[1-9]{1}[0-9]{2}\\s{0,1}[0-9]{3}$");
        if (pincode.test(inputvalues)) {
            return true;
        } else {
            alert('Please Enter Valid pin Number');
            $("#pin_code").val('');
            $("#pin_code").focus();
        }
    });
</script>
@endsection