<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obligation extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'job_id'];

    /**
     * RELATIONS
     */

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
