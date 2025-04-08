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
                  <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                  <li class="breadcrumb-item fw-bold font-heading" aria-current="page">User</li>
               </ol>
            </nav>
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
               Edit User
            </h5>
            <form id="employee_form" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('manage-merchant-employee.update',$employee->id) }}">
               @csrf
               {{ method_field('PATCH') }}
               <div class="row">
                  <div class="mb-4 col-md-4">
                     <label for="name" class="form-label font-14 font-heading">Name</label>
                     <input type="text" class="form-control" name="name" id="name" placeholder="Enter Name" value="{{$employee->name}}" />
                  </div>
                  <div class="mb-3 col-md-3">
                     <label for="mobile" class="form-label font-14 font-heading">Mobile</label>
                     <input type="text" class="form-control" name="mobile" id="mobile" placeholder="Enter Mobile"  minlength="10"  maxlength="10" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" value="{{$employee->mobile_no}}"/>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="email" class="form-label font-14 font-heading">Email</label>
                     <input type="text" class="form-control" name="email" id="email" placeholder="Enter Email" value="{{$employee->email}}"/>
                  </div>
                  <div class="mb-4 col-md-4">
                     <label for="address" class="form-label font-14 font-heading">Address</label>
                     <input type="text" class="form-control" name="address" id="address" placeholder="Enter Address" value="{{$employee->address}}"/>
                  </div>
                  <div class="mb-3 col-md-3">
                     <label class="form-label font-14 font-heading">Status</label>
                     <select class="form-select form-select-lg" name="status" aria-label="form-select-lg example" required>
                        <option value="">Select </option>
                        <option value="1" @if($employee->status==1) selected @endif>Enable</option>
                        <option value="0" @if($employee->status==0) selected @endif>Disable</option>
                     </select>
                  </div>
               </div>
               <div class="text-start">
                  <button type="submit" class="btn  btn-xs-primary" id="save_btn">
                     SUBMIT
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
      $("#save_btn").click(function(){          
         $("#employee_form").validate({
            ignore: [], 
            rules: {
               name: "required",
               mobile: {
                  required:true,
                  minlength: 10,
                  maxlength: 10
               },
               email: {
                  required: true,
                  email: true
               },
               address: "required",
               status: "required",
            },
            messages: {
               name: "Please enter name",
               mobile: "Please enter valid mobile no",
               email: "Please enter a valid email address",
               address: "Please enter address",
               status: "Please select status",             
            }
         });         
      });
   });
</script>
@endsection