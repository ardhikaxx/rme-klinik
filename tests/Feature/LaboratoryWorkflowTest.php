<?php

use App\Models\BillingInvoice;
use App\Models\Employee;
use App\Models\Laboratory;
use App\Models\LaboratoryRequest;
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
    $roleDokter = Role::create(['name' => 'dokter', 'display_name' => 'Dokter']);
    $roleAnalyst = Role::create(['name' => 'perawat_bidan', 'display_name' => 'Analis Lab']);

    $permOrder = Permission::create(['name' => 'laboratory.order', 'display_name' => 'Order Lab', 'module' => 'laboratory']);
    $permRecord = Permission::create(['name' => 'laboratory.record', 'display_name' => 'Entri Lab', 'module' => 'laboratory']);

    $roleDokter->permissions()->attach($permOrder->id);
    $roleAnalyst->permissions()->attach([$permRecord->id]);

    $this->userDokter = User::create([
        'name' => 'dr. Rudi Hermanto',
        'username' => 'dr.rudi',
        'email' => 'rudi@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userDokter->roles()->attach($roleDokter->id);

    $this->doctorEmployee = Employee::create([
        'user_id' => $this->userDokter->id,
        'employee_number' => 'EMP-DOC-030',
        'name' => 'dr. Rudi Hermanto',
        'gender' => 'L',
        'profession' => 'dokter',
        'is_active' => true,
    ]);

    $this->userAnalyst = User::create([
        'name' => 'Analis Rina, A.Md.AK',
        'username' => 'analis.rina',
        'email' => 'rina@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userAnalyst->roles()->attach($roleAnalyst->id);

    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'type' => 'umum',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000600',
        'nik' => '3573010101980009',
        'name' => 'Gita Gutawa',
        'date_of_birth' => '1998-07-11',
        'gender' => 'P',
        'blood_type' => 'AB',
        'phone' => '081234567444',
        'address' => 'Jl. Trunojoyo No. 40 Malang',
        'marital_status' => 'belum_kawin',
        'is_verified' => true,
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-0060',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctorEmployee->id,
        'queue_number' => 'UM-01',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'pemeriksaan_dokter_berlangsung',
        'registered_by' => 1,
    ]);

    $this->invoice = BillingInvoice::create([
        'invoice_number' => 'INV-20261115-0060',
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'total_amount' => 0,
        'discount_amount' => 0,
        'final_amount' => 0,
        'paid_amount' => 0,
        'balance_amount' => 0,
        'status' => 'belum_dibayar',
        'created_by' => 1,
    ]);

    $this->labHb = Laboratory::create([
        'code' => 'LAB-HB-01',
        'name' => 'Hemoglobin (Hb)',
        'category' => 'Hematologi',
        'sample_type' => 'darah',
        'unit' => 'g/dL',
        'reference_range' => '12.0 - 16.0',
        'critical_low' => 7.0,
        'critical_high' => 20.0,
        'tariff' => 35000,
        'is_active' => true,
    ]);
});

test('doctor can order lab test, analyst can collect sample, record results, and print', function () {
    // 1. Doctor creates lab request
    $response = $this->actingAs($this->userDokter)->post(route('laboratory.store_request', $this->visit->id), [
        'tests' => [$this->labHb->id],
        'clinical_notes' => 'Curiga anemia berat, pasien pucat dan konjungtiva anemis',
    ]);

    $labRequest = LaboratoryRequest::where('visit_id', $this->visit->id)->first();
    expect($labRequest)->not->toBeNull();
    expect($labRequest->request_number)->toMatch('/^LAB-\d{8}-\d{4}$/');
    expect($labRequest->status)->toBe('menunggu_sampel');
    expect($labRequest->items)->toHaveCount(1);

    // 2. Analyst records sample collection
    $this->actingAs($this->userAnalyst)->post(route('laboratory.collect_sample', $labRequest->id), [
        'sample_collector' => 'Analis Rina, A.Md.AK',
    ]);

    $labRequest->refresh();
    expect($labRequest->sample_collected_at)->not->toBeNull();
    expect($labRequest->sample_collector)->toBe('Analis Rina, A.Md.AK');
    expect($labRequest->status)->toBe('diproses');

    // 3. Analyst enters result with critical value (e.g. Hb = 6.2 g/dL, normal is 12-16)
    $this->actingAs($this->userAnalyst)->post(route('laboratory.save_results', $labRequest->id), [
        'results' => [
            $this->labHb->id => [
                'value' => '6.2',
                'notes' => 'Kadar Hb sangat rendah (kritis), konfirmasi ulang duplo',
            ],
        ],
    ]);

    $labRequest->refresh();
    expect($labRequest->status)->toBe('selesai');
    expect($labRequest->results)->toHaveCount(1);

    $result = $labRequest->results->first();
    expect($result->result_value)->toBe('6.2');
    expect($result->is_critical)->toBeTrue(); // Critical flag detected for Hb < 7.0

    // 4. Lab tariff attached to billing invoice: Rp35.000
    $this->invoice->refresh();
    expect($this->invoice->items->where('item_type', 'laboratorium'))->toHaveCount(1);
    expect((float) $this->invoice->items->where('item_type', 'laboratorium')->first()->subtotal)->toBe(35000.0);

    // 5. Test print lab report
    $printRes = $this->actingAs($this->userAnalyst)->get(route('laboratory.print', $labRequest->id));
    $printRes->assertOk();
    $printRes->assertSee('HASIL PEMERIKSAAN LABORATORIUM');
    $printRes->assertSee('Hemoglobin (Hb)');
    $printRes->assertSee('6.2');
});
