<?php

use App\Models\Employee;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\ServiceTariff;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::create(['name' => 'admisi_kasir', 'display_name' => 'Admisi']);
    $permissions = [
        Permission::create(['name' => 'visits.view', 'display_name' => 'Lihat', 'module' => 'visits']),
        Permission::create(['name' => 'visits.register', 'display_name' => 'Daftar', 'module' => 'visits']),
        Permission::create(['name' => 'visits.cancel', 'display_name' => 'Batal', 'module' => 'visits']),
    ];
    $role->permissions()->attach(collect($permissions)->pluck('id'));

    $this->user = User::create([
        'name' => 'Admisi Kasir',
        'username' => 'admisi',
        'email' => 'admisi@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->user->roles()->attach($role->id);

    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'type' => 'umum',
        'is_active' => true,
    ]);

    ServiceTariff::create([
        'code' => 'TRF-ADM-01',
        'name' => 'Biaya Administrasi Pasien Baru/Lama',
        'category' => 'administrasi',
        'tariff' => 15000,
        'doctor_fee' => 0,
        'nurse_fee' => 0,
        'clinic_fee' => 15000,
        'is_active' => true,
    ]);

    $this->doctorUser = User::create([
        'name' => 'dr. Hendra Gunawan',
        'username' => 'hendra',
        'email' => 'hendra@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);

    $this->doctor = Employee::create([
        'user_id' => $this->doctorUser->id,
        'employee_number' => 'EMP-DOC-002',
        'name' => 'dr. Hendra Gunawan',
        'gender' => 'L',
        'profession' => 'dokter',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000100',
        'nik' => '3573010101910001',
        'name' => 'Ratna Dewi',
        'date_of_birth' => '1991-01-01',
        'gender' => 'P',
        'blood_type' => 'A',
        'phone' => '081234567899',
        'address' => 'Jl. Ijen No. 100 Malang',
        'marital_status' => 'kawin',
        'is_verified' => true,
    ]);
});

test('visit registration creates sequential queue number and initializes invoice', function () {
    $visitData = [
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctor->id,
        'payment_type' => 'umum',
        'notes' => 'Pasien mengeluh pusing dan demam',
    ];

    $response = $this->actingAs($this->user)->post(route('visits.store'), $visitData);

    $visit = Visit::where('patient_id', $this->patient->id)->first();
    expect($visit)->not->toBeNull();
    expect($visit->queue_number)->toBe('UM-01');
    expect($visit->visit_number)->toMatch('/^REG-\d{8}-\d{4}$/');
    expect($visit->status)->toBe('menunggu_pemeriksaan_awal');

    // Check billing invoice initialization
    expect($visit->billingInvoice)->not->toBeNull();
    expect($visit->billingInvoice->items)->toHaveCount(1); // Administrative tariff item

    $response->assertRedirect(route('visits.show', $visit->id));
});

test('status transition records status history', function () {
    $visit = Visit::create([
        'visit_number' => 'VIS-20261115-0010',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctor->id,
        'queue_number' => 'A-001',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'menunggu_pemeriksaan_awal',
        'registered_by' => $this->user->id,
    ]);

    $this->actingAs($this->user)->post(route('visits.status', $visit->id), [
        'status' => 'pemeriksaan_awal_berlangsung',
        'reason' => 'Perawat memanggil pasien ke bilik TTV',
    ]);

    $visit->refresh();
    expect($visit->status)->toBe('pemeriksaan_awal_berlangsung');
    expect($visit->statusHistories)->toHaveCount(1);
    expect($visit->statusHistories->first()->status_to)->toBe('pemeriksaan_awal_berlangsung');
    expect($visit->statusHistories->first()->reason)->toBe('Perawat memanggil pasien ke bilik TTV');
});

test('visit cancellation updates status and requires reason', function () {
    $visit = Visit::create([
        'visit_number' => 'VIS-20261115-0011',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctor->id,
        'queue_number' => 'A-002',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'menunggu_pemeriksaan_awal',
        'registered_by' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)->post(route('visits.cancel', $visit->id), [
        'reason' => 'Pasien berhalangan dan meminta reschedule',
    ]);

    $visit->refresh();
    expect($visit->status)->toBe('dibatalkan');
    $response->assertRedirect(route('visits.index'));
});
