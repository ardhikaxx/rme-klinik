<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('type', 50); // surat_sakit, surat_sehat, rujukan, lab, informed_consent
            $table->longText('template_body');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medical_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 50)->unique();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', [
                'surat_keterangan_sakit',
                'surat_keterangan_sehat',
                'surat_rujukan',
                'surat_pengantar_lab',
                'informed_consent',
            ]);
            $table->date('issue_date');
            $table->json('content_data');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('visits')->nullOnDelete();
            $table->string('title', 255);
            $table->enum('document_type', [
                'radiologi',
                'lab_eksternal',
                'rujukan_eksternal',
                'identitas',
                'lainnya',
            ])->default('radiologi');
            $table->string('file_path', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_documents');
        Schema::dropIfExists('medical_documents');
        Schema::dropIfExists('document_templates');
    }
};
