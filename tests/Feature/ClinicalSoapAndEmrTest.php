<?php

use App\Models\Employee;
use App\Models\Icd10Code;
use App\Models\Icd9cmCode;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\ServiceTariff;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::create(['name' => 'dokter', 'display_name' => 'Dokter']);

    $this->doctorUser = User::create([
        'name' => 'dr. Anton Wijaya',
        'username' => 'dr.anton',
        'email' => 'anton@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->doctorUser->roles()->attach($role->id);

    $this->doctorEmployee = Employee::create([
        'user_id' => $this->doctorUser->id,
        'employee_number' => 'EMP-DOC-010',
        'name' => 'dr. Anton Wijaya',
        'gender' => 'L',
        'profession' => 'dokter',
        'sip_str_number' => 'SIP-2024-DOC-01',
        'is_active' => true,
    ]);

    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'type' => 'umum',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000200',
        'nik' => '3573010101950005',
        'name' => 'Hendra Setiawan',
        'date_of_birth' => '1995-04-10',
        'gender' => 'L',
        'blood_type' => 'A',
        'phone' => '081234567999',
        'address' => 'Jl. Borobudur No. 12 Malang',
        'marital_status' => 'belum_kawin',
        'is_verified' => true,
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-0020',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctorEmployee->id,
        'queue_number' => 'UM-01',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'menunggu_dokter',
        'registered_by' => 1,
    ]);

    Icd10Code::create([
        'code' => 'A09.9',
        'description' => 'Gastroenteritis and colitis of unspecified origin',
        'is_active' => true,
    ]);

    Icd9cmCode::create([
        'code' => '96.07',
        'description' => 'Insertion of other (naso-)gastric tube',
        'is_active' => true,
    ]);

    ServiceTariff::create([
        'code' => 'TRF-TND-01',
        'name' => 'Pemasangan NGT (Insertion of Nasogastric Tube)',
        'category' => 'tindakan_medis',
        'icd9cm_code' => '96.07',
        'tariff' => 75000,
        'doctor_fee' => 50000,
        'nurse_fee' => 10000,
        'clinic_fee' => 15000,
        'is_active' => true,
    ]);
});

test('doctor can save SOAP draft with structured ICD-10 and procedure snapshot', function () {
    $soapData = [
        'subjective' => 'Pasien BAB cair > 5 kali sejak semalam, mual, lemas',
        'objective' => 'Turgor kulit melambat, mata sedikit cekung, bising usus meningkat',
        'assessment' => 'Gastroenteritis Akut (GEA) Dehidrasi Ringan-Sedang',
        'plan' => 'Rehidrasi oralit, infus RL 20 tpm, diet lunak, antibiotik bila perlu',
        'education_plan' => 'Hindari makanan pedas dan asam, jaga kebersihan tangan',
        'follow_up_plan' => 'Kontrol jika BAB cair berlanjut > 3 hari atau ada darah',
        'diagnoses' => [
            [
                'code' => 'A09.9',
                'type' => 'primary',
            ],
        ],
        'procedures' => [
            [
                'code' => '96.07',
            ],
        ],
    ];

    $response = $this->actingAs($this->doctorUser)
        ->post(route('clinical.draft', $this->visit->id), $soapData);

    $record = MedicalRecord::where('visit_id', $this->visit->id)->first();
    expect($record)->not->toBeNull();
    expect($record->status)->toBe('draft');
    expect($record->is_locked)->toBeFalse();
    expect($record->subjective)->toBe('Pasien BAB cair > 5 kali sejak semalam, mual, lemas');

    // Verify structured ICD-10 diagnosis
    expect($record->diagnoses)->toHaveCount(1);
    expect($record->diagnoses->first()->icd10_code)->toBe('A09.9');
    expect($record->diagnoses->first()->type)->toBe('primary');

    // Verify procedure snapshot
    expect($record->procedures)->toHaveCount(1);
    expect($record->procedures->first()->icd9cm_code)->toBe('96.07');
    expect((float) $record->procedures->first()->tariff)->toBe(75000.0);

    $response->assertRedirect(route('clinical.examination', $this->visit->id));
});

test('doctor can finalize medical record and locking prevents further draft modification', function () {
    // Create draft
    $this->actingAs($this->doctorUser)->post(route('clinical.draft', $this->visit->id), [
        'subjective' => 'Keluhan awal',
        'objective' => 'Pemeriksaan fisik',
        'assessment' => 'Diagnosis kerja',
        'plan' => 'Rencana terapi',
    ]);

    $record = MedicalRecord::where('visit_id', $this->visit->id)->first();

    // Finalize
    $response = $this->actingAs($this->doctorUser)
        ->post(route('clinical.finalize', $record->id));

    $record->refresh();
    expect($record->status)->toBe('final');
    expect($record->is_locked)->toBeTrue();
    expect($record->finalized_at)->not->toBeNull();

    // Attempting to overwrite draft on a locked record throws exception / is rejected
    $attempt = $this->actingAs($this->doctorUser)->post(route('clinical.draft', $this->visit->id), [
        'subjective' => 'Mencoba merubah catatan yang terkunci',
        'objective' => 'Pemeriksaan fisik',
        'assessment' => 'Diagnosis kerja',
        'plan' => 'Rencana terapi',
    ]);

    // Record remains unchanged
    $record->refresh();
    expect($record->subjective)->toBe('Keluhan awal');
});

test('doctor can add addendum to a finalized record with documented reason', function () {
    $this->actingAs($this->doctorUser)->post(route('clinical.draft', $this->visit->id), [
        'subjective' => 'Keluhan awal',
        'objective' => 'Pemeriksaan fisik',
        'assessment' => 'Diagnosis kerja',
        'plan' => 'Rencana terapi',
    ]);

    $record = MedicalRecord::where('visit_id', $this->visit->id)->first();
    $this->actingAs($this->doctorUser)->post(route('clinical.finalize', $record->id));

    // Create Addendum
    $response = $this->actingAs($this->doctorUser)->post(route('clinical.addendum', $record->id), [
        'addendum_content' => 'Hasil laboratorium elektrolit menunjukkan hipokalemia ringan (K: 3.2 mEq/L). Pasien diberikan edukasi konsumsi pisang dan suplemen KSR.',
        'reason' => 'Konfirmasi hasil evaluasi laboratorium lanjutan',
    ]);

    $record->refresh();
    expect($record->addendums)->toHaveCount(1);
    expect($record->addendums->first()->reason)->toBe('Konfirmasi hasil evaluasi laboratorium lanjutan');
    expect($record->addendums->first()->doctor_id)->toBe($this->doctorEmployee->id);

    $response->assertRedirect(route('clinical.examination', $this->visit->id));
});
