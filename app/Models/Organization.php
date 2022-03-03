<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Facades\Purify;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    /*
     * RELACIONS 
     */

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function departments(){
        return $this->hasMany(Department::class);
    }

    public function employes(){
        return $this->hasMany(Employe::class);
    }

    public function jobs(){
        return $this->hasManyThrough(Job::class, Department::class);
    }

    /**
     * MUTATORS
     */
    public function setNameAttribute(String $value) :void
    {
        $this->attributes["name"] = Purify::clean($value);
    }

}
