<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('medical_record_number', 30)->unique();
            $table->string('nik', 16)->nullable()->index();
            $table->string('name', 150)->index();
            $table->string('place_of_birth', 100)->nullable();
            $table->date('date_of_birth')->index();
            $table->enum('gender', ['L', 'P'])->default('L');
            $table->string('blood_type', 10)->default('tidak_tahu');
            $table->text('address')->nullable();
            $table->string('rt', 10)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('city', 100)->default('Kota Malang');
            $table->string('province', 100)->default('Jawa Timur');
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 30)->index();
            $table->string('email', 100)->nullable();
            $table->string('marital_status', 30)->default('belum_kawin');
            $table->string('occupation', 100)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('education', 50)->nullable();
            $table->boolean('is_verified')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('patient_allergies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->enum('allergen_type', ['obat', 'makanan', 'lingkungan', 'lainnya'])->default('obat');
            $table->string('allergen_name', 150);
            $table->string('reaction', 255)->nullable();
            $table->enum('severity', ['ringan', 'sedang', 'berat'])->default('sedang');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('patient_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('relationship', 50);
            $table->string('phone', 30);
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_emergency_contacts');
        Schema::dropIfExists('patient_allergies');
        Schema::dropIfExists('patients');
    }
};
