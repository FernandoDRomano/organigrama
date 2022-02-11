<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\DepartmentRequest;
use App\Http\Resources\V1\DepartmentCollection;
use App\Http\Resources\V1\DepartmentResource;
use App\Models\Department;
use App\Models\Organization;

class DepartmentController extends Controller
{
    
    public function index(Organization $organization){
        $this->authorize('viewAny', [Department::class, $organization]);

        $departments = $organization->departments()->with('level', 'organization')->withCount(['jobs', 'departments'])->orderBy('id', 'DESC')->paginate(10);

        return (DepartmentCollection::make($departments))
               ->additional(["message" => "Departments all!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function store(Organization $organization, DepartmentRequest $request){
        $this->authorize('create', [Department::class, $organization]);

        $department = Department::create($request->all());

        $department->loadMissing(['jobs', 'departments'])->loadCount(['jobs', 'departments']);

        return (DepartmentResource::make($department))
               ->additional(["message" => "Department created!!"])
               ->response()
               ->setStatusCode(201);
    }

    public function show(Organization $organization, Department $department){
        $this->authorize('view', [$department, $organization]);

        $department->loadMissing(['jobs', 'departments'])->loadCount(['jobs', 'departments']);

        return (DepartmentResource::make($department))
               ->additional(["message" => "Department"])
               ->response()
               ->setStatusCode(200);
    }

    public function update(Organization $organization, Department $department, DepartmentRequest $request){
        $this->authorize('update', [$department, $organization]);

        $department->fill($request->all())->save();
            
        $department->loadMissing(['jobs', 'departments'])->loadCount(['jobs', 'departments']);

        return (DepartmentResource::make($department))
               ->additional(["message" => "Department updated!!"])
               ->response()
               ->setStatusCode(200);
    }

    public function destroy(Organization $organization, Department $department){
        $this->authorize('delete', [$department, $organization]);

        $department->delete();

        return response()->json([
            "message" => "Department deleted!!"
        ], 204);
    }

}
