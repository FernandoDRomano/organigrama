<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use App\Models\Organization;

class DepartmentController extends Controller
{
    
    public function index(Organization $organization)
    {
        return response()->json([
            "data" => $organization->departments
        ], 200);
    }

    public function store(Organization $organization, DepartmentRequest $request)
    {
        $department = Department::create($request->all());

        return response()->json([
            "message" => "Department created!!",
            "data" => $department
        ], 201);
    }

    public function show(Organization $organization, Department $department){
        if ($organization->id === $department->organization_id) {
            return response()->json([
                "data" => $department
            ], 200);
        }

        return response()->json([
            "message" => "The department id is invalid for organization"
        ], 404);
    }

    public function update(Organization $organization, Department $department, DepartmentRequest $request){
        $department->fill($request->all())->save();
            
        return response()->json([
            "message" => "Department updated!!",
            "data" => $department
        ], 200);
    }

    public function destroy(Organization $organization, Department $department){
        if ($organization->id === $department->organization_id) {
            $department->delete();

            return response()->json([
            "message" => "Department deleted!!"
            ], 200);
        }

        return response()->json([
            "message" => "The department id is does not correspond with organization id"
        ], 403);

    }

}
