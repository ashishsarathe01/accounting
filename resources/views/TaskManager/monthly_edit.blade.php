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
    <h5 class="transaction-table-title m-0 py-2">Edit Monthly Tasks</h5>
</div>

<div class="card shadow-lg border-0 mt-3">
<div class="card-body">

<form action="{{ route('task.monthly.update') }}" method="POST">
@csrf

<input type="hidden" name="id" value="{{ $template->id }}">

{{-- Assign To --}}
<div class="row mb-4">
    <div class="col-md-6">
        <label class="form-label fw-bold">Assign To</label>
        <select name="assigned_to" class="form-select" required>
            @foreach($users as $user)
                <option value="{{ $user->id }}"
                    {{ $template->assigned_to == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<hr>

<div class="row mb-3">

    <div class="col-md-4">
        <label class="form-label fw-bold">Task Title</label>
        <input type="text"
               name="title"
               value="{{ $template->title }}"
               class="form-control"
               required>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-bold">Description</label>
        <input type="text"
               name="description"
               value="{{ $template->description }}"
               class="form-control">
    </div>

    <div class="col-md-2">
        <label class="form-label fw-bold">Start Day</label>
        <input type="number"
               name="start_day"
               value="{{ $template->start_day }}"
               min="1" max="31"
               class="form-control"
               required>
    </div>

    <div class="col-md-2">
        <label class="form-label fw-bold">End Day</label>
        <input type="number"
               name="end_day"
               value="{{ $template->end_day }}"
               min="1" max="31"
               class="form-control"
               required>
    </div>

</div>

<div class="row mb-4">
    <div class="col-md-3">
        <label class="form-label fw-bold">Priority</label>
        <select name="priority" class="form-select">
            <option value="low" {{ $template->priority=='low'?'selected':'' }}>Low</option>
            <option value="medium" {{ $template->priority=='medium'?'selected':'' }}>Medium</option>
            <option value="high" {{ $template->priority=='high'?'selected':'' }}>High</option>
        </select>
    </div>
</div>

<div class="text-end">
    <a href="{{ route('task.monthly.index') }}"
       class="btn btn-border-body px-4 me-2">
        Cancel
    </a>

    <button type="submit" class="btn btn-success px-4">
        Update Monthly Task
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

@endsection
