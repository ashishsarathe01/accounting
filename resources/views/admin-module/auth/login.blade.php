@extends('layouts.app')
@section('content')
<section class="position-relative bg-body bg-img py-80">
   <div class="d-flex align-items-center">
      <div class="mx-auto card login-card p-4">
         <img class="d-flex mx-auto mb-4" src="public/assets/imgs/meri-logo.svg" alt="" />
         <div class="text-center mb-4">
            <h1>Login</h1>
            <p class="font-14">To access your account, Enter your registered email ID and password</p>
         </div>
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
         <div class="alert alert-danger" role="alert"> {{session('error')}}
         </div>
         @endif
         @if (session('success'))
         <div class="alert alert-success" role="alert">
            {{ session('success') }}
         </div>
         @endif
         <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="mb-4">
               <label for="email" class="form-label font-14 heading-color">Email ID</label>
               <div class="position-relative">
                  <input type="email" class="form-control" required id="email" name="email" placeholder="Enter email ID" />
               </div>
            </div>
            <div class="mb-4">
               <label for="password" class="form-label font-14 heading-color">Password</label>
               <div class="position-relative">
                  <input type="password" class="form-control" required id="password" name="password" placeholder="Enter password" />
               </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6 offset-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            
                        <label class="form-check-label" for="remember">
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 mb-4">LOGIN</button>
         </form>
         <div class="text-center">
            <a href="{{ route('password.forgot') }}" class="btn btn-link-primary">FORGOT PASSWORD?</a>
         </div>
      </div>
   </div>
</section>
@endsection