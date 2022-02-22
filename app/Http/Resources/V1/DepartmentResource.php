<?php

namespace App\Http\Resources\V1;

use App\Models\Department;
use App\Models\DepartmentLevel;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $jobs = $this->whenLoaded('jobs');
        $departments = $this->whenLoaded('departments');
        $level = $this->whenLoaded('level');

        $jobs_count = $this->jobs_count;
        $departments_count = $this->departments_count;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "level" => DepartmentLevelResource::make($level),
            "jobs" => JobResource::collection($jobs),
            "departments_children" => DepartmentResource::collection($departments),
            $this->mergeWhen( ( isset($jobs_count) ) ,[
                "counts" => [
                    "jobs" => $jobs_count,
                    "departments_children" => $departments_count
                ]
            ]),
        ];
    }
}
