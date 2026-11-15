<?php

use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ClinicalExaminationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FhirController;
use App\Http\Controllers\LaboratoryController;
use App\Http\Controllers\MaternalChildController;
use App\Http\Controllers\MedicalDocumentController;
use App\Http\Controllers\NursingAssessmentController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - RME KLINIK Terpadu
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/password/change', [AuthController::class, 'changePassword'])->name('password.change');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Master Patient Index (MPI)
    Route::prefix('patients')->name('patients.')->group(function () {
        Route::get('/', [PatientController::class, 'index'])->name('index')->middleware('permission:patients.view');
        Route::get('/create', [PatientController::class, 'create'])->name('create')->middleware('permission:patients.create');
        Route::post('/', [PatientController::class, 'store'])->name('store')->middleware('permission:patients.create');
        Route::get('/search/ajax', [PatientController::class, 'searchAjax'])->name('search.ajax');
        Route::post('/check-duplicate', [PatientController::class, 'checkDuplicate'])->name('check_duplicate');
        Route::get('/{patient}', [PatientController::class, 'show'])->name('show')->middleware('permission:patients.view');
        Route::get('/{patient}/edit', [PatientController::class, 'edit'])->name('edit')->middleware('permission:patients.update');
        Route::put('/{patient}', [PatientController::class, 'update'])->name('update')->middleware('permission:patients.update');
        Route::get('/{patient}/card', [PatientController::class, 'digitalCard'])->name('card')->middleware('permission:patients.card.print');
    });

    // Visit Registration & Internal Queue Tracking
    Route::prefix('visits')->name('visits.')->group(function () {
        Route::get('/', [VisitController::class, 'index'])->name('index')->middleware('permission:visits.view');
        Route::get('/create', [VisitController::class, 'create'])->name('create')->middleware('permission:visits.register');
        Route::post('/', [VisitController::class, 'store'])->name('store')->middleware('permission:visits.register');
        Route::get('/{visit}', [VisitController::class, 'show'])->name('show')->middleware('permission:visits.view');
        Route::post('/{visit}/status', [VisitController::class, 'updateStatus'])->name('status');
        Route::post('/{visit}/cancel', [VisitController::class, 'cancel'])->name('cancel')->middleware('permission:visits.cancel');
    });

    // Nurse Initial Assessment
    Route::prefix('nursing')->name('nursing.')->middleware('permission:nursing.assessment')->group(function () {
        Route::get('/', [NursingAssessmentController::class, 'index'])->name('index');
        Route::get('/assess/{visit}', [NursingAssessmentController::class, 'create'])->name('create');
        Route::post('/assess/{visit}', [NursingAssessmentController::class, 'store'])->name('store');
    });

    // Doctor Clinical Examination & EMR (SOAP)
    Route::prefix('clinical')->name('clinical.')->middleware('role:dokter,kepala_klinik')->group(function () {
        Route::get('/', [ClinicalExaminationController::class, 'index'])->name('index');
        Route::get('/exam/{visit}', [ClinicalExaminationController::class, 'examination'])->name('examination');
        Route::post('/exam/{visit}/draft', [ClinicalExaminationController::class, 'saveDraft'])->name('draft');
        Route::post('/records/{record}/finalize', [ClinicalExaminationController::class, 'finalize'])->name('finalize');
        Route::post('/records/{record}/addendum', [ClinicalExaminationController::class, 'addendum'])->name('addendum');
        Route::get('/icd10/ajax', [ClinicalExaminationController::class, 'icd10Ajax'])->name('icd10.ajax');
        Route::get('/icd9/ajax', [ClinicalExaminationController::class, 'icd9Ajax'])->name('icd9.ajax');
        Route::get('/timeline/{visit}', [ClinicalExaminationController::class, 'timeline'])->name('timeline');
    });

    // Specialty: Odontogram (Poli Gigi)
    Route::prefix('odontogram')->name('odontogram.')->middleware('role:dokter,kepala_klinik')->group(function () {
        Route::get('/', [OdontogramController::class, 'index'])->name('index');
        Route::get('/{visit}', [OdontogramController::class, 'show'])->name('show');
        Route::post('/{odontogram}/tooth', [OdontogramController::class, 'updateTooth'])->name('tooth');
        Route::post('/{odontogram}/save', [OdontogramController::class, 'saveFull'])->name('save');
        Route::get('/{visit}/print', [OdontogramController::class, 'print'])->name('print');
    });

    // Specialty: Poli KIA (Maternal & Child Health)
    Route::prefix('kia')->name('kia.')->middleware('permission:kia.manage')->group(function () {
        Route::get('/', [MaternalChildController::class, 'index'])->name('index');
        Route::get('/{visit}', [MaternalChildController::class, 'show'])->name('show');
        Route::post('/{visit}/anc', [MaternalChildController::class, 'storeAntenatal'])->name('anc.store');
        Route::post('/{visit}/child', [MaternalChildController::class, 'storeChildGrowth'])->name('child.store');
        Route::get('/chart-data/{patientId}', [MaternalChildController::class, 'growthChartData'])->name('chart.data');
    });

    // Pharmacy & Inventory Management
    Route::prefix('pharmacy')->name('pharmacy.')->group(function () {
        Route::get('/prescriptions', [PharmacyController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/prescriptions/{prescription}', [PharmacyController::class, 'showPrescription'])->name('show_prescription');
        Route::get('/prescriptions/create/{visit}', [PharmacyController::class, 'createPrescription'])->name('create_prescription')->middleware('permission:prescriptions.create');
        Route::post('/prescriptions/create/{visit}', [PharmacyController::class, 'storePrescription'])->name('store_prescription')->middleware('permission:prescriptions.create');
        Route::post('/prescriptions/{prescription}/verify', [PharmacyController::class, 'verify'])->name('verify')->middleware('permission:prescriptions.verify');
        Route::post('/prescriptions/{prescription}/dispense', [PharmacyController::class, 'dispense'])->name('dispense')->middleware('permission:pharmacy.dispense');
        Route::get('/prescriptions/{prescription}/label', [PharmacyController::class, 'printLabel'])->name('print_label');

        Route::middleware('permission:pharmacy.stock.view')->group(function () {
            Route::get('/medicines', [PharmacyController::class, 'medicines'])->name('medicines');
            Route::get('/medicines/search/ajax', [PharmacyController::class, 'searchMedicinesAjax'])->name('medicines.search.ajax');
            Route::get('/medicines/{medicine}/stock-card', [PharmacyController::class, 'stockCard'])->name('stock_card');
        });

        Route::middleware('permission:pharmacy.stock.manage')->group(function () {
            Route::post('/medicines', [PharmacyController::class, 'storeMedicine'])->name('medicines.store');
            Route::put('/medicines/{medicine}', [PharmacyController::class, 'updateMedicine'])->name('medicines.update');
            Route::get('/receipts', [PharmacyController::class, 'receipts'])->name('receipts');
            Route::post('/receipts', [PharmacyController::class, 'storeReceipt'])->name('receipts.store');
        });

        Route::middleware('permission:pharmacy.opname')->group(function () {
            Route::get('/opnames', [PharmacyController::class, 'opnames'])->name('opnames');
            Route::post('/opnames', [PharmacyController::class, 'storeOpname'])->name('opnames.store');
            Route::post('/opnames/{opname}/approve', [PharmacyController::class, 'approveOpname'])->name('opnames.approve');
        });
    });

    // Laboratory Module
    Route::prefix('laboratory')->name('laboratory.')->group(function () {
        Route::get('/', [LaboratoryController::class, 'index'])->name('index');
        Route::get('/{request}', [LaboratoryController::class, 'show'])->name('show');
        Route::post('/create/{visit}', [LaboratoryController::class, 'storeRequest'])->name('store_request')->middleware('permission:laboratory.order');
        Route::post('/{labRequest}/sample', [LaboratoryController::class, 'collectSample'])->name('collect_sample');
        Route::post('/{labRequest}/results', [LaboratoryController::class, 'saveResults'])->name('save_results')->middleware('permission:laboratory.record');
        Route::get('/{request}/print', [LaboratoryController::class, 'print'])->name('print');
    });

    // Billing & Cashier
    Route::prefix('billing')->name('billing.')->middleware('permission:billing.view')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/{invoice}', [BillingController::class, 'show'])->name('show');
        Route::post('/{invoice}/pay', [BillingController::class, 'pay'])->name('pay')->middleware('permission:billing.pay');
        Route::post('/{invoice}/discount', [BillingController::class, 'discount'])->name('discount')->middleware('permission:billing.discount');
        Route::post('/{invoice}/refund', [BillingController::class, 'refund'])->name('refund')->middleware('permission:billing.refund');
        Route::get('/receipt/{payment}', [BillingController::class, 'printReceipt'])->name('receipt');
    });

    // Medical Documents Generator
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [MedicalDocumentController::class, 'index'])->name('index');
        Route::get('/create/{visit}', [MedicalDocumentController::class, 'create'])->name('create')->middleware('permission:documents.generate');
        Route::post('/create/{visit}', [MedicalDocumentController::class, 'store'])->name('store')->middleware('permission:documents.generate');
        Route::get('/{document}', [MedicalDocumentController::class, 'show'])->name('show');
        Route::get('/{document}/print', [MedicalDocumentController::class, 'print'])->name('print');
        Route::post('/upload/{patient}', [MedicalDocumentController::class, 'uploadAttachment'])->name('upload')->middleware('permission:documents.upload');
        Route::get('/download/{document}', [MedicalDocumentController::class, 'downloadAttachment'])->name('download');
    });

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export', [ReportController::class, 'exportCsv'])->name('export');
    });

    // Settings & Configurations (Kepala Klinik / Admin)
    Route::prefix('settings')->name('settings.')->middleware('role:kepala_klinik')->group(function () {
        Route::get('/tariffs', [SettingController::class, 'tariffs'])->name('tariffs');
        Route::post('/tariffs', [SettingController::class, 'storeTariff'])->name('tariffs.store');
        Route::put('/tariffs/{tariff}', [SettingController::class, 'updateTariff'])->name('tariffs.update');

        Route::get('/schedules', [SettingController::class, 'schedules'])->name('schedules');
        Route::post('/schedules', [SettingController::class, 'storeSchedule'])->name('schedules.store');
        Route::post('/schedules/{schedule}/toggle', [SettingController::class, 'toggleSchedule'])->name('schedules.toggle');

        Route::get('/users', [SettingController::class, 'users'])->name('users');
        Route::post('/users', [SettingController::class, 'storeUser'])->name('users.store');
        Route::post('/users/{user}/toggle', [SettingController::class, 'toggleUser'])->name('users.toggle');
    });

    // Audit Trail (Security & Accountability)
    Route::get('/audit', [AuditTrailController::class, 'index'])->name('audit.index')->middleware('permission:audit_logs.view');

    // SATUSEHAT & FHIR Interoperability Readiness
    Route::prefix('fhir')->name('fhir.')->middleware('permission:fhir.manage')->group(function () {
        Route::get('/', [FhirController::class, 'index'])->name('index');
        Route::get('/preview/{resourceType}/{id}', [FhirController::class, 'preview'])->name('preview');
        Route::post('/stage', [FhirController::class, 'stage'])->name('stage');
    });
});
