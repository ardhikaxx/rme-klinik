<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('visit_number', 30)->unique();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('polyclinic_id')->constrained('polyclinics')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('registration_date')->index();
            $table->enum('visit_type', ['baru', 'lama'])->default('lama');
            $table->enum('payment_type', ['umum', 'bpjs', 'asuransi_lain'])->default('umum');
            $table->string('insurance_number', 50)->nullable();
            $table->enum('status', [
                'terdaftar',
                'menunggu_pemeriksaan_awal',
                'pemeriksaan_awal_berlangsung',
                'menunggu_dokter',
                'pemeriksaan_dokter_berlangsung',
                'menunggu_pemeriksaan_penunjang',
                'menunggu_farmasi',
                'menunggu_pembayaran',
                'selesai',
                'dibatalkan',
            ])->default('menunggu_pemeriksaan_awal')->index();
            $table->string('queue_number', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('visit_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->string('status_from', 50)->nullable();
            $table->string('status_to', 50);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_status_histories');
        Schema::dropIfExists('visits');
    }
};
