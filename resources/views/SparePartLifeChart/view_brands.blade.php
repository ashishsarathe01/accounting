@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Title --}}
                <div class="table-title-bottom-line position-relative d-flex justify-content-between
                    align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

                    <h5 class="transaction-table-title m-0 py-2">
                        Part Life Chart – Manage Brands
                    </h5>

                    <div class="d-md-flex d-block">
                        <input type="text" id="search" class="form-control" placeholder="Search">
                    </div>

                    <a href="{{ route('part-life.brands.add') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg"
                             width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z"
                                  fill="white"/>
                        </svg>
                    </a>
                </div>

                {{-- Table --}}
                <div class="transaction-table bg-white table-view shadow-sm mt-4">
                    <table class="table-striped table m-0 shadow-sm brand_table">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th class="w-min-120 border-none">Brand Name</th>
                                <th class="w-min-120 border-none text-center">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($brands as $brand)
                                <tr class="font-14 text-body">
                                    <td class="w-min-120 border-none">
                                        {{ $brand->brand_name }}
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('part-life.brands.edit', $brand->id) }}"
                                            class="border-0 bg-transparent">
                                            <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}"
                                                class="px-1" alt="Edit">
                                        </a>
                                        @if($brand->usage_count == 0)
                                        <button type="button"
                                                class="border-0 bg-transparent delete"
                                                data-id="{{ $brand->id }}">
                                            <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}"
                                                 class="px-1" alt="Delete">
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center">No brands found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="brandDeleteModal" tabindex="-1">
    <div class="modal-dialog w-360 modal-dialog-centered">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="deleteForm">
                @csrf
                @method('DELETE')

                <div class="modal-body text-center p-0">
                    <img class="delete-icon mb-3"
                         src="{{ asset('public/assets/imgs/administrator-delete-icon.svg') }}">
                    <h5 class="mb-3 fw-normal">Delete this brand</h5>
                    <p class="font-14 text-body">
                        Do you really want to delete this brand?
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

@include('layouts.footer')

<script>
// Delete modal
$(document).on("click", ".delete", function () {
    let id = $(this).data("id");
    let url = "{{ route('part-life.brands.delete', ':id') }}".replace(':id', id);
    $("#deleteForm").attr('action', url);
    $("#brandDeleteModal").modal('show');
});

// Cancel modal
$(document).on("click", ".cancel", function(){
    $("#brandDeleteModal").modal('hide');
});

// Search
$("#search").on("keyup", function () {
    let value = $(this).val().toLowerCase();
    $(".brand_table tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});
</script>

@endsection