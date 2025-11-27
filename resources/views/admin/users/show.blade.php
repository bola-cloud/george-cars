@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>User: {{ $user->name }} &lt;{{ $user->email }}&gt;</h1>
    <div>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-secondary">Edit User</a>
        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete user?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Delete User</button>
        </form>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <p><strong>Phone:</strong> {{ $user->phone }}</p>
        <p><strong>Admin:</strong> {{ $user->is_admin ? 'Yes' : 'No' }}</p>
    </div>
</div>

<h3>Devices</h3>
<table class="table table-striped">
    <thead>
        <tr><th>ID</th><th>Name</th><th>Serial</th><th>IP</th><th>Actions</th></tr>
    </thead>
    <tbody>
    @forelse($user->devices as $device)
        <tr>
            <td>{{ $device->id }}</td>
            <td>{{ $device->name }}</td>
            <td>{{ $device->serial }}</td>
            <td>{{ $device->ip }}</td>
            <td>
                <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="display:inline-block" onsubmit="return confirm('Delete device?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5">No devices</td></tr>
    @endforelse
    </tbody>
</table>

@endsection
