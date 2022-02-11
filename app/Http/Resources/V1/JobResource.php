<?php

namespace App\Http\Resources\V1;

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
        return [
            "id" => $this->id,
            "name" => $this->name,
            "obligations" => ObligationResource::collection($this->whenLoaded('obligations')),
            // "employes" => EmployeResource::collection($this->whenLoaded('employes')),
            "counts" => [
                "obligations" => $this->when($this->obligations_count, $this->obligations_count, 0),
                "employes" => $this->when($this->employes_count, $this->employes_count, 0)
            ]
        ];
    }
}
