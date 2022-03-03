<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Facades\Purify;

class JobLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'hierarchy'];

    /**
     * RELATIONS
     */

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

    public function setHierarchyAttribute($value) :void
    {
        $this->attributes["hierarchy"] = Purify::clean($value);
    }
}
