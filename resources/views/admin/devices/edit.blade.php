@extends('admin.layout')

@section('content')
<h1>Edit Device</h1>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.devices.update', $device->id) }}">
    @csrf
    @method('PATCH')
    <div class="mb-3">
        <label class="form-label">Serial</label>
        <input type="text" name="serial" class="form-control" value="{{ old('serial', $device->serial) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $device->name) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">IP</label>
        <input type="text" name="ip" class="form-control" value="{{ old('ip', $device->ip) }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Assign to user (optional)</label>
        <select name="user_id" class="form-select">
            <option value="">-- Unassigned --</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ $device->user_id == $u->id ? 'selected' : '' }}>{{ $u->email }} ({{ $u->name }})</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary">Save</button>
</form>

@endsection
