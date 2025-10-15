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

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Edit Sale Order</h5>

                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('sale-order.update',$saleOrder->id)}}" id="saleOrderForm">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="bill_to" class="form-label font-14 font-heading">Bill To *</label>
                            <select name="bill_to" class="form-select select2-single" id="bill_to" required>
                                <option value="">Select Account</option>
                                 @foreach($party_list as $party)
                                    <option value="{{$party->id}}" @if($party->id==$saleOrder->billTo->id) selected  @endif data-state_code="{{$party->state_code}}" data-gstin="{{$party->gstin}}" data-id="{{$party->id}}" data-address="{{$party->address}}, {{$party->pin_code}}" data-other_address="{{$party->otherAddress}}">{{$party->account_name}}</option>
                                @endforeach
                            </select>
                            <p id="bill_to_address" style="font-size: 10px;"></p>
                        </div>
                        <div class="col-md-3">
                            <label for="ship_to" class="form-label font-14 font-heading">Ship To *</label>
                            <select name="ship_to" id="ship_to" class="form-select select2-single" required>
                                <option value="">Select Account</option>
                                 @foreach($party_list as $party)
                                    <option value="{{$party->id}}" @if($party->id==$saleOrder->shippTo->id) selected  @endif data-state_code="{{$party->state_code}}" data-gstin="{{$party->gstin}}" data-id="{{$party->id}}" data-address="{{$party->address}}, {{$party->pin_code}}" data-other_address="{{$party->otherAddress}}">{{$party->account_name}}</option>
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
                            <input type="text" name="purchase_order_no" id="purchase_order_no" class="form-control" placeholder="Purchase Order No" value="{{$saleOrder->purchase_order_no}}">
                        </div>
                        <div class="col-md-3">
                            <label for="purchase_order_date" class="form-label font-14 font-heading">Purchase Order Date</label>
                            <input type="date" name="purchase_order_date" id="purchase_order_date" class="form-control" value="{{$saleOrder->purchase_order_date}}">
                        </div>
                        <div class="mb-3 col-md-3">
                                           
                                            <label for="freight" class="form-label font-14 font-heading freight_div">Freight *</label>
                                            <select id="freight" name="freight" class="form-select freight_div" required autofocus>
                                                <option value="">Select Freight</option>
                                                <option value="Yes" @if($saleOrder->freight=="Yes") selected @endif>Yes</option>
                                                <option value="No" @if($saleOrder->freight=="No") selected @endif>No</option>
                                            </select>
                                           
                                        
                                        </div>
                    </div>
                    <!-- Items Container -->
                    <div id="items_container">
                        @php $item_index = 1; @endphp
                        @foreach($saleOrder->items as $key => $value)
                            <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_{{$item_index}}">
                                <svg style="color: red; cursor: pointer; margin-right: 8px;float:right" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="35" height="35" fill="currentColor" class="bi bi-file-minus-fill remove-item-btn" viewBox="0 0 16 16" onclick="removeItem({{$item_index}})"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1" /></svg> 
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label font-14 font-heading">Item *</label>
                                        <select name="items[{{$item_index}}][item_id]" class="form-select select2-single" required>
                                            <option value="">Select Item</option>
                                            @if(isset($groups))
                                                @foreach($groups as $group)
                                                    @if($group->items->count() > 0)
                                                        <optgroup label="{{ $group->name }}">
                                                            @foreach($group->items as $item)
                                                                <option value="{{ $item->id }}" @if($value->item_id==$item->id) selected @endif>{{ $item->name }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="price_{{$item_index}}" class="form-label font-14 font-heading">Price *</label>
                                        <input type="number" name="items[{{$item_index}}][price]" class="form-control" placeholder="Enter Price" required id="price_{{$item_index}}" step="0.01" min="0" value="{{$value->price}}">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="bill_price_{{$item_index}}" class="form-label font-14 font-heading">Bill Price <input type="checkbox" class="bill_price_check" data-id="{{$item_index}}" value=""></label>
                                        <input type="text" id="bill_price_{{$item_index}}" class="form-control bill_price" name="items[{{$item_index}}][bill_price]" placeholder="Enter Price" readonly data-id="{{$item_index}}" value="{{$value->bill_price}}">
                                        <ul style="color: red;">
                                            @error('date'){{$message}}@enderror
                                        </ul> 
                                    </div>
                                    
                                        
                                    
                                    <div class="col-md-3 mb-3">
                                        <label for="unit_{{$item_index}}" class="form-label font-14 font-heading">Unit *</label>
                                        <select name="items[{{$item_index}}][unit]" class="form-select unit" required id="unit_{{$item_index}}" data-id="{{$item_index}}">
                                            <option value="">Select Unit</option>
                                            @if(isset($units))
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}" data-name="{{ $unit->name }}" data-unit_type="{{ $unit->unit_type }}" @if($value->unit==$unit->id) selected @endif>{{ $unit->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="sub_unit_{{$item_index}}" class="form-label font-14 font-heading">Sub Unit *</label>
                                        <select id="sub_unit_{{$item_index}}" name="items[{{$item_index}}][sub_unit]" class="form-select sub_unit" data-id="{{$item_index}}" required autofocus>
                                            <option value="">Select Sub Unit</option>
                                            <option value="INCH" @if($value->sub_unit=="INCH") selected @endif>INCH</option>
                                            <option value="CM" @if($value->sub_unit=="CM") selected @endif>CM</option>
                                            <option value="MM" @if($value->sub_unit=="MM") selected @endif>MM</option>
                                        </select>
                                        <ul style="color: red;">
                                            @error('voucher_no'){{$message}}@enderror
                                        </ul> 
                                    </div>
                                </div>

                                <!-- GSM / Sizes / Reels -->
                                <div class="row" id="dynamic_gsm_{{$item_index}}">
                                    @php $gsm_index = 1;@endphp
                                    @foreach($value->gsms as $k1 => $gsm)
                                        <div class="col-md-3 gsm-block" id="gsm_block_{{$item_index}}_{{$gsm_index}}">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <td style="width: 40%;">GSM
                                                        @if($gsm_index==1)
                                                            <svg style="color: green;cursor:pointer;" class="bg-primary rounded-circle add_gsm" data-item_id="{{$item_index}}" data-gsm_id="{{count($value->gsms)}}" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/></svg>
                                                        @else
                                                            <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove-gsm-btn" viewBox="0 0 16 16" onclick="removeGsm({{$item_index}},{{$gsm_index}})"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1" /></svg>
                                                        @endif
                                                        
                                                    
                                                    </td>
                                                    <td><input type="text" name="items[{{$item_index}}][gsms][{{$gsm_index}}][gsm]" class="form-control gsm gsm_{{$gsm_index}}" placeholder="Enter GSM" required value="{{$gsm->gsm}}" data-item_id="{{$item_index}}" data-gsm_id="{{$gsm_index}}"></td>
                                                </tr>
                                            </table>
                                            <table class="table table-bordered" id="table_{{$item_index}}_{{$gsm_index}}">
                                                <tr>
                                                    <td>SIZES</td>
                                                    <td class="qty_title_{{$gsm_index}}">{{$value->unitMaster->s_name}}</td>
                                                </tr>
                                                @php $row_index = 0;@endphp
                                                @foreach($gsm->details as $k2 => $detail)
                                                    <tr>
                                                        <td><input type="text" name="items[{{$item_index}}][gsms][{{$gsm_index}}][details][{{ $k2 }}][size]" class="form-control size size_{{$item_index}}_{{$gsm_index}}" placeholder="SIZES" onkeyup="approxCalculation({{$item_index}},{{$gsm_index}})" value="{{$detail->size}}"></td>
                                                        <td><input type="number" name="items[{{$item_index}}][gsms][{{$gsm_index}}][details][{{ $k2 }}][reel]" class="form-control quantity quantity_{{$item_index}}_{{$gsm_index}}" placeholder="REELS" data-item_id="{{$item_index}}" data-gsm_id="{{$gsm_index}}" data-quantity_id="{{ $k2 }}" onkeyup="approxCalculation({{$item_index}},{{$gsm_index}})" value="{{$detail->quantity}}"></td>
                                                    </tr>
                                                    @php $row_index++; @endphp
                                                @endforeach
                                                <tr>
                                                    <th style="text-align: center">Total</th>
                                                    <td>
                                                        <input type="number" class="form-control quantity_total" id="quantity_total_{{$item_index}}_{{$gsm_index}}" placeholder="0" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th style="text-align: center">Approx Qty</th>
                                                    <td>
                                                        <input type="number" class="form-control approx" id="approx_qty_{{$item_index}}_{{$gsm_index}}" placeholder="0" readonly>
                                                    </td>
                                                </tr>
                                            </table>
                                            <span class="add_row" data-item="{{$item_index}}" data-gsm="{{$gsm_index}}" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
                                        </div>
                                        @php $gsm_index++;@endphp
                                    @endforeach
                                </div>
                            </div>
                            @php $item_index++ @endphp
                        @endforeach
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
    $(".quantity").each(function(){
        $(this).trigger('keyup');
    });     
    $(".gsm").each(function(){
        var item_id = $(this).data("item_id");
        var gsm_id = $(this).data("gsm_id");
        approxCalculation(item_id,gsm_id);
    });
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
        newSection.find(".freight_div").remove("");
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
                        <td><input type="text" name="items[${itemIndex}][gsms][1][gsm]" class="form-control gsm gsm_${itemIndex}" placeholder="Enter GSM" required data-item_id="${itemIndex}" data-gsm_id="1"></td>
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
                        <td><input type="text" name="items[${item_id}][gsms][${gsm_id}][gsm]" class="form-control gsm gsm_${item_id}" placeholder="Enter GSM" required data-item_id="${item_id}" data-gsm_id="${gsm_id}"></td>
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
</script>
@endsection