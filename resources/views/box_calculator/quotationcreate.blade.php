@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">


<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3">

<div class="d-flex align-items-center justify-content-between">

    <h5 class="transaction-table-title m-0">

        Create Box Quotation

    </h5>

</div>

</div>


<div class="bg-white shadow-sm p-4">

<form method="POST"
      action="{{ route('box-calculator-quotation.save') }}">

@csrf


<div class="row">
    <div class="col-md-3 mb-3">

        <label>
            Quotation Date
        </label>

        <input type="date"
            name="quotation_date"
            class="form-control"
            value="{{ date('Y-m-d') }}">

    </div>



    <div class="col-md-6 mb-3">

        <label>
            Select Party
        </label>

        <select name="party_id"
            class="form-control select2-single"
            required>

            <option value="">
                Select Party
            </option>

            @foreach($party_list as $party)

            <option value="{{ $party->id }}">

                {{ $party->name }}

            </option>

            @endforeach

        </select>

    </div>

</div>



<div id="quotationRows">


<div class="quotation_row border rounded p-3 mb-3">

<div class="row align-items-start">


    <div class="col-md-3">

        <label class="fw-bold mb-2">
            Select Box
        </label>

        <select name="items[0][box_calculation_id]"
                class="form-control box_select select2-single"
                required>

            <option value="">
                Select Box
            </option>

            @foreach($boxes as $box)

            <option value="{{ $box->id }}">

                {{ $box->box_name }}

            </option>

            @endforeach

        </select>


        <input type="hidden"
            name="items[0][box_name]"
            class="box_name">


        <input type="hidden"
            name="items[0][ply]"
            class="ply">


        <input type="hidden"
            name="items[0][box_details]"
            class="box_details">

    </div>
    <div class="col-md-2">
        <label class="fw-bold mb-2">
            Dimensions
        </label>
        <div class="dimensions border rounded p-2 bg-light"
            style="min-height:90px;">
        </div>
        <input type="hidden"
            name="items[0][dimensions]"
            class="dimensions_input">
    </div>
    <div class="col-md-4">
    <label class="fw-bold mb-2">
        Paper Specification
    </label>
    <div class="paper_specification border rounded p-2 bg-light"
        style="min-height:90px;">
    </div>
    <input type="hidden"
        name="items[0][paper_specification]"
        class="paper_specification_input">
    <input type="hidden"
        name="items[0][calculation_details]"
        class="calculation_details_input">
    </div>
    <div class="col-md-2">
        <label class="fw-bold mb-2">
            Rate
        </label>
        <input type="number"
            step="0.01"
            name="items[0][rate]"
            class="form-control rate"
            readonly>
    </div>
    <div class="col-md-1 text-center">
        <label class="fw-bold mb-2 d-block">
            Action
        </label>
        <div class="action_btns">
        <button type="button"
                class="btn btn-success add_row">
            +
        </button>
        </div>
    </div>
</div>

</div>

</div>

    <div class="text-end mt-3">
        <button type="submit"
                class="btn btn-primary">
            Save Quotation
        </button>
    </div>


</form>

</div>

</div>

</div>

</section>

</div>


@include('layouts.footer')


<script>
    let rowIndex = 1;
    $(document).ready(function () {
        $('.select2-single').select2({
            width: '100%',
            placeholder: 'Select Option'
        });
    });
    $(document).on(
        'change',
        '.box_select',
        function(){
        let boxId =
            $(this).val();
        let row =
            $(this).closest('.quotation_row');
        $.ajax({
            url:
            "{{ url('/box-calculator/get-box-details') }}/"
            + boxId,
            type: "GET",
            success: function(response)
            {
                let box =
                    response.box;
                let layers =
                    response.layers;
                row.find('.box_name')
                .val(box.box_name);
                row.find('.ply')
                .val(box.ply + ' Ply');
                row.find('.dimensions')
                .html(
                    '<div class="fw-bold">'
                    + box.length
                    + ' × '

                    + box.width
                    + ' × '

                    + box.height
                    + ' '

                    + box.input_unit
                    + '</div>'

                );
                row.find('.dimensions_input')
                .val(
                    box.length
                    + ' × '
                    + box.width
                    + ' × '
                    + box.height
                    + ' '
                    + box.input_unit
                );
                row.find('.rate')
                .val(box.sale_without_gst);
                let paperSpec = '';
                layers.forEach(function(layer,index){

                    paperSpec +=

                    '<div class="mb-1">'

                    + (index + 1)

                    + '. '

                    + layer.layer_name

                    + ' '

                    + layer.bf

                    + '/'

                    + layer.gsm

                    + '</div>';

                });


                row.find('.paper_specification')
                .html(paperSpec);
                let paperSpecText = '';

                layers.forEach(function(layer,index){

                    paperSpecText +=

                    (index + 1)

                    + '. '

                    + layer.layer_name

                    + ' '

                    + layer.bf

                    + '/'

                    + layer.gsm

                    + '\n';

                });
                row.find('.paper_specification_input')
                .val(paperSpecText);
                let boxDetails =
                    'Box Name : '
                    + box.box_name
                    + '\nPly : '
                    + box.ply + ' Ply'
                    + '\nDimensions : '
                    + box.length
                    + ' × '
                    + box.width
                    + ' × '
                    + box.height
                    + ' '
                    + box.input_unit;
                row.find('.box_details')
                .val(boxDetails);
                let calculationDetails =
                    'Cutting Length : '
                    + box.cutting_length_result
                    + '\nDeckle Width : '
                    + box.deckle_result
                    + '\nSheet Weight : '
                    + box.sheet_weight
                    + '\nWeight Per Box : '
                    + box.weight_per_box
                    + '\nPaper Cost : ₹'
                    + box.paper_cost_per_box
                    + '\nTotal Cost : ₹'
                    + box.total_cost_per_box
                    + '\nSale Price : ₹'
                    + box.sale_with_gst;
                row.find('.calculation_details_input')
                .val(calculationDetails);
            }
        });
    });
    $(document).on(
        'click',
        '.add_row',
        function(){
        let currentRow =
            $(this).closest('.quotation_row');
        let selectedBox =
            currentRow.find('.box_select').val();
        if(selectedBox == '')
        {
            alert(
                'Please select box first'
            );

            return false;
        }
        $('.action_btns').html(`
            <button type="button"
                    class="btn btn-danger remove_row">
                -
            </button>
        `);
        let html = `
        <div class="quotation_row border rounded p-3 mb-3">
        <div class="row align-items-start">
        <div class="col-md-3">
        <label class="fw-bold mb-2">
            Select Box
        </label>
        <select name="items[\${rowIndex}][box_calculation_id]"
                class="form-control box_select select2-single"
                required>
            <option value="">
                Select Box
            </option>
            @foreach($boxes as $box)
            <option value="{{ $box->id }}">
                {{ $box->box_name }}
            </option>
            @endforeach
        </select>
        <input type="hidden"
            name="items[\${rowIndex}][box_name]"
            class="box_name">
        <input type="hidden"
            name="items[\${rowIndex}][ply]"
            class="ply">
        <input type="hidden"
            name="items[\${rowIndex}][box_details]"
            class="box_details">
        </div>
        <div class="col-md-2">
            <label class="fw-bold mb-2">
                Dimensions
            </label>
            <div class="dimensions border rounded p-2 bg-light"
                style="min-height:90px;">
            </div>
            <input type="hidden"
                name="items[\${rowIndex}][dimensions]"
                class="dimensions_input">
        </div>
        <div class="col-md-4">
            <label class="fw-bold mb-2">
                Paper Specification
            </label>
            <div class="paper_specification border rounded p-2 bg-light"
                style="min-height:90px;">
            </div>
            <input type="hidden"
                name="items[\${rowIndex}][paper_specification]"
                class="paper_specification_input">
            <input type="hidden"
                name="items[\${rowIndex}][calculation_details]"
                class="calculation_details_input">
        </div>
        <div class="col-md-2">
            <label class="fw-bold mb-2">
                Rate
            </label>
            <input type="number"
                step="0.01"
                name="items[\${rowIndex}][rate]"
                class="form-control rate"
                readonly>
        </div>
        <div class="col-md-1 text-center">
            <label class="fw-bold mb-2 d-block">
                Action
            </label>
            <div class="action_btns">
                <button type="button"
                        class="btn btn-danger remove_row">
                    -
                </button>
                <button type="button"
                        class="btn btn-success add_row">
                    +
                </button>
            </div>
        </div>
        </div>
        </div>
        `;
        $('#quotationRows')
        .append(html);
        $('#quotationRows .select2-single').select2({
            width: '100%',
            placeholder: 'Select Option'
        });
        rowIndex++;

    });
    $(document).on(
        'click',
        '.remove_row',
        function(){
        $(this)
        .closest('.quotation_row')
        .remove();
        let totalRows =
            $('.quotation_row').length;
        $('.add_row').remove();
        $('.quotation_row:last .action_btns').append(`
            <button type="button"
                    class="btn btn-success add_row">
                +
            </button>
        `);
        if(totalRows == 1)
        {
            $('.quotation_row:last .action_btns').html(`
                <button type="button"
                        class="btn btn-success add_row">
                    +
                </button>
            `);
        }

    });

</script>

@endsection