<?php

namespace Tests\Feature\Controllers\V1;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employe;
use App\Models\Department;
use App\Models\Organization;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AssignControllerTest extends TestCase
{
    use RefreshDatabase;

    public function getUserAuthenticated(String $role = User::ROLE_CUSTOMER) :User
    {
        $user = User::factory()->create(["role" => $role]);
        Sanctum::actingAs($user);

        return $user;
    }

    
    /**
     * @test
     */
    public function authenticated_users_can_assign_employe_to_job() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job1 = Job::factory()->for($department)->create();
        $job2 = Job::factory()->for($department)->create();
        $employe = Employe::factory()->for($organization)->create();

        $data = [
            "organization_id" => $organization->id,
            "employe_id" => $employe->id,
            "job_id" => $job1->id
        ];

        $response = $this->postJson( route("assign.store", $organization), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("employe_job", 1);
        $this->assertDatabaseHas("employe_job", [
            "employe_id" => $employe->id,
            "job_id" => $job1->id
        ]);

        $data = [
            "organization_id" => $organization->id,
            "employe_id" => $employe->id,
            "job_id" => $job2->id
        ];

        $response = $this->postJson( route("assign.store", $organization), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("employe_job", 2);
        $this->assertDatabaseHas("employe_job", [
            "employe_id" => $employe->id,
            "job_id" => $job2->id
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_destroy_assign_employe_to_job() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        Job::factory()->for($department)
            ->has( Employe::factory()->for($organization)->count(3) )->create();

        $data = [
            "organization_id" => $organization->id,
            "employe_id" => "1",
            "job_id" => "1"
        ];

        $response = $this->deleteJson( route("assign.destroy", $organization), $data );

        $response->assertStatus(204);
        $this->assertDatabaseCount("employe_job", 2);
        $this->assertDatabaseMissing("employe_job", [
            "employe_id" => "1",
            "job_id" => "1"
        ]);
    }

    /**
     * @test
     * @dataProvider invalidDataForCreateOrUpdateJobs
     */
    public function authenticated_users_cannot_create_or_delete_assign_with_invalid_data($method, $route, $invalidData, $inputInvalid, $param) :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $employe = Employe::factory()->for($organization)->create();

        $response = $this->$method( route($route, [$organization]), $invalidData );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($inputInvalid);
    }

    public function invalidDataForCreateOrUpdateJobs() :array
    {
        $store = "assign.store";
        $destroy = "assign.destroy";
        $post = "postJson";
        $delete = "deleteJson";

        return [
            "The field organization_id is required" => [$post, $store, ["organization_id" => ""], "organization_id", "1"],
            "The field organization_id is required" => [$delete, $destroy, ["organization_id" => ""], "organization_id", "1" ],
            "The field organization_id is invalid" => [ $post, $store, ["organization_id" => "2"] , "organization_id", "1"],
            "The field organization_id is invalid" => [$delete, $destroy, ["organization_id" => "2"], "organization_id", "1" ],

            "The field employe_id is required" => [$post, $store, ["employe_id" => ""], "organization_id", "1"],
            "The field employe_id is required" => [$delete, $destroy, ["employe_id" => ""], "organization_id", "1" ],
            "The field employe_id is invalid" => [ $post, $store, ["employe_id" => "2"] , "organization_id", "1"],
            "The field employe_id is invalid" => [$delete, $destroy, ["employe_id" => "2"], "organization_id", "1" ],

            "The field job_id is required" => [$post, $store, ["job_id" => ""], "organization_id", "1"],
            "The field job_id is required" => [$delete, $destroy, ["job_id" => ""], "organization_id", "1" ],
            "The field job_id is invalid" => [ $post, $store, ["job_id" => "2"] , "organization_id", "1"],
            "The field job_id is invalid" => [$delete, $destroy, ["job_id" => "2"], "organization_id", "1" ],
        ];
    }
    
     /**
     * @test
     */
    public function test_rule_OrganizationContainsValidId() :void
    {
        //This rule verify that organization_id send for request and parameter of route be equals
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $organization2 = Organization::factory()->create();

        $dataInvalid = [
            "organization_id" => $organization->id
        ];

        $response = $this->postJson( route("assign.store", $organization2), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("organization_id");

        $response = $this->deleteJson( route("assign.destroy", [$organization2]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("organization_id");
    }

    /**
     * @test
     */
    public function test_rule_ExistsEmployeInOrganization() :void
    {
        //This rule verify that employe_id exists in organization
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $employe = Employe::factory()->for($organization)->create();
        $organization2 = Organization::factory()->create();
        $employe2 = Employe::factory()->for($organization2)->create();

        $dataInvalid = [
            "employe_id" => $employe2->id
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("assign.store", $organization), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("employe_id");

        $response = $this->deleteJson( route("assign.destroy", [$organization]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("employe_id");

        //CASOS DE EXITO
        $dataValid = [
            "employe_id" => $employe->id
        ];

        $response = $this->postJson( route("assign.store", $organization), $dataValid );
        $response->assertJsonMissingValidationErrors("employe_id");

        $response = $this->deleteJson( route("assign.destroy", [$organization]), $dataValid );
        $response->assertJsonMissingValidationErrors("employe_id");
    }

    /**
     * @test
     */
    public function test_rule_EmployeHasThisJob() :void
    {
        //This rule verify that employe_id has not job_id assign
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $employe = Employe::factory()->for($organization)
                    ->has( Job::factory()->for($department)->count(1) )
                    ->create();
        $job = Job::factory()->for($department)->create();
        
        $dataInvalid = [
            "employe_id" => $employe->id,
            "job_id" => "1"
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("assign.store", $organization), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("employe_id");
        $response->assertJson([
            "errors" => [
                "employe_id" => [
                    "The employe id has this job id"
                ]
            ] 
        ]);

        $response = $this->deleteJson( route("assign.destroy", [$organization]), $dataInvalid );
        $response->assertJsonMissingValidationErrors("employe_id");

        // //CASOS DE EXITO
        $dataValid = [
            "employe_id" => $employe->id,
            "job_id" => $job->id
        ];

        $response = $this->postJson( route("assign.store", $organization), $dataValid );
        $response->assertJsonMissingValidationErrors("employe_id");

        $response = $this->deleteJson( route("assign.destroy", [$organization]), $dataValid );
        $response->assertJsonMissingValidationErrors("employe_id");
    }

    /**
     * @test
     */
    public function test_rule_ExistsJobInOrganization() :void
    {
        //This rule verify that job_id exists in organization
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $organization2 = Organization::factory()->create();
        $department2 = Department::factory()->for($organization2)->create();
        $job2 = Job::factory()->for($department2)->create();

        $dataInvalid = [
            "job_id" => $job2->id
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("assign.store", $organization), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_id");

        $response = $this->deleteJson( route("assign.destroy", [$organization]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_id");

        //CASOS DE EXITO
        $dataValid = [
            "job_id" => $job->id
        ];

        $response = $this->postJson( route("assign.store", $organization), $dataValid );
        $response->assertJsonMissingValidationErrors("job_id");

        $response = $this->deleteJson( route("assign.destroy", [$organization]), $dataValid );
        $response->assertJsonMissingValidationErrors("job_id");
    }

    /**
     * @test
     * @dataProvider invalidRouteForUsersGuest
     */
    public function users_guest_cannot_access_to_routes_protected($route, $method) :void
    {   
        $organization = Organization::factory()->create();
        $department = Department::factory()->for($organization)->create();
        Job::factory()->for($department)->create();
        Employe::factory()->for($organization)->create();
        
        $response = $this->$method( route($route, [$organization]) );

        $response->assertStatus(401);
    }
    
    public function invalidRouteForUsersGuest() :array
    {
        return [
            "Route assign.store" => [ "assign.store" , "postJson"],
            "Route assign.destroy" => [ "assign.destroy" , "deleteJson"],
        ];     
    }

    /**
     * @test
     * @dataProvider dataInvalidForUsersWithRoleCustomer
     */
    public function users_with_role_customer_cannot_perform_to_actions_protected($method, $route, $dataInvalid) :void
    {
        $this->getUserAuthenticated();
        $organization = Organization::factory()->create();
        $department = Department::factory()->for($organization)->create();
        Job::factory()->for($department)->create();
        Employe::factory()->for($organization)->create();

        $response = $this->$method( route($route, [$organization]), $dataInvalid );

        $response->assertStatus(403);
    }

    public function dataInvalidForUsersWithRoleCustomer() :array
    {
        $dataInvalid = [
            "organization_id" => "1",
            "employe_id" => "1",
            "job_id" => "1",
        ];
        
        return [
            "User with role customer cannot store assign employes to jobs not owner" => [ "postJson", "assign.store", $dataInvalid ], 
            "User with role customer cannot delete assign employes to jobs not owner" => [ "deleteJson", "assign.destroy", $dataInvalid ], 
        ];
    }

}
