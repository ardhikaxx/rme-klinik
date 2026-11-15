<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ImportsCsvDataset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClinicMasterSeeder extends Seeder
{
    use ImportsCsvDataset;

    /**
     * Run the database seeds.
     * ZERO loops: static multi-row inserts and native CSV bulk loader.
     */
    public function run(): void
    {
        $now = '2020-01-01 08:00:00';

        // 1. Clinic Entity
        DB::table('clinics')->insertOrIgnore([
            [
                'id' => 1,
                'code' => 'KPSM-MLG',
                'name' => 'Klinik Pratama Sehat Mandiri',
                'license_number' => '440/123/438.5.2/2023',
                'phone' => '(0341) 551234',
                'email' => 'info@kliniksehatmandiri.co.id',
                'address' => 'Jl. Besar Ijen No. 45, Kel. Gading Kasri, Kec. Klojen',
                'city' => 'Kota Malang',
                'province' => 'Jawa Timur',
                'postal_code' => '65115',
                'header_note' => 'Melayani dengan Sepenuh Hati dan Berstandar Medis Terpadu',
                'footer_note' => 'Simpan dokumen ini sebagai bukti pelayanan rekam medis yang sah.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 2. Settings
        DB::table('clinic_settings')->insertOrIgnore([
            ['id' => 1, 'clinic_id' => 1, 'key' => 'clinic_name', 'value' => 'Klinik Pratama Sehat Mandiri', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'clinic_id' => 1, 'key' => 'clinic_address', 'value' => 'Jl. Besar Ijen No. 45, Kel. Gading Kasri, Kec. Klojen', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'clinic_id' => 1, 'key' => 'clinic_phone', 'value' => '(0341) 551234', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'clinic_id' => 1, 'key' => 'mrn_prefix', 'value' => 'RM', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'clinic_id' => 1, 'key' => 'timezone', 'value' => 'Asia/Jakarta', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'clinic_id' => 1, 'key' => 'currency', 'value' => 'IDR', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'clinic_id' => 1, 'key' => 'satusehat_env', 'value' => 'staging', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'clinic_id' => 1, 'key' => 'satusehat_org_id', 'value' => '10000004', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 3. Departments
        DB::table('departments')->insertOrIgnore([
            ['id' => 1, 'code' => 'MEDIS', 'name' => 'Pelayanan Medis', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'KEP', 'name' => 'Keperawatan & Kebidanan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'FARM', 'name' => 'Instalasi Farmasi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'LAB', 'name' => 'Instalasi Laboratorium', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'ADM', 'name' => 'Admisi & Kasir', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'MGT', 'name' => 'Manajemen Operasional', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 4. Polyclinics
        DB::table('polyclinics')->insertOrIgnore([
            ['id' => 1, 'code' => 'POLI-UMUM', 'name' => 'Poli Umum', 'type' => 'umum', 'description' => 'Pelayanan kesehatan umum dan rawat jalan dokter umum.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'POLI-GIGI', 'name' => 'Poli Gigi', 'type' => 'gigi', 'description' => 'Pemeriksaan kesehatan gigi, mulut, odontogram, dan tindakan gigi.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'POLI-KIA', 'name' => 'Poli KIA & KB', 'type' => 'kia', 'description' => 'Kesehatan ibu dan anak, ANC ibu hamil, KB, dan tumbuh kembang balita.', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 5. Shifts
        DB::table('shifts')->insertOrIgnore([
            ['id' => 1, 'name' => 'Shift Pagi', 'start_time' => '07:30:00', 'end_time' => '14:00:00', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Shift Siang', 'start_time' => '14:00:00', 'end_time' => '20:30:00', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 6. Payment Methods
        DB::table('payment_methods')->insertOrIgnore([
            ['id' => 1, 'code' => 'cash', 'name' => 'Tunai (Cash)', 'account_info' => 'Kasir Tunai', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'qris', 'name' => 'QRIS Dinamis', 'account_info' => 'NMID: ID1020304050607', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'debit', 'name' => 'Kartu Debit / EDC', 'account_info' => 'EDC Bank Mandiri / BCA', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'transfer_bca', 'name' => 'Transfer Bank BCA', 'account_info' => 'No Rek: 011-234-5678 a.n. Klinik Sehat Mandiri', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'transfer_mandiri', 'name' => 'Transfer Bank Mandiri', 'account_info' => 'No Rek: 144-00-9876543-2 a.n. Klinik Sehat Mandiri', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 7. Suppliers
        DB::table('suppliers')->insertOrIgnore([
            ['id' => 1, 'code' => 'SUP-KF', 'name' => 'PT Kimia Farma Trading & Dist Malang', 'contact_person' => 'Bambang Sutrisno', 'phone' => '0341-491234', 'email' => 'malang@kimiafarma.co.id', 'address' => 'Jl. Sunandar Priyo Sudarmo No. 12, Malang', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'SUP-ENS', 'name' => 'PT Enseval Putera Megatrading Malang', 'contact_person' => 'Dwi Handayani', 'phone' => '0341-475678', 'email' => 'sales.mlg@enseval.com', 'address' => 'Kawasan Industri Singosari Kav. 4, Malang', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'SUP-KAL', 'name' => 'PT Kalbe Farma Malang Cabang', 'contact_person' => 'Hendra Setiawan', 'phone' => '0341-401122', 'email' => 'dist.mlg@kalbe.co.id', 'address' => 'Jl. Raya Karanglo No. 88, Malang', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 8. Medicine Categories
        DB::table('medicine_categories')->insertOrIgnore([
            ['id' => 1, 'name' => 'Analgesik & Antipiretik', 'description' => 'Obat penurun demam dan pereda nyeri', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Antibiotik', 'description' => 'Obat anti-bakteri dan infeksi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Antihistamin & Anti-alergi', 'description' => 'Pereda reaksi alergi, gatal, rhinitis', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Obat Saluran Cerna', 'description' => 'Antasida, PPI, anti-diare, anti-mual', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Obat Saluran Pernafasan', 'description' => 'Bronkodilator, ekspektoran, mukolitik', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Vitamin & Suplemen', 'description' => 'Suplemen multivitamin dan daya tahan tubuh', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'Kortikosteroid', 'description' => 'Anti-inflamasi steroid', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 9. Medicine Units
        DB::table('medicine_units')->insertOrIgnore([
            ['id' => 1, 'code' => 'TAB', 'name' => 'Tablet', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'KAP', 'name' => 'Kapsul', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'BOT', 'name' => 'Botol (Sirup)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'AMP', 'name' => 'Ampul', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'TBN', 'name' => 'Tube (Salep)', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'STR', 'name' => 'Strip', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'code' => 'BKS', 'name' => 'Bungkus', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 10. Laboratories Master
        DB::table('laboratories')->insertOrIgnore([
            ['id' => 1, 'code' => 'LAB-DL', 'name' => 'Darah Lengkap (CBC)', 'category' => 'hematologi', 'sample_type' => 'darah', 'unit' => 'panel', 'reference_range' => 'Hb: 12-16 g/dL, Leu: 4000-10000 /uL, Plt: 150000-450000 /uL', 'critical_low' => null, 'critical_high' => null, 'tariff' => 65000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'LAB-GDS', 'name' => 'Glukosa Darah Sewaktu (GDS)', 'category' => 'kimia_darah', 'sample_type' => 'darah', 'unit' => 'mg/dL', 'reference_range' => '70 - 140 mg/dL', 'critical_low' => 50, 'critical_high' => 400, 'tariff' => 25000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'LAB-CHOL', 'name' => 'Kolesterol Total', 'category' => 'kimia_darah', 'sample_type' => 'darah', 'unit' => 'mg/dL', 'reference_range' => '< 200 mg/dL', 'critical_low' => null, 'critical_high' => 300, 'tariff' => 35000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'LAB-UA', 'name' => 'Asam Urat (Uric Acid)', 'category' => 'kimia_darah', 'sample_type' => 'darah', 'unit' => 'mg/dL', 'reference_range' => 'Pria: 3.4-7.0, Wanita: 2.4-5.7 mg/dL', 'critical_low' => null, 'critical_high' => 12, 'tariff' => 30000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'LAB-UL', 'name' => 'Urin Rutin Lengkap', 'category' => 'urinalisis', 'sample_type' => 'urin', 'unit' => 'analisis', 'reference_range' => 'pH: 4.5-8.0, Bj: 1.005-1.030, Protein (-)', 'critical_low' => null, 'critical_high' => null, 'tariff' => 40000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'LAB-WIDAL', 'name' => 'Uji Widal (Tifoid)', 'category' => 'imunologi', 'sample_type' => 'darah', 'unit' => 'titer', 'reference_range' => 'Titer < 1/80 Negatif', 'critical_low' => null, 'critical_high' => null, 'tariff' => 45000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 11. Service Tariffs
        DB::table('service_tariffs')->insertOrIgnore([
            ['id' => 1, 'code' => 'TRF-ADM', 'name' => 'Biaya Administrasi & Registrasi Pasien', 'category' => 'administrasi', 'polyclinic_id' => null, 'icd9cm_code' => null, 'tariff' => 15000, 'doctor_fee' => 0, 'nurse_fee' => 0, 'clinic_fee' => 15000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'code' => 'TRF-KONS-UMUM', 'name' => 'Konsultasi & Pemeriksaan Dokter Umum', 'category' => 'konsultasi', 'polyclinic_id' => 1, 'icd9cm_code' => null, 'tariff' => 40000, 'doctor_fee' => 25000, 'nurse_fee' => 5000, 'clinic_fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'code' => 'TRF-KONS-GIGI', 'name' => 'Konsultasi & Pemeriksaan Dokter Gigi', 'category' => 'konsultasi', 'polyclinic_id' => 2, 'icd9cm_code' => null, 'tariff' => 50000, 'doctor_fee' => 35000, 'nurse_fee' => 5000, 'clinic_fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'code' => 'TRF-KONS-KIA', 'name' => 'Konsultasi & Pemeriksaan KIA / Bidan', 'category' => 'konsultasi', 'polyclinic_id' => 3, 'icd9cm_code' => null, 'tariff' => 35000, 'doctor_fee' => 20000, 'nurse_fee' => 5000, 'clinic_fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'code' => 'TRF-TND-NEB', 'name' => 'Tindakan Nebulisasi', 'category' => 'tindakan_medis', 'polyclinic_id' => 1, 'icd9cm_code' => '93.94', 'tariff' => 45000, 'doctor_fee' => 25000, 'nurse_fee' => 10000, 'clinic_fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'code' => 'TRF-TND-JAHIT', 'name' => 'Penjahitan Luka (Hecting)', 'category' => 'tindakan_medis', 'polyclinic_id' => 1, 'icd9cm_code' => '86.59', 'tariff' => 60000, 'doctor_fee' => 40000, 'nurse_fee' => 10000, 'clinic_fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'code' => 'TRF-TND-TAMBAL', 'name' => 'Penambalan Gigi Komposit', 'category' => 'tindakan_medis', 'polyclinic_id' => 2, 'icd9cm_code' => '23.2', 'tariff' => 120000, 'doctor_fee' => 85000, 'nurse_fee' => 10000, 'clinic_fee' => 25000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'code' => 'TRF-TND-SCALING', 'name' => 'Pembersihan Karang Gigi (Scaling)', 'category' => 'tindakan_medis', 'polyclinic_id' => 2, 'icd9cm_code' => '96.54', 'tariff' => 150000, 'doctor_fee' => 100000, 'nurse_fee' => 15000, 'clinic_fee' => 35000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'code' => 'TRF-TND-CABUT-SUSU', 'name' => 'Pencabutan Gigi Susu', 'category' => 'tindakan_medis', 'polyclinic_id' => 2, 'icd9cm_code' => '23.01', 'tariff' => 75000, 'doctor_fee' => 50000, 'nurse_fee' => 10000, 'clinic_fee' => 15000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'code' => 'TRF-TND-ANC', 'name' => 'Pemeriksaan Kehamilan (ANC Terpadu)', 'category' => 'tindakan_medis', 'polyclinic_id' => 3, 'icd9cm_code' => '88.78', 'tariff' => 50000, 'doctor_fee' => 35000, 'nurse_fee' => 5000, 'clinic_fee' => 10000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'code' => 'TRF-TND-IMUNISASI', 'name' => 'Tindakan Imunisasi Balita', 'category' => 'tindakan_keperawatan', 'polyclinic_id' => 3, 'icd9cm_code' => '99.39', 'tariff' => 35000, 'doctor_fee' => 0, 'nurse_fee' => 20000, 'clinic_fee' => 15000, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 12. Document Templates & Medical Fee Rules (from physical dataset CSV files)
        $dir = database_path('seeders/datasets');
        if (file_exists("{$dir}/document_templates.csv")) {
            $this->importCsv('document_templates', "{$dir}/document_templates.csv");
        }
        if (file_exists("{$dir}/medical_fee_rules.csv")) {
            $this->importCsv('medical_fee_rules', "{$dir}/medical_fee_rules.csv");
        }

        $this->command?->info('✓ Master klinik, poli, tarif, template dokumen & aturan jasa medis berhasil diinisialisasi.');
    }
}
