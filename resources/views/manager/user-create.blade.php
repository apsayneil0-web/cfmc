@extends('layouts.manager')

@section('content')
<div class="container">
    <div class="page-header">
        <div class="header-content">
            <h1>Create User</h1>
            <p>Create a new Manager or Farmer account.</p>
        </div>
        <div>
            <a href="/manager/users" class="btn">Back</a>
        </div>
    </div>

    <div class="card p-3">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        <form method="post" action="/manager/users">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required maxlength="50" />
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required maxlength="100" />
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required minlength="8" />
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8" />
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->RoleID }}">{{ $role->RoleName }}</option>
                    @endforeach
                </select>
                <p class="muted">Managers can only create Manager or Farmer accounts; Admin accounts are not allowed here.</p>
            </div>

            <div class="form-group">
                <label>Phone (optional)</label>
                <input type="text" name="phone" class="form-control" maxlength="20" />
            </div>

            <div style="margin-top:12px;">
                <button class="btn primary" type="submit">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
