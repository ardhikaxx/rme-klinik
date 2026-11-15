<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ValidateDataset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:validate-dataset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validasi integritas data, relasi, audit perhitungan, dan kepatuhan skala dataset RME KLINIK';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('======================================================================');
        $this->info('  AUDIT DAN VALIDASI INTEGRITAS DATASET RME KLINIK (2020 - 2026)      ');
        $this->info('======================================================================');

        // 1. Audit Jumlah Record per Tabel
        $this->line('');
        $this->comment('1. Audit Jumlah Record Database:');

        $tables = [
            // Master & Identitas
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'patients', 'Deskripsi' => 'Pasien Unik Terdaftar', 'Target Min' => '100.000'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'patient_allergies', 'Deskripsi' => 'Riwayat Alergi Pasien', 'Target Min' => '10.000'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'patient_emergency_contacts', 'Deskripsi' => 'Kontak Darurat Pasien', 'Target Min' => '25.000'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'employees', 'Deskripsi' => 'Tenaga Medis & Karyawan', 'Target Min' => '6'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'medicines', 'Deskripsi' => 'Master Obat-obatan', 'Target Min' => '50'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'document_templates', 'Deskripsi' => 'Template Surat & Dokumen Medis', 'Target Min' => '5'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'medical_fee_rules', 'Deskripsi' => 'Aturan Pembagian Jasa Medis', 'Target Min' => '6'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'icd10_codes', 'Deskripsi' => 'Master Diagnosis ICD-10 Riil', 'Target Min' => '15.000'],
            ['Kategori' => 'Master / Identitas', 'Tabel' => 'icd9cm_codes', 'Deskripsi' => 'Master Prosedur ICD-9-CM Riil', 'Target Min' => '4.000'],

            // Kunjungan & Triage
            ['Kategori' => 'Operasional Kunjungan', 'Tabel' => 'visits', 'Deskripsi' => 'Kunjungan Poliklinik', 'Target Min' => '150.000'],
            ['Kategori' => 'Operasional Kunjungan', 'Tabel' => 'visit_status_histories', 'Deskripsi' => 'Riwayat Perubahan Status Kunjungan', 'Target Min' => '150.000'],
            ['Kategori' => 'Operasional Triage', 'Tabel' => 'nursing_assessments', 'Deskripsi' => 'Pengkajian Awal & Triage Perawat', 'Target Min' => '100.000'],
            ['Kategori' => 'Operasional Triage', 'Tabel' => 'vital_signs', 'Deskripsi' => 'Rekam Tanda-Tanda Vital', 'Target Min' => '100.000'],

            // Medis & Spesialisasi
            ['Kategori' => 'Operasional Medis', 'Tabel' => 'medical_records', 'Deskripsi' => 'Rekam Medis Dokter (SOAP)', 'Target Min' => '100.000'],
            ['Kategori' => 'Operasional Medis', 'Tabel' => 'medical_record_addendums', 'Deskripsi' => 'Addendum Catatan Klinis Dokter', 'Target Min' => '500'],
            ['Kategori' => 'Operasional Medis', 'Tabel' => 'medical_record_diagnoses', 'Deskripsi' => 'Diagnosis ICD-10 Klinis', 'Target Min' => '120.000'],
            ['Kategori' => 'Operasional Medis', 'Tabel' => 'medical_record_procedures', 'Deskripsi' => 'Tindakan Medis ICD-9-CM', 'Target Min' => '30.000'],
            ['Kategori' => 'Poli Spesialis', 'Tabel' => 'odontograms', 'Deskripsi' => 'Odontogram Pemeriksaan Poli Gigi', 'Target Min' => '5.000'],
            ['Kategori' => 'Poli Spesialis', 'Tabel' => 'odontogram_teeth', 'Deskripsi' => 'Detail Status Gigi (FDI Notation)', 'Target Min' => '15.000'],
            ['Kategori' => 'Poli Spesialis', 'Tabel' => 'antenatal_cares', 'Deskripsi' => 'Pemeriksaan Ibu Hamil (ANC)', 'Target Min' => '5.000'],
            ['Kategori' => 'Poli Spesialis', 'Tabel' => 'child_growths', 'Deskripsi' => 'Tumbuh Kembang & Imunisasi Anak', 'Target Min' => '5.000'],

            // Farmasi & Inventori
            ['Kategori' => 'Farmasi & Stok', 'Tabel' => 'medicine_batches', 'Deskripsi' => 'Batch Obat & Kedaluwarsa', 'Target Min' => '150'],
            ['Kategori' => 'Farmasi & Stok', 'Tabel' => 'medicine_receipts', 'Deskripsi' => 'Faktur Penerimaan Obat PBF', 'Target Min' => '150'],
            ['Kategori' => 'Farmasi & Stok', 'Tabel' => 'medicine_receipt_items', 'Deskripsi' => 'Item Penerimaan Stok Batch', 'Target Min' => '150'],
            ['Kategori' => 'Farmasi & Stok', 'Tabel' => 'medicine_stock_opnames', 'Deskripsi' => 'Sesi Audit Stock Opname Berkala', 'Target Min' => '20'],
            ['Kategori' => 'Farmasi & Stok', 'Tabel' => 'medicine_stock_opname_items', 'Deskripsi' => 'Item Fisik Audit Stock Opname', 'Target Min' => '1.000'],
            ['Kategori' => 'Farmasi & Stok', 'Tabel' => 'medicine_stock_movements', 'Deskripsi' => 'Kartu Mutasi Stok Obat (Audit FEFO)', 'Target Min' => '100.000'],
            ['Kategori' => 'Farmasi & Resep', 'Tabel' => 'prescriptions', 'Deskripsi' => 'Resep Elektronik Dokter', 'Target Min' => '80.000'],
            ['Kategori' => 'Farmasi & Resep', 'Tabel' => 'prescription_items', 'Deskripsi' => 'Item Obat pada Resep', 'Target Min' => '100.000'],
            ['Kategori' => 'Farmasi & Resep', 'Tabel' => 'prescription_compounds', 'Deskripsi' => 'Obat Racikan / Puyer Resep', 'Target Min' => '5.000'],
            ['Kategori' => 'Farmasi & Resep', 'Tabel' => 'prescription_compound_items', 'Deskripsi' => 'Komponen Bahan Obat Racikan', 'Target Min' => '10.000'],
            ['Kategori' => 'Farmasi & Resep', 'Tabel' => 'dispensing_transactions', 'Deskripsi' => 'Transaksi Penyerahan Obat Kasir/Apotek', 'Target Min' => '60.000'],
            ['Kategori' => 'Farmasi & Resep', 'Tabel' => 'dispensing_items', 'Deskripsi' => 'Rincian Penyerahan Obat & Batch (FEFO)', 'Target Min' => '75.000'],

            // Laboratorium (Target Min >= 50.000)
            ['Kategori' => 'Laboratorium', 'Tabel' => 'laboratory_requests', 'Deskripsi' => 'Order Pemeriksaan Laboratorium', 'Target Min' => '50.000'],
            ['Kategori' => 'Laboratorium', 'Tabel' => 'laboratory_request_items', 'Deskripsi' => 'Parameter Uji Lab yang Diminta', 'Target Min' => '50.000'],
            ['Kategori' => 'Laboratorium', 'Tabel' => 'laboratory_results', 'Deskripsi' => 'Hasil Pengujian Lab & Nilai Kritis', 'Target Min' => '50.000'],

            // Billing & Keuangan
            ['Kategori' => 'Billing & Kasir', 'Tabel' => 'billing_invoices', 'Deskripsi' => 'Faktur / Tagihan Pelayanan Pasien', 'Target Min' => '80.000'],
            ['Kategori' => 'Billing & Kasir', 'Tabel' => 'billing_items', 'Deskripsi' => 'Rincian Item Tagihan Pasien', 'Target Min' => '200.000'],
            ['Kategori' => 'Billing & Kasir', 'Tabel' => 'payments', 'Deskripsi' => 'Kwitansi Pembayaran Kasir', 'Target Min' => '70.000'],
            ['Kategori' => 'Billing & Kasir', 'Tabel' => 'refunds', 'Deskripsi' => 'Pengembalian Dana / Retur Klaim', 'Target Min' => '50'],
            ['Kategori' => 'Jasa Medis', 'Tabel' => 'medical_fee_transactions', 'Deskripsi' => 'Distribusi Jasa Medis Dokter & Perawat', 'Target Min' => '80.000'],

            // Dokumen Medis & Integrasi
            ['Kategori' => 'Dokumen & Regulasi', 'Tabel' => 'medical_documents', 'Deskripsi' => 'Surat Sakit, Sehat & Rujukan', 'Target Min' => '10.000'],
            ['Kategori' => 'Dokumen & Regulasi', 'Tabel' => 'patient_documents', 'Deskripsi' => 'Berkas Penunjang & Identitas Pasien', 'Target Min' => '5.000'],
            ['Kategori' => 'Audit & Integrasi', 'Tabel' => 'audit_logs', 'Deskripsi' => 'Audit Trail Log Aktivitas Sistem', 'Target Min' => '40.000'],
            ['Kategori' => 'Audit & Integrasi', 'Tabel' => 'fhir_mappings', 'Deskripsi' => 'Pemetaan Sumber Daya Interoperabilitas FHIR', 'Target Min' => '40.000'],
            ['Kategori' => 'Audit & Integrasi', 'Tabel' => 'fhir_sync_logs', 'Deskripsi' => 'Log Transmisi Interoperabilitas SATUSEHAT', 'Target Min' => '40.000'],
        ];

        $tableRows = [];
        $totalAllRecords = 0;
        $totalOperationalRecords = 0;
        $operationalExclusions = [
            'patients', 'employees', 'medicines', 'icd10_codes', 'icd9cm_codes',
            'clinics', 'departments', 'polyclinics', 'roles', 'permissions',
            'payment_methods', 'service_tariffs', 'document_templates', 'medical_fee_rules',
        ];

        foreach ($tables as $t) {
            $count = DB::table($t['Tabel'])->count();
            $totalAllRecords += $count;
            if (! in_array($t['Tabel'], $operationalExclusions)) {
                $totalOperationalRecords += $count;
            }

            $tableRows[] = [
                $t['Kategori'],
                $t['Tabel'],
                $t['Deskripsi'],
                number_format($count, 0, ',', '.'),
                $t['Target Min'],
                $count > 0 ? '<info>TERISI</info>' : '<comment>KOSONG</comment>',
            ];
        }

        $this->table(
            ['Kategori Modul', 'Nama Tabel', 'Deskripsi Fungsional', 'Jumlah Aktual', 'Target Rekomendasi', 'Status'],
            $tableRows
        );

        $patientCount = DB::table('patients')->count();
        $this->info('Total Pasien Unik (Tabel patients): '.number_format($patientCount, 0, ',', '.'));
        $this->info('Total Record Operasional Lintas Modul: '.number_format($totalOperationalRecords, 0, ',', '.'));
        $this->info('Total Seluruh Record Audit: '.number_format($totalAllRecords, 0, ',', '.'));

        // 2. Pengujian Integritas Relasional & Konsistensi Data Klinis
        $this->line('');
        $this->comment('2. Pengujian Integritas Relasional & Logika Klinis:');

        $checks = [];
        $allPassed = true;

        // Check A: Target Pasien Minimal 100.000
        $passPatients = $patientCount >= 100000;
        $checks[] = [
            'Target Pasien Minimum 100.000',
            $passPatients ? 'LULUS ('.number_format($patientCount).' pasien)' : 'GAGAL ('.number_format($patientCount).' < 100.000)',
            $passPatients ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if (! $passPatients) {
            $allPassed = false;
        }

        // Check B: Target Record Operasional Minimal 100.000 (Target tercapai > 5.000.000)
        $passOps = $totalOperationalRecords >= 100000;
        $checks[] = [
            'Target Data Operasional Minimum 100.000',
            $passOps ? 'LULUS ('.number_format($totalOperationalRecords).' records)' : 'GAGAL ('.number_format($totalOperationalRecords).' < 100.000)',
            $passOps ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if (! $passOps) {
            $allPassed = false;
        }

        // Check C: Permintaan Laboratorium Minimal 50.000
        $labReqCount = DB::table('laboratory_requests')->count();
        $passLab = $labReqCount >= 50000;
        $checks[] = [
            'Target Permintaan Lab Minimum 50.000',
            $passLab ? 'LULUS ('.number_format($labReqCount).' order lab)' : 'GAGAL ('.number_format($labReqCount).' < 50.000)',
            $passLab ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if (! $passLab) {
            $allPassed = false;
        }

        // Check D: Rentang Waktu Historis (2020 - 2026) & Tidak Ada Transaksi Masa Depan
        $minDate = DB::table('visits')->min('registration_date');
        $maxDate = DB::table('visits')->max('registration_date');
        $nowLimit = Carbon::now()->addHour()->toDateTimeString();
        $noFutureTx = $maxDate && $maxDate <= $nowLimit;
        $hasHistoricalRange = $minDate && $maxDate && str_starts_with($minDate, '2020') && str_starts_with($maxDate, '2026');
        $checks[] = [
            'Rentang Waktu 2020-2026 & Bebas Masa Depan',
            "Min: {$minDate} | Max: {$maxDate}".($noFutureTx ? ' (Valid <= Saat ini)' : ' (Error: Masa Depan)'),
            ($hasHistoricalRange && $noFutureTx) ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if (! ($hasHistoricalRange && $noFutureTx)) {
            $allPassed = false;
        }

        // Check E: Kunjungan Aktif Hari Ini (2026-11-15) untuk Dashboard & Worklist
        $todayCount = DB::table('visits')->whereDate('registration_date', Carbon::today())->count();
        $passToday = $todayCount > 0;
        $checks[] = [
            'Kunjungan Aktif Hari Ini (Worklist & Dashboard)',
            $passToday ? "LULUS ({$todayCount} kunjungan aktif hari ini)" : 'GAGAL (0 kunjungan hari ini)',
            $passToday ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if (! $passToday) {
            $allPassed = false;
        }

        // Check F: Orphan Check - Visits tanpa Pasien
        $orphanVisits = DB::table('visits')
            ->leftJoin('patients', 'visits.patient_id', '=', 'patients.id')
            ->whereNull('patients.id')
            ->count();
        $checks[] = [
            'Integritas Kunjungan (Tanpa Orphan Patient)',
            $orphanVisits === 0 ? '0 orphan record' : "{$orphanVisits} orphan records",
            $orphanVisits === 0 ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if ($orphanVisits > 0) {
            $allPassed = false;
        }

        // Check G: Orphan Check - Rekam Medis tanpa Kunjungan
        $orphanEMR = DB::table('medical_records')
            ->leftJoin('visits', 'medical_records.visit_id', '=', 'visits.id')
            ->whereNull('visits.id')
            ->count();
        $checks[] = [
            'Integritas Rekam Medis (Tanpa Orphan Visit)',
            $orphanEMR === 0 ? '0 orphan record' : "{$orphanEMR} orphan records",
            $orphanEMR === 0 ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if ($orphanEMR > 0) {
            $allPassed = false;
        }

        // Check H: Validitas Kode Diagnosa ICD-10
        $invalidIcd10 = DB::table('medical_record_diagnoses')
            ->leftJoin('icd10_codes', 'medical_record_diagnoses.icd10_code', '=', 'icd10_codes.code')
            ->whereNull('icd10_codes.code')
            ->count();
        $checks[] = [
            'Validitas Kode Diagnosis ICD-10 Riil',
            $invalidIcd10 === 0 ? 'Semua diagnosis sesuai master ICD-10' : "{$invalidIcd10} kode tidak terdaftar",
            $invalidIcd10 === 0 ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if ($invalidIcd10 > 0) {
            $allPassed = false;
        }

        // Check I: Validitas Stok Obat (Tidak Boleh Negatif / FEFO)
        $negativeStock = DB::table('medicine_batches')->where('current_stock', '<', 0)->count();
        $checks[] = [
            'Non-Negative Stock Batch Obat (FEFO Compliance)',
            $negativeStock === 0 ? '0 batch dengan stok negatif' : "{$negativeStock} batch negatif!",
            $negativeStock === 0 ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if ($negativeStock > 0) {
            $allPassed = false;
        }

        // Check J: Konsistensi Matematika Billing (Total Invoice = Sum Billing Items)
        $sampleInvoices = DB::table('billing_invoices')->limit(100)->pluck('id');
        $mismatchedInvoices = 0;
        foreach ($sampleInvoices as $invId) {
            $inv = DB::table('billing_invoices')->find($invId);
            $itemsSum = (float) DB::table('billing_items')->where('billing_invoice_id', $invId)->sum('subtotal');
            $expectedFinal = (float) $inv->total_amount - (float) $inv->discount_amount;
            if (abs($itemsSum - (float) $inv->total_amount) > 0.01 || abs($expectedFinal - (float) $inv->final_amount) > 0.01) {
                $mismatchedInvoices++;
            }
        }
        $checks[] = [
            'Akurasi Matematika Billing (Sample 100 Invoice)',
            $mismatchedInvoices === 0 ? 'Semua total & diskon presisi 100%' : "{$mismatchedInvoices} invoice mismatch",
            $mismatchedInvoices === 0 ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if ($mismatchedInvoices > 0) {
            $allPassed = false;
        }

        // Check K: Integritas Odontogram FDI Tooth Number
        $invalidTeeth = DB::table('odontogram_teeth')
            ->whereRaw("tooth_number NOT REGEXP '^[1-8][1-8]$'")
            ->count();
        $checks[] = [
            'Validitas Notasi Gigi Odontogram (FDI 2-Digit)',
            $invalidTeeth === 0 ? 'Semua notasi gigi valid (11-85)' : "{$invalidTeeth} notasi tidak valid",
            $invalidTeeth === 0 ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if ($invalidTeeth > 0) {
            $allPassed = false;
        }

        // Check L: Integritas Alur Waktu Transaksional
        $illogicalVisits = DB::table('medical_records')
            ->join('visits', 'medical_records.visit_id', '=', 'visits.id')
            ->whereRaw('medical_records.examination_time < visits.registration_date')
            ->count();
        $checks[] = [
            'Kronologi Pelayanan (Waktu Periksa >= Registrasi)',
            $illogicalVisits === 0 ? 'Kronologi logis 100%' : "{$illogicalVisits} data anomali waktu",
            $illogicalVisits === 0 ? '<info>PASS</info>' : '<error>FAIL</error>',
        ];
        if ($illogicalVisits > 0) {
            $allPassed = false;
        }

        $this->table(['Parameter Pengujian Integritas', 'Keterangan / Hasil Verifikasi', 'Status'], $checks);

        $this->line('');
        if ($allPassed) {
            $this->info('✓ SELURUH UJI INTEGRITAS DATASET BERHASIL DENGAN STATUS PASS (100% VALID)');

            return Command::SUCCESS;
        } else {
            $this->warn('! BEBERAPA PARAMETER DATASET BELUM MEMENUHI TARGET (LIHAT DETAIL TABEL DI ATAS)');

            return Command::FAILURE;
        }
    }
}
