<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmployeRequest;
use App\Http\Resources\V1\EmployeCollection;
use App\Http\Resources\V1\EmployeResource;
use App\Models\Employe;
use App\Models\Organization;

class EmployeController extends Controller
{

    public function index(Organization $organization)
    {
        $this->authorize('viewAny', [Employe::class, $organization]);

        $employes = $organization->employes()->with('organization')->orderBy('id', 'DESC')->paginate(10);

        return (EmployeCollection::make($employes))
               ->additional(["message" => "Employes all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(EmployeRequest $request, Organization $organization)
    {
        $this->authorize('create', [Employe::class, $organization]);

        $employe = Employe::create($request->all());

        $employe->loadMissing('jobs')->loadCount('jobs');

        return (EmployeResource::make($employe))
               ->additional(['message' => "Employe created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(Organization $organization, Employe $employe)
    {
        $this->authorize('view', [$employe, $organization]);
        
        $employe->loadMissing('jobs')->loadCount('jobs');

        return (EmployeResource::make($employe))
               ->additional(['message' => "Employe"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(EmployeRequest $request, Organization $organization, Employe $employe)
    {           
        $this->authorize('update', [$employe, $organization]);
         
        $employe->fill($request->all())->save();
           
        $employe->loadMissing('jobs')->loadCount('jobs');

        return (EmployeResource::make($employe))
               ->additional(['message' => "Employe updated!!"])
               ->response()
               ->setStatusCode(200);
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
