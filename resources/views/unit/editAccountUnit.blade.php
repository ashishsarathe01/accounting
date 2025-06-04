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
                        <li class="breadcrumb-item fw-bold font-heading" aria-current="page">Manage Unit</li>
                    </ol>
                </nav>
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                edit Manage Unit
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-unit.update') }}">
                    @csrf
                    <input type="hidden" value="{{ $editunit->id }}" id="unit_id" name="unit_id" />
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ $editunit->name }}" placeholder="Enter name" required autofocus/>
                        </div>
                        
                        
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Short Name</label>
                            <input type="text" class="form-control" name="s_name" id="s_name" value="{{ $editunit->s_name }}" placeholder="Enter name" required/>
                        </div>

                        <div class="mb-4 col-md-4">
    <label for="name" class="form-label font-14 font-heading">Unit Quantity Code (UQC) - for GST Return</label>
    <select class="form-control select2-single" name="unit_code" id="unit_code" required>
        <option value="">-- Select UQC --</option>
        <option value="BAL - BALE" {{ $editunit->unit_code == 'BAL - BALE' ? 'selected' : '' }}>BAL - BALE</option>
        <option value="BDL - BUNDLES" {{ $editunit->unit_code == 'BDL - BUNDLES' ? 'selected' : '' }}>BDL - BUNDLES</option>
        <option value="BKL - BUCKLES" {{ $editunit->unit_code == 'BKL - BUCKLES' ? 'selected' : '' }}>BKL - BUCKLES</option>
        <option value="BOU - BILLION OF UNITS" {{ $editunit->unit_code == 'BOU - BILLION OF UNITS' ? 'selected' : '' }}>BOU - BILLION OF UNITS</option>
        <option value="BOX - BOX" {{ $editunit->unit_code == 'BOX - BOX' ? 'selected' : '' }}>BOX - BOX</option>
        <option value="BTL - BOTTLES" {{ $editunit->unit_code == 'BTL - BOTTLES' ? 'selected' : '' }}>BTL - BOTTLES</option>
        <option value="BUN - BUNCHES" {{ $editunit->unit_code == 'BUN - BUNCHES' ? 'selected' : '' }}>BUN - BUNCHES</option>
        <option value="CAN - CANS" {{ $editunit->unit_code == 'CAN - CANS' ? 'selected' : '' }}>CAN - CANS</option>
        <option value="CBM - CUBIC METERS" {{ $editunit->unit_code == 'CBM - CUBIC METERS' ? 'selected' : '' }}>CBM - CUBIC METERS</option>
        <option value="CCM - CUBIC CENTIMETERS" {{ $editunit->unit_code == 'CCM - CUBIC CENTIMETERS' ? 'selected' : '' }}>CCM - CUBIC CENTIMETERS</option>
        <option value="CMS - CENTIMETERS" {{ $editunit->unit_code == 'CMS - CENTIMETERS' ? 'selected' : '' }}>CMS - CENTIMETERS</option>
        <option value="CTN - CARTONS" {{ $editunit->unit_code == 'CTN - CARTONS' ? 'selected' : '' }}>CTN - CARTONS</option>
        <option value="DOZ - DOZENS" {{ $editunit->unit_code == 'DOZ - DOZENS' ? 'selected' : '' }}>DOZ - DOZENS</option>
        <option value="DRM - DRUMS" {{ $editunit->unit_code == 'DRM - DRUMS' ? 'selected' : '' }}>DRM - DRUMS</option>
        <option value="GGK - GREAT GROSS" {{ $editunit->unit_code == 'GGK - GREAT GROSS' ? 'selected' : '' }}>GGK - GREAT GROSS</option>
        <option value="GMS - GRAMMES" {{ $editunit->unit_code == 'GMS - GRAMMES' ? 'selected' : '' }}>GMS - GRAMMES</option>
        <option value="GRS - GROSS" {{ $editunit->unit_code == 'GRS - GROSS' ? 'selected' : '' }}>GRS - GROSS</option>
        <option value="GYD - GROSS YARDS" {{ $editunit->unit_code == 'GYD - GROSS YARDS' ? 'selected' : '' }}>GYD - GROSS YARDS</option>
        <option value="KGS - KILOGRAMS" {{ $editunit->unit_code == 'KGS - KILOGRAMS' ? 'selected' : '' }}>KGS - KILOGRAMS</option>
        <option value="KLR - KILOLITRE" {{ $editunit->unit_code == 'KLR - KILOLITRE' ? 'selected' : '' }}>KLR - KILOLITRE</option>
        <option value="KME - KILOMETRE" {{ $editunit->unit_code == 'KME - KILOMETRE' ? 'selected' : '' }}>KME - KILOMETRE</option>
        <option value="LTR - LITRES" {{ $editunit->unit_code == 'LTR - LITRES' ? 'selected' : '' }}>LTR - LITRES</option>
        <option value="MLT - MILILITRE" {{ $editunit->unit_code == 'MLT - MILILITRE' ? 'selected' : '' }}>MLT - MILILITRE</option>
        <option value="MTR - METERS" {{ $editunit->unit_code == 'MTR - METERS' ? 'selected' : '' }}>MTR - METERS</option>
        <option value="MTS - METRIC TON" {{ $editunit->unit_code == 'MTS - METRIC TON' ? 'selected' : '' }}>MTS - METRIC TON</option>
        <option value="NOS - NUMBERS" {{ $editunit->unit_code == 'NOS - NUMBERS' ? 'selected' : '' }}>NOS - NUMBERS</option>
        <option value="PAC - PACKS" {{ $editunit->unit_code == 'PAC - PACKS' ? 'selected' : '' }}>PAC - PACKS</option>
        <option value="PCS - PIECES" {{ $editunit->unit_code == 'PCS - PIECES' ? 'selected' : '' }}>PCS - PIECES</option>
        <option value="PRS - PAIRS" {{ $editunit->unit_code == 'PRS - PAIRS' ? 'selected' : '' }}>PRS - PAIRS</option>
        <option value="QTL - QUINTAL" {{ $editunit->unit_code == 'QTL - QUINTAL' ? 'selected' : '' }}>QTL - QUINTAL</option>
        <option value="ROL - ROLLS" {{ $editunit->unit_code == 'ROL - ROLLS' ? 'selected' : '' }}>ROL - ROLLS</option>
        <option value="SET - SETS" {{ $editunit->unit_code == 'SET - SETS' ? 'selected' : '' }}>SET - SETS</option>
        <option value="SQF - SQUARE FEET" {{ $editunit->unit_code == 'SQF - SQUARE FEET' ? 'selected' : '' }}>SQF - SQUARE FEET</option>
        <option value="SQM - SQUARE METERS" {{ $editunit->unit_code == 'SQM - SQUARE METERS' ? 'selected' : '' }}>SQM - SQUARE METERS</option>
        <option value="SQY - SQUARE YARDS" {{ $editunit->unit_code == 'SQY - SQUARE YARDS' ? 'selected' : '' }}>SQY - SQUARE YARDS</option>
        <option value="TBS - TABLETS" {{ $editunit->unit_code == 'TBS - TABLETS' ? 'selected' : '' }}>TBS - TABLETS</option>
        <option value="TGM - TEN GROSS" {{ $editunit->unit_code == 'TGM - TEN GROSS' ? 'selected' : '' }}>TGM - TEN GROSS</option>
        <option value="THD - THOUSANDS" {{ $editunit->unit_code == 'THD - THOUSANDS' ? 'selected' : '' }}>THD - THOUSANDS</option>
        <option value="TON - TONNES" {{ $editunit->unit_code == 'TON - TONNES' ? 'selected' : '' }}>TON - TONNES</option>
        <option value="TUB - TUBES" {{ $editunit->unit_code == 'TUB - TUBES' ? 'selected' : '' }}>TUB - TUBES</option>
        <option value="UGS - US GALLONS" {{ $editunit->unit_code == 'UGS - US GALLONS' ? 'selected' : '' }}>UGS - US GALLONS</option>
        <option value="UNT - UNITS" {{ $editunit->unit_code == 'UNT - UNITS' ? 'selected' : '' }}>UNT - UNITS</option>
        <option value="YDS - YARDS" {{ $editunit->unit_code == 'YDS - YARDS' ? 'selected' : '' }}>YDS - YARDS</option>
        <option value="OTH - OTHERS" {{ $editunit->unit_code == 'OTH - OTHERS' ? 'selected' : '' }}>OTH - OTHERS</option>
        <option value="Test - ER Scenario" {{ $editunit->unit_code == 'Test - ER Scenario' ? 'selected' : '' }}>Test - ER Scenario</option>
    </select>
</div>


                      
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg select2-single" id="status" name="status" aria-label="form-select-lg example" required>
                                <option value="">Select </option>
                                <option <?php echo $editunit->status ==1 ? 'selected':'';?> value="1">Enable</option>
                                <option <?php echo $editunit->status ==0 ? 'selected':'';?> value="0">Disable</option>
                            </select>
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
    // Initialize Select2
    $(".select2-single, .select2-multiple").select2();  

    // Set height using jQuery after initialization
    setTimeout(function() {
        $('.select2-container--default .select2-selection--single').css({
            'height': '45px',
            'padding': '8px 12px',
            'font-size': '14px'
        });

        $('.select2-container--default .select2-selection--single .select2-selection__rendered').css({
            'line-height': '30px' // vertical centering
        });

        $('.select2-container--default .select2-selection--single .select2-selection__arrow').css({
            'height': '45px'
        });
    }, 100); // timeout ensures styles are applied after DOM is rendered


     const focusMap = {
        '#name': '#s_name',
        '#s_name': '#unit_code',
        '#unit_code': '#status',
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