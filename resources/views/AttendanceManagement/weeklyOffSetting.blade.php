@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                {{-- Alerts --}}
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

                {{-- Page Title --}}
                <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="transaction-table-title m-0 py-2">
                        Set Weekly Off
                    </h5>
                </div>

                {{-- Card --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-body">

                        <form action="{{ route('attendance.weeklyoff.store') }}" method="POST">
                            @csrf

                            <div class="row">

                                {{-- Weekly Days --}}
                                <div class="col-md-12 mb-3">
                                    <label class="form-label fw-bold">
                                        Select Weekly Off Days
                                    </label>

                                    <div class="row mt-2">
                                        @php
                                            $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                                        @endphp

                                        @foreach($days as $day)
                                            <div class="col-md-3 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="off_days[]"
                                                           value="{{ $day }}"
                                                           id="day_{{ $day }}">

                                                    <label class="form-check-label"
                                                           for="day_{{ $day }}">
                                                        {{ $day }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Effective From --}}
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Effective From
                                    </label>
                                    <input type="date"
                                           name="effective_from"
                                           class="form-control"
                                           required>
                                </div>

                            </div>

                            <div class="mt-3">
                                <button type="submit"
                                        class="btn btn-success">
                                    Save Weekly Off Setting
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

                {{-- History Section --}}
                <div class="card shadow-sm mt-4">
                    <div class="card-body">

                        <h6 class="mb-3">Weekly Off History</h6>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Off Days</th>
                                        <th>Effective From</th>
                                        <th>Created At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($settings as $setting)
                                        <tr>
                                            <td>
                                                {{ implode(', ', json_decode($setting->off_days)) }}
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($setting->effective_from)->format('d-m-Y') }}
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($setting->created_at)->format('d-m-Y H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">
                                                No Weekly Off Records Found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

@endsection