@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 border-radius-4 mb-4">
                    <h5 class="m-0 py-2">Manage Credit Days</h5>
                    <a href="{{ route('sale-order.credit-days.create') }}" class="btn btn-xs-primary">ADD +</a>
                </div>

                @if(session('success')) 
                    <div class="alert alert-success">{{ session('success') }}</div> 
                @endif

                <div class="table-responsive">
                    <table class="table-striped table m-0 shadow-sm table-bordered">
                        <thead>
                            <tr class="font-12 text-body bg-light-pink">
                                <th style="width:10%;">S No.</th>
                                <th>Credit Days</th>
                                <th>Status</th>
                                <th class="text-center" style="width:120px;">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($creditDays as $key => $day)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $day->days }}</td>

                                    <td>
                                        @if($day->status == 1)
                                            <span class="text-success font-weight-bold">Enabled</span>
                                        @else
                                            <span class="text-danger font-weight-bold">Disabled</span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('sale-order.credit-days.edit', $day->id) }}">
                                            <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" class="px-1" alt="">
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">
                                        No credit days found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection
