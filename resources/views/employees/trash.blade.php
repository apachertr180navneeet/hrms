@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Trashed Employees</h3>
                    <div class="card-tools">
                        <a href="{{ route('employees.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Employees
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="trashed-employees-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Designation</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Deleted At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
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
    var table = $('#trashed-employees-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('employees.trashed-data') }}",
        columns: [
            {data: 'id', name: 'id'},
            {
                data: 'name',
                name: 'name',
                render: function(data, type, row) {
                    let photoHtml = '';
                    if (row.profile_photo) {
                        photoHtml = `<img src="/storage/${row.profile_photo}" class="img-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">`;
                    } else {
                        photoHtml = `<div class="img-circle mr-2 bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">${row.first_name.charAt(0)}</div>`;
                    }
                    return `<div class="d-flex align-items-center">${photoHtml}${row.first_name} ${row.last_name}</div>`;
                }
            },
            {data: 'department.name', name: 'department.name'},
            {data: 'designation.name', name: 'designation.name'},
            {data: 'email', name: 'email'},
            {data: 'phone', name: 'phone'},
            {data: 'deleted_at', name: 'deleted_at'},
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm restore-employee"
                                    data-employee-id="${row.id}"
                                    data-employee-name="${row.first_name} ${row.last_name}"
                                    title="Restore">
                                <i class="fas fa-trash-restore"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm force-delete-employee"
                                    data-employee-id="${row.id}"
                                    data-employee-name="${row.first_name} ${row.last_name}"
                                    title="Delete Permanently">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]]
    });

    // Handle restore
    $(document).on('click', '.restore-employee', function() {
        const button = $(this);
        const employeeId = button.data('employee-id');
        const employeeName = button.data('employee-name');

        Swal.fire({
            title: `Are you sure?`,
            text: `Do you want to restore ${employeeName}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, restore!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/employees/${employeeId}/restore`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            Swal.fire('Restored!', 'Employee restored successfully.', 'success');
                        } else {
                            Swal.fire('Error', response.message || 'Failed to restore employee', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'An error occurred while restoring the employee', 'error');
                    }
                });
            }
        });
    });

    // Handle permanent delete
    $(document).on('click', '.force-delete-employee', function() {
        const button = $(this);
        const employeeId = button.data('employee-id');
        const employeeName = button.data('employee-name');

        Swal.fire({
            title: `Are you sure?`,
            text: `Permanently delete ${employeeName}? This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete permanently!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/employees/${employeeId}/force-delete`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            Swal.fire('Deleted!', 'Employee permanently deleted.', 'success');
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
});
</script>
@endpush
