@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 border-radius-4 mb-4">
                    <h5 class="m-0 py-2">Edit Credit Day</h5>
                    <a href="{{ route('sale-order.credit-days') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <form action="{{ route('sale-order.credit-days.update', $day->id) }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Credit Days</label>
                            <input type="number" name="days" class="form-control" value="{{ $day->days }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" {{ $day->status == 1 ? 'selected' : '' }}>Enable</option>
                                <option value="0" {{ $day->status == 0 ? 'selected' : '' }}>Disable</option>
                            </select>
                        </div>
                    </div>

                    <button class="btn btn-primary">Update</button>
                </form>

            </div>
        </div>
    </section>
</div>
@endsection
