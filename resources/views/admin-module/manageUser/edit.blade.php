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
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Edit User</li>
                    </ol>
                </nav>

                {{-- Form --}}
                <div class="bg-white p-4 shadow-sm border-radius-8">
                    <h5 class="mb-4">Edit User</h5>

                    <form action="{{ route('admin.manageUser.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT') {{-- Important for update --}}

                        <div class="mb-2">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                        </div>

                        <div class="mb-2">
                            <label>Mobile</label>
                            <input type="text" name="mobile" class="form-control" value="{{ $user->mobile }}" required>
                        </div>

                        <div class="mb-2">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                        </div>

                        <div class="mb-2">
                            <label>Marital Status</label>
                            <select name="marital_status" class="form-control" required>
                                <option value="YES" {{ $user->marital_status=='YES'?'selected':'' }}>YES</option>
                                <option value="NO" {{ $user->marital_status=='NO'?'selected':'' }}>NO</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="MALE" {{ $user->gender=='MALE'?'selected':'' }}>MALE</option>
                                <option value="FEMALE" {{ $user->gender=='FEMALE'?'selected':'' }}>FEMALE</option>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="{{ $user->dob }}" required>
                        </div>

                        <div class="mb-2">
                            <label>Aadhar ID</label>
                            <input type="text" name="aadhar_id" class="form-control" value="{{ $user->aadhar_id }}" required>
                        </div>

                        <div class="mb-2">
                            <label>Attach Aadhar Card</label>
                            <input type="file" name="aadhar_image" class="form-control">
                            @if($user->aadhar_image)
                                <small>Current: {{ $user->aadhar_image }}</small>
                            @endif
                        </div>

                        <div class="mb-2">
                            <label>Present Address</label>
                            <textarea name="present_address" class="form-control" required>{{ $user->present_address }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label>Permanent Address</label>
                            <textarea name="permanent_address" class="form-control" required>{{ $user->permanent_address }}</textarea>
                        </div>

                        <div class="mb-2">
                            <label>Date of Appointment</label>
                            <input type="date" name="date_of_appointment" class="form-control" value="{{ $user->date_of_appointment }}" required>
                        </div>

                        <div class="mb-2">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" {{ $user->status==1?'selected':'' }}>Enable</option>
                                <option value="0" {{ $user->status==0?'selected':'' }}>Disable</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2">Update User</button>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@endsection
