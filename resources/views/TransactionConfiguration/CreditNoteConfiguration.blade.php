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


    {{-- PAGE TITLE --}}
    <div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3 d-flex justify-content-between align-items-center mt-3">

        <h5 class="transaction-table-title m-0">
            Credit Note Configuration
        </h5>

    </div>



    {{-- CONFIGURATION CARD --}}
    <div class="bg-white shadow-sm p-4">

        <form method="POST"
              action="{{ route('save.credit.note.configuration') }}">

            @csrf


            <div class="row">

                <div class="col-md-6 mb-4">

                    <label class="form-label fw-bold">

                        Link GSTR2A/GSTR2B Debit Note On Edit Page

                    </label>

                    <select
                        name="link_gstr2b_debit_note_edit"
                        class="form-select">

                        <option value="1"
                            {{ isset($config) &&
                            $config->config_value == '1'
                            ? 'selected'
                            : '' }}>
                            Yes
                        </option>

                        <option value="0"
                            {{ isset($config) &&
                            $config->config_value == '0'
                            ? 'selected'
                            : '' }}>
                            No
                        </option>

                    </select>

                </div>

            </div>



            <div class="mt-3">

                <button type="submit"
                        class="btn btn-primary">

                    Save Configuration

                </button>

            </div>

        </form>

    </div>

</div>

</div>

</section>

</div>

@endsection