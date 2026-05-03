@extends('layouts.admin')

@section('content')
<style>
/* Modern UI Overrides */
.modern-card { border: none !important; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important; margin-bottom: 1.5rem; overflow: hidden; background: #fff; }
.modern-card .card-header { background-color: #fff !important; border-bottom: 1px solid #f3f4f6 !important; padding: 1.5rem 1.5rem 1rem !important; }
.modern-card .card-title { font-weight: 700; color: #1e293b; font-size: 1.15rem; margin: 0; }
.modern-table { margin-bottom: 0; }
.modern-table thead th { border-bottom: none !important; border-top: none !important; background-color: #f8fafc !important; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem 1.25rem; }
.modern-table tbody td { padding: 1rem 1.25rem; vertical-align: middle; border-top: 1px solid #f3f4f6; color: #475569; font-size: 0.9rem; }
.modern-table tbody tr:hover { background-color: #f8fafc; }
.modern-badge { padding: 0.45em 0.85em; border-radius: 6px; font-weight: 600; letter-spacing: 0.3px; }
.modern-badge-success { background-color: #d1fae5 !important; color: #059669 !important; border: 1px solid #a7f3d0; }
.modern-badge-danger { background-color: #fee2e2 !important; color: #dc2626 !important; border: 1px solid #fecaca; }
.modern-badge-info { background-color: #e0e7ff !important; color: #4f46e5 !important; border: 1px solid #c7d2fe; }
.modern-badge-secondary { background-color: #f1f5f9 !important; color: #475569 !important; border: 1px solid #e2e8f0; }
.modern-badge-warning { background-color: #fef3c7 !important; color: #d97706 !important; border: 1px solid #fde68a; }
.modern-btn { border-radius: 8px; padding: 0.4rem 0.8rem; font-weight: 500; font-size: 0.85rem; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.modern-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.08); }
.modern-btn-circle { border-radius: 50%; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: #fff; color: #64748b; transition: all 0.2s; }
.modern-btn-circle:hover { background: #f8fafc; color: #0ea5e9; border-color: #0ea5e9; }
.modern-btn-circle.danger:hover { color: #ef4444; border-color: #ef4444; background: #fef2f2; }
.user-info-row th { width: 30%; color: #64748b; font-weight: 500; padding: 0.8rem 1rem; border-bottom: 1px solid #f3f4f6; }
.user-info-row td { color: #1e293b; font-weight: 600; padding: 0.8rem 1rem; border-bottom: 1px solid #f3f4f6; }
.user-info-row:last-child th, .user-info-row:last-child td { border-bottom: none; }
.info-section-icon { padding: 12px; background: #f0fdf4; color: #16a34a; border-radius: 12px; margin-right: 15px; font-size: 1.5rem; }
.info-section-icon.blue { background: #eff6ff; color: #3b82f6; }
.info-section-icon.purple { background: #faf5ff; color: #a855f7; }
</style>

<div class="content-header row mb-3 align-items-center">
    <div class="col-md-6 col-12">
        <h3 class="content-header-title mb-0" style="font-weight:700; color:#1e293b;">
            <i class="la la-user mr-1 text-primary"></i> {{ $user->name }}
        </h3>
        <p class="text-muted small mt-1 mb-0">{{ $user->email }}</p>
    </div>
    <div class="col-md-6 col-12 text-md-right mt-2 mt-md-0">
        <div class="btn-group">
            <form action="{{ route('admin.users.toggleActive', $user->id) }}" method="POST" style="margin:0;" class="mr-2">
                @csrf
                @if($user->is_active)
                    <button class="btn btn-white modern-btn modern-badge-warning" onclick="return confirm('Suspend user account?')">
                        <i class="la la-ban"></i> Suspend
                    </button>
                @else
                    <button class="btn btn-white modern-btn modern-badge-success" onclick="return confirm('Activate user account?')">
                        <i class="la la-check"></i> Activate
                    </button>
                @endif
            </form>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-white modern-btn modern-badge-info mr-2"><i class="la la-pencil"></i> Edit Profile</a>
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete user completely?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-white modern-btn modern-badge-danger"><i class="la la-trash"></i> Delete</button>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card modern-card">
            <div class="card-header d-flex align-items-center">
                <div class="info-section-icon blue"><i class="la la-info-circle"></i></div>
                <h4 class="card-title">User Information</h4>
            </div>
            <div class="card-content">
                <div class="card-body p-0">
                    <table class="table table-borderless table-sm mb-0">
                        <tr class="user-info-row">
                            <th>Account ID</th>
                            <td>#{{ $user->id }}</td>
                        </tr>
                        <tr class="user-info-row">
                            <th>Full Name</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr class="user-info-row">
                            <th>Phone Number</th>
                            <td>{{ $user->phone ?? 'Not Provided' }}</td>
                        </tr>
                        <tr class="user-info-row">
                            <th>System Role</th>
                            <td>
                                @if($user->is_admin)
                                    <span class="badge modern-badge modern-badge-info">Administrator</span>
                                @else
                                    <span class="badge modern-badge modern-badge-secondary">Standard User</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="user-info-row">
                            <th>Current Status</th>
                            <td>
                                @if($user->is_active)
                                    <span class="badge modern-badge modern-badge-success">Active Account</span>
                                @else
                                    <span class="badge modern-badge modern-badge-danger">Suspended</span>
                                @endif
                            </td>
                        </tr>
                        <tr class="user-info-row">
                            <th>Join Date</th>
                            <td>{{ $user->created_at ? $user->created_at->format('F j, Y - H:i') : 'Unknown' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card modern-card mt-2">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <div class="info-section-icon"><i class="la la-server"></i></div>
            <div>
                <h4 class="card-title">Owned Devices <span class="badge modern-badge modern-badge-info ml-2">{{ $user->devices->count() }}</span></h4>
                <p class="text-muted small mt-1 mb-0">Hardware devices currently registered to this user</p>
            </div>
        </div>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th style="width:80px;">ID</th>
                        <th>Device Identity</th>
                        <th>Network IP</th>
                        <th class="text-center" style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
        @forelse($user->devices as $device)
            <tr>
                <td class="text-muted font-weight-bold">#{{ $device->id }}</td>
                <td>
                    <div class="font-weight-bold text-dark" style="font-size:1rem;">{{ $device->name }}</div>
                    <code class="mt-1 d-inline-block">{{ $device->serial }}</code>
                </td>
                <td>
                    <span class="text-secondary"><i class="la la-wifi mr-1"></i>{{ $device->ip }}</span>
                </td>
                <td class="text-center">
                    <div class="d-flex justify-content-center">
                        <a href="{{ route('admin.devices.edit', $device->id) }}" class="modern-btn-circle mr-2" title="Edit"><i class="la la-pencil"></i></a>
                        <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="margin:0;" onsubmit="return confirm('Delete device?')">
                            @csrf
                            @method('DELETE')
                            <button class="modern-btn-circle danger" title="Delete"><i class="la la-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center py-5 text-muted"><i class="la la-inbox text-light" style="font-size:3rem; display:block; margin-bottom:10px;"></i>No devices associated with this user yet.</td></tr>
        @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="content-header row mt-5 mb-3">
    <div class="col-12">
        <h3 class="content-header-title mb-0" style="font-weight:700; color:#1e293b;">Device Sharing Permissions</h3>
        <p class="text-muted mt-1">Manage explicit device shares for this user based on the newest API logic.</p>
    </div>
</div>

<div class="card modern-card">
    <div class="card-header border-bottom d-flex align-items-center">
        <div class="info-section-icon blue" style="background:#eff6ff; color:#2563eb;"><i class="la la-mobile-phone"></i></div>
        <div>
            <h4 class="card-title">Specific Device Shares (device_shares)</h4>
            <p class="text-muted small mt-1 mb-0">Manage permissions for devices shared specifically with targeted users.</p>
        </div>
    </div>
    <div class="card-content">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th style="width:20%;">Resource (Device)</th>
                        <th style="width:20%;">Shared With User</th>
                        <th>Explicit Features / Permissions</th>
                        <th class="text-center" style="width:120px;">Control</th>
                    </tr>
                </thead>
                <tbody>
            @forelse($deviceShares as $dShare)
                <tr>
                    <td class="align-middle">
                        <div class="font-weight-bold text-dark" style="font-size:1rem;">{{ $dShare->device->name ?? 'Unknown' }}</div>
                        <div class="text-muted small mt-50">Device ID: #{{ $dShare->device_id }}</div>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle" style="background:#f1f5f9; color:#475569; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:50%; margin-right:15px;">
                                <i class="la la-user"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-dark">{{ $dShare->user->name ?? 'Unknown' }}</div>
                                <div class="text-muted small mt-50">Target ID: #{{ $dShare->user_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        @php
                            $perms = $dShare->meta['permissions'] ?? [];
                            $allPerms = ['can_open', 'can_stop', 'can_close', 'can_delete', 'can_rename', 'can_schedule', 'can_change_type', 'can_manage_presets'];
                        @endphp
                        <form action="{{ route('admin.device_shares.update', $dShare->id) }}" method="POST" style="margin:0;">
                            @csrf
                            @method('PUT')
                            <div class="row pt-2 pb-1 bg-light rounded px-2" style="border: 1px solid #f1f5f9;">
                                @foreach($allPerms as $p)
                                <div class="col-md-3 mb-2">
                                    <div class="custom-control custom-switch custom-control-inline">
                                        <input type="hidden" name="permissions[{{ $p }}]" value="0">
                                        <input class="custom-control-input" type="checkbox" name="permissions[{{ $p }}]" value="1" id="dShare_{{$dShare->id}}_{{$p}}" {{ isset($perms[$p]) && $perms[$p] ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold text-secondary" style="font-size: 0.80rem;" for="dShare_{{$dShare->id}}_{{$p}}">{{ ucfirst(str_replace('can_', '', $p)) }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-sm btn-white modern-btn modern-badge-info"><i class="la la-save"></i> Save Rules</button>
                            </div>
                        </form>
                    </td>
                    <td class="text-center align-middle">
                        <form method="POST" action="{{ route('admin.device_shares.destroy', $dShare->id) }}" style="margin:0;" onsubmit="return confirm('Revoke this device share?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-white modern-btn modern-badge-danger"><i class="la la-ban"></i> Revoke</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-5 text-muted"><i class="la la-shield text-light" style="font-size:3rem; display:block; margin-bottom:10px;"></i>No specific device shares active.</td></tr>
            @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
