@extends('layouts.app')

@section('content')
<section class="position-relative bg-body bg-img py-80">
    <div class="d-flex align-items-center">
        <div class="mx-auto card login-card p-4">
            <img class="d-flex mx-auto mb-4" src="public/assets/imgs/meri-logo.svg" alt="" />
            <div class="text-center mb-4">
                <h1>Login</h1>
                <p class="font-14">
                    To change password, Enter your new password
                </p>
            </div>
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
            @if (session('error'))
            <div class="alert alert-danger" role="alert"> {{session('error')}}
            </div>
            @endif
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif
            <form method="POST" action="{{ route('password.changepassword') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{$user_id}}" />
                <div class="mb-4">
                    <label for="email" class="form-label font-14 heading-color">New Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" required id="password" name="password" placeholder="Enter new password" />
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label font-14 heading-color">Confirm Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" required id="confirmpassword" name="confirmpassword" placeholder="Enter confirm password" />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 mb-4">CHANGE PASSWORD</button>
            </form>
            <div class="text-center mb-4">
                <a href="{{ route('otp.login') }}" class="btn btn-link-primary">LOG IN WITH MOBILE NUMBER</a>
            </div>
            <div class="text-center">
            <a href="{{ route('password.forgot') }}" class="btn btn-link-primary">FORGOT PASSWORD?</a>
                <span class="font-heading font-14">Don’t have an account?</span>
                <a href="{{ route('register.user') }}" class="btn btn-link-primary">CREATE ACCOUNT</a>
            </div>
            </form>
        </div>
    </div>
</section>
<!--<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('OTP Login') }}</div>
  
                <div class="card-body">
  
                    @if (session('error'))
                    <div class="alert alert-danger" role="alert"> {{session('error')}} 
                    </div>
                    @endif
  
                    <form method="POST" action="">
                        @csrf
  
                        <div class="row mb-3">
                            <label for="mobile_no" class="col-md-4 col-form-label text-md-end">{{ __('Mobile No') }}</label>
  
                            <div class="col-md-6">
                                <input id="mobile_no" type="text" class="form-control @error('mobile_no') is-invalid @enderror" name="mobile_no" value="{{ old('mobile_no') }}" required autocomplete="mobile_no" autofocus placeholder="Enter Your Registered Mobile Number">
  
                                @error('mobile_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
  
                        <div class="row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Generate OTP') }}
                                </button>
  
                                @if (Route::has('login'))
                                    <a class="btn btn-link" href="{{ route('login') }}">
                                        {{ __('Login With Email') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>-->
@endsection