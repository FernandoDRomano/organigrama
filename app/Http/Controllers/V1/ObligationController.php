<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\Organization;
use App\Http\Requests\V1\ObligationRequest;

class ObligationController extends Controller
{

    public function index(Organization $organization, Department $department, Job $job)
    {
        $this->authorize('viewAny', [Obligation::class, $organization, $department, $job]);

        return response()->json([
            "data" => $job->obligations
        ], 200);
    }

    public function store(ObligationRequest $request, Organization $organization, Department $department, Job $job)
    {
        $this->authorize('create', [Obligation::class, $organization, $department, $job]);

        $obligation = Obligation::create($request->all());

        return response()->json([
            "message" => "Obligation created!!",
            "data" => $obligation
         ], 201);
    }

    public function show(Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        $this->authorize('view', [$obligation, $organization, $department, $job]);

        return response()->json([
            "data" => $obligation
        ], 200);
    }

    public function update(ObligationRequest $request, Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        $this->authorize('update', [$obligation, $organization, $department, $job]);

        $obligation->fill($request->all())->save();
        return response()->json([
            "message" => "Obligation updated!!",
            "data" => $obligation
        ], 200);
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
