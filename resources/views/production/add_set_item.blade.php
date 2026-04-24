@extends('layouts.app')
@section('content')
@include('layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
 {{-- Alerts --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('csv_summary'))
                    <div class="alert alert-info">
                        <strong>{{ session('csv_summary') }}</strong>
                    </div>
                @endif

                @if (session('csv_errors') && count(session('csv_errors')) > 0)
                    <div class="alert alert-danger">
                        <h5>CSV Errors:</h5>
                        <ul>
                            @foreach(session('csv_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg') }}"
                            class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Production</li>
                        <img src="{{ URL::asset('public/assets/imgs/right-icon.svg') }}"
                            class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Add Set Item</li>
                    </ol>
                </nav>

                <div class="bg-white p-4 shadow-sm border-radius-8">
                    <form action="{{ route('production.set_item.store') }}" method="POST" enctype="multipart/form-data"> 
                        @csrf

                        <div class="item-section border rounded p-3 mb-3 position-relative">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label>Item *</label>
                                    <select name="item_id" id="item_id" class="form-select select2-single" required>
                                        <option value="">Select Item</option>
                                        @foreach($groups as $group)
                                            @if($group->items->count() > 0)
                                                <optgroup label="{{ $group->name }}">
                                                    @foreach($group->items as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>BF *</label>
                                    <input type="number" id="bf" name="bf" class="form-control" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>GSM *</label>
                                    <input type="number" id="gsm" name="gsm" class="form-control" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Speed *</label>
                                    <input type="number" name="speed" class="form-control" required>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label>Status</label>
                                    <select name="status" class="form-select">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="seriesContainer" class="row"></div>

                        <div class="row mt-3" id="reelButtons" style="display:none;">
                            <div class="col-md-12 d-flex gap-2">
                                <button type="button" class="btn btn-success" id="importCsvBtn">
                                    Import CSV (Reel-wise)
                                </button>

                                <button type="button" class="btn btn-secondary" id="addReelBtn">
                                    Add Reel Manually
                                </button>
                            </div>
                        </div>

                        <div id="csvUploadSection" class="mt-3" style="display:none;">
                                <div class="mb-3">
                                    <label for="csv_file" class="form-label">Select CSV File:</label>
                                    <input type="file" name="csv_file" id="csv_file" class="form-control" accept=".csv">
                                </div>
                        </div>

                        <div id="manualReelSection" style="display:none;">

                            
                            <div id="reelContainer" class="m-2">

                                <div class="reel-group border rounded p-3 mb-3 position-relative bg-white">
                                    <button type="button"
                                        class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2 remove-reel py-0 px-2"
                                        style="display:none;">×</button>

                                    <div class="row ">
                                        <div class="col-md-2">
                                            <label>Item Name</label>
                                            <select name="reels[0][item_id]" class="form-select item-select"
                                                data-type="item">

                                                <option value="{{ $item->id }}" 
                                                        data-bf="{{ $item->bf }}" 
                                                        data-gsm="{{ $item->gsm }}">
                                                    {{ $item->name }}
                                                </option>

                                            </select>
                                        </div>

                                        <div class="col-md-1">
                                            <label>BF</label>
                                            <input type="text" name="reels[0][bf]" class="form-control bf"
                                                data-type="bf">
                                        </div>

                                        <div class="col-md-1">
                                            <label>GSM</label>
                                            <input type="text" name="reels[0][gsm]" class="form-control gsm"
                                                data-type="gsm">
                                        </div>

                                        <div class="col-md-2">
                                            <label>Unit</label>
                                            <select name="reels[0][unit]" class="form-select unit" data-type="unit" >
                                                <option value="">Select</option>
                                                <option value="INCH" selected>INCH</option>
                                                <option value="CM">CM</option>
                                                <option value="MM">MM</option>
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <label>Reel No</label>
                                            <input type="number" name="reels[0][reel_no]" class="form-control reel-no"
                                                 placeholder="Enter Reel No.">
                                            <small class="text-danger reel-error d-none">Reel number already
                                                exists!</small>
                                        </div>

                                        <div class="col-md-2">
                                            <label>Size</label>
                                            <input type="text" name="reels[0][size]" class="form-control" 
                                                placeholder="Enter Size">
                                        </div>

                                        <div class="col-md-2">
                                            <label>Weight</label>
                                            <input type="number" name="reels[0][weight]" class="form-control weight"
                                                 placeholder="Enter Weight">
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="mb-3 text-end">
                                <strong>Total Weight: <span id="totalWeight">0</span></strong>
                            </div>

                            <div class="mb-3 text-end">
                                <button type="button" id="addMoreReel" class="btn btn-primary btn-sm">+ Add Another
                                    Reel</button>
                            </div>

                        </div>


                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">Save Item</button>
                            <a href="{{ route('production.set_item') }}"
                                class="btn btn-dark ms-2">Quit</a>
                        </div>
                    </form>
                                            <!-- Modal -->
                        <div class="modal fade" id="weightMismatchModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            
                            <div class="modal-header">
                                <h5 class="modal-title">Weight Mismatch</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                Total Weight (<b id="modalTotalWeight"></b>) does not match Opening Quantity 
                                (<b id="modalOpeningQty"></b>).  
                                <br><br>
                                Would you like to update Opening Quantity to match Total Weight?
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancel
                                </button>

                                <button type="button" class="btn btn-primary" id="updateOpeningQtyBtn">
                                Update Opening Qty
                                </button>
                            </div>

                            </div>
                        </div>
                        </div>

                        <div class="modal fade" id="weightMismatchModalcsv" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            
                            <div class="modal-header">
                                <h5 class="modal-title">Weight Mismatch</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                Total Weight (<b id="modalTotalWeightcsv"></b>) does not match Opening Quantity 
                                (<b id="modalOpeningQtycsv"></b>).  
                                <br><br>
                                Would you like to update Opening Quantity to match Total Weight?
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" id="cancelupdate" data-bs-dismiss="modal">
                                Cancel
                                </button>

                                <button type="button" class="btn btn-primary" id="updateOpeningQtyBtncsv">
                                Update Opening Qty
                                </button>
                            </div>

                            </div>
                        </div>
                        </div>

                </div>

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')
<script>
    $(document).on("change", "#item_id", function () {

        let itemID = $(this).val();

        if (itemID === "") {
            $("#seriesContainer").html("");
            $("#reelButtons").hide();
            return;
        }

        $.ajax({
            url: "{{ route('production.item.fetch', '') }}/" +
            itemID,
            type: "GET",
            success: function (res) {

                $("#seriesContainer").html(""); // clear existing

                $.each(res.series, function (key, value) {

                    let html = `
                <div class="mb-3 col-md-3">
                    <label class="form-label font-14 font-heading">BRANCH</label>
                    <input type="text" class="form-control" name="series[]" value="${value.series}" readonly>
                    
                </div>

                <div class="mb-3 col-md-3">
                    <label class="form-label font-14 font-heading">OPENING BAL. (Qty.)</label>
                    <input type="text" class="form-control" 
                           id="opening_qty_${key}" 
                           data-id="${key}" 
                           name="opening_qty[]" 
                           placeholder="OPENING BALANCE"
                           value="${value.opening_quantity ?? ''}">
                </div>
                      <div class="mb-3 col-md-3">
                    <label class="form-label font-14 font-heading">OPENING BAL. (Rs.)</label>
                    <input type="text" class="form-control" 
                           id="opening_amount_${key}" 
                           data-id="${key}" 
                           name="opening_amount[]" 
                           placeholder="OPENING BALANCE" 
                           value="${value.opening_amount ?? ''}">
                </div>
                  <div class="mb-3 col-md-3">
                    <select type="hidden" class="form-select form-select-lg" 
                            name="opening_balance_type[]" 
                            id="opening_balance_type_${key}" 
                            data-id="${key}">
                        <option value="">BALANCE TYPE</option>
                        <option value="Debit" selected>Debit</option>
                        <option value="Credit">Credit</option>
                    </select>
                </div>
               
                `;

                    $("#seriesContainer").append(html);
                });

                $("#reelButtons").show();


            }
        });
    });


    let reelIndex = 0;

    // Show manual reel section
   
$(document).on("click", "#addReelBtn", function () {

    $("#manualReelSection").show();
    $("#csvUploadSection").slideUp();
    $("#csv_file").val("");
    $("#addMoreReel").show();


    // If reelContainer is empty, recreate the first reel group
    if ($("#reelContainer .reel-group").length === 0) {
        const firstGroup = `
        <div class="reel-group border rounded p-3 mb-3 position-relative bg-white">
            <button type="button"
                class="btn btn-outline-danger btn-sm position-absolute top-0 end-0 m-2 remove-reel py-0 px-2"
                style="display:none;">×</button>

            <div class="row ">
                <div class="col-md-2">
                    <label>Item Name</label>
                    <select name="reels[0][item_id]" class="form-select item-select" data-type="item"></select>
                </div>

                <div class="col-md-1">
                    <label>BF</label>
                    <input type="text" name="reels[0][bf]" class="form-control bf" data-type="bf">
                </div>

                <div class="col-md-1">
                    <label>GSM</label>
                    <input type="text" name="reels[0][gsm]" class="form-control gsm" data-type="gsm">
                </div>

                <div class="col-md-2">
                    <label>Unit</label>
                    <select name="reels[0][unit]" class="form-select unit" data-type="unit" >
                        <option value="">Select</option>
                        <option value="INCH" selected>INCH</option>
                        <option value="CM">CM</option>
                        <option value="MM">MM</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Reel No</label>
                    <input type="number" name="reels[0][reel_no]" class="form-control reel-no" >
                    <small class="text-danger reel-error d-none">Reel number already exists!</small>
                </div>

                <div class="col-md-2">
                    <label>Size</label>
                    <input type="text" name="reels[0][size]" class="form-control" >
                </div>

                <div class="col-md-2">
                    <label>Weight</label>
                    <input type="number" name="reels[0][weight]" class="form-control weight">
                </div>
            </div>
        </div>
        `;

        $("#reelContainer").append(firstGroup);
        let mainItem = $('#item_id option:selected');
    let itemId = mainItem.val();
    let itemName = mainItem.text().trim();
    let itemBF = $("#bf").val();
    let itemGSM = $("#gsm").val();
   
    $(".item-select").html(
        `<option value="${itemId}" selected data-bf="${itemBF}" data-gsm="${itemGSM}">
            ${itemName}
        </option>`
    );

    $(".bf:first").val(itemBF);
    $(".gsm:first").val(itemGSM);
    }

    // Now auto-fill item, BF, GSM again
    let mainItem = $('#item_id option:selected');
    let itemId = mainItem.val();
    let itemName = mainItem.text().trim();
    let itemBF = $("#bf").val();
    let itemGSM = $("#gsm").val();
   
    $(".item-select").html(
        `<option value="${itemId}" selected data-bf="${itemBF}" data-gsm="${itemGSM}">
            ${itemName}
        </option>`
    );

    $(".bf:first").val(itemBF);
    $(".gsm:first").val(itemGSM);
});


    
    // Auto-fill BF & GSM when item changes


    // Append "X GSM" after typing size
    $(document).on("blur", '.reel-group input[name*="[size]"]', function () {
        const group = $(this).closest('.reel-group');
        const gsmValue = group.find('.gsm').val();
        let sizeVal = $(this).val().trim();

        sizeVal = sizeVal.replace(/\s*x\s*\d*$/i, '');

        if (gsmValue && sizeVal !== '') {
            $(this).val(sizeVal + 'X' + gsmValue);
        }
    });

    // Allow Enter key
    $(document).on("keypress", '.reel-group input[name*="[size]"]', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            $(this).blur();
        }
    });

    // Duplicate reel check (client + server)
    $(document).on("keyup change", ".reel-no", function () {
        const reelInput = $(this);
        const reelNo = reelInput.val().trim();
        const errorText = reelInput.siblings(".reel-error");

        const allReels = $(".reel-no")
            .map(function () {
                return $(this).val();
            })
            .get();

        const duplicates = allReels.filter((v, i, a) => v && a.indexOf(v) !== i);

        if (duplicates.includes(reelNo)) {
            errorText.text("Duplicate reel number on this page!").removeClass("d-none");
            reelInput.addClass("is-invalid");
            return;
        }

        if (reelNo) {
            $.get('{{ route("stock.checkReel") }}', {
                reel_no: reelNo
            }, function (res) {
                if (res.exists) {
                    errorText.text("Reel number already exists in database!").removeClass("d-none");
                    reelInput.addClass("is-invalid");
                } else {
                    errorText.addClass("d-none");
                    reelInput.removeClass("is-invalid");
                }
            });
        }
    });

    // Add new reel block
    $("#addMoreReel").on("click", function () {
        reelIndex++;

        const lastGroup = $("#reelContainer .reel-group:last"); // last reel group
        const newGroup = lastGroup.clone(); // clone it

        newGroup.find("input, select").each(function () {
            const name = $(this).attr("name");
            if (name) {
                $(this).attr("name", name.replace(/\[\d+\]/, `[${reelIndex}]`));
            }

            if ($(this).hasClass("bf") ||
                $(this).hasClass("gsm") ||
                $(this).hasClass("item-select")) {
                let firstItem = $(".item-select:first").find(":selected");

                $(this).html(
                    `<option value="${firstItem.val()}" data-bf="${firstItem.data("bf")}" data-gsm="${firstItem.data("gsm")}"  selected>
                    ${firstItem.text()}
                </option>`
                );
            } else if ($(this).hasClass("unit")) {
                    // Keep previous selected unit
                    $(this).val(lastGroup.find(".unit").val());
                }
                else {
                    $(this).val("");
            }
        });

        newGroup.find(".remove-reel").show();
        $("#reelContainer").append(newGroup);

        newGroup.find(".item-select").trigger("change");
    });

    // Remove reel
    $(document).on("click", ".remove-reel", function () {
        $(this).closest(".reel-group").remove();
        calculateTotalWeight();
    });

    // Total Weight Calculation
    $(document).on("input", ".weight", function () {
        calculateTotalWeight();
    });

    function calculateTotalWeight() {
        let total = 0;

        $(".weight").each(function () {
            const w = parseFloat($(this).val());
            if (!isNaN(w)) total += w;
        });

        $("#totalWeight").text(total.toFixed(2));
    }

    $(document).on("click", "#importCsvBtn", function () {
        $("#csvUploadSection").slideDown();

        // Remove all manually added reels
        $("#reelContainer").empty();

        // Reset total weight
        $("#totalWeight").text("0");

        // Hide add more reel button
        $("#addMoreReel").hide();
        });

$("form").on("submit", function(e) {

    toggleReelValidation(); // ensure correct validation state

    let totalOpeningQty = 0;

    $("input[name='opening_qty[]']").each(function() {
        let v = parseFloat($(this).val()) || 0;
        totalOpeningQty += v;
    });

    if (totalOpeningQty <= 0) {

        // Remove reel validation
        $("#reelContainer")
            .find("input, select")
            .prop("required", false);

        // Remove reel names so nothing is submitted
        $("input[name^='reels'], select[name^='reels']")
            .removeAttr("name");

        return true; // allow submit
    }

    let totalWeight = parseFloat($("#totalWeight").text()) || 0;

    if ($("#manualReelSection").is(":visible") && totalWeight !== totalOpeningQty) {

        e.preventDefault();

        $("#modalTotalWeight").text(totalWeight);
        $("#modalOpeningQty").text(totalOpeningQty);

        let modal = new bootstrap.Modal(
            document.getElementById('weightMismatchModal')
        );
        modal.show();
    }
});
// When user clicks UPDATE inside modal
$(document).on("click", "#updateOpeningQtyBtn", function () {
    $(this).data("confirmed", true);
    let totalWeight = parseFloat($("#totalWeight").text()) || 0;

    // update all opening_qty fields
    $("input[name='opening_qty[]']").val(totalWeight);
    $("#weightMismatchModal").modal("hide");
    
});

// Auto-upload CSV when user selects file
$(document).on("change", "#csv_file", function () {

    let file = this.files[0];

    if (!file) return;

    let formData = new FormData();
    formData.append("csv_file", file);
    formData.append("_token", "{{ csrf_token() }}");
    formData.append("selected_item", $("#item_id option:selected").text().trim());

    $.ajax({
        url: "{{ route('production.csv.readWeight') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        success: function (res) {

    if (!res.success) {
        alert(res.message);
        $("#csv_file").val(""); // clear file
        $("#totalWeight").text("0");
        return;
    }

    let csvWeight = parseFloat(res.total_weight);
    let openingQty = 0;

    $("input[name='opening_qty[]']").each(function () {
        openingQty += parseFloat($(this).val() || 0);
    });

    $("#totalWeight").text(csvWeight.toFixed(2));

    if (csvWeight === openingQty) {
        return;
    }

    $("#modalTotalWeightcsv").text(csvWeight);
    $("#modalOpeningQtycsv").text(openingQty);

    let modal = new bootstrap.Modal(document.getElementById('weightMismatchModalcsv'));
    modal.show();
}

    });
});
$(document).on("click", "#updateOpeningQtyBtncsv", function () {
    let totalWeight = parseFloat($("#totalWeight").text()) || 0;

    $("input[name='opening_qty[]']").val(totalWeight);

    $("#weightMismatchModalcsv").modal("hide");
});
$(document).on("click", "#cancelupdate", function () {
    if (!$("#updateOpeningQtyBtn").data("confirmed")) {
        $("#csv_file").val("");      // Clear CSV
        $("#totalWeight").text("0"); // Reset weight
    }
});


function validateOpeningBalanceAndResetReels() {

    let totalOpeningQty = 0;

    $("input[name='opening_qty[]']").each(function () {
        let value = parseFloat($(this).val());
        if (!isNaN(value)) {
            totalOpeningQty += value;
        }
    });

    // ❌ If no opening balance
    if (totalOpeningQty <= 0) {

        // Hide manual section
        $("#manualReelSection").hide();

        // Clear all reels
        $("#reelContainer").empty();

        // Reset total weight
        $("#totalWeight").text("0");

        // Remove required from reel fields (if any exist)
        $("#manualReelSection")
            .find("input, select")
            .prop("required", false);

        // Hide reel buttons
        $("#reelButtons").hide();

        return false;
    }

    // ✅ If opening balance exists
    $("#reelButtons").show();
    return true;
}  

$(document).on("input", "input[name='opening_qty[]']", function () {
    validateOpeningBalanceAndResetReels();
});

function toggleReelValidation() {

    let totalOpeningQty = 0;

    $("input[name='opening_qty[]']").each(function () {
        let value = parseFloat($(this).val());
        if (!isNaN(value)) {
            totalOpeningQty += value;
        }
    });

    if (totalOpeningQty > 0 && $("#manualReelSection").is(":visible")) {

        // Enable required only when needed
        $("#reelContainer")
            .find("input[name*='[reel_no]'], input[name*='[size]'], input[name*='[weight]'], select[name*='[unit]']")
            .prop("required", true);

    } else {

        // Disable required
        $("#reelContainer")
            .find("input, select")
            .prop("required", false);
    }
}

// When opening balance changes
$(document).on("input", "input[name='opening_qty[]']", function () {
    validateOpeningBalanceAndResetReels();
    toggleReelValidation();
});

// When manual section opens
$(document).on("click", "#addReelBtn", function () {
    setTimeout(function(){
        toggleReelValidation();
    }, 100);
});

</script>

@endsection
