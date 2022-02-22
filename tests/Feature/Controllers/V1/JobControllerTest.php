<?php

namespace Tests\Feature\Controllers\V1;

use App\Models\Job;
use Tests\TestCase;
use App\Models\User;
use App\Models\JobLevel;
use App\Models\Department;
use Illuminate\Support\Str;
use App\Models\Organization;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Sequence;

class JobControllerTest extends TestCase
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
    public function authenticated_users_can_view_all_jobs_in_order_desc() :void
    {
        $user = $this->getUserAuthenticated();
        JobLevel::factory()->create(["hierarchy" => 1]);
        JobLevel::factory()->create(["hierarchy" => 2]);
        JobLevel::factory()->create(["hierarchy" => 3]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $jobs = Job::factory(3)->for($department)
                    ->state(new Sequence(
                        ["job_level_id" => "1"],
                        ["job_level_id" => "2"],
                        ["job_level_id" => "3"]
                    ))->create();
        $jobs->loadCount(["obligations", "employes", "level"])->loadMissing(["department"]);

        $response = $this->getJson( route("jobs.index", [$organization, $department]) );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $jobs[2]->id,
                    "name" => $jobs[2]->name,
                    "level" => [],
                    "counts" => [
                        "obligations" => $jobs[2]->obligations_count,
                        "employes" => $jobs[2]->employes_count
                    ]
                ],
                [
                    "id" => $jobs[1]->id,
                    "name" => $jobs[1]->name,
                    "level" => [],
                    "counts" => [
                        "obligations" => $jobs[1]->obligations_count,
                        "employes" => $jobs[1]->employes_count
                    ]
                ],
                [
                    "id" => $jobs[0]->id,
                    "name" => $jobs[0]->name,
                    "level" => [],
                    "counts" => [
                        "obligations" => $jobs[0]->obligations_count,
                        "employes" => $jobs[0]->employes_count
                    ]
                ],
            ],
            "links" => [],
            "meta" => [],
            "relationship" => [
                "department" => []
            ],
            "message" => "Jobs all!!"
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_see_jobs_details() :void
    {
        $user = $this->getUserAuthenticated();
        JobLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $job->loadCount(["obligations", "employes", "level"])->loadMissing(["department"]);

        $response = $this->getJson( route("jobs.show", [$organization, $department, $job]) );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "name",
                "obligations" => [],
                "employes" => [],
                "level" => [],
                "counts" => [
                    "obligations",
                    "employes"
                ]    
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
        $jobLevel = JobLevel::factory()->create(["hierarchy" => 1]);
        $data = [
            "name" => "Job Name",
            "department_id" => $department->id,
            "job_level_id" => $jobLevel->id
        ];

        $response = $this->postJson( route("jobs.store", [$organization, $department]), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("jobs", 1);
        $this->assertDatabaseHas("jobs", $data);
    }

    /**
     * @test
     */
    public function authenticated_users_can_update_jobs() :void
    {
        $user = $this->getUserAuthenticated();
        $jobLevel = JobLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        $data = [
            "name" => "Job Name",
            "department_id" => $department->id,
            "job_level_id" => $jobLevel->id
        ];

        $response = $this->putJson( route("jobs.update", [$organization, $department, $job]), $data );

        $response->assertStatus(200);
        $this->assertDatabaseCount("jobs", 1);
        $this->assertDatabaseHas("jobs", $data);
    }

    /**
     * @test
     */
    public function authenticated_users_can_delete_jobs() :void
    {
        $user = $this->getUserAuthenticated();
        $jobLevel = JobLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job = Job::factory()->for($department)->create();
        
        $response = $this->deleteJson( route("jobs.destroy", [$organization, $department, $job]) );

        $response->assertStatus(204);
        $this->assertDatabaseCount("jobs", 0);
        $this->assertDatabaseMissing("jobs", [
            "name" => $job->name,
            "department_id" => $job->department_id,
            "job_level_id" => $job->job_level_id
        ]);
    }

    /**
     * @test
     * @dataProvider invalidDataForCreateOrUpdateJobs
     */
    public function authenticated_users_cannot_create_or_update_jobs_with_invalid_data($method, $route, $invalidData, $inputInvalid, $param) :void
    {
        $user = $this->getUserAuthenticated();
        JobLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        Job::factory()->for($department)->create(["job_level_id" => "1"]);

        $response = $this->$method( route($route, [$organization, $department, "job" => $param]), $invalidData );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($inputInvalid);
    }

    public function invalidDataForCreateOrUpdateJobs() :array
    {
        $store = "jobs.store";
        $update = "jobs.update";
        $post = "postJson";
        $put = "putJson";

        return [
            "The field name is required" => [$post, $store, ["name" => "", "department_id" => "1", "job_level_id" => "1"], "name", null],
            "The field name is required" => [$put, $update, ["name" => "", "department_id" => "1", "job_level_id" => "1"], "name", "1" ],
            "The field name must be at least 2 characters" => [$post, $store, ["name" => Str::random(1), "department_id" => "1", "job_level_id" => "1"], "name", null ],
            "The field name must be at least 2 characters" => [$put, $update, ["name" => Str::random(1), "department_id" => "1", "job_level_id" => "1"], "name", "1" ],
            "The field name must not be greater than 30 characters" => 
                [$post, $store, ["name" => Str::random(31), "department_id" => "1", "job_level_id" => "1"], "name", null ],
            "The field name must not be greater than 30 characters" => 
                [$put, $update, ["name" => Str::random(31), "department_id" => "1", "job_level_id" => "1"], "name", "1" ],

            "The field department_id is required" => 
                [ $post, $store, ["name" => "name", "job_level_id" => "1", "department_id" => ""] , "department_id", null],
            "The field department_id is required" => 
                [$put, $update, ["name" => "name", "job_level_id" => "1", "department_id" => ""], "department_id", "1" ],
            "The field department_id is invalid" => 
                [ $post, $store, ["name" => "name", "job_level_id" => "1", "department_id" => "2"] , "department_id", null],
            "The field department_id is invalid" => 
                [$put, $update, ["name" => "name", "job_level_id" => "1", "department_id" => "2"], "department_id", "1" ],

            "The field job_level_id is required" => 
                [ $post, $store, ["name" => "name", "department_id" => "1", "job_level_id" => ""] , "job_level_id", null],
            "The field job_level_id is required" => 
                [$put, $update, ["name" => "name", "department_id" => "1", "job_level_id" => ""], "job_level_id", "1" ],
            "The field job_level_id is invalid" => 
                [ $post, $store, ["name" => "name", "department_id" => "1", "job_level_id" => "2"] , "job_level_id", null],
            "The field job_level_id is invalid" => 
                [$put, $update, ["name" => "name", "department_id" => "1", "job_level_id" => "2"], "job_level_id", "1" ],
        ];
    }

    /**
     * @test
     */
    public function test_rule_DepartmentContainsValidId() :void
    {
        //This rule verify that department_id be equals to department send for parameter
        $user = $this->getUserAuthenticated();
        JobLevel::factory()->create(["hierarchy" => "1"]);
        $organization = Organization::factory()->for($user)->create();
        $department1 = Department::factory()->for($organization)->create();
        $department2 = Department::factory()->create();
        $job = Job::factory()->for($department1)->create();
        $dataInvalid = [
            "name" => "Name job",
            "job_level_id" => "1",
            "department_id" => $department2->id
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("jobs.store", [$organization, $department1]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                "department_id" => [
                    "The department id is invalid because department id does not correspont with url"
                ]
            ]
        ]);

        $response = $this->putJson( route("jobs.update", [$organization, $department1, $job]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                "department_id" => [
                    "The department id is invalid because department id does not correspont with url"
                ]
            ]
        ]);

        //ELIMINAR EL JOB INICIAL
        $this->deleteJson( route("jobs.destroy", [$organization, $department1, $job]) );

        //CASOS DE EXITO
        $dataValid = [
            "name" => "Name job",
            "job_level_id" => "1",
            "department_id" => $department1->id
        ];

        $response = $this->postJson( route("jobs.store", [$organization, $department1]), $dataValid );

        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("department_id");

        $response = $this->putJson( route("jobs.update", [$organization, $department1, "2"]), $dataValid );

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("department_id");
    }

    /**
     * @test
     */
    public function test_rule_ExitsDepartmentInOrganization() :void
    {
        //This rule verify that department_id exists in organization
        $user = $this->getUserAuthenticated();
        JobLevel::factory()->create(["hierarchy" => "1"]);
        $organization = Organization::factory()->for($user)->create();
        $department1 = Department::factory()->for($organization)->create();
        $department2 = Department::factory()->create();
        $job = Job::factory()->for($department1)->create();
        $dataInvalid = [
            "name" => "Name job",
            "job_level_id" => "1",
            "department_id" => $department2->id
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("jobs.store", [$organization, $department2]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                "department_id" => [
                    "The department id is invalid, because department id is not exists in organization"
                ]
            ]
        ]);

        $response = $this->putJson( route("jobs.update", [$organization, $department2, $job]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                "department_id" => [
                    "The department id is invalid, because department id is not exists in organization"
                ]
            ]
        ]);

        //ELIMINAR EL JOB INICIAL
        $this->deleteJson( route("jobs.destroy", [$organization, $department1, $job]) );

        //CASOS DE EXITO
        $dataValid = [
            "name" => "Name job",
            "job_level_id" => "1",
            "department_id" => $department1->id
        ];

        $response = $this->postJson( route("jobs.store", [$organization, $department1]), $dataValid );

        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("department_id");

        $response = $this->putJson( route("jobs.update", [$organization, $department1, "2"]), $dataValid );

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("department_id");
    }

    /**
     * @test
     */
    public function test_rule_JobLevelJustContainOneJobForLevel() :void
    {
        //This rule verify that job_level_id contains just one job for level
        $user = $this->getUserAuthenticated();
        JobLevel::factory()->create(["hierarchy" => "1"]);
        JobLevel::factory()->create(["hierarchy" => "2"]);
        JobLevel::factory()->create(["hierarchy" => "3"]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $job1 = Job::factory()->for($department)->create(["job_level_id" => "1"]);
        $job2 = Job::factory()->for($department)->create(["job_level_id" => "2"]);

        $dataInvalid = [
            "name" => "Name job",
            "job_level_id" => "1",
            "department_id" => $department->id
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("jobs.store", [$organization, $department]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_level_id");
        $response->assertJson([
            "errors" => [
                "job_level_id" => [
                    "The job level id contains more one job in this level"
                ]
            ]
        ]);

        $dataInvalid = [
            "name" => "Name job",
            "job_level_id" => "2",
            "department_id" => $department->id
        ];

        $response = $this->putJson( route("jobs.update", [$organization, $department, $job1]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("job_level_id");
        $response->assertJson([
            "errors" => [
                "job_level_id" => [
                    "The job level id contains more one job in this level"
                ]
            ]
        ]);

        //CASOS DE EXITO
        $dataValid = [
            "name" => "Name job",
            "job_level_id" => "3",
            "department_id" => $department->id
        ];

        $response = $this->postJson( route("jobs.store", [$organization, $department]), $dataValid );

        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("job_level_id");

        $dataValid = [
            "name" => "Name job updated",
            "job_level_id" => "1",
            "department_id" => $department->id
        ];

        $response = $this->putJson( route("jobs.update", [$organization, $department, $job1]), $dataValid );

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("job_level_id");

    }

    /**
     * @test
     * @dataProvider invalidRouteForUsersGuest
     */
    public function users_guest_cannot_access_to_routes_protected($route, $method, $param) :void
    {   
        $organization = Organization::factory()->create();
        $department = Department::factory()->for($organization)->create();
        Job::factory()->for($department)->create();
        
        $response = $this->$method( route($route, [$organization, $department, "job" => $param]) );

        $response->assertStatus(401);
    }
    
    public function invalidRouteForUsersGuest() :array
    {
        return [
            "Route jobs.index" => [ "jobs.index" , "getJson", null ],
            "Route jobs.show" => [ "jobs.show" , "getJson", "1" ],
            "Route jobs.store" => [ "jobs.store" , "postJson", null ],
            "Route jobs.update" => [ "jobs.update" , "putJson", "1" ],
            "Route jobs.destroy" => [ "jobs.destroy" , "deleteJson", "1" ],
        ];     
    }

    /**
     * @test
     * @dataProvider dataInvalidForUsersWithRoleCustomer
     */
    public function users_with_role_customer_cannot_perform_to_actions_protected($method, $route, $param, $dataInvalid) :void
    {
        $this->getUserAuthenticated();
        JobLevel::factory()->create(["hierarchy" => 1]);
        JobLevel::factory()->create(["hierarchy" => 2]);
        $organization = Organization::factory()->create();
        $department = Department::factory()->for($organization)->create();
        Job::factory()->for($department)->create(["job_level_id" => "1"]);

        $response = $this->$method( route($route, [$organization, $department, "job" => $param]), $dataInvalid );

        $response->assertStatus(403);
    }

    public function dataInvalidForUsersWithRoleCustomer() :array
    {
        $dataInvalid = [
            "name" => "Job Example",
            "department_id" => "1",
            "job_level_id" => "2",
        ];
        
        return [
            "User with role customer cannot view all jobs in department not owner" => [ "getJson", "jobs.index", null, $dataInvalid ], 
            "User with role customer cannot view jobs details in department not owner" => [ "getJson", "jobs.show", "1", $dataInvalid ], 
            "User with role customer cannot store jobs in department not owner" => [ "postJson", "jobs.store", null, $dataInvalid ], 
            "User with role customer cannot update jobs in department not owner" => [ "putJson", "jobs.update", "1", $dataInvalid ],
            "User with role customer cannot delete jobs in department not owner" => [ "putJson", "jobs.destroy", "1", $dataInvalid ], 
        ];
    }
}
