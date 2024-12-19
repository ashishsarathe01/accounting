@extends('layouts.app')
@section('content')
<section class="position-relative bg-body bg-img py-80">
    <div class="d-flex align-items-center">
        <div class="mx-auto card creat-card p-4">
            <img class="d-flex mx-auto mb-4" src="public/assets/imgs/meri-logo.svg" alt="" />
            <div class="text-center mb-4">
                <h1>Create Account</h1>
                <p class="font-14">
                    To create your account fill up the details below
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
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="row">
                    <div class="mb-4 col-md-6">
                        <label for="name" class="form-label font-14 heading-color">Full Name *</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="name" name="name" required placeholder="Enter full name" />
                        </div>
                    </div>
                    <div class="mb-4 col-md-6">
                        <label for="email" class="form-label font-14 heading-color">Email ID</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="email" required name="email" placeholder="Enter email ID" />
                        </div>
                    </div>
                    <div class="mb-4 col-md-6">
                        <label for="mobile_no" class="form-label font-14 heading-color">Mobile Number</label>
                        <div class="position-relative">
                            <input type="text" class="form-control pl-56" id="mobile_no" required name="mobile_no" placeholder="Enter mobile number" />
                            <span class="position-absolute login-number font-14">+91</span>
                            <button type="button" class="btn btn-link-primary border-0 font-12 position-absolute verify-button" data-bs-toggle="modal" data-bs-target="#exampleModal">VERIFY</button>
                        </div>
                    </div>
                    <!--<div class="mb-4 col-md-6">
                        <label for="contact-number" class="form-label font-14 heading-color">Company Name</label>
                        <div class="position-relative">
                            <input type="text" class="form-control" id="contact-number" placeholder="Enter company name" />
                        </div>
                    </div>-->
                    <div class="mb-4 col-md-6">
                        <label for="password" class="form-label font-14 heading-color">Set Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Set password" />
                            <span class="font-12 text-body d-flex align-items-start mt-1">
                                <img class="me-2" src="public/assets/imgs/error-icon.svg" alt="" />Password requirment one caps letter,one number, one special character,8 character min</span>
                        </div>
                    </div>
                    <div class="mb-4 col-md-6">
                        <label for="confirm_password" class="form-label font-14 heading-color">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control" id="confirm_password" required name="confirm_password" placeholder="Confirm password" />
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-creat btn-primary mb-4">CREATE ACCOUNT</button>
                </div>
            </form>
            <div class="text-center">
                <span class="font-heading font-14">Already have an account?
                </span>
                <a href="{{ route('password.login') }}" class="btn btn-link-primary">LOG IN</a>
            </div>
            </form>
        </div>
    </div>
</section>
<!--<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Register') }}</div>
  
                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
  
                        <div class="row mb-3">
                            <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}</label>
  
                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
  
                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
  
                        <div class="row mb-3">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}</label>
  
                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
  
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
  
                        <div class="row mb-3">
                            <label for="mobile_no" class="col-md-4 col-form-label text-md-end">{{ __('Mobile No') }}</label>
  
                            <div class="col-md-6">
                                <input id="mobile_no" type="text" class="form-control @error('mobile_no') is-invalid @enderror" name="mobile_no" value="{{ old('mobile_no') }}" required autocomplete="mobile_no" autofocus>
  
                                @error('mobile_no')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
  
                        <div class="row mb-3">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}</label>
  
                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
  
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
  
                        <div class="row mb-3">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}</label>
  
                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>
  
                        <div class="row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>-->
@endsection