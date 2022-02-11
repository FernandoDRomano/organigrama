<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\JobLevelResource;
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
            $relationship = [
                "department" => [
                    "id" => $this->collection->first()->department->id,
                    "name" => $this->collection->first()->department->name
                ],
                "job_level" => JobLevelResource::make($this->collection->first()->level)
            ];

            $arr['relationship'] = $relationship;
        }

        return $arr;
    }
}
