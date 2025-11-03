@extends('layouts.app')
@section('content')
@include('layouts.header')
<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                {{-- ✅ Success/Error --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <p class="mb-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                <div class="d-flex justify-content-between align-items-center bg-plum-viloet shadow-sm py-2 px-4 mb-3">
                    <h5 class="m-0">Add Reels to Stock</h5>
                </div>
                {{-- ✅ Form --}}
                <form id="addStockForm" action="{{ route('stock.store') }}" method="POST">
                    @csrf
                    <div id="reelContainer">
                        <div class="reel-group border rounded p-3 mb-3 position-relative bg-white">
                            <button type="button" class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2 remove-reel py-0 px-2" style="display:none;">×</button>                            
                            <div class="row">
                                <div class="col-md-2">
                                    <label>Item Name</label>
                                    <select name="reels[0][item_id]" class="form-select item-select" data-type="item" required>
                                        <option value="">-- Select Item --</option>
                                        @foreach($items as $item)
                                            <option value="{{ $item->item_id }}" data-bf="{{ $item->bf }}" data-gsm="{{ $item->gsm }}" data-opening_quantity="{{ $item->opening_quantity }}">
                                                {{ $item->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label>BF</label>
                                    <input type="text" name="reels[0][bf]" class="form-control bf" readonly data-type="bf">
                                </div>

                                <div class="col-md-1">
                                    <label>GSM</label>
                                    <input type="text" name="reels[0][gsm]" class="form-control gsm" readonly data-type="gsm">
                                </div>
                                <div class="col-md-2">
                                    <label>Unit</label>
                                    <select name="reels[0][unit]" class="form-select" data-type="unit" required>
                                        <option value="">Select</option>
                                        <option value="INCH">INCH</option>
                                        <option value="CM">CM</option>
                                        <option value="MM">MM</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Reel No</label>
                                    <input type="number" name="reels[0][reel_no]" class="form-control reel-no" required placeholder="Enter Reel No.">
                                    <small class="text-danger reel-error d-none">Reel number already exists!</small>
                                </div>

                            
                                <div class="col-md-2">
                                    <label>Size</label>
                                    <input type="text" name="reels[0][size]" class="form-control" required placeholder="Enter Size">
                                </div>
                                <div class="col-md-2">
                                    <label>Weight</label>
                                    <input type="number" name="reels[0][weight]" class="form-control weight" required placeholder="Enter Weight">
                                </div>
                            </div>
                            <div class="row mb-2">
                            
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 text-end">
                        <strong>Total Weight :  <span id="totalWeight">0</span></strong>
                    </div>
                    <div class="mb-3 text-end">
                        <button type="button" id="addReelBtn" class="btn btn-primary btn-sm">+ Add Another Reel</button>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-success" >Submit All Reels</button>
                        <a href="{{ route('deckle-process.manage-stock') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

{{-- ✅ jQuery --}}
<script>
$(document).ready(function() {
    let reelIndex = 0;
    let usedReels = [];

    // 🔹 Auto-fill BF & GSM when item changes
    $(document).on('change', '.item-select', function() {
        const bf = $(this).find(':selected').data('bf');
        const gsm = $(this).find(':selected').data('gsm');
        const group = $(this).closest('.reel-group');
        group.find('.bf').val(bf || '');
        group.find('.gsm').val(gsm || '');
    });

   // 🔹 Append "X GSM" only after user finishes typing (on blur)
$(document).on('blur', '.reel-group .form-control[name*="[size]"]', function() {
    const group = $(this).closest('.reel-group');
    const gsmValue = group.find('.gsm').val();
    let sizeVal = $(this).val().trim();

    // Remove any previous "X something" if present
    sizeVal = sizeVal.replace(/\s*x\s*\d*$/i, '');

    if (gsmValue && sizeVal !== '') {
        $(this).val(sizeVal + 'X' + gsmValue);
    }
});

// 🔹 Also allow pressing Enter to trigger it
$(document).on('keypress', '.reel-group .form-control[name*="[size]"]', function(e) {
    if (e.which === 13) { // Enter key
        e.preventDefault();
        $(this).blur(); // trigger blur handler
    }
});


    // 🔹 Prevent duplicate reel numbers within page + check DB uniqueness
    $(document).on('keyup change', '.reel-no', function() {
        const reelInput = $(this);
        const reelNo = reelInput.val().trim();
        const errorText = reelInput.siblings('.reel-error');
        const allReels = $('.reel-no').map(function() { return $(this).val(); }).get();

        // Client-side duplicate check
        const duplicates = allReels.filter((v, i, a) => v && a.indexOf(v) !== i);
        if (duplicates.includes(reelNo)) {
            errorText.text('Duplicate reel number on this page!').removeClass('d-none');
            reelInput.addClass('is-invalid');
            return;
        }

        // Server-side uniqueness check
        if (reelNo) {
            $.get('{{ route("stock.checkReel") }}', { reel_no: reelNo }, function(response) {
                if (response.exists) {
                    errorText.text('Reel number already exists in database!').removeClass('d-none');
                    reelInput.addClass('is-invalid');
                } else {
                    errorText.addClass('d-none');
                    reelInput.removeClass('is-invalid');
                }
            });
        }
    });

    // 🔹 Add new reel block
    $('#addReelBtn').on('click', function() {
        reelIndex++;
        const newGroup = $('.reel-group:first').clone();
        let lastItem = $('select[data-type="item"]').first().find('option:selected').val();
        let lastItemText = $('select[data-type="item"]').first().find('option:selected').text().trim();
        let lastItemBf = $('select[data-type="item"]').first().find('option:selected').attr('data-bf');
        let lastItemGsm = $('select[data-type="item"]').first().find('option:selected').attr('data-gsm');
        let lastUnit = $('select[data-type="unit"]').first().find('option:selected').val();
        newGroup.find('input, select').each(function() {
            const name = $(this).attr('name');
            const type = $(this).attr('data-type');
            if (name) {
                $(this).attr('name', name.replace(/\[\d+\]/, `[${reelIndex}]`));
            }
            if ($(this).data('type') === 'item') {
                $(this).html("<option value='"+lastItem+"' selected data-bf="+lastItemBf+" data-gsm="+lastItemGsm+">"+lastItemText+"</option>");
            }else if ($(this).data('type') === 'unit') {
                $(this).val(lastUnit);
            } else {
                $(this).val('');         // clear other inputs
            }
        });

        newGroup.find('.reel-error').addClass('d-none');
        newGroup.find('.remove-reel').show();

        $('#reelContainer').append(newGroup);
        let newSelect = newGroup.find('select[data-type="item"]');
    newSelect.trigger('change');

    // lock dropdown
    newSelect.prop('readonly', true);  
    });

    // 🔹 Remove reel block
    $(document).on('click', '.remove-reel', function() {
        $(this).closest('.reel-group').remove();
    });

    // 🔹 Prevent submission if invalid reel numbers exist
    $('#addStockForm').on('submit', function(e) {
        if ($('.is-invalid').length > 0) {
            alert('Please fix errors before submitting.');
            e.preventDefault();
        }
    });
});
$(document).on('input', '.weight', function() {
    let totalWeight = 0;
    $('.weight').each(function() {
        const weight = parseFloat($(this).val());
        if (!isNaN(weight)) {
            totalWeight += weight;
        }
    });
    $('#totalWeight').text(totalWeight.toFixed(2));
});
$('#addStockForm').on('submit', function(e) {
    e.preventDefault();
    let itemId = $('select[data-type="item"]').val();
    let total_weight = $('#totalWeight').text();
    
    //$('#addStockForm').off('submit').submit();
    $.ajax({
        url: '{{ url("validate-stock-weight") }}',
        type: 'POST',
        data: { item_weight: total_weight, item_id: itemId, _token: '{{ csrf_token() }}' },
        success: function(res) {
            if(res.status==true) {
                $('#addStockForm').off('submit').submit();
            } else {
                alert('Total weight not match with item opening weight.');
            }
            // if(res.exists) {

        }
    });
});

</script>


@endsection

