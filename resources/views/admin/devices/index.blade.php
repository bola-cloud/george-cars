@extends('layouts.admin')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header border-0 bg-white py-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Devices <span class="badge bg-primary rounded-pill fs-6">{{ $devices->total() }}</span></h4>
            <div class="text-muted small">Manage registered devices</div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <form method="GET" action="{{ route('admin.devices.index') }}" class="m-0">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="la la-search text-muted"></i></span>
                    <input type="search" name="q" class="form-control bg-light border-0 shadow-none" placeholder="Search by name, serial, ip..." value="{{ request('q') }}">
                </div>
            </form>
            <a href="{{ route('admin.devices.create') }}" class="btn btn-primary px-4 shadow-sm" style="border-radius: 8px;">
                <i class="la la-plus"></i> Create Device
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
                        <th class="fw-medium border-0" style="width:240px">Serial</th>
                        <th class="fw-medium border-0" style="width:160px">IP</th>
                        <th class="fw-medium border-0">User</th>
                        <th class="fw-medium border-0 text-center" style="width:150px">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                @foreach($devices as $device)
                    <tr>
                        <td class="ps-4 text-muted">#{{ $device->id }}</td>
                        <td class="fw-bold text-dark">{{ $device->name ?? '-' }}</td>
                        <td>
                            <code class="bg-light text-primary px-2 py-1 rounded" style="letter-spacing: 0.5px;">{{ $device->serial }}</code>
                        </td>
                        <td><span class="text-muted small"><i class="la la-network-wired me-1"></i>{{ $device->ip ?? '-' }}</span></td>
                        <td>
                            @if($device->user)
                            <a href="{{ route('admin.users.show', $device->user->id) }}" class="text-decoration-none d-flex align-items-center">
                                <div class="bg-secondary text-white rounded-circle d-flex justify-content-center align-items-center me-2" style="width:28px; height:28px; font-size:12px;">
                                    {{ strtoupper(substr($device->user->name, 0, 1)) }}
                                </div>
                                <span class="text-dark">{{ $device->user->email }}</span>
                            </a>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.devices.edit', $device->id) }}" class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px;" title="Edit">
                                    <i class="la la-pen fs-5 text-primary"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.devices.destroy', $device->id) }}" class="m-0" onsubmit="return confirm('Delete device?')">
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

    @if($devices->hasPages())
    <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center rounded-bottom-4">
        <div class="small text-muted">Showing <strong>{{ $devices->firstItem() ?? 0 }}</strong> to <strong>{{ $devices->lastItem() ?? 0 }}</strong> of <strong>{{ $devices->total() }}</strong> entries</div>
        <div>
            {{ $devices->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @else
    <div class="pb-3 text-center text-muted small">Showing all {{ $devices->total() }} entries</div>
    @endif
</div>

@endsection
