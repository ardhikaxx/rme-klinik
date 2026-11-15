<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Pasien - {{ $patient->medical_record_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .patient-card-print {
            width: 85.6mm;
            height: 53.98mm;
            background: #ffffff;
            border: 1px solid #416B82;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .card-header-clinic {
            border-bottom: 2px solid #2EACF0;
            padding-bottom: 6px;
        }
        .clinic-name {
            font-size: 11pt;
            font-weight: bold;
            color: #416B82;
            line-height: 1.1;
        }
        .clinic-sub {
            font-size: 6.5pt;
            color: #666;
        }
        .patient-mrn {
            font-size: 13pt;
            font-weight: 800;
            color: #2EACF0;
            letter-spacing: 0.5px;
        }
        .patient-name {
            font-size: 11pt;
            font-weight: bold;
            color: #2b3a42;
            margin-top: 2px;
        }
        .patient-detail {
            font-size: 7.5pt;
            color: #555;
            line-height: 1.3;
        }
        .card-footer-info {
            border-top: 1px dashed #dde4e8;
            padding-top: 4px;
            font-size: 6pt;
            color: #888;
            display: flex;
            justify-content: space-between;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .patient-card-print {
                box-shadow: none;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4 d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print me-1"></i> Cetak Kartu Pasien
        </button>
        <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
    </div>

    <div class="patient-card-print">
        <div class="card-header-clinic d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-hospital text-info fa-lg"></i>
                <div>
                    <div class="clinic-name">KLINIK PRATAMA SEHAT MANDIRI</div>
                    <div class="clinic-sub">Jl. Besar Ijen No. 45, Klojen, Kota Malang &bull; Telp: (0341) 551234</div>
                </div>
            </div>
        </div>

        <div class="my-auto py-1">
            <div class="patient-mrn">{{ $patient->medical_record_number }}</div>
            <div class="patient-name">{{ $patient->name }}</div>
            <div class="patient-detail mt-1">
                <div><strong>NIK:</strong> {{ $patient->nik ?? '-' }} &bull; <strong>JK:</strong> {{ $patient->gender === 'L' ? 'Laki-laki' : 'Perempuan' }} ({{ $patient->age }} thn)</div>
                <div><strong>Tgl Lahir:</strong> {{ $patient->date_of_birth ? $patient->date_of_birth->format('d-m-Y') : '-' }} &bull; <strong>Telp:</strong> {{ $patient->phone }}</div>
                <div class="text-truncate" style="max-width: 75mm;"><strong>Alamat:</strong> {{ $patient->full_address }}</div>
            </div>
        </div>

        <div class="card-footer-info">
            <span>Harap selalu membawa kartu ini saat berkunjung.</span>
            <span>RME Terpadu</span>
        </div>
    </div>
</body>
</html>
