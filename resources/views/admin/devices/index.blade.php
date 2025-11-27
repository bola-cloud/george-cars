@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Devices <small class="text-muted">({{ $devices->total() }})</small></h4>
            <div class="text-muted small">Manage registered devices</div>
        </div>

        <div class="d-flex align-items-center">
            <form method="GET" action="{{ route('admin.devices.index') }}" class="me-2">
                <div class="input-group input-group-sm">
                    <input type="search" name="q" class="form-control" placeholder="Search by name, serial, ip, or owner" value="{{ request('q') }}">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </form>
            <a href="{{ route('admin.devices.create') }}" class="btn btn-sm btn-primary">Create Device</a>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:64px">ID</th>
                        <th>Name</th>
                        <th style="width:220px">Serial</th>
                        <th style="width:140px">IP</th>
                        <th>User</th>
                        <th style="width:180px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($devices as $device)
                    <tr>
                        <td class="align-middle">{{ $device->id }}</td>
                        <td class="align-middle">{{ $device->name ?? '-' }}</td>
                        <td class="align-middle"><code class="text-monospace">{{ $device->serial }}</code></td>
                        <td class="align-middle"><span class="text-muted small">{{ $device->ip ?? '-' }}</span></td>
                        <td class="align-middle">@if($device->user)<a href="{{ route('admin.users.show', $device->user->id) }}">{{ $device->user->email }}</a>@else - @endif</td>
                        <td class="align-middle">
                            <div class="btn-group" role="group" aria-label="actions">
                                <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="la la-edit"></i></a>
                                <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="display:inline-block" onsubmit="return confirm('Delete device?')">
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
        <div class="small text-muted">Showing {{ $devices->firstItem() ?? 0 }} - {{ $devices->lastItem() ?? 0 }} of {{ $devices->total() }}</div>
        <div>
            {{ $devices->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </div>
</div>

@endsection
