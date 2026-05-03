@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header border-0 bg-white py-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Users <span class="badge bg-primary rounded-pill fs-6">{{ $users->total() }}</span></h4>
            <div class="text-muted small">Manage application users and accounts</div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="m-0">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="la la-search text-muted"></i></span>
                    <input type="search" name="q" class="form-control bg-light border-0 shadow-none" placeholder="Search by name, email, phone..." value="{{ request('q') }}">
                </div>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary px-4 shadow-sm" style="border-radius: 8px;">
                <i class="la la-plus"></i> Create User
            </a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted">
                    <tr>
                        <th class="ps-4 fw-medium border-0" style="width:80px">ID</th>
                        <th class="fw-medium border-0">Name</th>
                        <th class="fw-medium border-0">Email</th>
                        <th class="fw-medium border-0">Phone</th>
                        <th class="fw-medium border-0" style="width:110px">Status</th>
                        <th class="fw-medium border-0" style="width:110px">Role</th>
                        <th class="fw-medium border-0 text-center" style="width:190px">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                @foreach($users as $user)
                    <tr>
                        <td class="ps-4 text-muted">#{{ $user->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="fw-bold text-dark text-decoration-none d-flex align-items-center">
                                <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width:32px; height:32px; font-size:14px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                {{ $user->name }}
                            </a>
                        </td>
                        <td><a href="mailto:{{ $user->email }}" class="text-muted text-decoration-none">{{ $user->email }}</a></td>
                        <td class="text-muted">{{ $user->phone ?? '-' }}</td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2 border border-danger-subtle">Suspended</span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_admin)
                                <span class="badge bg-primary rounded-pill px-3 py-2">Administrator</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3 py-2 text-dark">User</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Show">
                                    <i class="la la-eye fs-5 text-info"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Edit">
                                    <i class="la la-pen fs-5 text-primary"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.users.toggleActive', $user->id) }}" class="m-0">
                                    @csrf
                                    <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="{{ $user->is_active ? 'Suspend' : 'Activate' }}">
                                        <i class="la {{ $user->is_active ? 'la-ban text-warning' : 'la-check text-success' }} fs-5"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" class="m-0" onsubmit="return confirm('Delete user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Delete">
                                        <i class="la la-trash fs-5 text-danger"></i>
                                    </button>
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
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center rounded-bottom-4">
        <div class="small text-muted">Showing <strong>{{ $users->firstItem() ?? 0 }}</strong> to <strong>{{ $users->lastItem() ?? 0 }}</strong> of <strong>{{ $users->total() }}</strong> entries</div>
        <div>
            {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @else
    <div class="pb-3 text-center text-muted small">Showing all {{ $users->total() }} entries</div>
    @endif
</div>

@endsection
