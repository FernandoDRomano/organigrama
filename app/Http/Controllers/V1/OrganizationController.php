<?php

namespace App\Http\Controllers\V1;

use App\Models\Organization;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\OrganizationRequest;
use App\Http\Resources\V1\OrganizationCollection;
use App\Http\Resources\V1\OrganizationResource;

class OrganizationController extends Controller
{
    
    public function index(){
        $organizations = auth()->user()->organizations()
                         ->with('user')->withCount(['departments', 'jobs', 'employes'])
                         ->orderBy('id', 'DESC')
                         ->paginate(10);

        return (OrganizationCollection::make($organizations))
               ->additional(["message" => "Organizations all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(OrganizationRequest $request){
        $organization = Organization::create([
            "name" => $request->name,
            "user_id" => auth()->user()->id
        ]);

        $organization->loadMissing(['departments', 'jobs', 'employes'])->loadCount(['departments', 'jobs', 'employes']);

        return (OrganizationResource::make($organization))
               ->additional(["message" => "Organization created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(Organization $organization){
        $this->authorize('view', $organization);

        $organization->loadMissing(['departments', 'jobs', 'employes'])->loadCount(['departments', 'jobs', 'employes']);

        return (OrganizationResource::make($organization))
               ->additional(["message" => "Organization"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(OrganizationRequest $request, Organization $organization){
        $this->authorize('update', $organization);

        $organization->fill($request->all())->save();

        $organization->loadMissing(['departments', 'jobs', 'employes'])->loadCount(['departments', 'jobs', 'employes']);

        return (OrganizationResource::make($organization))
               ->additional(["message" => "Organization updated!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function destroy(Organization $organization){
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->json([
            "message" => "Organization delete!!",
        ], 204);
    }

}
