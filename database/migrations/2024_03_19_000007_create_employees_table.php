<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed']);
            $table->string('blood_group')->nullable();
            $table->string('nationality');
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('country');
            $table->string('postal_code');
            $table->string('phone');
            $table->string('email')->unique();
            $table->foreignId('department_id')->constrained()->onDelete('restrict');
            $table->foreignId('designation_id')->constrained()->onDelete('restrict');
            $table->date('joining_date');
            $table->enum('employment_status', ['active', 'on_leave', 'terminated', 'resigned']);
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern']);
            $table->foreignId('reporting_to')->nullable()->constrained('employees')->onDelete('set null');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
