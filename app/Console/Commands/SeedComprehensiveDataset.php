<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedComprehensiveDataset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:seed-comprehensive
                            {--patients=100000 : Jumlah pasien unik yang akan dibuat}
                            {--visits=200000 : Jumlah kunjungan operasional yang akan dibuat}
                            {--chunk=1000 : Ukuran batch insert per transaksi}
                            {--fresh : Bersihkan data operasional sebelum generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menghasilkan dataset sintetis berskala besar (100.000+ pasien, 1.000.000+ record operasional 2020-2026)';

    /**
     * Cache pool master data
     */
    protected array $employees = [];

    protected array $polyclinics = [];

    protected array $icd10Pool = [];

    protected array $icd9Pool = [];

    protected array $medicines = [];

    protected array $medicineBatches = [];

    protected array $batchStockMap = [];

    protected array $suppliers = [];

    protected array $tariffs = [];

    protected array $laboratories = [];

    protected array $paymentMethods = [];

    protected array $users = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);
        DB::disableQueryLog();

        $targetPatients = (int) $this->option('patients');
        $targetVisits = (int) $this->option('visits');
        $chunkSize = (int) $this->option('chunk');
        $isFresh = (bool) $this->option('fresh');

        $this->info('======================================================================');
        $this->info('  GENERATOR DATASET SINTETIS BERSKALA BESAR RME KLINIK (2020 - 2026)  ');
        $this->info("  Target: {$targetPatients} Pasien Unik | {$targetVisits} Kunjungan Terintegrasi");
        $this->info('======================================================================');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        if ($isFresh) {
            $this->truncateOperationalTables();
        }

        $this->setupMasterData();
        $this->seedPatients($targetPatients, $chunkSize);
        $this->seedVisitsAndOperations($targetVisits, $targetPatients, $chunkSize);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('');
        $this->info('✓ GENERASI DATASET SELESAI!');
        $this->call('clinic:validate-dataset');

        return Command::SUCCESS;
    }

    /**
     * Truncate operational tables while preserving base users, roles, and static master catalogs.
     */
    protected function truncateOperationalTables(): void
    {
        $this->warn('Membersihkan tabel-tabel operasional...');
        $tables = [
            'patient_emergency_contacts',
            'patient_allergies',
            'patients',
            'visit_status_histories',
            'vital_signs',
            'nursing_assessments',
            'medical_record_diagnoses',
            'medical_record_procedures',
            'medical_record_addendums',
            'medical_records',
            'odontogram_teeth',
            'odontograms',
            'antenatal_cares',
            'child_growths',
            'prescription_compound_items',
            'prescription_compounds',
            'prescription_items',
            'prescriptions',
            'dispensing_items',
            'dispensing_transactions',
            'medicine_stock_movements',
            'medicine_receipt_items',
            'medicine_receipts',
            'medicine_batches',
            'laboratory_results',
            'laboratory_request_items',
            'laboratory_requests',
            'payments',
            'refunds',
            'medical_fee_transactions',
            'billing_items',
            'billing_invoices',
            'medical_documents',
            'patient_documents',
            'audit_logs',
            'fhir_sync_logs',
            'fhir_mappings',
            'visits',
        ];

        foreach ($tables as $tbl) {
            DB::table($tbl)->truncate();
        }
        $this->info('Tabel operasional berhasil dibersihkan.');
    }

    /**
     * Load & setup essential master records (medicines, batches, tariffs, employees).
     */
    protected function setupMasterData(): void
    {
        $this->comment('Memuat dan melengkapi master data...');

        // 1. Employees & Polyclinics
        $this->employees = DB::table('employees')->get()->toArray();
        $this->polyclinics = DB::table('polyclinics')->get()->toArray();
        $this->users = DB::table('users')->pluck('id')->toArray();
        $this->suppliers = DB::table('suppliers')->get()->toArray();
        $this->paymentMethods = DB::table('payment_methods')->get()->toArray();
        $this->tariffs = DB::table('service_tariffs')->get()->keyBy('code')->toArray();
        $this->laboratories = DB::table('laboratories')->get()->toArray();

        // 2. Expand master medicines if fewer than 50
        $currentMedCount = DB::table('medicines')->count();
        if ($currentMedCount < 50) {
            $this->expandMedicinesCatalog();
        }
        $this->medicines = DB::table('medicines')->get()->keyBy('code')->toArray();

        // 3. Ensure ample batches & receipts for FEFO non-negative stock
        $this->ensureMedicineBatchesAndReceipts();

        // 4. Load ICD-10 & ICD-9 clinical pools
        $this->loadIcdCodesPool();

        // 5. Ensure doctor schedules for Poli KIA
        $kiaSchedules = DB::table('doctor_schedules')->where('polyclinic_id', 3)->count();
        if ($kiaSchedules === 0) {
            $schedules = [];
            for ($d = 1; $d <= 6; $d++) {
                $schedules[] = [
                    'employee_id' => 3,
                    'polyclinic_id' => 3,
                    'day_of_week' => $d,
                    'start_time' => '08:00:00',
                    'end_time' => '13:00:00',
                    'quota' => 30,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('doctor_schedules')->insert($schedules);
        }
    }

    /**
     * Expand medicine catalog to realistic 60+ clinic medicines.
     */
    protected function expandMedicinesCatalog(): void
    {
        $categories = DB::table('medicine_categories')->pluck('id', 'name')->toArray();
        $units = DB::table('medicine_units')->pluck('id', 'code')->toArray();

        $catObatUmum = $categories['Obat Bebas & Bebas Terbatas'] ?? ($categories['Obat Keras'] ?? 1);
        $catKeras = $categories['Obat Keras'] ?? 1;
        $catAlkes = $categories['Alat Kesehatan & BMHP'] ?? 1;

        $unitTab = $units['TAB'] ?? 1;
        $unitKps = $units['KPS'] ?? 1;
        $unitBtl = $units['BTL'] ?? 1;
        $unitTbg = $units['TBG'] ?? 1;
        $unitSch = $units['SCH'] ?? 1;

        $newMedicines = [
            ['code' => 'MED-010', 'generic_name' => 'Amlodipine Besylate', 'brand_name' => 'Amlodipine 5 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '5 mg', 'purchase_price' => 400, 'selling_price' => 700],
            ['code' => 'MED-011', 'generic_name' => 'Amlodipine Besylate', 'brand_name' => 'Amlodipine 10 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 600, 'selling_price' => 1000],
            ['code' => 'MED-012', 'generic_name' => 'Captopril', 'brand_name' => 'Captopril 25 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '25 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['code' => 'MED-013', 'generic_name' => 'Candesartan Cilexetil', 'brand_name' => 'Candesartan 8 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '8 mg', 'purchase_price' => 1500, 'selling_price' => 2500],
            ['code' => 'MED-014', 'generic_name' => 'Metformin HCl', 'brand_name' => 'Metformin 500 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 350, 'selling_price' => 650],
            ['code' => 'MED-015', 'generic_name' => 'Glimepiride', 'brand_name' => 'Glimepiride 2 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '2 mg', 'purchase_price' => 500, 'selling_price' => 900],
            ['code' => 'MED-016', 'generic_name' => 'Simvastatin', 'brand_name' => 'Simvastatin 10 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 500, 'selling_price' => 900],
            ['code' => 'MED-017', 'generic_name' => 'Simvastatin', 'brand_name' => 'Simvastatin 20 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '20 mg', 'purchase_price' => 800, 'selling_price' => 1400],
            ['code' => 'MED-018', 'generic_name' => 'Allopurinol', 'brand_name' => 'Allopurinol 100 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '100 mg', 'purchase_price' => 400, 'selling_price' => 700],
            ['code' => 'MED-019', 'generic_name' => 'Ciprofloxacin HCl', 'brand_name' => 'Ciprofloxacin 500 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 900, 'selling_price' => 1600],
            ['code' => 'MED-020', 'generic_name' => 'Cefadroxil Monohydrate', 'brand_name' => 'Cefadroxil 500 mg', 'category_id' => $catKeras, 'unit_id' => $unitKps, 'dosage_form' => 'Kapsul', 'strength' => '500 mg', 'purchase_price' => 1100, 'selling_price' => 1800],
            ['code' => 'MED-021', 'generic_name' => 'Azithromycin Dihydrate', 'brand_name' => 'Azithromycin 500 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 4500, 'selling_price' => 7000],
            ['code' => 'MED-022', 'generic_name' => 'Cotrimoxazole', 'brand_name' => 'Cotrimoxazole 480 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '480 mg', 'purchase_price' => 400, 'selling_price' => 750],
            ['code' => 'MED-023', 'generic_name' => 'Metronidazole', 'brand_name' => 'Metronidazole 500 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 450, 'selling_price' => 800],
            ['code' => 'MED-024', 'generic_name' => 'Lansoprazole', 'brand_name' => 'Lansoprazole 30 mg', 'category_id' => $catKeras, 'unit_id' => $unitKps, 'dosage_form' => 'Kapsul', 'strength' => '30 mg', 'purchase_price' => 1200, 'selling_price' => 2200],
            ['code' => 'MED-025', 'generic_name' => 'Ranitidine HCl', 'brand_name' => 'Ranitidine 150 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '150 mg', 'purchase_price' => 400, 'selling_price' => 800],
            ['code' => 'MED-026', 'generic_name' => 'Domperidone', 'brand_name' => 'Domperidone 10 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 450, 'selling_price' => 850],
            ['code' => 'MED-027', 'generic_name' => 'Ondansetron HCl', 'brand_name' => 'Ondansetron 4 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '4 mg', 'purchase_price' => 1200, 'selling_price' => 2000],
            ['code' => 'MED-028', 'generic_name' => 'Oralit Sachet', 'brand_name' => 'Oralit 200 ml', 'category_id' => $catObatUmum, 'unit_id' => $unitSch, 'dosage_form' => 'Serbuk', 'strength' => '4.1 g', 'purchase_price' => 600, 'selling_price' => 1200],
            ['code' => 'MED-029', 'generic_name' => 'Zinc Sulfate', 'brand_name' => 'Zinc Dispersible 20 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '20 mg', 'purchase_price' => 700, 'selling_price' => 1300],
            ['code' => 'MED-030', 'generic_name' => 'Loperamide HCl', 'brand_name' => 'Loperamide 2 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '2 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['code' => 'MED-031', 'generic_name' => 'Loratadine', 'brand_name' => 'Loratadine 10 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 500, 'selling_price' => 950],
            ['code' => 'MED-032', 'generic_name' => 'Dexamethasone', 'brand_name' => 'Dexamethasone 0.5 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '0.5 mg', 'purchase_price' => 200, 'selling_price' => 450],
            ['code' => 'MED-033', 'generic_name' => 'Methylprednisolone', 'brand_name' => 'Methylprednisolone 4 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '4 mg', 'purchase_price' => 600, 'selling_price' => 1100],
            ['code' => 'MED-034', 'generic_name' => 'Ambroxol HCl', 'brand_name' => 'Ambroxol 30 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '30 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['code' => 'MED-035', 'generic_name' => 'Gliseril Guaiakolat (GG)', 'brand_name' => 'Gliseril Guaiakolat 100 mg', 'category_id' => $catObatUmum, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '100 mg', 'purchase_price' => 200, 'selling_price' => 450],
            ['code' => 'MED-036', 'generic_name' => 'Meloxicam', 'brand_name' => 'Meloxicam 15 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '15 mg', 'purchase_price' => 800, 'selling_price' => 1500],
            ['code' => 'MED-037', 'generic_name' => 'Natrium Diklofenak', 'brand_name' => 'Natrium Diklofenak 50 mg', 'category_id' => $catKeras, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '50 mg', 'purchase_price' => 400, 'selling_price' => 750],
            ['code' => 'MED-038', 'generic_name' => 'Vitamin B Kompleks', 'brand_name' => 'Vitamin B Complex', 'category_id' => $catObatUmum, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '1 tab', 'purchase_price' => 250, 'selling_price' => 500],
            ['code' => 'MED-039', 'generic_name' => 'Ferrous Fumarate + Asam Folat', 'brand_name' => 'Tablet Tambah Darah (Fe)', 'category_id' => $catObatUmum, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '60 mg Fe / 400 mcg Folat', 'purchase_price' => 300, 'selling_price' => 600],
            ['code' => 'MED-040', 'generic_name' => 'Kalsium Laktat (Kalk)', 'brand_name' => 'Kalsium Laktat 500 mg', 'category_id' => $catObatUmum, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['code' => 'MED-041', 'generic_name' => 'Asam Folat', 'brand_name' => 'Asam Folat 400 mcg', 'category_id' => $catObatUmum, 'unit_id' => $unitTab, 'dosage_form' => 'Tablet', 'strength' => '400 mcg', 'purchase_price' => 200, 'selling_price' => 450],
            ['code' => 'MED-042', 'generic_name' => 'Paracetamol Sirup', 'brand_name' => 'Paracetamol Sirup 120 mg/5ml', 'category_id' => $catObatUmum, 'unit_id' => $unitBtl, 'dosage_form' => 'Sirup', 'strength' => '120 mg/5ml (60ml)', 'purchase_price' => 6500, 'selling_price' => 11000],
            ['code' => 'MED-043', 'generic_name' => 'Amoxicillin Dry Syrup', 'brand_name' => 'Amoxicillin Sirup Kering 125 mg/5ml', 'category_id' => $catKeras, 'unit_id' => $unitBtl, 'dosage_form' => 'Sirup', 'strength' => '125 mg/5ml (60ml)', 'purchase_price' => 8500, 'selling_price' => 14000],
            ['code' => 'MED-044', 'generic_name' => 'Ambroxol Sirup', 'brand_name' => 'Ambroxol Sirup 15 mg/5ml', 'category_id' => $catKeras, 'unit_id' => $unitBtl, 'dosage_form' => 'Sirup', 'strength' => '15 mg/5ml (60ml)', 'purchase_price' => 6000, 'selling_price' => 10500],
            ['code' => 'MED-045', 'generic_name' => 'Hidrokortison Asetat', 'brand_name' => 'Hidrokortison Krim 2.5%', 'category_id' => $catKeras, 'unit_id' => $unitTbg, 'dosage_form' => 'Salep', 'strength' => '5 g', 'purchase_price' => 5000, 'selling_price' => 8500],
            ['code' => 'MED-046', 'generic_name' => 'Betametason Valerat', 'brand_name' => 'Betametason Krim 0.1%', 'category_id' => $catKeras, 'unit_id' => $unitTbg, 'dosage_form' => 'Salep', 'strength' => '5 g', 'purchase_price' => 4500, 'selling_price' => 8000],
            ['code' => 'MED-047', 'generic_name' => 'Gentamisin Sulfat', 'brand_name' => 'Gentamisin Salep Kulit 0.1%', 'category_id' => $catKeras, 'unit_id' => $unitTbg, 'dosage_form' => 'Salep', 'strength' => '5 g', 'purchase_price' => 4000, 'selling_price' => 7500],
            ['code' => 'MED-048', 'generic_name' => 'Ketokonazol', 'brand_name' => 'Ketokonazol Krim 2%', 'category_id' => $catKeras, 'unit_id' => $unitTbg, 'dosage_form' => 'Salep', 'strength' => '10 g', 'purchase_price' => 7000, 'selling_price' => 12000],
            ['code' => 'MED-049', 'generic_name' => 'Klorheksidin Glukonat', 'brand_name' => 'Minosep Obat Kumur 0.2%', 'category_id' => $catObatUmum, 'unit_id' => $unitBtl, 'dosage_form' => 'Cairan', 'strength' => '150 ml', 'purchase_price' => 18000, 'selling_price' => 26000],
            ['code' => 'MED-050', 'generic_name' => 'Kloramfenikol', 'brand_name' => 'Kloramfenikol Tetes Telinga 3%', 'category_id' => $catKeras, 'unit_id' => $unitBtl, 'dosage_form' => 'Tetes', 'strength' => '5 ml', 'purchase_price' => 5500, 'selling_price' => 9500],
        ];

        $now = now();
        foreach ($newMedicines as &$m) {
            $m['min_stock'] = 20;
            $m['is_active'] = true;
            $m['created_at'] = $now;
            $m['updated_at'] = $now;
        }

        DB::table('medicines')->insertOrIgnore($newMedicines);
    }

    /**
     * Ensure every medicine has active batches and stock movements with ample stock.
     */
    protected function ensureMedicineBatchesAndReceipts(): void
    {
        $meds = DB::table('medicines')->get();
        $supplierId = $this->suppliers[0]->id ?? 1;
        $adminUserId = $this->users[0] ?? 1;

        $batchInserts = [];
        $receiptInserts = [];
        $receiptItemInserts = [];
        $movementInserts = [];

        // Check if batches already exist
        $existingBatchCount = DB::table('medicine_batches')->count();
        if ($existingBatchCount >= 100) {
            $this->loadBatchesIntoMemory();

            return;
        }

        $now = now();
        $batchIdCounter = 1;
        $receiptIdCounter = 1;

        foreach ($meds as $med) {
            // Create 3 batches per medicine: 2024, 2026, 2028
            $years = [2024, 2026, 2028];
            foreach ($years as $idx => $yr) {
                $batchNo = "BTH-{$med->code}-{$yr}-".($idx + 1);
                $expiry = "{$yr}-12-31";
                $initialStock = 50000; // 50,000 units ensures non-negative stock throughout all prescriptions

                $batchInserts[] = [
                    'id' => $batchIdCounter,
                    'medicine_id' => $med->id,
                    'batch_number' => $batchNo,
                    'expiry_date' => $expiry,
                    'current_stock' => $initialStock,
                    'purchase_price' => $med->purchase_price,
                    'supplier_id' => $supplierId,
                    'created_at' => Carbon::create(2020, 1, 1, 8, 0, 0),
                    'updated_at' => $now,
                ];

                $receiptNo = "RCV-202001-{$batchIdCounter}";
                $receiptInserts[] = [
                    'id' => $receiptIdCounter,
                    'receipt_number' => $receiptNo,
                    'supplier_id' => $supplierId,
                    'invoice_number' => "INV-PBF-{$batchIdCounter}",
                    'received_date' => '2020-01-02',
                    'total_amount' => $initialStock * $med->purchase_price,
                    'notes' => 'Penerimaan Stok Awal Gudang Farmasi',
                    'received_by' => $adminUserId,
                    'created_at' => Carbon::create(2020, 1, 2, 9, 0, 0),
                    'updated_at' => $now,
                ];

                $receiptItemInserts[] = [
                    'medicine_receipt_id' => $receiptIdCounter,
                    'medicine_id' => $med->id,
                    'batch_number' => $batchNo,
                    'expiry_date' => $expiry,
                    'quantity' => $initialStock,
                    'purchase_price' => $med->purchase_price,
                    'subtotal' => $initialStock * $med->purchase_price,
                    'created_at' => Carbon::create(2020, 1, 2, 9, 0, 0),
                    'updated_at' => $now,
                ];

                $movementInserts[] = [
                    'medicine_id' => $med->id,
                    'batch_id' => $batchIdCounter,
                    'movement_type' => 'receipt',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'reference_type' => 'medicine_receipt',
                    'reference_id' => $receiptIdCounter,
                    'notes' => "Stok Awal Batch {$batchNo}",
                    'created_by' => $adminUserId,
                    'created_at' => Carbon::create(2020, 1, 2, 9, 0, 0),
                    'updated_at' => $now,
                ];

                $batchIdCounter++;
                $receiptIdCounter++;
            }
        }

        DB::table('medicine_batches')->insert($batchInserts);
        DB::table('medicine_receipts')->insert($receiptInserts);
        DB::table('medicine_receipt_items')->insert($receiptItemInserts);
        DB::table('medicine_stock_movements')->insert($movementInserts);

        $this->loadBatchesIntoMemory();
    }

    /**
     * Load batches into memory for fast FEFO deduction.
     */
    protected function loadBatchesIntoMemory(): void
    {
        $this->medicineBatches = DB::table('medicine_batches')
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->groupBy('medicine_id')
            ->toArray();

        foreach ($this->medicineBatches as $medId => $batches) {
            foreach ($batches as $b) {
                $this->batchStockMap[$b->id] = (int) $b->current_stock;
            }
        }
    }

    /**
     * Load ICD-10 and ICD-9 codes pool from database.
     */
    protected function loadIcdCodesPool(): void
    {
        $this->icd10Pool = DB::table('icd10_codes')
            ->whereIn('code', [
                'J06.9', 'I10', 'E11.9', 'K29.7', 'A09', 'K02.1', 'K04.0', 'K05.1',
                'Z34', 'Z35', 'Z00.1', 'M79.1', 'R50.9', 'L20.9', 'H10.9', 'J45.9',
                'E78.0', 'M10.9', 'G44.2', 'B01.9', 'J00', 'J02.9', 'J03.9', 'R05',
            ])
            ->get()
            ->keyBy('code')
            ->toArray();

        // Fallback if specific codes are missing, load any 50 codes
        if (empty($this->icd10Pool)) {
            $this->icd10Pool = DB::table('icd10_codes')->limit(50)->get()->keyBy('code')->toArray();
        }

        $this->icd9Pool = DB::table('icd9cm_codes')
            ->whereIn('code', ['89.03', '89.07', '96.52', '86.59', '93.94', '23.09', '23.2', '96.54', '99.29', '73.59', '99.59'])
            ->get()
            ->keyBy('code')
            ->toArray();

        if (empty($this->icd9Pool)) {
            $this->icd9Pool = DB::table('icd9cm_codes')->limit(20)->get()->keyBy('code')->toArray();
        }
    }

    /**
     * Seed 100,000 unique patients with complete Indonesian demographics, allergies, emergency contacts.
     */
    protected function seedPatients(int $targetPatients, int $chunkSize): void
    {
        $this->line('');
        $this->comment("2. Membangun {$targetPatients} Pasien Unik Indonesia...");

        $currentPatientCount = DB::table('patients')->count();
        if ($currentPatientCount >= $targetPatients) {
            $this->info("Dataset pasien sudah mencukupi ({$currentPatientCount} pasien). Melanjutkan ke kunjungan...");

            return;
        }

        $maleFirst = ['Ahmad', 'Muhammad', 'Budi', 'Bambang', 'Dedi', 'Eko', 'Fajar', 'Gunawan', 'Hendra', 'Indra', 'Joko', 'Kurniawan', 'Lukman', 'Mochamad', 'Nur', 'Oki', 'Prasetyo', 'Rian', 'Rizky', 'Surya', 'Teguh', 'Tri', 'Wahyu', 'Yoga', 'Zainal', 'Adi', 'Agus', 'Bayu', 'Candra', 'Dimas', 'Gilang', 'Ilham', 'Reza', 'Sigit', 'Yusuf', 'Denny', 'Arif', 'Bagus', 'Doni', 'Farhan', 'Hadi', 'Irfan', 'Nanang', 'Putra', 'Raden', 'Satria', 'Taufik', 'Vicky', 'Wisnu', 'Zulfikar'];
        $femaleFirst = ['Siti', 'Dewi', 'Sri', 'Nurul', 'Putri', 'Rina', 'Indah', 'Fitri', 'Ayu', 'Dian', 'Lestari', 'Wulandari', 'Ratna', 'Anisa', 'Maya', 'Nita', 'Yuni', 'Tuti', 'Mega', 'Rini', 'Kartika', 'Sari', 'Endang', 'Ika', 'Widya', 'Desi', 'Eka', 'Gita', 'Hana', 'Intan', 'Melati', 'Nadia', 'Pratiwi', 'Rahma', 'Suci', 'Triana', 'Utami', 'Vina', 'Wati', 'Yulia', 'Zahra', 'Bella', 'Citra', 'Dina', 'Erna', 'Fani', 'Kusuma', 'Lia', 'Marlina', 'Novita'];
        $lastNames = ['Pratama', 'Saputra', 'Kusuma', 'Hidayat', 'Santoso', 'Wijaya', 'Setiawan', 'Nugroho', 'Wibowo', 'Ramadhan', 'Firmansyah', 'Permana', 'Lestari', 'Utami', 'Wardani', 'Anggraini', 'Puspitasari', 'Purnomo', 'Maulana', 'Suryani', 'Handayani', 'Susanto', 'Irawan', 'Hermawan', 'Subagyo', 'Gunardi', 'Syahputra', 'Widodo', 'Suhendar', 'Siregar', 'Nasution', 'Lubis', 'Harahap', 'Ginting', 'Situmorang', 'Pasaribu', 'Manurung', 'Simanjuntak', 'Panjaitan', 'Tanjung', 'Chaniago', 'Koto', 'Piliang', 'Caniago', 'Sikumbang'];

        $streets = ['Jl. Ijen', 'Jl. Borobudur', 'Jl. Soekarno Hatta', 'Jl. Veteran', 'Jl. Bandung', 'Jl. Danau Toba', 'Jl. Mayjen Panjaitan', 'Jl. MT Haryono', 'Jl. Kawi', 'Jl. Semeru', 'Jl. Buring', 'Jl. Galunggung', 'Jl. Simpang Wilis', 'Jl. Bendungan Sigura-gura', 'Jl. Gajayana', 'Jl. Dinoyo', 'Jl. Candi Mendut', 'Jl. Candi Panggung', 'Jl. Terusan Dieng', 'Jl. Raya Tlogomas', 'Jl. Sulfat', 'Jl. Sunandar Priyo Sudarmo', 'Jl. Panglima Sudirman', 'Jl. Kolonel Sugiono', 'Jl. Gadang', 'Jl. Satsui Tubun', 'Jl. S. Supriadi', 'Jl. Kebonsari', 'Jl. Mergan'];
        $kelurahans = ['Oro-oro Dowo', 'Klojen', 'Kauman', 'Gadang', 'Sukun', 'Tanjungrejo', 'Lowokwaru', 'Mojolangu', 'Tulusrejo', 'Dinoyo', 'Jatimulyo', 'Sawojajar', 'Madyopuro', 'Lesanpuro', 'Blimbing', 'Purwantoro', 'Kesatrian', 'Bunulrejo', 'Arjosari', 'Polehan'];
        $kecamatans = ['Klojen', 'Sukun', 'Lowokwaru', 'Kedungkandang', 'Blimbing'];
        $bloodTypes = ['A', 'B', 'AB', 'O', 'tidak_tahu'];
        $religions = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha'];
        $educations = ['SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2'];
        $occupations = ['PNS / ASN', 'Karyawan Swasta', 'Wiraswasta', 'Pedagang', 'Guru / Dosen', 'Buruh', 'Ibu Rumah Tangga', 'Pelajar / Mahasiswa', 'Pensiunan'];

        $allergens = [
            ['allergen_type' => 'obat', 'allergen_name' => 'Amoxicillin / Penicillin', 'reaction' => 'Ruam kemerahan, gatal seluruh tubuh', 'severity' => 'sedang'],
            ['allergen_type' => 'obat', 'allergen_name' => 'Sulfonamide (Cotrimoxazole)', 'reaction' => 'Bibir bengkak dan urtikaria', 'severity' => 'sedang'],
            ['allergen_type' => 'obat', 'allergen_name' => 'Asam Mefenamat', 'reaction' => 'Nyeri ulu hati hebat dan mual', 'severity' => 'ringan'],
            ['allergen_type' => 'makanan', 'allergen_name' => 'Seafood / Udang', 'reaction' => 'Gatal-gatal pada kulit dan bibir bengkak', 'severity' => 'sedang'],
            ['allergen_type' => 'makanan', 'allergen_name' => 'Telur Ayam', 'reaction' => 'Dermatitis atopi kambuh dan gatal', 'severity' => 'ringan'],
            ['allergen_type' => 'lingkungan', 'allergen_name' => 'Debu Rumah & Tungau', 'reaction' => 'Bersin-bersin dan hidung tersumbat', 'severity' => 'ringan'],
            ['allergen_type' => 'obat', 'allergen_name' => 'Aspirin', 'reaction' => 'Sesak napas dan bronkospasme', 'severity' => 'berat'],
        ];

        $relationships = ['Suami', 'Istri', 'Orang Tua (Ayah)', 'Orang Tua (Ibu)', 'Anak Kandung', 'Saudara Kandung'];

        $bar = $this->output->createProgressBar($targetPatients);
        $bar->start();

        $patientChunk = [];
        $allergyChunk = [];
        $contactChunk = [];

        $now = now();
        $adminUserId = $this->users[0] ?? 1;

        for ($i = 1; $i <= $targetPatients; $i++) {
            $isFemale = ($i % 2 === 0);
            $gender = $isFemale ? 'P' : 'L';

            $firstName = $isFemale
                ? $femaleFirst[($i * 7) % count($femaleFirst)]
                : $maleFirst[($i * 11) % count($maleFirst)];
            $lastName = $lastNames[($i * 13) % count($lastNames)];
            $fullName = "{$firstName} {$lastName}";

            // Realistic age distribution (0 to 85 years)
            // 0-5 yo: 10%, 6-17 yo: 10%, 18-35 yo: 35%, 36-55 yo: 25%, 56-85 yo: 20%
            $seedMod = $i % 100;
            if ($seedMod < 10) {
                $ageYears = $i % 6; // 0 to 5 yo
            } elseif ($seedMod < 20) {
                $ageYears = 6 + ($i % 12); // 6 to 17 yo
            } elseif ($seedMod < 55) {
                $ageYears = 18 + ($i % 18); // 18 to 35 yo
            } elseif ($seedMod < 80) {
                $ageYears = 36 + ($i % 20); // 36 to 55 yo
            } else {
                $ageYears = 56 + ($i % 30); // 56 to 85 yo
            }

            $dob = Carbon::create(2026 - $ageYears, 1 + ($i % 12), 1 + ($i % 28));

            // Standard Dukcapil NIK: 3573 + (DD (+40 if female)) + MM + YY + 4 digits
            $day = $dob->day;
            if ($isFemale) {
                $day += 40;
            }
            $nikDay = str_pad($day, 2, '0', STR_PAD_LEFT);
            $nikMonth = str_pad($dob->month, 2, '0', STR_PAD_LEFT);
            $nikYear = substr((string) $dob->year, -2);
            $nikSuffix = str_pad((($i * 37) % 9999) + 1, 4, '0', STR_PAD_LEFT);
            $nik = "3573{$nikDay}{$nikMonth}{$nikYear}{$nikSuffix}";

            $noRm = 'RM-'.str_pad($i, 6, '0', STR_PAD_LEFT);

            $marital = ($ageYears < 18) ? 'belum_kawin' : (($seedMod % 5 === 0) ? 'cerai_hidup' : 'kawin');
            $occupation = ($ageYears < 6) ? 'Belum Bekerja' : (($ageYears < 18) ? 'Pelajar / Mahasiswa' : $occupations[$i % count($occupations)]);

            $street = $streets[$i % count($streets)].' No. '.(($i % 150) + 1);
            $kel = $kelurahans[$i % count($kelurahans)];
            $kec = $kecamatans[$i % count($kecamatans)];
            $rt = str_pad((($i % 12) + 1), 2, '0', STR_PAD_LEFT);
            $rw = str_pad((($i % 8) + 1), 2, '0', STR_PAD_LEFT);
            $phone = '08'.(1200000000 + (($i * 739) % 800000000));

            $regYear = 2020 + ($i % 7); // Registered between 2020 and 2026
            $regMonth = 1 + (($i * 3) % 12);
            $regDay = 1 + (($i * 7) % 28);
            $registeredAt = Carbon::create($regYear, $regMonth, $regDay, 8 + ($i % 10), ($i % 60), 0);

            $patientChunk[] = [
                'id' => $i,
                'medical_record_number' => $noRm,
                'nik' => $nik,
                'name' => $fullName,
                'place_of_birth' => 'Malang',
                'date_of_birth' => $dob->toDateString(),
                'gender' => $gender,
                'blood_type' => $bloodTypes[$i % count($bloodTypes)],
                'address' => $street,
                'rt' => $rt,
                'rw' => $rw,
                'kelurahan' => $kel,
                'kecamatan' => $kec,
                'city' => 'Kota Malang',
                'province' => 'Jawa Timur',
                'postal_code' => '651'.str_pad((($i % 45) + 10), 2, '0', STR_PAD_LEFT),
                'phone' => $phone,
                'email' => strtolower($firstName.'.'.$lastName.$i.'@example.com'),
                'marital_status' => $marital,
                'occupation' => $occupation,
                'religion' => $religions[$i % count($religions)],
                'education' => $educations[$i % count($educations)],
                'is_verified' => true,
                'created_by' => $adminUserId,
                'created_at' => $registeredAt,
                'updated_at' => $registeredAt,
            ];

            // ~15% patients have allergies
            if ($i % 7 === 0) {
                $al = $allergens[($i / 7) % count($allergens)];
                $allergyChunk[] = [
                    'patient_id' => $i,
                    'allergen_type' => $al['allergen_type'],
                    'allergen_name' => $al['allergen_name'],
                    'reaction' => $al['reaction'],
                    'severity' => $al['severity'],
                    'notes' => 'Tercatat pada anamnesis awal',
                    'created_by' => $adminUserId,
                    'created_at' => $registeredAt,
                    'updated_at' => $registeredAt,
                ];
            }

            // ~50% patients have emergency contact
            if ($i % 2 === 0) {
                $contactName = $lastNames[($i * 5) % count($lastNames)].' '.$firstName;
                $contactChunk[] = [
                    'patient_id' => $i,
                    'name' => $contactName,
                    'relationship' => $relationships[$i % count($relationships)],
                    'phone' => '08'.(1300000000 + (($i * 513) % 800000000)),
                    'address' => $street,
                    'created_at' => $registeredAt,
                    'updated_at' => $registeredAt,
                ];
            }

            if (count($patientChunk) >= $chunkSize) {
                DB::table('patients')->insert($patientChunk);
                $patientChunk = [];

                if (! empty($allergyChunk)) {
                    DB::table('patient_allergies')->insert($allergyChunk);
                    $allergyChunk = [];
                }

                if (! empty($contactChunk)) {
                    DB::table('patient_emergency_contacts')->insert($contactChunk);
                    $contactChunk = [];
                }

                $bar->advance($chunkSize);
            }
        }

        if (! empty($patientChunk)) {
            DB::table('patients')->insert($patientChunk);
            if (! empty($allergyChunk)) {
                DB::table('patient_allergies')->insert($allergyChunk);
            }
            if (! empty($contactChunk)) {
                DB::table('patient_emergency_contacts')->insert($contactChunk);
            }
            $bar->finish();
        }

        $this->line('');
        $this->info("✓ Sukses mengenerate {$targetPatients} Pasien Unik Indonesia.");
    }

    /**
     * Seed 200,000+ Visits and complete downstream clinical, pharmacy, lab, and financial transactions.
     */
    protected function seedVisitsAndOperations(int $targetVisits, int $totalPatients, int $chunkSize): void
    {
        $this->line('');
        $this->comment("3. Membangun {$targetVisits} Kunjungan Operasional & Seluruh Modul Terkait (2020 - 2026)...");

        $bar = $this->output->createProgressBar($targetVisits);
        $bar->start();

        // Doctors map
        $doctorUmum = 3; // Dr. Ahmad Fauzi Pratama
        $doctorGigi = 4; // Drg. Maya Anggraini
        $doctorKia = 3;  // Can also serve KIA or Dr. Maya
        $nurseId = 2;    // Siti Rahayu
        $pharmacistUserId = 5; // Budi Santoso
        $cashierUserId = 1;    // Dewi Safitri
        $adminUserId = 1;

        // Buffers for multi-table bulk insertion per batch
        $bufferVisits = [];
        $bufferHistories = [];
        $bufferAssessments = [];
        $bufferVitalSigns = [];
        $bufferMedicalRecords = [];
        $bufferDiagnoses = [];
        $bufferProcedures = [];
        $bufferOdontograms = [];
        $bufferOdontogramTeeth = [];
        $bufferAntenatal = [];
        $bufferChildGrowth = [];
        $bufferPrescriptions = [];
        $bufferPrescriptionItems = [];
        $bufferCompounds = [];
        $bufferCompoundItems = [];
        $bufferDispensing = [];
        $bufferDispensingItems = [];
        $bufferMovements = [];
        $bufferLabRequests = [];
        $bufferLabItems = [];
        $bufferLabResults = [];
        $bufferInvoices = [];
        $bufferBillingItems = [];
        $bufferPayments = [];
        $bufferRefunds = [];
        $bufferFees = [];
        $bufferDocuments = [];
        $bufferPatientDocs = [];
        $bufferAuditLogs = [];
        $bufferFhir = [];

        $now = now();
        $startDate = Carbon::create(2020, 1, 2, 8, 0, 0);
        $endDate = Carbon::create(2026, 11, 15, 17, 0, 0);
        $totalMinutes = $startDate->diffInMinutes($endDate);

        // Track sequential ID counters for foreign key coherence
        $visitId = 1;
        $historyId = 1;
        $assessmentId = 1;
        $mrId = 1;
        $odontogramId = 1;
        $rxId = 1;
        $compoundId = 1;
        $dispId = 1;
        $labReqId = 1;
        $invId = 1;
        $payId = 1;
        $refundId = 1;
        $docId = 1;
        $patientDocId = 1;

        for ($i = 1; $i <= $targetVisits; $i++) {
            // Pick patient: distributed across 1 to $totalPatients
            // Use deterministic modulo distribution so chronic patients revisit realistically
            $patientId = (($i * 17) % $totalPatients) + 1;

            // Polyclinic selection: Poli Umum 60%, Poli Gigi 20%, Poli KIA 20%
            $polyRand = $i % 10;
            if ($polyRand < 6) {
                $polyclinicId = 1; // Poli Umum
                $doctorId = $doctorUmum;
                $polyCode = 'UMUM';
            } elseif ($polyRand < 8) {
                $polyclinicId = 2; // Poli Gigi
                $doctorId = $doctorGigi;
                $polyCode = 'GIGI';
            } else {
                $polyclinicId = 3; // Poli KIA
                $doctorId = $doctorKia;
                $polyCode = 'KIA';
            }

            // Timestamp: chronological spread across 2020 to late 2026
            $minuteOffset = ($i / $targetVisits) * $totalMinutes;
            $regTime = (clone $startDate)->addMinutes($minuteOffset);
            // Confine to operating hours 08:00 - 18:00
            $regTime->setTime(8 + ($i % 10), ($i * 7) % 60, ($i * 13) % 60);

            $visitNumber = 'VIS-'.$regTime->format('Ymd').'-'.str_pad($i, 5, '0', STR_PAD_LEFT);
            $visitType = ($i <= $totalPatients && $i % 3 === 0) ? 'baru' : 'lama';

            $paymentRand = $i % 10;
            $paymentType = ($paymentRand < 5) ? 'umum' : (($paymentRand < 9) ? 'bpjs' : 'asuransi_lain');
            $insuranceNo = ($paymentType === 'bpjs') ? '000'.str_pad(($patientId * 37) % 99999999, 10, '0', STR_PAD_LEFT) : null;

            // Status: 96% completed, 4% cancelled
            $isCancelled = ($i % 25 === 0);
            $status = $isCancelled ? 'dibatalkan' : 'selesai';

            $bufferVisits[] = [
                'id' => $visitId,
                'visit_number' => $visitNumber,
                'patient_id' => $patientId,
                'polyclinic_id' => $polyclinicId,
                'doctor_id' => $doctorId,
                'registered_by' => $adminUserId,
                'registration_date' => $regTime,
                'visit_type' => $visitType,
                'payment_type' => $paymentType,
                'insurance_number' => $insuranceNo,
                'status' => $status,
                'queue_number' => $polyCode.'-'.str_pad(($i % 80) + 1, 3, '0', STR_PAD_LEFT),
                'notes' => $isCancelled ? 'Pasien membatalkan antrean karena keperluan mendesak' : 'Pelayanan selesai',
                'created_at' => $regTime,
                'updated_at' => $regTime,
            ];

            // 1. Visit Status History
            $bufferHistories[] = [
                'visit_id' => $visitId,
                'status_from' => null,
                'status_to' => 'menunggu_pemeriksaan_awal',
                'changed_by' => $adminUserId,
                'reason' => 'Pasien registrasi di loket pendaftaran',
                'created_at' => $regTime,
                'updated_at' => $regTime,
            ];

            if ($isCancelled) {
                $bufferHistories[] = [
                    'visit_id' => $visitId,
                    'status_from' => 'menunggu_pemeriksaan_awal',
                    'status_to' => 'dibatalkan',
                    'changed_by' => $adminUserId,
                    'reason' => 'Pasien batal dilayani',
                    'created_at' => (clone $regTime)->addMinutes(10),
                    'updated_at' => (clone $regTime)->addMinutes(10),
                ];
                $visitId++;
                $bar->advance(1);

                continue;
            }

            // Causal timestamps
            $assessmentTime = (clone $regTime)->addMinutes(8 + ($i % 10));
            $examTime = (clone $assessmentTime)->addMinutes(12 + ($i % 15));
            $rxTime = (clone $examTime)->addMinutes(5);
            $dispenseTime = (clone $rxTime)->addMinutes(15);
            $payTime = (clone $dispenseTime)->addMinutes(10);

            // History transitions
            $bufferHistories[] = [
                'visit_id' => $visitId,
                'status_from' => 'menunggu_pemeriksaan_awal',
                'status_to' => 'menunggu_dokter',
                'changed_by' => $nurseId,
                'reason' => 'Triage perawat selesai',
                'created_at' => $assessmentTime,
                'updated_at' => $assessmentTime,
            ];

            $bufferHistories[] = [
                'visit_id' => $visitId,
                'status_from' => 'menunggu_dokter',
                'status_to' => 'menunggu_farmasi',
                'changed_by' => $adminUserId,
                'reason' => 'Pemeriksaan dokter selesai, resep diterbitkan',
                'created_at' => $examTime,
                'updated_at' => $examTime,
            ];

            $bufferHistories[] = [
                'visit_id' => $visitId,
                'status_from' => 'menunggu_farmasi',
                'status_to' => 'menunggu_pembayaran',
                'changed_by' => $pharmacistUserId,
                'reason' => 'Obat siap diserahkan di apotek',
                'created_at' => $dispenseTime,
                'updated_at' => $dispenseTime,
            ];

            $bufferHistories[] = [
                'visit_id' => $visitId,
                'status_from' => 'menunggu_pembayaran',
                'status_to' => 'selesai',
                'changed_by' => $cashierUserId,
                'reason' => 'Pembayaran kasir lunas, pelayanan tuntas',
                'created_at' => $payTime,
                'updated_at' => $payTime,
            ];

            // 2. Clinical Scenario Determination
            $scenario = $this->determineClinicalScenario($polyclinicId, $i);

            // 3. Nursing Assessment & Vital Signs
            $systolic = $scenario['systolic'];
            $diastolic = $scenario['diastolic'];
            $temp = $scenario['temperature'];
            $pulse = 72 + ($i % 24);
            $rr = 18 + ($i % 6);
            $spo2 = 97.0 + (($i % 4) * 0.5);
            $weight = 52.0 + ($i % 35);
            $height = 155.0 + ($i % 25);
            $heightM = $height / 100;
            $bmi = round($weight / ($heightM * $heightM), 1);

            $bufferAssessments[] = [
                'id' => $assessmentId,
                'visit_id' => $visitId,
                'patient_id' => $patientId,
                'nurse_id' => $nurseId,
                'assessment_time' => $assessmentTime,
                'chief_complaint' => $scenario['complaint'],
                'current_illness_history' => $scenario['current_history'],
                'past_illness_history' => 'Tidak ada riwayat alergi obat berat disangkal',
                'family_illness_history' => 'Riwayat hipertensi / diabetes pada keluarga orang tua',
                'current_medication' => 'Tidak sedang mengonsumsi obat rutin selain dari dokter',
                'systolic' => $systolic,
                'diastolic' => $diastolic,
                'temperature' => $temp,
                'pulse_rate' => $pulse,
                'respiratory_rate' => $rr,
                'oxygen_saturation' => $spo2,
                'weight' => $weight,
                'height' => $height,
                'bmi' => $bmi,
                'waist_circumference' => 75.0 + ($i % 20),
                'consciousness' => 'compos_mentis',
                'pain_scale' => $scenario['pain_scale'],
                'fall_risk' => 'rendah',
                'nursing_care_plan' => 'Observasi tanda-tanda vital, edukasi istirahat dan hidrasi cukup',
                'notes' => 'Pasien kooperatif saat pengkajian awal',
                'created_at' => $assessmentTime,
                'updated_at' => $assessmentTime,
            ];

            $bufferVitalSigns[] = [
                'patient_id' => $patientId,
                'visit_id' => $visitId,
                'recorded_by' => $adminUserId,
                'recorded_at' => $assessmentTime,
                'systolic' => $systolic,
                'diastolic' => $diastolic,
                'temperature' => $temp,
                'pulse_rate' => $pulse,
                'respiratory_rate' => $rr,
                'oxygen_saturation' => $spo2,
                'weight' => $weight,
                'height' => $height,
                'bmi' => $bmi,
                'created_at' => $assessmentTime,
                'updated_at' => $assessmentTime,
            ];

            // 4. Medical Record (Doctor SOAP)
            $bufferMedicalRecords[] = [
                'id' => $mrId,
                'visit_id' => $visitId,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'examination_time' => $examTime,
                'subjective' => "Anamnesis: {$scenario['complaint']}. {$scenario['current_history']}",
                'objective' => "Pemeriksaan Fisik: KU Baik, Kesadaran Compos Mentis, TD {$systolic}/{$diastolic} mmHg, Nadi {$pulse}x/m, Suhu {$temp} C. {$scenario['objective_finding']}",
                'assessment' => "Diagnosis Kerja: {$scenario['icd10_desc']} ({$scenario['icd10_code']})",
                'plan' => 'Terapi medikamentosa sesuai resep. Edukasi pola makan teratur dan istirahat.',
                'status' => 'final',
                'finalized_at' => $examTime,
                'finalized_by' => $adminUserId,
                'education_plan' => 'Edukasi kepatuhan minum obat dan kenali tanda bahaya',
                'follow_up_plan' => 'Kontrol kembali jika keluhan berlanjut dalam 3 hari',
                'next_visit_date' => (clone $examTime)->addDays(7)->toDateString(),
                'created_at' => $examTime,
                'updated_at' => $examTime,
            ];

            // Diagnoses: Primary + Secondary
            $bufferDiagnoses[] = [
                'medical_record_id' => $mrId,
                'icd10_code' => $scenario['icd10_code'],
                'description' => $scenario['icd10_desc'],
                'type' => 'primary',
                'notes' => 'Diagnosis utama pelayanan',
                'created_at' => $examTime,
                'updated_at' => $examTime,
            ];

            if (! empty($scenario['sec_icd10'])) {
                $bufferDiagnoses[] = [
                    'medical_record_id' => $mrId,
                    'icd10_code' => $scenario['sec_icd10'],
                    'description' => $scenario['sec_icd10_desc'],
                    'type' => 'secondary',
                    'notes' => 'Diagnosis penyerta',
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];
            }

            // Procedures ICD-9
            if (! empty($scenario['icd9_code'])) {
                $bufferProcedures[] = [
                    'medical_record_id' => $mrId,
                    'icd9cm_code' => $scenario['icd9_code'],
                    'description' => $scenario['icd9_desc'],
                    'tariff' => $scenario['proc_tariff'] ?? 0,
                    'notes' => 'Prosedur tindakan klinis',
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];
            }

            // 5. Specialty Modules (Odontogram / ANC / Child Growth)
            if ($polyclinicId === 2) { // Dental
                $bufferOdontograms[] = [
                    'id' => $odontogramId,
                    'visit_id' => $visitId,
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'notes' => 'Odontogram pemeriksaan gigi',
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];

                $toothNum = $scenario['tooth'] ?? '46';
                $bufferOdontogramTeeth[] = [
                    'odontogram_id' => $odontogramId,
                    'tooth_number' => $toothNum,
                    'condition' => $scenario['tooth_condition'] ?? 'caries',
                    'surfaces' => 'MOD',
                    'treatment_notes' => 'Preparasi dan penambalan komposit',
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];

                // Add 2nd distinct tooth (strictly distinct from tooth 1)
                $dentalTeethPool = ['16', '26', '36', '46', '11', '21', '31', '41'];
                $idx = array_search($toothNum, $dentalTeethPool);
                $toothNum2 = $dentalTeethPool[(($idx !== false ? $idx : 0) + 1) % count($dentalTeethPool)];

                $bufferOdontogramTeeth[] = [
                    'odontogram_id' => $odontogramId,
                    'tooth_number' => $toothNum2,
                    'condition' => ($i % 2 === 0) ? 'filling' : 'sound',
                    'surfaces' => 'O',
                    'treatment_notes' => 'Tumpatan komposit baik',
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];

                $odontogramId++;
            } elseif ($polyclinicId === 3) { // KIA
                if ($scenario['is_anc']) {
                    $bufferAntenatal[] = [
                        'visit_id' => $visitId,
                        'patient_id' => $patientId,
                        'doctor_id' => $doctorId,
                        'gravida' => 1 + ($i % 3),
                        'para' => $i % 2,
                        'abortus' => 0,
                        'hpht' => (clone $examTime)->subWeeks(24)->toDateString(),
                        'estimated_due_date' => (clone $examTime)->addWeeks(16)->toDateString(),
                        'gestational_age_weeks' => 24.0,
                        'fundal_height' => 20.0,
                        'fetal_heart_rate' => 140 + ($i % 10),
                        'fetal_presentation' => 'kepala',
                        'systolic' => $systolic,
                        'diastolic' => $diastolic,
                        'weight' => $weight,
                        'complaints' => 'Pemeriksaan rutin kehamilan trimester 2',
                        'treatment_plan' => 'Suplementasi tablet Fe dan kalsium laktat',
                        'created_at' => $examTime,
                        'updated_at' => $examTime,
                    ];
                } else {
                    $bufferChildGrowth[] = [
                        'visit_id' => $visitId,
                        'patient_id' => $patientId,
                        'doctor_id' => $doctorId,
                        'age_months' => 9 + ($i % 24),
                        'weight' => 8.5 + (($i % 10) * 0.5),
                        'height' => 70.0 + ($i % 20),
                        'head_circumference' => 44.0 + (($i % 5) * 0.5),
                        'immunization_status' => 'Campak-Rubella (MR) Lengkap',
                        'development_milestone' => 'Mampu duduk mandiri dan merangkak aktif',
                        'notes' => 'Status gizi baik sesuai kurva KMS WHO',
                        'created_at' => $examTime,
                        'updated_at' => $examTime,
                    ];
                }
            }

            // 6. Prescriptions & Dispensing
            $medItems = $scenario['medicines'];
            $prescriptionTotal = 0;

            $rxNumber = 'RX-'.$regTime->format('Ymd').'-'.str_pad($rxId, 5, '0', STR_PAD_LEFT);
            $bufferPrescriptions[] = [
                'id' => $rxId,
                'prescription_number' => $rxNumber,
                'visit_id' => $visitId,
                'medical_record_id' => $mrId,
                'doctor_id' => $doctorId,
                'prescription_date' => $rxTime,
                'status' => 'selesai',
                'doctor_notes' => 'Konsumsi obat sesuai petunjuk aturan pakai',
                'pharmacist_notes' => 'Obat telah disiapkan dan diserahkan ke pasien',
                'verified_by' => $pharmacistUserId,
                'verified_at' => (clone $rxTime)->addMinutes(5),
                'dispensed_at' => $dispenseTime,
                'created_at' => $rxTime,
                'updated_at' => $dispenseTime,
            ];

            $dispNumber = 'DSP-'.$regTime->format('Ymd').'-'.str_pad($dispId, 5, '0', STR_PAD_LEFT);
            $bufferDispensing[] = [
                'id' => $dispId,
                'dispensing_number' => $dispNumber,
                'prescription_id' => $rxId,
                'visit_id' => $visitId,
                'dispensed_by' => $pharmacistUserId,
                'dispensed_at' => $dispenseTime,
                'total_amount' => 0, // updated below
                'notes' => 'Penyerahan obat tuntas',
                'created_at' => $dispenseTime,
                'updated_at' => $dispenseTime,
            ];

            $rxItemIndex = 1;
            foreach ($medItems as $mItem) {
                $medCode = $mItem['code'];
                $medObj = $this->medicines[$medCode] ?? (object) ['id' => 1, 'selling_price' => 1000];
                $qty = $mItem['qty'];
                $unitPrice = (float) $medObj->selling_price;
                $subtotal = $qty * $unitPrice;
                $prescriptionTotal += $subtotal;

                $bufferPrescriptionItems[] = [
                    'prescription_id' => $rxId,
                    'medicine_id' => $medObj->id,
                    'quantity' => $qty,
                    'dosage' => $mItem['dosage'],
                    'frequency' => $mItem['freq'],
                    'route' => 'oral',
                    'duration_days' => 3,
                    'instructions' => $mItem['inst'],
                    'special_notes' => null,
                    'created_at' => $rxTime,
                    'updated_at' => $rxTime,
                ];

                // FEFO batch assignment: pick active batch for this medicine
                $batches = $this->medicineBatches[$medObj->id] ?? [];
                $selectedBatch = ! empty($batches) ? $batches[0] : null;
                $batchId = $selectedBatch ? $selectedBatch->id : 1;

                // Deduct stock in memory
                $stockBefore = $this->batchStockMap[$batchId] ?? 10000;
                $stockAfter = max(0, $stockBefore - $qty);
                $this->batchStockMap[$batchId] = $stockAfter;

                $bufferDispensingItems[] = [
                    'dispensing_transaction_id' => $dispId,
                    'prescription_item_id' => null,
                    'medicine_id' => $medObj->id,
                    'batch_id' => $batchId,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                    'created_at' => $dispenseTime,
                    'updated_at' => $dispenseTime,
                ];

                // Stock movement card
                $bufferMovements[] = [
                    'medicine_id' => $medObj->id,
                    'batch_id' => $batchId,
                    'movement_type' => 'dispense',
                    'quantity' => -$qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => 'dispensing_transaction',
                    'reference_id' => $dispId,
                    'notes' => "Pengeluaran resep {$rxNumber}",
                    'created_by' => $pharmacistUserId,
                    'created_at' => $dispenseTime,
                    'updated_at' => $dispenseTime,
                ];

                $rxItemIndex++;
            }

            // Update dispensing total
            $bufferDispensing[count($bufferDispensing) - 1]['total_amount'] = $prescriptionTotal;

            // Racikan / Compound medicine (Puyer / Kapsul) for 10% of prescriptions
            if ($i % 10 === 0) {
                $bufferCompounds[] = [
                    'id' => $compoundId,
                    'prescription_id' => $rxId,
                    'compound_name' => 'Puyer Racikan Batuk Pilek',
                    'dosage_form' => 'Puyer',
                    'package_count' => 10,
                    'frequency' => '3x1 bungkus',
                    'instructions' => 'Sesudah makan',
                    'special_notes' => 'Dihaluskan dalam sachet puyer',
                    'created_at' => $rxTime,
                    'updated_at' => $rxTime,
                ];

                $bufferCompoundItems[] = [
                    'prescription_compound_id' => $compoundId,
                    'medicine_id' => 1,
                    'quantity' => 5,
                    'unit' => 'Tablet',
                    'created_at' => $rxTime,
                    'updated_at' => $rxTime,
                ];

                $bufferCompoundItems[] = [
                    'prescription_compound_id' => $compoundId,
                    'medicine_id' => 35,
                    'quantity' => 5,
                    'unit' => 'Tablet',
                    'created_at' => $rxTime,
                    'updated_at' => $rxTime,
                ];

                $compoundId++;
            }

            // 7. Laboratory Order (for relevant scenarios, e.g. GDS or DL)
            $labTotal = 0;
            if (! empty($scenario['lab_code'])) {
                $labCode = $scenario['lab_code'];
                $labObj = collect($this->laboratories)->firstWhere('code', $labCode) ?? (object) ['id' => 1, 'tariff' => 35000];
                $labTariff = (float) $labObj->tariff;
                $labTotal = $labTariff;

                $reqNo = 'LAB-'.$regTime->format('Ymd').'-'.str_pad($labReqId, 5, '0', STR_PAD_LEFT);
                $bufferLabRequests[] = [
                    'id' => $labReqId,
                    'request_number' => $reqNo,
                    'visit_id' => $visitId,
                    'doctor_id' => $doctorId,
                    'request_time' => $examTime,
                    'clinical_notes' => 'Pemeriksaan penunjang diagnostik',
                    'sample_collected_at' => (clone $examTime)->addMinutes(10),
                    'sample_collector' => 'Siti Rahayu',
                    'status' => 'selesai',
                    'created_at' => $examTime,
                    'updated_at' => $dispenseTime,
                ];

                $bufferLabItems[] = [
                    'laboratory_request_id' => $labReqId,
                    'laboratory_id' => $labObj->id,
                    'tariff' => $labTariff,
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];

                $bufferLabResults[] = [
                    'laboratory_request_id' => $labReqId,
                    'laboratory_id' => $labObj->id,
                    'result_value' => $scenario['lab_result_val'] ?? 'Normal',
                    'is_critical' => false,
                    'notes' => 'Dalam batas rujukan klinis',
                    'tested_by' => $adminUserId,
                    'verified_by' => $adminUserId,
                    'verified_at' => (clone $examTime)->addMinutes(20),
                    'created_at' => $dispenseTime,
                    'updated_at' => $dispenseTime,
                ];

                $labReqId++;
            }

            // 8. Billing & Payments
            $admFee = 15000.0;
            $consultFee = ($polyclinicId === 2) ? 50000.0 : (($polyclinicId === 3) ? 35000.0 : 40000.0);
            $procFee = (float) ($scenario['proc_tariff'] ?? 0);
            $invTotal = $admFee + $consultFee + $procFee + $prescriptionTotal + $labTotal;
            $discount = 0.0;
            $finalAmount = $invTotal - $discount;

            $invNumber = 'INV-'.$regTime->format('Ymd').'-'.str_pad($invId, 5, '0', STR_PAD_LEFT);
            $bufferInvoices[] = [
                'id' => $invId,
                'invoice_number' => $invNumber,
                'visit_id' => $visitId,
                'patient_id' => $patientId,
                'total_amount' => $invTotal,
                'discount_amount' => $discount,
                'discount_reason' => null,
                'final_amount' => $finalAmount,
                'paid_amount' => $finalAmount,
                'balance_amount' => 0,
                'status' => 'lunas',
                'created_by' => $cashierUserId,
                'created_at' => $payTime,
                'updated_at' => $payTime,
            ];

            // Line items
            $bufferBillingItems[] = [
                'billing_invoice_id' => $invId,
                'item_type' => 'administrasi',
                'item_code' => 'TRF-ADM',
                'item_name' => 'Biaya Administrasi & Registrasi Pasien',
                'quantity' => 1,
                'unit_price' => $admFee,
                'subtotal' => $admFee,
                'reference_type' => 'service_tariff',
                'reference_id' => 1,
                'created_at' => $payTime,
                'updated_at' => $payTime,
            ];

            $bufferBillingItems[] = [
                'billing_invoice_id' => $invId,
                'item_type' => 'konsultasi',
                'item_code' => ($polyclinicId === 2) ? 'TRF-KONS-GIGI' : (($polyclinicId === 3) ? 'TRF-KONS-KIA' : 'TRF-KONS-UMUM'),
                'item_name' => 'Konsultasi & Pemeriksaan Dokter',
                'quantity' => 1,
                'unit_price' => $consultFee,
                'subtotal' => $consultFee,
                'reference_type' => 'service_tariff',
                'reference_id' => 2,
                'created_at' => $payTime,
                'updated_at' => $payTime,
            ];

            if ($procFee > 0) {
                $bufferBillingItems[] = [
                    'billing_invoice_id' => $invId,
                    'item_type' => 'tindakan_medis',
                    'item_code' => 'TRF-TND',
                    'item_name' => $scenario['icd9_desc'] ?? 'Tindakan Medis',
                    'quantity' => 1,
                    'unit_price' => $procFee,
                    'subtotal' => $procFee,
                    'reference_type' => 'service_tariff',
                    'reference_id' => null,
                    'created_at' => $payTime,
                    'updated_at' => $payTime,
                ];
            }

            if ($prescriptionTotal > 0) {
                $bufferBillingItems[] = [
                    'billing_invoice_id' => $invId,
                    'item_type' => 'obat',
                    'item_code' => 'RX-ITEM',
                    'item_name' => 'Paket Resep Obat Farmasi',
                    'quantity' => 1,
                    'unit_price' => $prescriptionTotal,
                    'subtotal' => $prescriptionTotal,
                    'reference_type' => 'prescription',
                    'reference_id' => $rxId,
                    'created_at' => $payTime,
                    'updated_at' => $payTime,
                ];
            }

            if ($labTotal > 0) {
                $bufferBillingItems[] = [
                    'billing_invoice_id' => $invId,
                    'item_type' => 'laboratorium',
                    'item_code' => $scenario['lab_code'] ?? 'LAB',
                    'item_name' => 'Pemeriksaan Uji Laboratorium',
                    'quantity' => 1,
                    'unit_price' => $labTotal,
                    'subtotal' => $labTotal,
                    'reference_type' => 'laboratory_request',
                    'reference_id' => $labReqId - 1,
                    'created_at' => $payTime,
                    'updated_at' => $payTime,
                ];
            }

            // Payment receipt
            $payMethodId = 1 + ($i % count($this->paymentMethods));
            $rcptNumber = 'KW-'.$regTime->format('Ymd').'-'.str_pad($invId, 5, '0', STR_PAD_LEFT);
            $bufferPayments[] = [
                'receipt_number' => $rcptNumber,
                'billing_invoice_id' => $invId,
                'payment_method_id' => $payMethodId,
                'amount_paid' => $finalAmount,
                'amount_tendered' => $finalAmount,
                'change_due' => 0,
                'payment_time' => $payTime,
                'cashier_id' => $cashierUserId,
                'notes' => 'Pembayaran lunas kasir',
                'created_at' => $payTime,
                'updated_at' => $payTime,
            ];

            // Rare realistic refund (~0.5% for demonstration and testing of refund module)
            if ($i % 50 === 0) {
                $bufferRefunds[] = [
                    'billing_invoice_id' => $invId,
                    'payment_id' => $payId,
                    'refund_number' => 'REF-'.$regTime->format('Ymd').'-'.str_pad($refundId, 4, '0', STR_PAD_LEFT),
                    'amount' => $finalAmount,
                    'reason' => 'Pengembalian dana klaim revisi jaminan asuransi',
                    'approved_by' => $adminUserId,
                    'refunded_at' => (clone $payTime)->addHours(2),
                    'created_at' => (clone $payTime)->addHours(2),
                    'updated_at' => (clone $payTime)->addHours(2),
                ];
                $refundId++;
            }
            $payId++;

            // Medical Fee Transaction (Jasa Medis)
            $bufferFees[] = [
                'billing_item_id' => null,
                'visit_id' => $visitId,
                'doctor_id' => $doctorId,
                'nurse_id' => $nurseId,
                'doctor_fee' => $consultFee * 0.7,
                'nurse_fee' => 5000.0,
                'clinic_fee' => ($consultFee * 0.3) + $admFee,
                'status' => 'paid',
                'created_at' => $payTime,
                'updated_at' => $payTime,
            ];

            // 9. Medical Documents (~15% of visits receive Surat Sakit / Surat Sehat / Rujukan)
            if ($i % 7 === 0) {
                $docNo = 'SKS/'.$regTime->format('Y/m').'/'.str_pad($docId, 5, '0', STR_PAD_LEFT);
                $bufferDocuments[] = [
                    'id' => $docId,
                    'document_number' => $docNo,
                    'visit_id' => $visitId,
                    'patient_id' => $patientId,
                    'doctor_id' => $doctorId,
                    'type' => 'surat_keterangan_sakit',
                    'issue_date' => $examTime->toDateString(),
                    'content_data' => json_encode([
                        'rest_days' => 2,
                        'start_date' => $examTime->toDateString(),
                        'end_date' => (clone $examTime)->addDays(2)->toDateString(),
                        'diagnosis' => $scenario['icd10_desc'],
                    ]),
                    'notes' => 'Istirahat selama 2 hari',
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];
                $docId++;
            }

            // ~5% visits include uploaded supporting patient document
            if ($i % 20 === 0) {
                $docTypes = ['radiologi', 'lab_eksternal', 'rujukan_eksternal', 'identitas'];
                $selectedType = $docTypes[$i % count($docTypes)];
                $bufferPatientDocs[] = [
                    'patient_id' => $patientId,
                    'visit_id' => $visitId,
                    'title' => ($selectedType === 'radiologi') ? 'Rontgen Thorax AP/PA' : (($selectedType === 'identitas') ? 'Scan e-KTP Pasien' : 'Hasil Laboratorium Rujukan Eksternal'),
                    'document_type' => $selectedType,
                    'file_path' => 'documents/patients/'.$patientId.'/doc_'.$i.'.pdf',
                    'mime_type' => 'application/pdf',
                    'file_size' => 150000 + (($i * 31) % 500000),
                    'uploaded_by' => $adminUserId,
                    'created_at' => $examTime,
                    'updated_at' => $examTime,
                ];
                $patientDocId++;
            }

            // 10. Audit Log & FHIR Mapping (~25% of visits)
            if ($i % 4 === 0) {
                $bufferAuditLogs[] = [
                    'user_id' => $adminUserId,
                    'action' => 'FINALIZE_MEDICAL_RECORD',
                    'module' => 'rekam_medis',
                    'description' => "Finalisasi rekam medis untuk kunjungan {$visitNumber}",
                    'ip_address' => '127.0.0.1',
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/130.0.0.0',
                    'old_values' => json_encode(['status' => 'draft']),
                    'new_values' => json_encode(['status' => 'final']),
                    'created_at' => $examTime,
                ];

                $bufferFhir[] = [
                    'resource_type' => 'Encounter',
                    'local_table' => 'visits',
                    'local_id' => $visitId,
                    'fhir_id' => 'enc-'.$visitNumber,
                    'status' => 'synced',
                    'last_synced_at' => $payTime,
                    'created_at' => $examTime,
                    'updated_at' => $payTime,
                ];
            }

            $visitId++;
            $assessmentId++;
            $mrId++;
            $rxId++;
            $dispId++;
            $invId++;

            // Flush chunk to database
            if (count($bufferVisits) >= $chunkSize) {
                $this->flushOperationalBuffers(
                    $bufferVisits, $bufferHistories, $bufferAssessments, $bufferVitalSigns,
                    $bufferMedicalRecords, $bufferDiagnoses, $bufferProcedures,
                    $bufferOdontograms, $bufferOdontogramTeeth, $bufferAntenatal, $bufferChildGrowth,
                    $bufferPrescriptions, $bufferPrescriptionItems, $bufferCompounds, $bufferCompoundItems,
                    $bufferDispensing, $bufferDispensingItems, $bufferMovements,
                    $bufferLabRequests, $bufferLabItems, $bufferLabResults,
                    $bufferInvoices, $bufferBillingItems, $bufferPayments, $bufferRefunds, $bufferFees,
                    $bufferDocuments, $bufferPatientDocs, $bufferAuditLogs, $bufferFhir
                );
                $bar->advance($chunkSize);
            }
        }

        // Final buffer flush
        if (! empty($bufferVisits)) {
            $this->flushOperationalBuffers(
                $bufferVisits, $bufferHistories, $bufferAssessments, $bufferVitalSigns,
                $bufferMedicalRecords, $bufferDiagnoses, $bufferProcedures,
                $bufferOdontograms, $bufferOdontogramTeeth, $bufferAntenatal, $bufferChildGrowth,
                $bufferPrescriptions, $bufferPrescriptionItems, $bufferCompounds, $bufferCompoundItems,
                $bufferDispensing, $bufferDispensingItems, $bufferMovements,
                $bufferLabRequests, $bufferLabItems, $bufferLabResults,
                $bufferInvoices, $bufferBillingItems, $bufferPayments, $bufferRefunds, $bufferFees,
                $bufferDocuments, $bufferPatientDocs, $bufferAuditLogs, $bufferFhir
            );
            $bar->finish();
        }

        $this->line('');
        $this->info("✓ Sukses mengenerate {$targetVisits} Kunjungan Operasional & jutaan record lintas modul.");
    }

    /**
     * Flush all operational buffers into database using bulk inserts.
     */
    protected function flushOperationalBuffers(
        array &$visits, array &$histories, array &$assessments, array &$vitals,
        array &$medicalRecords, array &$diagnoses, array &$procedures,
        array &$odontograms, array &$odontogramTeeth, array &$antenatal, array &$childGrowth,
        array &$prescriptions, array &$prescriptionItems, array &$compounds, array &$compoundItems,
        array &$dispensing, array &$dispensingItems, array &$movements,
        array &$labRequests, array &$labItems, array &$labResults,
        array &$invoices, array &$billingItems, array &$payments, array &$refunds, array &$fees,
        array &$documents, array &$patientDocs, array &$auditLogs, array &$fhir
    ): void {
        if (! empty($visits)) {
            DB::table('visits')->insert($visits);
            $visits = [];
        }
        if (! empty($histories)) {
            DB::table('visit_status_histories')->insert($histories);
            $histories = [];
        }
        if (! empty($assessments)) {
            DB::table('nursing_assessments')->insert($assessments);
            $assessments = [];
        }
        if (! empty($vitals)) {
            DB::table('vital_signs')->insert($vitals);
            $vitals = [];
        }
        if (! empty($medicalRecords)) {
            DB::table('medical_records')->insert($medicalRecords);
            $medicalRecords = [];
        }
        if (! empty($diagnoses)) {
            DB::table('medical_record_diagnoses')->insert($diagnoses);
            $diagnoses = [];
        }
        if (! empty($procedures)) {
            DB::table('medical_record_procedures')->insert($procedures);
            $procedures = [];
        }
        if (! empty($odontograms)) {
            DB::table('odontograms')->insert($odontograms);
            $odontograms = [];
        }
        if (! empty($odontogramTeeth)) {
            DB::table('odontogram_teeth')->insert($odontogramTeeth);
            $odontogramTeeth = [];
        }
        if (! empty($antenatal)) {
            DB::table('antenatal_cares')->insert($antenatal);
            $antenatal = [];
        }
        if (! empty($childGrowth)) {
            DB::table('child_growths')->insert($childGrowth);
            $childGrowth = [];
        }
        if (! empty($prescriptions)) {
            DB::table('prescriptions')->insert($prescriptions);
            $prescriptions = [];
        }
        if (! empty($prescriptionItems)) {
            DB::table('prescription_items')->insert($prescriptionItems);
            $prescriptionItems = [];
        }
        if (! empty($compounds)) {
            DB::table('prescription_compounds')->insert($compounds);
            $compounds = [];
        }
        if (! empty($compoundItems)) {
            DB::table('prescription_compound_items')->insert($compoundItems);
            $compoundItems = [];
        }
        if (! empty($dispensing)) {
            DB::table('dispensing_transactions')->insert($dispensing);
            $dispensing = [];
        }
        if (! empty($dispensingItems)) {
            DB::table('dispensing_items')->insert($dispensingItems);
            $dispensingItems = [];
        }
        if (! empty($movements)) {
            DB::table('medicine_stock_movements')->insert($movements);
            $movements = [];
        }
        if (! empty($labRequests)) {
            DB::table('laboratory_requests')->insert($labRequests);
            $labRequests = [];
        }
        if (! empty($labItems)) {
            DB::table('laboratory_request_items')->insert($labItems);
            $labItems = [];
        }
        if (! empty($labResults)) {
            DB::table('laboratory_results')->insert($labResults);
            $labResults = [];
        }
        if (! empty($invoices)) {
            DB::table('billing_invoices')->insert($invoices);
            $invoices = [];
        }
        if (! empty($billingItems)) {
            DB::table('billing_items')->insert($billingItems);
            $billingItems = [];
        }
        if (! empty($payments)) {
            DB::table('payments')->insert($payments);
            $payments = [];
        }
        if (! empty($refunds)) {
            DB::table('refunds')->insert($refunds);
            $refunds = [];
        }
        if (! empty($fees)) {
            DB::table('medical_fee_transactions')->insert($fees);
            $fees = [];
        }
        if (! empty($documents)) {
            DB::table('medical_documents')->insert($documents);
            $documents = [];
        }
        if (! empty($patientDocs)) {
            DB::table('patient_documents')->insert($patientDocs);
            $patientDocs = [];
        }
        if (! empty($auditLogs)) {
            DB::table('audit_logs')->insert($auditLogs);
            $auditLogs = [];
        }
        if (! empty($fhir)) {
            DB::table('fhir_mappings')->insert($fhir);
            $fhir = [];
        }
    }

    /**
     * Map clinical scenarios for rich realistic diversity across polyclinics.
     */
    protected function determineClinicalScenario(int $polyclinicId, int $seed): array
    {
        if ($polyclinicId === 2) {
            // Dental Scenarios
            $dSeed = $seed % 4;
            $teethNumbers = ['16', '26', '36', '46', '11', '21', '31', '41'];
            $tooth = $teethNumbers[$seed % count($teethNumbers)];

            if ($dSeed === 0) {
                return [
                    'complaint' => "Gigi geraham {$tooth} terasa berdenyut ngilu tajam saat minum dingin dan malam hari.",
                    'current_history' => 'Keluhan dirasakan sejak 3 hari terakhir, memburuk saat mengunyah.',
                    'objective_finding' => "Gigi {$tooth} karies profunda mencapai pulpa, sondasi (+), perkusi (+).",
                    'icd10_code' => 'K04.0',
                    'icd10_desc' => 'Pulpitis Akut',
                    'sec_icd10' => 'K02.1',
                    'sec_icd10_desc' => 'Karies Dentin',
                    'icd9_code' => '23.2',
                    'icd9_desc' => 'Restorasi Gigi Komposit',
                    'proc_tariff' => 120000,
                    'systolic' => 120, 'diastolic' => 80, 'temperature' => 36.8, 'pain_scale' => 6,
                    'tooth' => $tooth, 'tooth_condition' => 'caries',
                    'medicines' => [
                        ['code' => 'MED-009', 'qty' => 10, 'dosage' => '500 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan jika nyeri'],
                        ['code' => 'MED-002', 'qty' => 10, 'dosage' => '500 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan habiskan'],
                    ],
                ];
            } elseif ($dSeed === 1) {
                return [
                    'complaint' => 'Gusi sering berdarah saat menyikat gigi dan karang gigi menumpuk di gigi bawah.',
                    'current_history' => 'Napas berbau dan gusi terasa gatal sudah 2 minggu.',
                    'objective_finding' => 'Kalkulus subgingiva dan supragingiva tebal regio anterior, margin gingiva edematous.',
                    'icd10_code' => 'K05.1',
                    'icd10_desc' => 'Gingivitis Kronis',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '96.54',
                    'icd9_desc' => 'Scaling dan Profilaksis Gigi',
                    'proc_tariff' => 150000,
                    'systolic' => 118, 'diastolic' => 78, 'temperature' => 36.6, 'pain_scale' => 2,
                    'tooth' => $tooth, 'tooth_condition' => 'sound',
                    'medicines' => [
                        ['code' => 'MED-049', 'qty' => 1, 'dosage' => '150 ml', 'freq' => '2x1 kumur', 'inst' => 'Kumur setelah gosok gigi'],
                        ['code' => 'MED-007', 'qty' => 10, 'dosage' => '500 mg', 'freq' => '1x1 hari', 'inst' => 'Sesudah makan'],
                    ],
                ];
            } else {
                return [
                    'complaint' => "Gigi {$tooth} sudah tinggal sisa akar dan sering menusuk gusi serta terselip makanan.",
                    'current_history' => 'Gigi berlubang lama hingga mahkota hancur.',
                    'objective_finding' => "Gigi {$tooth} radix terbenam, perkusi (-), jaringan sekitar normal.",
                    'icd10_code' => 'K02.1',
                    'icd10_desc' => 'Karies Dentin / Radix Dentis',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '23.09',
                    'icd9_desc' => 'Pencabutan Gigi (Ekstraksi)',
                    'proc_tariff' => 75000,
                    'systolic' => 125, 'diastolic' => 82, 'temperature' => 36.7, 'pain_scale' => 4,
                    'tooth' => $tooth, 'tooth_condition' => 'radix',
                    'medicines' => [
                        ['code' => 'MED-004', 'qty' => 10, 'dosage' => '400 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan jika nyeri'],
                        ['code' => 'MED-002', 'qty' => 10, 'dosage' => '500 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan habiskan'],
                    ],
                ];
            }
        } elseif ($polyclinicId === 3) {
            // KIA Scenarios: 60% ANC, 40% Child Growth
            if ($seed % 2 === 0) {
                return [
                    'is_anc' => true,
                    'complaint' => 'Kontrol rutin kehamilan trimester kedua, merasakan gerak janin aktif.',
                    'current_history' => 'Mual muntah sudah berkurang, nafsu makan membaik.',
                    'objective_finding' => 'TFU 20 cm, DJJ 142x/m reguler, letak kepala, edema ekstremitas (-).',
                    'icd10_code' => 'Z34',
                    'icd10_desc' => 'Pengawasan Kehamilan Normal',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '89.03',
                    'icd9_desc' => 'Pemeriksaan Antenatal Care Terpadu',
                    'proc_tariff' => 50000,
                    'systolic' => 110, 'diastolic' => 70, 'temperature' => 36.6, 'pain_scale' => 0,
                    'medicines' => [
                        ['code' => 'MED-039', 'qty' => 30, 'dosage' => '1 tab', 'freq' => '1x1 malam', 'inst' => 'Sebelum tidur dengan air putih'],
                        ['code' => 'MED-040', 'qty' => 30, 'dosage' => '500 mg', 'freq' => '1x1 pagi', 'inst' => 'Sesudah makan'],
                    ],
                ];
            } else {
                return [
                    'is_anc' => false,
                    'complaint' => 'Pemeriksaan tumbuh kembang rutin dan jadwal imunisasi balita.',
                    'current_history' => 'Anak aktif, makan dan ASI lancar, tidak ada keluhan demam.',
                    'objective_finding' => 'Status gizi baik, perkembangan motorik kasar dan halus sesuai usia balita.',
                    'icd10_code' => 'Z00.1',
                    'icd10_desc' => 'Pemeriksaan Rutin Perkembangan Anak',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '99.59',
                    'icd9_desc' => 'Tindakan Imunisasi Balita',
                    'proc_tariff' => 35000,
                    'systolic' => 95, 'diastolic' => 60, 'temperature' => 36.7, 'pain_scale' => 1,
                    'medicines' => [
                        ['code' => 'MED-042', 'qty' => 1, 'dosage' => '1 cth', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan bila demam'],
                    ],
                ];
            }
        } else {
            // General Clinic (Poli Umum) Scenarios
            $uSeed = $seed % 6;
            if ($uSeed === 0) {
                return [
                    'complaint' => 'Demam sejak 2 hari yang lalu, disertai batuk berdahak dan hidung tersumbat.',
                    'current_history' => 'Badan terasa pegal dan linu-linu, tenggorokan perih menelan.',
                    'objective_finding' => 'Faring hiperemis (+), pembesaran tonsil T1-T1, ronki (-), wheezing (-).',
                    'icd10_code' => 'J06.9',
                    'icd10_desc' => 'Infeksi Saluran Pernafasan Akut (ISPA)',
                    'sec_icd10' => 'R50.9',
                    'sec_icd10_desc' => 'Demam Tidak Spesifik',
                    'icd9_code' => '89.03',
                    'icd9_desc' => 'Wawancara Medis dan Evaluasi Klinis',
                    'proc_tariff' => 0,
                    'systolic' => 120, 'diastolic' => 80, 'temperature' => 38.2, 'pain_scale' => 3,
                    'medicines' => [
                        ['code' => 'MED-001', 'qty' => 10, 'dosage' => '500 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan'],
                        ['code' => 'MED-002', 'qty' => 10, 'dosage' => '500 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan dihabiskan'],
                        ['code' => 'MED-003', 'qty' => 10, 'dosage' => '10 mg', 'freq' => '1x1 malam', 'inst' => 'Sebelum tidur'],
                        ['code' => 'MED-034', 'qty' => 10, 'dosage' => '30 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan'],
                    ],
                ];
            } elseif ($uSeed === 1) {
                return [
                    'complaint' => 'Nyeri ulu hati perih melilit, mual, perut terasa begah dan kembung.',
                    'current_history' => 'Keluhan timbul terutama setelah telat makan dan minum kopi.',
                    'objective_finding' => 'Nyeri tekan regio epigastrium (+), bising usus normal (8x/m), hepar/lien normal.',
                    'icd10_code' => 'K29.7',
                    'icd10_desc' => 'Gastritis Tidak Spesifik',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '89.07',
                    'icd9_desc' => 'Pemeriksaan Fisik Umum',
                    'proc_tariff' => 0,
                    'systolic' => 115, 'diastolic' => 75, 'temperature' => 36.6, 'pain_scale' => 4,
                    'medicines' => [
                        ['code' => 'MED-005', 'qty' => 10, 'dosage' => '20 mg', 'freq' => '2x1 hari', 'inst' => '30 menit sebelum makan'],
                        ['code' => 'MED-006', 'qty' => 1, 'dosage' => '1 cth', 'freq' => '3x1 hari', 'inst' => '1 jam sebelum makan atau saat perih'],
                        ['code' => 'MED-026', 'qty' => 10, 'dosage' => '10 mg', 'freq' => '3x1 hari', 'inst' => '15 menit sebelum makan'],
                    ],
                ];
            } elseif ($uSeed === 2) {
                return [
                    'complaint' => 'Kontrol rutin tekanan darah tinggi, tengkuk terasa agak berat di pagi hari.',
                    'current_history' => 'Rutin minum obat antihipertensi, obat habis sejak 2 hari lalu.',
                    'objective_finding' => 'TD 150/95 mmHg, suara jantung S1-S2 murni, edema extremitas (-).',
                    'icd10_code' => 'I10',
                    'icd10_desc' => 'Hipertensi Esensial',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '89.03',
                    'icd9_desc' => 'Konsultasi Evaluasi Hipertensi',
                    'proc_tariff' => 0,
                    'systolic' => 150, 'diastolic' => 95, 'temperature' => 36.5, 'pain_scale' => 2,
                    'medicines' => [
                        ['code' => 'MED-011', 'qty' => 30, 'dosage' => '10 mg', 'freq' => '1x1 malam', 'inst' => 'Malam hari sesudah makan'],
                        ['code' => 'MED-013', 'qty' => 30, 'dosage' => '8 mg', 'freq' => '1x1 pagi', 'inst' => 'Pagi hari sesudah makan'],
                    ],
                ];
            } elseif ($uSeed === 3) {
                return [
                    'complaint' => 'Kontrol rutin gula darah bulanan, badan kadang cepat lelah.',
                    'current_history' => 'Riwayat DM Tipe 2 sejak 3 tahun lalu, pola makan diusahakan terkontrol.',
                    'objective_finding' => 'Sensibilitas telapak kaki baik, luka perifer (-), pulsasi a. dorsalis pedis kuat.',
                    'icd10_code' => 'E11.9',
                    'icd10_desc' => 'Diabetes Mellitus Tipe 2 Tanpa Komplikasi',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '89.03',
                    'icd9_desc' => 'Konsultasi Pengelolaan Diabetes',
                    'proc_tariff' => 0,
                    'lab_code' => 'LAB-GDS',
                    'lab_result_val' => '186 mg/dL',
                    'systolic' => 128, 'diastolic' => 82, 'temperature' => 36.7, 'pain_scale' => 1,
                    'medicines' => [
                        ['code' => 'MED-014', 'qty' => 60, 'dosage' => '500 mg', 'freq' => '2x1 hari', 'inst' => 'Sesudah makan'],
                        ['code' => 'MED-015', 'qty' => 30, 'dosage' => '2 mg', 'freq' => '1x1 pagi', 'inst' => 'Sebelum sarapan pagi'],
                    ],
                ];
            } elseif ($uSeed === 4) {
                return [
                    'complaint' => 'Buang air besar cair lebih dari 4 kali sejak kemarin, badan lemas.',
                    'current_history' => 'Mulas melilit, tidak ada lendir maupun darah, mual ringan.',
                    'objective_finding' => 'Mata tidak cekung, turgor kulit kembali cepat, bising usus 14x/m.',
                    'icd10_code' => 'A09',
                    'icd10_desc' => 'Diare dan Gastroenteritis',
                    'sec_icd10' => null, 'sec_icd10_desc' => null,
                    'icd9_code' => '89.07',
                    'icd9_desc' => 'Pemeriksaan Fisik Abdomen',
                    'proc_tariff' => 0,
                    'systolic' => 110, 'diastolic' => 70, 'temperature' => 37.3, 'pain_scale' => 3,
                    'medicines' => [
                        ['code' => 'MED-028', 'qty' => 6, 'dosage' => '1 sachet', 'freq' => 'Tiap BAB cair', 'inst' => 'Larutkan dalam 200 ml air matang'],
                        ['code' => 'MED-029', 'qty' => 10, 'dosage' => '20 mg', 'freq' => '1x1 hari', 'inst' => 'Sesudah makan habiskan selama 10 hari'],
                        ['code' => 'MED-001', 'qty' => 10, 'dosage' => '500 mg', 'freq' => '3x1 hari', 'inst' => 'Sesudah makan bila demam/nyeri'],
                    ],
                ];
            } else {
                return [
                    'complaint' => 'Nyeri pada kedua sendi lutut dan otot pinggang terasa kaku setelah aktivitas.',
                    'current_history' => 'Keluhan dirasakan berulang terutama saat bangun tidur pagi.',
                    'objective_finding' => 'ROM genu dextra terbatas ringan karena nyeri, krepitasi (+), edema (-).',
                    'icd10_code' => 'M79.1',
                    'icd10_desc' => 'Mialgia / Nyeri Otot',
                    'sec_icd10' => 'M10.9',
                    'sec_icd10_desc' => 'Gout Artritis',
                    'icd9_code' => '89.03',
                    'icd9_desc' => 'Evaluasi Klinis Reumatik / Mialgia',
                    'proc_tariff' => 0,
                    'lab_code' => 'LAB-UA',
                    'lab_result_val' => '6.8 mg/dL',
                    'systolic' => 130, 'diastolic' => 85, 'temperature' => 36.6, 'pain_scale' => 4,
                    'medicines' => [
                        ['code' => 'MED-036', 'qty' => 10, 'dosage' => '15 mg', 'freq' => '1x1 hari', 'inst' => 'Sesudah makan siang'],
                        ['code' => 'MED-038', 'qty' => 10, 'dosage' => '1 tab', 'freq' => '1x1 hari', 'inst' => 'Pagi hari sesudah makan'],
                    ],
                ];
            }
        }
    }
}
