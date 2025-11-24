@extends('admin.layout')

@section('content')
<h1>Create Device</h1>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.devices.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Serial</label>
        <input type="text" name="serial" class="form-control" value="{{ old('serial') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">IP</label>
        <input type="text" name="ip" class="form-control" value="{{ old('ip') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">Assign to user (optional)</label>
        <select name="user_id" class="form-select">
            <option value="">-- Unassigned --</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->email }} ({{ $u->name }})</option>
            @endforeach
        </select>
    </div>
    <button class="btn btn-primary">Create</button>
</form>

@endsection
