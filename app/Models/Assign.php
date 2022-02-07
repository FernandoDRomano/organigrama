<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Assign extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
    protected $model = "employe_job";
    protected $fillable = ['employe_id', 'job_id'];
}
