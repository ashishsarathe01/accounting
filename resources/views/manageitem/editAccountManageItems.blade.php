@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
    .select2-selection{
        height:48px !important;
    }
    .select2-selection__rendered{
        line-height: 46px !important;
    }
    .select2-selection__arrow{
        height: 43px !important;
    }
 </style>
<div class="list-of-view-company ">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('layouts.leftnav')
            <!-- view-table-Content -->
            <div class="col-md-12 ml-sm-auto  col-lg-10 px-md-4 bg-mint">                
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Edit Manage Item
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-manage-item.update') }}">
                    @csrf
                    <input type="hidden" value="{{ $manageitems->id }}" id="mangeitem_id" name="mangeitem_id" />
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="mb-3 col-md-12"> <h5>PART A</h5></div>
                                <hr>
                                <div class="mb-3 col-md-5">
                                    <label for="name" class="form-label font-14 font-heading">ITEM NAME</label>
                                    <input type="text" class="form-control" name="name" id="name" value="{{ $manageitems->name }}" placeholder="ENTER ITEM NAME" autofocus required/>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-5">
                                    <label for="name" class="form-label font-14 font-heading">PRINT NAME</label>
                                    <input type="text" class="form-control" name="p_name" id="p_name" value="{{ $manageitems->p_name }}" placeholder="ENTER PRINT NAME" />
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-5" style="padding-bottom:10px;">
                                    <label for="name" class="form-label font-14 font-heading">UNDER GROUP</label>
                                    <select class="form-select form-select-lg select2-single" name="g_name" id="g_name" aria-label="form-select-lg example" required>
                                        <option value="">SELECT GROUP</option>
                                        <?php
                                        foreach ($itemGroups as $value) {
                                            $sel = '';
                                            if ($manageitems->g_name == $value->id)
                                                $sel = 'selected'; ?>?>
                                        <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->group_name; ?></option>
                                    <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                @foreach($series as $key=>$value)
                                    <div class="mb-3 col-md-3">
                                        <label for="" class="form-label font-14 font-heading">BRANCH</label>
                                        <input type="text" class="form-control" name="series[]" value="{{$value->series}}" readonly>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="" class="form-label font-14 font-heading">OPENING BAL. (Rs.)</label>
                                        <input type="text" class="form-control" id="opening_amount_{{$key}}" data-id="{{$key}}" name="opening_amount[]" placeholder="OPENING BALANCE"  onkeyup="typevalidation({{$key}})"; value="{{$value->opening_amount}}">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="" class="form-label font-14 font-heading">OPENING BAL. (Qty.)</label>
                                        <input type="text" class="form-control" id="opening_qty_{{$key}}" data-id="{{$key}}" name="opening_qty[]" placeholder="OPENING BALANCE" value="{{$value->opening_quantity}}">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="" class="form-label font-14 font-heading">
                                        BALANCE TYPE <span id="balance_type_required_{{$key}}" style="color:red; display:none;">*</span>
                                        </label>
                                        <select class="form-select form-select-lg" name="opening_balance_type[]" id="opening_balance_type_{{$key}}" data-id="{{$key}}" aria-label="form-select-lg example">
                                            <option value="">BALANCE TYPE</option>
                                            <option value="Debit" @if($value->type=="Debit") selected @endif>Debit</option>
                                            <option value="Credit" @if($value->type=="Credit") selected @endif>Credit</option>
                                        </select>
                                    </div>
                                @endforeach 
                                <div class="mb-3 col-md-3">
                                    <label for="name" class="form-label font-14 font-heading">UNIT NAME</label>
                                    <select class="form-select form-select-lg select2-single" id="u_name" name="u_name" aria-label="form-select-lg example" required>
                                        <option value="">SELECT UNIT</option>
                                        <?php
                                        foreach ($accountunit as $value) {
                                            $sel = '';
                                            if ($manageitems->u_name == $value->id)
                                                $sel = 'selected'; ?>
                                            <option <?php echo $sel; ?> value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-3">
                                            <label class="form-label font-14 font-heading">GST RATE</label>
                                            <select class="form-select select2-single" id="gst_rate" name="gst_rate" required>
                                            <option value="">SELECT GST RATE</option>
                                        
                                            <option value="0" data-type="nil_rated"
                                                @if($manageitems->gst_rate == "0" && $manageitems->item_type == "nil_rated") selected @endif>
                                                0% (Nil Rated Goods)
                                            </option>
                                        
                                            <option value="0" data-type="exempted"
                                                @if($manageitems->gst_rate == "0" && $manageitems->item_type == "exempted") selected @endif>
                                                (Exempted Goods)
                                            </option>
                                        
                                            <option value="0.25" data-type="taxable" @if($manageitems->gst_rate == "0.25") selected @endif>0.25% (Precious stones, etc.)</option>
                                            <option value="3" data-type="taxable" @if($manageitems->gst_rate == "3") selected @endif>3% (Gold, jewelry)</option>
                                            <option value="5" data-type="taxable" @if($manageitems->gst_rate == "5") selected @endif>5%</option>
                                            <option value="12" data-type="taxable" @if($manageitems->gst_rate == "12") selected @endif>12%</option>
                                            <option value="18" data-type="taxable" @if($manageitems->gst_rate == "18") selected @endif>18%</option>
                                            <option value="28" data-type="taxable" @if($manageitems->gst_rate == "28") selected @endif>28%</option>
                                            
                                            </select>
                                        
                                        
                                            <!-- Hidden input to store data-type -->
                                        <input type="hidden" value="{{ $manageitems->item_type }}" name="item_type" id="item_type">
                                </div>

                                <div class="mb-3 col-md-3">
                                    <label for="name" class="form-label font-14 font-heading">HSN CODE</label>
                                    <input type="text" class="form-control" id="hsn_code" name="hsn_code" value="{{ $manageitems->hsn_code }}" placeholder="ENTER HSN CODE" />
                                </div>                        
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">STATUS</label>
                                    <select class="form-select form-select-lg select2-single" id="status" name="status" aria-label="form-select-lg example" required>
                                        <option value="">SELECT STATUS</option>
                                        <option <?php echo $manageitems->status == 1 ? 'selected' : ''; ?> value="1">Enable</option>
                                        <option <?php echo $manageitems->status == 0 ? 'selected' : ''; ?> value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5><input type="checkbox" name="partb" id="partb"> PART B</h5>
                            <hr>
                            <div class="row"> </div>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="submit" id="submit" class="btn  btn-xs-primary ">
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
<script type="text/javascript">
    $(document).ready(function(){ 
        $( ".select2-single, .select2-multiple" ).select2({ width: '100%' });
        $("#name").keyup(function(){
            $("#p_name").val($(this).val());
        });
        $("#partb").click(function(){
            $(".partb_div").hide();
            $("#section").val('');
            $("#rate_of_tcs").val('');
            $("#tcs_applicable").prop('checked',false);
            if($(this).prop('checked')==true){
                $(".partb_div").show();
                $(".tcs_applicable_div").hide();
            }
        });
        $("#tcs_applicable").click(function(){
            $(".tcs_applicable_div").hide();
            $("#section").val('').select2();
            $("#rate_of_tcs").val('');
            if($(this).prop('checked')==true){
                $(".tcs_applicable_div").show();
            }
        });
    });
    function typevalidation(key){
        $("#opening_balance_type_"+key).attr('required',false);
        $("#balance_type_required_"+key).hide();
        if($("#opening_amount_"+key).val()!=''){
            $("#opening_balance_type_"+key).attr('required',true);
            $("#balance_type_required_"+key).show();
        }
    }
   



$(document).ready(function() {

    const focusMap = {
        '#name': '#p_name',
        '#p_name': '#g_name',
        '#g_name': '#opening_balance',
        '#opening_balance': '#opening_balance_type',
        '#opening_balance_type': '#opening_balance_qty',
        '#opening_balance_qty': '#opening_balance_qt_type',
        '#opening_balance_qt_type': '#u_name',
        '#u_name': '#gst_rate',
        '#gst_rate': '#hsn_code',
        '#hsn_code': '#status',
        '#status': '#submit'
    };

    let currentSelect = null; // track open select2 id

    // Track which select2 is open
    $(document).on('select2:open', function(e) {
        currentSelect = $(e.target).attr('id');
    });

    // When select2 closes, move focus if Enter was pressed
    $(document).on('select2:close', function(e) {
        if (currentSelect) {
            const nextField = focusMap['#' + currentSelect];
            if (nextField) {
                setTimeout(function() {
                    $(nextField).focus();
                }, 100);
            }
            currentSelect = null; // reset
        }
    });

    // Prevent Enter key from submitting form anywhere
    $(document).on('keydown', 'form input, form select, .select2-search__field', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault(); // Always prevent form submit

            if ($(this).hasClass('select2-search__field')) {
                // If inside select2 search box
                if (currentSelect) {
                    $('#' + currentSelect).select2('close'); // Close it, select2:close will handle focus moving
                }
            } else {
                // Normal input/select fields
                const nextField = focusMap['#' + $(this).attr('id')];
                if (nextField) {
                    $(nextField).focus();
                }
            }
        }
    });

    // Special color change on Submit button focus
    $('#submit').on('focus', function() {
        $(this).css({
            'background-color': 'green',
            'color': 'white'
        });
    }).on('blur', function() {
        $(this).css({
            'background-color': '',
            'color': ''
        });
    });
});

  $(document).ready(function () {
        // Initialize Select2 (if not already initialized elsewhere)
        $('#gst_rate').select2();

        // Event listener for GST rate change
        $('#gst_rate').on('change', function () {
            // Get selected option
            var selectedOption = $(this).find('option:selected');
            
            // Get data-type attribute
            var gstType = selectedOption.data('type');
            
            // Set the value in hidden input
            $('#item_type').val(gstType);
        });
    });

    $(document).ready(function () {
        $('#gst_rate').on('change', function () {
            let selectedOption = $(this).find(':selected');
            let itemType = selectedOption.data('type') || '';
            $('#item_type').val(itemType);
        });

        // Trigger change to set initial value on page load (if needed)
        $('#gst_rate').trigger('change');
    }); 
</script>

@endsection