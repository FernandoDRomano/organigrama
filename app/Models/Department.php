<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'organization_id', 'department_level_id', 'department_id'];

    /*
     * RELACIONS
     */

    public function organization(){
        return $this->belongsTo(Organization::class);
    }

    public function departments(){
        return $this->hasMany(Department::class, 'department_id');
    }

    public function children(){
        return $this->hasMany(Department::class, 'department_id')->with('departments');
    }

    public function level(){
        return $this->belongsTo(DepartmentLevel::class, 'department_level_id');
    }

    public function jobs(){
        return $this->hasMany(Job::class);
    }
}
