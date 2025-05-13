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
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">Add Manage Item</h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-manage-item.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="mb-3 col-md-12"> <h5>PART A</h5></div>
                                <hr>
                                <div class="mb-6 col-md-5">
                                    <label for="name" class="form-label font-14 font-heading">ITEM NAME</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="ENTER ITEM NAME" required autofocus/>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-5">
                                    <label for="name" class="form-label font-14 font-heading">PRINT NAME</label>
                                    <input type="text" class="form-control" name="p_name" id="p_name" placeholder="ENTER PRINT NAME" />
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-5" style="padding-bottom:10px;">
                                    <label for="name" class="form-label font-14 font-heading">UNDER GROUP</label>
                                    <select class="form-select form-select-lg select2-single" name="g_name" id="g_name" aria-label="form-select-lg example" required >
                                        <option value="">SELECT GROUP</option>
                                        <?php
                                        foreach ($itemGroups as $value) { ?>
                                            <option value="<?php echo $value->id; ?>"><?php echo $value->group_name; ?></option>
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
                                        <input type="text" class="form-control" id="opening_amount_{{$key}}" data-id="{{$key}}" name="opening_amount[]" placeholder="OPENING BALANCE"  onkeyup="typevalidation({{$key}})";/>
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="" class="form-label font-14 font-heading">OPENING BAL. (Qty.)</label>
                                        <input type="text" class="form-control" id="opening_qty_{{$key}}" data-id="{{$key}}" name="opening_qty[]" placeholder="OPENING BALANCE">
                                    </div>
                                    <div class="mb-3 col-md-3">
                                        <label for="" class="form-label font-14 font-heading">
                                        BALANCE TYPE <span id="balance_type_required_{{$key}}" style="color:red; display:none;">*</span>
                                        </label>
                                        <select class="form-select form-select-lg" name="opening_balance_type[]" id="opening_balance_type_{{$key}}" data-id="{{$key}}" aria-label="form-select-lg example">
                                            <option value="">BALANCE TYPE</option>
                                            <option value="Debit">Debit</option>
                                            <option value="Credit">Credit</option>
                                        </select>
                                    </div>
                                @endforeach                               
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-3">
                                    <label for="name" class="form-label font-14 font-heading">UNIT NAME</label>
                                    <select class="form-select form-select-lg select2-single " name="u_name" id="u_name" aria-label="form-select-lg example" required>
                                        <option value="">SELECT UNIT</option>
                                        <?php
                                        foreach ($accountunit as $value) { ?>
                                            <option value="<?php echo $value->id; ?>"><?php echo $value->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3 col-md-12"></div>
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">GST RATE</label>
                                    <input type="text" class="form-control" id="gst_rate" name="gst_rate" placeholder="ENTER GST RATE" required />
                                </div>
                                <div class="mb-3 col-md-3">
                                    <label for="name" class="form-label font-14 font-heading">HSN CODE</label>
                                    <input type="text" class="form-control" id="hsn_code" name="hsn_code" placeholder="ENTER HSN CODE" required />
                                </div>
                                <div class="mb-3 col-md-12"> </div>                      
                                <div class="mb-3 col-md-3">
                                    <label class="form-label font-14 font-heading">STATUS</label>
                                    <select class="form-select form-select-lg select2-single" name="status" id="status" aria-label="form-select-lg example" required>
                                        <option value="">SELECT STATUS</option>
                                        <option value="1">Enable</option>
                                        <option value="0">Disable</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h5><input type="checkbox" name="partb" id="partb"> PART B</h5>
                            <hr>
                            <div class="row">                                
                                <div class="mb-6 col-md-6 partb_div" style="display:none">
                                    <label for="name" class="form-label font-14 font-heading"><input type="checkbox" name="tcs_applicable" id="tcs_applicable"> TCS APPLICABLE </label>                               
                                </div>
                                <div class="mb-6 col-md-12"></div>
                                <div class="mb-6 col-md-6 partb_div tcs_applicable_div" style="display:none">
                                    <label for="name" class="form-label font-14 font-heading">SECTION</label>
                                    <select class="form-select form-select-lg select2-single " name="section" id="section" aria-label="form-select-lg example">
                                        <option value="">SELECT SECTION</option>
                                        <option value="206CE-Scarp" data-rate="1">206CE-Scarp</option>
                                    </select>
                                </div>
                                <div class="mb-6 col-md-6 partb_div tcs_applicable_div" style="display:none">
                                    <label for="name" class="form-label font-14 font-heading">RATE OF TCS</label>
                                    <input type="text" class="form-control" name="rate_of_tcs" id="rate_of_tcs" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-start">
                        <button type="submit" id="submit" class="btn  btn-xs-primary ">
                            SUBMIT
                        </button>
                    </div>
                </form>
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
        // Define mappings for moving focus
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

        // Handle Enter key on all inputs and selects
        $(document).on('keydown', 'input, select, .select2-search__field', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Stop form submit on Enter

                let currentId = $(this).attr('id');

                // Special case: if inside Select2 search box
                if ($(this).hasClass('select2-search__field')) {
                    currentId = $(this).closest('.select2-container').prev('select').attr('id');
                }

                const nextField = focusMap['#' + currentId];
                if (nextField) {
                    setTimeout(function() {
                        $(nextField).focus();
                    }, 100);
                }
            }
        });

        // Also handle select2:close to move focus when user selects or presses Enter
        $('.select2-single').on('select2:close', function(e) {
            const currentId = $(this).attr('id');
            const nextField = focusMap['#' + currentId];
            if (nextField) {
                setTimeout(function() {
                    $(nextField).focus();
                }, 100);
            }
        });

        // Submit button focus styling
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



</script>

@endsection