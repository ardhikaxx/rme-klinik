@extends('layouts.app')

@section('title', 'Jejak Audit Keamanan & Klinis (Audit Trail)')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Jejak Audit Keamanan & Transaksi Klinis (Audit Trail)</h4>
            <p class="text-muted small mb-0">Pencatatan riwayat setiap aksi autentikasi, perubahan rekam medis, transaksi farmasi, dan pembayaran</p>
        </div>
        <span class="badge bg-secondary fs-6 px-3 py-2">
            <i class="fas fa-lock me-1"></i> Append-Only Immutable Log
        </span>
    </div>

    <!-- Security Information Alert -->
    <div class="alert alert-dark border-0 shadow-sm d-flex align-items-center mb-4 p-3" role="alert">
        <i class="fas fa-shield-alt fa-2x text-warning me-3"></i>
        <div class="small">
            <strong class="fs-6 d-block mb-1">Integritas Log Audit Sesuai Permenkes 24/2022</strong>
            Semua catatan di bawah ini disimpan dalam tabel berkas log append-only (hanya-tambah) tanpa fungsi modifikasi (update) maupun penghapusan (delete). Setiap aktivitas penting yang dilakukan oleh pegawai tercatat bersama dengan stempel waktu, alamat IP, peramban (user agent), dan identitas pengguna.
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('audit.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Pengguna / Pegawai</label>
                    <select class="form-select form-select-sm" name="user_id">
                        <option value="">Semua Pengguna</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->username }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Modul Sistem</label>
                    <select class="form-select form-select-sm" name="module">
                        <option value="">Semua Modul</option>
                        @foreach($modules as $mod)
                            <option value="{{ $mod }}" {{ $module == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Mulai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold mb-1">Sampai Tanggal</label>
                    <input type="date" class="form-control form-control-sm" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-2 d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                    @if($userId || $module || $startDate || $endDate)
                        <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-list-check me-2"></i>Catatan Aktivitas Sistem
            </h5>
            <span class="small text-muted">Total: {{ $logs->total() }} log tercatat</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 170px;">Waktu Kejadian</th>
                            <th>Pengguna</th>
                            <th>Modul</th>
                            <th>Aksi / Event</th>
                            <th>Uraian Aktivitas</th>
                            <th>Alamat IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $log->created_at->format('H:i:s') }} WIB</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $log->user?->name ?? 'Sistem Otomatis' }}</div>
                                    <small class="text-muted">{{ $log->user?->username ?? '-' }}</small>
                                </td>
                                <td>
                                    @php
                                        $modColor = match($log->module) {
                                            'AUTH' => 'bg-secondary',
                                            'PATIENT' => 'bg-info text-white',
                                            'VISIT' => 'bg-primary',
                                            'CLINICAL', 'EMR' => 'bg-danger',
                                            'PHARMACY' => 'bg-warning text-dark',
                                            'BILLING' => 'bg-success',
                                            default => 'bg-light text-dark border',
                                        };
                                    @endphp
                                    <span class="badge {{ $modColor }}">{{ $log->module }}</span>
                                </td>
                                <td>
                                    <code>{{ $log->action }}</code>
                                </td>
                                <td>
                                    <div class="small text-dark">{{ $log->description }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-muted border">{{ $log->ip_address ?? '127.0.0.1' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-list fa-3x text-muted mb-2"></i>
                                    <div>Tidak ada log audit ditemukan untuk filter ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
