@extends('layouts.app')

<style>
/* INPUT */
.form-control {
    height: 40px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    font-size: 14.5px;
}
.form-control:focus {
    border-color: #5e60ce;
    box-shadow: 0 0 0 0.1rem rgba(94,96,206,0.2);
}
</style>

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-3 px-4 mb-3">
        <h4 class="m-0">Manage Sale Rate</h4>

        <a href="{{ route('retail-item-rate.create') }}" class="btn btn-success">
            + Add
        </a>
    </div>

    <!-- FILTER -->
    <div class="card mb-3 shadow-sm border-0" style="border-radius:12px;">
        <div class="card-body py-3 px-3">

            <form method="GET">
                <div class="row align-items-end">

                   <div class="col-md-3">
                        <label><b>From Date</b></label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>

                    <div class="col-md-3">
                        <label><b>To Date</b></label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex gap-2 mt-2 mt-md-0">
                            <button class="btn btn-primary px-4">Search</button>

                            <a href="{{ route('party-item-rate.index') }}" 
                               class="btn btn-light border px-4">
                                Reset
                            </a>
                        </div>
                    </div>

                </div>
            </form>

        </div>
    </div>

    <!-- TABLE -->
   <div class="bg-white shadow-sm p-2" style="overflow-x:auto;">

<table class="table table-bordered text-center align-middle" style="min-width:900px;">

  <thead style="background:#eef3f7;">
    <tr>
        <th style="min-width:200px; position:sticky; left:0; background:#eef3f7; z-index:2;">
            Item
        </th>

        @foreach($dates as $d)
            <th style="min-width:140px; text-align:center;">

                <!-- Date -->
                <div style="font-weight:600;">
                    {{ \Carbon\Carbon::parse($d->date)->format('d M') }}
                </div>

                <!-- Time -->
                <small style="color:#666;">
                    {{ date('H:i', strtotime($d->time)) }}
                </small>

                <!-- Buttons -->
                <div class="mt-1">

                    <!-- Edit -->
                    <a href="{{ route('retail-rate.edit', $d->id) }}"
                       class="btn btn-xs btn-warning"
                       style="padding:2px 6px; font-size:11px;">
                        ✏️
                    </a>

                    <!-- Delete -->
                    <a href="javascript:void(0)"
                       class="btn btn-xs btn-danger deleteBtn"
                       data-id="{{ $d->id }}"
                       style="padding:2px 6px; font-size:11px;">
                        🗑
                    </a>

                </div>

            </th>
        @endforeach
    </tr>
</thead>

    <tbody>
        @foreach($items as $item)
            <tr>

                <!-- Sticky Item Column -->
                <td style="position:sticky; left:0; background:#fff; font-weight:600;">
                    {{ $item->name }}
                </td>

                @foreach($dates as $index => $d)

                  @php
    $current = '';
    $next = '';

    // Current value (latest column first)
    if(isset($rates[$item->id])) {
        $rec = $rates[$item->id]
            ->where('retail_rate_change_date_id', $d->id)
            ->first();

        $current = $rec->rate ?? '';
    }

    // Compare with NEXT (older date)
    if(isset($dates[$index + 1]) && isset($rates[$item->id])) {

        $nextDateId = $dates[$index + 1]->id;

        $nextRec = $rates[$item->id]
            ->where('retail_rate_change_date_id', $nextDateId)
            ->first();

        $next = $nextRec->rate ?? '';
    }

    // Highlight only if next exists and value changed
    $highlight = ($current !== '' && $next !== '' && $current != $next);
@endphp
<td style="{{ $highlight ? 'background:#fff3cd; font-weight:600;' : '' }}">

    {{ $current != '' ? number_format($current,2) : '-' }}

    @if($highlight)
        @if($current > $next)
            <span style="color:green; margin-left:5px;">▲</span>
        @else
            <span style="color:red; margin-left:5px;">▼</span>
        @endif
    @endif

</td>

                @endforeach

            </tr>
        @endforeach
    </tbody>

</table>

</div>

</div>
</div>
</section>
</div>

@include('layouts.footer')

<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){

    // Delete confirm
    $(document).on('click', '.deleteBtn', function(){
        if(confirm('Delete this rate record?')){
            let id = $(this).data('id');
            window.location.href = '/retail-rate/delete/' + id;
        }
    });

});
</script>

@endsection