@extends('layouts.app')

@section('content')
@include('layouts.header')
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    padding: 4px 10px;
}
.select2-container {
    width: 100% !important;
}
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if (session('error'))
                    <div class="alert alert-danger mt-3">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Payroll Statutory Settings
                </h5>

                <div class="card mt-4 shadow-sm">
                    <div class="card-body">

                        <form method="POST" action="{{ route('payroll.settings.save') }}">
                            @csrf

                            {{-- ================= PF Section ================= --}}
                            <div class="row mb-4 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Do you want to have PF account?</label>
                                    <select name="has_pf" id="has_pf" class="form-select">
                                        <option value="0">No</option>
                                        <option value="1"
                                            {{ isset($settings['pf']) && $settings['pf']->status == 1 ? 'selected' : '' }}>
                                            Yes
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Select PF Account</label>
                                    <select name="pf_account_id" id="pf_account_id" class="form-select select2-single">
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ isset($settings['pf']) && $settings['pf']->account_id == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('pf_account_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>


                            {{-- ================= ESIC Section ================= --}}
                            <div class="row mb-4 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Do you want to have ESIC account?</label>
                                    <select name="has_esic" id="has_esic" class="form-select">
                                        <option value="0">No</option>
                                        <option value="1"
                                            {{ isset($settings['esic']) && $settings['esic']->status == 1 ? 'selected' : '' }}>
                                            Yes
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Select ESIC Account</label>
                                    <select name="esic_account_id" id="esic_account_id" class="form-select select2-single">
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ isset($settings['esic']) && $settings['esic']->account_id == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('esic_account_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            {{-- ================= TDS Section ================= --}}
                            <div class="row mb-4 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Do you want to have TDS account?</label>
                                    <select name="has_tds" id="has_tds" class="form-select">
                                        <option value="0">No</option>
                                        <option value="1"
                                            {{ isset($settings['tds']) && $settings['tds']->status == 1 ? 'selected' : '' }}>
                                            Yes
                                        </option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Select TDS Account</label>
                                    <select name="tds_account_id" id="tds_account_id" class="form-select select2-single">
                                        <option value="">Select Account</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}"
                                                {{ isset($settings['tds']) && $settings['tds']->account_id == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('tds_account_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary px-4">
                                    Save Settings
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

{{-- ================= JavaScript ================= --}}
<script>
$(document).ready(function () {

    // Initialize Select2
    $('#pf_account_id, #esic_account_id, #tds_account_id').select2({
        placeholder: "Select Account",
        allowClear: true,
        width: '100%'
    });

    function togglePF() {
        let hasPF = $('#has_pf').val();

        if (hasPF === '1') {
            $('#pf_account_id').prop('disabled', false);
            $('#pf_account_id').prop('required', true);
        } else {
            $('#pf_account_id').val(null).trigger('change'); // clear select2
            $('#pf_account_id').prop('disabled', true);
            $('#pf_account_id').prop('required', false);
        }
    }

    function toggleESIC() {
        let hasESIC = $('#has_esic').val();

        if (hasESIC === '1') {
            $('#esic_account_id').prop('disabled', false);
            $('#esic_account_id').prop('required', true);
        } else {
            $('#esic_account_id').val(null).trigger('change'); // clear select2
            $('#esic_account_id').prop('disabled', true);
            $('#esic_account_id').prop('required', false);
        }
    }
    function toggleTDS() {
        let hasTDS = $('#has_tds').val();

        if (hasTDS === '1') {
            $('#tds_account_id').prop('disabled', false);
            $('#tds_account_id').prop('required', true);
        } else {
            $('#tds_account_id').val(null).trigger('change');
            $('#tds_account_id').prop('disabled', true);
            $('#tds_account_id').prop('required', false);
        }
    }
    $('#has_pf').on('change', togglePF);
    $('#has_esic').on('change', toggleESIC);
    $('#has_tds').on('change', toggleTDS);

    togglePF();
    toggleESIC();
    toggleTDS();
});
</script>


@endsection
