<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ImportsCsvDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IcdCodesSeeder extends Seeder
{
    use ImportsCsvDataset;

    /**
     * Run the database seeds.
     * ZERO loops: directly bulk imports pre-generated physical CSV datasets using MySQL LOAD DATA LOCAL INFILE.
     */
    public function run(): void
    {
        $this->command?->info('Mengimpor master katalog kode ICD-10 dan ICD-9-CM dari file dataset CSV fisik...');
        $dir = database_path('seeders/datasets');

        if (! file_exists("{$dir}/icd10_codes.csv")) {
            throw new \RuntimeException("File dataset {$dir}/icd10_codes.csv tidak ditemukan.");
        }
        if (! file_exists("{$dir}/icd9cm_codes.csv")) {
            throw new \RuntimeException("File dataset {$dir}/icd9cm_codes.csv tidak ditemukan.");
        }

        if (DB::table('icd10_codes')->count() === 0) {
            $this->importCsv('icd10_codes', "{$dir}/icd10_codes.csv");
            $this->command?->info('✓ Master ICD-10 berhasil diimpor.');
        }

        if (DB::table('icd9cm_codes')->count() === 0) {
            $this->importCsv('icd9cm_codes', "{$dir}/icd9cm_codes.csv");
            $this->command?->info('✓ Master ICD-9-CM berhasil diimpor.');
        }
    }
}
