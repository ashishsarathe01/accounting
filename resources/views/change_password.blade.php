@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<style>
    .password-eye {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 5;
}
</style>
<!-- list-view-company-section -->
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">            
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Change Password</h5>
                @if (session('error'))
                    <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('change-password-update') }}">
                    @csrf
                    <div class="mb-4 col-md-4">
                        <label class="form-label font-14 font-heading">New Password</label>

                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" required id="password" name="password" placeholder="Enter new password">

                            <span class="password-eye" onclick="togglePassword()">
                                <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}"
                                    width="18"
                                    alt="View">
                            </span>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="button" class="btn btn-xs-primary" data-bs-toggle="modal" data-bs-target="#changePasswordConfirm"> SUBMIT </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="changePasswordConfirm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog w-360 modal-dialog-centered">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <h5 class="mb-3 fw-normal">Change Password</h5>

                <p class="font-14 text-body">
                    Are you sure you want to change your password?
                </p>
            </div>
            <div class="modal-footer border-0 mx-auto p-0">
                <button type="button"
                        class="btn btn-border-body"
                        data-bs-dismiss="modal">
                    CANCEL
                </button>
                <button type="button"
                        class="ms-3 btn btn-red"
                        onclick="submitChangePassword()">
                    YES, CHANGE
                </button>
            </div>
        </div>
    </div>
</div>

</body>
@include('layouts.footer')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    passwordInput.type =
        passwordInput.type === 'password' ? 'text' : 'password';
}

function submitChangePassword() {
    document.querySelector('form[action="{{ route('change-password-update') }}"]').submit();
}
</script>
@endsection