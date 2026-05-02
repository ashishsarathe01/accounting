@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<div class="list-of-view-company">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('admin-module.layouts.leftnav')
         
         <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

            {{-- Success Message --}}
            @if(session('success'))
               <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-3 px-4 mb-4">
               <h5 class="m-0">GST Compliance</h5>
            </div>

            <div class="bg-white shadow-sm rounded-3">
               <div class="card-body p-4">


                {{-- FILTER --}}
                <form method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Select Month</label>
                            <input type="month" name="month" value="{{ $month }}" class="form-control">
                        </div>

                        <div class="col-md-2 mt-4">
                            <button class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>

                {{-- TABLE --}}
                <div class="table-responsive">
                    <table class="table table-bordered text-center">

                        <thead>
                            <tr>
                                <th rowspan="2">SR NO.</th>
                                <th rowspan="2">NAME</th>
                                <th colspan="3">GSTR-1</th>
                                <th colspan="3">GSTR-3B</th>
                            </tr>
                            <tr>
                                <th>STATUS</th>
                                <th>ARN</th>
                                <th>DATE OF FILING</th>

                                <th>STATUS</th>
                                <th>ARN</th>
                                <th>DATE OF FILING</th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($companies as $key => $row)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row->company->company_name ?? '-' }}</td>

                                {{-- GSTR-1 --}}
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>

                                {{-- GSTR-3B --}}
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8">No Data Found</td>
                            </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>
</div>

@endsection