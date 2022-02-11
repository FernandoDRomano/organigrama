<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class EmployeCollection extends ResourceCollection
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

        if ($this->collection->isNotEmpty()) {
            $relationships = [
                "organization" => [
                    "id" => $this->collection->first()->organization->id,
                    "name" => $this->collection->first()->organization->name
                ]
            ];

            $arr['relationships'] = $relationships;
        }

        return $arr;
    }
}
