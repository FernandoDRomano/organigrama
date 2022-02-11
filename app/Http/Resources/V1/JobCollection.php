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
        return [
            "data" => $this->collection,
            "relationship" => [
                $this->mergeWhen(($this->collection->first()->department), [
                    "department" => [
                        "id" => $this->collection->first()->department->id,
                        "name" => $this->collection->first()->department->name
                    ]
                ]),
                $this->mergeWhen(($this->collection->first()->level), [
                    "job_level" => JobLevelResource::make($this->collection->first()->level)
                ]),
            ],
        ];
    }
}
