<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->unique()->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->dateTime('examination_time');
            $table->text('subjective');
            $table->text('objective');
            $table->text('assessment');
            $table->text('plan');
            $table->enum('status', ['draft', 'final', 'addended'])->default('draft');
            $table->dateTime('finalized_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('education_plan')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->date('next_visit_date')->nullable();
            $table->timestamps();
        });

        Schema::create('medical_record_addendums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->text('addendum_content');
            $table->text('reason');
            $table->dateTime('created_at');
        });

        Schema::create('medical_record_diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->cascadeOnDelete();
            $table->string('icd10_code', 20);
            $table->string('description', 500);
            $table->enum('type', ['primary', 'secondary'])->default('secondary');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('medical_record_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->cascadeOnDelete();
            $table->string('icd9cm_code', 20);
            $table->string('description', 500);
            $table->decimal('tariff', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_record_procedures');
        Schema::dropIfExists('medical_record_diagnoses');
        Schema::dropIfExists('medical_record_addendums');
        Schema::dropIfExists('medical_records');
    }
};
