@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-title-bottom-line position-relative
                    d-flex justify-content-between align-items-center
                    bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

                    <h5 class="transaction-table-title m-0 py-2">
                        Spare Part – Vehicle Entry
                    </h5>
                    <a href="{{ route('add-purchase-info') }}">
                                    <button class="btn btn-info">ADD</button>
                                </a>
                    <a href="{{ route('spare-part.index') }}"
                       class="btn btn-border-body btn-sm">
                        BACK
                    </a>
                </div>

                <div class="transaction-table bg-white table-view shadow-sm mt-3">
                    <table class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th>Date</th>
                                <th>Vehicle No.</th>
                                <th>Group</th>
                                <th>Account Name</th>
                                <th>Gross Weight</th>
                                <th>Bill No.</th>
                                <th>Bill Amount</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicle_entries as $value)
                                <tr>
                                    <td>{{ date('d-m-Y', strtotime($value->entry_date)) }}</td>
                                    <td>{{ $value->vehicle_no }}</td>
                                    <td>{{ $value->group_name }}</td>
                                    <td>{{ $value->account_name }}</td>
                                    <td>{{ $value->gross_weight }}</td>
                                    <td>{{ $value->bill_no }}</td>
                                    <td>{{ $value->amount }}</td>
                                    <td class="w-min-120 text-center">
                                        <a href="{{ url('edit-purchase-info/' . $value->id) }}">
                                            <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1">
                                        </a>
                                        @can('view-module',249)
                                            <button type="button"
                                                class="border-0 bg-transparent delete"
                                                data-id="{{ $value->id }}">
                                                <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1">
                                            </button>
                                        @endif
                                        <img src="{{ asset('public/assets/imgs/start.svg') }}"
                                            class="px-1 start-spare-part"
                                            style="width:30px;cursor:pointer;"
                                            data-vehicle-id="{{ $value->id }}">

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">
                                        No Spare Part vehicle entries found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
</div>
<div class="modal fade" id="startSparePartModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">

            <h5 class="mb-3">Select Spare Part (Pending for Add Purchase)</h5>

            <table class="table-striped table m-0 shadow-sm">
                <thead>
                    <tr class="font-12 text-body bg-light-pink">
                        <th>Select</th>
                        <th>Date</th>
                        <th>PO No</th>
                        <th>Account Name</th>
                        <th>Item</th>
                        <th>Unit</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody id="pendingSparePartBody">
                    <!-- AJAX -->
                </tbody>
            </table>


            <div class="text-end mt-3">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button class="btn btn-primary" disabled id="sparePartNextBtn">
                    Next
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="delete_subhead" tabindex="-1">
    <div class="modal-dialog w-360 modal-dialog-centered">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('delete-purchase-info') }}">
                @csrf
                <div class="modal-body text-center p-0">
                    <img class="delete-icon mb-3 d-block mx-auto"
                         src="{{ asset('public/assets/imgs/administrator-delete-icon.svg') }}">
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body">
                        Do you really want to delete this record?
                    </p>
                </div>

                <input type="hidden" name="delete_id" id="delete_id">

                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('layouts.footer')

<script>

    let selectedVehicleEntryId = null;
let selectedSparePartId = null;

$(document).on('click', '.start-spare-part', function () {

    selectedVehicleEntryId = $(this).data('vehicle-id');
    selectedSparePartId = null;

    $("#sparePartNextBtn").prop('disabled', true);
    $("#pendingSparePartBody").html('');

    $.get("{{ route('spare-part.pending.modal') }}", function (rows) {

        let html = '';

        if (rows.length === 0) {
            html = `
                <tr>
                    <td colspan="7" class="text-center">
                        No Pending Spare Parts found
                    </td>
                </tr>`;
        }

        rows.forEach(row => {
            html += `
                <tr>
                    <td>
                        <input type="radio"
                               name="spare_part_id"
                               value="${row.spare_part_id}">
                    </td>
                    <td>${row.date}</td>
                    <td>${row.po_number}</td>
                    <td>${row.account_name}</td>
                    <td>${row.item_name}</td>
                    <td>${row.unit}</td>
                    <td>${row.quantity}</td>
                </tr>
            `;
        });

        $("#pendingSparePartBody").html(html);
        $("#startSparePartModal").modal('show');
    });
});

$(document).on('change', 'input[name="spare_part_id"]', function () {
    selectedSparePartId = $(this).val();
    $("#sparePartNextBtn").prop('disabled', false);
});


$("#sparePartNextBtn").on("click", function () {

    if (!selectedSparePartId || !selectedVehicleEntryId) {
        alert("Please select a spare part");
        return;
    }

    let url = "{{ url('supplier/spare-part/start-new') }}/"
        + selectedSparePartId
        + "?vehicle_entry_id="
        + selectedVehicleEntryId;


    window.location.href = url;
});
$(document).on('click', '.delete', function () {
    $('#delete_id').val($(this).data('id'));
    $('#delete_subhead').modal('show');
});

$('.cancel').on('click', function () {
    $('#delete_subhead').modal('hide');
});
</script>

@endsection
