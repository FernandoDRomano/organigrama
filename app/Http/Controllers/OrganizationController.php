<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizationRequest;
use App\Models\Organization;

class OrganizationController extends Controller
{
    
    public function index(){
        return response()->json([
            "data" => auth()->user()->organizations
        ], 200);
    }

    public function store(OrganizationRequest $request){
        $organization = Organization::create([
            "name" => $request->name,
            "user_id" => auth()->user()->id
        ]);

        return response()->json([
            "message" => "Organization created!!",
            "data" => $organization
        ], 201);
    }

    public function show(Organization $organization){
        $this->authorize('view', $organization);

        return response()->json([
            "data" => $organization
        ], 200);
    }

    public function update(OrganizationRequest $request, Organization $organization){
        $this->authorize('update', $organization);

        $organization->name = $request->name;
        $organization->save();

        return response()->json([
            "message" => "Organization updated!!",
            "data" => $organization,
        ], 200);
    }

    public function destroy(Organization $organization){
        $this->authorize('delete', $organization);

        $organization->delete();

        return response()->json([
            "message" => "Organization delete!!",
        ], 204);
    }

}
