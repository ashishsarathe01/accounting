@extends('layouts.app')
@section('content')

@include('layouts.header')

<style>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
    
    @include('layouts.leftnav')

    <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

        <div class="table-title-bottom-line position-relative d-flex justify-content-between 
            align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">

            <h5 class="transaction-table-title m-0 py-2">Manage Supplier Rate — Boiler Fuel</h5>
        </div>
        <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" 
              method="POST" 
              action="{{ route('store-fuel-item-rate') }}">
            @csrf

            <div class="row">

                <div class="mb-3 col-md-3">
                    <label class="form-label font-14 font-heading">Date</label>
                    <input type="date" class="form-control" id="fuel_date" 
                           name="fuel_date" value="{{ $fuel_date }}" required>
                </div>

                <div class="clearfix"></div>

                @foreach($items as $key => $item)
                <div class="mb-4 col-md-4">
                    @if($key==0)
                        <label class="form-label font-14 font-heading">Item</label>
                    @endif
                    <input type="text" class="form-control" value="{{ $item->name }}" readonly>
                    <input type="hidden" name="item_id[]" value="{{ $item->id }}"  >
                </div>

                <div class="mb-4 col-md-2">
                    @if($key==0)
                        <label class="form-label font-14 font-heading">Price</label>
                    @endif
                    <input type="number" step="any" name="item_price[]" class="form-control"
                           placeholder="Price"
                           value="{{ $fuelresult[$item->id] ?? '' }}" @cannot('view-module',210) readonly @endcannot>
                           
                </div>

                <div class="clearfix"></div>
                @endforeach

            </div>
              @can('view-module',210)
            <div class="text-start">
                <button type="submit" class="btn btn-xs-primary">SUBMIT</button>
            </div>
            @endcan

        </form>
        @can('view-module',211)
        <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet 
            position-relative title-border-redius border-divider shadow-sm">
            View Supplier Rate
        </h5>

        <div class="bg-white table-view shadow-sm">
            <table class="table table-striped m-0 shadow-sm">
                <thead>
                    <tr class="font-12 text-body bg-light-pink">
                        <th class="w-min-120">Date</th>
                        <th class="w-min-120">Rates</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($all_fuel_item_rate as $date => $values)
                    <tr>
                        <td>{{ date('d-m-Y', strtotime($date)) }}</td>
                        <td>
                            <table class="table table-bordered m-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($values as $row)
                                    <tr>
                                        <td>{{ $row['item_name'] }}</td>
                                        <td>{{ $row['item_price'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endcan
    </div>
</div>
</section>
</div>

@include('layouts.footer')

<script>
document.getElementById("fuel_date").addEventListener("change", function () {
    window.location = "{{ url('manage-supplier-rate/boiler-fuel') }}/" + this.value;
});
</script>

@endsection
