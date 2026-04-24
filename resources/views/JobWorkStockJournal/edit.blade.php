@extends('layouts.app')
@section('content')
@include('layouts.header')

<style type="text/css">
    .text-ellipsis { text-overflow: ellipsis; overflow: hidden; white-space: nowrap; }
    .w-min-50 { min-width: 50px; }
    .dataTables_filter, .dataTables_info, .dataTables_length, .dataTables_paginate { display: none; }
    .select2-container--default .select2-selection--single .select2-selection__rendered{ line-height: 29px !important; }
    .select2-container--default .select2-selection--single .select2-selection__arrow{ height: 30px !important; }
    .select2-container .select2-selection--single{ height: 30px !important; }
    .desc-cell { gap: 6px; display: flex; align-items: center; }
    .desc-cell .select2-container { flex: 1; }
    .configure-size-btn { width: 32px; height: 32px; padding: 0; }
    .select2-container { width: 100% !important; }
    .select2-container--default .select2-selection--single{ border-radius: 12px !important; }
    .selection{ font-size: 14px; }
    .form-control { height: 28px; }
    .form-select { height: 34px; }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none; -moz-appearance: none; appearance: none; margin: 0;
    }
    .pending-qty-col { background-color: #fff3cd; font-weight: bold; }
</style>

<div class="list-of-view-company">
    <section class="container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-lg-9 px-md-4 bg-mint">
                <h5 class="px-4 py-3 bg-plum-viloet shadow-sm">Edit Job Work Stock Journal - {{ $journal->voucher_no ?? 'N/A' }}</h5>

                <div class="bg-white px-4 py-3 shadow-sm">
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('jobwork.stockjournal.update', $journal->id) }}" id="frm">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="id" value="{{ $journal->id }}">

                        {{-- HEADER FIELDS --}}
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label font-14 font-heading">Type <span style="color:red">*</span></label>
                                <select class="form-select select2-single" id="type" name="type" required>
                                    <option value="in" {{ old('type', $journal->type ?? 'in') === 'in' ? 'selected' : '' }}>IN</option>
                                    <option value="out" {{ old('type', $journal->type ?? 'in') === 'out' ? 'selected' : '' }}>OUT</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-14 font-heading">Party <span style="color:red">*</span></label>
                                <select class="form-select select2-single" id="party_id" name="party_id" required>
                                    <option value="">Select Party</option>
                                    @foreach($parties ?? [] as $party)
                                        <option value="{{ $party->id }}"
                                            {{ old('party_id', $journal->party_id ?? '') == $party->id ? 'selected' : '' }}>
                                            {{ $party->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label font-14 font-heading">Party Voucher No (Optional)</label>
                                <select class="form-select select2-single" id="party_voucher_no" name="party_voucher_no">
                                    <option value="">All Vouchers</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label font-14 font-heading">Date</label>
                                <input type="date" class="form-control" name="date" id="journal_date"
                                       value="{{ old('date', $journal->date ?? date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-14 font-heading">
                                    Voucher No <span style="color:red">*</span>
                                </label>
                                <input type="text"
                                       class="form-control"
                                       name="voucher_no"
                                       id="voucher_no"
                                       value="{{ old('voucher_no', $journal->voucher_no ?? '') }}"
                                       placeholder="Enter Voucher No"
                                       required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label font-14 font-heading">Narration</label>
                                <input type="text"
                                       class="form-control"
                                       name="narration"
                                       value="{{ old('narration', $journal->narration ?? '') }}"
                                       placeholder="Enter Narration">
                            </div>
                        </div>

                        {{-- TABLES CONTAINER --}}
                        <div class="transaction-table transaction-main-table bg-white table-view shadow-sm border-radius-8 mb-4">

                            {{-- ITEMS CONSUMED Table --}}
                            <table class="table-striped table m-0 shadow-sm table-bordered">
                                <thead>
                                    <tr><th colspan="7" style="text-align:center">ITEMS CONSUMED</th></tr>
                                    <tr class="font-12 text-body bg-light-pink">
                                        <th class="w-min-50">S No.</th>
                                        <th style="width:36%">DESCRIPTION OF GOODS</th>
                                        <th style="text-align:right;padding-right:24px;">QTY</th>
                                        <th style="text-align:center;">UNIT</th>
                                        <th style="text-align:right;padding-right:24px;">Price</th>
                                        <th style="text-align:right;">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="consume_tbody">
                                    @if(isset($journal->consume_items) && count($journal->consume_items) > 0)
                                        @foreach($journal->consume_items as $index => $consume_item)
                                        <tr class="font-14 font-heading bg-white" id="consume_tr_{{ $index + 1 }}">
                                            <td class="w-min-50">{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center desc-cell">
                                                    <select class="form-control consume_item select2-single"
                                                            name="consume_item[]"
                                                            data-id="{{ $index + 1 }}"
                                                            id="consume_item_{{ $index + 1 }}">
                                                        <option value="{{ $consume_item->desc_id }}" selected
                                                                data-unit="{{ $consume_item->unit }}"
                                                                data-price="{{ $consume_item->price }}">
                                                            {{ $consume_item->item_name }}
                                                        </option>
                                                    </select>
                                                    <input type="hidden" name="item_size_info[]" id="item_size_info_{{ $index + 1 }}">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number"
                                                       name="consume_qty[]"
                                                       class="form-control consume_qty text-end"
                                                       id="consume_qty_{{ $index + 1 }}"
                                                       data-id="{{ $index + 1 }}"
                                                       value="{{ old('consume_qty.' . $index, $consume_item->qty ?? '') }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       class="form-control text-center"
                                                       id="consume_unit_tr_{{ $index + 1 }}"
                                                       readonly
                                                       value="{{ old('consume_unit_name.' . $index, $consume_item->unit ?? '') }}">
                                            </td>
                                            <td>
                                                <input type="number"
                                                       name="consume_price[]"
                                                       class="form-control consume_price text-end"
                                                       id="consume_price_{{ $index + 1 }}"
                                                       data-id="{{ $index + 1 }}"
                                                       value="{{ old('consume_price.' . $index, $consume_item->price ?? '') }}">
                                            </td>
                                            <td>
                                                <input type="text"
                                                       name="consume_amount[]"
                                                       class="form-control consume_amount text-end"
                                                       id="consume_amount_{{ $index + 1 }}"
                                                       readonly
                                                       value="{{ old('consume_amount.' . $index, $consume_item->amount ?? '') }}">
                                            </td>
                                            <td>
                                                @if($index == 0)
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="bg-primary rounded-circle add_more_consume"
                                                     width="24" height="24" viewBox="0 0 24 24"
                                                     style="cursor:pointer">
                                                    <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z"
                                                          fill="white"/>
                                                </svg>
                                                @else
                                                <svg style="color: red;cursor: pointer;"
                                                     xmlns="http://www.w3.org/2000/svg"
                                                     width="16" height="16"
                                                     fill="currentColor"
                                                     class="bi bi-file-minus-fill remove_consume"
                                                     data-id="{{ $index + 1 }}"
                                                     viewBox="0 0 16 16">
                                                    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                                                </svg>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr class="font-14 font-heading bg-white" id="consume_tr_1">
                                            <td class="w-min-50">1</td>
                                            <td>
                                                <div class="d-flex align-items-center desc-cell">
                                                    <select class="form-control consume_item select2-single"
                                                            name="consume_item[]" data-id="1" id="consume_item_1">
                                                        <option value="">Select Item</option>
                                                    </select>
                                                    <input type="hidden" name="item_size_info[]" id="item_size_info_1">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="consume_qty[]"
                                                       class="form-control consume_qty text-end"
                                                       id="consume_qty_1" data-id="1">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-center"
                                                       id="consume_unit_tr_1" readonly>
                                            </td>
                                            <td>
                                                <input type="number" name="consume_price[]"
                                                       class="form-control consume_price text-end"
                                                       id="consume_price_1" data-id="1">
                                            </td>
                                            <td>
                                                <input type="text" name="consume_amount[]"
                                                       class="form-control consume_amount text-end"
                                                       id="consume_amount_1" readonly>
                                            </td>
                                            <td>
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                     class="bg-primary rounded-circle add_more_consume"
                                                     width="24" height="24" viewBox="0 0 24 24"
                                                     style="cursor:pointer">
                                                    <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z"
                                                          fill="white"/>
                                                </svg>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>

                                <tfoot id="consume_total">
                                    <tr class="font-14 font-heading bg-white">
                                        <td class="fw-bold"></td>
                                        <td class="fw-bold">Total</td>
                                        <td class="fw-bold" id="qty_total" style="text-align: right;">0</td>
                                        <td class="fw-bold"></td>
                                        <td class="fw-bold"></td>
                                        <td class="fw-bold" id="amount_total" style="text-align: right;">0</td>
                                        <td class="fw-bold"></td>
                                    </tr>
                                </tfoot>
                            </table>

                            {{-- ITEMS GENERATED Table --}}
                            <table id="example11" class="table-striped table m-0 shadow-sm table-bordered">
                                <thead>
                                    <tr><th colspan="7" style="text-align:center">ITEMS GENERATED</th></tr>
                                    <tr class=" font-12 text-body bg-light-pink ">
                                        <th class="w-min-50 border-none bg-light-pink text-body">S No.</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body " style="width: 36%;">DESCRIPTION OF GOODS</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">QUANTITY</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: center;">UNIT</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body " style="text-align: right;padding-right: 24px;">Price</th>
                                        <th class="w-min-120 border-none bg-light-pink text-body ">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="generated_tbody">
                                    @if(isset($journal->generated_items) && count($journal->generated_items) > 0)
                                        @foreach($journal->generated_items as $index => $generated_item)
                                        <tr class="font-14 font-heading bg-white" id="tr1_{{ $index + 1 }}">
                                            <td class="w-min-50" id="generated_srn_{{ $index + 1 }}">{{ $index + 1 }}</td>
                                            <td class="w-min-50">
                                                <div class="d-flex align-items-center">
                                                    <select class="form-control generated_item select2-single"
                                                            name="generated_item[]"
                                                            data-id="{{ $index + 1 }}"
                                                            id="generated_item_{{ $index + 1 }}">
                                                        <option value="">Select Item</option>
                                                        @foreach($items ?? [] as $item)
                                                            <option value="{{ $item->id }}"
                                                                    data-unit_id="{{ $item->u_name }}"
                                                                    data-unit_name="{{ $item->unit }}"
                                                                    {{ old('generated_item.' . $index, $generated_item->item_id ?? '') == $item->id ? 'selected' : '' }}>
                                                                {{ $item->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="generated_size_info[]" id="generated_size_info_{{ $index + 1 }}" data-id="{{ $index + 1 }}">
                                                </div>
                                            </td>
                                            <td class="">
                                                <input type="number"
                                                       name="generated_weight[]"
                                                       class="form-control generated_weight"
                                                       data-id="{{ $index + 1 }}"
                                                       id="generated_weight_{{ $index + 1 }}"
                                                       value="{{ old('generated_weight.' . $index, $generated_item->weight ?? '') }}"
                                                       placeholder="Qty"
                                                       style="text-align: right;">
                                            </td>
                                            <td class="w-min-50">
                                                <input type="text"
                                                       class="w-100 form-control generated_unit"
                                                       id="generated_unit_tr_{{ $index + 1 }}"
                                                       readonly
                                                       style="text-align:center;"
                                                       data-id="{{ $index + 1 }}"
                                                       name="generated_unit_name[]"
                                                       value="{{ old('generated_unit_name.' . $index, $generated_item->unit ?? '') }}"/>
                                                <input type="hidden"
                                                       class="generated_units w-100"
                                                       name="generated_units[]"
                                                       id="generated_units_tr_{{ $index + 1 }}"
                                                       value="{{ old('generated_units.' . $index, $generated_item->unit_id ?? '') }}"/>
                                            </td>
                                            <td class="">
                                                <input type="number"
                                                       name="generated_price[]"
                                                       class="form-control generated_price"
                                                       data-id="{{ $index + 1 }}"
                                                       id="generated_price_{{ $index + 1 }}"
                                                       value="{{ old('generated_price.' . $index, $generated_item->price ?? '') }}"
                                                       placeholder="Price"
                                                       style="text-align: right;">
                                            </td>
                                            <td class="">
                                                <input type="text"
                                                       name="generated_amount[]"
                                                       class="form-control generated_amount"
                                                       data-id="{{ $index + 1 }}"
                                                       id="generated_amount_{{ $index + 1 }}"
                                                       value="{{ old('generated_amount.' . $index, $generated_item->amount ?? '') }}"
                                                       placeholder="Amount"
                                                       readonly
                                                       style="text-align: right;">
                                            </td>
                                            <td>
                                                @if($index == 0)
                                                <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more1" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                                    <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                                </svg>
                                                @else
                                                <svg style="color: red;cursor: pointer;"
                                                     xmlns="http://www.w3.org/2000/svg"
                                                     width="16" height="16"
                                                     fill="currentColor"
                                                     class="bi bi-file-minus-fill remove1"
                                                     data-id="{{ $index + 1 }}"
                                                     viewBox="0 0 16 16">
                                                    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                                                </svg>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr class="font-14 font-heading bg-white">
                                            <td class="w-min-50" id="generated_srn_1">1</td>
                                            <td class="w-min-50">
                                                <div class="d-flex align-items-center">
                                                    <select class="form-control generated_item select2-single" name="generated_item[]" data-id="1" id="generated_item_1">
                                                        <option value="">Select Item</option>
                                                        @foreach($items ?? [] as $item)
                                                            <option value="{{$item->id}}" data-unit_id="{{$item->u_name}}" data-unit_name="{{$item->unit}}">
                                                                {{$item->name}}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="generated_size_info[]" id="generated_size_info_1" data-id="1">
                                                </div>
                                            </td>
                                            <td class="">
                                                <input type="number" name="generated_weight[]"
                                                       class="form-control generated_weight"
                                                       data-id="1"
                                                       id="generated_weight_1"
                                                       placeholder="Qty"
                                                       style="text-align: right;">
                                            </td>
                                            <td class="w-min-50">
                                                <input type="text" class="w-100 form-control generated_unit" id="generated_unit_tr_1" readonly style="text-align:center;" data-id="1" name="generated_unit_name[]"/>
                                                <input type="hidden" class="generated_units w-100" name="generated_units[]" id="generated_units_tr_1" />
                                            </td>
                                            <td class="">
                                                <input type="number" name="generated_price[]" class="form-control generated_price" data-id="1" id="generated_price_1" placeholder="Price" style="text-align: right;">
                                            </td>
                                            <td class="">
                                                <input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="1" id="generated_amount_1" placeholder="Amount" readonly style="text-align: right;">
                                            </td>
                                            <td>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="bg-primary rounded-circle add_more1" width="24" height="24" viewBox="0 0 24 24" fill="none" style="cursor: pointer;">
                                                    <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white" />
                                                </svg>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                            <div class="total">
                                <tr class="font-14 font-heading bg-white">
                                    <td class="fw-bold"></td>
                                    <td class="fw-bold">Total</td>
                                    <td class="fw-bold" id="generated_weight_total" style="text-align: right;">0</td>
                                    <td class="fw-bold"></td>
                                    <td class="fw-bold"></td>
                                    <td class="fw-bold" id="generated_amount_total" style="text-align: right;">0</td>
                                    <td class="fw-bold"></td>
                                </tr>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('jobwork.stockjournal.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-xs-primary savebtn">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
    let consumeItemOptions = `<option value="">Select Item</option>`;
    let consumeRowCount = {{ count($journal->consume_items ?? []) }};
    let add_more_count1 = {{ count($journal->generated_items ?? []) }};

$(document).ready(function() {
    $(".select2-single").select2();

    // Party change - load party vouchers
    $('#party_id, #type').on('change', function() {
        let partyId = $('#party_id').val();
        let type = $('#type').val();
        $('#party_voucher_no').empty().append('<option value="">All Vouchers</option>');
        if (partyId) {
            $.ajax({
                url: "{{ route('jobwork.stockjournal.party-vouchers') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    party_id: partyId,
                    type: type
                },
                success: function(data) {
                    $.each(data.vouchers, function(index, voucher) {
                        let selected = '{{ $journal->party_voucher_no ?? '' }}' == voucher.id ? 'selected' : '';
                        $('#party_voucher_no').append(`<option value="${voucher.id}" ${selected}>${voucher.voucher_no}</option>`);
                    });
                }
            });
        }
        loadPendingItems();
    });

    // Party voucher change
    $('#party_voucher_no').on('change', function() {
        loadPendingItems();
    });

    // Load pending items based on party/voucher/date
    function loadPendingItems() {
        let partyId   = $('#party_id').val();
        let voucherId = $('#party_voucher_no').val();
        let date      = $('#journal_date').val();
        let type      = $('#type').val();

        if (!partyId || !date) {
            return;
        }

        $.ajax({
            url: "{{ route('jobwork.stockjournal.pending-items') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                party_id: partyId,
                party_voucher_no: voucherId,
                date: date,
                type: type
            },
            success: function (rows) {
                consumeItemOptions = `<option value="">Select Item</option>`;

                rows.forEach(row => {
                    consumeItemOptions += `
                        <option value="${row.id}"
                            data-unit="${row.unit}"
                            data-price="${row.price}"
                            data-pending="${row.pending_qty}">
                            ${row.name} (${row.pending_qty})
                        </option>`;
                });

                // Update all consume item dropdowns while preserving current selection
                $('.consume_item').each(function () {
                    let currentVal = $(this).val();
                    let currentText = $(this).find('option:selected').text();

                    $(this).select2('destroy');
                    $(this).html(consumeItemOptions);

                    // Re-add previously selected if exists
                    if (currentVal && currentText) {
                        $(this).prepend(`<option value="${currentVal}" selected>${currentText}</option>`);
                    }

                    $(this).select2();
                });
            }
        });
    }

    $('#journal_date').on('change', function () {
        loadPendingItems();
    });

    // Item change handler
    $(document).on('change', '.consume_item', function () {
        let id = $(this).data('id');
        let opt = $(this).find(':selected');

        $('#consume_unit_tr_' + id).val(opt.data('unit'));
        $('#consume_price_' + id).val(opt.data('price'));

        calculateRowAmount(id);
    });

    // Generated item change handler
    $(document).on('change', '.generated_item', function () {
        let id  = $(this).data('id');
        let opt = $(this).find(':selected');

        let unitId   = opt.data('unit_id');
        let unitName = opt.data('unit_name');

        $('#generated_unit_tr_' + id).val(unitName);
        $('#generated_units_tr_' + id).val(unitId);
    });

    // Calculate single row amount
    function calculateRowAmount(rowId) {
        let qty = parseFloat($('#consume_qty_' + rowId).val()) || 0;
        let price = parseFloat($('#consume_price_' + rowId).val()) || 0;
        let amount = qty * price;
        $('#consume_amount_' + rowId).val(amount.toLocaleString('en-IN', {maximumFractionDigits: 2}));
    }

    // Calculate CONSUMED totals
    function calculateConsumeTotals() {
        let totalQty = 0, totalAmount = 0;
        $('#consume_tbody tr').each(function() {
            let rowId = $(this).find('.consume_price').data('id');
            if (rowId) {
                let qty = parseFloat($('#consume_qty_' + rowId).val()) || 0;
                let price = parseFloat($('#consume_price_' + rowId).val()) || 0;
                let amount = qty * price;

                $('#consume_amount_' + rowId).val(amount.toLocaleString('en-IN', {maximumFractionDigits: 2}));
                totalQty += qty;
                totalAmount += amount;
            }
        });
        $('#qty_total').text(totalQty.toLocaleString('en-IN'));
        $('#amount_total').text(totalAmount.toLocaleString('en-IN', {maximumFractionDigits: 2}));
    }

    // Price/Qty change events
    $(document).on('input', '.consume_price, .consume_qty', function() {
        let rowId = $(this).data('id');
        calculateRowAmount(rowId);
        calculateConsumeTotals();
    });

    // Add more consume row
    $(document).on('click', '.add_more_consume', function () {
        consumeRowCount++;

        let newRow = `
        <tr id="consume_tr_${consumeRowCount}" class="font-14 font-heading bg-white">
            <td class="w-min-50">${consumeRowCount}</td>
            <td>
                <div class="d-flex align-items-center desc-cell">
                    <select class="form-control consume_item select2-single"
                        name="consume_item[]" data-id="${consumeRowCount}"
                        id="consume_item_${consumeRowCount}">
                        ${consumeItemOptions}
                    </select>
                    <input type="hidden" name="item_size_info[]" id="item_size_info_${consumeRowCount}">
                </div>
            </td>
            <td>
                <input type="number" name="consume_qty[]" class="form-control consume_qty text-end"
                    id="consume_qty_${consumeRowCount}" data-id="${consumeRowCount}">
            </td>
            <td>
                <input type="text" class="form-control text-center"
                    id="consume_unit_tr_${consumeRowCount}" readonly>
            </td>
            <td>
                <input type="number" name="consume_price[]" class="form-control consume_price text-end"
                    id="consume_price_${consumeRowCount}" data-id="${consumeRowCount}">
            </td>
            <td>
                <input type="text" name="consume_amount[]" class="form-control consume_amount text-end"
                    id="consume_amount_${consumeRowCount}" readonly>
            </td>
            <td>
                <svg style="color: red;cursor: pointer;"
                     xmlns="http://www.w3.org/2000/svg"
                     width="16" height="16"
                     fill="currentColor"
                     class="bi bi-file-minus-fill remove_consume"
                     data-id="${consumeRowCount}"
                     viewBox="0 0 16 16">
                    <path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/>
                </svg>
            </td>
        </tr>`;

        $('#consume_tbody').append(newRow);
        $(".select2-single").select2();
    });

    // Remove consume row
    $(document).on("click", ".remove_consume", function() {
        let id = $(this).attr('data-id');
        $("#consume_tr_" + id).remove();
        calculateConsumeTotals();
    });

    // Add more generated item
    $(".add_more1").click(function() {
        let empty_status = 0;
        $('.generated_item').each(function(){
            let i = $(this).attr('data-id');
            if($(this).val()=="" || $("#generated_weight_"+i).val()=="" || $("#generated_price_"+i).val()==""){
                empty_status=1;
            }
        });
        if(empty_status==1){
            alert("Please enter required fields");
            return;
        }

        let srn = $("#generated_srn_"+add_more_count1).html();
        srn = parseInt(srn) + 1;
        add_more_count1++;
        var $curRow = $(".total").closest('tr');
        var optionElements = '@foreach($items ?? [] as $item)<option value="{{$item->id}}" data-unit_id="{{$item->u_name}}" data-unit_name="{{$item->unit}}">{{$item->name}}</option>@endforeach';

        let newRow = `<tr id="tr1_${add_more_count1}" class="font-14 font-heading bg-white">
            <td class="w-min-50" id="generated_srn_${add_more_count1}">${srn}</td>
            <td>
                <div class="d-flex align-items-center">
                    <select class="form-control generated_item select2-single" name="generated_item[]" data-id="${add_more_count1}" id="generated_item_${add_more_count1}">
                        <option value="">Select Item</option>
                        ${optionElements}
                    </select>
                    <input type="hidden" name="generated_size_info[]" id="generated_size_info_${add_more_count1}" data-id="${add_more_count1}">
                </div>
            </td>
            <td>
                <input type="number" name="generated_weight[]" class="form-control generated_weight" data-id="${add_more_count1}" id="generated_weight_${add_more_count1}" placeholder="Qty" style="text-align: right;">
            </td>
            <td>
                <input type="text" class="w-100 form-control generated_unit" id="generated_unit_tr_${add_more_count1}" readonly style="text-align:center;" data-id="${add_more_count1}" name="generated_unit_name[]"/>
                <input type="hidden" class="generated_units w-100" name="generated_units[]" id="generated_units_tr_${add_more_count1}"/>
            </td>
            <td><input type="number" name="generated_price[]" class="form-control generated_price" data-id="${add_more_count1}" id="generated_price_${add_more_count1}" placeholder="Price" style="text-align: right;"></td>
            <td><input type="text" name="generated_amount[]" class="form-control generated_amount" data-id="${add_more_count1}" id="generated_amount_${add_more_count1}" placeholder="Amount" readonly style="text-align: right;"></td>
            <td><svg style="color: red;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-minus-fill remove1" data-id="${add_more_count1}" viewBox="0 0 16 16"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1"/></svg></td>
        </tr>`;

        $curRow.before(newRow);
        $(".select2-single").select2();
        calculateGeneratedTotals();
    });

    $(document).on("click", ".remove1", function() {
        let id = $(this).attr('data-id');
        $("#tr1_" + id).remove();
        calculateGeneratedTotals();
    });

    function calculateGeneratedTotals() {
        let totalWeight = 0, totalAmount = 0;
        $('.generated_weight').each(function() {
            let weight = parseFloat($(this).val() || 0);
            let price = parseFloat($(this).closest('tr').find('.generated_price').val() || 0);
            let amount = weight * price;

            $(this).closest('tr').find('.generated_amount').val(amount.toLocaleString('en-IN', {maximumFractionDigits: 2}));
            totalWeight += weight;
            totalAmount += amount;
        });
        $('#generated_weight_total').text(totalWeight.toLocaleString('en-IN'));
        $('#generated_amount_total').text(totalAmount.toLocaleString('en-IN', {maximumFractionDigits: 2}));
    }

    $(document).on('input', '.generated_price, .generated_weight', function() {
        calculateGeneratedTotals();
    });

    // Initialize totals on page load
    calculateConsumeTotals();
    calculateGeneratedTotals();

    // Load pending items on page load
    if ($('#party_id').val()) {
        $('#party_id').trigger('change');
    }

    $('.savebtn').click(function(e) {
        e.preventDefault();
        if (confirm('Are you sure to update?')) {
            $('#frm').submit();
        }
    });
});
</script>
@endsection
