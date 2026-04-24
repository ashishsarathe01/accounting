@extends('layouts.app')

@section('content')
@include('layouts.header')
<style>
.table-hover tbody tr:hover {
    background-color: #f1f5ff;
    transition: all 0.2s ease-in-out;
}
th, td {
    vertical-align: middle !important;
}
.card {
    border-radius: 1rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 29px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
      height: 30px !important;
   }
   .select2-container .select2-selection--single{
      height: 45px !important;
   }
   .select2-container{
          width: 300 px !important;
   }
   .select2-container--default .select2-selection--single{
      border-radius: 12px !important;
   }
   .selection{
      font-size: 14px;
   }

   .select2-container--default .select2-selection--single .select2-selection__placeholder {
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    width: 100%;
}
td div {
    line-height: 1.4;
    white-space: nowrap;
}
.item-row {
    padding: 6px 6px;
    line-height: 1.4;
}

.item-border {
    border-top: 1px solid #dcdcdc;
}

/* Alternate background */
.item-row:nth-child(even) {
    background-color: #f8f9fa;
}
/* ===== FILTER CARD ===== */

.filter-card {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
}

/* ===== LABELS ===== */

.filter-label {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #374151;
}

/* ===== INPUTS ===== */

.filter-input {
    height: 44px !important;
    font-size: 14px !important;
    border-radius: 10px !important;
}

.filter-input:focus {
    border-color: #1d3557;
    box-shadow: 0 0 0 2px rgba(29,53,87,0.15);
}

/* ===== BUTTONS ===== */

.btn-uniform {
    height: 40px;
    font-size: 14px;
    border-radius: 8px;
}

.btn-main {
    height: 44px;
    padding: 0 28px;
    font-size: 14px;
    border-radius: 10px;
}

/* ===== COLUMN DROPDOWN ===== */

.column-dropdown {
    width: 260px;
    max-height: 320px;
    overflow-y: auto;
    border-radius: 14px;
}
.table-responsive {
    max-height: 70vh;      /* makes vertical scroll */
    overflow-y: auto;
}

/* Sticky table header */
.table-responsive thead th {
    position: sticky;
    top: 0;
    z-index: 100;
    background: #264653;   /* same as your header color */
    color: #ffffff;
}
</style>
<div class="container-fluid py-4">
    <div class="card shadow-lg border-0 rounded-4">
       <div class="card-header text-white rounded-top-4 d-flex justify-content-between align-items-center" 
     style="background: linear-gradient(135deg, #1d3557, #457b9d);">
    <h2 class="mb-0 fw-bold text-center flex-grow-1">Sales Report</h2>
    <button type="button" class="btn btn-danger btn-sm ms-3" onclick="window.location='{{ url('dashboard') }}'">Quit</button>

</div>

        <div class="card-body p-4">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('salebook.index') }}" class="mb-4">

    <div class="filter-card p-4">

        {{-- ================= FILTER GRID ================= --}}
        <div class="row g-4">

            {{-- ROW 1 --}}
            <div class="col-md-3">
                <label class="filter-label">From Date</label>
                <input type="date" id="from_date" name="from_date"
                       class="form-control filter-input"
                       value="{{ $from_date }}">
            </div>

            <div class="col-md-3">
                <label class="filter-label">To Date</label>
                <input type="date" id="to_date" name="to_date"
                       class="form-control filter-input"
                       value="{{ $to_date }}">
            </div>

            <div class="col-md-3">
                <label class="filter-label">Series</label>
                <select name="series" id="series"
                        class="form-select filter-input select2-single">
                    <option value="">All</option>
                    @foreach($seriesList as $s)
                        <option value="{{ $s }}" {{ request('series')==$s ? 'selected' : '' }}>
                            {{ $s }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="filter-label">Party</label>
                <select name="party" id="party"
                        class="form-select filter-input select2-single">
                    <option value="">All</option>
                    @foreach($partyList as $p)
                        <option value="{{ $p }}" {{ request('party')==$p ? 'selected' : '' }}>
                            {{ $p }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- ROW 2 --}}
            <div class="col-md-3">
                <label class="filter-label">Material Center</label>
                <select name="material_center" id="material_center"
                        class="form-select filter-input select2-single">
                    <option value="">All</option>
                    @foreach($materialList as $m)
                        <option value="{{ $m }}" {{ request('material_center')==$m ? 'selected' : '' }}>
                            {{ $m }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="filter-label">Voucher No</label>
                <input type="text" name="voucher_no" id="voucher_no"
                       class="form-control filter-input"
                       value="{{ request('voucher_no') }}">
            </div>

            <div class="col-md-3">
                <label class="filter-label">Item Filter</label>
                <select name="item_id" id="item_id"
                        class="form-select filter-input select2-single">
                    <option value="">All Items</option>
                    @foreach($itemList as $id => $name)
                        <option value="{{ $id }}" {{ request('item_id')==$id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="filter-label">Bill Sundry</label>
                <select name="bill_sundry" id="bill_sundry"
                        class="form-select filter-input select2-single">
                    <option value="">All Sundries</option>
                    @foreach($sundryList as $id => $name)
                        <option value="{{ $id }}" {{ request('bill_sundry')==$id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        {{-- ================= CONTROLS ROW ================= --}}
        <div class="controls-row mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">

            {{-- LEFT SIDE: COLUMN CONTROLS --}}
            <div class="d-flex align-items-center gap-2">

                <div class="dropdown">
                    <button class="btn btn-outline-dark btn-uniform dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown">
                        Show Columns
                    </button>

                    <div class="dropdown-menu p-3 shadow column-dropdown">

                        @foreach($availableColumns as $key => $label)
                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       name="columns[]"
                                       value="{{ $key }}"
                                       {{ in_array($key, $selectedColumns) ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    {{ $label }}
                                </label>
                            </div>
                        @endforeach

                        <hr class="my-2">

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-secondary" id="selectAllCols">All</button>
                            <button type="button" class="btn btn-sm btn-warning" id="clearAllCols">Clear</button>
                            <button type="submit" class="btn btn-sm btn-success">Apply</button>
                        </div>

                    </div>
                </div>

            </div>

            {{-- RIGHT SIDE: MAIN ACTIONS --}}
            <div class="d-flex gap-3">

                <button type="submit" id="filter"
                        class="btn btn-primary btn-main">
                    Search
                </button>

                <button type="submit" name="download" value="csv"
                        class="btn btn-success btn-main">
                    Download CSV
                </button>

            </div>

        </div>

    </div>

</form>

            {{-- Sales Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center shadow-sm">
                    <thead style="background-color:#264653;color:white;">
                        <tr>
                            @foreach($selectedColumns as $col)
                                <th
                                    @if(Str::startsWith($col,'sundry_'))
                                        style="background:#2a9d8f;color:#fff;"
                                    @endif
                                >
                                    {{ $availableColumns[$col] ?? '' }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>


                    
                    <tbody>
@php
    $totalQty = 0;
    $totalAmount = 0;
    $totalAmountValue = 0;
    $sundryTotals = [];
    foreach ($allSundries as $s) {
        $sundryTotals[$s] = 0;
    }
@endphp

@foreach($sales as $sale)
<tr>
    @foreach($selectedColumns as $col)
        <td class="text-start">

            {{-- ITEM NAME --}}
            @if($col === 'item')
                @foreach($sale->items as $i => $item)
                    <div class="item-row {{ $i > 0 ? 'item-border' : '' }}">
                        {{ $item->item_name }}
                    </div>
                @endforeach

            {{-- QTY --}}
            @elseif($col === 'qty')
                @foreach($sale->items as $i => $item)
                    <div class="item-row {{ $i > 0 ? 'item-border' : '' }}">
                        {{ $item->qty }}
                    </div>
                    @php $totalQty += $item->qty; @endphp
                @endforeach

            {{-- PRICE --}}
            @elseif($col === 'price')
                @foreach($sale->items as $i => $item)
                    <div class="item-row {{ $i > 0 ? 'item-border' : '' }}">
                        ₹{{ number_format($item->price,2) }}
                    </div>
                @endforeach

            {{-- AMOUNT --}}
            @elseif($col === 'amount')
                @foreach($sale->items as $i => $item)
                    <div class="item-row {{ $i > 0 ? 'item-border' : '' }}">
                        ₹{{ number_format($item->amount,2) }}
                    </div>
                    @php $totalAmount += $item->amount; @endphp
                @endforeach

            {{-- DATE --}}
            @elseif($col === 'date')
                {{ \Carbon\Carbon::parse($sale->date)->format('d-M-Y') }}

            {{-- SERIES --}}
            @elseif($col === 'series')
                {{ $sale->series_no }}

            {{-- VOUCHER --}}
            @elseif($col === 'voucher_no')
                {{ $sale->voucher_no }}

            {{-- PARTY --}}
            @elseif($col === 'party')
                {{ $sale->party }}

            {{-- MATERIAL CENTER --}}
            @elseif($col === 'material_center')
                {{ $sale->material_center }}

            {{-- TOTAL --}}
            @elseif($col === 'total')
                ₹{{ number_format($sale->total,2) }}
                @php $totalAmountValue += $sale->total; @endphp
            {{-- BILL SUNDRY (DYNAMIC) --}}
            @elseif(Str::startsWith($col,'sundry_'))
                @php
                    $key = str_replace('sundry_','',$col);
                    $amt = $sale->sundries[$key] ?? 0;
                    $sundryTotals[$key] += $amt;
                @endphp
                ₹{{ number_format($amt,2) }}

            @endif

        </td>
    @endforeach
</tr>
@endforeach
</tbody>

                    <tfoot>
<tr class="fw-bold" style="background:#f1faee;">
    @foreach($selectedColumns as $col)
        <th>
            @if($col === 'qty')
                {{ $totalQty }}

            @elseif($col === 'amount')
                ₹{{ number_format($totalAmount,2) }}
            @elseif($col === 'total')
                ₹{{ number_format($totalAmountValue,2) }}

            @elseif(Str::startsWith($col,'sundry_'))
                @php $key = str_replace('sundry_','',$col); @endphp
                ₹{{ number_format($sundryTotals[$key] ?? 0,2) }}

            @endif
        </th>
    @endforeach
</tr>
</tfoot>


                </table>
            </div>
</div>
    </div>
</div></body>

@include('layouts.footer')





<script>
    $(document).ready(function() {
        $('.select2-single').select2({
            placeholder: "Select an option",
            allowClear: true,
            width: '100%'
        });
    });


     $(document).ready(function () {
    // Define mappings for moving focus
    const focusMap = {
        '#from_date': '#to_date',
        '#to_date': '#series',
        '#series': '#party',
        '#party': '#material_center',
        '#material_center': '#voucher_no',
        '#voucher_no': '#item_id',
        '#item_id': '#bill_sundry',
        '#bill_sundry': '#filter',
        '#filter': '#from_date'  // loop back to start
    };

    // Handle Enter key navigation
    $(document).on('keydown', 'input, select, .select2-search__field', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Stop form submit on Enter

            let currentId = $(this).attr('id');

            // If inside Select2 search box
            if ($(this).hasClass('select2-search__field')) {
                currentId = $(this).closest('.select2-container').prev('select').attr('id');
            }

            const nextField = focusMap['#' + currentId];
            if (nextField) {
                setTimeout(function () {
                    $(nextField).focus();
                }, 100);
            }
        }

        // Handle TAB on #filter to loop back to #from_date
        if (e.key === 'Tab' && $(this).attr('id') === 'filter') {
            e.preventDefault();
            $('#from_date').focus();
        }
    });

    // Handle Select2 close (on Enter or selection)
    $('.select2-single').on('select2:close', function (e) {
        const currentId = $(this).attr('id');
        const nextField = focusMap['#' + currentId];
        if (nextField) {
            setTimeout(function () {
                $(nextField).focus();
            }, 100);
        }
    });
});
$('#selectAllCols').click(() => $('input[name="columns[]"]').prop('checked', true));
$('#clearAllCols').click(() => $('input[name="columns[]"]').prop('checked', false));


</script>

@endsection