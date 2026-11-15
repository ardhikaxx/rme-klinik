@extends('layouts.app')

@section('title', 'Penerimaan Barang Farmasi (Goods Receipt)')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Penerimaan Barang Farmasi (Goods Receipt)</h4>
            <p class="text-muted small mb-0">Pencatatan faktur supplier, nomor batch, tanggal kedaluwarsa, dan penambahan stok sistem</p>
        </div>
        <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addReceiptModal">
            <i class="fas fa-truck-loading me-1"></i> Catat Penerimaan Barang Baru
        </button>
    </div>

    <!-- Receipts Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-file-invoice me-2"></i>Daftar Faktur Penerimaan Obat
            </h5>
            <span class="small text-muted">Total: {{ $receipts->total() }} transaksi</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Penerimaan</th>
                            <th>No. Faktur Supplier</th>
                            <th>Supplier / PBF</th>
                            <th>Tanggal Terima</th>
                            <th>Rincian Item & Batch Obat</th>
                            <th>Total Nilai (Rp)</th>
                            <th>Petugas Penerima</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $rc)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $rc->receipt_number }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $rc->invoice_number }}</span></td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $rc->supplier?->name }}</div>
                                    <small class="text-muted">{{ $rc->supplier?->phone }}</small>
                                </td>
                                <td>{{ $rc->received_date->format('d/m/Y') }}</td>
                                <td>
                                    <ul class="mb-0 ps-3 small text-muted">
                                        @foreach($rc->items as $it)
                                            <li>
                                                <strong>{{ $it->medicine?->brand_name }}</strong>: {{ $it->quantity }} {{ $it->medicine?->dosage_form }} 
                                                <span class="badge bg-light text-dark border">Batch: {{ $it->batch_number }}</span> (Exp: {{ $it->expiry_date?->format('d/m/Y') }})
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">Rp{{ number_format($rc->total_amount, 0, ',', '.') }}</div>
                                </td>
                                <td>
                                    <small><i class="fas fa-user-circle me-1 text-muted"></i>{{ $rc->receiver?->name ?? 'Apoteker' }}</small>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-truck-loading fa-3x text-muted mb-2"></i>
                                    <div>Belum ada data penerimaan barang farmasi.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($receipts->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Receipt Modal -->
<div class="modal fade" id="addReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form action="{{ route('pharmacy.receipts.store') }}" method="POST" id="receiptForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-truck-loading me-2"></i>Catat Penerimaan Barang Masuk (Faktur PBF)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Invoice Header -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Supplier / Pedagang Besar Farmasi (PBF) <span class="text-danger">*</span></label>
                            <select class="form-select" name="supplier_id" required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Nomor Faktur Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="invoice_number" placeholder="FAK-2026-XXXX" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tanggal Penerimaan Fisik <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="received_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Catatan Penerimaan</label>
                            <input type="text" class="form-control" name="notes" placeholder="Kondisi kemasan baik, segel utuh, suhu penyimpanan sesuai...">
                        </div>
                    </div>

                    <hr>

                    <!-- Items Section -->
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Daftar Obat Diterima</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btnAddReceiptItem">
                            <i class="fas fa-plus me-1"></i>Tambah Item Obat
                        </button>
                    </div>

                    <div id="receiptItemsContainer">
                        <div class="receipt-item-row p-3 border rounded bg-light mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Pilih Obat <span class="text-danger">*</span></label>
                                    <select class="form-select select2-receipt-med" name="items[0][medicine_id]" required>
                                        <option value="">-- Pilih Obat --</option>
                                        @foreach($medicines as $med)
                                            <option value="{{ $med->id }}">{{ $med->brand_name }} ({{ $med->generic_name }} - {{ $med->dosage_form }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">No. Batch Fisik <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="items[0][batch_number]" placeholder="BATCH-001" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Expired Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="items[0][expiry_date]" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Jumlah Masuk <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="items[0][quantity]" value="100" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold">Harga Beli Satuan (Rp) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="items[0][purchase_price]" value="1000" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fas fa-save me-1"></i>Simpan Penerimaan & Tambah Stok</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var rIndex = 1;

    function initSelect2() {
        $('.select2-receipt-med').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addReceiptModal')
        });
    }
    initSelect2();

    $('#btnAddReceiptItem').on('click', function() {
        var html = `
            <div class="receipt-item-row p-3 border rounded bg-light mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Pilih Obat <span class="text-danger">*</span></label>
                        <select class="form-select select2-receipt-med" name="items[${rIndex}][medicine_id]" required>
                            <option value="">-- Pilih Obat --</option>
                            @foreach($medicines as $med)
                                <option value="{{ $med->id }}">{{ $med->brand_name }} ({{ $med->generic_name }} - {{ $med->dosage_form }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">No. Batch Fisik <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="items[${rIndex}][batch_number]" placeholder="BATCH-00X" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Expired Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="items[${rIndex}][expiry_date]" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Jumlah Masuk <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="items[${rIndex}][quantity]" value="100" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex justify-content-between">
                            <label class="form-label small fw-bold">Harga Beli (Rp) <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-r-item"><i class="fas fa-trash"></i></button>
                        </div>
                        <input type="number" class="form-control" name="items[${rIndex}][purchase_price]" value="1000" min="0" required>
                    </div>
                </div>
            </div>
        `;
        $('#receiptItemsContainer').append(html);
        rIndex++;
        initSelect2();
    });

    $(document).on('click', '.btn-remove-r-item', function() {
        $(this).closest('.receipt-item-row').remove();
    });
});
</script>
@endpush
