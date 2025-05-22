@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employee Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('employees.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Employees
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Personal Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Employee ID</th>
                                    <td>{{ $employee->employee_id }}</td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $employee->first_name }} {{ $employee->last_name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $employee->email }}</td>
                                </tr>
                                <tr>
                                    <th>Phone</th>
                                    <td>{{ $employee->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Date of Birth</th>
                                    <td>{{ \Carbon\Carbon::parse($employee->date_of_birth)->format('d-m-y') }}</td>
                                </tr>
                                <tr>
                                    <th>Gender</th>
                                    <td>{{ ucfirst($employee->gender) }}</td>
                                </tr>
                                <tr>
                                    <th>Marital Status</th>
                                    <td>{{ ucfirst($employee->marital_status) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>Work Information</h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Department</th>
                                    <td>{{ $employee->department->name }}</td>
                                </tr>
                                <tr>
                                    <th>Designation</th>
                                    <td>{{ $employee->designation->name }}</td>
                                </tr>
                                <tr>
                                    <th>Reporting Manager</th>
                                    <td>{{ $employee->reportingManager ? $employee->reportingManager->first_name . ' ' . $employee->reportingManager->last_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Joining Date</th>
                                    <td>{{ \Carbon\Carbon::parse($employee->joining_date)->format('d-m-y') }}</td>
                                </tr>
                                <tr>
                                    <th>Employment Type</th>
                                    <td>{{ ucfirst($employee->employment_type) }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ ucfirst($employee->status) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
