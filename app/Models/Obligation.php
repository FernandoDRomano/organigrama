<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Obligation extends Model
{
    use HasFactory;

    protected $fillable = ['description', 'job_id'];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['job'];

    /**
     * RELATIONS
     */

    public function job()
    {
        return $this->belongsTo(Job::class);
    }
}
