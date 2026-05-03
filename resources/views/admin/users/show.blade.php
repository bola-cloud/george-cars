@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1 fw-bold text-dark d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-3 shadow-sm" style="width:48px; height:48px; font-size:20px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            {{ $user->name }}
        </h2>
        <div class="text-muted ms-5 ps-3">{{ $user->email }}</div>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.users.toggleActive', $user->id) }}" method="POST" class="m-0">
            @csrf
            @if($user->is_active)
                <button class="btn btn-warning shadow-sm border-warning rounded-3 px-3" onclick="return confirm('Suspend user account?')">
                    <i class="la la-ban me-1"></i> Suspend Account
                </button>
            @else
                <button class="btn btn-success shadow-sm border-success rounded-3 px-3" onclick="return confirm('Activate user account?')">
                    <i class="la la-check me-1"></i> Activate Account
                </button>
            @endif
        </form>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-light shadow-sm rounded-3 px-3 border"><i class="la la-pen text-primary me-1"></i> Edit User</a>
        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="m-0" onsubmit="return confirm('Delete user completely?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger shadow-sm rounded-3 px-3"><i class="la la-trash me-1"></i> Delete User</button>
        </form>
    </div>
</div>

<div class="row align-items-stretch mb-4 g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header border-0 bg-white py-3">
                <h5 class="mb-0 fw-bold">User Information</h5>
            </div>
            <div class="card-body pt-0">
                <table class="table table-borderless align-middle mb-0">
                    <tr>
                        <td class="text-muted ps-0" style="width: 120px;">Status</td>
                        <td class="fw-medium">
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 border border-danger-subtle">Suspended</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Phone</td>
                        <td class="fw-medium">{{ $user->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Role</td>
                        <td class="fw-medium">
                            @if($user->is_admin)
                                <span class="badge bg-primary rounded-pill px-3 py-2">Administrator</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 text-dark">User</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Registered</td>
                        <td class="fw-medium">{{ $user->created_at ? $user->created_at->format('M d, Y - H:i') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header border-0 bg-white py-4 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">Devices <span class="badge bg-primary rounded-pill ms-1">{{ $user->devices->count() }}</span></h5>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th class="ps-4 fw-medium border-0" style="width:80px">ID</th>
                        <th class="fw-medium border-0">Name</th>
                        <th class="fw-medium border-0">Serial</th>
                        <th class="fw-medium border-0">IP</th>
                        <th class="fw-medium border-0 text-center" style="width:120px">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
        @forelse($user->devices as $device)
            <tr>
                <td class="ps-4 text-muted">#{{ $device->id }}</td>
                <td class="fw-bold">{{ $device->name }}</td>
                <td><code class="bg-light text-primary px-2 py-1 rounded" style="letter-spacing: 0.5px;">{{ $device->serial }}</code></td>
                <td><span class="text-muted small"><i class="la la-network-wired me-1"></i>{{ $device->ip }}</span></td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Edit">
                            <i class="la la-pen text-primary fs-5"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" class="m-0" onsubmit="return confirm('Delete device?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Delete">
                                <i class="la la-trash text-danger fs-5"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center py-5 text-muted"><i class="la la-inbox fs-1 d-block mb-2 text-light"></i>No devices associated with this user.</td></tr>
        @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<h4 class="fw-bold mt-5 mb-4 px-2 text-dark">Sharing and Permissions</h4>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header border-0 bg-white py-4">
        <h5 class="mb-0 fw-bold text-primary"><i class="la la-globe me-2"></i>Users allowed universally by this user</h5>
        <div class="text-muted small mt-1">These users have access to all devices owned by {{ $user->name }}.</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th class="ps-4 fw-medium border-0" style="width:25%">Shared With</th>
                        <th class="fw-medium border-0">Permissions</th>
                        <th class="fw-medium border-0 text-center" style="width: 120px">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
            @forelse($userShares as $uShare)
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-3" style="width:36px; height:36px; font-size:14px;">
                                {{ strtoupper(substr($uShare->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <div class="fw-bold text-dark">{{ $uShare->user->name ?? 'Unknown' }}</div>
                                <div class="text-muted small">ID: #{{ $uShare->user_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3">
                        @php
                            $perms = $uShare->meta['permissions'] ?? [];
                            $allPerms = ['can_open', 'can_stop', 'can_close', 'can_delete', 'can_rename', 'can_schedule', 'can_change_type', 'can_manage_presets'];
                        @endphp
                        <form action="{{ route('admin.user_shares.update', $uShare->id) }}" method="POST" class="m-0">
                            @csrf
                            @method('PUT')
                            <div class="bg-light rounded-3 p-3 mb-2 row g-3">
                                @foreach($allPerms as $p)
                                <div class="col-md-auto">
                                    <div class="form-check form-switch form-check-inline m-0 d-flex align-items-center">
                                        <input type="hidden" name="permissions[{{ $p }}]" value="0">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" name="permissions[{{ $p }}]" value="1" id="uShare_{{$uShare->id}}_{{$p}}" {{ isset($perms[$p]) && $perms[$p] ? 'checked' : '' }}>
                                        <label class="form-check-label text-muted fw-medium" style="font-size: 0.85rem;" for="uShare_{{$uShare->id}}_{{$p}}">{{ ucfirst(str_replace('can_', '', $p)) }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm btn-light border px-3 rounded-pill text-primary fw-medium shadow-sm"><i class="la la-save me-1"></i> Save Changes</button>
                        </form>
                    </td>
                    <td class="text-center align-middle">
                        <form method="POST" action="{{ route('admin.user_shares.destroy', $uShare->id) }}" class="m-0" onsubmit="return confirm('Revoke this share?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width:36px; height:36px;" title="Revoke Share">
                                <i class="la la-trash text-danger fs-5"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center py-5 text-muted"><i class="la la-users fs-1 d-block mb-2 text-light"></i>No universal user shares.</td></tr>
            @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header border-0 bg-white py-4">
        <h5 class="mb-0 fw-bold text-info"><i class="la la-microchip me-2"></i>Specific Devices shared by this user</h5>
        <div class="text-muted small mt-1">Specific device access granted to particular users.</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th class="ps-4 fw-medium border-0" style="width:20%">Device</th>
                        <th class="fw-medium border-0" style="width:20%">Shared With</th>
                        <th class="fw-medium border-0">Permissions</th>
                        <th class="fw-medium border-0 text-center" style="width: 120px">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
            @forelse($deviceShares as $dShare)
                <tr>
                    <td class="ps-4 py-3">
                        <div class="fw-bold text-dark">{{ $dShare->device->name ?? 'Unknown' }}</div>
                        <div class="text-muted small"><i class="la la-hashtag me-1"></i>{{ $dShare->device_id }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width:28px; height:28px; font-size:12px;">
                                {{ strtoupper(substr($dShare->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <div class="fw-medium text-dark">{{ $dShare->user->name ?? 'Unknown' }}</div>
                        </div>
                    </td>
                    <td class="py-3">
                        @php
                            $perms = $dShare->meta['permissions'] ?? [];
                            $allPerms = ['can_open', 'can_stop', 'can_close', 'can_delete', 'can_rename', 'can_schedule', 'can_change_type', 'can_manage_presets'];
                        @endphp
                        <form action="{{ route('admin.device_shares.update', $dShare->id) }}" method="POST" class="m-0">
                            @csrf
                            @method('PUT')
                            <div class="bg-light rounded-3 p-3 mb-2 row g-3">
                                @foreach($allPerms as $p)
                                <div class="col-md-auto">
                                    <div class="form-check form-switch form-check-inline m-0 d-flex align-items-center">
                                        <input type="hidden" name="permissions[{{ $p }}]" value="0">
                                        <input class="form-check-input mt-0 me-2" type="checkbox" name="permissions[{{ $p }}]" value="1" id="dShare_{{$dShare->id}}_{{$p}}" {{ isset($perms[$p]) && $perms[$p] ? 'checked' : '' }}>
                                        <label class="form-check-label text-muted fw-medium" style="font-size: 0.85rem;" for="dShare_{{$dShare->id}}_{{$p}}">{{ ucfirst(str_replace('can_', '', $p)) }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <button type="submit" class="btn btn-sm btn-light border px-3 rounded-pill text-primary fw-medium shadow-sm"><i class="la la-save me-1"></i> Save Changes</button>
                        </form>
                    </td>
                    <td class="text-center align-middle">
                        <form method="POST" action="{{ route('admin.device_shares.destroy', $dShare->id) }}" class="m-0" onsubmit="return confirm('Revoke this share?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width:36px; height:36px;" title="Revoke Share">
                                <i class="la la-trash text-danger fs-5"></i>
                            </button>
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
