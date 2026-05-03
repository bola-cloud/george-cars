@extends('layouts.admin')

@section('content')
<div class="content-header row mb-3">
    <div class="content-header-left col-md-6 col-12 mb-2">
        <h3 class="content-header-title mb-0">User Details <small class="text-muted">#{{ $user->id }}</small></h3>
    </div>
    <div class="content-header-right col-md-6 col-12">
        <div class="btn-group float-md-right">
            <form action="{{ route('admin.users.toggleActive', $user->id) }}" method="POST" style="margin:0;">
                @csrf
                @if($user->is_active)
                    <button class="btn btn-warning" onclick="return confirm('Suspend user account?')">
                        <i class="la la-ban"></i> Suspend
                    </button>
                @else
                    <button class="btn btn-success" onclick="return confirm('Activate user account?')">
                        <i class="la la-check"></i> Activate
                    </button>
                @endif
            </form>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-info"><i class="la la-pencil"></i> Edit</a>
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete user completely?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger"><i class="la la-trash"></i> Delete</button>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header border-bottom">
                <h4 class="card-title">User Information</h4>
            </div>
            <div class="card-content collapse show">
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <th class="w-25 text-muted">Name</th>
                            <td class="font-weight-bold">{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Phone</th>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted">Role</th>
                            <td>
                                @if($user->is_admin)
                                    <span class="badge badge-info">Administrator</span>
                                @else
                                    <span class="badge badge-secondary">User</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Status</th>
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">Suspended</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted">Registered</th>
                            <td>{{ $user->created_at ? $user->created_at->format('Y-m-d H:i') : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title">Devices <span class="badge badge-primary">{{ $user->devices->count() }}</span></h4>
    </div>
    <div class="card-content collapse show">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width:64px;">ID</th>
                        <th>Name</th>
                        <th>Serial</th>
                        <th>IP</th>
                        <th class="text-center" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
        @forelse($user->devices as $device)
            <tr>
                <td class="align-middle text-muted">#{{ $device->id }}</td>
                <td class="align-middle font-weight-bold">{{ $device->name }}</td>
                <td class="align-middle"><code>{{ $device->serial }}</code></td>
                <td class="align-middle"><i class="la la-server text-muted"></i> {{ $device->ip }}</td>
                <td class="align-middle text-center">
                    <div class="btn-group">
                        <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-sm btn-info"><i class="la la-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="margin:0;" onsubmit="return confirm('Delete device?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="la la-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center py-4 text-muted">No devices associated with this user.</td></tr>
        @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="content-header row mt-4 mb-2">
    <div class="col-12">
        <h3 class="content-header-title mb-0">Sharing & Permissions</h3>
        <p class="text-muted">Manage how devices are shared according to the new API approach.</p>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title"><i class="la la-globe text-primary"></i> Universal Shares (user_shares)</h4>
        <p class="card-subtitle text-muted mt-1">This user shared <b>ALL</b> their current and future devices with the users below. This acts as a fallback or baseline permission.</p>
    </div>
    <div class="card-content collapse show">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width:25%;">Shared With</th>
                        <th>Baseline Permissions</th>
                        <th class="text-center" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
            @forelse($userShares as $uShare)
                <tr>
                    <td class="align-middle">
                        <div class="font-weight-bold">{{ $uShare->user->name ?? 'Unknown' }}</div>
                        <div class="text-muted small">Target ID: #{{ $uShare->user_id }}</div>
                    </td>
                    <td class="align-middle">
                        @php
                            $perms = $uShare->meta['permissions'] ?? [];
                            $allPerms = ['can_open', 'can_stop', 'can_close', 'can_delete', 'can_rename', 'can_schedule', 'can_change_type', 'can_manage_presets'];
                        @endphp
                        <form action="{{ route('admin.user_shares.update', $uShare->id) }}" method="POST" style="margin:0;">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                @foreach($allPerms as $p)
                                <div class="col-md-3 mb-1">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="hidden" name="permissions[{{ $p }}]" value="0">
                                        <input class="custom-control-input" type="checkbox" name="permissions[{{ $p }}]" value="1" id="uShare_{{$uShare->id}}_{{$p}}" {{ isset($perms[$p]) && $perms[$p] ? 'checked' : '' }}>
                                        <label class="custom-control-label" style="font-size: 0.85rem;" for="uShare_{{$uShare->id}}_{{$p}}">{{ ucfirst(str_replace('can_', '', $p)) }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-1"><i class="la la-save"></i> Save</button>
                        </form>
                    </td>
                    <td class="text-center align-middle">
                        <form method="POST" action="{{ route('admin.user_shares.destroy', $uShare->id) }}" style="margin:0;" onsubmit="return confirm('Revoke this share?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="la la-trash"></i> Revoke</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-4 text-muted">No universal shares found.</td></tr>
            @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title"><i class="la la-mobile-phone text-info"></i> Specific Device Shares (device_shares)</h4>
        <p class="card-subtitle text-muted mt-1">This user shared a <b>specific device</b> with the users below. These permissions override the universal share if both exist.</p>
    </div>
    <div class="card-content collapse show">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width:20%;">Device</th>
                        <th style="width:20%;">Shared With</th>
                        <th>Device-Level Permissions</th>
                        <th class="text-center" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
            @forelse($deviceShares as $dShare)
                <tr>
                    <td class="align-middle">
                        <div class="font-weight-bold">{{ $dShare->device->name ?? 'Unknown' }}</div>
                        <div class="text-muted small">Device ID: #{{ $dShare->device_id }}</div>
                    </td>
                    <td class="align-middle">
                        <div class="font-weight-bold">{{ $dShare->user->name ?? 'Unknown' }}</div>
                        <div class="text-muted small">Target ID: #{{ $dShare->user_id }}</div>
                    </td>
                    <td class="align-middle">
                        @php
                            $perms = $dShare->meta['permissions'] ?? [];
                            $allPerms = ['can_open', 'can_stop', 'can_close', 'can_delete', 'can_rename', 'can_schedule', 'can_change_type', 'can_manage_presets'];
                        @endphp
                        <form action="{{ route('admin.device_shares.update', $dShare->id) }}" method="POST" style="margin:0;">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                @foreach($allPerms as $p)
                                <div class="col-md-3 mb-1">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="hidden" name="permissions[{{ $p }}]" value="0">
                                        <input class="custom-control-input" type="checkbox" name="permissions[{{ $p }}]" value="1" id="dShare_{{$dShare->id}}_{{$p}}" {{ isset($perms[$p]) && $perms[$p] ? 'checked' : '' }}>
                                        <label class="custom-control-label" style="font-size: 0.85rem;" for="dShare_{{$dShare->id}}_{{$p}}">{{ ucfirst(str_replace('can_', '', $p)) }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-1"><i class="la la-save"></i> Save</button>
                        </form>
                    </td>
                    <td class="text-center align-middle">
                        <form method="POST" action="{{ route('admin.device_shares.destroy', $dShare->id) }}" style="margin:0;" onsubmit="return confirm('Revoke this share?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="la la-trash"></i> Revoke</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-4 text-muted">No specific device shares found.</td></tr>
            @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
