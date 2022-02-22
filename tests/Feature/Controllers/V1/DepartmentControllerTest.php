<?php

namespace Tests\Feature\Controllers\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Str;
use App\Models\Organization;
use Laravel\Sanctum\Sanctum;
use App\Models\DepartmentLevel;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentControllerTest extends TestCase
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
    public function authenticated_users_can_view_all_departments_in_order_desc() :void
    {
        $user = $this->getUserAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->for($user)->create();
        $departments = Department::factory(3)->for($organization)->create();
        $departments = $departments->loadCount(["jobs", "departments"])->loadMissing('level');

        $response = $this->getJson( route("departments.index", $organization) );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $departments[2]->id,
                    "name" => $departments[2]->name,
                    "level" => [
                        "id" => $departments[2]->level->id,
                        "name" => $departments[2]->level->name,
                        "hierarchy" => $departments[2]->level->hierarchy,
                    ],
                    "counts" => [
                        "jobs" => $departments[2]->jobs_count,
                        "departments_children" => $departments[2]->departments_count
                    ]
                ],
                [
                    "id" => $departments[1]->id,
                    "name" => $departments[1]->name,
                    "level" => [
                        "id" => $departments[1]->level->id,
                        "name" => $departments[1]->level->name,
                        "hierarchy" => $departments[1]->level->hierarchy,
                    ],
                    "counts" => [
                        "jobs" => $departments[1]->jobs_count,
                        "departments_children" => $departments[1]->departments_count
                    ]
                ],
                [
                    "id" => $departments[0]->id,
                    "name" => $departments[0]->name,
                    "level" => [
                        "id" => $departments[0]->level->id,
                        "name" => $departments[0]->level->name,
                        "hierarchy" => $departments[0]->level->hierarchy,
                    ],
                    "counts" => [
                        "jobs" => $departments[0]->jobs_count,
                        "departments_children" => $departments[0]->departments_count
                    ]
                ],
            ],
            "relationships" => [
                "organization" => [
                    "id" => $organization->id,
                    "name" => $organization->name
                ]
            ],
            "links" => [],
            "meta" => [],
            "message" => "Departments all!!"
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_see_department_details() :void{
        $user = $this->getUserAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create();
        $department = $department->loadCount(["jobs", "departments"])->loadMissing('level');

        $response = $this->getJson( route("departments.show", [$organization, $department]) );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "name",
                "level" => [],
                "jobs" => [],
                "departments_children" => [],
                "counts" => [
                    "jobs",
                    "departments_children"
                ]
            ],
            "message"
        ]);
    }
     
    /**
     * @test
     */
    public function authenticated_users_can_create_departments() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $data = [
            "name" => "Department Example",
            "organization_id" => "1",
            "department_level_id" => "1",
            "department_id" => null
        ];

        $response = $this->postJson( route("departments.store", $organization), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("departments", 1);
        $this->assertDatabaseHas("departments", $data);
    }

    /**
     * @test
     */
    public function authenticated_users_can_update_departments() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $data = [
            "name" => "Department Example",
            "organization_id" => "1",
            "department_level_id" => "1",
            "department_id" => null
        ];
        $department = Department::factory()->for($organization)->create($data);
        $data["name"] = "Department Update";
        
        $response = $this->putJson( route("departments.update", [$organization, $department]), $data);

        $response->assertStatus(200);
        $this->assertDatabaseCount("departments", 1);
        $this->assertDatabaseHas("departments", $data);
    }

    /**
     * @test
     */
    public function authenticated_users_can_delete_departments() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $data = [
            "name" => "Department Example",
            "organization_id" => "1",
            "department_level_id" => "1",
            "department_id" => null
        ];
        $department = Department::factory()->for($organization)->create($data);
        
        $response = $this->deleteJson( route("departments.destroy", [$organization, $department]));

        $response->assertStatus(204);
        $this->assertDatabaseCount("departments", 0);
        $this->assertDatabaseMissing("departments", $data);
    }

    /**
     * @test
     * @dataProvider invalidDataForCreateOrUpdateDepartments
     */
    public function authenticated_users_cannot_create_or_update_departments_with_invalid_data($method, $route, $invalidData, $inputInvalid, $param) :void
    {
        $user = $this->getUserAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        DepartmentLevel::factory()->create(["hierarchy" => 2]);
        $organization = Organization::factory()->for($user)->create();
        Department::factory()->for($organization)->create(["department_level_id" => "1"]);

        $response = $this->$method( route($route, [$organization, "department" => $param]), $invalidData );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($inputInvalid);
    }

    public function invalidDataForCreateOrUpdateDepartments() :array
    {
        $store = "departments.store";
        $update = "departments.update";
        $post = "postJson";
        $put = "putJson";

        return [
            "The field name is required" => [$post, $store, ["name" => "", "department_level_id" => "1"], "name", null],
            "The field name is required" => [$put, $update, ["name" => "", "department_level_id" => "1"], "name", "1" ],
            "The field name must be at least 3 characters" => [$post, $store, ["name" => Str::random(2), "department_level_id" => "1"], "name", null ],
            "The field name must be at least 3 characters" => [$put, $update, ["name" => Str::random(2), "department_level_id" => "1"], "name", "1" ],
            "The field name must not be greater than 30 characters" => [$post, $store, ["name" => Str::random(31), "department_level_id" => "1"], "name", null ],
            "The field name must not be greater than 30 characters" => [$put, $update, ["name" => Str::random(31), "department_level_id" => "1"], "name", "1" ],

            "The field organization_id is required" => 
                [ $post, $store, ["name" => "name", "department_level_id" => "1", "organization_id" => ""] , "organization_id", null],
            "The field organization_id is required" => 
                [$put, $update, ["name" => "name", "department_level_id" => "1", "organization_id" => ""], "organization_id", "1" ],
            "The field organization_id is invalid" => 
                [ $post, $store, ["name" => "name", "department_level_id" => "1", "organization_id" => "2"] , "organization_id", null],
            "The field organization_id is invalid" => 
                [$put, $update, ["name" => "name", "department_level_id" => "1", "organization_id" => "2"], "organization_id", "1" ],

            "The field department_level_id is required" => 
                [ $post, $store, ["name" => "name", "organization_id" => "1", "department_level_id" => ""] , "department_level_id", null],
            "The field department_level_id is required" => 
                [$put, $update, ["name" => "name", "organization_id" => "1", "department_level_id" => ""], "department_level_id", "1" ],
            "The field department_level_id is invalid" => 
                [ $post, $store, ["name" => "name", "organization_id" => "1", "department_level_id" => "3"] , "department_level_id", null],
            "The field department_level_id is invalid" => 
                [$put, $update, ["name" => "name", "organization_id" => "1", "department_level_id" => "3"], "department_level_id", "1" ],
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
        $department = Department::factory()->for($organization)->create();

        $dataInvalid = [
            "name" => "example",
            "organization_id" => $organization->id
        ];

        $response = $this->postJson( route("departments.store", $organization2), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("organization_id");
        $response->assertJson([
            "errors" => [
                 "organization_id" => [
                      "The organization id is invalid because organization id does not correspont with url"  
                 ]
             ]
       ]);

        $response = $this->putJson( route("departments.update", [$organization2, $department]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("organization_id");
        $response->assertJson([
            "errors" => [
                 "organization_id" => [
                      "The organization id is invalid because organization id does not correspont with url"  
                 ]
             ]
       ]);
    }

    /**
     * @test
     */
    public function test_rule_OnlyApartmentOnTheFirstLevel() :void
    {
        //This rule verify that department_level_id have only one department when hierarchy be equals to one 
        $user = $this->getUserAuthenticated();
        $firstLevel = DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->for($user)->create();
        $department1 = Department::factory()->for($organization)->create(["department_level_id" => $firstLevel->id]);
        $department2 = Department::factory()->for($organization)->create();

        $dataInvalid = [
            "name" => "example",
            "organization_id" => $organization->id,
            "department_level_id" => $firstLevel->id
        ];

        //PRIMER CASO, NO PUEDO CREAR UN DEPARTAMENTO SI YA EXISTE UNO CON LA JERARQUIA 1 EN LA ORGANIZACIÓN
        $response = $this->postJson( route("departments.store", $organization), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_level_id");      
        $response->assertJson([
            "errors" => [
                 "department_level_id" => [
                      "The department level id is invalid because exists one department in the first level"  
                 ]
             ]
       ]);

        //SEGUNDO CASO, NO PUEDO ACTUALIZAR UN DEPARTAMENTO CON LA JERARQUIA 1 SI YA EXISTE UN DEPARTAMENTO CON ESA JERARQUIA EN LA ORGANIZACIÓN
        $response = $this->putJson( route("departments.update", [$organization, $department2]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_level_id");
        $response->assertJson([
            "errors" => [
                 "department_level_id" => [
                      "The department level id is invalid because exists one department in the first level"  
                 ]
             ]
       ]);


       //TERCER CASO, PUEDO ACTUALIZAR UN DEPARTAMENTO QUE TIENE JERARQUIA 1
        $data = [
            "name" => "example updated",
            "organization_id" => $organization->id,
            "department_level_id" => $firstLevel->id
        ];

        $response = $this->putJson( route("departments.update", [$organization, $department1]), $data);

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("department_level_id");

    }

    /**
     * @test
     */
    public function test_rule_CanBeUpdatedIfHaveNotChildren() :void
    {
        // This rule verify that department_level_id can be updated if the department have not department children
        $user = $this->getUserAuthenticated();
        $firstLevel = DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $secondLevel = DepartmentLevel::factory()->create(["hierarchy" => 2]);
        $thirdLevel = DepartmentLevel::factory()->create(["hierarchy" => 3]);
        $organization = Organization::factory()->for($user)->create();
        $department1 = Department::factory()->for($organization)->create(["department_level_id" => $firstLevel->id]);
        Department::factory()->for($organization)->create(["department_level_id" => $secondLevel->id, "department_id" => $department1->id]);
        Department::factory()->for($organization)->create(["department_level_id" => $secondLevel->id, "department_id" => $department1->id]);

        $dataInvalid = [
            "name" => "Update Name",
            "organization_id" => $organization->id,
            "department_level_id" => $thirdLevel->id,
        ];

        $response = $this->putJson( route("departments.update", [$organization, $department1]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_level_id");      
        $response->assertJson([
            "errors" => [
                 "department_level_id" => [
                      "The department level id is invalid because exists more than departments children"  
                 ]
             ]
       ]);

       $dataValid = [
            "name" => "Update Name X2",
            "organization_id" => $organization->id,
            "department_level_id" => $firstLevel->id,
       ];

       $response = $this->putJson( route("departments.update", [$organization, $department1]), $dataValid );
    
       $response->assertStatus(200);
       $response->assertJsonMissingValidationErrors("department_level_id");
    }

    /**
     * @test
     */
    public function test_rule_requiredIf() :void
    {
        // This rule verify that department_id be required if hierarchy is greater than one
        $user = $this->getUserAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        DepartmentLevel::factory()->create(["hierarchy" => 2]);
        $organization = Organization::factory()->for($user)->create();
        $department = Department::factory()->for($organization)->create(["department_level_id" => "1"]);
        
        $dataInvalid = [
            "name" => "Name",
            "organization_id" => $organization->id,
            "department_level_id" => "2",
        ];

        $response = $this->postJson( route("departments.store", $organization), $dataInvalid );
    
        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                 "department_id" => [
                      "The department id field is required."  
                 ]
             ]
       ]);

        $response = $this->putJson( route("departments.update", [$organization, $department]), $dataInvalid );
    
        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                 "department_id" => [
                      "The department id field is required."  
                 ]
             ]
       ]); 
       
       //DELETE DEPARTMENT INITIAL FOR CREATE NEW DEPARMENT
        $this->deleteJson( route("departments.destroy", [$organization, $department]) );

        $dataValid = [
                "name" => "Name",
                "organization_id" => $organization->id,
                "department_level_id" => "1",
                "department_id" => null
        ];

        $response = $this->postJson( route("departments.store", $organization), $dataValid );
        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("department_id");

        $dataValid = [
            "name" => "Name Updated",
            "organization_id" => $organization->id,
            "department_level_id" => "1",
            "department_id" => null
        ];

        $response = $this->putJson( route("departments.update", [$organization, "2"]), $dataValid );
        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("department_id");
    }

    /**
     * @test
     */
    public function test_rule_ExitsDepartmentInOrganization() :void
    {
        // This rule verify that department_id exists in the organization
        $user = $this->getUserAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        DepartmentLevel::factory()->create(["hierarchy" => 2]);
        $organization = Organization::factory()->for($user)->create();
        $departments = Department::factory(3)->for($organization)
                        ->state(new Sequence(
                            ["department_level_id" => "1"],
                            ["department_level_id" => "2"],
                            ["department_level_id" => "2"],
                        ))->create();

        $dataInvalid = [
                "name" => "Name",
                "organization_id" => $organization->id,
                "department_level_id" => "2",
                "department_id" => "4"
        ];

        //CASOS DE ERROR
        $response = $this->postJson( route("departments.store", $organization), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                 "department_id" => [
                      "The department id is invalid, because department id is not exists in organization"  
                 ]
             ]
       ]); 

       $response = $this->putJson( route("departments.update", [$organization, $departments[1]]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                 "department_id" => [
                      "The department id is invalid, because department id is not exists in organization"  
                 ]
             ]
       ]); 

       //CASOS DE EXITOS
       $dataValid = [
            "name" => "Name Department",
            "organization_id" => $organization->id,
            "department_level_id" => "2",
            "department_id" => "1"
        ];

        $response = $this->postJson( route("departments.store", $organization), $dataValid );

        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("department_id");

        $response = $this->putJson( route("departments.update", [$organization, $departments[1]]), $dataValid );

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("department_id");
    }

    /**
     * @test
     */
    public function test_rule_SelectedDepartmentIfLevelIsGreaterThanOne() :void
    {
        // This rule verify that department_id be contains in departments of level higher
        $user = $this->getUserAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        DepartmentLevel::factory()->create(["hierarchy" => 2]);
        DepartmentLevel::factory()->create(["hierarchy" => 3]);
        $organization = Organization::factory()->for($user)->create();
        $departments = Department::factory(4)->for($organization)
                        ->state(new Sequence(
                            ["department_level_id" => "1"],
                            ["department_level_id" => "2", "department_id" => "1"],
                            ["department_level_id" => "2", "department_id" => "1"],
                            ["department_level_id" => "3", "department_id" => "2"]
                        ))->create();

        $dataInvalid = [
                "name" => "Name",
                "organization_id" => $organization->id,
                "department_level_id" => "3",
                "department_id" => "1"
        ];

        //CASOS DE ERROR

        $response = $this->postJson( route("departments.store", $organization), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                "department_id" => [
                    "The department id is invalid, because is not contains in the departments of level higher"
                ]
            ]
        ]);

        $response = $this->putJson( route("departments.update", [$organization, $departments[3]]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("department_id");
        $response->assertJson([
            "errors" => [
                "department_id" => [
                    "The department id is invalid, because is not contains in the departments of level higher"
                ]
            ]
        ]);

        //CASOS DE EXITO
        $dataValid = [
            "name" => "Name Department",
            "organization_id" => $organization->id,
            "department_level_id" => "3",
            "department_id" => "2"
        ];

        $response = $this->postJson( route("departments.store", $organization), $dataValid );

        $response->assertStatus(201);
        $response->assertJsonMissingValidationErrors("department_id");

        $response = $this->putJson( route("departments.update", [$organization, $departments[3]]), $dataValid );

        $response->assertStatus(200);
        $response->assertJsonMissingValidationErrors("department_id");
    }

    /**
     * @test
     * @dataProvider invalidRouteForUsersGuest
     */
    public function users_guest_cannot_access_to_routes_protected($route, $method, $param) :void
    {   
        $organization = Organization::factory()->create();
        $response = $this->$method( route($route, ["organization" => $organization, "department" => $param]) );

        $response->assertStatus(401);
    }
    
    public function invalidRouteForUsersGuest() :array
    {
        return [
            "Route departments.index" => [ "departments.index" , "getJson", null ],
            "Route departments.show" => [ "departments.show" , "getJson", "1" ],
            "Route departments.store" => [ "departments.store" , "postJson", null ],
            "Route departments.update" => [ "departments.update" , "putJson", "1" ],
            "Route departments.destroy" => [ "departments.destroy" , "deleteJson", "1" ],
        ];     
    }

    /**
     * @test
     * @dataProvider dataInvalidForUsersWithRoleCustomer
     */
    public function users_with_role_customer_cannot_perform_to_actions_protected($method, $route, $param, $dataInvalid) :void
    {
        $this->getUserAuthenticated();
        DepartmentLevel::factory()->create(["hierarchy" => 1]);
        $organization = Organization::factory()->has(Department::factory()->count(1))->create();

        $response = $this->$method( route($route, ["organization" => $organization, "department" => $param]), $dataInvalid );

        $response->assertStatus(403);
    }

    public function dataInvalidForUsersWithRoleCustomer() :array
    {
        $dataInvalid = [
            "name" => "Department Example",
            "organization_id" => "1",
            "department_level_id" => "1",
            "department_id" => null
        ];
        
        return [
            "User with role customer cannot view all departments in organizations not owner" => [ "getJson", "departments.index", null, $dataInvalid ], 
            "User with role customer cannot view departments details in organizations not owner" => [ "getJson", "departments.show", "1", $dataInvalid ], 
            "User with role customer cannot store departments in organizations not owner" => [ "postJson", "departments.store", null, $dataInvalid ], 
            "User with role customer cannot update departments in organizations not owner" => [ "putJson", "departments.update", "1", $dataInvalid ],
            "User with role customer cannot delete departments in organizations not owner" => [ "putJson", "departments.destroy", "1", $dataInvalid ], 
        ];
    }

}
