@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('admin-module.layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-3 px-4 mb-4">
        <h5 class="m-0">ESIC / PF Compliance</h5>
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
                            <th>SR NO.</th>
                            <th>NAME</th>
                            <th>ESIC</th>
                            <th>PF</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($companies as $key => $row)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td style="text-align:left;">
                                {{ $row->company->company_name ?? '-' }}
                            </td>

                            {{-- ESIC --}}
                            <td>
                                @if($row->esic)
                                    Paid
                                @else
                                    NA
                                @endif
                            </td>

                            {{-- PF --}}
                            <td>
                                @if($row->pf)
                                    Pending
                                @else
                                    NA
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">No Data Found</td>
                        </tr>
                        @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>
</div>
</section>
</div>

@endsection