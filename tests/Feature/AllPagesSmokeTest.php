<?php

use App\Models\BillingInvoice;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LaboratoryRequest;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\MedicineCategory;
use App\Models\MedicineUnit;
use App\Models\Odontogram;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Polyclinic;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
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

    $this->dept = Department::create([
        'code' => 'DEP-MED',
        'name' => 'Pelayanan Medis',
    ]);

    // Create users with different roles
    $roleKepala = Role::where('name', 'kepala_klinik')->first();
    $roleAdmisi = Role::where('name', 'admisi_kasir')->first();
    $rolePerawat = Role::where('name', 'perawat_bidan')->first();
    $roleDokter = Role::where('name', 'dokter')->first();
    $roleFarmasi = Role::where('name', 'farmasi')->first();

    $this->userKepala = User::create([
        'name' => 'dr. Hendra Kepala',
        'username' => 'kepala',
        'email' => 'kepala@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userKepala->roles()->attach($roleKepala->id);

    $this->userAdmisi = User::create([
        'name' => 'Petugas Admisi',
        'username' => 'admisi',
        'email' => 'admisi@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userAdmisi->roles()->attach($roleAdmisi->id);

    $this->userPerawat = User::create([
        'name' => 'Siti Perawat',
        'username' => 'perawat',
        'email' => 'perawat@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userPerawat->roles()->attach($rolePerawat->id);

    $this->userDokter = User::create([
        'name' => 'dr. Budi Dokter',
        'username' => 'dokter',
        'email' => 'dokter@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userDokter->roles()->attach($roleDokter->id);

    $this->userFarmasi = User::create([
        'name' => 'Apt. Dewi Farmasi',
        'username' => 'farmasi',
        'email' => 'farmasi@klinik.test',
        'password' => Hash::make('password123'),
        'is_active' => true,
    ]);
    $this->userFarmasi->roles()->attach($roleFarmasi->id);

    // Employees
    $this->doctorEmp = Employee::create([
        'user_id' => $this->userDokter->id,
        'department_id' => $this->dept->id,
        'employee_number' => 'EMP-DOC-001',
        'name' => 'dr. Budi Dokter',
        'gender' => 'L',
        'profession' => 'dokter',
        'sip_str_number' => 'SIP/123/2026',
        'is_active' => true,
    ]);

    $this->poly = Polyclinic::create([
        'code' => 'POL-UMU',
        'name' => 'Poli Umum',
        'is_active' => true,
    ]);

    $this->polyGigi = Polyclinic::create([
        'code' => 'POL-GIG',
        'name' => 'Poli Gigi',
        'is_active' => true,
    ]);

    $this->polyKia = Polyclinic::create([
        'code' => 'POL-KIA',
        'name' => 'Poli KIA',
        'is_active' => true,
    ]);

    $this->patient = Patient::create([
        'medical_record_number' => 'RM-2026-000001',
        'nik' => '3201234567890001',
        'name' => 'Slamet Riyadi',
        'gender' => 'L',
        'birth_place' => 'Surabaya',
        'date_of_birth' => '1985-04-12',
        'phone' => '081234567890',
        'address' => 'Jl. Diponegoro No. 12',
    ]);

    $this->visit = Visit::create([
        'visit_number' => 'REG-20261115-0001',
        'patient_id' => $this->patient->id,
        'polyclinic_id' => $this->poly->id,
        'doctor_id' => $this->doctorEmp->id,
        'registered_by' => $this->userAdmisi->id,
        'registration_date' => Carbon::today(),
        'visit_type' => 'baru',
        'status' => 'pemeriksaan_dokter_berlangsung',
    ]);

    $this->medRecord = MedicalRecord::create([
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctorEmp->id,
        'examination_time' => Carbon::now(),
        'subjective' => 'Demam sejak 2 hari lalu',
        'objective' => 'Suhu 38.5 C',
        'assessment' => 'Febris Akut',
        'plan' => 'Paracetamol 500mg 3x1',
        'status' => 'draft',
    ]);

    $this->odontogram = Odontogram::create([
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctorEmp->id,
        'notes' => 'Pemeriksaan gigi',
    ]);

    $cat = MedicineCategory::create(['code' => 'CAT-ANAL', 'name' => 'Analgetik']);
    $unit = MedicineUnit::create(['code' => 'TAB', 'name' => 'Tablet']);

    $this->medicine = Medicine::create([
        'code' => 'OBT-001',
        'category_id' => $cat->id,
        'unit_id' => $unit->id,
        'brand_name' => 'Paracetamol 500mg',
        'generic_name' => 'Paracetamol',
        'dosage_form' => 'tablet',
        'purchase_price' => 5000,
        'selling_price' => 8000,
        'min_stock' => 10,
        'is_active' => true,
    ]);

    $this->batch = MedicineBatch::create([
        'medicine_id' => $this->medicine->id,
        'batch_number' => 'BATCH-001',
        'expiry_date' => Carbon::today()->addMonths(6),
        'initial_stock' => 100,
        'current_stock' => 100,
        'purchase_price' => 5000,
        'is_active' => true,
    ]);

    $this->rx = Prescription::create([
        'visit_id' => $this->visit->id,
        'doctor_id' => $this->doctorEmp->id,
        'prescription_number' => 'RX-20261115-0001',
        'prescription_date' => Carbon::now(),
        'status' => 'menunggu_verifikasi',
    ]);

    $this->rxItem = PrescriptionItem::create([
        'prescription_id' => $this->rx->id,
        'medicine_id' => $this->medicine->id,
        'quantity' => 10,
        'dosage' => '500mg',
        'frequency' => '3x1 hari',
        'route' => 'oral',
        'duration_days' => 3,
        'instructions' => 'sesudah makan',
    ]);

    $this->invoice = BillingInvoice::create([
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'invoice_number' => 'INV-20261115-0001',
        'total_amount' => 50000,
        'discount_amount' => 0,
        'final_amount' => 50000,
        'paid_amount' => 50000,
        'balance_amount' => 0,
        'status' => 'lunas',
        'created_by' => $this->userAdmisi->id,
    ]);

    $payMethod = PaymentMethod::create(['code' => 'tunai', 'name' => 'Tunai', 'is_active' => true]);

    $this->payment = Payment::create([
        'receipt_number' => 'RCP-20261115-0001',
        'billing_invoice_id' => $this->invoice->id,
        'payment_method_id' => $payMethod->id,
        'payment_time' => Carbon::now(),
        'amount_paid' => 50000,
        'amount_tendered' => 50000,
        'change_due' => 0,
        'cashier_id' => $this->userAdmisi->id,
    ]);

    $this->labReq = LaboratoryRequest::create([
        'visit_id' => $this->visit->id,
        'doctor_id' => $this->doctorEmp->id,
        'request_number' => 'LAB-20261115-0001',
        'request_time' => Carbon::now(),
        'status' => 'menunggu_sampel',
        'clinical_notes' => 'Cek Darah Rutin',
    ]);

    $this->doc = MedicalDocument::create([
        'visit_id' => $this->visit->id,
        'patient_id' => $this->patient->id,
        'doctor_id' => $this->doctorEmp->id,
        'type' => 'surat_keterangan_sakit',
        'document_number' => 'DOC-SKS-20261115-0001',
        'issue_date' => Carbon::today(),
        'content_data' => ['rest_days' => 3, 'reason' => 'Demam tinggi'],
    ]);
});

test('role dashboards render correctly without errors', function () {
    // 1. Kepala Klinik Dashboard
    $resp = $this->actingAs($this->userKepala)->get(route('dashboard'))->assertOk();
    $resp->assertSee('images/logo-white.png');
    $resp->assertSee('images/logo-icon-white.png');

    // 2. Admisi Dashboard
    $this->actingAs($this->userAdmisi)->get(route('dashboard'))->assertOk();

    // 3. Perawat Dashboard
    $this->actingAs($this->userPerawat)->get(route('dashboard'))->assertOk();

    // 4. Dokter Dashboard
    $this->actingAs($this->userDokter)->get(route('dashboard'))->assertOk();

    // 5. Farmasi Dashboard
    $this->actingAs($this->userFarmasi)->get(route('dashboard'))->assertOk();
});

test('patient and visit management pages render correctly', function () {
    $this->actingAs($this->userAdmisi)->get(route('patients.index'))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('patients.create'))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('patients.show', $this->patient->id))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('patients.edit', $this->patient->id))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('patients.card', $this->patient->id))->assertOk();

    $this->actingAs($this->userAdmisi)->get(route('visits.index'))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('visits.create'))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('visits.show', $this->visit->id))->assertOk();
});

test('nursing assessment pages render correctly', function () {
    $this->actingAs($this->userPerawat)->get(route('nursing.index'))->assertOk();
    $this->actingAs($this->userPerawat)->get(route('nursing.create', $this->visit->id))->assertOk();
});

test('clinical examination, odontogram and kia pages render correctly', function () {
    $this->actingAs($this->userDokter)->get(route('clinical.index'))->assertOk();
    $this->actingAs($this->userDokter)->get(route('clinical.examination', $this->visit->id))->assertOk();
    $this->actingAs($this->userDokter)->get(route('clinical.timeline', $this->visit->id))->assertOk();

    $this->actingAs($this->userDokter)->get(route('odontogram.index'))->assertOk();
    $this->actingAs($this->userDokter)->get(route('odontogram.show', $this->visit->id))->assertOk();
    $this->actingAs($this->userDokter)->get(route('odontogram.print', $this->visit->id))->assertOk();

    $this->actingAs($this->userDokter)->get(route('kia.index'))->assertOk();
    $this->actingAs($this->userDokter)->get(route('kia.show', $this->visit->id))->assertOk();
    $this->actingAs($this->userDokter)->get(route('kia.chart.data', $this->patient->id))->assertOk();
});

test('pharmacy, laboratory, billing and documents pages render correctly', function () {
    $this->actingAs($this->userFarmasi)->get(route('pharmacy.prescriptions'))->assertOk();
    $this->actingAs($this->userFarmasi)->get(route('pharmacy.show_prescription', $this->rx->id))->assertOk();
    $this->actingAs($this->userFarmasi)->get(route('pharmacy.print_label', $this->rx->id))->assertOk();
    $this->actingAs($this->userFarmasi)->get(route('pharmacy.medicines'))->assertOk();
    $this->actingAs($this->userFarmasi)->get(route('pharmacy.stock_card', $this->medicine->id))->assertOk();
    $this->actingAs($this->userFarmasi)->get(route('pharmacy.receipts'))->assertOk();
    $this->actingAs($this->userFarmasi)->get(route('pharmacy.opnames'))->assertOk();

    $this->actingAs($this->userDokter)->get(route('laboratory.index'))->assertOk();
    $this->actingAs($this->userDokter)->get(route('laboratory.show', $this->labReq->id))->assertOk();
    $this->actingAs($this->userDokter)->get(route('laboratory.print', $this->labReq->id))->assertOk();

    $this->actingAs($this->userAdmisi)->get(route('billing.index'))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('billing.show', $this->invoice->id))->assertOk();
    $this->actingAs($this->userAdmisi)->get(route('billing.receipt', $this->payment->id))->assertOk();

    $this->actingAs($this->userDokter)->get(route('documents.index'))->assertOk();
    $this->actingAs($this->userDokter)->get(route('documents.create', $this->visit->id))->assertOk();
    $this->actingAs($this->userDokter)->get(route('documents.show', $this->doc->id))->assertOk();
    $this->actingAs($this->userDokter)->get(route('documents.print', $this->doc->id))->assertOk();
});

test('administration and settings pages render correctly', function () {
    $this->actingAs($this->userKepala)->get(route('settings.tariffs'))->assertOk();
    $this->actingAs($this->userKepala)->get(route('settings.schedules'))->assertOk();
    $this->actingAs($this->userKepala)->get(route('settings.users'))->assertOk();
    $this->actingAs($this->userKepala)->get(route('audit.index'))->assertOk();
    $this->actingAs($this->userKepala)->get(route('fhir.index'))->assertOk();
    $this->actingAs($this->userKepala)->get(route('reports.index'))->assertOk();
});
