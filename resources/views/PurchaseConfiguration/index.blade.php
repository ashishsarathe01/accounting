@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <!-- view-table-Content -->
         <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}} </div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
               <h5 class="table-title m-0 py-2 ">
                  List of Stock Transfer
               </h5>
            </div>
    <form method="POST" action="{{ route('purchase.configuration.store') }}">
        @csrf
        <input type="hidden" name="form_company_id" value="{{ $formCompanyId }}">

        <div class="card">
            <div class="card-body">

                <div class="row align-items-center mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">
                            Stock Entry Date
                        </label>
                    </div>

                    <div class="col-md-4">
                        <select name="stock_entry_status" class="form-select" required>
                            <option value="1"
                                {{ isset($company) && (int)$company->stock_entry_status === 1 ? 'selected' : '' }}>
                                Yes
                            </option>

                            <option value="0"
                                {{ isset($company) && (int)$company->stock_entry_status === 0 ? 'selected' : '' }}>
                                No
                            </option>
                        </select>

                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>
                </div>

            </div>
        </div>
    </form>
</div>
@include('layouts.footer')

@endsection
