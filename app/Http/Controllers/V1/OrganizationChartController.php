<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Organization;

class OrganizationChartController extends Controller
{
    public function __invoke(Organization $organization)
    {
        $organigrama = $organization->departments()->whereNull('department_id')->with('children')->get();

        return response()->json([
            "organigrama" => $organigrama
        ]);
    }
}
