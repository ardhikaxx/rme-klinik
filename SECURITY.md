# 🛡️ Kebijakan Keamanan (Security Policy)

Keamanan informasi, integritas data klinis, serta kerahasiaan rekam medis pasien adalah prioritas tertinggi dalam perancangan dan operasional sistem **RME KLINIK (Rekam Medis Elektronik Terpadu - Klinik Rawat Inap BeSt Medika)**.

Aplikasi ini dibangun dengan mengacu pada standar pengembangan perangkat lunak aman (*Secure Software Development Lifecycle*), **Peraturan Menteri Kesehatan (Permenkes) No. 24 Tahun 2022 tentang Rekam Medis**, serta **Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022)**.

---

## 📦 Versi yang Didukung

Pembaruan keamanan dan perbaikan kerentanan aktif diberikan untuk versi berikut:

| Versi | Status Pemeliharaan Keamanan |
| :--- | :--- |
| **v1.1.x** | ✅ Didukung Penuh (*Active Security Maintenance*) |
| **v1.0.x** | ⚠️ Pembaruan Kritis (*Critical Fixes Only*) |
| < 1.0.0 | ❌ Tidak Didukung |

---

## 🔒 Lapisan Keamanan Bawaan (Built-in Security Architecture)

1. **Autentikasi Kuat & Otorisasi RBAC**:
   - Autentikasi berbasis session terlindungi dengan regenerasi ID sesi otomatis setelah login untuk mencegah serangan *Session Fixation*.
   - Kontrol akses granular berbasis peran (*Role-Based Access Control*) menggunakan Spatie Permission untuk 5 role fungsional: **Kepala Klinik, Petugas Admisi & Kasir, Perawat & Bidan, Dokter, dan Tenaga Farmasi**.
   - Setiap endpoint controller diverifikasi di sisi server (backend) menggunakan Policy dan Gate — request tidak berizin langsung ditolak dengan status HTTP 403 Forbidden.
2. **Kerahasiaan Data Medis Pasien (UU PDP & Permenkes No. 24/2022)**:
   - Rekam medis elektronik (SOAP, odontogram gigi, catatan KIA) dikunci untuk pencegahan perubahan data sepihak setelah pemeriksaan diselesaikan (*finalized*).
   - Akses data rekam medis dibatasi hanya kepada tenaga kesehatan yang memiliki hak rawat pasien bersangkutan.
3. **Proteksi Kerentanan Web (OWASP Top 10 Compliance)**:
   - **Cross-Site Request Forgery (CSRF)**: Seluruh formulir POST/PUT/DELETE dilindungi token CSRF aktif.
   - **SQL Injection**: Seluruh kueri database dieksekusi melalui Eloquent ORM dan PDO Prepared Statements dengan parameter binding ketat.
   - **Cross-Site Scripting (XSS)**: Seluruh variabel Blade diemisi melalui mekanisme sanitasi bawaan (`{{ $data }}`), mencegah injeksi skrip berbahaya ke peramban.
4. **Keamanan Transaksi Farmasi & Kasir**:
   - Pengurangan stok obat menggunakan transaksi atomik database dengan *pessimistic row-locking* (`lockForUpdate`) untuk mencegah kondisi balapan (*race condition*) pada penjualan bersamaan.
   - Validasi saldo dan kembalian kasir dihitung ulang sepenuhnya di sisi backend.
5. **Jejak Audit Komprehensif (Audit Trail)**:
   - Setiap tindakan penting (login, logout, registrasi kunjungan, pengkajian perawat, input SOAP dokter, mutasi obat, penerimaan pembayaran, dan pembatalan transaksi) dicatat secara permanen pada tabel `audit_logs` lengkap dengan ID pengguna, nama, waktu presisi, alamat IP, dan user agent.

---

## 🚨 Pelaporan Celah Keamanan (Responsible Disclosure)

Bila Anda menemukan kerentanan atau potensi celah keamanan (*security vulnerability*) dalam sistem ini:

> [!CAUTION]
> **JANGAN** membuat laporan atau isu terbuka (*public issue*) di GitHub guna melindungi integritas dan kerahasiaan data pengguna sistem.

Silakan kirimkan laporan Anda secara privat melalui kanal resmi berikut:

* **Email Penanggung Jawab Keamanan**: `ardhikayanuar58@gmail.com`
* **Subjek Email**: `[SECURITY VULNERABILITY] - RME KLINIK`

### Informasi yang Diharapkan dalam Laporan:
1. Deskripsi lengkap mengenai tipe dan mekanisme kerentanan yang ditemukan.
2. Langkah-langkah detail atau naskah pembuktian konsep (*Proof of Concept / PoC*) untuk mereproduksi celah.
3. Analisis potensi dampak klinis, administratif, atau finansial.

Pengembang berkomitmen untuk meninjau dan merespons laporan Anda dalam waktu **1x24 jam** serta menerbitkan perbaikan (*security patch*) secepat mungkin.

---

**Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**
