<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentLevelRequest;
use App\Models\DepartmentLevel;

class DepartmentLevelController extends Controller
{

    public function index(){
        return response()->json([
            "data" => DepartmentLevel::all()
        ], 200);
    }

    public function store(DepartmentLevelRequest $request){
        $departmentLevel = DepartmentLevel::create($request->all());

        return response()->json([
            "message" => "Department level created!!",
            "data" => $departmentLevel
        ], 201);
    }

    public function show(DepartmentLevel $departmentLevel){
        return response()->json([
            "data" => $departmentLevel
        ], 200);
    }

    public function update(DepartmentLevel $departmentLevel, DepartmentLevelRequest $request){
        $departmentLevel->fill($request->all())->save();
    
        return response()->json([
            "message" => "Department level updated!!",
            "data" => $departmentLevel
        ], 200);
    }

    public function destroy(DepartmentLevel $departmentLevel){
        $departmentLevel->delete();

        return response()->json([
            "message" => "Department level deleted!!"
        ], 200);
    }

}
