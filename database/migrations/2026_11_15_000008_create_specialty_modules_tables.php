<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Poli Gigi: Odontogram
        Schema::create('odontograms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('odontogram_teeth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontogram_id')->constrained('odontograms')->cascadeOnDelete();
            $table->string('tooth_number', 5); // FDI two-digit notation: 11-18, 21-28, 31-38, 41-48, 51-55, 61-65, 71-75, 81-85
            $table->string('condition', 50); // sound, caries, filling, missing, radix, crown, root_canal, bridge, unerupted
            $table->string('surfaces', 50)->nullable(); // Mesial, Occlusal, Distal, Buccal, Lingual (MODBL)
            $table->text('treatment_notes')->nullable();
            $table->timestamps();

            $table->unique(['odontogram_id', 'tooth_number']);
        });

        // Poli KIA: Antenatal Care (Ibu Hamil)
        Schema::create('antenatal_cares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->tinyInteger('gravida')->default(1);
            $table->tinyInteger('para')->default(0);
            $table->tinyInteger('abortus')->default(0);
            $table->date('hpht')->nullable(); // Hari Pertama Haid Terakhir
            $table->date('estimated_due_date')->nullable(); // Hari Perkiraan Lahir
            $table->decimal('gestational_age_weeks', 4, 1)->nullable();
            $table->decimal('fundal_height', 4, 1)->nullable(); // cm
            $table->integer('fetal_heart_rate')->nullable(); // bpm
            $table->string('fetal_presentation', 50)->nullable(); // kepala, sungsang, lintang
            $table->integer('systolic')->nullable();
            $table->integer('diastolic')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('complaints')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->timestamps();
        });

        // Poli KIA: Child Growth & Development (Tumbuh Kembang Anak)
        Schema::create('child_growths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->integer('age_months');
            $table->decimal('weight', 5, 2); // kg
            $table->decimal('height', 5, 2); // cm
            $table->decimal('head_circumference', 4, 1)->nullable(); // cm
            $table->string('immunization_status', 100)->nullable();
            $table->text('development_milestone')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_growths');
        Schema::dropIfExists('antenatal_cares');
        Schema::dropIfExists('odontogram_teeth');
        Schema::dropIfExists('odontograms');
    }
};
