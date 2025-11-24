@extends('layouts.admin')

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
        <div class="input-group">
            <input type="text" id="serial" name="serial" class="form-control" value="{{ old('serial') }}">
            <button type="button" id="generate-serial" class="btn btn-outline-secondary">Generate</button>
        </div>
        <div class="form-text">Leave empty to auto-generate on save or click Generate to get a unique 14-character serial.</div>
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

@push('js')
<script>
    document.getElementById('generate-serial').addEventListener('click', function() {
        fetch("{{ route('admin.devices.generate-serial') }}", {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
        .then(resp => resp.json())
        .then(data => {
            if (data.serial) {
                document.getElementById('serial').value = data.serial;
            } else {
                alert('Failed to generate serial');
            }
        })
        .catch(() => alert('Failed to generate serial'));
    });
</script>
@endpush

@endsection
