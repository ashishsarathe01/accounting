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
</style>
<div class="container-fluid py-4">
    <div class="card shadow-lg border-0 rounded-4">
       <div class="card-header text-white rounded-top-4 d-flex justify-content-between align-items-center" 
     style="background: linear-gradient(135deg, #1d3557, #457b9d);">
    <h2 class="mb-0 fw-bold text-center flex-grow-1">📊 Sales Report</h2>
    <button type="button" class="btn btn-danger btn-sm ms-3" onclick="window.location='{{ url('dashboard') }}'">Quit</button>

</div>

        <div class="card-body p-4">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('salebook.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" id="from_date" name="from_date" class="form-control" value="{{ $from_date }}" autofocus>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" id="to_date" name="to_date" class="form-control" value="{{ $to_date }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Series</label>
                        <select name="series"  id="series" class="form-select select2-single">
                            <option value="">All</option>
                            @foreach($seriesList as $s)
                                <option value="{{ $s }}" {{ request('series')==$s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Party</label>
                        <select name="party" id="party" class="form-select select2-single">
                            <option value="">All</option>
                            @foreach($partyList as $p)
                                <option value="{{ $p }}" {{ request('party')==$p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Material Center</label>
                        <select name="material_center" id="material_center" class="form-select select2-single">
                            <option value="">All</option>
                            @foreach($materialList as $m)
                                <option value="{{ $m }}" {{ request('material_center')==$m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Voucher No</label>
                        <input type="text" name="voucher_no" id="voucher_no" class="form-control" value="{{ request('voucher_no') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Item Filter</label>
                        <select name="item_id" id="item_id" class="form-select select2-single">
                            <option value="">All Items</option>
                            @foreach($itemList as $id => $name)
                                <option value="{{ $id }}" {{ request('item_id')==$id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bill Sundry Filter</label>
                        <select name="bill_sundry" id="bill_sundry" class="form-select select2-single">
                            <option value="">All Sundries</option>
                            @foreach($sundryList as $id => $name)
                                <option value="{{ $id }}" {{ request('bill_sundry')==$id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" id="filter" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>

            {{-- Sales Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle text-center shadow-sm">
                    <thead style="background-color:#264653; color:white;">
                        <tr>
                            @if($isItemFilter)
                                <th>Series</th>
                                <th>Date</th>
                                <th>Party</th>
                                <th>Voucher No</th>
                                <th style="background:#1d3557;color:#fff;">Item</th>
                                <th style="background:#1d3557;color:#fff;">Qty</th>
                                <th style="background:#1d3557;color:#fff;">Price</th>
                                <th style="background:#1d3557;color:#fff;">Amount</th>
                            @else
                                <th>Series</th>
                                <th>Date</th>
                                <th>Voucher No</th>
                                <th>Party</th>
                                <th>Material Center</th>
                                <th>Total</th>
                                <th style="background:#1d3557;color:#fff;">Item</th>
                                <th style="background:#1d3557;color:#fff;">Qty</th>
                                <th style="background:#1d3557;color:#fff;">Price</th>
                                <th style="background:#1d3557;color:#fff;">Amount</th>
                                @foreach($allSundries as $sundry)
                                    <th style="background:#2a9d8f;color:#fff;">{{ $sundry }}</th>
                                @endforeach
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $totalQty = 0;
                            $totalAmount = 0;
                            $grandTotal = 0;
                            $sundryTotals = array_fill_keys($allSundries, 0);
                        @endphp

                        @foreach($sales as $sale)
                            @if($isItemFilter)
                                @foreach($sale->items->where('id',$itemFilter) as $item)
                                    <tr>
                                        <td>{{ $sale->series_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sale->date)->format('d-M-Y') }}</td>
                                        <td>{{ $sale->party }}</td>
                                        <td>{{ $sale->voucher_no }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>₹{{ number_format($item->price,2) }}</td>
                                        <td>₹{{ number_format($item->amount,2) }}</td>
                                    </tr>
                                    @php
                                        $totalQty += $item->qty;
                                        $totalAmount += $item->amount;
                                    @endphp
                                @endforeach
                            @else
                                @php $rowspan = max(count($sale->items),1); @endphp
                                @foreach($sale->items as $index => $item)
                                    <tr>
                                        @if($index === 0)
                                            <td rowspan="{{ $rowspan }}">{{ $sale->series_no }}</td>
                                            <td rowspan="{{ $rowspan }}">{{ \Carbon\Carbon::parse($sale->date)->format('d-M-Y') }}</td>
                                            <td rowspan="{{ $rowspan }}">{{ $sale->voucher_no }}</td>
                                            <td rowspan="{{ $rowspan }}">{{ $sale->party }}</td>
                                            <td rowspan="{{ $rowspan }}">{{ $sale->material_center }}</td>
                                            <td rowspan="{{ $rowspan }}">₹{{ number_format($sale->total,2) }}</td>
                                        @endif

                                        <td>{{ $item->item_name }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>₹{{ number_format($item->price,2) }}</td>
                                        <td>₹{{ number_format($item->amount,2) }}</td>

                                        @if($index === 0)
                                            @foreach($allSundries as $sundry)
                                                <td rowspan="{{ $rowspan }}">₹{{ number_format($sale->sundries[$sundry]??0,2) }}</td>
                                                @php $sundryTotals[$sundry] += $sale->sundries[$sundry] ?? 0; @endphp
                                            @endforeach
                                        @endif
                                    </tr>
                                    @php
                                        $totalQty += $item->qty;
                                        $totalAmount += $item->amount;
                                    @endphp
                                @endforeach
                                @php $grandTotal += $sale->total; @endphp
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="fw-bold">
                        @if($isItemFilter)
                            <tr style="background:#f1faee;">
                                <th class="text-end" colspan="5">Total</th>
                                <th>{{ $totalQty }}</th>
                                <th></th>
                                <th>₹{{ number_format($totalAmount,2) }}</th>
                            </tr>
                        @else
                            <tr style="background:#f1faee;">
                                <th colspan="5" class="text-end">Grand Total</th>
                                <th>₹{{ number_format($grandTotal,2) }}</th>
                                <th></th>
                                <th>{{ $totalQty }}</th>
                                <th></th>
                                <th>₹{{ number_format($totalAmount,2) }}</th>
                                @foreach($sundryTotals as $total)
                                    <th>₹{{ number_format($total,2) }}</th>
                                @endforeach
                            </tr>
                        @endif
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


</script>

@endsection