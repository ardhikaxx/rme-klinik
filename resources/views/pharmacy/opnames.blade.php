@extends('layouts.app')

@section('title', 'Stock Opname Farmasi & Penyesuaian Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Stock Opname & Penyesuaian Fisik Farmasi</h4>
            <p class="text-muted small mb-0">Pencocokan stok fisik obat dengan saldo sistem, selisih stok, dan persetujuan Kepala Klinik</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addOpnameModal">
            <i class="fas fa-clipboard-check me-1"></i> Buat Stock Opname Baru
        </button>
    </div>

    <!-- Opnames List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-tasks me-2"></i>Daftar Dokumen Stock Opname
            </h5>
            <span class="small text-muted">Total: {{ $opnames->total() }} opname</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Opname</th>
                            <th>Tanggal</th>
                            <th>Petugas Pelaksana</th>
                            <th>Status Persetujuan</th>
                            <th>Rincian Selisih Fisik & Sistem</th>
                            <th>Catatan</th>
                            <th class="text-center" style="width: 170px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($opnames as $op)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $op->opname_number }}</div>
                                </td>
                                <td>{{ $op->opname_date->format('d/m/Y') }}</td>
                                <td>
                                    <div><i class="fas fa-user-circle me-1 text-muted"></i>{{ $op->conductor?->name }}</div>
                                    @if($op->approver)
                                        <small class="text-muted">Disetujui: {{ $op->approver->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($op->status === 'approved')
                                        <span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Disetujui & Disesuaikan</span>
                                    @else
                                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Draf / Menunggu Persetujuan</span>
                                    @endif
                                </td>
                                <td>
                                    <ul class="mb-0 ps-3 small text-muted">
                                        @foreach($op->items as $it)
                                            <li>
                                                <strong>{{ $it->medicine?->brand_name }}</strong> (Batch: {{ $it->batch?->batch_number }}): 
                                                Sistem: {{ $it->system_stock }} | Fisik: {{ $it->physical_stock }} | 
                                                <span class="fw-bold {{ $it->difference < 0 ? 'text-danger' : ($it->difference > 0 ? 'text-success' : 'text-muted') }}">
                                                    Selisih: {{ $it->difference > 0 ? '+' . $it->difference : $it->difference }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td><small class="text-muted">{{ $op->notes ?? '-' }}</small></td>
                                <td class="text-center">
                                    @if($op->status === 'draft' && auth()->user()->hasAnyRole(['kepala_klinik']))
                                        <form action="{{ route('pharmacy.opnames.approve', $op->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success fw-bold" onclick="return confirm('Apakah Anda yakin menyetujui stock opname ini? Stok sistem obat akan langsung disesuaikan.')">
                                                <i class="fas fa-check me-1"></i>Setujui
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-clipboard-check fa-3x text-muted mb-2"></i>
                                    <div>Belum ada dokumen stock opname tercatat.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($opnames->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $opnames->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Opname Modal -->
<div class="modal fade" id="addOpnameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('pharmacy.opnames.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-clipboard-check me-2"></i>Entri Stock Opname Fisik Farmasi</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal Pelaksanaan Opname <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="opname_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Catatan Umum</label>
                            <input type="text" class="form-control" name="notes" placeholder="Contoh: Opname bulanan gudang farmasi...">
                        </div>
                    </div>

                    <hr>

                    <h6 class="fw-bold text-dark mb-3">Item Obat yang Diuji Fisik:</h6>
                    <div id="opnameItemsContainer">
                        @php $firstMed = $medicines->first(); @endphp
                        <div class="p-3 border rounded bg-light mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Obat</label>
                                    <select class="form-select opname-med-select" name="items[0][medicine_id]" required>
                                        @foreach($medicines as $med)
                                            <option value="{{ $med->id }}">{{ $med->brand_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Batch</label>
                                    <select class="form-select" name="items[0][batch_id]" required>
                                        @foreach($medicines->first()?->batches ?? [] as $b)
                                            <option value="{{ $b->id }}">{{ $b->batch_number }} (Sistem: {{ $b->current_stock }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Stok Fisik</label>
                                    <input type="number" class="form-control" name="items[0][physical_stock]" min="0" required placeholder="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Alasan Selisih</label>
                                    <input type="text" class="form-control" name="items[0][reason]" placeholder="Rusak, bocor, dll">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i>Simpan Draf Stock Opname</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
