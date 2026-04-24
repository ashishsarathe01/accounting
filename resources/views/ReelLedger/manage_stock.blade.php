@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
    .table thead th {
        background: #e3f2fd;
        font-weight: bold;
        text-align: center;
    }
    .table tbody tr:nth-child(even){
        background: #f7f9fc;
    }
    .zero-stock {
        color: red;
        font-weight: bold;
    }
    
    .clickable-row{ cursor:pointer; }
    .clickable-row:hover{ background:#e8f2ff; }

/* Make entire page text 16px */
body, table, th, td, input, button, label, select {
    font-size: 16px !important;
}
@page {
    margin: 0;
}@media print {

    html, body {
        height: auto !important;
    }

    /* REMOVE vh-100 effect */
    .vh-100 {
        height: auto !important;
        min-height: auto !important;
    }

    /* Remove flex alignment issues */
    .row {
        display: block !important;
    }

    /* Remove all spacing */
    .container-fluid,
    .row,
    .col-md-12,
    .card,
    .card-body {
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Show only table */
    body * {
        visibility: hidden;
    }

    .table-responsive,
    .table-responsive * {
        visibility: visible;
    }

    .table-responsive {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
    }
}



</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

    <div class="card shadow-sm">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Manage Stock — As On Date</h5>
        </div>

        <div class="card-body">

            {{-- Filter Form --}}
            <form method="GET" action="{{ url('/closing-stock/reels') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="date" value="{{ $A_date }}" class="form-control" required>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Show</button>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-success w-100" onclick="window.print()">Print</button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th width="50">S.No</th>
                            <th style="text-align:left;">Item Name</th>
                            <th style="text-align:right;" width="120">Reel Count</th>
                            <th style="text-align:right;" width="150">Total Weight</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $i = 1;
                         $totalReels = 0;
                         $totalWeight = 0;
                        @endphp

                        @forelse($stockData as $row)
                          @php
                                $totalReels += $row['reel_count'];
                                $totalWeight += $row['weight'];
                          @endphp
                       <tr class="clickable-row"
                                   data-url="{{ route('ManageStock.filter.detail', ['date' => $A_date, 'item_id' => $row['item_id']]) }}">
                                <td class="text-center">{{ $i++ }}</td>
                                <td>{{ $row['item_name'] }}</td>

                                <td class="text-end {{ $row['reel_count'] == 0 ? 'zero-stock' : '' }}">
                                    {{ $row['reel_count'] }}
                                </td>

                                <td class="text-end {{ $row['weight'] == 0 ? 'zero-stock' : '' }}">
                                    {{ formatIndianNumber($row['weight'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-danger">
                                    No stock records found
                                </td>
                            </tr>
                        @endforelse
                         @if(count($stockData) > 0)
                                <tr class="fw-bold" style="background:#fff8e1;">
                                    <td colspan="2" class="text-end">Total:</td>
                                    <td class="text-end">{{ formatIndianNumber($totalReels) }}</td>
                                    <td class="text-end">{{ formatIndianNumber($totalWeight, 2) }}</td>
                                </tr>
                            @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
</div>
            </section>
</div>

@include('layouts.footer')
<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".clickable-row").forEach(function(row){
        row.addEventListener("click", function(){
            let url = this.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });
    });
});
</script>
@endsection
