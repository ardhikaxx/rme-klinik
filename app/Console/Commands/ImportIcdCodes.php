<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportIcdCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'icd:import {--type=all : The codes to import (icd10, icd9, or all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ICD-10 and ICD-9-CM codes from public/assets JSON files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type') ?: 'all';

        if (in_array($type, ['all', 'icd10'])) {
            $this->importIcd10();
        }

        if (in_array($type, ['all', 'icd9', 'icd9cm'])) {
            $this->importIcd9cm();
        }

        $this->info('ICD import completed successfully!');

        return Command::SUCCESS;
    }

    protected function importIcd10(): void
    {
        $path = public_path('assets/icd10.json');
        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return;
        }

        $this->info('Reading and parsing ICD-10 data...');
        $content = file_get_contents($path);
        // Strip UTF-8 BOM if present
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        $items = json_decode($content, true);
        if (! is_array($items)) {
            $this->error('Failed to parse ICD-10 JSON: '.json_last_error_msg());

            return;
        }

        $total = count($items);
        $this->info("Found {$total} ICD-10 records. Importing...");

        $now = now();
        $chunks = array_chunk($items, 500);
        $bar = $this->output->createProgressBar(count($chunks));
        $bar->start();

        foreach ($chunks as $chunk) {
            $rows = [];
            foreach ($chunk as $item) {
                $code = trim($item['code'] ?? '');
                $desc = trim($item['description'] ?? '');
                if ($code === '' || $desc === '') {
                    continue;
                }

                // Extract category (e.g. A00-B99 chapter or letter prefix)
                $category = substr($code, 0, 1);

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
                DB::table('icd10_codes')->upsert(
                    $rows,
                    ['code'],
                    ['description', 'category', 'is_active', 'updated_at']
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('ICD-10 import finished. Total verified in DB: '.DB::table('icd10_codes')->count());
    }

    protected function importIcd9cm(): void
    {
        $path = public_path('assets/icd_9cm.json');
        if (! file_exists($path)) {
            $this->error("File not found: {$path}");

            return;
        }

        $this->info('Reading and parsing ICD-9-CM data...');
        $content = file_get_contents($path);
        $bom = pack('H*', 'EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        $items = json_decode($content, true);
        if (! is_array($items)) {
            $this->error('Failed to parse ICD-9-CM JSON: '.json_last_error_msg());

            return;
        }

        $total = count($items);
        $this->info("Found {$total} ICD-9-CM records. Importing...");

        $now = now();
        $chunks = array_chunk($items, 500);
        $bar = $this->output->createProgressBar(count($chunks));
        $bar->start();

        foreach ($chunks as $chunk) {
            $rows = [];
            foreach ($chunk as $item) {
                $code = trim($item['code'] ?? '');
                $desc = trim($item['description'] ?? '');
                if ($code === '' || $desc === '') {
                    continue;
                }

                $category = substr($code, 0, 2);

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
                DB::table('icd9cm_codes')->upsert(
                    $rows,
                    ['code'],
                    ['description', 'category', 'is_active', 'updated_at']
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('ICD-9-CM import finished. Total verified in DB: '.DB::table('icd9cm_codes')->count());
    }
}
