<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqPromptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'label' => '5 prinsip reka bentuk interaktif?',
                'system_prompt' => 'Rujuk muka surat 316-318 (Bab 3: Interaksi Manusia dan Komputer). 
                Sila senaraikan KESEMUA LIMA prinsip reka bentuk interaktif berikut secara lengkap:
                1. Konsistensi (rujuk contoh "butang Close pada Microsoft Windows")
                2. Kebolehan membuat pemerhatian (perceivability)
                3. Boleh dipelajari (learnability - rujuk contoh aplikasi "WhatsApp")
                4. Kebolehan untuk menjangka (predictability - rujuk "ikon emosi")
                5. Maklum balas (feedback - rujuk contoh "loading")
                Pastikan setiap prinsip disertakan penjelasan ringkas dari teks.',
                 'sort_order' => 1
            ],
            [
                'label' => 'Sebab HCI diperlukan?',
                'system_prompt' => 'Berdasarkan subtopik 3.1.1 pada muka surat 319, jelaskan enam sebab mengapa interaksi antara manusia dengan komputer (HCI) diperlukan dalam pembangunan perisian.',
                'sort_order' => 2
            ],
            [
                'label' => 'Proses reka bentuk interaksi?',
                'system_prompt' => 'Rujuk Rajah 3.3 pada muka surat 326. Huraikan empat aktiviti asas dalam proses reka bentuk interaksi iaitu mengenal pasti keperluan, membangunkan reka bentuk alternatif, membina prototaip, dan membuat penilaian.',
                'sort_order' => 3
            ],
            [
                'label' => 'Apa itu prototaip?',
                'system_prompt' => 'Berdasarkan muka surat 335, berikan definisi prototaip dan jelaskan empat tujuan utama mengapa prototaip dihasilkan sebelum membina produk sebenar.',
                'sort_order' => 4
            ]
        ];

        foreach ($data as $item) {
            \App\Models\FaqPrompt::create($item);
        }
    }
}
