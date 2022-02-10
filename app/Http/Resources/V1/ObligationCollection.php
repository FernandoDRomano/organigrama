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
        return [
            "data" => $this->collection,
            $this->mergeWhen(
                ($this->collection->first()->job), [
                    "relationship" => [
                        "job" => [
                            "id" => $this->collection->first()->job->id,
                            "name" => $this->collection->first()->job->name
                        ]
                    ]
                ]
            ),
        ];
    }
}
