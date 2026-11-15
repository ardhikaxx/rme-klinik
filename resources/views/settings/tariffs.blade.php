@extends('layouts.app')

@section('title', 'Master Tarif Layanan & Jasa Medis')
@section('page_title', 'Master Tarif Layanan & Jasa Medis')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Tarif Layanan & Jasa Medis</li>
@endsection

@section('page_actions')
    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addTariffModal">
        <i class="fas fa-plus me-1"></i> Tambah Tarif Baru
    </button>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Tariffs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-tag me-2"></i>Daftar Master Tarif Layanan
            </h5>
            <span class="small text-muted">Total: {{ $tariffs->total() }} tarif</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Layanan / Tindakan</th>
                            <th>Kategori</th>
                            <th>Poli Terkait</th>
                            <th>Kode ICD-9</th>
                            <th class="text-end">Tarif Pasien</th>
                            <th class="text-end">Porsi Dokter</th>
                            <th class="text-end">Porsi Perawat</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tariffs as $t)
                            <tr>
                                <td><span class="badge bg-light text-primary border fw-bold">{{ $t->code }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $t->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary text-capitalize">{{ str_replace('_', ' ', $t->category) }}</span>
                                </td>
                                <td>{{ $t->polyclinic?->name ?? 'Semua Poli' }}</td>
                                <td>{{ $t->icd9cm_code ?? '-' }}</td>
                                <td class="text-end fw-bold text-primary">Rp{{ number_format($t->tariff, 0, ',', '.') }}</td>
                                <td class="text-end text-success">Rp{{ number_format($t->doctor_fee, 0, ',', '.') }}</td>
                                <td class="text-end text-info">Rp{{ number_format($t->nurse_fee, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $t->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $t->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTariffModal-{{ $t->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Tariff Modal -->
                            <div class="modal fade" id="editTariffModal-{{ $t->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <form action="{{ route('settings.tariffs.update', $t->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Tarif: {{ $t->name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Nama Layanan <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="name" value="{{ $t->name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Kategori Layanan <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="category" required>
                                                            @foreach(['administrasi', 'konsultasi', 'tindakan_medis', 'tindakan_keperawatan', 'laboratorium', 'bmhp', 'lainnya'] as $cat)
                                                                <option value="{{ $cat }}" {{ $t->category == $cat ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $cat)) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Poliklinik</label>
                                                        <select class="form-select" name="polyclinic_id">
                                                            <option value="">Semua Poliklinik</option>
                                                            @foreach($polyclinics as $p)
                                                                <option value="{{ $p->id }}" {{ $t->polyclinic_id == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Kode ICD-9-CM Terkait</label>
                                                        <input type="text" class="form-control" name="icd9cm_code" value="{{ $t->icd9cm_code }}">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-bold">Tarif Total (Rp) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="tariff" value="{{ $t->tariff }}" min="0" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-bold">Jasa Dokter (Rp)</label>
                                                        <input type="number" class="form-control" name="doctor_fee" value="{{ $t->doctor_fee }}" min="0">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-bold">Jasa Perawat (Rp)</label>
                                                        <input type="number" class="form-control" name="nurse_fee" value="{{ $t->nurse_fee }}" min="0">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fw-bold">Porsi Klinik (Rp)</label>
                                                        <input type="number" class="form-control" name="clinic_fee" value="{{ $t->clinic_fee }}" min="0">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Status Keaktifan</label>
                                                        <select class="form-select" name="is_active">
                                                            <option value="1" {{ $t->is_active ? 'selected' : '' }}>Aktif</option>
                                                            <option value="0" {{ !$t->is_active ? 'selected' : '' }}>Nonaktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary fw-bold">Simpan Perubahan</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">Belum ada master tarif layanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$tariffs" label="tarif layanan" />
        </div>
    </div>
</div>

<!-- Add Tariff Modal -->
<div class="modal fade" id="addTariffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('settings.tariffs.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus me-2"></i>Tambah Master Tarif Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kode Tarif <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" placeholder="TRF-001" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nama Layanan / Tindakan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="Konsultasi Dokter Umum" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kategori Layanan <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" required>
                                <option value="konsultasi">Konsultasi</option>
                                <option value="tindakan_medis">Tindakan Medis</option>
                                <option value="tindakan_keperawatan">Tindakan Keperawatan</option>
                                <option value="administrasi">Administrasi</option>
                                <option value="laboratorium">Laboratorium</option>
                                <option value="bmhp">BMHP</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Poliklinik</label>
                            <select class="form-select" name="polyclinic_id">
                                <option value="">Semua Poliklinik</option>
                                @foreach($polyclinics as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kode ICD-9-CM Terkait</label>
                            <input type="text" class="form-control" name="icd9cm_code" placeholder="89.07">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tarif Total (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="tariff" value="50000" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jasa Dokter (Rp)</label>
                            <input type="number" class="form-control" name="doctor_fee" value="35000" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Jasa Perawat (Rp)</label>
                            <input type="number" class="form-control" name="nurse_fee" value="5000" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Porsi Klinik (Rp)</label>
                            <input type="number" class="form-control" name="clinic_fee" value="10000" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Simpan Tarif Baru</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
