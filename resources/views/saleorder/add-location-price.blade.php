@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Location & Price</h5>
                <div class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm">
                    <form action="{{ route('sale-order.store-location-price') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            @foreach($locations as $key => $value)
                                <div class="col-md-3">
                                    <label for="location_{{$key}}" class="form-label font-14 font-heading">Location</label>
                                    <input type="text" class="form-control" id="location_{{$key}}" name="location[]" value="{{$value->location}}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label for="price_{{$key}}" class="form-label font-14 font-heading">Price</label>
                                    <input type="text" class="form-control" id="price_{{$key}}" name="price[]" placeholder="Price" value="{{$value->price ?? ''}}">
                                </div>
                                <div class="clearfix"></div>
                            @endforeach
                        </div>
                        <div class="d-flex">
                            <div class="ms-auto">
                                <input type="submit" value="SAVE" class="btn btn-primary start_order">
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">QUIT</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>






@include('layouts.footer')

<script>
    

</script>
@endsection