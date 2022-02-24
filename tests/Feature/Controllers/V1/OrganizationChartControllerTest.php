<?php

namespace Tests\Feature\Controllers\V1;

use App\Models\Department;
use App\Models\DepartmentLevel;
use App\Models\Organization;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrganizationChartControllerTest extends TestCase
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
    public function authenticated_users_can_view_organization_chart() :void
    {
        $user = $this->getUserAuthenticated();
        DepartmentLevel::factory(3)
            ->state(new Sequence(
                ["hierarchy" => "1"],
                ["hierarchy" => "2"],
                ["hierarchy" => "3"]
            ))
            ->create();

        $organization = Organization::factory()
                            ->for($user)
                            ->has( 
                                Department::factory()
                                        ->state(new Sequence(
                                            ["department_level_id" => "1", "department_id" => null],
                                            ["department_level_id" => "2", "department_id" => "1"],
                                            ["department_level_id" => "2", "department_id" => "1"],
                                            ["department_level_id" => "2", "department_id" => "1"],
                                            ["department_level_id" => "3", "department_id" => "2"],
                                            ["department_level_id" => "3", "department_id" => "3"],
                                            ["department_level_id" => "3", "department_id" => "4"],
                                        ))
                                        ->count(7) 
                            )
                            ->create();

        $departments = $organization->departments;
        
        $response = $this->getJson( route("organization-chart.view", $organization) );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "data" => [
                [
                    "id",
                    "label",
                    "expand",
                    "department_parent",
                    "children" => [
                        "*" => [
                            "id",
                            "label",
                            "expand",
                            "department_parent",
                            "children" => []
                        ]
                    ]
                ]
            ],
            "message"
        ]);

        $response->assertJson([
            "data" => [
                [
                    "id" => $departments[0]->id,
                    "label" => $departments[0]->name,
                    "expand" => true,
                    "department_parent" => $departments[0]->department_id,
                    "children" => [
                        [
                            "id" => $departments[1]->id,
                            "label" => $departments[1]->name,
                            "expand" => true,
                            "department_parent" => $departments[1]->department_id,
                            "children" => [
                                [
                                    "id" => $departments[4]->id,
                                    "label" => $departments[4]->name,
                                    "expand" => true,
                                    "department_parent" => $departments[4]->department_id,
                                ]
                            ]
                        ],
                        [
                            "id" => $departments[2]->id,
                            "label" => $departments[2]->name,
                            "expand" => true,
                            "department_parent" => $departments[2]->department_id,
                            "children" => [
                                [
                                    "id" => $departments[5]->id,
                                    "label" => $departments[5]->name,
                                    "expand" => true,
                                    "department_parent" => $departments[5]->department_id,
                                ]
                            ]
                        ],
                        [
                            "id" => $departments[3]->id,
                            "label" => $departments[3]->name,
                            "expand" => true,
                            "department_parent" => $departments[3]->department_id,
                            "children" => [
                                [
                                    "id" => $departments[6]->id,
                                    "label" => $departments[6]->name,
                                    "expand" => true,
                                    "department_parent" => $departments[6]->department_id,
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            "message" => "Organization chart created!!"
        ]);

    }

    /**
     * @test
     */
    public function authenticated_users_cannot_view_organization_chart_not_owner() :void
    {
        $this->getUserAuthenticated();
        DepartmentLevel::factory(3)
            ->state(new Sequence(
                ["hierarchy" => "1"],
                ["hierarchy" => "2"],
                ["hierarchy" => "3"]
            ))
            ->create();

        $organization = Organization::factory()
                            ->has( 
                                Department::factory()
                                        ->state(new Sequence(
                                            ["department_level_id" => "1", "department_id" => null],
                                            ["department_level_id" => "2", "department_id" => "1"],
                                            ["department_level_id" => "2", "department_id" => "1"],
                                            ["department_level_id" => "2", "department_id" => "1"],
                                            ["department_level_id" => "3", "department_id" => "2"],
                                            ["department_level_id" => "3", "department_id" => "3"],
                                            ["department_level_id" => "3", "department_id" => "4"],
                                        ))
                                        ->count(7) 
                            )
                            ->create();
        
        $response = $this->getJson( route("organization-chart.view", $organization) );

        $response->assertStatus(403);
    }
}
