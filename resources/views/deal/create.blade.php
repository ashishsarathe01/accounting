@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
.select2-container .select2-selection--single {
    height: 48px;
    line-height: 45px;
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 8px;
}
.select2-container .select2-selection--single .select2-selection__arrow {
    height: 100%;
    top: 50%;
    transform: translateY(-50%);
}
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Add Deal
                </h5>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('deal.store') }}" id="dealForm">
                    @csrf

                    <div class="row mb-3">
                        <!-- Party Name -->
                        <div class="col-md-3">
                            <label for="party_name" class="form-label">Party Name *</label>
                            <select class="form-select select2-single" name="party_id" id="party_id" required>
                                <option value="">Select Account</option>
                                @foreach($party_list as $party)
                                    <option value="{{$party->id}}" data-address="{{$party->address}}, {{$party->pin_code}}">
                                        {{$party->account_name}}
                                    </option>
                                @endforeach
                            </select>   
                            <p id="party_address" style="font-size:12px;"></p>
                        </div>

                        <!-- Type -->
                        <div class="col-md-2">
                            <label for="type" class="form-label">Type *</label>
                            <select name="type" id="type" class="form-select" required>
                                <option value="">Select Type</option>
                                @foreach($deal_types as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="col-md-2">
                            <label for="quantity" class="form-label">Qty *</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" min="1" placeholder="Enter quantity" required>
                        </div>

                        <!-- Freight -->
                        <div class="col-md-2">
                            <label for="freight" class="form-label">Freight</label>
                            <select name="freight" id="freight" class="form-select">
                                <option value="">Select</option>
                                @foreach($freights as $f)
                                    <option value="{{ $f }}">{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Short Name -->
                        <div class="col-md-3">
                            <label for="short_name" class="form-label">Short Name</label>
                            <input type="text" name="short_name" id="short_name" class="form-control" placeholder="Enter short name">
                        </div>
                    </div>

                    <!-- Items Section -->
                    <h6 class="mb-2 mt-4">Items</h6>
                    <div id="items_container">
                        <div class="row mb-2 item-row">
                            <div class="col-md-6">
                                <select name="items[0][item_id]" class="form-select select2-single item-select" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="items[0][rate]" class="form-control" min="0" step="0.01" placeholder="Rate" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-center">
                                <button type="button" class="btn btn-danger btn-sm remove-item" style="display:none;">&times;</button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" id="add_item_btn" class="btn btn-success btn-sm">+ Add Item</button>
                    </div>

                    <div class="d-flex">
                        <div class="ms-auto">
                            <input type="submit" value="SAVE" class="btn btn-primary">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">QUIT</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
$(document).ready(function(){
    $('.select2-single').select2({ width: '100%' });

    // Show party address
    $('#party_id').change(function(){
        var address = $(this).find('option:selected').data('address');
        $('#party_address').text(address);
    });

    // --- Add Item Row ---
    let itemIndex = 1;
    $('#add_item_btn').click(function(){
        // Clone clean HTML instead of full Select2 structure
        let newRow = $('<div class="row mb-2 item-row">'+
            '<div class="col-md-6">'+
                '<select name="items['+itemIndex+'][item_id]" class="form-select select2-single item-select" required>'+
                    '<option value="">Select Item</option>'+
                    @json($items).map(item => `<option value="${item.id}">${item.name}</option>`).join('') +
                '</select>'+
            '</div>'+
            '<div class="col-md-4">'+
                '<input type="number" name="items['+itemIndex+'][rate]" class="form-control" min="0" step="0.01" placeholder="Rate" required>'+
            '</div>'+
            '<div class="col-md-2 d-flex align-items-center">'+
                '<button type="button" class="btn btn-danger btn-sm remove-item">&times;</button>'+
            '</div>'+
        '</div>');

        $('#items_container').append(newRow);
        newRow.find('.select2-single').select2({ width: '100%' });
        itemIndex++;
    });

    // Remove item row
    $(document).on('click', '.remove-item', function(){
        $(this).closest('.item-row').remove();
    });
});
</script>
@endsection
