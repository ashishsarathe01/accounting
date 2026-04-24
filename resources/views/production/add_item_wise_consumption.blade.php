@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
    .get_info:hover {
        color: blue;
    }

.select2-container .select2-selection--single {
    height: 45px !important;
    padding-top: 7px;
    font-size: 15px;
}

.select2-container .select2-selection__arrow {
    height: 45px !important;
}

</style>


<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">

            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="container mt-3">

                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-plum-viloet text-white py-3">
                        <h5 class="m-0">Define Item-wise Consumption Rate</h5>
                    </div>

                    <div class="card-body">

                        <form action="{{ route('ConsumptionRate.store') }}" method="POST">
                            @csrf

                            <!-- =================== FIRST ROW (3 INPUTS) =================== -->
                            <div class="row g-3">

                                <!-- 1. Production Item -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Select Production Item</label>
                                    <select name="production_item_id" id="production_item_id" class="form-control shadow-sm select2-single" required autofocus>
                                        <option value="">-- Select --</option>
                                        @foreach($Production_items as $p)
                                            <option value="{{ $p->item_id }}">{{ $p->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- 2. Per KG -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Define consumption per (KG)</label>
                                    <input type="number" name="per_kg" id="per_kg" class="form-control shadow-sm"
                                        placeholder="Example: 1000" required>
                                </div>

                                <!-- 3. Variance -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Bearable Variance (%)</label>
                                    <input type="number" name="variance" id="variance" class="form-control shadow-sm"
                                        placeholder="Example: 5" required>
                                </div>

                            </div>

                            <hr class="my-4">

                            <!-- =================== MATERIALS SECTION =================== -->
                            <h5 class="fw-bold mb-3">Materials Required</h5>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped shadow-sm" id="itemTable">
                                    <thead class="bg-light text-center">
                                        <tr>
                                            <th width="40%">Item</th>
                                            <th width="40%">Qty Required</th>
                                            <th width="10%">
                                                
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <select name="material_item_id[]" id="material_item_id" class="form-control shadow-sm select2-single" required>
                                                    <option value="">-- Select Item --</option>
                                                    @foreach($manage_items as $m)
                                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="any" name="material_qty[]" id="material_qty" class="form-control shadow-sm"
                                                    required>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-success btn-sm addRow" id="addRow">
                                                    <strong>+</strong>
                                                </button>
                                               
                                                
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Save Button -->
                            <div class="text-end mt-3">
                                <button class="btn btn-primary px-4 py-2">
                                    Save
                                </button>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
<!-- Show/Hide Group Dropdown -->
<script>
$(document).ready(function () {

    // =============== FIX SELECT2 INIT ===============
    function initSelect2() {
        $('.select2-single').select2();
    }
    initSelect2();


    // =============== ADD ROW (your original code kept) ===============
    $(document).on('click', '.addRow', function () {
        let newRow = `
        <tr>
            <td>
                <select name="material_item_id[]" class="form-control shadow-sm select2-single material_item" required>
                    <option value="">-- Select Item --</option>
                    @foreach($manage_items as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" step="any" name="material_qty[]" class="form-control material_qty" required>
            </td>

            <td class="text-center">
                <button type="button" class="btn btn-success btn-sm addRow"><strong>+</strong></button>
                <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
            </td>
        </tr>
        `;

        $("#itemTable tbody").append(newRow);
        initSelect2();
    });

    // REMOVE ROW
    $(document).on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
    });




    // ==================================================================
    //  ✔ FIXED ENTER-KEY BEHAVIOR — WORKS FOR DYNAMIC ROWS
    // ==================================================================

    $(document).on('keydown', 'input, select, .select2-search__field', function (e) {

        if (e.key !== "Enter") return;
        e.preventDefault();

        let element = $(this);

        // If inside select2 search box → map back to select
        if (element.hasClass('select2-search__field')) {
            element = element.closest('.select2-container').prev('select');
        }

        // ========= TOP SECTION FOCUS MAP ==========
        if (element.attr('id') === 'production_item_id') {
            $('#per_kg').focus();
            return;
            
        }
        if (element.attr('id') === 'per_kg') {
            $('#variance').focus();
            return;
        }
        if (element.attr('id') === 'variance') {
            // Focus first row item dropdown
            $('.material_item').first().select2('open');
            return;
        }

        // ========= DYNAMIC ROW LOGIC ==========
        let row = element.closest('tr');

        if (element.hasClass('material_item')) {
            // Move to qty of same row
            row.find('.material_qty').focus();
            return;
        }

        if (element.hasClass('material_qty')) {
            // Move to addRow button
            row.find('.addRow').focus();
            return;
        }

        if (element.hasClass('addRow')) {
            // Add new row
            element.click();

            // Focus new row's item
            setTimeout(() => {
                $("#itemTable tbody tr:last .material_item").select2('open');
            }, 80);
            return;
        }

        $(document).on('keydown', 'input, select, .select2-search__field', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Stop form submit on Enter

                let currentId = $(this).attr('id');

                // Special case: if inside Select2 search box
                if ($(this).hasClass('select2-search__field')) {
                    currentId = $(this).closest('.select2-container').prev('select').attr('id');
                }

                const nextField = focusMap['#' + currentId];
                if (nextField) {
                    setTimeout(function() {
                        $(nextField).focus();
                    }, 100);
                }
            }
        });
          $('.select2-single').on('select2:close', function(e) {
            const currentId = $(this).attr('id');
            const nextField = focusMap['#' + currentId];
            if (nextField) {
                setTimeout(function() {
                    $(nextField).focus();
                }, 100);
            }
        });

    });


    // ==================================================================
    // ✔ SELECT2 close event for dynamic rows
    // ==================================================================
    $(document).on('select2:close', '.material_item', function () {
        let row = $(this).closest('tr');
        row.find('.material_qty').focus();
    });

    // ============ ENTER KEY HANDLING (STATIC + DYNAMIC) ============

$(document).on('keydown', 'input, select, .select2-search__field', function(e) {
    if (e.key !== 'Enter') return;

    e.preventDefault();

    let element = $(this);

    // If typing inside a Select2 search field → redirect to select element
    if (element.hasClass('select2-search__field')) {
        element = element.closest('.select2-container').prev('select');
    }

    let id = element.attr('id');

    // ===================== TOP SECTION LOGIC (your original) =====================
    if (id === 'production_item_id') { $('#per_kg').focus(); return; }
    if (id === 'per_kg') { $('#variance').focus(); return; }
    if (id === 'variance') {
        // go to first material_item (dynamic or static)
        $('.material_item:first').select2('open');
        return;
    }

    // ===================== DYNAMIC ROW LOGIC =====================
    // CASE 1: select2 "Item"
    if (element.hasClass('material_item')) {
        element.closest('tr').find('.material_qty').focus();
        return;
    }

    // CASE 2: Qty input
    if (element.hasClass('material_qty')) {
        element.closest('tr').find('.addRow').focus();
        return;
    }

    // CASE 3: AddRow button
    if (element.hasClass('addRow')) {
        element.click(); // add row
        setTimeout(() => {
            $("#itemTable tbody tr:last .material_item").select2('open');
        }, 50);
        return;
    }
});


// ============ SELECT2 CLOSE → MOVE TO NEXT FIELD (STATIC + DYNAMIC) ============
$(document).on('select2:close', function(e) {

    let select = $(e.target);

    // TOP SECTION
    if (select.attr('id') === 'production_item_id') { $('#per_kg').focus(); return; }
    if (select.attr('id') === 'material_item_id') { $('#material_qty').focus(); return; }

    // DYNAMIC ROWS
    if (select.hasClass('material_item')) {
        select.closest('tr').find('.material_qty').focus();
    }
});


});
</script>
@endsection
