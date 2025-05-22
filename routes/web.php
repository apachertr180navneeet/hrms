<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Employee Management Routes
    Route::resource('employees', EmployeeController::class)->except(['show']);
    Route::patch('/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('employees.status');
    Route::post('/employees/{id}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::delete('/employees/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');
    Route::get('/employees/data', [EmployeeController::class, 'getEmployeesData'])->name('employees.data');
    Route::get('/employees/trashed-data', [EmployeeController::class, 'getTrashedEmployeesData'])->name('employees.trashed-data');
    Route::get('/employees/trash', [EmployeeController::class, 'trash'])->name('employees.trash');

    // Department Management Routes
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::patch('/departments/{department}/status', [DepartmentController::class, 'updateStatus'])->name('departments.status');
    Route::post('/departments/{id}/restore', [DepartmentController::class, 'restore'])->name('departments.restore');
    Route::delete('/departments/{id}/force-delete', [DepartmentController::class, 'forceDelete'])->name('departments.force-delete');

    Route::get('/departments/data', [DepartmentController::class, 'getDepartmentsData'])->name('departments.data');
    Route::get('/departments/trashed-data', [DepartmentController::class, 'getTrashedDepartmentsData'])->name('departments.trashed-data');
    Route::get('/departments/trash', [DepartmentController::class, 'trash'])->name('departments.trash');

    // Designation Management Routes
    Route::resource('designations', DesignationController::class)->except(['show']);
    Route::patch('/designations/{designation}/status', [DesignationController::class, 'updateStatus'])->name('designations.status');
    Route::post('/designations/{id}/restore', [DesignationController::class, 'restore'])->name('designations.restore');
    Route::delete('/designations/{id}/force-delete', [DesignationController::class, 'forceDelete'])->name('designations.force-delete');
    Route::get('/designations/data', [DesignationController::class, 'getDesignationsData'])->name('designations.data');
    Route::get('/designations/trashed-data', [DesignationController::class, 'getTrashedDesignationsData'])->name('designations.trashed-data');
    Route::get('/designations/trash', [DesignationController::class, 'trash'])->name('designations.trash');

    // API Routes for dynamic data
    Route::get('/api/departments/{department}/designations', function ($department) {
        return \App\Models\Designation::where('department_id', $department)
            ->where('status', 'active')
            ->get();
    });
});
