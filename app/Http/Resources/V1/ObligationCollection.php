<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ObligationCollection extends ResourceCollection
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

        // if ($this->collection->isNotEmpty()) {
        //     $relationship = [
        //         "job" => JobResource::make($this->collection->first()->job)
        //     ];

        //     $arr['relationship'] = $relationship;
        // }

        return $arr;
    }
}
