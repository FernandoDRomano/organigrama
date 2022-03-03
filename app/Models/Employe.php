<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Facades\Purify;

class Employe extends Model
{
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'dni', 'date_of_birth', 'address', 'organization_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * RELATIONS
     */

    public function organization(){
        return $this->belongsTo(Organization::class);
    }

    public function jobs(){
        return $this->belongsToMany(Job::class)->using(Assign::class)->withTimestamps();
    }

    /**
     * MUTATORS
     */
    public function setFirstNameAttribute(String $value) :void
    {
        $this->attributes["first_name"] = Purify::clean($value);
    }

    public function setLastNameAttribute(String $value) :void
    {
        $this->attributes["last_name"] = Purify::clean($value);
    }

    public function setDniAttribute(String $value) :void
    {
        $this->attributes["dni"] = Purify::clean($value);
    }

    public function setDateOfBirthAttribute(String $value) :void
    {
        $this->attributes["date_of_birth"] = Purify::clean($value);
    }

    public function setAddressAttribute(String $value) :void
    {
        $this->attributes["address"] = Purify::clean($value);
    }

}
