@extends('layouts.app')

@section('content')
<!-- header-section -->
@include('layouts.header')

<!-- list-view-company-section -->
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
                @endif

                @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
                @endif

                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Settings
                    </h5>
                </div>              
            </div>
        </div>
    </section>
</div>
@endsection
