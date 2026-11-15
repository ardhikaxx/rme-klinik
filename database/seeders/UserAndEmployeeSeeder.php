<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DoctorSchedule;
use App\Models\Employee;
use App\Models\Polyclinic;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserAndEmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmisi = Role::where('name', 'admisi_kasir')->first();
        $rolePerawat = Role::where('name', 'perawat_bidan')->first();
        $roleDokter = Role::where('name', 'dokter')->first();
        $roleFarmasi = Role::where('name', 'farmasi')->first();
        $roleKepala = Role::where('name', 'kepala_klinik')->first();

        $depMedis = Department::where('code', 'MEDIS')->first();
        $depKep = Department::where('code', 'KEP')->first();
        $depFarm = Department::where('code', 'FARM')->first();
        $depAdm = Department::where('code', 'ADM')->first();
        $depMgt = Department::where('code', 'MGT')->first();

        $polyUmum = Polyclinic::where('code', 'POLI-UMUM')->first();
        $polyGigi = Polyclinic::where('code', 'POLI-GIGI')->first();
        $polyKia = Polyclinic::where('code', 'POLI-KIA')->first();

        $defaultPassword = Hash::make('password123');

        // 1. Petugas Admisi & Kasir
        $userAdmisi = User::firstOrCreate(
            ['username' => 'admisi'],
            [
                'name' => 'Dewi Safitri, A.Md.',
                'email' => 'admisi@rme-klinik.id',
                'phone' => '081234567890',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $userAdmisi->roles()->syncWithoutDetaching([$roleAdmisi->id]);

        Employee::firstOrCreate(
            ['employee_number' => 'EMP-ADM-001'],
            [
                'user_id' => $userAdmisi->id,
                'department_id' => $depAdm->id,
                'name' => 'Dewi Safitri',
                'title_suffix' => 'A.Md.',
                'gender' => 'P',
                'profession' => 'administrasi',
                'phone' => '081234567890',
                'email' => 'admisi@rme-klinik.id',
                'address' => 'Jl. Danau Toba No. 12, Sawojajar, Kota Malang',
            ]
        );

        // 2. Perawat
        $userPerawat = User::firstOrCreate(
            ['username' => 'perawat'],
            [
                'name' => 'Siti Rahayu, S.Kep., Ns.',
                'email' => 'perawat@rme-klinik.id',
                'phone' => '081234567891',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $userPerawat->roles()->syncWithoutDetaching([$rolePerawat->id]);

        $empPerawat = Employee::firstOrCreate(
            ['employee_number' => 'EMP-NUR-001'],
            [
                'user_id' => $userPerawat->id,
                'department_id' => $depKep->id,
                'name' => 'Siti Rahayu',
                'title_suffix' => 'S.Kep., Ns.',
                'gender' => 'P',
                'profession' => 'perawat',
                'sip_str_number' => 'STR-NUR-3573-2024-001',
                'phone' => '081234567891',
                'email' => 'perawat@rme-klinik.id',
                'address' => 'Jl. Simpang Wilis No. 7, Gading Kasri, Kota Malang',
            ]
        );

        // 3. Dokter Umum
        $userDokter = User::firstOrCreate(
            ['username' => 'dokter'],
            [
                'name' => 'dr. Ahmad Fauzi Pratama',
                'email' => 'dokter@rme-klinik.id',
                'phone' => '081234567892',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $userDokter->roles()->syncWithoutDetaching([$roleDokter->id]);

        $empDokter = Employee::firstOrCreate(
            ['employee_number' => 'EMP-DOC-001'],
            [
                'user_id' => $userDokter->id,
                'department_id' => $depMedis->id,
                'name' => 'Ahmad Fauzi Pratama',
                'title_prefix' => 'dr.',
                'gender' => 'L',
                'profession' => 'dokter',
                'specialization' => 'Kedokteran Umum',
                'sip_str_number' => 'SIP-3573/123/DU/2023',
                'phone' => '081234567892',
                'email' => 'dokter@rme-klinik.id',
                'address' => 'Jl. Bondowoso No. 25, Gading Kasri, Kota Malang',
            ]
        );

        // 4. Dokter Gigi
        $userDokterGigi = User::firstOrCreate(
            ['username' => 'doktergigi'],
            [
                'name' => 'drg. Maya Anggraini',
                'email' => 'doktergigi@rme-klinik.id',
                'phone' => '081234567893',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $userDokterGigi->roles()->syncWithoutDetaching([$roleDokter->id]);

        $empDokterGigi = Employee::firstOrCreate(
            ['employee_number' => 'EMP-DEN-001'],
            [
                'user_id' => $userDokterGigi->id,
                'department_id' => $depMedis->id,
                'name' => 'Maya Anggraini',
                'title_prefix' => 'drg.',
                'gender' => 'P',
                'profession' => 'dokter',
                'specialization' => 'Kedokteran Gigi',
                'sip_str_number' => 'SIP-3573/456/DG/2023',
                'phone' => '081234567893',
                'email' => 'doktergigi@rme-klinik.id',
                'address' => 'Jl. Buring No. 14, Oro-oro Dowo, Kota Malang',
            ]
        );

        // 5. Apoteker / Farmasi
        $userFarmasi = User::firstOrCreate(
            ['username' => 'farmasi'],
            [
                'name' => 'apt. Budi Santoso, S.Farm.',
                'email' => 'farmasi@rme-klinik.id',
                'phone' => '081234567894',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $userFarmasi->roles()->syncWithoutDetaching([$roleFarmasi->id]);

        Employee::firstOrCreate(
            ['employee_number' => 'EMP-PHR-001'],
            [
                'user_id' => $userFarmasi->id,
                'department_id' => $depFarm->id,
                'name' => 'Budi Santoso',
                'title_prefix' => 'apt.',
                'title_suffix' => 'S.Farm.',
                'gender' => 'L',
                'profession' => 'apoteker',
                'sip_str_number' => 'SIPA-3573/789/2023',
                'phone' => '081234567894',
                'email' => 'farmasi@rme-klinik.id',
                'address' => 'Jl. Galunggung No. 33, Gading Kasri, Kota Malang',
            ]
        );

        // 6. Kepala Klinik
        $userKepala = User::firstOrCreate(
            ['username' => 'kepala'],
            [
                'name' => 'dr. Hendro Wicaksono, M.M.R.',
                'email' => 'kepala@rme-klinik.id',
                'phone' => '081234567895',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );
        $userKepala->roles()->syncWithoutDetaching([$roleKepala->id]);

        Employee::firstOrCreate(
            ['employee_number' => 'EMP-MGT-001'],
            [
                'user_id' => $userKepala->id,
                'department_id' => $depMgt->id,
                'name' => 'Hendro Wicaksono',
                'title_prefix' => 'dr.',
                'title_suffix' => 'M.M.R.',
                'gender' => 'L',
                'profession' => 'manajemen',
                'sip_str_number' => 'SIP-3573/001/DU/2021',
                'phone' => '081234567895',
                'email' => 'kepala@rme-klinik.id',
                'address' => 'Jl. Retawu No. 8, Klojen, Kota Malang',
            ]
        );

        // 7. Doctor Schedules (Monday through Saturday)
        for ($day = 1; $day <= 6; $day++) {
            // dr. Ahmad Fauzi (Poli Umum)
            DoctorSchedule::firstOrCreate(
                [
                    'employee_id' => $empDokter->id,
                    'polyclinic_id' => $polyUmum->id,
                    'day_of_week' => $day,
                ],
                [
                    'start_time' => '08:00:00',
                    'end_time' => '13:00:00',
                    'quota' => 40,
                    'is_active' => true,
                ]
            );

            // drg. Maya Anggraini (Poli Gigi)
            DoctorSchedule::firstOrCreate(
                [
                    'employee_id' => $empDokterGigi->id,
                    'polyclinic_id' => $polyGigi->id,
                    'day_of_week' => $day,
                ],
                [
                    'start_time' => '09:00:00',
                    'end_time' => '14:00:00',
                    'quota' => 25,
                    'is_active' => true,
                ]
            );
        }
    }
}
