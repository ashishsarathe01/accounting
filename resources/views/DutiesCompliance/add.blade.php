@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- PAGE TITLE --}}
    <div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3 d-flex justify-content-between align-items-center mt-3">

        <h5 class="transaction-table-title m-0">
            GST Return Compliance
        </h5>

    </div>


    {{-- FORM CARD --}}
    <div class="bg-white shadow-sm p-4">

        <form method="POST"
              action="{{ route('gst-return-compliance.store') }}">

            @csrf

            <div class="row">

                {{-- GST NUMBER --}}
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-bold">
                        GST Number
                    </label>

                    <select name="gst_number"
                            class="form-select"
                            required>

                        <option value="">
                            Select GST Number
                        </option>

                        @foreach($gst as $row)

                            <option value="{{ $row->gst_no }}"
                                {{ old('gst_number') == $row->gst_no ? 'selected' : '' }}>
                                {{ $row->gst_no }}
                            </option>

                        @endforeach

                    </select>

                </div>


                {{-- MONTH --}}
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-bold">
                        Month
                    </label>

                    <input type="month"
                        name="month_year"
                        class="form-control"
                        min="{{ $fy_start_month }}"
                        max="{{ $fy_end_month }}"
                        value="{{ old('month_year') }}"
                        required>

                </div>


                {{-- RETURN TYPE --}}
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-bold">
                        Return Type
                    </label>

                    <select name="return_type"
                            class="form-select"
                            required>

                        <option value="">
                            Select Return Type
                        </option>

                        <option value="GSTR-1">
                            GSTR-1
                        </option>

                        <option value="GSTR-3B">
                            GSTR-3B
                        </option>

                    </select>

                </div>


                {{-- ARN NUMBER --}}
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-bold">
                        ARN Number
                    </label>

                    <input type="text"
                        name="arn_number"
                        id="arn_number"
                        maxlength="15"
                        class="form-control"
                        required>

                    <small id="arn_error"></small>

                </div>


                {{-- LOCK STATUS --}}
                <div class="col-md-6 mb-4">

                    <label class="form-label fw-bold">
                        Lock Status
                    </label>

                    <select name="is_locked"
                            class="form-select"
                            required>

                        <option value="1">
                            Enable Lock
                        </option>

                        <option value="0">
                            Disable Lock
                        </option>

                    </select>

                </div>

            </div>


            <div class="mt-3">

                <button type="submit"
                        class="btn btn-primary">

                    Save Compliance

                </button>

                <a href="{{ route('gst-return-compliance.index') }}"
                   class="btn btn-secondary">

                    Back

                </a>

            </div>

        </form>

    </div>

</div>

</div>

</section>

</div>
@include('layouts.footer')
<script>
    $(document).on('blur', '#arn_number', function () {

    let arnNumber = $(this).val();

    if (arnNumber.length != 15) {
        $('#arn_error')
            .html('ARN Number must be 15 characters.')
            .css('color', 'red');
        return;
    }

    $.ajax({
        url: "{{ route('gst-return-compliance.check-arn') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            arn_number: arnNumber
        },
        success: function (response) {

            if (response.status == false) {

                $('#arn_error')
                    .html(response.message)
                    .css('color', 'red');

                $('#arn_number').val('').focus();

            } else {

                $('#arn_error')
                    .html(response.message)
                    .css('color', 'green');
            }
        }
    });

});
</script>
@endsection