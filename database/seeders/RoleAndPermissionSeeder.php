<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Define Granular Permissions (Bulk inserted without loops)
        $permissions = [
            // Patient Management
            ['name' => 'patients.view', 'display_name' => 'Melihat Data Pasien', 'group' => 'Pasien', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.create', 'display_name' => 'Mendaftarkan Pasien Baru', 'group' => 'Pasien', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.update', 'display_name' => 'Memperbarui Data Pasien', 'group' => 'Pasien', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'patients.card.print', 'display_name' => 'Mencetak Kartu Pasien', 'group' => 'Pasien', 'created_at' => $now, 'updated_at' => $now],

            // Visits & Queue
            ['name' => 'visits.view', 'display_name' => 'Melihat Daftar Kunjungan', 'group' => 'Kunjungan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'visits.register', 'display_name' => 'Mendaftarkan Kunjungan', 'group' => 'Kunjungan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'visits.cancel', 'display_name' => 'Membatalkan Kunjungan', 'group' => 'Kunjungan', 'created_at' => $now, 'updated_at' => $now],

            // Nursing Assessment
            ['name' => 'nursing.assessment', 'display_name' => 'Pengkajian Awal Perawat', 'group' => 'Keperawatan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'vital_signs.record', 'display_name' => 'Mencatat Tanda Vital', 'group' => 'Keperawatan', 'created_at' => $now, 'updated_at' => $now],

            // Doctor Clinical / EMR
            ['name' => 'medical_records.view', 'display_name' => 'Melihat Rekam Medis Pasien', 'group' => 'Rekam Medis', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'medical_records.create', 'display_name' => 'Mengisi Catatan SOAP & Diagnosis', 'group' => 'Rekam Medis', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'medical_records.finalize', 'display_name' => 'Memfinalisasi & Mengunci Rekam Medis', 'group' => 'Rekam Medis', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'medical_records.addendum', 'display_name' => 'Membuat Addendum Rekam Medis', 'group' => 'Rekam Medis', 'created_at' => $now, 'updated_at' => $now],

            // Specialty Modules
            ['name' => 'odontogram.manage', 'display_name' => 'Mengisi Odontogram Poli Gigi', 'group' => 'Poli Spesifik', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'kia.manage', 'display_name' => 'Mengisi Data KIA & Tumbuh Kembang', 'group' => 'Poli Spesifik', 'created_at' => $now, 'updated_at' => $now],

            // E-Prescription
            ['name' => 'prescriptions.create', 'display_name' => 'Membuat Resep Elektronik', 'group' => 'Farmasi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'prescriptions.verify', 'display_name' => 'Menelaah & Memverifikasi Resep', 'group' => 'Farmasi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pharmacy.dispense', 'display_name' => 'Dispensing & Penyerahan Obat', 'group' => 'Farmasi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pharmacy.stock.view', 'display_name' => 'Melihat Stok Farmasi', 'group' => 'Farmasi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pharmacy.stock.manage', 'display_name' => 'Mengelola Stok, Batch & Penerimaan', 'group' => 'Farmasi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'pharmacy.opname', 'display_name' => 'Melakukan Stock Opname', 'group' => 'Farmasi', 'created_at' => $now, 'updated_at' => $now],

            // Laboratory
            ['name' => 'laboratory.order', 'display_name' => 'Membuat Order Laboratorium', 'group' => 'Laboratorium', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'laboratory.record', 'display_name' => 'Memasukkan Hasil Laboratorium', 'group' => 'Laboratorium', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'laboratory.verify', 'display_name' => 'Verifikasi Hasil Laboratorium', 'group' => 'Laboratorium', 'created_at' => $now, 'updated_at' => $now],

            // Billing & Cashier
            ['name' => 'billing.view', 'display_name' => 'Melihat Tagihan & Kuitansi', 'group' => 'Kasir', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.pay', 'display_name' => 'Menerima Pembayaran Kasir', 'group' => 'Kasir', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.discount', 'display_name' => 'Memberikan Diskon Tagihan', 'group' => 'Kasir', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'billing.refund', 'display_name' => 'Memproses Refund Pembayaran', 'group' => 'Kasir', 'created_at' => $now, 'updated_at' => $now],

            // Medical Documents
            ['name' => 'documents.generate', 'display_name' => 'Menerbitkan Surat Medis', 'group' => 'Dokumen', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'documents.upload', 'display_name' => 'Mengunggah Berkas Penunjang', 'group' => 'Dokumen', 'created_at' => $now, 'updated_at' => $now],

            // Reports & Analytics
            ['name' => 'reports.financial', 'display_name' => 'Laporan Pendapatan & Kasir', 'group' => 'Laporan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reports.clinical', 'display_name' => 'Laporan Morbiditas & Kinerja Dokter', 'group' => 'Laporan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'reports.operational', 'display_name' => 'Laporan Kunjungan & Stok', 'group' => 'Laporan', 'created_at' => $now, 'updated_at' => $now],

            // Administration & Management
            ['name' => 'users.manage', 'display_name' => 'Mengelola Akun & Pegawai', 'group' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'tariffs.manage', 'display_name' => 'Mengelola Tarif & Jasa Medis', 'group' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'schedules.manage', 'display_name' => 'Mengelola Jadwal Dokter', 'group' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'clinic.settings', 'display_name' => 'Konfigurasi Profil Klinik', 'group' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'audit_logs.view', 'display_name' => 'Melihat Audit Trail Keamanan', 'group' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'fhir.manage', 'display_name' => 'Manajemen Kesiapan SATUSEHAT/FHIR', 'group' => 'Manajemen', 'created_at' => $now, 'updated_at' => $now],
        ];

        Permission::upsert($permissions, ['name'], ['display_name', 'group', 'updated_at']);

        // 2. Define the 5 Primary Roles (Bulk inserted without loops)
        $roles = [
            [
                'name' => 'admisi_kasir',
                'display_name' => 'Petugas Admisi & Kasir',
                'description' => 'Mengelola pendaftaran pasien, registrasi kunjungan, tagihan dan pembayaran kasir. Dibatasi dari akses SOAP rekam medis.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'perawat_bidan',
                'display_name' => 'Perawat atau Bidan',
                'description' => 'Melakukan pengkajian awal, pemeriksaan tanda-tanda vital, antropometri, skrining, riwayat alergi, dan pelayanan KIA.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'dokter',
                'display_name' => 'Dokter Pemeriksa',
                'description' => 'Memiliki kewenangan klinis penuh: SOAP, diagnosis ICD-10, tindakan ICD-9-CM, odontogram gigi, e-resep, permintaan lab, finalisasi RME.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'farmasi',
                'display_name' => 'Apoteker / TTK',
                'description' => 'Mengelola telaah resep masuk, dispensing obat FEFO, kartu stok, batch, kedaluwarsa, penerimaan barang dan stock opname.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'kepala_klinik',
                'display_name' => 'Kepala Klinik / Manajer',
                'description' => 'Memiliki kewenangan manajerial dan supervisi klinis menyeluruh di seluruh unit klinik.',
                'is_system' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        Role::upsert($roles, ['name'], ['display_name', 'description', 'is_system', 'updated_at']);

        // 3. Connect Permissions to Roles without loops
        $roleMap = Role::pluck('id', 'name');
        $permMap = Permission::pluck('id', 'name');

        // Admisi & Kasir permissions
        $admisiPerms = [
            'patients.view', 'patients.create', 'patients.update', 'patients.card.print',
            'visits.view', 'visits.register', 'visits.cancel',
            'billing.view', 'billing.pay',
            'reports.financial',
        ];
        $roleAdmisi = Role::find($roleMap['admisi_kasir']);
        $roleAdmisi->permissions()->sync(array_values(array_intersect_key($permMap->all(), array_flip($admisiPerms))));

        // Perawat & Bidan permissions
        $perawatPerms = [
            'patients.view', 'patients.card.print',
            'visits.view',
            'nursing.assessment', 'vital_signs.record',
            'kia.manage',
            'documents.upload',
        ];
        $rolePerawat = Role::find($roleMap['perawat_bidan']);
        $rolePerawat->permissions()->sync(array_values(array_intersect_key($permMap->all(), array_flip($perawatPerms))));

        // Dokter permissions
        $dokterPerms = [
            'patients.view',
            'visits.view',
            'medical_records.view', 'medical_records.create', 'medical_records.finalize', 'medical_records.addendum',
            'odontogram.manage', 'kia.manage',
            'prescriptions.create',
            'laboratory.order',
            'documents.generate', 'documents.upload',
            'reports.clinical',
        ];
        $roleDokter = Role::find($roleMap['dokter']);
        $roleDokter->permissions()->sync(array_values(array_intersect_key($permMap->all(), array_flip($dokterPerms))));

        // Farmasi permissions
        $farmasiPerms = [
            'patients.view',
            'visits.view',
            'prescriptions.verify', 'pharmacy.dispense',
            'pharmacy.stock.view', 'pharmacy.stock.manage', 'pharmacy.opname',
            'reports.operational',
        ];
        $roleFarmasi = Role::find($roleMap['farmasi']);
        $roleFarmasi->permissions()->sync(array_values(array_intersect_key($permMap->all(), array_flip($farmasiPerms))));

        // Kepala Klinik permissions (All permissions)
        $roleKepala = Role::find($roleMap['kepala_klinik']);
        $roleKepala->permissions()->sync($permMap->values()->all());
    }
}
