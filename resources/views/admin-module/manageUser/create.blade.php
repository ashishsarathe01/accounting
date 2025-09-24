@extends('admin-module.layouts.app')
@section('content')
@include('admin-module.layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Flash messages --}}
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif

                {{-- Breadcrumb --}}
                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Add User</li>
                    </ol>
                </nav>

                {{-- Form Card --}}
                <div class="bg-white p-4 shadow-sm border-radius-8">
                    <form action="{{ route('admin.manageUser.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Mobile</label>
                                <input type="text" name="mobile" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Marital Status</label>
                                <select name="marital_status" class="form-control" required>
                                    <option value="" selected disabled>Select Marital Status</option>
                                    <option value="YES">YES</option>
                                    <option value="NO">NO</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Gender</label>
                                <select name="gender" class="form-control" required>
                                    <option value="" selected disabled>Select Gender</option>
                                    <option value="MALE">MALE</option>
                                    <option value="FEMALE">FEMALE</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Aadhar ID</label>
                                <input type="text" name="aadhar_id" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Attach Aadhar Card</label>
                                <input type="file" name="aadhar_image" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date of Appointment</label>
                                <input type="date" name="date_of_appointment" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label>Present Address</label>
                                <textarea name="present_address" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3 d-flex align-items-center">
                                <label class="me-2 mb-0">Permanent Address</label>
                                <input type="checkbox" id="same_as_present" class="form-check-input me-2">
                                <label for="same_as_present" class="mb-0">Same as Present Address</label>
                            </div>
                            <textarea name="permanent_address" id="permanent_address" class="form-control mb-2" rows="2" required></textarea>
                            <div class="col-md-6 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control" required>
                                    <option value="" selected disabled>Select Status</option>
                                    <option value="1">Enable</option>
                                    <option value="0">Disable</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Save User</button>
                            <a href="{{ route('admin.manageUser.index') }}" class="btn btn-dark ms-2">Quit</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
    // Copy Present Address to Permanent Address
    document.getElementById('same_as_present').addEventListener('change', function() {
        if(this.checked){
            document.getElementById('permanent_address').value = document.querySelector('textarea[name="present_address"]').value;
        } else {
            document.getElementById('permanent_address').value = '';
        }
    });
</script>
@endsection
