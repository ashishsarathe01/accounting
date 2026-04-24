@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 mb-4">
                    <h5 class="m-0">Sale Order – Additional Info</h5>

                    @if(!($editMode ?? false))
                        <a href="{{ route('sale-order.info.edit') }}" class="btn btn-xs-primary">Edit</a>
                    @endif
                </div>

                <form action="{{ route('sale-order.info.store') }}" method="POST">
                    @csrf
                    {{-- ================= OWN VEHICLES ================= --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                            <span>Own Vehicles</span>

                            @if($editMode ?? false)
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addVehicleRow">
                                    + Add Vehicle
                                </button>
                            @endif
                        </div>

                        <div class="card-body">
                            <table class="table table-bordered" id="vehicleTable">
                                <thead>
                                    <tr>
                                        <th style="width:90%">Vehicle No</th>
                                        @if($editMode ?? false)
                                            <th style="width:10%">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vehicles as $v)
                                        <tr>
                                            <td>
                                                <input type="text"
                                                    name="vehicles[{{ $v->id }}]"
                                                    value="{{ $v->vehicle_no }}"
                                                    class="form-control"
                                                    {{ ($editMode ?? false) ? '' : 'readonly' }}>
                                            </td>

                                            @if($editMode ?? false)
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger removeVehicleRow">
                                                        ❌
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        @if($editMode ?? false)
                                            <tr>
                                                <td colspan="2" class="text-center text-muted">
                                                    No vehicles added
                                                </td>
                                            </tr>
                                        @endif
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- ================= TRANSPORTERS ================= --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                            <span>Transporters</span>

                            @if($editMode ?? false)
                                <button type="button" class="btn btn-sm btn-outline-primary" id="addTransporterRow">
                                    + Add Transporter
                                </button>
                            @endif
                        </div>

                        <div class="card-body">
                            <table class="table table-bordered" id="transporterTable">
                                <thead>
                                    <tr>
                                        <th style="width:90%">Transporter Account</th>
                                        @if($editMode ?? false)
                                            <th style="width:10%">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedTransporters as $accId)
                                        <tr>
                                            <td>
                                                <select name="transporters[]" class="form-select select2-single transporter-select"
                                                    {{ ($editMode ?? false) ? '' : 'disabled' }}>
                                                    <option value="">Select Account</option>
                                                    @foreach($transporterAccounts as $acc)
                                                        <option value="{{ $acc->id }}"
                                                            {{ $acc->id == $accId ? 'selected' : '' }}>
                                                            {{ $acc->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>

                                            @if($editMode ?? false)
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger removeTransporterRow">❌</button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach

                                    @if($editMode && count($selectedTransporters) == 0)
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">
                                                No transporters added
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- ================= BILL SUNDRY ================= --}}
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header fw-bold">
                            Bill Sundry/ Expense Account
                        </div>

                        <div class="card-body">
                            <div class="row mb-3">

    {{-- Bill Sundry --}}
    <div class="col-md-4">
        <label class="form-label small fw-bold">Bill Sundry</label>

        <select name="bill_sundry_id"
                class="form-select form-select-sm select2-single"
                {{ ($editMode ?? false) ? '' : 'disabled' }}>

            <option value="">Select Bill Sundry</option>

            @foreach($billsundry as $value)
                @if(
                    $value->nature_of_sundry != 'CGST' &&
                    $value->nature_of_sundry != 'SGST' &&
                    $value->nature_of_sundry != 'IGST' &&
                    $value->nature_of_sundry != 'ROUNDED OFF (+)' &&
                    $value->nature_of_sundry != 'ROUNDED OFF (-)'
                )
                    <option value="{{ $value->id }}"
                        {{ ($selectedBillSundry == $value->id) ? 'selected' : '' }}>
                        {{ $value->name }}
                    </option>
                @endif
            @endforeach

        </select>
    </div>

    {{-- Expense Account --}}
    <div class="col-md-4">
        <label class="form-label small fw-bold">Expense Account</label>

        <select name="expense_account_id"
                class="form-select form-select-sm select2-single"
                {{ ($editMode ?? false) ? '' : 'disabled' }}>

            <option value="">Select Expense Account</option>

            @foreach($expenseAccounts as $acc)
                <option value="{{ $acc->id }}"
                    {{ ($selectedExpenseAccount == $acc->id) ? 'selected' : '' }}>
                    {{ $acc->account_name }}
                </option>
            @endforeach

        </select>
    </div>

</div>
                        </div>
                    </div>

                    {{-- Sections will come here --}}

                    @if($editMode ?? false)
                        <button type="submit" class="btn btn-primary mt-3">Save</button>
                    @endif
                </form>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@if($editMode ?? false)
<script>
document.addEventListener('DOMContentLoaded', function () {

    const vehicleTable = document.querySelector('#vehicleTable tbody');
    const addBtn = document.getElementById('addVehicleRow');

    addBtn.addEventListener('click', function () {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <input type="text" name="vehicles[new][]" class="form-control" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger removeVehicleRow">❌</button>
            </td>
        `;

        vehicleTable.appendChild(row);
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeVehicleRow')) {
            e.target.closest('tr').remove();
        }
    });

});
</script>
@endif
@if($editMode ?? false)
<script>
document.addEventListener('DOMContentLoaded', function () {

    function initSelect2() {
        $('.select2-single').select2({
            width: '100%',
            placeholder: 'Select Account',
            allowClear: true
        });
    }

    // init on page load
    initSelect2();

    const transporterTable = document.querySelector('#transporterTable tbody');
    const addBtn = document.getElementById('addTransporterRow');

    addBtn.addEventListener('click', function () {
        const row = document.createElement('tr');

        row.innerHTML = `
            <td>
                <select name="transporters[]" 
                        class="form-control select2-single transporter-select">
                    <option value="">Select Account</option>
                    @foreach($transporterAccounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger removeTransporterRow">❌</button>
            </td>
        `;

        transporterTable.appendChild(row);

        // 🔥 Re-initialize select2 for new dropdown
        initSelect2();
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('removeTransporterRow')) {
            let row = e.target.closest('tr');
            $(row).find('.select2-single').select2('destroy');
            row.remove();
        }
    });

});
</script>
@endif

@endsection
