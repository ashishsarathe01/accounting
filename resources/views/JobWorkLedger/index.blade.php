@extends('layouts.app')
@section('content')

@include('layouts.header')

<style>
.group-toggle:hover {
    background: #e9ecef;
    cursor: pointer;
}

.toggle-icon {
    font-size: 12px;
}

.item-row td {
    padding-left: 25px;
}

.select2-container .select2-selection--single {
    height: 38px !important;
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    padding: 5px 10px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px !important;
}
.select2-container {
    width: 100% !important;
}
.d-flex .form-control,
.d-flex .select2-container {
    min-width: 180px;
}
.d-flex {
    gap: 10px;
}

tr.party-header td {
    background: #dce0f0;
    color: #1a1a2e;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    user-select: none;
    padding: 7px 12px;
    border-bottom: 2px solid #b0b8d8;
}
tr.party-header:hover td {
    background: #c9d0eb;
    cursor: pointer;
}

tr.item-header td {
    background: #f8f9fa;
    color: #333;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    user-select: none;
    padding: 6px 12px 6px 28px;
    border-bottom: 1px solid #dee2e6;
}
tr.item-header:hover td {
    background: #e9ecef;
}

.item-summary {
    font-size: 12px;
    color: #6c757d;
    margin-left: 10px;
}
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- TITLE BAR (original) --}}
                <div class="table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4">
                    <h5 class="m-0">Job Work Ledger</h5>

                    {{-- FILTER FORM --}}
                    <form method="POST" action="{{ route('job_work_ledger.fetch') }}">
                        @csrf
                        <div class="d-flex">
                            <input type="date" name="from_date" class="form-control"
                                value="{{ $filters['from_date'] ?? $fy_start_date }}" required>

                            <input type="date" name="to_date" class="form-control ms-2"
                                value="{{ $filters['to_date'] ?? $fy_end_date }}" required>

                            <select name="party_id" class="form-select party-select ms-2">
                                <option value="all" {{ ($filters['party_id'] ?? 'all') == 'all' ? 'selected' : '' }}>
                                    All Parties
                                </option>
                                @foreach($parties as $party)
                                    <option value="{{ $party->id }}"
                                        {{ ($filters['party_id'] ?? '') == $party->id ? 'selected' : '' }}>
                                        {{ $party->account_name }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="item_id" class="form-select item-select ms-2">
                                <option value="all" {{ ($filters['item_id'] ?? 'all') == 'all' ? 'selected' : '' }}>
                                    All Items
                                </option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}"
                                        {{ ($filters['item_id'] ?? '') == $item->id ? 'selected' : '' }}>
                                        {{ $item->name }}
                                    </option>
                                @endforeach
                            </select>

                            <button class="btn btn-info ms-2">Go</button>
                        </div>
                    </form>
                </div>

                <div class="transaction-table bg-white shadow-sm">
                    <table class="table sale_table">
                        <thead>
                            <tr class="bg-light-pink">
                                <th>Date</th>
                                <th>Party</th>
                                <th>Item</th>
                                <th>Type</th>
                                <th class="text-end">In Qty</th>
                                <th class="text-end">Out Qty</th>
                                <th class="text-end">Balance</th>
                                <th>Voucher No</th>
                            </tr>
                        </thead>
                        <tbody>

                        @if(!empty($ledgerData) && count($ledgerData))

                            @php $partyIdx = 0; @endphp

                            @foreach($ledgerData as $partyName => $itemsGroup)
                                @php
                                    $partyId = 'party-' . $partyIdx++;

                                    $partyTotalIn  = 0;
                                    $partyTotalOut = 0;
                                    $partyLastBal  = 0;
                                    foreach ($itemsGroup as $itData) {
                                        $partyTotalIn  += $itData['totals']['in'];
                                        $partyTotalOut += $itData['totals']['out'];
                                        $partyLastBal  += $itData['totals']['balance'];
                                    }
                                @endphp

                                <tr class="party-header group-toggle" data-party="{{ $partyId }}">
                                    <td colspan="8">
                                        <span class="toggle-icon me-2">▼</span>
                                        <strong>{{ $partyName }}</strong>
                                        <span class="item-summary party-summary" data-party="{{ $partyId }}" style="display:none;">
                                            <strong>In Qty: {{ $partyTotalIn }} | Out Qty: {{ $partyTotalOut }} | Balance: {{ $partyLastBal }}</strong>
                                        </span>
                                    </td>
                                </tr>

                                @php $itemIdx = 0; @endphp

                                @foreach($itemsGroup as $itemName => $data)
                                    @php
                                        $itemId = $partyId . '-item-' . $itemIdx++;
                                        $totals = $data['totals'];
                                        $unit   = $totals['unit'] ?? '';
                                    @endphp

                                    <tr class="item-header group-toggle party-child-row {{ $partyId }}"
                                        data-item="{{ $itemId }}">
                                        <td colspan="8">
                                            <span class="toggle-icon me-2">▼</span>
                                            <strong>{{ $itemName }}</strong>
                                            <span class="ms-3 text-muted item-summary item-summary-badge" data-item="{{ $itemId }}" style="display:none;">
                                                <strong>In Qty: {{ $totals['in'] }} {{ $unit }} | Out Qty: {{ $totals['out'] }} {{ $unit }} | Balance: {{ $totals['balance'] }} {{ $unit }}</strong>
                                            </span>
                                        </td>
                                    </tr>

                                    @foreach($data['rows'] as $row)
                                        <tr class="item-row item-child-row {{ $itemId }}">
                                            <td>{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                                            <td>{{ $row['party_name'] }}</td>
                                            <td>{{ $row['item_name'] }}</td>
                                            <td>{{ $row['type'] }}</td>
                                            <td class="text-end">{{ $row['in_qty']  ? $row['in_qty'].' '.$row['unit']  : '–' }}</td>
                                            <td class="text-end">{{ $row['out_qty'] ? $row['out_qty'].' '.$row['unit'] : '–' }}</td>
                                            <td class="text-end">{{ $row['balance'] }} {{ $row['unit'] }}</td>
                                            <td>{{ $row['voucher_no'] }}</td>
                                        </tr>
                                    @endforeach

                                    <tr class="fw-bold bg-light item-row item-child-row {{ $itemId }}">
                                        <td colspan="4" class="text-end">TOTAL</td>
                                        <td class="text-end">{{ $totals['in'] }} {{ $unit }}</td>
                                        <td class="text-end">{{ $totals['out'] }} {{ $unit }}</td>
                                        <td class="text-end">{{ $totals['balance'] }} {{ $unit }}</td>
                                        <td></td>
                                    </tr>

                                @endforeach

                            @endforeach

                        @else
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    {{ !empty($filters) ? 'No records found.' : 'Please select filters and click Go.' }}
                                </td>
                            </tr>
                        @endif

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
$(document).ready(function () {
    $('.party-select').select2({ placeholder: 'All Parties', allowClear: true, width: '100%' });
    $('.item-select').select2({  placeholder: 'All Items',   allowClear: true, width: '100%' });
});
</script>

<script>
(function () {
    const STORAGE_KEY = 'jwl_toggle_state_v2';
    let state = {};
    try { state = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {}; } catch(e) {}

    function save() {
        try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state)); } catch(e) {}
    }

    function setRowsVisible(cls, visible) {
        document.querySelectorAll('tr.' + cls).forEach(tr => {
            tr.style.display = visible ? '' : 'none';
        });
    }

    function setIcon(row, open) {
        const icon = row.querySelector('.toggle-icon');
        if (icon) icon.textContent = open ? '▼' : '▶';
    }

    function applyParty(partyId, partyRow, open, doSave) {
        state[partyId] = open;
        setIcon(partyRow, open);

        const badge = partyRow.querySelector('.party-summary');
        if (badge) badge.style.display = open ? 'none' : '';

        document.querySelectorAll('tr.party-child-row.' + partyId).forEach(itemRow => {
            itemRow.style.display = open ? '' : 'none';

            const itemId = itemRow.dataset.item;

            if (!open) {
                setRowsVisible(itemId, false);
            } else {
                // Party re-opened → restore each item to its own saved state
                const itemOpen = state[itemId] !== undefined ? state[itemId] : true;
                setRowsVisible(itemId, itemOpen);
                setIcon(itemRow, itemOpen);
                const itemBadge = itemRow.querySelector('.item-summary-badge');
                if (itemBadge) itemBadge.style.display = itemOpen ? 'none' : '';
            }
        });

        if (doSave) save();
    }

    function applyItem(itemId, itemRow, open, doSave) {
        state[itemId] = open;
        setIcon(itemRow, open);

        // Item summary badge: visible only when COLLAPSED
        const badge = itemRow.querySelector('.item-summary-badge');
        if (badge) badge.style.display = open ? 'none' : '';

        // Show/hide all data + total rows for this item
        setRowsVisible(itemId, open);

        if (doSave) save();
    }

    document.querySelectorAll('tr.party-header').forEach(partyRow => {
        const partyId = partyRow.dataset.party;
        const isOpen  = state[partyId] !== undefined ? state[partyId] : true;
        applyParty(partyId, partyRow, isOpen, false);

        partyRow.addEventListener('click', function () {
            applyParty(partyId, partyRow, !state[partyId], true);
        });
    });

    document.querySelectorAll('tr.item-header').forEach(itemRow => {
        const itemId = itemRow.dataset.item;
        const isOpen = state[itemId] !== undefined ? state[itemId] : true;
        applyItem(itemId, itemRow, isOpen, false);

        itemRow.addEventListener('click', function (e) {
            e.stopPropagation(); // prevent party click from firing
            applyItem(itemId, itemRow, !state[itemId], true);
        });
    });

})();
</script>

@endsection