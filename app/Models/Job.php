<?php

namespace App\Models;

use Stevebauman\Purify\Facades\Purify;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Job extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'job_level_id', 'department_id'];

    /**
     * RELATIONS
     */

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function level(){
        return $this->belongsTo(JobLevel::class, 'job_level_id');
    }

    public function organization(){
        return $this->hasOneThrough(Organization::class, Department::class);
    }

    public function obligations(){
        return $this->hasMany(Obligation::class);
    }

    public function employes(){
        return $this->belongsToMany(Employe::class)->using(Assign::class)->withTimestamps();
    }

    /**
     * MUTATORS
     */
    public function setNameAttribute(String $value) :void
    {
        $this->attributes["name"] = Purify::clean($value);
    }

}
