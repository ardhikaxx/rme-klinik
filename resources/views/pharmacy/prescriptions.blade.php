@extends('layouts.app')

@section('title', 'Daftar Resep Farmasi (E-Prescription)')
@section('page_title', 'Instalasi Farmasi & Apotek')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">E-Resep Farmasi</li>
@endsection

@section('page_actions')
    <div class="text-muted small">
        <i class="fas fa-pills me-1 text-warning"></i> Status Stok & Resep Real-Time
    </div>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Filter Status Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ empty($status) ? 'active bg-primary' : 'text-dark' }}" href="{{ route('pharmacy.prescriptions') }}">
                        Semua Resep
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'menunggu_telaah' ? 'active bg-warning text-dark' : 'text-dark' }}" href="{{ route('pharmacy.prescriptions', ['status' => 'menunggu_telaah']) }}">
                        <i class="fas fa-clock me-1"></i>Menunggu Telaah
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'menunggu_dispensing' ? 'active bg-info text-white' : 'text-dark' }}" href="{{ route('pharmacy.prescriptions', ['status' => 'menunggu_dispensing']) }}">
                        <i class="fas fa-mortar-pestle me-1"></i>Siap Dispensing
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $status === 'selesai' ? 'active bg-success text-white' : 'text-dark' }}" href="{{ route('pharmacy.prescriptions', ['status' => 'selesai']) }}">
                        <i class="fas fa-check-circle me-1"></i>Selesai / Diserahkan
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-prescription me-2"></i>Daftar Resep Elektronik
            </h5>
            <span class="small text-muted">Menampilkan {{ $prescriptions->count() }} resep</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Resep</th>
                            <th>Tanggal & Waktu</th>
                            <th>No. RM / Pasien</th>
                            <th>Dokter Penulis</th>
                            <th>Jumlah Item Obat</th>
                            <th>Status Alergi</th>
                            <th>Status Resep</th>
                            <th class="text-center" style="width: 180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescriptions as $p)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary fs-6">{{ $p->prescription_number }}</div>
                                    <span class="badge bg-light text-dark border">Kunjungan: {{ $p->visit?->visit_number }}</span>
                                </td>
                                <td class="text-nowrap">
                                    <div>{{ $p->created_at->format('d/m/Y') }}</div>
                                    <small class="text-muted">{{ $p->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $p->visit?->patient?->name }}</div>
                                    <small class="text-muted">RM: {{ $p->visit?->patient?->medical_record_number }} ({{ $p->visit?->patient?->age }} thn)</small>
                                </td>
                                <td>{{ $p->doctor?->formatted_name ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ $p->items->count() }} Non-Racikan
                                        @if($p->compounds && $p->compounds->count() > 0)
                                            + {{ $p->compounds->count() }} Racikan
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($p->visit?->patient?->allergies && $p->visit?->patient?->allergies->count() > 0)
                                        <span class="badge bg-danger">
                                            <i class="fas fa-hand-dots me-1"></i>{{ $p->visit?->patient?->allergies->count() }} Alergi
                                        </span>
                                    @else
                                        <span class="badge bg-light text-muted border">Tidak Ada</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $p->status_badge_class }}">{{ $p->status_label }}</span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('pharmacy.show_prescription', $p->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-mortar-pestle me-1"></i>Telaah & Dispensing
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-prescription-bottle-alt fa-3x text-muted mb-2"></i>
                                    <div>Tidak ada resep pada kategori ini.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$prescriptions" label="resep" />
        </div>
    </div>
</div>
@endsection
