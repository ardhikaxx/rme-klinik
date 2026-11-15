<?php

use App\Models\AntenatalCare;
use App\Models\ChildGrowth;
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
    $role = Role::create(['name' => 'perawat_bidan', 'display_name' => 'Bidan']);
    $perm = Permission::create(['name' => 'kia.manage', 'display_name' => 'Kelola KIA', 'module' => 'kia']);
    $role->permissions()->attach($perm->id);

    $this->bidanUser = User::create([
        'name' => 'Bidan Nurul',
        'username' => 'bidan.nurul',
        'email' => 'nurul@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->bidanUser->roles()->attach($role->id);

    $this->bidanEmployee = Employee::create([
        'user_id' => $this->bidanUser->id,
        'employee_number' => 'EMP-BDN-001',
        'name' => 'Bidan Nurul',
        'gender' => 'P',
        'profession' => 'bidan',
        'is_active' => true,
    ]);

    $this->polyKia = Polyclinic::create([
        'code' => 'POL-KIA',
        'name' => 'Poli KIA',
        'type' => 'kia',
        'is_active' => true,
    ]);

    $this->patientMother = Patient::create([
        'medical_record_number' => 'RM-2026-000400',
        'nik' => '3573010101940007',
        'name' => 'Ibu Rahmawati',
        'date_of_birth' => '1994-08-20',
        'gender' => 'P',
        'blood_type' => 'O',
        'phone' => '081234567222',
        'address' => 'Jl. Galunggung No. 20 Malang',
        'marital_status' => 'kawin',
        'is_verified' => true,
    ]);

    $this->visitMother = Visit::create([
        'visit_number' => 'REG-20261115-0040',
        'patient_id' => $this->patientMother->id,
        'polyclinic_id' => $this->polyKia->id,
        'doctor_id' => $this->bidanEmployee->id,
        'queue_number' => 'KI-01',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'pemeriksaan_dokter_berlangsung',
        'registered_by' => 1,
    ]);
});

test('midwife can record antenatal care examination for pregnant mother', function () {
    $ancData = [
        'gravida' => 2,
        'para' => 1,
        'abortus' => 0,
        'hpht' => '2026-03-01',
        'estimated_due_date' => '2026-12-08',
        'gestational_age_weeks' => 28.5,
        'fundal_height' => 26.0,
        'fetal_heart_rate' => 142,
        'fetal_presentation' => 'kepala',
        'systolic' => 115,
        'diastolic' => 75,
        'weight' => 62.5,
        'complaints' => 'Kaki sedikit pegal, gerakan janin aktif',
        'treatment_plan' => 'Pemberian tablet tambah darah (Fe) dan kalsium laktat',
    ];

    $response = $this->actingAs($this->bidanUser)
        ->post(route('kia.anc.store', $this->visitMother->id), $ancData);

    $anc = AntenatalCare::where('visit_id', $this->visitMother->id)->first();
    expect($anc)->not->toBeNull();
    expect($anc->gravida)->toBe(2);
    expect($anc->para)->toBe(1);
    expect((float) $anc->gestational_age_weeks)->toBe(28.5);
    expect($anc->fetal_heart_rate)->toBe(142);
    expect($anc->fetal_presentation)->toBe('kepala');

    $response->assertRedirect(route('kia.show', $this->visitMother->id));
});

test('midwife can record child growth monitoring and view chart data JSON', function () {
    $childData = [
        'age_months' => 9,
        'weight' => 8.5,
        'height' => 71.0,
        'head_circumference' => 44.5,
        'immunization_status' => 'Campak-Rubela 1',
        'development_milestone' => 'Bisa duduk stabil tanpa ditopang, merangkak aktif',
        'notes' => 'Status gizi baik sesuai KMS',
    ];

    $response = $this->actingAs($this->bidanUser)
        ->post(route('kia.child.store', $this->visitMother->id), $childData);

    $child = ChildGrowth::where('visit_id', $this->visitMother->id)->first();
    expect($child)->not->toBeNull();
    expect($child->age_months)->toBe(9);
    expect((float) $child->weight)->toBe(8.5);
    expect((float) $child->height)->toBe(71.0);

    // Test chart data API
    $chartResponse = $this->actingAs($this->bidanUser)
        ->getJson(route('kia.chart.data', $this->patientMother->id));

    $chartResponse->assertOk();
    $chartResponse->assertJsonStructure(['labels', 'weights', 'heights']);
    expect($chartResponse->json('labels.0'))->toBe('9 bln');
    expect((float) $chartResponse->json('weights.0'))->toBe(8.5);
});
