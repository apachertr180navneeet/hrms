<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Exception;
use Illuminate\Database\QueryException;

class DesignationController extends Controller
{
    public function index()
    {
        try {
            return view('designations.index');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the designations page.');
        }
    }

    public function trash()
    {
        try {
            return view('designations.trash');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the trash page.');
        }
    }

    public function getDesignationsData()
    {
        try {
            $designations = Designation::with('department')->select(['id', 'name', 'code', 'department_id', 'status']);

            return Datatables::of($designations)
                ->addColumn('actions', function($designation) {
                    $editUrl = route('designations.edit', $designation->id);
                    $deleteButton = '<button type="button" class="btn btn-sm btn-danger delete-designation" data-designation-id="' . $designation->id . '" data-designation-name="' . $designation->name . '">Delete</button>';
                    $editButton = '<a href="' . $editUrl . '" class="btn btn-sm btn-info">Edit</a>';
                    return $editButton . ' ' . $deleteButton;
                })
                ->addColumn('status_badge', function($designation) {
                    $statusClass = $designation->status === 'active' ? 'success' : 'danger';
                    return '<span class="badge bg-' . $statusClass . ' status-badge" style="cursor: pointer;" data-designation-id="' . $designation->id . '" data-status="' . $designation->status . '">' . ucfirst($designation->status) . '</span>';
                })
                ->rawColumns(['actions', 'status_badge'])
                ->make(true);
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching designations data.'], 500);
        }
    }

    public function getTrashedDesignationsData()
    {
        try {
            $trashedDesignations = Designation::with('department')->onlyTrashed()->select(['id', 'name', 'code', 'department_id', 'deleted_at']);

            return Datatables::of($trashedDesignations)
                ->addColumn('actions', function($designation) {
                    $restoreButton = '<button type="button" class="btn btn-sm btn-success restore-designation" data-designation-id="' . $designation->id . '" data-designation-name="' . $designation->name . '">Restore</button>';
                    $forceDeleteButton = '<button type="button" class="btn btn-sm btn-danger force-delete-designation" data-designation-id="' . $designation->id . '" data-designation-name="' . $designation->name . '">Delete Permanently</button>';
                    return $restoreButton . ' ' . $forceDeleteButton;
                })
                ->rawColumns(['actions'])
                ->make(true);
        } catch (Exception $e) {
            dd($e);
            return response()->json(['error' => 'An error occurred while fetching trashed designations data.'], 500);
        }
    }

    public function create()
    {
        try {
            $departments = Department::where('status', 'active')->get();
            return view('designations.create', compact('departments'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the create designation page.');
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:designations',
                'department_id' => 'required|exists:departments,id',
                'description' => 'nullable|string'
            ]);

            Designation::create($validated);

            return redirect()->route('designations.index')
                ->with('success', 'Designation created successfully.');
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred while creating the designation.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the designation.');
        }
    }

    public function edit(Designation $designation)
    {
        try {
            $departments = Department::where('status', 'active')->get();
            return view('designations.edit', compact('designation', 'departments'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while loading the edit designation page.');
        }
    }

    public function update(Request $request, Designation $designation)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:designations,code,' . $designation->id,
                'department_id' => 'required|exists:departments,id',
                'description' => 'nullable|string'
            ]);

            $designation->update($validated);

            return redirect()->route('designations.index')
                ->with('success', 'Designation updated successfully.');
        } catch (QueryException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Database error occurred while updating the designation.');
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the designation.');
        }
    }

    public function destroy(Designation $designation)
    {
        try {
            $designation->delete();
            return response()->json([
                'success' => true,
                'message' => 'Designation moved to trash successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the designation'
            ], 500);
        }
    }

    public function restore($id)
    {
        try {
            $designation = Designation::withTrashed()->findOrFail($id);
            $designation->restore();

            return response()->json([
                'success' => true,
                'message' => 'Designation restored successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while restoring the designation'
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        try {
            $designation = Designation::withTrashed()->findOrFail($id);
            $designation->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Designation permanently deleted'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while permanently deleting the designation'
            ], 500);
        }
    }

    public function updateStatus(Request $request, Designation $designation)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive'
            ]);

            $designation->update([
                'status' => $request->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Designation status updated successfully',
                'status' => $designation->status
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the designation status'
            ], 500);
        }
    }
}
