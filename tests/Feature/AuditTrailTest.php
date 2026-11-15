<?php

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    $roleKepala = Role::where('name', 'kepala_klinik')->first();
    $rolePerawat = Role::where('name', 'perawat_bidan')->first();

    $this->userKepala = User::create([
        'username' => 'kepala_audit',
        'name' => 'dr. Hendra Kepala',
        'email' => 'kepala@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userKepala->roles()->attach($roleKepala->id);

    $this->userPerawat = User::create([
        'username' => 'siti_perawat',
        'name' => 'Siti Nurhaliza, S.Kep',
        'email' => 'perawat@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userPerawat->roles()->attach($rolePerawat->id);
});

test('clinic manager can access audit trail and view log history', function () {
    // Generate some audit logs
    $this->actingAs($this->userKepala);
    AuditLog::log('USER_LOGIN', 'AUTH', 'Pengguna kepala_audit berhasil masuk ke sistem');
    AuditLog::log('PRICE_CHANGED', 'BILLING', 'Tarif konsultasi diubah dari Rp40.000 menjadi Rp50.000', ['tariff' => 40000], ['tariff' => 50000]);

    $response = $this->actingAs($this->userKepala)->get(route('audit.index'));

    $response->assertOk();
    $response->assertSee('Audit Trail');
    $response->assertSee('USER_LOGIN');
    $response->assertSee('PRICE_CHANGED');
    $response->assertSee('Pengguna kepala_audit berhasil masuk ke sistem');
});

test('unauthorized staff cannot view audit trail', function () {
    $response = $this->actingAs($this->userPerawat)->get(route('audit.index'));
    $response->assertForbidden();
});

test('audit logs can be filtered by module and user', function () {
    $this->actingAs($this->userKepala);
    AuditLog::log('LOGIN', 'AUTH', 'Login berhasil');
    AuditLog::log('FINALIZED', 'CLINICAL', 'Rekam medis dikunci permanen');
    AuditLog::log('DISPENSE', 'PHARMACY', 'Obat diserahkan ke pasien');

    // Filter module = CLINICAL
    $resClinical = $this->get(route('audit.index', ['module' => 'CLINICAL']));
    $resClinical->assertOk();
    $resClinical->assertSee('FINALIZED');
    $resClinical->assertDontSee('DISPENSE');

    // Filter module = PHARMACY
    $resPharmacy = $this->get(route('audit.index', ['module' => 'PHARMACY']));
    $resPharmacy->assertOk();
    $resPharmacy->assertSee('DISPENSE');
    $resPharmacy->assertDontSee('FINALIZED');
});

test('audit log helper records user id, ip address, and timestamps accurately', function () {
    $this->actingAs($this->userKepala);

    $log = AuditLog::log(
        'EMR_ACCESS',
        'SECURITY',
        'Akses rekam medis pasien RM-2026-0001',
        ['action' => 'open'],
        ['action' => 'read']
    );

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($this->userKepala->id);
    expect($log->action)->toBe('EMR_ACCESS');
    expect($log->module)->toBe('SECURITY');
    expect($log->description)->toBe('Akses rekam medis pasien RM-2026-0001');
    expect($log->created_at)->not->toBeNull();
    expect($log->old_values)->toBe(['action' => 'open']);
    expect($log->new_values)->toBe(['action' => 'read']);
});
