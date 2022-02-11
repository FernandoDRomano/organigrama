<?php

namespace App\Http\Resources\V1;

use App\Http\Resources\JobLevelResource;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $obligations = $this->whenLoaded('obligations');
        $employes = $this->whenLoaded('employes');
        
        $obligations_count = $this->obligations_count;
        $employes_count = $this->employes_count;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "obligations" => ObligationResource::collection($obligations),
            "employes" => EmployeResource::collection($employes),
            $this->mergeWhen( (isset($obligations_count) || isset($employes_count)), [
                "counts" => [
                    "obligations" => $obligations_count,
                    "employes" => $employes_count
                ]
            ])
        ];
    }
}
