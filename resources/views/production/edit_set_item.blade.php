@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Breadcrumb --}}
                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Production</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Edit Set Item</li>
                    </ol>
                </nav>

                {{-- Edit Item Form --}}
                <div class="bg-white p-4 shadow-sm border-radius-8">
                    <form action="{{ route('production.set_item.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Items Container --}}
                        <div id="items_container">
                            <div class="item-section border rounded p-3 mb-3 position-relative">
                                <div class="row">

                                    {{-- Item Dropdown --}}
                                    <div class="col-md-3 mb-3">
                                        <label>Item *</label>
                                        <select name="item_id" class="form-select select2-single" required>
                                            <option value="">Select Item</option>
                                            @foreach($groups as $group)
                                                @if($group->items->count() > 0)
                                                    <optgroup label="{{ $group->name }}">
                                                        @foreach($group->items as $groupItem)
                                                            <option value="{{ $groupItem->id }}" 
                                                                {{ $item->item_id == $groupItem->id ? 'selected' : '' }}>
                                                                {{ $groupItem->name }}
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- BF --}}
                                    <div class="col-md-3 mb-3">
                                        <label>BF *</label>
                                        <input type="number" name="bf" class="form-control" required value="{{ $item->bf }}">
                                    </div>

                                    {{-- GSM --}}
                                    <div class="col-md-3 mb-3">
                                        <label>GSM *</label>
                                        <input type="number" name="gsm" class="form-control" required value="{{ $item->gsm }}">
                                    </div>

                                    {{-- Speed --}}
                                    <div class="col-md-3 mb-3">
                                        <label>Speed</label>
                                        <input type="number" name="speed" class="form-control" value="{{ $item->speed }}">
                                    </div>

                                    {{-- Status --}}
                                    <div class="col-md-3 mb-3">
                                        <label>Status</label>
                                        <select name="status" class="form-select" required>
                                            <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ $item->status == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Update Item</button>
                            <a href="{{ route('production.set_item') }}" class="btn btn-dark ms-2">Quit</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
@endsection
