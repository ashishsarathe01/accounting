@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Page Heading --}}
                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 border-radius-4 mb-4">
                    <h5 class="m-0 py-2">Add Credit Days</h5>
                    <a href="{{ route('sale-order.credit-days') }}" class="btn btn-secondary btn-sm">Back</a>
                </div>

                {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Form --}}
                <form action="{{ route('sale-order.credit-days.store') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="days" class="form-label">Enter Credit Days</label>
                            <input type="number" name="days" id="days" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save</button>
                </form>

            </div>
        </div>
    </section>
</div>
@endsection
