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
                        <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Account Heading</li>
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
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Add Account Heading
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-heading.store') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name"  />
                        </div>
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">B/S Profile</label>
                            <select class="form-select" id="bs_profile" name="bs_profile" required>
                                <option value="">Select Profile</option>
                                <option value="1">Liabilities</option>
                                <option value="2">Assets</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">

                        </div>
                        
                        @if(Session::get('business_type')==2)
                           <div class="mb-4 col-md-4">
                               <label for="name" class="form-label font-14 font-heading">Name As Sch |||</label>
                               <input type="text" class="form-control" name="name_sch_three" id="name_sch_three" placeholder="Enter name" />
                           </div>
                           <div class="mb-4 col-md-4">
                               <label for="name" class="form-label font-14 font-heading">B/S Profile As Sch |||</label>
                               <select class="form-select" id="bs_profile_three" name="bs_profile_three">
                                   <option value="">Select Profile</option>
                                   <option value="1">Equity And Liabilities</option>
                                   <option value="2">Assets</option>
                               </select>
                           </div>
                        @endif
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                                <option value="">Select </option>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn  btn-xs-primary ">
                            SUBMIT
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>
</section>
</div>
</body>

</html>
@endsection