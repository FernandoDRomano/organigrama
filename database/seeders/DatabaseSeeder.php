<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\JobLevel;
use App\Models\Department;
use App\Models\Organization;
use App\Models\DepartmentLevel;
use App\Models\Employe;
use Illuminate\Database\Seeder;
use JetBrains\PhpStorm\Deprecated;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i <= 5; $i++) { 
            DepartmentLevel::create([
                "name" => "{$i}-Nivel",
                "hierarchy" => $i
            ]);

            JobLevel::create([
                "name" => "{$i}-Nivel",
                "hierarchy" => $i
            ]);
        }

        User::factory(3)
            ->has(
                Organization::factory()
                ->has(Employe::factory()->count(10))
                ->count(1))
            ->create();
        
        for ($i=1; $i <= 10 ; $i++) { 
            
            $j = null;
            $id = null;

            if ($i == 1) {
                $id = null;
                $j = 1;
            }else if ($i <= 4) {
                $id = 1;
                $j = 2;
            }else if ($i <= 6) {
                $id = 2;
                $j = 3;
            }else if ($i <= 8) {
                $id = 3;
                $j = 3;
            }else if ($i <= 10) {
                $id = 4;
                $j = 3;
            }

            Department::create([
                "name" => "Deparment {$i}",
                "organization_id" => 1,
                "department_level_id" => $j,
                "department_id" => $id
            ]);
        }
        

    }
}
