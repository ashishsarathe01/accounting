@extends('layouts.app')
@section('content')

    @include('layouts.header')

    <div class="list-of-view-company">
        <section class="list-of-view-company-section container-fluid">
            <div class="row vh-100">
                @include('layouts.leftnav')

                <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                    {{-- Alerts --}}
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3">

                        <form method="GET" action="{{ route('part-life.entries') }}">

                            <div class="d-flex align-items-center justify-content-between flex-nowrap">

                                <h5 class="transaction-table-title m-0 me-3 text-nowrap">
                                    Part Life Chart – Entries
                                </h5>

                                <div class="d-flex align-items-center flex-nowrap" style="gap:8px;">
                                    <input type="date" name="from_date"
                                           value="{{ request('from_date') }}"
                                           class="form-control form-control-sm"
                                           style="width:140px;">

                                    <input type="date" name="to_date"
                                           value="{{ request('to_date') }}"
                                           class="form-control form-control-sm"
                                           style="width:140px;">

                                    <select name="item_id" class="form-select form-control-sm select2-single" style="width:160px;">
                                        <option value="all">All Items</option>
                                        @foreach($items as $id => $name)
                                            <option value="{{ $id }}" {{ request('item_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <select name="location_id" class="form-select form-control-sm select2-single" style="width:160px;">
                                        <option value="all">All Locations</option>
                                        @foreach($locations as $id => $name)
                                            <option value="{{ $id }}" {{ request('location_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Filter
                                    </button>
                                </div>

                                {{-- RIGHT: ACTION BUTTONS --}}
                                <div class="d-flex align-items-center" style="gap:8px;">
                                    
                                    <button type="button"
                                            class="btn btn-sm btn-secondary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#settingsModal">
                                        Settings
                                    </button>

                                    <a href="{{ route('part-life.entries.add') }}"
                                       class="btn btn-xs-primary text-nowrap">
                                        ADD ENTRY
                                    </a>

                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="bg-white table-view shadow-sm" style="overflow-x:auto;">

                        <table class="table table-bordered table-striped m-0">
                            <thead>
                                <tr class="bg-light-pink text-body">
                                    <th>Date</th>
                                    <th>Required By</th>
                                    <th>Part</th>
                                    <th>Brand</th>
                                    <th>Location</th>
                                    <th style="text-align:right;">Qty</th>
                                    <th style="text-align:right;">Rate</th>
                                    <th>Replace Date</th>
                                    <th style="text-align:right;">Duration</th>
                                    <th>Reason</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @php $group_arr = []; @endphp

                                @foreach($entries as $row)

                                    <tr>

                                        {{-- DATE  --}}
                                        <td>
                                            @if(!in_array($row->entry_group_id, $group_arr))
                                                {{ date('d-m-Y', strtotime($row->entry_date)) }}
                                            @endif
                                        </td>

                                        {{-- TYPE --}}
                                        <td>
                                            {{ $row->required_by ?? '-' }}
                                        </td>

                                        {{-- PART --}}
                                        <td>{{ $row->item_name }}</td>

                                        {{-- BRAND --}}
                                        <td>{{ $row->brand_name }}</td>

                                        {{-- LOCATION --}}
                                        <td>{{ $row->location_name ?? '-' }}</td>

                                        {{-- QTY --}}
                                        <td style="text-align:right;">
                                            {{ $row->qty ?? '-' }}
                                        </td>
                                        {{-- RATE --}}
                                        <td style="text-align:right;">
                                            {{ number_format($row->rate,2) }}
                                        </td>

                                        {{-- REPLACE DATE --}}
                                        <td>
                                            {{ $row->replace_date ? date('d-m-Y', strtotime($row->replace_date)) : '-' }}
                                        </td>

                                        {{-- DURATION --}}
                                        <td style="text-align:right;">
                                            {{ $row->duration ?? '-' }}
                                        </td>

                                        {{-- REASON --}}
                                        <td>{{ $row->reason ?? '-' }}</td>

                                        {{-- ACTION (ONLY ONCE PER GROUP) --}}
                                        <td class="text-center">

                                            @if(!in_array($row->entry_group_id, $group_arr))
                                                @if($row->status != 2)
                                                    {{-- EDIT --}}
                                                    <a href="{{ route('part-life.entries.edit', $row->entry_group_id) }}">
                                                        <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1">
                                                    </a>

                                                    {{-- DELETE --}}
                                                    <button class="border-0 bg-transparent delete"
                                                            data-id="{{ $row->entry_group_id }}">
                                                        <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1">
                                                    </button>
                                                    
                                                    <img src="{{ asset('public/assets/imgs/start.svg') }}"
                                                         class="px-1 start_btn"
                                                         data-id="{{ $row->entry_group_id }}"
                                                         style="width:30px;cursor:pointer;">
                                                @endif
                                            @endif

                                        </td>

                                    </tr>

                                    @php
                                        $group_arr[] = $row->entry_group_id;
                                    @endphp

                                @endforeach

                                @if(count($entries) == 0)
                                    <tr>
                                        <td colspan="11" class="text-center">No entries found</td>
                                    </tr>
                                @endif

                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </section>
    </div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog w-360 modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">

         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <form method="POST" id="deleteForm">
            @csrf
            @method('DELETE')

            <div class="modal-body text-center p-0">

               <button class="border-0 bg-transparent" type="button">
                  <img class="delete-icon mb-3 d-block mx-auto"
                       src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg') }}">
               </button>

               <h5 class="mb-3 fw-normal">Delete this record</h5>

               <p class="font-14 text-body">
                  Do you really want to delete these records? this process cannot be undone.
               </p>

            </div>

            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel">CANCEL</button>
               <button type="submit" class="ms-3 btn btn-red">DELETE</button>
            </div>

         </form>

      </div>
   </div>
</div>
<!-- SETTINGS MODAL -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center">

                <div class="d-grid gap-3">

                    <a href="{{ route('part-life.locations') }}"
                    class="btn btn-sm btn-primary text-nowrap">
                        Manage Locations
                    </a>

                    <a href="{{ route('part-life.brands') }}"
                    class="btn btn-sm btn-success text-nowrap">
                        Manage Brands
                    </a>
                     <a href="{{ url('spare-part-life-chart') }}"
                    class="btn btn-sm btn-warning text-nowrap">
                        Manage Items
                    </a>

                </div>

            </div>

        </div>
    </div>
</div>
@include('layouts.footer')

<script>
$(document).on("click", ".delete", function () {

    let id = $(this).data("id");

    let url = "{{ route('part-life.entries.delete', ':id') }}".replace(':id', id);

    $("#deleteForm").attr('action', url);

    $("#deleteModal").modal('show');
});

$(document).on("click", ".cancel", function () {
    $("#deleteModal").modal('hide');
});

$(document).on('click', '.start_btn', function () {

    let group_id = $(this).data('id');

    $.ajax({
        url: "{{url('get-part-life-items')}}", 
        type: "GET",
        data: { group_id: group_id },

        success: function (res) {

            let entryDate = res.entry_date;
            let items = res.items;

            let url = "{{route('add-stock-journal')}}?" + $.param({
                entry_date: entryDate,
                items: JSON.stringify(items),
                part_life_entry_id: group_id  
            });

            window.location.href = url;
        }
    });

});
$(document).ready(function() {
    $('.select2-single').select2({
        placeholder: "Select option",
        allowClear: true,
        width: 'resolve'
    });
});
</script>

@endsection