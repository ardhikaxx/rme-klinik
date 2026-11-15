<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pemeriksaan Laboratorium - {{ $request->request_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #333; background: #fff; }
        .kop-surat { border-bottom: 3px double #333; padding-bottom: 12px; margin-bottom: 16px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">
    <div class="no-print mb-3 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Cetak Hasil Lab</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm ms-1">Tutup</button>
    </div>

    <!-- Kop Surat -->
    <div class="kop-surat d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">KLINIK PRATAMA SEHAT MANDIRI</h4>
            <div class="small">Izin Operasional: 440/12/35.73.300/2024 | Kode Faskes: 0172B001</div>
            <div class="small text-muted">Jl. Ijen No. 45, Klojen, Kota Malang, Jawa Timur 65115 | Telp: (0341) 555-123</div>
        </div>
        <div class="text-end">
            <h5 class="fw-bold mb-0">HASIL PEMERIKSAAN LABORATORIUM</h5>
            <div class="small text-muted">Instalasi Laboratorium Klinik</div>
        </div>
    </div>

    <!-- Patient & Order Details -->
    <table class="table table-sm table-bordered mb-4">
        <tr>
            <th class="bg-light" style="width: 18%;">No. Rekam Medis</th>
            <td style="width: 32%;" class="fw-bold">{{ $request->visit?->patient?->medical_record_number }}</td>
            <th class="bg-light" style="width: 18%;">No. Order Lab</th>
            <td style="width: 32%;" class="fw-bold">{{ $request->request_number }}</td>
        </tr>
        <tr>
            <th class="bg-light">Nama Pasien</th>
            <td class="fw-bold">{{ $request->visit?->patient?->name }}</td>
            <th class="bg-light">Waktu Permintaan</th>
            <td>{{ $request->request_time?->format('d/m/Y H:i') }} WIB</td>
        </tr>
        <tr>
            <th class="bg-light">TTL / Usia</th>
            <td>{{ $request->visit?->patient?->place_of_birth ?? '-' }}, {{ $request->visit?->patient?->date_of_birth?->format('d/m/Y') }} ({{ $request->visit?->patient?->age }} thn)</td>
            <th class="bg-light">Waktu Pengambilan Sampel</th>
            <td>{{ $request->sample_collected_at?->format('d/m/Y H:i') ?? '-' }} WIB</td>
        </tr>
        <tr>
            <th class="bg-light">Jenis Kelamin</th>
            <td>{{ $request->visit?->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            <th class="bg-light">Dokter Pengirim</th>
            <td>{{ $request->doctor?->formatted_name ?? '-' }}</td>
        </tr>
    </table>

    <!-- Results Table -->
    <div class="mb-4">
        <table class="table table-sm table-bordered align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 30%;">Pemeriksaan</th>
                    <th style="width: 20%;">Hasil</th>
                    <th style="width: 15%;">Satuan</th>
                    <th style="width: 20%;">Nilai Rujukan</th>
                    <th style="width: 10%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($request->results as $idx => $res)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="fw-bold">{{ $res->laboratory?->name }}</td>
                        <td class="text-center fw-bold {{ $res->is_critical ? 'text-danger' : 'text-dark' }} fs-6">
                            {{ $res->result_value }}
                        </td>
                        <td class="text-center">{{ $res->laboratory?->unit ?? '-' }}</td>
                        <td class="text-center text-muted">{{ $res->laboratory?->reference_range ?? '-' }}</td>
                        <td class="text-center">
                            @if($res->is_critical)
                                <span class="badge bg-danger">KRITIS</span>
                            @else
                                <span class="text-muted">{{ $res->notes ?? 'Normal' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">Belum ada hasil uji yang diinput.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Signatures -->
    <div class="row mt-5 pt-3">
        <div class="col-6 text-center">
            <div class="small text-muted mb-4">Analis / Pemeriksa Laboratorium,</div>
            <br><br>
            <div class="fw-bold text-decoration-underline">{{ $request->results->first()?->tester?->name ?? 'Analis Lab' }}</div>
            <div class="small text-muted">Pranata Laboratorium Kesehatan</div>
        </div>
        <div class="col-6 text-center">
            <div class="small mb-1">Kota Malang, {{ now()->format('d F Y') }}</div>
            <div class="small text-muted mb-4">Dokter Penanggung Jawab Lab / Pemeriksa,</div>
            <br><br>
            <div class="fw-bold text-decoration-underline">{{ $request->doctor?->formatted_name ?? 'dr. Penanggung Jawab' }}</div>
            <div class="small text-muted">SIP: {{ $request->doctor?->sip_str_number ?? '-' }}</div>
        </div>
    </div>
</body>
</html>
