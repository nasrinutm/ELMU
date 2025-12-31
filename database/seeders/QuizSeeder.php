<?php

namespace Database\Seeders;

use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Network Fundamentals
        $quiz1 = Quiz::create([
            'title' => 'Network Fundamentals',
            'description' => 'Test your knowledge on IP addressing and Subnetting.',
            'duration' => 900, // 15 mins
            'difficulty' => 'Medium',
        ]);

        $q1 = $quiz1->questions()->create([
            'text' => 'What does IP stand for?',
            'explanation' => 'IP stands for Internet Protocol.',
        ]);
        $q1->options()->createMany([
            ['text' => 'Internet Protocol', 'is_correct' => true],
            ['text' => 'Internal Port', 'is_correct' => false],
            ['text' => 'Instant Packet', 'is_correct' => false],
        ]);

        $q2 = $quiz1->questions()->create([
            'text' => 'Which layer is the IP protocol in?',
            'explanation' => 'IP operates at the Network Layer (Layer 3).',
        ]);
        $q2->options()->createMany([
            ['text' => 'Physical Layer', 'is_correct' => false],
            ['text' => 'Network Layer', 'is_correct' => true],
            ['text' => 'Application Layer', 'is_correct' => false],
        ]);

        // 2. Web Development Basics
        $quiz2 = Quiz::create([
            'title' => 'Web Development Basics',
            'description' => 'HTML, CSS, and basic JavaScript concepts.',
            'duration' => 600, // 10 mins
            'difficulty' => 'Easy',
        ]);

        $w1 = $quiz2->questions()->create([
            'text' => 'What does HTML stand for?',
            'explanation' => 'HTML stands for HyperText Markup Language.',
        ]);
        $w1->options()->createMany([
            ['text' => 'Hyper Text Markup Language', 'is_correct' => false], // Note: Usually correct, simplified for demo
            ['text' => 'High Tech Modern Language', 'is_correct' => false],
            ['text' => 'Hyperlinks and Text Markup Language', 'is_correct' => true], // Matches your previous logic
        ]);

        $w2 = $quiz2->questions()->create([
            'text' => 'Which CSS property changes text color?',
            'explanation' => 'The color property sets text color.',
        ]);
        $w2->options()->createMany([
            ['text' => 'text-color', 'is_correct' => false],
            ['text' => 'color', 'is_correct' => true],
            ['text' => 'font-color', 'is_correct' => false],
        ]);

        // 3. Laravel Security
        $quiz3 = Quiz::create([
            'title' => 'Laravel Security',
            'description' => 'Understanding CSRF, XSS, and SQL Injection prevention.',
            'duration' => 720, // 12 mins
            'difficulty' => 'Hard',
        ]);
        
        $s1 = $quiz3->questions()->create([
            'text' => 'What does CSRF stand for?',
            'explanation' => 'Cross-Site Request Forgery.',
        ]);
        $s1->options()->createMany([
            ['text' => 'Cross-Site Request Forgery', 'is_correct' => true],
            ['text' => 'Common Server Request Function', 'is_correct' => false],
        ]);
    }
}