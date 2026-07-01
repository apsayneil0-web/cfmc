@extends('layouts.manager')

@section('content')
<div class="container">
    <h1>Machinery Management</h1>

    <button class="btn btn-primary">+ Add Machinery</button>

    <table class="table">
        <thead>
            <tr>
                <th>Machine</th>
                <th>Brand</th>
                <th>Quantity</th>
                <th>Status</th>
            </tr>
        </thead>
    </table>
</div>
@endsection