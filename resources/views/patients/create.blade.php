@extends('layouts.app')

@section('title', 'Pendaftaran Pasien Baru')
@section('page_title', 'Pendaftaran Pasien Baru')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('patients.index') }}" style="color: var(--rme-info);">Pasien</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pendaftaran Baru</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        {{-- Duplicate Alert Container --}}
        <div id="duplicateAlertBox" class="alert alert-warning d-none mb-4 shadow-sm" style="border-radius: 10px;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="fa-solid fa-triangle-exclamation fa-lg text-warning"></i>
                <h6 class="fw-bold mb-0">Peringatan: Potensi Duplikasi Pasien Terdeteksi!</h6>
            </div>
            <p class="small mb-2">Ditemukan pasien dengan data serupa pada sistem. Pastikan pasien ini bukan pasien lama untuk mencegah rekam medis ganda:</p>
            <ul id="duplicateList" class="small mb-0 ps-3"></ul>
        </div>

        <form method="POST" action="{{ route('patients.store') }}" id="patientForm">
            @csrf

            {{-- 1. Identitas Pokok Pasien --}}
            <div class="card-healthcare mb-4">
                <div class="card-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-address-card text-primary"></i> 1. Identitas Pokok Pasien
                    </span>
                    <span class="badge bg-light text-dark border">No. RM Otomatis: {{ $nextMrn }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">NIK (Nomor Induk Kependudukan)</label>
                            <input type="text" name="nik" id="nikInput" value="{{ old('nik') }}" class="form-control" maxlength="16" placeholder="16 Digit NIK KTP/KK">
                            <small class="text-muted">Kosongkan dan centang "Identitas Sementara" jika NIK belum tersedia.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Identitas</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="isVerifiedCheck" checked>
                                <label class="form-check-label" for="isVerifiedCheck">Identitas Pasien Terverifikasi (KTP/KK Asli)</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Nama Lengkap Pasien <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="nameInput" value="{{ old('name') }}" class="form-control" required placeholder="Nama lengkap sesuai kartu identitas">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tempat Lahir</label>
                            <input type="text" name="place_of_birth" value="{{ old('place_of_birth', 'Kota Malang') }}" class="form-control" placeholder="Tempat lahir">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" id="dobInput" value="{{ old('date_of_birth') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Golongan Darah</label>
                            <select name="blood_type" class="form-select">
                                <option value="tidak_tahu" {{ old('blood_type') === 'tidak_tahu' ? 'selected' : '' }}>Tidak Tahu</option>
                                <option value="A" {{ old('blood_type') === 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ old('blood_type') === 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ old('blood_type') === 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ old('blood_type') === 'O' ? 'selected' : '' }}>O</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phoneInput" value="{{ old('phone') }}" class="form-control" required placeholder="Contoh: 08123456789">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Email (Opsional)</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="email@contoh.com">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Status Pernikahan</label>
                            <select name="marital_status" class="form-select">
                                <option value="belum_kawin" {{ old('marital_status') === 'belum_kawin' ? 'selected' : '' }}>Belum Kawin</option>
                                <option value="kawin" {{ old('marital_status') === 'kawin' ? 'selected' : '' }}>Kawin</option>
                                <option value="cerai_hidup" {{ old('marital_status') === 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                                <option value="cerai_mati" {{ old('marital_status') === 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Pekerjaan</label>
                            <input type="text" name="occupation" value="{{ old('occupation') }}" class="form-control" placeholder="Contoh: Karyawan Swasta, Wiraswasta">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Agama</label>
                            <select name="religion" class="form-select">
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Pendidikan Terakhir</label>
                            <select name="education" class="form-select">
                                <option value="SD">SD</option>
                                <option value="SMP">SMP</option>
                                <option value="SMA/SMK" selected>SMA/SMK</option>
                                <option value="Diploma">Diploma (D3)</option>
                                <option value="Sarjana">Sarjana (S1)</option>
                                <option value="Magister/Doktor">Pascasarjana (S2/S3)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Alamat Domisili --}}
            <div class="card-healthcare mb-4">
                <div class="card-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-map-location-dot text-info"></i> 2. Alamat Tempat Tinggal
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Alamat Lengkap (Jalan, No. Rumah, Blok) <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" required placeholder="Contoh: Jl. Bendungan Sigura-gura No. 18">{{ old('address') }}</textarea>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-semibold">RT</label>
                            <input type="text" name="rt" value="{{ old('rt') }}" class="form-control" placeholder="01">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-semibold">RW</label>
                            <input type="text" name="rw" value="{{ old('rw') }}" class="form-control" placeholder="05">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kelurahan / Desa</label>
                            <input type="text" name="kelurahan" value="{{ old('kelurahan', 'Sumbersari') }}" class="form-control" placeholder="Kelurahan">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kecamatan</label>
                            <input type="text" name="kecamatan" value="{{ old('kecamatan', 'Lowokwaru') }}" class="form-control" placeholder="Kecamatan">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kota / Kabupaten</label>
                            <input type="text" name="city" value="{{ old('city', 'Kota Malang') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province', 'Jawa Timur') }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kode Pos</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', '65145') }}" class="form-control" placeholder="65145">
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Riwayat Alergi (Medical Alert) --}}
            <div class="card-healthcare mb-4">
                <div class="card-header">
                    <span class="d-flex align-items-center gap-2 text-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i> 3. Riwayat Alergi (Medical Alert)
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnAddAllergy">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Alergi
                    </button>
                </div>
                <div class="card-body">
                    <div id="allergyContainer">
                        <p class="text-muted small mb-0" id="emptyAllergyText">Tidak ada riwayat alergi yang ditambahkan. Klik tombol di atas jika pasien memiliki alergi.</p>
                    </div>
                </div>
            </div>

            {{-- 4. Kontak Darurat --}}
            <div class="card-healthcare mb-4">
                <div class="card-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-phone-volume text-success"></i> 4. Kontak Darurat (Keluarga)
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-success" id="btnAddContact">
                        <i class="fa-solid fa-plus me-1"></i> Tambah Kontak
                    </button>
                </div>
                <div class="card-body">
                    <div id="contactContainer">
                        <div class="row g-2 mb-2 contact-row">
                            <div class="col-12 col-md-4">
                                <input type="text" name="contacts[0][name]" class="form-control form-control-sm" placeholder="Nama Kerabat / Penanggung Jawab">
                            </div>
                            <div class="col-6 col-md-3">
                                <select name="contacts[0][relationship]" class="form-select form-select-sm">
                                    <option value="Suami/Istri">Suami/Istri</option>
                                    <option value="Orang Tua">Orang Tua</option>
                                    <option value="Anak">Anak</option>
                                    <option value="Saudara Kandung">Saudara Kandung</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-4">
                                <input type="text" name="contacts[0][phone]" class="form-control form-control-sm" placeholder="Nomor Telepon Darurat">
                            </div>
                            <div class="col-12 col-md-1 text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-remove-row" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="{{ route('patients.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar Pasien
                </a>
                <button type="submit" class="btn btn-rme-primary px-4 py-2 fw-semibold">
                    <i class="fa-solid fa-save me-1"></i> Simpan Data Pasien Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let allergyIndex = 0;
    let contactIndex = 1;

    // Add Allergy Row
    $('#btnAddAllergy').on('click', function () {
        $('#emptyAllergyText').hide();
        const html = `
            <div class="row g-2 mb-2 allergy-row p-2 border rounded bg-light">
                <div class="col-12 col-md-3">
                    <select name="allergies[${allergyIndex}][allergen_type]" class="form-select form-select-sm">
                        <option value="obat">Obat (Antibiotik, NSAID, dll)</option>
                        <option value="makanan">Makanan (Seafood, Kacang, dll)</option>
                        <option value="lingkungan">Lingkungan (Debu, Dingin)</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" name="allergies[${allergyIndex}][allergen_name]" class="form-control form-control-sm" placeholder="Nama Zat Alergen (Wajib)" required>
                </div>
                <div class="col-12 col-md-3">
                    <input type="text" name="allergies[${allergyIndex}][reaction]" class="form-control form-control-sm" placeholder="Reaksi (Gatal, Sesak, Syok)">
                </div>
                <div class="col-8 col-md-2">
                    <select name="allergies[${allergyIndex}][severity]" class="form-select form-select-sm">
                        <option value="ringan">Ringan</option>
                        <option value="sedang" selected>Sedang</option>
                        <option value="berat">Berat (Anafilaktik)</option>
                    </select>
                </div>
                <div class="col-4 col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-remove-row"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#allergyContainer').append(html);
        allergyIndex++;
    });

    // Add Contact Row
    $('#btnAddContact').on('click', function () {
        const html = `
            <div class="row g-2 mb-2 contact-row">
                <div class="col-12 col-md-4">
                    <input type="text" name="contacts[${contactIndex}][name]" class="form-control form-control-sm" placeholder="Nama Kerabat">
                </div>
                <div class="col-6 col-md-3">
                    <select name="contacts[${contactIndex}][relationship]" class="form-select form-select-sm">
                        <option value="Suami/Istri">Suami/Istri</option>
                        <option value="Orang Tua">Orang Tua</option>
                        <option value="Anak">Anak</option>
                        <option value="Saudara Kandung">Saudara Kandung</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="col-6 col-md-4">
                    <input type="text" name="contacts[${contactIndex}][phone]" class="form-control form-control-sm" placeholder="Nomor Telepon Darurat">
                </div>
                <div class="col-12 col-md-1 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger w-100 btn-remove-row"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        `;
        $('#contactContainer').append(html);
        contactIndex++;
    });

    // Remove row
    $(document).on('click', '.btn-remove-row', function () {
        $(this).closest('.allergy-row, .contact-row').remove();
    });

    // AJAX Realtime Duplicate Check
    function checkPatientDuplicate() {
        const name = $('#nameInput').val();
        const dob = $('#dobInput').val();
        const nik = $('#nikInput').val();
        const phone = $('#phoneInput').val();

        if (name.length < 3 && (!nik || nik.length < 16)) {
            $('#duplicateAlertBox').addClass('d-none');
            return;
        }

        $.ajax({
            url: '{{ route('patients.check_duplicate') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                name: name,
                date_of_birth: dob,
                nik: nik,
                phone: phone
            },
            success: function (res) {
                if (res.found && res.duplicates.length > 0) {
                    let listHtml = '';
                    res.duplicates.forEach(d => {
                        listHtml += `<li><strong>[${d.medical_record_number}] ${d.name}</strong> - NIK: ${d.nik || '-'} - Lahir: ${d.date_of_birth || '-'} (Alamat: ${d.address})</li>`;
                    });
                    $('#duplicateList').html(listHtml);
                    $('#duplicateAlertBox').removeClass('d-none');
                } else {
                    $('#duplicateAlertBox').addClass('d-none');
                }
            }
        });
    }

    $('#nikInput, #nameInput, #dobInput, #phoneInput').on('blur', checkPatientDuplicate);
</script>
@endpush
