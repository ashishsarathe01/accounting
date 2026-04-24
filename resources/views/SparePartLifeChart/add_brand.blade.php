@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Title --}}
                <div class="table-title-bottom-line position-relative d-flex justify-content-between
                    align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

                    <h5 class="transaction-table-title m-0 py-2">
                        Part Life Chart – Add Brand
                    </h5>

                    <a href="{{ route('part-life.brands') }}" class="btn btn-border-body">
                        BACK
                    </a>
                </div>

                {{-- Form --}}
                <div class="transaction-table bg-white shadow-sm mt-4 p-4">
                    <form method="POST" action="{{ route('part-life.brands.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Brand Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="brand_name"
                                       class="form-control"
                                       placeholder="Enter Brand Name"
                                       value="{{ old('brand_name') }}"
                                       required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-xs-primary">
                                SAVE
                            </button>

                            <a href="{{ route('part-life.brands') }}"
                               class="btn btn-border-body ms-2">
                                CANCEL
                            </a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

@endsection