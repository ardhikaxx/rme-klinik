@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Pegawai Klinik')
@section('page_title', 'Manajemen Akun Pengguna & Pegawai')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Pengguna & Pegawai</li>
@endsection

@section('page_actions')
    <button type="button" class="btn btn-primary fw-bold" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-user-plus me-1"></i> Tambah Pengguna Baru
    </button>
@endsection

@section('content')
<div class="container-fluid p-0">

    <!-- Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="fas fa-users-cog me-2"></i>Daftar Pengguna & Tenaga Kesehatan
            </h5>
            <span class="small text-muted">Total: {{ $users->total() }} akun</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Lengkap</th>
                            <th>Username / Email</th>
                            <th>Peran Akses (Role)</th>
                            <th>Profesi / Bagian</th>
                            <th>No. SIP / STR</th>
                            <th>Status Akun</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark fs-6">{{ $u->name }}</div>
                                    <small class="text-muted">{{ $u->phone ?? '-' }}</small>
                                </td>
                                <td>
                                    <div><span class="badge bg-light text-primary border">{{ $u->username }}</span></div>
                                    <small class="text-muted">{{ $u->email }}</small>
                                </td>
                                <td>
                                    @foreach($u->roles as $r)
                                        <span class="badge bg-primary text-uppercase">{{ $r->display_name ?? $r->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <div class="text-capitalize">{{ $u->employee?->profession ?? 'Pegawai' }}</div>
                                    <small class="text-muted">{{ $u->employee?->department?->name ?? 'Klinik' }}</small>
                                </td>
                                <td>{{ $u->employee?->sip_str_number ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $u->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('settings.users.toggle', $u->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}" onclick="return confirm('Apakah Anda yakin ingin mengubah status akun {{ $u->name }}?')">
                                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-light text-muted border">Akun Anda</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Belum ada akun pengguna.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-3">
            <x-table-footer :paginator="$users" label="akun pengguna" />
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('settings.users.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" placeholder="dr. Ahmad Fauzi, Sp.PD" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Username Login <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="username" placeholder="ahmad.fauzi" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" placeholder="ahmad@klinik.id" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password Login <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" placeholder="Minimal 8 karakter" minlength="8" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hak Akses Role (RBAC) <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_id" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->id }}">{{ $r->display_name ?? $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Profesi Pegawai <span class="text-danger">*</span></label>
                            <select class="form-select" name="profession" required>
                                <option value="dokter">Dokter Umum / Spesialis</option>
                                <option value="perawat">Perawat</option>
                                <option value="bidan">Bidan</option>
                                <option value="apoteker">Apoteker / Tenaga Kefarmasian</option>
                                <option value="analis_lab">Analis Laboratorium</option>
                                <option value="admisi_kasir">Admisi / Kasir</option>
                                <option value="manajemen">Manajemen / Kepala Klinik</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor Telepon / WhatsApp</label>
                            <input type="text" class="form-control" name="phone" placeholder="08123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nomor SIP / STR (Untuk Nakes)</label>
                            <input type="text" class="form-control" name="sip_str_number" placeholder="SIP-2024-XXXX">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold">Buat Akun Pengguna</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
