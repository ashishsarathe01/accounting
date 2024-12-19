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
                <!-- director-details------------------------------>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius">
                    Bank Account Information
                </h5>
                <form class="bg-white px-4 py-3" method="POST" action="{{ route('submit-add-bank') }}">
                    @csrf
                    <input type="hidden" name="company_id" id="company_id" value="{{$company_id}}">
                    <input type="hidden" name="business_type" id="business_type" value="{{$business_type}}">
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="account_name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" placeholder="Enter Name " required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">A/C no.</label>
                            <input type="text" class="form-control" id="account_no" name="account_no" placeholder="Enter A/C no." required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">IFSC Code</label>
                            <input type="text" class="form-control" id="ifsc" name="ifsc" placeholder="Enter IFSC code " required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Bank Name</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Enter bank name " required />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Branch</label>
                            <input type="text" class="form-control" id="branch" name="branch" placeholder="Enter branch name" required />
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button type="submit" class="btn  btn-small-primary mb-4">
                            SAVE
                        </button>
                        <!--<a href="#" class="btn btn-secondary mb-4">
                            ADD
                            <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                            </svg>
                        </a>-->
                    </div>
                </form>
                    <div class="overflow-auto ">
                        <table class="table  mb-4 table-bordered">
                            <thead>
                                <tr class="font-12 text-body bg-light-pink title-border-redius">
                                    <th class="w-120">A/C No.</th>
                                    <th class="w-120">IFSC Code </th>
                                    <th class="w-120">Bank Name</th>
                                    <th class="w-120">Branch</th>
                                    <th class="w-120">Select for Display in Invoice</th>
                                    <th class="w-120 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                    foreach ($bank_data as $value) { ?>
                                <tr class="font-14 font-heading bg-white">
                                    <td class="w-120"><?php echo $value->account_no?></td>
                                    <td class="w-120"><?php echo $value->ifsc?></td>
                                    <td class="w-120"><?php echo $value->bank_name?></td>
                                    <td class="w-120"><?php echo $value->branch?></td>
                                    <td class="w-120"><?php echo $value->branch?></td>
                                    <td class="text-center">
                                        <img src="public/assets/imgs/eye-icon.svg" class="px-1" alt="">
                                        <img src="public/assets/imgs/edit-icon.svg" class="px-1" alt="">
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('add-shareholder') }}" class="btn btn-white mb-4">
                            Previous
                        </a>
                        <!--<input type="submit" value="NEXT" class="btn btn-heading mb-4">-->
                         <a href="{{ route('view-company') }}" class="btn btn-heading mb-4">
                            SUBMIT
                        </a>
                    </div>
            </div>
        </div>
    </section>
</div>
</body>

<!-- <script src="public/public/assets/js/vendors/bootstrap.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</html>
@endsection