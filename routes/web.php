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
    Route::resource('employees', EmployeeController::class);

    // Department Management Routes
    Route::resource('departments', DepartmentController::class);

    // Designation Management Routes
    Route::resource('designations', DesignationController::class);

    // API Routes for dynamic data
    Route::get('/api/departments/{department}/designations', function ($department) {
        return \App\Models\Designation::where('department_id', $department)
            ->where('status', 'active')
            ->get();
    });
});
