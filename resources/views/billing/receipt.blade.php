<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran - {{ $payment->receipt_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @if($format === 'thermal')
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 11px; color: #000; background: #f8f9fa; margin: 0; padding: 20px; }
        .thermal-slip {
            width: 80mm;
            background: #fff;
            padding: 12px 10px;
            margin: 0 auto;
            border: 1px solid #ccc;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .text-center { text-align: center; }
        .dashed-line { border-top: 1px dashed #000; margin: 6px 0; }
        @media print {
            body { background: #fff; padding: 0; margin: 0; }
            .no-print { display: none !important; }
            .thermal-slip { width: 100%; border: none; box-shadow: none; padding: 4px 0; }
        }
    </style>
    @else
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #333; background: #fff; padding: 25px; }
        .kop-surat { border-bottom: 3px double #333; padding-bottom: 12px; margin-bottom: 16px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
    @endif
</head>
<body>
    <div class="no-print mb-3 text-center">
        <div class="btn-group btn-group-sm">
            <a href="?format=thermal" class="btn {{ $format === 'thermal' ? 'btn-primary' : 'btn-outline-primary' }}"><i class="fas fa-receipt me-1"></i>Format Struk Thermal (80mm)</a>
            <a href="?format=a4" class="btn {{ $format === 'a4' ? 'btn-primary' : 'btn-outline-primary' }}"><i class="fas fa-file-invoice me-1"></i>Format Kuitansi A4</a>
        </div>
        <button onclick="window.print()" class="btn btn-success btn-sm ms-2"><i class="fas fa-print me-1"></i>Cetak</button>
        <button onclick="window.close()" class="btn btn-secondary btn-sm ms-1">Tutup</button>
    </div>

    @if($format === 'thermal')
    <!-- THERMAL RECEIPT (58/80mm) -->
    <div class="thermal-slip">
        <div class="text-center">
            <h6 class="fw-bold mb-0">KLINIK PRATAMA SEHAT MANDIRI</h6>
            <div style="font-size: 9px;">Jl. Ijen No. 45, Malang | Telp: (0341) 555-123</div>
            <div style="font-size: 9px;">Izin Operasional: 440/12/35.73.300/2024</div>
        </div>

        <div class="dashed-line"></div>

        <div>
            <div>No. Kuitansi: <strong>{{ $payment->receipt_number }}</strong></div>
            <div>Waktu: {{ $payment->created_at->format('d/m/Y H:i:s') }}</div>
            <div>Kasir: {{ $payment->cashier?->name }}</div>
            <div>No. RM: {{ $payment->invoice?->patient?->medical_record_number }}</div>
            <div>Pasien: <strong>{{ $payment->invoice?->patient?->name }}</strong></div>
            <div>Poli: {{ $payment->invoice?->visit?->polyclinic?->name }}</div>
        </div>

        <div class="dashed-line"></div>

        <div class="mb-1"><strong>RINCIAN BIAYA PELAYANAN:</strong></div>
        @foreach($payment->invoice?->items as $it)
            <div class="d-flex justify-content-between">
                <span>{{ $it->item_name }} ({{ $it->quantity }}x)</span>
                <span>{{ number_format($it->subtotal, 0, ',', '.') }}</span>
            </div>
        @endforeach

        <div class="dashed-line"></div>

        <div class="d-flex justify-content-between">
            <span>Total Tagihan:</span>
            <span>Rp{{ number_format($payment->invoice?->final_amount, 0, ',', '.') }}</span>
        </div>
        <div class="d-flex justify-content-between fw-bold">
            <span>Dibayar ({{ $payment->paymentMethod?->name }}):</span>
            <span>Rp{{ number_format($payment->amount_paid, 0, ',', '.') }}</span>
        </div>
        @if($payment->amount_tendered)
            <div class="d-flex justify-content-between">
                <span>Uang Diterima:</span>
                <span>Rp{{ number_format($payment->amount_tendered, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Kembalian:</span>
                <span>Rp{{ number_format($payment->change_amount, 0, ',', '.') }}</span>
            </div>
        @endif
        @if($payment->invoice?->balance_due > 0)
            <div class="d-flex justify-content-between text-danger">
                <span>Sisa Tagihan:</span>
                <span>Rp{{ number_format($payment->invoice?->balance_due, 0, ',', '.') }}</span>
            </div>
        @else
            <div class="text-center fw-bold my-1">*** LUNAS ***</div>
        @endif

        <div class="dashed-line"></div>

        <div class="text-center" style="font-size: 9px;">
            Terima kasih atas kunjungan Anda.<br>
            Semoga Lekas Sembuh & Sehat Selalu.
        </div>
    </div>
    @else
    <!-- STANDARD FORMAL A4 RECEIPT -->
    <div class="container-fluid p-0">
        <!-- Kop Surat -->
        <div class="kop-surat d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold mb-0 text-uppercase" style="letter-spacing: 1px;">KLINIK PRATAMA SEHAT MANDIRI</h4>
                <div class="small">Izin Operasional: 440/12/35.73.300/2024 | Kode Faskes: 0172B001</div>
                <div class="small text-muted">Jl. Ijen No. 45, Klojen, Kota Malang, Jawa Timur 65115 | Telp: (0341) 555-123</div>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0">KUITANSI PEMBAYARAN RESMI</h5>
                <div class="small text-muted">No: {{ $payment->receipt_number }}</div>
            </div>
        </div>

        <!-- Info Table -->
        <table class="table table-sm table-bordered mb-3">
            <tr>
                <th class="bg-light" style="width: 18%;">Telah Terima Dari</th>
                <td style="width: 32%;" class="fw-bold">{{ $payment->invoice?->patient?->name }} (No. RM: {{ $payment->invoice?->patient?->medical_record_number }})</td>
                <th class="bg-light" style="width: 18%;">No. Tagihan</th>
                <td style="width: 32%;" class="fw-bold">{{ $payment->invoice?->invoice_number }}</td>
            </tr>
            <tr>
                <th class="bg-light">Waktu Transaksi</th>
                <td>{{ $payment->created_at->format('d F Y, H:i') }} WIB</td>
                <th class="bg-light">Metode Pembayaran</th>
                <td><span class="badge bg-light text-dark border">{{ $payment->paymentMethod?->name ?? 'Tunai' }}</span></td>
            </tr>
            <tr>
                <th class="bg-light">Poli / Dokter</th>
                <td>{{ $payment->invoice?->visit?->polyclinic?->name }} / {{ $payment->invoice?->visit?->doctor?->formatted_name }}</td>
                <th class="bg-light">Status Tagihan</th>
                <td><span class="badge bg-success text-uppercase">{{ $payment->invoice?->status }}</span></td>
            </tr>
        </table>

        <!-- Details -->
        <table class="table table-sm table-bordered align-middle mb-3">
            <thead class="table-light">
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 50%;">Uraian Layanan Medis / Obat</th>
                    <th style="width: 15%;" class="text-center">Kategori</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 20%;" class="text-end">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->invoice?->items as $idx => $it)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $it->item_name }}</td>
                        <td class="text-center text-capitalize">{{ str_replace('_', ' ', $it->item_type) }}</td>
                        <td class="text-center">{{ $it->quantity }}</td>
                        <td class="text-end">Rp{{ number_format($it->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end bg-light">Total Tagihan Bersih:</th>
                    <th class="text-end bg-light">Rp{{ number_format($payment->invoice?->final_amount, 0, ',', '.') }}</th>
                </tr>
                <tr>
                    <th colspan="4" class="text-end text-success">Jumlah Dibayar (Kuitansi Ini):</th>
                    <th class="text-end text-success fw-bold">Rp{{ number_format($payment->amount_paid, 0, ',', '.') }}</th>
                </tr>
                @if($payment->invoice?->balance_due > 0)
                    <tr>
                        <th colspan="4" class="text-end text-danger">Sisa Tagihan Belum Lunas:</th>
                        <th class="text-end text-danger fw-bold">Rp{{ number_format($payment->invoice?->balance_due, 0, ',', '.') }}</th>
                    </tr>
                @endif
            </tfoot>
        </table>

        <!-- Signatures -->
        <div class="row mt-4 pt-2">
            <div class="col-8">
                <div class="small text-muted">
                    <em>Terbilang: {{ ucwords(\Illuminate\Support\Str::headline(number_format($payment->amount_paid, 0, '', ''))) }} Rupiah</em>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="small mb-1">Kota Malang, {{ $payment->created_at->format('d F Y') }}</div>
                <div class="small text-muted mb-4">Kasir Klinik,</div>
                <br><br>
                <div class="fw-bold text-decoration-underline">{{ $payment->cashier?->name ?? 'Kasir Klinik' }}</div>
                <div class="small text-muted">Petugas Admisi & Kasir</div>
            </div>
        </div>
    </div>
    @endif
</body>
</html>
