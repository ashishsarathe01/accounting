@extends('layouts.app')
@section('content')
@include('layouts.header')
<style>
.select2-container .select2-selection--single {
    height: 38px !important;
    padding: 5px 10px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important;
}
</style>
<div class="list-of-view-company">
<section class="list-of-view-company-section container-fluid">
<div class="row vh-100">
@include('layouts.leftnav')

<div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">

{{-- Alerts --}}
@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@php
    switch($task->status) {
        case 'pending':
            $statusLabel = 'Not Started Yet';
            break;

        case 'in_progress':
            $statusLabel = 'In Progress';
            break;

        case 'on_hold':
            $statusLabel = 'On Hold';
            break;

        case 'completed':
            $statusLabel = 'Completed';
            break;

        default:
            $statusLabel = ucfirst(str_replace('_',' ', $task->status));
    }

    $isOverdue = false;

    if ($task->status != 'completed' && \Carbon\Carbon::parse($task->deadline)->isPast()) {
        $isOverdue = true;
    }
@endphp


<div class="container-fluid py-4">

    {{-- HEADER STRIP --}}
    <div class="bg-white shadow-sm rounded p-4 mb-4">

        <div class="d-flex justify-content-between align-items-start">

            <div>
                @php
                    $from = request()->get('from');
                @endphp

                <a href="{{ $from == 'my-tasks' 
                            ? route('task.myTasks') 
                            : route('task.index') }}"
                style="font-size: 13px; color: #555; text-decoration: none; display: inline-block; margin-bottom: 6px;">
                    ← back
                </a>
                <h3 class="mb-1">{{ $task->title }}</h3>
                <div class="text-muted small">
                    Assigned by <strong>{{ $task->created_by_name }}</strong>
                    to <strong>{{ $task->assigned_to_name }}</strong>
                </div>
            </div>

            <div class="text-end">
                <div class="mb-2">
                    <span class="badge 
                        {{ $task->priority == 'high' ? 'bg-danger' : 
                           ($task->priority == 'medium' ? 'bg-warning text-dark' : 'bg-success') }}">
                        {{ ucfirst($task->priority) }} Priority
                    </span>
                </div>

                @if($isOverdue)
                    <span class="badge bg-danger">
                        Overdue
                    </span>
                @else
                    <span class="badge 
                        {{ $task->status == 'completed' ? 'bg-success' :
                        ($task->status == 'in_progress' ? 'bg-primary' :
                        ($task->status == 'on_hold' ? 'bg-dark' : 'bg-secondary')) }}">
                        {{ $statusLabel }}
                    </span>
                @endif



            </div>

        </div>

        <hr>
        @if($isOverdue)
        <div class="alert alert-danger py-2 mb-3">
            This task is overdue. Immediate attention required.
        </div>
        @endif

        <div class="row small text-muted">

            <div class="col-md-3">
                <strong>Deadline</strong><br>

                @if($isOverdue)
                    <span class="text-danger fw-bold">
                        {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y, H:i') }}
                    </span>
                    <div class="small text-danger">Deadline passed</div>
                @else
                    {{ \Carbon\Carbon::parse($task->deadline)->format('d M Y, H:i') }}
                @endif

            </div>

            <div class="col-md-3">
                <strong>Started</strong><br>
                {{ $task->started_at ? \Carbon\Carbon::parse($task->started_at)->format('d M Y, H:i') : '-' }}

            </div>

            <div class="col-md-3">
                <strong>Completed</strong><br>
                {{ $task->completed_at ? \Carbon\Carbon::parse($task->completed_at)->format('d M Y, H:i') : '-' }}

            </div>

            <div class="col-md-3">
                <strong>Status</strong><br>
                {{ $statusLabel }}
            </div>

        </div>

    </div>



    <div class="row">

        {{-- LEFT SIDE --}}
        <div class="col-md-8">

            <div class="bg-white shadow-sm rounded p-4 mb-4">
                <h5>Description</h5>
                <p class="text-muted">
                    {{ $task->description ?? 'No description provided.' }}
                </p>
            </div>
            @if($task->parent_task_id)

            <div class="bg-light border rounded p-3 mb-4 small">

                @php
                    $parent = DB::table('tasks')
                        ->leftJoin('users','tasks.assigned_to','=','users.id')
                        ->where('tasks.id',$task->parent_task_id)
                        ->select('tasks.title','users.name')
                        ->first();
                @endphp

                <strong>Delegated From:</strong><br>

                @if($parent)
                    {{ $parent->title }} → {{ $parent->name }}
                @endif

            </div>

            @endif

            <div class="bg-white shadow-sm rounded p-4">

                <h5 class="mb-4">Task Chat</h5>

                <div style="max-height:500px; overflow-y:auto;">

                    @if(count($timeline) > 0)

                        @foreach($timeline as $item)

                            @php
                                $isCreator  = $item->user_id == $task->created_by;
                                $isAssignee = $item->user_id == $task->assigned_to;
                                $isMe       = $item->user_id == Session::get('user_id');
                            @endphp


                            @if($item->message_type == 'system')

                                <div class="text-center my-3">
                                    <span class="badge bg-light text-dark px-3 py-2">
                                        {{ $item->description }}
                                        <br>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y, H:i') }}
                                        </small>
                                    </span>
                                </div>

                            @else

                                @php
                                    $isMe = $item->user_id == Session::get('user_id');
                                @endphp

                                <div class="d-flex mb-3 
                                    {{ $isMe ? 'justify-content-end' : 'justify-content-start' }}">

                                    <div style="
                                        max-width:70%;
                                        padding:12px;
                                        border-radius:15px;
                                        background-color:
                                            {{ $isMe ? '#0d6efd' : '#e9ecef' }};
                                        color:
                                            {{ $isMe ? 'white' : 'black' }};
                                    ">


                                        <div class="small fw-bold mb-1">
                                            {{ $item->name }}
                                        </div>

                                        <div>
                                            {{ $item->description }}
                                        </div>

                                        <div class="text-end mt-1">
                                            <small style="font-size:11px;">
                                                {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y, H:i') }}
                                            </small>
                                        </div>

                                    </div>
                                </div>

                            @endif

                        @endforeach

                    @else

                        <p class="text-muted text-center">No messages yet.</p>

                    @endif

                </div>


                @php
                    $currentUser = Session::get('user_id');
                    $isCreator  = $currentUser == $task->created_by;
                    $isAssignee = $currentUser == $task->assigned_to;
                @endphp

                @if(
                    ($isCreator || $isAssignee) &&
                    ($task->status != 'completed' || $isCreator)
                )

                    <hr>

                    <form action="{{ route('task.addResponse') }}" method="POST">
                        @csrf
                        <input type="hidden" name="task_id" value="{{ $task->id }}">

                        <div class="input-group">
                            <input type="text"
                                name="message"
                                class="form-control"
                                placeholder="Type a message..."
                                required>

                            <button class="btn btn-primary">
                                Send
                            </button>
                        </div>
                    </form>

                @endif

            </div>


        </div>

        <div class="col-md-4">

            @if(Session::get('user_id') == $task->assigned_to)

            <div class="bg-white shadow-sm rounded p-4 position-sticky" style="top:20px;">

                <h5 class="mb-3">Update Task</h5>

                <form action="{{ route('task.updateStatus') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">

                    <label class="small mb-1">Change Status</label>
                    <select name="status" class="form-select mb-2 select2-single">

                        <option value="pending" {{ $task->status == 'pending' ? 'selected' : '' }}>
                            Not Started Yet
                        </option>

                        <option value="in_progress" {{ $task->status == 'in_progress' ? 'selected' : '' }}>
                            In Progress
                        </option>

                        <option value="on_hold" {{ $task->status == 'on_hold' ? 'selected' : '' }}>
                            On Hold
                        </option>

                        <option value="completed" {{ $task->status == 'completed' ? 'selected' : '' }}>
                            Completed
                        </option>

                    </select>

                    <button class="btn btn-primary w-100">
                        Update Status
                    </button>
                </form>


            @if($task->delegation_allowed && $task->status != 'completed')

            <hr>
            <h6 class="mb-2">Delegate Task</h6>

            <form action="{{ route('task.delegate') }}" method="POST">
                @csrf
                <input type="hidden" name="task_id" value="{{ $task->id }}">

                <select name="assigned_to" class="form-select mb-2 select2-single" required>
                    <option value="">Select User</option>

                    @foreach(
                        \App\Models\User::where('company_id', Session::get('user_company_id'))
                            ->where('status', '1')
                            ->where('delete_status', '0')
                            ->whereNull('deleted_at')
                            ->get() 
                    as $user)

                        @if($user->id != Session::get('user_id'))
                            <option value="{{ $user->id }}"
                                @if(isset($childTask) && $childTask && $childTask->assigned_to == $user->id)
                                    selected
                                @endif
                            >
                                {{ $user->name }}
                            </option>
                        @endif

                    @endforeach
                </select>

                <div class="form-check mb-2">
                    <input class="form-check-input"
                        type="checkbox"
                        name="delegation_allowed"
                        value="1"
                        id="allowFurther"
                        @if(isset($childTask) && $childTask && $childTask->delegation_allowed)
                            checked
                        @endif
                    >
                    <label class="form-check-label small" for="allowFurther">
                        Allow further delegation
                    </label>
                </div>

                <button class="btn btn-outline-primary w-100">
                    {{ isset($childTask) && $childTask ? 'Update Delegation' : 'Delegate' }}
                </button>
            </form>

            @endif



            @if($task->status != 'completed')

                <hr>

                <form action="{{ route('task.addResponse') }}" method="POST">
                    @csrf
                    <input type="hidden" name="task_id" value="{{ $task->id }}">

                    <label class="small mb-1">Work Update</label>
                    <textarea name="message"
                            rows="4"
                            class="form-control mb-2"
                            placeholder="Explain progress, issue, next steps..."></textarea>

                    <button class="btn btn-success w-100">
                        Submit Update
                    </button>
                </form>

            @else

                <div class="alert alert-light border text-center">
                    Task is completed.<br>
                    Reopen task to add updates.
                </div>

            @endif


        </div>

        @endif

        </div>

    </div>

</div>

</div>
</div>
</section>
</div>
@include('layouts.footer')
<script>
$(document).ready(function() {
    $('.select2-single').select2({
        placeholder: "Select option",
        allowClear: false,
        width: '100%'
    });
});
</script>
@endsection
