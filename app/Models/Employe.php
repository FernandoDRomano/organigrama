<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
