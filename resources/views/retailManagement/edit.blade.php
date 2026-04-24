@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

<div class="bg-white p-4 shadow-sm">

<h5 class="mb-3">Edit Item Rates</h5>

<form method="POST" action="{{ route('retail-rate.update', $rateChange->id) }}">
    @csrf

    <!-- Date & Time -->
    <div class="row mb-3">
        <div class="col-md-4">
            <label><b>Select Date</b></label>
            <input type="date" name="rate_date" class="form-control"
                value="{{ $rateChange->date }}" required>
        </div>

        <div class="col-md-4">
            <label><b>Select Time</b></label>
            <input type="time" name="rate_time" class="form-control"
                value="{{ $rateChange->time }}" required>
        </div>
    </div>

    <!-- Items Table -->
    <table class="table table-bordered">
        <thead class="bg-light">
            <tr>
                <th width="60%">Item</th>
                <th width="40%">Rate</th>
            </tr>
        </thead>

        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>
                    {{ $item->name }}
                    <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">
                </td>

                <td>
                    <input type="number" step="0.01"
                        name="items[{{ $index }}][price]"
                        class="form-control"
                        value="{{ $rates[$item->id] ?? '' }}"
                        placeholder="Enter Price" required>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <button class="btn btn-primary">
        Update
    </button>

</form>

</div>
</div>
</div>
</section>
</div>

@include('layouts.footer')
<script>
$(document).ready(function(){

    // 🔥 store original values
    let originalDate = $('input[name="rate_date"]').val();
    let originalTime = $('input[name="rate_time"]').val();

    function validateDateTime() {

        let date = $('input[name="rate_date"]').val();
        let time = $('input[name="rate_time"]').val();
        let id   = "{{ $rateChange->id }}";

        if(date && time){
            $.ajax({
                url: "{{ route('check.latest.datetime') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    date: date,
                    time: time,
                    id: id
                },
                success: function(res){

                    if(!res.status){

                        // ❌ Show message
                        alert(res.message);

                        // 🔁 Revert to original values
                        $('input[name="rate_date"]').val(originalDate);
                        $('input[name="rate_time"]').val(originalTime);

                        $('button[type="submit"]').prop('disabled', false);

                    } else {

                        // ✅ Update original if valid change
                        originalDate = date;
                        originalTime = time;

                        $('button[type="submit"]').prop('disabled', false);
                    }
                }
            });
        }
    }

    // Trigger on change
    $('input[name="rate_date"], input[name="rate_time"]').on('change', function(){
        validateDateTime();
    });

});
</script>
@endsection