<?php

use App\Models\Patient;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $role = Role::create(['name' => 'admisi_kasir', 'display_name' => 'Admisi']);
    $permissions = [
        Permission::create(['name' => 'patients.view', 'display_name' => 'Lihat', 'module' => 'patients']),
        Permission::create(['name' => 'patients.create', 'display_name' => 'Tambah', 'module' => 'patients']),
        Permission::create(['name' => 'patients.update', 'display_name' => 'Ubah', 'module' => 'patients']),
        Permission::create(['name' => 'patients.card.print', 'display_name' => 'Cetak Kartu', 'module' => 'patients']),
    ];
    $role->permissions()->attach(collect($permissions)->pluck('id'));

    $this->user = User::create([
        'name' => 'Admisi Test',
        'username' => 'admisi',
        'email' => 'admisi@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->user->roles()->attach($role->id);
});

test('patient can be registered with auto-generated MRN and allergies', function () {
    $patientData = [
        'nik' => '3573021205950002',
        'name' => 'Dewi Sartika',
        'place_of_birth' => 'Malang',
        'date_of_birth' => '1995-05-12',
        'gender' => 'P',
        'blood_type' => 'B',
        'phone' => '081333444555',
        'email' => 'dewi@example.com',
        'address' => 'Jl. Bandung No. 10',
        'kelurahan' => 'Penanggungan',
        'kecamatan' => 'Klojen',
        'city' => 'Kota Malang',
        'province' => 'Jawa Timur',
        'marital_status' => 'belum_kawin',
        'allergies' => [
            [
                'allergen_type' => 'obat',
                'allergen_name' => 'Amoxicillin',
                'reaction' => 'Ruam merah gatal dan biduran',
                'severity' => 'sedang',
            ],
        ],
        'contacts' => [
            [
                'name' => 'Bambang Sudarmono',
                'relationship' => 'Orang Tua',
                'phone' => '081222333444',
            ],
        ],
    ];

    $response = $this->actingAs($this->user)->post(route('patients.store'), $patientData);

    $patient = Patient::where('nik', '3573021205950002')->first();
    expect($patient)->not->toBeNull();
    expect($patient->medical_record_number)->toMatch('/^RM-\d{4}-\d{6}$/');
    expect($patient->allergies)->toHaveCount(1);
    expect($patient->allergies->first()->allergen_name)->toBe('Amoxicillin');
    expect($patient->emergencyContacts)->toHaveCount(1);

    $response->assertRedirect(route('patients.show', $patient->id));
});

test('duplicate check endpoint identifies existing patient by NIK and phone', function () {
    Patient::create([
        'medical_record_number' => 'RM-2026-000001',
        'nik' => '3573010101900001',
        'name' => 'Ahmad Sanusi',
        'date_of_birth' => '1990-01-01',
        'gender' => 'L',
        'blood_type' => 'A',
        'phone' => '081999888777',
        'address' => 'Jl. Soekarno Hatta No. 5 Malang',
        'marital_status' => 'kawin',
        'is_verified' => true,
    ]);

    // Check duplicate by NIK
    $resNik = $this->actingAs($this->user)->postJson(route('patients.check_duplicate'), [
        'nik' => '3573010101900001',
    ]);
    $resNik->assertOk();
    $resNik->assertJsonPath('found', true);
    expect($resNik->json('duplicates.0.name'))->toBe('Ahmad Sanusi');

    // Check duplicate by phone
    $resPhone = $this->actingAs($this->user)->postJson(route('patients.check_duplicate'), [
        'phone' => '081999888777',
    ]);
    $resPhone->assertOk();
    $resPhone->assertJsonPath('found', true);

    // Non-duplicate check
    $resUnique = $this->actingAs($this->user)->postJson(route('patients.check_duplicate'), [
        'nik' => '3573099999999999',
        'name' => 'Nama Belum Ada',
    ]);
    $resUnique->assertOk();
    $resUnique->assertJsonPath('found', false);
});

test('patient registration rejects missing required fields and invalid NIK', function () {
    $invalidData = [
        'nik' => '12345', // Invalid length (must be 16)
        'name' => '',     // Missing name
        'date_of_birth' => 'not-a-date',
    ];

    $response = $this->actingAs($this->user)->post(route('patients.store'), $invalidData);
    $response->assertSessionHasErrors(['name', 'nik', 'date_of_birth', 'gender', 'phone', 'address']);
});

test('digital patient card view renders correctly', function () {
    $patient = Patient::create([
        'medical_record_number' => 'RM-2026-000099',
        'nik' => '3573030303880003',
        'name' => 'Kartu Pasien Test',
        'date_of_birth' => '1988-03-03',
        'gender' => 'L',
        'blood_type' => 'AB',
        'phone' => '081234567890',
        'address' => 'Jl. Veteran No. 1 Malang',
        'marital_status' => 'kawin',
        'is_verified' => true,
    ]);

    $response = $this->actingAs($this->user)->get(route('patients.card', $patient->id));
    $response->assertOk();
    $response->assertSee('RM-2026-000099');
    $response->assertSee('Kartu Pasien Test');
});
