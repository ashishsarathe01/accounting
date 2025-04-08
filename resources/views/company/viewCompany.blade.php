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
                
                <div class=" title-border-redius shadow-sm position-relative  border-divider shadow-sm d-flex justify-content-between py-3  px-4 align-items-center bg-plum-viloet">
                    <h5 class="table-title-bottom-line m-0">Company Info
                    </h5>
                    <a href="{{ route('company.company-edit') }}"><img src="public/assets/imgs/edit-blue.svg" alt=""></a>
                </div>
                <!-- company info - ------------------------------------------------>
                <form class="bg-white px-4 py-3  shadow-sm border-divider rounded-bottom-8">
                    <div class="row">
                        <div class=" col-md-4">
                            <label class=" font-14 ">Business Structure</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0 m-0">
                                <?php
                                if ($company->business_type == 1) {
                                    echo 'Proprietorship';
                                } else if ($company->business_type == 2) {
                                    echo 'Partnership';
                                } else {
                                    echo 'Company (PVT LTD)';
                                } ?></p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">GST Applicable</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0"><?php
                                                                                if ($company->gst_applicable == 1) {
                                                                                    echo 'Yes';
                                                                                } else {
                                                                                    echo 'No';
                                                                                } ?></p>
                        </div>
                        <div class=" col-md-4">
                            <label class=" font-14 ">GSTN (If Yes)</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $company->gst;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Firm’s Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0"><?php echo $company->company_name; ?></p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Address</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $company->address;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label class=" font-14 ">State</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $company->state_name;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Pin Code</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $company->pin_code;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Country Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $company->country_name;}}</p>
                        </div>
                        
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">PAN</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $company->pan;}}</p>
                        </div>
                        @if($company->business_type == 3)
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">CIN</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $company->cin}}</p>
                        </div>
                        @endif
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Date of Incorporation</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">@if($company->date_of_incorporation){{ date("d-m-Y", strtotime($company->date_of_incorporation));}}@endif</p>
                        </div>
                        <div class=" col-md-4">
                            <label class=" font-14 ">Books Start From</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ date("d-m-Y", strtotime($company->books_start_from));}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Current Financial Year</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $company->current_finacial_year;}}</p>
                        </div>
                        
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Email ID</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">{{ $company->email_id;}}</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Mobile Number</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">{{ $company->mobile_no;}}</p>
                        </div>
                    </div>
                </form>
                <!-- Owner/Director’s Details------------------------------------------------------------------------------------------------------ -->
                <div class="position-relative d-flex justify-content-between py-3 mt-4 px-4 align-items-center bg-plum-viloet shadow-sm border-divider title-border-redius">
                    <h5 class="table-title-bottom-line m-0"><?php
                                if ($company->business_type == 1) {
                                    echo 'Proprietorship';
                                } else if ($company->business_type == 2) {
                                    echo 'Partnership';
                                } else {
                                    echo 'Company (PVT LTD)';
                                } ?> Details
                    </h5>
                    <!-- <img src="public/assets/imgs/edit-blue.svg" alt=""> -->
                </div>
                <div class="bg-white  py-3 px-4 shadow-sm border-divider rounded-bottom-8">
                    <!--<div class="row">
                        <div class=" col-md-4">
                            <label class=" font-14 ">Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0 m-0">John</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Father’s Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">Andrew</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Date of Birth</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">13/05/2009</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Address</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">Lorem Ipsume</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">PAN</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">98768798XX</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Designation</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">Lorem ipsujme</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Date of Joining</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">12/03/2021</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Mobile Number</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">65472874662</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Email ID</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">John@gmail.com</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">DIN</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">AFF98J </p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Authorized Signatory</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">Lorem Ipsume dolor sit</p>
                        </div>
                    </div>-->
                    <!-- <p class="font-14 fw-bold font-heading bg-white m-0 pb-3 ">Joint Partner/Director</p> -->
                    <div class="overflow-auto  bg-white table-view ">
                        <table id="examplee" class="table table-striped border-divider border-radius-8 m-0">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink title-border-redius">
                                    <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                                    <th class="w-min-120 border-none bg-light-pink text-body">Father’s Name </th>
                                    <?php
                                    if ($company->business_type != 1) { ?>

                                        <th class="w-min-120 border-none bg-light-pink text-body">Date of Joining</th>
                                    <?php } ?>
                                    <th class="w-min-120 border-none bg-light-pink text-body">M.O.B No.</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($owner_data as $value) { ?>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-min-120"><?php echo $value->owner_name ?></td>
                                        <td class="w-min-120"><?php echo $value->father_name ?></td>
                                        <?php if ($company->business_type != 1) { ?>
                                            <td class="w-min-120"><?php echo date("d-m-Y", strtotime($value->date_of_joining)); ?></td>
                                        <?php } ?>
                                        <td class="w-min-120"><?php echo $value->mobile_no ?></td>
                                        
                                    </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>
                    <?php if ($company->business_type != 1) {
                        if (!empty(json_decode($owner_delete_data, 1))) {
                            //if($owner_delete_data=='') { 
                    ?>
                            <p class="font-14 fw-bold font-heading bg-white m-0 pb-3 pt-4 ">Resigned Partner/Director</p>
                            <div class="overflow-auto  bg-white table-view ">
                                <table id="example" class="table table-striped border-divider border-radius-8 mb-4">
                                    <thead>
                                        <tr class="font-12 text-body bg-light-pink title-border-redius">
                                            <th class="w-min-120 border-none bg-light-pink text-body">Name</th>
                                            <th class="w-min-120 border-none bg-light-pink text-body">Father’s Name</th>
                                            <th class="w-min-120 border-none bg-light-pink text-body">Date of Joining</th>
                                            <th class="w-min-120 border-none bg-light-pink text-body">Date of Resigning</th>
                                            <th class="w-min-120 border-none bg-light-pink text-body">M.O.B No.</th>
                                            <!-- <th class="w-min-120 border-none bg-light-pink text-body text-center">Action</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($owner_delete_data as $value) { ?>
                                            <tr class="font-14 font-heading bg-white">
                                                <td class="w-min-120"><?php echo $value->owner_name; ?></td>
                                                <td class="w-min-120"><?php echo $value->father_name; ?></td>
                                                <td class="w-min-120"><?php echo date("d-m-Y", strtotime($value->date_of_joining)); ?></td>
                                                <td class="w-min-120"><?php echo date("d-m-Y", strtotime($value->date_of_resigning)); ?></td>
                                                <td class="w-min-120"><?php echo $value->mobile_no ?></td>
                                                <!--<td class="text-center">
                                                    <img src="public/assets/imgs/eye-icon.svg" class="px-1" alt="">
                                                    <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                                </td>-->
                                            </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>
                            </div>
                    <?php }
                    } ?>
                </div>

                <?php
                if ($company->business_type == 3) { ?>

                    <!--- Shareholding Pattern in  Case of Company----------------- -->
                    <div class=" title-border-redius  position-relative d-flex justify-content-between py-3 mt-4  px-4 align-items-center bg-plum-viloet shadow-sm border-divider title-border-redius">
                        <h5 class="table-title-bottom-line m-0">Shareholding Pattern in Case of Company
                        </h5>
                        <!-- <img src="public/assets/imgs/edit-blue.svg" alt=""> -->
                    </div>
                    <div class="bg-white px-4 py-3 shadow-sm border-divider rounded-bottom-8">
                        <!--<div class="row">
                        <div class=" col-md-4">
                            <label class=" font-14 ">Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0 m-0">John Doe</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Father’s Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">Andrew Doe</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Date of Birth</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">13/05/2009</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Address</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">Lorem Ipsume</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">PAN</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">98768798XX</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Date of Purchase</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">24/07/2019</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">No. of Share (opening)</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">156</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Mobile Number</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">65472874662</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Email ID</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">John@gmail.com</p>
                        </div>
                    </div>-->
                        <p class="font-14 fw-bold font-heading bg-white m-0 pb-3 ">Joint Entries</p>
                        <div class="overflow-auto  bg-white table-view ">
                            <table id="case" class="table  mb-4 table-striped border-divider border-radius-8">
                                <thead>
                                    <tr class="font-12 text-body bg-light-pink title-border-redius">
                                        <th class="w-min-120">Name</th>
                                        <th class="w-min-120">Father’s Name </th>
                                        <th class="w-min-120">No. of Shares</th>
                                        <th class="w-min-120">M.O.B No.</th>
                                        <th class="w-min-120">Email ID</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($shareholder_data as $value) { ?>
                                        <tr class="font-14 font-heading bg-white">
                                            <td class="w-min-120"><?php echo $value->shareholders_name; ?></td>
                                            <td class="w-min-120"><?php echo $value->father_name; ?></td>
                                            <td class="w-min-120"><?php echo $value->no_of_share ?></td>
                                            <td class="w-min-120"><?php echo $value->mobile_no ?></td>
                                            <td class="w-min-120"><?php echo $value->email_id ?></td>
                                            
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- <p class="font-14 fw-bold font-heading bg-white m-0 pb-3 pt-4">Show here Transfer Entries</p>
                        <div class="overflow-auto  bg-white table-view ">
                            <table id="casecompany" class="table table-striped border-divider border-radius-8 mb-4">
                                <thead>
                                    <tr class="font-12 text-body bg-light-pink title-border-redius">
                                        <th class="w-min-120">Date of Transfer</th>
                                        <th class="w-min-120">Transfer From</th>
                                        <th class="w-min-120">No. of shares Transferred</th>
                                        <th class="w-min-120">Transfer To</th>
                                        <th class="w-min-120 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-min-120">11/03/2019</td>
                                        <td class="w-min-120">John Doe</td>
                                        <td class="w-min-120">153</td>
                                        <td class="w-min-120">Andrew Doe</td>
                                        <td class="text-center">
                                            <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                        </td>
                                    </tr>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-min-120">11/03/2019</td>
                                        <td class="w-min-120">John Doe</td>
                                        <td class="w-min-120">153</td>
                                        <td class="w-min-120">Andrew Doe</td>
                                        <td class="text-center">
                                            <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>-->
                    </div>

                <?php } ?>
                <!--  Bank Account Information---------------------------------------------------------------------------------------------------------->
                <div class=" title-border-redius  position-relative d-flex justify-content-between py-3 mt-4  px-4 align-items-center bg-plum-viloet shadow-sm border-divider ">
                    <h5 class="table-title-bottom-line m-0">Bank Account Information
                    </h5>
                    <!-- <img src="public/assets/imgs/edit-blue.svg" alt=""> -->
                </div>
                <div class="bg-white px-4 py-3 shadow-sm border-divider rounded-bottom-8">
                    <!--<div class="row">
                        <div class=" col-md-4">
                            <label class=" font-14 ">Name</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0 m-0">John Doe</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">A/C No.</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">8765 8765 8XXX</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">IFSC Code</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">354456456</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="contact-number" class=" font-14">Bank Name</label>
                            <p class=" font-14 mb-4 fw-bold font-heading m-0">Reserve Bank of India</p>
                        </div>
                        <div class=" col-md-4">
                            <label for="name" class=" font-14">Branch</label>
                            <p class="mb-4 fw-bold font-14 font-heading m-0">New Delhi</p>
                        </div>
                    </div>-->
                    <div class="overflow-auto  bg-white table-view border-radius-4">
                        <table id="bank" class="table  mb-4 table-striped border-divider border-radius-8">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink title-border-redius">
                                    <th class="w-min-120">A/C No.</th>
                                    <th class="w-min-120">IFSC Code </th>
                                    <th class="w-min-120">Bank Name</th>
                                    <th class="w-min-120">Branch</th>
                                    <th class="w-min-120">Select for Display in Invoice</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($bank_data as $value) { ?>
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="w-min-120"><?php echo $value->account_no ?></td>
                                        <td class="w-min-120"><?php echo $value->ifsc ?></td>
                                        <td class="w-min-120"><?php echo $value->bank_name ?></td>
                                        <td class="w-min-120"><?php echo $value->branch ?></td>
                                        <td class="w-min-120"><?php echo $value->branch ?></td>
                                        
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
</body>

</html>
@endsection