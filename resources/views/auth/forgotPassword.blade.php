@extends('layouts.app')
@section('content')
<section class="position-relative bg-body bg-img py-80">
    <div class="d-flex align-items-center">
        <div class="mx-auto card login-card p-4">
            <img class="d-flex mx-auto mb-4" src="public/assets/imgs/meri-logo.svg" alt="" />
            <div class="text-center mb-4">
                <h1>Login</h1>
                <p class="font-14">
                    To reset your password, Enter your registered mobile number to
                    receive an OTP
                </p>
            </div>
            @if (session('error'))
            <div class="alert alert-danger" role="alert"> {{session('error')}}
            </div>
            @endif
            <form method="POST" action="{{ route('forgot.otp') }}">
                @csrf
                <div class="mb-4">
                    <label for="contact-number" class="form-label font-14 heading-color">Mobile Number</label>
                    <div class="position-relative">
                        <input id="mobile_no" type="text" pattern="\d*" maxlength="10" class="form-control pl-56 @error('mobile_no') is-invalid @enderror" name="mobile_no" value="{{ old('mobile_no') }}" required autocomplete="mobile_no" autofocus placeholder="Enter Your Registered Mobile Number">
                        <span class="position-absolute login-number font-14">+91</span>
                    </div>
                    @error('mobile_no')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-4">RESET PASSWORD</button>
            </form>
            <div class="text-center mb-4">
                <a href="{{ route('password.login') }}" class="btn btn-link-primary">LOG IN WITH PASSWORD</a>
            </div>
            <div class="text-center">
                <span class="font-heading font-14">Don’t have an account?</span>
                <a href="{{ route('register.user') }}" class="btn btn-link-primary">CREATE ACCOUNT</a>
            </div>
            </form>
        </div>
    </div>
</section>
@endsection