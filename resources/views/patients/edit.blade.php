@extends('layouts.app')

@section('title', 'Edit Data Pasien')
@section('page_title', 'Perbarui Identitas Pasien')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('patients.index') }}" style="color: var(--rme-info);">Pasien</a></li>
    <li class="breadcrumb-item"><a href="{{ route('patients.show', $patient->id) }}" style="color: var(--rme-info);">{{ $patient->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <form method="POST" action="{{ route('patients.update', $patient->id) }}">
            @csrf
            @method('PUT')

            <div class="card-healthcare mb-4">
                <div class="card-header">
                    <span class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-address-card text-primary"></i> 1. Identitas Pokok Pasien
                    </span>
                    <span class="badge bg-primary fs-6">{{ $patient->medical_record_number }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">NIK (Nomor Induk Kependudukan)</label>
                            <input type="text" name="nik" value="{{ old('nik', $patient->nik) }}" class="form-control" maxlength="16" placeholder="16 Digit NIK KTP/KK">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Status Identitas</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_verified" value="1" id="isVerifiedCheck" {{ $patient->is_verified ? 'checked' : '' }}>
                                <label class="form-check-label" for="isVerifiedCheck">Identitas Pasien Terverifikasi (KTP/KK Asli)</label>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Nama Lengkap Pasien <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $patient->name) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="L" {{ old('gender', $patient->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('gender', $patient->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tempat Lahir</label>
                            <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $patient->place_of_birth) }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Tanggal Lahir <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Golongan Darah</label>
                            <select name="blood_type" class="form-select">
                                <option value="tidak_tahu" {{ old('blood_type', $patient->blood_type) === 'tidak_tahu' ? 'selected' : '' }}>Tidak Tahu</option>
                                <option value="A" {{ old('blood_type', $patient->blood_type) === 'A' ? 'selected' : '' }}>A</option>
                                <option value="B" {{ old('blood_type', $patient->blood_type) === 'B' ? 'selected' : '' }}>B</option>
                                <option value="AB" {{ old('blood_type', $patient->blood_type) === 'AB' ? 'selected' : '' }}>AB</option>
                                <option value="O" {{ old('blood_type', $patient->blood_type) === 'O' ? 'selected' : '' }}>O</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                            <input type="text" name="phone" value="{{ old('phone', $patient->phone) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email', $patient->email) }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Status Pernikahan</label>
                            <select name="marital_status" class="form-select">
                                <option value="belum_kawin" {{ old('marital_status', $patient->marital_status) === 'belum_kawin' ? 'selected' : '' }}>Belum Kawin</option>
                                <option value="kawin" {{ old('marital_status', $patient->marital_status) === 'kawin' ? 'selected' : '' }}>Kawin</option>
                                <option value="cerai_hidup" {{ old('marital_status', $patient->marital_status) === 'cerai_hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                                <option value="cerai_mati" {{ old('marital_status', $patient->marital_status) === 'cerai_mati' ? 'selected' : '' }}>Cerai Mati</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Pekerjaan</label>
                            <input type="text" name="occupation" value="{{ old('occupation', $patient->occupation) }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Agama</label>
                            <select name="religion" class="form-select">
                                <option value="Islam" {{ old('religion', $patient->religion) === 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Kristen" {{ old('religion', $patient->religion) === 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                <option value="Katolik" {{ old('religion', $patient->religion) === 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                <option value="Hindu" {{ old('religion', $patient->religion) === 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                <option value="Buddha" {{ old('religion', $patient->religion) === 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                <option value="Konghucu" {{ old('religion', $patient->religion) === 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Pendidikan Terakhir</label>
                            <select name="education" class="form-select">
                                <option value="SD" {{ old('education', $patient->education) === 'SD' ? 'selected' : '' }}>SD</option>
                                <option value="SMP" {{ old('education', $patient->education) === 'SMP' ? 'selected' : '' }}>SMP</option>
                                <option value="SMA/SMK" {{ old('education', $patient->education) === 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                                <option value="Diploma" {{ old('education', $patient->education) === 'Diploma' ? 'selected' : '' }}>Diploma (D3)</option>
                                <option value="Sarjana" {{ old('education', $patient->education) === 'Sarjana' ? 'selected' : '' }}>Sarjana (S1)</option>
                                <option value="Pascasarjana" {{ old('education', $patient->education) === 'Pascasarjana' ? 'selected' : '' }}>Pascasarjana</option>
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
                            <label class="form-label fw-semibold">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="address" class="form-control" rows="2" required>{{ old('address', $patient->address) }}</textarea>
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-semibold">RT</label>
                            <input type="text" name="rt" value="{{ old('rt', $patient->rt) }}" class="form-control">
                        </div>
                        <div class="col-6 col-md-2">
                            <label class="form-label fw-semibold">RW</label>
                            <input type="text" name="rw" value="{{ old('rw', $patient->rw) }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kelurahan</label>
                            <input type="text" name="kelurahan" value="{{ old('kelurahan', $patient->kelurahan) }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kecamatan</label>
                            <input type="text" name="kecamatan" value="{{ old('kecamatan', $patient->kecamatan) }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kota / Kabupaten</label>
                            <input type="text" name="city" value="{{ old('city', $patient->city) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Provinsi</label>
                            <input type="text" name="province" value="{{ old('province', $patient->province) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Kode Pos</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $patient->postal_code) }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="{{ route('patients.show', $patient->id) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-rme-primary px-4 py-2 fw-semibold">
                    <i class="fa-solid fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
