@extends('layouts.app')
@section('content')

@include('layouts.header')
<style>
/* Remove spinner */
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- TITLE --}}
<div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">
        Yield Report – Edit
    </h5>

    <a href="{{ route('yield-report.index') }}" class="btn btn-border-body">
        BACK
    </a>
</div>

<form method="POST" action="{{ route('yield-report.update', $id) }}">
@csrf

{{-- ================= SECTION 1 ================= --}}
<div class="card shadow-sm mt-4 p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-bold">Material Required</h6>
    </div>

    <div id="material_container">

        @foreach($materialItems as $index => $row)

        <div class="row g-2 align-items-end material_row">
            <input type="hidden" name="material[{{ $index }}][id]" value="{{ $row->id }}">
            {{-- ITEM --}}
            <div class="col-md-4">
                <label class="form-label">Item</label>
                <select name="material[{{ $index }}][item_id]" class="form-select select2-single item-select">
                    <option value="">-- Select Item --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}"
                            {{ $item->id == $row->item_id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- RECOVERY --}}
            <div class="col-md-3">
                <label class="form-label">Recovery</label>
                <select name="material[{{ $index }}][recovery_status]" class="form-select recovery-select">
                    <option value="0" {{ $row->recovery_status == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ $row->recovery_status == 1 ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            {{-- RECOVERY % --}}
            <div class="col-md-3">
                <label class="form-label">Recovery %</label>
                <input type="text"
                    name="material[{{ $index }}][recovery_percent]"
                    value="{{ $row->recovery_percent }}"
                    class="form-control recovery-percent"
                    step="0.01"
                    min="0"
                    max="100"
                    inputmode="decimal"
                    style="{{ $row->recovery_status ? '' : 'display:none;' }}">
            </div>

            {{-- BUTTON --}}
            <div class="col-md-2 d-flex align-items-end btn-col"></div>

        </div>

        @endforeach

    </div>
</div>

{{-- ================= SECTION 2 ================= --}}
<div class="card shadow-sm mt-4 p-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-bold">Main Raw Material Required</h6>
    </div>

    <div id="main_material_container">

        @foreach($mainItems as $index => $row)

        <div class="row g-2 align-items-end main_material_row">
            <input type="hidden" name="main_material[{{ $index }}][id]" value="{{ $row->id }}">
            {{-- ITEM --}}
            <div class="col-md-10">
                <label class="form-label">Item</label>
                <select name="main_material[{{ $index }}][item_id]" class="form-select select2-single item-select">
                    <option value="">-- Select Item --</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}"
                            {{ $item->id == $row->item_id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- BUTTON --}}
            <div class="col-md-2 d-flex align-items-end btn-col"></div>

        </div>

        @endforeach

    </div>
</div>

{{-- SAVE --}}
<div class="mt-4 text-end">
    <button type="submit" class="btn btn-success px-4">
        UPDATE
    </button>
</div>

</form>

</div>
</div>
</section>
</div>

@include('layouts.footer')
<script>

let materialIndex = {{ count($materialItems) }};
let mainMaterialIndex = {{ count($mainItems) }};


/* ================= BUTTON CONTROL ================= */
function updateButtons(container, addClass) {

    let rows = $(`#${container} .row`);
    let total = rows.length;

    rows.each(function (index) {

        let btnCol = $(this).find('.btn-col');
        btnCol.html('');

        if (total > 1) {
            btnCol.append(`<button type="button" class="btn btn-danger removeRow">−</button>`);
        }

        if (index === total - 1) {
            btnCol.append(`<button type="button" class="btn btn-success ${addClass}">+</button>`);
        }
    });
}


/* ================= ADD MATERIAL ================= */
$(document).on('click', '.addMaterial', function () {

    let firstRow = $('.material_row:first');
    firstRow.find('.select2-single').select2('destroy');

    let row = firstRow.clone();

    row.find('input, select').each(function () {
        let name = $(this).attr('name').replace(/\d+/, materialIndex);
        $(this).attr('name', name).val('');
    });

    row.find('.recovery-percent').hide();

    $('#material_container').append(row);

    $('.select2-single').select2({ width: '100%' });

    materialIndex++;

    updateButtons('material_container', 'addMaterial');
});


/* ================= ADD MAIN MATERIAL ================= */
$(document).on('click', '.addMainMaterial', function () {

    let firstRow = $('.main_material_row:first');
    firstRow.find('.select2-single').select2('destroy');

    let row = firstRow.clone();

    row.find('input, select').each(function () {
        let name = $(this).attr('name').replace(/\d+/, mainMaterialIndex);
        $(this).attr('name', name).val('');
    });

    $('#main_material_container').append(row);

    $('.select2-single').select2({ width: '100%' });

    mainMaterialIndex++;

    updateButtons('main_material_container', 'addMainMaterial');
});


/* ================= REMOVE ================= */
$(document).on('click', '.removeRow', function () {
    let container = $(this).closest('[id$="_container"]').attr('id');
    $(this).closest('.row').remove();

    updateButtons(container, container === 'material_container' ? 'addMaterial' : 'addMainMaterial');
});


/* ================= RECOVERY TOGGLE ================= */
$(document).on('change', '.recovery-select', function () {
    let row = $(this).closest('.row');
    let input = row.find('.recovery-percent');

    if ($(this).val() == '1') {
        input.show();
    } else {
        input.hide().val('');
    }
});


/* ================= DUPLICATE LOGIC ================= */
function getAllSelectedItems() {
    let selected = [];

    $('.item-select').each(function () {
        let val = $(this).val();
        if (val) selected.push(val);
    });

    return selected;
}

$(document).on('change', '.item-select', function () {

    let current = $(this);
    let selectedValue = current.val();

    if (!selectedValue) return;

    let allSelected = [];

    $('.item-select').not(current).each(function () {
        let val = $(this).val();
        if (val) allSelected.push(val);
    });

    if (allSelected.includes(selectedValue)) {
        alert('This item is already selected in another row.');
        current.val('').trigger('change');
    }
});

function refreshItemOptions() {

    let selectedItems = getAllSelectedItems();

    $('.item-select').each(function () {

        let currentVal = $(this).val();

        $(this).find('option').each(function () {

            let optionVal = $(this).attr('value');

            if (!optionVal) return;

            if (selectedItems.includes(optionVal) && optionVal != currentVal) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });

    });

    $('.item-select').trigger('change.select2');
}


/* ================= INPUT VALIDATION ================= */
$(document).on('wheel', '.recovery-percent', function () {
    this.blur();
});

$(document).on('keydown', '.recovery-percent', function (e) {
    if (e.key === '-' || e.key === 'Minus') {
        e.preventDefault();
    }
});

$(document).on('input', '.recovery-percent', function () {

    let value = $(this).val();

    if (value === '') return;

    value = value.replace(/[^0-9.]/g, '');

    let parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts[1];
        parts = value.split('.');
    }

    if (parts[1]) {
        parts[1] = parts[1].substring(0, 2);
        value = parts[0] + '.' + parts[1];
    }

    let num = parseFloat(value);
    if (!isNaN(num) && num > 100) {
        value = '100';
    }

    $(this).val(value);
});


/* ================= INIT ================= */
$(document).ready(function () {

    $('.select2-single').select2({ width: '100%' });

    updateButtons('material_container', 'addMaterial');
    updateButtons('main_material_container', 'addMainMaterial');

    $('.recovery-select').each(function () {
        let row = $(this).closest('.row');
        let input = row.find('.recovery-percent');

        if ($(this).val() == '1') {
            input.show();
        } else {
            input.hide();
        }
    });

    refreshItemOptions();
});

</script>
@endsection