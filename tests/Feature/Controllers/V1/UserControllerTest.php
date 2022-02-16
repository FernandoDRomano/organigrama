<?php

namespace Tests\Feature\Controllers\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Organization;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function getUserWithRoleAdmin() :User
    {
        return User::factory()->create(["role" => User::ROLE_ADMIN]);
    }

    public function sanctumActingAs() :void
    {
        Sanctum::actingAs( $this->getUserWithRoleAdmin() );
    }

    /**
     * @test
     */
    public function users_can_see_their_profile() :void
    {
        $user = User::factory()->create();
        $user->refresh()->loadCount('organizations');
        Sanctum::actingAs($user);

        $response = $this->getJson( route('users.profile') );
    
        $response->assertStatus(200);
        $response->assertExactJson([
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "status" => $user->status,
                "role" => $user->role,
                "counts" => [
                    "organizations" => $user->organizations_count,
                ]
            ],
            "message" => "User profile"
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_update_status_of_any_user() :void
    {
        $this->sanctumActingAs();

        $anyUser = User::factory()->create(); //default status 'active'

        $response = $this->putJson( route('users.status', $anyUser->id) );

        $response->assertStatus(200);
        $this->assertEquals($anyUser->refresh()->status, User::STATUS_BLOCKED);

        $response = $this->putJson( route('users.status', $anyUser->id) );

        $response->assertStatus(200);
        $this->assertEquals($anyUser->refresh()->status, User::STATUS_ACTIVE);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_see_all_users_in_order_desc() :void
    {
        $this->sanctumActingAs();

        $users = User::factory(3)->has( Organization::factory()->count(2) )->create();
        $users = $users->loadCount('organizations');

        $response = $this->getJson( route('users.index') );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                [
                    "id" => $users[2]->id,
                    "name" => $users[2]->name,
                    "email" => $users[2]->email,
                    "status" => $users[2]->status,
                    "role" => $users[2]->role,
                    "counts" => [
                        "organizations" => $users[2]->organizations_count,
                    ]
                ],
                [
                    "id" => $users[1]->id,
                    "name" => $users[1]->name,
                    "email" => $users[1]->email,
                    "status" => $users[1]->status,
                    "role" => $users[1]->role,
                    "counts" => [
                        "organizations" => $users[1]->organizations_count,
                    ]
                ],
                [
                    "id" => $users[0]->id,
                    "name" => $users[0]->name,
                    "email" => $users[0]->email,
                    "status" => $users[0]->status,
                    "role" => $users[0]->role,
                    "counts" => [
                        "organizations" => $users[0]->organizations_count,
                    ]
                ],
            ],
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_see_any_user() :void
    {
        $this->sanctumActingAs();

        $users = User::factory(3)->create();

        $response = $this->getJson( route('users.show', $users[0]->id) );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                "id" => $users[0]->id,
                "name" => $users[0]->name,
                "email" => $users[0]->email,
                "status" => $users[0]->status,
                "role" => $users[0]->role,
            ],
            "message" => "User"
        ]);

        $response = $this->getJson( route('users.show', $users[2]->id) );

        $response->assertStatus(200);
        $response->assertJson([
            "data" => [
                "id" => $users[2]->id,
                "name" => $users[2]->name,
                "email" => $users[2]->email,
                "status" => $users[2]->status,
                "role" => $users[2]->role,
            ],
            "message" => "User"
        ]);
    }

    /**
     * @test
     */
    public function users_with_role_admin_can_delete_any_user_with_role_customer() :void
    {
        $this->sanctumActingAs();
        $users = User::factory(3)->create();
        //There are four users with user role admin

        $response = $this->deleteJson( route('users.destroy', $users[1]->id) );

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ["email" => $users[1]->email]);
        $this->assertDatabaseCount('users', 3);

        $response = $this->deleteJson( route('users.destroy', $users[2]->id) );

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ["email" => $users[2]->email]);
        $this->assertDatabaseCount('users', 2);
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
            "Route users.profile" => [ "users.profile" , "getJson", null],
            "Route users.status" => [ "users.status" , "putJson", "1"],
            "Route users.index" => [ "users.index" , "getJson", null],
            "Route users.show" => [ "users.show" , "getJson", "1"],
            "Route users.destroy" => [ "users.destroy" , "deleteJson", "1"],
        ];
    }

    /**
     * @test
     * @dataProvider invalidRoutesForUserWithRoleCustomer
     */
    public function users_with_role_customer_cannot_access_to_routes_protected($route, $method, $param) :void
    {
        Sanctum::actingAs( User::factory()->create() );
        
        $response = $this->$method( route($route, $param) );

        $response->assertStatus(403);
    }

    public function invalidRoutesForUserWithRoleCustomer() :array
    {
        return [
            "Route users.status" => [ "users.status" , "putJson", "1"],
            "Route users.index" => [ "users.index" , "getJson", null],
            "Route users.show" => [ "users.show" , "getJson", "1"],
            "Route users.destroy" => [ "users.destroy" , "deleteJson", "1"],
        ];
    }
}
