<?php

namespace Tests\Feature\Controllers\V1;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthUserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function users_can_register() :void
    {
        $data = [
            "name" => "Fernando Daniel Romano",
            "email" => "admin@gmail.com",
            "password" => "password",
            "password_confirmation" => "password"
        ];
        $response = $this->postJson( route('register') , $data);

        $response->assertStatus(201);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            "name" => "Fernando Daniel Romano",
            "email" => "admin@gmail.com",
            "role" => "customer",
            "status" => "active"
        ]);
    }

    /**
     * @test
     * @dataProvider dataInvalidForRegister
     */
    public function users_cannot_register_with_invalid_data($invalidData, $invalidField) :void
    {
        User::factory()->create(["email" => "admin@gmail.com"]);
        $response = $this->postJson( route('register') , $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($invalidField);
    }

    public function dataInvalidForRegister() :array
    {
        return [
            "The field email is required" => [ ["email" => ""], "email" ],
            "The field email must be a valid email" => [ ["email" => "this-is-a-email"], "email" ],
            "The field email must be a string" => [ ["email" => 12312312], "email" ],
            "The field email has already been taken" => [ ["email" => "admin@gmail.com"], "email" ],
            "The field password is required" => [ ["password" => ""], "password" ],
            "The field password must be at least 6 characters" => [ ["password" => Str::random(5)], "password" ],
            "The field password must not be greater than 16 characters" => [ ["password" => Str::random(17)], "password" ],
            "The field password confirmation does not match" => [ ["password" => "12345678", "password_confirmation" => "1234567890"], "password" ]
        ];
    }

    /**
     * @test
     */
    public function registered_users_can_do_login() :void
    {
        $user = User::factory()->create();

        $response = $this->postJson( route('login'),  ["email" => $user->email, "password" => "password"]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                "id",
                "name",
                "email",
                "status",
                "role",
            ],
            "access_token"
        ]);
    }

    /**
     * @test
     * @dataProvider dataInvalidForLogin
     */
    public function users_cannot_do_login_with_status_blocked() :void
    {
        $user = User::factory()->create(["status" => "blocked"]);

        $response = $this->postJson( route('login'),  ["email" => $user->email, "password" => "password"]);

        $response->assertStatus(401);
    }

    /**
     * @test
     * @dataProvider dataInvalidForLogin
     */
    public function users_cannot_do_login_with_data_invalid($invalidData, $invalidField) :void
    {
        $response = $this->postJson( route('login') , $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors($invalidField);
    }

    public function dataInvalidForLogin() :array
    {
        return [
            "The field email is required" => [ ["email" => ""], "email" ],
            "The field email must be a valid email" => [ ["email" => "this-is-a-email"], "email" ],
            "The field email must be a string" => [ ["email" => 12312312], "email" ],
            "The field password is required" => [ ["password" => ""], "password" ],
        ];
    }
    

    /**
     * @test
     */
    public function users_logged_in_can_do_logout() :void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $response = $this->postJson( route('logout') );

        $response->assertStatus(200);      
        $response->assertJsonStructure([
            "message"
        ]);
    }

    /**
     * @test
     */
    public function users_guest_can_not_do_logout() :void
    {
        $response = $this->postJson( route('logout') );

        $response->assertStatus(401);
    }
}
