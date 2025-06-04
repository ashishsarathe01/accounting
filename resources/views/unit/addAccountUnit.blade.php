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
                Add Manage Unit
                </h5>
                <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" method="POST" action="{{ route('account-unit.store') }}">
                    @csrf
                    <div class="row">
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name" required autofocus/>
                        </div>
                        
                        
                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Short Name</label>
                            <input type="text" class="form-control" name="s_name" id="s_name" placeholder="Enter name" required/>
                        </div>


                        <div class="mb-4 col-md-4">
                            <label for="name" class="form-label font-14 font-heading">Unit Quantity Code(UQC)- for Gst Return</label>
                          <select class="form-control select2-single" name="unit_code" id="unit_code" required>
    <option value="">-- Select UQC --</option>
    <option value="BAL - BALE">BAL - BALE</option>
    <option value="BDL - BUNDLES">BDL - BUNDLES</option>
    <option value="BKL - BUCKLES">BKL - BUCKLES</option>
    <option value="BOU - BILLION OF UNITS">BOU - BILLION OF UNITS</option>
    <option value="BOX - BOX">BOX - BOX</option>
    <option value="BTL - BOTTLES">BTL - BOTTLES</option>
    <option value="BUN - BUNCHES">BUN - BUNCHES</option>
    <option value="CAN - CANS">CAN - CANS</option>
    <option value="CBM - CUBIC METERS">CBM - CUBIC METERS</option>
    <option value="CCM - CUBIC CENTIMETERS">CCM - CUBIC CENTIMETERS</option>
    <option value="CMS - CENTIMETERS">CMS - CENTIMETERS</option>
    <option value="CTN - CARTONS">CTN - CARTONS</option>
    <option value="DOZ - DOZENS">DOZ - DOZENS</option>
    <option value="DRM - DRUMS">DRM - DRUMS</option>
    <option value="GGK - GREAT GROSS">GGK - GREAT GROSS</option>
    <option value="GMS - GRAMMES">GMS - GRAMMES</option>
    <option value="GRS - GROSS">GRS - GROSS</option>
    <option value="GYD - GROSS YARDS">GYD - GROSS YARDS</option>
    <option value="KGS - KILOGRAMS">KGS - KILOGRAMS</option>
    <option value="KLR - KILOLITRE">KLR - KILOLITRE</option>
    <option value="KME - KILOMETRE">KME - KILOMETRE</option>
    <option value="LTR - LITRES">LTR - LITRES</option>
    <option value="MLT - MILILITRE">MLT - MILILITRE</option>
    <option value="MTR - METERS">MTR - METERS</option>
    <option value="MTS - METRIC TON">MTS - METRIC TON</option>
    <option value="NOS - NUMBERS">NOS - NUMBERS</option>
    <option value="PAC - PACKS">PAC - PACKS</option>
    <option value="PCS - PIECES">PCS - PIECES</option>
    <option value="PRS - PAIRS">PRS - PAIRS</option>
    <option value="QTL - QUINTAL">QTL - QUINTAL</option>
    <option value="ROL - ROLLS">ROL - ROLLS</option>
    <option value="SET - SETS">SET - SETS</option>
    <option value="SQF - SQUARE FEET">SQF - SQUARE FEET</option>
    <option value="SQM - SQUARE METERS">SQM - SQUARE METERS</option>
    <option value="SQY - SQUARE YARDS">SQY - SQUARE YARDS</option>
    <option value="TBS - TABLETS">TBS - TABLETS</option>
    <option value="TGM - TEN GROSS">TGM - TEN GROSS</option>
    <option value="THD - THOUSANDS">THD - THOUSANDS</option>
    <option value="TON - TONNES">TON - TONNES</option>
    <option value="TUB - TUBES">TUB - TUBES</option>
    <option value="UGS - US GALLONS">UGS - US GALLONS</option>
    <option value="UNT - UNITS">UNT - UNITS</option>
    <option value="YDS - YARDS">YDS - YARDS</option>
    <option value="OTH - OTHERS">OTH - OTHERS</option>
    <option value="Test - ER Scenario">Test - ER Scenario</option>
</select>

                        </div>
                      
                        <div class="mb-4 col-md-4">
                            <label class="form-label font-14 font-heading">Status</label>
                            <select class="form-select form-select-lg select2-single" name="status" id="status" aria-label="form-select-lg example" required>
                                <option value="">Select </option>
                                <option value="1">Enable</option>
                                <option value="0">Disable</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-start">
                        <button id="submit" type="submit" class="btn  btn-xs-primary ">
                            SUBMIT
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