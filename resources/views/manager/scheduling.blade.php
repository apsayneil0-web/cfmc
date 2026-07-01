@extends('layouts.manager')

@section('content')
<div class="container">
    <h1>Machine Rental Scheduling</h1>

    <button class="btn btn-primary">+ Add Schedule</button>

    <table class="table">
        <thead>
            <tr>
                <th>Farmer</th>
                <th>Machine</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Status</th>
            </tr>
        </thead>
    </table>
</div>
@endsection