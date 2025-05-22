@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Trashed Designations</h5>
                    <a href="{{ route('designations.index') }}" class="btn btn-primary btn-sm">Back to Designations</a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="trashed-designations-table" class="table table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
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
    // Initialize Trashed Designations DataTable
    const trashedDesignationsTable = $('#trashed-designations-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route('designations.trashed-data') }}',
        columns: [
            { data: 'name', name: 'name' },
            { data: 'department.name', name: 'department.name' },
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

    // Handle restore button click
    $('#trashed-designations-table tbody').on('click', '.restore-designation', function() {
        const button = $(this);
        const designationId = button.data('designation-id');
        const designationName = button.data('designation-name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to restore "${designationName}".`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, restore it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true);

                $.ajax({
                    url: `/designations/${designationId}/restore`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        trashedDesignationsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error('Error restoring designation');
                        console.error('Error:', xhr.responseJSON);
                        button.prop('disabled', false);
                    }
                });
            }
        });
    });

    // Handle permanent delete button click
    $('#trashed-designations-table tbody').on('click', '.force-delete-designation', function() {
        const button = $(this);
        const designationId = button.data('designation-id');
        const designationName = button.data('designation-name');

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to permanently delete "${designationName}". This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete permanently!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                button.prop('disabled', true);

                $.ajax({
                    url: `/designations/${designationId}/force-delete`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        toastr.success(response.message);
                        trashedDesignationsTable.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        toastr.error('Error permanently deleting designation');
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
