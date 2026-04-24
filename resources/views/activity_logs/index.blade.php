@extends('layouts.app')
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

    <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
        <h5 class="transaction-table-title m-0">Activity Logs</h5>

        <div class="d-md-flex d-block">
            <input type="text" id="search" class="form-control" placeholder="Search">
        </div>
    </div>

    <div class="transaction-table bg-white table-view shadow-sm">
        <table class="table-striped table m-0 shadow-sm activity_table">
            <thead>
                <tr class="font-12 text-body bg-light-pink">
                    <th>Date</th>
                    <th>Module</th>
                    <th>Reference</th>
                    <th>Party / Account</th>
                    <th>Action</th>
                    <th>Action By</th>
                    <th class="text-center">View</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                    @php
                        $data = $log->action === 'edit'
                            ? ($log->new_data ?? [])
                            : ($log->old_data ?? []);
                    @endphp
                    <tr class="font-14 font-heading bg-white">
                        <td>{{ date('d-m-Y H:i', strtotime($log->action_at)) }}</td>
                        <td>{{ ucfirst($log->module_type) }}</td>
                        @php
                            $map = [
                                'sale'            => 'sale',
                                'sale_return'     => 'sale_return',
                                'purchase_return' => 'purchase_return',
                                'payment'         => 'payment',
                                'receipt'         => 'receipt',
                                'journal'         => 'journal',
                                'contra'          => 'contra',
                                'stock_journal'   => 'stock_journal',
                                'stock_transfer'  => 'stock_transfer',
                            ];

                            $rootKey = $map[$log->module_type] ?? 'purchase';
                        @endphp

                        @php
                            $refMap = [
                                'sale' => fn () =>
                                    $data['sale']['voucher_no_prefix']
                                    ?? $data['sale']['voucher_no']
                                    ?? '-',

                                'sale_return' => fn () =>
                                    $data['sale_return']['sr_prefix']
                                    ?? $data['sale_return']['sale_return_no']
                                    ?? '-',

                                'purchase_return' => fn () =>
                                    $data['purchase_return']['sr_prefix']
                                    ?? $data['purchase_return']['purchase_return_no']
                                    ?? '-',

                                'payment' => fn () =>
                                    $data['payment']['voucher_no'] ?? '-',

                                'receipt' => fn () =>
                                    $data['receipt']['voucher_no'] ?? '-',

                                'journal' => fn () =>
                                    $data['journal']['voucher_no'] ?? '-',

                                'contra' => fn () =>
                                    $data['contra']['voucher_no'] ?? '-',

                                'stock_journal' => fn () =>
                                    $data['stock_journal']['voucher_no_prefix'] ?? '-',

                                'stock_transfer' => fn () =>
                                    $data['stock_transfer']['voucher_no'] ?? '-',
                            ];

                            $ref = isset($refMap[$log->module_type])
                                ? $refMap[$log->module_type]()
                                : (
                                    $data['purchase']['voucher_no_prefix']
                                    ?? $data['purchase']['voucher_no']
                                    ?? '-'
                                );
                        @endphp

                        <td>{{ $ref }}</td>

                        <td>
                            @php
                            $partyId = null;

                            if ($log->module_type === 'payment') {

                                $partyId = collect($data['details'] ?? [])
                                    ->where('type', 'Debit')
                                    ->pluck('account_name')
                                    ->first();

                            } elseif ($log->module_type === 'receipt') {   

                                $partyId = collect($data['details'] ?? [])
                                    ->where('type', 'Credit')
                                    ->pluck('account_name')
                                    ->first();

                            } elseif ($log->module_type === 'journal') {

                                // Prefer CREDIT account (standard journal meaning)
                                $partyId = collect($data['details'] ?? [])
                                    ->where('type', 'Credit')
                                    ->pluck('account_name')
                                    ->first();
                            } elseif ($log->module_type === 'contra') {

                                // Prefer CREDIT account, fallback to DEBIT
                                $partyId = collect($data['details'] ?? [])
                                    ->where('type', 'Credit')
                                    ->pluck('account_name')
                                    ->first()
                                    ?? collect($data['details'] ?? [])
                                        ->where('type', 'Debit')
                                        ->pluck('account_name')
                                        ->first();
                            } elseif ($log->module_type === 'stock_journal') {
                                $partyId = null;
                            } else {

                                $partyId = $data[$rootKey]['party']
                                    ?? $data[$rootKey]['party_id']
                                    ?? null;
                            }
                            @endphp

                        {{ optional(\App\Models\Accounts::find($partyId))->account_name ?? '-' }}
                        </td>
                        <td>
                            <span class="badge {{ $log->action === 'delete' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td>{{ optional(\App\Models\User::find($log->action_by))->name ?? 'System' }}</td>
                        <td class="text-center">
                            <button class="btn btn-link p-0 me-3 view-log"
                                data-log='@json($log)'
                                data-action-by="{{ optional(\App\Models\User::find($log->action_by))->name ?? 'System' }}">
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

<!-- MODAL -->
<div class="modal fade" id="activityLogModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content p-4 border-divider border-radius-8">

      <div class="modal-header border-0">
        <h5 class="modal-title">Activity Log Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div id="logSummary" class="mb-3 font-14"></div>
        <div id="logData"></div>
      </div>

      <div class="modal-footer border-0">
        <button type="button" class="btn btn-border-body" data-bs-dismiss="modal">
          Close
        </button>

        <button type="button" class="btn btn-success d-none" id="modalApproveBtn">
          Approve
        </button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="approve_log_modal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog w-360 modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">

         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>

         <div class="modal-body text-center p-0">
            <button class="border-0 bg-transparent">
               <img class="delete-icon mb-3 d-block mx-auto"
                    src="{{ URL::asset('public/assets/imgs/administrator-approve-icon.svg') }}"
                    alt="">
            </button>

            <h5 class="mb-3 fw-normal">Approve this activity</h5>
            <p class="font-14 text-body">
               Do you really want to approve this record?
               Once approved, it will not appear in the list.
            </p>
         </div>

         <input type="hidden" id="approve_log_id">

         <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-border-body" data-bs-dismiss="modal">
               CANCEL
            </button>
            <button type="button" class="ms-3 btn btn-success" id="confirmApprove">
               APPROVE
            </button>
         </div>

      </div>
   </div>
</div>

@include('layouts.footer')
<script>
const paymentModeResolver = (mode) => {
    if (mode === '1' || mode === 1) {
        return 'Cash';
    }
    if (mode === '2' || mode === 2) {
        return 'CHEQUE';
    }
    if (mode === '0' || mode === 0) {
        return 'IMPS/NEFT/RTGS';
    }
    return 'IMPS/NEFT/RTGS';
};
    const itemMap = @json($itemsMap); 
    const sundryMap = @json($sundryMap);
    const stateMap = @json($states);
    const accountMap = @json($accountMap);
</script>

<script>
function formatDateTime(value) {
    if (!value) return '-';

    let date = new Date(value);
    if (isNaN(date.getTime())) return value;

    let day = String(date.getDate()).padStart(2, '0');
    let month = String(date.getMonth() + 1).padStart(2, '0');
    let year = date.getFullYear();

    let hours = String(date.getHours()).padStart(2, '0');
    let minutes = String(date.getMinutes()).padStart(2, '0');

    return `${day}-${month}-${year} ${hours}:${minutes}`;
}

    const dateFields = [
        'date',
        'invoice_date',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    const deleteFieldMap = {
        voucher_no: "Voucher No",
        date: "Date",
        billing_name: "Party",
        financial_year: "Financial Year",

        taxable_amt: "Taxable Amount",
        tax: "Tax",
        total: "Total",

        billing_gst: "GST No",
        billing_state: "State",

        vehicle_no: "Vehicle No",
        transport_name: "Transport Name",
        ewaybill_no: "E-Way Bill No",

    };

$(document).on('click', '.view-log', function () {

    let log = $(this).data('log');
    $('#modalApproveBtn')
        .data('id', log.id)
        .removeClass('d-none');
    let actionByName = $(this).attr('data-action-by');
    let snapshot = log.action === 'edit' ? log.new_data : log.old_data;
    let rootKey =
        log.module_type === 'sale'
            ? 'sale'
            : log.module_type === 'sale_return'
                ? 'sale_return'
                : log.module_type === 'purchase_return'
                    ? 'purchase_return'
                    : log.module_type === 'receipt'
                        ? 'receipt'
                        : log.module_type === 'payment'
                            ? 'payment'
                            : log.module_type === 'journal'
                                ? 'journal'
                                : 'purchase';
    let master = snapshot[rootKey] ?? {};
    let voucher = '-';
    let party   = '-';

    switch (log.module_type) {

        case 'sale': {
            voucher = master.voucher_no_prefix ?? master.voucher_no ?? '-';
            party   = master.billing_name ?? '-';
            break;
        }

        case 'sale_return': {
            voucher = master.sr_prefix ?? master.sale_return_no ?? '-';
            party   = accountMap[master.party] ?? '-';
            break;
        }

        case 'purchase_return': {
            voucher = master.sr_prefix ?? master.purchase_return_no ?? '-';
            party   = accountMap[master.party] ?? '-';
            break;
        }

        case 'payment': {
            const pay = log.action === 'edit'
                ? log.new_data.payment
                : log.old_data.payment;

            voucher = pay.voucher_no ?? '-';

            const debitRow =
                (log.action === 'edit'
                    ? log.new_data.details
                    : log.old_data.details
                )?.find(d => d.type === 'Debit');

            party = debitRow
                ? accountMap[debitRow.account_name] ?? '-'
                : '-';
            break;
        }

        case 'receipt': {
            const rec = log.action === 'edit'
                ? log.new_data.receipt
                : log.old_data.receipt;

            voucher = rec?.voucher_no ?? '-';

            const creditRow =
                (log.action === 'edit'
                    ? log.new_data.details
                    : log.old_data.details
                )?.find(d => d.type === 'Credit');

            party = creditRow
                ? accountMap[creditRow.account_name] ?? '-'
                : '-';
            break;
        }

        case 'journal': {
            const journalDetails =
                (log.action === 'edit'
                    ? log.new_data.details
                    : log.old_data.details
                ) ?? [];

            const creditRow = journalDetails.find(d => d.type === 'Credit');
            const debitRow  = journalDetails.find(d => d.type === 'Debit');

            party = creditRow
                ? accountMap[creditRow.account_name] ?? '-'
                : debitRow
                    ? accountMap[debitRow.account_name] ?? '-'
                    : '-';

            voucher = master.voucher_no ?? '-';
            break;
        }
        case 'contra': {
        const contraDetails =
            (log.action === 'edit'
                ? log.new_data.details
                : log.old_data.details
            ) ?? [];

        const creditRow = contraDetails.find(d => d.type === 'Credit');
        const debitRow  = contraDetails.find(d => d.type === 'Debit');

        party = creditRow
            ? accountMap[creditRow.account_name] ?? '-'
            : debitRow
                ? accountMap[debitRow.account_name] ?? '-'
                : '-';

        voucher = master.voucher_no ?? '-';
        break;
        }
        case 'stock_journal': {
            const sj = log.action === 'edit'
                ? log.new_data.stock_journal
                : log.old_data.stock_journal;

            voucher = sj
                ? (sj.voucher_no_prefix  ?? '')
                : '-';

            party = '-'; 
            break;
        }

        case 'stock_transfer': {
            const st = log.action === 'edit'
                ? log.new_data.stock_transfer
                : log.old_data.stock_transfer;

            voucher = st?.voucher_no ?? '-';
            party = '-';
            break;
        }
        default: {
            voucher = master.voucher_no_prefix ?? master.voucher_no ?? '-';
            party   = master.billing_name ?? '-';
        }
    }
    $('#logSummary').html(`
    <div class="container-fluid font-14 mb-3">
        <div class="row mb-1">
            <div class="col-md-6">
                <strong>Module:</strong> ${log.module_type.toUpperCase()}
            </div>
            <div class="col-md-6">
                <strong>Action:</strong> ${log.action.toUpperCase()}
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-6">
                <strong>Voucher:</strong> ${voucher}
            </div>
            <div class="col-md-6">
                <strong>Party:</strong> ${
                    log.module_type === 'stock_journal' ? '-' : party
                }
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-6">
                <strong>Action By:</strong> ${actionByName}
            </div>
            <div class="col-md-6">
                <strong>Action At:</strong> ${formatDateTime(log.action_at)}
            </div>
        </div>
    </div>
    `);

    let html = '';
    /* ================= PAYMENT : EDIT VIEW ================= */
    if (log.module_type === 'payment' && log.action === 'edit') {

        const oldP = log.old_data.payment ?? {};
        const newP = log.new_data.payment ?? {};

        html += `
        <h6 class="mb-2 text-primary">Edit Payment Voucher</h6>

        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Field</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
        `;

        const headerFields = [
            { key: 'date', label: 'Date', format: formatDateTime },
            { key: 'voucher_no', label: 'Voucher No.' },
            { key: 'series_no', label: 'Series No.' },
            {
                key: 'mode',
                label: 'Mode',
                format: paymentModeResolver
            },
            { key: 'cheque_no', label: 'Cheque No.' },
        ];

        headerFields.forEach(f => {
            let oldVal = oldP[f.key] ?? '-';
            let newVal = newP[f.key] ?? '-';

            if (f.format) {
                oldVal = oldVal !== '-' ? f.format(oldVal) : '-';
                newVal = newVal !== '-' ? f.format(newVal) : '-';
            }

            if (oldVal != newVal) {
                html += `
                    <tr>
                        <td>${f.label}</td>
                        <td class="text-danger">${oldVal}</td>
                        <td class="fw-bold text-success">${newVal}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;
        const oldDetails = log.old_data.details ?? [];
        const newDetails = log.new_data.details ?? [];
        let debitTotal  = 0;
        let creditTotal = 0;

        html += `
        <h6 class="mt-3 mb-2 text-primary">Account Changes</h6>

        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Type</th>
                    <th>Field</th>
                    <th>Old</th>
                    <th>New</th>
                </tr>
            </thead>
            <tbody>
        `;

        ['Debit', 'Credit'].forEach(type => {

            const oldRow = oldDetails.find(d => d.type === type) ?? {};
            const newRow = newDetails.find(d => d.type === type) ?? {};

            if (oldRow.account_name != newRow.account_name) {
                html += `
                    <tr>
                        <td>${type}</td>
                        <td>Account</td>
                        <td class="text-danger">${accountMap[oldRow.account_name] ?? '-'}</td>
                        <td class="fw-bold text-success">${accountMap[newRow.account_name] ?? '-'}</td>
                    </tr>
                `;
            }

        let oldAmount = type === 'Debit'
            ? Number(oldRow.debit ?? 0)
            : Number(oldRow.credit ?? 0);

        let newAmount = type === 'Debit'
            ? Number(newRow.debit ?? 0)
            : Number(newRow.credit ?? 0);

        if (oldAmount !== newAmount) {
            html += `
                <tr>
                    <td>${type}</td>
                    <td>Amount</td>
                    <td class="text-danger">${oldAmount}</td>
                    <td class="fw-bold text-success">${newAmount}</td>
                </tr>
            `;
        }

        if ((oldRow.narration ?? '') != (newRow.narration ?? '')) {
            html += `
                <tr>
                    <td>${type}</td>
                    <td>Narration</td>
                    <td class="text-danger">${oldRow.narration ?? '-'}</td>
                    <td class="fw-bold text-success">${newRow.narration ?? '-'}</td>
                </tr>
            `;
        }
    });

    html += `</tbody></table>`;

        if (newP.long_narration) {
            html += `
                <div class="mt-2">
                    <strong>Narration:</strong> ${newP.long_narration}
                </div>
            `;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }
    /* ================= PAYMENT : DELETE VIEW ================= */
    if (log.module_type === 'payment' && log.action === 'delete') {

        const p = log.old_data.payment ?? {};
        const details = log.old_data.details ?? [];

        let debitTotal = 0;
        let creditTotal = 0;

        html += `
            <h6 class="mb-2 text-danger">Deleted Payment Voucher</h6>

            <table class="table table-bordered font-13 mb-3">
                <tbody>
                    <tr>
                        <th>Date</th>
                        <td>${formatDateTime(p.date)}</td>
                        <th>Voucher No</th>
                        <td>${p.voucher_no ?? '-'}</td>
                    </tr>
                    <tr>
                        <th>Series No</th>
                        <td>${p.series_no ?? '-'}</td>
                        <th>Mode</th>
                        <td>${paymentModeResolver(p.mode)}</td>
                    </tr>
                    <tr>
                        <th>Cheque No</th>
                        <td colspan="3">${p.cheque_no ?? '-'}</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="mb-2 text-danger">Ledger Details (at time of deletion)</h6>

            <table class="table table-bordered font-13">
                <thead class="bg-light">
                    <tr>
                        <th>Type</th>
                        <th>Account</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th>Narration</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (details.length === 0) {
            html += `
                <tr>
                    <td colspan="5" class="text-center text-muted">No ledger entries</td>
                </tr>
            `;
        } else {
            details.forEach(d => {
                let debit = Number(d.debit ?? 0);
                let credit = Number(d.credit ?? 0);

                debitTotal += debit;
                creditTotal += credit;

                html += `
                    <tr>
                        <td>${d.type}</td>
                        <td>${accountMap[d.account_name] ?? d.account_name}</td>
                        <td class="text-end">${debit}</td>
                        <td class="text-end">${credit}</td>
                        <td>${d.narration ?? '-'}</td>
                    </tr>
                `;
            });
        }

        html += `
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">${debitTotal}</td>
                        <td class="text-end">${creditTotal}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        `;

        if (p.long_narration) {
            html += `
                <div class="mt-2">
                    <strong>Narration:</strong> ${p.long_narration}
                </div>
            `;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }
    /* ================= RECEIPT : EDIT VIEW ================= */
    if (log.module_type === 'receipt' && log.action === 'edit') {

        const oldP = log.old_data.receipt ?? {};
        const newP = log.new_data.receipt ?? {};

        html += `
        <h6 class="mb-2 text-primary">Edit Receipt Voucher</h6>

        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Field</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
        `;

        const headerFields = [
            { key: 'date', label: 'Date', format: formatDateTime },
            { key: 'voucher_no', label: 'Voucher No.' },
            { key: 'series_no', label: 'Series No.' },
            {
                key: 'mode',
                label: 'Mode',
                format: paymentModeResolver
            },
            { key: 'cheque_no', label: 'Cheque No.' },
        ];

        headerFields.forEach(f => {
            let oldVal = oldP[f.key] ?? '-';
            let newVal = newP[f.key] ?? '-';

            if (f.format) {
                oldVal = oldVal !== '-' ? f.format(oldVal) : '-';
                newVal = newVal !== '-' ? f.format(newVal) : '-';
            }

            if (oldVal != newVal) {
                html += `
                    <tr>
                        <td>${f.label}</td>
                        <td class="text-danger">${oldVal}</td>
                        <td class="fw-bold text-success">${newVal}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;
        const oldDetails = log.old_data.details ?? [];
        const newDetails = log.new_data.details ?? [];
        let debitTotal  = 0;
        let creditTotal = 0;

        html += `
        <h6 class="mt-3 mb-2 text-primary">Account Changes</h6>

        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Type</th>
                    <th>Field</th>
                    <th>Old</th>
                    <th>New</th>
                </tr>
            </thead>
            <tbody>
        `;

        ['Debit', 'Credit'].forEach(type => {

        const oldRow = oldDetails.find(d => d.type === type) ?? {};
        const newRow = newDetails.find(d => d.type === type) ?? {};

        if (oldRow.account_name != newRow.account_name) {
            html += `
                <tr>
                    <td>${type}</td>
                    <td>Account</td>
                    <td class="text-danger">${accountMap[oldRow.account_name] ?? '-'}</td>
                    <td class="fw-bold text-success">${accountMap[newRow.account_name] ?? '-'}</td>
                </tr>
            `;
        }

        let oldAmount = type === 'Debit'
            ? Number(oldRow.debit ?? 0)
            : Number(oldRow.credit ?? 0);

        let newAmount = type === 'Debit'
            ? Number(newRow.debit ?? 0)
            : Number(newRow.credit ?? 0);

        if (oldAmount !== newAmount) {
            html += `
                <tr>
                    <td>${type}</td>
                    <td>Amount</td>
                    <td class="text-danger">${oldAmount}</td>
                    <td class="fw-bold text-success">${newAmount}</td>
                </tr>
            `;
        }

            if ((oldRow.narration ?? '') != (newRow.narration ?? '')) {
                html += `
                    <tr>
                        <td>${type}</td>
                        <td>Narration</td>
                        <td class="text-danger">${oldRow.narration ?? '-'}</td>
                        <td class="fw-bold text-success">${newRow.narration ?? '-'}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;

            if (newP.long_narration) {
                html += `
                    <div class="mt-2">
                        <strong>Narration:</strong> ${newP.long_narration}
                    </div>
                `;
            }

            $('#logData').html(html);
            $('#activityLogModal').modal('show');
            return;
    }
    /* ================= RECEIPT : DELETE VIEW ================= */
    if (log.module_type === 'receipt' && log.action === 'delete') {

        const p = log.old_data.receipt ?? {};
        const details = log.old_data.details ?? [];

        let debitTotal = 0;
        let creditTotal = 0;

        html += `
        <h6 class="mb-2 text-danger">Deleted Receipt Voucher</h6>

            <table class="table table-bordered font-13 mb-3">
                <tbody>
                    <tr>
                        <th>Date</th>
                        <td>${formatDateTime(p.date)}</td>
                        <th>Voucher No</th>
                        <td>${p.voucher_no ?? '-'}</td>
                    </tr>
                    <tr>
                        <th>Series No</th>
                        <td>${p.series_no ?? '-'}</td>
                        <th>Mode</th>
                        <td>${paymentModeResolver(p.mode)}</td>
                    </tr>
                    <tr>
                        <th>Cheque No</th>
                        <td colspan="3">${p.cheque_no ?? '-'}</td>
                    </tr>
                </tbody>
            </table>

            <h6 class="mb-2 text-danger">Ledger Details (at time of deletion)</h6>

            <table class="table table-bordered font-13">
                <thead class="bg-light">
                    <tr>
                        <th>Type</th>
                        <th>Account</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th>Narration</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (details.length === 0) {
            html += `
                <tr>
                    <td colspan="5" class="text-center text-muted">No ledger entries</td>
                </tr>
            `;
        } else {
            details.forEach(d => {
                let debit = Number(d.debit ?? 0);
                let credit = Number(d.credit ?? 0);

                debitTotal += debit;
                creditTotal += credit;

                html += `
                    <tr>
                        <td>${d.type}</td>
                        <td>${accountMap[d.account_name] ?? d.account_name}</td>
                        <td class="text-end">${debit}</td>
                        <td class="text-end">${credit}</td>
                        <td>${d.narration ?? '-'}</td>
                    </tr>
                `;
            });
        }

        html += `
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <td colspan="2" class="text-end">Total</td>
                        <td class="text-end">${debitTotal}</td>
                        <td class="text-end">${creditTotal}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        `;

        if (p.long_narration) {
            html += `
                <div class="mt-2">
                    <strong>Narration:</strong> ${p.long_narration}
                </div>
            `;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }
    function sumReelWeight(reels, itemId) {
        return reels
            .filter(r => r.item_id == itemId)
            .reduce((t, r) => t + Number(r.weight || 0), 0);
    }

    function uniqueItemIds(oldArr = [], newArr = []) {
        return [...new Set([
            ...oldArr.map(r => r.item_id),
            ...newArr.map(r => r.item_id)
        ])];
    }

    /* ================= STOCK JOURNAL : EDIT VIEW ================= */
    if (log.module_type === 'stock_journal' && log.action === 'edit') {

        const oldSJ = log.old_data.stock_journal ?? {};
        const newSJ = log.new_data.stock_journal ?? {};

        let html = `
        <h6 class="mb-2 text-primary">Edit Stock Journal</h6>

        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Field</th>
                    <th>Old</th>
                    <th>New</th>
                </tr>
            </thead>
            <tbody>
        `;

        const headerFields = [
            { key: 'jdate', label: 'Date', format: formatDateTime },
            { key: 'voucher_no_prefix', label: 'Voucher Prefix' },
            { key: 'voucher_no', label: 'Voucher No' },
            { key: 'series_no', label: 'Series No' },
            { key: 'material_center', label: 'Material Center' },
        ];

        headerFields.forEach(f => {
            let o = oldSJ[f.key] ?? '-';
            let n = newSJ[f.key] ?? '-';

            if (f.format) {
                o = o !== '-' ? f.format(o) : '-';
                n = n !== '-' ? f.format(n) : '-';
            }

            if (o != n) {
                html += `
                    <tr>
                        <td>${f.label}</td>
                        <td class="text-danger">${o}</td>
                        <td class="fw-bold text-success">${n}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;
        const oldConsumeReels = log.old_data.size_consumed ?? [];
        const newConsumeReels = log.new_data.size_consumed ?? [];

        html += `
        <h6 class="mt-3 text-danger">Consumed Items</h6>
        <table class="table table-bordered font-13">
        <thead class="bg-light">
        <tr>
        <th>Item</th>
        <th>Old Weight</th>
        <th>New Weight</th>
        </tr>
        </thead>
        <tbody>
        `;

        uniqueItemIds(oldConsumeReels, newConsumeReels).forEach(itemId => {
            const oldW = sumReelWeight(oldConsumeReels, itemId);
            const newW = sumReelWeight(newConsumeReels, itemId);

            if (oldW !== newW) {
                html += `
                <tr>
                    <td>${itemMap[itemId]}</td>
                    <td class="text-danger">${oldW}</td>
                    <td class="fw-bold text-success">${newW}</td>
                </tr>`;
            }
        });

        html += `</tbody></table>`;
        const oldGenReels = log.old_data.size_generated ?? [];
        const newGenReels = log.new_data.size_generated ?? [];

        html += `
        <h6 class="mt-3 text-success">Generated Items</h6>
        <table class="table table-bordered font-13">
        <thead class="bg-light">
        <tr>
        <th>Item</th>
        <th>Old Weight</th>
        <th>New Weight</th>
        </tr>
        </thead>
        <tbody>
        `;

        uniqueItemIds(oldGenReels, newGenReels).forEach(itemId => {
            const oldW = sumReelWeight(oldGenReels, itemId);
            const newW = sumReelWeight(newGenReels, itemId);

            if (oldW !== newW) {
                html += `
                <tr>
                    <td>${itemMap[itemId]}</td>
                    <td class="text-danger">${oldW}</td>
                    <td class="fw-bold text-success">${newW}</td>
                </tr>`;
            }
        });

        html += `</tbody></table>`;
        html += `
        <h6 class="mt-3 text-danger">Consumed Reel / Size Changes</h6>
        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Change</th>
                    <th>Item</th>
                    <th>Reel No</th>
                    <th>Size</th>
                    <th>Old Weight</th>
                    <th>New Weight</th>
                </tr>
            </thead>
        <tbody>
        `;

        oldConsumeReels.forEach(o => {
            const n = newConsumeReels.find(r =>
                r.item_id == o.item_id && r.reel_no == o.reel_no
            );

            if (!n) {
                html += `
                <tr class="text-danger">
                    <td>Removed</td>
                    <td>${itemMap[o.item_id]}</td>
                    <td>${o.reel_no}</td>
                    <td>${o.size}</td>
                    <td>${o.weight}</td>
                    <td>0</td>
                </tr>`;
            } else if (o.size !== n.size || Number(o.weight) !== Number(n.weight)) {
                html += `
                <tr class="text-warning">
                    <td>Modified</td>
                    <td>${itemMap[o.item_id]}</td>
                    <td>${o.reel_no}</td>
                    <td>${o.size} → ${n.size}</td>
                    <td>${o.weight}</td>
                    <td>${n.weight}</td>
                </tr>`;
            }
        });

        newConsumeReels.forEach(n => {
            const exists = oldConsumeReels.find(o =>
                o.item_id == n.item_id && o.reel_no == n.reel_no
            );

            if (!exists) {
                html += `
                <tr class="text-success">
                    <td>Added</td>
                    <td>${itemMap[n.item_id]}</td>
                    <td>${n.reel_no}</td>
                    <td>${n.size}</td>
                    <td>0</td>
                    <td>${n.weight}</td>
                </tr>`;
            }
        });

        html += `</tbody></table>`;
        html += `
        <h6 class="mt-3 text-success">Generated Reel / Size Changes</h6>
        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Change</th>
                    <th>Item</th>
                    <th>Reel No</th>
                    <th>Size</th>
                    <th>Old Weight</th>
                    <th>New Weight</th>
                </tr>
            </thead>
        <tbody>
        `;

        oldGenReels.forEach(o => {
            const n = newGenReels.find(r =>
                r.item_id == o.item_id && r.reel_no == o.reel_no
            );

            if (!n) {
                html += `
                <tr class="text-danger">
                    <td>Removed</td>
                    <td>${itemMap[o.item_id]}</td>
                    <td>${o.reel_no}</td>
                    <td>${o.size}</td>
                    <td>${o.weight}</td>
                    <td>0</td>
                </tr>`;
            } else if (o.size !== n.size || Number(o.weight) !== Number(n.weight)) {
                html += `
                <tr class="text-warning">
                    <td>Modified</td>
                    <td>${itemMap[o.item_id]}</td>
                    <td>${o.reel_no}</td>
                    <td>${o.size} → ${n.size}</td>
                    <td>${o.weight}</td>
                    <td>${n.weight}</td>
                </tr>`;
            }
        });

        newGenReels.forEach(n => {
            const exists = oldGenReels.find(o =>
                o.item_id == n.item_id && o.reel_no == n.reel_no
            );

            if (!exists) {
                html += `
                <tr class="text-success">
                    <td>Added</td>
                    <td>${itemMap[n.item_id]}</td>
                    <td>${n.reel_no}</td>
                    <td>${n.size}</td>
                    <td>0</td>
                    <td>${n.weight}</td>
                </tr>`;
            }
        });

        html += `</tbody></table>`;

            if (newSJ.narration) {
                html += `<div class="mt-2"><strong>Narration:</strong> ${newSJ.narration}</div>`;
            }

            $('#logData').html(html);
            $('#activityLogModal').modal('show');
            return;
    }
    /* ================= STOCK JOURNAL : DELETE VIEW ================= */
    if (log.module_type === 'stock_journal' && log.action === 'delete') {

        const sj        = log.old_data.stock_journal ?? {};
        const consume   = log.old_data.consume_details ?? [];
        const generate  = log.old_data.generate_details ?? [];
        const conReels  = log.old_data.size_consumed ?? [];
        const genReels  = log.old_data.size_generated ?? [];

        let html = `
        <h6 class="mb-2 text-danger">Deleted Stock Journal</h6>

        <table class="table table-bordered font-13 mb-3">
            <tbody>
                <tr>
                    <th>Date</th>
                    <td>${formatDateTime(sj.jdate)}</td>
                    <th>Voucher</th>
                    <td>${(sj.voucher_no_prefix ?? '')}</td>
                </tr>
                <tr>
                    <th>Series</th>
                    <td>${sj.series_no ?? '-'}</td>
                    <th>Material Center</th>
                    <td>${sj.material_center ?? '-'}</td>
                </tr>
            </tbody>
        </table>
        `;

        html += `
        <h6 class="text-danger">Consumed Items</h6>
        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Item</th>
                    <th class="text-end">Weight</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
        `;

        consume.forEach(c => {
            html += `
                <tr>
                    <td>${itemMap[c.consume_item] ?? c.consume_item}</td>
                    <td class="text-end">${c.consume_weight}</td>
                    <td class="text-end">${c.consume_price}</td>
                    <td class="text-end">${c.consume_amount}</td>
                </tr>
            `;
        });

        html += `</tbody></table>`;

        html += `
        <h6 class="text-success">Generated Items</h6>
        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Item</th>
                    <th class="text-end">Weight</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
        `;

        generate.forEach(g => {
            html += `
                <tr>
                    <td>${itemMap[g.new_item] ?? g.new_item}</td>
                    <td class="text-end">${g.new_weight}</td>
                    <td class="text-end">${g.new_price}</td>
                    <td class="text-end">${g.new_amount}</td>
                </tr>
            `;
        });

        html += `</tbody></table>`;

        if (conReels.length) {
            html += `
            <h6 class="text-danger">Consumed Reel / Size Details</h6>
            <table class="table table-bordered font-13 mb-3">
                <thead class="bg-light">
                    <tr>
                        <th>Item</th>
                        <th>Reel No</th>
                        <th>Size</th>
                        <th class="text-end">Weight</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
            `;

            conReels.forEach(r => {
                html += `
                    <tr>
                        <td>${itemMap[r.item_id] ?? r.item_id}</td>
                        <td>${r.reel_no}</td>
                        <td>${r.size}</td>
                        <td class="text-end">${r.weight}</td>
                        <td>${r.unit}</td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;
        }

        if (genReels.length) {
            html += `
            <h6 class="text-success">Generated Reel / Size Details</h6>
            <table class="table table-bordered font-13 mb-3">
                <thead class="bg-light">
                    <tr>
                        <th>Item</th>
                        <th>Reel No</th>
                        <th>Size</th>
                        <th class="text-end">Weight</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
            `;

            genReels.forEach(r => {
                html += `
                    <tr>
                        <td>${itemMap[r.item_id] ?? r.item_id}</td>
                        <td>${r.reel_no}</td>
                        <td>${r.size}</td>
                        <td class="text-end">${r.weight}</td>
                        <td>${r.unit}</td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;
        }

        if (sj.narration) {
            html += `<div class="mt-2"><strong>Narration:</strong> ${sj.narration}</div>`;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }

    /* ================= JOURNAL : EDIT VIEW ================= */
    if (log.module_type === 'journal' && log.action === 'edit') {

        const oldJ = log.old_data.journal ?? {};
        const newJ = log.new_data.journal ?? {};

        html += `
        <h6 class="mb-2 text-primary">Edit Journal Voucher</h6>

        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Field</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
        `;

        const headerFields = [
            { key: 'date', label: 'Date', format: formatDateTime },
            { key: 'voucher_no', label: 'Voucher No.' },
            { key: 'series_no', label: 'Series No.' },
            //{ key: 'invoice_no', label: 'Invoice No.' },
            { key: 'vendor_gstin', label: 'Vendor GSTIN' },
            { key: 'claim_gst_status', label: 'GST Claimed' },
            { key: 'total_amount', label: 'Total Amount' },
        ];

        headerFields.forEach(f => {
            let oldVal = oldJ[f.key] ?? '-';
            let newVal = newJ[f.key] ?? '-';

            if (f.format) {
                oldVal = oldVal !== '-' ? f.format(oldVal) : '-';
                newVal = newVal !== '-' ? f.format(newVal) : '-';
            }

            if (oldVal != newVal) {
                html += `
                    <tr>
                        <td>${f.label}</td>
                        <td class="text-danger">${oldVal}</td>
                        <td class="fw-bold text-success">${newVal}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;

        /* ===== JOURNAL ACCOUNT CHANGES ===== */
        const oldDetails = log.old_data.details ?? [];
        const newDetails = log.new_data.details ?? [];

        html += `
        <h6 class="mt-3 mb-2 text-primary">Account Changes</h6>

        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Type</th>
                    <th>Account</th>
                    <th>Old Amount</th>
                    <th>New Amount</th>
                </tr>
            </thead>
            <tbody>
        `;

        oldDetails.forEach(o => {
            const n = newDetails.find(d => d.type === o.type && d.account_name === o.account_name);

            const oldAmt = Number(o.debit ?? o.credit ?? 0);
            const newAmt = Number(n?.debit ?? n?.credit ?? 0);

            if (!n || oldAmt !== newAmt) {
                html += `
                    <tr>
                        <td>${o.type}</td>
                        <td>${accountMap[o.account_name] ?? o.account_name}</td>
                        <td class="text-danger">${oldAmt}</td>
                        <td class="fw-bold text-success">${newAmt || 0}</td>
                    </tr>
                `;
            }
        });

        newDetails.forEach(n => {
            const exists = oldDetails.find(o => o.type === n.type && o.account_name === n.account_name);
            if (!exists) {
                const amt = Number(n.debit ?? n.credit ?? 0);
                html += `
                    <tr class="text-success">
                        <td>${n.type}</td>
                        <td>${accountMap[n.account_name] ?? n.account_name}</td>
                        <td>-</td>
                        <td>${amt}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;

        if (newJ.long_narration) {
            html += `
                <div class="mt-2">
                    <strong>Narration:</strong> ${newJ.long_narration}
                </div>
            `;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }
    /* ================= JOURNAL : DELETE VIEW ================= */
    if (log.module_type === 'journal' && log.action === 'delete') {

        const j = log.old_data.journal ?? {};
        const details = log.old_data.details ?? [];

        let debitTotal = 0;
        let creditTotal = 0;

        html += `
        <h6 class="mb-2 text-danger">Deleted Journal Voucher</h6>

        <table class="table table-bordered font-13 mb-3">
            <tbody>
                <tr>
                    <th>Date</th>
                    <td>${formatDateTime(j.date)}</td>
                    <th>Voucher No</th>
                    <td>${j.voucher_no ?? '-'}</td>
                </tr>
                <tr>
                    <th>Series No</th>
                    <td>${j.series_no ?? '-'}</td>
                </tr>
            </tbody>
        </table>

        <h6 class="mb-2 text-danger">Ledger Details (at time of deletion)</h6>

        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Type</th>
                    <th>Account</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                </tr>
            </thead>
            <tbody>
        `;

        if (details.length === 0) {
            html += `
                <tr>
                    <td colspan="4" class="text-center text-muted">No ledger entries</td>
                </tr>
            `;
        } else {
            details.forEach(d => {
                let debit = Number(d.debit ?? 0);
                let credit = Number(d.credit ?? 0);

                debitTotal += debit;
                creditTotal += credit;

                html += `
                    <tr>
                        <td>${d.type}</td>
                        <td>${accountMap[d.account_name] ?? d.account_name}</td>
                        <td class="text-end">${debit}</td>
                        <td class="text-end">${credit}</td>
                    </tr>
                `;
            });
        }

        html += `
            </tbody>
            <tfoot class="fw-bold">
                <tr>
                    <td colspan="2" class="text-end">Total</td>
                    <td class="text-end">${debitTotal}</td>
                    <td class="text-end">${creditTotal}</td>
                </tr>
            </tfoot>
        </table>
        `;

        if (j.long_narration) {
            html += `
                <div class="mt-2">
                    <strong>Narration:</strong> ${j.long_narration}
                </div>
            `;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }

    /* ================= STOCK TRANSFER : EDIT VIEW ================= */
    if (log.module_type === 'stock_transfer' && log.action === 'edit') {

        const oldT = log.old_data.stock_transfer ?? {};
        const newT = log.new_data.stock_transfer ?? {};

        const oldItems = log.old_data.details ?? [];
        const newItems = log.new_data.details ?? [];

        let html = `
        <h6 class="mb-2 text-primary">Edit Stock Transfer</h6>

        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Field</th>
                    <th>Old</th>
                    <th>New</th>
                </tr>
            </thead>
            <tbody>
        `;

        const headerFields = [
            { key: 'transfer_date', label: 'Date', format: formatDateTime },
            { key: 'voucher_no', label: 'Voucher No' },
            { key: 'series_no', label: 'From Series' },
            { key: 'series_no_to', label: 'To Series' },
            { key: 'vehicle_no', label: 'Vehicle No' },
        ];

        headerFields.forEach(f => {
            let o = oldT[f.key] ?? '-';
            let n = newT[f.key] ?? '-';

            if (f.format) {
                o = o !== '-' ? f.format(o) : '-';
                n = n !== '-' ? f.format(n) : '-';
            }

            if (o != n) {
                html += `
                    <tr>
                        <td>${f.label}</td>
                        <td class="text-danger">${o}</td>
                        <td class="fw-bold text-success">${n}</td>
                    </tr>`;
            }
        });

        html += `</tbody></table>`;

        html += `
        <h6 class="mt-3 text-primary">Item Changes</h6>
        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Change</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
        `;

        oldItems.forEach(o => {
            const n = newItems.find(i => i.goods_discription == o.goods_discription);

            if (!n) {
                html += `
                    <tr class="text-danger">
                        <td>Removed</td>
                        <td>${itemMap[o.goods_discription]}</td>
                        <td>${o.qty}</td>
                        <td>${o.price}</td>
                        <td>${o.amount}</td>
                    </tr>`;
            } else if (
                o.qty != n.qty ||
                o.price != n.price ||
                o.amount != n.amount
            ) {
                html += `
                    <tr class="text-warning">
                        <td>Modified</td>
                        <td>${itemMap[o.goods_discription]}</td>
                        <td>${o.qty} → ${n.qty}</td>
                        <td>${o.price} → ${n.price}</td>
                        <td>${o.amount} → ${n.amount}</td>
                    </tr>`;
            }
        });

        newItems.forEach(n => {
            const exists = oldItems.find(o => o.goods_discription == n.goods_discription);
            if (!exists) {
                html += `
                    <tr class="text-success">
                        <td>Added</td>
                        <td>${itemMap[n.goods_discription]}</td>
                        <td>${n.qty}</td>
                        <td>${n.price}</td>
                        <td>${n.amount}</td>
                    </tr>`;
            }
        });

        html += `</tbody></table>`;

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }
    /* ================= STOCK TRANSFER : DELETE VIEW ================= */
    if (log.module_type === 'stock_transfer' && log.action === 'delete') {

        const t = log.old_data.stock_transfer ?? {};
        const items = log.old_data.details ?? [];
        const sundries = log.old_data.sundries ?? [];

        let html = `
        <h6 class="mb-2 text-danger">Deleted Stock Transfer</h6>

        <table class="table table-bordered font-13 mb-3">
            <tbody>
                <tr>
                    <th>Date</th>
                    <td>${formatDateTime(t.transfer_date)}</td>
                    <th>Voucher No</th>
                    <td>${t.voucher_no ?? '-'}</td>
                </tr>
                <tr>
                    <th>From Series</th>
                    <td>${t.series_no ?? '-'}</td>
                    <th>To Series</th>
                    <td>${t.series_no_to ?? '-'}</td>
                </tr>
                <tr>
                    <th>Vehicle No</th>
                    <td>${t.vehicle_no ?? '-'}</td>
                    <th>Transport</th>
                    <td>${t.transport_id ?? '-'}</td>
                </tr>
            </tbody>
        </table>
        `;

        html += `
        <h6 class="mb-2 text-danger">Items (at time of deletion)</h6>

        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Item</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
        `;

        if (!items.length) {
            html += `
                <tr>
                    <td colspan="5" class="text-center text-muted">No items</td>
                </tr>`;
        } else {
            items.forEach(i => {
                html += `
                    <tr>
                        <td>${itemMap[i.goods_discription] ?? i.goods_discription}</td>
                        <td class="text-end">${i.qty}</td>
                        <td class="text-end">${i.price}</td>
                        <td class="text-end">${i.amount}</td>
                    </tr>`;
            });
        }

        html += `
            </tbody>
        </table>
        `;

        if (sundries.length) {
            html += `
            <h6 class="mb-2 text-danger">Bill Sundries</h6>

            <table class="table table-bordered font-13 mb-3">
                <thead class="bg-light">
                    <tr>
                        <th>Sundry</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
            `;

            sundries.forEach(s => {
                html += `
                    <tr>
                        <td>${sundryMap[s.bill_sundry] ?? s.bill_sundry}</td>
                        <td class="text-end">${s.rate}</td>
                        <td class="text-end">${s.amount}</td>
                    </tr>`;
            });

            html += `
                </tbody>
            </table>`;
        }

        if (t.other_detail) {
            html += `
                <div class="mt-2">
                    <strong>Other Details:</strong> ${t.other_detail}
                </div>`;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }

    /* ================= CONTRA : EDIT VIEW ================= */
    if (log.module_type === 'contra' && log.action === 'edit') {

        const oldC = log.old_data.contra ?? {};
        const newC = log.new_data.contra ?? {};
        const oldDetails = log.old_data.details ?? [];
        const newDetails = log.new_data.details ?? [];

        let html = `
        <h6 class="mb-2 text-primary">Edit Contra Voucher</h6>

        <table class="table table-bordered font-13 mb-3">
            <thead class="bg-light">
                <tr>
                    <th>Field</th>
                    <th>Old Value</th>
                    <th>New Value</th>
                </tr>
            </thead>
            <tbody>
        `;

        const headerFields = [
            { key: 'date', label: 'Date', format: formatDateTime },
            { key: 'voucher_no', label: 'Voucher No' },
            { key: 'series_no', label: 'Series No' },
            { key: 'mode', label: 'Mode', format: paymentModeResolver },
            { key: 'cheque_no', label: 'Cheque No' },
        ];

        headerFields.forEach(f => {
            let o = oldC[f.key] ?? '-';
            let n = newC[f.key] ?? '-';

            if (f.format) {
                o = o !== '-' ? f.format(o) : '-';
                n = n !== '-' ? f.format(n) : '-';
            }

            if (o != n) {
                html += `
                    <tr>
                        <td>${f.label}</td>
                        <td class="text-danger">${o}</td>
                        <td class="fw-bold text-success">${n}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;

        html += `
        <h6 class="mt-3 mb-2 text-primary">Account Changes</h6>
        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Type</th>
                    <th>Account</th>
                    <th>Old Amount</th>
                    <th>New Amount</th>
                </tr>
            </thead>
            <tbody>
        `;

        oldDetails.forEach(o => {
            const n = newDetails.find(d =>
                d.type === o.type && d.account_name === o.account_name
            );

            const oldAmt = Number(o.debit ?? o.credit ?? 0);
            const newAmt = Number(n?.debit ?? n?.credit ?? 0);

            if (!n || oldAmt !== newAmt) {
                html += `
                    <tr>
                        <td>${o.type}</td>
                        <td>${accountMap[o.account_name] ?? o.account_name}</td>
                        <td class="text-danger">${oldAmt}</td>
                        <td class="fw-bold text-success">${newAmt}</td>
                    </tr>
                `;
            }
        });

        newDetails.forEach(n => {
            const exists = oldDetails.find(o =>
                o.type === n.type && o.account_name === n.account_name
            );

            if (!exists) {
                const amt = Number(n.debit ?? n.credit ?? 0);
                html += `
                    <tr class="text-success">
                        <td>${n.type}</td>
                        <td>${accountMap[n.account_name] ?? n.account_name}</td>
                        <td>-</td>
                        <td>${amt}</td>
                    </tr>
                `;
            }
        });

        html += `</tbody></table>`;

        if (newC.long_narration) {
            html += `<div class="mt-2"><strong>Narration:</strong> ${newC.long_narration}</div>`;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }
    /* ================= CONTRA : DELETE VIEW ================= */
    if (log.module_type === 'contra' && log.action === 'delete') {

        const c = log.old_data.contra ?? {};
        const details = log.old_data.details ?? [];

        let debitTotal = 0;
        let creditTotal = 0;

        let html = `
        <h6 class="mb-2 text-danger">Deleted Contra Voucher</h6>

        <table class="table table-bordered font-13 mb-3">
            <tbody>
                <tr>
                    <th>Date</th>
                    <td>${formatDateTime(c.date)}</td>
                    <th>Voucher No</th>
                    <td>${c.voucher_no ?? '-'}</td>
                </tr>
                <tr>
                    <th>Series No</th>
                    <td>${c.series_no ?? '-'}</td>
                    <th>Mode</th>
                    <td>${paymentModeResolver(c.mode)}</td>
                </tr>
            </tbody>
        </table>

        <h6 class="mb-2 text-danger">Ledger Details (at time of deletion)</h6>

        <table class="table table-bordered font-13">
            <thead class="bg-light">
                <tr>
                    <th>Type</th>
                    <th>Account</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                </tr>
            </thead>
            <tbody>
        `;

        if (!details.length) {
            html += `<tr><td colspan="4" class="text-center text-muted">No ledger entries</td></tr>`;
        } else {
            details.forEach(d => {
                const debit = Number(d.debit ?? 0);
                const credit = Number(d.credit ?? 0);

                debitTotal += debit;
                creditTotal += credit;

                html += `
                    <tr>
                        <td>${d.type}</td>
                        <td>${accountMap[d.account_name] ?? d.account_name}</td>
                        <td class="text-end">${debit}</td>
                        <td class="text-end">${credit}</td>
                    </tr>
                `;
            });
        }

        html += `
            </tbody>
            <tfoot class="fw-bold">
                <tr>
                    <td colspan="2" class="text-end">Total</td>
                    <td class="text-end">${debitTotal}</td>
                    <td class="text-end">${creditTotal}</td>
                </tr>
            </tfoot>
        </table>
        `;

        if (c.long_narration) {
            html += `<div class="mt-2"><strong>Narration:</strong> ${c.long_narration}</div>`;
        }

        $('#logData').html(html);
        $('#activityLogModal').modal('show');
        return;
    }
    /* ================= EDIT VIEW ================= */

    if (log.action === 'edit') {

        html += `
            <table class="table table-bordered font-13">
                <thead class="bg-light">
                    <tr>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>
        `;

        let oldPurchase = log.old_data[rootKey] ?? {};
        let newPurchase = log.new_data[rootKey] ?? {};

        Object.keys(newPurchase).forEach(function (key) {

        let oldVal = oldPurchase[key] ?? '';
        let newVal = newPurchase[key] ?? '';

        if (dateFields.includes(key)) {
            oldVal = formatDateTime(oldVal);
            newVal = formatDateTime(newVal);
        }

        if (oldVal != newVal) {
            html += `
                <tr>
                <td>${key.replaceAll('_',' ').toUpperCase()}</td>
                <td>${oldVal || '-'}</td>
                <td class="fw-bold text-success">${newVal || '-'}</td>
                </tr>
            `;
        }
        });
        let oldItems = log.old_data.items ?? [];
        let newItems = log.new_data.items ?? [];
        function itemSignature(i) {
            return `${i.qty}|${i.price}|${i.amount}`;
        }

        html += `
        <tr class="table-secondary">
        <td colspan="3"><strong>ITEM CHANGES</strong></td>
        </tr>
        <tr>
            <th>Change</th>
            <th>Item</th>
            <th>Qty / Rate / Amount</th>
        </tr>
        `;

        let itemChanges = false;

        oldItems.forEach(o => {
            let n = newItems.find(n => n.goods_discription == o.goods_discription);

            if (!n) {
                itemChanges = true;
                html += `
                    <tr class="text-danger">
                        <td>Removed</td>
                        <td>${itemMap[o.goods_discription] ?? o.goods_discription}</td>
                        <td>${o.qty} × ${o.price} = ${o.amount}</td>
                    </tr>
                `;
            }
            else if (itemSignature(o) !== itemSignature(n)) {
                itemChanges = true;
                html += `
                    <tr class="text-warning">
                        <td>Modified</td>
                        <td>${itemMap[o.goods_discription] ?? o.goods_discription}</td>
                        <td>
                            ${o.qty} × ${o.price} = ${o.amount}
                            <br>
                            <strong>→</strong>
                            ${n.qty} × ${n.price} = ${n.amount}
                        </td>
                    </tr>
                `;
            }
        });

        newItems.forEach(n => {
            let o = oldItems.find(o => o.goods_discription == n.goods_discription);
            if (!o) {
                itemChanges = true;
                html += `
                    <tr class="text-success">
                        <td>Added</td>
                        <td>${itemMap[n.goods_discription] ?? n.goods_discription}</td>
                        <td>${n.qty} × ${n.price} = ${n.amount}</td>
                    </tr>
                `;
            }
        });

        if (!itemChanges) {
            html += `
                <tr>
                    <td colspan="3" class="text-center text-muted">
                        No item-level changes
                    </td>
                </tr>
            `;
        }
        function normalizeSundries(list = []) {
            return list.map(s => ({
                id: s.bill_sundry,
                rate: s.rate,
                amount: parseFloat(s.amount).toFixed(2)
            }));
        }
        let oldSundries = normalizeSundries(log.old_data?.sundries);
        let newSundries = normalizeSundries(log.new_data?.sundries);

        let sundryHtml = '';
        let sundryChanges = false;

        oldSundries.forEach(o => {
            let exists = newSundries.find(n =>
                n.id == o.id && n.rate == o.rate && n.amount == o.amount
            );
            if (!exists) {
                sundryChanges = true;
                sundryHtml += `
                    <tr class="text-danger">
                        <td>Removed</td>
                        <td>${sundryMap[o.id] ?? o.id}</td>
                        <td>${o.rate}%</td>
                        <td>${o.amount}</td>
                    </tr>
                `;
            }
        });

        newSundries.forEach(n => {
            let exists = oldSundries.find(o =>
                o.id == n.id && o.rate == n.rate && o.amount == n.amount
            );
            if (!exists) {
                sundryChanges = true;
                sundryHtml += `
                    <tr class="text-success">
                        <td>Added</td>
                        <td>${sundryMap[n.id] ?? n.id}</td>
                        <td>${n.rate}%</td>
                        <td>${n.amount}</td>
                    </tr>
                `;
            }
        });
        if (sundryChanges) {
            html += `
                <h6 class="mt-4">BILL SUNDRY CHANGES</h6>
                <table class="table table-bordered font-13">
                    <thead class="bg-light">
                        <tr>
                            <th>Change</th>
                            <th>Sundry</th>
                            <th>Rate</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${sundryHtml}
                    </tbody>
                </table>
            `;
        }
        html += `</tbody></table>`;
    }

    else {
        const p = log.old_data[rootKey] ?? {};
        const items = log.old_data.items ?? [];
        const sundries = log.old_data.sundries ?? [];
        const ledgers = log.old_data.account_ledgers ?? [];

        let voucherNo = '-';
        let partyName = '-';

        switch (log.module_type) {

            case 'sale':
                voucherNo = p.voucher_no_prefix ?? p.voucher_no ?? '-';
                partyName = p.billing_name ?? '-';
                break;

            case 'sale_return':
                voucherNo = p.sr_prefix ?? p.sale_return_no ?? '-';
                partyName = accountMap[p.party] ?? '-';
                break;

            case 'purchase_return':
                voucherNo = p.sr_prefix ?? p.purchase_return_no ?? '-';
                partyName = accountMap[p.party] ?? '-';
                break;

            default:
                voucherNo = p.voucher_no_prefix ?? p.voucher_no ?? '-';
                partyName = p.billing_name ?? '-';
        }

        html += `
        <h6 class="mb-2 text-primary">
        ${log.module_type.toUpperCase()} Details
        </h6>

        <table class="table table-bordered font-13 mb-4">
            <tbody>
                <tr>
                    <th>Voucher No</th>
                    <td>${voucherNo}</td>
                    <th>Date</th>
                    <td>${formatDateTime(p.date)}</td>
                </tr>

                <tr>
                    <th>Party</th>
                    <td>${partyName}</td>
                    <th>Material Center</th>
                    <td>${p.material_center ?? '-'}</td>
                </tr>

                <tr>
                    <th>Taxable Amount</th>
                    <td>${p.taxable_amt ?? '-'}</td>
                    <th>Total</th>
                    <td>${p.total ?? '-'}</td>
                </tr>

                <tr>
                    <th>GST No</th>
                    <td>${p.billing_gst ?? '-'}</td>
                    <th>State</th>
                    <td>${stateMap[p.billing_state] ?? p.billing_state ?? '-'}</td>
                </tr>

                <tr>
                    <th>Vehicle No</th>
                    <td>${p.vehicle_no ?? '-'}</td>
                    <th>Transport</th>
                    <td>${p.transport_name ?? '-'}</td>
                </tr>
            </tbody>
        </table>
        `;

        html += `
            <h6 class="mb-2 text-primary">Items (at time of deletion)</h6>
            <table class="table table-bordered font-13 mb-4">
                <thead class="bg-light">
                    <tr>
                        <th>Item</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (items.length === 0) {
            html += `<tr><td colspan="4" class="text-center">No items</td></tr>`;
        } else {
            items.forEach(it => {
                html += `
                    <tr>
                        <td>${itemMap[it.goods_discription] ?? it.goods_discription}</td>
                        <td class="text-end">${it.qty}</td>
                        <td class="text-end">${it.price}</td>
                        <td class="text-end">${it.amount}</td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table>`;

        html += `
            <h6 class="mb-2 text-primary">Bill Sundries</h6>
            <table class="table table-bordered font-13 mb-4">
                <thead class="bg-light">
                    <tr>
                        <th>Sundry</th>
                        <th class="text-end">Rate</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
        `;

        if (sundries.length === 0) {
            html += `<tr><td colspan="3" class="text-center">No sundries</td></tr>`;
        } else {
            sundries.forEach(s => {
                html += `
                    <tr>
                        <td>${sundryMap[s.bill_sundry] ?? s.bill_sundry}</td>
                        <td class="text-end">${s.rate}</td>
                        <td class="text-end">${s.amount}</td>
                    </tr>
                `;
            });
        }

        html += `</tbody></table>`;

        html += `
            <table class="table table-bordered font-13">
                <tbody>
                    <tr>
                        <th>Deleted At</th>
                        <td>${formatDateTime(log.action_at)}</td>
                    </tr>
                </tbody>
            </table>
        `;
    }

    $('#logData').html(html);
    $('#activityLogModal').modal('show');
});

$("#search").keyup(function () {
    let value = this.value.toLowerCase().trim();
    $(".activity_table tr").each(function (index) {
        if (!index) return;
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

$('#modalApproveBtn').on('click', function () {

    const id = $(this).data('id');

    $('#approve_log_id').val(id);
    $('#activityLogModal').modal('hide');
    $('#approve_log_modal').modal('show');
});


$('#confirmApprove').on('click', function () {

    let id = $('#approve_log_id').val();

    $.ajax({
        url: "{{ url('activity-logs') }}/" + id + "/approve",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function (res) {
            if (res.status) {
                $('#approve_log_modal').modal('hide');

                // remove approved row
                $('.view-log').filter(function () {
                    return $(this).data('log').id == id;
                }).closest('tr').remove();

            }
        }
    });
});
</script>

@endsection
