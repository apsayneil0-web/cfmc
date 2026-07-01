@extends('layouts.manager')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="header-content">
            <h1>User Manager</h1>
            <p>View, search, and filter user accounts by role.</p>
        </div>
        <div>
            <a href="/manager/users/create" class="btn">Create User</a>
        </div>
    </div>

    <div class="search-filter-card">
        <form class="search-box" method="get" action="/manager/users">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search by username or email" class="form-control" />
        </form>

        <div class="filter-box">
            <form method="get" action="/manager/users">
                <input type="hidden" name="q" value="{{ $q }}" />
                <select name="role" class="form-control" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->RoleName }}" {{ $roleFilter == $role->RoleName ? 'selected' : '' }}>{{ $role->RoleName }} ({{ $roleCounts[$role->RoleName]->total ?? 0 }})</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <div style="margin-bottom:12px;">
        <a href="/manager/users" class="btn {{ empty($roleFilter) ? 'active' : '' }}">All ({{ $users->total() }})</a>
        @foreach($roles as $role)
            <a href="/manager/users?role={{ urlencode($role->RoleName) }}" class="btn {{ $roleFilter == $role->RoleName ? 'active' : '' }}">{{ $role->RoleName }} ({{ $roleCounts[$role->RoleName]->total ?? 0 }})</a>
        @endforeach
    </div>

    <div class="table-card p-3">
        <table class="table" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Phone</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->RoleName ?? 'N/A' }}</td>
                        <td>{{ $user->Status ?? '' }}</td>
                        <td>{{ $user->PhoneNumber ?? '' }}</td>
                        <td>********</td>
                        <td>
                            <a href="/manager/users/{{ $user->id }}" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:12px;">
            {{ $users->links() }}
        </div>
    </div>

</div>
@endsection
