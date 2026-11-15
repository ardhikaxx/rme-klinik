<?php

use App\Models\BillingInvoice;
use App\Models\BillingItem;
use App\Models\Employee;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleAndPermissionSeeder::class);

    // 1. Roles & Users
    $roleKasir = Role::where('name', 'admisi_kasir')->first();
    $roleKepala = Role::where('name', 'kepala_klinik')->first();

    $this->userKasir = User::create([
        'username' => 'kasir',
        'name' => 'Siti Kasir',
        'email' => 'kasir@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userKasir->roles()->attach($roleKasir->id);

    $this->userKepala = User::create([
        'username' => 'kepala',
        'name' => 'dr. Hendra Kepala',
        'email' => 'kepala@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userKepala->roles()->attach($roleKepala->id);

    $roleDokter = Role::where('name', 'dokter')->first();
    $this->userDokter = User::create([
        'username' => 'dokter1',
        'name' => 'dr. Budi Santoso',
        'email' => 'dokter1@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userDokter->roles()->attach($roleDokter->id);

    $this->doctorEmployee = Employee::create([
        'user_id' => $this->userDokter->id,
        'employee_number' => 'EMP-DOK-01',
        'name' => 'dr. Budi Santoso',
        'gender' => 'L',
        'profession' => 'dokter',
        'phone' => '081234567890',
        'address' => 'Jl. Ijen No. 12 Malang',
        'hire_date' => '2020-01-01',
        'is_active' => true,
    ]);

    // 2. Polyclinic
    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'type' => 'umum',
        'is_active' => true,
    ]);

    // 3. Payment Methods
    $this->payCash = PaymentMethod::create([
        'code' => 'cash',
        'name' => 'Tunai (Cash)',
        'account_info' => 'Kasir Tunai',
        'is_active' => true,
    ]);

    $this->payQris = PaymentMethod::create([
        'code' => 'qris',
        'name' => 'QRIS Dinamis',
        'account_info' => 'NMID: ID1020304050607',
        'is_active' => true,
    ]);

    // 4. Patient & Visit
    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000700',
        'nik' => '3573010101990008',
        'name' => 'Dewi Sartika',
        'date_of_birth' => '1999-08-17',
        'gender' => 'P',
        'blood_type' => 'O',
        'phone' => '081234567789',
        'address' => 'Jl. Bandung No. 10 Malang',
        'marital_status' => 'kawin',
        'is_verified' => true,
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-0070',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctorEmployee->id,
        'queue_number' => 'UM-01',
        'registration_date' => now(),
        'payment_type' => 'umum',
        'status' => 'menunggu_pembayaran',
        'registered_by' => $this->userKasir->id,
    ]);

    // 5. Billing Invoice with itemized charges (Total Rp 150.000)
    // - Konsultasi Dokter: Rp 50.000
    // - Tindakan Injeksi: Rp 40.000
    // - Obat Paracetamol: Rp 25.000
    // - Pemeriksaan Lab Hb: Rp 35.000
    $this->invoice = BillingInvoice::create([
        'invoice_number' => 'INV-20261115-0070',
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'total_amount' => 150000,
        'discount_amount' => 0,
        'final_amount' => 150000,
        'paid_amount' => 0,
        'balance_amount' => 150000,
        'status' => 'belum_dibayar',
        'created_by' => $this->userKasir->id,
    ]);

    BillingItem::create([
        'billing_invoice_id' => $this->invoice->id,
        'item_type' => 'konsultasi',
        'item_code' => 'KONS-01',
        'item_name' => 'Jasa Konsultasi & Pemeriksaan Dokter',
        'quantity' => 1,
        'unit_price' => 50000,
        'subtotal' => 50000,
    ]);

    BillingItem::create([
        'billing_invoice_id' => $this->invoice->id,
        'item_type' => 'tindakan_medis',
        'item_code' => 'TDK-01',
        'item_name' => 'Tindakan Injeksi Intramuskular',
        'quantity' => 1,
        'unit_price' => 40000,
        'subtotal' => 40000,
    ]);

    BillingItem::create([
        'billing_invoice_id' => $this->invoice->id,
        'item_type' => 'obat',
        'item_code' => 'MED-01',
        'item_name' => 'Paracetamol 500mg Strip (10 Tablet)',
        'quantity' => 1,
        'unit_price' => 25000,
        'subtotal' => 25000,
    ]);

    BillingItem::create([
        'billing_invoice_id' => $this->invoice->id,
        'item_type' => 'laboratorium',
        'item_code' => 'LAB-01',
        'item_name' => 'Pemeriksaan Darah Lengkap (Hb)',
        'quantity' => 1,
        'unit_price' => 35000,
        'subtotal' => 35000,
    ]);
});

test('cashier can view billing index and invoice detail with itemized breakdown', function () {
    $indexRes = $this->actingAs($this->userKasir)->get(route('billing.index'));
    $indexRes->assertOk();
    $indexRes->assertSee('INV-20261115-0070');
    $indexRes->assertSee('Dewi Sartika');

    $showRes = $this->actingAs($this->userKasir)->get(route('billing.show', $this->invoice->id));
    $showRes->assertOk();
    $showRes->assertSee('Jasa Konsultasi &amp; Pemeriksaan Dokter', false);
    $showRes->assertSee('Tindakan Injeksi Intramuskular');
    $showRes->assertSee('Paracetamol 500mg');
    $showRes->assertSee('Pemeriksaan Darah Lengkap');
    $showRes->assertSee('150.000');
});

test('manager can apply discount with reason and recalculate balance amount', function () {
    // Kepala Klinik applies Rp20.000 promo discount
    $response = $this->actingAs($this->userKepala)->post(route('billing.discount', $this->invoice->id), [
        'discount_amount' => 20000,
        'discount_reason' => 'Program Diskon Hari Kesehatan Nasional',
    ]);

    $response->assertSessionHas('success');

    $this->invoice->refresh();
    expect((float) $this->invoice->discount_amount)->toBe(20000.0);
    expect((float) $this->invoice->final_amount)->toBe(130000.0);
    expect((float) $this->invoice->balance_amount)->toBe(130000.0);
    expect($this->invoice->discount_reason)->toBe('Program Diskon Hari Kesehatan Nasional');
});

test('unauthorized user cannot apply discount', function () {
    $response = $this->actingAs($this->userKasir)->post(route('billing.discount', $this->invoice->id), [
        'discount_amount' => 20000,
        'discount_reason' => 'Diskon ilegal',
    ]);

    $response->assertForbidden();
});

test('cashier can process partial payment', function () {
    $response = $this->actingAs($this->userKasir)->post(route('billing.pay', $this->invoice->id), [
        'payment_method_id' => $this->payCash->id,
        'amount_paid' => 50000,
        'amount_tendered' => 50000,
        'notes' => 'Pembayaran uang muka DP',
    ]);

    $response->assertSessionHas('success');

    $this->invoice->refresh();
    expect((float) $this->invoice->paid_amount)->toBe(50000.0);
    expect((float) $this->invoice->balance_amount)->toBe(100000.0);
    expect($this->invoice->status)->toBe('pembayaran_sebagian');

    $payment = Payment::where('billing_invoice_id', $this->invoice->id)->first();
    expect($payment)->not->toBeNull();
    expect((float) $payment->amount_paid)->toBe(50000.0);
    expect((float) $payment->change_due)->toBe(0.0);
    expect($payment->receipt_number)->toMatch('/^KW-\d{8}-\d{4}$/');
});

test('cashier can process settlement payment with cash tender and change calculation', function () {
    // Pay full Rp 150.000 with Rp 200.000 cash (change Rp 50.000)
    $response = $this->actingAs($this->userKasir)->post(route('billing.pay', $this->invoice->id), [
        'payment_method_id' => $this->payCash->id,
        'amount_paid' => 150000,
        'amount_tendered' => 200000,
        'notes' => 'Pelunasan tagihan rawat jalan',
    ]);

    $response->assertSessionHas('success');

    $this->invoice->refresh();
    expect((float) $this->invoice->paid_amount)->toBe(150000.0);
    expect((float) $this->invoice->balance_amount)->toBe(0.0);
    expect($this->invoice->status)->toBe('lunas');

    // Visit status automatically updated to selesai
    $this->visit->refresh();
    expect($this->visit->status)->toBe('selesai');

    $payment = Payment::where('billing_invoice_id', $this->invoice->id)->first();
    expect($payment)->not->toBeNull();
    expect((float) $payment->amount_paid)->toBe(150000.0);
    expect((float) $payment->amount_tendered)->toBe(200000.0);
    expect((float) $payment->change_due)->toBe(50000.0);

    // Verify receipt view can be rendered
    $receiptRes = $this->actingAs($this->userKasir)->get(route('billing.receipt', $payment->id));
    $receiptRes->assertOk();
    $receiptRes->assertSee($payment->receipt_number);
    $receiptRes->assertSee('Dewi Sartika');
    $receiptRes->assertSee('150.000');
});

test('cannot pay more than invoice balance amount', function () {
    $response = $this->actingAs($this->userKasir)->post(route('billing.pay', $this->invoice->id), [
        'payment_method_id' => $this->payCash->id,
        'amount_paid' => 200000, // Balance is only 150.000
    ]);

    $response->assertSessionHasErrors(['amount_paid']);
});

test('manager can process refund with documented reason', function () {
    // 1. First make a payment of Rp 150.000
    $this->actingAs($this->userKasir)->post(route('billing.pay', $this->invoice->id), [
        'payment_method_id' => $this->payCash->id,
        'amount_paid' => 150000,
        'amount_tendered' => 150000,
    ]);

    $this->invoice->refresh();
    expect($this->invoice->status)->toBe('lunas');

    // 2. Manager processes partial refund of Rp 35.000 (e.g., cancelled lab test)
    $response = $this->actingAs($this->userKepala)->post(route('billing.refund', $this->invoice->id), [
        'refund_amount' => 35000,
        'reason' => 'Pemeriksaan laboratorium dibatalkan atas permintaan pasien',
    ]);

    $response->assertSessionHas('success');

    $this->invoice->refresh();
    expect((float) $this->invoice->paid_amount)->toBe(115000.0);
    expect((float) $this->invoice->balance_amount)->toBe(35000.0);
    expect($this->invoice->status)->toBe('refund');
});

test('cannot refund amount greater than total paid amount', function () {
    // Payment of Rp 50.000
    $this->actingAs($this->userKasir)->post(route('billing.pay', $this->invoice->id), [
        'payment_method_id' => $this->payCash->id,
        'amount_paid' => 50000,
    ]);

    // Try to refund Rp 100.000 (paid is only 50.000)
    $response = $this->actingAs($this->userKepala)->post(route('billing.refund', $this->invoice->id), [
        'refund_amount' => 100000,
        'reason' => 'Kelebihan refund',
    ]);

    $response->assertSessionHasErrors(['refund_amount']);
});
