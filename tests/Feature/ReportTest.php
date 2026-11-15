<?php

use App\Models\Employee;
use App\Models\Patient;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $roleKepala = Role::where('name', 'kepala_klinik')->first();

    $this->userKepala = User::create([
        'username' => 'kepala_laporan',
        'name' => 'dr. Hendra Kepala',
        'email' => 'kepala_laporan@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userKepala->roles()->attach($roleKepala->id);

    $this->doctor = Employee::create([
        'user_id' => $this->userKepala->id,
        'employee_number' => 'EMP-DOC-999',
        'name' => 'dr. Hendra Kepala',
        'gender' => 'L',
        'profession' => 'dokter',
        'is_active' => true,
    ]);

    $this->poly = Polyclinic::create([
        'code' => 'POLI-UMUM',
        'name' => 'Poli Umum',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-99999',
        'nik' => '3201234567890001',
        'name' => 'Budi Santoso',
        'gender' => 'L',
        'date_of_birth' => '1990-05-10',
        'phone' => '081234567890',
        'address' => 'Jl. Merdeka No. 10',
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-9999',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctor->id,
        'registered_by' => $this->userKepala->id,
        'registration_date' => Carbon::today(),
        'visit_type' => 'baru',
        'status' => 'selesai',
    ]);
});

test('clinic manager can access reports index page without array property errors', function () {
    $response = $this->actingAs($this->userKepala)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSee('Laporan Manajemen');
    $response->assertSee('Tren Kunjungan Harian');
    $response->assertSee('10 Besar Penyakit');
    $response->assertSee('10 Besar Pemakaian Obat');
    $response->assertSee('Laporan Pembagian Jasa Medis');
});

test('reports can be filtered by date range', function () {
    $start = Carbon::today()->subDays(7)->toDateString();
    $end = Carbon::today()->toDateString();

    $response = $this->actingAs($this->userKepala)->get(route('reports.index', [
        'start_date' => $start,
        'end_date' => $end,
    ]));

    $response->assertOk();
    $response->assertSee('Laporan Manajemen');
});

test('exporting reports as csv works for all supported types', function () {
    $types = ['visits', 'diseases', 'medicines', 'fees'];

    foreach ($types as $type) {
        $response = $this->actingAs($this->userKepala)->get(route('reports.export', [
            'type' => $type,
            'start_date' => Carbon::today()->startOfMonth()->toDateString(),
            'end_date' => Carbon::today()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
});
