<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ImportsCsvDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ComprehensivePatientSeeder extends Seeder
{
    use ImportsCsvDataset;

    /**
     * Run the database seeds.
     * ZERO loops: directly bulk imports pre-generated CSV datasets using MySQL LOAD DATA LOCAL INFILE.
     */
    public function run(): void
    {
        $this->command?->info('Mengimpor 100.000 Pasien Unik Indonesia dari pre-generated dataset CSV...');
        $dir = database_path('seeders/datasets');

        if (! file_exists("{$dir}/patients.csv")) {
            $this->command?->warn("Dataset {$dir}/patients.csv tidak ditemukan. Menghasilkan dataset otomatis...");
            Artisan::call('clinic:generate-dataset-files', [
                '--patients' => 100000,
                '--visits' => 200000,
            ]);
        }

        $this->importCsv('patients', "{$dir}/patients.csv");
        $this->importCsv('patient_allergies', "{$dir}/patient_allergies.csv");
        $this->importCsv('patient_emergency_contacts', "{$dir}/patient_emergency_contacts.csv");

        $count = DB::table('patients')->count();
        $this->command?->info("✓ Sukses mengimpor {$count} Pasien Unik Indonesia dari dataset.");
    }
}
