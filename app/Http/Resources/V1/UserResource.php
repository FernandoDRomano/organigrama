<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $organizations_count = $this->organizations_count;
        $organizations = $this->whenLoaded('organizations');

        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "status" => $this->status,
            "role" => $this->role,
            "organizations" => OrganizationResource::collection($organizations),
            $this->mergeWhen( (isset($organizations_count) ), [
                "counts" => [
                    "organizations" => $organizations_count,
                ]
            ])
        ];
    }
}
