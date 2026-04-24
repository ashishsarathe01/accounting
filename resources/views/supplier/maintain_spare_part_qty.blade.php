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

                {{-- ALERTS --}}
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- TITLE --}}
                <div class="table-title-bottom-line d-flex justify-content-between align-items-center
                            bg-plum-viloet shadow-sm py-2 px-4 mb-4">
                    <h5 class="m-0 fw-bold text-uppercase">
                        Maintain Spare Part Quantity
                    </h5>
                </div>

                {{-- FORM --}}
                <form method="POST" action="{{ route('spare-part.maintain.save') }}">
                    @csrf

                    <div class="card border-0 shadow-sm p-4">

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th width="20%">Maintain Qty</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($groups as $group)
                                        <tr class="table-secondary group-toggle"
                                            data-group-id="{{ $group->id }}"
                                            style="cursor: pointer;">
                                            <td colspan="2">
                                                <strong>
                                                    <span class="toggle-icon me-2">▶</span>
                                                    {{ $group->group_name }}
                                                </strong>
                                            </td>
                                        </tr>


                                        <tbody id="group-{{ $group->id }}" class="group-body">
                                        @foreach ($group->items as $item)
                                            <tr>
                                                <td class="fw-semibold">
                                                    {{ $item->name }}
                                                </td>

                                                <td>
                                                    <input type="number"
                                                        class="form-control"
                                                        name="maintain_qty[{{ $item->id }}]"
                                                        value="{{ $item->maintain_quantity }}"
                                                        min="0">
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>

                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- ACTION --}}
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-success px-4">
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
document.addEventListener('DOMContentLoaded', function () {

    const STORAGE_KEY = 'spare_part_group_state_maintain';
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
