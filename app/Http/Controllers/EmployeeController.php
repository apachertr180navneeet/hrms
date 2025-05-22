<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Database\QueryException;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $query = Employee::with(['department', 'designation', 'reportingManager'])
                ->latest();

            // Apply search filter
            if (request()->has('search') && !empty(request('search'))) {
                $searchValue = request('search');
                $query->where(function($q) use ($searchValue) {
                    $q->where('first_name', 'like', "%{$searchValue}%")
                      ->orWhere('last_name', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('phone', 'like', "%{$searchValue}%");
                });
            }

            // Apply department filter
            if (request()->has('department') && !empty(request('department'))) {
                $query->where('department_id', request('department'));
            }

            // Apply status filter
            if (request()->has('status') && !empty(request('status'))) {
                $query->where('status', request('status'));
            }

            $employees = $query->paginate(10);
            $departments = Department::where('status', 'active')->get();

            if (request()->ajax()) {
                return response()->json([
                    'table_rows' => view('employees.partials.table_rows', compact('employees'))->render(),
                    'pagination' => $employees->links()->toHtml()
                ]);
            }

            return view('employees.index', compact('employees', 'departments'));
        } catch (Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => 'An error occurred while loading the employees data.'], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while loading the employees page.');
        }
    }

    public function trash()
    {
        try {
            return view('employees.trash');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the trash page.');
        }
    }

    public function getEmployeesData()
    {
        try {
            $query = Employee::with(['department', 'designation'])
                ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'department_id', 'designation_id', 'status', 'profile_photo']);

            // Apply search filter
            if (request()->has('search') && !empty(request('search')['value'])) {
                $searchValue = request('search')['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('first_name', 'like', "%{$searchValue}%")
                      ->orWhere('last_name', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('phone', 'like', "%{$searchValue}%");
                });
            }

            // Apply department filter
            if (request()->has('department') && !empty(request('department'))) {
                $query->where('department_id', request('department'));
            }

            // Apply status filter
            if (request()->has('status') && !empty(request('status'))) {
                $query->where('status', request('status'));
            }

            return DataTables::of($query)
                ->addColumn('name', function($employee) {
                    return $employee->first_name . ' ' . $employee->last_name;
                })
                ->addColumn('actions', function($employee) {
                    return ''; // This will be rendered by DataTables
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching employees data.'], 500);
        }
    }

    public function getTrashedEmployeesData()
    {
        try {
            $query = Employee::with(['department', 'designation'])
                ->onlyTrashed()
                ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'department_id', 'designation_id', 'deleted_at', 'profile_photo']);

            return DataTables::of($query)
                ->addColumn('name', function($employee) {
                    return $employee->first_name . ' ' . $employee->last_name;
                })
                ->addColumn('actions', function($employee) {
                    return ''; // This will be rendered by DataTables
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching trashed employees data.'], 500);
        }
    }

    public function create()
    {
        try {
            $departments = Department::where('status', 'active')->get();
            $designations = Designation::where('status', 'active')->get();
            $managers = Employee::where('status', 'active')->get();

            return view('employees.form', compact('departments', 'designations', 'managers'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the create employee page.');
        }
    }

    private function generateEmployeeId($departmentId)
    {
        // Get department code
        $department = Department::find($departmentId);
        $deptCode = strtoupper(substr($department->code, 0, 3));

        // Get current year
        $year = date('Y');

        // Get the last employee number for this department and year
        $lastEmployee = Employee::where('employee_id', 'like', $deptCode . '-' . $year . '-%')
            ->orderBy('employee_id', 'desc')
            ->first();

        if ($lastEmployee) {
            // Extract the number part and increment
            $lastNumber = (int)substr($lastEmployee->employee_id, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            // Start with 0001 if no previous employee
            $newNumber = '0001';
        }

        // Format: DEPTCODE-YEAR-XXXX (e.g., IT-2024-0001)
        $newEmployeeId = $deptCode . '-' . $year . '-' . $newNumber;

        // Double check if the ID is unique
        while (Employee::where('employee_id', $newEmployeeId)->exists()) {
            $newNumber = str_pad((int)$newNumber + 1, 4, '0', STR_PAD_LEFT);
            $newEmployeeId = $deptCode . '-' . $year . '-' . $newNumber;
        }

        return $newEmployeeId;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'address' => 'required|string',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'joining_date' => 'required|date',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Create user account
            $user = \App\Models\User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => bcrypt('password'), // Default password
                'role' => 'employee'
            ]);

            // Add user_id to validated data
            $validated['user_id'] = $user->id;

            // Generate unique employee ID
            $validated['employee_id'] = $this->generateEmployeeId($validated['department_id']);

            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store('employee-photos', 'public');
                $validated['profile_photo'] = $path;
            }

            $employee = Employee::create($validated);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully with ID: ' . $validated['employee_id'],
                'redirect' => route('employees.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'designation', 'reportingManager']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        try {
            $departments = Department::where('status', 'active')->get();
            $designations = Designation::where('status', 'active')->get();
            $managers = Employee::where('status', 'active')
                ->where('id', '!=', $employee->id)
                ->get();

            return view('employees.form', compact('employee', 'departments', 'designations', 'managers'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the edit employee page.');
        }
    }

    public function update(Request $request, Employee $employee)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:employees,email,' . $employee->id,
                'phone' => 'required|string|max:20',
                'date_of_birth' => 'required|date',
                'gender' => 'required|in:male,female,other',
                'marital_status' => 'required|in:single,married,divorced,widowed',
                'address' => 'required|string',
                'city' => 'required|string|max:255',
                'state' => 'required|string|max:255',
                'country' => 'required|string|max:255',
                'postal_code' => 'required|string|max:20',
                'department_id' => 'required|exists:departments,id',
                'designation_id' => 'required|exists:designations,id',
                'reporting_manager_id' => 'nullable|exists:employees,id',
                'joining_date' => 'required|date',
                'employment_type' => 'required|in:full-time,part-time,contract,intern',
                'profile_photo' => 'nullable|image|max:2048',
                'status' => 'required|in:active,inactive'
            ]);

            DB::beginTransaction();
            try {
                if ($request->hasFile('profile_photo')) {
                    // Delete old photo if exists
                    if ($employee->profile_photo) {
                        Storage::disk('public')->delete($employee->profile_photo);
                    }
                    $path = $request->file('profile_photo')->store('employee-photos', 'public');
                    $validated['profile_photo'] = $path;
                }

                $employee->update($validated);

                DB::commit();
                return redirect()->route('employees.index')
                    ->with('success', 'Employee updated successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Error updating employee: ' . $e->getMessage());
            }
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred while updating the employee.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the employee.');
        }
    }

    public function destroy(Employee $employee)
    {
        try {
            if ($employee->profile_photo) {
                Storage::disk('public')->delete($employee->profile_photo);
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee moved to trash successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the employee'
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->restore();

            return response()->json([
                'success' => true,
                'message' => 'Employee restored successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while restoring the employee'
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);

            // Delete profile photo if exists
            if ($employee->profile_photo) {
                Storage::disk('public')->delete($employee->profile_photo);
            }

            // Delete associated user account
            if ($employee->user) {
                $employee->user->delete();
            }

            $employee->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Employee permanently deleted'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while permanently deleting the employee'
            ], 500);
        }
    }

    public function updateStatus(Request $request, Employee $employee)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive'
            ]);

            DB::beginTransaction();
            try {
                $employee->update([
                    'status' => $request->status
                ]);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Employee status updated successfully',
                    'status' => $employee->status
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating employee status: ' . $e->getMessage()
                ], 500);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the employee status'
            ], 500);
        }
    }
}
