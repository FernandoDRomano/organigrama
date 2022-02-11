<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\JobLevelResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class JobCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $arr = [
            "data" => $this->collection,
        ];

        if ($this->collection->isNotEmpty()) {
            $job = $this->collection->first();
            
            $relationship = [
                "department" => DepartmentResource::make($job->department),
                "job_level" => JobLevelResource::make($job->level)
            ];

            $arr['relationship'] = $relationship;
        }

        return $arr;
    }
}
