@extends('admin-module.layouts.app')
@section('content')
<!-- header-section -->
@include('admin-module.layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if($errors->any())
         <div class="alert alert-danger">
            <ul>
               @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
               @endforeach
            </ul>
         </div>
         @endif
                @if (session('error'))
                <div class="alert alert-danger" role="alert"> {{session('error')}} </div>
                @endif
                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                @endif
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Change Password</h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="post" action="{{route('admin.change-password-update')}}">
                @csrf
                <div class="row">
                    <div class="mb-4 col-md-4">
                        <label for="name" class="form-label font-14 font-heading">New Password</label>
                        <input type="password" class="form-control"  id="password" name="password" placeholder="Enter New Password" required />

                    </div>                  
                </div>
                <div class="text-start">
                    <button type="submit" class="btn  btn-xs-primary ">SUBMIT</button>
                </div>
                </form>
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')

@endsection