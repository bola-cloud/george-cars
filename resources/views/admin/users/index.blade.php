@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Users</h1>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Create User</a>
</div>

<table class="table table-striped">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Admin</th><th>Actions</th></tr></thead>
    <tbody>
    @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
            <td>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline-block" onsubmit="return confirm('Delete user?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

{{ $users->links() }}

@endsection
