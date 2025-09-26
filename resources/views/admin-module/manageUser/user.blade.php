@extends('admin-module.layouts.app')
@section('content')
@include('admin-module.layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Users</li>
                    </ol>
                </nav>

                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 border-radius-4">
                    <h5 class="m-0 py-2">List of Users</h5>
                    <a href="{{ route('admin.manageUser.create') }}" class="btn btn-xs-primary">
                        ADD
                    </a>
                </div>

                <div class="bg-white table-view shadow-sm">
                    <table id="example" class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Date of Appointment</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="font-14 bg-white">
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->mobile }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->date_of_appointment }}</td>
                                    <td>
                                        <span class="bg-secondary-opacity-16 border-radius-4 text-secondary py-1 px-2 fw-bold">
                                            {{ $user->status == 1 ? 'Enable' : 'Disable' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.manageUser.edit', $user->id) }}">
                                            <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" alt="Edit">
                                        </a>

                                        <button type="button" class="border-0 bg-transparent delete" data-id="{{ $user->id }}">
                                            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" alt="Delete">
                                        </button>

                                        <a href="{{ route('admin.manageUser.privileges', $user->id) }}">
                                            <img src="{{ URL::asset('public/assets/imgs/permission.png')}}" alt="Privileges" style="width: 30px;">
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="delete_user" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog w-360 modal-dialog-centered">
        <div class="modal-content p-4 border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" id="deleteForm">
                @csrf
                @method('DELETE')
                <div class="modal-body text-center p-0">
                    <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                    <h5 class="mb-3 fw-normal">Delete this record</h5>
                    <p class="font-14 text-body">Do you really want to delete this record? This process cannot be undone.</p>
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
$(document).ready(function() {
    // Update Delete Form action to use the new route
    $(".delete").click(function() {
        var id = $(this).data("id");
        var url = "{{ url('admin/manageUser') }}/" + id; // Updated to match route for admins table
        $("#deleteForm").attr("action", url);
        $("#delete_user").modal("show");
    });

    $(".cancel").click(function() {
        $("#delete_user").modal("hide");
    });
});
</script>
@endsection
