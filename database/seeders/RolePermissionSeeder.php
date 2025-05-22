<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Superuser with full system control',
            ],
            [
                'name' => 'HR Manager',
                'slug' => 'hr-manager',
                'description' => 'Manages employee records, recruitment, leave, payroll, performance',
            ],
            [
                'name' => 'Employee',
                'slug' => 'employee',
                'description' => 'Limited access to view/update their own profile',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // Create Permissions
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'view-dashboard', 'module' => 'Dashboard'],

            // User Management
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'module' => 'User Management'],
            ['name' => 'Manage Roles', 'slug' => 'manage-roles', 'module' => 'User Management'],

            // Employee Management
            ['name' => 'Create Employee', 'slug' => 'create-employee', 'module' => 'Employee Management'],
            ['name' => 'Edit Employee', 'slug' => 'edit-employee', 'module' => 'Employee Management'],
            ['name' => 'View Employee', 'slug' => 'view-employee', 'module' => 'Employee Management'],

            // Attendance
            ['name' => 'Manage Attendance', 'slug' => 'manage-attendance', 'module' => 'Attendance'],
            ['name' => 'View Attendance', 'slug' => 'view-attendance', 'module' => 'Attendance'],

            // Leave Management
            ['name' => 'Manage Leave', 'slug' => 'manage-leave', 'module' => 'Leave Management'],
            ['name' => 'Apply Leave', 'slug' => 'apply-leave', 'module' => 'Leave Management'],
            ['name' => 'View Leave', 'slug' => 'view-leave', 'module' => 'Leave Management'],

            // Payroll
            ['name' => 'Process Payroll', 'slug' => 'process-payroll', 'module' => 'Payroll'],
            ['name' => 'View Payroll', 'slug' => 'view-payroll', 'module' => 'Payroll'],
            ['name' => 'View Payslip', 'slug' => 'view-payslip', 'module' => 'Payroll'],

            // Performance
            ['name' => 'Manage Performance', 'slug' => 'manage-performance', 'module' => 'Performance'],
            ['name' => 'Self Assess', 'slug' => 'self-assess', 'module' => 'Performance'],
            ['name' => 'View Performance', 'slug' => 'view-performance', 'module' => 'Performance'],

            // Recruitment
            ['name' => 'Manage Recruitment', 'slug' => 'manage-recruitment', 'module' => 'Recruitment'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'view-reports', 'module' => 'Reports'],

            // Exit Management
            ['name' => 'Manage Exit', 'slug' => 'manage-exit', 'module' => 'Exit Management'],
            ['name' => 'Submit Resignation', 'slug' => 'submit-resignation', 'module' => 'Exit Management'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign permissions to roles
        $admin = Role::where('slug', 'admin')->first();
        $hrManager = Role::where('slug', 'hr-manager')->first();
        $employee = Role::where('slug', 'employee')->first();

        // Admin gets all permissions
        $admin->permissions()->attach(Permission::all());

        // HR Manager permissions
        $hrManager->permissions()->attach(Permission::whereIn('slug', [
            'view-dashboard',
            'create-employee',
            'edit-employee',
            'view-employee',
            'manage-attendance',
            'view-attendance',
            'manage-leave',
            'view-leave',
            'process-payroll',
            'view-payroll',
            'view-payslip',
            'manage-performance',
            'view-performance',
            'manage-recruitment',
            'view-reports',
            'manage-exit',
        ])->get());

        // Employee permissions
        $employee->permissions()->attach(Permission::whereIn('slug', [
            'view-dashboard',
            'view-employee',
            'view-attendance',
            'apply-leave',
            'view-leave',
            'view-payslip',
            'self-assess',
            'view-performance',
            'submit-resignation',
        ])->get());
    }
}
