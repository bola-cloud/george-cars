@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title">Users <span class="badge badge-primary">{{ $users->total() }}</span></h4>
        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        <div class="heading-elements">
            <div class="d-flex align-items-center">
                <form method="GET" action="{{ route('admin.users.index') }}" class="mr-2 mb-0">
                    <div class="input-group input-group-sm">
                        <input type="search" name="q" class="form-control" placeholder="Search users by name, email or phone" value="{{ request('q') }}">
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="submit"><i class="la la-search"></i> Search</button>
                        </div>
                    </div>
                </form>
                <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary"><i class="la la-plus"></i> Create User</a>
            </div>
        </div>
    </div>

    <div class="card-content collapse show">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="bg-light">
                    <tr>
                        <th style="width:64px">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th style="width:100px">Status</th>
                        <th style="width:110px">Role</th>
                        <th style="width:160px" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td class="align-middle text-muted">#{{ $user->id }}</td>
                        <td class="align-middle">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="font-weight-bold">{{ $user->name }}</a>
                        </td>
                        <td class="align-middle"><a href="mailto:{{ $user->email }}" class="text-muted">{{ $user->email }}</a></td>
                        <td class="align-middle">{{ $user->phone ?? '-' }}</td>
                        <td class="align-middle">
                            @if($user->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Suspended</span>
                            @endif
                        </td>
                        <td class="align-middle">
                            @if($user->is_admin)
                                <span class="badge badge-info">Administrator</span>
                            @else
                                <span class="badge badge-secondary">User</span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-info" title="Show"><i class="la la-eye"></i></a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-secondary" title="Edit"><i class="la la-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.users.toggleActive', $user->id) }}" style="display:inline-block; margin:0;">
                                    @csrf
                                    <button class="btn btn-sm {{ $user->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $user->is_active ? 'Suspend' : 'Activate' }}">
                                        <i class="la {{ $user->is_active ? 'la-ban' : 'la-check' }}"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline-block; margin:0;" onsubmit="return confirm('Delete user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="la la-trash"></i></button>
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
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="small text-muted">Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} of {{ $users->total() }}</div>
        <div>
            {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @endif
</div>
@endsection
