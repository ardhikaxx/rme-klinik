@extends('layouts.app')

@section('title', 'Detail Dokumen - ' . $document->document_number)
@section('page_title', 'Detail Dokumen Medis')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('documents.index') }}">Dokumen Medis</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $document->document_number }}</li>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Detail Dokumen Medis</h4>
                <span class="badge bg-primary fs-6">{{ $document->type_label }}</span>
            </div>
            <div class="text-muted small mt-1">
                No. Dokumen: <strong>{{ $document->document_number }}</strong> | 
                Waktu Terbit: {{ $document->created_at->format('d/m/Y H:i') }} WIB | 
                Dokter: <strong>{{ $document->doctor?->formatted_name }}</strong>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Daftar Dokumen
            </a>
            <a href="{{ route('documents.print', $document->id) }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-print me-1"></i> Cetak Lembar Dokumen
            </a>
        </div>
    </div>

    <!-- Document Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-file-alt me-2"></i>Pratinjau Dokumen Medis
            </h5>
            <span class="badge bg-light text-dark border">No: {{ $document->document_number }}</span>
        </div>
        <div class="card-body p-4">
            <!-- Patient Demographics -->
            <table class="table table-sm table-bordered mb-4">
                <tr>
                    <th class="bg-light" style="width: 20%;">No. Rekam Medis</th>
                    <td style="width: 30%;" class="fw-bold">{{ $document->patient?->medical_record_number }}</td>
                    <th class="bg-light" style="width: 20%;">Dokter Pemeriksa</th>
                    <td style="width: 30%;">{{ $document->doctor?->formatted_name }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Nama Pasien</th>
                    <td class="fw-bold">{{ $document->patient?->name }}</td>
                    <th class="bg-light">Poli Asal</th>
                    <td>{{ $document->visit?->polyclinic?->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">TTL / Usia</th>
                    <td>{{ $document->patient?->place_of_birth ?? '-' }}, {{ $document->patient?->date_of_birth?->format('d/m/Y') }} ({{ $document->patient?->age }} thn)</td>
                    <th class="bg-light">Jenis Kelamin</th>
                    <td>{{ $document->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Alamat</th>
                    <td colspan="3">{{ $document->patient?->full_address }}</td>
                </tr>
            </table>

            <!-- Document Specific Content Body -->
            <div class="p-4 border rounded bg-light mb-4">
                @php $data = $document->content_data ?? []; @endphp

                @if($document->type === 'surat_keterangan_sakit')
                    <h5 class="fw-bold text-dark text-center mb-3">SURAT KETERANGAN SAKIT</h5>
                    <p>Yang bertanda tangan di bawah ini, Dokter Pemeriksa pada Klinik Pratama Sehat Mandiri Kota Malang, menerangkan dengan sebenarnya bahwa:</p>
                    <p class="ps-3 mb-2">
                        Nama: <strong>{{ $document->patient?->name }}</strong><br>
                        Umur: {{ $document->patient?->age }} Tahun<br>
                        Pekerjaan / Instansi: {{ $data['workplace'] ?? '-' }}<br>
                        Diagnosis: <em>{{ $data['diagnosis'] ?? '-' }}</em>
                    </p>
                    <p>Berhubung dengan keadaan sakitnya, pasien tersebut di atas memerlukan <strong>istirahat sakit selama {{ $data['duration_days'] ?? '-' }} hari</strong>, terhitung mulai tanggal <strong>{{ isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date'])->translatedFormat('d F Y') : '-' }}</strong> sampai dengan tanggal <strong>{{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->translatedFormat('d F Y') : '-' }}</strong>.</p>
                    <p class="mb-0">Demikian surat keterangan ini diberikan untuk dapat dipergunakan sebagaimana mestinya.</p>

                @elseif($document->type === 'surat_keterangan_sehat')
                    <h5 class="fw-bold text-dark text-center mb-3">SURAT KETERANGAN SEHAT</h5>
                    <p>Menerangkan bahwa pasien telah dilakukan pemeriksaan fisik dengan hasil sebagai berikut:</p>
                    <ul class="mb-3">
                        <li>Tekanan Darah: <strong>{{ $data['blood_pressure'] ?? '-' }}</strong></li>
                        <li>Tinggi / Berat Badan: <strong>{{ $data['height'] ?? '-' }} / {{ $data['weight'] ?? '-' }}</strong></li>
                        <li>Penglihatan / Tes Buta Warna: <strong>{{ $data['color_blindness'] ?? '-' }}</strong></li>
                        <li>Status Kesehatan: <strong>{{ $data['health_status'] ?? 'Sehat' }}</strong></li>
                    </ul>
                    <p>Surat keterangan ini diterbitkan untuk keperluan: <strong>{{ $data['purpose'] ?? '-' }}</strong>.</p>

                @elseif($document->type === 'surat_rujukan')
                    <h5 class="fw-bold text-dark text-center mb-3">SURAT RUJUKAN PASIEN</h5>
                    <p>Kepada Yth. Dokter Spesialis / Poli <strong>{{ $data['destination_specialist'] ?? '-' }}</strong><br>
                    di <strong>{{ $data['destination_hospital'] ?? '-' }}</strong></p>
                    <p>Dengan hormat, mohon konsultasi dan penanganan lebih lanjut terhadap pasien:</p>
                    <p class="ps-3 mb-2">
                        Nama: <strong>{{ $document->patient?->name }}</strong> (No. RM: {{ $document->patient?->medical_record_number }})<br>
                        Diagnosis Kerja Sementara: <strong>{{ $data['temporary_diagnosis'] ?? '-' }}</strong><br>
                        Alasan Rujukan: {{ $data['referral_reason'] ?? '-' }}<br>
                        Tindakan / Terapi Diberikan: {{ $data['treatment_given'] ?? '-' }}
                    </p>
                    <p class="mb-0">Atas bantuan dan kerjasamanya kami ucapkan terima kasih.</p>

                @elseif($document->type === 'informed_consent')
                    <h5 class="fw-bold text-dark text-center mb-3">PERSETUJUAN TINDAKAN MEDIS (INFORMED CONSENT)</h5>
                    <p>Yang bertanda tangan di bawah ini menyatakan telah menerima penjelasan mengenai:</p>
                    <ul>
                        <li>Tindakan Medis yang akan dilakukan: <strong>{{ $data['action_name'] ?? '-' }}</strong></li>
                        <li>Nama Penandatangan: <strong>{{ $data['patient_or_guardian_name'] ?? '-' }}</strong> (Hubungan: {{ $data['relation_to_patient'] ?? 'Diri Sendiri' }})</li>
                        <li>Risiko dan komplikasi telah dijelaskan dan dipahami secara sadar.</li>
                    </ul>
                    <p class="mb-0">Dengan ini menyatakan <strong>SETUJU</strong> untuk dilakukan tindakan medis tersebut di atas.</p>
                @endif
            </div>

            <!-- Signature Preview -->
            <div class="row pt-3">
                <div class="col-8">
                    @if($document->notes)
                        <div class="small text-muted"><strong>Catatan:</strong> {{ $document->notes }}</div>
                    @endif
                </div>
                <div class="col-4 text-center">
                    <div class="small mb-1">Kota Malang, {{ $document->created_at->format('d F Y') }}</div>
                    <div class="small text-muted mb-4">Dokter yang Menerangkan,</div>
                    <br><br>
                    <div class="fw-bold text-decoration-underline">{{ $document->doctor?->formatted_name }}</div>
                    <div class="small text-muted">SIP: {{ $document->doctor?->sip_str_number ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
