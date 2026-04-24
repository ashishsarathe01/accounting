@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 col-lg-9 bg-mint px-4">

<div class="table-title-bottom-line d-flex justify-content-between bg-plum-viloet py-2 px-4">
    <h5 class="m-0">Business Activity Logs</h5>
</div>

<div class="bg-white shadow-sm mt-3">
<table class="table table-striped m-0 activity_table">
    <thead class="bg-light">
        <tr>
            <th>Date</th>
            <th>Module</th>
            <th>Party / Account</th>
            <th>Action</th>
            <th>Action By</th>
            <th class="text-center">View</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
            @php
                $old = is_array($log->old_data) ? $log->old_data : json_decode($log->old_data, true);
                $new = is_array($log->new_data) ? $log->new_data : json_decode($log->new_data, true);

                $partyId = null;

                switch ($log->module_type) {

                    case 'supplier_bonus':
                    case 'manage_supplier':
                        $partyId = $old['account_id'] ?? null;
                        break;

                    case 'boiler_fuel_supplier':
                        $partyId = $old['supplier']['account_id'] ?? null;
                        break;

                    case 'purchase_report':
                        $partyId = $purchaseContext[$log->module_id]['account_id'] ?? null;
                        break;

                    default:
                        $partyId = null;
                }
            @endphp


            <tr>
                <td>{{ date('d-m-Y H:i', strtotime($log->action_at)) }}</td>
                <td>{{ strtoupper(str_replace('_',' ', $log->module_type)) }}</td>
                <td>{{ $accountMap[$partyId] ?? '-' }}</td>
                <td>
                    @php
                        $actionLabel = $log->action == 2 ? 'Deleted' : 'Changed';
                        $actionClass = $log->action == 2 ? 'bg-danger' : 'bg-warning text-dark';
                    @endphp

                    <span class="badge {{ $actionClass }}">
                        {{ $actionLabel }}
                    </span>

                </td>
                <td>{{ optional(\App\Models\User::find($log->action_by))->name ?? 'System' }}</td>
                <td class="text-center">
                    <button
                        class="btn btn-link view-log"
                        data-log='@json($log)'
                        data-party="{{ $accountMap[$partyId] ?? '-' }}"
                    >
                        View
                    </button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
</div>

</div>
</div>
</section>
</div>

{{-- VIEW MODAL --}}
<div class="modal fade" id="activityModal">
<div class="modal-dialog modal-lg modal-dialog-centered">
<div class="modal-content p-3">

<div class="modal-header">
    <h5 class="modal-title">Activity Details</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <div id="activitySummary" class="mb-3"></div>
    <div id="activityDetails"></div>
</div>

<div class="modal-footer">
    <button class="btn btn-border-body" data-bs-dismiss="modal">Close</button>
    <button class="btn btn-success" id="approveBtn">Approve</button>
</div>

</div>
</div>
</div>

@include('layouts.footer')
<script>
    const accountMap  = @json($accountMap);
    const itemMap     = @json($itemMap);
    const headMap     = @json($headMap);
    const locationMap = @json($locationMap);
    const groupMap    = @json($groupMap); 
    const purchaseContext = @json($purchaseContext);
</script>
<script>
function resolveField(key, value, context = {}) {

    let label = key.replaceAll('_',' ').toUpperCase();
    let displayValue = value;

    if (key === 'account_id') {
        label = 'Supplier';
        displayValue = accountMap[value] ?? value;
    }

    if (key === 'item_id') {
        label = 'Item';
        displayValue = itemMap[value] ?? value;
    }

    if (key === 'head_id') {
        label = 'Head';
        displayValue = headMap[value] ?? value;
    }

    if (key === 'location_id') {
        label = 'Location';
        displayValue = locationMap[value] ?? value;
    }

    if (key === 'price' || key === 'head_rate') {
        label = 'Rate';
    }

    if (key === 'status') {
        label = 'Status';
        displayValue = value == 1 ? 'Active' : 'Inactive';
    }

    if (key === 'id' || key === 'parent_id' || key === 'company_id') {
        return null;
    }

    return { label, displayValue };
}

function renderObject(obj = {}, indent = 0) {

    let html = '';
    const pad = '&nbsp;'.repeat(indent * 4);

    for (const key in obj) {

        const value = obj[key];

        if (typeof value === 'object' && value !== null) {

            html += `
                <tr class="table-secondary">
                    <td colspan="3">${pad}<strong>${key.replaceAll('_',' ').toUpperCase()}</strong></td>
                </tr>
            `;

            if (Array.isArray(value)) {
                value.forEach((row, index) => {

                    if (row.item_id) {
                        html += `
                            <tr class="table-info">
                                <td colspan="3">
                                    <strong>Item:</strong> ${itemMap[row.item_id] ?? row.item_id}
                                </td>
                            </tr>
                        `;
                    }

                    html += renderObject(row, indent + 1);
                });
            } else {
                html += renderObject(value, indent + 1);
            }

            continue;
        }

        const resolved = resolveField(key, value);

        if (!resolved) continue;

        html += `
            <tr>
                <td>${pad}${resolved.label}</td>
                <td colspan="2">${resolved.displayValue ?? '-'}</td>
            </tr>
        `;
    }

    return html;
}

function renderDiff(oldObj = {}, newObj = {}, indent = 0, context = {}) {

    let html = '';
    const pad = '&nbsp;'.repeat(indent * 4);
    const keys = new Set([...Object.keys(oldObj), ...Object.keys(newObj)]);

    keys.forEach(key => {

        const oldVal = oldObj[key];
        const newVal = newObj[key];

        if (Array.isArray(oldVal) || Array.isArray(newVal)) {

            html += `
                <tr class="table-secondary">
                    <td colspan="3"><strong>${key.replaceAll('_',' ').toUpperCase()}</strong></td>
                </tr>
            `;

            (newVal || oldVal).forEach((row, index) => {
                const itemId = row.item_id || oldVal?.[index]?.item_id;

                html += `
                    <tr class="table-info">
                        <td colspan="3">
                            <strong>Item:</strong> ${itemMap[itemId] ?? itemId}
                        </td>
                    </tr>
                `;

                html += renderDiff(oldVal?.[index] || {}, newVal?.[index] || {}, indent + 1);
            });

            return;
        }

        if (
            typeof oldVal === 'object' && oldVal !== null ||
            typeof newVal === 'object' && newVal !== null
        ) {
            html += `
                <tr class="table-secondary">
                    <td colspan="3">${pad}<strong>${key.replaceAll('_',' ').toUpperCase()}</strong></td>
                </tr>
            `;
            html += renderDiff(oldVal || {}, newVal || {}, indent + 1);
            return;
        }

        if (oldVal != newVal) {

            const oldResolved = resolveField(key, oldVal);
            const newResolved = resolveField(key, newVal);

            if (!oldResolved && !newResolved) return;

            html += `
                <tr>
                    <td>${pad}${oldResolved?.label ?? newResolved.label}</td>
                    <td class="text-danger">${oldResolved?.displayValue ?? '-'}</td>
                    <td class="fw-bold text-success">${newResolved?.displayValue ?? '-'}</td>
                </tr>
            `;
        }
    });

    return html;
}


$(document).on('click', '.view-log', function () {

    let log   = $(this).data('log');
    let party = $(this).data('party');
    let ctx = purchaseContext[log.module_id] || {};
    let groupName = groupMap[ctx.group_id] ?? '-';
    let supplierName = accountMap[ctx.account_id] ?? party ?? '-';
    let slipNo = ctx.voucher_no ?? log.module_id;
    const oldData = typeof log.old_data === 'string'
        ? JSON.parse(log.old_data)
        : (log.old_data || {});

    const newData = typeof log.new_data === 'string'
        ? JSON.parse(log.new_data)
        : (log.new_data || {});

    $('#approveBtn').data('id', log.id).show();

    let summaryHtml = `
    <div><strong>Module:</strong> PURCHASE REPORT</div>
    <div><strong>Supplier:</strong> ${supplierName}</div>
    <div><strong>Group:</strong> ${groupName}</div>
    <div><strong>Slip No:</strong> ${slipNo}</div>
    <div><strong>Action:</strong> ${log.action == 2 ? 'DELETED' : 'CHANGED'}</div>
    <div><strong>Date:</strong> ${log.action_at}</div>
`;

$('#activitySummary').html(summaryHtml);

    let html = `
        <table class="table table-bordered font-14">
            <thead class="bg-light">
                <tr>
                    <th width="30%">Field</th>
                    <th width="35%">Old</th>
                    <th width="35%">New</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (log.action == 2) {
        html += `
            <tr class="table-danger">
                <td colspan="3"><strong>Deleted Snapshot</strong></td>
            </tr>
        `;
        html += renderObject(oldData);
    }

    else {

        if (log.module_type === 'purchase_report') {
            html = renderPurchaseReportDiff(oldData, newData);
        } else {
            html += renderDiff(oldData, newData);
        }
    }


    html += '</tbody></table>';

    $('#activityDetails').html(html);
    $('#activityModal').modal('show');
});

$('#approveBtn').on('click', function () {

    const id = $(this).data('id');

    const url = "{{ route('business.activity.logs.approve', ['id' => '__ID__']) }}"
        .replace('__ID__', id);

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function (res) {
            if (res.status) {
                $('#activityModal').modal('hide');

                $('.view-log').filter(function () {
                    return $(this).data('log').id == id;
                }).closest('tr').remove();
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert('Approve failed. Check console.');
        }
    });
});

function renderPurchaseReportDiff(oldData, newData) {

    let html = `
        <table class="table table-bordered font-14">
            <thead class="bg-light">
                <tr>
                    <th width="30%">Head</th>
                    <th width="30%">Field</th>
                    <th width="20%">Old</th>
                    <th width="20%">New</th>
                </tr>
            </thead>
            <tbody>
    `;

    const oldHeads = oldData?.heads || {};
    const newHeads = newData?.heads || {};

    Object.keys({...oldHeads, ...newHeads}).forEach(headId => {

        const oldHead = oldHeads[headId] || {};
        const newHead = newHeads[headId] || {};

        const headName =
            headId === 'cut' ? 'CUT' :
            headId === 'short_weight' ? 'SHORT WEIGHT' :
            headMap[headId] ?? headId;

        ['net_weight', 'bill_rate', 'contract_rate'].forEach(field => {

            if (oldHead[field] == newHead[field]) return;

            html += `
                <tr>
                    <td><strong>${headName}</strong></td>
                    <td>${field.replace('_',' ').toUpperCase()}</td>
                    <td class="text-danger">${oldHead[field] ?? '-'}</td>
                    <td class="fw-bold text-success">${newHead[field] ?? '-'}</td>
                </tr>
            `;
        });
    });

    html += '</tbody></table>';
    return html;
}

</script>

@endsection
