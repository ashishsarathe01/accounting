@extends('layouts.app')

@section('content')

<section class="position-relative bg-body bg-img py-80">
    <div class="d-flex align-items-center">
        <div class="mx-auto card login-card p-4">

        <img class="d-flex mx-auto mb-4"
             src="{{ asset('public/assets/imgs/meri-logo.svg') }}"
             alt="" />

        <div class="text-center mb-4">
            <h1>Create Password</h1>

            <p class="font-14">
                Welcome {{ $user->name }}, please create your password to access your account.
            </p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST"
              action="{{ route('user.save.password') }}">

            @csrf

            <input type="hidden"
                   name="token"
                   value="{{ $token }}">

            <div class="mb-4">
                <label class="form-label font-14 heading-color">
                    Password
                </label>

                <input type="password"
                       class="form-control"
                       name="password"
                       required
                       placeholder="Enter Password">
            </div>

            <div class="mb-4">
                <label class="form-label font-14 heading-color">
                    Confirm Password
                </label>

                <input type="password"
                       class="form-control"
                       name="password_confirmation"
                       required
                       placeholder="Confirm Password">
            </div>

            <button type="submit"
                    class="btn btn-primary w-100 mb-4">
                CREATE PASSWORD
            </button>

            <div class="text-center">
                <a href="{{ route('password.login') }}"
                   class="btn btn-link-primary">
                    BACK TO LOGIN
                </a>
            </div>

        </form>

    </div>
</div>

</section>
@endsection
