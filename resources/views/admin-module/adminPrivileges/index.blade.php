@extends('admin-module.layouts.app')
@section('content')
@include('admin-module.layouts.header')
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2">Admin Panel Privileges</h5>
                    <a href="{{ route('admin.admin-privilege.create') }}" class="btn btn-xs-primary">
                        ADD
                        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
                        </svg>
                    </a>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table id="example" class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th>Id</th>
                                <th>Privilege Name</th>
                                <th>Parent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($privileges as $value)
                                <tr>
                                    <td>{{ $value->id }}</td>
                                    <td>{{ $value->module_name }}</td>
                                    <td>{{ $value->parent->module_name ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.admin-privilege.edit', $value->id) }}">
                                            <img src="{{ asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt="">
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
@include('layouts.footer')
<script>
$(document).ready(function(){
    if ($.fn.DataTable.isDataTable('#example')) {
        $('#example').DataTable().destroy();
    }
    $('#example').DataTable({
        order: [[2, 'asc']],
        responsive: true,
        language: { emptyTable: "No data available in table" }
    });
});
</script>
@endsection
