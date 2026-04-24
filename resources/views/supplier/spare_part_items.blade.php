@extends('layouts.app')

@section('content')
@include('layouts.header')
<style>
.group-body {
    display: none;
}
.group-body.show {
    display: table-row-group;
}
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

                    <h5 class="m-0 fw-bold text-uppercase">Manage Spare Part Items</h5>
                        <a href="{{ route('spare-part.maintain') }}">
                            <button class="btn btn-primary btn-sm d-flex align-items-center">Maintain Quantity</button>
                        </a>
                </div>

                <form method="POST" action="{{ route('spare-part.next') }}">
                    @csrf
                    <input type="hidden" name="form_company_id" value="{{ $formCompanyId }}">
                    <input type="hidden" name="form_company_name" value="{{ $formCompanyName }}">
                    <input type="hidden" name="action_type" id="action_type">

                    <div class="card border-0 shadow-sm p-4">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="4%" class="text-center">
                                            <input type="checkbox" id="checkAll">
                                        </th>
                                        <th>Item</th>
                                        <th width="12%" class="text-end">Quantity</th>
                                        <th width="15%">Maintain Qty</th>
                                        <th width="15%" class="text-end">Ordered Qty</th>
                                        <th width="18%" class="text-end">To Be Order Qty</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($groups as $group)
                                    <tr class="table-secondary group-toggle"
                                        data-group-id="{{ $group->id }}"
                                        style="cursor: pointer;">
                                            <td colspan="6">
                                                <strong>
                                                    <span class="toggle-icon me-2">▶</span>
                                                    {{ $group->group_name }}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tbody id="group-{{ $group->id }}" class="group-body">
                                        @foreach ($group->items as $item)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        class="form-check-input spare-check"
                                                        name="items[{{ $item->id }}][selected]"
                                                        value="1">
                                                </td>

                                                <td class="fw-semibold">
                                                    {{ $item->name }}
                                                </td>

                                                <td class="current-qty text-end fw-semibold">
                                                    {{ $item->current_qty }}
                                                </td>

                                                <td>
                                                    <input type="number"
                                                        class="form-control maintain-qty"
                                                        value="{{ $item->maintain_quantity }}"
                                                        readonly>
                                                </td>

                                                <td class="ordered-qty text-end text-primary fw-bold">
                                                    {{ $item->ordered_quantity ?? 0 }}
                                                </td>

                                                <td class="to-order-qty text-end fw-bold text-danger">
                                                    0
                                                </td>

                                                <input type="hidden"
                                                    class="order-qty-hidden"
                                                    name="items[{{ $item->id }}][quantity]"
                                                    value="0">
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="submit"
                                        id="nextBtn"
                                        class="btn btn-primary px-4"
                                        formaction="{{ route('spare-part.next') }}"
                                        onclick="document.getElementById('action_type').value='next'"
                                        disabled>
                                    Next
                                </button>                        
                        </div>

                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
let isDirty = false;

function calculateRow(row) {

    let currentQty  = parseFloat(row.find('.current-qty').text()) || 0;
    let maintainQty = parseFloat(row.find('.maintain-qty').val()) || 0;
    let orderedQty  = parseFloat(row.find('.ordered-qty').text()) || 0;

    let toOrder = maintainQty - currentQty - orderedQty;
    if (toOrder < 0) toOrder = 0;
    if(orderedQty==0 && toOrder==0){
        row.hide();
    }
    row.find('.to-order-qty').text(toOrder);
    row.find('.order-qty-hidden').val(toOrder);

    let checkbox = row.find('.spare-check');

    if (toOrder === 0) {
        checkbox.prop('checked', false).prop('disabled', true);
    } else {
        checkbox.prop('disabled', false);
    }

    updateNextButton();
}


$(document).on('change', '.spare-check', function () {
    updateNextButton();
});

$('#checkAll').on('change', function () {

    if (isDirty) {
        alert('Please save changes before selecting items.');
        $(this).prop('checked', false);
        return;
    }

    let checked = $(this).is(':checked');

    $('.spare-check').each(function () {
        if (!$(this).is(':disabled')) {
            $(this).prop('checked', checked);
        }
    });

    updateNextButton();
});

function updateNextButton() {

    if (isDirty) {
        $('#nextBtn').prop('disabled', true);
        return;
    }

    let anyChecked = $('.spare-check:checked').length > 0;
    $('#nextBtn').prop('disabled', !anyChecked);
}

$(document).ready(function () {

    $('.maintain-qty').each(function () {
        calculateRow($(this).closest('tr'));
    });

    updateNextButton();
});

document.getElementById('nextBtn').addEventListener('click', function (e) {

    const formCompanyId   = document.querySelector('[name="form_company_id"]').value;
    const formCompanyName = document.querySelector('[name="form_company_name"]').value;
    const sessionCompanyId = localStorage.getItem('active_company_id');

   if (sessionCompanyId && sessionCompanyId !== formCompanyId) {
        const sessionCompanyName = localStorage.getItem('active_company_name') || 'another company';

        const msg =
         `This Sale belongs to "${formCompanyName}"

         You have switched company in another tab.

         Saving will STORE under "${formCompanyName}"

         Do you want to continue?`;

        if (!confirm(msg)) {
            e.preventDefault();
            return false;
        }
   }
});

document.addEventListener('DOMContentLoaded', function () {

    const STORAGE_KEY = 'spare_part_group_state';
    let groupState = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};

    document.querySelectorAll('.group-toggle').forEach(toggle => {

        const groupId = toggle.dataset.groupId;
        const body = document.getElementById('group-' + groupId);
        const icon = toggle.querySelector('.toggle-icon');

        // Restore state (default open)
        const isOpen = groupState[groupId] !== undefined
            ? groupState[groupId]
            : true;

        body.classList.toggle('show', isOpen);
        icon.textContent = isOpen ? '▼' : '▶';

        // Click handler
        toggle.addEventListener('click', function () {

            const nowOpen = !body.classList.contains('show');

            body.classList.toggle('show', nowOpen);
            icon.textContent = nowOpen ? '▼' : '▶';

            groupState[groupId] = nowOpen;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(groupState));
        });
    });

});
</script>
@endsection
