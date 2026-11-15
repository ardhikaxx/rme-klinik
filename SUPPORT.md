# 💬 Dukungan & Bantuan Teknis (Support)

Terima kasih telah mempercayai dan menggunakan sistem informasi **RME KLINIK (Rekam Medis Elektronik Terpadu - Klinik Rawat Inap BeSt Medika)**.

Jika Anda membutuhkan bantuan teknis instalasi, konsultasi implementasi rekam medis elektronik, atau kustomisasi fitur untuk fasilitas pelayanan kesehatan Anda, silakan hubungi kanal resmi pengembang berikut:

---

## 📬 Kanal Komunikasi Resmi

* **Pemilik & Pengembang Sistem**: **Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**
* **Email Resmi**: `ardhikayanuar58@gmail.com`
* **GitHub Issues**: [https://github.com/ardhikaxx/rme-klinik/issues](https://github.com/ardhikaxx/rme-klinik/issues)
* **Repositori Resmi**: [https://github.com/ardhikaxx/rme-klinik](https://github.com/ardhikaxx/rme-klinik)

---

## 📚 Sumber Dokumentasi Mandiri

Sebelum mengajukan pertanyaan teknis, pastikan Anda telah mempelajari dokumen panduan yang tersedia:

1. [`README.md`](./README.md) — Gambaran arsitektur sistem, modul klinis, panduan instalasi, dan kredensial akun demo per role.
2. [`CHANGELOG.md`](./CHANGELOG.md) — Riwayat lengkap pembaruan fitur, perbaikan bug, dan rilis versi sistem.
3. [`SECURITY.md`](./SECURITY.md) — Kebijakan keamanan data medis dan prosedur pelaporan celah.
4. [`CONTRIBUTING.md`](./CONTRIBUTING.md) — Pedoman kontribusi kode, standar PSR-12, dan alur git.
5. [`LICENSE`](./LICENSE) — Ketentuan hukum kepemilikan dan batasan lisensi perangkat lunak.
6. [`CITATION.md`](./CITATION.md) — Format sitasi akademik karya ilmiah.

---

## 🧭 Sebelum Menghubungi Dukungan Teknis

Untuk mempermudah proses diagnosis kendala, pastikan Anda telah memeriksa poin-poin berikut:

1. **Kesesuaian Lingkungan Pengembangan**:
   - PHP **8.4+** dengan ekstensi aktif: `pdo_mysql`, `curl`, `mbstring`, `openssl`, `bcmath`.
   - Database MySQL 8.0+ atau MariaDB 10.4+ dengan konfigurasi `local_infile = 1` aktif (diperlukan untuk import dataset bulk).
   - Composer 2.x dan Node.js 18+.
2. **Pemeriksaan Log Error**:
   - Selalu periksa file log di `storage/logs/laravel.log` bila sistem menghasilkan status error 500.
3. **Penyertaan Informasi Kendala**:
   - Sertakan tangkapan layar antarmuka, salinan pesan error pada log, serta langkah-langkah reproduksi saat mengajukan pertanyaan.

---

## 🛠️ Layanan Kustomisasi & Implementasi Klinik

Bagi klinik, rumah sakit tipe D, atau fasilitas rawat inap yang membutuhkan solusi spesifik, layanan konsultasi dan kustomisasi resmi meliputi:

* **Penerbitan Lisensi Komersial**: Izin resmi penggunaan operasional sistem untuk klinik nyata (lihat [LICENSE](./LICENSE)).
* **Instalasi & Deployment Server**: Setup VPS, load balancing, SSL, database replication, dan konfigurasi backup otomatis.
* **Integrasi Bridging SATUSEHAT Kemenkes**: Pendaftaran OAuth2 client, pemetaan KFA, LOINC, SNOMED-CT, dan integrasi FHIR R4 ke server Kemenkes.
* **Bridging BPJS Kesehatan (PCare / VClaim)**: Integrasi bridging klaim dan antrean BPJS.
* **Kustomisasi Modul Khusus**: Penambahan poli spesialis, integrasi alat laboratorium otomatis (LIS), rekam medis rawat inap berkelanjutan, atau sistem antrean suara/display TV.

---

**Yanuar Ardhika Rahmadhani Ubaidillah (@ardhikaxx)**
Republic of Indonesia
