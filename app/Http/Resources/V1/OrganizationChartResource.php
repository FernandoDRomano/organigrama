<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationChartResource extends JsonResource
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
            "id" => $this->id, //some_id
            "label" => $this->name, //name
            "expand" => true,
            "department_parent" => $this->department_id,
            "children" => OrganizationChartCollection::make($this->children)
        ];
    }
}
