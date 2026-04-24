@extends('layouts.app')
<style>

/* GLOBAL */
body {
    font-size: 15.5px;
    background: #f4f6f9;
}

/* CARD / CONTAINER */
.bg-white {
    border-radius: 12px;
    border: 1px solid #e3e6ea;
}

/* HEADER */
.table-title-bottom-line {
    background: linear-gradient(135deg, #5e60ce, #6930c3);
    border-radius: 12px;
}
.table-title-bottom-line h5 {
    font-size: 20px;
    font-weight: 600;
}

/* SELECT2 */
.select2-container--default .select2-selection--single {
    height: 40px !important;
    border-radius: 8px !important;
    border: 1px solid #ced4da !important;
}
.select2-container--default .select2-selection__rendered {
    line-height: 38px !important;
    font-size: 14.5px;
}
.select2-container {
    width: 100% !important;
}

/* INPUT */
.form-control {
    height: 40px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    font-size: 14.5px;
}
.form-control:focus {
    border-color: #5e60ce;
    box-shadow: 0 0 0 0.1rem rgba(94,96,206,0.2);
}

/* LABEL */
label {
    font-weight: 500;
    margin-bottom: 6px;
    color: #444;
}

/* TABLE */
#itemTable {
    font-size: 14.5px;
    border-radius: 10px;
    overflow: hidden;
}

#itemTable thead {
    background-color: #eef3f7;
    color: #34495e;
    border-bottom: 2px solid #dce3ea;
}

#itemTable th {
    padding: 13px;
    font-weight: 600;
}

#itemTable td {
    padding: 11px;
    vertical-align: middle;
}

/* ROW HOVER */
#itemTable tbody tr:hover {
    background: #f8fbff;
    transition: 0.2s;
}

/* BUTTONS */
.btn {
    border-radius: 6px;
    font-size: 13.5px;
}

/* ADD BUTTON */
.btn-success {
    background-color: #38b000;
    border: none;
}
.btn-success:hover {
    background-color: #2d9200;
}

/* REMOVE BUTTON */
.btn-danger {
    background-color: #e63946;
    border: none;
}
.btn-danger:hover {
    background-color: #c82333;
}

/* SUBMIT BUTTON */
.btn-primary {
    background: #5e60ce;
    border: none;
    padding: 8px 18px;
}
.btn-primary:hover {
    background: #4c4fd1;
}

/* ALERT */
.alert {
    border-radius: 8px;
    font-size: 14px;
}

/* SMALL SHADOW IMPROVEMENT */
.shadow-sm {
    box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
}

</style>
@section('content')

@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

    <div class="table-title-bottom-line d-flex justify-content-between align-items-center shadow-sm px-4 py-3"
     style="background: linear-gradient(135deg, #6f42c1, #5a2ea6); border-radius: 10px;">

    <div class="d-flex align-items-center gap-3">
        
        <!-- ICON -->
        <div style="
            background: rgba(255,255,255,0.15);
            padding: 10px;
            border-radius: 10px;
            font-size: 20px;
            color: #fff;">
            <i class="fa fa-list"></i>
        </div>

        <!-- TITLE -->
        <h5 class="m-0 text-white" style="
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;">
            Set Party Item Rates
        </h5>
    </div>

    <!-- OPTIONAL RIGHT SIDE (can remove if not needed) -->
    <div>
        <span style="
            font-size: 13px;
            color: #ddd;">
            Manage Rates Easily
        </span>
    </div>

</div>

    <div class="bg-white p-4 shadow-sm">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('retail-item-rate.store') }}">
            @csrf

            <!-- Party -->
          <div class="row mb-3">
                <div class="col-md-4">
                    <label><b>Select Date</b></label>
                    <input type="date" name="rate_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label><b>Select Time</b></label>
                    <input type="time" name="rate_time" value="{{ date('H:i') }}" class="form-control" required>
                </div>
            </div>
            @if(!empty($rates))
            <div class="alert alert-info">
                Previous rates loaded. You can modify if needed.
            </div>
            @endif
            <!-- Table -->
            <table class="table table-bordered" id="itemTable">
                <thead style="
                    background-color:#eef5f9;
                    font-size:15px;
                    color:#2c3e50;
                    border-bottom:2px solid #d6e4ec;
                            ">
                    <tr>
                    <tr>
                            <th width="60%">Item</th>
                            <th width="40%">Price</th>
                        </tr>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                    <tr>
                        <td>
                            {{ $item->name }}
                            <input type="hidden" name="items[{{ $index }}][item_id]" value="{{ $item->id }}">
                        </td>

                        <td>
                           <input type="number" step="0.01" 
                                    name="items[{{ $index }}][price]" 
                                    class="form-control" 
                                    value="{{ $rates[$item->id] ?? '' }}"
                                    placeholder="Enter Price" required>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <button class="btn btn-primary">Submit</button>

        </form>

    </div>
</div>
</div>
</section>
</div>

@include('layouts.footer')

<!-- ✅ REQUIRED SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- SELECT2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function(){

    function validateDateTime() {
        let date = $('input[name="rate_date"]').val();
        let time = $('input[name="rate_time"]').val();

        if(date && time){
            $.ajax({
                url: "{{ route('check.latest.datetime') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    date: date,
                    time: time
                },
               success: function(res){

    if(!res.status){
        alert(res.message);

        // ✅ Reset to current date & time
        let now = new Date();

        let today = now.toISOString().split('T')[0];
        let hours = String(now.getHours()).padStart(2, '0');
        let minutes = String(now.getMinutes()).padStart(2, '0');

        $('input[name="rate_date"]').val(today);
        $('input[name="rate_time"]').val(hours + ':' + minutes);

        $('button[type="submit"]').prop('disabled', true);

    } else {
        $('button[type="submit"]').prop('disabled', false);
    }
}
            });
        }
    }

    // Trigger on change
    $('input[name="rate_date"], input[name="rate_time"]').on('change', function(){
        validateDateTime();
    });

});
</script>

@endsection