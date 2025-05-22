@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employees</h3>
                    <div class="card-tools">
                        <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Employee
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="search" placeholder="Search employees...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="department">
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="status">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($employee->profile_photo)
                                                    <img src="{{ Storage::url($employee->profile_photo) }}"
                                                         class="img-circle mr-2"
                                                         style="width: 32px; height: 32px; object-fit: cover;">
                                                @else
                                                    <div class="img-circle mr-2 bg-secondary text-white d-flex align-items-center justify-content-center"
                                                         style="width: 32px; height: 32px;">
                                                        {{ substr($employee->first_name, 0, 1) }}
                                                    </div>
                                                @endif
                                                {{ $employee->first_name }} {{ $employee->last_name }}
                                            </div>
                                        </td>
                                        <td>{{ $employee->department->name }}</td>
                                        <td>{{ $employee->designation->name }}</td>
                                        <td>{{ $employee->email }}</td>
                                        <td>{{ $employee->phone }}</td>
                                        <td>
                                            <span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'danger' }}">
                                                {{ ucfirst($employee->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('employees.show', $employee) }}"
                                                   class="btn btn-info btn-sm"
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('employees.edit', $employee) }}"
                                                   class="btn btn-primary btn-sm"
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('employees.destroy', $employee) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this employee?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No employees found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $employees->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for dropdowns
    $('#department, #status').select2({
        theme: 'bootstrap4'
    });

    // Handle search and filter
    function applyFilters() {
        const search = $('#search').val();
        const department = $('#department').val();
        const status = $('#status').val();

        window.location.href = `{{ route('employees.index') }}?search=${search}&department=${department}&status=${status}`;
    }

    // Debounce search input
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

    // Handle filter changes
    $('#department, #status').on('change', applyFilters);
});
</script>
@endpush
