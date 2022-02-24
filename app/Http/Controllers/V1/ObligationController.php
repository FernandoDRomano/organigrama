<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\Organization;
use App\Http\Requests\V1\ObligationRequest;
use App\Http\Resources\V1\ObligationCollection;
use App\Http\Resources\V1\ObligationResource;

class ObligationController extends Controller
{

    public function index(Organization $organization, Department $department, Job $job)
    {
        $this->authorize('viewAny', [Obligation::class, $organization, $department, $job]);

        $obligations = $job->obligations()->with('job')->orderBy('id', 'DESC')->paginate(10);
        
        return (ObligationCollection::make($obligations))
               ->additional(["message" => "Obligations all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(ObligationRequest $request, Organization $organization, Department $department, Job $job)
    {
        $this->authorize('create', [Obligation::class, $organization, $department, $job]);

        $obligation = Obligation::create($request->all());

        return (ObligationResource::make($obligation))
               ->additional(["message" => "Obligation created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        $this->authorize('view', [$obligation, $organization, $department, $job]);

        return (ObligationResource::make($obligation->loadMissing('job')))
               ->additional(["message" => "Obligation"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(ObligationRequest $request, Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        $this->authorize('update', [$obligation, $organization, $department, $job]);

        $obligation->fill($request->all())->save();

        return (ObligationResource::make($obligation))
               ->additional(["message" => "Obligation updated!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function destroy(Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        $this->authorize('delete', [$obligation, $organization, $department, $job]);
        
        $obligation->delete();
    
        return response()->json([
            "message" => "Obligation deleted!!"
        ], 204);
    }
}
