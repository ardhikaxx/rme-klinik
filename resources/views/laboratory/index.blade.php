@extends('layouts.app')

@section('title', 'Daftar Permintaan Pemeriksaan Laboratorium')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Instalasi Laboratorium Medis</h4>
            <p class="text-muted small mb-0">Pengambilan spesimen, entri hasil pengujian, deteksi nilai kritis, dan verifikasi analis</p>
        </div>
        <div class="text-muted small">
            <i class="fas fa-flask me-1 text-primary"></i> Laboratorium Klinik Terintegrasi
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ empty($status) ? 'active bg-primary' : 'text-dark' }}" href="{{ route('laboratory.index') }}">
                        Semua Order Lab
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'requested' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('laboratory.index', ['status' => 'requested']) }}">
                        <i class="fas fa-clock me-1"></i>Menunggu Sampel
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'sample_collected' ? 'active bg-info text-white' : 'text-dark' }}" href="{{ route('laboratory.index', ['status' => 'sample_collected']) }}">
                        <i class="fas fa-vial me-1"></i>Sampel Diambil (Proses Uji)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'completed' ? 'active bg-success text-white' : 'text-dark' }}" href="{{ route('laboratory.index', ['status' => 'completed']) }}">
                        <i class="fas fa-check-circle me-1"></i>Hasil Selesai
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-microscope me-2"></i>Daftar Permintaan Uji Laboratorium
            </h5>
            <span class="small text-muted">Total: {{ $requests->total() }} order</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Order Lab</th>
                            <th>Waktu Permintaan</th>
                            <th>No. RM / Nama Pasien</th>
                            <th>Dokter Pemohon</th>
                            <th>Jenis Pemeriksaan Diminta</th>
                            <th>Catatan Klinis</th>
                            <th>Status Uji</th>
                            <th class="text-center" style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $req->request_number }}</div>
                                    <span class="badge bg-light text-dark border">Kunjungan: {{ $req->visit?->visit_number }}</span>
                                </td>
                                <td>
                                    <div>{{ $req->request_time?->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $req->request_time?->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $req->visit?->patient?->name }}</div>
                                    <small class="text-muted">{{ $req->visit?->patient?->medical_record_number }} ({{ $req->visit?->patient?->gender == 'L' ? 'L' : 'P' }}, {{ $req->visit?->patient?->age }} thn)</small>
                                </td>
                                <td>{{ $req->doctor?->formatted_name ?? '-' }}</td>
                                <td>
                                    <ul class="mb-0 ps-3 small text-muted">
                                        @foreach($req->items as $it)
                                            <li><strong>{{ $it->laboratory?->name }}</strong> ({{ $it->laboratory?->category }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td><small class="text-muted">{{ $req->clinical_notes ?? '-' }}</small></td>
                                <td>
                                    <span class="badge {{ $req->status_badge_class }}">{{ $req->status_label }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('laboratory.show', $req->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-vial me-1"></i>Proses & Entri Hasil
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-flask fa-3x text-muted mb-2"></i>
                                    <div>Tidak ada permintaan uji laboratorium pada status ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($requests->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
