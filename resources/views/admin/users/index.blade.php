@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Users <small class="text-muted">({{ $users->total() }})</small></h4>
            <div class="text-muted small">Manage application users</div>
        </div>

        <div class="d-flex align-items-center">
            <form method="GET" action="{{ route('admin.users.index') }}" class="me-2">
                <div class="input-group input-group-sm">
                    <input type="search" name="q" class="form-control" placeholder="Search users by name, email or phone" value="{{ request('q') }}">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">Create User</a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:64px">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th style="width:90px">Admin</th>
                        <th style="width:220px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="align-middle">{{ $user->id }}</td>
                        <td class="align-middle"><a href="{{ route('admin.users.show', $user->id) }}" class="fw-bold text-decoration-none">{{ $user->name }}</a></td>
                        <td class="align-middle"><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                        <td class="align-middle">{{ $user->phone ?? '-' }}</td>
                        <td class="align-middle">
                            @if($user->is_admin)
                                <span class="badge bg-success">Admin</span>
                            @else
                                <span class="badge bg-secondary">User</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            <div class="btn-group" role="group" aria-label="actions">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-outline-info" title="Show"><i class="la la-eye"></i></a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="la la-edit"></i></a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline-block" onsubmit="return confirm('Delete user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="la la-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="small text-muted">Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</div>
        <div>
            {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

@endsection
