@extends('layouts.app')

@section('title', 'Pemeriksaan Laboratorium - ' . $request->request_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Pengujian Laboratorium & Hasil Uji</h4>
                <span class="badge {{ $request->status_badge_class }} fs-6">{{ $request->status_label }}</span>
            </div>
            <div class="text-muted small mt-1">
                No. Order: <strong>{{ $request->request_number }}</strong> | 
                Kunjungan: <strong>{{ $request->visit?->visit_number }}</strong> | 
                Dokter Pemohon: <strong>{{ $request->doctor?->formatted_name }}</strong> | 
                Waktu Permintaan: {{ $request->request_time?->format('d/m/Y H:i') }} WIB
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('laboratory.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Antrean Lab
            </a>
            @if($request->status === 'completed')
                <a href="{{ route('laboratory.print', $request->id) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-print me-1"></i> Cetak Lembar Hasil Lab
                </a>
            @endif
        </div>
    </div>

    <!-- Patient Demographics Banner -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary text-white p-3 fs-4 fw-bold text-center" style="width: 55px; height: 55px;">
                            <i class="fas fa-flask"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $request->visit?->patient?->name }}</div>
                            <div class="text-muted small">
                                No. RM: <span class="badge bg-primary">{{ $request->visit?->patient?->medical_record_number }}</span> | 
                                TTL / Usia: {{ $request->visit?->patient?->place_of_birth ?? '-' }}, {{ $request->visit?->patient?->date_of_birth?->format('d/m/Y') }} ({{ $request->visit?->patient?->age }} thn) | 
                                JK: {{ $request->visit?->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} (Gol: {{ $request->visit?->patient?->blood_type }})
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 border-start-md mt-3 mt-md-0">
                    <div class="small">
                        <div><strong class="text-muted">Poli Asal:</strong> {{ $request->visit?->polyclinic?->name }}</div>
                        <div><strong class="text-muted">Catatan Klinis:</strong> <span class="text-dark fst-italic">"{{ $request->clinical_notes ?? 'Pemeriksaan penunjang rutin' }}"</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Step 1: Specimen Collection -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-vial me-2"></i>1. Pengambilan Spesimen
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if($request->sample_collected_at)
                        <div class="alert alert-success border-0 shadow-sm mb-0">
                            <h6 class="fw-bold mb-1"><i class="fas fa-check-circle me-1"></i>Sampel Telah Diambil</h6>
                            <div class="small">
                                Petugas Pengambil: <strong>{{ $request->sample_collector ?? 'Petugas Lab' }}</strong><br>
                                Waktu Pengambilan: {{ $request->sample_collected_at?->format('d/m/Y H:i:s') }} WIB
                            </div>
                        </div>
                    @else
                        <form action="{{ route('laboratory.collect_sample', $request->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Petugas Pengambil Sampel <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="sample_collector" value="{{ auth()->user()->name }}" required placeholder="Nama analis...">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold">
                                <i class="fas fa-check me-1"></i>Konfirmasi Sampel Diterima
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Lab Info Guidelines -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="card-title mb-0 fw-bold text-dark">
                        <i class="fas fa-info-circle text-info me-2"></i>Standar Nilai Kritis
                    </h6>
                </div>
                <div class="card-body p-3 small text-muted">
                    <p class="mb-2">Jika hasil pemeriksaan berada di luar batas aman yang mengancam jiwa (nilai kritis), sistem akan memberikan penandaan merah khusus dan segera memberi notifikasi pada dokter pemohon.</p>
                    <ul class="mb-0 ps-3">
                        <li>Hemoglobin: &lt; 7.0 g/dL atau &gt; 20.0 g/dL</li>
                        <li>Glukosa Darah: &lt; 50 mg/dL atau &gt; 400 mg/dL</li>
                        <li>Trombosit: &lt; 50.000 /uL</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Step 2: Results Entry -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0 fw-bold text-primary">
                        <i class="fas fa-clipboard-check me-2"></i>2. Hasil Pengujian Laboratorium
                    </h5>
                    @if($request->status === 'completed')
                        <span class="badge bg-success"><i class="fas fa-check-double me-1"></i>Telah Diverifikasi</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('laboratory.save_results', $request->id) }}" method="POST">
                        @csrf

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Parameter Uji</th>
                                        <th style="width: 25%;">Hasil Uji <span class="text-danger">*</span></th>
                                        <th style="width: 15%;">Satuan</th>
                                        <th style="width: 20%;">Nilai Rujukan</th>
                                        <th style="width: 15%;">Catatan Analis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($request->items as $idx => $it)
                                        @php
                                            $lab = $it->laboratory;
                                            $existingResult = $request->results->firstWhere('laboratory_id', $lab?->id);
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $lab?->name }}</div>
                                                <small class="text-muted">{{ $lab?->category }} (Spesimen: {{ $lab?->specimen_type }})</small>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control fw-bold" 
                                                       name="results[{{ $lab->id }}][value]" 
                                                       value="{{ old("results.{$lab->id}.value", $existingResult?->result_value) }}" 
                                                       placeholder="Nilai angka/teks" required>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $lab?->unit ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $lab?->reference_range ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="results[{{ $lab->id }}][notes]" 
                                                       value="{{ old("results.{$lab->id}.notes", $existingResult?->notes) }}" 
                                                       placeholder="Keterangan">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('laboratory.index') }}" class="btn btn-light px-4">Kembali</a>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">
                                <i class="fas fa-save me-2"></i>Simpan & Verifikasi Hasil Lab
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
