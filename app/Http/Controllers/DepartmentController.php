<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Database\QueryException;
use App\Models\Designation;

class DepartmentController extends Controller
{
    public function index()
    {
        try {
            return view('departments.index');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the departments page.');
        }
    }

    public function trash()
    {
        // Trashed departments page
        return view('departments.trash');
    }

    public function getDepartmentsData()
    {
        try {
            $departments = Department::select(['id', 'name', 'status']);

            return Datatables::of($departments)
                ->addColumn('actions', function($department) {
                    $editUrl = route('departments.edit', $department->id);
                    $deleteButton = '<button type="button" class="btn btn-sm btn-danger delete-department" data-department-id="' . $department->id . '" data-department-name="' . $department->name . '">Delete</button>';
                    $editButton = '<a href="' . $editUrl . '" class="btn btn-sm btn-info">Edit</a>';
                    return $editButton . ' ' . $deleteButton;
                })
                ->addColumn('status_badge', function($department) {
                    $statusClass = $department->status === 'active' ? 'success' : 'danger';
                    return '<span class="badge bg-' . $statusClass . ' status-badge" style="cursor: pointer;" data-department-id="' . $department->id . '" data-status="' . $department->status . '">' . ucfirst($department->status) . '</span>';
                })
                ->rawColumns(['actions', 'status_badge'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching departments data.'], 500);
        }
    }

    public function getTrashedDepartmentsData()
    {
        try {
            $trashedDepartments = Department::onlyTrashed()->select(['id', 'name', 'deleted_at']);

            return Datatables::of($trashedDepartments)
                ->addColumn('actions', function($department) {
                    $restoreButton = '<button type="button" class="btn btn-sm btn-success restore-department" data-department-id="' . $department->id . '" data-department-name="' . $department->name . '">Restore</button>';
                    $forceDeleteButton = '<button type="button" class="btn btn-sm btn-danger force-delete-department" data-department-id="' . $department->id . '" data-department-name="' . $department->name . '">Delete Permanently</button>';
                    return $restoreButton . ' ' . $forceDeleteButton;
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching trashed departments data.'], 500);
        }
    }

    public function create()
    {
        try {
            $departments = Department::all();
            $employees = Employee::all();
            return view('departments.create', compact('departments', 'employees'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the create department page.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments',
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:departments,id',
                'head_id' => 'nullable|exists:employees,id'
            ]);

            Department::create($validated);

            return redirect()->route('departments.index')
                ->with('success', 'Department created successfully.');
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred while creating the department.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the department.');
        }
    }

    public function edit(Department $department)
    {
        try {
            $departments = Department::where('id', '!=', $department->id)->get();
            $employees = Employee::all();
            return view('departments.edit', compact('department', 'departments', 'employees'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the edit department page.');
        }
    }

    public function update(Request $request, Department $department)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:departments,id',
                'head_id' => 'nullable|exists:employees,id'
            ]);

            $department->update($validated);

            return redirect()->route('departments.index')
                ->with('success', 'Department updated successfully.');
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred while updating the department.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the department.');
        }
    }

    public function destroy(Department $department)
    {
        try {
            $department->delete();
            return response()->json([
                'success' => true,
                'message' => 'Department moved to trash successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the department'
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $department = Department::withTrashed()->findOrFail($id);
            $department->restore();

            return response()->json([
                'success' => true,
                'message' => 'Department restored successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while restoring the department'
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $department = Department::withTrashed()->findOrFail($id);
            $department->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Department permanently deleted'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while permanently deleting the department'
            ], 500);
        }
    }

    public function updateStatus(Request $request, Department $department)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive'
            ]);

            $department->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department status updated successfully',
                'status' => $department->status
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the department status'
            ], 500);
        }
    }

    public function getDesignations(Department $department)
    {
        try {
            $designations = Designation::where('department_id', $department->id)
                ->where('status', 'active')
                ->get(['id', 'name']);

            return response()->json($designations);
        } catch (Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to load designations. Please try again.'
            ], 500);
        }
    }
}
