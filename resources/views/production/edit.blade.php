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

                            {{-- ðŸ”¹ Deckle Information --}}
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">Deckle No.</label>
                                    <input type="text" name="deckle_no" class="form-control" value="{{ $deckle->deckle_no }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Start Time</label>
                                    <input type="text" class="form-control" name="start_time" placeholder="DD-MM-YYYY HH:MM:SS"
                                        value="{{ date('d-m-Y H:i:s', strtotime($deckle->start_time_stamp)) }}" data-old="{{ date('d-m-Y H:i:s', strtotime($deckle->start_time_stamp)) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">End Time</label>
                                    <input type="text" class="form-control" name="end_time" placeholder="DD-MM-YYYY HH:MM:SS" value="{{ date('d-m-Y H:i:s', strtotime($deckle->end_time_stamp)) }}" data-old="{{ date('d-m-Y H:i:s', strtotime($deckle->end_time_stamp)) }}">
                                    
                                </div>
                            </div>

                            {{-- ðŸ”¹ Quality Details --}}
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
                                                        <option value="{{ $item->id }}" {{ $q->item_id == $item->item_id ? 'selected' : '' }}>
                                                            {{ $item->name }}
                                                        </option>
                                                      

                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control bf-input" name="qualities[{{ $q->id }}][bf]" value="{{ $q->bf }}" readonly></td>
                                            <td><input type="text" class="form-control gsm-input" name="qualities[{{ $q->id }}][gsm]" value="{{ $q->gsm }}" ></td>
                                            <td><input type="number" class="form-control" name="qualities[{{ $q->id }}][speed]" value="{{ $q->speed }}" required></td>
                                            <td><input type="number" class="form-control" name="qualities[{{ $q->id }}][production_in_kg]" value="{{ $q->production_in_kg }}" required></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                                            </td>


                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
    
                            {{-- ðŸ”¹ Buttons --}}
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

{{-- âœ… jQuery Section --}}
<script>
$(document).ready(function() {

    // Refresh + and - buttons visibility
    function refreshActionButtons() {
        let rows = $('#qualityTable tbody tr');
        let rowCount = rows.length;

        rows.find('.addRowBtn').remove();

        rows.each(function(index) {
            let actionCell = $(this).find('td:last');
            actionCell.find('.removeRow').remove();

            if (rowCount === 1) {
                actionCell.html(`
                    <button type="button" class="btn btn-sm btn-success addRowBtn">+</button>
                `);
            } else {
                if (index === rowCount - 1) {
                    actionCell.html(`
                        <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                        <button type="button" class="btn btn-sm btn-success addRowBtn">+</button>
                    `);
                } else {
                    actionCell.html(`
                        <button type="button" class="btn btn-danger btn-sm removeRow">-</button>
                    `);
                }
            }
        });
    }

    // Add new row dynamically
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
                <td><input type="text" class="form-control bf-input" name="new_qualities[${index}][bf]" readonly placeholder="BF"></td>
                <td><input type="text" class="form-control gsm-input" name="new_qualities[${index}][gsm]" placeholder="GSM"></td>
                <td><input type="number" class="form-control text-end" name="new_qualities[${index}][speed]" required placeholder="Speed"></td>
                <td><input type="number" class="form-control text-end" name="new_qualities[${index}][production_in_kg]" required placeholder="Production (KG)"></td>
                <td class="text-center"></td>
            </tr>
        `;

        $('#qualityTable tbody').append(newRow);
        refreshActionButtons();
    });

    // Remove row
    $(document).on('click', '.removeRow', function() {
        $(this).closest('tr').remove();
        refreshActionButtons();
    });

    // Auto-fill BF & GSM on item change
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

    // âœ… Form validation before submit
    $('#editDeckleForm').on('submit', function(e) {
        let valid = true;
        let errorMsg = '';
        // let start = $('input[name="start_time"]').val().trim();
        // let end   = $('input[name="end_time"]').val().trim();

        // if (!isValidDateTime(start)) {
        //     alert("Invalid Start Time!");
        //     e.preventDefault();
        //     return;
        // }

        // if (!isValidDateTime(end)) {
        //     alert("Invalid End Time!");
        //     e.preventDefault();
        //     return;
        // }


        // let startDate = parseDateTime(start);
        // let endDate   = parseDateTime(end);

        // if (endDate < startDate) {
        //     alert("End Time cannot be less than Start Time!");
        //     e.preventDefault();
        //     return;
        // }
        $('#qualityTable tbody tr').each(function(index) {
            let item = $(this).find('.item-select').val();
            let gsm = $(this).find('.gsm-input').val();
            let speed = $(this).find('input[name*="[speed]"]').val();
            let prod = $(this).find('input[name*="[production_in_kg]"]').val();

            if (!item || !gsm || !speed || !prod) {
                valid = false;
                errorMsg = `Please fill all fields in row ${index + 1}.`;
                $(this).addClass('table-danger');
            } else {
                $(this).removeClass('table-danger');
            }
        });

        if (!valid) {
            e.preventDefault();
            alert(errorMsg);
        }
    });

    // Initialize buttons on load
    refreshActionButtons();
    // $('input[name="start_time"]').on('change', function () {
    //     let val = $(this).val().trim();
    //     let old = $(this).data('old');

    //     let end = $('input[name="end_time"]').val().trim();

    //     if (!isValidDateTime(val)) {
    //         alert("Invalid Start Time!");
    //         $(this).val(old);
    //         return;
    //     }

    //     let startDate = parseDateTime(val);
    //     let endDate   = parseDateTime(end);

    //     if (endDate < startDate) {
    //         alert("Start Time cannot be greater than End Time!");
    //         $(this).val(old);
    //         return;
    //     }

    //     $(this).data('old', val);
    // });

    // $('input[name="end_time"]').on('change', function () {
    //     let val = $(this).val().trim();
    //     let old = $(this).data('old');

    //     let start = $('input[name="start_time"]').val().trim();
    //     if (!isValidDateTime(val)) {
    //         alert("Invalid End Time!");
    //         $(this).val(old);
    //         return;
    //     }

    //     let startDate = parseDateTime(start);
    //     let endDate   = parseDateTime(val);

    //     if (endDate < startDate) {
    //         alert("End Time cannot be less than Start Time!");
    //         $(this).val(old);
    //         return;
    //     }

    //     $(this).data('old', val);
    // });
    
    // function isValidDateTime(dt) {
    //     let regex = /^(\d{2})-(\d{2})-(20\d{2}) (\d{2}):(\d{2}):(\d{2})$/;
    //     let match = dt.match(regex);

    //     if (!match) return false;

    //     let [_, d, m, y, h, i, s] = match;

    //     d = parseInt(d);
    //     m = parseInt(m);
    //     y = parseInt(y);
    //     h = parseInt(h);
    //     i = parseInt(i);
    //     s = parseInt(s);

    //     if (m < 1 || m > 12) return false;
    //     if (h > 23 || i > 59 || s > 59) return false;

    //     let date = new Date(y, m - 1, d);

    //     if (
    //         date.getFullYear() !== y ||
    //         date.getMonth() !== m - 1 ||
    //         date.getDate() !== d
    //     ) {
    //         return false;
    //     }

    //     return true;
    // }  
});
</script>



@endsection