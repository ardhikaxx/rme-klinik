<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ImportsCsvDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ComprehensiveOperationSeeder extends Seeder
{
    use ImportsCsvDataset;

    /**
     * Run the database seeds.
     * ZERO loops: directly bulk imports pre-generated CSV datasets using MySQL LOAD DATA LOCAL INFILE.
     */
    public function run(): void
    {
        $this->command?->info('Mengimpor 200.000 Kunjungan & jutaan data operasional dari pre-generated dataset CSV...');
        $dir = database_path('seeders/datasets');

        if (! file_exists("{$dir}/visits.csv")) {
            $this->command?->warn("Dataset {$dir}/visits.csv tidak ditemukan. Menghasilkan dataset otomatis...");
            Artisan::call('clinic:generate-dataset-files', [
                '--patients' => 100000,
                '--visits' => 200000,
            ]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clean tables and reset foreign keys for safe bulk import
        DB::table('medicine_stock_movements')->truncate();
        DB::table('medicine_receipt_items')->truncate();
        DB::table('medicine_receipts')->truncate();
        DB::table('medicine_batches')->truncate();

        // 1. Master Obat & Penerimaan Stok Farmasi
        $this->importCsv('medicines', "{$dir}/medicines.csv", null, true);
        $this->importCsv('medicine_batches', "{$dir}/medicine_batches.csv");
        $this->importCsv('medicine_receipts', "{$dir}/medicine_receipts.csv");
        $this->importCsv('medicine_receipt_items', "{$dir}/medicine_receipt_items.csv");

        // 2. Kunjungan & Riwayat Status Pelayanan
        $this->importCsv('visits', "{$dir}/visits.csv");
        $this->importCsv('visit_status_histories', "{$dir}/visit_status_histories.csv");

        // 3. Triage & Asesmen Keperawatan
        $this->importCsv('nursing_assessments', "{$dir}/nursing_assessments.csv");
        $this->importCsv('vital_signs', "{$dir}/vital_signs.csv");

        // 4. Rekam Medis Dokter, Diagnosis ICD-10, Tindakan ICD-9-CM
        $this->importCsv('medical_records', "{$dir}/medical_records.csv");
        $this->importCsv('medical_record_diagnoses', "{$dir}/medical_record_diagnoses.csv");
        $this->importCsv('medical_record_procedures', "{$dir}/medical_record_procedures.csv");

        // 5. Poli Spesialis: Odontogram Gigi & Modul KIA (ANC / Balita)
        $this->importCsv('odontograms', "{$dir}/odontograms.csv");
        $this->importCsv('odontogram_teeth', "{$dir}/odontogram_teeth.csv");
        $this->importCsv('antenatal_cares', "{$dir}/antenatal_cares.csv");
        $this->importCsv('child_growths', "{$dir}/child_growths.csv");

        // 6. Resep Farmasi, Obat Racikan, Penyerahan Obat & Mutasi Stok (FEFO)
        $this->importCsv('prescriptions', "{$dir}/prescriptions.csv");
        $this->importCsv('prescription_items', "{$dir}/prescription_items.csv");
        $this->importCsv('prescription_compounds', "{$dir}/prescription_compounds.csv");
        $this->importCsv('prescription_compound_items', "{$dir}/prescription_compound_items.csv");
        $this->importCsv('dispensing_transactions', "{$dir}/dispensing_transactions.csv");
        $this->importCsv('dispensing_items', "{$dir}/dispensing_items.csv");
        $this->importCsv('medicine_stock_movements', "{$dir}/medicine_stock_movements.csv");

        // 7. Pemeriksaan Laboratorium & Nilai Hasil Pengujian
        $this->importCsv('laboratory_requests', "{$dir}/laboratory_requests.csv");
        $this->importCsv('laboratory_request_items', "{$dir}/laboratory_request_items.csv");
        $this->importCsv('laboratory_results', "{$dir}/laboratory_results.csv");

        // 8. Billing, Tagihan Pasien, Pembayaran Kasir, Refund & Jasa Medis
        $this->importCsv('billing_invoices', "{$dir}/billing_invoices.csv");
        $this->importCsv('billing_items', "{$dir}/billing_items.csv");
        $this->importCsv('payments', "{$dir}/payments.csv");
        $this->importCsv('refunds', "{$dir}/refunds.csv");
        $this->importCsv('medical_fee_transactions', "{$dir}/medical_fee_transactions.csv");

        // 9. Dokumen Medis, Berkas Pasien, Audit Trail & FHIR SatuSehat
        $this->importCsv('medical_documents', "{$dir}/medical_documents.csv");
        $this->importCsv('patient_documents', "{$dir}/patient_documents.csv");
        $this->importCsv('audit_logs', "{$dir}/audit_logs.csv");
        $this->importCsv('fhir_mappings', "{$dir}/fhir_mappings.csv");

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command?->info('✓ Seluruh data operasional klinis berhasil diimpor ke database MySQL.');
    }
}
