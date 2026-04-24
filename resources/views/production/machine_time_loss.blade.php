@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">

@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- Alerts --}}
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p class="mb-0">{{ $error }}</p>
        @endforeach
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

{{-- Page Title --}}
<div class="position-relative table-title-bottom-line d-flex justify-content-between align-items-center
            bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h5 class="table-title m-0">Machine Time Loss</h5>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addMachineLossModal">
        Add Machine Loss
    </button>
</div>

<div class="bg-white table-view shadow-sm p-3">

{{-- Filters --}}
<form method="GET" action="{{ route('machine.time.loss') }}" class="row g-2 mb-3">
    <div class="col-md-3">
        <label>From Date</label>
        <input type="date" name="from_date"
               value="{{ $from_date ?? now()->format('Y-m-d') }}"
               class="form-control">
    </div>
    <div class="col-md-3">
        <label>To Date</label>
        <input type="date" name="to_date"
               value="{{ $to_date ?? now()->format('Y-m-d') }}"
               class="form-control">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-primary">Filter</button>
    </div>
</form>

<div class="table-responsive">
<table class="table table-bordered table-sm">

<thead class="table-light">
<tr>
    <th>Date</th>
    <th>Shift A (08–20)</th>
    <th>Shift B (20–08)</th>
    <th>Total</th>
</tr>
</thead>

<tbody>

@php
    $grandShiftA = 0;
    $grandShiftB = 0;

    $fmt = function($m){
        $m = ceil($m);
        $h = intdiv($m,60);
        $r = $m % 60;
        return $h>0 ? ($r>0 ? "$h hr $r min" : "$h hr") : "$r min";
    };
@endphp

@forelse($report as $date => $row)

@php
    $grandShiftA += $row['shift_a'];
    $grandShiftB += $row['shift_b'];

    $shiftA = $fmt($row['shift_a']);
    $shiftB = $fmt($row['shift_b']);
    $total  = $fmt($row['shift_a'] + $row['shift_b']);
    $key    = str_replace('-', '', $date);

    $shiftATotal = collect($row['details']['A'])->sum('minutes');
    $shiftBTotal = collect($row['details']['B'])->sum('minutes');
@endphp

{{-- SUMMARY ROW --}}
<tr>
    <td>{{ \Carbon\Carbon::parse($date)->format('d-m-Y') }}</td>
    <td>
        <a href="javascript:void(0)" onclick="toggleShift('{{ $key }}','A')">
            {{ $shiftA }}
        </a>
    </td>
    <td>
        <a href="javascript:void(0)" onclick="toggleShift('{{ $key }}','B')">
            {{ $shiftB }}
        </a>
    </td>
    <td>{{ $total }}</td>
</tr>

{{-- DETAILS ROW --}}
<tr id="row_{{ $key }}" style="display:none;background:#f9f9f9;">
<td colspan="4">

<div class="row g-3">

{{-- SHIFT A --}}
<div class="col-md-6" id="A_{{ $key }}" style="visibility:hidden;height:0;">
    <strong>Shift A</strong>
    <table class="table table-bordered table-sm mt-2 mb-0">
        <thead class="table-light">
        <tr>
            <th>Stopped At</th>
            <th>Started At</th>
            <th>Duration</th>
            <th>Reason</th>
            <th>Remark</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($row['details']['A'] as $d)
            <tr>
                <td>{{ $d['stopped_at'] }}</td>
                <td>{{ $d['start_at'] ?? '-' }}</td>
                <td>{{ $fmt($d['minutes']) }}</td>
                <td>{{ $d['reason'] }}</td>
                <td>{{ $d['remark'] }}</td>
                <td class="text-center">
                    <a href="javascript:void(0)"
                    class="editLossBtn"
                    data-id="{{ $d['id'] }}">
                        <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}" alt="Edit">
                    </a>
                    <button type="button"
                        class="border-0 bg-transparent deleteLossBtn"
                        data-id="{{ $d['id'] }}">
                        <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}" class="px-1" alt="Delete">
                    </button>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center">No records</td></tr>
        @endforelse
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="2" class="text-end">Total</td>
                <td>{{ $fmt($shiftATotal) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- SHIFT B --}}
<div class="col-md-6" id="B_{{ $key }}" style="visibility:hidden;height:0;">
    <strong>Shift B</strong>
    <table class="table table-bordered table-sm mt-2 mb-0">
        <thead class="table-light">
        <tr>
            <th>Stopped At</th>
            <th>Started At</th>
            <th>Duration</th>
            <th>Reason</th>
            <th>Remark</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        @forelse($row['details']['B'] as $d)
        <tr>
            <td>{{ $d['stopped_at'] }}</td>
            <td>{{ $d['start_at'] ?? '-' }}</td>
            <td>{{ $fmt($d['minutes']) }}</td>
            <td>{{ $d['reason'] }}</td>
            <td>{{ $d['remark'] }}</td>
            <td class="text-center">
                <a href="javascript:void(0)"
                class="editLossBtn"
                data-id="{{ $d['id'] }}">
                    <img src="{{ asset('public/assets/imgs/edit-icon.svg') }}"
                        alt="Edit"
                        style="width:18px; cursor:pointer;">
                </a>
                <button type="button"
                    class="border-0 bg-transparent deleteLossBtn"
                    data-id="{{ $d['id'] }}">
                    <img src="{{ asset('public/assets/imgs/delete-icon.svg') }}"
                        alt="Delete"
                        style="width:18px; cursor:pointer;">
                </button>

            </td>
        </tr>
        @empty
            <tr><td colspan="6" class="text-center">No records</td></tr>
        @endforelse
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="2" class="text-end">Total</td>
                <td>{{ $fmt($shiftBTotal) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

</div>
</td>
</tr>

@empty
<tr>
    <td colspan="4" class="text-center">No data found</td>
</tr>
@endforelse

@if(count($report))
<tr class="table-warning fw-bold">
    <td class="text-end">GRAND TOTAL</td>
    <td>{{ $fmt($grandShiftA) }}</td>
    <td>{{ $fmt($grandShiftB) }}</td>
    <td>{{ $fmt($grandShiftA + $grandShiftB) }}</td>
</tr>
@endif

</tbody>
</table>
</div>

</div>
</div>
</div>
</section>
</div>
<!-- Edit Machine Loss Modal -->
<div class="modal fade" id="editMachineLossModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Edit Machine Time Loss</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="edit_loss_id">

                <div class="mb-3">
                    <label class="form-label">Stopped At</label>
                    <input type="datetime-local" class="form-control" id="edit_stopped_at">
                </div>

                <div class="mb-3">
                    <label class="form-label">Started At</label>
                    <input type="datetime-local" class="form-control" id="edit_started_at">
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <select class="form-select" id="edit_reason">
                        <option value="">Select Reason</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Breakdown">Breakdown</option>
                        <option value="Power Failure">Power Failure</option>
                        <option value="Shift Change">Shift Change</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remark</label>
                    <textarea class="form-control" id="edit_remark" rows="3"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="update_machine_loss">Update</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

<!-- Add Machine Loss Modal -->
<div class="modal fade" id="addMachineLossModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Machine Time Loss</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Stopped At</label>
                    <input type="datetime-local" class="form-control" id="add_stopped_at">
                </div>

                <div class="mb-3">
                    <label class="form-label">Started At</label>
                    <input type="datetime-local" class="form-control" id="add_started_at">
                </div>

                <div class="mb-3">
                    <label class="form-label">Reason</label>
                    <select class="form-select" id="add_reason">
                        <option value="">Select Reason</option>
                        <option value="Maintenance">Maintenance</option>
                        <option value="Breakdown">Breakdown</option>
                        <option value="Power Failure">Power Failure</option>
                        <option value="Shift Change">Shift Change</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remark</label>
                    <textarea class="form-control" id="add_remark"></textarea>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="save_machine_loss">Save</button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@include('layouts.footer')

<script>
function toggleShift(dateKey, shift) {
    const row = document.getElementById('row_' + dateKey);
    const box = document.getElementById(shift + '_' + dateKey);

    row.style.display = 'table-row';

    if (box.style.visibility === 'hidden') {
        box.style.visibility = 'visible';
        box.style.height = 'auto';
    } else {
        box.style.visibility = 'hidden';
        box.style.height = '0';
    }

    const aOpen = document.getElementById('A_' + dateKey).style.visibility === 'visible';
    const bOpen = document.getElementById('B_' + dateKey).style.visibility === 'visible';

    if (!aOpen && !bOpen) {
        row.style.display = 'none';
    }
}

$(document).on("click",".editLossBtn",function(){

    let id = $(this).data("id");

    $.get("{{ url('machine-loss/get') }}/"+id,function(res){

        if(res.status){

            let data = res.data;

            $("#edit_loss_id").val(data.id);
            $("#edit_stopped_at").val(data.stopped_at.replace(' ','T'));
            $("#edit_started_at").val(data.start_at ? data.start_at.replace(' ','T') : '');
            $("#edit_reason").val(data.reason);
            $("#edit_remark").val(data.remark);

            $("#editMachineLossModal").modal("show");

        }

    });

});


$("#update_machine_loss").click(function(){

    $.post("{{ url('machine-loss/update') }}",{

        _token:"{{ csrf_token() }}",
        id: $("#edit_loss_id").val(),
        stopped_at: $("#edit_stopped_at").val(),
        started_at: $("#edit_started_at").val(),
        reason: $("#edit_reason").val(),
        remark: $("#edit_remark").val()

    },function(res){

        if(res.status){
            alert(res.message);
            location.reload();
        }else{
            alert(res.message || "Update failed");
        }

    });

});

$("#save_machine_loss").click(function(){

    let stopped_at = $("#add_stopped_at").val();
    let started_at = $("#add_started_at").val();
    let reason = $("#add_reason").val();
    let remark = $("#add_remark").val();


    if(!stopped_at){
        alert("Please enter Stopped At time");
        return;
    }

    if(!started_at){
        alert("Please enter Started At time");
        return;
    }

    if(!reason){
        alert("Please select Reason");
        return;
    }

    if(!remark){
        alert("Please enter Remark");
        return;
    }


    $.post("{{ url('machine-loss/store') }}",{

        _token:"{{ csrf_token() }}",
        stopped_at: stopped_at,
        started_at: started_at,
        reason: reason,
        remark: remark

    },function(res){

        if(res.status){
            alert("Machine loss added successfully");
            location.reload();
        }else{
            alert(res.message || "Insert failed");
        }

    });

});

$(document).on("click",".deleteLossBtn",function(){

    let id = $(this).data("id");

    if(!confirm("Are you sure you want to delete this record?")){
        return;
    }

    $.post("{{ url('machine-loss/delete') }}",{

        _token:"{{ csrf_token() }}",
        id: id

    },function(res){

        if(res.status){
            alert("Deleted successfully");
            location.reload();
        }else{
            alert("Delete failed");
        }

    });

});

</script>

@endsection
