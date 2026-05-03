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
.modern-btn { border-radius: 8px; padding: 0.4rem 0.8rem; font-weight: 500; font-size: 0.85rem; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid transparent; }
.modern-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 6px rgba(0,0,0,0.08); }
.modern-input { border-radius: 8px; border: 1px solid #e2e8f0; background: #f8fafc; padding: 0.4rem 1rem; box-shadow: inset 0 1px 2px rgba(0,0,0,0.01); }
.modern-input:focus { background: #fff; border-color: #cbd5e1; box-shadow: 0 0 0 3px rgba(226, 232, 240, 0.5); outline: none; }
.modern-btn-group .btn { margin-right: 0.3rem !important; border-radius: 6px !important; }
.modern-btn-group .btn:last-child { margin-right: 0 !important; }
.avatar-circle { width: 36px; height: 36px; background-color: #e0e7ff; color: #4f46e5; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; margin-right: 12px; font-size: 14px; }
</style>

<div class="card modern-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="card-title">Users <span class="badge modern-badge modern-badge-info ml-2">{{ $users->total() }}</span></h4>
            <p class="text-muted small mt-1 mb-0">Manage system users and their accounts</p>
        </div>
        <div class="d-flex align-items-center">
            <form method="GET" action="{{ route('admin.users.index') }}" class="mr-3 mb-0">
                <div class="d-flex align-items-center">
                    <input type="search" name="q" class="modern-input mr-2" style="min-width: 250px;" placeholder="Search users by name, email..." value="{{ request('q') }}">
                    <button class="btn btn-secondary modern-btn" type="submit"><i class="la la-search"></i> Search</button>
                </div>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary modern-btn" style="background:#4f46e5; border-color:#4f46e5;"><i class="la la-plus"></i> Create User</a>
        </div>
    </div>

    <div class="card-content collapse show">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th style="width:64px">ID</th>
                        <th>User Details</th>
                        <th>Phone</th>
                        <th style="width:120px">Status</th>
                        <th style="width:120px">Role</th>
                        <th style="width:180px" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $user->id }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="font-weight-bold text-dark" style="font-size:1rem;">{{ $user->name }}</a>
                                    <div class="text-muted small mt-50"><i class="la la-envelope"></i> {{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="text-muted">{{ $user->phone ?? '-' }}</span></td>
                        <td>
                            @if($user->is_active)
                                <span class="badge modern-badge modern-badge-success">Active</span>
                            @else
                                <span class="badge modern-badge modern-badge-danger">Suspended</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge modern-badge modern-badge-info">Administrator</span>
                            @else
                                <span class="badge modern-badge modern-badge-secondary">User</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group modern-btn-group" role="group">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-light border" title="Show"><i class="la la-eye text-primary"></i></a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-light border" title="Edit"><i class="la la-pencil text-info"></i></a>
                                <form method="POST" action="{{ route('admin.users.toggleActive', $user->id) }}" style="display:inline-block; margin:0;">
                                    @csrf
                                    <button class="btn btn-sm btn-light border" title="{{ $user->is_active ? 'Suspend' : 'Activate' }}">
                                        <i class="la {{ $user->is_active ? 'la-ban text-warning' : 'la-check text-success' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline-block; margin:0;" onsubmit="return confirm('Delete user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light border" title="Delete"><i class="la la-trash text-danger"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center py-3">
        <div class="small text-muted font-weight-bold">Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} results</div>
        <div>
            {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @endif
</div>
@endsection
