@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header border-bottom">
        <h4 class="card-title">Devices <span class="badge badge-primary">{{ $devices->total() }}</span></h4>
        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        <div class="heading-elements">
            <div class="d-flex align-items-center">
                <form method="GET" action="{{ route('admin.devices.index') }}" class="mr-2 mb-0">
                    <div class="input-group input-group-sm">
                        <input type="search" name="q" class="form-control" placeholder="Search devices..." value="{{ request('q') }}">
                        <div class="input-group-append">
                            <button class="btn btn-secondary" type="submit"><i class="la la-search"></i> Search</button>
                        </div>
                    </div>
                </form>
                <a href="{{ route('admin.devices.create') }}" class="btn btn-sm btn-primary"><i class="la la-plus"></i> Create Device</a>
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
                        <th style="width:220px">Serial</th>
                        <th style="width:140px">IP</th>
                        <th>User</th>
                        <th style="width:120px" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($devices as $device)
                    <tr>
                        <td class="align-middle text-muted">#{{ $device->id }}</td>
                        <td class="align-middle font-weight-bold">{{ $device->name ?? '-' }}</td>
                        <td class="align-middle"><code>{{ $device->serial }}</code></td>
                        <td class="align-middle"><span class="text-muted"><i class="la la-server"></i> {{ $device->ip ?? '-' }}</span></td>
                        <td class="align-middle">
                            @if($device->user)
                                <a href="{{ route('admin.users.show', $device->user->id) }}">{{ $device->user->email }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="align-middle text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-sm btn-info" title="Edit"><i class="la la-pencil"></i></a>
                                <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" style="display:inline-block; margin:0;" onsubmit="return confirm('Delete device?')">
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

    @if($devices->hasPages())
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="small text-muted">Showing {{ $devices->firstItem() ?? 0 }} - {{ $devices->lastItem() ?? 0 }} of {{ $devices->total() }}</div>
        <div>
            {{ $devices->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
        </div>
    </div>
    @endif
</div>
@endsection
