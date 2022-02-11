<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Department;
use App\Models\Organization;
use App\Http\Requests\V1\JobRequest;
use App\Http\Resources\V1\JobCollection;
use App\Http\Resources\V1\JobResource;

class JobController extends Controller
{

    public function index(Organization $organization, Department $department)
    {
        $this->authorize('viewAny', [Job::class, $organization, $department]);

        $jobs = $department->jobs()
                ->with(['employes', 'obligations'])
                ->withCount(['employes', 'obligations'])
                ->paginate(10);

        return (JobCollection::make($jobs))
               ->additional(["message" => "Jobs all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(JobRequest $request, Organization $organization, Department $department)
    {
        $this->authorize('create', [Job::class, $organization, $department]);

        $job = Job::create($request->all());

        return (JobResource::make($job->loadMissing(['employes', 'obligations'])->loadCount(['employes', 'obligations'])))
               ->additional(["message" => "Job created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(Organization $organization, Department $department, Job $job)
    {
        $this->authorize('view', [$job, $organization, $department]);

        return (JobResource::make($job->loadMissing(['employes', 'obligations'])->loadCount(['employes', 'obligations'])))
               ->additional(["message" => "Job!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(JobRequest $request, Organization $organization, Department $department, Job $job)
    {
        $this->authorize('update', [$job, $organization, $department]);

        $job->fill($request->all())->save();
        
        return (JobResource::make($job->loadMissing(['employes', 'obligations'])->loadCount(['employes', 'obligations'])))
               ->additional(["message" => "Job updated!!"])
               ->response()
               ->setStatusCode(200);
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
