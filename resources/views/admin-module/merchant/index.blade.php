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
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center" style="height: 48px;">
                        @foreach ($errors->all() as $error)
                            <p class="mb-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success" style="height: 48px; display: flex; align-items: center;">
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                @endif
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        DB Back Up
                    </h5>
                     <a href="{{ url('backup/create') }}" class="btn btn-primary">Create Backup</a>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table class="table table-bordered">
                        <tr>
                            <th>File</th>
                            <th>Size</th>
                            <th>Action</th>
                        </tr>
                    @foreach($backups as $backup)
                        <tr>
                            <td>{{ $backup->file_name }}</td>
                            <td>{{ round(($backup->file_size/1024)/1024,2) }} MB</td>
                            <td>
                            <a href="{{ url('storage/app/')}}/{{ $backup->file_name }}">Download</a>
                            <!--<a href="{{url('backup/restore/')}}/{{ $backup->file_name }}">Restore</a>-->
                            </td>
                        </tr>
                    @endforeach
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
@include('layouts.footer')

@endsection
    