# RME KLINIK - Pre-Generated Static Datasets Architecture

Folder ini merupakan lokasi penyimpanan file dataset CSV statis (*pre-generated CSV datasets*) berskala besar untuk aplikasi **RME KLINIK**.

## Prinsip Desain & Zero-Loop Architecture

Sesuai arsitektur performa tinggi:
1. **Zero-Loop Seeder**: File seeder Laravel (`ComprehensivePatientSeeder.php` dan `ComprehensiveOperationSeeder.php`) **sama sekali tidak menggunakan perulangan** (`for`, `foreach`, `while`, dll.).
2. **Native Bulk Loader**: Seeder memanfaatkan `LOAD DATA LOCAL INFILE` via PDO MySQL/MariaDB dengan fitur escaping otomatis kolom reserved keyword (misal: `` `condition` ``).
3. **Kecepatan Impor**:
   - 100.000 data pasien unik diimpor dalam **~7 detik**.
   - ~5.940.000 data transaksi operasional diimpor dalam **~102 detik**.
4. **Ukuran File & GitHub Limit**: Karena total dataset berukuran ~975 MB dengan beberapa file melampaui 100 MB (`visit_status_histories.csv` 162 MB, `medical_records.csv` 114 MB) yang akan ditolak oleh GitHub file size policy, file `*.csv` dieksklusikan dari repository git dan dapat di-generate ulang kapan saja secara instan.
5. **Auto-Regenerate Fallback**: Jika file CSV belum tersedia pada repository lokal baru, seeder secara otomatis mendeteksi dan menjalankan generator `clinic:generate-dataset-files`.

## Perintah Artisan Generator & Validasi

```bash
# Menghasilkan seluruh file CSV (100.000 pasien & 200.000 kunjungan)
php artisan clinic:generate-dataset-files --patients=100000 --visits=200000

# Menjalankan migrasi ulang dan impor data berkecepatan tinggi
php artisan migrate:fresh --seed

# Memvalidasi seluruh integritas data, relasi, dan akurasi matematika billing
php artisan clinic:validate-dataset
```

## Daftar File Dataset (37 Tabel)

| Nama File CSV | Tabel Database | Deskripsi Data |
|---|---|---|
| `patients.csv` | `patients` | 100.000 Pasien Unik Indonesia (NIK, RM-000001 s/d RM-100000) |
| `patient_allergies.csv` | `patient_allergies` | Riwayat alergi obat, makanan, dan udara |
| `patient_emergency_contacts.csv` | `patient_emergency_contacts` | Kontak darurat keluarga terdekat |
| `medicines.csv` | `medicines` | 50 Master obat esensial klinik |
| `medicine_batches.csv` | `medicine_batches` | 150 Batch obat berjenjang FEFO (2024, 2026, 2028) |
| `medicine_receipts.csv` | `medicine_receipts` | Faktur penerimaan PBF awal |
| `medicine_receipt_items.csv` | `medicine_receipt_items` | Rincian penerimaan stok per batch |
| `medicine_stock_movements.csv` | `medicine_stock_movements` | Kartu mutasi stok (inflow penerimaan & outflow dispensing) |
| `visits.csv` | `visits` | 200.000 Kunjungan poliklinik (Poli Umum, Gigi, KIA) rentang 2020-2026 |
| `visit_status_histories.csv` | `visit_status_histories` | 1.168.000 Riwayat transisi status alur pelayanan |
| `nursing_assessments.csv` | `nursing_assessments` | 192.000 Asesmen awal perawat & triage |
| `vital_signs.csv` | `vital_signs` | 192.000 Tanda-tanda vital lengkap (TD, Suhu, Nadi, RR, SpO2, BMI) |
| `medical_records.csv` | `medical_records` | 192.000 Rekam medis dokter (SOAP terstruktur) |
| `medical_record_diagnoses.csv` | `medical_record_diagnoses` | 224.000 Diagnosis ICD-10 primer & sekunder |
| `medical_record_procedures.csv` | `medical_record_procedures` | 192.000 Tindakan medis ICD-9-CM |
| `odontograms.csv` | `odontograms` | 40.000 Pemeriksaan poli gigi |
| `odontogram_teeth.csv` | `odontogram_teeth` | 80.000 Kondisi gigi spesifik dengan standar FDI 2-digit |
| `antenatal_cares.csv` | `antenatal_cares` | 20.000 Pemeriksaan kehamilan (ANC) Poli KIA |
| `child_growths.csv` | `child_growths` | 20.000 Rekam tumbuh kembang anak & balita |
| `prescriptions.csv` | `prescriptions` | 192.000 Resep dokter terintegrasi |
| `prescription_items.csv` | `prescription_items` | 488.000 Item obat resep |
| `prescription_compounds.csv` | `prescription_compounds` | 16.000 Resep obat racikan / puyer |
| `prescription_compound_items.csv` | `prescription_compound_items` | 32.000 Komponen racikan farmasi |
| `dispensing_transactions.csv` | `dispensing_transactions` | 192.000 Transaksi penyerahan obat kasir/apotek |
| `dispensing_items.csv` | `dispensing_items` | 488.000 Penyerahan obat dengan alokasi batch FEFO |
| `laboratory_requests.csv` | `laboratory_requests` | 20.000 Permintaan uji laboratorium |
| `laboratory_request_items.csv` | `laboratory_request_items` | 20.000 Parameter uji laboratorium yang diminta |
| `laboratory_results.csv` | `laboratory_results` | 20.000 Hasil uji lab lengkap dengan nilai rujukan |
| `billing_invoices.csv` | `billing_invoices` | 192.000 Faktur tagihan pasien (status lunas) |
| `billing_items.csv` | `billing_items` | 692.000 Rincian tagihan (adm, konsul, tindakan, obat, lab) |
| `payments.csv` | `payments` | 192.000 Kwitansi kasir (tunai, transfer, QRIS, debit) |
| `refunds.csv` | `refunds` | 4.000 Transaksi retur / pembatalan klaim |
| `medical_fee_transactions.csv` | `medical_fee_transactions` | 192.000 Bagi hasil jasa medis (dokter, perawat, klinik) |
| `medical_documents.csv` | `medical_documents` | 27.429 Surat keterangan medis (sakit, sehat, rujukan) |
| `patient_documents.csv` | `patient_documents` | 8.000 Berkas identitas & penunjang pasien |
| `audit_logs.csv` | `audit_logs` | 48.000 Log audit jejak rekam medis |
| `fhir_mappings.csv` | `fhir_mappings` | 48.000 Pemetaan interoperabilitas FHIR SatuSehat Kemenkes |
