# 🤝 Panduan Kontribusi (Contributing Guidelines)

Terima kasih atas minat Anda pada proyek **RME KLINIK (Rekam Medis Elektronik Terpadu - Klinik Rawat Inap BeSt Medika)**!

Sistem informasi ini dirancang dan dikembangkan secara independen oleh **Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**. Mengingat proyek ini berlisensi **Proprietary (All Rights Reserved)**, segala bentuk kontribusi teknis dan telaah kode tunduk pada ketentuan berikut.

---

## 📌 Prinsip & Kebijakan Hak Cipta

1. **Kepemilikan Penuh**: Basis kode, skema database medis, modul klinis, dan arsitektur sistem adalah hak milik eksklusif **Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**.
2. **Larangan Plagiarisme**: Dilarang menghapus atau memodifikasi watermark, metadata hak cipta, dan *author attribution* yang tertanam dalam aplikasi (lihat [LICENSE](./LICENSE)).
3. Setiap kontribusi (*pull request*) yang diterima akan menjadi bagian dari repositori resmi dan hak kepemilikannya melekat pada pemilik proyek.
4. Kontributor menjamin bahwa kode yang diserahkan adalah karya asli dan tidak melanggar hak cipta pihak ketiga mana pun.

---

## 🛠️ Standar Kualitas & Arsitektur Kode

* **Runtime & Framework**: PHP 8.4+ dan Laravel 12.x/13.x.
* **Format Gaya Kode (Formatting)**: Wajib mematuhi standar **PSR-12** dan konvensi Laravel Pint. Jalankan formatter sebelum commit:
  ```bash
  vendor/bin/pint --dirty --format agent
  ```
* **Pola Arsitektur**:
  - Terapkan pemisahan tanggung jawab (*Separation of Concerns*): `Controller` → `FormRequest` → `Service` → `Model`.
  - Logika bisnis kompleks (kalkulasi billing, mutasi stok FEFO, pembagian antrean, audit trail) wajib berada pada Service Layer (`app/Services/`), bukan di controller maupun Blade view.
  - Validasi formulir menggunakan FormRequest mandiri dengan pesan berbahasa Indonesia.
* **Keamanan & Otorisasi RBAC**:
  - Setiap endpoint dilindungi middleware `auth` dan pengecekan permission Spatie / Policy sesuai peran (Kepala Klinik, Admisi Kasir, Perawat Bidan, Dokter, Farmasi).
  - Data medis SOAP, odontogram, dan KIA hanya dapat diubah oleh tenaga medis yang berwenang.
  - Kasir dan pembayaran hanya dapat diproses oleh petugas kasir terdaftar.
  - Penyesuaian stok obat wajib atomik dengan row-locking (`lockForUpdate`).
* **Bahasa & Lokalisasi**: Seluruh antarmuka, terminologi medis (ICD-10, ICD-9-CM), label form, dan pesan sistem menggunakan **Bahasa Indonesia**.
* **Pengujian Mandiri (Testing)**:
  - Setiap penambahan fitur atau perbaikan bug wajib disertai pengujian Pest (`php artisan make:test --pest {NameTest}`).
  - Seluruh test suite wajib lolos 100%:
    ```bash
    php artisan test --compact
    ```

---

## 🌿 Alur Kerja Git (Git Workflow)

1. **Clone Repositori**:
   ```bash
   git clone https://github.com/ardhikaxx/rme-klinik.git
   cd rme-klinik
   ```

2. **Buat Branch Kerja Baru**:
   * `feat/nama-fitur` (untuk implementasi fitur klinis baru)
   * `fix/nama-bug` (untuk perbaikan kendala operasional)
   * `docs/nama-dokumen` (untuk pembaruan dokumentasi)

   ```bash
   git checkout -b feat/integrasi-modul-baru
   ```

3. **Format Pesan Commit (Conventional Commits)**:
   Gunakan struktur [Conventional Commits](https://www.conventionalcommits.org/) dengan deskripsi yang jelas dalam **Bahasa Indonesia**:
   * `feat: penambahan pencatatan triase gawat darurat`
   * `fix: perbaikan kalkulasi sisa piutang tagihan pasien`
   * `docs: pembaruan panduan konfigurasi fhir kemenkes`
   * `style: penyesuaian jarak padding pada tabel rawat inap`
   * `test: penambahan feature test verifikasi resep racikan`

4. **Pengajuan Pull Request**:
   * Setiap Pull Request wajib memuat deskripsi jelas mengenai perubahan yang dilakukan, alasan klinis/teknis, dan bukti bahwa seluruh pengujian Pest lulus tanpa error.

---

**Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**
