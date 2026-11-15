<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('license_number')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->default('Kota Malang');
            $table->string('province', 100)->default('Jawa Timur');
            $table->string('postal_code', 10)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('header_note')->nullable();
            $table->string('footer_note')->nullable();
            $table->timestamps();
        });

        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string');
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('polyclinics', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('type', 30)->default('umum'); // umum, gigi, kia, spesialis
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Pagi, Siang, Malam
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number', 30)->unique();
            $table->string('name');
            $table->string('title_prefix', 30)->nullable();
            $table->string('title_suffix', 30)->nullable();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('profession', 50); // dokter, perawat, bidan, apoteker, ttk, administrasi, manajemen
            $table->string('specialization', 100)->nullable();
            $table->string('sip_str_number', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('polyclinic_id')->constrained('polyclinics')->cascadeOnDelete();
            $table->tinyInteger('day_of_week'); // 1 = Senin, 2 = Selasa, ..., 7 = Minggu
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('quota')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('polyclinics');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('clinic_settings');
        Schema::dropIfExists('clinics');
    }
};
