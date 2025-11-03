@extends('layouts.app')
@section('content')
@include('layouts.header')

<style>
    /* .remove-item-btn {
        position: absolute;
        top: 155px;
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
    } */
    /* Increase height of select2 single box */
    .select2-container .select2-selection--single {
        height: 48px;              /* set your desired height */
        line-height: 45px;         /* aligns text vertically */
        padding: 6px 12px;         /* optional, adds spacing inside */
        font-size: 14px;           /* optional, bigger text */
        border-radius: 8px;
    }

    /* Adjust the arrow alignment */
    .select2-container .select2-selection--single .select2-selection__arrow {
        height: 100%;
        top: 50%;
        transform: translateY(-50%);
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
                            <label for="bill_to" class="form-label font-14 font-heading">Bill To *</label>
                            <select name="bill_to" class="form-select select2-single" id="bill_to" required>
                                <option value="">Select Account</option>
                                 @foreach($party_list as $party)
                                    <option value="{{$party->id}}" data-state_code="{{$party->state_code}}" data-gstin="{{$party->gstin}}" data-id="{{$party->id}}" data-address="{{$party->address}}, {{$party->pin_code}}" data-other_address="{{$party->otherAddress}}">{{$party->account_name}}</option>
                                @endforeach
                            </select>
                            <p id="bill_to_address" style="font-size: 10px;"></p>
                        </div>
                        <div class="col-md-3">
                            <label for="ship_to" class="form-label font-14 font-heading">Ship To *</label>
                            <select name="ship_to" id="ship_to" class="form-select select2-single" required>
                                <option value="">Select Account</option>
                                 @foreach($party_list as $party)
                                    <option value="{{$party->id}}" data-state_code="{{$party->state_code}}" data-gstin="{{$party->gstin}}" data-id="{{$party->id}}" data-address="{{$party->address}}, {{$party->pin_code}}" data-other_address="{{$party->otherAddress}}">{{$party->account_name}}</option>
                                @endforeach
                            </select>
                            <p id="shipp_to_address" style="font-size: 10px;"></p>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label for="deal" class="form-label font-14 font-heading">Deal</label>
                            <select id="deal" name="deal" class="form-select" autofocus>
                                <option value="">Select Deal</option>
                            </select>
                            <ul style="color: red;">
                                @error('voucher_no'){{$message}}@enderror
                            </ul> 
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="purchase_order_no" class="form-label font-14 font-heading">Purchase Order No.</label>
                            <input type="text" name="purchase_order_no" id="purchase_order_no" class="form-control" placeholder="Purchase Order No">
                        </div>
                        <div class="col-md-3">
                            <label for="purchase_order_date" class="form-label font-14 font-heading">Purchase Order Date</label>
                            <input type="date" name="purchase_order_date" id="purchase_order_date" class="form-control">
                        </div>
                    
                        <div class="mb-3 col-md-3">
                                    <label for="freight" class="form-label font-14 font-heading freight_div">Freight *</label>
                                    <select id="freight" name="freight" class="form-select freight_div" required >
                                        <option value="">Select Freight</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                    <ul style="color: red;">
                                        @error('voucher_no'){{$message}}@enderror
                                    </ul> 
                        </div>
                    </div>
                    <!-- Items Container -->
                    <div id="items_container">
                        <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                            <svg style="color: red; cursor: pointer; margin-right: 8px;float:right" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="35" height="35" fill="currentColor" class="bi bi-file-minus-fill remove-item-btn" viewBox="0 0 16 16" onclick="removeItem(1)"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1" /></svg> 
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-14 font-heading">Item *</label>
                                    <select name="items[1][item_id]" class="form-select select2-single" required>
                                        <option value="">Select Item</option>
                                        @if(isset($groups))
                                            @foreach($groups as $group)
                                                @if($group->items->count() > 0)
                                                    <optgroup label="{{ $group->name }}">
                                                        @foreach($group->items as $item)
                                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="price_1" class="form-label font-14 font-heading">Price *</label>
                                    <input type="number" name="items[1][price]" class="form-control" placeholder="Enter Price" required id="price_1" step="0.01" min="0">
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="bill_price_1" class="form-label font-14 font-heading">Bill Price <input type="checkbox" class="bill_price_check" data-id="1" value="1"></label>
                                    <input type="text" id="bill_price_1" class="form-control bill_price" name="items[1][bill_price]" placeholder="Enter Price" readonly data-id="1">
                                    <ul style="color: red;">
                                        @error('date'){{$message}}@enderror
                                     </ul> 
                                </div>
                            
                                <div class="col-md-3 mb-3">
                                    <label for="unit_1" class="form-label font-14 font-heading">Unit *</label>
                                    <select name="items[1][unit]" class="form-select unit" required id="unit_1" data-id="1">
                                        <option value="">Select Unit</option>
                                        @if(isset($units))
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" data-name="{{ $unit->name }}" data-unit_type="{{ $unit->unit_type }}">{{ $unit->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="sub_unit_1" class="form-label font-14 font-heading">Sub Unit *</label>
                                    <select id="sub_unit_1" name="items[1][sub_unit]" class="form-select sub_unit" data-id="1" required autofocus>
                                        <option value="">Select Sub Unit</option>
                                        <option value="INCH">INCH</option>
                                        <option value="CM">CM</option>
                                        <option value="MM">MM</option>
                                    </select>
                                    <ul style="color: red;">
                                        @error('voucher_no'){{$message}}@enderror
                                    </ul> 
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
                                               
                                            </td>
                                            <td><input type="text" name="items[1][gsms][1][gsm]" class="form-control gsm_1" placeholder="Enter GSM" required></td>
                                        </tr>
                                    </table>
                                    <table class="table table-bordered" id="table_1_1">
                                        <tr>
                                            <td>SIZES</td>
                                            <td class="qty_title_1">REELS</td>
                                        </tr>
                                        @php $row_index = 0;@endphp
                                        @while ($row_index < 5) 
                                            <tr>
                                                <td><input type="text" name="items[1][gsms][1][details][{{ $row_index }}][size]" class="form-control size size_1_1" placeholder="SIZES" onkeyup="approxCalculation(1,1)"></td>
                                                <td><input type="number" name="items[1][gsms][1][details][{{ $row_index }}][reel]" class="form-control quantity quantity_1_1" placeholder="REELS" data-item_id="1" data-gsm_id="1" data-quantity_id="{{ $row_index }}" onkeyup="approxCalculation(1,1)"></td>
                                            </tr>
                                            @php $row_index++; @endphp
                                        @endwhile
                                        <tr>
                                            <th style="text-align: center">Total</th>
                                            <td>
                                                <input type="number" class="form-control quantity_total" id="quantity_total_1_1" placeholder="0" readonly>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align: center">Approx Qty</th>
                                            <td>
                                                <input type="number" class="form-control approx" id="approx_qty_1_1" placeholder="0" readonly id="approx_qty_1_1">
                                            </td>
                                        </tr>
                                    </table>
                                    <span class="add_row" data-item="1" data-gsm="1" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p>
                        <svg id="add_item_btn" 
                        width="36" height="36" viewBox="0 0 24 24" 
                        xmlns="http://www.w3.org/2000/svg" cursor="pointer">
                        <!-- circle background -->
                        <circle cx="12" cy="12" r="12" fill="#007bff"/>
                        <!-- plus sign -->
                        <path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/>
                        </svg>
                    </p>
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

<script>

$(document).ready(function() {
    $('.select2-single').select2({
        width: '100%' // Optional: ensures full width
    });
});
$(document).ready(function() {
    let itemIndex = $("#items_container .item-section").length;
    
    // Add Item
    $(document).on("click", "#add_item_btn", function() {
        
        itemIndex++;
        let newSection = $("#item_section_1").clone();

        // Remove Select2 extra markup from cloned section
        newSection.find(".select2").remove();        // remove the generated Select2 container
        newSection.find("select").removeAttr("data-select2-id").show(); 
        newSection.find("input, select").val("");

        newSection.attr("id", "item_section_" + itemIndex);
        newSection.find(".remove-item-btn").attr("onclick", "removeItem(" + itemIndex + ")");
        newSection.find(".bill_price_check").attr("data-id",itemIndex);
        newSection.find(".bill_price").attr("id","bill_price_"+itemIndex);
        newSection.find("#unit_1").attr("data-id",itemIndex);
        newSection.find("#sub_unit_1").attr("data-id",itemIndex);
        newSection.find("#unit_1").attr("id","unit_"+itemIndex);
        newSection.find("#sub_unit_1").attr("id","sub_unit_"+itemIndex);

        

       
        newSection.find(".bill_price").attr("id","bill_price_"+itemIndex);
        let detailsRows = '';
        for (let row_index = 0; row_index < 5; row_index++) {
            detailsRows += `
                <tr>
                    <td>
                        <input type="text" name="items[${itemIndex}][gsms][1][details][${row_index}][size]" class="form-control  size size_${itemIndex}_1" placeholder="SIZES" onkeyup="approxCalculation(${itemIndex},1)">
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][gsms][1][details][${row_index}][reel]" class="form-control quantity quantity_${itemIndex}_1" data-item_id="${itemIndex}" data-gsm_id="1" data-quantity_id="${row_index}" placeholder="REELS" onkeyup="approxCalculation(${itemIndex},1)">
                    </td>
                </tr>
            `;
        }
        detailsRows += `<tr>
                        <th style="text-align: center">Total</th>
                        <td>
                            <input type="number" class="form-control quantity_total" id="quantity_total_${itemIndex}_1" placeholder="0" readonly>
                        </td>
                    </tr>
                    <tr>
                        <th style="text-align: center">Approx Qty</th>
                        <td>
                            <input type="number"  class="form-control approx" placeholder="0" id="approx_qty_${itemIndex}_1" readonly>
                        </td>
                    </tr>`;
        newSection.find("[id^='dynamic_gsm_']").attr("id", "dynamic_gsm_" + itemIndex).html(`
            <div class="col-md-3 gsm-block" id="gsm_block_${itemIndex}_1">
                <table class="table table-bordered">
                    <tr>
                        <td style="width: 40%;">GSM
                           <svg style="color: green;cursor:pointer;" class="bg-primary rounded-circle add_gsm" data-item_id="${itemIndex}" data-gsm_id="1" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/></svg>
                        </td>
                        <td><input type="text" name="items[${itemIndex}][gsms][1][gsm]" class="form-control gsm_${itemIndex}" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
                <table class="table table-bordered" id="table_${itemIndex}_1">
                    <tr>
                        <td>SIZES</td>
                        <td class="qty_title_${itemIndex}">REELS</td>
                    </tr>`+
                    detailsRows+
                `</table>
                <span class="add_row" data-item="${itemIndex}" data-gsm="1" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
            </div>
        `);
        newSection.find("select, input").each(function () {
            let name = $(this).attr("name");
            if (name) {
                name = name.replace(/items\[\d+\]/, "items[" + itemIndex + "]");
                $(this).attr("name", name).val("");
            }
            
        });
        $("#items_container").append(newSection);
        newSection.find(".select2-single").select2({
        width: '100%' // or any config you used initially
    });
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

        let unit_name = $("#unit_"+item_id+" option:selected").attr("data-name");
        let detailsRows = '';
        for (let row_index = 0; row_index < 5; row_index++) {
            detailsRows += `
                <tr>
                    <td>
                        <input type="text" name="items[${item_id}][gsms][${gsm_id}][details][${row_index}][size]" class="form-control  size size_${item_id}_${gsm_id}" placeholder="SIZES" onkeyup="approxCalculation(${item_id},${gsm_id})">
                    </td>
                    <td>
                        <input type="number" name="items[${item_id}][gsms][${gsm_id}][details][${row_index}][reel]" class="form-control quantity quantity_${item_id}_${gsm_id}" data-item_id="${item_id}" data-gsm_id="${gsm_id}" data-quantity_id="${row_index}" placeholder="${unit_name}" onkeyup="approxCalculation(${item_id},${gsm_id})">
                    </td>
                </tr>
            `;
        }
        detailsRows += `<tr>
            <th style="text-align: center">Total</th>
            <td>
                <input type="number" class="form-control quantity_total" id="quantity_total_${item_id}_${gsm_id}" placeholder="0" readonly>
            </td>
        </tr>
        <tr>
            <th style="text-align: center">Approx Qty</th>
            <td>
                <input type="number" class="form-control approx" placeholder="0" id="approx_qty_${item_id}_${gsm_id}" readonly>
            </td>
        </tr>`;
        $("#dynamic_gsm_" + item_id).append(`
            <div class="col-md-3 gsm-block" id="gsm_block_${item_id}_${gsm_id}">
                <table class="table table-bordered">
                    <tr>
                        <td style="width:40%;">GSM
                            
                           <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove-gsm-btn" viewBox="0 0 16 16" onclick="removeGsm(${item_id},${gsm_id})"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1" /></svg>
                        </td>
                        <td><input type="text" name="items[${item_id}][gsms][${gsm_id}][gsm]" class="form-control gsm_${item_id}" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
                <table class="table table-bordered" id="table_${item_id}_${gsm_id}">
                    <tr>
                        <td>SIZES</td>
                        <td class="qty_title_${item_id}">${unit_name}</td>
                    </tr>`+
                    detailsRows+`
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
        let rowIndex = table.find("tr").length - 3; // exclude header row
        table.find("tr").eq(-2).before(`
            <tr>
                <td>
                    <input type="text" name="items[${itemId}][gsms][${gsmId}][details][${rowIndex}][size]" class="form-control  size size_${itemId}_${gsmId}" placeholder="SIZES" onkeyup="approxCalculation(${itemId},${gsmId})">
                </td>
                <td>
                    <input type="number" name="items[${itemId}][gsms][${gsmId}][details][${rowIndex}][reel]" class="form-control quantity quantity_${itemId}_${gsmId}" data-item_id="${itemId}" data-gsm_id="${gsmId}" data-quantity_id="${rowIndex}" placeholder="REELS" onkeyup="approxCalculation(${itemId},${gsmId})">
                </td>
            </tr>
        `);
    });

    // Initialize existing GSM buttons
    $("#items_container .item-section").each(function() {
        let id = $(this).attr("id").split("_").pop();
        updateGsmButtons(id);
    });

    $("#bill_to").change(function() {
        var selectedOption = $(this).find('option:selected');
        var address = selectedOption.data('address');
        var fullAddress = address;        
        $("#bill_to_address").text(fullAddress);
    });
    $("#ship_to").change(function() {
        var selectedOption = $(this).find('option:selected');
        var address = selectedOption.data('address');
        var fullAddress = address;        
        $("#shipp_to_address").text(fullAddress);
    });
    
});

$(document).on('click', '.bill_price_check', function() {
    var id = $(this).data("id");
    if($(this).is(":checked")){
        $("#bill_price_"+id).prop("readonly", false);
        $("#bill_price_"+id).val('');
        $("#bill_price_"+id).focus();
    }else{
        $("#bill_price_"+id).prop("readonly", true);
        $("#bill_price_"+id).val('');
    }
});
$(document).on('keyup', '.quantity', function() {
    var item_id = $(this).data("item_id");
    var gsm_id = $(this).data("gsm_id");
    var quantity_id = $(this).data("quantity_id");
    var total = 0;
    // Calculate total for this GSM block
    $(".quantity_"+item_id+"_"+gsm_id).each(function() {
        var val = parseFloat($(this).val());
        if (!isNaN(val)) {
            total += val;
        }
    });   
    // Update the total field for this GSM block
    $("#quantity_total_" + item_id + "_" + gsm_id).val(total);
});
function approxCalculation(item_id,gsm_id){
    let kg_per_inch = 15;
    var total_qty = 0;
    var reelArr = [];
    var sizeArr = [];
    $(".quantity_"+item_id+"_"+gsm_id).each(function(e,i) {
        if($(this).val()==''){
            reelArr.push(0);
        }else{
            reelArr.push($(this).val());
        }
        if($(this).val()!=''){
            total_qty = parseInt(total_qty) + parseInt($(this).val()); 
        }
    });
    $(".size_"+item_id+"_"+gsm_id).each(function(e,i) {
        if($(this).val()==''){
            sizeArr.push(0);
        }else{
            sizeArr.push($(this).val());
        }    
    });
    let approx_qty = 0;
    if($("#unit_"+item_id+" option:selected").attr("data-unit_type")=='REEL'){
        for(let i=0;i<reelArr.length;i++){
            approx_qty = approx_qty + reelArr[i] * sizeArr[i] * kg_per_inch;
        }
    }else{
        for(let i=0;i<reelArr.length;i++){
            if(reelArr[i]!=""){
                approx_qty = approx_qty + reelArr[i]/(sizeArr[i] * kg_per_inch);
            }
        }
    }
    if($("#unit_"+item_id+" option:selected").attr("data-unit_type")=='REEL' && kg_per_inch!=''){
        //totalWeight = Math.round(totalWeight / 100) * 100;   
        if($("#sub_unit_"+item_id).val()=="CM"){
            approx_qty = approx_qty/ 2.54;
        }else if($("#sub_unit_"+item_id).val()=="MM"){
            approx_qty = approx_qty/ 25.4;
        }
        approx_qty = Math.round(approx_qty / 100) * 100;
        $("#approx_qty_"+item_id+"_"+gsm_id).val(approx_qty);
    }else if($("#unit_"+item_id+" option:selected").attr("data-unit_type")=='KG' && kg_per_inch!=''){
        approx_qty = Math.round(approx_qty);
        $("#approx_qty_"+item_id+"_"+gsm_id).val(approx_qty);
    }
    return;
    let total_w = 0;
    $(".app_weight").each(function() {
        total_w = parseInt(total_w)+parseInt($(this).html());
    });
    $("#total_"+a+"_"+b).html(c);
    let total_r = 0;
    $(".total_reel").each(function() {
        total_r=parseInt(total_r)+parseInt($(this).html());
    });
    if(total_w!=0){
        $("#total_reel").html(total_r+' ('+total_w+')');
    }else{
        $("#total_reel").html(total_r);
    }
}
$(document).on('change','.sub_unit',function(){
    var id = $(this).attr('data-id');
    let ind = 1;
    $(".gsm_"+id).each(function(){
        approxCalculation(id,ind);
        ind++;
    });
});
$(document).on('change','.unit',function(){
    var id = $(this).attr('data-id');
    var name = $(this).find("option:selected").attr("data-name");
    let ind = 1;
    $(".gsm_"+id).each(function(){
        approxCalculation(id,ind);
        $(".qty_title_"+id).html(name);
        $(".quantity_"+id+"_"+ind).attr('placeholder',name);
        ind++;
    });
});

// Form submission validation for at least one filled size/reel per item
$('#saleOrderForm').on('submit', function(e) {
    let formValid = true;

    $('#items_container .item-section').each(function() {
        let hasFilledRow = false;
        let firstEmpty = null;

        $(this).find('.gsm-block').each(function() {
            $(this).find('table tr').each(function() {
                let sizeInput = $(this).find('input[name*="[size]"]');
                let reelInput = $(this).find('input[name*="[reel]"]');

                if(sizeInput.length && reelInput.length) {
                    if(sizeInput.val() && reelInput.val()) {
                        hasFilledRow = true;
                    }
                    if(!firstEmpty && (!sizeInput.val() || !reelInput.val())) {
                        firstEmpty = {size: sizeInput, reel: reelInput};
                    }
                }
            });
        });

        if(!hasFilledRow) {
            // prevent submission and trigger browser validation
            e.preventDefault();
            if(firstEmpty) {
                firstEmpty.size.prop('required', true);
                firstEmpty.reel.prop('required', true);
                firstEmpty.size[0].reportValidity();
            }
            formValid = false;
            return false; // stop checking other items
        }
    });

    return formValid;
});


$(document).ready(function() {

    // 🔹 When user selects "Bill To"
    $('#bill_to').on('change', function() {
        var party_id = $(this).val();
        $('#deal').html('<option value="">Loading deals...</option>');

        if (party_id) {
            $.ajax({
                url: '{{ url("get-deals-by-party") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    party_id: party_id
                },
                success: function(response) {
                    $('#deal').html('<option value="">Select Deal</option>');
                    if (response.deals.length > 0) {
                        $.each(response.deals, function(index, deal) {
                            $('#deal').append('<option value="' + deal.id + '">Deal No: ' + deal.deal_no + '</option>');
                        });
                    } else {
                        $('#deal').append('<option value="">No deals found</option>');
                    }
                }
            });
        } else {
            $('#deal').html('<option value="">Select Deal</option>');
        }
    });

    // 🔹 When user selects "Deal"
    $('#deal').on('change', function() {
        var deal_id = $(this).val();
        if (deal_id) {
            $.ajax({
                url: '{{ url("get-deal-details") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    deal_id: deal_id
                },
                success: function(response) {

                    // 🔹 Update Freight
                    $('#freight').val(response.freight || '');
                    // 🔹 Update all item dropdowns dynamically
                    $('#items_container .item-section').each(function() {
                        var itemSelect = $(this).find("select[name*='[item_id]']");
                        var priceInput = $(this).find("input[name*='[price]']");

                        // Clear current options
                        itemSelect.html('<option value="">Select Item</option>');

                        if (response.items.length > 0) {
                            $.each(response.items, function(index, item) {
                                itemSelect.append('<option value="' + item.item_id + '" data-rate="' + item.rate + '">' + item.item_name + '</option>');
                            });
                        } else {
                            itemSelect.append('<option value="">No items found</option>');
                        }

                        // Reset price input
                        priceInput.val('');
                    });
                }
            });
        } else {
            $('#freight').val('');
            $('#items_container .item-section').each(function() {
                var itemSelect = $(this).find("select[name*='[item_id]']");
                var priceInput = $(this).find("input[name*='[price]']");
                itemSelect.html('<option value="">Select Item</option>');
                priceInput.val('');
            });
        }
    });

    // 🔹 Auto-fill price when item is selected (works for dynamically added items)
    $(document).on('change', "select[name*='[item_id]']", function() {
        var rate = $(this).find(':selected').data('rate') || '';
        var row = $(this).closest('.item-section');
        row.find("input[name*='[price]']").val(rate);
    });
    $(document).on('input', '.size', function() {
        let currentInput = $(this);
        let currentVal = currentInput.val().trim();

        if(currentVal === '') return; // ignore empty

        // Find GSM block (parent div with class 'gsm-block')
        let gsmBlock = currentInput.closest('.gsm-block');

        // Collect all other size values within this GSM block
        let allSizes = [];
        gsmBlock.find('.size').each(function(){
            let val = $(this).val().trim();
            if(val !== '') allSizes.push(val);
        });

        // Check if this value appears more than once
        let duplicateCount = allSizes.filter(v => v === currentVal).length;

        if(duplicateCount > 1){
            alert('This size already exists! Please enter a unique size.');
            currentInput.val(''); // clear duplicate entry
            currentInput.focus();
        }
    });


});



</script>
@endsection