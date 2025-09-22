@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
    .remove-item-btn {
        position: absolute;
        top: 144px;
        right: 65px;
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
                    <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="alert">{{ session('success') }}</div>
                @endif            

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Sale Order</h5>

                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale-order.store')}}" id="purchaseForm">
                    @csrf

                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Bill To *</label>
                            <select id="bill_to" name="bill_to" class="form-select" required>
                                <option value="">Select Account</option>                        
                            </select>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Ship To *</label>
                            <select id="ship_to" name="ship_to" class="form-select" required>
                                <option value="">Select Account</option>                        
                            </select>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label class="form-label font-14 font-heading">Deal</label>
                            <select id="deal" name="deal" class="form-select">
                                <option value="">Select Deal</option>                        
                            </select>
                        </div>
                    </div>

                    <!-- Items Container -->
                    <div id="items_container">
                        <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                            <!-- Remove Item button -->
                            <button type="button" class="btn btn-danger btn-sm remove-item-btn" onclick="removeItem(1)">Remove</button>

                            <div class="row">
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">Item *</label>
                                    <select id="item_1" name="item[]" class="form-select item" data-id="1" required>
                                        <option value="">Select Item</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">Price *</label>
                                    <input type="number" id="price_1" class="form-control price" name="price[]" placeholder="Enter Price" data-id="1" required>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">Bill Price <input type="checkbox"></label>
                                    <input type="text" id="bill_price_1" class="form-control" name="bill_price[]" placeholder="Enter Price" readonly data-id="1">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">Freight *</label>
                                    <select id="freight_1" name="freight[]" class="form-select" required>
                                        <option value="">Select Freight</option>
                                    </select>
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">Unit *</label>
                                    <select id="unit_1" name="unit[]" class="form-select unit" data-id="1" required>
                                        <option value="">Select Unit</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">Sub Unit *</label>
                                    <select id="sub_unit_1" name="sub_unit[]" class="form-select sub_unit" data-id="1" required>
                                        <option value="">Select Sub Unit</option>
                                        <option value="INCH">INCH</option>
                                        <option value="CM">CM</option>
                                        <option value="MM">MM</option>
                                    </select>
                                </div>
                            </div>

                            <!-- GSM + Sizes/Reels -->
                            <div class="row" id="dynamic_gsm_1">
                                <div class="mb-3 col-md-3 gsm-block" id="gsm_block_1_1">
                                    <div class="form-group">
                                        <table class="table table-bordered">
                                            <tr>
                                                <td style="width: 40%;">GSM 
                                                    <svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                                                         fill="currentColor" class="bg-primary rounded-circle add_gsm" data-item_id="1" data-gsm_id="1" id="add_gsm_1">
                                                        <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                                                    </svg>
                                                    <button type="button" class="remove-gsm-btn" onclick="removeGsm(1,1)">Remove GSM</button>
                                                </td>
                                                <td><input type="text" id="gsm_1_1" class="form-control gsm_1" name="gsm[]" placeholder="Enter GSM" required></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="form-group">
                                        <table class="table table-bordered" id="table_1_1">
                                            <tr>
                                                <td>SIZES</td>
                                                <td>REELS</td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" id="size_1_1_1" class="form-control size_1 size_1_1" name="size[]" placeholder="SIZES" required></td>
                                                <td><input type="text" id="reel_1_1_1" class="form-control reel_1 reel_1_1" name="reel[]" placeholder="REELS" required></td>
                                            </tr>
                                            <tr>
                                                <td>Total</td>
                                                <td id="total_reel_1_1" class="total_reel">0</td>
                                            </tr>
                                        </table>
                                        <span class="add_row" onClick="addRow(1,1)" style="color:#3c8dbc;cursor: pointer;">Add Row</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p><button type="button" id="add_item_btn" class="btn btn-info">ADD ITEM</button></p>

                    <div class="d-flex">
                        <div class="ms-auto">
                            <input type="submit" value="SAVE" class="btn btn-xs-primary" id="purchaseBtn">
                            <a href="{{ url()->previous() }}" class="btn btn-black">QUIT</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<script>
// Add Row function
function addRow(itemId, gsmId) {        
    $("#table_"+itemId+"_"+gsmId+" tr").eq(-2).before(`
        <tr>
            <td><input type="text" name="size[]" class="form-control size_${itemId}_${gsmId}" placeholder="SIZES" required></td>
            <td><input type="number" min="1" class="form-control reel_${itemId}_${gsmId}" name="reel[]" placeholder="REELS" required></td>
        </tr>
    `);
}

// Add GSM dynamically
$(document).on('click', '.add_gsm', function() {
    let item_id = $(this).attr('data-item_id');
    let gsm_id = parseInt($(this).attr('data-gsm_id'));
    gsm_id++;

    // Update the triggering button's gsm_id for future additions
    $(this).attr('data-gsm_id', gsm_id);

    $("#dynamic_gsm_" + item_id).append(`
        <div class="mb-3 col-md-3 gsm-block" id="gsm_block_${item_id}_${gsm_id}">
            <div class="form-group">
                <table class="table table-bordered">
                    <tr>
                        <td style="width: 40%;">GSM 
                            <svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                                fill="currentColor" class="bg-primary rounded-circle add_gsm" 
                                data-item_id="${item_id}" data-gsm_id="1" id="add_gsm_${item_id}_${gsm_id}">
                                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                            </svg>
                            <button type="button" class="remove-gsm-btn" onclick="removeGsm(${item_id},${gsm_id})">Remove GSM</button>
                        </td>
                        <td><input type="text" id="gsm_${item_id}_${gsm_id}" class="form-control gsm_${item_id}" name="gsm[]" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
            </div>
            <div class="form-group">
                <table class="table table-bordered" id="table_${item_id}_${gsm_id}">
                    <tr>
                        <td>SIZES</td>
                        <td>REELS</td>
                    </tr>
                    <tr>
                        <td><input type="text" class="form-control size_${item_id}_${gsm_id}" name="size[]" placeholder="SIZES" required></td>
                        <td><input type="text" class="form-control reel_${item_id}_${gsm_id}" name="reel[]" placeholder="REELS" required></td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td id="total_reel_${item_id}_${gsm_id}" class="total_reel">0</td>
                    </tr>
                </table>
                <span class="add_row" onClick="addRow(${item_id},${gsm_id})" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
            </div>
        </div>
    `);

    updateGsmButtons(item_id);
});

// Remove GSM with rule (must keep 1)
function removeGsm(itemId, gsmId) {
    $("#gsm_block_" + itemId + "_" + gsmId).remove();
    updateGsmButtons(itemId);
}

// Update visibility of Remove GSM buttons
function updateGsmButtons(itemId) {
    let gsmBlocks = $("#dynamic_gsm_" + itemId + " .gsm-block");
    if (gsmBlocks.length === 1) {
        gsmBlocks.find(".remove-gsm-btn").hide();
    } else {
        gsmBlocks.find(".remove-gsm-btn").show();
    }
}

// Add Item
let itemIndex = 1;
$("#add_item_btn").on("click", function() {
    itemIndex++;
    let newSection = $("#item_section_1").clone();

    newSection.attr("id", "item_section_" + itemIndex);
    newSection.find(".remove-item-btn").attr("onclick", "removeItem(" + itemIndex + ")");

    // Reset GSM container with 1 block (with add + remove GSM)
    newSection.find(".row[id^='dynamic_gsm_']").html(`
        <div class="mb-3 col-md-3 gsm-block" id="gsm_block_${itemIndex}_1">
            <div class="form-group">
                <table class="table table-bordered">
                    <tr>
                        <td style="width: 40%;">GSM 
                            <svg style="color: green;cursor: pointer;" xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                                fill="currentColor" class="bg-primary rounded-circle add_gsm" 
                                data-item_id="${itemIndex}" data-gsm_id="1" id="add_gsm_${itemIndex}">
                                <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                            </svg>
                            <button type="button" class="remove-gsm-btn" onclick="removeGsm(${itemIndex},1)">Remove GSM</button>
                        </td>
                        <td><input type="text" id="gsm_${itemIndex}_1" class="form-control gsm_${itemIndex}" name="gsm[]" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
            </div>
            <div class="form-group">
                <table class="table table-bordered" id="table_${itemIndex}_1">
                    <tr>
                        <td>SIZES</td>
                        <td>REELS</td>
                    </tr>
                    <tr>
                        <td><input type="text" id="size_${itemIndex}_1_1" class="form-control size_${itemIndex}_1" name="size[]" placeholder="SIZES" required></td>
                        <td><input type="text" id="reel_${itemIndex}_1_1" class="form-control reel_${itemIndex}_1" name="reel[]" placeholder="REELS" required></td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td id="total_reel_${itemIndex}_1" class="total_reel">0</td>
                    </tr>
                </table>
                <span class="add_row" onClick="addRow(${itemIndex},1)" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
            </div>
        </div>
    `).attr("id", "dynamic_gsm_" + itemIndex);

    // Reset all inputs/selects
    newSection.find("input").val("");
    newSection.find("select").val("");

    // Update input IDs and names if necessary (optional based on backend needs)
    newSection.find("[id]").each(function(){
        let oldId = $(this).attr("id");
        if(oldId && oldId.includes("_1")) {
            let newId = oldId.replace(/\d+(_\d+)?$/, function(match){
                return match.includes("_") ? itemIndex + "_1" : itemIndex;
            });
            $(this).attr("id", newId);
        }
    });

    $("#items_container").append(newSection);
    updateGsmButtons(itemIndex);
});

// Remove Item
function removeItem(id) {
    if ($("#items_container .item-section").length > 1) {
        $("#item_section_" + id).remove();
    } else {
        alert("At least one item section is required.");
    }
}

// Initialize on load
$(document).ready(function(){
    updateGsmButtons(1);
});
</script>


@endsection
