<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Department;
use App\Models\Obligation;
use App\Models\Organization;
use App\Http\Requests\ObligationRequest;

class ObligationController extends Controller
{

    public function index(Organization $organization, Department $department, Job $job)
    {
        return response()->json([
            "data" => $job->obligations
        ], 200);
    }

    public function store(ObligationRequest $request, Organization $organization, Department $department, Job $job)
    {
        $obligation = Obligation::create($request->all());

        return response()->json([
            "message" => "Obligation created!!",
            "data" => $obligation
         ], 201);
    }

    public function show(Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        if ($organization->id === $department->organization_id && $department->id === $job->department_id && $job->id === $obligation->job_id) {
            return response()->json([
                "data" => $obligation
            ], 200);
        }

        return response()->json([
            "message" => "Unauthorized for see this record."
        ], 403);
    }

    public function update(ObligationRequest $request, Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        if ($organization->id === $department->organization_id && $department->id === $job->department_id && $job->id === $obligation->job_id) {

            $obligation->fill($request->all())->save();
            return response()->json([
                "message" => "Obligation updated!!",
                "data" => $obligation
             ], 200);
        }

        return response()->json([
            "message" => "Unauthorized for update this record."
        ], 403);
    }

    public function destroy(Organization $organization, Department $department, Job $job, Obligation $obligation)
    {
        if ($organization->id === $department->organization_id && $department->id === $job->department_id && $job->id === $obligation->job_id) {
            $obligation->delete();
    
            return response()->json([
                "message" => "Obligation deleted!!"
            ], 200);
        }

        return response()->json([
            "message" => "Unauthorized for delete this record."
        ], 403);
    }
}
