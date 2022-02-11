<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\JobLevelRequest;
use App\Http\Resources\V1\JobLevelResource;
use App\Models\JobLevel;

class JobLevelController extends Controller
{
    
    public function index(){
        $jobLevels = JobLevel::paginate(10);

        return (JobLevelResource::collection($jobLevels))
               ->additional(["message" => "Job levels all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(JobLevelRequest $request){
        $jobLevel = JobLevel::create($request->all());

        return (JobLevelResource::make($jobLevel))
               ->additional(["message" => "Job level created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(JobLevel $jobLevel){
        return (JobLevelResource::make($jobLevel))
               ->additional(["message" => "Job level!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(JobLevel $jobLevel, JobLevelRequest $request){
        $jobLevel->fill($request->all())->save();

        return (JobLevelResource::make($jobLevel))
               ->additional(["message" => "Job level updated!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function destroy(JobLevel $jobLevel){
        $jobLevel->delete();

        return response()->json([
            "message" => "Job level deleted!!"
        ], 204);
    }

}
