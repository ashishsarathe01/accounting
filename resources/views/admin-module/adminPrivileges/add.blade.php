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
                    <h5 class="table-title m-0 py-2">Add Admin Privilege</h5>
                    <a href="{{ route('admin.admin-privilege.index') }}" class="btn btn-xs-primary">
                        VIEW
                    </a>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('admin.admin-privilege.store') }}">
                        @csrf
                        <div class="row">
                            <div class="mb-4 col-md-4">
                                <label class="form-label font-14 font-heading">Name</label>
                                <input type="text" class="form-control" name="module_name" placeholder="Enter name" required />
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label font-14 font-heading">Parent</label>
                                <select class="form-select" name="parent">
                                    <option value="">Parent</option>
                                    @foreach($privileges as $value)
                                        <option value="{{ $value->id }}">{{ $value->module_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4 col-md-4">
                                <label class="form-label font-14 font-heading">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="">Select</option>
                                    <option value="1">Enable</option>
                                    <option value="0">Disable</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-start">
                            <button type="submit" class="btn btn-xs-primary">ADD</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@include('layouts.footer')
@endsection
