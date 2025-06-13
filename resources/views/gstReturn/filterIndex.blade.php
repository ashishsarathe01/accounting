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
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            <div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4 mb-4">
               <h5 class="master-table-title m-0 py-2">GSTR-1</h5> 
                </div>

    <form action="{{ route('gstr1') }}" method="POST">
        @csrf
        <div class="mb-3 col-md-2">
        <label for="series" class="form-label" style="font-size: 1.05rem">Series</label>
       <select name="series" id="series" class="form-select form-select-lg select2-single" aria-label="form-select-lg example" required autofocus>
    <option value="">Select Series</option>
    @foreach($seriesList as $series)
        <option value="{{ $series['gst_no'] }}">
            {{ $series['series_name'] }}
        </option>
    @endforeach
</select>

    </div>

    <!-- From Date -->
    <div class="mb-3 col-md-2">
        <label for="from_date" class="form-label" style="font-size: 1.05rem">From Date</label>
        <input type="date" name="from_date" id="from_date" class="form-control" value="{{ old('from_date') }}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}" required>
    </div>

    <!-- To Date -->
    <div class="mb-3 col-md-2">
        <label for="to_date" class="form-label" style="font-size: 1.05rem">To Date</label>
        <input type="date" name="to_date" id="to_date" class="form-control" value="{{ old('to_date') }}" min="{{Session::get('from_date')}}" max="{{Session::get('to_date')}}" required>
    </div>

    <!-- Button -->
   <div class="mb-3 text-start">
        <button id="submit" type="submit" class="btn btn-primary px-4">Generate Report</button>
    </div>



@include('layouts.footer')
<script>
$(document).ready(function () {
    // Initialize Select2
    $(".select2-single, .select2-multiple").select2({ width: '100%' });

    // Adjust height of Select2 to match Bootstrap inputs (38px)
    setTimeout(function () {
        $('.select2-selection--single').css({
            'height': '45px',
            'padding': '6px 12px',
            'border': '1px solid #ced4da',
            'border-radius': '0.375rem',
            'display': 'flex',
            'align-items': 'center'
        });

        $('.select2-selection__rendered').css({
            'line-height': '24px'
        });

        $('.select2-selection__arrow').css({
            'height': '36px'
        });
    }, 0); // Run after DOM is updated

    const focusMap = {
        '#series': '#from_date',
        '#from_date': '#to_date',
        '#to_date': '#submit'
        
    };

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