<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class ComprehensiveOperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $targetVisits = 200000, int $chunkSize = 1000): void
    {
        $this->command->info("Menghasilkan {$targetVisits} Kunjungan Operasional & jutaan data klinis terpadu...");

        // Jalankan perintah generator komprehensif untuk memastikan efisiensi memori dan eksekusi batch
        Artisan::call('clinic:seed-comprehensive', [
            '--patients' => 100000,
            '--visits' => $targetVisits,
            '--chunk' => $chunkSize,
        ], $this->command->getOutput());
    }
}
