@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 col-lg-10 bg-mint">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-plum-viloet text-white d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Edit Pop Roll - Deckle No. {{ $deckle->deckle_no }}</h5>
                        <a href="{{ route('deckle-process.index') }}" class="btn btn-light btn-sm">Back to List</a>
                    </div>

                    <div class="card-body bg-white">
                        <form action="{{ route('deckle-process.update', $deckle->id) }}" method="POST" id="editDeckleForm">
                            @csrf
                            @method('PUT')

                            {{-- 🔹 Deckle Information --}}
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Deckle No.</label>
                                    <input type="text" name="deckle_no" class="form-control" value="{{ $deckle->deckle_no }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Start Time</label>
                                    <input type="text" class="form-control" name="start_time"
                                        value="{{ date('d-m-Y H:i:s', strtotime($deckle->start_time_stamp)) }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">End Time</label>
                                    <input type="text" class="form-control" name="end_time"
                                        value="{{ date('d-m-Y H:i:s', strtotime($deckle->end_time_stamp)) }}" readonly>
                                </div>
                            </div>

                            {{-- 🔹 Quality Details --}}
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mt-2">Quality Details</h5>
                               
                            </div>

                            <table class="table table-bordered" id="qualityTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>BF</th>
                                        <th>GSM</th>
                                        <th>Speed</th>
                                        <th>Production (KG)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deckle->quality as $q)
                                        <tr>
                                            <td>
                                                <select name="qualities[{{ $q->id }}][item_id]" class="form-select item-select" required>
                                                    @foreach($items as $item)
                                                        <option value="{{ $item->item_id }}" {{ $q->item_id == $item->item_id ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                      

                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control bf-input" name="qualities[{{ $q->id }}][bf]" value="{{ $q->bf }}" readonly></td>
                                            <td><input type="text" class="form-control gsm-input" name="qualities[{{ $q->id }}][gsm]" value="{{ $q->gsm }}" readonly></td>
                                            <td><input type="number" class="form-control" name="qualities[{{ $q->id }}][speed]" value="{{ $q->speed }}" required></td>
                                            <td><input type="number" class="form-control" name="qualities[{{ $q->id }}][production_in_kg]" value="{{ $q->production_in_kg }}" required></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                                            </td>


                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
    
                            {{-- 🔹 Buttons --}}
                            <div class="text-end mt-3">
                                <button type="submit" class="btn btn-success">Update</button>
                                <a href="{{ route('deckle-process.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

{{-- ✅ jQuery Section --}}
<script>
$(document).ready(function() {

    // 🔹 Function to refresh + and - buttons visibility
    function refreshActionButtons() {
        let rows = $('#qualityTable tbody tr');
        let rowCount = rows.length;

        // Remove all + buttons first
        rows.find('.addRowBtn').remove();

        rows.each(function(index) {
            let actionCell = $(this).find('td:last');

            // Always remove old remove buttons to reset
            actionCell.find('.removeRow').remove();

            if (rowCount === 1) {
                // ✅ Only one row: show only +
                actionCell.html(`
                    <button type="button" class="btn btn-sm btn-success addRowBtn">+</button>
                `);
            } else {
                // ✅ More than one row
                if (index === rowCount - 1) {
                    // Last row → show both + and -
                    actionCell.html(`
                        <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                        <button type="button" class="btn btn-sm btn-success addRowBtn">+</button>
                    `);
                } else {
                    // Other rows → only -
                    actionCell.html(`
                        <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                    `);
                }
            }
        });
    }

    // 🔹 Add new row dynamically
    $(document).on('click', '.addRowBtn', function() {
        let index = $('#qualityTable tbody tr').length + 1;

        let newRow = `
            <tr>
                <td>
                    <select name="new_qualities[${index}][item_id]" class="form-select item-select" required>
                        <option value="">-- Select Item --</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" class="form-control bf-input" name="new_qualities[${index}][bf]" readonly></td>
                <td><input type="text" class="form-control gsm-input" name="new_qualities[${index}][gsm]" readonly></td>
                <td><input type="number" class="form-control text-end" name="new_qualities[${index}][speed]" required></td>
                <td><input type="number" class="form-control text-end" name="new_qualities[${index}][production_in_kg]" required></td>
                <td class="text-center"></td>
            </tr>
        `;

        $('#qualityTable tbody').append(newRow);
        refreshActionButtons(); // ✅ Update buttons visibility
    });

    // 🔹 Remove row (but only if more than one)
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        refreshActionButtons(); // ✅ Update buttons visibility
    });

    // 🔹 Auto-fill BF & GSM on item change
    $(document).on('change', '.item-select', function() {
        let row = $(this).closest('tr');
        let item_id = $(this).val();

        if (item_id) {
            $.ajax({
                url: '{{ url("get-item-details") }}/' + item_id,
                type: 'GET',
                success: function(data) {
                    row.find('.bf-input').val(data.bf);
                    row.find('.gsm-input').val(data.gsm);
                }
            });
        } else {
            row.find('.bf-input, .gsm-input').val('');
        }
    });

    // ✅ Initialize buttons on load
    refreshActionButtons();
});
</script>


@endsection