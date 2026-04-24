@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- ✅ Page Header --}}
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h4 class="mb-0">Import Reels from CSV</h4>
                    <a href="{{ route('deckle-process.manage-stock') }}" class="btn btn-secondary btn-sm ms-auto">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>

                <hr>

                {{-- ✅ Success Message --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                        <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- ✅ Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <strong>Errors:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ✅ File Upload Form --}}
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <form action="{{ route('reel.import.process') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="csv_file" class="form-label">Select CSV File:</label>
                                <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv" required>
                            </div>

                            <div class="d-flex justify-content-start gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-cloud-upload"></i> Upload & Import
                                </button>
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>

                    {{-- ✅ CSV Format Example --}}
                    <div class="mt-4 px-3 pb-3">
                        <h6><strong>CSV Format Example:</strong></h6>
                        <pre class="bg-light p-3 rounded border">
Item Name,size,weight,reel_no,bf,gsm,unit
                        </pre>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection
