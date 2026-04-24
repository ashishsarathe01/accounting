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

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Spare Part</h5>
            
        <div class="card-body">

            <form id="unitForm" action="{{ route('spare-part.store') }}" method="POST">
                @csrf
                <input type="hidden" name="form_company_id" value="{{ $formCompanyId }}">
                <input type="hidden" name="form_company_name" value="{{ $formCompanyName }}">

                {{-- ================= HEADER DETAILS SECTION ================= --}}
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-light">
                        <strong>Spare Part Requirement Details</strong>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Department </label>
                                <input type="text" class="form-control" name="department" placeholder="Department">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Purpose </label>
                                <input type="text" class="form-control" name="purpose" placeholder="Purpose">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Department Head </label>
                                <input type="text" class="form-control" name="department_head" placeholder="Department Head">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Requirement By </label>
                                <input type="text" class="form-control" name="requirement_by" placeholder="Requirement By">
                            </div>

                            {{-- <div class="col-md-4 mb-3">
                                <label class="form-label">HOD </label>
                                <input type="text" class="form-control" name="hod">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Approved for Quotation </label>
                                <input type="text" class="form-control" name="approved_for_quotation">
                            </div> --}}

                        </div>
                    </div>
                </div>
                {{-- ================= END HEADER DETAILS ================= --}}
                <hr>
                <h6 class="mt-4 mb-3"><strong>Spare Part Items</strong></h6>

                <div id="itemRows">

                @if(!empty($prefillItems) && count($prefillItems) > 0)

                    {{-- ================= PREFILLED ROWS FROM MANAGE ITEMS ================= --}}
                    @foreach($prefillItems as $row)
                    <div class="row item-row mb-3 align-items-end">

                        {{-- ITEM --}}
                        <div class="col-md-3">
                            <label class="form-label">Item</label>
                            <select class="form-select item-select" name="item_id[]" required>
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}"
                                        data-unit-id="{{ $item->u_name }}"
                                        {{ $item->id == $row['item_id'] ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- UNIT --}}
                        <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control unit_name" readonly>
                            <input type="hidden"
                                class="unit_id"
                                name="unit_id[]"
                                value="{{ $row['unit_id'] }}">
                        </div>

                        {{-- QUANTITY --}}
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input type="number"
                                class="form-control"
                                name="quantity[]"
                                value="{{ $row['quantity'] }}"
                                required
                                min="0"
                                step="0.01">
                        </div>

                        {{-- REQUIRED DATE --}}
                        <div class="col-md-2">
                            <label class="form-label">Required Date</label>
                            <input type="date"
                                class="form-control"
                                name="required_date[]">
                        </div>

                        {{-- NARRATION --}}
                        <div class="col-md-2">
                            <label class="form-label">Narration</label>
                            <input type="text"
                                class="form-control"
                                name="narration[]"
                                placeholder="Optional">
                        </div>

                        {{-- REMOVE BUTTON --}}
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-danger remove-row">X</button>
                        </div>

                    </div>
                    @endforeach

                @else

                    {{-- ================= NORMAL EMPTY ROW ================= --}}
                    <div class="row item-row mb-3 align-items-end">

                        {{-- ITEM --}}
                        <div class="col-md-3">
                            <label class="form-label">Item</label>
                            <select class="form-select item-select select2-single" name="item_id[]" required>
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}"
                                            data-unit-id="{{ $item->u_name }}">
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- UNIT --}}
                        <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <input type="text" class="form-control unit_name" readonly>
                            <input type="hidden" class="unit_id" name="unit_id[]">
                        </div>

                        {{-- QUANTITY --}}
                        <div class="col-md-2">
                            <label class="form-label">Quantity</label>
                            <input type="number"
                                class="form-control"
                                name="quantity[]"
                                required
                                min="0"
                                step="0.01">
                        </div>

                        {{-- REQUIRED DATE --}}
                        <div class="col-md-2">
                            <label class="form-label">Required Date</label>
                            <input type="date"
                                class="form-control"
                                name="required_date[]">
                        </div>

                        {{-- NARRATION --}}
                        <div class="col-md-2">
                            <label class="form-label">Narration</label>
                            <input type="text"
                                class="form-control"
                                name="narration[]"
                                placeholder="Optional">
                        </div>

                        {{-- REMOVE BUTTON --}}
                        <div class="col-md-1 text-center">
                            <button type="button" class="btn btn-danger remove-row">X</button>
                        </div>

                    </div>

                @endif

                </div>


                {{-- ADD NEW ROW BUTTON --}}
                <div class="text-start mt-3">
                    <button type="button" id="addRowBtn" class="btn btn-success">+ Add Row</button>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')

<script>
$(document).ready(function () {

    $('.select2-single').select2({ width: '100%' });

    $('.item-row').each(function () {
        applyUnitAutoFill(this);
    });

});
function applyUnitAutoFill(row) {

    $(row).find('.item-select')
        .off('change.unit select2:select.unit')
        .on('change.unit select2:select.unit', function () {

            const unitId = $(this).find(':selected').data('unit-id');

            if (!unitId) {
                $(row).find('.unit_name').val('');
                $(row).find('.unit_id').val('');
                return;
            }

            fetch(`{{ url('get-unit-name') }}/${unitId}`)
                .then(res => res.json())
                .then(data => {
                    $(row).find('.unit_name').val(data.name ?? '');
                    $(row).find('.unit_id').val(unitId);
                });
        });
}

    document.querySelectorAll('.item-row').forEach(row => {
        applyUnitAutoFill(row);

        let select = row.querySelector('.item-select');
        if (select && select.value) {
            select.dispatchEvent(new Event('change'));
        }
    });

    document.getElementById('addRowBtn').addEventListener('click', function () {

    const container = $('#itemRows');
    const firstRow  = container.find('.item-row:first');

    // ✅ destroy only if initialized
    destroySelect2(firstRow);

    const newRow = firstRow.clone();

    // reset values
    newRow.find('input').val('');
    newRow.find('select').val('');

    container.append(newRow);

    // ✅ init select2 ONLY on new row
    newRow.find('.select2-single').select2({
        width: '100%'
    });

    // ✅ bind unit autofill
    applyUnitAutoFill(newRow);
});

document.querySelectorAll('.remove-row').forEach(btn => {
    btn.addEventListener('click', function () {
        // Prevent deleting the last row
        if (document.querySelectorAll('.item-row').length > 1) {
            this.closest('.item-row').remove();
        }
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
function initSelect2(context = document) {
    $(context).find('.select2-single').select2({
        width: '100%'
    });
}
function destroySelect2(context) {
    $(context).find('.select2-single').each(function () {
        if ($(this).hasClass('select2-hidden-accessible')) {
            $(this).select2('destroy');
        }
    });
}
</script>



@endsection
