@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>User details: {{ $user->name }} <small class="text-muted">&lt;{{ $user->email }}&gt;</small></h2>
    <div>
        <form action="{{ route('admin.users.toggleActive', $user->id) }}" method="POST" style="display:inline-block">
            @csrf
            @if($user->is_active)
                <button class="btn btn-warning me-2" onclick="return confirm('Suspend user account?')">
                    <i class="fas fa-ban"></i> Suspend Account
                </button>
            @else
                <button class="btn btn-success me-2" onclick="return confirm('Activate user account?')">
                    <i class="fas fa-check"></i> Activate Account
                </button>
            @endif
        </form>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-secondary me-2">Edit User</a>
        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Delete user completely?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Delete User</button>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <th class="w-25">Status</th>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Suspended</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $user->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            @if($user->is_admin)
                                <span class="badge bg-primary">Admin</span>
                            @else
                                <span class="badge bg-secondary">User</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Registered</th>
                        <td>{{ $user->created_at ? $user->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Devices</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-hover mb-0">
            <thead class="table-light">
                <tr><th>ID</th><th>Name</th><th>Serial</th><th>IP</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
    @forelse($user->devices as $device)
        <tr>
            <td>{{ $device->id }}</td>
            <td>{{ $device->name }}</td>
            <td>{{ $device->serial }}</td>
            <td>{{ $device->ip }}</td>
            <td class="text-end">
                <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="display:inline-block" onsubmit="return confirm('Delete device?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center py-3 text-muted">No devices associated with this user.</td></tr>
    @endforelse
    </tbody>
        </table>
    </div>
</div>

<hr class="my-5">

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Users allowed universally by this user (User Shares)</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr><th>Shared With</th><th>Permissions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($userShares as $uShare)
                <tr>
                    <td class="align-middle"><strong>{{ $uShare->user->name ?? 'Unknown' }}</strong> <br><small class="text-muted">ID: {{ $uShare->user_id }}</small></td>
                    <td>
                        @php
                            $perms = $uShare->meta['permissions'] ?? [];
                            $allPerms = ['can_open', 'can_stop', 'can_close', 'can_delete', 'can_rename', 'can_schedule', 'can_change_type', 'can_manage_presets'];
                        @endphp
                        <form action="{{ route('admin.user_shares.update', $uShare->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-2">
                                @foreach($allPerms as $p)
                                <div class="col-md-3">
                                    <div class="form-check form-switch form-check-inline">
                                        <input type="hidden" name="permissions[{{ $p }}]" value="0">
                                        <input class="form-check-input" type="checkbox" name="permissions[{{ $p }}]" value="1" id="uShare_{{$uShare->id}}_{{$p}}" {{ isset($perms[$p]) && $perms[$p] ? 'checked' : '' }}>
                                        <label class="form-check-label" style="font-size: 0.85rem;" for="uShare_{{$uShare->id}}_{{$p}}">{{ str_replace('can_', '', $p) }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-2">Save Permissions</button>
                        </form>
                    </td>
                    <td class="text-end align-middle">
                        <form method="POST" action="{{ route('admin.user_shares.destroy', $uShare->id) }}" onsubmit="return confirm('Revoke this share?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Revoke</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-3 text-muted">No universal user shares</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Specific Devices shared by this user (Device Shares)</h5>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr><th>Device</th><th>Shared With</th><th>Permissions</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($deviceShares as $dShare)
                <tr>
                    <td class="align-middle"><strong>{{ $dShare->device->name ?? 'Unknown' }}</strong> <br><small class="text-muted">ID: {{ $dShare->device_id }}</small></td>
                    <td class="align-middle"><strong>{{ $dShare->user->name ?? 'Unknown' }}</strong> <br><small class="text-muted">ID: {{ $dShare->user_id }}</small></td>
                    <td>
                        @php
                            $perms = $dShare->meta['permissions'] ?? [];
                            $allPerms = ['can_open', 'can_stop', 'can_close', 'can_delete', 'can_rename', 'can_schedule', 'can_change_type', 'can_manage_presets'];
                        @endphp
                        <form action="{{ route('admin.device_shares.update', $dShare->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-2">
                                @foreach($allPerms as $p)
                                <div class="col-md-3">
                                    <div class="form-check form-switch form-check-inline">
                                        <input type="hidden" name="permissions[{{ $p }}]" value="0">
                                        <input class="form-check-input" type="checkbox" name="permissions[{{ $p }}]" value="1" id="dShare_{{$dShare->id}}_{{$p}}" {{ isset($perms[$p]) && $perms[$p] ? 'checked' : '' }}>
                                        <label class="form-check-label" style="font-size: 0.85rem;" for="dShare_{{$dShare->id}}_{{$p}}">{{ str_replace('can_', '', $p) }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-2">Save Permissions</button>
                        </form>
                    </td>
                    <td class="text-end align-middle">
                        <form method="POST" action="{{ route('admin.device_shares.destroy', $dShare->id) }}" onsubmit="return confirm('Revoke this share?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Revoke</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-3 text-muted">No specific device shares</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
