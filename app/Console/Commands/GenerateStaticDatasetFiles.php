<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateStaticDatasetFiles extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'clinic:generate-dataset-files 
                            {--patients=100000 : Jumlah pasien unik yang digenerate}
                            {--visits=200000 : Jumlah kunjungan operasional}
                            {--output= : Direktori output dataset}';

    /**
     * The console command description.
     */
    protected $description = 'Generate pre-generated CSV dataset files into database/seeders/datasets/ without loops in seeders';

    protected string $outputDir;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        $targetPatients = (int) $this->option('patients');
        $targetVisits = (int) $this->option('visits');
        $this->outputDir = $this->option('output') ?: database_path('seeders/datasets');

        if (! File::isDirectory($this->outputDir)) {
            File::makeDirectory($this->outputDir, 0755, true);
        }

        $this->info('======================================================================');
        $this->info('  GENERATOR PRE-GENERATED DATASET CSV RME KLINIK (2020 - 2026)        ');
        $this->info("  Output Directory: {$this->outputDir}");
        $this->info("  Target Pasien   : {$targetPatients} | Target Kunjungan: {$targetVisits}");
        $this->info('======================================================================');

        // 1. Generate Patients & Demographics
        $this->generatePatientDatasets($targetPatients);

        // 2. Generate Master Medicines & Initial Inventory Batches
        $this->generateMedicineAndBatchDatasets();

        // 3. Generate Visits & Complete Operations
        $this->generateOperationalDatasets($targetVisits, $targetPatients);

        $this->line('');
        $this->info('✓ SELURUH FILE DATASET CSV BERHASIL DIBUAT!');
        $this->info("Lokasi dataset: {$this->outputDir}");

        return Command::SUCCESS;
    }

    /**
     * Helper to write a CSV row safely converting NULL to \N for MySQL LOAD DATA LOCAL INFILE.
     */
    protected function writeCsvRow($fp, array $row): void
    {
        foreach ($row as &$val) {
            if ($val === null) {
                $val = '\N';
            } elseif (is_bool($val)) {
                $val = $val ? 1 : 0;
            }
        }
        fputcsv($fp, $row);
    }

    /**
     * Generate patients.csv, patient_allergies.csv, patient_emergency_contacts.csv.
     */
    protected function generatePatientDatasets(int $targetPatients): void
    {
        $this->comment("1. Membuat dataset pasien unik ({$targetPatients} baris)...");

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

        $fpPatients = fopen("{$this->outputDir}/patients.csv", 'w');
        $fpAllergies = fopen("{$this->outputDir}/patient_allergies.csv", 'w');
        $fpContacts = fopen("{$this->outputDir}/patient_emergency_contacts.csv", 'w');

        // Headers matching migrations
        fputcsv($fpPatients, [
            'id', 'medical_record_number', 'nik', 'name', 'place_of_birth', 'date_of_birth',
            'gender', 'blood_type', 'address', 'rt', 'rw', 'kelurahan', 'kecamatan', 'city',
            'province', 'postal_code', 'phone', 'email', 'marital_status', 'occupation',
            'religion', 'education', 'is_verified', 'created_by', 'created_at', 'updated_at',
        ]);

        fputcsv($fpAllergies, [
            'id', 'patient_id', 'allergen_type', 'allergen_name', 'reaction', 'severity',
            'notes', 'created_by', 'created_at', 'updated_at',
        ]);

        fputcsv($fpContacts, [
            'id', 'patient_id', 'name', 'relationship', 'phone', 'address', 'created_at', 'updated_at',
        ]);

        $allergyId = 1;
        $contactId = 1;
        $adminUserId = 1;

        $bar = $this->output->createProgressBar($targetPatients);
        $bar->start();

        for ($i = 1; $i <= $targetPatients; $i++) {
            $isFemale = ($i % 2 === 0);
            $gender = $isFemale ? 'P' : 'L';

            $firstName = $isFemale
                ? $femaleFirst[($i * 7) % count($femaleFirst)]
                : $maleFirst[($i * 11) % count($maleFirst)];
            $lastName = $lastNames[($i * 13) % count($lastNames)];
            $fullName = "{$firstName} {$lastName}";

            // Demografis Usia (0 s/d 85 thn)
            $seedMod = $i % 100;
            if ($seedMod < 10) {
                $ageYears = $i % 6; // Balita
            } elseif ($seedMod < 20) {
                $ageYears = 6 + ($i % 12); // Remaja
            } elseif ($seedMod < 55) {
                $ageYears = 18 + ($i % 18); // Usia produktif
            } elseif ($seedMod < 80) {
                $ageYears = 36 + ($i % 20); // Paruh baya
            } else {
                $ageYears = 56 + ($i % 30); // Lansia
            }

            $dob = Carbon::create(2026 - $ageYears, 1 + ($i % 12), 1 + ($i % 28));

            // Format NIK resmi Dukcapil: 3573 + (DD (+40 jika wanita)) + MM + YY + 4 digit
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

            $regYear = 2020 + ($i % 7);
            $regMonth = 1 + (($i * 3) % 12);
            $regDay = 1 + (($i * 7) % 28);
            $registeredAt = Carbon::create($regYear, $regMonth, $regDay, 8 + ($i % 10), ($i % 60), 0)->toDateTimeString();

            $this->writeCsvRow($fpPatients, [
                $i,
                $noRm,
                $nik,
                $fullName,
                'Malang',
                $dob->toDateString(),
                $gender,
                $bloodTypes[$i % count($bloodTypes)],
                $street,
                $rt,
                $rw,
                $kel,
                $kec,
                'Kota Malang',
                'Jawa Timur',
                '651'.str_pad((($i % 45) + 10), 2, '0', STR_PAD_LEFT),
                $phone,
                strtolower($firstName.'.'.$lastName.$i.'@example.com'),
                $marital,
                $occupation,
                $religions[$i % count($religions)],
                $educations[$i % count($educations)],
                1,
                $adminUserId,
                $registeredAt,
                $registeredAt,
            ]);

            // ~15% pasien mempunyai alergi
            if ($i % 7 === 0) {
                $al = $allergens[($i / 7) % count($allergens)];
                $this->writeCsvRow($fpAllergies, [
                    $allergyId++,
                    $i,
                    $al['allergen_type'],
                    $al['allergen_name'],
                    $al['reaction'],
                    $al['severity'],
                    'Tercatat pada anamnesis awal',
                    $adminUserId,
                    $registeredAt,
                    $registeredAt,
                ]);
            }

            // ~50% pasien mempunyai kontak darurat
            if ($i % 2 === 0) {
                $contactName = $lastNames[($i * 5) % count($lastNames)].' '.$firstName;
                $this->writeCsvRow($fpContacts, [
                    $contactId++,
                    $i,
                    $contactName,
                    $relationships[$i % count($relationships)],
                    '08'.(1300000000 + (($i * 513) % 800000000)),
                    $street,
                    $registeredAt,
                    $registeredAt,
                ]);
            }

            if ($i % 5000 === 0) {
                $bar->advance(5000);
            }
        }

        $bar->finish();
        fclose($fpPatients);
        fclose($fpAllergies);
        fclose($fpContacts);
        $this->line('');
        $this->info('✓ Dataset Pasien selesai dibuat (patients.csv, allergies.csv, emergency_contacts.csv).');
    }

    /**
     * Generate medicines.csv, medicine_batches.csv, medicine_receipts.csv, medicine_receipt_items.csv.
     */
    protected function generateMedicineAndBatchDatasets(): void
    {
        $this->comment('2. Membuat dataset master obat, batch, dan penerimaan farmasi...');

        // Master Medicines Catalog (MED-001 s/d MED-050)
        $newMedicines = [
            ['id' => 1, 'code' => 'MED-001', 'generic_name' => 'Paracetamol', 'brand_name' => 'Paracetamol 500 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 350, 'selling_price' => 700],
            ['id' => 2, 'code' => 'MED-002', 'generic_name' => 'Amoxicillin Trihydrate', 'brand_name' => 'Amoxicillin 500 mg', 'category_id' => 2, 'unit_id' => 2, 'dosage_form' => 'Kapsul', 'strength' => '500 mg', 'purchase_price' => 750, 'selling_price' => 1500],
            ['id' => 3, 'code' => 'MED-003', 'generic_name' => 'Cetirizine HCl', 'brand_name' => 'Cetirizine 10 mg', 'category_id' => 3, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 450, 'selling_price' => 1000],
            ['id' => 4, 'code' => 'MED-004', 'generic_name' => 'Ibuprofen', 'brand_name' => 'Ibuprofen 400 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '400 mg', 'purchase_price' => 500, 'selling_price' => 1200],
            ['id' => 5, 'code' => 'MED-005', 'generic_name' => 'Omeprazole', 'brand_name' => 'Omeprazole 20 mg', 'category_id' => 4, 'unit_id' => 2, 'dosage_form' => 'Kapsul', 'strength' => '20 mg', 'purchase_price' => 900, 'selling_price' => 2000],
            ['id' => 6, 'code' => 'MED-006', 'generic_name' => 'Antasida DOEN', 'brand_name' => 'Antasida DOEN Suspensi', 'category_id' => 4, 'unit_id' => 3, 'dosage_form' => 'Sirup', 'strength' => '60 ml', 'purchase_price' => 6000, 'selling_price' => 10000],
            ['id' => 7, 'code' => 'MED-007', 'generic_name' => 'Asam Askorbat', 'brand_name' => 'Vitamin C 500 mg', 'category_id' => 6, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['id' => 8, 'code' => 'MED-008', 'generic_name' => 'Salbutamol Sulfat', 'brand_name' => 'Salbutamol 2 mg', 'category_id' => 5, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '2 mg', 'purchase_price' => 250, 'selling_price' => 600],
            ['id' => 9, 'code' => 'MED-009', 'generic_name' => 'Asam Mefenamat', 'brand_name' => 'Asam Mefenamat 500 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 450, 'selling_price' => 1000],
            ['id' => 10, 'code' => 'MED-010', 'generic_name' => 'Ciprofloxacin HCl', 'brand_name' => 'Ciprofloxacin 500 mg', 'category_id' => 2, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 800, 'selling_price' => 1500],
            ['id' => 11, 'code' => 'MED-011', 'generic_name' => 'Cefadroxil Monohydrate', 'brand_name' => 'Cefadroxil 500 mg', 'category_id' => 2, 'unit_id' => 2, 'dosage_form' => 'Kapsul', 'strength' => '500 mg', 'purchase_price' => 1200, 'selling_price' => 2200],
            ['id' => 12, 'code' => 'MED-012', 'generic_name' => 'Antasida DOEN Kunyah', 'brand_name' => 'Antasida DOEN Tablet Kunyah', 'category_id' => 4, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '1 Tab', 'purchase_price' => 200, 'selling_price' => 450],
            ['id' => 13, 'code' => 'MED-013', 'generic_name' => 'Dimenhidrinat', 'brand_name' => 'Dimenhidrinat 50 mg', 'category_id' => 3, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '50 mg', 'purchase_price' => 250, 'selling_price' => 500],
            ['id' => 14, 'code' => 'MED-014', 'generic_name' => 'Loratadine', 'brand_name' => 'Loratadine 10 mg', 'category_id' => 3, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 400, 'selling_price' => 800],
            ['id' => 15, 'code' => 'MED-015', 'generic_name' => 'Ambroxol HCl', 'brand_name' => 'Ambroxol 30 mg', 'category_id' => 5, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '30 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['id' => 16, 'code' => 'MED-016', 'generic_name' => 'Asetilsistein', 'brand_name' => 'Asetilsistein 200 mg', 'category_id' => 5, 'unit_id' => 2, 'dosage_form' => 'Kapsul', 'strength' => '200 mg', 'purchase_price' => 900, 'selling_price' => 1800],
            ['id' => 17, 'code' => 'MED-017', 'generic_name' => 'Sianokobalamin', 'brand_name' => 'Vitamin B12 50 mcg', 'category_id' => 6, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '50 mcg', 'purchase_price' => 200, 'selling_price' => 400],
            ['id' => 18, 'code' => 'MED-018', 'generic_name' => 'Piridoksin HCl', 'brand_name' => 'Vitamin B6 10 mg', 'category_id' => 6, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 200, 'selling_price' => 400],
            ['id' => 19, 'code' => 'MED-019', 'generic_name' => 'Amlodipine Besylate', 'brand_name' => 'Amlodipine 5 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '5 mg', 'purchase_price' => 350, 'selling_price' => 700],
            ['id' => 20, 'code' => 'MED-020', 'generic_name' => 'Amlodipine Besylate', 'brand_name' => 'Amlodipine 10 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 500, 'selling_price' => 1000],
            ['id' => 21, 'code' => 'MED-021', 'generic_name' => 'Metformin HCl', 'brand_name' => 'Metformin 500 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 350, 'selling_price' => 700],
            ['id' => 22, 'code' => 'MED-022', 'generic_name' => 'Glimepiride', 'brand_name' => 'Glimepiride 2 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '2 mg', 'purchase_price' => 800, 'selling_price' => 1500],
            ['id' => 23, 'code' => 'MED-023', 'generic_name' => 'Allopurinol', 'brand_name' => 'Allopurinol 100 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '100 mg', 'purchase_price' => 450, 'selling_price' => 900],
            ['id' => 24, 'code' => 'MED-024', 'generic_name' => 'Simvastatin', 'brand_name' => 'Simvastatin 20 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '20 mg', 'purchase_price' => 900, 'selling_price' => 1800],
            ['id' => 25, 'code' => 'MED-025', 'generic_name' => 'Atorvastatin', 'brand_name' => 'Atorvastatin 20 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '20 mg', 'purchase_price' => 2500, 'selling_price' => 4500],
            ['id' => 26, 'code' => 'MED-026', 'generic_name' => 'Salbutamol', 'brand_name' => 'Salbutamol 2 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '2 mg', 'purchase_price' => 250, 'selling_price' => 500],
            ['id' => 27, 'code' => 'MED-027', 'generic_name' => 'Salbutamol Cairan Inhalasi', 'brand_name' => 'Ventolin Nebules 2.5 mg', 'category_id' => 1, 'unit_id' => 2, 'dosage_form' => 'Cairan', 'strength' => '2.5 mg / 2.5 ml', 'purchase_price' => 11000, 'selling_price' => 16000],
            ['id' => 28, 'code' => 'MED-028', 'generic_name' => 'Loperamide HCl', 'brand_name' => 'Loperamide 2 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '2 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['id' => 29, 'code' => 'MED-029', 'generic_name' => 'Oralit', 'brand_name' => 'Oralit 200 ml Sachet', 'category_id' => 2, 'unit_id' => 3, 'dosage_form' => 'Serbuk', 'strength' => '4.1 g', 'purchase_price' => 700, 'selling_price' => 1200],
            ['id' => 30, 'code' => 'MED-030', 'generic_name' => 'Domperidone', 'brand_name' => 'Domperidone 10 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 500, 'selling_price' => 1000],
            ['id' => 31, 'code' => 'MED-031', 'generic_name' => 'Ranitidine HCl', 'brand_name' => 'Ranitidine 150 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '150 mg', 'purchase_price' => 450, 'selling_price' => 900],
            ['id' => 32, 'code' => 'MED-032', 'generic_name' => 'Lansoprazole', 'brand_name' => 'Lansoprazole 30 mg', 'category_id' => 1, 'unit_id' => 4, 'dosage_form' => 'Kapsul', 'strength' => '30 mg', 'purchase_price' => 1200, 'selling_price' => 2200],
            ['id' => 33, 'code' => 'MED-033', 'generic_name' => 'Metronidazole', 'brand_name' => 'Metronidazole 500 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 500, 'selling_price' => 1000],
            ['id' => 34, 'code' => 'MED-034', 'generic_name' => 'Dexamethasone', 'brand_name' => 'Dexamethasone 0.5 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '0.5 mg', 'purchase_price' => 200, 'selling_price' => 450],
            ['id' => 35, 'code' => 'MED-035', 'generic_name' => 'Methylprednisolone', 'brand_name' => 'Methylprednisolone 4 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '4 mg', 'purchase_price' => 700, 'selling_price' => 1400],
            ['id' => 36, 'code' => 'MED-036', 'generic_name' => 'Meloxicam', 'brand_name' => 'Meloxicam 15 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '15 mg', 'purchase_price' => 800, 'selling_price' => 1600],
            ['id' => 37, 'code' => 'MED-037', 'generic_name' => 'Ketorolac Tromethamine', 'brand_name' => 'Ketorolac 10 mg', 'category_id' => 1, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '10 mg', 'purchase_price' => 1200, 'selling_price' => 2200],
            ['id' => 38, 'code' => 'MED-038', 'generic_name' => 'Vitamin B Kompleks', 'brand_name' => 'Vitamin B Complex Tablet', 'category_id' => 3, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '1 Tab', 'purchase_price' => 250, 'selling_price' => 500],
            ['id' => 39, 'code' => 'MED-039', 'generic_name' => 'Kalsium Laktat (Kalk)', 'brand_name' => 'Kalsium Laktat 500 mg', 'category_id' => 3, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '500 mg', 'purchase_price' => 300, 'selling_price' => 600],
            ['id' => 40, 'code' => 'MED-040', 'generic_name' => 'Ferrous Fumarate + Asam Folat', 'brand_name' => 'Tablet Tambah Darah (TTD)', 'category_id' => 3, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '60 mg Besi / 400 mcg Folat', 'purchase_price' => 300, 'selling_price' => 600],
            ['id' => 41, 'code' => 'MED-041', 'generic_name' => 'Asam Folat', 'brand_name' => 'Asam Folat 1 mg', 'category_id' => 3, 'unit_id' => 1, 'dosage_form' => 'Tablet', 'strength' => '1 mg', 'purchase_price' => 200, 'selling_price' => 400],
            ['id' => 42, 'code' => 'MED-042', 'generic_name' => 'Paracetamol Sirup', 'brand_name' => 'Paracetamol Sirup 120 mg/5ml', 'category_id' => 2, 'unit_id' => 2, 'dosage_form' => 'Sirup', 'strength' => '120 mg/5ml (60ml)', 'purchase_price' => 6500, 'selling_price' => 11000],
            ['id' => 43, 'code' => 'MED-043', 'generic_name' => 'Amoxicillin Sirup Kering', 'brand_name' => 'Amoxicillin Dry Syrup 125 mg/5ml', 'category_id' => 1, 'unit_id' => 2, 'dosage_form' => 'Sirup', 'strength' => '125 mg/5ml (60ml)', 'purchase_price' => 8500, 'selling_price' => 14000],
            ['id' => 44, 'code' => 'MED-044', 'generic_name' => 'Ambroxol Sirup', 'brand_name' => 'Ambroxol Sirup 15 mg/5ml', 'category_id' => 1, 'unit_id' => 2, 'dosage_form' => 'Sirup', 'strength' => '15 mg/5ml (60ml)', 'purchase_price' => 6000, 'selling_price' => 10500],
            ['id' => 45, 'code' => 'MED-045', 'generic_name' => 'Hidrokortison Asetat', 'brand_name' => 'Hidrokortison Krim 2.5%', 'category_id' => 1, 'unit_id' => 5, 'dosage_form' => 'Salep', 'strength' => '5 g', 'purchase_price' => 5000, 'selling_price' => 8500],
            ['id' => 46, 'code' => 'MED-046', 'generic_name' => 'Betametason Valerat', 'brand_name' => 'Betametason Krim 0.1%', 'category_id' => 1, 'unit_id' => 5, 'dosage_form' => 'Salep', 'strength' => '5 g', 'purchase_price' => 4500, 'selling_price' => 8000],
            ['id' => 47, 'code' => 'MED-047', 'generic_name' => 'Gentamisin Sulfat', 'brand_name' => 'Gentamisin Salep Kulit 0.1%', 'category_id' => 1, 'unit_id' => 5, 'dosage_form' => 'Salep', 'strength' => '5 g', 'purchase_price' => 4000, 'selling_price' => 7500],
            ['id' => 48, 'code' => 'MED-048', 'generic_name' => 'Ketokonazol', 'brand_name' => 'Ketokonazol Krim 2%', 'category_id' => 1, 'unit_id' => 5, 'dosage_form' => 'Salep', 'strength' => '10 g', 'purchase_price' => 7000, 'selling_price' => 12000],
            ['id' => 49, 'code' => 'MED-049', 'generic_name' => 'Klorheksidin Glukonat', 'brand_name' => 'Minosep Obat Kumur 0.2%', 'category_id' => 2, 'unit_id' => 2, 'dosage_form' => 'Cairan', 'strength' => '150 ml', 'purchase_price' => 18000, 'selling_price' => 26000],
            ['id' => 50, 'code' => 'MED-050', 'generic_name' => 'Kloramfenikol', 'brand_name' => 'Kloramfenikol Tetes Telinga 3%', 'category_id' => 1, 'unit_id' => 2, 'dosage_form' => 'Tetes', 'strength' => '5 ml', 'purchase_price' => 5500, 'selling_price' => 9500],
        ];

        $fpMeds = fopen("{$this->outputDir}/medicines.csv", 'w');
        fputcsv($fpMeds, [
            'id', 'code', 'generic_name', 'brand_name', 'category_id', 'unit_id', 'dosage_form',
            'strength', 'purchase_price', 'selling_price', 'min_stock', 'is_active', 'created_at', 'updated_at',
        ]);

        $now = now()->toDateTimeString();
        foreach ($newMedicines as $m) {
            $this->writeCsvRow($fpMeds, [
                $m['id'], $m['code'], $m['generic_name'], $m['brand_name'], $m['category_id'],
                $m['unit_id'], $m['dosage_form'], $m['strength'], $m['purchase_price'],
                $m['selling_price'], 20, 1, $now, $now,
            ]);
        }
        fclose($fpMeds);

        // 150 Batches & Receipts (3 batches per 50 medicines: 2024, 2026, 2028)
        $fpBatches = fopen("{$this->outputDir}/medicine_batches.csv", 'w');
        $fpReceipts = fopen("{$this->outputDir}/medicine_receipts.csv", 'w');
        $fpReceiptItems = fopen("{$this->outputDir}/medicine_receipt_items.csv", 'w');

        fputcsv($fpBatches, ['id', 'medicine_id', 'batch_number', 'expiry_date', 'current_stock', 'purchase_price', 'supplier_id', 'created_at', 'updated_at']);
        fputcsv($fpReceipts, ['id', 'receipt_number', 'supplier_id', 'invoice_number', 'received_date', 'total_amount', 'notes', 'received_by', 'created_at', 'updated_at']);
        fputcsv($fpReceiptItems, ['id', 'medicine_receipt_id', 'medicine_id', 'batch_number', 'expiry_date', 'quantity', 'purchase_price', 'subtotal', 'created_at', 'updated_at']);

        $batchId = 1;
        $receiptId = 1;
        $years = [2024, 2026, 2028];
        $initialStock = 50000;

        for ($mId = 1; $mId <= 50; $mId++) {
            $medCode = 'MED-'.str_pad($mId, 3, '0', STR_PAD_LEFT);
            $medPrice = 1000.00;

            foreach ($years as $idx => $yr) {
                $batchNo = "BTH-{$medCode}-{$yr}-".($idx + 1);
                $expiry = "{$yr}-12-31";

                $this->writeCsvRow($fpBatches, [
                    $batchId,
                    $mId,
                    $batchNo,
                    $expiry,
                    $initialStock,
                    $medPrice,
                    1,
                    '2020-01-01 08:00:00',
                    $now,
                ]);

                $this->writeCsvRow($fpReceipts, [
                    $receiptId,
                    "RCV-202001-{$batchId}",
                    1,
                    "INV-PBF-{$batchId}",
                    '2020-01-02',
                    $initialStock * $medPrice,
                    'Penerimaan Stok Awal Gudang Farmasi',
                    1,
                    '2020-01-02 09:00:00',
                    $now,
                ]);

                $this->writeCsvRow($fpReceiptItems, [
                    $receiptId,
                    $receiptId,
                    $mId,
                    $batchNo,
                    $expiry,
                    $initialStock,
                    $medPrice,
                    $initialStock * $medPrice,
                    '2020-01-02 09:00:00',
                    $now,
                ]);

                $batchId++;
                $receiptId++;
            }
        }

        fclose($fpBatches);
        fclose($fpReceipts);
        fclose($fpReceiptItems);
        $this->info('✓ Master Obat & 150 Batch Farmasi selesai dibuat.');
    }

    /**
     * Generate visits.csv and all downstream clinical, pharmacy, lab, and financial CSVs.
     */
    protected function generateOperationalDatasets(int $targetVisits, int $totalPatients): void
    {
        $this->comment("3. Membuat dataset operasional ({$targetVisits} kunjungan & jutaan transaksi)...");

        // Open all operational CSV files
        $fpVisits = fopen("{$this->outputDir}/visits.csv", 'w');
        $fpHistories = fopen("{$this->outputDir}/visit_status_histories.csv", 'w');
        $fpAssessments = fopen("{$this->outputDir}/nursing_assessments.csv", 'w');
        $fpVitals = fopen("{$this->outputDir}/vital_signs.csv", 'w');
        $fpMR = fopen("{$this->outputDir}/medical_records.csv", 'w');
        $fpDiagnoses = fopen("{$this->outputDir}/medical_record_diagnoses.csv", 'w');
        $fpProcedures = fopen("{$this->outputDir}/medical_record_procedures.csv", 'w');
        $fpOdontograms = fopen("{$this->outputDir}/odontograms.csv", 'w');
        $fpOdontogramTeeth = fopen("{$this->outputDir}/odontogram_teeth.csv", 'w');
        $fpAntenatal = fopen("{$this->outputDir}/antenatal_cares.csv", 'w');
        $fpChildGrowth = fopen("{$this->outputDir}/child_growths.csv", 'w');
        $fpPrescriptions = fopen("{$this->outputDir}/prescriptions.csv", 'w');
        $fpRxItems = fopen("{$this->outputDir}/prescription_items.csv", 'w');
        $fpCompounds = fopen("{$this->outputDir}/prescription_compounds.csv", 'w');
        $fpCompoundItems = fopen("{$this->outputDir}/prescription_compound_items.csv", 'w');
        $fpDispensing = fopen("{$this->outputDir}/dispensing_transactions.csv", 'w');
        $fpDispensingItems = fopen("{$this->outputDir}/dispensing_items.csv", 'w');
        $fpMovements = fopen("{$this->outputDir}/medicine_stock_movements.csv", 'w');
        $fpLabRequests = fopen("{$this->outputDir}/laboratory_requests.csv", 'w');
        $fpLabItems = fopen("{$this->outputDir}/laboratory_request_items.csv", 'w');
        $fpLabResults = fopen("{$this->outputDir}/laboratory_results.csv", 'w');
        $fpInvoices = fopen("{$this->outputDir}/billing_invoices.csv", 'w');
        $fpBillingItems = fopen("{$this->outputDir}/billing_items.csv", 'w');
        $fpPayments = fopen("{$this->outputDir}/payments.csv", 'w');
        $fpRefunds = fopen("{$this->outputDir}/refunds.csv", 'w');
        $fpFees = fopen("{$this->outputDir}/medical_fee_transactions.csv", 'w');
        $fpDocs = fopen("{$this->outputDir}/medical_documents.csv", 'w');
        $fpPatientDocs = fopen("{$this->outputDir}/patient_documents.csv", 'w');
        $fpAudit = fopen("{$this->outputDir}/audit_logs.csv", 'w');
        $fpFhir = fopen("{$this->outputDir}/fhir_mappings.csv", 'w');

        // Write Exact Migration-Compliant Headers
        fputcsv($fpVisits, ['id', 'visit_number', 'patient_id', 'polyclinic_id', 'doctor_id', 'registered_by', 'registration_date', 'visit_type', 'payment_type', 'insurance_number', 'status', 'queue_number', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpHistories, ['id', 'visit_id', 'status_from', 'status_to', 'changed_by', 'reason', 'created_at', 'updated_at']);
        fputcsv($fpAssessments, ['id', 'visit_id', 'patient_id', 'nurse_id', 'assessment_time', 'chief_complaint', 'current_illness_history', 'past_illness_history', 'family_illness_history', 'current_medication', 'systolic', 'diastolic', 'temperature', 'pulse_rate', 'respiratory_rate', 'oxygen_saturation', 'weight', 'height', 'bmi', 'waist_circumference', 'consciousness', 'pain_scale', 'fall_risk', 'nursing_care_plan', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpVitals, ['id', 'patient_id', 'visit_id', 'recorded_by', 'recorded_at', 'systolic', 'diastolic', 'temperature', 'pulse_rate', 'respiratory_rate', 'oxygen_saturation', 'weight', 'height', 'bmi', 'created_at', 'updated_at']);
        fputcsv($fpMR, ['id', 'visit_id', 'patient_id', 'doctor_id', 'examination_time', 'subjective', 'objective', 'assessment', 'plan', 'status', 'finalized_at', 'finalized_by', 'education_plan', 'follow_up_plan', 'next_visit_date', 'created_at', 'updated_at']);
        fputcsv($fpDiagnoses, ['id', 'medical_record_id', 'icd10_code', 'description', 'type', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpProcedures, ['id', 'medical_record_id', 'icd9cm_code', 'description', 'tariff', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpOdontograms, ['id', 'visit_id', 'patient_id', 'doctor_id', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpOdontogramTeeth, ['id', 'odontogram_id', 'tooth_number', 'condition', 'surfaces', 'treatment_notes', 'created_at', 'updated_at']);
        fputcsv($fpAntenatal, ['id', 'visit_id', 'patient_id', 'doctor_id', 'gravida', 'para', 'abortus', 'hpht', 'estimated_due_date', 'gestational_age_weeks', 'fundal_height', 'fetal_heart_rate', 'fetal_presentation', 'systolic', 'diastolic', 'weight', 'complaints', 'treatment_plan', 'created_at', 'updated_at']);
        fputcsv($fpChildGrowth, ['id', 'visit_id', 'patient_id', 'doctor_id', 'age_months', 'weight', 'height', 'head_circumference', 'immunization_status', 'development_milestone', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpPrescriptions, ['id', 'prescription_number', 'visit_id', 'medical_record_id', 'doctor_id', 'prescription_date', 'status', 'doctor_notes', 'pharmacist_notes', 'verified_by', 'verified_at', 'dispensed_at', 'created_at', 'updated_at']);
        fputcsv($fpRxItems, ['id', 'prescription_id', 'medicine_id', 'quantity', 'dosage', 'frequency', 'route', 'duration_days', 'instructions', 'special_notes', 'created_at', 'updated_at']);
        fputcsv($fpCompounds, ['id', 'prescription_id', 'compound_name', 'dosage_form', 'package_count', 'frequency', 'instructions', 'special_notes', 'created_at', 'updated_at']);
        fputcsv($fpCompoundItems, ['id', 'prescription_compound_id', 'medicine_id', 'quantity', 'unit', 'created_at', 'updated_at']);
        fputcsv($fpDispensing, ['id', 'dispensing_number', 'prescription_id', 'visit_id', 'dispensed_by', 'dispensed_at', 'total_amount', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpDispensingItems, ['id', 'dispensing_transaction_id', 'prescription_item_id', 'medicine_id', 'batch_id', 'quantity', 'unit_price', 'subtotal', 'created_at', 'updated_at']);
        fputcsv($fpMovements, ['id', 'medicine_id', 'batch_id', 'movement_type', 'quantity', 'stock_before', 'stock_after', 'reference_type', 'reference_id', 'notes', 'created_by', 'created_at', 'updated_at']);
        fputcsv($fpLabRequests, ['id', 'request_number', 'visit_id', 'doctor_id', 'request_time', 'clinical_notes', 'sample_collected_at', 'sample_collector', 'status', 'created_at', 'updated_at']);
        fputcsv($fpLabItems, ['id', 'laboratory_request_id', 'laboratory_id', 'tariff', 'created_at', 'updated_at']);
        fputcsv($fpLabResults, ['id', 'laboratory_request_id', 'laboratory_id', 'result_value', 'is_critical', 'notes', 'tested_by', 'verified_by', 'verified_at', 'created_at', 'updated_at']);
        fputcsv($fpInvoices, ['id', 'invoice_number', 'visit_id', 'patient_id', 'total_amount', 'discount_amount', 'discount_reason', 'final_amount', 'paid_amount', 'balance_amount', 'status', 'created_by', 'created_at', 'updated_at']);
        fputcsv($fpBillingItems, ['id', 'billing_invoice_id', 'item_type', 'item_code', 'item_name', 'quantity', 'unit_price', 'subtotal', 'reference_type', 'reference_id', 'created_at', 'updated_at']);
        fputcsv($fpPayments, ['id', 'receipt_number', 'billing_invoice_id', 'payment_method_id', 'amount_paid', 'amount_tendered', 'change_due', 'payment_time', 'cashier_id', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpRefunds, ['id', 'billing_invoice_id', 'payment_id', 'refund_number', 'amount', 'reason', 'approved_by', 'refunded_at', 'created_at', 'updated_at']);
        fputcsv($fpFees, ['id', 'billing_item_id', 'visit_id', 'doctor_id', 'nurse_id', 'doctor_fee', 'nurse_fee', 'clinic_fee', 'status', 'created_at', 'updated_at']);
        fputcsv($fpDocs, ['id', 'document_number', 'visit_id', 'patient_id', 'doctor_id', 'type', 'issue_date', 'content_data', 'notes', 'created_at', 'updated_at']);
        fputcsv($fpPatientDocs, ['id', 'patient_id', 'visit_id', 'title', 'document_type', 'file_path', 'mime_type', 'file_size', 'uploaded_by', 'created_at', 'updated_at']);
        fputcsv($fpAudit, ['id', 'user_id', 'action', 'module', 'description', 'ip_address', 'user_agent', 'old_values', 'new_values', 'created_at']);
        fputcsv($fpFhir, ['id', 'resource_type', 'local_table', 'local_id', 'fhir_id', 'status', 'last_synced_at', 'created_at', 'updated_at']);

        // Write initial 150 stock receipts movements into movements CSV
        $movementId = 1;
        $initialStock = 50000;
        $years = [2024, 2026, 2028];
        $bId = 1;
        for ($mId = 1; $mId <= 50; $mId++) {
            $medCode = 'MED-'.str_pad($mId, 3, '0', STR_PAD_LEFT);
            foreach ($years as $idx => $yr) {
                $batchNo = "BTH-{$medCode}-{$yr}-".($idx + 1);
                $this->writeCsvRow($fpMovements, [
                    $movementId++,
                    $mId,
                    $bId,
                    'receipt',
                    $initialStock,
                    0,
                    $initialStock,
                    'medicine_receipt',
                    $bId,
                    "Stok Awal Batch {$batchNo}",
                    1,
                    '2020-01-02 09:00:00',
                    '2020-01-02 09:00:00',
                ]);
                $bId++;
            }
        }

        // Initialize Counters
        $historyId = 1;
        $assessmentId = 1;
        $vitalsId = 1;
        $mrId = 1;
        $diagnosisId = 1;
        $procedureId = 1;
        $odontogramId = 1;
        $odontogramToothId = 1;
        $ancId = 1;
        $childId = 1;
        $rxId = 1;
        $rxItemId = 1;
        $compoundId = 1;
        $compoundItemId = 1;
        $dispId = 1;
        $dispItemId = 1;
        $labReqId = 1;
        $labReqItemId = 1;
        $labResId = 1;
        $invId = 1;
        $billingItemId = 1;
        $paymentId = 1;
        $refundId = 1;
        $feeId = 1;
        $docId = 1;
        $patientDocId = 1;
        $auditId = 1;
        $fhirId = 1;

        // In-memory stock tracker for FEFO
        $batchStockTracker = [];
        for ($b = 1; $b <= 150; $b++) {
            $batchStockTracker[$b] = 50000;
        }

        $startDate = Carbon::create(2020, 1, 2, 8, 0, 0);
        $totalDays = 2510; // 2020-01-02 to 2026-11-15

        $bar = $this->output->createProgressBar($targetVisits);
        $bar->start();

        for ($i = 1; $i <= $targetVisits; $i++) {
            $patientId = (($i * 31) % $totalPatients) + 1;

            // Polyclinic allocation: Poli Umum (60%), Poli Gigi (20%), Poli KIA (20%)
            $polyMod = $i % 10;
            if ($polyMod < 6) {
                $polyclinicId = 1; // Poli Umum
                $doctorId = 3; // dr. Ahmad Fauzi
            } elseif ($polyMod < 8) {
                $polyclinicId = 2; // Poli Gigi
                $doctorId = 4; // drg. Maya Anggraini
            } else {
                $polyclinicId = 3; // Poli KIA
                $doctorId = 3; // Dokter Umum / Bidan
            }
            $nurseId = 2; // Siti Rahayu

            // Realistic chronological date distribution (2020 to 2026)
            $dayOffset = (int) (($i / $targetVisits) * $totalDays);
            $regDate = (clone $startDate)->addDays($dayOffset);

            // Skip Sundays
            if ($regDate->dayOfWeek === Carbon::SUNDAY) {
                $regDate->addDay();
            }

            $hour = 8 + (($i * 7) % 8); // 08:00 to 15:00
            $minute = ($i * 13) % 60;
            $second = ($i * 17) % 60;
            $regTime = (clone $regDate)->setTime($hour, $minute, $second);

            $visitDateStr = $regTime->toDateString();
            $visitNumber = 'VIS-'.$regTime->format('Ymd').'-'.str_pad($i, 5, '0', STR_PAD_LEFT);
            $queueNumber = 'A-'.str_pad((($i % 45) + 1), 3, '0', STR_PAD_LEFT);

            $payTypeMod = $i % 10;
            if ($payTypeMod < 6) {
                $payMethodType = 'bpjs';
                $insuranceNumber = '000'.(1234567890 + (($i * 37) % 800000000));
            } elseif ($payTypeMod < 9) {
                $payMethodType = 'umum';
                $insuranceNumber = null;
            } else {
                $payMethodType = 'asuransi_lain';
                $insuranceNumber = 'PRU-'.(1000000 + ($i % 900000));
            }

            $isCancelled = ($i % 25 === 0); // 4% cancelled
            $status = $isCancelled ? 'dibatalkan' : 'selesai';
            $cancelReason = $isCancelled ? 'Pasien membatalkan antrean karena keperluan mendesak' : null;

            // 1. Visit Row
            $this->writeCsvRow($fpVisits, [
                $i,
                $visitNumber,
                $patientId,
                $polyclinicId,
                $doctorId,
                1, // registered_by
                $regTime->toDateTimeString(), // registration_date
                'lama', // visit_type ('baru', 'lama')
                $payMethodType, // payment_type ('umum', 'bpjs', 'asuransi_lain')
                $insuranceNumber,
                $status, // enum status
                $queueNumber,
                $cancelReason, // notes
                $regTime->toDateTimeString(),
                $isCancelled ? $regTime->toDateTimeString() : (clone $regTime)->addMinutes(90)->toDateTimeString(),
            ]);

            // 2. Status Histories
            $this->writeCsvRow($fpHistories, [
                $historyId++,
                $i,
                null,
                'terdaftar',
                1,
                'Pasien mendaftar di loket admisi',
                $regTime->toDateTimeString(),
                $regTime->toDateTimeString(),
            ]);

            if ($isCancelled) {
                $this->writeCsvRow($fpHistories, [
                    $historyId++,
                    $i,
                    'terdaftar',
                    'dibatalkan',
                    1,
                    $cancelReason,
                    (clone $regTime)->addMinutes(5)->toDateTimeString(),
                    (clone $regTime)->addMinutes(5)->toDateTimeString(),
                ]);

                if ($i % 5000 === 0) {
                    $bar->advance(5000);
                }

                continue;
            }

            // Timeline progression
            $assessTime = (clone $regTime)->addMinutes(10 + ($i % 10));
            $examTime = (clone $assessTime)->addMinutes(15 + ($i % 15));
            $dispTime = (clone $examTime)->addMinutes(20 + ($i % 10));
            $payTime = (clone $dispTime)->addMinutes(10 + ($i % 10));

            $this->writeCsvRow($fpHistories, [$historyId++, $i, 'terdaftar', 'menunggu_pemeriksaan_awal', 1, 'Menunggu antrean triage perawat', $regTime->toDateTimeString(), $regTime->toDateTimeString()]);
            $this->writeCsvRow($fpHistories, [$historyId++, $i, 'menunggu_pemeriksaan_awal', 'pemeriksaan_awal_berlangsung', $nurseId, 'Pemeriksaan vital sign dan anamnesis', $assessTime->toDateTimeString(), $assessTime->toDateTimeString()]);
            $this->writeCsvRow($fpHistories, [$historyId++, $i, 'pemeriksaan_awal_berlangsung', 'pemeriksaan_dokter_berlangsung', $doctorId, 'Pemeriksaan dokter dan penegakan diagnosis', $examTime->toDateTimeString(), $examTime->toDateTimeString()]);
            $this->writeCsvRow($fpHistories, [$historyId++, $i, 'pemeriksaan_dokter_berlangsung', 'menunggu_farmasi', 5, 'Resep disiapkan di instalasi farmasi', $dispTime->toDateTimeString(), $dispTime->toDateTimeString()]);
            $this->writeCsvRow($fpHistories, [$historyId++, $i, 'menunggu_farmasi', 'selesai', 1, 'Pembayaran kasir dan penyerahan obat selesai', $payTime->toDateTimeString(), $payTime->toDateTimeString()]);

            // Determine Clinical Scenario
            $scenario = $this->determineScenario($polyclinicId, $i);

            // 3. Nursing Assessment
            $weight = 50 + ($i % 30);
            $height = 155 + ($i % 25);
            $bmi = round($weight / (($height / 100) ** 2), 1);

            $this->writeCsvRow($fpAssessments, [
                $assessmentId,
                $i,
                $patientId,
                $nurseId,
                $assessTime->toDateTimeString(),
                $scenario['chief_complaint'],
                $scenario['present_illness'],
                'Tidak ada riwayat penyakit berat sebelumnya',
                'Tidak ada riwayat penyakit keluarga',
                'Tidak sedang mengonsumsi obat rutin',
                $scenario['systolic'],
                $scenario['diastolic'],
                $scenario['temperature'],
                72 + ($i % 16),
                18 + ($i % 4),
                98 + ($i % 3),
                $weight,
                $height,
                $bmi,
                75.0,
                'compos_mentis',
                $scenario['pain_scale'],
                'rendah',
                'Edukasi pasien dan observasi tanda vital berkala',
                'Pasien kooperatif selama pemeriksaan awal',
                $assessTime->toDateTimeString(),
                $assessTime->toDateTimeString(),
            ]);

            // 4. Vital Signs
            $this->writeCsvRow($fpVitals, [
                $vitalsId++,
                $patientId,
                $i,
                1,
                $assessTime->toDateTimeString(),
                $scenario['systolic'],
                $scenario['diastolic'],
                $scenario['temperature'],
                72 + ($i % 16),
                18 + ($i % 4),
                98 + ($i % 3),
                $weight,
                $height,
                $bmi,
                $assessTime->toDateTimeString(),
                $assessTime->toDateTimeString(),
            ]);

            // 5. Medical Record
            $this->writeCsvRow($fpMR, [
                $mrId,
                $i,
                $patientId,
                $doctorId,
                $examTime->toDateTimeString(),
                $scenario['subjective'],
                $scenario['objective'],
                $scenario['assessment'],
                $scenario['plan'],
                'final',
                $examTime->toDateTimeString(),
                1,
                $scenario['education'],
                'Kontrol kembali bila keluhan belum membaik dalam 3 hari',
                (clone $regTime)->addDays(7)->toDateString(),
                $examTime->toDateTimeString(),
                $examTime->toDateTimeString(),
            ]);

            // 6. Diagnoses (Primary + Secondary)
            $this->writeCsvRow($fpDiagnoses, [
                $diagnosisId++,
                $mrId,
                $scenario['icd10_primary'],
                $scenario['assessment'],
                'primary',
                'Diagnosis utama berdasarkan anamnesis dan pemeriksaan fisik',
                $examTime->toDateTimeString(),
                $examTime->toDateTimeString(),
            ]);

            if (! empty($scenario['icd10_secondary'])) {
                $this->writeCsvRow($fpDiagnoses, [
                    $diagnosisId++,
                    $mrId,
                    $scenario['icd10_secondary'],
                    'Gejala klinis penyerta',
                    'secondary',
                    'Diagnosis sekunder / penyerta',
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);
            }

            // 7. Procedure
            $this->writeCsvRow($fpProcedures, [
                $procedureId++,
                $mrId,
                $scenario['icd9_code'],
                $scenario['icd9_desc'],
                $scenario['procedure_fee'],
                'Tindakan klinis poliklinik',
                $examTime->toDateTimeString(),
                $examTime->toDateTimeString(),
            ]);

            // Specialty Modules
            if ($polyclinicId === 2) {
                // Odontogram
                $this->writeCsvRow($fpOdontograms, [
                    $odontogramId,
                    $i,
                    $patientId,
                    $doctorId,
                    'Pemeriksaan gigi menyeluruh, ditemukan kelainan pada elemen gigi target',
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);

                $teethNumbers = ['16', '26', '36', '46', '11', '21', '31', '41'];
                $toothIndex = $i % count($teethNumbers);
                $toothPrimary = $teethNumbers[$toothIndex];
                $toothSecondary = $teethNumbers[($toothIndex + 1) % count($teethNumbers)];

                $this->writeCsvRow($fpOdontogramTeeth, [$odontogramToothId++, $odontogramId, $toothPrimary, $scenario['tooth_condition'], 'MOD', 'Kavitas karies dentin', $examTime->toDateTimeString(), $examTime->toDateTimeString()]);
                $this->writeCsvRow($fpOdontogramTeeth, [$odontogramToothId++, $odontogramId, $toothSecondary, 'sound', 'none', 'Mahkota utuh sehat', $examTime->toDateTimeString(), $examTime->toDateTimeString()]);
                $odontogramId++;
            } elseif ($polyclinicId === 3) {
                if ($scenario['is_anc']) {
                    $this->writeCsvRow($fpAntenatal, [
                        $ancId++,
                        $i,
                        $patientId,
                        $doctorId,
                        2, // gravida
                        1, // para
                        0, // abortus
                        (clone $regTime)->subWeeks($scenario['gestational_weeks'])->toDateString(),
                        (clone $regTime)->addWeeks(40 - $scenario['gestational_weeks'])->toDateString(),
                        $scenario['gestational_weeks'],
                        $scenario['fundal_height'],
                        140 + ($i % 10),
                        'kepala',
                        110 + ($i % 15),
                        70 + ($i % 10),
                        58 + ($i % 10),
                        $scenario['chief_complaint'],
                        'Pemberian Tablet Tambah Darah & Kalsium',
                        $examTime->toDateTimeString(),
                        $examTime->toDateTimeString(),
                    ]);
                } else {
                    $this->writeCsvRow($fpChildGrowth, [
                        $childId++,
                        $i,
                        $patientId,
                        $doctorId,
                        $scenario['age_months'],
                        $scenario['weight_kg'],
                        $scenario['height_cm'],
                        38 + ($i % 10),
                        $scenario['immunization'],
                        'Perkembangan motorik kasar dan halus sesuai usia KPSP',
                        'Gizi baik dan nafsu makan normal',
                        $examTime->toDateTimeString(),
                        $examTime->toDateTimeString(),
                    ]);
                }
            }

            // 8. Prescription
            $rxNumber = 'RX-'.$regTime->format('Ymd').'-'.str_pad($rxId, 5, '0', STR_PAD_LEFT);
            $this->writeCsvRow($fpPrescriptions, [
                $rxId,
                $rxNumber,
                $i,
                $mrId,
                $doctorId,
                $examTime->toDateTimeString(),
                'selesai',
                'Minum obat secara teratur sesuai petunjuk dokter/apoteker',
                'Telaah resep selesai dan obat diserahkan lengkap',
                1,
                (clone $examTime)->addMinutes(5)->toDateTimeString(),
                $dispTime->toDateTimeString(),
                $examTime->toDateTimeString(),
                $dispTime->toDateTimeString(),
            ]);

            // 9. Dispensing Transaction
            $dispNumber = 'DSP-'.$regTime->format('Ymd').'-'.str_pad($dispId, 5, '0', STR_PAD_LEFT);

            // Prescription Items & FEFO Dispensing Items
            $rxItemsTotal = 0;
            foreach ($scenario['medicines'] as $medIndex => $medInfo) {
                $medId = $medInfo['id'];
                $qty = $medInfo['qty'];
                $unitPrice = $medInfo['price'];
                $subtotal = $qty * $unitPrice;
                $rxItemsTotal += $subtotal;
                $currentRxItemId = $rxItemId++;

                $this->writeCsvRow($fpRxItems, [
                    $currentRxItemId,
                    $rxId,
                    $medId,
                    $qty,
                    $medInfo['dosage'],
                    $medInfo['frequency'],
                    'oral',
                    3,
                    $medInfo['instructions'],
                    'Habiskan sesuai petunjuk',
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);

                // FEFO Batch Allocation (batch 1, 2, or 3 for that medicine)
                $batchOffset = ($medId - 1) * 3;
                $allocatedBatchId = $batchOffset + 1; // 2024 batch
                if ($regTime->year >= 2025) {
                    $allocatedBatchId = $batchOffset + 2; // 2026 batch
                }

                $this->writeCsvRow($fpDispensingItems, [
                    $dispItemId++,
                    $dispId,
                    $currentRxItemId,
                    $medId,
                    $allocatedBatchId,
                    $qty,
                    $unitPrice,
                    $subtotal,
                    $dispTime->toDateTimeString(),
                    $dispTime->toDateTimeString(),
                ]);

                // Stock Movement (negative quantity for reduction)
                $stockBefore = $batchStockTracker[$allocatedBatchId];
                $stockAfter = max(0, $stockBefore - $qty);
                $batchStockTracker[$allocatedBatchId] = $stockAfter;

                $this->writeCsvRow($fpMovements, [
                    $movementId++,
                    $medId,
                    $allocatedBatchId,
                    'dispense',
                    -$qty,
                    $stockBefore,
                    $stockAfter,
                    'dispensing_transaction',
                    $dispId,
                    "Penyerahan Resep {$rxNumber}",
                    1,
                    $dispTime->toDateTimeString(),
                    $dispTime->toDateTimeString(),
                ]);
            }

            // Compounds for ~8% visits
            $compoundTotal = 0;
            if ($i % 12 === 0) {
                $compPrice = 18000;
                $compoundTotal = $compPrice;
                $this->writeCsvRow($fpCompounds, [
                    $compoundId,
                    $rxId,
                    'Puyer Batuk Pilek Terpadu',
                    'Puyer',
                    10,
                    '3x1 bungkus',
                    'Sesudah makan',
                    'Bila batuk dan pilek',
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);

                $this->writeCsvRow($fpCompoundItems, [$compoundItemId++, $compoundId, 1, 5.0, 'Tablet', $examTime->toDateTimeString(), $examTime->toDateTimeString()]);
                $this->writeCsvRow($fpCompoundItems, [$compoundItemId++, $compoundId, 8, 5.0, 'Tablet', $examTime->toDateTimeString(), $examTime->toDateTimeString()]);
                $compoundId++;
            }

            $this->writeCsvRow($fpDispensing, [
                $dispId,
                $dispNumber,
                $rxId,
                $i,
                1,
                $dispTime->toDateTimeString(),
                $rxItemsTotal + $compoundTotal,
                'Pasien telah diedukasi aturan minum obat dan kemungkinan efek samping ringan',
                $dispTime->toDateTimeString(),
                $dispTime->toDateTimeString(),
            ]);

            // Laboratory Request (~19% visits)
            $labTotal = 0;
            if ($scenario['needs_lab']) {
                $labReqNumber = 'LAB-'.$regTime->format('Ymd').'-'.str_pad($labReqId, 5, '0', STR_PAD_LEFT);
                $labPrice = $scenario['lab_price'];
                $labTotal = $labPrice;

                $this->writeCsvRow($fpLabRequests, [
                    $labReqId,
                    $labReqNumber,
                    $i,
                    $doctorId,
                    $examTime->toDateTimeString(),
                    $scenario['lab_clinical_notes'],
                    (clone $examTime)->addMinutes(10)->toDateTimeString(),
                    'Analis Lab',
                    'selesai',
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);

                $this->writeCsvRow($fpLabItems, [
                    $labReqItemId,
                    $labReqId,
                    $scenario['laboratory_id'],
                    $labPrice,
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);

                $this->writeCsvRow($fpLabResults, [
                    $labResId++,
                    $labReqId,
                    $scenario['laboratory_id'],
                    $scenario['lab_result_val'],
                    $scenario['lab_abnormal'] ? 1 : 0,
                    'Pemeriksaan sesuai SOP lab',
                    1,
                    1,
                    (clone $examTime)->addMinutes(25)->toDateTimeString(),
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);

                $labReqItemId++;
                $labReqId++;
            }

            // 10. Billing Invoice & Items
            $invNumber = 'INV-'.$regTime->format('Ymd').'-'.str_pad($invId, 5, '0', STR_PAD_LEFT);
            $admFee = 15000;
            $consultFee = $scenario['consult_fee'];
            $procFee = $scenario['procedure_fee'];
            $totalAmount = $admFee + $consultFee + $procFee + $rxItemsTotal + $compoundTotal + $labTotal;
            $discount = 0;
            $finalAmount = $totalAmount - $discount;

            $this->writeCsvRow($fpInvoices, [
                $invId,
                $invNumber,
                $i,
                $patientId,
                $totalAmount,
                $discount,
                null, // discount_reason
                $finalAmount,
                $finalAmount, // paid_amount
                0, // balance_amount
                'lunas', // status
                1,
                $payTime->toDateTimeString(),
                $payTime->toDateTimeString(),
            ]);

            // Billing Items
            $this->writeCsvRow($fpBillingItems, [$billingItemId++, $invId, 'administrasi', 'TRF-ADM', 'Biaya Administrasi & Registrasi Pasien', 1, $admFee, $admFee, null, null, $payTime->toDateTimeString(), $payTime->toDateTimeString()]);
            $this->writeCsvRow($fpBillingItems, [$billingItemId++, $invId, 'konsultasi', 'TRF-KONS', $scenario['consult_name'], 1, $consultFee, $consultFee, null, null, $payTime->toDateTimeString(), $payTime->toDateTimeString()]);
            if ($procFee > 0) {
                $this->writeCsvRow($fpBillingItems, [$billingItemId++, $invId, 'tindakan_medis', 'TRF-TND', $scenario['procedure_name'], 1, $procFee, $procFee, null, null, $payTime->toDateTimeString(), $payTime->toDateTimeString()]);
            }
            if ($rxItemsTotal > 0) {
                $this->writeCsvRow($fpBillingItems, [$billingItemId++, $invId, 'obat', 'TRF-MED', 'Paket Obat Resep Rawat Jalan', 1, $rxItemsTotal, $rxItemsTotal, 'prescriptions', $rxId, $payTime->toDateTimeString(), $payTime->toDateTimeString()]);
            }
            if ($compoundTotal > 0) {
                $this->writeCsvRow($fpBillingItems, [$billingItemId++, $invId, 'obat', 'TRF-RACIK', 'Obat Racikan / Puyer Resep', 1, $compoundTotal, $compoundTotal, 'prescription_compounds', $compoundId - 1, $payTime->toDateTimeString(), $payTime->toDateTimeString()]);
            }
            if ($labTotal > 0) {
                $this->writeCsvRow($fpBillingItems, [$billingItemId++, $invId, 'laboratorium', 'TRF-LAB', $scenario['lab_name'], 1, $labTotal, $labTotal, 'laboratories', $scenario['laboratory_id'], $payTime->toDateTimeString(), $payTime->toDateTimeString()]);
            }

            // 11. Payment Receipt
            $rcptNumber = 'KW-'.$regTime->format('Ymd').'-'.str_pad($invId, 5, '0', STR_PAD_LEFT);
            $payMethodId = 1 + ($i % 4);

            $this->writeCsvRow($fpPayments, [
                $paymentId,
                $rcptNumber,
                $invId,
                $payMethodId,
                $finalAmount,
                $finalAmount,
                0,
                $payTime->toDateTimeString(),
                1,
                'Pembayaran lunas kasir',
                $payTime->toDateTimeString(),
                $payTime->toDateTimeString(),
            ]);

            // Rare realistic refunds (~2% for testing refund module)
            if ($i % 50 === 3) {
                $this->writeCsvRow($fpRefunds, [
                    $refundId,
                    $invId,
                    $paymentId,
                    'REF-'.$regTime->format('Ymd').'-'.str_pad($refundId, 4, '0', STR_PAD_LEFT),
                    $finalAmount,
                    'Pengembalian dana klaim revisi jaminan asuransi',
                    1, // approved_by
                    (clone $payTime)->addHours(2)->toDateTimeString(),
                    (clone $payTime)->addHours(2)->toDateTimeString(),
                    (clone $payTime)->addHours(2)->toDateTimeString(),
                ]);
                $refundId++;
            }
            $paymentId++;

            // 12. Medical Fee Transactions (Jasa Medis)
            $this->writeCsvRow($fpFees, [
                $feeId++,
                null,
                $i,
                $doctorId,
                $nurseId,
                $scenario['doc_fee'],
                $scenario['nurse_fee'],
                $scenario['clinic_fee'],
                'paid',
                $payTime->toDateTimeString(),
                $payTime->toDateTimeString(),
            ]);

            // 13. Medical Documents (~14% visits)
            if ($i % 7 === 0) {
                $docTypes = ['surat_keterangan_sakit', 'surat_keterangan_sehat', 'surat_rujukan'];
                $docType = $docTypes[$i % count($docTypes)];
                $docNo = 'DOC-'.$regTime->format('Ymd').'-'.str_pad($docId, 5, '0', STR_PAD_LEFT);

                $contentJson = json_encode([
                    'diagnosis' => $scenario['assessment'],
                    'recommendation' => 'Istirahat selama 3 hari terhitung mulai tanggal pemeriksaan',
                    'days_rest' => 3,
                ]);

                $this->writeCsvRow($fpDocs, [
                    $docId++,
                    $docNo,
                    $i,
                    $patientId,
                    $doctorId,
                    $docType,
                    $visitDateStr,
                    $contentJson,
                    'Dokumen diterbitkan atas persetujuan dokter pemeriksa',
                    $examTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                ]);
            }

            // 14. Patient Documents (~4% visits)
            if ($i % 25 === 7) {
                $this->writeCsvRow($fpPatientDocs, [
                    $patientDocId++,
                    $patientId,
                    $i,
                    'Berkas Identitas & Rujukan KTP / BPJS',
                    'identitas',
                    "documents/patients/{$patientId}/ktp_bpjs.pdf",
                    'application/pdf',
                    245760,
                    1,
                    $regTime->toDateTimeString(),
                    $regTime->toDateTimeString(),
                ]);
            }

            // 15. Audit Trail Log (~25% visits)
            if ($i % 4 === 0) {
                $this->writeCsvRow($fpAudit, [
                    $auditId++,
                    1,
                    'FINALIZED_EMR',
                    'MedicalRecord',
                    "Petugas memfinalisasi rekam medis elektronik kunjungan {$visitNumber}",
                    '127.0.0.1',
                    'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    null,
                    null,
                    $examTime->toDateTimeString(),
                ]);
            }

            // 16. FHIR SatuSehat Mapping (~25% visits)
            if ($i % 4 === 0) {
                $this->writeCsvRow($fpFhir, [
                    $fhirId++,
                    'Encounter',
                    'visits',
                    $i,
                    'enc-'.$visitNumber,
                    'synced',
                    $payTime->toDateTimeString(),
                    $examTime->toDateTimeString(),
                    $payTime->toDateTimeString(),
                ]);
            }

            $assessmentId++;
            $mrId++;
            $rxId++;
            $dispId++;
            $invId++;

            if ($i % 5000 === 0) {
                $bar->advance(5000);
            }
        }

        $bar->finish();

        // Close all file pointers
        fclose($fpVisits);
        fclose($fpHistories);
        fclose($fpAssessments);
        fclose($fpVitals);
        fclose($fpMR);
        fclose($fpDiagnoses);
        fclose($fpProcedures);
        fclose($fpOdontograms);
        fclose($fpOdontogramTeeth);
        fclose($fpAntenatal);
        fclose($fpChildGrowth);
        fclose($fpPrescriptions);
        fclose($fpRxItems);
        fclose($fpCompounds);
        fclose($fpCompoundItems);
        fclose($fpDispensing);
        fclose($fpDispensingItems);
        fclose($fpMovements);
        fclose($fpLabRequests);
        fclose($fpLabItems);
        fclose($fpLabResults);
        fclose($fpInvoices);
        fclose($fpBillingItems);
        fclose($fpPayments);
        fclose($fpRefunds);
        fclose($fpFees);
        fclose($fpDocs);
        fclose($fpPatientDocs);
        fclose($fpAudit);
        fclose($fpFhir);

        $this->line('');
        $this->info("✓ Seluruh file dataset operasional ({$targetVisits} kunjungan) berhasil dibuat.");
    }

    /**
     * Map diverse clinical scenarios across polyclinics.
     */
    protected function determineScenario(int $polyclinicId, int $seed): array
    {
        if ($polyclinicId === 2) {
            // Dental Scenarios
            $dSeed = $seed % 4;
            if ($dSeed === 0) {
                return [
                    'chief_complaint' => 'Gigi geraham belakang berlubang dan ngilu saat minum dingin',
                    'present_illness' => 'Nyeri hilang timbul sejak 4 hari, memberat saat kemasukan sisa makanan',
                    'pain_scale' => 3, 'pain_location' => 'Rahang kanan bawah',
                    'systolic' => 120, 'diastolic' => 80, 'temperature' => 36.6,
                    'subjective' => 'Pasien mengeluhkan gigi geraham kanan bawah berlubang, sering terselip makanan dan terasa ngilu.',
                    'objective' => 'Inspeksi: Tampak kavitas pada oklusal gigi 46, kedalaman mencapai dentin. Perkusi (-), Palpasi (-), CE (+).',
                    'assessment' => 'Karies Dentin (K02.1)',
                    'plan' => 'Preparasi kavitas, aplikasi etsa, bonding, dan tumpatan resin komposit.',
                    'education' => 'Edukasi cara menyikat gigi yang benar 2x sehari dan kontrol berkala 6 bulan sekali.',
                    'icd10_primary' => 'K02.1', 'icd10_secondary' => null,
                    'icd9_code' => '23.2', 'icd9_desc' => 'Restoration of tooth by filling / composite',
                    'tooth_condition' => 'caries', 'restoration' => 'composite',
                    'is_anc' => false,
                    'medicines' => [
                        ['id' => 3, 'qty' => 10, 'price' => 700, 'dosage' => '500 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Bila nyeri'],
                        ['id' => 2, 'qty' => 10, 'price' => 1200, 'dosage' => '500 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Habiskan'],
                    ],
                    'needs_lab' => false,
                    'consult_name' => 'Konsultasi & Pemeriksaan Dokter Gigi', 'consult_fee' => 50000,
                    'procedure_name' => 'Penambalan Gigi Komposit', 'procedure_fee' => 120000,
                    'doc_fee' => 120000, 'nurse_fee' => 15000, 'clinic_fee' => 35000,
                ];
            } elseif ($dSeed === 1) {
                return [
                    'chief_complaint' => 'Gusi sering berdarah saat menyikat gigi dan terasa kotor berkarang',
                    'present_illness' => 'Keluhan dirasakan sejak 3 minggu terakhir, napas terasa kurang segar',
                    'pain_scale' => 1, 'pain_location' => 'Gingiva rahang bawah',
                    'systolic' => 118, 'diastolic' => 78, 'temperature' => 36.5,
                    'subjective' => 'Pasien merasa gusi mudah berdarah saat sikat gigi dan karang gigi menebal.',
                    'objective' => 'Kalkulus supragingival dan subgingival tebal pada regio anterior rahang bawah. Gingiva hiperemis, BOP (+).',
                    'assessment' => 'Gingivitis Kronis (K05.1)',
                    'plan' => 'Pembersihan karang gigi supragingiva & subgingiva (Ultrasonic Scaling) serta polishing.',
                    'education' => 'Instruksi penggunaan benang gigi (dental floss) dan obat kumur antiseptik.',
                    'icd10_primary' => 'K05.1', 'icd10_secondary' => null,
                    'icd9_code' => '96.54', 'icd9_desc' => 'Dental scaling, prophylaxis, and polishing',
                    'tooth_condition' => 'calculus', 'restoration' => 'none',
                    'is_anc' => false,
                    'medicines' => [
                        ['id' => 49, 'qty' => 1, 'price' => 26000, 'dosage' => '150 ml', 'frequency' => '2x1 kumur', 'instructions' => 'Kumur 1 menit sesudah sikat gigi'],
                        ['id' => 7, 'qty' => 10, 'price' => 1000, 'dosage' => '500 mg', 'frequency' => '1x1 sesudah makan', 'instructions' => 'Pagi hari'],
                    ],
                    'needs_lab' => false,
                    'consult_name' => 'Konsultasi & Pemeriksaan Dokter Gigi', 'consult_fee' => 50000,
                    'procedure_name' => 'Pembersihan Karang Gigi (Scaling)', 'procedure_fee' => 150000,
                    'doc_fee' => 135000, 'nurse_fee' => 20000, 'clinic_fee' => 45000,
                ];
            } else {
                return [
                    'chief_complaint' => 'Nyeri berdenyut hebat pada gigi geraham kiri bawah terutama saat malam',
                    'present_illness' => 'Gigi berlubang dalam sejak 2 bulan, nyeri menjalar hingga ke pelipis',
                    'pain_scale' => 4, 'pain_location' => 'Gigi 36 rahang kiri bawah',
                    'systolic' => 125, 'diastolic' => 82, 'temperature' => 36.8,
                    'subjective' => 'Pasien mengeluhkan sakit gigi cekot-cekot malam hari hingga tidak bisa tidur.',
                    'objective' => 'Gigi 36 karies profunda perforasi pulpa. Sondasi (+), Perkusi (+), Dingin (+) bertambah nyeri menetap.',
                    'assessment' => 'Pulpitis Irreversibel (K04.0)',
                    'plan' => 'Open bur, ekstirpasi pulpa, medikasi intrakanal, tumpatan sementara.',
                    'education' => 'Kontrol kembali 1 minggu untuk perawatan saluran akar lanjutan.',
                    'icd10_primary' => 'K04.0', 'icd10_secondary' => null,
                    'icd9_code' => '23.09', 'icd9_desc' => 'Pulp extirpation and open drainage',
                    'tooth_condition' => 'caries', 'restoration' => 'none',
                    'is_anc' => false,
                    'medicines' => [
                        ['id' => 3, 'qty' => 10, 'price' => 700, 'dosage' => '500 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Bila nyeri'],
                        ['id' => 2, 'qty' => 10, 'price' => 1200, 'dosage' => '500 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Habiskan'],
                        ['id' => 34, 'qty' => 10, 'price' => 450, 'dosage' => '0.5 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Antiinflamasi'],
                    ],
                    'needs_lab' => false,
                    'consult_name' => 'Konsultasi & Pemeriksaan Dokter Gigi', 'consult_fee' => 50000,
                    'procedure_name' => 'Perawatan Saluran Akar / Ekstirpasi Pulpa', 'procedure_fee' => 100000,
                    'doc_fee' => 110000, 'nurse_fee' => 15000, 'clinic_fee' => 25000,
                ];
            }
        } elseif ($polyclinicId === 3) {
            // KIA Scenarios: 50% Ibu Hamil (ANC), 50% Balita / Tumbuh Kembang & Imunisasi
            if ($seed % 2 === 0) {
                // ANC
                $weeks = 12 + ($seed % 26);
                $fundal = max(12, $weeks - 2);

                return [
                    'chief_complaint' => 'Kontrol rutin kehamilan trimester 2, keluhan pusing ringan dan mual kadang-kadang',
                    'present_illness' => 'Hamil kedua, gerak janin aktif dirasakan, tidak ada perdarahan atau nyeri perut hebat',
                    'pain_scale' => 0, 'pain_location' => '-',
                    'systolic' => 115, 'diastolic' => 75, 'temperature' => 36.6,
                    'subjective' => "Ibu hamil G2P1A0 usia kehamilan {$weeks} minggu datang untuk kontrol rutin ANC.",
                    'objective' => "TFU {$fundal} cm, DJJ 142x/menit reguler kuat. Letak kepala, puka, edema ekstremitas (-).",
                    'assessment' => 'Pengawasan Kehamilan Normal (Z34)',
                    'plan' => 'Pemberian suplemen zat besi, kalsium, dan edukasi nutrisi ibu hamil.',
                    'education' => 'Tanda bahaya kehamilan trimester 2 & 3, nutrisi gizi seimbang, dan istirahat cukup.',
                    'icd10_primary' => 'Z34', 'icd10_secondary' => null,
                    'icd9_code' => '88.78', 'icd9_desc' => 'Diagnostic ultrasound of gravid uterus / ANC examination',
                    'is_anc' => true,
                    'gestational_weeks' => $weeks,
                    'fundal_height' => $fundal,
                    'medicines' => [
                        ['id' => 40, 'qty' => 30, 'price' => 600, 'dosage' => '1 Tab', 'frequency' => '1x1 malam', 'instructions' => 'Sebelum tidur'],
                        ['id' => 39, 'qty' => 30, 'price' => 600, 'dosage' => '500 mg', 'frequency' => '1x1 pagi', 'instructions' => 'Sesudah makan'],
                    ],
                    'needs_lab' => false,
                    'consult_name' => 'Konsultasi & Pemeriksaan KIA / Bidan', 'consult_fee' => 35000,
                    'procedure_name' => 'Pemeriksaan Kehamilan (ANC Terpadu)', 'procedure_fee' => 50000,
                    'doc_fee' => 55000, 'nurse_fee' => 10000, 'clinic_fee' => 20000,
                ];
            } else {
                // Balita / Imunisasi
                $months = 2 + ($seed % 36);
                $weight = round(5.0 + ($months * 0.35), 1);
                $height = round(58.0 + ($months * 1.1), 1);
                $imunisasiList = ['DPT-HB-Hib 1', 'Polio 2', 'PCV 1', 'DPT-HB-Hib 2', 'Campak-Rubella (MR)', 'DPT-HB-Hib Lanjutan'];
                $imunisasi = $imunisasiList[$seed % count($imunisasiList)];

                return [
                    'chief_complaint' => "Kunjungan imunisasi rutin balita usia {$months} bulan dan pemantauan tumbuh kembang",
                    'present_illness' => 'Anak aktif, menyusu dan makan lahap, tidak sedang demam atau batuk',
                    'pain_scale' => 0, 'pain_location' => '-',
                    'systolic' => 95, 'diastolic' => 60, 'temperature' => 36.7,
                    'subjective' => "Ibu membawa anak usia {$months} bulan untuk mendapatkan imunisasi rutin {$imunisasi}.",
                    'objective' => "BB {$weight} kg, TB {$height} cm. Status gizi baik, perkembangan motorik sesuai kurva KMS.",
                    'assessment' => 'Pemeriksaan Kesehatan Anak Rutin (Z00.1)',
                    'plan' => "Pemberian imunisasi {$imunisasi} secara intramuskular pada paha anterolateral.",
                    'education' => 'Observasi KIPI pasca imunisasi, kompres hangat bila bengkak, paracetamol bila demam.',
                    'icd10_primary' => 'Z00.1', 'icd10_secondary' => null,
                    'icd9_code' => '99.39', 'icd9_desc' => 'Administration of vaccine / immunization',
                    'is_anc' => false,
                    'age_months' => $months,
                    'weight_kg' => $weight,
                    'height_cm' => $height,
                    'immunization' => $imunisasi,
                    'medicines' => [
                        ['id' => 42, 'qty' => 1, 'price' => 11000, 'dosage' => '120 mg/5ml', 'frequency' => '3x1/2 cth', 'instructions' => 'Bila demam pasca imunisasi'],
                    ],
                    'needs_lab' => false,
                    'consult_name' => 'Konsultasi & Pemeriksaan KIA / Bidan', 'consult_fee' => 35000,
                    'procedure_name' => 'Tindakan Imunisasi Balita', 'procedure_fee' => 40000,
                    'doc_fee' => 50000, 'nurse_fee' => 10000, 'clinic_fee' => 15000,
                ];
            }
        }

        // Poli Umum Scenarios (5 Variasi Medis Utama)
        $uSeed = $seed % 5;
        if ($uSeed === 0) {
            // ISPA / Common Cold
            return [
                'chief_complaint' => 'Batuk berdahak, pilek hidung tersumbat, dan badan meriang sejak 3 hari',
                'present_illness' => 'Dahak warna putih kehijauan, tenggorokan terasa serak dan nyeri saat menelan',
                'pain_scale' => 1, 'pain_location' => 'Tenggorokan',
                'systolic' => 118, 'diastolic' => 78, 'temperature' => 37.5,
                'subjective' => 'Pasien mengeluh batuk, hidung tersumbat, bersin-bersin, tenggorokan nyeri, dan demam sumer sejak 3 hari.',
                'objective' => 'Faring hiperemis (+), tonsil T1/T1 tenang. Konka hidung hipertrofi hiperemis (+). Cor/Pulmo dalam batas normal.',
                'assessment' => 'Infeksi Saluran Pernapasan Akut / Common Cold (J06.9)',
                'plan' => 'Simptomatis: antipiretik-analgesik, ekspektoran, antihistamin-dekongestan, multivitamin.',
                'education' => 'Istirahat cukup, perbanyak minum air hangat minimal 2 liter/hari, gunakan masker bila keluar rumah.',
                'icd10_primary' => 'J06.9', 'icd10_secondary' => 'J00',
                'icd9_code' => '89.03', 'icd9_desc' => 'Interview and evaluation, comprehensive',
                'is_anc' => false,
                'medicines' => [
                    ['id' => 1, 'qty' => 10, 'price' => 600, 'dosage' => '500 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Bila demam / pusing'],
                    ['id' => 8, 'qty' => 10, 'price' => 700, 'dosage' => '30 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Pereda batuk berdahak'],
                    ['id' => 5, 'qty' => 10, 'price' => 500, 'dosage' => '10 mg', 'frequency' => '1x1 malam', 'instructions' => 'Meredakan pilek & alergi'],
                    ['id' => 7, 'qty' => 10, 'price' => 1000, 'dosage' => '500 mg', 'frequency' => '1x1 pagi', 'instructions' => 'Meningkatkan daya tahan tubuh'],
                ],
                'needs_lab' => false,
                'consult_name' => 'Konsultasi & Pemeriksaan Dokter Umum', 'consult_fee' => 40000,
                'procedure_name' => '', 'procedure_fee' => 0,
                'doc_fee' => 25000, 'nurse_fee' => 5000, 'clinic_fee' => 10000,
            ];
        } elseif ($uSeed === 1) {
            // Hipertensi Primer
            return [
                'chief_complaint' => 'Kepala pusing berdenyut dan tengkuk terasa kaku tegang sejak kemarin',
                'present_illness' => 'Pasien memiliki riwayat darah tinggi namun sering lupa minum obat',
                'pain_scale' => 2, 'pain_location' => 'Tengkuk dan kepala belakang',
                'systolic' => 150, 'diastolic' => 95, 'temperature' => 36.7,
                'subjective' => 'Pasien mengeluh kepala cekot-cekot, leher kaku, dan sulit tidur nyenyak.',
                'objective' => 'TD 150/95 mmHg, Nadi 84x/m. Cor S1 S2 reguler tunggal, murmur (-). Pulmo vesikuler (+/+).',
                'assessment' => 'Hipertensi Primer / Esensial Grade 1 (I10)',
                'plan' => 'Terapi antihipertensi lini pertama CCB (Amlodipine 5 mg), edukasi pembatasan konsumsi garam dapur.',
                'education' => 'Diet rendah garam (< 1 sdt/hari), olahraga aerobik ringan 30 menit 3x/minggu, kontrol tekanan darah rutin.',
                'icd10_primary' => 'I10', 'icd10_secondary' => null,
                'icd9_code' => '89.07', 'icd9_desc' => 'General physical examination',
                'is_anc' => false,
                'medicines' => [
                    ['id' => 4, 'qty' => 30, 'price' => 1000, 'dosage' => '5 mg', 'frequency' => '1x1 malam', 'instructions' => 'Diminum rutin tiap jam 20.00'],
                    ['id' => 1, 'qty' => 10, 'price' => 600, 'dosage' => '500 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Bila pusing berat'],
                ],
                'needs_lab' => false,
                'consult_name' => 'Konsultasi & Pemeriksaan Dokter Umum', 'consult_fee' => 40000,
                'procedure_name' => '', 'procedure_fee' => 0,
                'doc_fee' => 25000, 'nurse_fee' => 5000, 'clinic_fee' => 10000,
            ];
        } elseif ($uSeed === 2) {
            // Dispepsia / Gastritis Akut
            return [
                'chief_complaint' => 'Nyeri perih pada ulu hati, perut kembung begah, dan mual muntah',
                'present_illness' => 'Nyeri memberat setelah terlambat makan dan minum kopi pekat',
                'pain_scale' => 3, 'pain_location' => 'Epigastrium',
                'systolic' => 115, 'diastolic' => 75, 'temperature' => 36.5,
                'subjective' => 'Pasien mengeluh ulu hati perih melilit, sendawa terus menerus, mual, dan rasa asam di mulut.',
                'objective' => 'Nyeri tekan epigastrium (+), bising usus normal 10x/m, hepar/lien tidak teraba.',
                'assessment' => 'Gastritis / Dispepsia Akut (K29.7)',
                'plan' => 'Antasida suspensi DOEN, PPI (Omeprazole 20 mg), prokinetik bila mual.',
                'education' => 'Pola makan teratur porsi kecil tapi sering, hindari makanan pedas, asam, bersantan, kopi, dan stres.',
                'icd10_primary' => 'K29.7', 'icd10_secondary' => null,
                'icd9_code' => '89.03', 'icd9_desc' => 'Interview and evaluation',
                'is_anc' => false,
                'medicines' => [
                    ['id' => 9, 'qty' => 1, 'price' => 10000, 'dosage' => '60 ml', 'frequency' => '3x1 Cth', 'instructions' => '1 jam sebelum makan & sebelum tidur'],
                    ['id' => 6, 'qty' => 14, 'price' => 2000, 'dosage' => '20 mg', 'frequency' => '2x1 sebelum makan', 'instructions' => '30 menit sebelum sarapan & makan malam'],
                    ['id' => 30, 'qty' => 10, 'price' => 1000, 'dosage' => '10 mg', 'frequency' => '3x1 sebelum makan', 'instructions' => 'Meredakan mual'],
                ],
                'needs_lab' => false,
                'consult_name' => 'Konsultasi & Pemeriksaan Dokter Umum', 'consult_fee' => 40000,
                'procedure_name' => '', 'procedure_fee' => 0,
                'doc_fee' => 25000, 'nurse_fee' => 5000, 'clinic_fee' => 10000,
            ];
        } elseif ($uSeed === 3) {
            // Diabetes Melitus Tipe 2 (Dengan Pemeriksaan Lab GDS)
            return [
                'chief_complaint' => 'Sering merasa haus, sering kencing malam hari lebih dari 4 kali, dan badan lemas',
                'present_illness' => 'Berat badan turun 4 kg dalam 2 bulan terakhir tanpa program diet',
                'pain_scale' => 0, 'pain_location' => '-',
                'systolic' => 125, 'diastolic' => 82, 'temperature' => 36.6,
                'subjective' => 'Pasien mengeluhkan gejala trias DM: polidipsi, poliuri, dan polifagi disertai rasa lelah berkepanjangan.',
                'objective' => 'Mata: anemis (-), sklera ikterik (-). Ekstremitas hangat, luka diabetik (-). Hasil Lab GDS: 245 mg/dL.',
                'assessment' => 'Diabetes Melitus Tipe 2 Tanpa Komplikasi (E11.9)',
                'plan' => 'Pemeriksaan laboratorium Gula Darah Sewaktu (GDS), OHO Metformin 500 mg, konseling gizi diabetisi.',
                'education' => 'Edukasi 3J (Jadwal, Jumlah, Jenis makanan), batasi karbohidrat sederhana/gula, cek gula darah berkala.',
                'icd10_primary' => 'E11.9', 'icd10_secondary' => null,
                'icd9_code' => '89.07', 'icd9_desc' => 'General physical examination',
                'is_anc' => false,
                'medicines' => [
                    ['id' => 21, 'qty' => 30, 'price' => 700, 'dosage' => '500 mg', 'frequency' => '2x1 bersama makan', 'instructions' => 'Mencegah gangguan lambung'],
                    ['id' => 38, 'qty' => 30, 'price' => 500, 'dosage' => '1 Tab', 'frequency' => '1x1 sesudah makan', 'instructions' => 'Menjaga kesehatan saraf perifer'],
                ],
                'needs_lab' => true,
                'laboratory_id' => 2,
                'lab_name' => 'Pemeriksaan Gula Darah Sewaktu (GDS)', 'lab_price' => 25000,
                'lab_clinical_notes' => 'Skrining & evaluasi kadar glukosa darah pada dugaan klinis DM',
                'lab_parameter' => 'Glukosa Darah Sewaktu', 'lab_result_val' => '245 mg/dL',
                'lab_range' => '< 140 mg/dL', 'lab_unit' => 'mg/dL', 'lab_abnormal' => true,
                'consult_name' => 'Konsultasi & Pemeriksaan Dokter Umum', 'consult_fee' => 40000,
                'procedure_name' => '', 'procedure_fee' => 0,
                'doc_fee' => 25000, 'nurse_fee' => 5000, 'clinic_fee' => 10000,
            ];
        } else {
            // Gastroenteritis Akut / Diare
            return [
                'chief_complaint' => 'Buang air besar cair lebih dari 5 kali sejak tadi malam disertai kram perut',
                'present_illness' => 'Tinja cair tidak berdarah, ada lendir sedikit, muntah 1 kali setelah makan jajanan pedas',
                'pain_scale' => 2, 'pain_location' => 'Perut bawah',
                'systolic' => 110, 'diastolic' => 70, 'temperature' => 37.2,
                'subjective' => 'Pasien mengeluh diare cair mendadak, mules melilit, badan lemas, dan rasa haus.',
                'objective' => 'Mata cekung (-), turgor kulit kembali cepat, bibir agak kering. Bising usus meningkat 16x/m.',
                'assessment' => 'Gastroenteritis Akut Tanpa Dehidrasi Berat (A09)',
                'plan' => 'Rehidrasi oralit aktif, adsorben / antimotilitas bila perlu, probiotik & diet lunak.',
                'education' => 'Minum 1 gelas oralit tiap selesai BAB cair, jaga kebersihan tangan dan makanan, hindari susu sapi sementara.',
                'icd10_primary' => 'A09', 'icd10_secondary' => null,
                'icd9_code' => '89.03', 'icd9_desc' => 'Interview and evaluation',
                'is_anc' => false,
                'medicines' => [
                    ['id' => 29, 'qty' => 6, 'price' => 1200, 'dosage' => '200 ml', 'frequency' => 'Tiap BAB cair', 'instructions' => 'Larutkan 1 sachet dalam 200 ml air'],
                    ['id' => 28, 'qty' => 6, 'price' => 600, 'dosage' => '2 mg', 'frequency' => 'Bila diare berlanjut', 'instructions' => 'Maksimal 4 tablet/hari'],
                    ['id' => 1, 'qty' => 10, 'price' => 600, 'dosage' => '500 mg', 'frequency' => '3x1 sesudah makan', 'instructions' => 'Bila demam / kram'],
                ],
                'needs_lab' => false,
                'consult_name' => 'Konsultasi & Pemeriksaan Dokter Umum', 'consult_fee' => 40000,
                'procedure_name' => '', 'procedure_fee' => 0,
                'doc_fee' => 25000, 'nurse_fee' => 5000, 'clinic_fee' => 10000,
            ];
        }
    }
}
