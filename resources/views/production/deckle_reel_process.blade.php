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
                @if ($errors->any())
                    <div class="alert alert-danger d-flex align-items-center" style="height: 48px;">
                        @foreach ($errors->all() as $error)
                            <p class="mb-0">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success" style="height: 48px; display: flex; align-items: center;">
                        <p class="mb-0">{{ session('success') }}</p>
                    </div>
                @endif
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        Completed Pop Roll
                    </h5>
                    <button class="btn btn-info start_btn">Start Pop Roll</button>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table  class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Deckle No.</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Start Time</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">End Time</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Quality</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deckles as $key => $deckle)
                                <tr>
                                    <td>{{$deckle->deckle_no}}</td>
                                    <td>{{$deckle->start_time_stamp}}</td>
                                    <td>{{$deckle->end_time_stamp}}</td>
                                    <td>
                                        <table class="table table-borderd">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>BF</th>
                                                    <th>GSM</th>
                                                    <th>PRODUCTION IN KG</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>{{$deckle->name}}</td>
                                                    <td>{{$deckle->bf}}</td>
                                                    <td>{{$deckle->gsm}}</td>
                                                    <td>{{$deckle->production_in_kg}}</td>
                                                </tr>
                                                @foreach($deckle->quality as $key => $quality)
                                                    <tr>
                                                        <td>{{$quality->name}}</td>
                                                        <td>{{$quality->bf}}</td>
                                                        <td>{{$quality->gsm}}</td>
                                                        <td>{{$quality->production_in_kg}}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 " >
                    <h5 class="table-title m-0 py-2 start_poproll_div" style="display: none">
                        Start Pop Roll
                    </h5>
                </div>
                <div class="bg-white table-view shadow-sm start_poproll_div" style="display: none">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('start-deckle')}}">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <label>Pop Roll</label>
                                <select name="pop_rolls" class="form-select" required>
                                    <option value="">Select Pop Roll</option>
                                    @foreach ($deckles as $deckle)
                                        <option value="{{ $deckle->id }}">POP ROLL - {{ $deckle->deckle_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success btn-sm" style="margin-top: 18px; ">Start</button>
                            </div>
                        </div>
                    </form>
                </div>
                @if($start_deckle)
                    <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                        <h5 class="table-title m-0 py-2 ">
                            Pop Roll Reel Start Process
                        </h5>
                    </div>
                    <div class="bg-white table-view shadow-sm">
                        <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('store-deckle-item')}}">
                            @csrf
                            @if($start_deckle)
                                <div id="popRollContainer">
                                    <div class="pop-roll" data-index="0">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label>Pop Roll</label>
                                                <input type="text" name="pop_roll" class="form-control me-2" readonly value="Pop Roll - {{$start_deckle->deckle_no}}" >
                                                <input type="hidden" name="pop_roll_id" class="form-control me-2" readonly value="{{$start_deckle->id}}" >
                                            </div>
                                            <div class="col-md-6"></div>
                                            <div class="col-md-3">
                                                <button type="button" class="btn btn-success mt-3" id="addPopRoll">+ Add Pop Roll</button>
                                                <input type="hidden" name="add_new_pop_roll" id="add_new_pop_roll" value="">
                                                <button type="submit" class="btn btn-primary mt-3" id="submit_btn">Complete</button> 
                                            </div>
                                        </div>
                                        <div class="reel-section mt-3">
                                            <div class="reel-row mb-2 " data-reel-index="0" style="display: none">
                                                <input type="text" name="pop_rolls[0][reels][0][reel_no]" class="form-control me-2 reel_no" placeholder="Reel No" readonly value="" data-select_type="reel_no" >
                                                <select name="pop_rolls[0][reels][0][quality_id]" class="form-select quality-select me-2" data-select_type="quality"  data-index="0">
                                                    <option value="">Select Quality</option>
                                                    @foreach($start_deckle->quality as $key => $value)
                                                        <option value="{{$value->id}}" data-bf="{{$value->bf}}" data-gsm="{{$value->gsm}}" data-quality_row_id="{{$value->quality_row_id}}" @if(count($start_deckle->quality)==1) selected @endif>{{$value->name}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="pop_rolls[0][reels][0][quality_row_id]" id="quality_row_id_0" data-select_type="quality_row_id" >
                                                <input type="text" name="pop_rolls[0][reels][0][bf]" class="form-control me-2" placeholder="BF" id="bf_0"  readonly data-select_type="bf">
                                                <input type="text" name="pop_rolls[0][reels][0][gsm]" class="form-control me-2" placeholder="GSM" id="gsm_0"  readonly data-select_type="gsm">
                                                <select name="pop_rolls[0][reels][0][unit]" class="form-select me-2" >
                                                    <option value="">Select Unit</option>
                                                    <option value="INCH">INCH</option>
                                                    <option value="CM">CM</option>
                                                    <option value="MM">MM</option>
                                                </select>
                                                <input type="text" name="pop_rolls[0][reels][0][size]" data-select_type="size" class="form-control me-2 size" placeholder="Size" data-index="0" >
                                                <input type="text" name="pop_rolls[0][reels][0][weight]" class="form-control me-2" placeholder="Weight" >
                                                <button type="button" class="btn btn-danger remove-reel">-</button>
                                            </div>

                                            <button type="button" class="btn btn-sm btn-info add-reel mt-2">+ Add Reel</button>
                                        </div>
                                        <hr class="my-4">
                                    </div>
                                </div>
                            @endif
                            <div class="modal" id="add_new_poproll_Modal">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Add Pop Roll</h4>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <!-- 29-10-2025 khushi code start here-->

                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="row">
                                <div class="mb-3 mt-3">
                                    <label for="new_actual_production_in_kg" class="form-label">Select Pop Roll:</label>
                                    <select class="form-select" name="new_pop_roll_id" id="new_pop_roll_id">
                                        <option value="">Select Pop Roll</option>
                                        @foreach($completed_poprolls as $poproll)
                                            <option value="{{ $poproll->id }}">POP ROLL - {{ $poproll->deckle_no }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-success" id="modalSubmitBtn">Submit</button>
                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                @endif

                <div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
                    <h5 class="table-title m-0 py-2 ">
                        Generated Pop Roll Reels
                    </h5>
                    <form  action="{{ route('deckle-process.manage-reel') }}" method="GET">
                        @csrf
                        <div class="d-md-flex d-block">                  
                            <div class="calender-administrator my-2 my-md-0">
                                <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="From date" required name="from_date" value="{{ !empty($from_date) ? date('Y-m-d', strtotime($from_date)) : '' }}">
                            </div>
                            <div class="calender-administrator ms-md-4">
                                <input type="date" id="customDate" class="form-control calender-bg-icon calender-placeholder" placeholder="To date" required name="to_date" value="{{ !empty($to_date) ? date('Y-m-d', strtotime($to_date)) : '' }}">
                            </div>
                            <button class="btn btn-info" style="margin-left: 5px;">Search</button>
                        </div>
                    </form>
                </div>
                <div class="bg-white table-view shadow-sm">
                    <table  class="table-striped table m-0 shadow-sm">
                        <thead>
                            <tr class=" font-12 text-body bg-light-pink ">
                                <th class="w-min-120 border-none bg-light-pink text-body">Deckle No.</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Details</th>
                                <th class="w-min-120 border-none bg-light-pink text-body">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completed_deckles as $key => $deckle)
                                @php $edit_cancel_status = 1;@endphp
                                <tr>
                                    <td>{{$deckle->deckle_no}}</td>
                                    <td>
                                        <table class="table table-borderd">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Reel No.</th>
                                                    <th>Size</th>
                                                    <th>Weight</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($deckle->quality as $key => $item)
                                                    <tr>
                                                        <td>{{$item->name}}</td>
                                                        <td>
                                                            @foreach($item->items as $key => $size)
                                                                {{$size->reel_no}}<br>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach($item->items as $key => $size)
                                                                &nbsp;&nbsp;&nbsp; {{$size->size}}<br>
                                                            @endforeach
                                                        </td>
                                                        <td>
                                                            @foreach($item->items as $key => $size)
                                                                &nbsp;&nbsp;&nbsp; {{$size->weight}}<br>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            
                                        </table>
                                    </td>
                                    <td>
                                        <button type="button" class="border-0 bg-transparent cancel_btn"   data-id="<?php echo $deckle->id;?>">
                                            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" class="px-1" alt="">
                                        </button>
                                        <a href="{{ URL::to('edit-pop-roll-reel/'.$deckle->id) }}"><img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" class="px-1" alt=""></a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="delete_sale" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog w-360  modal-dialog-centered  ">
        <div class="modal-content p-4 border-divider border-radius-8">
            <div class="modal-header border-0 p-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form class="" method="POST" action="{{ route('cancel-pop-roll-reel') }}">
                @csrf
                <div class="modal-body text-center p-0">
                    <button class="border-0 bg-transparent">
                        <img class="delete-icon mb-3 d-block mx-auto" src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}" alt="">
                    </button>
                    <h5 class="mb-3 fw-normal">Cancel this pop roll</h5>
                    <p class="font-14 text-body "> Do you really want to cancel these  pop roll? this process cannot be undone.</p>
                </div>
                <input type="hidden" id="cancel_deckle_id" name="pop_roll_id" />
                <div class="modal-footer border-0 mx-auto p-0">
                    <button type="button" class="btn btn-border-body cancel">CANCEL</button>
                    <button type="submit" class="ms-3 btn btn-red">DELETE</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
@include('layouts.footer')

<script>
    var reel_no = "{{$reel_no}}";
    reel_no = ++reel_no
    $(document).ready(function() {
        let popRollIndex = 0;
        // Add new Pop Roll section    
        $('#addPopRoll').on('click', function() {
            $("#add_new_poproll_Modal").modal('toggle');
            $("#add_new_pop_roll").val("YES");
            // popRollIndex++;
            // reel_no++; // increment for new pop roll
            // let newPopRoll = $('#popRollContainer .pop-roll:first').clone();

            // newPopRoll.attr('data-index', popRollIndex);

            // newPopRoll.find('select, input').each(function() {
            //     let name = $(this).attr('name');
            //     let select_type = $(this).attr('data-select_type');
            //     if (name) {
            //         name = name.replace(/\[\d+\]/, '[' + popRollIndex + ']');
            //         name = name.replace(/\[reels\]\[\d+\]/, '[reels][0]');
            //         if(select_type=='reel_no'){
            //             $(this).val(reel_no);
            //         } else {
            //             $(this).val('');
            //         }
            //     }
            // });

            // newPopRoll.find('.reel-section .reel-row:not(:first)').remove();

            // // Show remove button for new pop rolls
            // newPopRoll.find('.remove-poproll').show();

            // $('#popRollContainer').append(newPopRoll);
        });
        // Add new reel row inside a Pop Roll
        $(document).on('click', '.add-reel', function () {
            let popRoll = $(this).closest('.pop-roll');
            let reelSection = popRoll.find('.reel-section');
            let popIndex = popRoll.data('index');
            let reelIndex = reelSection.find('.reel-row').length;

            let newReel = reelSection.find('.reel-row:first').clone();
            newReel.removeAttr('style').addClass('d-flex');
            newReel.attr('data-reel-index', reelIndex);

            let lastReel = reelSection.find('.reel-row.d-flex').last();
            let hasExisting = reelSection.find('.reel-row.d-flex').length > 0;

            newReel.find('select, input').each(function () {
                let name = $(this).attr('name');
                let select_type = $(this).attr('data-select_type');
                if (name) {
                    name = name.replace(/\[reels\]\[\d+\]/, `[reels][${reelIndex}]`);
                    $(this).attr('name', name);
                }

                if (select_type === 'reel_no') {
                    $(this).val(reel_no);
                } 
                else if (select_type === 'quality') {
                    $(this).attr('data-index', reelIndex);

                    if (hasExisting) {
                        let lastQuality = lastReel.find('[data-select_type="quality"]').val();
                        $(this).val(lastQuality).trigger('change');
                    } else {
                        $(this).trigger('change');
                    }
                } 
                else if (select_type === 'bf') {
                    $(this).attr('id', "bf_" + reelIndex);
                    if (hasExisting) {
                        $(this).val(lastReel.find('[data-select_type="bf"]').val());
                    } else {
                        $(this).val('');
                    }
                } 
                else if (select_type === 'gsm') {
                    $(this).attr('id', "gsm_" + reelIndex);
                    if (hasExisting) {
                        $(this).val(lastReel.find('[data-select_type="gsm"]').val());
                    } else {
                        $(this).val('');
                    }
                } 
                else if (select_type === 'quality_row_id') {
                    $(this).attr('id', "quality_row_id_" + reelIndex);
                } 
                else if ($(this).is('select[name*="[unit]"]')) {
                    if (hasExisting) {
                        $(this).val(lastReel.find('select[name*="[unit]"]').val());
                    } else {
                        $(this).val('');
                    }
                } 
                else {
                    $(this).val('');
                }

                if (select_type === 'size') {
                    $(this).attr('data-index', reelIndex);
                }
            });

            // Insert new reel row before "+ Add Reel" button
            reelSection.find('.add-reel').before(newReel);

            // Trigger auto-fill for BF & GSM if quality already selected
            newReel.find('.quality-select').trigger('change');

            // Increment global reel number for next add
            reel_no++;
        });
        // Remove a reel
        $(document).on('click', '.remove-reel', function() {
            let reelSection = $(this).closest('.reel-section');
            let reelRows = reelSection.find('.reel-row');

            if (reelRows.length > 1) {
                $(this).closest('.reel-row').remove();
            }

            // Recalculate reel numbers in order
            let start_no = parseInt("{{ $reel_no }}"); // starting from DB last reel no + 1
            $(".reel_no").each(function(i, e) {
                $(this).val(start_no + i);
            });

            // Update global counter for next add
            reel_no = parseInt($(".reel_no:last").val()) + 1;
        });
        // On change of Pop Roll → load qualities via AJAX    
        $(document).on('change','.quality-select',function(){
            let type = $(this).find(":selected").attr('data-status');
            let index = $(this).attr('data-index');
            $("#bf_"+index).val($(this).find(":selected").attr('data-bf'));
            $("#gsm_"+index).val($(this).find(":selected").attr('data-gsm'));
            $("#quality_row_id_"+index).val($(this).find(":selected").attr('data-quality_row_id'));
            
            // $(this).closest('div').find('input[name$="[quality_type]"]').val(type);
        });
    });
    $(document).on('click', '.remove-poproll', function(){
        $(this).closest('.pop-roll').remove();
        $(".reel_no").each(function(i,e){
            if(i==0){
                reel_no = "{{$reel_no}}";
            }            
            $(this).val(reel_no);
            if (i !== $(".reel_no").length - 1) {
                    reel_no++;
                }
        });
    });
    $(document).on('change', '.size', function(){
        let size = $(this).val();
        size = size.split('X');
        size = size[0];
        let index = $(this).attr('data-index');
        let gsm = $("#gsm_"+$(this).attr('data-index')).val();

        $(this).val(size+"X"+gsm);
    });
    $(".start_btn").click(function(){
        $(".start_poproll_div").show();
    });
    $(document).on('click','.cancel_btn',function(){
        var id = $(this).attr("data-id");
        $("#cancel_deckle_id").val(id);
        $("#delete_sale").modal("show");
    });
    $(".cancel").click(function() {
        $("#delete_sale").modal("hide");
    });
    $(document).on('click', '#modalSubmitBtn', function(e) {
        e.preventDefault();

        let selectedPopRoll = $("#new_pop_roll_id").val();
        if (!selectedPopRoll) {
            alert("Please select a Pop Roll first.");
            return;
        }

        // Target only the main 'store-deckle-item' form (Pop Roll Reel Start Process)
        const form = $('form[action="{{ route('store-deckle-item') }}"]');

        // Remove any existing hidden field (avoid duplicates)
        form.find('input[name="new_pop_roll_id"]').remove();

        // Append hidden input with selected pop roll
        $('<input>').attr({
            type: 'hidden',
            name: 'new_pop_roll_id',
            value: selectedPopRoll
        }).appendTo(form);

        // Close modal
        $("#add_new_poproll_Modal").modal('hide');

        // Trigger form submit
        $("#submit_btn").trigger("click");
    });
    $(document).on('click', '#submit_btn', function(e) {
    let visibleReels = $('.reel-row.d-flex').length;

    if (visibleReels === 0) {
        e.preventDefault(); 
        alert('Please add at least one reel before completing the Pop Roll.');
        return false;
    }

    $(this).prop('disabled', true);
    $(this).closest('form').submit();
});
</script>



@endsection
