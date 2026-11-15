<?php

use App\Models\Employee;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::create(['name' => 'dokter', 'display_name' => 'Dokter']);

    $this->doctorUser = User::create([
        'name' => 'drg. Maya Anggraini',
        'username' => 'drg.maya',
        'email' => 'maya@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->doctorUser->roles()->attach($role->id);

    $this->doctorEmployee = Employee::create([
        'user_id' => $this->doctorUser->id,
        'employee_number' => 'EMP-DNT-001',
        'name' => 'drg. Maya Anggraini',
        'gender' => 'P',
        'profession' => 'dokter',
        'sip_str_number' => 'SIP-GIGI-2024-001',
        'is_active' => true,
    ]);

    $this->polyGigi = Polyclinic::create([
        'code' => 'POL-GIG',
        'name' => 'Poli Gigi dan Mulut',
        'type' => 'gigi',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000300',
        'nik' => '3573010101960006',
        'name' => 'Bayu Pratama',
        'date_of_birth' => '1996-06-15',
        'gender' => 'L',
        'blood_type' => 'B',
        'phone' => '081234567111',
        'address' => 'Jl. Kawi No. 15 Malang',
        'marital_status' => 'belum_kawin',
        'is_verified' => true,
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-0030',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->polyGigi->id,
        'doctor_id' => $this->doctorEmployee->id,
        'queue_number' => 'GI-01',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'menunggu_dokter',
        'registered_by' => 1,
    ]);
});

test('odontogram show automatically initializes odontogram and adult teeth records', function () {
    $response = $this->actingAs($this->doctorUser)
        ->get(route('odontogram.show', $this->visit->id));

    $response->assertOk();
    $odontogram = Odontogram::where('visit_id', $this->visit->id)->first();
    expect($odontogram)->not->toBeNull();
    expect($odontogram->patient_id)->toBe($this->patient->id);
    expect($odontogram->teeth->count())->toBeGreaterThanOrEqual(32); // FDI Adult teeth 11-48
});

test('doctor can update tooth condition and surfaces via AJAX', function () {
    $this->actingAs($this->doctorUser)->get(route('odontogram.show', $this->visit->id));
    $odontogram = Odontogram::where('visit_id', $this->visit->id)->first();

    $response = $this->actingAs($this->doctorUser)->postJson(route('odontogram.tooth', $odontogram->id), [
        'tooth_number' => '16',
        'condition' => 'caries',
        'surfaces' => 'MOD',
        'treatment_notes' => 'Karies profunda pada oklusal, mesial, distal. Pro tumpatan komposit.',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('tooth.tooth_number', '16');
    $response->assertJsonPath('tooth.condition', 'caries');
    $response->assertJsonPath('tooth.surfaces', 'MOD');

    $tooth = $odontogram->teeth()->where('tooth_number', '16')->first();
    expect($tooth)->not->toBeNull();
    expect($tooth->condition)->toBe('caries');
    expect($tooth->surfaces)->toBe('MOD');
    expect($tooth->condition_color)->toBe('#dc3545'); // Caries is red
});

test('doctor can save general notes and print odontogram', function () {
    $this->actingAs($this->doctorUser)->get(route('odontogram.show', $this->visit->id));
    $odontogram = Odontogram::where('visit_id', $this->visit->id)->first();

    $this->actingAs($this->doctorUser)->post(route('odontogram.save', $odontogram->id), [
        'notes' => 'Oklusi normal bite, tidak ada torus, oral hygiene cukup.',
    ]);

    $odontogram->refresh();
    expect($odontogram->general_notes)->toBe('Oklusi normal bite, tidak ada torus, oral hygiene cukup.');

    $printResponse = $this->actingAs($this->doctorUser)->get(route('odontogram.print', $this->visit->id));
    $printResponse->assertOk();
    $printResponse->assertSee('REKAM MEDIS ODONTOGRAM');
    $printResponse->assertSee('Bayu Pratama');
});
