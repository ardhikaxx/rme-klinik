# 📋 Riwayat Perubahan (Changelog)

Semua perubahan penting dan riwayat pengembangan sistem **RME KLINIK (Rekam Medis Elektronik Terpadu - Klinik Rawat Inap BeSt Medika)** didokumentasikan secara terstruktur dalam dokumen ini.

Format pencatatan mengacu pada standar [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mematuhi [Semantic Versioning (SemVer)](https://semver.org/).

---

## [1.1.0] - 2026-11-15
### 📊 Dataset Klinis Skala Besar & Modernisasi Antarmuka Pengguna
Dikembangkan oleh **Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**.

### ✨ Added (Fitur Baru)
- **Dataset Skala Besar (>6.69 Juta Record)**:
  - 100.000 data pasien unik (*Master Patient Index*) dengan identitas demografi lengkap, riwayat alergi, dan kontak darurat.
  - Lebih dari 6.570.000 data operasional klinis saling terhubung lintas modul untuk rentang waktu tahun 2020 hingga November 2026.
  - Implementasi seeder **Zero-Loop** tanpa perulangan (`for`, `foreach`, `while`, generator acak), memanfaatkan MySQL `LOAD DATA LOCAL INFILE` dari berkas fisik CSV pada `database/seeders/datasets/` dengan waktu eksekusi super cepat (~2 menit 7 detik).
  - Perintah validasi integritas dataset otomatis: `php artisan clinic:validate-dataset`.
- **Modernisasi Breadcrumbs Terpadu**:
  - Implementasi breadcrumb hierarkis interaktif berbasis pill container pada seluruh 42 halaman aplikasi.
  - Pemisah chevron modern FontAwesome 6 dan tautan kembali langsung ke Beranda.
- **Logo Resmi Monokrom Putih pada Sidebar**:
  - Integrasi logo resmi *Klinik Rawat Inap BeSt Medika* monokrom satu warna putih (`#FFFFFF`) dengan transparansi presisi pada sidebar.
  - Dukungan responsif otomatis untuk mode sidebar penuh (`logo-white.png`) dan mode sidebar mengecil/collapsed (`logo-icon-white.png`).

### 🎨 Changed (Penyempurnaan & Refactoring)
- **Optimalisasi Tata Letak Tabel Data**:
  - Penambahan padding pembungkus (`0.85rem 1.15rem`) pada seluruh card table sehingga isi data tidak menempel pada batas kartu.
  - Penyesuaian ukuran font baris data ke `0.8125rem` (13px) dengan line-height proporsional, menampilkan 30-40% lebih banyak baris per layar dengan keterbacaan yang sangat nyaman.
- **Penataan Ulang Floating Topbar**:
  - Pemindahan judul halaman aktif (`@yield('page_title')`) langsung ke sisi kiri floating topbar di samping tombol toggle navigasi.
  - Penghapusan tombol-tombol shortcut cepat pada topbar guna menghadirkan tata letak header yang bersih, tenang, dan elegan.
- **Standardisasi Formatting & Kode**:
  - Pembersihan gaya penulisan PHP sesuai PSR-12 menggunakan Laravel Pint.
  - Seluruh pengujian fitur (61 Pest feature tests, 382 assertions) lolos 100%.

---

## [1.0.0] - 2026-10-15
### 🚀 Rilis Perdana Sistem (Initial Production Release)
Arsitek & Pengembang Utama: **Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**.

### ✨ Added (Fitur Inti)
- **Autentikasi & Otorisasi Berbasis Peran (RBAC)**:
  - Manajemen akses berbasis Spatie Laravel Permission untuk 5 peran: Kepala Klinik, Admisi & Kasir, Perawat & Bidan, Dokter, dan Tenaga Farmasi.
  - Middleware perlindungan status akun pengguna aktif.
- **Master Patient Index (MPI)**:
  - Pengelolaan data pasien lengkap: No. Rekam Medis unik, NIK terverifikasi, demografi, alamat, pekerjaan, penanggung jawab, kontak darurat, dan riwayat alergi obat/makanan.
- **Pendaftaran Kunjungan & Antrean Poli**:
  - Alur pendaftaran pasien baru dan lama, pemilihan dokter & jadwal poli tujuan, metode pembayaran, nomor antrean harian otomatis.
- **Pelayanan Keperawatan (Pengkajian Awal & Tanda Vital)**:
  - Form pengkajian awal komprehensif: keluhan utama, riwayat penyakit, skrining gizi, skrining jatuh, tanda vital (tensi, nadi, suhu, nafas, SpO2), antropometri (TB, BB, IMT otomatis).
- **Ruang Pemeriksaan Dokter (Klinis SOAP)**:
  - Pemeriksaan medis berstandar SOAP (*Subjective, Objective, Assessment, Plan*).
  - Integrasi katalog diagnosis ICD-10 dan tindakan/prosedur medis ICD-9-CM.
  - Fasilitas e-resep langsung dari ruang pemeriksaan terhubung ke modul farmasi.
- **Odontogram Gigi (Standar FDI)**:
  - Diagram visual 32 gigi dewasa dan 20 gigi susu dengan notasi internasional FDI Two-Digit.
  - Pencatatan kondisi gigi (karies, tambalan, mahkota, gigi hilang) dan cetak rekam odontogram.
- **Kesehatan Ibu & Anak (KIA)**:
  - Pemantauan kehamilan (HPHT, HPL, usia gestasi, tinggi fundus, DJJ), imunisasi anak, dan visualisasi grafik pertumbuhan persentil berat/tinggi badan.
- **Pelayanan Farmasi & Manajemen Obat**:
  - Pengelolaan master obat, kategori, satuan, bentuk sediaan, supplier, dan nomor batch.
  - Dispensasi e-resep dokter dengan aturan pakai dan frekuensi.
  - Manajemen stok obat dengan prinsip **FEFO** (*First Expired, First Out*), kartu stok riwayat mutasi obat, dan formulir stok opname fisik.
- **Pelayanan Kasir & Penagihan (Billing)**:
  - Pembuatan invoice otomatis dari tindakan dokter, pemeriksaan lab, dan resep obat yang ditebus.
  - Dukungan multi-metode pembayaran: Tunai, Transfer Bank, Debit, dan QRIS.
  - Perhitungan diskon, kembalian otomatis, dan cetak kuitansi resmi pembayaran.
- **Laboratorium Klinik**:
  - Permintaan uji laboratorium oleh dokter, manajemen sampel, pencatatan hasil parameter lab dengan rentang nilai rujukan normal, dan cetak laporan hasil lab.
- **Penerbitan Dokumen Medis**:
  - Generator otomatis Surat Keterangan Sakit, Surat Keterangan Sehat, dan Surat Rujukan Faskes Lanjutan.
- **Integrasi Standar SATUSEHAT HL7 FHIR (R4)**:
  - Transformasi data klinis ke profil FHIR Release 4 Kemenkes RI: Patient, Encounter, Condition (ICD-10), Observation (Vital Signs), dan MedicationRequest.
- **Audit Trail & Laporan Eksekutif**:
  - Rekam jejak seluruh mutasi data penting pada tabel `audit_logs`.
  - Dashboard analitik kunjungan pasien, 10 besar diagnosis terbanyak, pendapatan keuangan, dan tren penggunaan obat.
