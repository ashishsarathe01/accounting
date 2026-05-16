@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Merchant Credentials
                    </h5>
                </div>
                <div class="transaction-table bg-white table-view shadow-sm purchase_table">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm"
                          method="POST"
                          action="{{ route('merchant_credentials.store') }}">
                        @csrf
                        <div id="credential_append_div">
                            @if(count($credentials) > 0)
                                @foreach($credentials as $key => $credential)
                                    <div class="row credential_row credential_row_{{$key}} mb-3">
                                        <div class="col-md-3">
                                            <label class="form-label font-14 font-heading">
                                                HEADING / TYPE
                                            </label>
                                            <select name="credential_type[]"
                                                    class="form-select"
                                                    required>
                                                <option value="">Select</option>
                                                <option value="GST Login"
                                                    @if($credential->credential_type == 'GST Login') selected @endif>
                                                    GST Login
                                                </option>
                                                <option value="E Way Bill Login"
                                                    @if($credential->credential_type == 'E Way Bill Login') selected @endif>
                                                    E Way Bill Login
                                                </option>
                                                <option value="E Invoice Login"
                                                    @if($credential->credential_type == 'E Invoice Login') selected @endif>
                                                    E Invoice Login
                                                </option>
                                                <option value="Income Tax Login"
                                                    @if($credential->credential_type == 'Income Tax Login') selected @endif>
                                                    Income Tax Login
                                                </option>
                                                <option value="ESIC Login"
                                                    @if($credential->credential_type == 'ESIC Login') selected @endif>
                                                    ESIC Login
                                                </option>
                                                <option value="Traces Login"
                                                    @if($credential->credential_type == 'Traces Login') selected @endif>
                                                    Traces Login
                                                </option>
                                                <option value="PF Login"
                                                    @if($credential->credential_type == 'PF Login') selected @endif>
                                                    PF Login
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label font-14 font-heading">
                                                USER NAME
                                            </label>
                                            <input type="text"
                                                   name="username[]"
                                                   class="form-control"
                                                   value="{{ $credential->username }}"
                                                   required>
                                        </div>
                                        <div class="col-md-3">

                                            <label class="form-label font-14 font-heading">
                                                PASSWORD
                                            </label>

                                            <div style="position: relative;">

                                                <input type="password"
                                                    name="password[]"
                                                    class="form-control password_input pe-5"
                                                    value="{{ $credential->password }}"
                                                    required>

                                                <span class="toggle_password"
                                                    style="
                                                            position: absolute;
                                                            right: 15px;
                                                            top: 50%;
                                                            transform: translateY(-50%);
                                                            cursor: pointer;
                                                            color: #6c757d;
                                                            z-index: 10;
                                                    ">

                                                    <i class="fa fa-eye"></i>

                                                </span>

                                            </div>

                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label font-14 font-heading">
                                                ACTION
                                            </label>
                                            <div class="d-flex">
                                                <button type="button"
                                                        class="btn btn-danger remove_row me-2"
                                                        data-id="{{$key ?? 1}}">
                                                    -
                                                </button>
                                                <button type="button"
                                                        class="btn btn-primary add_row">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="row credential_row credential_row_1 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label font-14 font-heading">
                                            HEADING / TYPE
                                        </label>
                                        <select name="credential_type[]"
                                                class="form-select"
                                                required>
                                            <option value="">Select</option>
                                            <option value="GST Login">GST Login</option>
                                            <option value="E Way Bill Login">E Way Bill Login</option>
                                            <option value="E Invoice Login">E Invoice Login</option>
                                            <option value="Income Tax Login">Income Tax Login</option>
                                            <option value="ESIC Login">ESIC Login</option>
                                            <option value="Traces Login">Traces Login</option>
                                            <option value="PF Login">PF Login</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label font-14 font-heading">
                                            USER NAME
                                        </label>
                                        <input type="text"
                                               name="username[]"
                                               class="form-control"
                                               required>
                                    </div>
                                    <div class="col-md-3">

                                        <label class="form-label font-14 font-heading">
                                            PASSWORD
                                        </label>

                                        <div style="position: relative;">

                                            <input type="password"
                                                name="password[]"
                                                class="form-control password_input pe-5"
                                                required>

                                            <span class="toggle_password"
                                                style="
                                                        position: absolute;
                                                        right: 15px;
                                                        top: 50%;
                                                        transform: translateY(-50%);
                                                        cursor: pointer;
                                                        color: #6c757d;
                                                        z-index: 10;
                                                ">

                                                <i class="fa fa-eye"></i>

                                            </span>

                                        </div>

                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label font-14 font-heading">
                                            ACTION
                                        </label>
                                        <div class="d-flex">
                                            <button type="button"
                                                    class="btn btn-danger remove_row me-2"
                                                    data-id="{{$key ?? 1}}">
                                                -
                                            </button>
                                            <button type="button"
                                                    class="btn btn-primary add_row">
                                                +
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <br>
                        <div class="text-start">
                            <button type="submit"
                                    class="btn btn-xs-primary save_btn">
                                SAVE
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-1 d-none d-lg-flex justify-content-center px-1">
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')
<script>
    let credential_index = 100;
    function refreshButtons()
    {
        $(".add_row").hide();
        $(".remove_row").show();

        let total_rows = $(".credential_row").length;

        if(total_rows == 1){

            $(".remove_row").hide();
        }

        $(".credential_row:last .add_row").show();
    }

    $(document).on('click', '.add_row', function(){

        credential_index++;

        $("#credential_append_div").append(`

            <div class="row credential_row credential_row_${credential_index} mb-3">

                <div class="col-md-3">

                    <label class="form-label font-14 font-heading">
                        HEADING / TYPE
                    </label>

                    <select name="credential_type[]"
                            class="form-select"
                            required>

                        <option value="">Select</option>
                        <option value="GST Login">GST Login</option>
                        <option value="E Way Bill Login">E Way Bill Login</option>
                        <option value="E Invoice Login">E Invoice Login</option>
                        <option value="Income Tax Login">Income Tax Login</option>
                        <option value="ESIC Login">ESIC Login</option>
                        <option value="Traces Login">Traces Login</option>
                        <option value="PF Login">PF Login</option>

                    </select>

                </div>

                <div class="col-md-3">

                    <label class="form-label font-14 font-heading">
                        USER NAME
                    </label>

                    <input type="text"
                           name="username[]"
                           class="form-control"
                           required>

                </div>

                <div class="col-md-3">

                    <label class="form-label font-14 font-heading">
                        PASSWORD
                    </label>

                    <div style="position: relative;">

                        <input type="password"
                            name="password[]"
                            class="form-control password_input pe-5"
                            required>

                        <span class="toggle_password"
                            style="
                                    position: absolute;
                                    right: 15px;
                                    top: 50%;
                                    transform: translateY(-50%);
                                    cursor: pointer;
                                    color: #6c757d;
                                    z-index: 10;
                            ">

                            <i class="fa fa-eye"></i>

                        </span>

                    </div>

                </div>

                <div class="col-md-2">

                    <label class="form-label font-14 font-heading">
                        ACTION
                    </label>

                    <div class="d-flex">

                        <button type="button"
                                class="btn btn-danger remove_row me-2"
                                data-id="${credential_index}">
                            -
                        </button>

                        <button type="button"
                                class="btn btn-primary add_row">
                            +
                        </button>

                    </div>

                </div>

            </div>

        `);

        refreshButtons();

    });

    $(document).on('click', '.remove_row', function(){

        let id = $(this).attr('data-id');

        $(".credential_row_" + id).remove();

        refreshButtons();

    });
    $(document).on('click', '.toggle_password', function(){

        let input = $(this).closest('div').find('.password_input');

        if(input.attr('type') == 'password'){

            input.attr('type', 'text');

            $(this).html('<i class="fa fa-eye-slash"></i>');

        }else{

            input.attr('type', 'password');

            $(this).html('<i class="fa fa-eye"></i>');
        }

    });
    $(document).ready(function(){

        refreshButtons();

    });
</script>
@endsection