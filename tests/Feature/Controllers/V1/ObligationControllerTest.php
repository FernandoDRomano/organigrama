<?php

namespace Tests\Feature\Controllers\V1;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use App\Models\JobLevel;
use App\Models\Department;
use App\Models\Obligation;
use Illuminate\Support\Str;
use App\Models\Organization;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ObligationControllerTest extends TestCase
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
    public function authenticated_users_can_view_all_obligations_in_order_desc() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $obligations = Obligation::factory(3)->for($job)->create();

        $obligations->loadMissing(["job"]);

        $response = $this->getJson( route("obligations.index", [$organization, $department, $job]) );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $obligations[2]->id,
                    "description" => $obligations[2]->description,
                    "job" => [],
                ],
                [
                    "id" => $obligations[1]->id,
                    "description" => $obligations[1]->description,
                    "job" => [],
                ],
                [
                    "id" => $obligations[0]->id,
                    "description" => $obligations[0]->description,
                    "job" => [],
                ],
            ],
            "links" => [],
            "meta" => [],
            "message" => "Obligations all!!"
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_see_obligations_details() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $obligation = Obligation::factory()->for($job)->create();

        $obligation->loadMissing(["job"]);

        $response = $this->getJson( route("obligations.show", [$organization, $department, $job, $obligation]) );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "description",
                "job" => [],   
            ],
            "message"
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_create_jobs() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        
        $data = [
            "description" => "This is description",
            "job_id" => $job->id,
        ];

        $response = $this->postJson( route("obligations.store", [$organization, $department, $job]), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("obligations", 1);
        $this->assertDatabaseHas("obligations", $data);
    }

    /**
     * @test
     */
    public function authenticated_users_can_update_jobs() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $obligation = Obligation::factory()->for($job)->create();

        $data = [
            "description" => "This is description",
            "job_id" => $job->id,
        ];

        $response = $this->putJson( route("obligations.update", [$organization, $department, $job, $obligation]), $data );

        $response->assertStatus(200);
        $this->assertDatabaseCount("obligations", 1);
        $this->assertDatabaseHas("obligations", $data);
    }

    /**
     * @test
     */
    public function authenticated_users_can_delete_jobs() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $obligation = Obligation::factory()->for($job)->create();
        
        $response = $this->deleteJson( route("obligations.destroy", [$organization, $department, $job, $obligation]) );

        $response->assertStatus(204);
        $this->assertDatabaseCount("obligations", 0);
        $this->assertDatabaseMissing("obligations", [
            "description" => $obligation->description,
            "job_id" => $job->id,
        ]);
    }

    /**
     * @test
     * @dataProvider invalidDataForCreateOrUpdateObligations
     */
    public function authenticated_users_cannot_create_or_update_obligations_with_invalid_data($method, $route, $invalidData, $inputInvalid, $param) :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        Obligation::factory()->for($job)->create();

        $response = $this->$method( route($route, [$organization, $department, $job, "obligation" => $param]), $invalidData );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($inputInvalid);
    }

    public function invalidDataForCreateOrUpdateObligations() :array
    {
        $store = "obligations.store";
        $update = "obligations.update";
        $post = "postJson";
        $put = "putJson";

        return [
            "The field description is required" => [$post, $store, ["description" => ""], "description", null],
            "The field description is required" => [$put, $update, ["description" => ""], "description", "1" ],
            "The field description must be at least 3 characters" => [$post, $store, ["description" => Str::random(2)], "description", null ],
            "The field description must be at least 3 characters" => [$put, $update, ["description" => Str::random(2)], "description", "1" ],
            "The field description must not be greater than 120 characters" => 
                [$post, $store, ["description" => Str::random(121)], "description", null ],
            "The field description must not be greater than 120 characters" => 
                [$put, $update, ["description" => Str::random(121)], "description", "1" ],

        
            "The field job_id is required" => [ $post, $store, ["job_id" => ""] , "job_id", null],
            "The field job_id is required" => [$put, $update, ["job_id" => ""], "job_id", "1" ],
            "The field job_id is invalid" => [ $post, $store, ["job_id" => "2"] , "job_id", null],
            "The field job_id is invalid" => [$put, $update, ["job_id" => "2"], "job_id", "1" ],
        ];
    }

    /**
     * @test
     */
    public function test_rule_JobContainValidId() :void
    {
        //This rule verify that job_id be equals to job send for parameter in url
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job1 = Job::factory()->for($department)->create();
        $job2 = Job::factory()->create();
        $obligation = Obligation::factory()->for($job1)->create();

        $dataInvalid = [
            "description" => "Description...",
            "job_id" => $job2->id,
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("obligations.store", [$organization, $department, $job1]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_id");
        $response->assertJson([
            "errors" => [
                "job_id" => [
                    "The job id is invalid because job id does not correspont with url"
                ]
            ]
        ]);

        $response = $this->putJson( route("obligations.update", [$organization, $department, $job1, $obligation]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_id");
        $response->assertJson([
            "errors" => [
                "job_id" => [
                    "The job id is invalid because job id does not correspont with url"
                ]
            ]
        ]);

        // //ELIMINAR EL JOB INICIAL
        $this->deleteJson( route("obligations.destroy", [$organization, $department, $job1, $obligation]) );

        // //CASOS DE EXITO
        $dataValid = [
            "description" => "Description...",
            "job_id" => $job1->id,
        ];

        $response = $this->postJson( route("obligations.store", [$organization, $department, $job1]), $dataValid );

        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("job_id");

        $response = $this->putJson( route("obligations.update", [$organization, $department, $job1, "2"]), $dataValid );

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("job_id");
    }

    /**
     * @test
     */
    public function test_rule_ValidateJobInDepartmentInOrganization() :void
    {
        //This rule verify that job_id exists in department in organization
        $user = $this->getUserAuthenticated();
        $organization1 = Organization::factory()->for($user)->create();
        $department1 = Department::factory()->for($organization1)->create();
        $job1 = Job::factory()->for($department1)->create();
        $obligation1 = Obligation::factory()->for($job1)->create();
        
        $organization2 = Organization::factory()->for($user)->create();
        $department2 = Department::factory()->for($organization2)->create();
        $job2 = Job::factory()->for($department2)->create();

        $dataInvalid = [
            "description" => "Description...",
            "job_id" => $job1->id,
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("obligations.store", [$organization2, $department2, $job1]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_id");
        $response->assertJson([
            "errors" => [
                "job_id" => [
                    "The job id is invalid because is not contains in the department and the organization"
                ]
            ]
        ]);

        $response = $this->putJson( route("obligations.update", [$organization2, $department2, $job1, $obligation1]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_id");
        $response->assertJson([
            "errors" => [
                "job_id" => [
                    "The job id is invalid because is not contains in the department and the organization"
                ]
            ]
        ]);


        //CASOS DE EXITO
        $dataValid = [
            "description" => "Description...",
            "job_id" => $job1->id,
        ];

        $response = $this->postJson( route("obligations.store", [$organization1, $department1, $job1]), $dataValid );

        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("job_id");

        $response = $this->putJson( route("obligations.update", [$organization1, $department1, $job1, "2"]), $dataValid );

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("job_id");
    }

    /**
     * @test
     * @dataProvider invalidRouteForUsersGuest
     */
    public function users_guest_cannot_access_to_routes_protected($route, $method, $param) :void
    {   
        $organization = Organization::factory()->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        Obligation::factory()->for($job)->create();
        
        $response = $this->$method( route($route, [$organization, $department, $job, "obligation" => $param]) );

        $response->assertStatus(401);
    }
    
    public function invalidRouteForUsersGuest() :array
    {
        return [
            "Route obligations.index" => [ "obligations.index" , "getJson", null ],
            "Route obligations.show" => [ "obligations.show" , "getJson", "1" ],
            "Route obligations.store" => [ "obligations.store" , "postJson", null ],
            "Route obligations.update" => [ "obligations.update" , "putJson", "1" ],
            "Route obligations.destroy" => [ "obligations.destroy" , "deleteJson", "1" ],
        ];     
    }

    /**
     * @test
     * @dataProvider dataInvalidForUsersWithRoleCustomer
     */
    public function users_with_role_customer_cannot_perform_to_actions_protected($method, $route, $param, $dataInvalid) :void
    {
        $this->getUserAuthenticated();
        $organization = Organization::factory()->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        Obligation::factory()->for($job)->create();

        $response = $this->$method( route($route, [$organization, $department, $job, "obligation" => $param]), $dataInvalid );

        $response->assertStatus(403);
    }

    public function dataInvalidForUsersWithRoleCustomer() :array
    {
        $dataInvalid = [
            "description" => "Description...",
            "job_id" => "1",
        ];
        
        return [
            "User with role customer cannot view all obligations in job not owner" => [ "getJson", "obligations.index", null, $dataInvalid ], 
            "User with role customer cannot view obligations details in job not owner" => [ "getJson", "obligations.show", "1", $dataInvalid ], 
            "User with role customer cannot store obligations in job not owner" => [ "postJson", "obligations.store", null, $dataInvalid ], 
            "User with role customer cannot update obligations in job not owner" => [ "putJson", "obligations.update", "1", $dataInvalid ],
            "User with role customer cannot delete obligations in job not owner" => [ "putJson", "obligations.destroy", "1", $dataInvalid ], 
        ];
    }

}
