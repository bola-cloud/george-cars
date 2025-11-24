@extends('admin.layout')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Devices</h1>
    <a href="{{ route('admin.devices.create') }}" class="btn btn-primary">Create Device</a>
</div>

<table class="table table-striped">
    <thead><tr><th>ID</th><th>Name</th><th>Serial</th><th>IP</th><th>User</th><th>Actions</th></tr></thead>
    <tbody>
    @foreach($devices as $device)
        <tr>
            <td>{{ $device->id }}</td>
            <td>{{ $device->name }}</td>
            <td>{{ $device->serial }}</td>
            <td>{{ $device->ip }}</td>
            <td>{{ $device->user ? $device->user->email : '-' }}</td>
            <td>
                <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="display:inline-block" onsubmit="return confirm('Delete device?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $devices->links() }}

@endsection
