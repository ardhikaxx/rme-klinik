<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis Odontogram - {{ $visit->patient->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .kop-surat { border-bottom: 3px double #333; padding-bottom: 12px; margin-bottom: 16px; }
        .tooth-box-print { border: 1px solid #333; width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; font-size: 9px; font-weight: bold; color: #fff; margin: 1px; }
        .tooth-num-print { font-size: 10px; font-weight: bold; text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Cetak Dokumen</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm ms-1">Tutup</button>
    </div>

    <!-- Kop Surat Klinik -->
    <div class="kop-surat d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">KLINIK PRATAMA SEHAT MANDIRI</h4>
            <div class="small">Izin Operasional: 440/12/35.73.300/2024 | Kode Faskes: 0172B001</div>
            <div class="small text-muted">Jl. Ijen No. 45, Klojen, Kota Malang, Jawa Timur 65115 | Telp: (0341) 555-123</div>
        </div>
        <div class="text-end">
            <h5 class="fw-bold mb-0">REKAM MEDIS ODONTOGRAM</h5>
            <div class="small text-muted">Standar FDI World Dental Federation</div>
        </div>
    </div>

    <!-- Patient Info -->
    <table class="table table-sm table-bordered mb-3">
        <tr>
            <th class="bg-light" style="width: 15%;">No. Rekam Medis</th>
            <td style="width: 35%;" class="fw-bold">{{ $visit->patient->medical_record_number }}</td>
            <th class="bg-light" style="width: 15%;">Tanggal Periksa</th>
            <td style="width: 35%;">{{ $visit->registration_date->format('d F Y') }}</td>
        </tr>
        <tr>
            <th class="bg-light">Nama Pasien</th>
            <td class="fw-bold">{{ $visit->patient->name }}</td>
            <th class="bg-light">Dokter Pemeriksa</th>
            <td>{{ $visit->doctor?->formatted_name ?? 'drg. Maya Anggraini' }}</td>
        </tr>
        <tr>
            <th class="bg-light">TTL / Usia</th>
            <td>{{ $visit->patient->place_of_birth ?? '-' }}, {{ $visit->patient->date_of_birth?->format('d/m/Y') }} ({{ $visit->patient->age }} thn)</td>
            <th class="bg-light">Jenis Kelamin</th>
            <td>{{ $visit->patient->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
        </tr>
    </table>

    <!-- Odontogram Chart Print -->
    <div class="card mb-3">
        <div class="card-header bg-light py-1 text-center fw-bold small">
            BAGAN STATUS ODONTOGRAM GIGI
        </div>
        <div class="card-body py-2 text-center">
            <!-- Upper Adult -->
            <div class="small fw-bold text-muted mb-1">RAHANG ATAS - GIGI PERMANEN</div>
            <div class="d-flex justify-content-center gap-1 mb-2">
                <div class="d-flex flex-row-reverse border-end pe-2">
                    @foreach([11, 12, 13, 14, 15, 16, 17, 18] as $tn)
                        @php $td = $teethMap->get((string)$tn); @endphp
                        <div class="text-center" style="width: 36px;">
                            <div class="tooth-num-print">{{ $tn }}</div>
                            <div class="tooth-box-print" style="background-color: {{ $td ? $td->condition_color : '#28a745' }};">
                                {{ strtoupper(substr($td ? $td->condition : 'sou', 0, 3)) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex flex-row ps-2">
                    @foreach([21, 22, 23, 24, 25, 26, 27, 28] as $tn)
                        @php $td = $teethMap->get((string)$tn); @endphp
                        <div class="text-center" style="width: 36px;">
                            <div class="tooth-num-print">{{ $tn }}</div>
                            <div class="tooth-box-print" style="background-color: {{ $td ? $td->condition_color : '#28a745' }};">
                                {{ strtoupper(substr($td ? $td->condition : 'sou', 0, 3)) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Lower Adult -->
            <div class="d-flex justify-content-center gap-1 mt-3">
                <div class="d-flex flex-row-reverse border-end pe-2">
                    @foreach([41, 42, 43, 44, 45, 46, 47, 48] as $tn)
                        @php $td = $teethMap->get((string)$tn); @endphp
                        <div class="text-center" style="width: 36px;">
                            <div class="tooth-box-print" style="background-color: {{ $td ? $td->condition_color : '#28a745' }};">
                                {{ strtoupper(substr($td ? $td->condition : 'sou', 0, 3)) }}
                            </div>
                            <div class="tooth-num-print">{{ $tn }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="d-flex flex-row ps-2">
                    @foreach([31, 32, 33, 34, 35, 36, 37, 38] as $tn)
                        @php $td = $teethMap->get((string)$tn); @endphp
                        <div class="text-center" style="width: 36px;">
                            <div class="tooth-box-print" style="background-color: {{ $td ? $td->condition_color : '#28a745' }};">
                                {{ strtoupper(substr($td ? $td->condition : 'sou', 0, 3)) }}
                            </div>
                            <div class="tooth-num-print">{{ $tn }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="small fw-bold text-muted mt-1">RAHANG BAWAH - GIGI PERMANEN</div>
        </div>
    </div>

    <!-- Table of Findings -->
    <div class="mb-3">
        <h6 class="fw-bold mb-2">Temuan Klinis & Status Patologis Gigi:</h6>
        <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">Elemen</th>
                    <th style="width: 150px;">Kondisi Gigi</th>
                    <th style="width: 100px;">Permukaan</th>
                    <th>Rencana Tindakan / Catatan Perawatan</th>
                </tr>
            </thead>
            <tbody>
                @php $hasIssues = false; @endphp
                @foreach($odontogram->teeth as $t)
                    @if($t->condition !== 'sound')
                        @php $hasIssues = true; @endphp
                        <tr>
                            <td class="fw-bold text-center">{{ $t->tooth_number }}</td>
                            <td><span class="badge" style="background-color: {{ $t->condition_color }}; color: #fff;">{{ $t->condition_label }}</span></td>
                            <td>{{ $t->surfaces ?? '-' }}</td>
                            <td>{{ $t->treatment_notes ?? '-' }}</td>
                        </tr>
                    @endif
                @endforeach
                @if(!$hasIssues)
                    <tr>
                        <td colspan="4" class="text-center text-muted py-2">Seluruh elemen gigi dalam keadaan sehat / sound.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($odontogram->general_notes)
        <div class="p-2 border rounded mb-3">
            <strong>Catatan Tambahan / Evaluasi Oklusi & Jaringan Lunak:</strong>
            <div>{{ $odontogram->general_notes }}</div>
        </div>
    @endif

    <!-- Signature Block -->
    <div class="row mt-4 pt-2">
        <div class="col-8">
            <div class="small text-muted">
                Dokumen ini dicetak otomatis melalui Rekam Medis Elektronik RME KLINIK pada {{ now()->format('d/m/Y H:i') }} WIB.
            </div>
        </div>
        <div class="col-4 text-center">
            <div class="small mb-1">Kota Malang, {{ $visit->registration_date->format('d F Y') }}</div>
            <div class="small text-muted mb-4">Dokter Gigi Pemeriksa,</div>
            <br><br>
            <div class="fw-bold text-decoration-underline">{{ $visit->doctor?->formatted_name ?? 'drg. Maya Anggraini' }}</div>
            <div class="small text-muted">SIP: {{ $visit->doctor?->sip_str_number ?? 'SIP-GIGI-2024-001' }}</div>
        </div>
    </div>
</body>
</html>
