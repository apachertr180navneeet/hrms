<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'marital_status',
        'blood_group',
        'nationality',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'phone',
        'email',
        'department_id',
        'designation_id',
        'joining_date',
        'employment_status',
        'employment_type',
        'reporting_to',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_to');
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'reporting_to');
    }

    public function jobHistory()
    {
        return $this->hasMany(JobHistory::class);
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
