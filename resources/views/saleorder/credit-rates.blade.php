@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 border-radius-4 mb-4">
                    <h5 class="m-0 py-2">Manage Credit Rates</h5>

                    @if(!($editMode ?? false))
                        <a href="{{ route('sale-order.credit-days.rates.edit') }}" class="btn btn-xs-primary">Edit</a>
                    @endif
                </div>

                @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

                <form action="{{ route('sale-order.credit-days.rates.store') }}" method="POST">
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th>Item \ Day</th>
                                    @foreach($creditDays as $day)
                                        <th>{{ $day->days }}</th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td class="text-start">{{ $item->name }}</td>

                                        @foreach($creditDays as $day)
                                            @php
                                                $key = $item->id . '_' . $day->id;
                                                $value = $existingRates[$key] ?? '';
                                            @endphp
                                            <td>
                                                <input type="number" step="0.01"
                                                    class="form-control"
                                                    name="rate[{{ $item->id }}][{{ $day->id }}]"
                                                    value="{{ $value }}"
                                                    {{ ($editMode ?? false) ? '' : 'readonly' }}>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($editMode ?? false)
                        <button type="submit" class="btn btn-primary mt-3">Save Rates</button>
                    @endif

                </form>

            </div>
        </div>
    </section>
</div>
@endsection
