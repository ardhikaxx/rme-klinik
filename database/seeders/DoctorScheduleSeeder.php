<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DoctorScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Explicit schedules without loops.
     */
    public function run(): void
    {
        $this->command?->info('Menyiapkan jadwal kerja dokter dan tenaga kesehatan...');

        DB::table('doctor_schedules')->truncate();

        $now = now();
        $schedules = [
            // dr. Ahmad Fauzi Pratama (Poli Umum - Senin s/d Sabtu)
            ['employee_id' => 3, 'polyclinic_id' => 1, 'day_of_week' => 1, 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'quota' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 1, 'day_of_week' => 2, 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'quota' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 1, 'day_of_week' => 3, 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'quota' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 1, 'day_of_week' => 4, 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'quota' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 1, 'day_of_week' => 5, 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'quota' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 1, 'day_of_week' => 6, 'start_time' => '08:00:00', 'end_time' => '14:00:00', 'quota' => 50, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            // drg. Maya Anggraini (Poli Gigi - Senin s/d Sabtu)
            ['employee_id' => 4, 'polyclinic_id' => 2, 'day_of_week' => 1, 'start_time' => '08:30:00', 'end_time' => '13:30:00', 'quota' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 4, 'polyclinic_id' => 2, 'day_of_week' => 2, 'start_time' => '08:30:00', 'end_time' => '13:30:00', 'quota' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 4, 'polyclinic_id' => 2, 'day_of_week' => 3, 'start_time' => '08:30:00', 'end_time' => '13:30:00', 'quota' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 4, 'polyclinic_id' => 2, 'day_of_week' => 4, 'start_time' => '08:30:00', 'end_time' => '13:30:00', 'quota' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 4, 'polyclinic_id' => 2, 'day_of_week' => 5, 'start_time' => '08:30:00', 'end_time' => '13:30:00', 'quota' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 4, 'polyclinic_id' => 2, 'day_of_week' => 6, 'start_time' => '08:30:00', 'end_time' => '13:30:00', 'quota' => 30, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],

            // Tenaga Medis KIA (Poli KIA - Senin s/d Sabtu)
            ['employee_id' => 3, 'polyclinic_id' => 3, 'day_of_week' => 1, 'start_time' => '08:00:00', 'end_time' => '13:00:00', 'quota' => 35, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 3, 'day_of_week' => 2, 'start_time' => '08:00:00', 'end_time' => '13:00:00', 'quota' => 35, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 3, 'day_of_week' => 3, 'start_time' => '08:00:00', 'end_time' => '13:00:00', 'quota' => 35, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 3, 'day_of_week' => 4, 'start_time' => '08:00:00', 'end_time' => '13:00:00', 'quota' => 35, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 3, 'day_of_week' => 5, 'start_time' => '08:00:00', 'end_time' => '13:00:00', 'quota' => 35, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 3, 'polyclinic_id' => 3, 'day_of_week' => 6, 'start_time' => '08:00:00', 'end_time' => '13:00:00', 'quota' => 35, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('doctor_schedules')->insert($schedules);
        $this->command?->info('✓ Jadwal dokter seluruh poli berhasil dibuat (18 jadwal).');
    }
}
