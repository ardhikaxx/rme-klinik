<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes that back the sort / filter order of every paginated list page.
     *
     * Each index is only created when it does not exist yet, so the migration
     * stays safe to run on databases that already carry an equivalent index.
     * Indexes never change the returned rows, only how fast they are found.
     */
    private array $indexes = [
        'billing_invoices' => [
            ['bill_created_idx', ['created_at']],
        ],
        'medical_documents' => [
            ['med_doc_created_idx', ['created_at']],
        ],
        'laboratory_requests' => [
            ['lab_req_time_idx', ['request_time']],
        ],
        'prescriptions' => [
            ['rx_created_idx', ['created_at']],
        ],
        'medicine_receipts' => [
            ['med_receipt_date_idx', ['received_date']],
        ],
        'medicine_stock_opnames' => [
            ['opname_date_idx', ['opname_date']],
        ],
        'medical_fee_transactions' => [
            ['med_fee_created_idx', ['created_at']],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as [$name, $columns]) {
                if (! Schema::hasIndex($table, $name)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
                        $blueprint->index($columns, $name);
                    });
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->indexes as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as [$name]) {
                if (Schema::hasIndex($table, $name)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($name) {
                        $blueprint->dropIndex($name);
                    });
                }
            }
        }
    }
};
