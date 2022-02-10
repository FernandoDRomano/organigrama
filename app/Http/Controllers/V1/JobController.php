<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Department;
use App\Models\Organization;
use App\Http\Requests\V1\JobRequest;

class JobController extends Controller
{

    public function index(Organization $organization, Department $department)
    {
        $this->authorize('viewAny', [Job::class, $organization, $department]);

        return response()->json([
            "data" =>  $department->jobs
        ], 200);
    }

    public function store(JobRequest $request, Organization $organization, Department $department)
    {
        $this->authorize('create', [Job::class, $organization, $department]);

        $job = Job::create($request->all());
        return response()->json([
            "message" => "Job created!!",
            "data" => $job
        ], 201);

    }

    public function show(Organization $organization, Department $department, Job $job)
    {
        $this->authorize('view', [$job, $organization, $department]);

        return response()->json([
            "data" => $job
        ], 200);
    }

    public function update(JobRequest $request, Organization $organization, Department $department, Job $job)
    {
        $this->authorize('update', [$job, $organization, $department]);

        $job->fill($request->all())->save();
        return response()->json([
            "message" => "Job updated!!",
            "data" => $job
        ], 200);
    }

    public function destroy(Organization $organization, Department $department, Job $job)
    {
        $this->authorize('delete', [$job, $organization, $department]);

        $job->delete();
        return response()->json([
            "message" => "Job deleted!!"
        ], 204);
    }
}
