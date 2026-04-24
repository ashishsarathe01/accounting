@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">
    <section class="container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                <div class="card mb-4 shadow-sm">
                    <div class="card-header fw-bold">
                        Purchase Order Summary
                    </div>

                    <div class="card-body">
                        <input type="hidden" id="form_company_id" value="{{ $formCompanyId }}">
                        <input type="hidden" id="form_company_name" value="{{ $formCompanyName }}">

                        <input type="hidden"
                            class="account-select"
                            value="{{ $sparePart->account_id }}">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Supplier</label>
                                <div class="form-control form-control-sm bg-light">
                                    {{ $sparePart->account->account_name ?? '-' }}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">PO Number</label>
                                <div class="form-control form-control-sm bg-light">
                                    {{ $sparePart->po_number }}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">PO Date</label>
                                <div class="form-control form-control-sm bg-light">
                                    {{ \Carbon\Carbon::parse($sparePart->po_date)->format('d-m-Y') }}
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Freight</label>
                                <div class="form-control form-control-sm bg-light text-center">
                                    {{ $sparePart->freight_text }}
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Vehicle No</label>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="vehicle_no"
                                    value="{{ $vehicleEntry->vehicle_no ?? '' }}"
                                    required placeholder="Vehicle No">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Transport</label>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="transport"
                                    required placeholder="Transport">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Invoice No</label>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="invoice_no"
                                    required placeholder="Invoice No">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Invoice Date</label>
                                <input type="date"
                                    class="form-control form-control-sm"
                                    id="invoice_date"
                                    value="{{ $sparePart->po_date }}"
                                    required >
                            </div>
                        </div>

                        @if($sparePart->freight == 1)
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Bill Sundry</label>
                                <select class="form-select form-select-sm" id="bill_sundry_id">
                                    <option value="">Select Bill Sundry</option>
                                    @foreach($billsundry as $value)
                                        @if(
                                            $value->nature_of_sundry != 'CGST' &&
                                            $value->nature_of_sundry != 'SGST' &&
                                            $value->nature_of_sundry != 'IGST' &&
                                            $value->nature_of_sundry != 'ROUNDED OFF (+)' &&
                                            $value->nature_of_sundry != 'ROUNDED OFF (-)'
                                        )
                                            <option value="{{ $value->id }}">
                                                {{ $value->name }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Freight Amt</label>
                                <input type="number"
                                    class="form-control form-control-sm"
                                    id="freight_amount"
                                    required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">E-Way Bill No</label>
                                <input type="text"
                                    class="form-control form-control-sm"
                                    id="eway_bill_no"
                                    required>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Bill To</label>
                                <div class="form-control bg-light">
                                    {{ $sparePart->bill_to_name }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Ship To</label>
                                <div class="form-control bg-light">
                                    {{ $sparePart->ship_to_name }}
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                    <input type="hidden" id="spare_part_id" value="{{ $sparePart->id }}">
                    <input type="hidden" id="vehicle_entry_id" value="{{ $vehicleEntryId }}">

                    <div id="itemRows">
                    @foreach($sparePart->items as $item)
                        <div class="row mb-3 item-row align-items-end">

                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Item</label>
                                <input type="text" class="form-control" value="{{ $item->item->name }}" readonly>
                                <input type="hidden" name="item_id[]" value="{{ $item->item_id }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Unit</label>
                                <input type="text" class="form-control" value="{{ $item->unit }}" readonly>
                                <input type="hidden" name="unit[]" value="{{ $item->unit }}">
                            </div>


                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Ordered Qty</label>
                                <input type="number" class="form-control ordered-qty" value="{{ $item->quantity }}" readonly>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Got Qty</label>
                                <input type="number" class="form-control got-qty"
                                    value="{{ $item->quantity }}"
                                    min="0" max="{{ $item->quantity }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Price</label>
                                <input type="number" class="form-control" value="{{ $item->price }}" readonly>
                                <input type="hidden" name="price[]" value="{{ $item->price }}">
                            </div>

                        </div>
                    @endforeach
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" id="submit" class="btn btn-primary">
                            Start
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </section>
</div>

{{-- ================= PARTIAL QTY CONFIRM MODAL ================= --}}
<div class="modal fade" id="partialQtyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-3">

            <div class="modal-header border-0">
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>

            <div class="modal-body text-center px-4 pb-4">
                <p class="mb-4 text-dark">
                    You received less quantity than ordered.
                    <br>
                    What do you want?
                </p>

                <div class="d-flex justify-content-center gap-3">
                    <button type="button"
                            class="btn btn-outline-secondary px-4 rounded-pill"
                            id="keepPendingBtn">
                        Keep Pending
                    </button>

                    <button type="button"
                            class="btn btn-primary px-4 rounded-pill"
                            id="closePurchaseBtn">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="entryTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-3">

            <div class="modal-header border-0">
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body text-center px-4 pb-4">

                <p class="mb-4 text-dark">
                    Where do you want to create entry?
                </p>

                <div class="d-flex justify-content-center gap-3">

                    <button type="button"
                            class="btn btn-primary px-4 rounded-pill"
                            id="redirectPurchase">
                        Purchase
                    </button>

                    <button type="button"
                            class="btn btn-success px-4 rounded-pill"
                            id="redirectJournal">
                        Journal
                    </button>

                </div>

            </div>

        </div>
    </div>
</div>
@include('layouts.footer')

<script>
let pendingPayload = null;
let confirmedOnce = false;

function checkCompanyMismatchAndProceed(callback) {

    if (confirmedOnce) {
        callback();
        return;
    }

    const formCompanyId   = document.getElementById('form_company_id').value;
    const formCompanyName = document.getElementById('form_company_name').value;
    const sessionCompanyId = localStorage.getItem('active_company_id');

    if (sessionCompanyId && sessionCompanyId !== formCompanyId) {

        const msg =
`This record belongs to "${formCompanyName}"

You have switched company in another tab.

Saving will STORE under "${formCompanyName}"

Do you want to continue?`;

        if (confirm(msg)) {
            confirmedOnce = true;
            callback();
        }

        return;
    }

    callback();
}
$(document).ready(function () {

    $('.select2-single').select2({
        placeholder: "Select an account",
        allowClear: true,
        width: '100%'
    });

    $('#submit').on('click', function (e) {
        e.preventDefault();

        let account_id     = $('.account-select').val();
        let spare_part_id  = $('#spare_part_id').val();
        let vehicle_no     = $('#vehicle_no').val();
        let transport      = $('#transport').val();
        let invoice_no     = $('#invoice_no').val();
        let invoice_date   = $('#invoice_date').val();
        let bill_sundry_id = $('#bill_sundry_id').val();
        let freight_amount = $('#freight_amount').val() || null;
        let eway_bill_no   = $('#eway_bill_no').val() || null;

        if (!account_id) {
            alert('Account not found');
            return;
        }

        if (!vehicle_no) {
            alert('Vehicle number is required');
            $('#vehicle_no').focus();
            return;
        }

        if (!transport) {
            alert('Transport is required');
            $('#transport').focus();
            return;
        }

        if (!invoice_no) {
            alert('Invoice number is required');
            $('#invoice_no').focus();
            return;
        }

        if (!invoice_date) {
            alert('Invoice date is required');
            $('#invoice_date').focus();
            return;
        }

        @if($sparePart->freight == 1)
            if (!bill_sundry_id) {
                alert('Please select Bill Sundry');
                $('#bill_sundry_id').focus();
                return;
            }

            if (!freight_amount) {
                alert('Freight amount is required');
                $('#freight_amount').focus();
                return;
            }


            if (!eway_bill_no) {
                alert('E-Way Bill number is required');
                $('#eway_bill_no').focus();
                return;
            }
        @endif

        let items = [];
        let hasPartial = false;

        $('.item-row').each(function () {

            let ordered = parseFloat($(this).find('.ordered-qty').val());
            let got     = parseFloat($(this).find('.got-qty').val());

            if (got < ordered) {
                hasPartial = true;
            }

            items.push({
                item_id: $(this).find("input[name='item_id[]']").val(),
                ordered_qty: ordered,
                got_qty: got,
                unit: $(this).find("input[name='unit[]']").val(),
                price: $(this).find("input[name='price[]']").val()
            });
        });

        pendingPayload = {
            items,
            account_id,
            spare_part_id,
            vehicle_no,
            transport,
            invoice_no,
            invoice_date,
            bill_sundry_id,
            freight_amount,
            eway_bill_no
        };

        checkCompanyMismatchAndProceed(function () {

    if (hasPartial) {
    $('#partialQtyModal').modal('show');
    } else {
        pendingPayload.closePurchase = false;
        $('#entryTypeModal').modal('show');
    }
    $('#redirectPurchase').on('click', function () {

        $('#entryTypeModal').modal('hide');

        checkCompanyMismatchAndProceed(function () {
            proceedToPurchase(pendingPayload.closePurchase ?? false);
        });

    });

    $('#redirectJournal').on('click', function () {

        $('#entryTypeModal').modal('hide');

        checkCompanyMismatchAndProceed(function () {
            proceedToJournal(pendingPayload.closePurchase ?? false);
        });

    });
});

    });

    $('#closePurchaseBtn').on('click', function () {

    $('#partialQtyModal').modal('hide');

    pendingPayload.closePurchase = true;

    $('#entryTypeModal').modal('show');

});
    $('#keepPendingBtn').on('click', function () {

    $('#partialQtyModal').modal('hide');

    pendingPayload.closePurchase = false;

    $('#entryTypeModal').modal('show');

});
});
function proceedToPurchase(closePurchase) {

    let payloadItems = pendingPayload.items.map(i => ({
        item_id: i.item_id,
        quantity: closePurchase ? i.ordered_qty : i.got_qty,
        unit: i.unit,
        price: i.price
    }));

    let jsonData = encodeURIComponent(JSON.stringify(payloadItems));

    window.location.href =
        "{{ url('purchase/create') }}" +
        "?items=" + jsonData +
        "&account_id=" + pendingPayload.account_id +
        "&spare_part_id=" + pendingPayload.spare_part_id +
        "&close_purchase=" + (closePurchase ? 1 : 0) +
        "&vehicle_no=" + encodeURIComponent(pendingPayload.vehicle_no) +
        "&transport=" + encodeURIComponent(pendingPayload.transport) +
        "&invoice_no=" + encodeURIComponent(pendingPayload.invoice_no) +
        "&invoice_date=" + encodeURIComponent(pendingPayload.invoice_date) +
        "&bill_sundry_id=" + pendingPayload.bill_sundry_id +
        "&freight_amount=" + encodeURIComponent(pendingPayload.freight_amount ?? '') +
        "&eway_bill_no=" + encodeURIComponent(pendingPayload.eway_bill_no ?? '')
        + "&vehicle_entry_id=" + $('#vehicle_entry_id').val();
}

function proceedToJournal(closePurchase) {

    let payloadItems = pendingPayload.items.map(i => ({
        item_id: i.item_id,
        quantity: closePurchase ? i.ordered_qty : i.got_qty,
        unit: i.unit,
        price: i.price
    }));

    let jsonData = encodeURIComponent(JSON.stringify(payloadItems));

    window.location.href =
        "{{ url('journal/create') }}" +
        "?items=" + jsonData +
        "&account_id=" + pendingPayload.account_id +
        "&spare_part_id=" + pendingPayload.spare_part_id +
        "&close_purchase=" + (closePurchase ? 1 : 0) +
        "&vehicle_no=" + encodeURIComponent(pendingPayload.vehicle_no) +
        "&transport=" + encodeURIComponent(pendingPayload.transport) +
        "&invoice_no=" + encodeURIComponent(pendingPayload.invoice_no) +
        "&invoice_date=" + encodeURIComponent(pendingPayload.invoice_date) +
        "&bill_sundry_id=" + pendingPayload.bill_sundry_id +
        "&freight_amount=" + encodeURIComponent(pendingPayload.freight_amount ?? '') +
        "&eway_bill_no=" + encodeURIComponent(pendingPayload.eway_bill_no ?? '') +
        "&vehicle_entry_id=" + $('#vehicle_entry_id').val();

}
</script>

@endsection
