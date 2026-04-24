@extends('layouts.app')

@section('content')
    @include('layouts.header')
<style>
    .select2-container {
    width: 100% !important;
}

.select2-selection--single {
    height: 34px !important;
    display: flex !important;
    align-items: center !important;
    border-radius: 6px !important;
}

.select2-selection__rendered {
    line-height: normal !important;
}

.select2-selection__arrow {
    height: 100% !important;
}
</style>
    <div class="list-of-view-company">
        <section class="list-of-view-company-section container-fluid">
            <div class="row vh-100">

                @include('layouts.leftnav')

                <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet shadow-sm">
                        Edit Spare Part – Supplier Comparison
                    </h5>

                    @php
                        $groupedOffers = $offers->groupBy('account_id');
                    @endphp

                    {{-- ================= SUPPLIER OFFERS COMPARISON ================= --}}
                    <div class="card mb-4">
                        <div class="card-header fw-bold">
                            Supplier Offers Comparison
                        </div>

                        <form method="POST"
                            id="finalizeSparePartForm"
                            action="{{ url('supplier/spare-part/'.$sparePart->id.'/finalize') }}">

                            @csrf

                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Select</th>
                                            <th>Account</th>
                                            <th>Item</th>
                                            <th>Unit</th>
                                            <th>Qty Offered</th>
                                            <th>Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($groupedOffers as $accountId => $supplierOffers)
                                            @php
                                                $rowspan     = $supplierOffers->count();
                                                $accountName = $supplierOffers->first()->account->account_name;
                                            @endphp

                                            @foreach ($supplierOffers as $i => $offer)
                                                <tr>
                                                    @if ($i === 0)
                                                        <td rowspan="{{ $rowspan }}"
                                                            class="text-center align-middle">
                                                            <input type="radio"
                                                                   name="selected_account_id"
                                                                   value="{{ $accountId }}"
                                                                   {{ (int) $sparePart->account_id === (int) $accountId ? 'checked' : '' }}
                                                                   required>
                                                        </td>

                                                        <td rowspan="{{ $rowspan }}"
                                                            class="align-middle">
                                                            {{ $accountName }}
                                                        </td>
                                                    @endif

                                                    <td>{{ $offer->item->name }}</td>
                                                    <td>{{ $offer->item->unit->name ?? '-' }}</td>
                                                    <td>{{ $offer->offered_quantity }}</td>
                                                    <td>{{ number_format($offer->offered_price, 2) }}</td>

                                                    @if ($i === 0)
                                                        <td rowspan="{{ $rowspan }}"
                                                            class="align-middle text-center">
                                                            <button type="button"
                                                                    class="btn btn-warning btn-sm editSupplier"
                                                                    data-spare="{{ $sparePart->id }}"
                                                                    data-account="{{ $accountId }}">
                                                                Edit
                                                            </button>

                                                            <button type="button"
                                                                    class="btn btn-danger btn-sm deleteSupplier"
                                                                    data-spare="{{ $sparePart->id }}"
                                                                    data-account="{{ $accountId }}">
                                                                Delete
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="7"
                                                    class="text-center text-muted">
                                                    No supplier offers added yet
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{-- ================= PURCHASE ORDER DETAILS ================= --}}
                            <div class="card mb-4">
                                <div class="card-header fw-bold">
                                    Purchase Order Details
                                </div>

                                <div class="card-body">
                                    <div class="row">

                                        {{-- PO NUMBER --}}
                                        @if(!empty($sparePart->po_number))
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">PO Number</label>
                                                <input type="text"
                                                    class="form-control"
                                                    value="{{ $sparePart->po_number }}"
                                                    readonly>
                                            </div>
                                        @endif


                                        {{-- PO DATE --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">PO Date</label>
                                            <input type="date"
                                                name="po_date"
                                                class="form-control"
                                                value="{{ $sparePart->po_date ?? now()->format('Y-m-d') }}"
                                                required>
                                        </div>
                                        {{-- FREIGHT --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Freight</label>
                                            <select name="freight"
                                                    class="form-select"
                                                    required>
                                                <option value="">Select</option>
                                                <option value="1"
                                                    {{ old('freight', $sparePart->freight) == '1' ? 'selected' : '' }}>
                                                    Yes
                                                </option>
                                                <option value="0"
                                                    {{ old('freight', $sparePart->freight) == '0' ? 'selected' : '' }}>
                                                    No
                                                </option>
                                            </select>
                                        </div>


                                        {{-- BILL TO --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Bill To</label>

                                            @php
                                                // Decide selected value
                                                if ($sparePart->bill_to_company_id) {
                                                    $billToSelected = 'company_' . $sparePart->bill_to_company_id;
                                                } elseif ($sparePart->bill_to_account_id) {
                                                    $billToSelected = 'account_' . $sparePart->bill_to_account_id;
                                                } else {
                                                    // default → session company
                                                    $billToSelected = 'company_' . $company->id;
                                                }
                                            @endphp

                                            <select name="bill_to_selector"
                                                    class="form-select select2-single"
                                                    required>

                                                {{-- COMPANY --}}
                                                <optgroup label="Company">
                                                    <option value="company_{{ $company->id }}"
                                                        {{ $billToSelected === 'company_'.$company->id ? 'selected' : '' }}>
                                                        {{ $company->company_name }}
                                                    </option>
                                                </optgroup>

                                                {{-- ACCOUNTS --}}
                                                <optgroup label="Accounts">
                                                    @foreach($accounts as $acc)
                                                        <option value="account_{{ $acc->id }}"
                                                            {{ $billToSelected === 'account_'.$acc->id ? 'selected' : '' }}>
                                                            {{ $acc->account_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                            <input type="hidden" name="bill_to_company_id" id="bill_to_company_id">
                                        <input type="hidden" name="bill_to_account_id" id="bill_to_account_id">

                                        </div>
                                        {{-- SHIP TO --}}
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Ship To</label>

                                            @php
                                                if ($sparePart->ship_to_company_id) {
                                                    $shipToSelected = 'company_' . $sparePart->ship_to_company_id;
                                                } elseif ($sparePart->ship_to_account_id) {
                                                    $shipToSelected = 'account_' . $sparePart->ship_to_account_id;
                                                } else {
                                                    $shipToSelected = 'company_' . $company->id;
                                                }
                                            @endphp

                                            <select name="ship_to_selector"
                                                    class="form-select select2-single"
                                                    required>

                                                <optgroup label="Company">
                                                    <option value="company_{{ $company->id }}"
                                                        {{ $shipToSelected === 'company_'.$company->id ? 'selected' : '' }}>
                                                        {{ $company->company_name }}
                                                    </option>
                                                </optgroup>

                                                <optgroup label="Accounts">
                                                    @foreach($accounts as $acc)
                                                        <option value="account_{{ $acc->id }}"
                                                            {{ $shipToSelected === 'account_'.$acc->id ? 'selected' : '' }}>
                                                            {{ $acc->account_name }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                            <input type="hidden" name="ship_to_company_id" id="ship_to_company_id">
                                        <input type="hidden" name="ship_to_account_id" id="ship_to_account_id">

                                        </div>

                                        {{-- PO NARRATION --}}
                                        <div class="col-md-12">
                                            <label class="form-label">PO Narration</label>
                                            <textarea name="po_narration"
                                                    class="form-control"
                                                    rows="2"
                                                    placeholder="Optional narration for this purchase order">{{ $sparePart->po_narration }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="text-end p-3">
                                <button type="button"
                                        class="btn btn-primary"
                                        id="openFinalizeModal"
                                        {{ $offers->count() === 0 ? 'disabled' : '' }}>
                                    Next / Finalize Selected Supplier
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ================= ADD SUPPLIER OFFER ================= --}}
                    <div class="card mb-4">
                        <div class="card-header fw-bold">
                            Add Supplier Offer
                        </div>

                        <div class="card-body">
                            <form method="POST"
                                  action="{{ $sparePart->id == 0
                                        ? route('spare-part.draft.save-offers')
                                        : route('spare-part.save-offers', $sparePart->id) }}">
                                @csrf

                                <div id="offerRows">
                                    <div class="offer-row border rounded p-3 mb-3"
                                         data-index="0">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">
                                                    Account
                                                </label>

                                                <select name="offers[0][account_id]"
                                                        class="form-select select2-single"
                                                        required>
                                                    <option value="">
                                                        Select Account
                                                    </option>

                                                    @foreach ($accounts as $account)
                                                        <option value="{{ $account->id }}">
                                                            {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        @foreach ($sparePart->items as $i => $item)
                                            <div class="row mt-3">
                                                <div class="col-md-3">
                                                    <label>Item</label>
                                                    <input class="form-control"
                                                           value="{{ $item->item->name }}"
                                                           readonly>

                                                    <input type="hidden"
                                                           name="offers[0][items][{{ $i }}][item_id]"
                                                           value="{{ $item->item_id }}">
                                                </div>

                                                <div class="col-md-2">
                                                    <label>Unit</label>
                                                    <input class="form-control"
                                                           value="{{ $item->unit }}"
                                                           readonly>
                                                </div>

                                                <div class="col-md-2">
                                                    <label>Quantity</label>
                                                    <input type="number"
                                                           name="offers[0][items][{{ $i }}][quantity]"
                                                           class="form-control"
                                                           value="{{ $item->quantity }}"
                                                           step="any"
                                                           required>
                                                </div>

                                                <div class="col-md-2">
                                                    <label>Price</label>
                                                    <input type="number"
                                                           name="offers[0][items][{{ $i }}][price]"
                                                           class="form-control"
                                                           step="any"
                                                           required>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit"
                                            class="btn btn-success">
                                        Save Offers
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ================= EDIT MODAL ================= --}}
                    <div class="modal fade"
                         id="editSupplierModal"
                         tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <form method="POST"
                                  action="{{ route('spare-part.offer.update') }}">
                                @csrf

                                <input type="hidden"
                                       name="spare_part_id"
                                       id="edit_spare_part_id">

                                <input type="hidden"
                                       name="old_account_id"
                                       id="edit_old_account_id">

                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5>Edit Supplier Offer</h5>
                                        <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label>Account</label>
                                            <select name="account_id"
                                                    id="edit_account_id"
                                                    class="form-control select2-single"
                                                    required>
                                                @foreach ($accounts as $acc)
                                                    <option value="{{ $acc->id }}">
                                                        {{ $acc->account_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <hr>

                                        <div id="editItemsContainer"></div>
                                        <div class="text-end mt-2">
                                            <button type="button" class="btn btn-primary btn-sm" id="addEditRow">
                                                + Add Item
                                            </button>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button"
                                        class="btn btn-secondary"
                                        data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                        <button type="submit" class="btn btn-success">
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>

    {{-- ================= FINALIZE CONFIRM MODAL ================= --}}
    <div class="modal fade"
         id="finalizeConfirmModal"
         tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm rounded-3">
                <div class="modal-body text-center py-4 px-4">
                    <p class="mb-4 fs-6 text-dark">
                        Are you sure you want to finalize this supplier?
                    </p>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="button"
                                class="btn btn-outline-secondary px-4 rounded-pill"
                                data-bs-dismiss="modal">
                            No
                        </button>

                        <button type="submit"
                                class="btn btn-primary px-4 rounded-pill"
                                form="finalizeSparePartForm">
                            Yes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    @php
        $allItems = \App\Models\ManageItems::select('id','name','u_name')
            ->where('company_id', session('user_company_id'))
            ->where('status','1')
            ->where('delete','0')
            ->get();

        $formattedItems = [];

        foreach ($allItems as $i) {
            $formattedItems[] = [
                'id' => $i->id,
                'name' => $i->name,
                'unit' => \App\Models\Units::where('id', $i->u_name)->value('name')
            ];
        }
        $baseItemIds = $sparePart->items->pluck('item_id')->toArray();
    @endphp
    {{-- ================= JS ================= --}}
    <script>
        let allItems = @json($formattedItems);
        let baseItems = @json($baseItemIds);
        $(document).on('click', '.editSupplier', function () {
            let spareId   = $(this).data('spare');
            let accountId = $(this).data('account');

            $('#edit_spare_part_id').val(spareId);
            $('#edit_old_account_id').val(accountId);
            $('#edit_account_id').val(accountId).trigger('change');

            $.get("{{ route('spare-part.offer.fetch') }}", {
                spare_part_id: spareId,
                account_id: accountId
            }, function (res) {
                let html = '';

                res.forEach((r, i) => {
                    html += getEditRow(
                        r.item_id,
                        r.offered_quantity,
                        r.offered_price,
                        i
                    );
                });

                $('#editItemsContainer').html(html);

                // 🔥 INIT SELECT2 INSIDE MODAL
                $('#editItemsContainer .select2-single').select2({
                    dropdownParent: $('#editSupplierModal')
                });

                // 🔥 ADD THIS BLOCK
                $('#editItemsContainer .edit-row').each(function () {
                    let select = $(this).find('.item-select');
                    select.trigger('change');
                });

                $('#editSupplierModal').modal('show');

            });
        });

        $(document).on('click', '.deleteSupplier', function () {
            if (!confirm('Delete this supplier offer?')) return;

            $('<form>', {
                method: 'POST',
                action: "{{ route('spare-part.offer.delete') }}"
            })
                .append('@csrf')
                .append(`<input type="hidden" name="spare_part_id" value="${$(this).data('spare')}">`)
                .append(`<input type="hidden" name="account_id" value="${$(this).data('account')}">`)
                .appendTo('body')
                .submit();
        });

        $('#openFinalizeModal').on('click', function () {

            syncBillShipSelectors();

            let supplierSelected = $('input[name="selected_account_id"]:checked').length;

            let billCompany = $('#bill_to_company_id').val();
            let billAccount = $('#bill_to_account_id').val();
            let shipCompany = $('#ship_to_company_id').val();
            let shipAccount = $('#ship_to_account_id').val();

            if (!supplierSelected) {
                alert('Please select a supplier before finalizing.');
                return;
            }

            if (!billCompany && !billAccount) {
                alert('Please select Bill To.');
                return;
            }

            if (!shipCompany && !shipAccount) {
                alert('Please select Ship To.');
                return;
            }

            $('#finalizeConfirmModal').modal('show');
        });

        function syncBillShipSelectors() {

            let billVal = $('select[name="bill_to_selector"]').val();
            $('#bill_to_company_id').val('');
            $('#bill_to_account_id').val('');

            if (billVal) {
                let [type, id] = billVal.split('_');
                if (type === 'company') {
                    $('#bill_to_company_id').val(id);
                } else {
                    $('#bill_to_account_id').val(id);
                }
            }

            let shipVal = $('select[name="ship_to_selector"]').val();
            $('#ship_to_company_id').val('');
            $('#ship_to_account_id').val('');

            if (shipVal) {
                let [type, id] = shipVal.split('_');
                if (type === 'company') {
                    $('#ship_to_company_id').val(id);
                } else {
                    $('#ship_to_account_id').val(id);
                }
            }
        }

        $(document).ready(syncBillShipSelectors);
        $(document).on('change', 'select[name="bill_to_selector"], select[name="ship_to_selector"]', syncBillShipSelectors);

        $(document).ready(function(){
            $(".select2-single").select2();
        })

        function getEditRow(itemId = '', qty = '', price = '', index = 0) {

            let isBaseItem = baseItems.includes(parseInt(itemId));

            let options = '<option value="">Select Item</option>';

            allItems.forEach(item => {
                let selected = (parseInt(item.id) === parseInt(itemId)) ? 'selected' : '';

                options += `<option value="${item.id}" ${selected}>
                    ${item.name}
                </option>`;
            });

            let unit = (allItems.find(i => parseInt(i.id) === parseInt(itemId)) || {}).unit || '';

            return `
                <div class="row mb-2 edit-row align-items-center p-2 border rounded">

                    <!-- ITEM -->
                    <div class="col-md-4">
                        <select name="items[${index}][item_id]"
                                class="form-select form-select-sm item-select select2-single"
                                ${isBaseItem ? 'disabled' : ''}
                                required>
                            ${options}
                        </select>

                        ${isBaseItem ? `
                            <input type="hidden" 
                                name="items[${index}][item_id]" 
                                value="${itemId}">
                        ` : ''}
                    </div>

                    <!-- UNIT -->
                    <div class="col-md-2">
                        <input class="form-control form-control-sm unit-field text-center bg-light"
                            value="${unit}"
                            readonly>
                    </div>

                    <!-- QTY -->
                    <div class="col-md-2">
                        <input type="number"
                            name="items[${index}][quantity]"
                            class="form-control form-control-sm text-end"
                            value="${qty}"
                            placeholder="Qty"
                            required type="any">
                    </div>

                    <!-- PRICE -->
                    <div class="col-md-2">
                        <input type="number"
                            name="items[${index}][price]"
                            class="form-control form-control-sm text-end"
                            value="${price}"
                            placeholder="Price"
                            required type="any">
                    </div>

                    <!-- REMOVE -->
                    <div class="col-md-1 text-center">
                        ${!isBaseItem ? `
                            <button type="button"
                                    class="btn btn-danger btn-sm removeRow">
                                ×
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        $('#addEditRow').on('click', function () {
            let index = $('#editItemsContainer .edit-row').length;

            let newRow = $(getEditRow(null, '', '', index));

        $('#editItemsContainer').append(newRow);

        // 🔥 APPLY SELECT2 TO NEW ROW
        newRow.find('.select2-single').select2({
            dropdownParent: $('#editSupplierModal')
        });
        });
        $(document).on('click', '.removeRow', function () {
            $(this).closest('.edit-row').remove();
        });
        $(document).on('change', '.item-select', function () {
            let itemId = $(this).val();
            let row = $(this).closest('.edit-row');

            let item = allItems.find(i => parseInt(i.id) === parseInt(itemId));

            row.find('.unit-field').val(item ? item.unit : '');
        });

        $('#editSupplierModal').on('hidden.bs.modal', function () {
            $('#editItemsContainer').html('');
        });
    </script>
@endsection
