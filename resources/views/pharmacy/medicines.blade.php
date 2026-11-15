@extends('layouts.app')

@section('title', 'Katalog Obat & Master Farmasi')
@section('page_title', 'Master Obat & Manajemen Persediaan Farmasi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pharmacy.prescriptions') }}">Farmasi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Katalog & Stok Obat</li>
@endsection

@section('page_actions')
    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addMedicineModal">
        <i class="fas fa-plus me-1"></i> Tambah Obat Baru
    </button>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('pharmacy.medicines') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" name="search" value="{{ $search }}" placeholder="Cari kode obat, nama dagang, atau nama generik...">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Cari</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('pharmacy.medicines') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Medicines Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-tablets me-2"></i>Katalog Obat Aktif
            </h5>
            <span class="small text-muted">Total: {{ $medicines->total() }} obat</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Dagang / Generik</th>
                            <th>Kategori & Satuan</th>
                            <th>Bentuk & Kekuatan</th>
                            <th>Harga Beli / Jual</th>
                            <th class="text-center">Total Stok</th>
                            <th>Status Stok</th>
                            <th class="text-center" style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($medicines as $med)
                            @php
                                $totalStock = $med->total_stock;
                                $isLow = $totalStock <= $med->min_stock;
                            @endphp
                            <tr>
                                <td><span class="badge bg-light text-primary border fw-bold">{{ $med->code }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $med->brand_name }}</div>
                                    <small class="text-muted">{{ $med->generic_name }}</small>
                                </td>
                                <td>
                                    <div>{{ $med->category?->name ?? '-' }}</div>
                                    <small class="badge bg-light text-secondary border">{{ $med->unit?->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <div>{{ $med->dosage_form }}</div>
                                    <small class="text-muted">{{ $med->strength ?? '-' }}</small>
                                </td>
                                <td>
                                    <div class="small text-muted">Beli: Rp{{ number_format($med->purchase_price, 0, ',', '.') }}</div>
                                    <div class="fw-bold text-dark">Jual: Rp{{ number_format($med->selling_price, 0, ',', '.') }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="fs-6 fw-bold {{ $isLow ? 'text-danger' : 'text-dark' }}">{{ number_format($totalStock, 0, ',', '.') }}</span>
                                    <small class="text-muted d-block">(Min: {{ number_format($med->min_stock, 0, ',', '.') }})</small>
                                </td>
                                <td>
                                    @if($totalStock == 0)
                                        <span class="badge bg-danger">Habis</span>
                                    @elseif($isLow)
                                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Hampir Habis</span>
                                    @else
                                        <span class="badge bg-success">Aman</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('pharmacy.stock_card', $med->id) }}" class="btn btn-outline-info" title="Kartu Stok">
                                            <i class="fas fa-history"></i> Kartu Stok
                                        </a>
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMedModal-{{ $med->id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editMedModal-{{ $med->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <form action="{{ route('pharmacy.medicines.update', $med->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Edit Obat: {{ $med->brand_name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Nama Dagang (Brand) <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="brand_name" value="{{ $med->brand_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Nama Generik <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="generic_name" value="{{ $med->generic_name }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Kategori</label>
                                                        <select class="form-select" name="category_id" required>
                                                            @foreach($categories as $cat)
                                                                <option value="{{ $cat->id }}" {{ $med->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Satuan</label>
                                                        <select class="form-select" name="unit_id" required>
                                                            @foreach($units as $u)
                                                                <option value="{{ $u->id }}" {{ $med->unit_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Bentuk Sediaan</label>
                                                        <input type="text" class="form-control" name="dosage_form" value="{{ $med->dosage_form }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Kekuatan / Dosis (Strength)</label>
                                                        <input type="text" class="form-control" name="strength" value="{{ $med->strength }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="purchase_price" value="{{ $med->purchase_price }}" min="0" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                                                        <input type="number" class="form-control" name="selling_price" value="{{ $med->selling_price }}" min="0" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Minimum Stok Pengingat</label>
                                                        <input type="number" class="form-control" name="min_stock" value="{{ $med->min_stock }}" min="0" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold">Status Keaktifan</label>
                                                        <select class="form-select" name="is_active">
                                                            <option value="1" {{ $med->is_active ? 'selected' : '' }}>Aktif</option>
                                                            <option value="0" {{ !$med->is_active ? 'selected' : '' }}>Nonaktif</option>
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
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-tablets fa-3x text-muted mb-2"></i>
                                    <div>Tidak ada data obat ditemukan.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$medicines" label="master obat" />
        </div>
    </div>
</div>

<!-- Add Medicine Modal -->
<div class="modal fade" id="addMedicineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('pharmacy.medicines.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Tambah Obat Baru ke Katalog</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kode Obat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="code" placeholder="MED-001" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nama Dagang (Brand) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="brand_name" placeholder="Amoxsan" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nama Generik <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="generic_name" placeholder="Amoxicillin Trihydrate" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" name="category_id" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Satuan <span class="text-danger">*</span></label>
                            <select class="form-select" name="unit_id" required>
                                <option value="">-- Pilih Satuan --</option>
                                @foreach($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Bentuk Sediaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="dosage_form" placeholder="Tablet, Kapsul, Sirup..." required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Kekuatan (Strength)</label>
                            <input type="text" class="form-control" name="strength" placeholder="500 mg, 125 mg/5ml">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="purchase_price" placeholder="1000" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="selling_price" placeholder="1500" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Minimum Stok Pengingat <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="min_stock" value="20" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Tambah Obat</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
