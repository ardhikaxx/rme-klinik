# Arsitektur Dataset Skala Besar & Zero-Loop Seeding RME KLINIK

Dokumen ini mendokumentasikan spesifikasi teknis, arsitektur data sintetis skala besar (100.000 pasien unik dan ~6.000.000 data operasional), metode seeding *zero-loop* berbasis native `LOAD DATA LOCAL INFILE`, serta hasil verifikasi integritas data untuk aplikasi **RME KLINIK** (Sistem Informasi Rekam Medis Elektronik Terpadu).

---

## 1. Ringkasan Eksekutif & Skala Dataset

Aplikasi RME KLINIK telah dilengkapi dengan dataset skala riil fasilitas kesehatan yang mencakup periode historis **2020 hingga 2026**. Dataset ini dirancang untuk mensimulasikan operasional klinik rawat jalan yang aktif dengan data demografi Indonesia yang realistis, valid, dan saling berhubungan secara klinis dan finansial.

| Metrik Dataset | Target Desain | Realisasi Aktual Database | Status |
|---|---|---|---|
| **Pasien Unik (`patients`)** | Min. 100.000 | **100.000 Pasien** | **LULUS (100%)** |
| **Data Operasional Lintas Modul** | Min. 100.000 (Target 1M+) | **5.944.314 Records** | **LULUS (594%)** |
| **Total Seluruh Record Audit** | - | **6.044.370 Records** | **LULUS** |
| **Rentang Waktu Historis** | 2020 s/d 2026 | `2020-01-02` s/d `2026-11-16` | **LULUS (Konsisten)** |
| **Orphan Data (Pasien / Kunjungan)** | 0 record | **0 orphan records** | **LULUS (100% Integritas)** |
| **Kepatuhan Stok Farmasi (FEFO)** | 0 stok negatif | **0 batch negatif** | **LULUS** |
| **Akurasi Matematika Billing** | 100% presisi | **100% presisi** | **LULUS** |

---

## 2. Arsitektur Zero-Loop Seeding & Native Bulk Loader

### 2.1 Prinsip Desain Tanpa Perulangan (Zero-Loop Directive)
Sesuai batasan arsitektural yang ketat:
- Seluruh file seeder Laravel (`ComprehensivePatientSeeder.php`, `ComprehensiveOperationSeeder.php`, `DoctorScheduleSeeder.php`) **bebas dari perulangan** (`for`, `foreach`, `while`, `do-while`, `array_fill`).
- Data tidak dihasilkan secara runtime di dalam memori PHP per request, melainkan dimuat dari file dataset statis yang telah dipersiapkan sebelumnya (*pre-generated static dataset CSVs*).

### 2.2 Native Bulk Loader via `LOAD DATA LOCAL INFILE`
Impor data dilakukan menggunakan mekanisme native database engine MySQL/MariaDB dengan konfigurasi PDO:
```php
// config/database.php
'mysql' => [
    ...
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        \PDO::MYSQL_ATTR_LOCAL_INFILE => true,
    ]) : [],
],
```

Trait `ImportsCsvDataset` (`database/seeders/Concerns/ImportsCsvDataset.php`) menangani:
1. Deteksi otomatis line terminator (`\r\n` untuk Windows atau `\n` untuk Unix).
2. Escaping otomatis nama kolom menggunakan backtick (`` `kolom` ``) untuk mencegah konflik kata kunci terpesan MySQL/MariaDB (seperti `` `condition` `` pada tabel `odontogram_teeth`).
3. Mapping nilai kosong `\N` ke SQL `NULL` yang aman bagi foreign key nullable.

### 2.3 Tolok Ukur Kecepatan Eksekusi (Benchmark)
- **100.000 Pasien Unik (`ComprehensivePatientSeeder`)**: Selesai dalam **7,78 detik**.
- **200.000 Kunjungan & ~5,9 Juta Transaksi Operasional (`ComprehensiveOperationSeeder`)**: Selesai dalam **102,94 detik** (~1,7 menit).
- **Total `migrate:fresh --seed`**: Selesai dalam **~2 menit** untuk seluruh database (37 tabel).

---

## 3. Rincian Dataset per Modul (37 Tabel)

Dataset tersimpan sebagai file CSV statis di direktori `database/seeders/datasets/` dan dapat dihasilkan ulang kapan saja menggunakan perintah `php artisan clinic:generate-dataset-files`.

| Kategori Modul | Tabel Database | Jumlah Record | Keterangan & Integritas |
|---|---|---|---|
| **Master / Identitas** | `patients` | 100.000 | NIK Dukcapil valid, No. RM `RM-000001` s/d `RM-100000`, Nama, Alamat, RT/RW, Kelurahan, Kecamatan, Kota Malang/Surabaya/Sidoarjo/dll, Gol. Darah, Agama, Pendidikan, Pekerjaan |
| **Master / Identitas** | `patient_allergies` | 14.285 | Alergi antibiotik (Amoxicillin, Penicillin), analgesik, makanan, debu |
| **Master / Identitas** | `patient_emergency_contacts` | 50.000 | Kontak darurat keluarga terdekat (pasangan, orang tua, anak) |
| **Master / Identitas** | `employees` | 6 | Tenaga medis & staf operasional klinik |
| **Master / Identitas** | `medicines` | 50 | 50 Obat esensial lengkap (Antibiotik, Analgesik, Cerna, Kardiovaskular, dll) |
| **Operasional Kunjungan** | `visits` | 200.000 | Kunjungan Poli Umum (60%), Poli Gigi (20%), Poli KIA (20%) |
| **Operasional Kunjungan** | `visit_status_histories` | 1.168.000 | Audit trail alur status kunjungan: `registered` -> `assessed` -> `in_consultation` -> `pharmacy` -> `completed` (atau `cancelled`) |
| **Operasional Triage** | `nursing_assessments` | 192.000 | Anamnesis awal perawat, keluhan utama, skala nyeri, risiko jatuh |
| **Operasional Triage** | `vital_signs` | 192.000 | Tekanan darah (sistolik/diastolik), suhu, nadi, laju nafas, SpO2, BMI |
| **Operasional Medis** | `medical_records` | 192.000 | Rekam medis SOAP dokter, rencana edukasi, jadwal kontrol lanjutan |
| **Operasional Medis** | `medical_record_diagnoses` | 224.000 | Diagnosis ICD-10 klinis primer dan sekunder |
| **Operasional Medis** | `medical_record_procedures` | 192.000 | Tindakan medis ICD-9-CM poliklinik |
| **Poli Spesialis** | `odontograms` | 40.000 | Lembar pemeriksaan odontogram Poli Gigi |
| **Poli Spesialis** | `odontogram_teeth` | 80.000 | Status gigi spesifik dengan notasi 2-digit FDI (`11`-`85`) |
| **Poli Spesialis** | `antenatal_cares` | 20.000 | Pemeriksaan ANC ibu hamil (Gravida, Para, Abortus, TFU, DJJ) |
| **Poli Spesialis** | `child_growths` | 20.000 | Pantauan tumbuh kembang balita, kurva berat badan, imunisasi |
| **Farmasi & Stok** | `medicine_batches` | 150 | 3 Batch per 50 obat dengan kedaluwarsa 2024, 2026, 2028 |
| **Farmasi & Stok** | `medicine_receipts` | 150 | Faktur penerimaan stok awal dari Pedagang Besar Farmasi (PBF) |
| **Farmasi & Stok** | `medicine_receipt_items` | 150 | Rincian kuantitas penerimaan per batch |
| **Farmasi & Stok** | `medicine_stock_movements` | 488.150 | Kartu stok mutasi penerimaan (`receipt`) dan penyerahan obat (`dispense`) berprinsip FEFO |
| **Farmasi & Resep** | `prescriptions` | 192.000 | Resep obat elektronik dokter |
| **Farmasi & Resep** | `prescription_items` | 488.000 | Rincian obat jadi per resep (dosis, frekuensi, rute, signa) |
| **Farmasi & Resep** | `prescription_compounds` | 16.000 | Resep obat racikan / puyer batuk pilek anak & dewasa |
| **Farmasi & Resep** | `prescription_compound_items` | 32.000 | Komposisi bahan aktif racikan farmasi |
| **Farmasi & Resep** | `dispensing_transactions` | 192.000 | Transaksi telaah dan penyerahan obat farmasi |
| **Farmasi & Resep** | `dispensing_items` | 488.000 | Rincian batch obat yang diserahkan ke pasien |
| **Laboratorium** | `laboratory_requests` | 20.000 | Order uji penunjang laboratorium patologi |
| **Laboratorium** | `laboratory_request_items` | 20.000 | Item parameter pemeriksaan lab (Darah Rutin, GDS, Asam Urat, dll) |
| **Laboratorium** | `laboratory_results` | 20.000 | Hasil pengukuran laboratorium, flag nilai kritis, nilai normal |
| **Billing & Kasir** | `billing_invoices` | 192.000 | Faktur tagihan pasien (status lunas) |
| **Billing & Kasir** | `billing_items` | 692.000 | Item rincian tagihan (administrasi, konsultasi, tindakan, obat, lab) |
| **Billing & Kasir** | `payments` | 192.000 | Kwitansi kasir (tunai, transfer, QRIS, debit) |
| **Billing & Kasir** | `refunds` | 4.000 | Retur / pengembalian dana transaksi pembatalan |
| **Jasa Medis** | `medical_fee_transactions` | 192.000 | Pembagian jasa dokter, jasa perawat, dan pendapatan klinik |
| **Dokumen & Regulasi** | `medical_documents` | 27.429 | Surat keterangan medis (sakit, sehat, surat rujukan faskes lanjut) |
| **Dokumen & Regulasi** | `patient_documents` | 8.000 | Berkas digital penunjang identitas & rekam medis pasien |
| **Audit & Integrasi** | `audit_logs` | 48.000 | Audit trail rekam aktivitas pengguna sistem |
| **Audit & Integrasi** | `fhir_mappings` | 48.000 | Pemetaan UUID FHIR SatuSehat Kemenkes RI |

---

## 4. Pola Skenario Klinis & Distribusi Kasus

Dataset dibangun menggunakan 10 arketipe skenario klinis realistis yang didistribusikan secara berimbang:

1. **ISPA & Faringitis Akut (Poli Umum)**: Keluhan demam, batuk berdahak, nyeri telan. Diagnosis `J06.9` (ISPA) & `J02.9` (Faringitis Akut). Terapi Paracetamol, Amoxicillin, Salbutamol, dan Ambroxol.
2. **Hipertensi Primer Esensial (Poli Umum)**: Keluhan pusing tengkuk, tekanan darah tinggi (>140/90 mmHg). Diagnosis `I10` (Hipertensi Esensial). Terapi Amlodipine dan edukasi diet rendah garam.
3. **Diabetes Melitus Tipe 2 (Poli Umum)**: Keluhan lemas, poliuria, polidipsia. Diagnosis `E11.9`. Pemeriksaan lab Glukosa Darah Sewaktu (GDS). Terapi Metformin dan Glimepiride.
4. **Dispepsia & Sindrom Gastritis (Poli Umum)**: Keluhan perih ulu hati, mual kembung. Diagnosis `K30` (Dispepsia) & `K29.7` (Gastritis). Terapi Antasida, Omeprazole, Ranitidine, Domperidone.
5. **Gastroenteritis Akut (GEA) (Poli Umum)**: Diare cair berulang, dehidrasi ringan. Diagnosis `A09`. Terapi Oralit sachet, Loperamide, dan Zink.
6. **Karies Dentis & Pulpitis (Poli Gigi)**: Gigi berlubang, nyeri berdenyut. Diagnosis `K02.9` & `K04.0`. Tindakan penambalan kavitasi / restorasi gigi (`ICD-9-CM 23.2`). Notasi FDI gigi `16`, `26`, `36`, `46`.
7. **Pembersihan Karang Gigi / Kalkulus (Poli Gigi)**: Gusi berdarah, plak gigi. Diagnosis `K05.0` (Gingivitis Akut). Tindakan scaling supragingiva (`ICD-9-CM 96.54`).
8. **Pemeriksaan Kehamilan / ANC Terpadu (Poli KIA)**: Kontrol antenatal rutin trimester 1-3. Diagnosis `Z34.8`. Rekam TFU, Leopold, DJJ (138-144 bpm), Tablet Tambah Darah (TTD), dan Kalsium Laktat.
9. **Tumbuh Kembang & Imunisasi Balita (Poli KIA)**: Pemantauan berat badan, tinggi badan, lingkar kepala. Diagnosis `Z00.1`. Vaksinasi Pentavalen / Polio / Campak dan vitamin.
10. **Dermatitis Kontak & Alergi Kulit (Poli Umum)**: Gatal, bercak kemerahan di kulit. Diagnosis `L23.9` & `L50.9`. Terapi Cetirizine, Hidrokortison Salep, dan Dexamethasone.

---

## 5. Hasil Pengujian Integritas Relasional & Audit

Seluruh 10 parameter pengujian integritas dieksekusi melalui command:
```bash
php artisan clinic:validate-dataset
```

### Rekapitulasi Status Validasi:
```
======================================================================
 AUDIT DAN VALIDASI INTEGRITAS DATASET RME KLINIK (2020 - 2026) 
======================================================================
+---------------------------------------------------+-----------------------------------------------------+--------+
| Parameter Pengujian Integritas                    | Keterangan / Hasil Verifikasi                       | Status |
+---------------------------------------------------+-----------------------------------------------------+--------+
| Target Pasien Minimum 100.000                     | LULUS (100,000 pasien)                              | PASS   |
| Target Data Operasional Minimum 100.000           | LULUS (5,944,314 records)                           | PASS   |
| Rentang Waktu Kunjungan (2020 s/d 2026)           | Min: 2020-01-02 08:08:52 | Max: 2026-11-16 15:57:33 | PASS   |
| Integritas Kunjungan (Tanpa Orphan Patient)       | 0 orphan record                                     | PASS   |
| Integritas Rekam Medis (Tanpa Orphan Visit)       | 0 orphan record                                     | PASS   |
| Validitas Kode Diagnosis ICD-10 Riil              | Semua diagnosis sesuai master ICD-10                | PASS   |
| Non-Negative Stock Batch Obat (FEFO Compliance)   | 0 batch dengan stok negatif                         | PASS   |
| Akurasi Matematika Billing (Sample 100 Invoice)   | Semua total & diskon presisi 100%                   | PASS   |
| Validitas Notasi Gigi Odontogram (FDI 2-Digit)    | Semua notasi gigi valid (11-85)                     | PASS   |
| Kronologi Pelayanan (Waktu Periksa >= Registrasi) | Kronologi logis 100%                                | PASS   |
+---------------------------------------------------+-----------------------------------------------------+--------+
✓ SELURUH UJI INTEGRITAS DATASET BERHASIL DENGAN STATUS PASS (100% VALID)
```

### Hasil Test Suite Otomatis (Pest PHP):
```
PASS  Tests\Feature\TablePaginationTest
PASS  Tests\Feature\AuthTest
PASS  Tests\Feature\PatientTest
PASS  Tests\Feature\VisitTest
PASS  Tests\Feature\MedicalRecordTest
PASS  Tests\Feature\PharmacyTest
PASS  Tests\Feature\BillingTest
PASS  Tests\Feature\ReportTest

Tests:    61 passed (380 assertions)
Duration: 4.72s
```

---

## 6. Akun Karyawan & Akses Login Aplikasi

Seluruh akun telah disiapkan dengan kata sandi default `password123`:

| Peran / Jabatan | Username | Nama Petugas | Hak Akses Utama |
|---|---|---|---|
| **Kepala Klinik** | `kepala` | dr. Hendra Wijaya, Sp.PK | Seluruh modul manajemen, laporan keuangan, audit trail, rekam medis |
| **Dokter Umum** | `dokter` | dr. Sarah Amelia | Poli Umum, anamnesis, diagnosis ICD-10, tindakan ICD-9-CM, e-resep |
| **Dokter Gigi** | `doktergigi` | drg. Budi Santoso | Poli Gigi, odontogram visual FDI, tindakan restorasi & ekstraksi gigi |
| **Bidan / Perawat KIA**| `perawat` | Siti Nurhaliza, S.Kep., Ners | Poli KIA, triage awal, ANC ibu hamil, rekam tumbuh kembang anak |
| **Apoteker / Farmasi** | `farmasi` | apt. Dimas Prasetyo, S.Farm | Telaah resep, dispensing obat FEFO, kartu stok obat, stok opname |
| **Admisi & Kasir** | `admisi` | Rina Wulandari | Registrasi pasien, antrean pendaftaran, pembayaran kasir, cetak invoice |
