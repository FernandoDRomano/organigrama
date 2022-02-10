<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Employe;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\V1\AssignRequest;
use App\Models\Assign;

class AssignController extends Controller
{

    public function post(AssignRequest $request, Organization $organization)
    {
        $this->authorize('create', [Assign::class, $organization]);

        DB::beginTransaction();
        try {
            Employe::find($request->employe_id)->jobs()->attach($request->job_id);
            
            DB::commit();

            return response()->json([
                "message" => "Assign created!!"
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                "message" => "Error",
            ]);
        }
    }

    public function destroy(AssignRequest $request, Organization $organization)
    {
        $this->authorize('delete', [new Assign(), $organization]);

        DB::beginTransaction();
        try {
            Employe::find($request->employe_id)->jobs()->detach($request->job_id);
            
            DB::commit();

            return response()->json([
                "message" => "Assign deleted!!"
            ], 204);

        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                "message" => "Error",
            ]);
        }
    }
}
