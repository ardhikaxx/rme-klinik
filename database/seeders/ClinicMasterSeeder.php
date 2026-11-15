<?php

namespace Database\Seeders;

use App\Models\Clinic;
use App\Models\ClinicSetting;
use App\Models\Department;
use App\Models\Laboratory;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\MedicineStockMovement;
use App\Models\MedicineUnit;
use App\Models\PaymentMethod;
use App\Models\Polyclinic;
use App\Models\ServiceTariff;
use App\Models\Shift;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ClinicMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clinic Entity
        $clinic = Clinic::firstOrCreate(
            ['code' => 'KPSM-MLG'],
            [
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
            ]
        );

        // 2. Settings
        $settings = [
            'clinic_name' => $clinic->name,
            'clinic_address' => $clinic->address,
            'clinic_phone' => $clinic->phone,
            'mrn_prefix' => 'RM',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
            'satusehat_env' => 'staging',
            'satusehat_org_id' => '10000004',
        ];
        foreach ($settings as $k => $v) {
            ClinicSetting::firstOrCreate(['key' => $k], ['value' => $v, 'clinic_id' => $clinic->id]);
        }

        // 3. Departments
        $departments = [
            ['code' => 'MEDIS', 'name' => 'Pelayanan Medis'],
            ['code' => 'KEP', 'name' => 'Keperawatan & Kebidanan'],
            ['code' => 'FARM', 'name' => 'Instalasi Farmasi'],
            ['code' => 'LAB', 'name' => 'Instalasi Laboratorium'],
            ['code' => 'ADM', 'name' => 'Admisi & Kasir'],
            ['code' => 'MGT', 'name' => 'Manajemen Operasional'],
        ];
        foreach ($departments as $d) {
            Department::firstOrCreate(['code' => $d['code']], $d);
        }

        // 4. Polyclinics
        $polyclinics = [
            ['code' => 'POLI-UMUM', 'name' => 'Poli Umum', 'type' => 'umum', 'description' => 'Pelayanan kesehatan umum dan rawat jalan dokter umum.'],
            ['code' => 'POLI-GIGI', 'name' => 'Poli Gigi', 'type' => 'gigi', 'description' => 'Pemeriksaan kesehatan gigi, mulut, odontogram, dan tindakan gigi.'],
            ['code' => 'POLI-KIA', 'name' => 'Poli KIA & KB', 'type' => 'kia', 'description' => 'Kesehatan ibu dan anak, ANC ibu hamil, KB, dan tumbuh kembang balita.'],
        ];
        foreach ($polyclinics as $p) {
            Polyclinic::firstOrCreate(['code' => $p['code']], $p);
        }

        // 5. Shifts
        $shifts = [
            ['name' => 'Shift Pagi', 'start_time' => '07:30:00', 'end_time' => '14:00:00'],
            ['name' => 'Shift Siang', 'start_time' => '14:00:00', 'end_time' => '20:30:00'],
        ];
        foreach ($shifts as $s) {
            Shift::firstOrCreate(['name' => $s['name']], $s);
        }

        // 6. Payment Methods
        $paymentMethods = [
            ['code' => 'cash', 'name' => 'Tunai (Cash)', 'account_info' => 'Kasir Tunai'],
            ['code' => 'qris', 'name' => 'QRIS Dinamis', 'account_info' => 'NMID: ID1020304050607'],
            ['code' => 'debit', 'name' => 'Kartu Debit / EDC', 'account_info' => 'EDC Bank Mandiri / BCA'],
            ['code' => 'transfer_bca', 'name' => 'Transfer Bank BCA', 'account_info' => 'No Rek: 011-234-5678 a.n. Klinik Sehat Mandiri'],
            ['code' => 'transfer_mandiri', 'name' => 'Transfer Bank Mandiri', 'account_info' => 'No Rek: 144-00-9876543-2 a.n. Klinik Sehat Mandiri'],
        ];
        foreach ($paymentMethods as $pm) {
            PaymentMethod::firstOrCreate(['code' => $pm['code']], $pm);
        }

        // 7. Suppliers
        $suppliers = [
            ['code' => 'SUP-KF', 'name' => 'PT Kimia Farma Trading & Dist Malang', 'contact_person' => 'Bambang Sutrisno', 'phone' => '0341-491234', 'email' => 'malang@kimiafarma.co.id', 'address' => 'Jl. Sunandar Priyo Sudarmo No. 12, Malang'],
            ['code' => 'SUP-ENS', 'name' => 'PT Enseval Putera Megatrading Malang', 'contact_person' => 'Dwi Handayani', 'phone' => '0341-475678', 'email' => 'sales.mlg@enseval.com', 'address' => 'Kawasan Industri Singosari Kav. 4, Malang'],
            ['code' => 'SUP-KAL', 'name' => 'PT Kalbe Farma Malang Cabang', 'contact_person' => 'Hendra Setiawan', 'phone' => '0341-401122', 'email' => 'dist.mlg@kalbe.co.id', 'address' => 'Jl. Raya Karanglo No. 88, Malang'],
        ];
        foreach ($suppliers as $sup) {
            Supplier::firstOrCreate(['code' => $sup['code']], $sup);
        }

        // 8. Medicine Categories
        $categories = [
            ['name' => 'Analgesik & Antipiretik', 'description' => 'Obat penurun demam dan pereda nyeri'],
            ['name' => 'Antibiotik', 'description' => 'Obat anti-bakteri dan infeksi'],
            ['name' => 'Antihistamin & Anti-alergi', 'description' => 'Pereda reaksi alergi, gatal, rhinitis'],
            ['name' => 'Obat Saluran Cerna', 'description' => 'Antasida, PPI, anti-diare, anti-mual'],
            ['name' => 'Obat Saluran Pernafasan', 'description' => 'Bronkodilator, ekspektoran, mukolitik'],
            ['name' => 'Vitamin & Suplemen', 'description' => 'Suplemen multivitamin dan daya tahan tubuh'],
            ['name' => 'Kortikosteroid', 'description' => 'Anti-inflamasi steroid'],
        ];
        foreach ($categories as $cat) {
            MedicineCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // 9. Medicine Units
        $units = [
            ['code' => 'TAB', 'name' => 'Tablet'],
            ['code' => 'KAP', 'name' => 'Kapsul'],
            ['code' => 'BOT', 'name' => 'Botol (Sirup)'],
            ['code' => 'AMP', 'name' => 'Ampul'],
            ['code' => 'TBN', 'name' => 'Tube (Salep)'],
            ['code' => 'STR', 'name' => 'Strip'],
            ['code' => 'BKS', 'name' => 'Bungkus'],
        ];
        foreach ($units as $u) {
            MedicineUnit::firstOrCreate(['code' => $u['code']], $u);
        }

        // 10. Master Medicines & Initial Batches
        $catAnalgesik = MedicineCategory::where('name', 'Analgesik & Antipiretik')->first();
        $catAntibiotik = MedicineCategory::where('name', 'Antibiotik')->first();
        $catAlergi = MedicineCategory::where('name', 'Antihistamin & Anti-alergi')->first();
        $catCerna = MedicineCategory::where('name', 'Obat Saluran Cerna')->first();
        $catNafas = MedicineCategory::where('name', 'Obat Saluran Pernafasan')->first();
        $catVitamin = MedicineCategory::where('name', 'Vitamin & Suplemen')->first();

        $unitTab = MedicineUnit::where('code', 'TAB')->first();
        $unitKap = MedicineUnit::where('code', 'KAP')->first();
        $unitBot = MedicineUnit::where('code', 'BOT')->first();

        $kfSupplier = Supplier::where('code', 'SUP-KF')->first();

        $medicinesList = [
            [
                'code' => 'MED-001',
                'generic_name' => 'Paracetamol',
                'brand_name' => 'Paracetamol 500 mg',
                'category_id' => $catAnalgesik->id,
                'unit_id' => $unitTab->id,
                'dosage_form' => 'Tablet',
                'strength' => '500 mg',
                'purchase_price' => 350,
                'selling_price' => 700,
                'min_stock' => 100,
                'batch' => 'BATCH-PCT-2601',
                'expiry' => Carbon::today()->addMonths(18),
                'stock' => 500,
            ],
            [
                'code' => 'MED-002',
                'generic_name' => 'Amoxicillin Trihydrate',
                'brand_name' => 'Amoxicillin 500 mg',
                'category_id' => $catAntibiotik->id,
                'unit_id' => $unitKap->id,
                'dosage_form' => 'Kapsul',
                'strength' => '500 mg',
                'purchase_price' => 750,
                'selling_price' => 1500,
                'min_stock' => 50,
                'batch' => 'BATCH-AMX-2602',
                'expiry' => Carbon::today()->addMonths(14),
                'stock' => 300,
            ],
            [
                'code' => 'MED-003',
                'generic_name' => 'Cetirizine HCl',
                'brand_name' => 'Cetirizine 10 mg',
                'category_id' => $catAlergi->id,
                'unit_id' => $unitTab->id,
                'dosage_form' => 'Tablet',
                'strength' => '10 mg',
                'purchase_price' => 450,
                'selling_price' => 1000,
                'min_stock' => 50,
                'batch' => 'BATCH-CTZ-2603',
                'expiry' => Carbon::today()->addMonths(20),
                'stock' => 250,
            ],
            [
                'code' => 'MED-004',
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Ibuprofen 400 mg',
                'category_id' => $catAnalgesik->id,
                'unit_id' => $unitTab->id,
                'dosage_form' => 'Tablet',
                'strength' => '400 mg',
                'purchase_price' => 500,
                'selling_price' => 1200,
                'min_stock' => 50,
                'batch' => 'BATCH-IBU-2604',
                'expiry' => Carbon::today()->addMonths(16),
                'stock' => 200,
            ],
            [
                'code' => 'MED-005',
                'generic_name' => 'Omeprazole',
                'brand_name' => 'Omeprazole 20 mg',
                'category_id' => $catCerna->id,
                'unit_id' => $unitKap->id,
                'dosage_form' => 'Kapsul',
                'strength' => '20 mg',
                'purchase_price' => 900,
                'selling_price' => 2000,
                'min_stock' => 40,
                'batch' => 'BATCH-OMZ-2605',
                'expiry' => Carbon::today()->addMonths(12),
                'stock' => 200,
            ],
            [
                'code' => 'MED-006',
                'generic_name' => 'Antasida DOEN',
                'brand_name' => 'Antasida DOEN Suspensi',
                'category_id' => $catCerna->id,
                'unit_id' => $unitBot->id,
                'dosage_form' => 'Sirup',
                'strength' => '60 ml',
                'purchase_price' => 6000,
                'selling_price' => 10000,
                'min_stock' => 15,
                'batch' => 'BATCH-ANT-2606',
                'expiry' => Carbon::today()->addMonths(10),
                'stock' => 40,
            ],
            [
                'code' => 'MED-007',
                'generic_name' => 'Asam Askorbat',
                'brand_name' => 'Vitamin C 500 mg',
                'category_id' => $catVitamin->id,
                'unit_id' => $unitTab->id,
                'dosage_form' => 'Tablet',
                'strength' => '500 mg',
                'purchase_price' => 300,
                'selling_price' => 600,
                'min_stock' => 100,
                'batch' => 'BATCH-VTC-2607',
                'expiry' => Carbon::today()->addMonths(22),
                'stock' => 600,
            ],
            [
                'code' => 'MED-008',
                'generic_name' => 'Salbutamol Sulfat',
                'brand_name' => 'Salbutamol 2 mg',
                'category_id' => $catNafas->id,
                'unit_id' => $unitTab->id,
                'dosage_form' => 'Tablet',
                'strength' => '2 mg',
                'purchase_price' => 250,
                'selling_price' => 600,
                'min_stock' => 30,
                'batch' => 'BATCH-SBM-2608',
                'expiry' => Carbon::today()->addMonths(15),
                'stock' => 150,
            ],
            [
                'code' => 'MED-009',
                'generic_name' => 'Asam Mefenamat',
                'brand_name' => 'Asam Mefenamat 500 mg',
                'category_id' => $catAnalgesik->id,
                'unit_id' => $unitTab->id,
                'dosage_form' => 'Tablet',
                'strength' => '500 mg',
                'purchase_price' => 450,
                'selling_price' => 1000,
                'min_stock' => 50,
                'batch' => 'BATCH-AMF-2609',
                'expiry' => Carbon::today()->addMonths(18),
                'stock' => 250,
            ],
        ];

        foreach ($medicinesList as $med) {
            $batchNum = $med['batch'];
            $exp = $med['expiry'];
            $initialStock = $med['stock'];
            unset($med['batch'], $med['expiry'], $med['stock']);

            $medicine = Medicine::firstOrCreate(['code' => $med['code']], $med);

            $batch = MedicineBatch::firstOrCreate(
                [
                    'medicine_id' => $medicine->id,
                    'batch_number' => $batchNum,
                ],
                [
                    'expiry_date' => $exp,
                    'current_stock' => $initialStock,
                    'purchase_price' => $med['purchase_price'],
                    'supplier_id' => $kfSupplier->id,
                ]
            );

            // Log stock movement for initial inventory setup
            if ($batch->wasRecentlyCreated) {
                MedicineStockMovement::create([
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'receipt',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'reference_type' => 'InitialSeeder',
                    'reference_id' => $batch->id,
                    'notes' => 'Saldo awal stok farmasi',
                    'created_by' => null,
                ]);
            }
        }

        // 11. Laboratories Master
        $labs = [
            [
                'code' => 'LAB-DL',
                'name' => 'Darah Lengkap (CBC)',
                'category' => 'hematologi',
                'sample_type' => 'darah',
                'unit' => 'panel',
                'reference_range' => 'Hb: 12-16 g/dL, Leu: 4000-10000 /uL, Plt: 150000-450000 /uL',
                'critical_low' => null,
                'critical_high' => null,
                'tariff' => 65000,
            ],
            [
                'code' => 'LAB-GDS',
                'name' => 'Glukosa Darah Sewaktu (GDS)',
                'category' => 'kimia_darah',
                'sample_type' => 'darah',
                'unit' => 'mg/dL',
                'reference_range' => '70 - 140 mg/dL',
                'critical_low' => 50,
                'critical_high' => 400,
                'tariff' => 25000,
            ],
            [
                'code' => 'LAB-CHOL',
                'name' => 'Kolesterol Total',
                'category' => 'kimia_darah',
                'sample_type' => 'darah',
                'unit' => 'mg/dL',
                'reference_range' => '< 200 mg/dL',
                'critical_low' => null,
                'critical_high' => 300,
                'tariff' => 35000,
            ],
            [
                'code' => 'LAB-UA',
                'name' => 'Asam Urat (Uric Acid)',
                'category' => 'kimia_darah',
                'sample_type' => 'darah',
                'unit' => 'mg/dL',
                'reference_range' => 'Pria: 3.4-7.0, Wanita: 2.4-5.7 mg/dL',
                'critical_low' => null,
                'critical_high' => 12,
                'tariff' => 30000,
            ],
            [
                'code' => 'LAB-UL',
                'name' => 'Urin Rutin Lengkap',
                'category' => 'urinalisis',
                'sample_type' => 'urin',
                'unit' => 'analisis',
                'reference_range' => 'pH: 4.5-8.0, Bj: 1.005-1.030, Protein (-)',
                'critical_low' => null,
                'critical_high' => null,
                'tariff' => 40000,
            ],
            [
                'code' => 'LAB-WIDAL',
                'name' => 'Uji Widal (Tifoid)',
                'category' => 'imunologi',
                'sample_type' => 'darah',
                'unit' => 'titer',
                'reference_range' => 'Titer < 1/80 Negatif',
                'critical_low' => null,
                'critical_high' => null,
                'tariff' => 45000,
            ],
        ];
        foreach ($labs as $lab) {
            Laboratory::firstOrCreate(['code' => $lab['code']], $lab);
        }

        // 12. Service Tariffs
        $polyUmum = Polyclinic::where('code', 'POLI-UMUM')->first();
        $polyGigi = Polyclinic::where('code', 'POLI-GIGI')->first();
        $polyKia = Polyclinic::where('code', 'POLI-KIA')->first();

        $tariffs = [
            [
                'code' => 'TRF-ADM',
                'name' => 'Biaya Administrasi & Registrasi Pasien',
                'category' => 'administrasi',
                'polyclinic_id' => null,
                'tariff' => 15000,
                'doctor_fee' => 0,
                'nurse_fee' => 0,
                'clinic_fee' => 15000,
            ],
            [
                'code' => 'TRF-KONS-UMUM',
                'name' => 'Konsultasi & Pemeriksaan Dokter Umum',
                'category' => 'konsultasi',
                'polyclinic_id' => $polyUmum->id,
                'tariff' => 40000,
                'doctor_fee' => 25000,
                'nurse_fee' => 5000,
                'clinic_fee' => 10000,
            ],
            [
                'code' => 'TRF-KONS-GIGI',
                'name' => 'Konsultasi & Pemeriksaan Dokter Gigi',
                'category' => 'konsultasi',
                'polyclinic_id' => $polyGigi->id,
                'tariff' => 50000,
                'doctor_fee' => 35000,
                'nurse_fee' => 5000,
                'clinic_fee' => 10000,
            ],
            [
                'code' => 'TRF-KONS-KIA',
                'name' => 'Konsultasi & Pemeriksaan KIA / Bidan',
                'category' => 'konsultasi',
                'polyclinic_id' => $polyKia->id,
                'tariff' => 35000,
                'doctor_fee' => 20000,
                'nurse_fee' => 5000,
                'clinic_fee' => 10000,
            ],
            [
                'code' => 'TRF-TND-NEB',
                'name' => 'Tindakan Nebulisasi',
                'category' => 'tindakan_medis',
                'polyclinic_id' => $polyUmum->id,
                'icd9cm_code' => '93.94',
                'tariff' => 45000,
                'doctor_fee' => 25000,
                'nurse_fee' => 10000,
                'clinic_fee' => 10000,
            ],
            [
                'code' => 'TRF-TND-JAHIT',
                'name' => 'Penjahitan Luka (Hecting)',
                'category' => 'tindakan_medis',
                'polyclinic_id' => $polyUmum->id,
                'icd9cm_code' => '86.59',
                'tariff' => 60000,
                'doctor_fee' => 40000,
                'nurse_fee' => 10000,
                'clinic_fee' => 10000,
            ],
            [
                'code' => 'TRF-TND-TAMBAL',
                'name' => 'Penambalan Gigi Komposit',
                'category' => 'tindakan_medis',
                'polyclinic_id' => $polyGigi->id,
                'icd9cm_code' => '23.2',
                'tariff' => 120000,
                'doctor_fee' => 85000,
                'nurse_fee' => 10000,
                'clinic_fee' => 25000,
            ],
            [
                'code' => 'TRF-TND-SCALING',
                'name' => 'Pembersihan Karang Gigi (Scaling)',
                'category' => 'tindakan_medis',
                'polyclinic_id' => $polyGigi->id,
                'icd9cm_code' => '96.54',
                'tariff' => 150000,
                'doctor_fee' => 100000,
                'nurse_fee' => 15000,
                'clinic_fee' => 35000,
            ],
            [
                'code' => 'TRF-TND-CABUT-SUSU',
                'name' => 'Pencabutan Gigi Susu',
                'category' => 'tindakan_medis',
                'polyclinic_id' => $polyGigi->id,
                'icd9cm_code' => '23.01',
                'tariff' => 75000,
                'doctor_fee' => 50000,
                'nurse_fee' => 10000,
                'clinic_fee' => 15000,
            ],
            [
                'code' => 'TRF-TND-ANC',
                'name' => 'Pemeriksaan Kehamilan (ANC Terpadu)',
                'category' => 'tindakan_medis',
                'polyclinic_id' => $polyKia->id,
                'icd9cm_code' => '88.78',
                'tariff' => 50000,
                'doctor_fee' => 35000,
                'nurse_fee' => 5000,
                'clinic_fee' => 10000,
            ],
            [
                'code' => 'TRF-TND-IMUNISASI',
                'name' => 'Tindakan Imunisasi Balita',
                'category' => 'tindakan_keperawatan',
                'polyclinic_id' => $polyKia->id,
                'icd9cm_code' => '99.39',
                'tariff' => 35000,
                'doctor_fee' => 0,
                'nurse_fee' => 20000,
                'clinic_fee' => 15000,
            ],
        ];

        foreach ($tariffs as $trf) {
            ServiceTariff::firstOrCreate(['code' => $trf['code']], $trf);
        }
    }
}
