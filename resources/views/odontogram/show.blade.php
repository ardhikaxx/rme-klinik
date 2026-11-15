@extends('layouts.app')

@section('title', 'Peta Odontogram Gigi - ' . $visit->patient->name)
@section('page_title', 'Peta Odontogram Gigi (FDI System)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('odontogram.index') }}">Poli Gigi</a></li>
    <li class="breadcrumb-item active" aria-current="page">Odontogram {{ $visit->visit_number }}</li>
@endsection

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0 text-dark fw-bold">Peta Odontogram Gigi (FDI System)</h4>
                <span class="badge bg-primary fs-6">{{ $visit->patient->name }} ({{ $visit->patient->medical_record_number }})</span>
            </div>
            <div class="text-muted small mt-1">
                Kunjungan: <strong>{{ $visit->visit_number }}</strong> | 
                Dokter Gigi: <strong>{{ $visit->doctor?->formatted_name ?? 'drg. Maya Anggraini' }}</strong> | 
                Tanggal: {{ $visit->registration_date->format('d/m/Y') }}
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('clinical.examination', $visit->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Ke Ruang Pemeriksaan Dokter
            </a>
            <a href="{{ route('odontogram.print', $visit->id) }}" class="btn btn-outline-secondary" target="_blank">
                <i class="fas fa-print me-1"></i> Cetak Odontogram
            </a>
        </div>
    </div>

    <!-- Allergy Alert Banner -->
    @if($visit->patient->allergies && $visit->patient->allergies->count() > 0)
        <div class="alert alert-danger py-2 d-flex align-items-center mb-4" role="alert">
            <i class="fas fa-hand-dots fs-5 me-2 text-danger"></i>
            <div>
                <strong>Peringatan Alergi:</strong>
                @foreach($visit->patient->allergies as $alg)
                    <span class="badge bg-danger ms-1">{{ $alg->allergen_name }} ({{ $alg->reaction }})</span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Odontogram Color Legend -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 small">
                <span class="fw-bold text-dark"><i class="fas fa-palette me-1"></i>Keterangan Kondisi Gigi:</span>
                <span><span class="badge me-1" style="background-color: #28a745;">■</span> Sehat / Normal (Sound)</span>
                <span><span class="badge me-1" style="background-color: #dc3545;">■</span> Karies / Berlubang (Caries)</span>
                <span><span class="badge me-1" style="background-color: #007bff;">■</span> Tumpatan / Tambal (Filling)</span>
                <span><span class="badge me-1" style="background-color: #6c757d;">■</span> Hilang / Dicabut (Missing)</span>
                <span><span class="badge me-1" style="background-color: #ffc107; color: #000;">■</span> Sisa Akar (Radix)</span>
                <span><span class="badge me-1" style="background-color: #17a2b8;">■</span> Mahkota (Crown)</span>
                <span><span class="badge me-1" style="background-color: #6f42c1;">■</span> Perawatan Saluran Akar (PSA)</span>
                <span><span class="badge me-1" style="background-color: #fd7e14;">■</span> Jembatan (Bridge)</span>
                <span><span class="badge me-1" style="background-color: #20c997;">■</span> Belum Erupsi (Unerupted)</span>
            </div>
        </div>
    </div>

    <!-- Interactive Odontogram Chart -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-tooth me-2"></i>Bagan Pemeriksaan Odontogram (Klik Elemen Gigi Untuk Mengubah Status)
            </h5>
            <span class="small text-muted">Standar FDI Two-Digit Notation</span>
        </div>
        <div class="card-body p-4">
            <!-- 1. ADULT TEETH UPPER (18-11 & 21-28) -->
            <div class="text-center mb-3">
                <span class="badge bg-light text-dark border px-3 py-1 fw-bold">RAHANG ATAS - GIGI PERMANEN / DEWASA</span>
            </div>

            <div class="odontogram-jaw mb-4">
                <div class="d-flex justify-content-center flex-wrap gap-1">
                    <!-- Quadrant 1 (18 to 11) -->
                    <div class="d-flex flex-row-reverse gap-1 me-2 pe-2 border-end border-2 border-primary">
                        @foreach([11, 12, 13, 14, 15, 16, 17, 18] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-num fw-bold small text-primary">{{ $tNum }}</div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: {{ in_array($cond, ['radix', 'sound']) ? '#fff' : '#fff' }}; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-surface text-muted" style="font-size: 9px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Quadrant 2 (21 to 28) -->
                    <div class="d-flex flex-row gap-1">
                        @foreach([21, 22, 23, 24, 25, 26, 27, 28] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-num fw-bold small text-primary">{{ $tNum }}</div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: #fff; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-surface text-muted" style="font-size: 9px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 2. DECIDUOUS TEETH UPPER (55-51 & 61-65) -->
            <div class="text-center mb-2">
                <span class="badge bg-light text-secondary border px-3 py-1 small">GIGI SUSU / DECIDUOUS (ANAK)</span>
            </div>
            <div class="odontogram-jaw mb-4">
                <div class="d-flex justify-content-center flex-wrap gap-1">
                    <!-- Quadrant 5 (55 to 51) -->
                    <div class="d-flex flex-row-reverse gap-1 me-2 pe-2 border-end border-2 border-secondary">
                        @foreach([51, 52, 53, 54, 55] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-num fw-bold text-muted" style="font-size: 11px;">{{ $tNum }}</div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-surface text-muted" style="font-size: 8px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Quadrant 6 (61 to 65) -->
                    <div class="d-flex flex-row gap-1">
                        @foreach([61, 62, 63, 64, 65] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-num fw-bold text-muted" style="font-size: 11px;">{{ $tNum }}</div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-surface text-muted" style="font-size: 8px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <hr class="my-3">

            <!-- 3. DECIDUOUS TEETH LOWER (85-81 & 71-75) -->
            <div class="odontogram-jaw mb-4">
                <div class="d-flex justify-content-center flex-wrap gap-1">
                    <!-- Quadrant 8 (85 to 81) -->
                    <div class="d-flex flex-row-reverse gap-1 me-2 pe-2 border-end border-2 border-secondary">
                        @foreach([81, 82, 83, 84, 85] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-surface text-muted" style="font-size: 8px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-num fw-bold text-muted" style="font-size: 11px;">{{ $tNum }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Quadrant 7 (71 to 75) -->
                    <div class="d-flex flex-row gap-1">
                        @foreach([71, 72, 73, 74, 75] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-surface text-muted" style="font-size: 8px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: #fff; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-num fw-bold text-muted" style="font-size: 11px;">{{ $tNum }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- 4. ADULT TEETH LOWER (48-41 & 31-38) -->
            <div class="text-center mb-3">
                <span class="badge bg-light text-dark border px-3 py-1 fw-bold">RAHANG BAWAH - GIGI PERMANEN / DEWASA</span>
            </div>
            <div class="odontogram-jaw">
                <div class="d-flex justify-content-center flex-wrap gap-1">
                    <!-- Quadrant 4 (48 to 41) -->
                    <div class="d-flex flex-row-reverse gap-1 me-2 pe-2 border-end border-2 border-primary">
                        @foreach([41, 42, 43, 44, 45, 46, 47, 48] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-surface text-muted" style="font-size: 9px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: #fff; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-num fw-bold small text-primary">{{ $tNum }}</div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Quadrant 3 (31 to 38) -->
                    <div class="d-flex flex-row gap-1">
                        @foreach([31, 32, 33, 34, 35, 36, 37, 38] as $tNum)
                            @php 
                                $tData = $teethMap->get((string)$tNum);
                                $cond = $tData ? $tData->condition : 'sound';
                                $color = $tData ? $tData->condition_color : '#28a745';
                            @endphp
                            <div class="tooth-item text-center cursor-pointer" onclick="openToothModal('{{ $tNum }}', '{{ $cond }}', '{{ $tData?->surfaces }}', '{{ $tData?->treatment_notes }}')">
                                <div class="tooth-surface text-muted" style="font-size: 9px;" id="tooth-surf-{{ $tNum }}">
                                    {{ $tData?->surfaces ? $tData->surfaces : '-' }}
                                </div>
                                <div class="tooth-box border rounded p-1 mb-1" id="tooth-box-{{ $tNum }}" style="background-color: {{ $color }}; color: #fff; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold;">
                                    <span id="tooth-label-{{ $tNum }}">{{ strtoupper(substr($cond, 0, 3)) }}</span>
                                </div>
                                <div class="tooth-num fw-bold small text-primary">{{ $tNum }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- General Dental Notes Form -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-notes-medical me-2"></i>Catatan Pemeriksaan Kesehatan Gigi & Mulut
            </h5>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('odontogram.save', $odontogram->id) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Klinis / Evaluasi Oklusi, Torus & Gingiva</label>
                    <textarea class="form-control" name="notes" rows="3" placeholder="Contoh: Oklusi normal bite, tidak ada torus palatinus/mandibularis, gingiva anterior bawah hiperemis ringan...">{{ old('notes', $odontogram->general_notes) }}</textarea>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-save me-2"></i>Simpan Catatan Gigi & Mulut
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update Tooth Condition -->
<div class="modal fade" id="toothModal" tabindex="-1" aria-labelledby="toothModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="toothModalLabel">
                    <i class="fas fa-tooth me-2"></i>Pemeriksaan Elemen Gigi <span id="modalToothNum" class="badge bg-white text-primary fs-6">11</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalInputToothNum">

                <div class="mb-3">
                    <label class="form-label fw-bold">Kondisi Gigi <span class="text-danger">*</span></label>
                    <select class="form-select" id="modalCondition">
                        <option value="sound">Sehat / Normal (Sound)</option>
                        <option value="caries">Karies / Berlubang (Caries)</option>
                        <option value="filling">Tumpatan / Tambalan (Filling)</option>
                        <option value="missing">Gigi Hilang / Dicabut (Missing)</option>
                        <option value="radix">Sisa Akar (Radix)</option>
                        <option value="crown">Mahkota / Jaket (Crown)</option>
                        <option value="root_canal">Perawatan Saluran Akar (PSA / Endo)</option>
                        <option value="bridge">Jembatan (Bridge Pontic/Abutment)</option>
                        <option value="unerupted">Belum Erupsi / Impaksi (Unerupted)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Permukaan Gigi Terlibat (MODBL)</label>
                    <div class="d-flex gap-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input surface-check" type="checkbox" id="surf_M" value="M">
                            <label class="form-check-label fw-bold" for="surf_M">M (Mesial)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input surface-check" type="checkbox" id="surf_O" value="O">
                            <label class="form-check-label fw-bold" for="surf_O">O (Oklusal)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input surface-check" type="checkbox" id="surf_D" value="D">
                            <label class="form-check-label fw-bold" for="surf_D">D (Distal)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input surface-check" type="checkbox" id="surf_B" value="B">
                            <label class="form-check-label fw-bold" for="surf_B">B (Bukal/Labial)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input surface-check" type="checkbox" id="surf_L" value="L">
                            <label class="form-check-label fw-bold" for="surf_L">L (Lingual/Palatal)</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Rencana Tindakan / Catatan Khusus Gigi Ini</label>
                    <input type="text" class="form-control" id="modalTreatmentNotes" placeholder="Contoh: Pro tumpatan RK, pro ekstraksi, kavitas profunda...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary fw-bold" id="btnSaveTooth">
                    <i class="fas fa-check me-1"></i>Simpan Perubahan Gigi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var currentToothModal;

function openToothModal(toothNum, cond, surfaces, notes) {
    $('#modalToothNum').text(toothNum);
    $('#modalInputToothNum').val(toothNum);
    $('#modalCondition').val(cond || 'sound');
    $('#modalTreatmentNotes').val(notes || '');

    $('.surface-check').prop('checked', false);
    if (surfaces) {
        var surfArr = surfaces.split('');
        surfArr.forEach(function(s) {
            $('#surf_' + s).prop('checked', true);
        });
    }

    if (!currentToothModal) {
        currentToothModal = new bootstrap.Modal(document.getElementById('toothModal'));
    }
    currentToothModal.show();
}

$(document).ready(function() {
    $('#btnSaveTooth').on('click', function() {
        var toothNum = $('#modalInputToothNum').val();
        var condition = $('#modalCondition').val();
        var notes = $('#modalTreatmentNotes').val();

        var selectedSurfaces = [];
        $('.surface-check:checked').each(function() {
            selectedSurfaces.push($(this).val());
        });
        var surfacesStr = selectedSurfaces.join('');

        $.ajax({
            url: '{{ route('odontogram.tooth', $odontogram->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                tooth_number: toothNum,
                condition: condition,
                surfaces: surfacesStr,
                treatment_notes: notes
            },
            success: function(resp) {
                if (resp.success) {
                    $('#tooth-box-' + toothNum).css('background-color', resp.color);
                    $('#tooth-label-' + toothNum).text(condition.substring(0, 3).toUpperCase());
                    $('#tooth-surf-' + toothNum).text(surfacesStr || '-');

                    currentToothModal.hide();

                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Gigi ' + toothNum + ' berhasil diperbarui (' + resp.condition_label + ')',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Gagal menyimpan status gigi.', 'error');
            }
        });
    });
});
</script>
@endpush
