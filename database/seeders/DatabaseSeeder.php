<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database in proper topological dependency order.
     */
    public function run(): void
    {
        $this->call([
            // 1. Fondasi Keamanan & Aksesibilitas Role & Permission
            RoleAndPermissionSeeder::class,

            // 2. Master Klinik, Departemen, Poliklinik, Tarif, Obat Dasar & Pembayaran
            ClinicMasterSeeder::class,

            // 3. Tenaga Medis, Karyawan & Akun Pengguna Inti (membutuhkan Departemen & Poliklinik)
            UserAndEmployeeSeeder::class,

            // 4. Katalog Diagnosis ICD-10 & ICD-9-CM
            IcdCodesSeeder::class,

            // 5. Jadwal Kerja Dokter Seluruh Poliklinik (Umum, Gigi, KIA)
            DoctorScheduleSeeder::class,

            // 6. Dataset Pasien Unik Indonesia Berskala Besar (100.000 Pasien)
            ComprehensivePatientSeeder::class,

            // 7. Dataset Operasional Klinis, Rekam Medis, Farmasi, Lab, Billing (200.000 Kunjungan)
            ComprehensiveOperationSeeder::class,
        ]);
    }
}
