<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category', 50)->default('hematologi'); // hematologi, kimia_darah, urinalisis, imunologi, mikrobiologi
            $table->string('sample_type', 50)->default('darah'); // darah, urin, feses, swab, sputum
            $table->string('unit', 30)->nullable(); // mg/dL, g/dL, %, /uL, dll
            $table->string('reference_range', 100)->nullable(); // Nilai rujukan normal
            $table->decimal('critical_low', 10, 2)->nullable();
            $table->decimal('critical_high', 10, 2)->nullable();
            $table->decimal('tariff', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('laboratory_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->dateTime('request_time');
            $table->text('clinical_notes')->nullable();
            $table->dateTime('sample_collected_at')->nullable();
            $table->string('sample_collector', 100)->nullable();
            $table->enum('status', ['menunggu_sampel', 'diproses', 'selesai', 'dibatalkan'])->default('menunggu_sampel');
            $table->timestamps();
        });

        Schema::create('laboratory_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_request_id')->constrained('laboratory_requests')->cascadeOnDelete();
            $table->foreignId('laboratory_id')->constrained('laboratories')->cascadeOnDelete();
            $table->decimal('tariff', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('laboratory_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_request_id')->constrained('laboratory_requests')->cascadeOnDelete();
            $table->foreignId('laboratory_id')->constrained('laboratories')->cascadeOnDelete();
            $table->string('result_value', 150);
            $table->boolean('is_critical')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_results');
        Schema::dropIfExists('laboratory_request_items');
        Schema::dropIfExists('laboratory_requests');
        Schema::dropIfExists('laboratories');
    }
};
