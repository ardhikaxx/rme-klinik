<?php

use App\Models\Employee;
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
    // Setup roles
    $this->roleAdmisi = Role::create(['name' => 'admisi_kasir', 'display_name' => 'Admisi & Kasir']);
    $this->roleDokter = Role::create(['name' => 'dokter', 'display_name' => 'Dokter']);
    $this->rolePerawat = Role::create(['name' => 'perawat_bidan', 'display_name' => 'Perawat']);
    $this->roleKepala = Role::create(['name' => 'kepala_klinik', 'display_name' => 'Kepala Klinik']);

    // Permissions
    $permPatientView = Permission::create(['name' => 'patients.view', 'display_name' => 'Lihat Pasien', 'module' => 'patients']);
    $permVisitsView = Permission::create(['name' => 'visits.view', 'display_name' => 'Lihat Kunjungan', 'module' => 'visits']);
    $this->roleAdmisi->permissions()->attach([$permPatientView->id, $permVisitsView->id]);

    // Users
    $this->userAdmisi = User::create([
        'name' => 'Petugas Admisi',
        'username' => 'admisi',
        'email' => 'admisi@klinik.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userAdmisi->roles()->attach($this->roleAdmisi->id);

    $this->userDokter = User::create([
        'name' => 'dr. Budi Santoso',
        'username' => 'dokter',
        'email' => 'dokter@klinik.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userDokter->roles()->attach($this->roleDokter->id);

    $this->empDokter = Employee::create([
        'user_id' => $this->userDokter->id,
        'employee_number' => 'EMP-DOC-001',
        'name' => 'dr. Budi Santoso',
        'gender' => 'L',
        'profession' => 'dokter',
        'is_active' => true,
    ]);

    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'type' => 'umum',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000001',
        'nik' => '3573010101900001',
        'name' => 'Siti Aminah',
        'date_of_birth' => '1990-01-01',
        'gender' => 'P',
        'blood_type' => 'O',
        'phone' => '08123456789',
        'address' => 'Jl. Ijen No. 1 Malang',
        'marital_status' => 'kawin',
        'is_verified' => true,
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'VIS-20261115-0001',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->empDokter->id,
        'queue_number' => 'A-001',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'menunggu_dokter',
        'registered_by' => $this->userAdmisi->id,
    ]);
});

test('petugas admisi can access visits index but cannot access doctor examination', function () {
    // Admisi can view visits
    $this->actingAs($this->userAdmisi)
        ->get(route('visits.index'))
        ->assertStatus(200);

    // Admisi is forbidden from clinical examination
    $this->actingAs($this->userAdmisi)
        ->get(route('clinical.examination', $this->visit->id))
        ->assertStatus(403);
});

test('dokter can access clinical examination but is forbidden from clinic settings', function () {
    // Dokter can access clinical exam
    $this->actingAs($this->userDokter)
        ->get(route('clinical.examination', $this->visit->id))
        ->assertStatus(200);

    // Dokter cannot access settings tariffs (only kepala_klinik)
    $this->actingAs($this->userDokter)
        ->get(route('settings.tariffs'))
        ->assertStatus(403);
});

test('unauthenticated request to protected route is redirected to login', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->get(route('patients.index'))
        ->assertRedirect(route('login'));
});
