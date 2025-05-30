@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Departments</h5>
                    <a href="{{ route('departments.create') }}" class="btn btn-primary btn-sm">Add Department</a>
                    <a href="{{ route('departments.trash') }}" class="btn btn-danger btn-sm">View Trash</a>
                </div>

                <div class="card-body">
                    {{-- Session messages handled by Toastr --}}

                    <div class="table-responsive">
                        <table id="departments-table" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTables will load data here --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Active Departments DataTable
    const departmentsTable = $('#departments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('departments.data') }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Initialize Trashed Departments DataTable
    const trashedDepartmentsTable = $('#trashed-departments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('departments.trashed-data') }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'deleted_at', name: 'deleted_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Handle status badge click (delegated event for DataTables)
    $('#departments-table tbody').on('click', '.status-badge', function() {
        const badge = $(this);
        const departmentId = badge.data('department-id');
        const currentStatus = badge.data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

        // Disable badge while processing
        badge.css('pointer-events', 'none');

        // Send AJAX request
        $.ajax({
            url: `/departments/${departmentId}/status`,
            method: 'PATCH',
            data: {
                status: newStatus,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Update badge text and class directly for immediate feedback (optional)
                    badge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
                    badge.removeClass('bg-success bg-danger')
                        .addClass(response.status === 'active' ? 'bg-success' : 'bg-danger');
                    badge.data('status', response.status);

                    // Show success message
                    toastr.success(response.message);

                    // Reload DataTables to reflect changes (especially if status affects filtering/sorting)
                    // departmentsTable.ajax.reload(null, false); // Reload without resetting pagination

                } else {
                    toastr.error('Failed to update status');
                }
            },
            error: function(xhr) {
                toastr.error('Error updating status');
                console.error('Error:', xhr.responseJSON);
            },
            complete: function() {
                // Re-enable badge
                badge.css('pointer-events', 'auto');
            }
        });
    });

    // Handle delete button click (delegated event for DataTables)
    $('#departments-table tbody').on('click', '.delete-department', function() {
        const button = $(this);
        const departmentId = button.data('department-id');
        const departmentName = button.data('department-name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to move "${departmentName}" to trash.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, move to trash!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button while processing
                button.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/departments/${departmentId}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Show success message
                        toastr.success(response.message);

                        // Reload both tables
                        departmentsTable.ajax.reload(null, false);
                        trashedDepartmentsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error('Error moving to trash');
                        console.error('Error:', xhr.responseJSON);
                        // Re-enable button on error
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Handle restore button click (delegated event for DataTables)
    $('#trashed-departments-table tbody').on('click', '.restore-department', function() {
        const button = $(this);
        const departmentId = button.data('department-id');
        const departmentName = button.data('department-name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to restore "${departmentName}".`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button while processing
                button.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/departments/${departmentId}/restore`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Show success message
                        toastr.success(response.message);

                        // Reload both tables
                        departmentsTable.ajax.reload(null, false);
                        trashedDepartmentsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error('Error restoring department');
                        console.error('Error:', xhr.responseJSON);
                        // Re-enable button on error
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Handle permanent delete button click (delegated event for DataTables)
    $('#trashed-departments-table tbody').on('click', '.force-delete-department', function() {
        const button = $(this);
        const departmentId = button.data('department-id');
        const departmentName = button.data('department-name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to permanently delete "${departmentName}". This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete permanently!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Disable button while processing
                button.prop('disabled', true);

                // Send AJAX request
                $.ajax({
                    url: `/departments/${departmentId}/force-delete`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // Show success message
                        toastr.success(response.message);

                        // Reload trash table
                        trashedDepartmentsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error('Error permanently deleting department');
                        console.error('Error:', xhr.responseJSON);
                        // Re-enable button on error
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Display session success message with Toastr (if any)
    @if (session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    // Display session error message with Toastr (if any)
    @if (session('error'))
        toastr.error('{{ session('error') }}');
    @endif
});
</script>
@endpush
@endsection
