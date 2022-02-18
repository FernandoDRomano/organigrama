<?php

namespace Tests\Feature\Controllers\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\JobLevel;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Sequence;

class JobLevelControllerTest extends TestCase
{
    use RefreshDatabase;

    public function getUserWithRoleAdminAuthenticated() :User
    {
        $user = User::factory()->create(["role" => User::ROLE_ADMIN]);
        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_see_all_job_levels_order_by_hierarchy()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $jobLevels = JobLevel::factory()->count(3)->state(
                    new Sequence(
                        ["hierarchy" => 1],
                        ["hierarchy" => 2],
                        ["hierarchy" => 3],
                    ))->create();

        $response = $this->getJson( route("job-levels.index") );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $jobLevels[0]->id,
                    "name" => $jobLevels[0]->name,
                    "hierarchy" => (string) $jobLevels[0]->hierarchy
                ],
                [
                    "id" => $jobLevels[1]->id,
                    "name" => $jobLevels[1]->name,
                    "hierarchy" => (string) $jobLevels[1]->hierarchy
                ],
                [
                    "id" => $jobLevels[2]->id,
                    "name" => $jobLevels[2]->name,
                    "hierarchy" => (string) $jobLevels[2]->hierarchy
                ],
            ],
            "links" => [],
            "meta" => [],
            "message" => "Job levels all!!"
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_see_job_levels_detail()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $jobLevel = JobLevel::factory()->create();

        $response = $this->getJson( route("job-levels.show", $jobLevel) );

        $response->assertStatus(200);
        $response->assertExactJson([
            "data" => [
                "id" => $jobLevel->id,
                "name" => $jobLevel->name,
                "hierarchy" => (string) $jobLevel->hierarchy
            ],
            "message" => "Job level!!"
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_create_job_levels()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $data = [ "name" => "1°", "hierarchy" => "1" ];

        $response = $this->postJson( route('job-levels.store'), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("job_levels", 1);
        $this->assertDatabaseHas("job_levels", [
            "name" => $data["name"],
            "hierarchy" => $data["hierarchy"]
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_update_job_levels()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $jobLevel = JobLevel::factory()->create(["hierarchy" => "1"]);

        $data = [ "name" => "Nuevo Nivel Update", "hierarchy" => "1" ];

        $response = $this->putJson( route('job-levels.update', $jobLevel), $data );

        $response->assertStatus(200);
        $this->assertDatabaseCount("job_levels", 1);
        $this->assertNotEquals($jobLevel->name, $data["name"]);
        $this->assertDatabaseHas("job_levels", [
            "name" => $data["name"],
            "hierarchy" => $data["hierarchy"]
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_delete_job_levels()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $jobLevel = JobLevel::factory()->create();

        $response = $this->deleteJson( route('job-levels.destroy', $jobLevel) );

        $response->assertStatus(204);
        $this->assertDatabaseCount("job_levels", 0);
        $this->assertDatabaseMissing("job_levels", [
            "name" => $jobLevel->name,
            "hierarchy" => $jobLevel->hierarchy
        ]);
    }


    /**
     * @test
     * @dataProvider invalidDataForCreateOrUpdateJobLevels
     */
    public function users_with_role_admin_cannot_create_job_levels_with_invalid_data($method, $route, $invalidData, $inputInvalid, $param) :void
    {
        $this->getUserWithRoleAdminAuthenticated();
        JobLevel::factory()->create(["hierarchy" => 1]);

        $response = $this->$method( route($route, $param), $invalidData );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($inputInvalid);
    }

    public function invalidDataForCreateOrUpdateJobLevels() :array
    {
        $store = "job-levels.store";
        $update = "job-levels.update";
        $post = "postJson";
        $put = "putJson";

        return [
            "The field name is required" => [$post, $store, ["name" => ""], "name", null],
            "The field name is required" => [$put, $update, ["name" => ""], "name", "1" ],
            "The field name must be at least 2 characters" => [$post, $store, ["name" => Str::random(1)], "name", null ],
            "The field name must be at least 2 characters" => [$put, $update, ["name" => Str::random(1)], "name", "1" ],
            "The field name must not be greater than 20 characters" => [$post, $store, ["name" => Str::random(21)], "name", null ],
            "The field name must not be greater than 20 characters" => [$put, $update, ["name" => Str::random(21)], "name", "1" ],
            "The field hierarchy is required" => [$post, $store, ["hierarchy" => ""], "hierarchy", null ],
            "The field hierarchy is required" => [$put, $update, ["hierarchy" => ""], "hierarchy", "1" ],
            "The field hierarchy must be at least 1" => [$post, $store, ["hierarchy" => 0], "hierarchy", null ],
            "The field hierarchy must be at least 1" => [$put, $update, ["hierarchy" => 0], "hierarchy", "1" ],
            "The field hierarchy must not be greater than 11" => [$post, $store, ["hierarchy" => 11], "hierarchy", null ],
            "The field hierarchy must not be greater than 11" => [$put, $update, ["hierarchy" => 11], "hierarchy", "1" ],
            "The field hierarchy has already been taken" => [$post, $store, ["hierarchy" => 1], "hierarchy", null ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidRouteForUsersGuest
     */
    public function users_guest_cannot_access_to_routes_protected($route, $method, $param) :void
    {   
        $response = $this->$method( route($route, $param) );

        $response->assertStatus(401);
    }
    
    public function invalidRouteForUsersGuest() :array
    {
        return [
            "Route job-levels.index" => [ "job-levels.index" , "getJson", null ],
            "Route job-levels.show" => [ "job-levels.show" , "getJson", "1" ],
            "Route job-levels.store" => [ "job-levels.store" , "postJson", null ],
            "Route job-levels.update" => [ "job-levels.update" , "putJson", "1" ],
            "Route job-levels.destroy" => [ "job-levels.destroy" , "deleteJson", "1" ],
        ];     
    }

    /**
     * @test
     * @dataProvider invalidActionsForUsersCustomer
     */
    public function users_with_role_customer_cannot_access_to_routes_protected($route, $method, $param) :void
    {   
        Sanctum::actingAs( User::factory()->create() );
        JobLevel::factory()->create(["hierarchy" => 1]);
        $data = ["name" => "Name of job level", "hierarchy" => 10];

        $response = $this->$method( route($route, $param), $data );

        $response->assertStatus(403);
    }
    
    public function invalidActionsForUsersCustomer() :array
    {
        return [
            "Route job-levels.index" => [ "job-levels.index" , "getJson", null ],
            "Route job-levels.show" => [ "job-levels.show" , "getJson", "1" ],
            "Route job-levels.destroy" => [ "job-levels.destroy" , "deleteJson", "1" ],
            "Route job-levels.store" => [ "job-levels.store" , "postJson", null ],
            "Route job-levels.update" => [ "job-levels.update" , "putJson", "1" ],
        ];     
    }
    
}
