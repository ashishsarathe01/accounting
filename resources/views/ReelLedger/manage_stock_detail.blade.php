@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<style>

/* ---------- PROFESSIONAL COLOR PALETTE ---------- */
:root {
    --primary-light: #e8f0ff;
    --primary: #5b8dfb;
    --primary-dark: #2f63e0;

    --row-even: #f3f6fb;
    --row-odd: #ffffff;

    --collapse-bg: #f0f4ff;
    --border-color: #d5ddf5;
}

/* -------- PAGE LOOK IMPROVEMENTS -------- */
.bg-mint {
    background: #fafcff !important;
    padding: 20px;
}

/* -------- TABLE HEADER -------- */
.table thead th {
    background: var(--primary);
    color: white;
    font-weight: bold;
    border-color: var(--primary-dark);
}

/* -------- SIZE HEADER ROW (CLICKABLE) -------- */
.size-header-row {
    background: var(--primary-light) !important;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.2s ease-in-out;
}
.size-header-row:hover {
    background: var(--primary) !important;
    color: white;
}

/* -------- COLLAPSE SECTION -------- */
.collapse-row {
    display: none;
    background: var(--collapse-bg);
}

/* -------- ODD EVEN ROWS -------- */
.table tbody tr:nth-child(odd) {
    background: var(--row-odd);
}
.table tbody tr:nth-child(even) {
    background: var(--row-even);
}

/* -------- INNER TABLE -------- */
.inner-table th {
    background: var(--primary-dark);
    color: #fff;
    border-color: var(--primary);
}

.inner-table tbody tr:hover {
    background: #e9f1ff;
}

/* -------- SOFT CARD EFFECT -------- */
.table {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0px 3px 8px rgba(0,0,0,0.05);
}

</style>


<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

    <h3 class="mb-3" style="color: var(--primary-dark); font-weight:700;">Available Reel Stock (Item Wise)- {{$item_name}}</h3>
    <form method="GET" action="{{ url('/closing-stock/reels/detail') }}" class="row g-3 mb-4">

    <div class="col-md-3">
        <label class="form-label">Select Date</label>
        <input type="date" name="date" value="{{ $aA_date }}" class="form-control" required>
        <input type="hidden" name="item_id" value="{{ $item_id }}" class="form-control" required>
    </div>

    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary w-100">Show</button>
    </div>

    <!-- 📌 Download PDF button small & properly aligned -->
    <div class="col-md-2 d-flex align-items-end">
        <a href="{{ route('ReelStock.pdf', ['date' => request('date'), 'item_id' => request('item_id')]) }}"
           class="btn btn-sm btn-success w-100">
            <i class="fa fa-file-pdf-o"></i> Download PDF
        </a>
    </div>

    <!-- Back button -->
    <div class="col-md-2 d-flex align-items-end ms-auto">
        <a href="{{ route('ManageStock.filter') }}" class="btn btn-secondary w-100">Back</a>
    </div>

    </form>
    @if($GroupedData->isEmpty())
        <div class="alert alert-warning">No reels available for this item.</div>
    @endif


    <table class="table table-bordered table-sm mt-3 size-table">
        <thead>
            <tr>
                <th style="text-align:left; font-size:16px;padding-left:10px;" width="50">Size</th>
                <th style="text-align:right; font-size:16px;padding-right:10px;" width="50">Total Reels</th>
            </tr>
        </thead>

        <tbody>
@php $i=0; @endphp
        @foreach($GroupedData as $size => $reelsByNo)

            {{-- SIZE HEADER ROW --}}
            <tr class="size-header-row"
                onclick="toggleSection('size_{{ md5($size) }}')">
                <td style="text-align:left; font-size:16px;padding-left:10px;"><b>{{ $size }}</b></td>
                <td style="text-align:right; font-size:16px;padding-right:10px;"><b>{{ $reelsByNo->flatten(1)->count() }}</b></td>
            </tr>

            {{-- COLLAPSIBLE ROW --}}
            <tr id="size_{{ md5($size) }}" class="collapse-row">
                <td colspan="2">

                    @php
                        $final = [];
                        $serial = 1;

                        foreach($reelsByNo as $reel_no => $reels) {
                            foreach($reels as $r) {
                                $final[] = [
                                    'sn'    => $serial++,
                                    'size'  => $size,
                                    'reel_no' => $r->reel_no,
                                    'weight' => $r->weight,
                                    'unit'   => $r->unit
                                ];
                            }
                        }
                    @endphp

                    <!-- INNER TABLE -->
                    <table class="table table-bordered table-sm mt-2 inner-table">
                        <thead>
                            <tr>
                                <th style="text-align:center; font-size:15px;" width="40">S.No</th>
                                <th style="text-align:left; font-size:15px; padding-left:10px;" width="100">Size</th>
                                <th style="text-align:center; font-size:15px;" width="100">Reel No</th>
                                <th style="text-align:right; font-size:15px; padding-right:10px;" width="150">Weight</th>
                                <th style="text-align:center; font-size:15px;" width="80">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                             @php $total_size_weight = 0;@endphp
                            @foreach($final as $row)
                             @php $total_size_weight += $row['weight']; 
                             $i += 1;
                             @endphp
                            <tr>
                                <td style="text-align:center">{{ $row['sn'] }}</td>
                                <td style="text-align:left; padding-left:10px;"><b>{{ $row['size'] }}</b></td>
                                <td style="text-align:center">{{ $row['reel_no'] }}</td>
                                <td style="text-align:right;padding-right:10px;">{{ formatIndianNumber($row['weight'], 2) }}</td>
                                <td style="text-align:center">{{ $row['unit'] }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td style="text-align:right; padding-right:10px;" colspan="3">Total</td>
                                <td style="text-align:right;padding-right:10px;" >{{formatIndianNumber($total_size_weight,2)}}</td>
                                <td></td>

                            </tr>
                        </tbody>
                    </table>

                </td>
            </tr>

        @endforeach
<tr class="fw-bold" style="background:#fff8e1;" >
   <td style="text-align:right;padding-right:10px;" >Total</td> 
   <td style="text-align:right;padding-right:10px;">{{$i}}</td> 
</tr>
        </tbody>
    </table>

</div>
</div>
</section>
</div>

@include('layouts.footer')

<script>
function toggleSection(id) {
    let el = document.getElementById(id);
    el.style.display = 
        (el.style.display === "none" || el.style.display === "") 
            ? "table-row" 
            : "none";
}
</script>

@endsection
