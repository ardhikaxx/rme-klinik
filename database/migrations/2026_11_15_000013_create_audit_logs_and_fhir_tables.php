<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();
            $table->string('module', 50)->index();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->index();
        });

        Schema::create('fhir_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('resource_type', 50)->index(); // Patient, Encounter, Condition, Observation, Procedure, MedicationRequest, MedicationDispense, DiagnosticReport
            $table->string('local_table', 50);
            $table->unsignedBigInteger('local_id');
            $table->string('fhir_id', 100)->nullable()->index();
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending')->index();
            $table->dateTime('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['local_table', 'local_id']);
        });

        Schema::create('fhir_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fhir_mapping_id')->nullable()->constrained('fhir_mappings')->nullOnDelete();
            $table->string('resource_type', 50)->index();
            $table->json('payload')->nullable();
            $table->integer('response_code')->nullable();
            $table->json('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('attempt')->default(1);
            $table->dateTime('synced_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fhir_sync_logs');
        Schema::dropIfExists('fhir_mappings');
        Schema::dropIfExists('audit_logs');
    }
};
