<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Menyiapkan jadwal kerja dokter dan tenaga kesehatan...');

        DB::table('doctor_schedules')->truncate();

        $now = now();
        $schedules = [];

        // Poli Umum (Poli 1, Dokter 3 - Dr. Ahmad Fauzi Pratama): Senin - Sabtu 08:00 - 14:00
        for ($d = 1; $d <= 6; $d++) {
            $schedules[] = [
                'employee_id' => 3,
                'polyclinic_id' => 1,
                'day_of_week' => $d,
                'start_time' => '08:00:00',
                'end_time' => '14:00:00',
                'quota' => 50,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Poli Gigi (Poli 2, Dokter 4 - Drg. Maya Anggraini): Senin - Sabtu 08:30 - 13:30
        for ($d = 1; $d <= 6; $d++) {
            $schedules[] = [
                'employee_id' => 4,
                'polyclinic_id' => 2,
                'day_of_week' => $d,
                'start_time' => '08:30:00',
                'end_time' => '13:30:00',
                'quota' => 30,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Poli KIA (Poli 3, Dokter 3 / Tenaga Kesehatan): Senin - Sabtu 08:00 - 13:00
        for ($d = 1; $d <= 6; $d++) {
            $schedules[] = [
                'employee_id' => 3,
                'polyclinic_id' => 3,
                'day_of_week' => $d,
                'start_time' => '08:00:00',
                'end_time' => '13:00:00',
                'quota' => 35,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('doctor_schedules')->insert($schedules);
        $this->command->info('✓ Jadwal dokter seluruh poli berhasil dibuat ('.count($schedules).' jadwal).');
    }
}
