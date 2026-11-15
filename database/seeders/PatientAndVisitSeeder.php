<?php

namespace Database\Seeders;

use App\Models\BillingInvoice;
use App\Models\DispensingTransaction;
use App\Models\Employee;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\NursingAssessment;
use App\Models\Odontogram;
use App\Models\OdontogramTooth;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Polyclinic;
use App\Models\Prescription;
use App\Models\ServiceTariff;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientAndVisitSeeder extends Seeder
{
    public function run(): void
    {
        $admisiUser = User::where('username', 'admisi')->first();
        $perawatUser = User::where('username', 'perawat')->first();
        $dokterUser = User::where('username', 'dokter')->first();
        $farmasiUser = User::where('username', 'farmasi')->first();

        $empPerawat = Employee::where('profession', 'perawat')->first();
        $empDokter = Employee::where('employee_number', 'EMP-DOC-001')->first();
        $empDokterGigi = Employee::where('employee_number', 'EMP-DEN-001')->first();

        $polyUmum = Polyclinic::where('code', 'POLI-UMUM')->first();
        $polyGigi = Polyclinic::where('code', 'POLI-GIGI')->first();
        $polyKia = Polyclinic::where('code', 'POLI-KIA')->first();

        $cashMethod = PaymentMethod::where('code', 'cash')->first();
        $qrisMethod = PaymentMethod::where('code', 'qris')->first();

        $adminTariff = ServiceTariff::where('category', 'administrasi')->first();
        $konsUmumTariff = ServiceTariff::where('code', 'TRF-KONS-UMUM')->first();
        $konsGigiTariff = ServiceTariff::where('code', 'TRF-KONS-GIGI')->first();

        // 1. Realistic Patient Profiles (Malang City, synthetic NIKs)
        $patientsData = [
            [
                'nik' => '3573011508850001',
                'name' => 'Bambang Suryono',
                'place_of_birth' => 'Malang',
                'date_of_birth' => '1985-08-15',
                'gender' => 'L',
                'blood_type' => 'O',
                'address' => 'Jl. Bendungan Sigura-gura No. 18, RT 02 RW 05',
                'rt' => '02', 'rw' => '05',
                'kelurahan' => 'Sumbersari', 'kecamatan' => 'Lowokwaru',
                'phone' => '081234500001',
                'occupation' => 'Wiraswasta',
                'marital_status' => 'kawin',
                'allergies' => [
                    ['type' => 'obat', 'name' => 'Amoxicillin', 'reaction' => 'Gatal kemerahan di kulit', 'severity' => 'sedang'],
                ],
                'contact' => ['name' => 'Endang Lestari', 'rel' => 'Istri', 'phone' => '081234500011'],
            ],
            [
                'nik' => '3573024503920002',
                'name' => 'Ratna Dewi Kusuma',
                'place_of_birth' => 'Surabaya',
                'date_of_birth' => '1992-03-25',
                'gender' => 'P',
                'blood_type' => 'A',
                'address' => 'Jl. Terusan Dieng No. 42, RT 04 RW 02',
                'rt' => '04', 'rw' => '02',
                'kelurahan' => 'Pisang Candi', 'kecamatan' => 'Sukun',
                'phone' => '081234500002',
                'occupation' => 'Guru',
                'marital_status' => 'kawin',
                'allergies' => [],
                'contact' => ['name' => 'Agus Priyanto', 'rel' => 'Suami', 'phone' => '081234500022'],
            ],
            [
                'nik' => '3573031011980003',
                'name' => 'Dimas Arya Putra',
                'place_of_birth' => 'Malang',
                'date_of_birth' => '1998-11-10',
                'gender' => 'L',
                'blood_type' => 'B',
                'address' => 'Jl. Danau Ranau Raya G7/D12, RT 01 RW 09',
                'rt' => '01', 'rw' => '09',
                'kelurahan' => 'Sawojajar', 'kecamatan' => 'Kedungkandang',
                'phone' => '081234500003',
                'occupation' => 'Karyawan Swasta',
                'marital_status' => 'belum_kawin',
                'allergies' => [
                    ['type' => 'makanan', 'name' => 'Udang & Kepiting', 'reaction' => 'Bengkak pada bibir dan biduran', 'severity' => 'berat'],
                ],
                'contact' => ['name' => 'Sri Hartini', 'rel' => 'Ibu Kandung', 'phone' => '081234500033'],
            ],
            [
                'nik' => '3573046006760004',
                'name' => 'Indah Wahyuni',
                'place_of_birth' => 'Blitar',
                'date_of_birth' => '1976-06-20',
                'gender' => 'P',
                'blood_type' => 'AB',
                'address' => 'Jl. Borobudur Agung No. 5, RT 03 RW 08',
                'rt' => '03', 'rw' => '08',
                'kelurahan' => 'Mojolangu', 'kecamatan' => 'Lowokwaru',
                'phone' => '081234500004',
                'occupation' => 'Ibu Rumah Tangga',
                'marital_status' => 'kawin',
                'allergies' => [],
                'contact' => ['name' => 'Supriadi', 'rel' => 'Suami', 'phone' => '081234500044'],
            ],
            [
                'nik' => '3573050101030005',
                'name' => 'Rizky Ramadhan',
                'place_of_birth' => 'Malang',
                'date_of_birth' => '2003-01-01',
                'gender' => 'L',
                'blood_type' => 'O',
                'address' => 'Jl. Kawi No. 15, RT 01 RW 03',
                'rt' => '01', 'rw' => '03',
                'kelurahan' => 'Bareng', 'kecamatan' => 'Klojen',
                'phone' => '081234500005',
                'occupation' => 'Mahasiswa',
                'marital_status' => 'belum_kawin',
                'allergies' => [],
                'contact' => ['name' => 'Hadi Sucipto', 'rel' => 'Ayah', 'phone' => '081234500055'],
            ],
        ];

        $createdPatients = [];
        foreach ($patientsData as $pData) {
            $allergies = $pData['allergies'];
            $contact = $pData['contact'];
            unset($pData['allergies'], $pData['contact']);

            $existing = Patient::where('nik', $pData['nik'])->first();
            if (! $existing) {
                $pData['medical_record_number'] = Patient::generateNextMrn();
                $pData['created_by'] = $admisiUser->id;
                $patient = Patient::create($pData);

                foreach ($allergies as $alg) {
                    $patient->allergies()->create([
                        'allergen_type' => $alg['type'],
                        'allergen_name' => $alg['name'],
                        'reaction' => $alg['reaction'],
                        'severity' => $alg['severity'],
                        'created_by' => $admisiUser->id,
                    ]);
                }

                if (! empty($contact)) {
                    $patient->emergencyContacts()->create([
                        'name' => $contact['name'],
                        'relationship' => $contact['rel'],
                        'phone' => $contact['phone'],
                    ]);
                }
            } else {
                $patient = $existing;
            }

            $createdPatients[] = $patient;
        }

        // 2. Realistic Complete Visit Flow 1: Bambang Suryono (Poli Umum, ISPA/Demam)
        $p1 = $createdPatients[0];
        $v1Date = Carbon::now()->subHours(3);

        $v1 = Visit::firstOrCreate(
            ['visit_number' => 'REG-'.$v1Date->format('Ymd').'-0001'],
            [
                'patient_id' => $p1->id,
                'polyclinic_id' => $polyUmum->id,
                'doctor_id' => $empDokter->id,
                'registered_by' => $admisiUser->id,
                'registration_date' => $v1Date,
                'visit_type' => 'baru',
                'payment_type' => 'umum',
                'status' => 'selesai',
                'queue_number' => 'UM-01',
                'notes' => 'Pasien mengeluh demam dan pusing 2 hari',
            ]
        );

        // Nurse Assessment for Visit 1
        $weight1 = 68.0;
        $height1 = 170.0;
        $bmi1 = NursingAssessment::calculateBmi($weight1, $height1);
        NursingAssessment::firstOrCreate(
            ['visit_id' => $v1->id],
            [
                'patient_id' => $p1->id,
                'nurse_id' => $empPerawat->id,
                'assessment_time' => $v1Date->copy()->addMinutes(10),
                'chief_complaint' => 'Demam menggigil sejak 2 hari yang lalu, sakit kepala berdenyut, dan badan pegal-pegal.',
                'current_illness_history' => 'Keluhan demam timbul mendadak terutama pada sore dan malam hari, sudah minum obat warung tetapi belum mereda.',
                'past_illness_history' => 'Riwayat maag sesekali.',
                'family_illness_history' => 'Tidak ada riwayat hipertensi atau diabetes.',
                'systolic' => 120,
                'diastolic' => 80,
                'temperature' => 38.4,
                'pulse_rate' => 88,
                'respiratory_rate' => 20,
                'oxygen_saturation' => 98.0,
                'weight' => $weight1,
                'height' => $height1,
                'bmi' => $bmi1,
                'consciousness' => 'compos_mentis',
                'pain_scale' => 3,
                'fall_risk' => 'rendah',
            ]
        );

        // Medical Record SOAP for Visit 1
        $mr1 = MedicalRecord::firstOrCreate(
            ['visit_id' => $v1->id],
            [
                'patient_id' => $p1->id,
                'doctor_id' => $empDokter->id,
                'examination_time' => $v1Date->copy()->addMinutes(25),
                'subjective' => 'Pasien mengeluh demam sejak 2 hari yang lalu, badan lemas, meriang, nafsu makan menurun. Tidak ada batuk pilek hebat.',
                'objective' => 'Keadaan umum: Tampak sakit sedang, compos mentis. Suhu: 38.4 C, TD: 120/80 mmHg, Nadi: 88x/menit, RR: 20x/menit. Faring hiperemis (-), tonsil T1-T1 tenang. Cor/Pulmo: Dalam batas normal. Abdomen: Supel, nyeri tekan epigastrium ringan (-). Ekstremitas: Akral hangat, CRT < 2 detik.',
                'assessment' => 'Febris Akut Hari Ke-2 ec Suspek Infeksi Virus / Observasi Febris',
                'plan' => 'Terapi simptomatik antipiretik, hidrasi oral cukup (minimal 2 liter/hari), istirahat tirah baring. Bila demam menetap hari ke-3 dianjurkan cek darah perifer lengkap.',
                'status' => 'final',
                'finalized_at' => $v1Date->copy()->addMinutes(35),
                'finalized_by' => $dokterUser->id,
                'education_plan' => 'Edukasi kompres air hangat, minum air yang cukup, kontrol bila demam tidak turun dalam 3 hari atau muncul tanda dehidrasi.',
                'next_visit_date' => Carbon::today()->addDays(3),
            ]
        );

        $mr1->diagnoses()->firstOrCreate(
            ['icd10_code' => 'R50.9'],
            ['description' => 'Fever, unspecified', 'type' => 'primary']
        );

        // Prescription for Visit 1: Paracetamol + Vitamin C
        $rx1 = Prescription::firstOrCreate(
            ['prescription_number' => 'RX-'.$v1Date->format('Ymd').'-0001'],
            [
                'visit_id' => $v1->id,
                'medical_record_id' => $mr1->id,
                'doctor_id' => $empDokter->id,
                'prescription_date' => $v1Date->copy()->addMinutes(30),
                'status' => 'selesai',
                'doctor_notes' => 'Pasien alergi Amoxicillin (HINDARI PENISILIN)',
                'verified_by' => $farmasiUser->id,
                'verified_at' => $v1Date->copy()->addMinutes(40),
                'dispensed_at' => $v1Date->copy()->addMinutes(50),
            ]
        );

        $medPct = Medicine::where('code', 'MED-001')->first();
        $medVitC = Medicine::where('code', 'MED-007')->first();

        $rxItem1 = $rx1->items()->firstOrCreate(
            ['medicine_id' => $medPct->id],
            [
                'quantity' => 10,
                'dosage' => '500 mg',
                'frequency' => '3x1 hari',
                'route' => 'oral',
                'duration_days' => 3,
                'instructions' => 'Bila demam atau nyeri, sesudah makan',
            ]
        );

        $rxItem2 = $rx1->items()->firstOrCreate(
            ['medicine_id' => $medVitC->id],
            [
                'quantity' => 10,
                'dosage' => '500 mg',
                'frequency' => '1x1 hari',
                'route' => 'oral',
                'duration_days' => 10,
                'instructions' => 'Sesudah makan pagi',
            ]
        );

        // Dispensing Transaction for Visit 1
        $dsp1 = DispensingTransaction::firstOrCreate(
            ['dispensing_number' => 'DSP-'.$v1Date->format('Ymd').'-0001'],
            [
                'prescription_id' => $rx1->id,
                'visit_id' => $v1->id,
                'dispensed_by' => $farmasiUser->id,
                'dispensed_at' => $v1Date->copy()->addMinutes(50),
                'total_amount' => (10 * 700) + (10 * 600), // 7000 + 6000 = 13000
                'notes' => 'Telah dilakukan telaah obat dan telaah resep. Edukasi alergi disampaikan.',
            ]
        );

        // Billing Invoice for Visit 1
        $inv1 = BillingInvoice::firstOrCreate(
            ['invoice_number' => 'INV-'.$v1Date->format('Ymd').'-0001'],
            [
                'visit_id' => $v1->id,
                'patient_id' => $p1->id,
                'total_amount' => 15000 + 40000 + 13000, // 68000
                'discount_amount' => 0,
                'final_amount' => 68000,
                'paid_amount' => 68000,
                'balance_amount' => 0,
                'status' => 'lunas',
                'created_by' => $admisiUser->id,
            ]
        );

        $inv1->items()->firstOrCreate(
            ['item_type' => 'administrasi', 'item_code' => 'TRF-ADM'],
            ['item_name' => 'Biaya Administrasi & Registrasi Pasien', 'quantity' => 1, 'unit_price' => 15000, 'subtotal' => 15000]
        );
        $inv1->items()->firstOrCreate(
            ['item_type' => 'konsultasi', 'item_code' => 'TRF-KONS-UMUM'],
            ['item_name' => 'Konsultasi & Pemeriksaan Dokter Umum', 'quantity' => 1, 'unit_price' => 40000, 'subtotal' => 40000]
        );
        $inv1->items()->firstOrCreate(
            ['item_type' => 'obat', 'item_code' => 'MED-001'],
            ['item_name' => 'Paracetamol 500 mg (10 tab)', 'quantity' => 10, 'unit_price' => 700, 'subtotal' => 7000]
        );
        $inv1->items()->firstOrCreate(
            ['item_type' => 'obat', 'item_code' => 'MED-007'],
            ['item_name' => 'Vitamin C 500 mg (10 tab)', 'quantity' => 10, 'unit_price' => 600, 'subtotal' => 6000]
        );

        // Payment for Visit 1
        Payment::firstOrCreate(
            ['receipt_number' => 'KW-'.$v1Date->format('Ymd').'-0001'],
            [
                'billing_invoice_id' => $inv1->id,
                'payment_method_id' => $cashMethod->id,
                'amount_paid' => 68000,
                'amount_tendered' => 70000,
                'change_due' => 2000,
                'payment_time' => $v1Date->copy()->addMinutes(60),
                'cashier_id' => $admisiUser->id,
                'notes' => 'Pembayaran tunai lunas',
            ]
        );

        // 3. Realistic Complete Visit Flow 2: Ratna Dewi Kusuma (Poli Gigi, Odontogram & Penambalan)
        $p2 = $createdPatients[1];
        $v2Date = Carbon::now()->subHours(2);

        $v2 = Visit::firstOrCreate(
            ['visit_number' => 'REG-'.$v2Date->format('Ymd').'-0002'],
            [
                'patient_id' => $p2->id,
                'polyclinic_id' => $polyGigi->id,
                'doctor_id' => $empDokterGigi->id,
                'registered_by' => $admisiUser->id,
                'registration_date' => $v2Date,
                'visit_type' => 'baru',
                'payment_type' => 'umum',
                'status' => 'selesai',
                'queue_number' => 'GG-01',
                'notes' => 'Gigi geraham bawah ngilu saat minum dingin',
            ]
        );

        NursingAssessment::firstOrCreate(
            ['visit_id' => $v2->id],
            [
                'patient_id' => $p2->id,
                'nurse_id' => $empPerawat->id,
                'assessment_time' => $v2Date->copy()->addMinutes(10),
                'chief_complaint' => 'Gigi belakang kanan bawah ngilu saat terkena makanan manis atau minuman dingin sejak 1 minggu.',
                'systolic' => 110,
                'diastolic' => 70,
                'temperature' => 36.6,
                'pulse_rate' => 76,
                'respiratory_rate' => 18,
                'oxygen_saturation' => 99.0,
                'weight' => 54.0,
                'height' => 160.0,
                'bmi' => 21.1,
                'consciousness' => 'compos_mentis',
                'pain_scale' => 2,
                'fall_risk' => 'rendah',
            ]
        );

        // Odontogram Teeth Entry
        $odo = Odontogram::firstOrCreate(
            ['visit_id' => $v2->id],
            [
                'patient_id' => $p2->id,
                'doctor_id' => $empDokterGigi->id,
                'notes' => 'Ditemukan karies media pada gigi 46 bagian oklusal.',
            ]
        );

        OdontogramTooth::firstOrCreate(
            ['odontogram_id' => $odo->id, 'tooth_number' => '46'],
            [
                'condition' => 'filling',
                'surfaces' => 'O',
                'treatment_notes' => 'Kavitas dibersihkan dan dilakukan penambalan komposit light-cure.',
            ]
        );

        OdontogramTooth::firstOrCreate(
            ['odontogram_id' => $odo->id, 'tooth_number' => '38'],
            [
                'condition' => 'missing',
                'treatment_notes' => 'Riwayat pencabutan tahun 2021.',
            ]
        );

        // Medical Record SOAP for Dental Visit
        $mr2 = MedicalRecord::firstOrCreate(
            ['visit_id' => $v2->id],
            [
                'patient_id' => $p2->id,
                'doctor_id' => $empDokterGigi->id,
                'examination_time' => $v2Date->copy()->addMinutes(20),
                'subjective' => 'Pasien mengeluhkan gigi geraham bawah kanan (gigi 46) terasa linu saat makan/minum dingin.',
                'objective' => 'Intraoral: Gigi 46 karies media pada aspek oklusal, sondasi (+), perkusi (-), palpasi (-), mobilitas normal.',
                'assessment' => 'K02.1 - Caries of dentine pada gigi 46',
                'plan' => 'Preparasi kavitas, etsa, bonding, dan restorasi tumpatan resin komposit gigi 46.',
                'status' => 'final',
                'finalized_at' => $v2Date->copy()->addMinutes(45),
                'finalized_by' => $dokterUser->id,
                'education_plan' => 'Instruksi tidak mengunyah makanan keras di sisi kanan selama 24 jam. Jaga kebersihan gigi.',
            ]
        );

        $mr2->diagnoses()->firstOrCreate(
            ['icd10_code' => 'K02.1'],
            ['description' => 'Caries of dentine', 'type' => 'primary']
        );

        $mr2->procedures()->firstOrCreate(
            ['icd9cm_code' => '23.2'],
            ['description' => 'Restoration of tooth by filling (Komposit)', 'tariff' => 120000]
        );

        // Billing Invoice for Visit 2 (Adm 15k + Kons 50k + Tambal 120k = 185k)
        $inv2 = BillingInvoice::firstOrCreate(
            ['invoice_number' => 'INV-'.$v2Date->format('Ymd').'-0002'],
            [
                'visit_id' => $v2->id,
                'patient_id' => $p2->id,
                'total_amount' => 185000,
                'discount_amount' => 0,
                'final_amount' => 185000,
                'paid_amount' => 185000,
                'balance_amount' => 0,
                'status' => 'lunas',
                'created_by' => $admisiUser->id,
            ]
        );

        $inv2->items()->firstOrCreate(
            ['item_type' => 'administrasi', 'item_code' => 'TRF-ADM'],
            ['item_name' => 'Biaya Administrasi & Registrasi Pasien', 'quantity' => 1, 'unit_price' => 15000, 'subtotal' => 15000]
        );
        $inv2->items()->firstOrCreate(
            ['item_type' => 'konsultasi', 'item_code' => 'TRF-KONS-GIGI'],
            ['item_name' => 'Konsultasi & Pemeriksaan Dokter Gigi', 'quantity' => 1, 'unit_price' => 50000, 'subtotal' => 50000]
        );
        $inv2->items()->firstOrCreate(
            ['item_type' => 'tindakan_medis', 'item_code' => '23.2'],
            ['item_name' => 'Penambalan Gigi Komposit (Gigi 46)', 'quantity' => 1, 'unit_price' => 120000, 'subtotal' => 120000]
        );

        Payment::firstOrCreate(
            ['receipt_number' => 'KW-'.$v2Date->format('Ymd').'-0002'],
            [
                'billing_invoice_id' => $inv2->id,
                'payment_method_id' => $qrisMethod->id,
                'amount_paid' => 185000,
                'amount_tendered' => 185000,
                'change_due' => 0,
                'payment_time' => $v2Date->copy()->addMinutes(50),
                'cashier_id' => $admisiUser->id,
                'notes' => 'Pembayaran via QRIS Mandiri',
            ]
        );

        // 4. Visit in progress (Active today queue demonstration)
        $p3 = $createdPatients[2];
        $v3 = Visit::firstOrCreate(
            ['visit_number' => 'REG-'.Carbon::today()->format('Ymd').'-0003'],
            [
                'patient_id' => $p3->id,
                'polyclinic_id' => $polyUmum->id,
                'doctor_id' => $empDokter->id,
                'registered_by' => $admisiUser->id,
                'registration_date' => Carbon::now()->subMinutes(30),
                'visit_type' => 'baru',
                'payment_type' => 'umum',
                'status' => 'menunggu_dokter',
                'queue_number' => 'UM-02',
                'notes' => 'Perut perih mual sejak tadi pagi',
            ]
        );

        // Assessment for Visit 3
        NursingAssessment::firstOrCreate(
            ['visit_id' => $v3->id],
            [
                'patient_id' => $p3->id,
                'nurse_id' => $empPerawat->id,
                'assessment_time' => Carbon::now()->subMinutes(20),
                'chief_complaint' => 'Nyeri ulu hati terasa panas perih dan mual setelah terlambat makan.',
                'systolic' => 118,
                'diastolic' => 78,
                'temperature' => 36.7,
                'pulse_rate' => 80,
                'respiratory_rate' => 18,
                'oxygen_saturation' => 99.0,
                'weight' => 62.0,
                'height' => 172.0,
                'bmi' => 21.0,
                'consciousness' => 'compos_mentis',
                'pain_scale' => 4,
                'fall_risk' => 'rendah',
            ]
        );

        // 5. Visit waiting for assessment
        $p4 = $createdPatients[3];
        Visit::firstOrCreate(
            ['visit_number' => 'REG-'.Carbon::today()->format('Ymd').'-0004'],
            [
                'patient_id' => $p4->id,
                'polyclinic_id' => $polyUmum->id,
                'doctor_id' => $empDokter->id,
                'registered_by' => $admisiUser->id,
                'registration_date' => Carbon::now()->subMinutes(10),
                'visit_type' => 'baru',
                'payment_type' => 'umum',
                'status' => 'menunggu_pemeriksaan_awal',
                'queue_number' => 'UM-03',
                'notes' => 'Pemeriksaan tensi rutin',
            ]
        );
    }
}
