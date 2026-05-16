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
    td.clickable-cell:hover {
        background: #fff4d3 !important;
        cursor: pointer;
    }
    /* Modal loading spinner */
    .modal-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        gap: 12px;
        color: #6c757d;
    }
    /* Clickable row highlight */
    .clickable-row:hover {
        background: #f0f8ff !important;
        cursor: pointer;
    }
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                <div class="">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="fw-bold">Item Ledger – Reel Wise</h4>

                        <form method="GET" action="{{ url('opening-stock-reel-wise/filter') }}" class="mt-3">
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
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label>From Date *</label>
                                    <input type="date" class="form-control" name="f_date"
                                        value="{{ request('f_date') ?? $f_date ?? '' }}" required>
                                </div>

                                <div class="col-md-3">
                                    <label>To Date *</label>
                                    <input type="date" class="form-control" name="t_date"
                                        value="{{ request('t_date') ?? $t_date ?? '' }}" required>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button class="btn btn-info w-100">Search</button>
                                </div>
                            </div>
                        </form>

                        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm">Back</a>
                    </div>

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
                                <div class="summary-value">{{ formatIndianNumber($opening_weight, 2) }} Kg</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="summary-box">
                                <div class="summary-title">Item</div>
                                <div class="summary-value" id="item_opening_name">{{ $itemName ?? '' }}</div>
                            </div>
                        </div>
                    </div>


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
                                        <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">In Reels</th>
                                        <th style="text-align:right; border-right:3px solid #ffecb3; border-bottom:3px solid #ffecb3;" width="120">In Weight</th>
                                        <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">Out Reels</th>
                                        <th style="text-align:right; border-right:3px solid #ffecb3; border-bottom:3px solid #ffecb3;" width="120">Out Weight</th>
                                        <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">Closing Reels</th>
                                        <th style="text-align:right; border-bottom:3px solid #ffecb3;" width="120">Closing Weight</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach($ledgerRows as $row)
                                        @if($row['in_count'] == 0 && $row['out_count'] == 0)
                                            @continue
                                        @endif
                                        <tr class="alt-row">

                                            <td style="text-align:center; border-right:3px solid #ffecb3;">
                                                {{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right;"
                                                onclick="showDetails('{{ $row['date'] }}', 'opening')">
                                                {{ $row['opening_count'] }}
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right; border-right:3px solid #ffecb3;"
                                                onclick="showDetails('{{ $row['date'] }}', 'opening')">
                                                {{ formatIndianNumber($row['opening_weight'], 2) }}
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right;"
                                                onclick="showDetails('{{ $row['date'] }}', 'in')">
                                                {{ $row['in_count'] }}
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right; border-right:3px solid #ffecb3;"
                                                onclick="showDetails('{{ $row['date'] }}', 'in')">
                                                {{ formatIndianNumber($row['in_weight'], 2) }}
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right;"
                                                onclick="showDetails('{{ $row['date'] }}', 'out')">
                                                {{ $row['out_count'] }}
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right; border-right:3px solid #ffecb3;"
                                                onclick="showDetails('{{ $row['date'] }}', 'out')">
                                                {{ formatIndianNumber($row['out_weight'], 2) }}
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right;"
                                                onclick="showDetails('{{ $row['date'] }}', 'closing')">
                                                @if($row['closing_count'] < 0)
                                                    <span class="text-danger fw-bold">{{ $row['closing_count'] }}</span>
                                                @else
                                                    {{ $row['closing_count'] }}
                                                @endif
                                            </td>

                                            <td class="clickable-cell"
                                                style="text-align:right;"
                                                onclick="showDetails('{{ $row['date'] }}', 'closing')">
                                                {{ formatIndianNumber($row['closing_weight'], 2) }}
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
                                <div class="summary-value">{{ formatIndianNumber($closing_weight, 2) }} Kg</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="summary-box">
                                <div class="summary-title">Item</div>
                                <div class="summary-value" id="item_closing_name">{{ $itemName ?? '' }}</div>
                            </div>
                        </div>
                    </div>

                </div>{{-- /.col inner --}}
            </div>{{-- /.col-lg-9 --}}
        </div>{{-- /.row --}}
    </section>
</div>
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="detailsModalLabel">Reel Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="modalContent">
                {{-- Content injected by JS after AJAX --}}
            </div>

        </div>
    </div>
</div>


@include('layouts.footer')

<script>
const MODAL_DETAIL_URL = "{{ route('reel.ledger.modal.detail') }}";
const ITEM_ID          = "{{ $item_id }}";
const F_DATE           = "{{ $f_date }}";   

const BASE_URLS = {
    sale          : "{{ url('sale-invoice') }}/",
    saleReturn    : "{{ url('sale-return-invoice') }}/",
    stockJournal  : "{{ url('edit-stock-journal') }}/",
    purchase      : "{{ url('purchase-invoice') }}/",
    purchaseReturn: "{{ url('purchase-return-invoice') }}/",
    productionEdit: "{{ url('production.set_item/edit') }}/",
};

function getTransactionInfo(r, type) {
    if (type === 'out' && r.sale_id) {
        return { label: 'Sale',            link: BASE_URLS.sale + r.sale_id };
    }
    if ((type === 'in' || type === 'opening' || type === 'closing') && r.sale_return_id) {
        return { label: 'Sale Return',     link: BASE_URLS.saleReturn + r.sale_return_id };
    }
    if ((type === 'in' || type === 'opening' || type === 'closing') && r.sj_generated_id) {
        return { label: 'Stock Journal',   link: BASE_URLS.stockJournal + r.sj_generated_id };
    }
    if (type === 'out' && r.sj_consumption_id) {
        return { label: 'Stock Journal',   link: BASE_URLS.stockJournal + r.sj_consumption_id };
    }
    if ((type === 'in' || type === 'opening' || type === 'closing') && r.purchase_id) {
        return { label: 'Purchase',        link: BASE_URLS.purchase + r.purchase_id };
    }
    if (type === 'out' && r.purchase_return_id) {
        return { label: 'Purchase Return', link: BASE_URLS.purchaseReturn + r.purchase_return_id };
    }
    if ((type === 'in' || type === 'opening' || type === 'closing') && r.deckle_id == 0) {
        return { label: 'Opening',         link: BASE_URLS.productionEdit + (r.production_id ?? '') };
    }
    if ((type === 'in' || type === 'opening' || type === 'closing') && r.deckle_id > 0) {
        return { label: 'Production',      link: '' };
    }
    return { label: '-', link: '' };
}
function renderReelTable(reels, type, totalWeight, date) {
    const formattedDate = (() => {
        const [y, m, d] = date.split('-');
        return `${d}-${m}-${y}`;
    })();

    let html = `
        <h5 class="mb-3 text-primary">
            ${type.toUpperCase()} &nbsp;|&nbsp; ${formattedDate}
            &nbsp;
            <small class="text-secondary fw-normal">
                (${reels.length} reel${reels.length !== 1 ? 's' : ''}
                &nbsp;·&nbsp; ${parseFloat(totalWeight).toFixed(2)} Kg)
            </small>
        </h5>

        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Transaction</th>
                    <th>Reel No.</th>
                    <th>Width</th>
                    <th style="text-align:right;">Weight</th>
                    <th>Unit</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (!reels || reels.length === 0) {
        html += `<tr><td colspan="6" class="text-center text-muted py-3">No reels found</td></tr>`;
    } else {
        reels.forEach((r, idx) => {
            const { label, link } = getTransactionInfo(r, type);
            const rowClass = link ? 'clickable-row' : '';
            const dataLink = link ? `data-link="${link}"` : '';

            html += `
                <tr class="${rowClass}" ${dataLink} style="${link ? 'cursor:pointer;' : ''}">
                    <td>${idx + 1}</td>
                    <td>${label}</td>
                    <td>${r.reel_no ?? '-'}</td>
                    <td>${r.size ?? '-'}</td>
                    <td style="text-align:right;">${parseFloat(r.weight).toFixed(2)}</td>
                    <td>${r.unit ?? ''}</td>
                </tr>
            `;
        });
    }

    html += `</tbody></table>`;
    return html;
}
function showDetails(date, type) {

    $("#detailsModal").modal("show");
    $("#detailsModalLabel").text(
        type.charAt(0).toUpperCase() + type.slice(1) + ' Reels — Loading…'
    );
    $("#modalContent").html(`
        <div class="modal-loading">
            <div class="spinner-border spinner-border-sm text-info" role="status"></div>
            <span>Fetching reel details…</span>
        </div>
    `);

    const params = {
        item_id : ITEM_ID,
        date    : date,
        type    : type,
        f_date  : F_DATE,  
    };

    $.ajax({
        url     : MODAL_DETAIL_URL,
        method  : 'GET',
        data    : params,
        success : function (response) {
            const html = renderReelTable(
                response.reels,
                type,
                response.total_weight,
                date
            );

            $("#detailsModalLabel").text(
                type.charAt(0).toUpperCase() + type.slice(1) + ' Reels'
            );
            $("#modalContent").html(html);

            /* 4. Bind row-click navigation (same as before) */
            $("#modalContent").off("click", ".clickable-row")
                              .on("click", ".clickable-row", function () {
                const link = $(this).data("link");
                if (link) window.location.href = link;
            });
        },
        error : function (xhr) {
            let msg = 'Failed to load reel details. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            $("#modalContent").html(
                `<div class="alert alert-danger mb-0">${msg}</div>`
            );
        }
    });
}
</script>

@endsection