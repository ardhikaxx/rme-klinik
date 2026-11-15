<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensivePatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(int $targetPatients = 100000, int $chunkSize = 2000): void
    {
        $this->command->info("Menghasilkan {$targetPatients} Pasien Unik Indonesia berskala besar...");

        $currentCount = DB::table('patients')->count();
        if ($currentCount >= $targetPatients) {
            $this->command->info("Data pasien sudah mencukupi ({$currentCount} pasien). Melewati seeding pasien.");

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

        $bar = $this->command->getOutput()->createProgressBar($targetPatients);
        $bar->start();

        $patientChunk = [];
        $allergyChunk = [];
        $contactChunk = [];
        $adminUserId = DB::table('users')->value('id') ?? 1;

        for ($i = 1; $i <= $targetPatients; $i++) {
            $isFemale = ($i % 2 === 0);
            $gender = $isFemale ? 'P' : 'L';

            $firstName = $isFemale
                ? $femaleFirst[($i * 7) % count($femaleFirst)]
                : $maleFirst[($i * 11) % count($maleFirst)];
            $lastName = $lastNames[($i * 13) % count($lastNames)];
            $fullName = "{$firstName} {$lastName}";

            // Demografis usia (0 s/d 85 thn)
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

            // Format NIK resmi Dukcapil: 3573 + (DD (+40 jika wanita)) + MM + YY + 4 digit acak
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

            // ~15% pasien mempunyai alergi
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

            // ~50% pasien mempunyai kontak darurat
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
                    foreach (array_chunk($allergyChunk, 500) as $alSlice) {
                        DB::table('patient_allergies')->insert($alSlice);
                    }
                    $allergyChunk = [];
                }

                if (! empty($contactChunk)) {
                    foreach (array_chunk($contactChunk, 500) as $ctSlice) {
                        DB::table('patient_emergency_contacts')->insert($ctSlice);
                    }
                    $contactChunk = [];
                }

                $bar->advance($chunkSize);
            }
        }

        if (! empty($patientChunk)) {
            DB::table('patients')->insert($patientChunk);
            if (! empty($allergyChunk)) {
                foreach (array_chunk($allergyChunk, 500) as $alSlice) {
                    DB::table('patient_allergies')->insert($alSlice);
                }
            }
            if (! empty($contactChunk)) {
                foreach (array_chunk($contactChunk, 500) as $ctSlice) {
                    DB::table('patient_emergency_contacts')->insert($ctSlice);
                }
            }
            $bar->finish();
        }

        $this->command->info('');
        $this->command->info("✓ Sukses mengenerate {$targetPatients} Pasien Unik Indonesia.");
    }
}
