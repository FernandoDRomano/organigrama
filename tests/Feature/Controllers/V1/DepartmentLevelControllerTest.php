<?php

namespace Tests\Feature\Controllers\V1;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use App\Models\DepartmentLevel;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Sequence;

class DepartmentLevelControllerTest extends TestCase
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
    public function users_with_role_admin_can_see_all_department_levels_order_by_hierarchy()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $departmentLevels = DepartmentLevel::factory()->count(3)->state(
                    new Sequence(
                        ["hierarchy" => 1],
                        ["hierarchy" => 2],
                        ["hierarchy" => 3],
                    ))->create();

        $response = $this->getJson( route("department-levels.index") );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $departmentLevels[0]->id,
                    "name" => $departmentLevels[0]->name,
                    "hierarchy" => (string) $departmentLevels[0]->hierarchy
                ],
                [
                    "id" => $departmentLevels[1]->id,
                    "name" => $departmentLevels[1]->name,
                    "hierarchy" => (string) $departmentLevels[1]->hierarchy
                ],
                [
                    "id" => $departmentLevels[2]->id,
                    "name" => $departmentLevels[2]->name,
                    "hierarchy" => (string) $departmentLevels[2]->hierarchy
                ],
            ],
            "links" => [],
            "meta" => [],
            "message" => "Department levels all!!"
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_see_department_levels_detail()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $departmentLevel = DepartmentLevel::factory()->create();

        $response = $this->getJson( route("department-levels.show", $departmentLevel) );

        $response->assertStatus(200);
        $response->assertExactJson([
            "data" => [
                "id" => $departmentLevel->id,
                "name" => $departmentLevel->name,
                "hierarchy" => (string) $departmentLevel->hierarchy
            ],
            "message" => "Department level!!"
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_create_department_levels()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $data = [ "name" => "1°", "hierarchy" => "1" ];

        $response = $this->postJson( route('department-levels.store'), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("department_levels", 1);
        $this->assertDatabaseHas("department_levels", [
            "name" => $data["name"],
            "hierarchy" => $data["hierarchy"]
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_update_department_levels()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $departmentLevel = DepartmentLevel::factory()->create(["hierarchy" => "1"]);

        $data = [ "name" => "Nuevo Nivel Update", "hierarchy" => "1" ];

        $response = $this->putJson( route('department-levels.update', $departmentLevel), $data );

        $response->assertStatus(200);
        $this->assertDatabaseCount("department_levels", 1);
        $this->assertNotEquals($departmentLevel->name, $data["name"]);
        $this->assertDatabaseHas("department_levels", [
            "name" => $data["name"],
            "hierarchy" => $data["hierarchy"]
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_delete_department_levels()
    {
        $this->getUserWithRoleAdminAuthenticated();
        $departmentLevel = DepartmentLevel::factory()->create();

        $response = $this->deleteJson( route('department-levels.destroy', $departmentLevel) );

        $response->assertStatus(204);
        $this->assertDatabaseCount("department_levels", 0);
        $this->assertDatabaseMissing("department_levels", [
            "name" => $departmentLevel->name,
            "hierarchy" => $departmentLevel->hierarchy
        ]);
    }


    /**
     * @test
     * @dataProvider invalidDataForCreateOrUpdateDepartmentLevels
     */
    public function users_with_role_admin_cannot_create_department_levels_with_invalid_data($method, $route, $invalidData, $inputInvalid, $param) :void
    {
        $this->getUserWithRoleAdminAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);

        $response = $this->$method( route($route, $param), $invalidData );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($inputInvalid);
    }

    public function invalidDataForCreateOrUpdateDepartmentLevels() :array
    {
        $store = "department-levels.store";
        $update = "department-levels.update";
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
            "Route department-levels.index" => [ "department-levels.index" , "getJson", null ],
            "Route department-levels.show" => [ "department-levels.show" , "getJson", "1" ],
            "Route department-levels.store" => [ "department-levels.store" , "postJson", null ],
            "Route department-levels.update" => [ "department-levels.update" , "putJson", "1" ],
            "Route department-levels.destroy" => [ "department-levels.destroy" , "deleteJson", "1" ],
        ];     
    }

    /**
     * @test
     * @dataProvider invalidActionsForUsersCustomer
     */
    public function users_with_role_customer_cannot_access_to_routes_protected($route, $method, $param) :void
    {   
        Sanctum::actingAs( User::factory()->create() );
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $data = ["name" => "Name of job level", "hierarchy" => 10];

        $response = $this->$method( route($route, $param), $data );

        $response->assertStatus(403);
    }
    
    public function invalidActionsForUsersCustomer() :array
    {
        return [
            "Route department-levels.index" => [ "department-levels.index" , "getJson", null ],
            "Route department-levels.show" => [ "department-levels.show" , "getJson", "1" ],
            "Route department-levels.destroy" => [ "department-levels.destroy" , "deleteJson", "1" ],
            "Route department-levels.store" => [ "department-levels.store" , "postJson", null ],
            "Route department-levels.update" => [ "department-levels.update" , "putJson", "1" ],
        ];     
    }
    
}
