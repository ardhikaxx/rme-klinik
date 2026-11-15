@extends('layouts.app')

@section('title', 'Pembuatan Resep Elektronik - ' . $visit->patient->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Pembuatan Resep Elektronik (E-Prescription)</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('clinical.examination', $visit->id) }}">Pemeriksaan</a></li>
                    <li class="breadcrumb-item active">Buat Resep Baru</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Pemeriksaan
        </a>
    </div>

    <!-- Patient Header Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning text-dark p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            <i class="fas fa-prescription"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $visit->patient->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $visit->patient->medical_record_number }}</span> | 
                                Usia: {{ $visit->patient->age }} thn ({{ $visit->patient->date_of_birth?->format('d/m/Y') }}) | 
                                JK: {{ $visit->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} | 
                                BB: {{ $visit->nursingAssessment?->weight ?? '-' }} kg
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Poli:</strong> {{ $visit->polyclinic?->name }} | <strong class="text-muted">Dokter:</strong> {{ $visit->doctor?->formatted_name }}</div>
                        <div><strong class="text-muted">Diagnosis Kerja:</strong> {{ $visit->medicalRecord?->assessment ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Allergy Alert -->
            @if($visit->patient->allergies && $visit->patient->allergies->count() > 0)
                <div class="alert alert-danger mt-3 mb-0 py-2 d-flex align-items-center" role="alert">
                    <i class="fas fa-hand-dots fs-5 me-2 text-danger"></i>
                    <div>
                        <strong class="text-danger">Peringatan Alergi Obat Pasien:</strong>
                        @foreach($visit->patient->allergies as $alg)
                            <span class="badge bg-danger ms-1">{{ $alg->allergen_name }} ({{ $alg->reaction }})</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Prescription Form -->
    <form action="{{ route('pharmacy.store_prescription', $visit->id) }}" method="POST" id="prescForm">
        @csrf

        <!-- Section 1: Non-Racikan Medicines -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-pills me-2"></i>1. Obat Non-Racikan (Obat Jadi / Tablet / Sirup)
                </h5>
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" id="btnAddItem">
                    <i class="fas fa-plus me-1"></i>Tambah Obat
                </button>
            </div>
            <div class="card-body p-4">
                <div id="itemsContainer">
                    <div class="item-row p-3 border rounded bg-light mb-3">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Pilih Obat & Sisa Stok <span class="text-danger">*</span></label>
                                <select class="form-select select2-med" name="items[0][medicine_id]" required>
                                    <option value="">-- Ketik Nama Obat --</option>
                                    @foreach($medicines as $m)
                                        <option value="{{ $m->id }}">
                                            {{ $m->brand_name }} ({{ $m->generic_name }} - {{ $m->strength }}) [Stok: {{ $m->total_stock }} {{ $m->dosage_form }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="items[0][quantity]" value="10" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Dosis <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="items[0][dosage]" value="1 Tablet" required placeholder="1 Tablet/5ml">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-bold">Frekuensi <span class="text-danger">*</span></label>
                                <select class="form-select" name="items[0][frequency]" required>
                                    <option value="3 x sehari" selected>3 x sehari</option>
                                    <option value="2 x sehari">2 x sehari</option>
                                    <option value="1 x sehari">1 x sehari</option>
                                    <option value="4 x sehari">4 x sehari</option>
                                    <option value="Bila perlu (prn)">Bila perlu (prn)</option>
                                </select>
                            </div>
                            <div class="col-md-2 text-end">
                                <label class="form-label small d-block">&nbsp;</label>
                                <button type="button" class="btn btn-outline-danger btn-remove-item"><i class="fas fa-trash me-1"></i>Hapus</button>
                            </div>
                        </div>

                        <div class="row g-2 align-items-center mt-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Instruksi Konsumsi <span class="text-danger">*</span></label>
                                <select class="form-select" name="items[0][instructions]" required>
                                    <option value="Sesudah makan" selected>Sesudah makan</option>
                                    <option value="Sebelum makan">Sebelum makan</option>
                                    <option value="Bersama makan">Bersama makan</option>
                                    <option value="Sebelum tidur">Sebelum tidur</option>
                                    <option value="Habiskan (Antibiotik)">Habiskan (Antibiotik)</option>
                                    <option value="Oleskan tipis pada luka">Oleskan tipis pada luka</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Rute Pemberian</label>
                                <select class="form-select" name="items[0][route]">
                                    <option value="Oral" selected>Oral</option>
                                    <option value="Topikal">Topikal</option>
                                    <option value="Sublingual">Sublingual</option>
                                    <option value="Inhalasi">Inhalasi</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Durasi (Hari)</label>
                                <input type="number" class="form-control" name="items[0][duration_days]" value="3" min="1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Compound Medicines (Racikan) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold text-primary">
                    <i class="fas fa-mortar-pestle me-2"></i>2. Obat Racikan (Opsional / Puyer Anak / Kapsul)
                </h5>
                <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold" id="btnAddCompound">
                    <i class="fas fa-plus me-1"></i>Tambah Racikan Baru
                </button>
            </div>
            <div class="card-body p-4" id="compoundsContainer">
                <div class="text-muted small text-center py-2" id="compoundEmptyText">
                    Klik tombol <strong>+ Tambah Racikan Baru</strong> jika pasien membutuhkan puyer, kapsul, atau sirup racikan.
                </div>
            </div>
        </div>

        <!-- Doctor Notes & Submit -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Khusus Dokter untuk Apoteker</label>
                    <textarea class="form-control" name="doctor_notes" rows="2" placeholder="Contoh: Pasien ada riwayat maag, kurangi dosis paracetamol jika demam reda..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-light px-4">Batal</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold">
                        <i class="fas fa-paper-plane me-2"></i>Kirim E-Resep ke Farmasi
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var itemIndex = 1;
    var compoundIndex = 0;

    function initSelect2() {
        $('.select2-med').select2({
            theme: 'bootstrap-5',
            placeholder: 'Pilih obat...'
        });
    }
    initSelect2();

    // Add item
    $('#btnAddItem').on('click', function() {
        var html = `
            <div class="item-row p-3 border rounded bg-light mb-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Pilih Obat & Sisa Stok <span class="text-danger">*</span></label>
                        <select class="form-select select2-med" name="items[${itemIndex}][medicine_id]" required>
                            <option value="">-- Ketik Nama Obat --</option>
                            @foreach($medicines as $m)
                                <option value="{{ $m->id }}">{{ $m->brand_name }} ({{ $m->generic_name }} - {{ $m->strength }}) [Stok: {{ $m->total_stock }} {{ $m->dosage_form }}]</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Jumlah <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="items[${itemIndex}][quantity]" value="10" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Dosis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="items[${itemIndex}][dosage]" value="1 Tablet" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Frekuensi <span class="text-danger">*</span></label>
                        <select class="form-select" name="items[${itemIndex}][frequency]" required>
                            <option value="3 x sehari" selected>3 x sehari</option>
                            <option value="2 x sehari">2 x sehari</option>
                            <option value="1 x sehari">1 x sehari</option>
                            <option value="Bila perlu (prn)">Bila perlu (prn)</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-end">
                        <label class="form-label small d-block">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger btn-remove-item"><i class="fas fa-trash me-1"></i>Hapus</button>
                    </div>
                </div>
                <div class="row g-2 align-items-center mt-2">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Instruksi Konsumsi <span class="text-danger">*</span></label>
                        <select class="form-select" name="items[${itemIndex}][instructions]" required>
                            <option value="Sesudah makan" selected>Sesudah makan</option>
                            <option value="Sebelum makan">Sebelum makan</option>
                            <option value="Bersama makan">Bersama makan</option>
                            <option value="Habiskan (Antibiotik)">Habiskan (Antibiotik)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Rute Pemberian</label>
                        <select class="form-select" name="items[${itemIndex}][route]">
                            <option value="Oral" selected>Oral</option>
                            <option value="Topikal">Topikal</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Durasi (Hari)</label>
                        <input type="number" class="form-control" name="items[${itemIndex}][duration_days]" value="3" min="1">
                    </div>
                </div>
            </div>
        `;
        $('#itemsContainer').append(html);
        itemIndex++;
        initSelect2();
    });

    $(document).on('click', '.btn-remove-item', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('.item-row').remove();
        } else {
            Swal.fire('Info', 'Resep minimal memiliki 1 item obat.', 'info');
        }
    });

    // Add Compound
    $('#btnAddCompound').on('click', function() {
        $('#compoundEmptyText').hide();
        var html = `
            <div class="compound-card p-3 border rounded bg-light mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0">Racikan #${compoundIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-comp"><i class="fas fa-times me-1"></i>Hapus Racikan</button>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Nama Racikan</label>
                        <input type="text" class="form-control" name="compounds[${compoundIndex}][compound_name]" value="Puyer Batuk Pilek" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Bentuk Sediaan</label>
                        <select class="form-select" name="compounds[${compoundIndex}][dosage_form]">
                            <option value="puyer">Puyer / Serbuk</option>
                            <option value="kapsul">Kapsul</option>
                            <option value="sirup">Sirup</option>
                            <option value="salep">Salep</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Jumlah Bungkus</label>
                        <input type="number" class="form-control" name="compounds[${compoundIndex}][quantity]" value="10" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Aturan Pakai</label>
                        <input type="text" class="form-control" name="compounds[${compoundIndex}][dosage]" value="3 x 1 bungkus" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold">Instruksi</label>
                        <input type="text" class="form-control" name="compounds[${compoundIndex}][instructions]" value="Sesudah makan" required>
                    </div>
                </div>
            </div>
        `;
        $('#compoundsContainer').append(html);
        compoundIndex++;
    });

    $(document).on('click', '.btn-remove-comp', function() {
        $(this).closest('.compound-card').remove();
        if ($('.compound-card').length === 0) {
            $('#compoundEmptyText').show();
        }
    });
});
</script>
@endpush
