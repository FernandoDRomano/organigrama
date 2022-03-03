<?php

namespace App\Models;

use Stevebauman\Purify\Facades\Purify;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    /**
     * MUTATORS
     */
    public function setNameAttribute(String $value) :void
    {
        $this->attributes["name"] = Purify::clean($value);
    }
}
