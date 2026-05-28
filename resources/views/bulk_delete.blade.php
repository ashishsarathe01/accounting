@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">

        <div class="card-header bg-danger text-white">
            <h4 class="mb-0">
                Bulk Delete Sales
            </h4>
        </div>

        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('sale.bulk.delete') }}"
                  method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            From Date
                        </label>

                        <input type="date"
                               name="from_date"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            To Date
                        </label>

                        <input type="date"
                               name="to_date"
                               class="form-control"
                               required>
                    </div>

                    <div class="col-md-4 mb-3 d-flex align-items-end">

                        <button type="submit"
                                class="btn btn-danger w-100"
                                onclick="return confirm(
                                    'Are you sure?\n\nThis will permanently delete all sales between selected dates.'
                                )">

                            Bulk Delete Sales

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection