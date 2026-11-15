<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Etiket Obat - {{ $prescription->prescription_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        .label-card {
            width: 70mm;
            min-height: 45mm;
            border: 2px solid #222;
            padding: 8px 10px;
            margin-bottom: 15px;
            background: #fff;
            box-sizing: border-box;
            display: inline-block;
            vertical-align: top;
            margin-right: 15px;
            page-break-inside: avoid;
        }
        .clinic-header { font-size: 11px; font-weight: bold; text-align: center; border-bottom: 1.5px solid #222; padding-bottom: 4px; margin-bottom: 6px; }
        .patient-info { font-size: 10px; margin-bottom: 6px; line-height: 1.3; }
        .med-name { font-size: 12px; font-weight: bold; color: #000; text-align: center; margin: 4px 0; }
        .usage-box { border: 1.5px solid #222; font-size: 12px; font-weight: bold; text-align: center; padding: 4px; margin: 4px 0; background: #f0f0f0; }
        .instruction-box { font-size: 11px; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 4px; }
        .footer-info { font-size: 9px; text-align: center; border-top: 1px dashed #666; padding-top: 3px; margin-top: 4px; color: #444; }
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .label-card { border: 1.5px solid #000; margin-right: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4">
        <div class="d-flex align-items-center justify-content-between p-3 bg-white rounded shadow-sm">
            <div>
                <h5 class="fw-bold mb-0">Cetak Etiket Obat Farmasi (Label Stiker)</h5>
                <span class="text-muted small">No. Resep: <strong>{{ $prescription->prescription_number }}</strong> | Pasien: <strong>{{ $prescription->visit?->patient?->name }}</strong></span>
            </div>
            <div>
                <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-1"></i> Cetak Etiket</button>
                <button onclick="window.close()" class="btn btn-secondary ms-1">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Generate Label for each Medicine Item -->
    <div class="d-flex flex-wrap">
        @foreach($prescription->items as $item)
            <div class="label-card">
                <div class="clinic-header">
                    KLINIK PRATAMA SEHAT MANDIRI<br>
                    <span style="font-size: 8px; font-weight: normal;">Jl. Ijen No. 45 Malang | Telp: (0341) 555-123</span>
                </div>
                <div class="patient-info">
                    <div class="d-flex justify-content-between">
                        <span>No. RM: <strong>{{ $prescription->visit?->patient?->medical_record_number }}</strong></span>
                        <span>{{ $prescription->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>Nama: <strong>{{ $prescription->visit?->patient?->name }}</strong></div>
                </div>

                <div class="med-name">
                    {{ $item->medicine?->brand_name }} ({{ $item->quantity }} {{ $item->medicine?->dosage_form }})
                </div>

                <div class="usage-box">
                    {{ $item->dosage }} &bull; {{ $item->frequency }}
                </div>

                <div class="instruction-box">
                    {{ $item->instructions }}
                </div>

                <div class="footer-info d-flex justify-content-between">
                    <span>Semoga Lekas Sembuh</span>
                    <span>Paraf: _______</span>
                </div>
            </div>
        @endforeach

        <!-- Generate Label for Compounds -->
        @foreach($prescription->compounds as $comp)
            <div class="label-card">
                <div class="clinic-header">
                    KLINIK PRATAMA SEHAT MANDIRI<br>
                    <span style="font-size: 8px; font-weight: normal;">Jl. Ijen No. 45 Malang | Telp: (0341) 555-123</span>
                </div>
                <div class="patient-info">
                    <div class="d-flex justify-content-between">
                        <span>No. RM: <strong>{{ $prescription->visit?->patient?->medical_record_number }}</strong></span>
                        <span>{{ $prescription->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div>Nama: <strong>{{ $prescription->visit?->patient?->name }}</strong></div>
                </div>

                <div class="med-name text-danger">
                    [RACIKAN] {{ $comp->compound_name }} ({{ $comp->quantity }} {{ $comp->dosage_form }})
                </div>

                <div class="usage-box">
                    {{ $comp->dosage }} &bull; {{ $comp->frequency }}
                </div>

                <div class="instruction-box">
                    {{ $comp->instructions }}
                </div>

                <div class="footer-info d-flex justify-content-between">
                    <span>Simpan di Tempat Kering</span>
                    <span>Paraf: _______</span>
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
