<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Option;

class Chapter3QuizSeeder extends Seeder
{
    public function run()
    {
        // ==========================================
        // KUIZ 1: Asas Penskripan Klien (HTML & CSS)
        // ==========================================
        $quiz1 = Quiz::create([
            'title' => 'Bab 3.1: Asas Penskripan Klien',
            'description' => 'Uji kefahaman anda tentang HTML, CSS, dan perbezaan antara penskripan klien dan pelayan.',
            'duration' => 600, // 10 minit
            'max_attempts' => 3,
            'difficulty' => 'Easy',
        ]);

        $questions1 = [
            [
                'text' => 'Antara berikut, yang manakah merupakan Bahasa Penskripan Klien?',
                'explanation' => 'JavaScript adalah bahasa penskripan yang dijalankan di pelayar web pengguna (klien), manakala PHP dan Python dijalankan di pelayan.',
                'options' => [
                    ['text' => 'PHP', 'is_correct' => false],
                    ['text' => 'JavaScript', 'is_correct' => true],
                    ['text' => 'Python', 'is_correct' => false],
                    ['text' => 'ASP.NET', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Apakah fungsi utama Cascading Style Sheets (CSS) dalam pembangunan web?',
                'explanation' => 'HTML digunakan untuk struktur, manakala CSS digunakan untuk menggayakan (style) dan menyusun atur paparan laman web.',
                'options' => [
                    ['text' => 'Membangunkan logik pangkalan data', 'is_correct' => false],
                    ['text' => 'Menentukan struktur kandungan laman web', 'is_correct' => false],
                    ['text' => 'Menetapkan gaya, warna, dan susun atur laman web', 'is_correct' => true],
                    ['text' => 'Menghantar data ke pelayan', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Tag HTML manakah yang digunakan untuk membuat pautan (hyperlink)?',
                'explanation' => 'Tag <a> (anchor) digunakan untuk membuat pautan. Contoh: <a href="page.html">Link</a>.',
                'options' => [
                    ['text' => '<link>', 'is_correct' => false],
                    ['text' => '<a>', 'is_correct' => true],
                    ['text' => '<p>', 'is_correct' => false],
                    ['text' => '<href>', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Dalam CSS, simbol manakah digunakan untuk memilih elemen berdasarkan ID?',
                'explanation' => 'Simbol # digunakan untuk ID (contoh: #header), manakala . digunakan untuk Class (contoh: .menu).',
                'options' => [
                    ['text' => '.', 'is_correct' => false],
                    ['text' => '#', 'is_correct' => true],
                    ['text' => '$', 'is_correct' => false],
                    ['text' => '@', 'is_correct' => false],
                ]
            ]
        ];

        $this->createQuestions($quiz1, $questions1);

        // ==========================================
        // KUIZ 2: Pengenalan PHP (Penskripan Pelayan)
        // ==========================================
        $quiz2 = Quiz::create([
            'title' => 'Bab 3.2: Asas Bahasa PHP',
            'description' => 'Soalan berkaitan sintaks asas PHP, pemboleh ubah, dan struktur kawalan.',
            'duration' => 900, // 15 minit
            'max_attempts' => 3,
            'difficulty' => 'Medium',
        ]);

        $questions2 = [
            [
                'text' => 'Bagaimanakah cara yang betul untuk mengisytiharkan pemboleh ubah dalam PHP?',
                'explanation' => 'Dalam PHP, semua pemboleh ubah mesti bermula dengan simbol $ (contoh: $nama).',
                'options' => [
                    ['text' => 'var nama = "Ali";', 'is_correct' => false],
                    ['text' => '$nama = "Ali";', 'is_correct' => true],
                    ['text' => 'nama = "Ali";', 'is_correct' => false],
                    ['text' => 'int nama = "Ali";', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Simbol manakah yang digunakan untuk menulis komen satu baris dalam PHP?',
                'explanation' => '// digunakan untuk komen satu baris. /* ... */ digunakan untuk komen banyak baris.',
                'options' => [
                    ['text' => '#', 'is_correct' => false],
                    ['text' => '//', 'is_correct' => true],
                    ['text' => '', 'is_correct' => false],
                    ['text' => '**', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Apakah output bagi kod berikut: echo 5 . 5;',
                'explanation' => 'Simbol titik (.) dalam PHP digunakan untuk concatenation (penyambungan string), bukan tambah. Jadi "5" disambung dengan "5" menjadi "55".',
                'options' => [
                    ['text' => '10', 'is_correct' => false],
                    ['text' => '55', 'is_correct' => true],
                    ['text' => '5.5', 'is_correct' => false],
                    ['text' => 'Ralat', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Fungsi manakah digunakan untuk mengira panjang rentetan (string) dalam PHP?',
                'explanation' => 'strlen() adalah fungsi standard PHP untuk mengira bilangan aksara dalam string.',
                'options' => [
                    ['text' => 'strlength()', 'is_correct' => false],
                    ['text' => 'count()', 'is_correct' => false],
                    ['text' => 'strlen()', 'is_correct' => true],
                    ['text' => 'length()', 'is_correct' => false],
                ]
            ]
        ];

        $this->createQuestions($quiz2, $questions2);

        // ==========================================
        // KUIZ 3: Pangkalan Data & Interaksi PHP
        // ==========================================
        $quiz3 = Quiz::create([
            'title' => 'Bab 3.3: PHP & MySQL',
            'description' => 'Uji pengetahuan tentang SQL dan interaksi antara PHP dengan pangkalan data.',
            'duration' => 1200, // 20 minit
            'max_attempts' => 3,
            'difficulty' => 'Hard',
        ]);

        $questions3 = [
            [
                'text' => 'Apakah pernyataan SQL yang digunakan untuk mengambil data daripada pangkalan data?',
                'explanation' => 'SELECT digunakan untuk memilih data. INSERT untuk masuk data, UPDATE untuk kemaskini, DELETE untuk hapus.',
                'options' => [
                    ['text' => 'GET', 'is_correct' => false],
                    ['text' => 'EXTRACT', 'is_correct' => false],
                    ['text' => 'OPEN', 'is_correct' => false],
                    ['text' => 'SELECT', 'is_correct' => true],
                ]
            ],
            [
                'text' => 'Dalam jadual pangkalan data, apakah fungsi "Kunci Primar" (Primary Key)?',
                'explanation' => 'Kunci Primar adalah nilai unik yang membezakan setiap rekod (baris) dalam jadual untuk mengelakkan pertindihan data.',
                'options' => [
                    ['text' => 'Mengunci pangkalan data daripada diakses', 'is_correct' => false],
                    ['text' => 'Mengenal pasti setiap rekod secara unik', 'is_correct' => true],
                    ['text' => 'Menyambungkan dua jadual berbeza', 'is_correct' => false],
                    ['text' => 'Menyimpan kata laluan pengguna', 'is_correct' => false],
                ]
            ],
            [
                'text' => 'Fungsi PHP manakah yang digunakan untuk menyambung ke pangkalan data MySQL?',
                'explanation' => 'mysqli_connect() digunakan untuk membuka sambungan baru ke pelayan MySQL.',
                'options' => [
                    ['text' => 'mysql_link()', 'is_correct' => false],
                    ['text' => 'db_connect()', 'is_correct' => false],
                    ['text' => 'mysqli_connect()', 'is_correct' => true],
                    ['text' => 'connect_db()', 'is_correct' => false],
                ]
            ],
        ];

        $this->createQuestions($quiz3, $questions3);
    }

    // Helper function to keep code clean
    private function createQuestions($quiz, $questionsData)
    {
        foreach ($questionsData as $qData) {
            $question = $quiz->questions()->create([
                'text' => $qData['text'],
                'explanation' => $qData['explanation'],
            ]);

            foreach ($qData['options'] as $optData) {
                $question->options()->create([
                    'text' => $optData['text'],
                    'is_correct' => $optData['is_correct']
                ]);
            }
        }
    }
}