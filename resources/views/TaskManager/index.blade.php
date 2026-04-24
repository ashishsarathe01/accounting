@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 5px 10px;
    display: flex;
    align-items: center;
}

.select2-container--default .select2-selection__rendered {
    line-height: normal !important;
    padding-left: 0 !important;
}

.select2-container--default .select2-selection__arrow {
    height: 100%;
}

.select2-container {
    width: 100% !important;
}
.select2-container--default .select2-selection--single .select2-selection__clear {
    position: absolute;
    right: 28px;  
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #999;
    cursor: pointer;
    padding: 0;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    right: 8px;
    height: 100%;
}

.select2-container--default .select2-selection--single {
    padding-right: 45px !important;
}
</style>
<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">Tasks Assigned By You</h5>

    <a href="{{ route('task.create') }}" class="btn btn-xs-primary">
        ADD
        <svg class="position-relative ms-2" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M9.1665 15.8327V10.8327H4.1665V9.16602H9.1665V4.16602H10.8332V9.16602H15.8332V10.8327H10.8332V15.8327H9.1665Z" fill="white" />
        </svg>
    </a>
</div>

<div class="bg-white table-view shadow-sm p-3 mb-3">

<form method="GET" action="{{ route('task.index') }}">
    <div class="row align-items-end">
    {{-- Employees --}}
    <div class="col-md-2">
        <label class="small">Employee</label>
        <select name="employee" class="form-select select2-single">
            <option value="">Select</option>

            @foreach($users as $user)
                <option value="{{ $user->id }}"
                    {{ request('employee') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>
    {{-- Priority --}}
    <div class="col-md-2">
        <label class="small">Priority</label>
        <select name="priority" class="form-select select2-single">
            <option value="all">All</option>
            <option value="low" {{ request('priority')=='low'?'selected':'' }}>Low</option>
            <option value="medium" {{ request('priority')=='medium'?'selected':'' }}>Medium</option>
            <option value="high" {{ request('priority')=='high'?'selected':'' }}>High</option>
        </select>
    </div>

    {{-- Status --}}
    <div class="col-md-2">
        <label class="small">Status</label>
        <select name="status" id="statusFilter" class="form-select select2-single">
            <option value="all">All</option>
            <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Not Started</option>
            <option value="in_progress" {{ request('status')=='in_progress'?'selected':'' }}>In Progress</option>
            <option value="on_hold" {{ request('status')=='on_hold'?'selected':'' }}>On Hold</option>
            <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
            <option value="overdue" {{ request('status')=='overdue'?'selected':'' }}>Overdue</option>
        </select>
    </div>

        {{-- From Date (Only Completed) --}}
        <div class="col-md-2 completedDateFilter"
            style="{{ request('status') == 'completed' ? '' : 'display:none;' }}">
            <label class="small">From Date</label>
            <input type="date"
                name="from_date"
                value="{{ request('from_date') }}"
                class="form-control">
        </div>

        {{-- To Date (Only Completed) --}}
        <div class="col-md-2 completedDateFilter"
            style="{{ request('status') == 'completed' ? '' : 'display:none;' }}">
            <label class="small">To Date</label>
            <input type="date"
                name="to_date"
                value="{{ request('to_date') }}"
                class="form-control">
        </div>

        {{-- Filter Button --}}
        <div class="col-md-2">
            <button class="btn btn-info w-100">Filter</button>
        </div>

        {{-- Reset --}}
        <div class="col-md-2">
            <a href="{{ route('task.index') }}" class="btn btn-border-body w-100">
                Reset
            </a>
        </div>

    </div>

</form>

</div>

<div class="transaction-table bg-white table-view shadow-sm">

<table class="table-striped table m-0 shadow-sm">

<thead>
<tr class="font-12 text-body bg-light-pink">
    <th>#</th>
    <th>Title</th>
    <th>Assigned To</th>
    <th>Priority</th>
    <th>Status</th>
    <th>Deadline</th>
    <th class="text-center">Action</th>
</tr>
</thead>

<tbody>

@if(count($tasks) > 0)

@foreach($tasks as $key => $task)

@php
$isOverdue = false;

if($task->status != 'completed' &&
   \Carbon\Carbon::parse($task->deadline)->isPast()){
    $isOverdue = true;
}
@endphp

<tr class="font-14 font-heading bg-white {{ $isOverdue ? 'table-danger' : '' }}">

    <td>{{ $key+1 }}</td>

    <td>{{ $task->title }}</td>

    <td>{{ $task->assigned_user }}</td>

    {{-- Priority --}}
    <td>
        @if($task->priority == 'high')
            <span class="badge bg-danger">High</span>
        @elseif($task->priority == 'medium')
            <span class="badge bg-warning text-dark">Medium</span>
        @else
            <span class="badge bg-success">Low</span>
        @endif
    </td>

    {{-- Status --}}
    <td>
        @if($isOverdue)
            <span class="badge bg-danger">Overdue</span>
        @elseif($task->status == 'pending')
            <span class="badge bg-secondary">Not Started</span>
        @elseif($task->status == 'in_progress')
            <span class="badge bg-primary">In Progress</span>
        @elseif($task->status == 'completed')
            <span class="badge bg-success">Completed</span>
        @else
            <span class="badge bg-dark">On Hold</span>
        @endif
    </td>

    <td>
        @if($isOverdue)
            <span class="text-danger fw-bold">
                {{ \Carbon\Carbon::parse($task->deadline)->format('d-m-Y H:i') }}
            </span>
        @else
            {{ \Carbon\Carbon::parse($task->deadline)->format('d-m-Y H:i') }}
        @endif
    </td>

    <td class="text-center">

    @php
        $canModify = (
            $task->created_by == Session::get('user_id') &&
            is_null($task->parent_task_id)
        );
    @endphp

    @if($canModify)
        <a href="{{ URL::to('task/edit/'.$task->id) }}" title="Edit Task">
            <img src="{{ URL::asset('public/assets/imgs/edit-icon.svg')}}" 
                 class="px-1" 
                 alt="Edit">
        </a>
    @endif

    @if($canModify)
        <button type="button"
                class="border-0 bg-transparent delete"
                data-id="{{ $task->id }}"
                title="Delete Task">
            <img src="{{ URL::asset('public/assets/imgs/delete-icon.svg')}}" 
                 class="px-1" 
                 alt="Delete">
        </button>
    @endif

    <a title="View Task"
       href="{{ route('task.detail', $task->id) }}?from=tasks">
        <img src="{{ asset('public/assets/imgs/eye-icon.svg') }}" 
             class="px-1" 
             alt="View Task">
    </a>

</td>


</tr>

@endforeach

@else

<tr>
    <td colspan="7" class="text-center py-4">
        No Tasks Found
    </td>
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

<div class="modal fade" id="delete_task" tabindex="-1">
   <div class="modal-dialog w-360 modal-dialog-centered">
      <div class="modal-content p-4 border-divider border-radius-8">
         <div class="modal-header border-0 p-0">
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
         </div>

         <form method="POST" action="{{ route('task.delete') }}">
            @csrf

            <div class="modal-body text-center p-0">
               <button class="border-0 bg-transparent">
                  <img class="delete-icon mb-3 d-block mx-auto"
                       src="{{ URL::asset('public/assets/imgs/administrator-delete-icon.svg')}}"
                       alt="">
               </button>
               <h5 class="mb-3 fw-normal">Delete this task?</h5>
               <p class="font-14 text-body">
                   This process cannot be undone.
               </p>
            </div>

            <input type="hidden" id="task_id" name="task_id" />

            <div class="modal-footer border-0 mx-auto p-0">
               <button type="button" class="btn btn-border-body cancel" data-bs-dismiss="modal">
                   CANCEL
               </button>
               <button type="submit" class="ms-3 btn btn-red">
                   DELETE
               </button>
            </div>
         </form>
      </div>
   </div>
</div>

@include('layouts.footer')

<script>
$(document).on('click','.delete',function(){
    var id = $(this).data("id");
    $("#task_id").val(id);
    $("#delete_task").modal("show");
});

$(document).ready(function(){

    function toggleDateFilter() {
        var status = $('#statusFilter').val();

        if(status === 'completed') {
            $('.completedDateFilter').show();
        } else {
            $('.completedDateFilter').hide();
            $('input[name="from_date"]').val('');
            $('input[name="to_date"]').val('');
        }
    }

    $('#statusFilter').on('change', toggleDateFilter);

    toggleDateFilter(); 
});

$(document).ready(function() {
    $('.select2-single').select2({
        placeholder: "Select",
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0 
    });
});
</script>

@endsection
