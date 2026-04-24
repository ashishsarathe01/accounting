@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Mail Settings</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('company.mail.settings.update') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label>SMTP Host</label>
            <input type="text" name="smtp_host" class="form-control"
                value="{{ old('smtp_host', $company->smtp_host ?? 'smtp.gmail.com') }}">
        </div>

        <div class="mb-3">
            <label>SMTP Port</label>
            <input type="number" name="smtp_port" class="form-control"
                value="{{ old('smtp_port', $company->smtp_port ?? 587) }}">
        </div>

        <div class="mb-3">
            <label>SMTP Username (Email)</label>
            <input type="email" name="smtp_username" class="form-control"
                value="{{ old('smtp_username', $company->smtp_username) }}">
        </div>

        <div class="mb-3">
            <label>SMTP Password</label>
            <input type="password" name="smtp_password" class="form-control">
            <small>Leave blank to keep existing password</small>
        </div>

        <div class="mb-3">
            <label>Encryption</label>
            <select name="smtp_encryption" class="form-control">
                <option value="tls" {{ $company->smtp_encryption == 'tls' ? 'selected' : '' }}>TLS</option>
                <option value="ssl" {{ $company->smtp_encryption == 'ssl' ? 'selected' : '' }}>SSL</option>
                <option value="null" {{ $company->smtp_encryption == null ? 'selected' : '' }}>None</option>
            </select>
        </div>

        <div class="mb-3">
            <label>From Name</label>
            <input type="text" name="smtp_from_name" class="form-control"
                value="{{ old('smtp_from_name', $company->smtp_from_name ?? $company->company_name) }}">
        </div>

        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>

    <hr>

    <form action="{{ route('company.mail.settings.test') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success">Send Test Email</button>
    </form>
</div>
@endsection