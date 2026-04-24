@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Edit Spare Part
                </h5>
            
                <div class="card-body">
                    <form id="unitForm" action="{{ route('spare-part.update', $sparePart->id) }}" method="POST">
                        @csrf
                         <input type="hidden" name="form_company_id" value="{{ $formCompanyId }}">
                        <input type="hidden" name="form_company_name" value="{{ $formCompanyName }}">
                        @method('PUT')
{{-- ================= HEADER DETAILS SECTION ================= --}}
<div class="card mb-4 shadow-sm">
    <div class="card-header bg-light">
        <strong>Spare Part Requirement Details</strong>
    </div>

    <div class="card-body">
        <div class="row">

            <div class="col-md-4 mb-3">
                <label class="form-label">Department</label>
                <input type="text" class="form-control"
                       name="department"
                       value="{{ $sparePart->department }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Purpose</label>
                <input type="text" class="form-control"
                       name="purpose"
                       value="{{ $sparePart->purpose }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Department Head</label>
                <input type="text" class="form-control"
                       name="department_head"
                       value="{{ $sparePart->department_head }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Requirement By</label>
                <input type="text" class="form-control"
                       name="requirement_by"
                       value="{{ $sparePart->requirement_by }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">HOD</label>
                <input type="text" class="form-control"
                       name="hod"
                       value="{{ $sparePart->hod }}">
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Approved for Quotation</label>
                <input type="text" class="form-control"
                       name="approved_for_quotation"
                       value="{{ $sparePart->approved_for_quotation }}">
            </div>
        </div>
    </div>
</div>

                        <hr>
<h6 class="mt-4 mb-3"><strong>Spare Part Items</strong></h6>

<div id="itemRows">

@foreach($sparePart->items as $spareItem)
<div class="row item-row mb-3 align-items-end">

    {{-- ITEM --}}
    <div class="col-md-3">
        <label class="form-label">Item</label>
        <select class="form-select item-select" name="item_id[]" required>
            <option value="">Select Item</option>
            @foreach($items as $item)
                <option value="{{ $item->id }}"
                    data-unit-id="{{ $item->u_name }}"
                    {{ $item->id == $spareItem->item_id ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- UNIT --}}
    <div class="col-md-2">
        <label class="form-label">Unit</label>
        <input type="text" class="form-control unit_name"
               value="{{ $spareItem->unit }}" readonly>
        <input type="hidden" class="unit_id"
               name="unit_id[]"
               value="{{ $spareItem->unit_id ?? '' }}">
    </div>

    {{-- QUANTITY --}}
    <div class="col-md-2">
        <label class="form-label">Quantity</label>
        <input type="number" class="form-control"
               name="quantity[]"
               value="{{ $spareItem->quantity }}"
               required min="0" step="0.01">
    </div>

    {{-- REQUIRED DATE --}}
    <div class="col-md-2">
        <label class="form-label">Required Date</label>
        <input type="date" class="form-control"
               name="required_date[]"
               value="{{ $spareItem->required_date }}">
    </div>

    {{-- NARRATION --}}
    <div class="col-md-2">
        <label class="form-label">Narration</label>
        <input type="text"
               class="form-control"
               name="narration[]"
               value="{{ $spareItem->narration }}">
    </div>

    {{-- REMOVE --}}
    <div class="col-md-1 text-center">
        <button type="button" class="btn btn-danger remove-row">X</button>
    </div>

</div>
@endforeach

</div>
<div class="text-start mt-3">
    <button type="button" id="addRowBtn" class="btn btn-success">+ Add Row</button>
</div>


                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')

<script>
function applyUnitAutoFill(row) {
    let itemSelect = row.querySelector('.item-select');
    if (!itemSelect) return;

    itemSelect.addEventListener('change', function () {
        let unitId = this.options[this.selectedIndex].getAttribute('data-unit-id');
        if (unitId) {
            fetch(`{{ url('get-unit-name') }}/${unitId}`)
                .then(res => res.json())
                .then(data => {
                    row.querySelector('.unit_name').value = data.name;
                    row.querySelector('.unit_id').value = unitId;
                });
        } else {
            row.querySelector('.unit_name').value = '';
            row.querySelector('.unit_id').value = '';
        }
    });
}

// Prefilled rows remove button
document.querySelectorAll('.remove-row').forEach(btn => {
    btn.addEventListener('click', function () {
        if (document.querySelectorAll('.item-row').length > 1) {
            this.closest('.item-row').remove();
        }
    });
});

document.getElementById('addRowBtn').addEventListener('click', function () {

    let container = document.getElementById('itemRows');
    let template = document.querySelector('.item-row').cloneNode(true);

    template.querySelectorAll('input').forEach(i => i.value = '');
    template.querySelectorAll('select').forEach(s => s.value = '');

    container.appendChild(template);

    applyUnitAutoFill(template);

    template.querySelector('.remove-row').addEventListener('click', function () {
        template.remove();
    });

});

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('unitForm');
    let confirmedOnce = false;

    form.addEventListener('submit', function (e) {

        if (confirmedOnce) {
            return true; // allow submit
        }

        const formCompanyId   = form.querySelector('[name="form_company_id"]').value;
        const formCompanyName = form.querySelector('[name="form_company_name"]').value;
        const sessionCompanyId = localStorage.getItem('active_company_id');

        if (sessionCompanyId && sessionCompanyId !== formCompanyId) {

            e.preventDefault(); // ⛔ stop default submit FIRST

            const msg =
`This record belongs to "${formCompanyName}"

You have switched company in another tab.

Saving will STORE under "${formCompanyName}"

Do you want to continue?`;

            if (confirm(msg)) {
                confirmedOnce = true;
                form.submit(); 
            }

            return false;
        }
    });
});
</script>

@endsection
