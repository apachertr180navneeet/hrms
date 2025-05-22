@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employees</h3>
                    <div class="card-tools">
                        <a href="{{ route('employees.trash') }}" class="btn btn-warning btn-sm mr-2">
                            <i class="fas fa-trash"></i> Trash
                        </a>
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
                        <div class="col-md-3">
                            <button type="button" class="btn btn-secondary" id="reset-filters">
                                <i class="fas fa-undo"></i> Reset Filters
                            </button>
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
                            <tbody id="employees-table-body">
                                @include('employees.partials.table_rows')
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3" id="pagination-links">
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

    // Function to load table data
    function loadTableData(page = 1) {
        const search = $('#search').val();
        const department = $('#department').val();
        const status = $('#status').val();

        $.ajax({
            url: `{{ route('employees.index') }}?page=${page}`,
            method: 'GET',
            data: {
                search: search,
                department: department,
                status: status
            },
            success: function(response) {
                $('#employees-table-body').html(response.table_rows);
                $('#pagination-links').html(response.pagination);
                initializeEventHandlers();
            },
            error: function(xhr) {
                toastr.error('Error loading employees data');
            }
        });
    }

    // Initialize event handlers for dynamic content
    function initializeEventHandlers() {
        // Status change handler
        $('.status-badge').on('click', function() {
            const badge = $(this);
            const employeeId = badge.data('employee-id');
            const currentStatus = badge.data('status');
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            $.ajax({
                url: `/employees/${employeeId}/status`,
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        // Update badge appearance
                        badge.removeClass('badge-success badge-danger')
                            .addClass(newStatus === 'active' ? 'badge-success' : 'badge-danger')
                            .text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1))
                            .data('status', newStatus);

                        // Reload table data to ensure consistency
                        loadTableData();

                        toastr.success('Status updated successfully');
                    } else {
                        toastr.error(response.message || 'Failed to update status');
                    }
                },
                error: function(xhr) {
                    console.error('Status update error:', xhr.responseJSON);
                    toastr.error('An error occurred while updating status');
                }
            });
        });

        // Delete handler
        $('.delete-employee').on('click', function() {
            const button = $(this);
            const employeeId = button.data('employee-id');
            const employeeName = button.data('employee-name');

            Swal.fire({
                title: `Are you sure?`,
                text: `Do you want to delete ${employeeName}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/employees/${employeeId}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                loadTableData();
                                Swal.fire('Deleted!', 'Employee moved to trash successfully.', 'success');
                            } else {
                                Swal.fire('Error', response.message || 'Failed to delete employee', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'An error occurred while deleting the employee', 'error');
                        }
                    });
                }
            });
        });
    }

    // Handle search and filter
    function applyFilters() {
        loadTableData();
    }

    // Reset filters
    $('#reset-filters').on('click', function() {
        $('#search').val('');
        $('#department').val('').trigger('change');
        $('#status').val('').trigger('change');
        loadTableData();
    });

    // Debounce search input
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });

    // Handle filter changes
    $('#department, #status').on('change', applyFilters);

    // Handle pagination clicks
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        loadTableData(page);
    });

    // Initialize event handlers on page load
    initializeEventHandlers();
});
</script>
@endpush
