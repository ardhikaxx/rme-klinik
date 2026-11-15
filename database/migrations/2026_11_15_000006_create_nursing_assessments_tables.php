<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nursing_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->unique()->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('nurse_id')->constrained('employees')->cascadeOnDelete();
            $table->dateTime('assessment_time');
            $table->text('chief_complaint');
            $table->text('current_illness_history')->nullable();
            $table->text('past_illness_history')->nullable();
            $table->text('family_illness_history')->nullable();
            $table->text('current_medication')->nullable();
            $table->integer('systolic')->nullable(); // mmHg
            $table->integer('diastolic')->nullable(); // mmHg
            $table->decimal('temperature', 4, 1)->nullable(); // Celcius
            $table->integer('pulse_rate')->nullable(); // bpm
            $table->integer('respiratory_rate')->nullable(); // breaths/min
            $table->decimal('oxygen_saturation', 4, 1)->nullable(); // %
            $table->decimal('weight', 5, 2)->nullable(); // kg
            $table->decimal('height', 5, 2)->nullable(); // cm
            $table->decimal('bmi', 4, 1)->nullable(); // kg/m^2
            $table->decimal('waist_circumference', 5, 2)->nullable(); // cm
            $table->enum('consciousness', ['compos_mentis', 'apatis', 'somnolen', 'sopor', 'koma'])->default('compos_mentis');
            $table->tinyInteger('pain_scale')->default(0); // 0-10
            $table->enum('fall_risk', ['rendah', 'sedang', 'tinggi'])->default('rendah');
            $table->text('nursing_care_plan')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('recorded_at');
            $table->integer('systolic')->nullable();
            $table->integer('diastolic')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('pulse_rate')->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->decimal('oxygen_saturation', 4, 1)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('nursing_assessments');
    }
};
