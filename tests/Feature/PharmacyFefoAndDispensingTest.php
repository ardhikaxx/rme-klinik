<?php

use App\Models\BillingInvoice;
use App\Models\Employee;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\MedicineStockMovement;
use App\Models\MedicineUnit;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Polyclinic;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use App\Services\PharmacyService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $roleFarmasi = Role::create(['name' => 'farmasi', 'display_name' => 'Farmasi']);
    $roleDokter = Role::create(['name' => 'dokter', 'display_name' => 'Dokter']);

    $permCreatePresc = Permission::create(['name' => 'prescriptions.create', 'display_name' => 'Buat Resep', 'module' => 'pharmacy']);
    $permVerifyPresc = Permission::create(['name' => 'prescriptions.verify', 'display_name' => 'Verifikasi', 'module' => 'pharmacy']);
    $permDispense = Permission::create(['name' => 'pharmacy.dispense', 'display_name' => 'Dispensing', 'module' => 'pharmacy']);

    $roleDokter->permissions()->attach($permCreatePresc->id);
    $roleFarmasi->permissions()->attach([$permVerifyPresc->id, $permDispense->id]);

    $this->userDokter = User::create([
        'name' => 'dr. Resep Test',
        'username' => 'dr.resep',
        'email' => 'resep@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userDokter->roles()->attach($roleDokter->id);

    $this->doctorEmployee = Employee::create([
        'user_id' => $this->userDokter->id,
        'employee_number' => 'EMP-DOC-020',
        'name' => 'dr. Resep Test',
        'gender' => 'L',
        'profession' => 'dokter',
        'is_active' => true,
    ]);

    $this->userFarmasi = User::create([
        'name' => 'Apt. Farhan, S.Farm',
        'username' => 'apt.farhan',
        'email' => 'farhan@test.id',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userFarmasi->roles()->attach($roleFarmasi->id);

    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'type' => 'umum',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000500',
        'nik' => '3573010101970008',
        'name' => 'Wahyu Hidayat',
        'date_of_birth' => '1997-02-14',
        'gender' => 'L',
        'blood_type' => 'O',
        'phone' => '081234567333',
        'address' => 'Jl. Semeru No. 33 Malang',
        'marital_status' => 'belum_kawin',
        'is_verified' => true,
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-0050',
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
        'invoice_number' => 'INV-20261115-0050',
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

    $category = MedicineCategory::create(['code' => 'CAT-ANT', 'name' => 'Antibiotik']);
    $unit = MedicineUnit::create(['code' => 'TAB', 'name' => 'Tablet']);

    $this->medicine = Medicine::create([
        'code' => 'MED-AMX-500',
        'category_id' => $category->id,
        'unit_id' => $unit->id,
        'brand_name' => 'Amoxsan 500mg',
        'generic_name' => 'Amoxicillin Trihydrate',
        'dosage_form' => 'Kapsul',
        'strength' => '500 mg',
        'purchase_price' => 1200,
        'selling_price' => 2000,
        'min_stock' => 10,
        'is_active' => true,
    ]);

    // Batch 1: Expiring earlier (2026-12-01), Stock = 10
    $this->batchEarly = MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-AMX-01',
        'expiry_date' => Carbon::parse('2026-12-01'),
        'initial_stock' => 10,
        'current_stock' => 10,
        'purchase_price' => 1200,
        'is_active' => true,
    ]);

    // Batch 2: Expiring later (2027-06-01), Stock = 50
    $this->batchLate = MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-AMX-02',
        'expiry_date' => Carbon::parse('2027-06-01'),
        'initial_stock' => 50,
        'current_stock' => 50,
        'purchase_price' => 1200,
        'is_active' => true,
    ]);
});

test('doctor can create e-prescription and pharmacist can verify and dispense with FEFO batch allocation', function () {
    // 1. Doctor creates prescription for 15 capsules of Amoxsan
    $prescData = [
        'doctor_notes' => 'Minum teratur setelah makan sampai habis',
        'items' => [
            [
                'medicine_id' => $this->medicine->id,
                'quantity' => 15, // Will consume 10 from Batch 1 (FEFO) + 5 from Batch 2
                'dosage' => '1 Kapsul',
                'frequency' => '3 x sehari',
                'instructions' => 'Habiskan (Antibiotik)',
                'route' => 'Oral',
                'duration_days' => 5,
            ],
        ],
    ];

    $this->actingAs($this->userDokter)
        ->post(route('pharmacy.store_prescription', $this->visit->id), $prescData);

    $prescription = Prescription::where('visit_id', $this->visit->id)->first();
    expect($prescription)->not->toBeNull();
    expect($prescription->prescription_number)->toMatch('/^RX-\d{8}-\d{4}$/');
    expect($prescription->status)->toBe('menunggu_verifikasi');
    expect($prescription->items)->toHaveCount(1);

    // 2. Pharmacist verifies prescription
    $this->actingAs($this->userFarmasi)
        ->post(route('pharmacy.verify', $prescription->id), [
            'pharmacist_notes' => 'Resep telah ditelaah, tidak ada interaksi obat.',
        ]);

    $prescription->refresh();
    expect($prescription->status)->toBe('diverifikasi');
    expect($prescription->pharmacist_notes)->toBe('Resep telah ditelaah, tidak ada interaksi obat.');

    // 3. Pharmacist dispenses medicine
    $this->actingAs($this->userFarmasi)
        ->post(route('pharmacy.dispense', $prescription->id), [
            'notes' => 'Obat telah diserahkan dan diedukasi kepada pasien.',
        ]);

    $prescription->refresh();
    expect($prescription->status)->toBe('selesai');

    // 4. Verify FEFO Batch Deduction:
    // Batch 1 (expiring earlier) should have 0 left (was 10, used 10)
    $this->batchEarly->refresh();
    expect($this->batchEarly->current_stock)->toBe(0);

    // Batch 2 (expiring later) should have 45 left (was 50, used 5)
    $this->batchLate->refresh();
    expect($this->batchLate->current_stock)->toBe(45);

    // 5. Verify Stock Movements (Ledger Cards)
    $movements = MedicineStockMovement::where('medicine_id', $this->medicine->id)->get();
    expect($movements)->toHaveCount(2); // One movement for batch 1, one movement for batch 2
    expect($movements->where('batch_id', $this->batchEarly->id)->first()->quantity_out)->toBe(10);
    expect($movements->where('batch_id', $this->batchLate->id)->first()->quantity_out)->toBe(5);

    // 6. Verify Billing Invoice updated with medicine cost: 15 * Rp2.000 = Rp30.000
    $this->invoice->refresh();
    expect($this->invoice->items->where('item_type', 'obat'))->toHaveCount(1);
    expect((float) $this->invoice->items->where('item_type', 'obat')->first()->subtotal)->toBe(30000.0);
});

test('dispensing prevents negative stock when requested quantity exceeds total stock', function () {
    // Attempting to prescribe 100 units when total stock is only 60 (10 + 50)
    $presc = Prescription::create([
        'prescription_number' => 'RX-20261115-0999',
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctorEmployee->id,
        'prescription_date' => now(),
        'status' => 'diverifikasi',
    ]);

    $presc->items()->create([
        'medicine_id' => $this->medicine->id,
        'quantity' => 100, // Exceeds available stock of 60
        'dosage' => '1 Kapsul',
        'frequency' => '3 x sehari',
        'instructions' => 'Sesudah makan',
    ]);

    // Expect dispensing to fail / throw exception without corrupting stock
    expect(function () use ($presc) {
        app(PharmacyService::class)->dispensePrescription($presc);
    })->toThrow(Exception::class);

    // Ensure stock remained intact (no negative stock!)
    $this->batchEarly->refresh();
    $this->batchLate->refresh();
    expect($this->batchEarly->current_stock)->toBe(10);
    expect($this->batchLate->current_stock)->toBe(50);
});
