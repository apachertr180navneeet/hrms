@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Designations</h5>
                    <div>
                        <a href="{{ route('designations.create') }}" class="btn btn-primary btn-sm">Add Designation</a>
                        <a href="{{ route('designations.trash') }}" class="btn btn-danger btn-sm">View Trash</a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="designations-table" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Department</th>
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
    // Initialize Active Designations DataTable
    const designationsTable = $('#designations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('designations.data') }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'code', name: 'code' },
            { data: 'department.name', name: 'department.name' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ]
    });

    // Handle status badge click
    $('#designations-table tbody').on('click', '.status-badge', function() {
        const badge = $(this);
        const designationId = badge.data('designation-id');
        const currentStatus = badge.data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

        badge.css('pointer-events', 'none');

        $.ajax({
            url: `/designations/${designationId}/status`,
            method: 'PATCH',
            data: {
                status: newStatus,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    badge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
                    badge.removeClass('bg-success bg-danger')
                        .addClass(response.status === 'active' ? 'bg-success' : 'bg-danger');
                    badge.data('status', response.status);
                    toastr.success(response.message);
                } else {
                    toastr.error('Failed to update status');
                }
            },
            error: function(xhr) {
                toastr.error('Error updating status');
                console.error('Error:', xhr.responseJSON);
            },
            complete: function() {
                badge.css('pointer-events', 'auto');
            }
        });
    });

    // Handle delete button click
    $('#designations-table tbody').on('click', '.delete-designation', function() {
        const button = $(this);
        const designationId = button.data('designation-id');
        const designationName = button.data('designation-name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to move "${designationName}" to trash.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, move to trash!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true);

                $.ajax({
                    url: `/designations/${designationId}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        designationsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error('Error moving to trash');
                        console.error('Error:', xhr.responseJSON);
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Display session messages
    @if (session('success'))
        toastr.success('{{ session('success') }}');
    @endif

    @if (session('error'))
        toastr.error('{{ session('error') }}');
    @endif
});
</script>
@endpush
@endsection
