@extends('layouts.app')

@section('title', 'Detail Resep & Dispensing - ' . $prescription->prescription_number)
@section('page_title', 'Telaah Resep & Dispensing Obat')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('pharmacy.prescriptions') }}">Farmasi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Telaah {{ $prescription->prescription_number }}</li>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Telaah Resep & Dispensing Obat</h4>
                <span class="badge {{ $prescription->status_badge_class }} fs-6">{{ $prescription->status_label }}</span>
            </div>
            <div class="text-muted small mt-1">
                No. Resep: <strong>{{ $prescription->prescription_number }}</strong> | 
                Kunjungan: <strong>{{ $prescription->visit?->visit_number }}</strong> | 
                Dokter: <strong>{{ $prescription->doctor?->formatted_name }}</strong> | 
                Waktu Resep: {{ $prescription->created_at->format('d/m/Y H:i') }} WIB
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('pharmacy.prescriptions') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Daftar Resep
            </a>
            <a href="{{ route('pharmacy.print_label', $prescription->id) }}" class="btn btn-outline-primary" target="_blank">
                <i class="fas fa-tag me-1"></i> Cetak Etiket Obat
            </a>
        </div>
    </div>

    <!-- Patient Info & Allergy Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning text-dark p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            <i class="fas fa-pills"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $prescription->visit?->patient?->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $prescription->visit?->patient?->medical_record_number }}</span> | 
                                TTL / Usia: {{ $prescription->visit?->patient?->place_of_birth ?? '-' }}, {{ $prescription->visit?->patient?->date_of_birth?->format('d/m/Y') }} ({{ $prescription->visit?->patient?->age }} thn) | 
                                Gender: {{ $prescription->visit?->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} | 
                                BB: {{ $prescription->visit?->nursingAssessment?->weight ?? '-' }} kg
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Poli Asal:</strong> {{ $prescription->visit?->polyclinic?->name }}</div>
                        <div><strong class="text-muted">Penjamin:</strong> <span class="badge bg-secondary text-uppercase">{{ $prescription->visit?->payment_type }}</span></div>
                        @if($prescription->doctor_notes)
                            <div class="mt-1"><strong class="text-muted">Catatan Dokter:</strong> <span class="text-dark fst-italic">"{{ $prescription->doctor_notes }}"</span></div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Allergy Alert -->
            @if($prescription->visit?->patient?->allergies && $prescription->visit?->patient?->allergies->count() > 0)
                <div class="alert alert-danger mt-3 mb-0 py-2 d-flex align-items-center" role="alert">
                    <i class="fas fa-hand-dots fs-4 me-2 text-danger"></i>
                    <div>
                        <strong class="text-danger">Peringatan Kritis Alergi Pasien:</strong>
                        @foreach($prescription->visit?->patient?->allergies as $alg)
                            <span class="badge bg-danger ms-1">{{ $alg->allergen_name }} ({{ $alg->reaction }})</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Items to Dispense -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-list-check me-2"></i>Daftar Obat yang Diresepkan
            </h5>
            <span class="badge bg-light text-primary border">Alokasi Stok FEFO (First Expired First Out)</span>
        </div>
        <div class="card-body p-0">
            <!-- Non-Compound Medicines -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Obat & Komposisi</th>
                            <th>Bentuk & Satuan</th>
                            <th class="text-center">Jumlah</th>
                            <th>Dosis & Aturan Pakai</th>
                            <th>Instruksi / Waktu Konsumsi</th>
                            <th>Batch FEFO Tersedia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescription->items as $idx => $item)
                            @php 
                                $med = $item->medicine;
                                $activeBatches = $med?->batches?->where('current_stock', '>', 0)->sortBy('expiry_date');
                            @endphp
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $med?->brand_name }}</div>
                                    <small class="text-muted">{{ $med?->generic_name }} ({{ $med?->strength }})</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $med?->dosage_form }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold fs-6 text-primary">{{ $item->quantity }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $item->dosage }}</div>
                                    <small class="text-muted">{{ $item->frequency }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $item->instructions }}</span>
                                    @if($item->duration_days)
                                        <div class="small text-muted mt-1">Durasi: {{ $item->duration_days }} hari</div>
                                    @endif
                                </td>
                                <td>
                                    @if($activeBatches && $activeBatches->count() > 0)
                                        @foreach($activeBatches->take(2) as $b)
                                            <div class="small">
                                                <span class="badge bg-light text-dark border">{{ $b->batch_number }}</span>
                                                <span class="text-muted">(Exp: {{ $b->expiry_date->format('d/m/Y') }}, Sisa: {{ $b->current_stock }})</span>
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="badge bg-danger">Stok Habis!</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">Tidak ada item obat non-racikan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Compound Medicines (Racikan) if any -->
            @if($prescription->compounds && $prescription->compounds->count() > 0)
                <div class="p-3 bg-light border-top">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-mortar-pestle me-2 text-warning"></i>Obat Racikan (Compound Prescription):</h6>
                    @foreach($prescription->compounds as $cIdx => $comp)
                        <div class="card border mb-2">
                            <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <strong class="text-primary">{{ $comp->compound_name }}</strong> 
                                    <span class="badge bg-secondary text-uppercase ms-2">{{ $comp->dosage_form }}</span>
                                </div>
                                <div>
                                    Jumlah: <strong>{{ $comp->quantity }} Bungkus/Kapsul</strong> | 
                                    Aturan Pakai: <strong>{{ $comp->dosage }}, {{ $comp->frequency }} ({{ $comp->instructions }})</strong>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Bahan Obat</th>
                                            <th>Dosis per Bungkus</th>
                                            <th>Total Kebutuhan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($comp->items as $cItem)
                                            <tr>
                                                <td>{{ $cItem->medicine?->brand_name }} ({{ $cItem->medicine?->generic_name }})</td>
                                                <td>{{ $cItem->dose_per_unit }}</td>
                                                <td class="fw-bold">{{ $cItem->quantity_used }} {{ $cItem->medicine?->dosage_form }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Pharmacist Review & Dispensing Section -->
    <div class="row g-4">
        <!-- 1. Telaah Farmasi Box -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-clipboard-check me-2"></i>1. Telaah Resep (7 Benar Farmasi)
                    </h5>
                    @if($prescription->status !== 'draft' && $prescription->status !== 'menunggu_telaah')
                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Sudah Diverifikasi</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <div class="mb-3 small">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked disabled id="c1">
                            <label class="form-check-label" for="c1">Tepat Pasien, Umur, Berat Badan & Jenis Kelamin</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked disabled id="c2">
                            <label class="form-check-label" for="c2">Tepat Nama Obat, Bentuk Sediaan & Kekuatan</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked disabled id="c3">
                            <label class="form-check-label" for="c3">Tepat Dosis, Frekuensi & Cara Pemberian</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" checked disabled id="c4">
                            <label class="form-check-label" for="c4">Tidak Ada Duplikasi Obat & Polifarmasi Tidak Perlu</label>
                        </div>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" checked disabled id="c5">
                            <label class="form-check-label" for="c5">Tidak Ada Kontraindikasi & Reaksi Alergi Diketahui</label>
                        </div>
                    </div>

                    @if($prescription->pharmacist_notes)
                        <div class="alert alert-info py-2 small mb-3">
                            <strong>Catatan Apoteker:</strong> {{ $prescription->pharmacist_notes }}
                        </div>
                    @endif

                    @if($prescription->status === 'menunggu_telaah' || $prescription->status === 'draft')
                        <form action="{{ route('pharmacy.verify', $prescription->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Catatan Telaah Apoteker / TTK</label>
                                <textarea class="form-control" name="pharmacist_notes" rows="2" placeholder="Contoh: Resep absah, dosis amoxicillin sesuai BB anak, tidak ada interaksi..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="fas fa-check-circle me-1"></i>Konfirmasi Verifikasi Telaah Resep
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Dispensing & Stock Deduction Box -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-success">
                        <i class="fas fa-hand-holding-medical me-2"></i>2. Dispensing & Pemotongan Stok
                    </h5>
                    @if($prescription->dispensingTransaction)
                        <span class="badge bg-success">{{ $prescription->dispensingTransaction->dispensing_number }}</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    @if($prescription->dispensingTransaction)
                        <div class="alert alert-success border-0 shadow-sm mb-3">
                            <h6 class="fw-bold mb-1"><i class="fas fa-check-double me-2"></i>Obat Telah Didispensing</h6>
                            <div class="small">
                                No. Transaksi: <strong>{{ $prescription->dispensingTransaction->dispensing_number }}</strong><br>
                                Waktu Dispensing: {{ $prescription->dispensingTransaction->created_at->format('d/m/Y H:i:s') }} WIB<br>
                                Petugas Farmasi: {{ $prescription->dispensingTransaction->pharmacist?->name ?? 'Apoteker' }}
                            </div>
                        </div>

                        <!-- Deducted Batches Details -->
                        <h6 class="fw-bold small text-dark mb-2">Riwayat Pemotongan Stok Batch:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Obat</th>
                                        <th>No. Batch</th>
                                        <th>Expired</th>
                                        <th class="text-center">Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescription->dispensingTransaction->items as $dItem)
                                        <tr>
                                            <td>{{ $dItem->medicine?->brand_name }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $dItem->batch?->batch_number }}</span></td>
                                            <td>{{ $dItem->batch?->expiry_date?->format('d/m/Y') }}</td>
                                            <td class="text-center fw-bold">{{ $dItem->quantity }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 border rounded bg-light mb-4">
                            <h6 class="fw-bold text-dark mb-2"><i class="fas fa-shield-alt text-primary me-2"></i>Mekanisme Keamanan Transaksi</h6>
                            <ul class="mb-0 ps-3 small text-muted">
                                <li>Pemotongan stok batch dilakukan secara <strong>First Expired First Out (FEFO)</strong>.</li>
                                <li>Menggunakan <strong>Row-Level Locking (lockForUpdate())</strong> untuk mencegah stok negatif akibat akses bersamaan.</li>
                                <li>Setiap pengeluaran dicatat ke <strong>Kartu Stok (MedicineStockMovement)</strong> secara otomatis.</li>
                                <li>Biaya obat otomatis ditambahkan ke <strong>Billing Tagihan Pasien</strong>.</li>
                            </ul>
                        </div>

                        @if($prescription->status === 'menunggu_dispensing')
                            <form id="dispenseForm" action="{{ route('pharmacy.dispense', $prescription->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Catatan Penyerahan Obat ke Pasien (Opsional)</label>
                                    <input type="text" class="form-control" name="notes" placeholder="Contoh: Pasien telah diberikan penjelasan cara minum obat...">
                                </div>
                                <button type="button" class="btn btn-success btn-lg w-100 fw-bold" id="btnDispense">
                                    <i class="fas fa-check-double me-2"></i>Dispensing Obat & Potong Stok FEFO
                                </button>
                            </form>
                        @else
                            <div class="text-center py-3 text-muted">
                                <i class="fas fa-info-circle me-1"></i> Selesaikan telaah resep terlebih dahulu sebelum melakukan dispensing obat.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnDispense').on('click', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Dispensing Obat?',
            text: 'Sistem akan memotong stok batch fisik obat secara FEFO dan mencatat transaksi ke kartu stok & billing pasien.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Dispensing Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#dispenseForm').submit();
            }
        });
    });
});
</script>
@endpush
