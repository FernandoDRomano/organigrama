<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DepartmentLevelRequest;
use App\Http\Resources\V1\DepartmentLevelResource;
use App\Models\DepartmentLevel;

class DepartmentLevelController extends Controller
{

    public function index(){
        $this->authorize('viewAny', DepartmentLevel::class);

        $departmentLevels = DepartmentLevel::orderBy("hierarchy")->paginate(10);

        return (DepartmentLevelResource::collection($departmentLevels))
               ->additional(["message" => "Department levels all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(DepartmentLevelRequest $request){
        $this->authorize('create', DepartmentLevel::class);

        $departmentLevel = DepartmentLevel::create($request->all());

        return (DepartmentLevelResource::make($departmentLevel))
               ->additional(["message" => "Department level created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(DepartmentLevel $departmentLevel){
        $this->authorize('view', $departmentLevel);

        return (DepartmentLevelResource::make($departmentLevel))
               ->additional(["message" => "Department level!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(DepartmentLevel $departmentLevel, DepartmentLevelRequest $request){
        $this->authorize('update', $departmentLevel);

        $departmentLevel->fill($request->all())->save();
    
        return (DepartmentLevelResource::make($departmentLevel))
               ->additional(["message" => "Department level updated!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function destroy(DepartmentLevel $departmentLevel){
        $this->authorize('delete', $departmentLevel);

        $departmentLevel->delete();

        return response()->json([
            "message" => "Department level deleted!!"
        ], 204);
    }

}
