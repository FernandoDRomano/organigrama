<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeRequest;
use App\Models\Employe;
use App\Models\Organization;
use Illuminate\Http\Request;

class EmployeController extends Controller
{

    public function index(Organization $organization)
    {
        return response()->json([
            "data" => $organization->employes
        ], 200);
    }

    public function store(EmployeRequest $request, Organization $organization)
    {
        $employe = Employe::create($request->all());

        return response()->json([
            "message" => "Employe created!!",
            "data" => $employe
        ], 201);
    }

    public function show(Organization $organization, Employe $employe)
    {
        if ($organization->id === $employe->organization_id) {
            return response()->json([
                "data" => $employe
            ], 200);
        }

        return response()->json([
            "message" => "The employe id is does not contains in organization"
        ]);
    }

    public function update(EmployeRequest $request, Organization $organization, Employe $employe)
    {
        if ($organization->id === $employe->organization_id) {
            
            $employe->fill($request->all())->save();
           
            return response()->json([
                "message" => "Employe updated!!",
                "data" => $employe
            ], 200);
        }

        return response()->json([
            "message" => "The employe id is does not contains in organization"
        ]);
    }

    public function destroy(Organization $organization, Employe $employe)
    {
        if ($organization->id === $employe->organization_id) {
            $employe->delete();

            return response()->json([
                "message" => "Employe deleted!!"
            ], 200);
        }

        return response()->json([
            "message" => "The employe id is does not contains in organization"
        ]);
    }
}
