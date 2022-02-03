<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
