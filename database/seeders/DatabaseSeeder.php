<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $manageActivities = Permission::firstOrCreate(['name' => 'manage activities']);

        // 2. Ensure the Role exists
        $teacherRole = Role::firstOrCreate(['name' => 'Teacher']);

        // 3. Assign the Permission to the Role
        $teacherRole->givePermissionTo($manageActivities);

        // Optional: Assign the permission to the 'admin' role too if needed
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->givePermissionTo($manageActivities);

        $this->call([
            FaqPromptSeeder::class,
        ]);
    }
}
