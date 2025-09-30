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
                    <!-- Items Container -->
                    <div id="items_container">
                        <div class="item-section border rounded p-2 mb-3 position-relative" id="item_section_1">
                            <svg style="color: red; cursor: pointer; margin-right: 8px;float:right" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="35" height="35" fill="currentColor" class="bi bi-file-minus-fill remove-item-btn" viewBox="0 0 16 16" onclick="removeItem(1)"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1" /></svg> 
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label>Item *</label>
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
                                    <label>Price *</label>
                                    <input type="number" name="items[1][price]" class="form-control" placeholder="Enter Price" required>
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="bill_price_1" class="form-label font-14 font-heading">Bill Price <input type="checkbox" class="bill_price_check" data-id="1" value="1"></label>
                                    <input type="text" id="bill_price_1" class="form-control bill_price" name="items[1][bill_price]" placeholder="Enter Price" readonly data-id="1">
                                    <ul style="color: red;">
                                        @error('date'){{$message}}@enderror
                                     </ul> 
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="freight" class="form-label font-14 font-heading freight_div">Freight *</label>
                                    <select id="freight" name="freight" class="form-select freight_div" required autofocus>
                                        <option value="">Select Freight</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                    <ul style="color: red;">
                                        @error('voucher_no'){{$message}}@enderror
                                    </ul> 
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label>Unit *</label>
                                    <select name="items[1][unit]" class="form-select" required>
                                        <option value="">Select Unit</option>
                                        @if(isset($units))
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
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
                                            <td><input type="text" name="items[1][gsms][1][gsm]" class="form-control" placeholder="Enter GSM" required></td>
                                        </tr>
                                    </table>
                                    <table class="table table-bordered" id="table_1_1">
                                        <tr>
                                            <td>SIZES</td>
                                            <td>REELS</td>
                                        </tr>
                                        @php $row_index = 0;@endphp
                                        @while ($row_index < 5) 
                                            <tr>
                                                <td><input type="text" name="items[1][gsms][1][details][{{ $row_index }}][size]" class="form-control" placeholder="SIZES" ></td>
                                                <td><input type="number" name="items[1][gsms][1][details][{{ $row_index }}][reel]" class="form-control" placeholder="REELS" ></td>
                                            </tr>
                                            @php $row_index++; @endphp
                                            
                                        @endwhile
                                        {{-- <tr>
                                            <td><input type="text" name="items[1][gsms][1][details][0][size]" class="form-control" placeholder="SIZES" required></td>
                                            <td><input type="number" name="items[1][gsms][1][details][0][reel]" class="form-control" placeholder="REELS" required></td>
                                        </tr> --}}
                                    </table>
                                    <span class="add_row" data-item="1" data-gsm="1" style="color:#3c8dbc;cursor:pointer;">Add Row</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p><svg id="add_item_btn" 
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
        newSection.find(".bill_price").attr("data-id",itemIndex);
        newSection.find(".freight_div").remove("");
        let detailsRows = '';
        for (let row_index = 0; row_index < 5; row_index++) {
            detailsRows += `
                <tr>
                    <td>
                        <input type="text" name="items[${itemIndex}][gsms][1}][details][${row_index}][size]" class="form-control" placeholder="SIZES" >
                    </td>
                    <td>
                        <input type="number" name="items[${itemIndex}][gsms][1][details][${row_index}][reel]" class="form-control" placeholder="REELS" >
                    </td>
                </tr>
            `;
        }
        newSection.find("[id^='dynamic_gsm_']").attr("id", "dynamic_gsm_" + itemIndex).html(`
            <div class="col-md-3 gsm-block" id="gsm_block_${itemIndex}_1">
                <table class="table table-bordered">
                    <tr>
                        <td style="width: 40%;">GSM
                           <svg style="color: green;cursor:pointer;" class="bg-primary rounded-circle add_gsm" data-item_id="${itemIndex}" data-gsm_id="1" width="24" height="24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M11 19V13H5V11H11V5H13V11H19V13H13V19H11Z" fill="white"/></svg>
                        </td>
                        <td><input type="text" name="items[${itemIndex}][gsms][1][gsm]" class="form-control" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
                <table class="table table-bordered" id="table_${itemIndex}_1">
                    <tr>
                        <td>SIZES</td>
                        <td>REELS</td>
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
        let detailsRows = '';
        for (let row_index = 0; row_index < 5; row_index++) {
            detailsRows += `
                <tr>
                    <td>
                        <input type="text" name="items[${item_id}][gsms][${gsm_id}][details][${row_index}][size]" class="form-control" placeholder="SIZES" >
                    </td>
                    <td>
                        <input type="number" name="items[${item_id}][gsms][${gsm_id}][details][${row_index}][reel]" class="form-control" placeholder="REELS" >
                    </td>
                </tr>
            `;
        }
        $("#dynamic_gsm_" + item_id).append(`
            <div class="col-md-3 gsm-block" id="gsm_block_${item_id}_${gsm_id}">
                <table class="table table-bordered">
                    <tr>
                        <td style="width:40%;">GSM
                            
                           <svg style="color: red; cursor: pointer; margin-right: 8px;" xmlns="http://www.w3.org/2000/svg" tabindex="0" width="24" height="24" fill="currentColor" class="bi bi-file-minus-fill remove-gsm-btn" viewBox="0 0 16 16" onclick="removeGsm(${item_id},${gsm_id})"><path d="M12 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M6 7.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1 0-1" /></svg>
                        </td>
                        <td><input type="text" name="items[${item_id}][gsms][${gsm_id}][gsm]" class="form-control" placeholder="Enter GSM" required></td>
                    </tr>
                </table>
                <table class="table table-bordered" id="table_${item_id}_${gsm_id}">
                    <tr>
                        <td>SIZES</td>
                        <td>REELS</td>
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
        let rowIndex = table.find("tr").length - 1; // exclude header row
        // table.find("tr").eq(1).before(`
        table.find("tbody").append(`
            <tr>
                <td><input type="text" name="items[${itemId}][gsms][${gsmId}][details][${rowIndex}][size]" class="form-control" placeholder="SIZES" ></td>
                <td><input type="number" name="items[${itemId}][gsms][${gsmId}][details][${rowIndex}][reel]" class="form-control" placeholder="REELS" ></td>
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
</script>
@endsection