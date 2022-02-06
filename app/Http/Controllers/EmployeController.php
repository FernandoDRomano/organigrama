<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeRequest;
use App\Models\Employe;
use App\Models\Organization;

class EmployeController extends Controller
{

    public function index(Organization $organization)
    {
        $this->authorize('viewAny', [Employe::class, $organization]);

        return response()->json([
            "data" => $organization->employes
        ], 200);
    }

    public function store(EmployeRequest $request, Organization $organization)
    {
        $this->authorize('create', [Employe::class, $organization]);

        $employe = Employe::create($request->all());

        return response()->json([
            "message" => "Employe created!!",
            "data" => $employe
        ], 201);
    }

    public function show(Organization $organization, Employe $employe)
    {
        $this->authorize('view', [$employe, $organization]);
            
        return response()->json([
            "data" => $employe
        ], 200);
    }

    public function update(EmployeRequest $request, Organization $organization, Employe $employe)
    {           
        $this->authorize('update', [$employe, $organization]);
         
        $employe->fill($request->all())->save();
           
        return response()->json([
            "message" => "Employe updated!!",
            "data" => $employe
        ], 200);
    }

    public function destroy(Organization $organization, Employe $employe)
    {
        $this->authorize('delete', [$employe, $organization]);

        $employe->delete();

        return response()->json([
            "message" => "Employe deleted!!"
        ], 204);
    }
}
