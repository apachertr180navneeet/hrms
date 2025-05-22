@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($employee) ? 'Edit Employee' : 'Add Employee' }}</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($employee) ? route('employees.update', $employee) : route('employees.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @if(isset($employee))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <h4 class="mb-3">Personal Information</h4>

                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text"
                                           class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name"
                                           name="first_name"
                                           value="{{ old('first_name', $employee->first_name ?? '') }}"
                                           required>
                                    @error('first_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text"
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           id="last_name"
                                           name="last_name"
                                           value="{{ old('last_name', $employee->last_name ?? '') }}"
                                           required>
                                    @error('last_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $employee->email ?? '') }}"
                                           required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone', $employee->phone ?? '') }}"
                                           required>
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date"
                                           class="form-control @error('date_of_birth') is-invalid @enderror"
                                           id="date_of_birth"
                                           name="date_of_birth"
                                           value="{{ old('date_of_birth', isset($employee) ? $employee->date_of_birth->format('Y-m-d') : '') }}"
                                           required>
                                    @error('date_of_birth')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-control @error('gender') is-invalid @enderror"
                                            id="gender"
                                            name="gender"
                                            required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $employee->gender ?? '') == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $employee->gender ?? '') == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $employee->gender ?? '') == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="marital_status">Marital Status</label>
                                    <select class="form-control @error('marital_status') is-invalid @enderror"
                                            id="marital_status"
                                            name="marital_status"
                                            required>
                                        <option value="">Select Marital Status</option>
                                        <option value="single" {{ old('marital_status', $employee->marital_status ?? '') == 'single' ? 'selected' : '' }}>Single</option>
                                        <option value="married" {{ old('marital_status', $employee->marital_status ?? '') == 'married' ? 'selected' : '' }}>Married</option>
                                        <option value="divorced" {{ old('marital_status', $employee->marital_status ?? '') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                        <option value="widowed" {{ old('marital_status', $employee->marital_status ?? '') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                    </select>
                                    @error('marital_status')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Address Information -->
                            <div class="col-md-6">
                                <h4 class="mb-3">Address Information</h4>

                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror"
                                              id="address"
                                              name="address"
                                              rows="3"
                                              required>{{ old('address', $employee->address ?? '') }}</textarea>
                                    @error('address')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text"
                                           class="form-control @error('city') is-invalid @enderror"
                                           id="city"
                                           name="city"
                                           value="{{ old('city', $employee->city ?? '') }}"
                                           required>
                                    @error('city')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text"
                                           class="form-control @error('state') is-invalid @enderror"
                                           id="state"
                                           name="state"
                                           value="{{ old('state', $employee->state ?? '') }}"
                                           required>
                                    @error('state')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="country">Country</label>
                                    <input type="text"
                                           class="form-control @error('country') is-invalid @enderror"
                                           id="country"
                                           name="country"
                                           value="{{ old('country', $employee->country ?? '') }}"
                                           required>
                                    @error('country')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text"
                                           class="form-control @error('postal_code') is-invalid @enderror"
                                           id="postal_code"
                                           name="postal_code"
                                           value="{{ old('postal_code', $employee->postal_code ?? '') }}"
                                           required>
                                    @error('postal_code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <!-- Employment Information -->
                            <div class="col-md-12 mt-4">
                                <h4 class="mb-3">Employment Information</h4>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="department_id">Department</label>
                                            <select class="form-control @error('department_id') is-invalid @enderror"
                                                    id="department_id"
                                                    name="department_id"
                                                    required>
                                                <option value="">Select Department</option>
                                                @foreach($departments as $department)
                                                    <option value="{{ $department->id }}"
                                                            {{ old('department_id', $employee->department_id ?? '') == $department->id ? 'selected' : '' }}>
                                                        {{ $department->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('department_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="designation_id">Designation</label>
                                            <select class="form-control @error('designation_id') is-invalid @enderror"
                                                    id="designation_id"
                                                    name="designation_id"
                                                    required>
                                                <option value="">Select Designation</option>
                                                @foreach($designations as $designation)
                                                    <option value="{{ $designation->id }}"
                                                            {{ old('designation_id', $employee->designation_id ?? '') == $designation->id ? 'selected' : '' }}>
                                                        {{ $designation->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('designation_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="reporting_to">Reporting Manager</label>
                                            <select class="form-control @error('reporting_to') is-invalid @enderror"
                                                    id="reporting_to"
                                                    name="reporting_to">
                                                <option value="">Select Manager</option>
                                                @foreach($managers as $manager)
                                                    <option value="{{ $manager->id }}"
                                                            {{ old('reporting_to', $employee->reporting_to ?? '') == $manager->id ? 'selected' : '' }}>
                                                        {{ $manager->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('reporting_to')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="joining_date">Joining Date</label>
                                            <input type="date"
                                                   class="form-control @error('joining_date') is-invalid @enderror"
                                                   id="joining_date"
                                                   name="joining_date"
                                                   value="{{ old('joining_date', isset($employee) ? $employee->joining_date->format('Y-m-d') : '') }}"
                                                   required>
                                            @error('joining_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="employment_type">Employment Type</label>
                                            <select class="form-control @error('employment_type') is-invalid @enderror"
                                                    id="employment_type"
                                                    name="employment_type"
                                                    required>
                                                <option value="">Select Employment Type</option>
                                                <option value="full_time" {{ old('employment_type', $employee->employment_type ?? '') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                                <option value="part_time" {{ old('employment_type', $employee->employment_type ?? '') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                                <option value="contract" {{ old('employment_type', $employee->employment_type ?? '') == 'contract' ? 'selected' : '' }}>Contract</option>
                                                <option value="intern" {{ old('employment_type', $employee->employment_type ?? '') == 'intern' ? 'selected' : '' }}>Intern</option>
                                            </select>
                                            @error('employment_type')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    @if(isset($employee))
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select class="form-control @error('status') is-invalid @enderror"
                                                    id="status"
                                                    name="status"
                                                    required>
                                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
                                            @error('status')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    {{ isset($employee) ? 'Update Employee' : 'Create Employee' }}
                                </button>
                                <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize select2 for better dropdown experience
        $('select').select2({
            theme: 'bootstrap4'
        });

        // Department change event to filter designations
        $('#department_id').on('change', function() {
            var departmentId = $(this).val();
            if (departmentId) {
                $.get('/api/departments/' + departmentId + '/designations', function(data) {
                    var designationSelect = $('#designation_id');
                    designationSelect.empty();
                    designationSelect.append('<option value="">Select Designation</option>');
                    $.each(data, function(key, value) {
                        designationSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                });
            }
        });
    });
</script>
@endpush
