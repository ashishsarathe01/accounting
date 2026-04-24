@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>

/* ================= PAGE WRAPPER ================= */
.list-of-view-company {
    background: #f8fafc;
}

/* ================= TITLE BAR ================= */
.page-title-bar {
    background: #ffffff;
    border-radius: 14px;
    padding: 18px 24px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.page-title-bar h5 {
    margin: 0;
    font-weight: 700;
    font-size: 18px;
}

/* ================= FILTER FORM ================= */
.filter-inline {
    display: flex;
    gap: 12px;
    align-items: end;
}

.filter-inline .form-control {
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

/* ================= BUTTONS ================= */
.btn-primary {
    border-radius: 6px;
    padding: 7px 18px;
    font-weight: 600;
}

.btn-outline-purple {
    background: #6f42c1;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 7px 16px;
    font-weight: 600;
    transition: 0.2s;
}

.btn-outline-purple:hover {
    background: #5933a5;
    color: #fff;
}

/* ================= TABLE ================= */
.table-wrapper {
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    overflow: auto;
    margin-top: 20px;
}

.table {
    margin-bottom: 0;
}

/* Gradient Header */
.table thead {
    background: linear-gradient(90deg,#5f6df5,#8f94fb);
    color: #fff;
}

.table thead th {
    border: none !important;
    padding: 12px;
    font-size: 14px;
}

/* Body */
.table tbody td {
    padding: 10px 12px;
    font-size: 14px;
}

/* Hover */
.table tbody tr:hover {
    background: #eef4ff;
}

/* Total row */
.total-row {
    background: #f1f5ff;
    font-weight: 700;
}

/* Amount alignment */
.text-end {
    font-weight: 600;
}

</style>
<div class="list-of-view-company" style="background:#f8fafc;">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4">
     @if (session('error'))
             <div class="alert alert-danger" role="alert"> {{session('error')}}
             </div>
             @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif

            <div class="container-fluid mt-4">

            <h5 class="table-title-bottom-line px-4 py-3 mb-3 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm"
                style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">

                <!-- LEFT TITLE -->
                <span style="font-size:20px; font-weight:700; color:#2b2b2b;">
                    Aging Report
                </span>

                <!-- FORM -->
                <form method="GET" class="row"
                    style="display:flex; align-items:end; gap:15px; margin:0;">

                    <div class="col-md-3" style="min-width:180px;">
                        <input type="date" name="date" value="{{ $today }}"
                            class="form-control"
                            style="border-radius:10px; padding:8px 12px; border:1px solid #d0d0d0; box-shadow:none; font-size:14px;">
                    </div>

                    <div class="col-md-2 d-flex align-items-end" style="min-width:120px;">
                        <button class="btn btn-primary w-100"
                            style="border-radius:10px; padding:8px 0; font-weight:600; font-size:14px; box-shadow:0 3px 6px rgba(0,0,0,0.15);">
                            Show
                        </button>
                    </div>
                </form>

                <!-- UPDATE BUCKET BUTTON -->
                <a href="{{ route('bucket.set') }}"
                    style="
                        background:#6f42c1;
                        color:#fff;
                        padding:8px 16px;
                        border-radius:10px;
                        font-weight:600;
                        font-size:14px;
                        text-decoration:none;
                        box-shadow:0 3px 6px rgba(0,0,0,0.15);
                        transition:0.2s;
                    "
                    onmouseover="this.style.background='#5933a5'"
                    onmouseout="this.style.background='#6f42c1'">
                    Update Days Buckets
                </a>

            </h5>


    {{-- FILTER FORM --}}
    

    {{-- TABLE --}}
    <div style="
        background:white; 
        border-radius:12px; 
        padding:0; 
        box-shadow:0 2px 10px rgba(0,0,0,0.06);
        overflow:auto;
    ">

        <table class="table table-bordered mb-0" 
            style="border-radius:10px; overflow:hidden;">
            <thead style="background:#f1f5f9; font-weight:700; position:sticky; top:0; z-index:10;">
                <tr>
                    <th>S.no.</th>
                    <th>Party</th>
                    <th class="text-end">Receiable</th>

                    {{-- Dynamic Buckets --}}
                    @foreach($buckets as $b)
                        <th class="text-end">
                            {{ $b->label }} <br>
                            <span style="font-size:12px; color:#6b7280;">
                                ({{ $b->from_days }} - {{ $b->to_days }} Days)
                            </span>
                        </th>
                        @php $maxDays = $b->to_days; @endphp
                    @endforeach

                    <th class="text-end">More Than {{ $maxDays }} Days</th>
                </tr>
            </thead>

            <tbody style="background:white;">
                
                @php
                    $i=1;
                    $grandReceivable = 0;
                    $bucketTotals = [];
                    foreach ($buckets as $b) {
                        $bucketTotals[$b->id] = 0;
                    }
                    $moreThanTotal = 0;
                @endphp

                @foreach($data as $row)
                
                @php
                    $grandReceivable += $row['total'];
                    foreach ($buckets as $b) {
                        $bucketTotals[$b->id] += $row['buckets'][$b->id];
                    }
                    $moreThanTotal += $row['moreThan'];
                @endphp
                <tr style="transition:0.2s;" 
                    onmouseover="this.style.background='#eef6ff';"
                    onmouseout="this.style.background='#fff';">

                    <td>{{ $i++ }}</td>
                    <td>{{ $row['party'] }}</td>

                    <td class="text-end" style="font-weight:600;">
                        {{ FormatIndianNumber($row['total'],2) }}
                    </td>

                    @foreach($buckets as $b)
                        <td class="text-end">
                            {{ FormatIndianNumber($row['buckets'][$b->id], 2) }}
                        </td>
                    @endforeach

                    <td class="text-end" >
                        {{ FormatIndianNumber($row['moreThan'], 2) }}
                    </td>

                </tr>
                @endforeach
                <tr class="total-row">
                        <td colspan="2" class="text-end">TOTAL</td>
                    
                        <td class="text-end">
                            {{ FormatIndianNumber($grandReceivable, 2) }}
                        </td>
                    
                        @foreach($buckets as $b)
                            <td class="text-end">
                                {{ FormatIndianNumber($bucketTotals[$b->id], 2) }}
                            </td>
                        @endforeach
                    
                        <td class="text-end">
                            {{ FormatIndianNumber($moreThanTotal, 2) }}
                        </td>
                    </tr>

            </tbody>
        </table>

    </div>

</div>
</div>
</div>
</section>
</div>

@endsection
