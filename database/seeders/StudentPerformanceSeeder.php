<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Activity;
use App\Models\Quiz;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class StudentPerformanceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a fresh student since the old ones were deleted
        $student = User::firstOrCreate(
            ['email' => 'wanmuhammadhafiz@graduate.utm.my'],
            [
                'name' => 'Wan Muhammad Hafiz',
                'password' => Hash::make('password123'),
                'username' => 'wanhafiz'
            ]
        );
        
        // Ensure they have the student role (requires spatie/laravel-permission)
        // If you don't use roles yet, you can skip this line
        // $student->assignRole('student');

        // 2. Create sample Activities
        $activity1 = Activity::firstOrCreate(['title' => 'Introduction to Laravel']);
        $activity2 = Activity::firstOrCreate(['title' => 'Database Basics']);

        // 3. Attach to student
        $student->activities()->syncWithoutDetaching([
            $activity1->id => ['status' => 'completed', 'completed_at' => Carbon::now()->subDays(2)],
            $activity2->id => ['status' => 'completed', 'completed_at' => Carbon::now()->subDays(1)],
        ]);

        // 4. Create sample Quiz
        $quiz1 = Quiz::firstOrCreate(['title' => 'PHP Fundamentals Quiz']);
        
        $student->quizzes()->syncWithoutDetaching([
            $quiz1->id => [
                'score' => 85, 
                'status' => 'completed', 
                'completed_at' => Carbon::now()
            ],
        ]);
    }
}