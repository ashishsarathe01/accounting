@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="bg-white p-4 shadow-sm">

<h5>Edit Party Item Rates</h5>

<form method="POST" action="{{ route('party-item-rate.update', $party_id) }}">
@csrf

<!-- Party (Disabled) -->
<div class="mb-3">
    <label><b>Party</b></label>
    <select class="form-control select2" disabled>
        @foreach($party_list as $party)
            <option value="{{ $party->id }}" {{ $party->id == $party_id ? 'selected' : '' }}>
                {{ $party->account_name }}
            </option>
        @endforeach
    </select>
</div>

<table class="table table-bordered" id="itemTable">
    <thead class="bg-light">
        <tr>
            <th>Item</th>
            <th>Rate</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>

        @foreach($rates as $key => $rate)
        <tr>
            <td>
                <select name="items[{{ $key }}][item_id]" class="form-control select2" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $rate->item_id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" step="0.01" name="items[{{ $key }}][price]" value="{{ $rate->price }}" class="form-control" required>
            </td>

            <td>
                <button type="button" class="btn btn-success addRow">+</button>
                <button type="button" class="btn btn-danger removeRow">X</button>
            </td>
        </tr>
        @endforeach

    </tbody>
</table>

<button class="btn btn-primary">Update</button>

</form>

</div>
</div>
</div>
</section>
</div>

@include('layouts.footer')

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function(){

    $('.select2').select2();

    let rowIndex = {{ count($rates) }};

    // ADD
    $(document).on('click','.addRow',function(){

        let row = `
        <tr>
            <td>
                <select name="items[${rowIndex}][item_id]" class="form-control select2" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control" required>
            </td>

            <td>
                <button type="button" class="btn btn-success addRow">+</button>
                <button type="button" class="btn btn-danger removeRow">X</button>
            </td>
        </tr>
        `;

        $('#itemTable tbody').append(row);
        $('#itemTable tbody tr:last .select2').select2();

        rowIndex++;
    });

    // REMOVE
    $(document).on('click','.removeRow',function(){
        if($('#itemTable tbody tr').length > 1){
            $(this).closest('tr').remove();
        }
    });

});
</script>

@endsection