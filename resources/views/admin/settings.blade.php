@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Settings</h4>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Broker IP</label>
                <input type="text" name="broker_ip" class="form-control" value="{{ old('broker_ip', $brokerIp) }}" placeholder="e.g. 192.168.1.100">
                @error('broker_ip')<div class="text-danger small">{{ $message }}</div>@enderror
                <div class="form-text">This value is stored in your <code>.env</code> as <code>BROKER_IP</code>.</div>
            </div>
            <button class="btn btn-primary">Save</button>
        </form>
    </div>
</div>

@endsection
