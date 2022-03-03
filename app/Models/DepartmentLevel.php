<?php

namespace App\Models;

use Stevebauman\Purify\Facades\Purify;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DepartmentLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'hierarchy'];

    /*
     * RELACIONS 
     */

    public function departments(){
        return $this->hasMany(Department::class);
    }

    /**
     * MUTATORS
     */
    public function setNameAttribute(String $value) :void
    {
        $this->attributes["name"] = Purify::clean($value);
    }

    public function setHierarchyAttribute($value) :void
    {
        $this->attributes["hierarchy"] = Purify::clean($value);
    }
}
