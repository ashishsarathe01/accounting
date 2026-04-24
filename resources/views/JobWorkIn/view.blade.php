@extends('layouts.app')

@section('content')
@include('layouts.header')

<div class="container mt-4">
    <h4>Job Work IN – View</h4>

    <pre>
        {{ print_r($jobWork, true) }}
    </pre>

    <pre>
        {{ print_r($jobWorkDescriptions, true) }}
    </pre>
</div>

@include('layouts.footer')
@endsection
