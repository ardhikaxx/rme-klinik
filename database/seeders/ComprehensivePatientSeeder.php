<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ImportsCsvDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensivePatientSeeder extends Seeder
{
    use ImportsCsvDataset;

    /**
     * Run the database seeds.
     * ZERO loops: directly bulk imports pre-generated physical CSV datasets using MySQL LOAD DATA LOCAL INFILE.
     */
    public function run(): void
    {
        $this->command?->info('Mengimpor 100.000 Pasien Unik Indonesia dari physical dataset CSV...');
        $dir = database_path('seeders/datasets');

        if (! file_exists("{$dir}/patients.csv")) {
            throw new \RuntimeException("File dataset {$dir}/patients.csv tidak ditemukan di direktori proyek.");
        }
        if (! file_exists("{$dir}/patient_allergies.csv")) {
            throw new \RuntimeException("File dataset {$dir}/patient_allergies.csv tidak ditemukan di direktori proyek.");
        }
        if (! file_exists("{$dir}/patient_emergency_contacts.csv")) {
            throw new \RuntimeException("File dataset {$dir}/patient_emergency_contacts.csv tidak ditemukan di direktori proyek.");
        }

        $this->importCsv('patients', "{$dir}/patients.csv");
        $this->importCsv('patient_allergies', "{$dir}/patient_allergies.csv");
        $this->importCsv('patient_emergency_contacts', "{$dir}/patient_emergency_contacts.csv");

        $count = DB::table('patients')->count();
        $this->command?->info("✓ Sukses mengimpor {$count} Pasien Unik Indonesia dari file dataset fisik.");
    }
}
