@extends('layouts.app')

@section('title', 'Poli Gigi & Mulut (Odontogram)')
@section('page_title', 'Poli Gigi & Mulut (Odontogram)')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Poli Gigi & Odontogram</li>
@endsection

@section('page_actions')
    <div class="text-muted small">
        <i class="fas fa-tooth me-1 text-primary"></i> Poliklinik: <strong>{{ $polyGigi?->name ?? 'Poli Gigi' }}</strong> | 
        <i class="fas fa-calendar-day ms-2 me-1 text-primary"></i> {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-teeth-open me-2"></i>Daftar Pasien Poli Gigi & Odontogram
                <span class="badge bg-primary ms-2">{{ $visits->total() }} Pasien</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;" class="text-center">No. Antre</th>
                            <th>No. RM / Pasien</th>
                            <th>Keluhan Pasien</th>
                            <th>Dokter Gigi</th>
                            <th>Status Gigi Tercatat</th>
                            <th>Status Kunjungan</th>
                            <th class="text-center" style="width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $v)
                            @php
                                $teethCount = $v->odontogram?->teeth?->count() ?? 0;
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-primary fs-5 px-3 py-2">{{ $v->queue_number }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold fs-6 text-dark">{{ $v->patient->name }}</div>
                                    <div class="text-muted small">
                                        <span class="badge bg-light text-primary border">{{ $v->patient->medical_record_number }}</span> | 
                                        {{ $v->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}, {{ $v->patient->age }} thn
                                    </div>
                                </td>
                                <td>
                                    <span class="small text-dark">{{ $v->nursingAssessment?->chief_complaint ?? $v->notes ?? '-' }}</span>
                                </td>
                                <td>{{ $v->doctor?->formatted_name ?? '-' }}</td>
                                <td>
                                    @if($teethCount > 0)
                                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>{{ $teethCount }} Gigi Diperiksa</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Belum Dipetakan</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $v->status_badge_class }}">{{ $v->status_label }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('odontogram.show', $v->id) }}" class="btn btn-primary fw-bold">
                                        <i class="fas fa-tooth me-1"></i>Buka Odontogram
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-teeth fa-3x text-muted mb-2"></i>
                                    <div>Tidak ada antrean pasien Poli Gigi hari ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$visits" label="pasien poli gigi" />
        </div>
    </div>
</div>
@endsection
