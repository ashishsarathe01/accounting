@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">
                <nav>
                    <ol class="breadcrumb m-0 py-4 px-2 px-md-0 font-12">
                        <li class="breadcrumb-item">Dashboard</li>
                        <img src="public/assets/imgs/right-icon.svg" class="px-1" alt="">
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Bill Sundry</li>
                    </ol>
                </nav>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Edit Bill Sundry
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-bill-sundry.update') }}">
                    @csrf
                    <input type="hidden" value="{{ $editbill->id }}" id="bill_id" name="bill_id" />
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $editbill->name }}" placeholder="Enter name" autofocus required/>
                        </div>
                        <div class="mb-4 col-md-4">
                           <label class="form-label font-14 font-heading">Nature Of Sundry</label>
                           <select class="form-select form-select-lg select2-single" name="nature_of_sundry" id="nature_of_sundry" aria-label="form-select-lg example" required onchange="showHideGstCalculation()">
                              <option value="">Select Nature Of Sundry</option>
                              <option value="CGST" data-sequence="1" @if($editbill->nature_of_sundry=='CGST') selected @endif>CGST</option>
                              <option value="SGST" data-sequence="2" @if($editbill->nature_of_sundry=='SGST') selected @endif>SGST</option>
                              <option value="IGST" data-sequence="3" @if($editbill->nature_of_sundry=='IGST') selected @endif>IGST</option>
                              <option value="TCS" data-sequence="4" @if($editbill->nature_of_sundry=='TCS') selected @endif>TCS/TDS</option>
                              <option value="TDS" data-sequence="5" @if($editbill->nature_of_sundry=='TDS') selected @endif>TDS</option>
                              <option value="OTHER" data-sequence="0" @if($editbill->nature_of_sundry=='OTHER') selected @endif>OTHER</option>
                           </select>
                           <input type="hidden" name="sequence" id="sequence" value="{{ $editbill->sequence }}">
                        </div>
                        

                    </div>
                    <div class="row">
                     <div class="mb-4 col-md-4">
                         <label for="name" class="form-label font-14 font-heading">Bill Sundry Type</label>
                         <select class="form-select form-select-lg select2-single" name="bill_sundry_type" id="bill_sundry_type" aria-label="form-select-lg example">
                             <option value="">Select </option>
                             <option <?php echo $editbill->bill_sundry_type == 'additive' ? 'selected' : ''; ?> value="additive">Additive</option>
                             <option <?php echo $editbill->bill_sundry_type == 'subtractive' ? 'selected' : ''; ?> value="subtractive">Subtractive</option>
                         </select>
                     </div>
                    </div>
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Adjust in Sale Amount</label>
                            <select class="form-select form-select-lg select2-single" name="adjust_sale_amt" id="adjust_sale_amt" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->adjust_sale_amt == 'Yes' ? 'selected' : ''; ?> value="Yes">Yes</option>
                                <option <?php echo $editbill->adjust_sale_amt == 'No' ? 'selected' : ''; ?> value="No">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">List Of Account</label>
                            <select class="form-select form-select-lg select2-single" name="sale_amt_account" id="sale_amt_account" aria-label="form-select-lg example" <?php echo $editbill->adjust_sale_amt =='No' ? '':'disabled';?>>
                                <option selected>Select </option>
                                <?php
                                foreach ($account as $value) {
                                    $sel='';
                                    if($editbill->sale_amt_account == $value->id) 
                                     $sel= 'selected';?>
                                    <option <?php echo $sel; ?>  value="<?php echo $value->id; ?>"><?php echo $value->account_name ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Adjust in Purchase Amount</label>
                            <select class="form-select form-select-lg select2-single" name="adjust_purchase_amt" id="adjust_purchase_amt" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->adjust_purchase_amt == 'Yes' ? 'selected' : ''; ?> value="Yes">Yes</option>
                                <option <?php echo $editbill->adjust_purchase_amt == 'No' ? 'selected' : ''; ?> value="No">No</option>
                            </select>
                        </div>
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">List Of Account</label>
                            <select class="form-select form-select-lg select2-single" name="purchase_amt_account" id="purchase_amt_account" aria-label="form-select-lg example" <?php echo $editbill->adjust_purchase_amt =='No' ? '':'disabled';?>>
                                <option selected>Select </option>
                                <?php
                                foreach ($account as $value) { 
                                    $sel='';
                                    if($editbill->purchase_amt_account == $value->id) 
                                     $sel= 'selected';?>
                                    <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->account_name; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                     
                     <div class="row">
                        
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg select2-single" name="status" id="status" aria-label="form-select-lg example">
                                <option value="">Select </option>
                                <option <?php echo $editbill->status == 1 ? 'selected' : ''; ?> value="1">Enable</option>
                                <option <?php echo $editbill->status == 2 ? 'selected' : ''; ?> value="2">Disable</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="submit" id="submit" class="btn btn-xs-primary ">
                            UPDATE
                        </button>
                    </div>
                </form>
            </div>
        </div>
</div>
</section>
</div>
</body>
@include('layouts.footer')
<script>
    // $(document).ready(function() {
    //     showHideGstCalculation();
    //     $("#adjust_sale_amt").change(function() {
    //         if ($("#adjust_sale_amt").val() == "Yes") {
    //             $("#sale_amt_account").prop('disabled', true);
    //         } else if ($("#adjust_sale_amt").val() == "No") {
    //             $("#sale_amt_account").prop('disabled', false);
    //         }
    //     });

    //     $("#adjust_purchase_amt").change(function() {
    //         if ($("#adjust_purchase_amt").val() == "Yes") {
    //             $("#purchase_amt_account").prop('disabled', true);
    //         } else if ($("#adjust_purchase_amt").val() == "No") {
    //             $("#purchase_amt_account").prop('disabled', false);
    //         }
    //     });
    // });
    function showHideGstCalculation(){
        var sequence = $("#nature_of_sundry option:selected").attr('data-sequence');
        $("#sequence").val(sequence);
    }
    $(document).ready(function () {
    // Initialize Select2
    $(".select2-single, .select2-multiple").select2({ width: '100%' });

    // Adjust custom height for Select2
    setTimeout(function () {
        const select2Ids = [
            '#purchase_amt_account',
            '#adjust_purchase_amt',
            '#adjust_sale_amt',
            '#sale_amt_account',
            '#nature_of_sundry',
            '#bill_sundry_type',
            '#status'
        ];
        select2Ids.forEach(function (id) {
            const container = $(id).next('.select2-container');
            container.find('.select2-selection--single').css({
                'height': '45px',
                'line-height': '45px'
            });
            container.find('.select2-selection__rendered').css({
                'line-height': '45px'
            });
            container.find('.select2-selection__arrow').css({
                'height': '45px'
            });
        });
    }, 100);

    // Sync sequence field
    $("#nature_of_sundry").change(function () {
        const sequence = $("#nature_of_sundry option:selected").attr('data-sequence');
        $("#sequence").val(sequence);
    });

    // Enable/Disable dropdowns
    $("#adjust_sale_amt").change(function () {
        const isYes = $(this).val() === "Yes";
        $("#sale_amt_account").prop('disabled', isYes).trigger('change.select2');
    });

    $("#adjust_purchase_amt").change(function () {
        const isYes = $(this).val() === "Yes";
        $("#purchase_amt_account").prop('disabled', isYes).trigger('change.select2');
    });

    // Prevent opening Select2 if disabled
    $('#sale_amt_account, #purchase_amt_account').on('select2:opening', function (e) {
        if ($(this).prop('disabled')) {
            e.preventDefault();
        }
    });

    // Focus map
    const focusMap = {
        '#name': '#nature_of_sundry',
        '#nature_of_sundry': '#bill_sundry_type',
        '#bill_sundry_type': '#adjust_sale_amt',
        '#adjust_sale_amt': '#sale_amt_account',
        '#sale_amt_account': '#adjust_purchase_amt',
        '#adjust_purchase_amt': '#purchase_amt_account',
        '#purchase_amt_account': '#status',
        '#status': '#submit'
    };

    // Enter key navigation with skip if disabled
    $(document).on('keydown', 'input, select, .select2-search__field', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let currentId = $(this).attr('id');
            if ($(this).hasClass('select2-search__field')) {
                currentId = $(this).closest('.select2-container').prev('select').attr('id');
            }

            let nextField = focusMap['#' + currentId];

            // Skip disabled fields
            while (nextField && $(nextField).is(':disabled')) {
                nextField = focusMap[nextField];
            }

            if (nextField) {
                setTimeout(function () {
                    $(nextField).focus();
                }, 100);
            }
        }
    });

    // Move focus when Select2 closes (with skip if disabled)
    $('.select2-single').on('select2:close', function () {
        const currentId = $(this).attr('id');
        let nextField = focusMap['#' + currentId];

        while (nextField && $(nextField).is(':disabled')) {
            nextField = focusMap[nextField];
        }

        if (nextField) {
            setTimeout(function () {
                $(nextField).focus();
            }, 100);
        }
    });

    // Highlight submit button on focus
    $('#submit').on('focus', function () {
        $(this).css({ 'background-color': 'green', 'color': 'white' });
    }).on('blur', function () {
        $(this).css({ 'background-color': '', 'color': '' });
    });
});


</script>
@endsection