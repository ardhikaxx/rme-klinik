@extends('layouts.app')

@section('title', 'Pemeriksaan Klinis - ' . $visit->patient->name)

@section('content')
<div class="container-fluid">
    <!-- Top Action & Navigation Bar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Pemeriksaan Dokter (SOAP)</h4>
                @if($record && $record->status === 'final')
                    <span class="badge bg-success fs-6"><i class="fas fa-lock me-1"></i>TERKUNCI (FINAL)</span>
                @else
                    <span class="badge bg-warning text-dark fs-6"><i class="fas fa-edit me-1"></i>DRAF PEMERIKSAAN</span>
                @endif
            </div>
            <div class="text-muted small mt-1">
                Kunjungan: <strong>{{ $visit->visit_number }}</strong> (Antrean: <strong class="text-primary">{{ $visit->queue_number }}</strong>) | 
                Poli: <strong>{{ $visit->polyclinic?->name }}</strong> | 
                Dokter: <strong>{{ $visit->doctor?->formatted_name }}</strong>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clinical.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Antrean Pasien
            </a>
            <a href="{{ route('clinical.timeline', $visit->id) }}" class="btn btn-outline-info" target="_blank">
                <i class="fas fa-history me-1"></i> RME Lengkap
            </a>

            @if($visit->polyclinic && $visit->polyclinic->type === 'gigi')
                <a href="{{ route('odontogram.show', $visit->id) }}" class="btn btn-info text-white">
                    <i class="fas fa-tooth me-1"></i> Odontogram Gigi
                </a>
            @elseif($visit->polyclinic && $visit->polyclinic->type === 'kia')
                <a href="{{ route('kia.show', $visit->id) }}" class="btn btn-info text-white">
                    <i class="fas fa-baby me-1"></i> Modul Poli KIA
                </a>
            @endif

            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#labOrderModal">
                <i class="fas fa-flask me-1"></i> Order Lab
            </button>

            <a href="{{ route('pharmacy.create_prescription', $visit->id) }}" class="btn btn-outline-warning text-dark">
                <i class="fas fa-pills me-1"></i> Buat E-Resep
            </a>

            <a href="{{ route('documents.create', $visit->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-file-medical me-1"></i> Surat Medis
            </a>
        </div>
    </div>

    <!-- Patient Info Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            {{ substr($visit->patient->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $visit->patient->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $visit->patient->medical_record_number }}</span> | 
                                NIK: {{ $visit->patient->nik ?? '-' }} | 
                                TTL: {{ $visit->patient->place_of_birth ?? '-' }}, {{ $visit->patient->date_of_birth?->format('d/m/Y') }} ({{ $visit->patient->age }} thn) | 
                                JK: {{ $visit->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} (Gol: {{ $visit->patient->blood_type }})
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Alamat:</strong> {{ $visit->patient->full_address }}</div>
                        <div><strong class="text-muted">Penjamin:</strong> <span class="badge bg-secondary text-uppercase">{{ $visit->payment_type }}</span> {{ $visit->insurance_number ? '(' . $visit->insurance_number . ')' : '' }}</div>
                        <div><strong class="text-muted">No. HP:</strong> {{ $visit->patient->phone ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Allergy Alert -->
            @if($visit->patient->allergies && $visit->patient->allergies->count() > 0)
                <div class="alert alert-danger mt-3 mb-0 py-2 d-flex align-items-center" role="alert">
                    <i class="fas fa-hand-dots fs-5 me-2 text-danger"></i>
                    <div>
                        <strong class="text-danger">Peringatan Alergi Pasien:</strong>
                        @foreach($visit->patient->allergies as $alg)
                            <span class="badge bg-danger ms-1">{{ $alg->allergen_name }} ({{ $alg->reaction }})</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Alert if record is locked -->
    @if($record && $record->status === 'final')
        <div class="alert alert-secondary border-0 shadow-sm d-flex align-items-center justify-content-between p-3 mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-lock fa-2x text-secondary me-3"></i>
                <div>
                    <h6 class="fw-bold mb-1 text-dark">Catatan Rekam Medis Ini Telah Difinalisasi & Terkunci Permanen</h6>
                    <div class="small text-muted">
                        Difinalisasi oleh <strong>{{ $record->doctor?->formatted_name ?? 'Dokter' }}</strong> pada {{ $record->locked_at?->format('d/m/Y H:i') }} WIB. 
                        Sesuai standar Permenkes 24/2022, rekam medis yang telah final tidak dapat diubah kembali. Gunakan fitur <strong>Addendum</strong> di bawah ini untuk mencatat koreksi atau catatan tambahan.
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-warning text-dark fw-bold ms-3" data-bs-toggle="modal" data-bs-target="#addendumModal">
                <i class="fas fa-plus-circle me-1"></i> Tambah Addendum
            </button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left Column: Nursing Assessment & Quick Medical History -->
        <div class="col-lg-4">
            <!-- Nursing Assessment Accordion -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-heartbeat me-2"></i>Pengkajian Awal Perawat
                    </h6>
                </div>
                <div class="card-body p-3">
                    @if($visit->nursingAssessment)
                        @php $ns = $visit->nursingAssessment; @endphp
                        <div class="mb-3">
                            <span class="small text-muted">Perawat: {{ $ns->nurse?->formatted_name }} ({{ $ns->assessment_time?->format('H:i') }} WIB)</span>
                        </div>
                        <div class="row g-2 text-center mb-3">
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <div class="small text-muted">Tekanan Darah</div>
                                    <div class="fw-bold {{ $ns->is_blood_pressure_abnormal ? 'text-danger' : 'text-dark' }}">{{ $ns->systolic }}/{{ $ns->diastolic }} mmHg</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <div class="small text-muted">Suhu Tubuh</div>
                                    <div class="fw-bold {{ $ns->is_temperature_abnormal ? 'text-danger' : 'text-dark' }}">{{ $ns->temperature }} °C</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <div class="small text-muted">Denyut Nadi</div>
                                    <div class="fw-bold text-dark">{{ $ns->pulse_rate }} x/mnt</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <div class="small text-muted">Pernapasan</div>
                                    <div class="fw-bold text-dark">{{ $ns->respiratory_rate }} x/mnt</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <div class="small text-muted">BB / TB / IMT</div>
                                    <div class="fw-bold text-dark">{{ $ns->weight }}kg / {{ $ns->height }}cm</div>
                                    <span class="badge bg-info mt-1">{{ $ns->bmi }} ({{ $ns->bmi_category }})</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded bg-light">
                                    <div class="small text-muted">Skala Nyeri</div>
                                    <div class="fw-bold text-dark">{{ $ns->pain_scale }}/10</div>
                                    <span class="badge {{ $ns->pain_scale > 3 ? 'bg-warning text-dark' : 'bg-success' }} mt-1">
                                        {{ $ns->pain_scale > 3 ? 'Nyeri Sedang' : 'Ringan' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="small">
                            <div class="mb-2"><strong>Keluhan Utama:</strong> {{ $ns->chief_complaint }}</div>
                            @if($ns->current_illness_history)
                                <div class="mb-2"><strong>Riw. Penyakit Sekarang:</strong> {{ $ns->current_illness_history }}</div>
                            @endif
                            @if($ns->past_illness_history)
                                <div class="mb-2"><strong>Riw. Penyakit Dahulu:</strong> {{ $ns->past_illness_history }}</div>
                            @endif
                            @if($ns->current_medication)
                                <div class="mb-2"><strong>Riw. Pengobatan:</strong> {{ $ns->current_medication }}</div>
                            @endif
                            <div><strong>Risiko Jatuh:</strong> <span class="badge {{ $ns->fall_risk == 'tinggi' ? 'bg-danger' : 'bg-light text-dark border' }}">{{ ucfirst($ns->fall_risk) }}</span></div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>Belum ada data pengkajian perawat.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Prescriptions & Lab Results for this Visit -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-pills me-2 text-warning"></i>E-Resep & Lab Hari Ini
                    </h6>
                </div>
                <div class="card-body p-3 small">
                    <!-- Prescriptions -->
                    <div class="fw-bold text-dark mb-2"><i class="fas fa-prescription me-1"></i>Resep Pasien:</div>
                    @forelse($visit->prescriptions as $pr)
                        <div class="p-2 border rounded bg-light mb-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $pr->prescription_number }}</strong>
                                <span class="badge {{ $pr->status_badge_class }}">{{ $pr->status_label }}</span>
                            </div>
                            <ul class="mb-0 ps-3 mt-1 text-muted">
                                @foreach($pr->items as $it)
                                    <li>{{ $it->medicine?->brand_name }} ({{ $it->quantity }} {{ $it->medicine?->dosage_form }}) - {{ $it->instructions }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @empty
                        <div class="text-muted fst-italic mb-3">Belum ada resep yang dibuat.</div>
                    @endforelse

                    <!-- Lab Requests -->
                    <div class="fw-bold text-dark mb-2 mt-3"><i class="fas fa-flask me-1 text-primary"></i>Hasil Lab:</div>
                    @forelse($visit->laboratoryRequests as $lr)
                        <div class="p-2 border rounded bg-light mb-2">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $lr->request_number }}</strong>
                                <span class="badge {{ $lr->status_badge_class }}">{{ $lr->status_label }}</span>
                            </div>
                            @if($lr->results->count() > 0)
                                <table class="table table-sm table-borderless mb-0 mt-1">
                                    @foreach($lr->results as $res)
                                        <tr>
                                            <td>{{ $res->laboratory?->name }}</td>
                                            <td class="fw-bold {{ $res->is_critical ? 'text-danger' : 'text-dark' }}">{{ $res->result_value }} {{ $res->unit }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @else
                                <div class="text-muted small mt-1">Sampel sedang diproses di laboratorium.</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted fst-italic">Belum ada pemeriksaan lab.</div>
                    @endforelse
                </div>
            </div>

            <!-- Historical Visits Timeline Accordion -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-history me-2 text-info"></i>Riwayat Kunjungan Sebelumnya
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="timeline-sm">
                        @forelse($timeline as $tVisit)
                            @if($tVisit->id !== $visit->id)
                                <div class="border-bottom pb-2 mb-2">
                                    <div class="d-flex justify-content-between small">
                                        <strong class="text-primary">{{ $tVisit->registration_date->format('d/m/Y') }}</strong>
                                        <span class="badge bg-light text-dark border">{{ $tVisit->polyclinic?->name }}</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        Dr. {{ $tVisit->doctor?->name ?? '-' }}
                                    </div>
                                    @if($tVisit->medicalRecord)
                                        <div class="small text-dark mt-1">
                                            <strong>Dx:</strong> 
                                            @foreach($tVisit->medicalRecord->diagnoses as $d)
                                                <span class="badge bg-secondary">{{ $d->icd10_code }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @empty
                            <div class="text-muted small text-center py-3">Tidak ada riwayat kunjungan lampau.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: SOAP Examination Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-stethoscope me-2"></i>Catatan Pemeriksaan Dokter (SOAP)
                    </h5>
                    @if($record && $record->status !== 'final')
                        <span class="badge bg-warning text-dark"><i class="fas fa-save me-1"></i>Draf Tersimpan</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('clinical.draft', $visit->id) }}" method="POST" id="soapForm">
                        @csrf

                        <fieldset {{ ($record && $record->status === 'final') ? 'disabled' : '' }}>
                            <!-- Subjective -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">
                                    <i class="fas fa-comment-medical me-1"></i>S - Subjective (Keluhan Pasien, Anamnesis Terarah) <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('subjective') is-invalid @enderror" 
                                          name="subjective" rows="3" required 
                                          placeholder="Uraikan keluhan utama, riwayat perjalanan penyakit, riwayat alergi yang dikonfirmasi...">{{ old('subjective', $record?->subjective ?? $visit->nursingAssessment?->chief_complaint) }}</textarea>
                                @error('subjective')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Objective -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">
                                    <i class="fas fa-stethoscope me-1"></i>O - Objective (Pemeriksaan Fisik Dokter & Status Lokalis) <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('objective') is-invalid @enderror" 
                                          name="objective" rows="3" required 
                                          placeholder="Temuan fisik: kepala/leher, thoraks, abdomen, ekstremitas, status lokalis...">{{ old('objective', $record?->objective ?? ('Keadaan umum: Baik. TTV: TD ' . ($visit->nursingAssessment?->systolic ?? 120) . '/' . ($visit->nursingAssessment?->diastolic ?? 80) . ' mmHg, Nadi ' . ($visit->nursingAssessment?->pulse_rate ?? 80) . 'x/m, Suhu ' . ($visit->nursingAssessment?->temperature ?? 36.5) . 'C.')) }}</textarea>
                                @error('objective')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Assessment -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">
                                    <i class="fas fa-clipboard-check me-1"></i>A - Assessment (Diagnosis Kerja & Diagnosis Banding) <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('assessment') is-invalid @enderror" 
                                          name="assessment" rows="2" required 
                                          placeholder="Uraian diagnosis klinis...">{{ old('assessment', $record?->assessment) }}</textarea>
                                @error('assessment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Structured ICD-10 Selection -->
                                <div class="mt-3 p-3 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-bold mb-0 text-dark">
                                            <i class="fas fa-book-medical me-1 text-primary"></i>Kodifikasi Diagnosis ICD-10 Terstruktur (Permenkes 24/2022)
                                        </label>
                                        @if(!$record || $record->status !== 'final')
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddDiagnosis">
                                                <i class="fas fa-plus me-1"></i>Tambah Baris ICD-10
                                            </button>
                                        @endif
                                    </div>
                                    <div id="diagnosesContainer">
                                        @php
                                            $existingDiagnoses = $record ? $record->diagnoses : collect();
                                        @endphp
                                        @forelse($existingDiagnoses as $idx => $diag)
                                            <div class="row g-2 align-items-center mb-2 diagnosis-row">
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm" name="diagnoses[{{ $idx }}][type]">
                                                        <option value="primary" {{ $diag->type === 'primary' ? 'selected' : '' }}>Primer (Utama)</option>
                                                        <option value="secondary" {{ $diag->type === 'secondary' ? 'selected' : '' }}>Sekunder (Penyerta)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <select class="form-select form-select-sm select2-icd10" name="diagnoses[{{ $idx }}][code]">
                                                        <option value="{{ $diag->icd10_code }}" selected>[{{ $diag->icd10_code }}] {{ $diag->icd10_description }}</option>
                                                    </select>
                                                </div>
                                                @if(!$record || $record->status !== 'final')
                                                    <div class="col-md-1 text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="row g-2 align-items-center mb-2 diagnosis-row">
                                                <div class="col-md-3">
                                                    <select class="form-select form-select-sm" name="diagnoses[0][type]">
                                                        <option value="primary" selected>Primer (Utama)</option>
                                                        <option value="secondary">Sekunder (Penyerta)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-8">
                                                    <select class="form-select form-select-sm select2-icd10" name="diagnoses[0][code]">
                                                        <option value="">-- Ketik Kode atau Nama Diagnosis ICD-10 --</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-1 text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Plan -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary">
                                    <i class="fas fa-tasks me-1"></i>P - Plan (Instruksi Medis, Terapi, & Penatalaksanaan) <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('plan') is-invalid @enderror" 
                                          name="plan" rows="3" required 
                                          placeholder="Uraikan rencana terapi, obat-obatan, diet, edukasi, atau rencana rujukan...">{{ old('plan', $record?->plan) }}</textarea>
                                @error('plan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <!-- Structured ICD-9-CM Procedures Selection -->
                                <div class="mt-3 p-3 border rounded bg-light">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-bold mb-0 text-dark">
                                            <i class="fas fa-syringe me-1 text-primary"></i>Tindakan / Prosedur Medis ICD-9-CM (Snapshot Tarif)
                                        </label>
                                        @if(!$record || $record->status !== 'final')
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddProcedure">
                                                <i class="fas fa-plus me-1"></i>Tambah Baris Tindakan
                                            </button>
                                        @endif
                                    </div>
                                    <div id="proceduresContainer">
                                        @php
                                            $existingProcedures = $record ? $record->procedures : collect();
                                        @endphp
                                        @forelse($existingProcedures as $pIdx => $proc)
                                            <div class="row g-2 align-items-center mb-2 procedure-row">
                                                <div class="col-md-11">
                                                    <select class="form-select form-select-sm select2-icd9" name="procedures[{{ $pIdx }}][code]">
                                                        <option value="{{ $proc->icd9cm_code }}" selected>[{{ $proc->icd9cm_code }}] {{ $proc->icd9cm_description }} (Tarif: Rp{{ number_format($proc->tariff, 0, ',', '.') }})</option>
                                                    </select>
                                                </div>
                                                @if(!$record || $record->status !== 'final')
                                                    <div class="col-md-1 text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="row g-2 align-items-center mb-2 procedure-row">
                                                <div class="col-md-11">
                                                    <select class="form-select form-select-sm select2-icd9" name="procedures[0][code]">
                                                        <option value="">-- Ketik Kode atau Nama Tindakan ICD-9-CM --</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-1 text-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Clinical Fields -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Rencana Edukasi Pasien</label>
                                    <textarea class="form-control" name="education_plan" rows="2" 
                                              placeholder="Edukasi diet, istirahat, cara minum obat...">{{ old('education_plan', $record?->education_plan) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Rencana Tindak Lanjut / Kontrol</label>
                                    <textarea class="form-control" name="follow_up_plan" rows="2" 
                                              placeholder="Rencana kontrol ulang atau evaluasi lab...">{{ old('follow_up_plan', $record?->follow_up_plan) }}</textarea>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tanggal Kontrol Berikutnya (Jika Ada)</label>
                                    <input type="date" class="form-control" name="next_visit_date" 
                                           value="{{ old('next_visit_date', $record?->next_visit_date?->format('Y-m-d')) }}">
                                </div>
                            </div>
                        </fieldset>

                        @if(!$record || $record->status !== 'final')
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-primary px-4 fw-bold">
                                    <i class="fas fa-save me-2"></i>Simpan Draf Pemeriksaan (SOAP)
                                </button>
                                @if($record)
                                    <button type="button" class="btn btn-success px-4 fw-bold" id="btnFinalizeRecord">
                                        <i class="fas fa-lock me-2"></i>Finalisasi & Kunci Rekam Medis
                                    </button>
                                @endif
                            </div>
                        @endif
                    </form>

                    <!-- Hidden Finalize Form -->
                    @if($record && $record->status !== 'final')
                        <form id="finalizeForm" action="{{ route('clinical.finalize', $record->id) }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @endif
                </div>
            </div>

            <!-- Addendums Section (Visible if finalized) -->
            @if($record && $record->status === 'final')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-pencil-alt me-2 text-warning"></i>Catatan Addendum Rekam Medis
                        </h5>
                        <button type="button" class="btn btn-sm btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#addendumModal">
                            <i class="fas fa-plus me-1"></i>Buat Addendum Baru
                        </button>
                    </div>
                    <div class="card-body p-4">
                        @forelse($record->addendums as $add)
                            <div class="border rounded p-3 bg-light mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">
                                    <div class="small fw-bold text-primary">
                                        <i class="fas fa-user-md me-1"></i>{{ $add->doctor?->formatted_name ?? 'Dokter' }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $add->created_at->format('d/m/Y H:i:s') }} WIB
                                    </div>
                                </div>
                                <div class="small text-muted mb-1"><strong>Alasan Addendum:</strong> {{ $add->reason }}</div>
                                <div class="text-dark bg-white p-2 rounded border">{{ $add->addendum_content }}</div>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3">Belum ada addendum yang dicatat untuk rekam medis ini.</div>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Laboratory Order Modal -->
<div class="modal fade" id="labOrderModal" tabindex="-1" aria-labelledby="labOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('laboratory.store_request', $visit->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="labOrderModalLabel"><i class="fas fa-flask me-2"></i>Order Pemeriksaan Laboratorium</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Pilih pemeriksaan laboratorium yang diperlukan untuk pasien <strong>{{ $visit->patient->name }}</strong>:</p>

                    @php
                        $availableLabs = \App\Models\Laboratory::where('is_active', true)->get();
                    @endphp

                    <div class="mb-3">
                        <label class="form-label fw-bold">Pemeriksaan Lab <span class="text-danger">*</span></label>
                        <div class="row g-2 border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                            @foreach($availableLabs as $lab)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="tests[]" value="{{ $lab->id }}" id="lab_{{ $lab->id }}">
                                        <label class="form-check-label small" for="lab_{{ $lab->id }}">
                                            <strong>{{ $lab->name }}</strong> (Rp{{ number_format($lab->tariff, 0, ',', '.') }})
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Klinis untuk Analis Lab</label>
                        <textarea class="form-control" name="clinical_notes" rows="2" placeholder="Contoh: Curiga DHF hari ke-3, periksa trombosit segera..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-paper-plane me-1"></i>Kirim Permintaan Lab</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Addendum Modal -->
@if($record && $record->status === 'final')
<div class="modal fade" id="addendumModal" tabindex="-1" aria-labelledby="addendumModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('clinical.addendum', $record->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="addendumModalLabel"><i class="fas fa-pencil-alt me-2"></i>Tambah Addendum Rekam Medis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        <i class="fas fa-info-circle me-1"></i> Addendum adalah catatan koreksi atau penjelasan tambahan resmi atas rekam medis yang telah terkunci.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan Penambahan Addendum <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="reason" required placeholder="Contoh: Penyesuaian hasil lab lanjutan atau klarifikasi anamnesis...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Isi Catatan Addendum <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="addendum_content" rows="4" required placeholder="Uraikan catatan medis tambahan secara lengkap..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fas fa-save me-1"></i>Simpan Catatan Addendum</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var diagIndex = {{ $existingDiagnoses->count() > 0 ? $existingDiagnoses->count() : 1 }};
    var procIndex = {{ $existingProcedures->count() > 0 ? $existingProcedures->count() : 1 }};

    function initSelect2Icd10() {
        $('.select2-icd10').select2({
            theme: 'bootstrap-5',
            placeholder: 'Ketik Kode / Deskripsi ICD-10...',
            minimumInputLength: 2,
            ajax: {
                url: '{{ route('clinical.icd10.ajax') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data.results };
                },
                cache: true
            }
        });
    }

    function initSelect2Icd9() {
        $('.select2-icd9').select2({
            theme: 'bootstrap-5',
            placeholder: 'Ketik Kode / Deskripsi ICD-9-CM...',
            minimumInputLength: 2,
            ajax: {
                url: '{{ route('clinical.icd9.ajax') }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data.results };
                },
                cache: true
            }
        });
    }

    initSelect2Icd10();
    initSelect2Icd9();

    // Add Diagnosis Row
    $('#btnAddDiagnosis').on('click', function() {
        var html = `
            <div class="row g-2 align-items-center mb-2 diagnosis-row">
                <div class="col-md-3">
                    <select class="form-select form-select-sm" name="diagnoses[${diagIndex}][type]">
                        <option value="secondary">Sekunder (Penyerta)</option>
                        <option value="primary">Primer (Utama)</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <select class="form-select form-select-sm select2-icd10" name="diagnoses[${diagIndex}][code]">
                        <option value="">-- Ketik Kode atau Nama Diagnosis ICD-10 --</option>
                    </select>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#diagnosesContainer').append(html);
        diagIndex++;
        initSelect2Icd10();
    });

    // Add Procedure Row
    $('#btnAddProcedure').on('click', function() {
        var html = `
            <div class="row g-2 align-items-center mb-2 procedure-row">
                <div class="col-md-11">
                    <select class="form-select form-select-sm select2-icd9" name="procedures[${procIndex}][code]">
                        <option value="">-- Ketik Kode atau Nama Tindakan ICD-9-CM --</option>
                    </select>
                </div>
                <div class="col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#proceduresContainer').append(html);
        procIndex++;
        initSelect2Icd9();
    });

    // Remove dynamic row
    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('.row').remove();
    });

    // Finalize SweetAlert2 confirmation
    $('#btnFinalizeRecord').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Kunci & Finalisasi Rekam Medis?',
            text: 'Setelah difinalisasi, catatan medis SOAP tidak dapat diedit kembali (hanya dapat ditambahkan Addendum). Pastikan semua data diagnosis dan tindakan telah benar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Finalisasi & Kunci',
            cancelButtonText: 'Periksa Kembali'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#finalizeForm').submit();
            }
        });
    });
});
</script>
@endpush
