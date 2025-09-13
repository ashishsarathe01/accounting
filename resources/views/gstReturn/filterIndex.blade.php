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

    <form id="gstr1-form" action="{{ route('gstr1') }}" method="get">
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
  @php
    $fy = Session::get('from_date'); // Format: Y-m-d
    $fyYear = \Carbon\Carbon::parse($fy)->format('Y'); // Start year
    $fyNextYear = \Carbon\Carbon::parse($fy)->addYear()->format('Y'); // Next year
@endphp

<!-- Month Selector -->
<div class="mb-3 col-md-3">
    <label for="month_select" class="form-label" style="font-size: 1.05rem">Select Month</label>
    <select id="month_select" name="month" class="form-select" required>
        <option value="">-- Select Month --</option>
        <option value="04">April</option>
        <option value="05">May</option>
        <option value="06">June</option>
        <option value="07">July</option>
        <option value="08">August</option>
        <option value="09">September</option>
        <option value="10">October</option>
        <option value="11">November</option>
        <option value="12">December</option>
        <option value="01">January</option>
        <option value="02">February</option>
        <option value="03">March</option>
    </select>
</div>

<!-- Hidden Inputs to submit -->
<input type="hidden" name="from_date" id="from_date">
<input type="hidden" name="to_date" id="to_date">


    <!-- Button -->
   <div class="mb-3 text-start">
       <!-- Existing submit button (type=button) -->
<button id="submit" type="button" class="btn btn-primary px-4">Generate Report</button>

<!-- Hidden real submit button -->
<button id="real-submit" type="submit" style="display: none;"></button>

    </div>


<div class="modal fade" id="otpModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <p><h5 class="modal-title">OTP Verification</h5></p>
            <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="form-group">
               <input type="text" class="form-control" id="otp" placeholder="Enter OTP">
               <input type="hidden" id="fgstin">
            </div>
         </div>
         <div class="modal-footer border-0 mx-auto p-0">
            <button type="button" class="btn btn-border-body close" data-bs-dismiss="modal">CANCEL</button>
            <button type="button" class="ms-3 btn btn-red verify_otp">SUBMIT</button>
         </div>
      </div>
   </div>
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

document.addEventListener("DOMContentLoaded", function () {
    const monthSelect = document.getElementById("month_select");
    const fromDateInput = document.getElementById("from_date");
    const toDateInput = document.getElementById("to_date");

    const fyStartYear = {{ \Carbon\Carbon::parse($fy)->format('Y') }};
    const fyEndYear = {{ \Carbon\Carbon::parse($fy)->addYear()->format('Y') }};

    monthSelect.addEventListener("change", function () {
        const month = this.value;

        if (!month) {
            fromDateInput.value = '';
            toDateInput.value = '';
            return;
        }

        // Determine year for the month
        const year = parseInt(month) >= 4 ? fyStartYear : fyEndYear;

        // Determine last day of month (handle leap year for Feb)
        const lastDay = new Date(year, month, 0).getDate(); // 0th day of next month = last day of selected month

        // Format YYYY-MM-DD
        const from_date = `${year}-${month}-01`;
        const to_date = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;

        fromDateInput.value = from_date;
        toDateInput.value = to_date;
    });
});

$(document).ready(function() {
   $('#submit').on('click', function(e) {
   
    let form = $('#gstr1-form');
    let series = $('#series').val();
    let from_date = $('#from_date').val();
    let to_date = $('#to_date').val();

    if (!series || !from_date || !to_date) {
        alert("Please fill all fields");
        return;
    }

    $.ajax({
        url: "{{route('gstr1-detail')}}",
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            series: series,
            from_date: from_date,
            to_date: to_date
        },
        success: function(res) {
        
            if (res.status === true && res.message === 'TOKEN-VALID') {
               $('#real-submit').click();
            } else if (res.status === true && res.message === 'TOKEN-OTP') {
                $('#fgstin').val(series);
                $('#otpModal').modal('show');
            } else {
                alert(res.message || 'Token error');
            }
        },
        error: function() {
            alert("Something went wrong while checking the token");
        }
    });
});

    // OTP verification submit
    $('.verify_otp').on('click', function() {
        let otp = $('#otp').val();
        let fgstin = $('#fgstin').val();

        if (!otp) {
            alert("Please enter OTP");
            return;
        }

        $.ajax({
            url: "{{route('verify-gst-token-otp')}}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                otp: otp,
                gstin: fgstin
            },
            success: function(res) {
            //    if (res.status === true) {
            //         // $('#real-submit').click();
            //         $('#otpModal').modal('hide');
            //     }else {
            //         alert(res.message || 'OTP verification failed');
            //     }
                if(res!=""){
                  let obj = JSON.parse(res);
                  if(obj.status==true){
                        $('#real-submit').click();
                        $('#otpModal').modal('hide');
                  }else{
                     alert(obj.message);
                  }
               }else{
                  alert("Something Went Wrong.Please Try Again.");
               }
            },
            error: function() {
                alert("Error verifying OTP");
            }
        });
    });
});
</script>


    @endsection 