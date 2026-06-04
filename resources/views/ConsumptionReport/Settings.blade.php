@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3 d-flex justify-content-between align-items-center">

    <h5 class="transaction-table-title m-0">
        Consumption Report Settings
    </h5>

</div>

<form method="POST" action="{{ route('consumption-report-settings-save') }}">
    @csrf

    <div class="bg-white table-view shadow-sm" style="overflow-x:auto;">

        <table class="table table-bordered table-striped m-0">

            <thead>
                <tr>
                    <th style="min-width:250px;">
                        Consumed Item
                    </th>

                    @foreach($generatedItems as $generated)
                        <th class="text-center">
                            {{ $generated->name }}
                        </th>
                    @endforeach

                </tr>
            </thead>

            <tbody>

                @foreach($consumedItems as $consumed)

                    <tr>

                        <td>
                            <strong>{{ $consumed->name }}</strong>
                        </td>

                        @foreach($generatedItems as $generated)

                            <td class="text-center">

                                @php
                                    $mappingKey = $consumed->item_id . '_' . $generated->id;
                                @endphp

                                <input
                                    type="checkbox"
                                    name="settings[{{ $consumed->item_id }}][]"
                                    value="{{ $generated->id }}"

                                    @if(count($savedMappings) == 0)
                                        checked
                                    @elseif(isset($savedMappings[$mappingKey]))
                                        checked
                                    @endif
                                >

                            </td>

                        @endforeach

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

    <div class="mt-3 text-right">
        <button type="submit" class="btn btn-primary">
            Save Settings
        </button>
    </div>

</form>

</div>
</div>
</section>
</div>

@endsection