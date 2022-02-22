<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\DepartmentLevelResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DepartmentCollection extends ResourceCollection
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
            "data" => $this->collection
        ];

        if($this->collection->isNotEmpty()){
            $department = $this->collection->first();
            
            $relationships =  [
                "organization" => [
                    "id" => $department->organization->id,
                    "name" => $department->organization->name,
                ],
            ];

            $arr["relationships"] = $relationships;
        }

        return $arr;
    }
}
