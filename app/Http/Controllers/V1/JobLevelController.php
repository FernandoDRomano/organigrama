<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\JobLevelRequest;
use App\Http\Resources\V1\JobLevelResource;
use App\Models\JobLevel;

class JobLevelController extends Controller
{
    
    public function index(){
        $this->authorize('viewAny', JobLevel::class);

        $jobLevels = JobLevel::orderBy("hierarchy")->paginate(10);

        return (JobLevelResource::collection($jobLevels))
               ->additional(["message" => "Job levels all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(JobLevelRequest $request){
        $this->authorize('create', JobLevel::class);

        $jobLevel = JobLevel::create($request->all());

        return (JobLevelResource::make($jobLevel))
               ->additional(["message" => "Job level created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(JobLevel $jobLevel){
        $this->authorize('view', $jobLevel);

        return (JobLevelResource::make($jobLevel))
               ->additional(["message" => "Job level!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(JobLevel $jobLevel, JobLevelRequest $request){
        $this->authorize('update', $jobLevel);

        $jobLevel->fill($request->all())->save();

        return (JobLevelResource::make($jobLevel))
               ->additional(["message" => "Job level updated!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function destroy(JobLevel $jobLevel){
        $this->authorize('delete', $jobLevel);

        $jobLevel->delete();

        return response()->json([
            "message" => "Job level deleted!!"
        ], 204);
    }

}
