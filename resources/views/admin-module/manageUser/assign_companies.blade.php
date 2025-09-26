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
                    <h5 class="table-title m-0 py-2">Assign Companies to: {{ $user->name }}</h5>
                </div>

                <div class="bg-white table-view shadow-sm mt-2">
                    <form method="POST" action="{{ route('admin.manageUser.storeAssignCompanies') }}">
                        @csrf
                        <input type="hidden" name="admin_user_id" value="{{ $user->id }}">

                        <table class="table table-striped table-bordered m-0">
                            <thead class="bg-light-pink font-12">
                                <tr>
                                    <th>Merchant Name</th>
                                    <th>Mobile</th>
                                    <th>Companies</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($merchants as $merchant)
                                <tr>
                                    <td class="font-14 font-heading">{{ $merchant->name }}</td>
                                    <td class="font-14 font-heading">{{ $merchant->mobile_no }}</td>
                                    <td>
                                        @if(count($merchant->company) > 0)
                                            @foreach($merchant->company as $company)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="merchant_companies[{{ $merchant->id }}][]" 
                                                           value="{{ $company->id }}"
                                                           id="comp_{{ $merchant->id }}_{{ $company->id }}"
                                                           @if(in_array($company->id, $assigned)) checked @endif>
                                                    <label class="form-check-label" 
                                                           for="comp_{{ $merchant->id }}_{{ $company->id }}">
                                                        {{ $company->company_name }} ({{ $company->gst }})
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">No companies</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="text-center mt-3 mb-3">
                            <button type="submit" class="btn btn-primary">Assign Selected Companies</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@endsection
