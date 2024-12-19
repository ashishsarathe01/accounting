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
                @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    <nav>
                        <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                            <li class="breadcrumb-item">Dashboard</li>
                            <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                        </ol>
                    </nav>
                    
                </div>
            </div>
        </section>
    </div>
</body>
@include('layouts.footer')
</html>
@endsection
