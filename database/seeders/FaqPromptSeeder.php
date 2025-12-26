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
                'system_prompt' => 'Rujuk muka surat 316 hingga 318 dalam Buku Teks Sains Komputer Tingkatan 4. Senaraikan dan terangkan dengan lengkap 5 prinsip reka bentuk interaktif: Konsistensi, Kebolehan membuat pemerhatian, Boleh dipelajari, Kebolehan untuk menjangka, dan Maklum balas.',
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
