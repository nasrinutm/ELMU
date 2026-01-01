<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;

class Chapter3QuizSeeder extends Seeder
{
    public function run()
    {
        // QUIZ 1: HTML & CSS
        Quiz::create([
            'title' => 'Bab 3.1: Penskripan Klien (HTML/CSS)',
            'description' => 'Kuiz tentang asas HTML dan penggayaan CSS.',
            'duration' => 600,
            'difficulty' => 'Easy',
            'content' => [ // <--- ALL QUESTIONS IN ONE ARRAY
                [
                    'id' => 1,
                    'question' => 'Antara berikut, yang manakah Bahasa Penskripan Klien?',
                    'explanation' => 'JavaScript dijalankan di pelayar klien.',
                    'options' => [
                        ['text' => 'PHP', 'is_correct' => false],
                        ['text' => 'JavaScript', 'is_correct' => true],
                        ['text' => 'Python', 'is_correct' => false],
                    ]
                ],
                [
                    'id' => 2,
                    'question' => 'Tag HTML untuk membuat pautan?',
                    'explanation' => 'Tag <a> digunakan untuk anchor/link.',
                    'options' => [
                        ['text' => '<a>', 'is_correct' => true],
                        ['text' => '<link>', 'is_correct' => false],
                        ['text' => '<href>', 'is_correct' => false],
                    ]
                ]
            ]
        ]);

        // QUIZ 2: PHP Basics
        Quiz::create([
            'title' => 'Bab 3.2: Penskripan Pelayan (PHP)',
            'description' => 'Kuiz tentang sintaks asas PHP.',
            'duration' => 900,
            'difficulty' => 'Medium',
            'content' => [
                [
                    'id' => 1,
                    'question' => 'Simbol untuk pemboleh ubah PHP?',
                    'explanation' => 'Semua variable PHP bermula dengan $.',
                    'options' => [
                        ['text' => '#', 'is_correct' => false],
                        ['text' => '$', 'is_correct' => true],
                        ['text' => '@', 'is_correct' => false],
                    ]
                ],
                [
                    'id' => 2,
                    'question' => 'Function untuk kira panjang string?',
                    'explanation' => 'strlen() mengira bilangan huruf.',
                    'options' => [
                        ['text' => 'count()', 'is_correct' => false],
                        ['text' => 'length()', 'is_correct' => false],
                        ['text' => 'strlen()', 'is_correct' => true],
                    ]
                ]
            ]
        ]);
    }
}