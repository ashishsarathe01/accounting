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

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet text-white shadow-sm">
    Edit Task
</h5>

<div class="card shadow-lg border-0 mt-3">
<div class="card-body">

<form action="{{ route('task.update') }}" method="POST">
@csrf

<input type="hidden" name="task_id" value="{{ $task->id }}">

<div class="row">

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Task Title</label>
        <input type="text"
               name="title"
               value="{{ old('title', $task->title) }}"
               class="form-control">
    </div>

    {{-- Assign To --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Assign To</label>
        <select name="assigned_to" class="form-control select2-single">
    <option value="">Select User</option>
    @foreach($users as $user)
        <option value="{{ $user->id }}"
            {{ $task->assigned_to == $user->id ? 'selected' : '' }}>
            {{ $user->name }} ({{ $user->type }})
        </option>
    @endforeach
</select>
    </div>

    {{-- Deadline --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Deadline</label>
        <input type="datetime-local"
               name="deadline"
               value="{{ \Carbon\Carbon::parse($task->deadline)->format('Y-m-d\TH:i') }}"
               class="form-control">
    </div>

    {{-- Priority --}}
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Priority</label>
        <select name="priority" class="form-control select2-single">
    <option value="low" {{ $task->priority=='low'?'selected':'' }}>Low</option>
    <option value="medium" {{ $task->priority=='medium'?'selected':'' }}>Medium</option>
    <option value="high" {{ $task->priority=='high'?'selected':'' }}>High</option>
</select>
    </div>

    {{-- Description --}}
    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Description</label>
        <textarea name="description"
                  rows="4"
                  class="form-control">{{ old('description', $task->description) }}</textarea>
    </div>


</div>

<div class="text-end">
    <a href="{{ route('task.index') }}" class="btn btn-secondary px-4">Cancel</a>
    <button type="submit" class="btn btn-success px-4">
        Update Task
    </button>
</div>

</form>

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
        allowClear: true,
        width: '100%'
    });
});
</script>
@endsection
