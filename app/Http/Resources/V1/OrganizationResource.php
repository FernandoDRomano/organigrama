<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $departments = $this->whenLoaded('departments');
        $jobs = $this->whenLoaded('jobs');
        $employes = $this->whenLoaded('employes');

        $departments_count = $this->departments_count;
        $jobs_count = $this->jobs_count;
        $employes_count = $this->employes_count;

        return [
            "id" => $this->id,
            "name" => $this->name,
            $this->mergeWhen( ( isset($departments_count) || isset($jobs_count) || isset($employes_count) ), [
                "counts" => [
                    "departments" => $departments_count,
                    "jobs" => $jobs_count,
                    "employes" => $employes_count
                ]
            ])
        ];
    }
}
