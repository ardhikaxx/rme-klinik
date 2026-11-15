@extends('layouts.app')

@section('title', 'Jadwal Praktik Dokter')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Jadwal Praktik Mingguan Dokter</h4>
            <p class="text-muted small mb-0">Alokasi jadwal dinas dokter, jam pelayanan poli, kuota antrean harian, dan kontrol kapasitas</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="fas fa-calendar-plus me-1"></i> Tambah Jadwal Praktik
        </button>
    </div>

    <!-- Schedules Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-calendar-alt me-2"></i>Daftar Jadwal Praktik Dokter
            </h5>
            <span class="small text-muted">{{ number_format($schedules->total(), 0, ',', '.') }} jadwal terdaftar</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Hari Praktik</th>
                            <th>Poliklinik</th>
                            <th>Nama Dokter</th>
                            <th>Jam Pelayanan</th>
                            <th class="text-center">Kuota Pasien</th>
                            <th>Status Jadwal</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $sc)
                            <tr>
                                <td>
                                    @php
                                        $dayName = match($sc->day_of_week) {
                                            1 => 'Senin',
                                            2 => 'Selasa',
                                            3 => 'Rabu',
                                            4 => 'Kamis',
                                            5 => 'Jumat',
                                            6 => 'Sabtu',
                                            7 => 'Minggu',
                                            default => '-',
                                        };
                                    @endphp
                                    <span class="badge bg-primary fs-6">{{ $dayName }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $sc->polyclinic?->name }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $sc->employee?->formatted_name }}</div>
                                    <small class="text-muted">SIP: {{ $sc->employee?->sip_str_number ?? '-' }}</small>
                                </td>
                                <td>
                                    <i class="fas fa-clock me-1 text-primary"></i>
                                    {{ substr($sc->start_time, 0, 5) }} - {{ substr($sc->end_time, 0, 5) }} WIB
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border fs-6">{{ $sc->quota }} Pasien</span>
                                </td>
                                <td>
                                    <span class="badge {{ $sc->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $sc->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('settings.schedules.toggle', $sc->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $sc->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                            {{ $sc->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Belum ada jadwal praktik dokter terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$schedules" label="jadwal praktik" />
        </div>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('settings.schedules.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-calendar-plus me-2"></i>Tambah Jadwal Praktik</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Dokter Pemeriksa <span class="text-danger">*</span></label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">-- Pilih Dokter --</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->formatted_name }} ({{ $d->sip_str_number ?? 'SIP Aktif' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Poliklinik Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select" name="polyclinic_id" required>
                            <option value="">-- Pilih Poliklinik --</option>
                            @foreach($polyclinics as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Hari Praktik <span class="text-danger">*</span></label>
                        <select class="form-select" name="day_of_week" required>
                            <option value="1">Senin</option>
                            <option value="2">Selasa</option>
                            <option value="3">Rabu</option>
                            <option value="4">Kamis</option>
                            <option value="5">Jumat</option>
                            <option value="6">Sabtu</option>
                            <option value="7">Minggu</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" value="08:00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" value="14:00" required>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Kuota Maksimal Pasien <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quota" value="30" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan Jadwal</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
