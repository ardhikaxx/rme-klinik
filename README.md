# 🏥 RME KLINIK — Rekam Medis Elektronik Terpadu & Manajemen Rawat Inap

[![Laravel](https://img.shields.io/badge/Laravel-12.x%20%2F%2013.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![MariaDB / MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-10.4%2B-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mariadb.org)
[![Tested with Pest](https://img.shields.io/badge/Tested%20with-Pest%20PHP-EB4432?style=for-the-badge&logo=pest&logoColor=white)](https://pestphp.com)
[![License](https://img.shields.io/badge/License-Proprietary-red?style=for-the-badge)](./LICENSE)
[![Author](https://img.shields.io/badge/Author-Yanuar%20Ardhika%20Rahmadhani%20Ubaidillah-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/ardhikaxx)

Sistem Informasi **RME KLINIK (Rekam Medis Elektronik Terpadu - Klinik Rawat Inap BeSt Medika)** adalah platform digital komprehensif berstandar nasional untuk tata kelola pelayanan fasilitas kesehatan tingkat pertama (FKTP) dan klinik rawat inap. 

Sistem ini dirancang dengan mematuhi regulasi **Peraturan Menteri Kesehatan (Permenkes) No. 24 Tahun 2022 tentang Rekam Medis Elektronik**, standar interoperabilitas **HL7 FHIR Release 4 Kemenkes RI**, serta **Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022)**.

---

## ✨ Fitur & Modul Utama Sistem

### 1. 🔑 Kontrol Akses Berbasis Peran Granular (RBAC)
- Menggunakan Spatie Laravel Permission yang memisahkan hak akses ke dalam **5 Peran Spesifik**:
  1. **Kepala Klinik**: Pemantauan KPI eksekutif, laporan pendapatan harian/bulanan, tren morbiditas 10 penyakit terbesar, dan audit trail log.
  2. **Petugas Admisi & Kasir**: Pendaftaran pasien baru/lama (Master Patient Index), penerbitan nomor antrean per poli, kalkulasi penagihan (billing), multi-metode pembayaran, dan cetak kuitansi.
  3. **Perawat & Bidan**: Triase, pengkajian keperawatan awal, pemeriksaan tanda-tanda vital, antropometri, skrining jatuh, dan skrining nutrisi.
  4. **Dokter Pemeriksa**: Anamnesis klinis SOAP (*Subjective, Objective, Assessment, Plan*), kodifikasi diagnosis ICD-10, tindakan medis ICD-9-CM, odontogram visual FDI, dan e-resep farmasi.
  5. **Tenaga Farmasi**: Telaah resep dokter, verifikasi ketersediaan stok obat, kartu stok digital, mutasi obat metode FEFO (*First Expired, First Out*), dan stok opname berkala.

### 2. 📇 Master Patient Index (MPI) & Demografi Pasien
- Nomor Rekam Medis unik terstandarisasi (`RM-YYYY-XXXXXX`).
- Identitas lengkap: NIK terverifikasi, jenis kelamin, tanggal & tempat lahir, golongan darah, alamat lengkap, kontak telepon, pekerjaan, pendidikan, serta data penanggung jawab & kontak darurat.
- Peringatan riwayat alergi obat dan makanan yang selalu muncul di seluruh formulir pelayanan klinis.

### 3. 🎫 Pendaftaran Kunjungan & Antrean Layanan
- Pendaftaran kunjungan pasien baru dan pasien lama terhubung ke jadwal dokter dan poli tujuan (Poli Umum, Poli Gigi, Poli KIA, dll.).
- Penomoran antrean registrasi harian otomatis anti-bentrok.
- Status alur kunjungan transparan: *Terdaftar → Menunggu Pengkajian → Dalam Pengkajian → Menunggu Dokter → Dalam Pemeriksaan → Farmasi / Kasir → Selesai*.

### 4. 🩺 Pengkajian Keperawatan & Tanda Vital
- Pengkajian komprehensif perawat: keluhan utama, riwayat penyakit dahulu & keluarga, riwayat alergi obat/makanan, tingkat kesadaran (*Glasgow Coma Scale*), dan keadaan umum.
- Pemeriksaan TTV lengkap: Tekanan Darah (Sistolik & Diastolik), Frekuensi Nadi, Laju Pernapasan, Suhu Tubuh, Saturasi Oksigen ($SpO_2$).
- Antropometri cerdas: Tinggi Badan, Berat Badan, Lingkar Kepala, Lingkar Lengan Atas, serta perhitungan otomatis Indeks Massa Tubuh (IMT) beserta kategori klinisnya.

### 5. 👨‍⚕️ Ruang Periksa Dokter (Pemeriksaan Klinis SOAP)
- Format pencatatan SOAP medis lengkap dengan validasi integratif.
- Pencarian cepat katalog diagnosis **ICD-10** (ribuan kode referensi WHO/Kemenkes).
- Pencatatan tindakan medis dan prosedur klinis **ICD-9-CM** beserta penetapan tarif tindakan.
- Fasilitas e-resep langsung dari ruang periksa ke modul instalasi farmasi.

### 6. 🦷 Odontogram Gigi Visual (Standar FDI Internasional)
- Visualisasi anatomis 32 gigi permanen dewasa dan 20 gigi desidui (anak-anak) menggunakan standar notasi internasional *FDI Two-Digit Dental System*.
- Pemetaan kondisi per gigi: karies gigi (*caries*), tambalan (*restoration*), gigi hilang (*missing*), sisa akar, mahkota (*crown*), dan kalkulus.
- Cetak dokumen rekam medis odontogram resmi klinik.

### 7. 👶 Kesehatan Ibu & Anak (KIA) & Grafik Pertumbuhan
- Pencatatan antenatal care (ANC): HPHT, taksiran persalinan (HPL), usia kehamilan, tinggi fundus uteri, denyut jantung janin (DJJ), dan letak janin.
- Pencatatan imunisasi dasar lengkap anak.
- Visualisasi grafik kurva persentil pertumbuhan anak standar CDC/WHO secara dinamis.

### 8. 💊 Instalasi Farmasi, Mutasi FEFO & e-Resep
- Manajemen katalog master obat: kategori farmasi, bentuk sediaan (tablet, sirup, salep, tetes, injeksi), satuan, produsen/supplier, dan nomor izin edar.
- Pelacakan batch obat berbasis **FEFO** (*First Expired, First Out*) — obat dengan tanggal kedaluwarsa terdekat otomatis direkomendasikan terlebih dahulu.
- Kartu stok otomatis mencatat setiap penerimaan, peresepan, koreksi, dan mutasi obat.
- Formulir stok opname fisik dengan kalkulasi selisih stok dan alasan koreksi.

### 9. 💳 Kasir, Penagihan Terpadu (Billing) & Kuitansi
- Pembentukan tagihan otomatis (*auto-billing*) mengintegrasikan biaya konsultasi/karcis, tindakan dokter, pemeriksaan penunjang lab, dan resep farmasi.
- Multi-metode pembayaran: **Tunai, Transfer Bank, Kartu Debit, dan QRIS**.
- Kalkulasi diskon, subtotal, total akhir, nominal bayar, dan uang kembalian secara presisi di sisi backend.
- Cetak kuitansi sah pembayaran dengan nomor kuitansi unik per transaksi.

### 10. 🔬 Laboratorium Klinik
- Pengelolaan permintaan pemeriksaan laboratorium penunjang medis dari dokter periksa.
- Manajemen sampel dan verifikasi hasil analisis laboratorium.
- Parameter uji lengkap (Darah Rutin, Hemoglobin, Leukosit, Trombosit, Gula Darah, Kolesterol, Asam Urat, Urine Lengkap) dilengkapi rentang nilai rujukan normal.
- Lembar cetak laporan hasil pemeriksaan laboratorium resmi bertanda tangan analis.

### 11. 📄 Dokumen Medis & Rujukan Pasien
- Penerbitan otomatis dokumen medis resmi:
  - **Surat Keterangan Sakit (SKS)** dengan durasi istirahat dan rekomendasi dokter.
  - **Surat Keterangan Sehat (SK Sehat)** untuk keperluan administrasi/pekerjaan.
  - **Surat Rujukan Faskes Lanjutan** (Klinik Rawat Inap / Rumah Sakit Rujukan).

### 12. 🌐 Interoperabilitas Standar SATUSEHAT HL7 FHIR (R4)
- Fasilitas transformasi data medis internal ke spesifikasi standar profil **HL7 FHIR Release 4 Kemenkes RI**:
  - `Patient` (Identitas Demografi & Kependudukan Pasien)
  - `Encounter` (Riwayat Kunjungan & Interaksi Faskes)
  - `Condition` (Diagnosis Klinis ICD-10)
  - `Observation` (Tanda Vital & Pemeriksaan Fisik)
  - `MedicationRequest` (Permintaan Resep Terapi Farmasi)

### 13. 📊 Dashboard Analitik Eksekutif & Jejak Audit (Audit Trail)
- Dashboard dinamis per role dengan ringkasan statistik kunjungan hari ini, antrean aktif, pendapatan klinik, dan stok obat kritis.
- Jejak audit (*immutable audit logs*) mencatat setiap transaksi perubahan data penting, pengguna bersangkutan, waktu, alamat IP, dan user agent.

---

## ⚡ Arsitektur Dataset Skala Besar (>6.6 Juta Record)

Sistem dilengkapi dengan dataset komprehensif berskala besar untuk simulasi dan uji performa faskes nyata (*real-world benchmark*):

| Komponen Data | Jumlah Record | Keterangan |
| :--- | :--- | :--- |
| **Pasien Unik (Master Patient Index)** | **100.000 Pasien** | Identitas demografi lengkap, riwayat alergi, dan kontak darurat |
| **Data Operasional Lintas Modul** | **>6.570.000 Record** | Tersebar di 35 tabel operasional klinik periode **2020 – 2026** |
| **Total Record Seeder Sistem** | **>6.690.000 Record** | Siap uji beban (*stress-test*) dan paginasi data |
| **Kunjungan Aktif Hari Ini (Demo)** | **80 Kunjungan** | Terbagi rata di seluruh antrean poli untuk simulasi pelayanan |

### 🚀 Zero-Loop Bulk Import
- Seluruh data disiapkan secara fisik dalam **45 berkas CSV terstruktur** di direktori `database/seeders/datasets/`.
- File seeder PHP ditulis **tanpa perulangan** (`for`, `foreach`, `while`), melainkan mengandalkan perintah native MySQL / MariaDB **`LOAD DATA LOCAL INFILE`**.
- Seluruh 6,69 juta record berhasil diimpor hanya dalam waktu **~2 menit 7 detik** (~127 detik total eksekusi).
- Verifikasi integritas relasi dan konsistensi data dapat dijalankan kapan saja dengan:
  ```bash
  php artisan clinic:validate-dataset
  ```

---

## 🎨 Antarmuka Pengguna yang Bersih & Ergonomis

1. **Logo Resmi Monokrom Putih**:
   - Sidebar dilengkapi logo resmi *Klinik Rawat Inap BeSt Medika* satu warna putih bersih (`#FFFFFF`) dengan efek drop-shadow lembut.
   - Beralih otomatis ke versi emblem monokrom saat sidebar dalam mode mengecil (*collapsed*).
2. **Breadcrumb Interaktif**:
   - Seluruh 42 halaman dilengkapi panduan navigasi hierarkis model pill dengan ikon chevron modern FontAwesome 6.
3. **Optimasi Card & Tabel Data**:
   - Card table memiliki padding pembungkus (`0.85rem 1.15rem`) sehingga baris data tidak menempel pada batas tepi kartu.
   - Ukuran font baris data disesuaikan menjadi `0.8125rem` (13px) sehingga mampu menampilkan 30–40% lebih banyak baris tanpa mengorbankan kenyamanan membaca.
4. **Floating Topbar Modern**:
   - Judul halaman aktif disematkan langsung di sisi kiri floating topbar di samping tombol toggle navigasi sidebar, memberikan tampilan yang tenang dan fokus.

---

## 🛠️ Panduan Instalasi & Menjalankan Sistem

### 1. Prasyarat Lingkungan (Prerequisites)
- **PHP**: Versi **8.4+** dengan ekstensi `pdo_mysql`, `bcmath`, `curl`, `mbstring`, `openssl`.
- **Database**: **MariaDB 10.4+** atau **MySQL 8.0+** dengan parameter `local_infile = 1`.
- **Composer**: Versi 2.x.
- **Node.js**: Versi 18+ & NPM.

### 2. Langkah Instalasi

```bash
# 1. Clone repositori resmi
git clone https://github.com/ardhikaxx/rme-klinik.git
cd rme-klinik

# 2. Instal dependensi PHP
composer install

# 3. Buat berkas konfigurasi .env
cp .env.example .env

# 4. Generate Application Key
php artisan key:generate

# 5. Konfigurasi Database pada .env
# Pastikan nama database, user, password, dan port sesuai dengan lingkungan lokal Anda:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=rme_klinik
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Jalankan Migrasi & Import Dataset Skala Besar (>6.6 Juta Record)
php artisan migrate:fresh --seed

# 7. Validasi Integritas Dataset (Opsional)
php artisan clinic:validate-dataset

# 8. Jalankan Local Development Server
php artisan serve
```

Aplikasi kini siap diakses melalui peramban pada alamat: **`http://localhost:8000`**

---

## 🔐 Kredensial Akun Pengguna Demo

Seluruh akun demo telah dikonfigurasi dan siap digunakan untuk menguji fungsionalitas masing-masing peran:

| Peran (Role) | Nama Pengguna (*Username*) | Kata Sandi (*Password*) | Lingkup Wewenang Utama |
| :--- | :--- | :--- | :--- |
| **Kepala Klinik** | `kepala` | `password` | Dashboard KPI, Laporan Keuangan, Audit Trail |
| **Petugas Admisi & Kasir** | `admisi` | `password` | Pendaftaran Pasien, Antrean Poli, Kasir & Kuitansi |
| **Perawat / Bidan** | `perawat` | `password` | Tanda Vital, Pengkajian Keperawatan, Asuhan KIA |
| **Dokter Umum / Spesialis** | `dokter` | `password` | Pemeriksaan SOAP, Odontogram Gigi, ICD-10, e-Resep |
| **Petugas Farmasi** | `farmasi` | `password` | Telaah e-Resep, Mutasi Stok FEFO, Stok Opname |

---

## 🧪 Pengujian Otomatis (Automated Testing)

Sistem dilengkapi pengujian otomatis berbasis **Pest PHP** yang mencakup pengujian otentikasi, otorisasi RBAC, pengkajian keperawatan, pemeriksaan klinis, odontogram, farmasi, kasir, dan penanganan dokumen:

```bash
# Jalankan seluruh test suite
php artisan test --compact
```

*Status Terkini: **61 Feature Tests passed, 382 Assertions (100% Green)**.*

---

## ⚖️ Lisensi & Hak Cipta (Proprietary License)

Sistem perangkat lunak ini, termasuk kode sumber, skema arsitektur database, antarmuka pengguna, dan dataset klinis terkait, adalah hak milik intelektual eksklusif dari:

**Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**  
*Copyright (c) 2024 – 2026. All Rights Reserved.*

> [!IMPORTANT]
> Proyek ini berlisensi **Proprietary (All Rights Reserved)**. Anda **DILARANG** menyalin, memodifikasi, mendistribusikan ulang, melisensikan ulang, menjual, mengklaim kepemilikan, atau menerapkan sistem ini untuk operasional fasilitas kesehatan secara komersial tanpa persetujuan tertulis dari pemilik hak cipta. Penggunaan lokal hanya diizinkan untuk keperluan pembelajaran mandiri (*personal learning*). Rincian lengkap tersedia pada dokumen [LICENSE](./LICENSE).

---

## 📬 Kontak & Konsultasi

Untuk konsultasi implementasi, kustomisasi modul klinik, atau penerbitan izin lisensi komersial resmi:
- **Email**: `ardhikayanuar58@gmail.com`
- **GitHub**: [@ardhikaxx](https://github.com/ardhikaxx)
- **Repositori**: [https://github.com/ardhikaxx/rme-klinik](https://github.com/ardhikaxx/rme-klinik)
