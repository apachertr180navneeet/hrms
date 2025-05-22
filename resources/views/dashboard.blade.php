@extends('layouts.app')

@section('title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    <!-- Total Employees Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\Employee::count() }}</h3>
                <p>Total Employees</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('employees.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Departments Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \App\Models\Department::count() }}</h3>
                <p>Total Departments</p>
            </div>
            <div class="icon">
                <i class="fas fa-building"></i>
            </div>
            <a href="{{ route('departments.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Active Employees Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Models\Employee::where('status', 'active')->count() }}</h3>
                <p>Active Employees</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
            <a href="{{ route('employees.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Designations Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ \App\Models\Designation::count() }}</h3>
                <p>Total Designations</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <a href="{{ route('designations.index') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Department Distribution Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Department Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Employee Status Chart -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Employee Status</h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Employees -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Employees</h3>
            </div>
            <div class="card-body p-0">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    @foreach(\App\Models\Employee::with('department')->latest()->take(5)->get() as $employee)
                        <li class="item">
                            <div class="product-img">
                                @if($employee->profile_photo)
                                    <img src="{{ Storage::url($employee->profile_photo) }}" alt="Employee Image" class="img-size-50">
                                @else
                                    <div class="img-size-50 bg-secondary text-white d-flex align-items-center justify-content-center">
                                        {{ substr($employee->first_name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="product-info">
                                <a href="{{ route('employees.show', $employee) }}" class="product-title">
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                    <span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'danger' }} float-right">
                                        {{ ucfirst($employee->status) }}
                                    </span>
                                </a>
                                <span class="product-description">
                                    {{ $employee->department->name }}
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('employees.index') }}" class="uppercase">View All Employees</a>
            </div>
        </div>
    </div>

    <!-- Department Overview -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Department Overview</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Employees</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\App\Models\Department::withCount('employees')->get() as $department)
                                <tr>
                                    <td>{{ $department->name }}</td>
                                    <td>{{ $department->employees_count }}</td>
                                    <td>
                                        <span class="badge badge-{{ $department->status === 'active' ? 'success' : 'danger' }}">
                                            {{ ucfirst($department->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Department Distribution Chart
    var departmentCtx = document.getElementById('departmentChart').getContext('2d');
    var departmentChart = new Chart(departmentCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(\App\Models\Department::pluck('name')) !!},
            datasets: [{
                data: {!! json_encode(\App\Models\Department::withCount('employees')->pluck('employees_count')) !!},
                backgroundColor: [
                    '#17a2b8',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6f42c1',
                    '#fd7e14',
                    '#20c997',
                    '#007bff'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Employee Status Chart
    var statusCtx = document.getElementById('statusChart').getContext('2d');
    var statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [
                    {{ \App\Models\Employee::where('status', 'active')->count() }},
                    {{ \App\Models\Employee::where('status', 'inactive')->count() }}
                ],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>
@endpush