# RME KLINIK - Pre-Generated Physical Datasets Architecture

Direktori ini berisi seluruh file dataset CSV fisik (*pre-generated CSV datasets*) berskala besar untuk aplikasi **RME KLINIK Terpadu**.
Dataset ini merupakan data nyata pengembangan yang telah disiapkan secara fisik dan lengkap sebelum proses seeding dijalankan, mencakup periode tahun **2020 sampai 2026**.

## 1. Prinsip Desain & Zero-Loop Architecture

Sesuai aturan ketat dan arsitektur berkinerja tinggi:
1. **Zero-Loop Seeder**: Seluruh file seeder Laravel (`ComprehensivePatientSeeder.php`, `ComprehensiveOperationSeeder.php`, `ClinicMasterSeeder.php`, `IcdCodesSeeder.php`, `DoctorScheduleSeeder.php`, `RoleAndPermissionSeeder.php`, `UserAndEmployeeSeeder.php`, `DatabaseSeeder.php`) **sama sekali tidak menggunakan perulangan** (`for`, `foreach`, `while`, `do-while`, generator, rekursi).
2. **Physical Dataset First**: Seluruh record data telah disiapkan dalam file CSV fisik di direktori proyek ini (`database/seeders/datasets/`), bukan di-generate secara procedural saat seeder berjalan.
3. **High-Speed MySQL Bulk Loader**: Seeder memanfaatkan PDO MySQL `LOAD DATA LOCAL INFILE` dengan konfigurasi:
   - `FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' ESCAPED BY '\\'`
   - `LINES TERMINATED BY '\r\n'` atau `'\n'`
   - Konversi nilai `NULL` menggunakan notasi standar `\N`.
4. **Kecepatan Impor Skala Jutaan**:
   - 100.000 data pasien unik Indonesia diimpor dalam **~7 detik**.
   - > 6.000.000 data operasional klinis diimpor dalam **~110 detik**.
5. **Relational & Temporal Integrity**:
   - Seluruh foreign key terhubung valid antar-tabel tanpa orphan record.
   - Kronologi waktu konsisten: Waktu Pendaftaran Pasien <= Waktu Kunjungan <= Asesmen Triage <= Pemeriksaan Dokter <= Resep & Lab <= Kasir & Pembayaran.
   - Tidak ada transaksi yang diselesaikan di masa depan (maksimal waktu transaksi dibatasi sampai saat ini, 15 November 2026).
   - Seluruh antrean kerja aktif hari ini (*active worklist queues*) terisi dengan variasi status realistis (`terdaftar`, `menunggu_pemeriksaan_awal`, `pemeriksaan_awal_berlangsung`, `menunggu_dokter`, `pemeriksaan_dokter_berlangsung`, `menunggu_farmasi`, `menunggu_pembayaran`, `selesai`, `dibatalkan`).

---

## 2. Inventaris Lengkap File Dataset Fisik (45 File)

| No | File CSV | Tabel Database | Deskripsi Record | Est. Baris |
|---|---|---|---|---|
| 1 | `patients.csv` | `patients` | 100.000 Pasien Unik Indonesia lengkap (NIK sintetis valid, NRM RM-000001 s/d RM-100000, biodata, alamat Kota Malang) | 100.000 |
| 2 | `patient_allergies.csv` | `patient_allergies` | Riwayat alergi obat, makanan, dan udara pada pasien | ~14.285 |
| 3 | `patient_emergency_contacts.csv` | `patient_emergency_contacts` | Kontak darurat keluarga penanggung jawab pasien | 50.000 |
| 4 | `patient_documents.csv` | `patient_documents` | Berkas penunjang dan identitas digital pasien | ~8.000 |
| 5 | `document_templates.csv` | `document_templates` | Master template surat sakit, sehat, rujukan, lab, dan informed consent | 5 |
| 6 | `medical_fee_rules.csv` | `medical_fee_rules` | Master aturan perhitungan jasa medis dokter, perawat, bidan, dan klinik | 6 |
| 7 | `icd10_codes.csv` | `icd10_codes` | Katalog diagnosis internasional ICD-10 resmi | 18.543 |
| 8 | `icd9cm_codes.csv` | `icd9cm_codes` | Katalog prosedur & tindakan medis internasional ICD-9-CM resmi | 4.626 |
| 9 | `medicines.csv` | `medicines` | Master 50 obat esensial poliklinik terpadu | 50 |
| 10 | `medicine_batches.csv` | `medicine_batches` | 150 Batch obat dengan variasi tanggal kedaluwarsa berjenjang (2024, 2026, 2028) | 150 |
| 11 | `medicine_receipts.csv` | `medicine_receipts` | Faktur penerimaan pasokan obat dari Pedagang Besar Farmasi (PBF) | 150 |
| 12 | `medicine_receipt_items.csv` | `medicine_receipt_items` | Rincian penerimaan stok obat per batch | 150 |
| 13 | `medicine_stock_opnames.csv` | `medicine_stock_opnames` | 28 Sesi audit stock opname fisik berkala (triwulanan 2020-2026) | 28 |
| 14 | `medicine_stock_opname_items.csv` | `medicine_stock_opname_items` | Rincian fisik stok obat yang diaudit dalam stock opname | 1.400 |
| 15 | `medicine_stock_movements.csv` | `medicine_stock_movements` | Kartu mutasi stok obat (inflow penerimaan & outflow dispensing FEFO) | ~488.150 |
| 16 | `visits.csv` | `visits` | 200.000 Kunjungan poliklinik (Poli Umum, Gigi, KIA) rentang 2020 s/d 2026 | 200.000 |
| 17 | `visit_status_histories.csv` | `visit_status_histories` | Riwayat kronologis alur status pelayanan pasien | ~1.168.000 |
| 18 | `nursing_assessments.csv` | `nursing_assessments` | Pengkajian awal perawat, anamnesis, triage, dan rencana keperawatan | ~192.000 |
| 19 | `vital_signs.csv` | `vital_signs` | Rekam tanda-tanda vital lengkap (TD, Nadi, Suhu, RR, SpO2, BMI, TB, BB) | ~192.000 |
| 20 | `medical_records.csv` | `medical_records` | Rekam medis dokter (SOAP terstruktur: Subjective, Objective, Assessment, Plan) | ~192.000 |
| 21 | `medical_record_addendums.csv` | `medical_record_addendums` | Catatan addendum revisi/pelengkap klinis oleh dokter penanggung jawab | 1.000 |
| 22 | `medical_record_diagnoses.csv` | `medical_record_diagnoses` | Diagnosis ICD-10 klinis (primer dan sekunder/penyerta) | ~224.000 |
| 23 | `medical_record_procedures.csv` | `medical_record_procedures` | Tindakan klinis ICD-9-CM beserta tarif layanan | ~192.000 |
| 24 | `odontograms.csv` | `odontograms` | Pemeriksaan odontogram poli gigi | 40.000 |
| 25 | `odontogram_teeth.csv` | `odontogram_teeth` | Detail kondisi gigi individual dengan sistem penomoran FDI 2-digit (11-85) | 80.000 |
| 26 | `antenatal_cares.csv` | `antenatal_cares` | Pemeriksaan Antenatal Care (ANC) ibu hamil pada Poli KIA | 20.000 |
| 27 | `child_growths.csv` | `child_growths` | Pemantauan tumbuh kembang anak, antropometri, dan imunisasi rutin balita | 20.000 |
| 28 | `prescriptions.csv` | `prescriptions` | Resep elektronik dokter poliklinik | ~192.000 |
| 29 | `prescription_items.csv` | `prescription_items` | Item obat terperinci pada resep (dosis, frekuensi, aturan pakai) | ~488.000 |
| 30 | `prescription_compounds.csv` | `prescription_compounds` | Resep obat racikan / puyer terpadu | 16.000 |
| 31 | `prescription_compound_items.csv` | `prescription_compound_items` | Komponen bahan obat dalam racikan farmasi | 32.000 |
| 32 | `dispensing_transactions.csv` | `dispensing_transactions` | Transaksi penyerahan obat apotek/kasir | ~192.000 |
| 33 | `dispensing_items.csv` | `dispensing_items` | Rincian penyerahan obat dengan alokasi batch FEFO (First Expired, First Out) | ~488.000 |
| 34 | `laboratory_requests.csv` | `laboratory_requests` | Permintaan uji laboratorium klinik (Target >= 50.000) | ~81.000 |
| 35 | `laboratory_request_items.csv` | `laboratory_request_items` | Parameter uji lab yang diminta dokter (DL, GDS, Kolesterol, Asam Urat, Urin, Widal) | ~81.000 |
| 36 | `laboratory_results.csv` | `laboratory_results` | Hasil uji laboratorium lengkap dengan nilai rujukan dan bendera abnormalitas | ~81.000 |
| 37 | `billing_invoices.csv` | `billing_invoices` | Faktur tagihan pelayanan pasien (status lunas dan belum_dibayar) | ~192.000 |
| 38 | `billing_items.csv` | `billing_items` | Rincian item tagihan (adm, konsultasi, tindakan medis, obat, lab) | ~750.000 |
| 39 | `payments.csv` | `payments` | Kwitansi bukti pembayaran kasir (Tunai, QRIS, Debit, Transfer) | ~192.000 |
| 40 | `refunds.csv` | `refunds` | Transaksi pengembalian dana / retur klaim revisi asuransi | 4.000 |
| 41 | `medical_fee_transactions.csv` | `medical_fee_transactions` | Perhitungan bagi hasil jasa medis dokter, perawat, dan sarana klinik | ~192.000 |
| 42 | `medical_documents.csv` | `medical_documents` | Dokumen surat keterangan sakit, berbadan sehat, dan rujukan pasien | ~28.000 |
| 43 | `audit_logs.csv` | `audit_logs` | Catatan audit trail kepegawaian dan jejak aktivitas medis sistem | ~48.000 |
| 44 | `fhir_mappings.csv` | `fhir_mappings` | Pemetaan interoperabilitas FHIR SatuSehat Kemenkes RI | ~48.000 |
| 45 | `fhir_sync_logs.csv` | `fhir_sync_logs` | Catatan log transmisi HTTP response FHIR SatuSehat | ~48.000 |

**Total Record Keseluruhan:** > 6.200.000 Record.

---

## 3. Eksekusi Seeder

Untuk menjalankan migrasi ulang dan impor dataset:

```bash
php artisan migrate:fresh --seed
```

Untuk memvalidasi integritas relasi, non-negative stock, dan konsistensi matematika billing:

```bash
php artisan clinic:validate-dataset
```
