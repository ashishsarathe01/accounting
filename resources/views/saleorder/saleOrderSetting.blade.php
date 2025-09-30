@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mb-4">
                    <h5 class="master-table-title m-0 py-2">Sale Order Settings</h5>
                </div>

                <form action="{{ route('sale-order.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Items (Grouped)</h6>
                        @foreach($groups as $group)
                            <div class="mb-2 border-bottom pb-2">
                                <div class="d-flex align-items-center group-label">
                                    @if($group->items->count() > 0)
                                        <input type="checkbox" class="group-checkbox me-2" data-group="{{ $group->id }}">
                                        <span class="toggle-items ms-2 cursor-pointer" data-group="{{ $group->id }}">[+]</span>
                                        <strong class="ms-2">{{ $group->group_name }}</strong>
                                    @else
                                        <strong>{{ $group->group_name }}</strong>
                                    @endif
                                </div>

                                @if($group->items->count() > 0)
                                    <div class="ms-4 items-list" id="group-{{ $group->id }}" style="display:none;">
                                        @foreach($group->items as $item)
                                            <div>
                                                <input type="checkbox" name="items[]" value="{{ $item->id }}" 
                                                       class="item-checkbox" data-group="{{ $group->id }}"
                                                       @if(in_array($item->id, $selectedItems)) checked @endif>
                                                {{ $item->name }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-4">
                        <h6 style="font-size:1.3rem;">Units</h6>
                        @foreach($units as $unit)
                            <div style="font-size:1.25rem;">
                                <input type="checkbox" name="units[]" value="{{ $unit->id }}" class="unit-checkbox"
                                @if(in_array($unit->id, $selectedUnits)) checked @endif>
                                {{ $unit->name }}
                            </div>
                        @endforeach
                    </div>

                    <div class="mb-3 text-start">
                        <button type="submit" class="btn btn-primary px-4">Save Settings</button>
                    </div>
                </form>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<style>
    .group-label strong {
        font-size: 1.5rem; /* increased size */
    }
    .toggle-items {
        font-size: 1.5rem;
        cursor: pointer;
    }
    .items-list div,
    .unit-checkbox + label,
    .unit-checkbox {
        font-size: 1.5rem; /* same as items */
    }
    input[type="checkbox"] {
        transform: scale(1.2);
        margin-right: 0.5rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Toggle group items visibility
    document.querySelectorAll('.toggle-items').forEach(function(toggle) {
        toggle.addEventListener('click', function () {
            let groupId = this.dataset.group;
            let itemsDiv = document.getElementById('group-' + groupId);
            if (itemsDiv.style.display === 'none') {
                itemsDiv.style.display = 'block';
                this.textContent = '[-]';
            } else {
                itemsDiv.style.display = 'none';
                this.textContent = '[+]';
            }
        });
    });

    // Update group checkbox based on items
    function updateGroupCheckbox(groupId) {
        let itemCheckboxes = document.querySelectorAll(`#group-${groupId} .item-checkbox`);
        let groupCheckbox = document.querySelector(`.group-checkbox[data-group="${groupId}"]`);
        if (itemCheckboxes.length === 0) return;
        let allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
        let someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);

        groupCheckbox.checked = allChecked;
        groupCheckbox.indeterminate = !allChecked && someChecked;
    }

    // When an item checkbox changes, update group checkbox
    document.querySelectorAll('.items-list .item-checkbox').forEach(function(itemCb) {
        itemCb.addEventListener('change', function() {
            let groupId = this.dataset.group;
            updateGroupCheckbox(groupId);
        });
    });

    // When group checkbox changes, update all items in the group
    document.querySelectorAll('.group-checkbox').forEach(function(groupCb) {
        groupCb.addEventListener('change', function() {
            let groupId = this.dataset.group;
            let itemCheckboxes = document.querySelectorAll(`#group-${groupId} .item-checkbox`);
            itemCheckboxes.forEach(cb => cb.checked = groupCb.checked);
            updateGroupCheckbox(groupId);
        });
    });

    // Initialize all group checkboxes on load
    document.querySelectorAll('.group-checkbox').forEach(function(groupCb) {
        let groupId = groupCb.dataset.group;
        updateGroupCheckbox(groupId);
    });
});
</script>

@endsection
