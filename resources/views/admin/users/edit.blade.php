@extends('layouts.admin')

@section('content')
<h1>Edit User</h1>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user->id) }}">
    @csrf
    @method('PATCH')
    <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password (leave blank to keep)</label>
        <input type="password" name="password" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_admin" value="1" id="is_admin" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_admin">Is Admin</label>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Account Active</label>
    </div>
    <div class="mb-3">
        <label class="form-label">OneSignal Player ID</label>
        <input type="text" name="onesignal[player_id]" class="form-control" value="{{ old('onesignal.player_id', optional($user->onesignal)['player_id'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label class="form-label">OneSignal Device</label>
        <input type="text" name="onesignal[device]" class="form-control" value="{{ old('onesignal.device', optional($user->onesignal)['device'] ?? '') }}">
    </div>
    <button class="btn btn-primary">Save</button>
</form>

@endsection
