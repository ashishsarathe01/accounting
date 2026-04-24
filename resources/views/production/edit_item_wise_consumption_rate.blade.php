@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
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
                    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                @endif

                <div class="container mt-3">

                    <div class="card shadow-sm border-0 rounded-3">
                        <div class="card-header bg-plum-viloet text-white py-3">
                            <h5 class="m-0">Edit Item-wise Consumption Rate</h5>
                        </div>

                        <div class="card-body">

                            <form action="{{ route('consumption_rate.update', $editData->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- =================== FIRST ROW (3 INPUTS) =================== -->
                                <div class="row g-3">

                                    <!-- Production Item -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Production Item</label>
                                        <select class="form-control shadow-sm select2-single" disabled>
                                            @foreach($Production_items as $p)
                                                <option value="{{ $p->item_id }}" {{ $p->item_id == $editData->item_id ? 'selected' : '' }}>
                                                    {{ $p->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Per KG -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Define consumption per (KG)</label>
                                        <input type="number" name="per_kg" class="form-control shadow-sm"
                                            value="{{ $editData->per_kg }}" required>
                                    </div>

                                    <!-- Variance -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Bearable Variance (%)</label>
                                        <input type="number" name="variance" class="form-control shadow-sm"
                                            value="{{ $editData->variance_rate }}" required>
                                    </div>

                                </div>

                                <hr class="my-4">

                                <!-- =================== MATERIAL TABLE =================== -->
                                <h5 class="fw-bold mb-3">Materials Required</h5>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped shadow-sm" id="itemTable">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                <th width="40%">Item</th>
                                                <th width="40%">Qty Required</th>
                                                <th width="10%">
                                                    <button type="button" class="btn btn-success btn-sm" id="addRow"><strong>+</strong></button>
                                                </th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($editMaterials as $mat)
                                            <tr>
                                                <td>
                                                    <select name="material_item_id[]" class="form-control shadow-sm select2-single" required>
                                                        <option value="">-- Select Item --</option>
                                                        @foreach($manage_items as $m)
                                                            <option value="{{ $m->id }}" {{ $m->id == $mat->item_id ? 'selected' : '' }}>
                                                                {{ $m->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>

                                                <td>
                                                    <input type="number" step="any" name="material_qty[]" class="form-control shadow-sm"
                                                        value="{{ $mat->consumption_rate }}" required>
                                                </td>

                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Save Button -->
                                <div class="text-end mt-3">
                                    <button class="btn btn-primary px-4 py-2">Update</button>
                                </div>

                            </form>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
$(document).ready(function () {

    function initSelect2() {
        $('.select2-single').select2();
    }
    initSelect2();

    // ADD ROW
    $('#addRow').on('click', function () {

        let newRow = `
        <tr>
            <td>
                <select name="material_item_id[]" class="form-control shadow-sm select2-single" required>
                    <option value="">-- Select Item --</option>
                    @foreach($manage_items as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
            </td>

            <td>
                <input type="number" step="any" name="material_qty[]" class="form-control shadow-sm" required>
            </td>

            <td class="text-center">
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

});
</script>

@endsection
