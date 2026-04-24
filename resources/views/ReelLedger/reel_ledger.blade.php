@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
    .table thead th {
        background: #f1f5f9;
        font-weight: bold;
        border-bottom: 2px solid #ddd;
    }
    .summary-box {
        background: #eef6ff;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        border-left: 4px solid #3b82f6;
    }
    .summary-title {
        font-size: 16px;
        font-weight: 600;
        color: #0b3d91;
    }
    .summary-value {
        font-size: 20px;
        font-weight: bold;
        color: #111;
    }
    .alt-row:nth-child(even) {
        background: #f9fcff;
    }
    td:hover {
        background: #fff4d3 !important;
        cursor: pointer;
    }
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')

       <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
        <div class="">

    <!-- =======================
         PAGE TITLE + FILTER
    ======================== -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">Item Ledger – Reel Wise</h4>

        <form method="GET" action="{{ url('opening-stock/filter') }}" class="mt-3">
            <div class="row g-2">

                <div class="col-md-3">
                    <label>Item *</label>
                    <select class="form-select select2-single" id="item_name" name="item_id" required>
                        <option value="">SELECT QUALITY</option>
                        @foreach ($items as $item)
                           <option value="{{ $item->item_id }}" 
                                        {{ request('item_id') == $item->item_id ? 'selected' : '' }}>
                                    {{ $item->name }}
                                </option>

                               
                            </option>
                        @endforeach
                    </select>
                </div>

               <div class="col-md-3">
                    <label>From Date *</label>
                    <input type="date" class="form-control" name="f_date"
                        value="{{ request('f_date') ?? $from_date ?? '' }}" required>
                </div>


                <div class="col-md-3">
                    <label>To Date *</label>
                    <input type="date" class="form-control" name="t_date"
                           value="{{ request('t_date') ?? $to_date ?? '' }}" required>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-info w-100">Search</button>
                </div>
            </div>
        </form>

        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>


    <!-- =======================
         SUMMARY BOXES
    ======================== -->
    <div class="row">
        <div class="col-md-4">
            <div class="summary-box">
                <div class="summary-title">Opening Reels</div>
                <div class="summary-value">{{ $opening_count }}</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-box">
                <div class="summary-title">Opening Weight</div>
                <div class="summary-value">{{ formatIndianNumber($opening_weight,2) }} Kg</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-box">
                <div class="summary-title">Item</div>
                <div class="summary-value" id="item_opening_name"> {{ $itemName ?? '' }}</div>
            </div>
        </div>
    </div>



    <!-- =======================
         DATE-WISE LEDGER TABLE
    ======================== -->
    <div class="card shadow-sm mt-3">
        <div class="card-header bg-primary text-white fw-bold">
            Date-wise Reel Ledger
        </div>

        <div class="card-body p-0">
            <table class="table table-bordered mb-5">
                <thead>
                <tr>
                    <th style="text-align:center; border-right:3px solid #ffecb3; border-bottom:3px solid #ffecb3;" width="120">Date</th>
                    <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">Opening Reels</th>
                    <th style="text-align:right; border-right:3px solid #ffecb3; border-bottom:3px solid #ffecb3;" width="120">Opening Weight</th>
                    <th style="text-align:right;border-bottom:3px solid #ffecb3; " width="120">In Reels</th>
                    <th style="text-align:right; border-right:3px solid #ffecb3; border-bottom:3px solid #ffecb3;" width="120">In Weight</th>
                    <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">Out Reels</th>
                    <th style="text-align:right; border-right:3px solid #ffecb3; border-bottom:3px solid #ffecb3;"  width="120">Out Weight</th>
                    <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">Closing Reels</th>
                    <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">Closing Weight</th>
                </tr>
                </thead>

                <tbody style="margin-bottom:10px;">

                @foreach($ledgerRows as $row)
                @if($row['in_count'] == 0 && $row['out_count'] == 0)
                    @continue
                @endif
                    <tr class="alt-row">

                        <td style="text-align:center; border-right:3px solid #ffecb3;" >{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>

                        <!-- Opening -->
                        <td style="text-align:right;" ondblclick="showDetails('{{ $row['date'] }}','opening')">
                            {{ $row['opening_count'] }}
                        </td>

                        <td style="text-align:right; border-right:3px solid #ffecb3;" ondblclick="showDetails('{{ $row['date'] }}','opening')">
                            {{ formatIndianNumber($row['opening']['weight'],2) }}
                        </td>

                        <!-- In -->
                        <td style="text-align:right;" ondblclick="showDetails('{{ $row['date'] }}','in')">
                            {{ $row['in_count'] }}
                        </td>

                        <td  style="text-align:right; border-right:3px solid #ffecb3;" ondblclick="showDetails('{{ $row['date'] }}','in')">
                            {{ formatIndianNumber($row['in']['weight'],2) }}
                        </td>

                        <!-- Out -->
                        <td style="text-align:right;" ondblclick="showDetails('{{ $row['date'] }}','out')">
                            {{ $row['out_count'] }}
                        </td>

                        <td style="text-align:right; border-right:3px solid #ffecb3;" ondblclick="showDetails('{{ $row['date'] }}','out')">
                            {{ formatIndianNumber($row['out']['weight'],2) }}
                        </td>

                        <!-- Closing -->
                        <td style="text-align:right;" 
    ondblclick="showDetails('{{ $row['date'] }}','closing')">

    @if($row['closing_count'] < 0)
        <span class="text-danger fw-bold">
            {{ $row['closing_count'] }}
        </span>
    @else
        {{ $row['closing_count'] }}
    @endif

</td>

                        <td style="text-align:right;" ondblclick="showDetails('{{ $row['date'] }}','closing')">
                            {{ formatIndianNumber($row['closing']['weight'],2) }}
                        </td>

                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>

      <div class="row mt-3">
        <div class="col-md-4">
            <div class="summary-box">
                <div class="summary-title">Closing Reels</div>
                <div class="summary-value">{{ $closing_count }}</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-box">
                <div class="summary-title">Closing Weight</div>
                <div class="summary-value">{{ formatIndianNumber($closing_weight,2) }} Kg</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="summary-box">
                <div class="summary-title">Item</div>
                <div class="summary-value" id="item_closing_name" > {{ $itemName ?? '' }}</div>
            </div>
        </div>
    </div>
</div>

</div>
</div>
            </section>
</div>

<!-- ====================================
     MODAL FOR REEL DETAILS (AJAX LOAD)
==================================== -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Reel Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="modalContent">
                Loading...
            </div>
        </div>

    </div>
</div>


</body>
@include('layouts.footer')
<script>
    
window.ledgerData = @json($ledgerRows);

function showDetails(date, type) {
     console.log("TYPE RECEIVED:", type);  
    $("#detailsModal").modal("show");

    const row = window.ledgerData.find(r => r.date === date);

    if (!row || !row[type]) {
        $("#modalContent").html("<p class='text-danger'>No data found</p>");
        return;
    }

    const reels = row[type].reels;

    let html = `
        <h5 class="mb-3 text-primary">
            ${type.toUpperCase()} - ${date} (Total: ${row[type].weight.toFixed(2)} Kg)
        </h5>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Transaction</th>
                    <th>Reel No.</th>
                    <th>Width</th>
                    <th>Weight</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
    `;
    // console.log(reels.length);return;
    if (reels.length === 0) {
        html += `<tr><td colspan="4" class="text-center text-muted">No reels</td></tr>`;
    } else {
        reels.forEach(r => {
    let transaction = '';
    let link = '';  // <-- IMPORTANT: define link variable

    if (type === 'out' && r.sale_id) {
        transaction = 'Sale';
        link = "{{ url('sale-invoice') }}/" + r.sale_id;
    }
    else if ((type === 'in' || type === 'opening' || type === 'closing') && r.sale_return_id) {
        transaction = 'Sale Return';
        link = "{{ url('sale-return-invoice') }}/" + r.sale_return_id;
    }
    else if ((type === 'in' || type === 'opening' || type === 'closing') && r.sj_generated_id) {
        transaction = 'Stock Journal';
        link = "{{ url('edit-stock-journal') }}/" + r.sj_generated_id;
    }
    else if (type === 'out' && r.sj_consumption_id) {
        transaction = 'Stock Journal';
        link = "{{ url('edit-stock-journal') }}/" + r.sj_consumption_id;
    }
    else if ((type === 'in' || type === 'opening' || type === 'closing') && r.purchase_id) {
        transaction = 'Purchase';
        link = "{{ url('purchase-invoice') }}/" + r.purchase_id;
    }
    else if (type === 'out' && r.purchase_return_id) {
        transaction = 'Purchase Return';
        link = "{{ url('purchase-return-invoice') }}/" + r.purchase_return_id;
    }
    else if ((type === 'in' || type === 'opening' || type === 'closing') && r.deckle_id == 0) {
        transaction = 'Opening';
        link = "{{ url('production.set_item/edit') }}/" + r.production_id;
    }
    else if ((type === 'in' || type === 'opening' || type === 'closing') && r.deckle_id > 0) {
        transaction = 'Production';
        link = "";   // No link available
    }
    else {
        transaction = '-';
        link = "";
    }

    html += `
        <tr class="clickable-row" data-link="${link}">
            <td>${transaction}</td>
            <td>${r.reel_no}</td>
            <td>${r.size ?? '-'}</td>
            <td>${parseFloat(r.weight).toFixed(2)}</td>
            <td>${r.unit ?? ''}</td>
        </tr>
    `;
});
    }

    html += `</tbody></table>`;

    $("#modalContent").html(html);
    $("#modalContent").on("click", ".clickable-row", function () {
    const link = $(this).data("link");

    if (link) {
        window.location.href = link;
    }
});

}





</script>

@endsection
