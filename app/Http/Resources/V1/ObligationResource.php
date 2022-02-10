<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class ObligationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "description" => $this->description,
            $this->mergeWhen(
                ($this->whenLoaded('job')), [
                    "relationship" => [
                        "job" => [
                            "id" => $this->job->id,
                            "name" => $this->job->name
                        ]
                    ]
                ]
            ),
        ];
    }
}
