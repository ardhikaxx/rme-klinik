@extends('layouts.app')

@section('title', 'Penerbitan Dokumen Medis - ' . $visit->patient->name)
@section('page_title', 'Penerbitan Dokumen Medis Resmi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Dokumen Medis</a></li>
    <li class="breadcrumb-item"><a href="{{ route('clinical.examination', $visit->id) }}">Pemeriksaan</a></li>
    <li class="breadcrumb-item active" aria-current="page">Terbitkan Surat Baru</li>
@endsection

@section('page_actions')
    <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Kembali ke Pemeriksaan
    </a>
@endsection

@section('content')
<div class="container-fluid p-0">
    </div>

    <!-- Patient Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $visit->patient->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $visit->patient->medical_record_number }}</span> | 
                                NIK: {{ $visit->patient->nik ?? '-' }} | 
                                TTL: {{ $visit->patient->place_of_birth ?? '-' }}, {{ $visit->patient->date_of_birth?->format('d/m/Y') }} ({{ $visit->patient->age }} thn) | 
                                Alamat: {{ $visit->patient->full_address }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Poli:</strong> {{ $visit->polyclinic?->name }} | <strong class="text-muted">Dokter:</strong> {{ $visit->doctor?->formatted_name }}</div>
                        <div><strong class="text-muted">Diagnosis SOAP:</strong> {{ $visit->medicalRecord?->assessment ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Generator Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-edit me-2"></i>Formulir Pembuatan Dokumen Medis
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('documents.store', $visit->id) }}" method="POST" id="docForm">
                @csrf

                <!-- Document Type Selector -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Pilih Jenis Dokumen Medis yang Diterbitkan <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg fw-bold text-primary" name="type" id="docTypeSelect" required>
                        <option value="surat_keterangan_sakit" selected>Surat Keterangan Sakit (Izin Istirahat Kerja/Sekolah)</option>
                        <option value="surat_keterangan_sehat">Surat Keterangan Sehat (Uji Kesehatan Fisik)</option>
                        <option value="surat_rujukan">Surat Rujukan Medis Antar Faskes (FKTL / Rumah Sakit)</option>
                        <option value="informed_consent">Formulir Persetujuan Tindakan Medis (Informed Consent)</option>
                    </select>
                </div>

                <hr>

                <!-- SECTION 1: SURAT KETERANGAN SAKIT -->
                <div class="doc-section" id="section_surat_keterangan_sakit">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-bed-pulse me-2 text-danger"></i>Parameter Surat Keterangan Sakit</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jumlah Hari Istirahat <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="content_data[duration_days]" id="duration_days" value="3" min="1">
                                <span class="input-group-text">Hari</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Mulai Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="content_data[start_date]" id="start_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Sampai Dengan Tanggal</label>
                            <input type="date" class="form-control" name="content_data[end_date]" id="end_date" value="{{ date('Y-m-d', strtotime('+2 days')) }}">
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Diagnosis / Keterangan Sakit</label>
                            <input type="text" class="form-control" name="content_data[diagnosis]" value="{{ $visit->medicalRecord?->assessment ?? 'Demam / ISPA' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Instansi / Perusahaan / Sekolah Tujuan</label>
                            <input type="text" class="form-control" name="content_data[workplace]" placeholder="Contoh: Tempat Bekerja / Universitas / Sekolah">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: SURAT KETERANGAN SEHAT -->
                <div class="doc-section" id="section_surat_keterangan_sehat" style="display: none;">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-heart-circle-check me-2 text-success"></i>Parameter Surat Keterangan Sehat</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Keperluan Surat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="content_data[purpose]" placeholder="Contoh: Melamar Pekerjaan / Pendaftaran Perguruan Tinggi / Pembuatan SIM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Status Kesehatan Fisik</label>
                            <select class="form-select" name="content_data[health_status]">
                                <option value="Sehat & Layak Beraktivitas / Bekerja" selected>Sehat & Layak Beraktivitas / Bekerja</option>
                                <option value="Sehat dengan Catatan Ringan">Sehat dengan Catatan Ringan</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tekanan Darah</label>
                            <input type="text" class="form-control" name="content_data[blood_pressure]" value="{{ ($visit->nursingAssessment?->systolic ?? 120) . '/' . ($visit->nursingAssessment?->diastolic ?? 80) }} mmHg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tinggi Badan</label>
                            <input type="text" class="form-control" name="content_data[height]" value="{{ $visit->nursingAssessment?->height ?? 165 }} cm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Berat Badan</label>
                            <input type="text" class="form-control" name="content_data[weight]" value="{{ $visit->nursingAssessment?->weight ?? 60 }} kg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tes Buta Warna</label>
                            <select class="form-select" name="content_data[color_blindness]">
                                <option value="Tidak Buta Warna (Normal)" selected>Tidak Buta Warna (Normal)</option>
                                <option value="Buta Warna Parsial">Buta Warna Parsial</option>
                                <option value="Buta Warna Total">Buta Warna Total</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: SURAT RUJUKAN -->
                <div class="doc-section" id="section_surat_rujukan" style="display: none;">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-hospital-user me-2 text-primary"></i>Parameter Surat Rujukan Faskes</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fasilitas Kesehatan Tujuan Rujukan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="content_data[destination_hospital]" placeholder="Contoh: RSUD Dr. Saiful Anwar Kota Malang">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Poli / Spesialis Tujuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="content_data[destination_specialist]" placeholder="Contoh: Spesialis Penyakit Dalam / Poli Jantung">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Alasan Rujukan</label>
                        <textarea class="form-control" name="content_data[referral_reason]" rows="2" placeholder="Mohon penanganan dan evaluasi lebih lanjut terhadap kecurigaan..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Diagnosis Kerja Sementara</label>
                        <input type="text" class="form-control" name="content_data[temporary_diagnosis]" value="{{ $visit->medicalRecord?->assessment ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tindakan / Terapi yang Telah Diberikan di Faskes Primer</label>
                        <textarea class="form-control" name="content_data[treatment_given]" rows="2" placeholder="Terapi cairan infus, antipiretik, antibiotik awal..."></textarea>
                    </div>
                </div>

                <!-- SECTION 4: INFORMED CONSENT -->
                <div class="doc-section" id="section_informed_consent" style="display: none;">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-signature me-2 text-warning"></i>Parameter Persetujuan Tindakan Medis (Informed Consent)</h6>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Tindakan Medis yang Dilakukan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="content_data[action_name]" placeholder="Contoh: Ekstraksi Gigi Geraham / Jahit Luka Robek / Insisi Abses">
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Penandatangan Persetujuan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="content_data[patient_or_guardian_name]" value="{{ $visit->patient->name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hubungan dengan Pasien <span class="text-danger">*</span></label>
                            <select class="form-select" name="content_data[relation_to_patient]">
                                <option value="Diri Sendiri" selected>Diri Sendiri (Pasien Langsung)</option>
                                <option value="Suami / Istri">Suami / Istri</option>
                                <option value="Orang Tua / Wali">Orang Tua / Wali</option>
                                <option value="Anak Kandung">Anak Kandung</option>
                                <option value="Keluarga Terdekat">Keluarga Terdekat</option>
                            </select>
                        </div>
                    </div>
                    <div class="p-3 border rounded bg-light mb-3 small">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="content_data[risk_explained]" value="1" checked id="consentRisk">
                            <label class="form-check-label fw-bold" for="consentRisk">
                                Pasien / keluarga telah diberikan penjelasan secara lengkap mengenai diagnosis, tata cara tindakan, tujuan, risiko, komplikasi yang mungkin terjadi, serta alternatif tindakan lainnya dan menyetujui secara sadar tanpa paksaan.
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Tambahan untuk Dokumen (Opsional)</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Catatan khusus atau klausul tambahan..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-light px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                        <i class="fas fa-check-circle me-2"></i>Terbitkan Dokumen & Siap Cetak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function toggleDocSections() {
        var selectedType = $('#docTypeSelect').val();
        $('.doc-section').hide();
        $('#section_' + selectedType).fadeIn(200);
    }

    $('#docTypeSelect').on('change', toggleDocSections);
    toggleDocSections();

    // Auto calculate end date for surat sakit
    $('#duration_days, #start_date').on('change input', function() {
        var days = parseInt($('#duration_days').val()) || 1;
        var start = $('#start_date').val();
        if (start) {
            var sDate = new Date(start);
            sDate.setDate(sDate.getDate() + (days - 1));
            var yyyy = sDate.getFullYear();
            var mm = String(sDate.getMonth() + 1).padStart(2, '0');
            var dd = String(sDate.getDate()).padStart(2, '0');
            $('#end_date').val(yyyy + '-' + mm + '-' + dd);
        }
    });
});
</script>
@endpush
