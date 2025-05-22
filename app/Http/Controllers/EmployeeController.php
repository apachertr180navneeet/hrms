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
            $employees = Employee::with(['department', 'designation', 'reportingManager'])
                ->latest()
                ->paginate(10);

            return view('employees.index', compact('employees'));
        } catch (Exception $e) {
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
            $employees = Employee::with(['department', 'designation'])
                ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'department_id', 'designation_id', 'status']);

            return Datatables::of($employees)
                ->addColumn('full_name', function($employee) {
                    return $employee->first_name . ' ' . $employee->last_name;
                })
                ->addColumn('actions', function($employee) {
                    $editUrl = route('employees.edit', $employee->id);
                    $deleteButton = '<button type="button" class="btn btn-sm btn-danger delete-employee" data-employee-id="' . $employee->id . '" data-employee-name="' . $employee->first_name . ' ' . $employee->last_name . '">Delete</button>';
                    $editButton = '<a href="' . $editUrl . '" class="btn btn-sm btn-info">Edit</a>';
                    return $editButton . ' ' . $deleteButton;
                })
                ->addColumn('status_badge', function($employee) {
                    $statusClass = $employee->status === 'active' ? 'success' : 'danger';
                    return '<span class="badge bg-' . $statusClass . ' status-badge" style="cursor: pointer;" data-employee-id="' . $employee->id . '" data-status="' . $employee->status . '">' . ucfirst($employee->status) . '</span>';
                })
                ->rawColumns(['actions', 'status_badge'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching employees data.'], 500);
        }
    }

    public function getTrashedEmployeesData()
    {
        try {
            $trashedEmployees = Employee::with(['department', 'designation'])
                ->onlyTrashed()
                ->select(['id', 'first_name', 'last_name', 'email', 'phone', 'department_id', 'designation_id', 'deleted_at']);

            return Datatables::of($trashedEmployees)
                ->addColumn('full_name', function($employee) {
                    return $employee->first_name . ' ' . $employee->last_name;
                })
                ->addColumn('actions', function($employee) {
                    $restoreButton = '<button type="button" class="btn btn-sm btn-success restore-employee" data-employee-id="' . $employee->id . '" data-employee-name="' . $employee->first_name . ' ' . $employee->last_name . '">Restore</button>';
                    $forceDeleteButton = '<button type="button" class="btn btn-sm btn-danger force-delete-employee" data-employee-id="' . $employee->id . '" data-employee-name="' . $employee->first_name . ' ' . $employee->last_name . '">Delete Permanently</button>';
                    return $restoreButton . ' ' . $forceDeleteButton;
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

    public function store(Request $request)
    {
        try {
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
                'employment_type' => 'required|in:full-time,part-time,contract,intern',
                'profile_photo' => 'nullable|image|max:2048',
                'status' => 'required|in:active,inactive'
            ]);

            DB::beginTransaction();
            try {
                if ($request->hasFile('profile_photo')) {
                    $path = $request->file('profile_photo')->store('employee-photos', 'public');
                    $validated['profile_photo'] = $path;
                }

                $employee = Employee::create($validated);

                DB::commit();
                return redirect()->route('employees.index')
                    ->with('success', 'Employee created successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Error creating employee: ' . $e->getMessage());
            }
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred while creating the employee.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the employee.');
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

            $employee->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Employee status updated successfully',
                'status' => $employee->status
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the employee status'
            ], 500);
        }
    }
}
