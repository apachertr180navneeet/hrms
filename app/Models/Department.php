<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'head_id',
        'status',
    ];

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function head()
    {
        return $this->belongsTo(Employee::class, 'head_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }
}
