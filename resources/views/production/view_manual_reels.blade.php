@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- ✅ Success/Error --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <p class="mb-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

<div class="container">
    <h2 class="mb-4">📦 Item Size Stocks</h2>

    @foreach($stocks as $itemName => $group)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">{{ $itemName }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Reel No</th>
                            <th>Size</th>
                            <th>Weight</th>
                            <th>BF</th>
                            <th>GSM</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group as $stock)
                            <tr>
                                <td>{{ $stock->reel_no }}</td>
                                <td>{{ $stock->size }}</td>
                                <td>{{ $stock->weight }}</td>
                                <td>{{ $stock->bf }}</td>
                                <td>{{ $stock->gsm }}</td>
                                <td>{{ $stock->unit }}</td>
                                <td>
                                    @if($stock->status == 1)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Sold</span>
                                    @endif
                                </td>
                                <td>
                                    @if($stock->status == 1)
                                        <a href="{{ route('item-size-stocks.edit', $stock->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>Sold</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
</div></div></section>
</div>

@endsection
