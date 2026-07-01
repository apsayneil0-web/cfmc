@extends('layouts.manager')

@section('content')
<div class="container">
    <h1>Payment Management</h1>

    <button class="btn btn-primary">+ Record Payment</button>

    <table class="table">
        <thead>
            <tr>
                <th>Farmer</th>
                <th>Payment Type</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
    </table>
</div>
@endsection