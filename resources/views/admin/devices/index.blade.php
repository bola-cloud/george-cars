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
.modern-badge-info { background-color: #e0e7ff !important; color: #4f46e5 !important; border: 1px solid #c7d2fe; }
.modern-btn { border-radius: 8px; padding: 0.5rem 1rem; font-weight: 500; font-size: 0.85rem; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: none; }
.modern-btn-primary { background-color: #0ea5e9; color: #fff; }
.modern-btn-primary:hover { background-color: #0284c7; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(14,165,233,0.2); }
.modern-btn-circle { border-radius: 50%; width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; background: #fff; color: #64748b; transition: all 0.2s; }
.modern-btn-circle:hover { background: #f8fafc; color: #0ea5e9; border-color: #0ea5e9; }
.modern-btn-circle.danger:hover { color: #ef4444; border-color: #ef4444; background: #fef2f2; }
.search-wrapper .form-control { border-radius: 8px 0 0 8px; border-color: #e2e8f0; padding: 0.5rem 1rem; focus-color: #0ea5e9; font-size:0.9rem;}
.search-wrapper .form-control:focus { box-shadow: none; border-color: #0ea5e9; }
.search-wrapper .btn { border-radius: 0 8px 8px 0; border: 1px solid #e2e8f0; border-left: none; background: #f8fafc; color: #475569; padding: 0.5rem 1rem; }
.search-wrapper .btn:hover { background: #e2e8f0; }
.pagination { margin: 0; }
.page-item .page-link { border: none; padding: 0.5rem 0.8rem; border-radius: 6px; color: #64748b; margin: 0 2px; }
.page-item.active .page-link { background-color: #0ea5e9; color: #fff; box-shadow: 0 2px 4px rgba(14,165,233,0.3); }
</style>

<div class="content-header row mb-3 align-items-center">
    <div class="col-md-6 col-12">
        <h3 class="content-header-title mb-0" style="font-weight:700; color:#1e293b;">
            Devices Database
        </h3>
        <p class="text-muted small mt-1 mb-0">Manage system registered devices</p>
    </div>
</div>

<div class="card modern-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px;">
        <h4 class="card-title d-flex align-items-center">
            All Devices
            <span class="badge modern-badge modern-badge-info ml-2">{{ $devices->total() }}</span>
        </h4>
        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
            <form method="GET" action="{{ route('admin.devices.index') }}" class="m-0">
                <div class="input-group search-wrapper">
                    <div class="input-group-prepend">
                        <span class="input-group-text" style="background:#fff; border-color:#e2e8f0; border-right:none; border-radius:8px 0 0 8px;"><i class="la la-search text-muted"></i></span>
                    </div>
                    <input type="search" name="q" class="form-control" style="border-left:none; border-radius:0;" placeholder="Search serial or name..." value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button class="btn" type="submit" style="border-left:1px solid #e2e8f0; border-radius: 0 8px 8px 0;">Search</button>
                    </div>
                </div>
            </form>
            <a href="{{ route('admin.devices.create') }}" class="btn modern-btn modern-btn-primary">
                <i class="la la-plus"></i> New
            </a>
        </div>
    </div>

    <div class="card-content">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th style="width:64px">System ID</th>
                        <th>Device Name</th>
                        <th style="width:220px">Serial / Identifier</th>
                        <th style="width:140px">Network IP</th>
                        <th>Owner Account</th>
                        <th style="width:120px" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($devices as $device)
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $device->id }}</td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $device->name ?? '-' }}</div>
                        </td>
                        <td>
                            <code style="background:#f1f5f9; padding:4px 8px; border-radius:6px; color:#475569; font-size:0.85rem;">{{ $device->serial }}</code>
                        </td>
                        <td>
                            <span style="color:#64748b;"><i class="la la-wifi mr-1" style="color:#94a3b8"></i>{{ $device->ip ?? '-' }}</span>
                        </td>
                        <td>
                            @if($device->user)
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle" style="background:#e0e7ff; color:#4f46e5; width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; border-radius:50%; margin-right:10px; font-size:0.8rem;">
                                        <i class="la la-user"></i>
                                    </div>
                                    <a href="{{ route('admin.users.show', $device->user->id) }}" style="color:#0ea5e9; font-weight:500;">
                                        {{ $device->user->email }}
                                    </a>
                                </div>
                            @else
                                <span class="badge modern-badge modern-badge-secondary">Unassigned</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center">
                                <a href="{{ route('admin.devices.edit', $device->id) }}" class="modern-btn-circle mr-2" title="Edit Properties"><i class="la la-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="margin:0;" onsubmit="return confirm('Permanently delete this device?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="modern-btn-circle danger" title="Delete Device"><i class="la la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="la la-server text-light" style="font-size:3rem; display:block; margin-bottom:10px;"></i>
                            No devices registered in the system yet.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($devices->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center" style="background: #fff; border-top: 1px solid #f3f4f6; padding: 1rem 1.5rem;">
        <div class="small text-muted font-weight-500">
            Showing {{ $devices->firstItem() ?? 0 }} to {{ $devices->lastItem() ?? 0 }} of {{ $devices->total() }} devices
        </div>
        <div>
            {{ $devices->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @endif
</div>
@endsection
