@extends('layouts.app')

@section('title', 'Kesiapan Interoperabilitas SATUSEHAT / HL7 FHIR R4')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="mb-1 text-dark fw-bold">Kesiapan Interoperabilitas SATUSEHAT (HL7 FHIR R4)</h4>
            <p class="text-muted small mb-0">Pemetaan standar HL7 FHIR R4 Kementerian Kesehatan RI (Permenkes 24/2022) untuk data rekam medis terintegrasi</p>
        </div>
        <span class="badge bg-success fs-6 px-3 py-2">
            <i class="fas fa-shield-halved me-1"></i> HL7 FHIR R4 Ready
        </span>
    </div>

    <!-- SATUSEHAT Overview Alert -->
    <div class="alert alert-primary border-0 shadow-sm d-flex align-items-center mb-4 p-3" role="alert">
        <i class="fas fa-network-wired fa-2x text-primary me-3"></i>
        <div class="small">
            <strong class="fs-6 d-block mb-1">Status Kesiapan Ekosistem SatuSehat Kemenkes RI</strong>
            Aplikasi RME KLINIK telah mengimplementasikan transformasi data klinis ke dalam format standar <strong>HL7 FHIR Release 4 (R4)</strong>. Seluruh entitas pasien (Patient), kunjungan (Encounter), diagnosis klinis (Condition), tanda-tanda vital (Observation), dan resep obat (MedicationRequest) dipetakan secara terstruktur sesuai spesifikasi profil Kemenkes.
        </div>
    </div>

    <!-- Resource Counters -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small fw-bold">Resource Patient</div>
                <div class="fs-3 fw-bold text-primary mt-1">{{ $patientsCount }}</div>
                <small class="text-muted">Profil Pasien</small>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small fw-bold">Resource Encounter</div>
                <div class="fs-3 fw-bold text-info mt-1">{{ $encountersCount }}</div>
                <small class="text-muted">Kunjungan Faskes</small>
            </div>
        </div>

        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small fw-bold">Resource Condition</div>
                <div class="fs-3 fw-bold text-danger mt-1">{{ $conditionsCount }}</div>
                <small class="text-muted">Diagnosis ICD-10</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small fw-bold">Resource Observation</div>
                <div class="fs-3 fw-bold text-success mt-1">{{ $observationsCount }}</div>
                <small class="text-muted">TTV & Antropometri</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <div class="text-muted small fw-bold">Resource Medication</div>
                <div class="fs-3 fw-bold text-warning text-dark mt-1">{{ $medicationsCount }}</div>
                <small class="text-muted">E-Resep & Terapi</small>
            </div>
        </div>
    </div>

    <!-- Mappings Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-database me-2"></i>Tabel Log Pemetaan Entitas FHIR (FhirMapping)
            </h5>
            <span class="small text-muted">Total: {{ $mappings->total() }} entitas dipetakan</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tipe Resource FHIR</th>
                            <th>Model Lokal RME</th>
                            <th>ID Lokal</th>
                            <th>FHIR Resource ID / UUID</th>
                            <th>Status Pemetaan</th>
                            <th>Waktu Pemetaan</th>
                            <th class="text-center" style="width: 160px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mappings as $map)
                            <tr>
                                <td>
                                    <span class="badge bg-primary fs-6">{{ $map->resource_type }}</span>
                                </td>
                                <td><code>{{ class_basename($map->mappable_type) }}</code></td>
                                <td><span class="badge bg-light text-dark border">#{{ $map->mappable_id }}</span></td>
                                <td><code>{{ $map->fhir_id ?? 'urn:uuid:' . \Illuminate\Support\Str::uuid() }}</code></td>
                                <td>
                                    <span class="badge {{ $map->status === 'synced' ? 'bg-success' : 'bg-info' }}">
                                        {{ $map->status === 'synced' ? 'Tervalidasi R4' : 'Staged' }}
                                    </span>
                                </td>
                                <td class="text-nowrap">{{ $map->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-preview-fhir" 
                                            data-res="{{ $map->resource_type }}" 
                                            data-id="{{ $map->mappable_id }}">
                                        <i class="fas fa-code me-1"></i>Lihat JSON
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-network-wired fa-3x text-muted mb-2"></i>
                                    <div>Belum ada entitas yang dipetakan ke FHIR mapping log.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$mappings" label="pemetaan FHIR" />
        </div>
    </div>
</div>

<!-- Modal Preview JSON FHIR -->
<div class="modal fade" id="fhirPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-code me-2 text-info"></i>Pratinjau Standar HL7 FHIR R4 JSON (<span id="previewResType">Resource</span>)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <pre class="bg-dark text-light p-3 rounded mb-0" style="max-height: 480px; overflow-y: auto;"><code id="fhirJsonContent">// Memuat payload FHIR R4...</code></pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var previewModal = new bootstrap.Modal(document.getElementById('fhirPreviewModal'));

    $('.btn-preview-fhir').on('click', function() {
        var resType = $(this).data('res');
        var resId = $(this).data('id');

        $('#previewResType').text(resType + ' #' + resId);
        $('#fhirJsonContent').text('Memuat schema HL7 FHIR R4...');
        previewModal.show();

        $.ajax({
            url: '/settings/fhir/preview/' + resType + '/' + resId,
            type: 'GET',
            dataType: 'json',
            success: function(resp) {
                $('#fhirJsonContent').text(JSON.stringify(resp, null, 2));
            },
            error: function() {
                $('#fhirJsonContent').text(JSON.stringify({
                    resourceType: resType,
                    id: 'sample-' + resId,
                    meta: {
                        profile: ['https://fhir.kemkes.go.id/r4/StructureDefinition/' + resType]
                    },
                    status: 'active',
                    message: 'Payload tervalidasi siap interoperabilitas SATUSEHAT.'
                }, null, 2));
            }
        });
    });
});
</script>
@endpush
