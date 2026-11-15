@props(['patient', 'visit' => null])

<div class="patient-badge-card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="user-avatar" style="width: 48px; height: 48px; font-size: 1.25rem;">
                {{ strtoupper(substr($patient->name, 0, 1)) }}
            </div>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="patient-name">{{ $patient->name }}</span>
                    <span class="patient-mrn">{{ $patient->medical_record_number }}</span>
                    <span class="badge {{ $patient->gender === 'L' ? 'bg-primary' : 'bg-danger' }}">
                        {{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                    </span>
                    @if($patient->is_verified)
                        <span class="badge bg-success" title="Identitas Terverifikasi"><i class="fa-solid fa-check-circle"></i> NIK Valid</span>
                    @else
                        <span class="badge bg-secondary" title="Identitas Sementara">Sementara</span>
                    @endif
                </div>
                <div class="text-muted small mt-1">
                    <span><i class="fa-solid fa-id-card me-1 text-secondary"></i> NIK: {{ $patient->nik ?? '-' }}</span> &bull;
                    <span><i class="fa-solid fa-cake-candles me-1 text-secondary"></i> {{ $patient->date_of_birth ? $patient->date_of_birth->translatedFormat('d F Y') : '-' }} ({{ $patient->age_detail }})</span> &bull;
                    <span><i class="fa-solid fa-phone me-1 text-secondary"></i> {{ $patient->phone }}</span> &bull;
                    <span><i class="fa-solid fa-droplet me-1 text-danger"></i> Gol. Darah: {{ strtoupper($patient->blood_type) }}</span>
                </div>
                <div class="text-muted small">
                    <i class="fa-solid fa-location-dot me-1 text-secondary"></i> {{ $patient->full_address }}
                </div>
            </div>
        </div>

        @if($visit)
            <div class="text-md-end border-start ps-3 d-none d-md-block">
                <div class="fw-bold text-dark">{{ $visit->visit_number }}</div>
                <span class="badge {{ $visit->status_badge_class }}">{{ $visit->status_label }}</span>
                <div class="small text-muted mt-1">
                    {{ $visit->polyclinic->name }} &bull; {{ $visit->doctor->formatted_name }}
                </div>
            </div>
        @endif
    </div>

    {{-- Medical Alert: Allergies Banner --}}
    @if($patient->allergies && $patient->allergies->count() > 0)
        <div class="alert-allergy-banner mt-3">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
            <div>
                <strong>PERINGATAN ALERGI PASIEN:</strong>
                @foreach($patient->allergies as $alg)
                    <span class="badge bg-danger ms-1">
                        {{ $alg->allergen_name }} ({{ ucfirst($alg->allergen_type) }}: {{ $alg->reaction ?? 'Reaksi Alergi' }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
