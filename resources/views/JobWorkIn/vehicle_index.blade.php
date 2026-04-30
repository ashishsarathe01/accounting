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
                        Job Work – Vehicle Entry
                    </h5>
                    <a href="{{ route('add-purchase-info') }}">
                        <button class="btn btn-info">ADD</button>
                    </a>
                    <a href="{{ route('jobwork.vehicle.index') }}"
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
                                            <button type="button"
                                                class="border-0 bg-transparent delete"
                                                data-id="{{ $value->id }}">
                                                <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1">
                                            </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No Job Work vehicle entries found.
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
$(document).on('click', '.delete', function () {
    $('#delete_id').val($(this).data('id'));
    $('#delete_subhead').modal('show');
});

$('.cancel').on('click', function () {
    $('#delete_subhead').modal('hide');
});
</script>

@endsection
