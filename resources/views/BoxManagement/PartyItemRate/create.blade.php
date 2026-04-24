@extends('layouts.app')
<style>

/* GLOBAL */
body {
    font-size: 15.5px;
    background: #f4f6f9;
}

/* CARD / CONTAINER */
.bg-white {
    border-radius: 12px;
    border: 1px solid #e3e6ea;
}

/* HEADER */
.table-title-bottom-line {
    background: linear-gradient(135deg, #5e60ce, #6930c3);
    border-radius: 12px;
}
.table-title-bottom-line h5 {
    font-size: 20px;
    font-weight: 600;
}

/* SELECT2 */
.select2-container--default .select2-selection--single {
    height: 40px !important;
    border-radius: 8px !important;
    border: 1px solid #ced4da !important;
}
.select2-container--default .select2-selection__rendered {
    line-height: 38px !important;
    font-size: 14.5px;
}
.select2-container {
    width: 100% !important;
}

/* INPUT */
.form-control {
    height: 40px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    font-size: 14.5px;
}
.form-control:focus {
    border-color: #5e60ce;
    box-shadow: 0 0 0 0.1rem rgba(94,96,206,0.2);
}

/* LABEL */
label {
    font-weight: 500;
    margin-bottom: 6px;
    color: #444;
}

/* TABLE */
#itemTable {
    font-size: 14.5px;
    border-radius: 10px;
    overflow: hidden;
}

#itemTable thead {
    background-color: #eef3f7;
    color: #34495e;
    border-bottom: 2px solid #dce3ea;
}

#itemTable th {
    padding: 13px;
    font-weight: 600;
}

#itemTable td {
    padding: 11px;
    vertical-align: middle;
}

/* ROW HOVER */
#itemTable tbody tr:hover {
    background: #f8fbff;
    transition: 0.2s;
}

/* BUTTONS */
.btn {
    border-radius: 6px;
    font-size: 13.5px;
}

/* ADD BUTTON */
.btn-success {
    background-color: #38b000;
    border: none;
}
.btn-success:hover {
    background-color: #2d9200;
}

/* REMOVE BUTTON */
.btn-danger {
    background-color: #e63946;
    border: none;
}
.btn-danger:hover {
    background-color: #c82333;
}

/* SUBMIT BUTTON */
.btn-primary {
    background: #5e60ce;
    border: none;
    padding: 8px 18px;
}
.btn-primary:hover {
    background: #4c4fd1;
}

/* ALERT */
.alert {
    border-radius: 8px;
    font-size: 14px;
}

/* SMALL SHADOW IMPROVEMENT */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
}

</style>
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    <div class="table-title-bottom-line d-flex justify-content-between align-items-center shadow-sm px-4 py-3"
     style="background: linear-gradient(135deg, #6f42c1, #5a2ea6); border-radius: 10px;">

    <div class="d-flex align-items-center gap-3">
        
        <!-- ICON -->
        <div style="
            background: rgba(255,255,255,0.15);
            padding: 10px;
            border-radius: 10px;
            font-size: 20px;
            color: #fff;">
            <i class="fa fa-list"></i>
        </div>

        <!-- TITLE -->
        <h5 class="m-0 text-white" style="
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;">
            Set Party Item Rates
        </h5>
    </div>

    <!-- OPTIONAL RIGHT SIDE (can remove if not needed) -->
    <div>
        <span style="
            font-size: 13px;
            color: #ddd;">
            Manage Rates Easily
        </span>
    </div>

</div>

    <div class="bg-white p-4 shadow-sm">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('party-item-rate.store') }}">
            @csrf

            <!-- Party -->
            <div class="mb-3">
                <label><b>Select Party</b></label>
                <select name="party_id" class="form-control select2" required>
                    <option value="">Select Party</option>
                    @foreach($party_list as $party)
                        <option value="{{ $party->id }}">{{ $party->account_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Table -->
            <table class="table table-bordered" id="itemTable">
                <thead style="
    background-color:#eef5f9;
    font-size:15px;
    color:#2c3e50;
    border-bottom:2px solid #d6e4ec;
">
    <tr>
        <th width="40%" style="padding:13px; font-weight:600;">Item</th>
        <th width="30%" style="padding:13px; font-weight:600;">Rate</th>
        <th width="20%" style="padding:13px; font-weight:600;" class="text-center">Action</th>
    </tr>
</thead>
                <tbody>

                    <tr>
                        <td>
                            <select name="items[0][item_id]" class="form-control select2" required>
                                <option value="">Select Item</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </td>

                        <td>
                            <input type="number" step="0.01" name="items[0][price]" class="form-control" required>
                        </td>

                        <td>
                            <button type="button" class="btn btn-success addRow">+</button>
                            <button type="button" class="btn btn-danger removeRow">X</button>
                        </td>
                    </tr>

                </tbody>
            </table>

            <button class="btn btn-primary">Submit</button>

        </form>

    </div>
</div>
</div>
</section>
</div>

@include('layouts.footer')

<!-- ✅ REQUIRED SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SELECT2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {

    let rowIndex = 1;

    // init select2
    $('.select2').select2();

    // ADD ROW
    $(document).on('click', '.addRow', function () {

        let row = `
        <tr>
            <td>
                <select name="items[${rowIndex}][item_id]" class="form-control select2" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control" required>
            </td>

            <td>
                <button type="button" class="btn btn-success addRow">+</button>
                <button type="button" class="btn btn-danger removeRow">X</button>
            </td>
        </tr>
        `;

        $('#itemTable tbody').append(row);

        // re-init select2 ONLY for new row
        $('#itemTable tbody tr:last .select2').select2();

        rowIndex++;
    });

    // REMOVE ROW
    $(document).on('click', '.removeRow', function () {
        if ($('#itemTable tbody tr').length > 1) {
            $(this).closest('tr').remove();
        }
    });

    // PREVENT DUPLICATE ITEM
    $(document).on('change', 'select[name*="[item_id]"]', function () {

        let selected = [];

        $('select[name*="[item_id]"]').each(function () {
            let val = $(this).val();

            if (val) {
                if (selected.includes(val)) {
                    alert('Item already selected!');
                    $(this).val('').trigger('change');
                }
                selected.push(val);
            }
        });

    });

});
</script>

@endsection