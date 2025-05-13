@extends('layouts.app')
@section('content')
<section class="position-relative bg-body bg-img py-80">
    <div class="d-flex vh-100 align-items-center">
        <div class="mx-auto login-card card p-4">
            <img class="d-flex mx-auto mb-4" src="public/assets/imgs/meri-logo.svg" alt="" />
            <div class="text-center mb-4">
                <h1>OTP Verification</h1>
                <p class="font-14">Enter the OTP sent to <?php echo str_repeat("x", (strlen($mobile_no) - 4)) . substr($mobile_no, -4, 4); ?></p>
            </div>
            @if(isset($successMessage))
            <!-- Display success message -->
            <div class="alert alert-success">
                {{ $successMessage }}
            </div>
            @endif
            @if(isset($errorMessage))
            <!-- Display success message -->
            <div class="alert alert-danger">
                {{ $errorMessage }}
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger" role="alert"> {{session('error')}}
            </div>
            @endif
            <form method="POST" action="{{ route('submit.otplogin') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{$user_id}}" />
                <input type="hidden" name="mobile_no" value="{{$mobile_no}}" />
                <div class="mb-4 input-otp mx-auto">
                    <label for="contact-number" class="form-label font-14 heading-color">OTP</label>
                    <div class="d-flex align-items-center mb-4">
                        <input type="text" id="n0" class="authInput form-control me-md-4 me-2 text-center" name="otp1" maxlength="1" autocomplete="off" autofocus data-next="1">
                        <input type="text" id="n1" class="authInput form-control me-md-4 me-2 text-center" name="otp2" maxlength="1" autocomplete="off" data-next="2">
                        <input type="text" id="n2" class="authInput form-control me-md-4 me-2 text-center" name="otp3" maxlength="1" autocomplete="off" data-next="3">
                        <input type="text" id="n3" class="authInput form-control me-md-4 me-2 text-center" name="otp4" maxlength="1" autocomplete="off" data-next="4">

                        <!-- <input type="text" maxlength="1" class="authInput form-control me-md-4 me-2 text-center" id="n1" autofocus data-next="1" placeholder="-" required />
                        <input type="text" maxlength="1" class="authInput form-control me-md-4 me-2 text-center" id="n2" data-next="2" placeholder="-" required />
                        <input type="text" maxlength="1" class="authInput form-control me-md-4 me-2 text-center" id="n3" data-next="3" placeholder="-" required />
                        <input type="text" maxlength="1" class="authInput form-control me-md-4 me-2 text-center" id="n4" data-next="4" placeholder="-" required />-->
                    </div>
                </div>
                @error('otp')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
                @enderror
                <!--<div class="text-end mb-4">
                    <a href="#" class="btn btn-link-primary">RESEND OTP</a>
                </div>-->
                <button type="submit" class="btn btn-primary w-100 mb-4">CONTINUE</button>
            </form>
            <div class="text-center mb-4">
                <a href="{{ route('password.login') }}" class="btn btn-link-primary">BACK TO LOG IN</a>
            </div>
            <div class="text-center">
                <span class="font-heading font-14">Don’t have an account?</span>
                <a href="{{ route('register.user') }}" class="btn btn-link-primary">CREATE ACCOUNT</a>
            </div>
            </form>
        </div>
    </div>
</section>
@include('layouts.footer')
<script>
    $(document).ready(function() {
  $('.otp-input').on('input', function() {
    if (this.value.length === 1) {
      $(this).next('.otp-input').focus();
    }
  });

  $('.otp-input').on('keydown', function(e) {
    if (e.key === "Backspace" && this.value === '') {
      $(this).prev('.otp-input').focus();
    }
  });
});
</script>
@endsection