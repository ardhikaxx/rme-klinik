@extends('layouts.app')

@section('title', 'Pendaftaran Kunjungan Baru')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Pendaftaran Kunjungan Pasien</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('visits.index') }}">Kunjungan</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Daftar Kunjungan Baru</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('visits.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Antrean
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="fas fa-exclamation-triangle me-2"></i>Terdapat kesalahan pengisian formulir:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-clipboard-user me-2"></i>Formulir Registrasi Kunjungan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('visits.store') }}" method="POST" id="visitForm">
                        @csrf

                        <!-- Patient Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pilih Pasien <span class="text-danger">*</span></label>
                            @if($selectedPatient)
                                <input type="hidden" name="patient_id" value="{{ $selectedPatient->id }}">
                                <div class="p-3 border rounded bg-light d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold text-dark fs-5">{{ $selectedPatient->name }}</div>
                                        <div class="text-muted small">
                                            No. RM: <span class="badge bg-primary">{{ $selectedPatient->medical_record_number }}</span> | 
                                            NIK: {{ $selectedPatient->nik ?? '-' }} | 
                                            TTL: {{ $selectedPatient->place_of_birth ?? '-' }}, {{ $selectedPatient->date_of_birth?->format('d-m-Y') }} ({{ $selectedPatient->age }} thn) | 
                                            Gender: {{ $selectedPatient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-map-marker-alt me-1 text-danger"></i>{{ $selectedPatient->full_address }}
                                        </div>
                                    </div>
                                    <a href="{{ route('visits.create') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Ganti Pasien
                                    </a>
                                </div>
                            @else
                                <select class="form-select select2-ajax-patient" id="patient_id" name="patient_id" required>
                                    <option value="">-- Ketik Nama / No. RM / NIK / No. HP Pasien --</option>
                                </select>
                                <div class="form-text mt-1">
                                    Belum terdaftar? <a href="{{ route('patients.create') }}" class="fw-bold text-primary" target="_blank"><i class="fas fa-user-plus me-1"></i>Daftarkan Pasien Baru</a>
                                </div>
                            @endif
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Polyclinic -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tujuan Poli / Unit Layanan <span class="text-danger">*</span></label>
                                <select class="form-select @error('polyclinic_id') is-invalid @enderror" name="polyclinic_id" id="polyclinic_id" required>
                                    <option value="">-- Pilih Poliklinik --</option>
                                    @foreach($polyclinics as $poly)
                                        <option value="{{ $poly->id }}" {{ old('polyclinic_id') == $poly->id ? 'selected' : '' }}>
                                            {{ $poly->name }} (Kode: {{ $poly->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('polyclinic_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Doctor -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Dokter Pemeriksa <span class="text-danger">*</span></label>
                                <select class="form-select @error('doctor_id') is-invalid @enderror" name="doctor_id" id="doctor_id" required>
                                    <option value="">-- Pilih Dokter --</option>
                                    @foreach($doctors as $doc)
                                        <option value="{{ $doc->id }}" {{ old('doctor_id') == $doc->id ? 'selected' : '' }}>
                                            {{ $doc->formatted_name }} ({{ $doc->sip_str_number ?? 'SIP Aktif' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('doctor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <!-- Payment Type -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jenis Penjamin / Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-select @error('payment_type') is-invalid @enderror" name="payment_type" id="payment_type" required>
                                    <option value="umum" {{ old('payment_type', 'umum') == 'umum' ? 'selected' : '' }}>Umum / Mandiri</option>
                                    <option value="bpjs" {{ old('payment_type') == 'bpjs' ? 'selected' : '' }}>BPJS Kesehatan</option>
                                    <option value="asuransi_lain" {{ old('payment_type') == 'asuransi_lain' ? 'selected' : '' }}>Asuransi Swasta / Rekanan</option>
                                </select>
                                @error('payment_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Insurance Number -->
                            <div class="col-md-6" id="insuranceWrapper" style="display: none;">
                                <label class="form-label fw-bold">No. Kartu BPJS / Polis Asuransi</label>
                                <input type="text" class="form-control @error('insurance_number') is-invalid @enderror" 
                                       name="insurance_number" id="insurance_number" value="{{ old('insurance_number') }}"
                                       placeholder="Masukkan nomor kepesertaan">
                                @error('insurance_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Pendaftaran / Keluhan Awal Singkat</label>
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Contoh: Kontrol rutin pasca operasi, demam sejak 3 hari, atau rujukan internal...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="{{ route('visits.index') }}" class="btn btn-light px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="fas fa-check me-2"></i>Daftarkan Kunjungan & Buat Antrean
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle text-info me-2"></i>Alur Kunjungan Klinik
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline-stepper">
                        <div class="step-item active">
                            <div class="step-icon"><i class="fas fa-user-check"></i></div>
                            <div class="step-content">
                                <div class="step-title">1. Registrasi Admisi</div>
                                <div class="step-desc">Pencatatan data kunjungan, penentuan poli, dokter & jenis penjamin.</div>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon"><i class="fas fa-user-nurse"></i></div>
                            <div class="step-content">
                                <div class="step-title">2. Pengkajian Awal Perawat</div>
                                <div class="step-desc">Anamnesis awal, TTV, antropometri, skala nyeri, dan skrining jatuh.</div>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon"><i class="fas fa-stethoscope"></i></div>
                            <div class="step-content">
                                <div class="step-title">3. Pemeriksaan Dokter</div>
                                <div class="step-desc">Pemeriksaan klinis SOAP, diagnosis ICD-10, tindakan ICD-9-CM, resep, atau order lab.</div>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon"><i class="fas fa-pills"></i></div>
                            <div class="step-content">
                                <div class="step-title">4. Farmasi / Penunjang</div>
                                <div class="step-desc">Telaah resep, peracikan obat, dispensing batch FEFO, atau tes lab.</div>
                            </div>
                        </div>
                        <div class="step-item">
                            <div class="step-icon"><i class="fas fa-cash-register"></i></div>
                            <div class="step-content">
                                <div class="step-title">5. Kasir & Billing</div>
                                <div class="step-desc">Penyelesaian tagihan medis terintegrasi dan cetak kuitansi pembayaran.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select2 AJAX for Patient
    $('.select2-ajax-patient').select2({
        theme: 'bootstrap-5',
        placeholder: 'Ketik Nama, No. RM, NIK atau No. HP...',
        minimumInputLength: 2,
        ajax: {
            url: '{{ route('patients.search.ajax') }}',
            dataType: 'json',
            delay: 300,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data.results };
            },
            cache: true
        }
    });

    // Toggle Insurance input
    function checkPaymentType() {
        var val = $('#payment_type').val();
        if (val === 'bpjs' || val === 'asuransi_lain') {
            $('#insuranceWrapper').show();
            $('#insurance_number').attr('required', true);
        } else {
            $('#insuranceWrapper').hide();
            $('#insurance_number').removeAttr('required');
        }
    }

    $('#payment_type').on('change', checkPaymentType);
    checkPaymentType();
});
</script>
@endpush
