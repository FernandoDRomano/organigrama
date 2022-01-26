<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobLevelRequest;
use App\Models\JobLevel;

class JobLevelController extends Controller
{
    
    public function index(){
        return response()->json([
            "data" => JobLevel::all()
        ], 200);
    }

    public function store(JobLevelRequest $request){
        $jobLevel = JobLevel::create($request->all());

        return response()->json([
            "message" => "Job level created!!",
            "data" => $jobLevel
        ], 201);
    }

    public function show(JobLevel $jobLevel){
        return response()->json([
            "data" => $jobLevel
        ], 200);
    }

    public function update(JobLevel $jobLevel, JobLevelRequest $request){
        $jobLevel->fill($request->all())->save();

        return response()->json([
            "message" => "Job level updated!!",
            "data" => $jobLevel
        ], 200);
    }

    public function destroy(JobLevel $jobLevel){
        $jobLevel->delete();

        return response()->json([
            "message" => "Job level deleted!!"
        ], 200);
    }

}
