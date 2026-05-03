@extends('layouts.admin')

@section('content')
<h1>Create User</h1>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.users.store') }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="is_admin" {{ old('is_admin') ? 'checked' : '' }}>
        <label class="form-check-label" for="is_admin">Is Admin</label>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Account Active</label>
    </div>
    <div class="mb-3">
        <label class="form-label">OneSignal Player ID</label>
        <input type="text" name="onesignal[player_id]" class="form-control" value="{{ old('onesignal.player_id') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">OneSignal Device</label>
        <input type="text" name="onesignal[device]" class="form-control" value="{{ old('onesignal.device') }}">
    </div>
    <button class="btn btn-primary">Create</button>
</form>

@endsection
