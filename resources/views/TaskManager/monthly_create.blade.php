@extends('layouts.app')
@section('content')
@include('layouts.header')

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

<div class="table-title-bottom-line position-relative d-flex justify-content-between align-items-center bg-plum-viloet title-border-redius border-divider shadow-sm py-2 px-4">
    <h5 class="transaction-table-title m-0 py-2">Create Monthly Tasks</h5>
</div>

<div class="card shadow-lg border-0 mt-3">
<div class="card-body">

<form action="{{ route('task.monthly.store') }}" method="POST">
@csrf

{{-- Assign To --}}
<div class="row mb-4">
    <div class="col-md-6">
        <label class="form-label fw-bold">Assign To</label>
        <select name="assigned_to" class="form-select" required>
            <option value="">Select User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}">
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<hr>

{{-- Dynamic Task Rows --}}
<div id="taskRows">

<div class="row align-items-end mb-3 task-row">

    <div class="col-md-3">
        <label class="form-label fw-bold">Task Title</label>
        <input type="text" name="title[]" class="form-control"
               placeholder="Enter task title" required>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-bold">Description</label>
        <input type="text" name="description[]" class="form-control"
               placeholder="Short description">
    </div>

    <div class="col-md-2">
        <label class="form-label fw-bold">Start Day</label>
        <input type="number" name="start_day[]" class="form-control"
               min="1" max="31" placeholder="1-31" required>
    </div>

    <div class="col-md-2">
        <label class="form-label fw-bold">End Day</label>
        <input type="number" name="end_day[]" class="form-control"
               min="1" max="31" placeholder="1-31" required>
    </div>

    <div class="col-md-2">
        <label class="form-label fw-bold">Priority</label>
        <select name="priority[]" class="form-select">
            <option value="low">Low</option>
            <option value="medium" selected>Medium</option>
            <option value="high">High</option>
        </select>
    </div>

</div>

</div>

<div class="mb-3">
    <button type="button" class="btn btn-border-body" onclick="addRow()">
        + Add More Task
    </button>
</div>

<div class="text-end">
    <button type="submit" class="btn btn-success px-4">
        Save Monthly Tasks
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
function addRow(){
    let row = `
    <div class="row align-items-end mb-3 task-row">

        <div class="col-md-3">
            <input type="text" name="title[]" class="form-control"
                   placeholder="Enter task title" required>
        </div>

        <div class="col-md-3">
            <input type="text" name="description[]" class="form-control"
                   placeholder="Short description">
        </div>

        <div class="col-md-2">
            <input type="number" name="start_day[]" class="form-control"
                   min="1" max="31" placeholder="Start Day" required>
        </div>

        <div class="col-md-2">
            <input type="number" name="end_day[]" class="form-control"
                   min="1" max="31" placeholder="End Day" required>
        </div>

        <div class="col-md-2">
            <select name="priority[]" class="form-control">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
            </select>
        </div>

    </div>`;

    document.getElementById('taskRows')
        .insertAdjacentHTML('beforeend', row);
}
</script>

@endsection
