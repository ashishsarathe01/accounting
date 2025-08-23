@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
    /* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}

</style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
                <!-- Display validation errors -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Manage Supplier Rate
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('store-supplier-rate') }}">
                    @csrf
                    <div class="row">
                        @foreach($locations as $key => $location)
                            @php $supplier_rate = $supplier_rates->where('location_id',$location->id)->first();@endphp
                            <div class="mb-4 col-md-4">
                                @if($key==0)<label for="name" class="form-label font-14 font-heading">Location</label>@endif
                                <input type="text" class="form-control" name="location[]" value="{{$location->name}}" readonly>
                                <input type="hidden" class="form-control" name="location_id[]" value="{{$location->id}}">
                            </div>
                            <div class="mb-2 col-md-2">
                                @if($key==0)<label for="name" class="form-label font-14 font-heading">KRAFT I RATE</label>@endif
                                <input type="number" step="any" class="form-control" name="kraft_i_rate[]" placeholder="Enter KRAFT I RATE" required value="{{$supplier_rate ? $supplier_rate->kraft_i : ''}}">
                            </div>
                            <div class="mb-2 col-md-2">
                                @if($key==0)<label for="name" class="form-label font-14 font-heading">KRAFT II RATE</label>@endif
                                <input type="number" step="any" class="form-control" name="kraft_ii_rate[]" placeholder="Enter KRAFT II RATE" required value="{{$supplier_rate ? $supplier_rate->kraft_ii : ''}}">
                            </div>
                            <div class="mb-2 col-md-2">
                                @if($key==0)<label for="name" class="form-label font-14 font-heading">DUPLEX RATE</label>@endif
                                <input type="number" step="any" class="form-control" name="duplex_rate[]" placeholder="Enter DUPLEX RATE" required value="{{$supplier_rate ? $supplier_rate->duplex : ''}}">
                            </div>
                            <div class="mb-2 col-md-2">
                                @if($key==0)<label for="name" class="form-label font-14 font-heading">POOR RATE</label>@endif
                                <input type="number" step="any" class="form-control" name="poor_rate[]" placeholder="Enter POOR RATE" required value="{{$supplier_rate ? $supplier_rate->poor : ''}}">
                            </div>
                        @endforeach
                    </div>
                    <div class="text-start">
                        <button type="submit" class="btn  btn-xs-primary ">
                            SUBMIT
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
</body>
@include('layouts.footer')
<script>
$(document).ready(function(){
});

</script>
@endsection