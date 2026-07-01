@extends('layouts.manager')

@section('content')
<div class="container">
    <h1>Reports Management</h1>

    <div class="report-buttons">
        <button>Harvest Report</button>
        <button>Loan Report</button>
        <button>CBU Report</button>
        <button>Payment Report</button>
        <button>Machinery Report</button>
        <button>Financial Report</button>
    </div>

    <div class="report-preview">
        <h3>Generated Report</h3>
    </div>
</div>
@endsection