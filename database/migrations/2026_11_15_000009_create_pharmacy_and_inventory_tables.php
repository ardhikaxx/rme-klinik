<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('medicine_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('generic_name', 150);
            $table->string('brand_name', 150);
            $table->foreignId('category_id')->constrained('medicine_categories')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('medicine_units')->cascadeOnDelete();
            $table->string('dosage_form', 50); // Tablet, Kapsul, Sirup, Injeksi, Salep, dll
            $table->string('strength', 50)->nullable(); // 500 mg, 125 mg/5ml, dll
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->integer('min_stock')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date')->index();
            $table->integer('current_stock')->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->timestamps();

            $table->index(['medicine_id', 'expiry_date']);
        });

        Schema::create('medicine_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->enum('movement_type', ['receipt', 'dispense', 'adjustment_plus', 'adjustment_minus', 'return', 'opname']);
            $table->integer('quantity'); // positive for addition, negative for reduction
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['medicine_id', 'created_at']);
        });

        Schema::create('medicine_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->string('invoice_number', 50);
            $table->date('received_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('medicine_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_receipt_id')->constrained('medicine_receipts')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->string('batch_number', 50);
            $table->date('expiry_date');
            $table->integer('quantity');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });

        Schema::create('medicine_stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number', 50)->unique();
            $table->date('opname_date');
            $table->enum('status', ['draft', 'approved', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('conducted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('medicine_stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_stock_opname_id')->constrained('medicine_stock_opnames')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('medicine_batches')->nullOnDelete();
            $table->integer('system_stock');
            $table->integer('physical_stock');
            $table->integer('difference');
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number', 50)->unique();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->cascadeOnDelete();
            $table->dateTime('prescription_date');
            $table->enum('status', [
                'menunggu_verifikasi',
                'diverifikasi',
                'disiapkan',
                'siap_diserahkan',
                'diserahkan_sebagian',
                'selesai',
                'ditolak',
                'dibatalkan',
            ])->default('menunggu_verifikasi')->index();
            $table->text('doctor_notes')->nullable();
            $table->text('pharmacist_notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('dispensed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->integer('quantity');
            $table->string('dosage', 50); // e.g. 500mg, 1 tablet
            $table->string('frequency', 50); // e.g. 3x1 hari
            $table->string('route', 50)->default('oral'); // oral, injeksi, tetes, luar
            $table->integer('duration_days')->default(1);
            $table->string('instructions', 255); // sesudah makan, sebelum makan, saat nyeri
            $table->text('special_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_compounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->string('compound_name', 100);
            $table->string('dosage_form', 50)->default('Puyer'); // Puyer, Kapsul, Salep Campur
            $table->integer('package_count'); // Jumlah bungkus/kapsul
            $table->string('frequency', 50);
            $table->string('instructions', 255);
            $table->text('special_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('prescription_compound_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_compound_id')->constrained('prescription_compounds')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->decimal('quantity', 8, 2);
            $table->string('unit', 30);
            $table->timestamps();
        });

        Schema::create('dispensing_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('dispensing_number', 50)->unique();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('visit_id')->constrained('visits')->cascadeOnDelete();
            $table->foreignId('dispensed_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('dispensed_at');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('dispensing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensing_transaction_id')->constrained('dispensing_transactions')->cascadeOnDelete();
            $table->foreignId('prescription_item_id')->nullable()->constrained('prescription_items')->nullOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('batch_id')->constrained('medicine_batches')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensing_items');
        Schema::dropIfExists('dispensing_transactions');
        Schema::dropIfExists('prescription_compound_items');
        Schema::dropIfExists('prescription_compounds');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medicine_stock_opname_items');
        Schema::dropIfExists('medicine_stock_opnames');
        Schema::dropIfExists('medicine_receipt_items');
        Schema::dropIfExists('medicine_receipts');
        Schema::dropIfExists('medicine_stock_movements');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('medicine_units');
        Schema::dropIfExists('medicine_categories');
        Schema::dropIfExists('suppliers');
    }
};
