<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::withoutForeignKeyConstraints(function () {
            Role::truncate();
        });

        Role::factory()->patient()->create();

        Role::factory()->doctor()->create();

        Role::factory()->admin()->create();

        Role::factory()->superAdmin()->create();
    }
}
