@extends('layouts.app')

@section('title', 'Daftar Dokumen & Surat Medis Pasien')
@section('page_title', 'Dokumen Medis & Surat Keterangan Pasien')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dokumen Medis</li>
@endsection

@section('page_actions')
    <div class="text-muted small">
        <i class="fas fa-file-medical me-1 text-primary"></i> Generator Dokumen Resmi Klinik
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Filter Type Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ empty($type) ? 'active bg-primary' : 'text-dark' }}" href="{{ route('documents.index') }}">
                        Semua Dokumen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type === 'surat_keterangan_sakit' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('documents.index', ['type' => 'surat_keterangan_sakit']) }}">
                        Surat Sakit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type === 'surat_keterangan_sehat' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('documents.index', ['type' => 'surat_keterangan_sehat']) }}">
                        Surat Sehat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type === 'surat_rujukan' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('documents.index', ['type' => 'surat_rujukan']) }}">
                        Surat Rujukan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $type === 'informed_consent' ? 'active bg-primary' : 'text-dark' }}" href="{{ route('documents.index', ['type' => 'informed_consent']) }}">
                        Informed Consent
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-folder-open me-2"></i>Daftar Surat Medis Diterbitkan
            </h5>
            <span class="small text-muted">Total: {{ $documents->total() }} dokumen</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Dokumen</th>
                            <th>Jenis Dokumen</th>
                            <th>No. RM / Nama Pasien</th>
                            <th>Dokter Penandatangan</th>
                            <th>Tanggal Terbit</th>
                            <th>Ringkasan Isi</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $doc->document_number }}</div>
                                    <small class="badge bg-light text-dark border">Kunjungan: {{ $doc->visit?->visit_number }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fw-bold">{{ $doc->type_label }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $doc->patient?->name }}</div>
                                    <small class="text-muted">{{ $doc->patient?->medical_record_number }} ({{ $doc->patient?->age }} thn)</small>
                                </td>
                                <td>{{ $doc->doctor?->formatted_name ?? '-' }}</td>
                                <td class="text-nowrap">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <small class="text-muted">
                                        @if($doc->type === 'surat_keterangan_sakit')
                                            Istirahat {{ $doc->content_data['duration_days'] ?? '-' }} hari ({{ $doc->content_data['start_date'] ?? '-' }})
                                        @elseif($doc->type === 'surat_rujukan')
                                            Rujukan ke: {{ $doc->content_data['destination_hospital'] ?? '-' }}
                                        @elseif($doc->type === 'surat_keterangan_sehat')
                                            Keperluan: {{ $doc->content_data['purpose'] ?? '-' }}
                                        @else
                                            {{ $doc->notes ?? '-' }}
                                        @endif
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('documents.show', $doc->id) }}" class="btn btn-outline-primary" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('documents.print', $doc->id) }}" class="btn btn-outline-secondary" target="_blank" title="Cetak">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-file-alt fa-3x text-muted mb-2"></i>
                                    <div>Belum ada dokumen medis pada kategori ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$documents" label="dokumen medis" />
        </div>
    </div>
</div>
@endsection
