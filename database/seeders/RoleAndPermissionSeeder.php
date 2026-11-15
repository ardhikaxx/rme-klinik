<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Define Granular Permissions
        $permissions = [
            // Patient Management
            ['name' => 'patients.view', 'display_name' => 'Melihat Data Pasien', 'group' => 'Pasien'],
            ['name' => 'patients.create', 'display_name' => 'Mendaftarkan Pasien Baru', 'group' => 'Pasien'],
            ['name' => 'patients.update', 'display_name' => 'Memperbarui Data Pasien', 'group' => 'Pasien'],
            ['name' => 'patients.card.print', 'display_name' => 'Mencetak Kartu Pasien', 'group' => 'Pasien'],

            // Visits & Queue
            ['name' => 'visits.view', 'display_name' => 'Melihat Daftar Kunjungan', 'group' => 'Kunjungan'],
            ['name' => 'visits.register', 'display_name' => 'Mendaftarkan Kunjungan', 'group' => 'Kunjungan'],
            ['name' => 'visits.cancel', 'display_name' => 'Membatalkan Kunjungan', 'group' => 'Kunjungan'],

            // Nursing Assessment
            ['name' => 'nursing.assessment', 'display_name' => 'Pengkajian Awal Perawat', 'group' => 'Keperawatan'],
            ['name' => 'vital_signs.record', 'display_name' => 'Mencatat Tanda Vital', 'group' => 'Keperawatan'],

            // Doctor Clinical / EMR
            ['name' => 'medical_records.view', 'display_name' => 'Melihat Rekam Medis Pasien', 'group' => 'Rekam Medis'],
            ['name' => 'medical_records.create', 'display_name' => 'Mengisi Catatan SOAP & Diagnosis', 'group' => 'Rekam Medis'],
            ['name' => 'medical_records.finalize', 'display_name' => 'Memfinalisasi & Mengunci Rekam Medis', 'group' => 'Rekam Medis'],
            ['name' => 'medical_records.addendum', 'display_name' => 'Membuat Addendum Rekam Medis', 'group' => 'Rekam Medis'],

            // Specialty Modules
            ['name' => 'odontogram.manage', 'display_name' => 'Mengisi Odontogram Poli Gigi', 'group' => 'Poli Spesifik'],
            ['name' => 'kia.manage', 'display_name' => 'Mengisi Data KIA & Tumbuh Kembang', 'group' => 'Poli Spesifik'],

            // E-Prescription
            ['name' => 'prescriptions.create', 'display_name' => 'Membuat Resep Elektronik', 'group' => 'Farmasi'],
            ['name' => 'prescriptions.verify', 'display_name' => 'Menelaah & Memverifikasi Resep', 'group' => 'Farmasi'],
            ['name' => 'pharmacy.dispense', 'display_name' => 'Dispensing & Penyerahan Obat', 'group' => 'Farmasi'],
            ['name' => 'pharmacy.stock.view', 'display_name' => 'Melihat Stok Farmasi', 'group' => 'Farmasi'],
            ['name' => 'pharmacy.stock.manage', 'display_name' => 'Mengelola Stok, Batch & Penerimaan', 'group' => 'Farmasi'],
            ['name' => 'pharmacy.opname', 'display_name' => 'Melakukan Stock Opname', 'group' => 'Farmasi'],

            // Laboratory
            ['name' => 'laboratory.order', 'display_name' => 'Membuat Order Laboratorium', 'group' => 'Laboratorium'],
            ['name' => 'laboratory.record', 'display_name' => 'Memasukkan Hasil Laboratorium', 'group' => 'Laboratorium'],
            ['name' => 'laboratory.verify', 'display_name' => 'Verifikasi Hasil Laboratorium', 'group' => 'Laboratorium'],

            // Billing & Cashier
            ['name' => 'billing.view', 'display_name' => 'Melihat Tagihan & Kuitansi', 'group' => 'Kasir'],
            ['name' => 'billing.pay', 'display_name' => 'Menerima Pembayaran Kasir', 'group' => 'Kasir'],
            ['name' => 'billing.discount', 'display_name' => 'Memberikan Diskon Tagihan', 'group' => 'Kasir'],
            ['name' => 'billing.refund', 'display_name' => 'Memproses Refund Pembayaran', 'group' => 'Kasir'],

            // Medical Documents
            ['name' => 'documents.generate', 'display_name' => 'Menerbitkan Surat Medis', 'group' => 'Dokumen'],
            ['name' => 'documents.upload', 'display_name' => 'Mengunggah Berkas Penunjang', 'group' => 'Dokumen'],

            // Reports & Analytics
            ['name' => 'reports.financial', 'display_name' => 'Laporan Pendapatan & Kasir', 'group' => 'Laporan'],
            ['name' => 'reports.clinical', 'display_name' => 'Laporan Morbiditas & Kinerja Dokter', 'group' => 'Laporan'],
            ['name' => 'reports.operational', 'display_name' => 'Laporan Kunjungan & Stok', 'group' => 'Laporan'],

            // Administration & Management
            ['name' => 'users.manage', 'display_name' => 'Mengelola Akun & Pegawai', 'group' => 'Manajemen'],
            ['name' => 'tariffs.manage', 'display_name' => 'Mengelola Tarif & Jasa Medis', 'group' => 'Manajemen'],
            ['name' => 'schedules.manage', 'display_name' => 'Mengelola Jadwal Dokter', 'group' => 'Manajemen'],
            ['name' => 'clinic.settings', 'display_name' => 'Konfigurasi Profil Klinik', 'group' => 'Manajemen'],
            ['name' => 'audit_logs.view', 'display_name' => 'Melihat Audit Trail Keamanan', 'group' => 'Manajemen'],
            ['name' => 'fhir.manage', 'display_name' => 'Manajemen Kesiapan SATUSEHAT/FHIR', 'group' => 'Manajemen'],
        ];

        foreach ($permissions as $permData) {
            Permission::firstOrCreate(['name' => $permData['name']], $permData);
        }

        // 2. Define the 5 Primary Roles
        $roles = [
            'admisi_kasir' => [
                'display_name' => 'Petugas Admisi & Kasir',
                'description' => 'Mengelola pendaftaran pasien, registrasi kunjungan, tagihan dan pembayaran kasir. Dibatasi dari akses SOAP rekam medis.',
                'permissions' => [
                    'patients.view', 'patients.create', 'patients.update', 'patients.card.print',
                    'visits.view', 'visits.register', 'visits.cancel',
                    'billing.view', 'billing.pay',
                    'reports.financial',
                ],
            ],
            'perawat_bidan' => [
                'display_name' => 'Perawat atau Bidan',
                'description' => 'Melakukan pengkajian awal, pemeriksaan tanda-tanda vital, antropometri, skrining, riwayat alergi, dan pelayanan KIA.',
                'permissions' => [
                    'patients.view', 'patients.card.print',
                    'visits.view',
                    'nursing.assessment', 'vital_signs.record',
                    'kia.manage',
                    'documents.upload',
                ],
            ],
            'dokter' => [
                'display_name' => 'Dokter Pemeriksa',
                'description' => 'Memiliki kewenangan klinis penuh: SOAP, diagnosis ICD-10, tindakan ICD-9-CM, odontogram gigi, e-resep, permintaan lab, finalisasi RME.',
                'permissions' => [
                    'patients.view',
                    'visits.view',
                    'medical_records.view', 'medical_records.create', 'medical_records.finalize', 'medical_records.addendum',
                    'odontogram.manage', 'kia.manage',
                    'prescriptions.create',
                    'laboratory.order',
                    'documents.generate', 'documents.upload',
                    'reports.clinical',
                ],
            ],
            'farmasi' => [
                'display_name' => 'Apoteker / TTK',
                'description' => 'Mengelola telaah resep masuk, dispensing obat FEFO, kartu stok, batch, kedaluwarsa, penerimaan barang dan stock opname.',
                'permissions' => [
                    'patients.view',
                    'visits.view',
                    'prescriptions.verify', 'pharmacy.dispense',
                    'pharmacy.stock.view', 'pharmacy.stock.manage', 'pharmacy.opname',
                    'reports.operational',
                ],
            ],
            'kepala_klinik' => [
                'display_name' => 'Kepala Klinik / Manajer',
                'description' => 'Memiliki kewenangan manajerial: konfigurasi klinik, master data tarif, jadwal pegawai, audit logs, laporan eksekutif dan SATUSEHAT.',
                'permissions' => [
                    'patients.view',
                    'visits.view',
                    'billing.view', 'billing.discount', 'billing.refund',
                    'pharmacy.stock.view',
                    'reports.financial', 'reports.clinical', 'reports.operational',
                    'users.manage', 'tariffs.manage', 'schedules.manage',
                    'clinic.settings', 'audit_logs.view', 'fhir.manage',
                ],
            ],
        ];

        foreach ($roles as $roleKey => $roleInfo) {
            $role = Role::firstOrCreate(
                ['name' => $roleKey],
                [
                    'display_name' => $roleInfo['display_name'],
                    'description' => $roleInfo['description'],
                    'is_system' => true,
                ]
            );

            $permissionIds = Permission::whereIn('name', $roleInfo['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
