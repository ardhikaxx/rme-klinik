<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('category', [
                'administrasi',
                'konsultasi',
                'tindakan_medis',
                'tindakan_keperawatan',
                'laboratorium',
                'bmhp',
                'lainnya',
            ])->default('tindakan_medis');
            $table->foreignId('polyclinic_id')->nullable()->constrained('polyclinics')->nullOnDelete();
            $table->string('icd9cm_code', 20)->nullable();
            $table->decimal('tariff', 15, 2)->default(0);
            $table->decimal('doctor_fee', 15, 2)->default(0);
            $table->decimal('nurse_fee', 15, 2)->default(0);
            $table->decimal('clinic_fee', 15, 2)->default(0);
            $table->decimal('doctor_fee_percent', 5, 2)->default(0);
            $table->decimal('nurse_fee_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medical_fee_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('profession', 50); // dokter, perawat, bidan
            $table->enum('calculation_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('value', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->string('discount_reason', 255)->nullable();
            $table->decimal('final_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2)->default(0);
            $table->enum('status', [
                'belum_dibayar',
                'pembayaran_sebagian',
                'lunas',
                'dibatalkan',
                'refund',
            ])->default('belum_dibayar')->index();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('billing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_invoice_id')->constrained('billing_invoices')->cascadeOnDelete();
            $table->enum('item_type', [
                'administrasi',
                'konsultasi',
                'tindakan_medis',
                'tindakan_keperawatan',
                'obat',
                'laboratorium',
                'bmhp',
                'lainnya',
            ]);
            $table->string('item_code', 50)->nullable();
            $table->string('item_name', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('account_info')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->foreignId('billing_invoice_id')->constrained('billing_invoices')->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('payment_methods')->cascadeOnDelete();
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('amount_tendered', 15, 2)->nullable();
            $table->decimal('change_due', 15, 2)->default(0);
            $table->dateTime('payment_time');
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_invoice_id')->constrained('billing_invoices')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->string('refund_number', 50)->unique();
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->foreignId('approved_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('refunded_at');
            $table->timestamps();
        });

        Schema::create('medical_fee_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_item_id')->nullable()->constrained('billing_items')->nullOnDelete();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('nurse_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->decimal('doctor_fee', 15, 2)->default(0);
            $table->decimal('nurse_fee', 15, 2)->default(0);
            $table->decimal('clinic_fee', 15, 2)->default(0);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_fee_transactions');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('billing_items');
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('medical_fee_rules');
        Schema::dropIfExists('service_tariffs');
    }
};
