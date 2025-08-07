@extends('layouts.app')
@section('content')
<!-- header-section -->
@include('layouts.header')
<!-- list-view-company-section -->
<style>
   .text-ellipsis {
      text-overflow: ellipsis;
      overflow: hidden;
      white-space: nowrap;
   }
   .w-min-50 {
      min-width: 50px;
   }
   .dataTables_filter,
   .dataTables_info,
   .dataTables_length,
   .dataTables_paginate {
      display: none;
   }
   .select2-container--default .select2-selection--single .select2-selection__rendered{
      line-height: 29px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
      height: 30px !important;
   }
   .select2-container .select2-selection--single{
      height: 30px !important;
   }
   .select2-container{
          width: 300px !important;
   }
   .select2-container--default .select2-selection--single{
      border-radius: 12px !important;
   }
   .selection{
      font-size: 14px;
   }
   .form-control {
      height: 28px;
   }
   .form-select {
      height: 34px;
   }
   input[type=number]::-webkit-inner-spin-button, 
   input[type=number]::-webkit-outer-spin-button { 
       -webkit-appearance: none;
       -moz-appearance: none;
       appearance: none;
       margin: 0; 
   }
</style>
<div class="list-of-view-company ">
   <section class="list-of-view-company-section container-fluid">
      <div class="row vh-100">
         @include('layouts.leftnav')
         <div class="col-md-12 ml-sm-auto  col-lg-9 px-md-4 bg-mint">
            @if (session('error'))
               <div class="alert alert-danger" role="alert"> {{session('error')}}</div>
            @endif
            @if (session('success'))
               <div class="alert alert-success" role="alert">
                  {{ session('success') }}
               </div>
            @endif
            
            <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">GSTR3B</h5>
            <form class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm" id="gstr1-form" action="{{ route('gstr3B.view') }}" method="GET" >
               @csrf
               <div class="row">
                  <div class="row">
                            <div class="mb-3 col-md-3">
                                <label for="month" class="form-label font-14 font-heading">Month</label>
                                <input type="month" class="form-control" name="month" id="month" required value="{{ date('Y-m', strtotime('-1 month')) }}">
                            </div>

                            <input type="hidden" name="from_date" id="from_date">
                            <input type="hidden" name="to_date" id="to_date">

                  <div class="mb-3 col-md-3">
                     <label for="gstin" class="form-label font-14 font-heading">GSTIN</label>
                     <select class="form-select" name="series" id="series">
                        @foreach ($gst as $value)
                           <option value="{{$value->gst_no}}">{{$value->gst_no}}</option>
                        @endforeach
                     </select>
                  </div>
                  <div class="mb-3 col-md-3">
                     <button id="submit" type="button" class="btn btn-primary px-4">Generate Report</button>
                     <!-- Hidden real submit button -->
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
</body>
@include('layouts.footer')
<script>
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



    document.addEventListener("DOMContentLoaded", function () {
        const monthInput = document.getElementById('month');
        const fromDateInput = document.getElementById('from_date');
        const toDateInput = document.getElementById('to_date');

        function updateDateRange() {
            const selectedMonth = monthInput.value; // format: YYYY-MM
            if (!selectedMonth) return;

            const [year, month] = selectedMonth.split('-');

            // 1st day of the month
            const fromDate = `${year}-${month}-01`;

            // Last day of the month (using Date object trick)
            const lastDay = new Date(year, month, 0).getDate(); // day 0 of next month = last day of selected month
            const toDate = `${year}-${month}-${lastDay.toString().padStart(2, '0')}`;

            fromDateInput.value = fromDate;
            toDateInput.value = toDate;
        }

        // Call on load to set default
        updateDateRange();

        // Call on change
        monthInput.addEventListener('change', updateDateRange);
    });



</script>
@endsection 