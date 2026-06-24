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
                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">GSTR-1</h5>
                <form id="gstr1-form" action="{{ route('gstr1') }}" method="get" class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-3">
                            <label for="month_select" class="form-label font-14 font-heading">Month</label>
                            @php
                                $fy = Session::get('from_date'); // Format: Y-m-d
                                $fyYear = \Carbon\Carbon::parse($fy)->format('Y'); // Start year
                                $fyNextYear = \Carbon\Carbon::parse($fy)->addYear()->format('Y'); // Next year
                            @endphp
                            <select id="month_select" name="month" class="form-select" required>
                                <option value="">-- Select Month --</option>
                                @php
                                    $fy = Session::get('from_date');
                                    $fyStartYear = \Carbon\Carbon::parse($fy)->format('Y');
                                    $fyEndYear = \Carbon\Carbon::parse($fy)->addYear()->format('Y');
                            
                                    // Month list in financial year order (Apr -> Mar)
                                    $months = [
                                        '04' => 'April',
                                        '05' => 'May',
                                        '06' => 'June',
                                        '07' => 'July',
                                        '08' => 'August',
                                        '09' => 'September',
                                        '10' => 'October',
                                        '11' => 'November',
                                        '12' => 'December',
                                        '01' => 'January',
                                        '02' => 'February',
                                        '03' => 'March',
                                    ];
                                @endphp
    
                                @foreach($months as $num => $name)
                                     @php
                                        // Decide year exactly as your JS logic does
                                        $year = (intval($num) >= 4) ? $fyStartYear : $fyEndYear;
                                
                                        // Create value like 042026 or 032027
                                        $monthValue = $num . $year;
                                    @endphp
                                    <option value="{{ $monthValue }}" @php if(date('mY', strtotime('-1 month'))==$monthValue){ echo "selected"; } @endphp>{{ $name }} {{ $year }}</option>
                                @endforeach
                            </select>
                    </div>
                        <div class="mb-3 col-md-2">
                            <label for="series" class="form-label" style="font-size: 1.05rem">GSTIN</label>
                            <select name="series" id="series" class="form-select form-select-lg select2-single" aria-label="form-select-lg example" required autofocus>
                                <option value="">Select GSTIN</option>
                                @foreach ($gst as $value)
                                   <option value="{{$value->gst_no}}">{{$value->gst_no}}</option>
                                @endforeach
                            </select>
                    </div>
                        <!-- Hidden Inputs to submit -->
                        <input type="hidden" name="from_date" id="from_date">
                        <input type="hidden" name="to_date" id="to_date">
                        <!-- Data Source -->
                        <div class="mb-3 col-md-3">
                            <label for="data_source" class="form-label" style="font-size: 1.05rem">
                                Data Source
                            </label>
                        
                            <select 
                                name="data_source" 
                                id="data_source" 
                                class="form-select form-select-lg"
                            >
                                <option value="books" selected>
                                    Books Only
                                </option>
                        
                                <option value="portal">
                                    Books + GST Portal
                                </option>
                            </select>
                        
                            <small class="text-muted">
                                Choose whether to load only books data or include GST portal data.
                            </small>
                    </div>
                        <div class="mb-3 col-md-3">
                            <button id="submit" type="button" class="btn btn-xs-primary submit_btn" style="margin-top: 20px;">Generate Report</button>
                            <button id="real-submit" type="submit" style="display: none;"></button>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
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
        '#series': '#month_select',
        '#month_select': '#data_source',
        '#data_source': '#submit'
        
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



$(document).ready(function() {
    $("#month_select").on("change", function () {
        let value = $(this).val();

        if (!value) {
            $("#from_date").val('');
            $("#to_date").val('');
            return;
        }

        let month = value.substring(0, 2);
        let year = value.substring(2, 6);

        let lastDay = new Date(year, month, 0).getDate();

        $("#from_date").val(`${year}-${month}-01`);
        $("#to_date").val(`${year}-${month}-${String(lastDay).padStart(2,'0')}`);
    });

    $("#month_select").trigger('change');
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
            to_date: to_date,
            data_source : $('#data_source').val()
        },
        success: function(res) {
           if (res.status === true && res.message === 'TOKEN-VALID') {

                   $('#real-submit').click();
                
                } else if (res.status === true && res.message === 'BOOK-DATA') {
                
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
                alert('TXN = ' + res.txn);
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