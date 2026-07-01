@extends('layouts.manager')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="header-content">
            <h1>User Details</h1>
            <p>Account information for {{ $user->username }}</p>
        </div>
        <div>
            <a href="/manager/users" class="btn">Back to users</a>
        </div>
    </div>

    <div class="card p-3">
        <table class="table" style="width:100%">
            <tr><th>ID</th><td>{{ $user->id }}</td></tr>
            <tr><th>Username</th><td>{{ $user->username }}</td></tr>
            <tr><th>Email</th><td>{{ $user->email }}</td></tr>
            <tr><th>Role</th><td>{{ $user->RoleName ?? 'N/A' }}</td></tr>
            <tr><th>Status</th><td>{{ $user->Status }}</td></tr>
            <tr><th>Phone</th><td>{{ $user->PhoneNumber }}</td></tr>
            <tr>
                <th>Password</th>
                <td>
                    <div>Masked: ********</div>
                    @if($hashedPreview)
                        <div style="font-family:monospace; color:#666; margin-top:6px;">Hashed (preview): {{ $hashedPreview }}</div>
                    @endif
                </td>
            </tr>
            <tr><th>Created At</th><td>{{ $user->created_at }}</td></tr>
            <tr><th>Updated At</th><td>{{ $user->updated_at }}</td></tr>
        </table>
    </div>
</div>
@endsection
