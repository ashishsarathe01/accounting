@extends('admin-module.layouts.app')
@section('content')

@include('admin-module.layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('admin-module.layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- Title Bar --}}
<div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

    <h5 class="table-title m-0 py-2">Database Backup</h5>

    <a href="{{ route('admin.database.backup.download') }}" class="btn btn-xs-primary">
        DOWNLOAD BACKUP
    </a>

</div>


{{-- Content Card --}}
<div class="bg-white table-view shadow-sm p-4">

    <div class="text-center">


        <h6 class="mb-2">Download Database Backup</h6>

        <p class="text-muted font-13">
            Click the button above to download the latest database backup.
        </p>

    </div>

</div>


</div>
</div>
</section>
</div>

@include('layouts.footer')

@endsection