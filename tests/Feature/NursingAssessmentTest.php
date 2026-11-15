<?php

use App\Models\Employee;
use App\Models\NursingAssessment;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::create(['name' => 'perawat_bidan', 'display_name' => 'Perawat']);
    $perm = Permission::create(['name' => 'nursing.assessment', 'display_name' => 'Pengkajian Perawat', 'module' => 'nursing']);
    $role->permissions()->attach($perm->id);

    $this->nurseUser = User::create([
        'name' => 'Perawat Siti',
        'username' => 'perawat.siti',
        'email' => 'perawat@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->nurseUser->roles()->attach($role->id);

    $this->nurseEmployee = Employee::create([
        'user_id' => $this->nurseUser->id,
        'employee_number' => 'EMP-NUR-001',
        'name' => 'Perawat Siti',
        'gender' => 'P',
        'profession' => 'perawat',
        'is_active' => true,
    ]);

    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'type' => 'umum',
        'is_active' => true,
    ]);

    $docUser = User::create([
        'name' => 'dr. Dokter Test',
        'username' => 'dr.test',
        'email' => 'dr@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $doctor = Employee::create([
        'user_id' => $docUser->id,
        'employee_number' => 'EMP-DOC-003',
        'name' => 'dr. Dokter Test',
        'gender' => 'L',
        'profession' => 'dokter',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000101',
        'nik' => '3573010101920002',
        'name' => 'Budi Santoso',
        'date_of_birth' => '1992-01-01',
        'gender' => 'L',
        'blood_type' => 'O',
        'phone' => '081234567888',
        'address' => 'Jl. Soekarno Hatta Malang',
        'marital_status' => 'kawin',
        'is_verified' => true,
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-0015',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $doctor->id,
        'queue_number' => 'UM-01',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'menunggu_pemeriksaan_awal',
        'registered_by' => 1,
    ]);
});

test('nurse can record initial assessment with vital signs and auto BMI', function () {
    $assessmentData = [
        'chief_complaint' => 'Demam tinggi menggigil sejak 3 hari disertai batuk kering',
        'current_illness_history' => 'Demam naik terutama malam hari',
        'past_illness_history' => 'Riwayat tipoid 2 tahun lalu',
        'systolic' => 145,
        'diastolic' => 95,
        'temperature' => 38.8,
        'pulse_rate' => 98,
        'respiratory_rate' => 22,
        'oxygen_saturation' => 97,
        'weight' => 70,
        'height' => 170,
        'waist_circumference' => 82,
        'consciousness' => 'compos_mentis',
        'pain_scale' => 3,
        'fall_risk' => 'rendah',
        'nursing_care_plan' => 'Kompres hangat, anjurkan banyak minum air',
    ];

    $response = $this->actingAs($this->nurseUser)
        ->post(route('nursing.store', $this->visit->id), $assessmentData);

    $assessment = NursingAssessment::where('visit_id', $this->visit->id)->first();
    expect($assessment)->not->toBeNull();
    expect($assessment->chief_complaint)->toBe('Demam tinggi menggigil sejak 3 hari disertai batuk kering');

    // Check auto BMI: 70 / (1.70^2) = 24.22
    expect($assessment->bmi)->toBeGreaterThanOrEqual(24.0);
    expect($assessment->bmi)->toBeLessThanOrEqual(24.5);
    expect($assessment->bmi_category)->toBe('Normal');

    // Check abnormal flags
    expect($assessment->is_blood_pressure_abnormal)->toBeTrue(); // 145/95 is hypertension
    expect($assessment->is_temperature_abnormal)->toBeTrue();    // 38.8 is fever

    // Check visit status transition
    $this->visit->refresh();
    expect($this->visit->status)->toBe('menunggu_dokter');

    $response->assertRedirect(route('nursing.index'));
});
