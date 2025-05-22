@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Trashed Departments</h5>
                    <a href="{{ route('departments.index') }}" class="btn btn-primary btn-sm">Back to Departments</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="trashed-departments-table" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Deleted At</th>
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
    // Initialize Trashed Departments DataTable
    const trashedDepartmentsTable = $('#trashed-departments-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('departments.trashed-data') }}',
        columns: [
            { data: 'name', name: 'name' },
            {
                data: 'deleted_at',
                name: 'deleted_at',
                render: function(data) {
                    return moment(data).format('DD-MM-YYYY');
                }
            },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
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

                        // Reload trash table
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
