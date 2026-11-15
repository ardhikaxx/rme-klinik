<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IcdCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Memeriksa dan mengimpor kode diagnosa ICD-10 dan ICD-9-CM...');

        // 1. ICD-10 Codes
        if (DB::table('icd10_codes')->count() === 0) {
            $path10 = public_path('assets/icd10.json');
            if (file_exists($path10)) {
                $content = file_get_contents($path10);
                $bom = pack('H*', 'EFBBBF');
                $content = preg_replace("/^$bom/", '', $content);
                $items10 = json_decode($content, true);

                if (is_array($items10)) {
                    $now = now();
                    foreach (array_chunk($items10, 500) as $chunk) {
                        $rows = [];
                        foreach ($chunk as $item) {
                            $code = trim($item['code'] ?? '');
                            $desc = trim($item['description'] ?? '');
                            if ($code === '' || $desc === '') {
                                continue;
                            }
                            $rows[] = [
                                'code' => $code,
                                'description' => $desc,
                                'category' => substr($code, 0, 1),
                                'is_active' => true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                        if (! empty($rows)) {
                            DB::table('icd10_codes')->insertOrIgnore($rows);
                        }
                    }
                    $this->command->info('✓ Master ICD-10 berhasil diimpor.');
                }
            }
        }

        // 2. ICD-9-CM Codes
        if (DB::table('icd9cm_codes')->count() === 0) {
            $path9 = public_path('assets/icd_9cm.json');
            if (file_exists($path9)) {
                $content = file_get_contents($path9);
                $bom = pack('H*', 'EFBBBF');
                $content = preg_replace("/^$bom/", '', $content);
                $items9 = json_decode($content, true);

                if (is_array($items9)) {
                    $now = now();
                    foreach (array_chunk($items9, 500) as $chunk) {
                        $rows = [];
                        foreach ($chunk as $item) {
                            $code = trim($item['code'] ?? '');
                            $desc = trim($item['description'] ?? '');
                            if ($code === '' || $desc === '') {
                                continue;
                            }
                            $category = str_contains($code, '.') ? explode('.', $code)[0] : substr($code, 0, 2);
                            $rows[] = [
                                'code' => $code,
                                'description' => $desc,
                                'category' => $category,
                                'is_active' => true,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                        if (! empty($rows)) {
                            DB::table('icd9cm_codes')->insertOrIgnore($rows);
                        }
                    }
                    $this->command->info('✓ Master ICD-9-CM berhasil diimpor.');
                }
            }
        }
    }
}
