<?php

namespace App\Http\Controllers\V1;

use App\Models\Organization;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\V1\OrganizationChartCollection;

class OrganizationChartController extends Controller
{
    public function __invoke(Organization $organization)
    {
        Gate::authorize('view-organization-chart', $organization);

        $organigrama = $organization
                       ->departments()
                       ->whereNull('department_id')
                       ->with('children')
                       ->get();

        return (OrganizationChartCollection::make($organigrama))
               ->additional(["message" => "Organization chart created!!"])
               ->response()
               ->setStatusCode(200);
    }
}
