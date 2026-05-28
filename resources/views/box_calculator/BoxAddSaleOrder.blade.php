@extends('layouts.app')

@section('content')

@include('layouts.header')

<div class="list-of-view-company">

<section class="list-of-view-company-section container-fluid">

<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- SUCCESS --}}
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

{{-- TITLE --}}
<div class="table-title-bottom-line bg-plum-viloet shadow-sm py-2 px-3 d-flex justify-content-between align-items-center">

    <h5 class="transaction-table-title m-0">
        Box Sale Order
    </h5>

    <a href="{{ route('box.sale.order.index') }}"
       class="btn btn-xs-primary">
        BACK
    </a>

</div>

<form action="{{ route('box.sale.order.store') }}"
      method="POST">

@csrf

<div class="bg-white p-3 shadow-sm mb-3">

    <div class="row">

        {{-- PARTY --}}
        <div class="col-md-3 mb-3">

            <label>
                Party
            </label>

            <select name="party_id"
                    class="form-control select2-single"
                    required>

                <option value="">
                    Select Party
                </option>

                @foreach($parties as $party)

                    <option value="{{ $party->id }}">
                        {{ $party->name }}
                    </option>

                @endforeach

            </select>

        </div>



        {{-- DATE --}}
        <div class="col-md-3 mb-3">

            <label>
                Date
            </label>

            <input type="date"
                   name="order_date"
                   class="form-control"
                   value="{{ date('Y-m-d') }}"
                   required>

        </div>



        {{-- SO NUMBER --}}
        <div class="col-md-3 mb-3">

            <label>
                Sale Order No
            </label>

            <input type="text"
                   name="sale_order_no"
                   class="form-control"
                   value="{{ $saleOrderNo }}"
                   readonly>

        </div>



        {{-- PO NUMBER --}}
        <div class="col-md-3 mb-3">

            <label>
                PO Number
            </label>

            <input type="text"
       name="po_number"
       class="form-control"
       required>

        </div>
{{-- PO DATE --}}
<div class="col-md-3 mb-3">

    <label>
        PO Date
    </label>

    <input type="date"
       name="po_date"
       class="form-control"
       required>

</div>
    </div>

</div>

{{-- ITEMS TABLE --}}
<div class="bg-white table-view shadow-sm mb-3"
     style="overflow-x:auto;">

<table class="table table-bordered table-striped m-0"
       id="saleOrderTable">

    <thead>

        <tr class="bg-light-pink text-body">

            <th width="18%">
                Item
            </th>

            <th width="40%">
                Description
            </th>

            <th width="10%">
                Qty
            </th>

            <th width="12%">
                Price
            </th>

            <th width="12%">
                Amount
            </th>

            <th width="8%">
                Action
            </th>

        </tr>

    </thead>

    <tbody>

        <tr>

            {{-- ITEM --}}
            <td>

                <select name="item_id[]"
                        class="form-control select2-single item-select"
                        required>

                    <option value="">
                        Select Item
                    </option>

                    @foreach($items as $item)

                        <option value="{{ $item->id }}">
                            {{ $item->name }}
                        </option>

                    @endforeach

                </select>

            </td>

            {{-- DESCRIPTION --}}
            <td>

                <div class="item-description border rounded p-2 bg-light"
                    style="
                        min-width:300px;
                        min-height:140px;
                        white-space:pre-line;
                    ">
                </div>

                <input type="hidden"
                    name="description[]"
                    class="item-description-input">

            </td>

            {{-- QTY --}}
            <td>

                <input type="number"
                       step="0.01"
                       name="qty[]"
                       class="form-control qty"
                       required>

            </td>

            {{-- PRICE --}}
            <td>

                <input type="number"
                       step="0.01"
                       name="price[]"
                       class="form-control price"
                       required>

            </td>

            {{-- AMOUNT --}}
            <td>

                <input type="number"
                       step="0.01"
                       name="amount[]"
                       class="form-control amount"
                       readonly>

            </td>

            {{-- ACTION --}}
            <td class="text-center align-middle">

                <button type="button"
                        class="btn btn-sm btn-success add-row">
                    +
                </button>

            </td>

        </tr>

    </tbody>

</table>

</div>

{{-- TOTAL --}}
<div class="bg-white p-3 shadow-sm mb-3">

    <div class="row justify-content-end">

        <div class="col-md-3">

            <label>
                Grand Total
            </label>

            <input type="text"
                   name="total_amount"
                   id="grandTotal"
                   class="form-control"
                   readonly>
        </div>

    </div>

</div>

{{-- SAVE --}}
<div class="text-right mb-5">

    <button type="submit"
            class="btn btn-primary">

        Save Sale Order

    </button>

</div>

</form>

</div>

</div>

</section>

</div>


@include('layouts.footer')

<script>

$(document).ready(function () {

    $('.select2-single').select2();

    $(document).on('click', '.add-row', function () {

        let row = `
        <tr>

            <td>

                <select name="item_id[]"
                        class="form-control select2-single item-select"
                        required>

                    <option value="">
                        Select Item
                    </option>

                    @foreach($items as $item)

                        <option value="{{ $item->id }}">
                            {{ $item->name }}
                        </option>

                    @endforeach

                </select>

            </td>

            <td>

                <div class="item-description border rounded p-2 bg-light"
                    style="
                        min-width:300px;
                        min-height:140px;
                        white-space:pre-line;
                    ">
                </div>

                <input type="hidden"
                    name="description[]"
                    class="item-description-input">

            </td>

            <td>

                <input type="number"
                       step="0.01"
                       name="qty[]"
                       class="form-control qty"
                       required>

            </td>

            <td>

                <input type="number"
                       step="0.01"
                       name="price[]"
                       class="form-control price"
                       required>

            </td>

            <td>

                <input type="number"
                       step="0.01"
                       name="amount[]"
                       class="form-control amount"
                       readonly>

            </td>

            <td class="text-center align-middle">

                <button type="button"
                        class="btn btn-sm btn-danger remove-row">
                    -
                </button>

            </td>

        </tr>
        `;

        $('#saleOrderTable tbody').append(row);

        $('.select2-single').select2();

    });

    $(document).on('click', '.remove-row', function () {

        $(this).closest('tr').remove();

        calculateGrandTotal();

    });

    $(document).on('change', '.item-select', function () {

        let itemId = $(this).val();

        let row = $(this).closest('tr');

        if(itemId != '') {

            $.ajax({

                url: "{{ url('/box-sale-order-item-details') }}/" + itemId,

                type: "GET",

                success: function (response) {

                    let item =
                        response.item;

                    let box =
                        response.box;

                    let layers =
                        response.layers;


                    let description = '';



                    if(box)
                    {

                        description +=
                            'Dimensions\n';


                        description +=
                            box.length
                            + ' × '
                            + box.width
                            + ' × '
                            + box.height
                            + ' '
                            + box.input_unit
                            + '\n\n';


                        description +=
                            'Paper Specification\n';


                        layers.forEach(function(layer,index){

                            description +=

                                (index + 1)

                                + '. '

                                + layer.layer_name

                                + ' '

                                + layer.bf

                                + '/'

                                + layer.gsm

                                + '\n';

                        });


                        row.find('.price')
                            .val(box.sale_without_gst);

                    }
                    else
                    {

                        description = '';

                        row.find('.price')
                            .val(0);

                    }

                    row.find('.item-description')
                        .html(description);

                    row.find('.item-description-input')
                        .val(description);


                        calculateRowAmount(row);

                }

            });

        }

    });




    // CALCULATE ROW
    $(document).on('keyup change', '.qty, .price', function () {

        let row = $(this).closest('tr');

        calculateRowAmount(row);

    });




    function calculateRowAmount(row)
    {

        let qty = parseFloat(
            row.find('.qty').val()
        ) || 0;

        let price = parseFloat(
            row.find('.price').val()
        ) || 0;

        let amount = qty * price;

        row.find('.amount')
            .val(amount.toFixed(2));

        calculateGrandTotal();
    }




    function calculateGrandTotal()
    {

        let total = 0;

        $('.amount').each(function () {

            total += parseFloat($(this).val()) || 0;

        });

        $('#grandTotal').val(total.toFixed(2));
    }
// CHECK DUPLICATE PO NUMBER
$(document).on('change', 'input[name="po_number"]', function () {

    let poNumber =
        $(this).val();

    let partyId =
        $('select[name="party_id"]').val();

    let currentInput =
        $(this);

    if(poNumber != '' && partyId != '')
    {

        $.ajax({

            url: "{{ route('check.box.sale.order.po') }}",

            type: "GET",

            data: {

                po_number: poNumber,
                party_id: partyId

            },

            success: function(response)
            {

                if(response.exists)
                {

                    alert(
                        'This PO Number already exists for this party.'
                    );

                    currentInput.val('');

                    currentInput.focus();

                }

            }

        });

    }

});
// VALIDATE PO DATE
$(document).on(
    'change',
    'input[name="po_date"], input[name="order_date"]',
    function () {

        let poDate =
            $('input[name="po_date"]').val();

        let soDate =
            $('input[name="order_date"]').val();

        if(poDate != '' && soDate != '')
        {

            if(poDate > soDate)
            {

                alert(
                    'PO Date cannot be greater than SO Date.'
                );

                $('input[name="po_date"]').val('');

                $('input[name="po_date"]').focus();

            }

        }

});
});
</script>

@endsection