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

                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 mb-3">
                    <h5 class="m-0"> Edit Reel No: {{ $stock->reel_no }}</h5>
                </div>

    <form action="{{ route('item-size-stocks.update', $stock->id) }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Item</label>
                <select name="item_id" class="form-select" required>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $stock->item_id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Size</label>
                <input type="text" name="size" class="form-control" value="{{ $stock->size }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Weight</label>
                <input type="number" name="weight" class="form-control" value="{{ $stock->weight }}" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">BF</label>
                <input type="number" name="bf" class="form-control" value="{{ $stock->bf }}"  readonly required>
            </div>

            <div class="col-md-4">
                <label class="form-label">GSM</label>
                <input type="number" name="gsm" class="form-control" value="{{ $stock->gsm }}" readonly required>
            </div>

            <div class="col-md-4">
    <label class="form-label">Unit</label>
    <select name="unit" class="form-select" required>
        <option value="">Select</option>
        <option value="INCH" {{ isset($stock->unit) && $stock->unit == 'INCH' ? 'selected' : '' }}>INCH</option>
        <option value="CM" {{ isset($stock->unit) && $stock->unit == 'CM' ? 'selected' : '' }}>CM</option>
        <option value="MM" {{ isset($stock->unit) && $stock->unit == 'MM' ? 'selected' : '' }}>MM</option>
    </select>
</div>

        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-success">Update</button>
            <a href="{{ route('item-size-stocks.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

            </div>
        </div>
    </section>
</div>
@endsection
