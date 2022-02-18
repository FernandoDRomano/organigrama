<?php

namespace Tests\Feature\Controllers\V1;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Organization;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function getUserAuthenticated() :User
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * @test
     */
    public function users_can_see_their_organizations() :void
    {
        $user = $this->getUserAuthenticated();
        $organizations = Organization::factory(2)->for($user)->create();
        $organizations = $organizations->loadCount(['departments', 'jobs', 'employes']);

        $response = $this->getJson( route('organizations.index') );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $organizations[1]->id,
                    "name" => $organizations[1]->name,
                    "counts" => [
                        "departments" => $organizations[1]->departments_count,
                        "jobs" => $organizations[1]->jobs_count,
                        "employes" => $organizations[1]->employes_count
                    ]
                ],
                [
                    "id" => $organizations[0]->id,
                    "name" => $organizations[0]->name,
                    "counts" => [
                        "departments" => $organizations[0]->departments_count,
                        "jobs" => $organizations[0]->jobs_count,
                        "employes" => $organizations[0]->employes_count
                    ]
                ],
            ],
            "relationships" => [
                "user" => [
                    "id" => $user->id,
                    "name" => $user->name,
                    "email" => $user->email,
                    "status" => $user->status,
                    "role" => $user->role
                ]
            ],
            "links" => [],
            "meta" => [],
            "message" => "Organizations all!!"
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_create_organizations() :void
    {
        $user = $this->getUserAuthenticated();
        $data = [
            "name" => "Organization New"
        ];

        $response = $this->postJson( route('organizations.store'), $data );

        $response->assertStatus(201);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "name",
                "counts" => [
                    "departments",
                    "jobs",
                    "employes"
                ]
            ],
            "message",
        ]);
        $this->assertDatabaseHas('organizations', [
            "name" => $data["name"],
            "user_id" => $user->id
        ]);
        $this->assertDatabaseCount("organizations", 1);
    }

    /**
     * @test
     * @dataProvider invalidDataForCreateOrganization
     */
    public function authenticated_users_cannot_create_organizations_with_invalid_data($invalidData, $invalidField) :void
    {
        $this->getUserAuthenticated();
        $response = $this->postJson( route('organizations.store') , $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($invalidField);
    }

    public function invalidDataForCreateOrganization() :array{
        return [
            "The field name is required" => [ [ "name" => "" ], "name" ],
            "The field name must be at least 3 characters" => [ ["name" => Str::random(2)], "name" ],
            "The field name must not be greater than 25 characters" => [ ["name" => Str::random(26)], "name" ]
        ];
    }

    /**
     * @test
     */
    public function authenticated_users_can_see_organization_detail() :void
    {
        $user = $this->getUserAuthenticated();
        $organizations = Organization::factory(2)->for($user)->create();

        $response = $this->getJson( route("organizations.show", $organizations[1]) );
        $organizations[1]->loadCount(['departments', 'jobs', 'employes']);

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                "id" => $organizations[1]->id,
                "name" => $organizations[1]->name,
                "counts" => [
                    "departments" => $organizations[1]->departments_count,
                    "jobs" =>  $organizations[1]->jobs_count,
                    "employes" =>  $organizations[1]->employes_count
                ]
            ]
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_update_their_organizations() :void
    {
        $user = $this->getUserAuthenticated();
        $organization = Organization::factory()->for($user)->create();
        $data = [
            "name" => "Organization Rename"
        ];

        $response = $this->putJson( route('organizations.update', $organization), $data );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "name",
                "counts" => [
                    "departments",
                    "jobs",
                    "employes"
                ]
            ],
            "message",
        ]);
        $this->assertDatabaseHas("organizations", [
            "name" => $data["name"],
            "user_id" => $user->id
        ]);
        $this->assertDatabaseMissing("organizations", [
            "name" => $organization->name,
            "user_id" => $user->id
        ]);
    }

    /**
     * @test
     */
    public function authenticated_users_can_delete_their_organizations() :void
    {
        $user = $this->getUserAuthenticated();
        $organizations = Organization::factory(3)->for($user)->create();

        $response = $this->deleteJson( route("organizations.destroy", $organizations[1]) );

        $response->assertStatus(204);
        $this->assertDatabaseCount("organizations", 2);
        $this->assertDatabaseMissing("organizations", [
            "id" => $organizations[1]->id,
            "name" => $organizations[1]->name,
            "user_id" => $organizations[1]->user_id
        ]);
    }

    /**
     * @test
     * @dataProvider authenticatedUsersCannotUpdateDeleteOrShowOthersOrganizationsNotOwner
     */
    public function authenticated_users_cannot_perform_actions_in_others_organizations_not_owner($route, $method, $param) :void
    {   
        $user = $this->getUserAuthenticated();
        Organization::factory()->create();
        $data = ["name" => "Organization XXX"];

        $response = $this->$method( route($route, $param), $data );

        $response->assertStatus(403);
    }

    public function authenticatedUsersCannotUpdateDeleteOrShowOthersOrganizationsNotOwner() :array
    {
        return [
            "Users cannot update organizations not owner" => [ "organizations.update", "putJson", "1" ],
            "Users cannot delete organizations not owner" => [ "organizations.destroy", "deleteJson", "1" ],
            "Users cannot show organizations not owner" => [ "organizations.show", "getJson", "1" ]
        ];
    }

    /**
     * @test
     * @dataProvider invalidRoutesForUserGuest
     */
    public function users_guest_cannot_access_to_routes_protected($route, $method, $param) :void
    {
        $response = $this->$method( route($route, $param) );

        $response->assertStatus(401);
    }

    public function invalidRoutesForUserGuest() :array
    {
        return [
            "Route organizations.index" => [ "organizations.index" , "getJson", null],
            "Route organizations.show" => [ "organizations.show" , "getJson", "1"],
            "Route organizations.store" => [ "organizations.store" , "postJson", null],
            "Route organizations.update" => [ "organizations.update" , "putJson", "1"],
            "Route organizations.destroy" => [ "organizations.destroy" , "deleteJson", "1"],
        ];
    }

}
