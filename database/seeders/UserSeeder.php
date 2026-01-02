<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Roles (to make sure they exist)
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleTeacher = Role::firstOrCreate(['name' => 'teacher']);
        $roleStudent = Role::firstOrCreate(['name' => 'student']);

        // 2. Create Admin Account
        $admin = User::firstOrCreate(
            ['email' => 'mohamadnasrin@graduate.utm.my'], 
            [
                'name' => 'Admin Nasrin',
                'password' => Hash::make('11111111'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($roleAdmin);

        // 3. Create Teacher Account
        $teacher = User::firstOrCreate(
            ['email' => 'nasrin98jb@gmail.com'], 
            [
                'name' => 'Cikgu Nasrin',
                'password' => Hash::make('11111111'),
                'email_verified_at' => now(),
            ]
        );
        $teacher->assignRole($roleTeacher);

        // 4. Create Student Account
        $student = User::firstOrCreate(
            ['email' => 'wanmuhammadhafiz@graduate.utm.my'], 
            [
                'name' => 'Wan Hafiz',
                'password' => Hash::make('11111111'),
                'email_verified_at' => now(),
            ]
        );
        $student->assignRole($roleStudent);
    }
}