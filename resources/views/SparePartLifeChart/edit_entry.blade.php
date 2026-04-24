@extends('layouts.app')
@section('content')

@include('layouts.header')
<style>
.btn-col button {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    font-weight: bold;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.2s ease;
}

/* PLUS */
.btn-success {
    background: #198754;
}
.btn-success:hover {
    background: #157347;
    transform: scale(1.1);
}

/* MINUS */
.btn-danger {
    background: #dc3545;
}
.btn-danger:hover {
    background: #bb2d3b;
    transform: scale(1.1);
}

/* spacing fix */
.btn-col {
    min-width: 70px;
}
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

{{-- Alerts --}}
@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif
{{-- Title --}}
<div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">
        Part Life Chart – Edit Entries
    </h5>

    <div>
        <a href="{{ route('part-life.entries') }}" class="btn btn-border-body me-2">BACK</a>
    </div>
</div>

<form method="POST" action="{{ route('part-life.entries.update', $group_id) }}">
@csrf
@method('PUT')
<div class="card shadow-sm mt-4 p-4">
    <div class="row">
        <div class="col-md-3">
            <label class="form-label fw-bold">Entry Date</label>
            <input type="date" name="entry_date" id="entry_date"
                   class="form-control"
                   value="{{ $entryDate ?? date('Y-m-d') }}">
        </div>
    </div>
</div>
{{-- ================= PART A ================= --}}
<div class="card shadow-sm mt-4 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0 fw-bold">Items</h6>
    </div>

    <div id="partA_container">
        @forelse($itemsData as $index => $entry)
            <div class="row g-2 align-items-end partA_row">

                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $entry->id }}">

                {{-- PART --}}
                <div class="col-md-2">
                    <label class="form-label">Part</label>
                    <select name="items[{{ $index }}][item_id]" class="form-select item-select select2-single">
                        <option value="" disabled>-- Select Part --</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}"
                                    data-unit="{{ $item->unit }}"
                                    data-unit-id="{{ $item->u_name }}"
                                    {{ $entry->item_id == $item->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BRAND --}}
                <div class="col-md-2">
                    <label class="form-label">Brand</label>
                    <select name="items[{{ $index }}][brand_id]" class="form-select select2-single">
                        <option value="" disabled>-- Select Brand --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}"
                                {{ $entry->brand_id == $brand->id ? 'selected' : '' }}>
                                {{ $brand->brand_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- LOCATION --}}
                <div class="col-md-2">
                    <label class="form-label">Location</label>
                    <select name="items[{{ $index }}][location_id]" class="form-select select2-single">
                        <option value="" disabled>-- Select Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}"
                                {{ $entry->location_id == $loc->id ? 'selected' : '' }}>
                                {{ $loc->location_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- QTY --}}
                <div class="col-md-1">
                    <label class="form-label">Qty</label>
                    <input type="number"
                        name="items[{{ $index }}][qty]"
                        class="form-control qty-field"
                        value="{{ number_format($entry->qty, 2, '.', '') }}"
                        min="0"
                        step="0.01"
                        oninput="validateDecimal(this)">
                </div>

                {{-- UNIT --}}
                <div class="col-md-1">
                    <label class="form-label">Unit</label>
                    <input type="text"
                           name="items[{{ $index }}][unit]"
                           class="form-control unit-field"
                           value="{{ $entry->unit }}"
                           readonly>

                    <input type="hidden"
                           name="items[{{ $index }}][unit_id]"
                           class="unit-id-field"
                           value="{{ $entry->unit_id }}">
                </div>

                {{-- RATE --}}
                <div class="col-md-1">
                    <label class="form-label">Rate</label>
                    <input type="number"
                        name="items[{{ $index }}][rate]"
                        class="form-control rate-field"
                        value="{{ number_format($entry->rate, 2, '.', '') }}"
                        min="0"
                        step="0.01"
                        oninput="validateDecimal(this)">
                </div>
                {{-- REQUIRED BY --}}
                <div class="col-md-1">
                    <label class="form-label">Required By</label>
                    <input type="text"
                        name="items[{{ $index }}][required_by]"
                        class="form-control"
                        value="{{ $entry->required_by ?? '' }}">
                </div>
                {{-- REASON --}}
                <div class="col-md-1">
                    <label class="form-label">Reason</label>
                    <input type="text"
                           name="items[{{ $index }}][reason]"
                           class="form-control"
                           value="{{ $entry->reason }}">
                </div>

                {{-- BUTTONS --}}
                <div class="col-md-1 d-flex align-items-end justify-content-end gap-1 btn-col"></div>
                <div class="col-md-12 text-danger small error-msg"></div>

            </div>
        @empty
            <div class="row g-2 align-items-end partA_row">

                {{-- PART --}}
                <div class="col-md-2">
                    <label class="form-label">Part</label>
                    <select name="items[0][item_id]" class="form-select item-select select2-single">
                        <option value="" disabled selected>-- Select Part --</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}"
                                    data-unit="{{ $item->unit }}"
                                    data-unit-id="{{ $item->u_name }}">
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- BRAND --}}
                <div class="col-md-2">
                    <label class="form-label">Brand</label>
                    <select name="items[0][brand_id]" class="form-select select2-single">
                        <option value="" disabled selected>-- Select Brand --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->brand_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- LOCATION --}}
                <div class="col-md-2">
                    <label class="form-label">Location</label>
                    <select name="items[0][location_id]" class="form-select select2-single">
                        <option value="" disabled selected>-- Select Location --</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- QTY --}}
                <div class="col-md-1">
                    <label class="form-label">Qty</label>
                    <input type="number"
                        name="items[0][qty]"
                        class="form-control qty-field"
                        min="0"
                        step="0.01"
                        oninput="validateDecimal(this)">
                </div>

                {{-- UNIT --}}
                <div class="col-md-1">
                    <label class="form-label">Unit</label>
                    <input type="text" name="items[0][unit]" class="form-control unit-field" readonly>

                    <input type="hidden" name="items[0][unit_id]" class="unit-id-field">
                </div>

                {{-- RATE --}}
                <div class="col-md-1">
                    <label class="form-label">Rate</label>
                    <input type="number"
                        name="items[0][rate]"
                        class="form-control rate-field"
                        min="0"
                        step="0.01"
                        oninput="validateDecimal(this)">
                </div>
                <div class="col-md-1">
                    <label class="form-label">Required By</label>
                    <input type="text"
                        name="items[0][required_by]"
                        class="form-control"
                        placeholder="Enter name">
                </div>
                {{-- REASON --}}
                <div class="col-md-1">
                    <label class="form-label">Reason</label>
                    <input type="text" name="items[0][reason]" class="form-control">
                </div>

                {{-- BUTTON --}}
                <div class="col-md-1 d-flex align-items-end justify-content-end gap-1 btn-col"></div>
                <div class="col-md-12 text-danger small error-msg"></div>

            </div>
        @endforelse
    </div>
</div>


{{-- SAVE --}}
<div class="mt-4 text-end">
    <button type="submit" class="btn btn-success px-4">
        UPDATE ALL
    </button>

</div>

</form>

</div>
</div>
</section>
</div>

@include('layouts.footer')

<script>
let partAIndex = {{ isset($itemsData) && count($itemsData) > 0 ? count($itemsData) : 1 }};

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

$(document).on('click', '.addRowA', function () {

    let firstRow = $('.partA_row:first');

    firstRow.find('.select2-single').select2('destroy');

    let row = firstRow.clone();
    row.addClass('new-row-A');

    row.find('input, select').each(function () {
        let name = $(this).attr('name');
        if (name) {
            name = name.replace(/\[\d+\]/, '[' + partAIndex + ']');
            $(this).attr('name', name);
        }

        if ($(this).is('select')) {
            $(this).val('');
        } else {
            $(this).val('');
        }
    });

    row.find('input[name*="[id]"]').remove();

    row.find('.unit-id-field').val('');
    row.find('.unit-field').val('');

    row.find('.error-msg').text('');
    row.find('.is-invalid').removeClass('is-invalid');

    $('#partA_container').append(row);

    $('.select2-single').select2({ width: '100%' });

    row.find('.select2-single').val('').trigger('change');

    row.find('.item-select').trigger('change');

    partAIndex++;

    updateButtons('partA_container', 'addRowA');
});

$(document).on('click', '.removeRow', function () {
    let container = $(this).closest('[id$="_container"]').attr('id');

    $(this).closest('.row').remove();

    updateButtons('partA_container', 'addRowA');
});

$(document).ready(function () {
    updateButtons('partA_container', 'addRowA');
    $('.select2-single').select2({ width: '100%' });
    $('.partA_row').each(function () {
        updateRate($(this));
    });
});
$(document).on('change', '.item-select', function () {

    let row = $(this).closest('.row');
    let selected = $(this).find(':selected');

    let unit = selected.data('unit');
    let unit_id = selected.data('unit-id');

    row.find('.unit-field').val(unit || '');
    row.find('.unit-id-field').val(unit_id || '');
});
function updateRate(row) {

    let item_id = row.find('.item-select').val();
    let entry_date = $('#entry_date').val();

    if (!item_id || !entry_date) return;

    $.ajax({
        url: "{{ route('get-item-average-price') }}",
        type: "GET",
        data: {
            item_id: item_id,
            series_no: 1,
            entry_date: entry_date
        },
        success: function (res) {
            if (res.price) {

    let price = parseFloat(res.price);
    price = price.toFixed(2);

    row.find('input[name*="[rate]"]').val(price);
}
        }
    });
}
$(document).on('change keyup', '.item-select, input[name*="[qty]"]', function () {

    let row = $(this).closest('.row');
    updateRate(row);

});
$(document).on('change', '#entry_date', function () {

    $('.partA_row').each(function () {
        updateRate($(this));
    });

});
$(document).on('change', 
    '.partA_row input[name*="[issue_date]"], .partA_row select[name*="[item_id]"], .partA_row select[name*="[location_id]"]', 
function () {
    let row = $(this).closest('.partA_row');

    let issue_date = row.find('input[name*="[issue_date]"]').val();
    let item_id = row.find('select[name*="[item_id]"]').val();
    let location_id = row.find('select[name*="[location_id]"]').val();

    let errorDiv = row.find('.error-msg');

    if (issue_date && item_id && location_id) {
        $.ajax({
            url: "{{ route('part-life.check-date') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                issue_date: issue_date,
                item_id: item_id,
                location_id: location_id,
                group_id: "{{ $group_id ?? '' }}",
        current_id: row.find('input[name*="[id]"]').val() || ''
            },
            success: function (res) {
                if (!res.status) {
                    errorDiv.text(res.message);
                    row.find('input[name*="[issue_date]"]').val('');
                } else {
                    errorDiv.text('');
                }
            }
        });
    }
});

$('form').on('submit', function (e) {

    let hasError = false;
    let hasAtLeastOneRow = false;

    $('#partA_container .partA_row').each(function () {

        let item = $(this).find('[name*="[item_id]"]').val();
        let qty  = $(this).find('[name*="[qty]"]').val();

        if (item && qty) {
            hasAtLeastOneRow = true;
        }
    });


    if (!hasAtLeastOneRow) {
        alert("Please enter at least one item.");
        e.preventDefault();
        return false;
    }

    $('.new-row-A').each(function () {

        let row = $(this);

        let item = row.find('[name*="[item_id]"]').val();
        let brand = row.find('[name*="[brand_id]"]').val();
        let location = row.find('[name*="[location_id]"]').val();
        let qty = row.find('[name*="[qty]"]').val();
        let rate = row.find('[name*="[rate]"]').val();
        let required_by = row.find('[name*="[required_by]"]').val();

        if (item == "" || brand == "" || location == "" || qty == "" || rate == "") {
            hasError = true;
        }
    });


    $('.error-msg').each(function () {
        if ($(this).text().trim() !== '') {
            hasError = true;
        }
    });

    if (hasError) {
        alert(" Please fill all fields in newly added rows or remove them.");
        e.preventDefault();
        return false;
    }

});
function validateDecimal(input) {

    let value = input.value;

    if (value < 0) {
        input.value = '';
        return;
    }

    if (value.includes('.')) {
        let parts = value.split('.');
        parts[1] = parts[1].substring(0, 2);
        input.value = parts.join('.');
    }
}
$(document).on('keydown', '.qty-field, .rate-field', function (e) {
    if (e.key === '-' || e.key === 'Minus') {
        e.preventDefault();
    }
});

</script>

@endsection
