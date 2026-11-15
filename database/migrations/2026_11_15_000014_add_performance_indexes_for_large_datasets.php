<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Visits composite indexes
        Schema::table('visits', function (Blueprint $table) {
            $table->index(['patient_id', 'registration_date'], 'visits_patient_reg_idx');
            $table->index(['polyclinic_id', 'status', 'registration_date'], 'visits_poly_status_date_idx');
            $table->index(['doctor_id', 'registration_date'], 'visits_doctor_date_idx');
            $table->index(['payment_type', 'registration_date'], 'visits_payment_date_idx');
        });

        // 2. Patients index
        Schema::table('patients', function (Blueprint $table) {
            $table->index('created_at', 'patients_created_at_idx');
        });

        // 3. Nursing assessments
        Schema::table('nursing_assessments', function (Blueprint $table) {
            $table->index(['patient_id', 'assessment_time'], 'nursing_assessments_patient_time_idx');
        });

        // 4. Medical records
        Schema::table('medical_records', function (Blueprint $table) {
            $table->index(['patient_id', 'examination_time'], 'medical_records_patient_time_idx');
            $table->index(['doctor_id', 'examination_time'], 'medical_records_doctor_time_idx');
            $table->index('status', 'medical_records_status_idx');
        });

        // 5. Medical record diagnoses
        Schema::table('medical_record_diagnoses', function (Blueprint $table) {
            $table->index('icd10_code', 'mr_diag_code_idx');
            $table->index(['type', 'icd10_code'], 'mr_diag_type_code_idx');
        });

        // 6. Medical record procedures
        Schema::table('medical_record_procedures', function (Blueprint $table) {
            $table->index('icd9cm_code', 'mr_proc_code_idx');
        });

        // 7. Prescriptions
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->index(['prescription_date', 'status'], 'rx_date_status_idx');
            $table->index(['doctor_id', 'prescription_date'], 'rx_doctor_date_idx');
        });

        // 8. Prescription items
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->index(['medicine_id', 'created_at'], 'rx_items_med_created_idx');
        });

        // 9. Dispensing transactions & items
        Schema::table('dispensing_transactions', function (Blueprint $table) {
            $table->index(['dispensed_at', 'visit_id'], 'disp_date_visit_idx');
        });

        Schema::table('dispensing_items', function (Blueprint $table) {
            $table->index(['medicine_id', 'batch_id'], 'disp_items_med_batch_idx');
        });

        // 10. Billing invoices & items
        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'bill_status_created_idx');
            $table->index(['patient_id', 'created_at'], 'bill_patient_created_idx');
        });

        Schema::table('billing_items', function (Blueprint $table) {
            $table->index(['reference_type', 'reference_id'], 'bill_items_ref_idx');
            $table->index('item_type', 'bill_items_type_idx');
        });

        // 11. Payments
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['payment_time', 'payment_method_id'], 'payments_time_method_idx');
        });

        // 12. Laboratory requests
        Schema::table('laboratory_requests', function (Blueprint $table) {
            $table->index(['status', 'request_time'], 'lab_req_status_time_idx');
        });

        // 13. Medical documents
        Schema::table('medical_documents', function (Blueprint $table) {
            $table->index(['type', 'issue_date'], 'med_doc_type_date_idx');
            $table->index(['patient_id', 'issue_date'], 'med_doc_patient_date_idx');
        });

        // 14. Audit logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['module', 'created_at'], 'audit_mod_created_idx');
            $table->index(['user_id', 'created_at'], 'audit_user_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_mod_created_idx');
            $table->dropIndex('audit_user_created_idx');
        });

        Schema::table('medical_documents', function (Blueprint $table) {
            $table->dropIndex('med_doc_type_date_idx');
            $table->dropIndex('med_doc_patient_date_idx');
        });

        Schema::table('laboratory_requests', function (Blueprint $table) {
            $table->dropIndex('lab_req_status_time_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_time_method_idx');
        });

        Schema::table('billing_items', function (Blueprint $table) {
            $table->dropIndex('bill_items_ref_idx');
            $table->dropIndex('bill_items_type_idx');
        });

        Schema::table('billing_invoices', function (Blueprint $table) {
            $table->dropIndex('bill_status_created_idx');
            $table->dropIndex('bill_patient_created_idx');
        });

        Schema::table('dispensing_items', function (Blueprint $table) {
            $table->dropIndex('disp_items_med_batch_idx');
        });

        Schema::table('dispensing_transactions', function (Blueprint $table) {
            $table->dropIndex('disp_date_visit_idx');
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropIndex('rx_items_med_created_idx');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropIndex('rx_date_status_idx');
            $table->dropIndex('rx_doctor_date_idx');
        });

        Schema::table('medical_record_procedures', function (Blueprint $table) {
            $table->dropIndex('mr_proc_code_idx');
        });

        Schema::table('medical_record_diagnoses', function (Blueprint $table) {
            $table->dropIndex('mr_diag_code_idx');
            $table->dropIndex('mr_diag_type_code_idx');
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropIndex('medical_records_patient_time_idx');
            $table->dropIndex('medical_records_doctor_time_idx');
            $table->dropIndex('medical_records_status_idx');
        });

        Schema::table('nursing_assessments', function (Blueprint $table) {
            $table->dropIndex('nursing_assessments_patient_time_idx');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('patients_created_at_idx');
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_patient_reg_idx');
            $table->dropIndex('visits_poly_status_date_idx');
            $table->dropIndex('visits_doctor_date_idx');
            $table->dropIndex('visits_payment_date_idx');
        });
    }
};
