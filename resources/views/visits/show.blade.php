@extends('layouts.app')

@section('title', 'Detail Kunjungan - ' . $visit->visit_number)
@section('page_title', 'Detail Pelayanan Kunjungan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('visits.index') }}">Kunjungan</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $visit->visit_number }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Kunjungan: {{ $visit->visit_number }}</h4>
                <span class="badge {{ $visit->status_badge_class }} fs-6 px-3 py-2">
                    {{ $visit->status_label }}
                </span>
            </div>
            <div class="text-muted small mt-1">
                No. Antrean: <span class="fw-bold text-primary fs-6">{{ $visit->queue_number }}</span> | 
                Waktu Registrasi: {{ $visit->registration_date->format('d/m/Y H:i') }} WIB | 
                Petugas Admisi: {{ $visit->registeredByUser?->name ?? 'Sistem' }}
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Antrean
            </a>

            <!-- Quick Clinical Actions -->
            @if(auth()->user()->hasAnyRole(['perawat_bidan', 'kepala_klinik']))
                <a href="{{ route('nursing.create', $visit->id) }}" class="btn btn-primary">
                    <i class="fas fa-user-nurse me-1"></i> Pengkajian Awal
                </a>
            @endif

            @if(auth()->user()->hasAnyRole(['dokter', 'kepala_klinik']))
                <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-success">
                    <i class="fas fa-stethoscope me-1"></i> Pemeriksaan Dokter (SOAP)
                </a>
            @endif

            @if($visit->billingInvoice && auth()->user()->hasAnyRole(['admisi_kasir', 'kepala_klinik']))
                <a href="{{ route('billing.show', $visit->billingInvoice->id) }}" class="btn btn-warning text-dark">
                    <i class="fas fa-cash-register me-1"></i> Kasir / Tagihan
                </a>
            @endif

            <!-- Update Status Dropdown -->
            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-exchange-alt me-1"></i> Ganti Status
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header">Ubah Status Kunjungan</h6></li>
                @php
                    $allStatuses = [
                        'terdaftar' => 'Terdaftar (Admisi)',
                        'menunggu_pemeriksaan_awal' => 'Menunggu Pemeriksaan Awal',
                        'pemeriksaan_awal_berlangsung' => 'Pemeriksaan Awal Berlangsung',
                        'menunggu_dokter' => 'Menunggu Dokter',
                        'pemeriksaan_dokter_berlangsung' => 'Pemeriksaan Dokter Berlangsung',
                        'menunggu_pemeriksaan_penunjang' => 'Menunggu Lab / Penunjang',
                        'menunggu_farmasi' => 'Menunggu Farmasi',
                        'menunggu_pembayaran' => 'Menunggu Pembayaran',
                        'selesai' => 'Pelayanan Selesai',
                    ];
                @endphp
                @foreach($allStatuses as $stCode => $stLabel)
                    @if($visit->status !== $stCode)
                        <li>
                            <form action="{{ route('visits.status', $visit->id) }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="status" value="{{ $stCode }}">
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-arrow-right text-muted me-2"></i>{{ $stLabel }}
                                </button>
                            </form>
                        </li>
                    @endif
                @endforeach
                @if($visit->status !== 'dibatalkan' && $visit->status !== 'selesai')
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="fas fa-ban me-2"></i>Batalkan Kunjungan
                        </button>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Patient Banner Card -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-id-card me-2"></i>Informasi Pasien
                    </h5>
                    <a href="{{ route('patients.show', $visit->patient_id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                        <i class="fas fa-external-link-alt me-1"></i>Profil Lengkap Pasien
                    </a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 140px;">No. Rekam Medis:</td>
                                    <td><span class="badge bg-primary fs-6">{{ $visit->patient->medical_record_number }}</span></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Nama Lengkap:</td>
                                    <td class="fw-bold text-dark fs-6">{{ $visit->patient->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">NIK:</td>
                                    <td>{{ $visit->patient->nik ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">TTL / Usia:</td>
                                    <td>{{ $visit->patient->place_of_birth ?? '-' }}, {{ $visit->patient->date_of_birth?->format('d/m/Y') }} ({{ $visit->patient->age }} tahun)</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Jenis Kelamin:</td>
                                    <td>{{ $visit->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} (Gol. Darah: {{ $visit->patient->blood_type }})</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6 border-start-md">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 130px;">Poli Tujuan:</td>
                                    <td class="fw-bold text-primary">{{ $visit->polyclinic?->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Dokter:</td>
                                    <td class="fw-bold text-dark">{{ $visit->doctor?->formatted_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Penjamin:</td>
                                    <td>
                                        <span class="badge bg-secondary text-uppercase">{{ $visit->payment_type }}</span>
                                        @if($visit->insurance_number)
                                            <span class="small ms-1">({{ $visit->insurance_number }})</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">No. Telepon:</td>
                                    <td>{{ $visit->patient->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Alamat:</td>
                                    <td class="small">{{ $visit->patient->full_address }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Allergy Alert Banner -->
                    @if($visit->patient->allergies && $visit->patient->allergies->count() > 0)
                        <div class="alert alert-danger mt-3 mb-0 d-flex align-items-center" role="alert">
                            <i class="fas fa-hand-dots fs-4 me-3 text-danger"></i>
                            <div>
                                <strong class="text-danger">Peringatan Alergi Pasien:</strong>
                                @foreach($visit->patient->allergies as $alg)
                                    <span class="badge bg-danger ms-1">{{ $alg->allergen_name }} ({{ $alg->allergen_type }}: {{ $alg->reaction }})</span>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="alert alert-success mt-3 mb-0 py-2 d-flex align-items-center small" role="alert">
                            <i class="fas fa-check-circle me-2 text-success"></i> Tidak ada riwayat alergi yang tercatat pada pasien ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Visit Progress & Fast Links -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-th-list text-primary me-2"></i>Akses Modul Terkait
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="list-group list-group-flush">
                        <!-- Nursing Assessment -->
                        <a href="{{ route('nursing.create', $visit->id) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 text-primary me-3">
                                    <i class="fas fa-user-nurse fa-fw"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Pengkajian Perawat</div>
                                    <div class="small text-muted">
                                        {{ $visit->nursingAssessment ? 'Sudah diisi (' . $visit->nursingAssessment->nurse?->formatted_name . ')' : 'Belum diisi' }}
                                    </div>
                                </div>
                            </div>
                            <span class="badge {{ $visit->nursingAssessment ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $visit->nursingAssessment ? 'Selesai' : 'Belum' }}
                            </span>
                        </a>

                        <!-- Clinical Doctor -->
                        <a href="{{ route('clinical.examination', $visit->id) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 text-success me-3">
                                    <i class="fas fa-stethoscope fa-fw"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Rekam Medis (SOAP)</div>
                                    <div class="small text-muted">
                                        {{ $visit->medicalRecord ? ($visit->medicalRecord->status === 'final' ? 'Terkunci (Final)' : 'Draf Pemeriksaan') : 'Belum diperiksa' }}
                                    </div>
                                </div>
                            </div>
                            <span class="badge {{ $visit->medicalRecord ? ($visit->medicalRecord->status === 'final' ? 'bg-success' : 'bg-info') : 'bg-secondary' }}">
                                {{ $visit->medicalRecord ? ($visit->medicalRecord->status === 'final' ? 'Final' : 'Draf') : 'Kosong' }}
                            </span>
                        </a>

                        <!-- Polyclinic Specialty Modules -->
                        @if($visit->polyclinic && $visit->polyclinic->type === 'gigi')
                            <a href="{{ route('odontogram.show', $visit->id) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 text-info me-3">
                                        <i class="fas fa-tooth fa-fw"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">Odontogram Gigi</div>
                                        <div class="small text-muted">Peta Gigi Dewasa & Anak</div>
                                    </div>
                                </div>
                                <span class="badge bg-primary"><i class="fas fa-chevron-right"></i></span>
                            </a>
                        @elseif($visit->polyclinic && $visit->polyclinic->type === 'kia')
                            <a href="{{ route('kia.show', $visit->id) }}" class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-2 text-info me-3">
                                        <i class="fas fa-baby fa-fw"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">Poli KIA (ANC & Anak)</div>
                                        <div class="small text-muted">Pemeriksaan Bumil & Tumbuh Kembang</div>
                                    </div>
                                </div>
                                <span class="badge bg-primary"><i class="fas fa-chevron-right"></i></span>
                            </a>
                        @endif

                        <!-- Prescriptions -->
                        <div class="list-group-item p-3 border rounded mb-2 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 text-warning me-3">
                                    <i class="fas fa-pills fa-fw"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Resep Farmasi</div>
                                    <div class="small text-muted">{{ $visit->prescriptions->count() }} Resep Diterbitkan</div>
                                </div>
                            </div>
                            @if($visit->prescriptions->count() > 0)
                                <a href="{{ route('pharmacy.show_prescription', $visit->prescriptions->first()->id) }}" class="btn btn-sm btn-outline-primary">Lihat Resep</a>
                            @else
                                <span class="badge bg-light text-dark">Belum Ada</span>
                            @endif
                        </div>

                        <!-- Invoices -->
                        <div class="list-group-item p-3 border rounded d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-light p-2 text-danger me-3">
                                    <i class="fas fa-file-invoice-dollar fa-fw"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">Billing & Pembayaran</div>
                                    <div class="small text-muted">
                                        @if($visit->billingInvoice)
                                            Tagihan: Rp{{ number_format($visit->billingInvoice->final_amount, 0, ',', '.') }}
                                        @else
                                            Belum Digenerate
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($visit->billingInvoice)
                                <a href="{{ route('billing.show', $visit->billingInvoice->id) }}" class="btn btn-sm btn-outline-primary">Lihat Kasir</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Sections (Tabs) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 px-3 pt-2" id="visitDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold py-3" id="ttv-tab" data-bs-toggle="tab" data-bs-target="#ttv" type="button" role="tab">
                        <i class="fas fa-heartbeat me-2"></i>Pengkajian & TTV Perawat
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3" id="soap-tab" data-bs-toggle="tab" data-bs-target="#soap" type="button" role="tab">
                        <i class="fas fa-file-medical me-2"></i>Catatan SOAP Dokter
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3" id="pharmacy-tab" data-bs-toggle="tab" data-bs-target="#pharmacy" type="button" role="tab">
                        <i class="fas fa-prescription me-2"></i>Resep Elektronik ({{ $visit->prescriptions->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3" id="lab-tab" data-bs-toggle="tab" data-bs-target="#lab" type="button" role="tab">
                        <i class="fas fa-flask me-2"></i>Hasil Laboratorium ({{ $visit->laboratoryRequests->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold py-3" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                        <i class="fas fa-history me-2"></i>Histori Status Kunjungan
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="visitDetailTabsContent">
                <!-- TTV & Nursing Tab -->
                <div class="tab-pane fade show active" id="ttv" role="tabpanel">
                    @if($visit->nursingAssessment)
                        @php $ns = $visit->nursingAssessment; @endphp
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Data Pengkajian Awal Keperawatan</h6>
                                <span class="small text-muted">Dicatat oleh: {{ $ns->nurse?->formatted_name ?? 'Perawat' }} pada {{ $ns->assessment_time?->format('d/m/Y H:i') }} WIB</span>
                            </div>
                            @if(auth()->user()->hasAnyRole(['perawat_bidan', 'kepala_klinik']))
                                <a href="{{ route('nursing.create', $visit->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Edit Pengkajian
                                </a>
                            @endif
                        </div>

                        <!-- Vital Signs Grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-2">
                                <div class="p-3 border rounded bg-light text-center">
                                    <div class="text-muted small">Tekanan Darah</div>
                                    <div class="fs-5 fw-bold text-dark">{{ $ns->systolic }}/{{ $ns->diastolic }} <span class="small text-muted fs-6">mmHg</span></div>
                                    @if($ns->is_blood_pressure_abnormal)
                                        <span class="badge bg-danger mt-1">Abnormal</span>
                                    @else
                                        <span class="badge bg-success mt-1">Normal</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-3 border rounded bg-light text-center">
                                    <div class="text-muted small">Suhu Tubuh</div>
                                    <div class="fs-5 fw-bold text-dark">{{ $ns->temperature }} <span class="small text-muted fs-6">°C</span></div>
                                    @if($ns->is_temperature_abnormal)
                                        <span class="badge bg-danger mt-1">Demam</span>
                                    @else
                                        <span class="badge bg-success mt-1">Normal</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-3 border rounded bg-light text-center">
                                    <div class="text-muted small">Denyut Nadi</div>
                                    <div class="fs-5 fw-bold text-dark">{{ $ns->pulse_rate }} <span class="small text-muted fs-6">x/mnt</span></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-3 border rounded bg-light text-center">
                                    <div class="text-muted small">Pernapasan</div>
                                    <div class="fs-5 fw-bold text-dark">{{ $ns->respiratory_rate }} <span class="small text-muted fs-6">x/mnt</span></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-3 border rounded bg-light text-center">
                                    <div class="text-muted small">Saturasi O2</div>
                                    <div class="fs-5 fw-bold text-dark">{{ $ns->oxygen_saturation ?? '-' }} <span class="small text-muted fs-6">%</span></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-3 border rounded bg-light text-center">
                                    <div class="text-muted small">IMT / Status Gizi</div>
                                    <div class="fs-5 fw-bold text-dark">{{ $ns->bmi ?? '-' }}</div>
                                    <span class="badge bg-info mt-1">{{ $ns->bmi_category }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th class="bg-light" style="width: 160px;">Keluhan Utama</th>
                                        <td>{{ $ns->chief_complaint }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Riw. Penyakit Sekarang</th>
                                        <td>{{ $ns->current_illness_history ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Riw. Penyakit Dahulu</th>
                                        <td>{{ $ns->past_illness_history ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Riw. Pengobatan</th>
                                        <td>{{ $ns->current_medication ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <tr>
                                        <th class="bg-light" style="width: 160px;">Kesadaran</th>
                                        <td class="text-capitalize">{{ str_replace('_', ' ', $ns->consciousness) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Skala Nyeri (0-10)</th>
                                        <td><span class="fw-bold">{{ $ns->pain_scale }}/10</span> ({{ $ns->pain_scale > 3 ? 'Nyeri Sedang-Berat' : 'Ringan/Tidak Nyeri' }})</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Risiko Jatuh</th>
                                        <td><span class="badge {{ $ns->fall_risk == 'tinggi' ? 'bg-danger' : ($ns->fall_risk == 'sedang' ? 'bg-warning text-dark' : 'bg-success') }}">{{ ucfirst($ns->fall_risk) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Rencana Keperawatan</th>
                                        <td>{{ $ns->nursing_care_plan ?? '-' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-notes-medical fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Pengkajian awal perawat belum dilakukan untuk kunjungan ini.</h6>
                            @if(auth()->user()->hasAnyRole(['perawat_bidan', 'kepala_klinik']))
                                <a href="{{ route('nursing.create', $visit->id) }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Lakukan Pengkajian Awal Sekarang
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- SOAP Doctor Tab -->
                <div class="tab-pane fade" id="soap" role="tabpanel">
                    @if($visit->medicalRecord)
                        @php $mr = $visit->medicalRecord; @endphp
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Catatan Medis Rawat Jalan (SOAP)</h6>
                                <span class="small text-muted">
                                    Dokter: {{ $mr->doctor?->formatted_name ?? '-' }} | 
                                    Status: <span class="badge {{ $mr->status === 'final' ? 'bg-success' : 'bg-warning text-dark' }}">{{ strtoupper($mr->status) }}</span>
                                    @if($mr->is_locked)
                                        | Terkunci: {{ $mr->locked_at?->format('d/m/Y H:i') }} WIB
                                    @endif
                                </span>
                            </div>
                            @if(auth()->user()->hasAnyRole(['dokter', 'kepala_klinik']))
                                <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-stethoscope me-1"></i>Buka Ruang Pemeriksaan
                                </a>
                            @endif
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-light mb-3">
                                    <div class="fw-bold text-primary mb-1"><i class="fas fa-comment-medical me-1"></i>S - Subjective (Keluhan & Anamnesis)</div>
                                    <div class="text-dark">{{ $mr->subjective }}</div>
                                </div>
                                <div class="border rounded p-3 bg-light">
                                    <div class="fw-bold text-primary mb-1"><i class="fas fa-stethoscope me-1"></i>O - Objective (Pemeriksaan Fisik)</div>
                                    <div class="text-dark">{{ $mr->objective }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 bg-light mb-3">
                                    <div class="fw-bold text-primary mb-1"><i class="fas fa-clipboard-check me-1"></i>A - Assessment (Diagnosis)</div>
                                    <div class="text-dark mb-2">{{ $mr->assessment }}</div>
                                    <!-- Structured Diagnoses -->
                                    <div class="fw-bold small text-muted">Diagnosis ICD-10 Terstruktur:</div>
                                    @forelse($mr->diagnoses as $diag)
                                        <div class="small">
                                            <span class="badge {{ $diag->type === 'primary' ? 'bg-danger' : 'bg-secondary' }}">{{ ucfirst($diag->type) }}</span>
                                            <strong>[{{ $diag->icd10_code }}]</strong> {{ $diag->icd10_description }}
                                        </div>
                                    @empty
                                        <div class="small text-muted fst-italic">Belum ada diagnosis terstruktur.</div>
                                    @endforelse
                                </div>
                                <div class="border rounded p-3 bg-light">
                                    <div class="fw-bold text-primary mb-1"><i class="fas fa-tasks me-1"></i>P - Plan (Penatalaksanaan & Tindakan)</div>
                                    <div class="text-dark mb-2">{{ $mr->plan }}</div>
                                    @if($mr->procedures && $mr->procedures->count() > 0)
                                        <div class="fw-bold small text-muted">Tindakan ICD-9-CM Terstruktur:</div>
                                        @foreach($mr->procedures as $proc)
                                            <div class="small">
                                                <span class="badge bg-info text-dark">Tindakan</span>
                                                <strong>[{{ $proc->icd9cm_code }}]</strong> {{ $proc->icd9cm_description }}
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-stethoscope fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Pemeriksaan dokter belum dilakukan untuk kunjungan ini.</h6>
                            @if(auth()->user()->hasAnyRole(['dokter', 'kepala_klinik']))
                                <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-primary mt-2">
                                    <i class="fas fa-stethoscope me-1"></i>Mulai Pemeriksaan Dokter
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Pharmacy Tab -->
                <div class="tab-pane fade" id="pharmacy" role="tabpanel">
                    @forelse($visit->prescriptions as $presc)
                        <div class="card border mb-3">
                            <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
                                <div>
                                    <strong class="text-primary">{{ $presc->prescription_number }}</strong> | 
                                    Dokter: {{ $presc->doctor?->formatted_name ?? '-' }} | 
                                    Tanggal: {{ $presc->created_at->format('d/m/Y H:i') }}
                                </div>
                                <div>
                                    <span class="badge {{ $presc->status_badge_class }}">{{ $presc->status_label }}</span>
                                    <a href="{{ route('pharmacy.show_prescription', $presc->id) }}" class="btn btn-sm btn-outline-secondary ms-2">
                                        <i class="fas fa-eye me-1"></i>Detail Resep
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nama Obat</th>
                                                <th>Bentuk</th>
                                                <th>Jumlah</th>
                                                <th>Dosis & Aturan Pakai</th>
                                                <th>Instruksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($presc->items as $it)
                                                <tr>
                                                    <td class="fw-bold">{{ $it->medicine?->brand_name }} ({{ $it->medicine?->generic_name }})</td>
                                                    <td>{{ $it->medicine?->dosage_form }}</td>
                                                    <td>{{ $it->quantity }}</td>
                                                    <td>{{ $it->dosage }}, {{ $it->frequency }}</td>
                                                    <td>{{ $it->instructions }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Belum ada resep elektronik yang diterbitkan untuk kunjungan ini.</h6>
                        </div>
                    @endforelse
                </div>

                <!-- Laboratory Tab -->
                <div class="tab-pane fade" id="lab" role="tabpanel">
                    @forelse($visit->laboratoryRequests as $labReq)
                        <div class="card border mb-3">
                            <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
                                <div>
                                    <strong class="text-primary">{{ $labReq->request_number }}</strong> | 
                                    Dokter Pemohon: {{ $labReq->doctor?->formatted_name }} | 
                                    Waktu Permintaan: {{ $labReq->request_time?->format('d/m/Y H:i') }}
                                </div>
                                <div>
                                    <span class="badge {{ $labReq->status_badge_class }}">{{ $labReq->status_label }}</span>
                                    <a href="{{ route('laboratory.show', $labReq->id) }}" class="btn btn-sm btn-outline-secondary ms-2">
                                        <i class="fas fa-flask me-1"></i>Detail Lab
                                    </a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Pemeriksaan</th>
                                                <th>Hasil</th>
                                                <th>Nilai Rujukan</th>
                                                <th>Satuan</th>
                                                <th>Catatan / Kritis</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($labReq->results as $res)
                                                <tr>
                                                    <td class="fw-bold">{{ $res->laboratory?->name }}</td>
                                                    <td class="{{ $res->is_critical ? 'fw-bold text-danger' : 'fw-bold text-dark' }}">{{ $res->result_value }}</td>
                                                    <td>{{ $res->reference_range ?? '-' }}</td>
                                                    <td>{{ $res->unit ?? '-' }}</td>
                                                    <td>
                                                        @if($res->is_critical)
                                                            <span class="badge bg-danger">NILAI KRITIS</span>
                                                        @endif
                                                        {{ $res->notes ?? '-' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-vial fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Tidak ada pemeriksaan laboratorium untuk kunjungan ini.</h6>
                        </div>
                    @endforelse
                </div>

                <!-- History Tab -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu Perubahan</th>
                                    <th>Status Sebelum</th>
                                    <th>Status Baru</th>
                                    <th>Diubah Oleh</th>
                                    <th>Alasan / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($visit->statusHistories as $h)
                                    <tr>
                                        <td>{{ $h->created_at->format('d/m/Y H:i:s') }} WIB</td>
                                        <td><span class="badge bg-light text-dark border">{{ $h->status_from ?? 'Awal' }}</span></td>
                                        <td><span class="badge bg-primary">{{ $h->status_to }}</span></td>
                                        <td><i class="fas fa-user-circle me-1 text-muted"></i>{{ $h->user?->name ?? 'Sistem' }}</td>
                                        <td>{{ $h->reason ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat perubahan status.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Visit Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('visits.cancel', $visit->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold" id="cancelModalLabel"><i class="fas fa-exclamation-circle me-2"></i>Batalkan Kunjungan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Apakah Anda yakin ingin membatalkan kunjungan <strong>{{ $visit->visit_number }}</strong> untuk pasien <strong>{{ $visit->patient->name }}</strong>?</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan Pembatalan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="3" required placeholder="Contoh: Pasien batal berobat, salah poli, atau panggilan berkali-kali tidak ada respon..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Kembali</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-ban me-1"></i>Konfirmasi Batalkan</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
