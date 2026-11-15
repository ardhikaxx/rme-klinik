<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->type_label }} - {{ $document->document_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; color: #111; background: #fff; padding: 30px; line-height: 1.6; }
        .kop-surat { border-bottom: 3px double #111; padding-bottom: 12px; margin-bottom: 24px; }
        .doc-title { font-size: 16px; font-weight: bold; text-decoration: underline; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .doc-num { text-align: center; font-size: 12px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4 text-end">
        <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print me-1"></i> Cetak Dokumen</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm ms-1">Tutup</button>
    </div>

    <!-- Kop Surat Resmi Klinik -->
    <div class="kop-surat d-flex align-items-center justify-content-between">
        <div>
            <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1.5px; font-family: Arial, sans-serif;">KLINIK PRATAMA SEHAT MANDIRI</h4>
            <div class="small" style="font-family: Arial, sans-serif;">Izin Operasional Pelayanan Kesehatan: 440/12/35.73.300/2024 | Kode Faskes: 0172B001</div>
            <div class="small text-muted" style="font-family: Arial, sans-serif;">Jl. Ijen No. 45, Klojen, Kota Malang, Jawa Timur 65115 | Telp: (0341) 555-123 | Email: klinik.sehatmandiri@gmail.com</div>
        </div>
    </div>

    @php $data = $document->content_data ?? []; @endphp

    @if($document->type === 'surat_keterangan_sakit')
        <div class="doc-title">SURAT KETERANGAN SAKIT</div>
        <div class="doc-num">Nomor: {{ $document->document_number }}</div>

        <p>Yang bertanda tangan di bawah ini, Dokter Pemeriksa Klinik Pratama Sehat Mandiri Kota Malang, menerangkan dengan sebenarnya bahwa:</p>

        <table class="table table-sm table-borderless ms-4 mb-3" style="width: 85%;">
            <tr>
                <td style="width: 25%;">Nama Pasien</td>
                <td style="width: 5%;">:</td>
                <td class="fw-bold">{{ $document->patient?->name }}</td>
            </tr>
            <tr>
                <td>Nomor Rekam Medis</td>
                <td>:</td>
                <td>{{ $document->patient?->medical_record_number }}</td>
            </tr>
            <tr>
                <td>Tempat / Tgl. Lahir</td>
                <td>:</td>
                <td>{{ $document->patient?->place_of_birth ?? '-' }}, {{ $document->patient?->date_of_birth?->format('d F Y') }} ({{ $document->patient?->age }} Tahun)</td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td>:</td>
                <td>{{ $document->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td>Alamat Tinggal</td>
                <td>:</td>
                <td>{{ $document->patient?->full_address }}</td>
            </tr>
            <tr>
                <td>Instansi / Pekerjaan</td>
                <td>:</td>
                <td>{{ $data['workplace'] ?? '-' }}</td>
            </tr>
        </table>

        <p>Mengingat kondisi kesehatannya, yang bersangkutan memerlukan <strong>istirahat sakit selama {{ $data['duration_days'] ?? '-' }} ({{ ucwords(\Illuminate\Support\Str::headline($data['duration_days'] ?? '')) }}) hari</strong>, terhitung mulai tanggal <strong>{{ isset($data['start_date']) ? \Carbon\Carbon::parse($data['start_date'])->translatedFormat('d F Y') : '-' }}</strong> sampai dengan tanggal <strong>{{ isset($data['end_date']) ? \Carbon\Carbon::parse($data['end_date'])->translatedFormat('d F Y') : '-' }}</strong>.</p>

        <p>Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya oleh pihak yang berkepentingan.</p>

    @elseif($document->type === 'surat_keterangan_sehat')
        <div class="doc-title">SURAT KETERANGAN SEHAT</div>
        <div class="doc-num">Nomor: {{ $document->document_number }}</div>

        <p>Yang bertanda tangan di bawah ini Dokter Pemeriksa Klinik Pratama Sehat Mandiri Kota Malang, menerangkan bahwa telah melakukan pemeriksaan kesehatan terhadap:</p>

        <table class="table table-sm table-borderless ms-4 mb-3" style="width: 85%;">
            <tr>
                <td style="width: 25%;">Nama Pasien</td>
                <td style="width: 5%;">:</td>
                <td class="fw-bold">{{ $document->patient?->name }}</td>
            </tr>
            <tr>
                <td>Nomor Rekam Medis</td>
                <td>:</td>
                <td>{{ $document->patient?->medical_record_number }}</td>
            </tr>
            <tr>
                <td>Tempat / Tgl. Lahir</td>
                <td>:</td>
                <td>{{ $document->patient?->place_of_birth ?? '-' }}, {{ $document->patient?->date_of_birth?->format('d F Y') }} ({{ $document->patient?->age }} Tahun)</td>
            </tr>
            <tr>
                <td>Alamat Tinggal</td>
                <td>:</td>
                <td>{{ $document->patient?->full_address }}</td>
            </tr>
        </table>

        <p>Dengan hasil pemeriksaan fisik sebagai berikut:</p>
        <table class="table table-sm table-bordered ms-4 mb-3" style="width: 85%;">
            <tr>
                <th class="bg-light" style="width: 30%;">Tekanan Darah</th>
                <td>{{ $data['blood_pressure'] ?? '-' }}</td>
                <th class="bg-light" style="width: 25%;">Berat Badan</th>
                <td>{{ $data['weight'] ?? '-' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Tinggi Badan</th>
                <td>{{ $data['height'] ?? '-' }}</td>
                <th class="bg-light">Tes Buta Warna</th>
                <td>{{ $data['color_blindness'] ?? 'Normal' }}</td>
            </tr>
            <tr>
                <th class="bg-light">Kesimpulan</th>
                <td colspan="3" class="fw-bold">{{ $data['health_status'] ?? 'Sehat & Layak Beraktivitas / Bekerja' }}</td>
            </tr>
        </table>

        <p>Surat keterangan ini diberikan atas permintaan yang bersangkutan untuk keperluan: <strong>{{ $data['purpose'] ?? '-' }}</strong>.</p>

    @elseif($document->type === 'surat_rujukan')
        <div class="doc-title">SURAT RUJUKAN PASIEN</div>
        <div class="doc-num">Nomor: {{ $document->document_number }}</div>

        <p>Kepada Yth.<br>
        <strong>Dokter Spesialis / Poli {{ $data['destination_specialist'] ?? '-' }}</strong><br>
        {{ $data['destination_hospital'] ?? 'Rumah Sakit Rujukan' }}<br>
        di Tempat</p>

        <p>Dengan hormat,<br>
        Mohon bantuan pemeriksaan, konsultasi, dan penanganan lebih lanjut terhadap pasien:</p>

        <table class="table table-sm table-borderless ms-4 mb-3" style="width: 85%;">
            <tr>
                <td style="width: 25%;">Nama Pasien</td>
                <td style="width: 5%;">:</td>
                <td class="fw-bold">{{ $document->patient?->name }} (No. RM: {{ $document->patient?->medical_record_number }})</td>
            </tr>
            <tr>
                <td>Umur / Gender</td>
                <td>:</td>
                <td>{{ $document->patient?->age }} Tahun / {{ $document->patient?->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
            </tr>
            <tr>
                <td>Diagnosis Kerja</td>
                <td>:</td>
                <td class="fw-bold">{{ $data['temporary_diagnosis'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alasan Rujukan</td>
                <td>:</td>
                <td>{{ $data['referral_reason'] ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tindakan Diberikan</td>
                <td>:</td>
                <td>{{ $data['treatment_given'] ?? '-' }}</td>
            </tr>
        </table>

        <p>Demikian surat rujukan ini kami buat, atas perhatian dan kerjasama rekan sejawat kami ucapkan terima kasih.</p>

    @elseif($document->type === 'informed_consent')
        <div class="doc-title">PERSETUJUAN TINDAKAN MEDIS (INFORMED CONSENT)</div>
        <div class="doc-num">Nomor: {{ $document->document_number }}</div>

        <p>Yang bertanda tangan di bawah ini:</p>
        <table class="table table-sm table-borderless ms-4 mb-3" style="width: 85%;">
            <tr>
                <td style="width: 25%;">Nama Lengkap</td>
                <td style="width: 5%;">:</td>
                <td class="fw-bold">{{ $data['patient_or_guardian_name'] ?? $document->patient?->name }}</td>
            </tr>
            <tr>
                <td>Hubungan dg Pasien</td>
                <td>:</td>
                <td>{{ $data['relation_to_patient'] ?? 'Diri Sendiri' }}</td>
            </tr>
            <tr>
                <td>Nama Pasien</td>
                <td>:</td>
                <td class="fw-bold">{{ $document->patient?->name }} (No. RM: {{ $document->patient?->medical_record_number }})</td>
            </tr>
        </table>

        <p>Menyatakan bahwa saya telah mendapatkan penjelasan secara rinci dan memahami sepenuhnya mengenai:</p>
        <ol class="ms-3 mb-3">
            <li>Diagnosis dan tata cara tindakan medis yang akan dilakukan: <strong>{{ $data['action_name'] ?? '-' }}</strong></li>
            <li>Tujuan tindakan medis dan manfaat yang diharapkan bagi kesehatan pasien.</li>
            <li>Risiko, komplikasi, dan efek samping yang mungkin timbul selama atau pasca tindakan.</li>
        </ol>

        <p>Dengan ini menyatakan <strong>SETUJU (MENERIMA)</strong> untuk dilakukannya tindakan medis tersebut secara sadar tanpa paksaan dari pihak manapun.</p>
    @endif

    <!-- Tanda Tangan -->
    <div class="row mt-5 pt-3">
        <div class="col-7"></div>
        <div class="col-5 text-center">
            <div>Kota Malang, {{ $document->created_at->format('d F Y') }}</div>
            <div class="text-muted mb-4">Dokter Pemeriksa,</div>
            <br><br>
            <div class="fw-bold text-decoration-underline">{{ $document->doctor?->formatted_name }}</div>
            <div class="small">SIP: {{ $document->doctor?->sip_str_number ?? '-' }}</div>
        </div>
    </div>
</body>
</html>
