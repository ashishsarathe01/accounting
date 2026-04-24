@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

                {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                {{-- Breadcrumb --}}
                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Production</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg')}}" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Edit Set Item</li>
                    </ol>
                </nav>

                {{-- Edit Item Form --}}
                <div class="bg-white p-4 shadow-sm border-radius-8">

                    <form id="editItemForm" action="{{ route('production.set_item.update', $item->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Item *</label>
                                <select name="item_id" class="form-select select2-single" required>
                                  <option value="{{ $production->id }}" >
                                                        {{ $production->name }}
                                                    </option>     
                                </select>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label>BF *</label>
                                <input type="number" name="bf" class="form-control" required value="{{ $production->bf }}">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label>GSM *</label>
                                <input type="number" name="gsm" class="form-control" required value="{{ $production->gsm }}">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label>Speed</label>
                                <input type="number" name="speed" class="form-control" value="{{ $production->speed }}">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="1" {{ $production->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $production->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <hr>

                        {{-- SERIES OPENING --}}
                        <h6 class="fw-bold mt-3">Series Opening Details</h6>
                        <table class="table table-bordered" id="seriesTable">
                            <thead>
                                <tr>
                                    <th>Series</th>
                                    <th>Opening Qty</th>
                                    <th>Opening Amount</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($series as $i => $s)
                                    <tr>
                                        <td>
                                            {{ $s->series }}
                                            <input type="hidden" name="series_inputs[{{ $i }}][series]" value="{{ $s->series }}">
                                        </td>
                                        <td>
                                            <input type="number" step="any" min="0" class="form-control series_qty"
                                                   name="series_inputs[{{ $i }}][qty]" value="{{ $s->opening_quantity }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="series_inputs[{{ $i }}][amt]" value="{{ $s->opening_amount }}">
                                        </td>
                                        <td>
                                            <select class="form-control" name="series_inputs[{{ $i }}][type]">
                                                <option value="">Select</option>
                                                <option value="Debit" {{ ($s->type ?? '') == 'Debit' ? 'selected' : '' }}>Debit</option>
                                                <option value="Credit" {{ ($s->type ?? '') == 'Credit' ? 'selected' : '' }}>Credit</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <hr>

                        {{-- REELS LIST (deckle_id = 0) --}}
                        <h6 class="fw-bold mt-3">Reels</h6>
                        <table class="table table-striped table-bordered" id="reelsTable">
                            <thead>
                                <tr>
                                    <th>S.no.</th>
                                    <th>Reel No</th>
                                    <th>Size</th>
                                    <th>BF</th>
                                    <th>GSM</th>
                                    <th>Weight</th>
                                    <th>Unit</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $total_weight = 0; 
                                $i=0;
                                @endphp
                                
                                @foreach($reels as $rIndex => $r)
                                @php 
                                $total_weight += $r->weight;
                                @endphp
                                
                                <tr data-row-index="{{ $rIndex }}">
                                    <input type="hidden" name="reels[{{ $rIndex }}][id]" value="{{ $r->id }}">
                                    <td>{{ ++$i }}</td>

                                    <td>
                                        <input type="text" name="reels[{{ $rIndex }}][reel_no]" value="{{ $r->reel_no }}" class="form-control"
                                               @if($r->status != 1) disabled @endif>
                                    </td>

                                    <td>
                                        <input type="text" name="reels[{{ $rIndex }}][size]" value="{{ $r->size }}" class="form-control"
                                               @if($r->status != 1) disabled @endif>
                                    </td>

                                    <td>
                                        <input type="text" name="reels[{{ $rIndex }}][bf]" value="{{ $r->bf }}" class="form-control"
                                               @if($r->status != 1) disabled @endif>
                                    </td>

                                    <td>
                                        <input type="text" name="reels[{{ $rIndex }}][gsm]" value="{{ $r->gsm }}" class="form-control"
                                               @if($r->status != 1) disabled @endif>
                                    </td>

                                    <td>
                                        <input type="number" name="reels[{{ $rIndex }}][weight]" step="any" class="form-control reel_weight"
                                               value="{{ $r->weight }}" @if($r->status != 1) disabled @endif>
                                    </td>

                                    <td>
                                        <input type="text" name="reels[{{ $rIndex }}][unit]" value="{{ $r->unit }}" class="form-control"
                                               @if($r->status != 1) disabled @endif>
                                    </td>

                                    <td>
                                        @if($r->status == 1)
                                            <button type="button" class="btn btn-danger btn-sm delete-existing-reel">Delete</button>
                                        @else
                                            <a href="{{ url('sale-invoice/' . $r->sale_id) }}" class="btn btn-success btn-sm">Sold</a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                 <tr>
                                    <td class="fw-bold" colspan="5">Total</td>
                                    <td class="fw-bold" >{{$total_weight}}</td>
                                    <td></td>

                                </tr>
                            </tbody>
                        </table>

                        <hr>

                        {{-- Add new reels area --}}
                        <h6 class="fw-bold mt-3">Add New Reels</h6>
                        <table class="table table-bordered" id="newReelsTable">
                            <thead>
                                <tr>
                                    <th>Reel No</th>
                                    <th>Size</th>
                                    <th>BF</th>
                                    <th>GSM</th>
                                    <th>Weight</th>
                                    <th>Unit</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                        <button type="button" id="addNewReelRow" class="btn btn-sm btn-outline-primary">Add Reel</button>

                        <div class="text-end mt-3">
                            <button id="submitBtn" type="submit" class="btn btn-primary">Update Item</button>
                            <a href="{{ route('production.set_item') }}" class="btn btn-dark ms-2">Quit</a>
                        </div>

                        {{-- hidden input to send deleted reel ids --}}
                        <input type="hidden" name="deleted_reels" id="deleted_reels" value="">

                    </form>
                </div>

            </div>
        </div>
    </section>
</div>

{{-- Confirmation modal for opening weight mismatch --}}
<div class="modal fade" id="weightMismatchModal" tabindex="-1" aria-labelledby="weightMismatchModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><strong>Opening Qty and Total Reel Weight Mismatch</strong></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="mismatchText"></p>
        <p>Would you like to <strong>update opening quantity</strong> so it equals the total reel weight?</p>
      </div>
      <div class="modal-footer">
        <button type="button" id="modalCancel" class="btn btn-secondary" data-bs-dismiss="modal">No — I'll fix</button>
        <button type="button" id="modalUpdateOpening" class="btn btn-primary">Yes — Update Opening Qty & Submit</button>
      </div>
    </div>
  </div>
</div>

@include('layouts.footer')

{{-- JS: uses jQuery + Bootstrap (assumes included in layout) --}}
<script>
$(document).ready(function(){

    // collect ids of deleted reels
    var deletedIds = [];

    // delete-existing-reel button handler
    $(document).on('click', '.delete-existing-reel', function(){
        var $tr = $(this).closest('tr');
        var id = $tr.find('input[name^="reels"][name$="[id]"]').val() || $tr.data('id') || $tr.find('td:first').text();
        if (!id) {
            $tr.remove();
            return;
        }
        // mark for deletion and remove from DOM
        deletedIds.push(id);
        $('#deleted_reels').val(JSON.stringify(deletedIds));
        $tr.remove();
    });

    // Add new reel row
    $('#addNewReelRow').on('click', function(){
        var idx = $('#newReelsTable tbody tr').length;
        var html = '<tr>' +
            '<td><input type="text" name="new_reels['+idx+'][reel_no]" class="form-control"></td>' +
            '<td><input type="text" name="new_reels['+idx+'][size]" class="form-control"></td>' +
            '<td><input type="text" name="new_reels['+idx+'][bf]" class="form-control"></td>' +
            '<td><input type="text" name="new_reels['+idx+'][gsm]" class="form-control"></td>' +
            '<td><input type="number" step="any" name="new_reels['+idx+'][weight]" class="form-control new_reel_weight"></td>' +
            '<td><input type="text" name="new_reels['+idx+'][unit]" class="form-control"></td>' +
            '<td><button type="button" class="btn btn-danger btn-sm remove-new-reel">Remove</button></td>' +
            '</tr>';
        $('#newReelsTable tbody').append(html);
    });

    // remove new reel
    $(document).on('click', '.remove-new-reel', function(){
        $(this).closest('tr').remove();
    });

    // helper: sum series qty
    function sumSeriesQty(){
        var total = 0;
        $('.series_qty').each(function(){
            var v = parseFloat($(this).val());
            if (!isNaN(v)) total += v;
        });
        return total;
    }

    // helper: sum reels weights (existing editable ones + new ones)
    function sumReelWeights(){
        var total = 0;
        // existing editable reels
        $('.reel_weight').each(function(){
            
            var v = parseFloat($(this).val());
            if (!isNaN(v)) total += v;
        });
        // new reels
        $('.new_reel_weight').each(function(){
            var v = parseFloat($(this).val());
            if (!isNaN(v)) total += v;
        });
        return total;
    }

    // on submit: check mismatch
    $('#editItemForm').on('submit', function(e){
        // compute totals
        var seriesTotal = sumSeriesQty();
        var reelsTotal = sumReelWeights();

        // consider small floating rounding: use toFixed(4)
        var sTotal = parseFloat(seriesTotal.toFixed(6));
        var rTotal = parseFloat(reelsTotal.toFixed(6));

        if (sTotal !== rTotal) {
            e.preventDefault();
            // show modal
            $('#mismatchText').html('Opening total quantity = <strong>' + sTotal + '</strong><br>' +
                                    'Total reel weight = <strong>' + rTotal + '</strong>');
            $('#weightMismatchModal').modal('show');

            // when user chooses to update opening qty and submit
            $('#modalUpdateOpening').off('click').on('click', function(){
                // Update opening qty to equal reelsTotal by adjusting first non-empty series row.
                var diff = rTotal - sTotal;
                var adjusted = false;
                $('.series_qty').each(function(){
                    var prev = parseFloat($(this).val()) || 0;
                    // find first series input (non-empty) to adjust
                    if (!adjusted) {
                        var newv = prev + diff;
                        if (newv < 0) newv = 0; // avoid negative
                        $(this).val(parseFloat(newv.toFixed(6)));
                        adjusted = true;
                    }
                });
                // if none found (no series rows), create one hidden series input with name series_inputs[0][series] = 'NA'
                if (!adjusted) {
                    var idx = $('#seriesTable tbody tr').length;
                    var html = '<tr>' +
                           '<td>NA<input type="hidden" name="series_inputs['+idx+'][series]" value="NA"></td>' +
                           '<td><input type="number" step="any" min="0" class="form-control series_qty" name="series_inputs['+idx+'][qty]" value="'+ rTotal +'"></td>' +
                           '<td><input type="text" class="form-control" name="series_inputs['+idx+'][amt]" value=""></td>' +
                           '<td><select class="form-control" name="series_inputs['+idx+'][type]"><option value="">Select</option><option value="Debit">Debit</option><option value="Credit">Credit</option></select></td>' +
                           '</tr>';
                    $('#seriesTable tbody').append(html);
                }
                $('#weightMismatchModal').modal('hide');
                // allow submit now (re-run validation quickly)
                setTimeout(function(){ $('#editItemForm').submit(); }, 150);
            });

            // if user cancels (modalCancel) do nothing — let them continue editing
            return false;
        }

        // if equal, allow submit
        return true;
    });

    // close modal handler: nothing (user will continue editing)
    $('#modalCancel').on('click', function(){
        $('#weightMismatchModal').modal('hide');
    });

}); // ready
</script>

@endsection
