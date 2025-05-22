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
            <span class="badge badge-{{ $employee->status === 'active' ? 'success' : 'danger' }} status-badge"
                  style="cursor: pointer;"
                  data-employee-id="{{ $employee->id }}"
                  data-status="{{ $employee->status }}">
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
                <button type="button"
                        class="btn btn-danger btn-sm delete-employee"
                        data-employee-id="{{ $employee->id }}"
                        data-employee-name="{{ $employee->first_name }} {{ $employee->last_name }}"
                        title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center">No employees found.</td>
    </tr>
@endforelse
