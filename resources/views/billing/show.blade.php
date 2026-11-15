@extends('layouts.app')

@section('title', 'Kasir & Rincian Tagihan - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Kasir & Tagihan Pasien</h4>
                <span class="badge {{ $invoice->status_badge_class }} fs-6">{{ $invoice->status_label }}</span>
            </div>
            <div class="text-muted small mt-1">
                No. Tagihan: <strong>{{ $invoice->invoice_number }}</strong> | 
                Kunjungan: <strong>{{ $invoice->visit?->visit_number }}</strong> | 
                Waktu Terbit: {{ $invoice->created_at->format('d/m/Y H:i') }} WIB
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Daftar Kasir
            </a>

            @if($invoice->balance_due > 0)
                <button type="button" class="btn btn-success fw-bold px-4" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-money-bill-wave me-1"></i> Proses Pembayaran
                </button>
                <button type="button" class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#discountModal">
                    <i class="fas fa-percentage me-1"></i> Terapkan Diskon
                </button>
            @endif

            @if($invoice->paid_amount > 0)
                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#refundModal">
                    <i class="fas fa-undo me-1"></i> Ajukan Refund
                </button>
            @endif
        </div>
    </div>

    <!-- Patient Details Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $invoice->patient?->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $invoice->patient?->medical_record_number }}</span> | 
                                NIK: {{ $invoice->patient?->nik ?? '-' }} | 
                                Usia: {{ $invoice->patient?->age }} thn | 
                                Alamat: {{ $invoice->patient?->full_address }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Poli:</strong> {{ $invoice->visit?->polyclinic?->name }}</div>
                        <div><strong class="text-muted">Dokter:</strong> {{ $invoice->visit?->doctor?->formatted_name }}</div>
                        <div><strong class="text-muted">Penjamin:</strong> <span class="badge bg-secondary text-uppercase">{{ $invoice->visit?->payment_type }}</span> {{ $invoice->visit?->insurance_number ? '(' . $invoice->visit?->insurance_number . ')' : '' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Itemized Bill -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-receipt me-2"></i>Rincian Komponen Biaya Pelayanan (Itemized Bill)
                    </h5>
                    <span class="small text-muted">{{ $invoice->items->count() }} item tagihan</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Kategori Layanan</th>
                                    <th>Uraian / Tindakan / Obat</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-end">Tarif Satuan</th>
                                    <th class="text-end">Subtotal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $idx => $item)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>
                                            @php
                                                $catBadge = match($item->item_type) {
                                                    'administrasi' => 'bg-secondary',
                                                    'konsultasi' => 'bg-info text-white',
                                                    'tindakan_medis' => 'bg-primary',
                                                    'laboratorium' => 'bg-warning text-dark',
                                                    'obat' => 'bg-success',
                                                    default => 'bg-light text-dark border',
                                                };
                                            @endphp
                                            <span class="badge {{ $catBadge }} text-capitalize">{{ str_replace('_', ' ', $item->item_type) }}</span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $item->item_name }}</div>
                                            @if($item->item_code)
                                                <small class="text-muted">Kode: {{ $item->item_code }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end text-muted">
                                            Rp{{ number_format($item->unit_price, 0, ',', '.') }}
                                        </td>
                                        <td class="text-end fw-bold text-dark">
                                            Rp{{ number_format($item->subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada item tagihan yang digenerate.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment Transactions History -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-success">
                        <i class="fas fa-history me-2"></i>Histori Pembayaran & Kuitansi Kasir
                    </h5>
                    <span class="badge bg-light text-success border">{{ $invoice->payments->count() }} Pembayaran</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Kuitansi</th>
                                    <th>Waktu Bayar</th>
                                    <th>Metode</th>
                                    <th class="text-end">Jumlah Bayar</th>
                                    <th>Kasir</th>
                                    <th class="text-center">Cetak Kuitansi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->payments as $pay)
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $pay->receipt_number }}</td>
                                        <td>{{ $pay->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $pay->paymentMethod?->name ?? 'Tunai' }}</span>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            Rp{{ number_format($pay->amount_paid, 0, ',', '.') }}
                                            @if($pay->amount_tendered)
                                                <small class="d-block text-muted">Diterima: Rp{{ number_format($pay->amount_tendered, 0, ',', '.') }} | Kembalian: Rp{{ number_format($pay->change_amount, 0, ',', '.') }}</small>
                                            @endif
                                        </td>
                                        <td><small><i class="fas fa-user-circle me-1 text-muted"></i>{{ $pay->cashier?->name }}</small></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('billing.receipt', $pay->id) }}?format=thermal" class="btn btn-outline-dark" target="_blank" title="Struk Thermal 58/80mm">
                                                    <i class="fas fa-receipt me-1"></i>Thermal
                                                </a>
                                                <a href="{{ route('billing.receipt', $pay->id) }}?format=a4" class="btn btn-outline-primary" target="_blank" title="Kuitansi A4">
                                                    <i class="fas fa-file-invoice me-1"></i>A4
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-3 text-muted">Belum ada transaksi pembayaran untuk tagihan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Refunds History if any -->
            @if($invoice->refunds && $invoice->refunds->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 border-bottom text-danger fw-bold">
                        <i class="fas fa-undo me-2"></i>Histori Pengembalian Dana (Refund)
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Refund</th>
                                    <th>Waktu</th>
                                    <th>Jumlah Refund</th>
                                    <th>Alasan</th>
                                    <th>Disetujui Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->refunds as $ref)
                                    <tr>
                                        <td class="fw-bold text-danger">{{ $ref->refund_number }}</td>
                                        <td>{{ $ref->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="fw-bold text-danger">Rp{{ number_format($ref->amount, 0, ',', '.') }}</td>
                                        <td>{{ $ref->reason }}</td>
                                        <td>{{ $ref->approver?->name ?? 'Kasir' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary & Balance Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-calculator me-2 text-primary"></i>Ringkasan Keuangan
                    </h5>
                </div>
                <div class="card-body p-4">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Total Tagihan Bruto:</td>
                            <td class="text-end fw-bold">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                            <tr>
                                <td class="text-danger">Diskon / Potongan:</td>
                                <td class="text-end text-danger fw-bold">- Rp{{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-muted small fst-italic pt-0">Alasan: {{ $invoice->discount_reason }}</td>
                            </tr>
                        @endif
                        <tr class="border-top">
                            <td class="fw-bold text-dark fs-6">Total Tagihan Bersih:</td>
                            <td class="text-end fw-bold text-primary fs-5">Rp{{ number_format($invoice->final_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-success fw-bold">Total Telah Dibayar:</td>
                            <td class="text-end text-success fw-bold fs-6">Rp{{ number_format($invoice->paid_amount, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-top border-2">
                            <td class="fw-bold text-dark fs-5">Sisa Tagihan:</td>
                            <td class="text-end fw-bold {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }} fs-4">
                                Rp{{ number_format($invoice->balance_due, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>

                    @if($invoice->balance_due > 0)
                        <div class="mt-4">
                            <button type="button" class="btn btn-success btn-lg w-100 fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="fas fa-cash-register me-2"></i>Bayar Sisa Tagihan
                            </button>
                        </div>
                    @else
                        <div class="alert alert-success mt-4 mb-0 text-center fw-bold">
                            <i class="fas fa-check-circle me-1"></i> Tagihan Pasien Telah Lunas
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Process Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('billing.pay', $invoice->id) }}" method="POST" id="formProcessPayment">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-cash-register me-2"></i>Penerimaan Pembayaran Kasir</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select class="form-select" name="payment_method_id" id="payment_method_id" required>
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}" data-type="{{ $pm->type }}">{{ $pm->name }} ({{ strtoupper($pm->type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control fw-bold fs-5 text-primary" name="amount_paid" id="amount_paid" 
                               value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" min="1" required>
                        <div class="form-text">Dapat menerima pembayaran sebagian (parsial). Sisa tagihan saat ini: Rp{{ number_format($invoice->balance_due, 0, ',', '.') }}</div>
                    </div>

                    <div class="mb-3" id="cashCalculator">
                        <label class="form-label fw-bold">Uang Diterima dari Pasien (Rp)</label>
                        <input type="number" class="form-control fs-5" name="amount_tendered" id="amount_tendered" placeholder="0">
                        <div class="d-flex justify-content-between align-items-center mt-2 p-2 border rounded bg-light">
                            <span class="fw-bold text-dark">Kembalian Pasien:</span>
                            <span class="fs-5 fw-bold text-success" id="changeAmountText">Rp0</span>
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Catatan Pembayaran (Opsional)</label>
                        <input type="text" class="form-control" name="notes" placeholder="No. referensi transfer, kode otorisasi EDC...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold"><i class="fas fa-check me-1"></i>Proses Pembayaran & Terbitkan Kuitansi</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Apply Discount -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('billing.discount', $invoice->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-percentage me-2"></i>Terapkan Diskon Tagihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Besaran Diskon (Nominal Rupiah) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="discount_amount" min="1" max="{{ $invoice->final_amount }}" required placeholder="Contoh: 25000">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Alasan Pemberian Diskon <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="discount_reason" rows="2" required placeholder="Contoh: Keringanan pasien kurang mampu, program CSR klinik, diskon karyawan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fas fa-check me-1"></i>Terapkan Diskon</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Refund -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('billing.refund', $invoice->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-undo me-2"></i>Pengembalian Dana (Refund)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal Refund (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="refund_amount" min="1" max="{{ $invoice->paid_amount }}" required placeholder="Maksimal: {{ $invoice->paid_amount }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaksi Pembayaran yang Direfund</label>
                        <select class="form-select" name="payment_id">
                            @foreach($invoice->payments as $p)
                                <option value="{{ $p->id }}">{{ $p->receipt_number }} - Rp{{ number_format($p->amount_paid, 0, ',', '.') }} ({{ $p->paymentMethod?->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Alasan Refund <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="reason" rows="2" required placeholder="Contoh: Pasien retur obat yang belum dibuka, salah input kasir..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="fas fa-undo me-1"></i>Konfirmasi Refund</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    function calculateChange() {
        var paid = parseFloat($('#amount_paid').val()) || 0;
        var tendered = parseFloat($('#amount_tendered').val()) || 0;
        var change = tendered - paid;
        if (change >= 0) {
            $('#changeAmountText').text('Rp' + change.toLocaleString('id-ID'));
        } else {
            $('#changeAmountText').text('Rp0 (Uang Kurang)');
        }
    }

    $('#amount_paid, #amount_tendered').on('input change', calculateChange);
});
</script>
@endpush
