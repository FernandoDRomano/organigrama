<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Department;
use App\Models\Organization;
use App\Http\Requests\JobRequest;

class JobController extends Controller
{

    public function index(Organization $organization, Department $department)
    {
        return response()->json([
            "data" =>  $department->jobs
        ], 200);
    }

    public function store(JobRequest $request, Organization $organization, Department $department)
    {
        $job = Job::create($request->all());
        return response()->json([
            "message" => "Job created!!",
            "data" => $job
        ], 201);

    }

    public function show(Organization $organization, Department $department, Job $job)
    {
        if ($organization->id === $department->organization_id && $department->id === $job->department_id) {
            return response()->json([
                "data" => $job
            ], 200);
        }

        return response()->json([
            "message" => "The job is do not contains in the department and organization"
        ]);
    }

    public function update(JobRequest $request, Organization $organization, Department $department, Job $job)
    {
        $job->fill($request->all())->save();
        return response()->json([
            "message" => "Job updated!!",
            "data" => $job
        ], 200);
    }

    public function destroy(Organization $organization, Department $department, Job $job)
    {
        if ($organization->id === $department->organization_id && $department->id === $job->department_id) {
            $job->delete();
            return response()->json([
                "message" => "Job deleted!!"
            ], 200);
        }

        return response()->json([
            "message" => "The job is do not contains in the department and organization"
        ]);
    }
}
