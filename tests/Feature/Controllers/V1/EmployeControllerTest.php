<?php

namespace Tests\Feature\Controllers\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employe;
use Illuminate\Support\Str;
use App\Models\Organization;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeControllerTest extends TestCase
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
    public function authenticated_users_can_view_all_employes_order_by_desc() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $employes = Employe::factory(2)->for($organization)->create();
        $employes->loadCount("jobs");

        $response = $this->getJson( route("employes.index", $organization) );
    
        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $employes[1]->id,
                    "full_name" => ucwords($employes[1]->last_name . ', ' . $employes[1]->first_name),
                    "dni" => $employes[1]->dni,
                    "date_of_birth" => $employes[1]->date_of_birth->format('m-d-Y'),
                    "address" => $employes[1]->address,
                    "counts" => [
                        "jobs" => $employes[1]->jobs_count
                    ]
                ],
                [
                    "id" => $employes[0]->id,
                    "full_name" => ucwords($employes[0]->last_name . ', ' . $employes[0]->first_name),
                    "dni" => $employes[0]->dni,
                    "date_of_birth" => $employes[0]->date_of_birth->format('m-d-Y'),
                    "address" => $employes[0]->address,
                    "counts" => [
                        "jobs" => $employes[0]->jobs_count
                    ]
                ]
                    
            ],
            "relationships" => [
                "organization" => [
                    "id" => $organization->id,
                    "name" => $organization->name
                ]
            ],
            "links" => [],
            "meta" => [],
            "message" => "Employes all!!"
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_show_employes_detail() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $employe = Employe::factory()->for($organization)->create();
        $employe->loadCount("jobs")->loadMissing("jobs");

        $response = $this->getJson( route("employes.show", [$organization, $employe]) );
    
        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "full_name",
                "dni", 
                "date_of_birth",
                "address",
                "jobs" => [],
                "counts" => [
                    "jobs"
                ]
            ],
            "message"
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_create_employes() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $data = [
            "first_name" => "Fernando Daniel", 
            "last_name" => "Romano", 
            "dni" => "12345678", 
            "date_of_birth" => "1992/01/01", 
            "address" => "Casa 1234", 
            "organization_id" => $organization->id
        ];

        $response = $this->postJson( route("employes.store", $organization), $data );

        $response->assertStatus(201);
        $this->assertDatabaseCount("employes", 1);
        $this->assertDatabaseHas("employes", [
            "first_name" => "Fernando Daniel", 
            "last_name" => "Romano", 
            "dni" => "12345678", 
            "address" => "Casa 1234", 
            "organization_id" => $organization->id
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_update_employes() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $employe = Employe::factory()->for($organization)->create();
        $data = [
            "first_name" => "First Name Update", 
            "last_name" => "Last Name Update", 
            "dni" => "87654321", 
            "date_of_birth" => "2000/01/01", 
            "address" => "Casa 4321", 
            "organization_id" => $organization->id
        ];

        $response = $this->putJson( route("employes.update", [$organization, $employe]), $data );

        $response->assertStatus(200);
        $this->assertDatabaseCount("employes", 1);
        $this->assertDatabaseHas("employes", [
            "first_name" => "First Name Update", 
            "last_name" => "Last Name Update", 
            "dni" => "87654321", 
            "address" => "Casa 4321", 
            "organization_id" => $organization->id
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_delete_employes() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $employe = Employe::factory()->for($organization)->create();

        $response = $this->deleteJson( route("employes.destroy", [$organization, $employe]) );

        $response->assertStatus(204);
        $this->assertDatabaseCount("employes", 0);
        $this->assertDatabaseMissing("employes", [
            "id" => $employe->id,
            "first_name" => $employe->first_name,
            "last_name" => $employe->last_name,
            "organization_id" => $employe->organization_id
        ]);
    }

    /**
     * @test
     * @dataProvider invalidDataForCreateOrUpdateEmployes
     */
    public function authenticated_users_cannot_create_or_update_employes_with_invalid_data($method, $route, $invalidData, $inputInvalid, $param) :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        Employe::factory()->for($organization)->create();

        $response = $this->$method( route($route, [$organization, "employe" => $param]), $invalidData );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($inputInvalid);
    }

    public function invalidDataForCreateOrUpdateEmployes() :array
    {
        $store = "employes.store";
        $update = "employes.update";
        $post = "postJson";
        $put = "putJson";

        return [
            "The field first_name is required" => [$post, $store, ["first_name" => ""], "first_name", null],
            "The field first_name is required" => [$put, $update, ["first_name" => ""], "first_name", "1" ],
            "The field first_name must be at least 2 characters" => [$post, $store, ["first_name" => Str::random(1)], "first_name", null ],
            "The field first_name must be at least 2 characters" => [$put, $update, ["first_name" => Str::random(1)], "first_name", "1" ],
            "The field first_name must not be greater than 40 characters" => [$post, $store, ["first_name" => Str::random(41)], "first_name", null ],
            "The field first_name must not be greater than 40 characters" => [$put, $update, ["first_name" => Str::random(41)], "first_name", "1" ],
            "The field last_name is required" => [$post, $store, ["last_name" => ""], "last_name", null],
            "The field last_name is required" => [$put, $update, ["last_name" => ""], "last_name", "1" ],
            "The field last_name must be at least 2 characters" => [$post, $store, ["last_name" => Str::random(1)], "last_name", null ],
            "The field last_name must be at least 2 characters" => [$put, $update, ["last_name" => Str::random(1)], "last_name", "1" ],
            "The field last_name must not be greater than 25 characters" => [$post, $store, ["last_name" => Str::random(26)], "last_name", null ],
            "The field last_name must not be greater than 25 characters" => [$put, $update, ["last_name" => Str::random(26)], "last_name", "1" ],
            "The field dni is required" => [$post, $store, ["dni" => ""], "dni", null ],
            "The field dni is required" => [$put, $update, ["dni" => ""], "dni", "1" ],
            "The field dni must be between 7 and 8 digits" => [$post, $store, ["dni" => "123456"], "dni", null ],
            "The field dni must be between 7 and 8 digits" => [$put, $update, ["dni" => "123456"], "dni", "1" ],
            "The field dni must be between 7 and 8 digits" => [$post, $store, ["dni" => "123456789"], "dni", null ],
            "The field dni must be between 7 and 8 digits" => [$put, $update, ["dni" => "123456789"], "dni", "1" ],
            "The field date_of_birth is required" => [$post, $store, ["date_of_birth" => ""], "date_of_birth", null ],
            "The field date_of_birth is required" => [$put, $update, ["date_of_birth" => ""], "date_of_birth", "1" ],
            "The field date_of_birth is not a valid date" => [$post, $store, ["date_of_birth" => Str::random(10)], "date_of_birth", null ],
            "The field date_of_birth is not a valid date" => [$put, $update, ["date_of_birth" => Str::random(10)], "date_of_birth", "1" ],
            "The field date_of_birth does not match the format Y/m/d" => [$post, $store, ["date_of_birth" => "01/01/1990"], "date_of_birth", null ],
            "The field date_of_birth does not match the format Y/m/d" => [$put, $update, ["date_of_birth" => "01/01/1990"], "date_of_birth", "1" ],
            "The field address is required" => [$post, $store, ["address" => ""], "address", null],
            "The field address is required" => [$put, $update, ["address" => ""], "address", "1" ],
            "The field address must be at least 3 characters" => [$post, $store, ["address" => Str::random(2)], "address", null ],
            "The field address must be at least 3 characters" => [$put, $update, ["address" => Str::random(2)], "address", "1" ],
            "The field address must not be greater than 70 characters" => [$post, $store, ["address" => Str::random(71)], "address", null ],
            "The field address must not be greater than 70 characters" => [$put, $update, ["address" => Str::random(71)], "address", "1" ],
            "The field organization_id is required" => [$post, $store, ["organization_id" => ""], "organization_id", null],
            "The field organization_id is required" => [$put, $update, ["organization_id" => ""], "organization_id", "1" ],
            "The field organization_id is invalid" => [$post, $store, ["organization_id" => "2"], "organization_id", null],
            "The field organization_id is invalid" => [$put, $update, ["organization_id" => "2"], "organization_id", "1" ],
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
        $employe = Employe::factory()->for($organization)->create();

        $dataInvalid = [
            "organization_id" => $organization->id
        ];

        $response = $this->postJson( route("employes.store", $organization2), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("organization_id");

        $response = $this->putJson( route("employes.update", [$organization2, $employe]), $dataInvalid );

        $response->assertStatus(422);
        $response->assertJsonValidationErrors("organization_id");
    }

      /**
     * @test
     * @dataProvider invalidRouteForUsersGuest
     */
    public function users_guest_cannot_access_to_routes_protected($route, $method, $param) :void
    {   
        $organization = Organization::factory()->create();
        $response = $this->$method( route($route, ["organization" => $organization, "employe" => $param]) );

        $response->assertStatus(401);
    }
    
    public function invalidRouteForUsersGuest() :array
    {
        return [
            "Route employes.index" => [ "employes.index" , "getJson", null ],
            "Route employes.show" => [ "employes.show" , "getJson", "1" ],
            "Route employes.store" => [ "employes.store" , "postJson", null ],
            "Route employes.update" => [ "employes.update" , "putJson", "1" ],
            "Route employes.destroy" => [ "employes.destroy" , "deleteJson", "1" ],
        ];     
    }

    /**
     * @test
     * @dataProvider dataInvalidForUsersWithRoleCustomer
     */
    public function users_with_role_customer_cannot_perform_to_actions_protected($method, $route, $param, $dataInvalid) :void
    {
        $this->getUserAuthenticated();
        $organization = Organization::factory()
                        ->has( Employe::factory()->count(3) )
                        ->create();

        $response = $this->$method( route($route, ["organization" => $organization, "employe" => $param]), $dataInvalid );

        $response->assertStatus(403);
    }

    public function dataInvalidForUsersWithRoleCustomer() :array
    {
        $dataInvalid = [
            "first_name" => "Fernando Daniel", 
            "last_name" => "Romano", 
            "dni" => "12345678", 
            "date_of_birth" => "1992/01/01", 
            "address" => "Casa 1234", 
            "organization_id" => "1"
        ];
        
        return [
            "User with role customer cannot view all employes in organizations not owner" => [ "getJson", "employes.index", null, $dataInvalid ], 
            "User with role customer cannot view employes details in organizations not owner" => [ "getJson", "employes.show", "1", $dataInvalid ], 
            "User with role customer cannot store employes in organizations not owner" => [ "postJson", "employes.store", null, $dataInvalid ], 
            "User with role customer cannot update employes in organizations not owner" => [ "putJson", "employes.update", "1", $dataInvalid ],
            "User with role customer cannot delete employes in organizations not owner" => [ "putJson", "employes.destroy", "1", $dataInvalid ], 
        ];
    }
}
