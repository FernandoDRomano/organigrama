<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeResource extends JsonResource
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

        $jobs_count = $this->jobs_count;
        
        return [
            "id" => $this->id,
            "full_name" => ucwords($this->last_name . ', ' . $this->first_name),
            "dni" => $this->dni,
            "date_of_birth" => $this->date_of_birth->format('m-d-Y'),
            "address" => $this->address,
            "jobs" => JobResource::collection($jobs),
            $this->mergeWhen( ( isset($jobs_count) ), [
                "counts" => [
                    "jobs" => $jobs_count
                ]
            ])
        ];
    }
}
