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
                        Edit Pop Roll Reel
                    </h5>
                </div>
                
                <div class="bg-white table-view shadow-sm">
                    <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{route('update-pop-roll-reel')}}">
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
                                           
                                            
                                            <button type="submit" class="btn btn-primary mt-3" id="submit_btn">Submit</button> 
                                        </div>
                                    </div>
                                    <div class="reel-section mt-3">
                                        @php $row_index = 0; @endphp
                                        @foreach($item_reel as $key => $reels)
                                            <div class="reel-row mb-2" data-reel-index="{{$row_index}}" style="display: flex;">
                                                 <input type="hidden" name="pop_rolls[0][reels][{{$row_index}}][row_id]" value="{{$reels->id}}">
                                                <input type="text" name="pop_rolls[0][reels][{{$row_index}}][reel_no]" class="form-control me-2 reel_no" placeholder="Reel No" readonly data-select_type="reel_no" value="{{$reels->reel_no}}">
                                                <select name="pop_rolls[0][reels][{{$row_index}}][quality_id]" class="form-select quality-select me-2" data-select_type="quality"  data-index="{{$row_index}}">
                                                    <option value="">Select Quality</option>
                                                    @foreach($start_deckle->quality as $key => $value)
                                                        <option value="{{$value->id}}" @if($value->id==$reels->quality_id) selected @endif data-bf="{{$value->bf}}" data-gsm="{{$value->gsm}}" data-quality_row_id="{{$value->quality_row_id}}">{{$value->name}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" name="pop_rolls[0][reels][{{$row_index}}][quality_row_id]" id="quality_row_id_{{$row_index}}" data-select_type="quality_row_id" value="{{$reels->quality_row_id}}">
                                                <input type="text" name="pop_rolls[0][reels][{{$row_index}}][bf]" class="form-control me-2" placeholder="BF" id="bf_{{$row_index}}"  readonly data-select_type="bf" value="{{$reels->bf}}">
                                                <input type="text" name="pop_rolls[0][reels][{{$row_index}}][gsm]" class="form-control me-2" placeholder="GSM" id="gsm_{{$row_index}}"  readonly data-select_type="gsm" value="{{$reels->gsm}}">
                                                <select name="pop_rolls[0][reels][{{$row_index}}][unit]" class="form-select me-2" >
                                                    <option value="">Select Unit</option>
                                                    <option value="INCH" @if("INCH"==$reels->unit) selected @endif>INCH</option>
                                                    <option value="CM" @if("CM"==$reels->unit) selected @endif>CM</option>
                                                    <option value="MM" @if("MM"==$reels->unit) selected @endif>MM</option>
                                                </select>
                                                <input type="text" name="pop_rolls[0][reels][{{$row_index}}][size]" data-select_type="size" class="form-control me-2 size" placeholder="Size" data-index="{{$row_index}}" value="{{$reels->size}}">
                                                <input type="text" name="pop_rolls[0][reels][{{$row_index}}][weight]" class="form-control me-2" placeholder="Weight" value="{{$reels->weight}}">
                                                <button type="button" class="btn btn-danger remove-reel">-</button>
                                            </div>
                                            @php $row_index++; @endphp
                                        @endforeach
                                        <button type="button" class="btn btn-sm btn-info add-reel mt-2">+ Add Reel</button>
                                    </div>
                                    <hr class="my-4">
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

</body>
@include('layouts.footer')

<script>
    var reel_no = "{{$reel_no}}";
    reel_no = ++reel_no
    $(document).ready(function() {
        let popRollIndex = 0;
        
        // Add new reel row inside a Pop Roll
        $(document).on('click', '.add-reel', function() {
            let popRoll = $(this).closest('.pop-roll');
            let reelSection = popRoll.find('.reel-section');
            let popIndex = popRoll.data('index');
            let reelIndex = reelSection.find('.reel-row').length;

            let newReel = reelSection.find('.reel-row:first').clone();
            newReel.removeAttr('style').addClass('d-flex');
            newReel.attr('data-reel-index', reelIndex);

            let lastReel = reelSection.find('.reel-row.d-flex').last();
            let hasExisting = reelSection.find('.reel-row.d-flex').length > 0;
            
            newReel.find('select, input').each(function() {
                let name = $(this).attr('name');
                let select_type = $(this).attr('data-select_type');
                if (name) {
                    name = name.replace(/\[reels\]\[\d+\]/, `[reels][${reelIndex}]`);
                    if (select_type == 'reel_no') {
                        $(this).attr('name', name).val(reel_no);
                    } else if (select_type != 'quality') {
                        $(this).attr('name', name).val('');
                    } else {
                        $(this).attr('data-index', reelIndex);
                        $(this).attr('name', name);
                    }

                    if (select_type == 'bf') {
                        $(this).attr('id', "bf_" + reelIndex);
                    } else if (select_type == 'gsm') {
                        $(this).attr('id', "gsm_" + reelIndex);
                    } else if (select_type == 'size') {
                        $(this).attr('data-index', reelIndex);
                    } else if (select_type == 'quality_row_id') {
                        $(this).attr('id', "quality_row_id_" + reelIndex);
                    }
                }
            });

            reelSection.find('.add-reel').before(newReel);

            // Set default quality, bf, gsm, and quality_row_id for new reel
            let qualitySelect = newReel.find('.quality-select');
            let firstOption = qualitySelect.find('option[value!=""]:first');
            if (firstOption.length) {
                qualitySelect.val(firstOption.val());
                let index = qualitySelect.attr('data-index');
                let bf = firstOption.attr('data-bf') || '';
                let gsm = firstOption.attr('data-gsm') || '';
                let quality_row_id = firstOption.attr('data-quality_row_id') || '';
                newReel.find('#bf_' + index).val(bf);
                newReel.find('#gsm_' + index).val(gsm);
                newReel.find('#quality_row_id_' + index).val(quality_row_id);
                newReel.find('#quality_row_id_' + index).val(quality_row_id);
            }

            // Increment reel number for next add
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
            // let start_no = parseInt("{{ $reel_no }}"); // starting from DB last reel no + 1
            // $(".reel_no").each(function(i, e) {
            //     $(this).val(start_no + i);
            // });

            // // Update global counter for next add
            // reel_no = parseInt($(".reel_no:last").val()) + 1;
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
    
    $(document).on('change', '.size', function(){
        let size = $(this).val();
        size = size.split('X');
        size = size[0];
        let index = $(this).attr('data-index');
        let gsm = $("#gsm_"+$(this).attr('data-index')).val();

        $(this).val(size+"X"+gsm);
    });
    
    
</script>



@endsection
