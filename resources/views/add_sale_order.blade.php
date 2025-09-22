@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
    .remove-item-btn {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    .remove-gsm-btn {
        background: #ff9933;
        border: none;
        color: white;
        padding: 2px 6px;
        cursor: pointer;
        border-radius: 3px;
        margin-left: 5px;
    }
    .remove-gsm-btn:hover {
        background: #e68a00;
    }
</style>

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <div class="col-md-12 ml-sm-auto col-lg-9 px-md-4 bg-mint">
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Sale Order</h5>

                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale-order.store')}}" id="saleOrderForm">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Bill To *</label>
                            <select name="bill_to" class="form-select" required>
                                <option value="">Select Account</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Ship To *</label>
                            <select name="ship_to" class="form-select" required>
                                <option value="">Select Account</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Deal</label>
                            <select name="deal" class="form-select">
                                <option value="">Select Deal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Items Container -->
                    <div id="items_container">
                        <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                            <button type="button" class="btn btn-danger btn-sm remove-item-btn" onclick="removeItem(1)">Remove</button>

                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label>Item *</label>
                                    <select name="item[]" class="form-select" required>
                                        <option value="">Select Item</option>
                                        @if(isset($groups))
                                            @foreach($groups as $group)
                                                <optgroup label="{{ $group->name }}">
                                                    @foreach($group->items as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label>Price *</label>
                                    <input type="number" name="price[]" class="form-control" placeholder="Enter Price" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label>Unit *</label>
                                    <select name="unit[]" class="form-select" required>
                                        <option value="">Select Unit</option>
                                        @if(isset($units))
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <!-- GSM / Sizes / Reels -->
                            <div class="row" id="dynamic_gsm_1">
                                <div class="col-md-3 gsm-block" id="gsm_block_1_1">
                                    <table class="table table-bordered">
                                        <tr>
                                            <td style="width: 40%;">GSM
                                                <svg style="color: green;cursor:pointer;" class="bg-primary rounded-circle add_gsm" data-item_id="1" data-gsm_id="1" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                                                </svg>
                                                <button type="button" class="remove-gsm-btn" onclick="removeGsm(1,1)">Remove GSM</button>
                                            </td>
                                            <td><input type="text" name="gsm[]" class="form-control" placeholder="Enter GSM" required></td>
                                        </tr>
                                    </table>
                                    <table class="table table-bordered" id="table_1_1">
                                        <tr>
                                            <td>SIZES</td>
                                            <td>REELS</td>
                                        </tr>
                                        <tr>
                                            <td><input type="text" name="size[]" class="form-control" placeholder="SIZES" required></td>
                                            <td><input type="number" name="reel[]" class="form-control" placeholder="REELS" required></td>
                                        </tr>
                                    </table>
                                    <span class="add_row" data-item="1" data-gsm="1" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p><button type="button" id="add_item_btn" class="btn btn-info">ADD ITEM</button></p>

                    <div class="d-flex">
                        <div class="ms-auto">
                            <input type="submit" value="SAVE" class="btn btn-primary">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">QUIT</a>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<!-- JS for dynamic form -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = $("#items_container .item-section").length;

    // Add Item
    $(document).on("click", "#add_item_btn", function() {
        itemIndex++;
        let newSection = $("#item_section_1").clone();
        newSection.find("input, select").val("");
        newSection.attr("id", "item_section_" + itemIndex);
        newSection.find(".remove-item-btn").attr("onclick", "removeItem(" + itemIndex + ")");
        newSection.find("[id^='dynamic_gsm_']").attr("id", "dynamic_gsm_" + itemIndex).html(`
            <div class="col-md-3 gsm-block" id="gsm_block_${itemIndex}_1">
                <table class="table table-bordered">
                    <tr>
                        <td style="width: 40%;">GSM
                            <svg class="bg-primary rounded-circle add_gsm" data-item_id="${itemIndex}" data-gsm_id="1" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="cursor:pointer;">
                                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                            </svg>
                            <button type="button" class="remove-gsm-btn" onclick="removeGsm(${itemIndex},1)">Remove GSM</button>
                        </td>
                        <td><input type="text" name="gsm[]" class="form-control" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
                <table class="table table-bordered" id="table_${itemIndex}_1">
                    <tr>
                        <td>SIZES</td>
                        <td>REELS</td>
                    </tr>
                    <tr>
                        <td><input type="text" name="size[]" class="form-control" placeholder="SIZES" required></td>
                        <td><input type="number" name="reel[]" class="form-control" placeholder="REELS" required></td>
                    </tr>
                </table>
                <span class="add_row" data-item="${itemIndex}" data-gsm="1" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
            </div>
        `);
        $("#items_container").append(newSection);
        updateGsmButtons(itemIndex);
    });

    // Remove Item
    window.removeItem = function(id) {
        if ($("#items_container .item-section").length > 1) {
            $("#item_section_" + id).remove();
        } else {
            alert("At least one item is required.");
        }
    };

    // Add GSM
    $(document).on("click", ".add_gsm", function() {
        let item_id = $(this).data("item_id");
        let gsm_id = parseInt($(this).data("gsm_id")) + 1;
        $(this).data("gsm_id", gsm_id);

        $("#dynamic_gsm_" + item_id).append(`
            <div class="col-md-3 gsm-block" id="gsm_block_${item_id}_${gsm_id}">
                <table class="table table-bordered">
                    <tr>
                        <td style="width:40%;">GSM
                            <svg class="bg-primary rounded-circle add_gsm" data-item_id="${item_id}" data-gsm_id="1" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="cursor:pointer;">
                                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                            </svg>
                            <button type="button" class="remove-gsm-btn" onclick="removeGsm(${item_id},${gsm_id})">Remove GSM</button>
                        </td>
                        <td><input type="text" name="gsm[]" class="form-control" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
                <table class="table table-bordered" id="table_${item_id}_${gsm_id}">
                    <tr>
                        <td>SIZES</td>
                        <td>REELS</td>
                    </tr>
                    <tr>
                        <td><input type="text" name="size[]" class="form-control" placeholder="SIZES" required></td>
                        <td><input type="number" name="reel[]" class="form-control" placeholder="REELS" required></td>
                    </tr>
                </table>
                <span class="add_row" data-item="${item_id}" data-gsm="${gsm_id}" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
            </div>
        `);
        updateGsmButtons(item_id);
    });

    // Remove GSM
    window.removeGsm = function(itemId, gsmId) {
        $("#gsm_block_" + itemId + "_" + gsmId).remove();
        updateGsmButtons(itemId);
    };

    // Update GSM buttons
    function updateGsmButtons(itemId) {
        let blocks = $("#dynamic_gsm_" + itemId + " .gsm-block");
        if(blocks.length === 1) {
            blocks.find(".remove-gsm-btn").hide();
        } else {
            blocks.find(".remove-gsm-btn").show();
        }
    }

    // Add Row (Sizes/Reels)
    $(document).on("click", ".add_row", function() {
        let itemId = $(this).data("item");
        let gsmId = $(this).data("gsm");
        let table = $("#table_" + itemId + "_" + gsmId);
        table.find("tr").eq(-2).before(`
            <tr>
                <td><input type="text" name="size[]" class="form-control" placeholder="SIZES" required></td>
                <td><input type="number" name="reel[]" class="form-control" placeholder="REELS" required></td>
            </tr>
        `);
    });

    // Initialize existing GSM buttons
    $("#items_container .item-section").each(function() {
        let id = $(this).attr("id").split("_").pop();
        updateGsmButtons(id);
    });
});
</script>
@endsection
