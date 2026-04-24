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

.group-toggle {
    background: #f1f3f5;
    transition: all 0.2s ease;
}
.group-toggle:hover {
    background: #e9ecef;
}

.group-title {
    font-weight: 600;
    font-size: 14px;
}

.item-row td {
    padding-left: 25px;
}

.toggle-icon {
    font-size: 12px;
}

.card {
    border-radius: 10px;
}
</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4">
    <h5 class="m-0 fw-bold text-uppercase">Part Life Chart - Items</h5>
</div>

<form method="POST" action="{{ route('part-life-chart.store') }}">
@csrf

<div class="card border-0 shadow-sm p-4 mt-3">

<div class="table-responsive">
<table class="table table-bordered align-middle mb-0">

<thead class="table-light">
<tr>
    <th width="5%" class="text-center">
        <input type="checkbox" id="checkAll">
    </th>
    <th>Item Name</th>
</tr>
</thead>

<tbody>
@foreach ($groups as $group)

<!-- GROUP HEADER -->
<tr class="group-toggle"
    data-group-id="{{ $group->id }}"
    style="cursor: pointer;">
    <td colspan="2">
        <div class="d-flex align-items-center">
            <span class="toggle-icon me-2">▼</span>
            <span class="group-title">{{ $group->group_name }}</span>
        </div>
    </td>
</tr>

<!-- GROUP ITEMS -->
<tbody id="group-{{ $group->id }}" class="group-body show">
@foreach ($group->items as $item)
<tr class="item-row">

    <!-- Checkbox -->
    <td class="text-center">
        <input type="checkbox"
    class="item-check"
    name="items[{{ $item->id }}][selected]"
    value="1"
    {{ $savedItems->contains($item->id) ? 'checked' : '' }}>
    </td>

    <!-- Item -->
    <td class="fw-semibold">
        {{ $item->name }}
    </td>

    <!-- Type -->


</tr>
@endforeach
</tbody>

@endforeach
</tbody>

</table>
</div>

<div class="text-end mt-3">
    <button type="submit" class="btn btn-primary px-4">
        Save
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
// ================= GROUP TOGGLE =================
document.addEventListener('DOMContentLoaded', function () {

    const STORAGE_KEY = 'part_life_chart_group_state';
    let groupState = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};

    document.querySelectorAll('.group-toggle').forEach(toggle => {

        const groupId = toggle.dataset.groupId;
        const body = document.getElementById('group-' + groupId);
        const icon = toggle.querySelector('.toggle-icon');

        const isOpen = groupState[groupId] !== undefined
            ? groupState[groupId]
            : true;

        body.classList.toggle('show', isOpen);
        icon.textContent = isOpen ? '▼' : '▶';

        toggle.addEventListener('click', function () {

            const nowOpen = !body.classList.contains('show');

            body.classList.toggle('show', nowOpen);
            icon.textContent = nowOpen ? '▼' : '▶';

            groupState[groupId] = nowOpen;
            localStorage.setItem(STORAGE_KEY, JSON.stringify(groupState));
        });
    });

});

// Individual checkbox change
$(document).on('change', '.item-check', function () {

    let total = $('.item-check').length;
    let checked = $('.item-check:checked').length;

    let selectAll = $('#checkAll')[0];

    if (checked === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } 
    else if (checked === total) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } 
    else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    }
});


// Top "Select All" checkbox
$('#checkAll').on('change', function () {

    let checked = $(this).is(':checked');

    // Apply to ALL checkboxes
    $('.item-check').prop('checked', checked);

    // Reset indeterminate state
    this.indeterminate = false;
});


// ================= INITIAL LOAD SYNC =================
$(document).ready(function () {

    let total = $('.item-check').length;
    let checked = $('.item-check:checked').length;

    let selectAll = $('#checkAll')[0];

    if (checked === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } 
    else if (checked === total) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } 
    else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    }
});
</script>

@endsection