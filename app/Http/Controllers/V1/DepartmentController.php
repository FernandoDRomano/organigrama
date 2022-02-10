<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DepartmentRequest;
use App\Models\Department;
use App\Models\Organization;

class DepartmentController extends Controller
{
    
    public function index(Organization $organization){
        $this->authorize('viewAny', [Department::class, $organization]);

        return response()->json([
            "data" => $organization->departments
        ], 200);
    }

    public function store(Organization $organization, DepartmentRequest $request){
        $this->authorize('create', [Department::class, $organization]);

        $department = Department::create($request->all());

        return response()->json([
            "message" => "Department created!!",
            "data" => $department
        ], 201);
    }

    public function show(Organization $organization, Department $department){
        $this->authorize('view', [$department, $organization]);

        return response()->json([
            "data" => $department
        ], 200);
    }

    public function update(Organization $organization, Department $department, DepartmentRequest $request){
        $this->authorize('update', [$department, $organization]);

        $department->fill($request->all())->save();
            
        return response()->json([
            "message" => "Department updated!!",
            "data" => $department
        ], 200);
    }

    public function destroy(Organization $organization, Department $department){
        $this->authorize('delete', [$department, $organization]);

        $department->delete();

        return response()->json([
            "message" => "Department deleted!!"
        ], 204);
    }

}
