@extends('layouts.app')

@section('title', 'Kronologis Rekam Medis (EMR Timeline) - ' . $visit->patient->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Kronologis Rekam Medis Pasien (EMR Timeline)</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('patients.show', $visit->patient_id) }}">{{ $visit->patient->name }}</a></li>
                    <li class="breadcrumb-item active">Riwayat Klinis Longitudinal</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Kembali ke Pemeriksaan
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Cetak Riwayat
            </button>
        </div>
    </div>

    <!-- Patient Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $visit->patient->name }}</h5>
                    <div class="text-muted small">
                        No. RM: <span class="badge bg-primary">{{ $visit->patient->medical_record_number }}</span> | 
                        NIK: {{ $visit->patient->nik ?? '-' }} | 
                        TTL: {{ $visit->patient->place_of_birth ?? '-' }}, {{ $visit->patient->date_of_birth?->format('d/m/Y') }} ({{ $visit->patient->age }} thn) | 
                        Gol. Darah: <strong>{{ $visit->patient->blood_type }}</strong>
                    </div>
                </div>
                <div>
                    @if($visit->patient->allergies->count() > 0)
                        <div class="badge bg-danger p-2">
                            <i class="fas fa-hand-dots me-1"></i> Riwayat Alergi: 
                            @foreach($visit->patient->allergies as $alg)
                                {{ $alg->allergen_name }} ({{ $alg->reaction }}){{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </div>
                    @else
                        <span class="badge bg-light text-success border p-2"><i class="fas fa-check-circle me-1"></i> Tidak Ada Riwayat Alergi</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Wrapper -->
    <div class="timeline-container">
        @forelse($timeline as $v)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <span class="badge bg-primary fs-6 me-2">{{ $v->registration_date->format('d F Y') }}</span>
                        <strong class="text-dark">{{ $v->visit_number }}</strong> | 
                        Poli: <strong>{{ $v->polyclinic?->name }}</strong> | 
                        Dokter: <strong>{{ $v->doctor?->formatted_name }}</strong>
                    </div>
                    <div>
                        <span class="badge {{ $v->status_badge_class }}">{{ $v->status_label }}</span>
                        @if($v->medicalRecord && $v->medicalRecord->status === 'final')
                            <span class="badge bg-success ms-1"><i class="fas fa-lock me-1"></i>Final</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Vital Signs if available -->
                    @if($v->nursingAssessment)
                        @php $ns = $v->nursingAssessment; @endphp
                        <div class="p-3 border rounded bg-light mb-3">
                            <div class="fw-bold small text-muted mb-1"><i class="fas fa-heartbeat me-1 text-danger"></i>Tanda-Tanda Vital & Pengkajian:</div>
                            <div class="d-flex flex-wrap gap-3 small">
                                <div><strong>TD:</strong> {{ $ns->systolic }}/{{ $ns->diastolic }} mmHg</div>
                                <div><strong>Suhu:</strong> {{ $ns->temperature }}°C</div>
                                <div><strong>Nadi:</strong> {{ $ns->pulse_rate }} x/mnt</div>
                                <div><strong>RR:</strong> {{ $ns->respiratory_rate }} x/mnt</div>
                                <div><strong>IMT:</strong> {{ $ns->bmi }} ({{ $ns->bmi_category }})</div>
                                <div><strong>Keluhan Utama:</strong> {{ $ns->chief_complaint }}</div>
                            </div>
                        </div>
                    @endif

                    <!-- SOAP Details -->
                    @if($v->medicalRecord)
                        @php $mr = $v->medicalRecord; @endphp
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-light h-100">
                                    <div class="fw-bold text-primary small"><i class="fas fa-comment-medical me-1"></i>Subjective:</div>
                                    <div class="small text-dark">{{ $mr->subjective }}</div>
                                    <div class="fw-bold text-primary small mt-2"><i class="fas fa-stethoscope me-1"></i>Objective:</div>
                                    <div class="small text-dark">{{ $mr->objective }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-light h-100">
                                    <div class="fw-bold text-primary small"><i class="fas fa-clipboard-check me-1"></i>Assessment (Diagnosis):</div>
                                    <div class="small text-dark mb-2">{{ $mr->assessment }}</div>
                                    @foreach($mr->diagnoses as $diag)
                                        <div class="small">
                                            <span class="badge {{ $diag->type === 'primary' ? 'bg-danger' : 'bg-secondary' }}">{{ ucfirst($diag->type) }}</span>
                                            <strong>[{{ $diag->icd10_code }}]</strong> {{ $diag->icd10_description }}
                                        </div>
                                    @endforeach

                                    <div class="fw-bold text-primary small mt-3"><i class="fas fa-tasks me-1"></i>Plan:</div>
                                    <div class="small text-dark mb-2">{{ $mr->plan }}</div>
                                    @foreach($mr->procedures as $proc)
                                        <div class="small">
                                            <span class="badge bg-info text-dark">Tindakan</span>
                                            <strong>[{{ $proc->icd9cm_code }}]</strong> {{ $proc->icd9cm_description }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Addendums if any -->
                        @if($mr->addendums && $mr->addendums->count() > 0)
                            <div class="alert alert-warning py-2 mb-3 small">
                                <div class="fw-bold"><i class="fas fa-pencil-alt me-1"></i>Catatan Addendum:</div>
                                @foreach($mr->addendums as $add)
                                    <div class="mt-1">
                                        <em>[{{ $add->created_at->format('d/m/Y H:i') }} oleh {{ $add->doctor?->name }} - Alasan: {{ $add->reason }}]:</em> 
                                        {{ $add->addendum_content }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif

                    <!-- Prescriptions -->
                    @if($v->prescriptions && $v->prescriptions->count() > 0)
                        <div class="border rounded p-3 bg-light mb-3">
                            <div class="fw-bold small text-muted mb-1"><i class="fas fa-pills me-1 text-warning"></i>Terapi Obat / E-Resep:</div>
                            @foreach($v->prescriptions as $p)
                                <div class="small">
                                    <strong>{{ $p->prescription_number }}:</strong>
                                    @foreach($p->items as $pi)
                                        {{ $pi->medicine?->brand_name }} ({{ $pi->quantity }} {{ $pi->medicine?->dosage_form }}) - {{ $pi->dosage }} ({{ $pi->instructions }}){{ !$loop->last ? '; ' : '' }}
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Lab Results -->
                    @if($v->laboratoryRequests && $v->laboratoryRequests->count() > 0)
                        <div class="border rounded p-3 bg-light">
                            <div class="fw-bold small text-muted mb-1"><i class="fas fa-flask me-1 text-primary"></i>Hasil Pemeriksaan Laboratorium:</div>
                            @foreach($v->laboratoryRequests as $lr)
                                @foreach($lr->results as $res)
                                    <div class="small">
                                        <strong>{{ $res->laboratory?->name }}:</strong> 
                                        <span class="{{ $res->is_critical ? 'fw-bold text-danger' : 'text-dark' }}">{{ $res->result_value }} {{ $res->unit }}</span> 
                                        <span class="text-muted">(Rujukan: {{ $res->reference_range ?? '-' }})</span>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm p-5 text-center text-muted">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <h6>Belum ada riwayat rekam medis untuk pasien ini.</h6>
            </div>
        @endforelse
    </div>
</div>
@endsection
