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
                            <button type="button" class="btn btn-link-primary border-0 font-12 position-absolute verify-button">VERIFY</button>
                        </div>
                    </div>
                    
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
                    <button type="submit" disabled class="btn btn-creat btn-primary mb-4 create_account_btn">CREATE ACCOUNT</button>
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
<div class="modal fade" id="otp_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog w-360  modal-dialog-centered  ">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
         <h4 class="modal-title"><span id="modal_parameter_name"></span>Enter OTP</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
            <div class="modal-body text-center p-0">               
               <input type="text" class="form-control mb-4" id="otp" required name="otp" placeholder="Enter OTP" />
            </div>
            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="ms-3 btn btn-red verify_otp">Submit</button>
            </div>
      </div>
   </div>
</div>
@include('layouts.footer')
<script>
    $(document).ready(function() {
        $('.verify-button').on('click', function() {
            let mobileNumber = $('#mobile_no').val();
            if (mobileNumber.length!=10) {
                alert('Please enter a valid mobile number');
                return;
            }
            // Make an AJAX request to send the OTP
            $.ajax({
                url: "{{route('send-otp')}}", // Update with your route to send OTP
                type: 'POST',
                data: {
                    mobile_no: mobileNumber,
                    _token: '{{ csrf_token() }}' // Include CSRF token for security
                },
                success: function(res) {
                    let response = JSON.parse(res);
                    if (response.status==1) {
                        alert('OTP sent successfully!');
                        $('#otp_modal').modal('show');
                        // Optionally, you can show the OTP modal here
                    } else {
                        alert('Failed to send OTP. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    if(xhr.responseJSON.errors.mobile_no[0]){
                        alert(xhr.responseJSON.errors.mobile_no[0]);
                    }else{
                        alert('An error occurred. Please try again.');
                    }
                    
                }
            });
        });
        $('.verify_otp').on('click', function() {
            let otp = $('#otp').val();
            if (otp.length!=4) {
                alert('Please enter a valid otp');
                return;
            }
            // Make an AJAX request to send the OTP
            $.ajax({
                url: "{{route('verify-otp')}}", // Update with your route to send OTP
                type: 'POST',
                data: {
                    otp: otp,
                    _token: '{{ csrf_token() }}' // Include CSRF token for security
                },
                success: function(res) {
                    let response = JSON.parse(res);
                    if (response.status==1) {
                        alert(response.message);
                        $('.create_account_btn').removeAttr('disabled');
                        $('#otp_modal').modal('hide');
                        $(".verify-button").html("<span style='color:green'>VERIFIED</span>");
                    }else if (response.status==0) {
                        alert(response.message);
                    } else {
                        alert('Failed to send OTP. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred. Please try again.');
                }
            });
        });
        $("#mobile_no").on('input', function() {
            $(".verify-button").html("VERIFY");
            $('.create_account_btn').attr('disabled', true);
            $('#otp').val('');
            $('#otp_modal').modal('hide');
            $.ajax({
                url: "{{route('change-otp-verify-status')}}", // Update with your route to send OTP
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}' // Include CSRF token for security
                },
                success: function(res) {
                    
                }
            });
        });
    });
</script>
@endsection