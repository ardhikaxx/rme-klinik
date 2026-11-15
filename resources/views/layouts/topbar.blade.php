<header class="topbar">
    <div class="d-flex align-items-center gap-3">
        <button type="button" class="nav-toggle-btn" title="Toggle Navigasi">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <span class="fw-bold text-dark d-none d-md-inline">Klinik Pratama Sehat Mandiri</span>
            <span class="badge bg-light text-secondary border ms-2 d-none d-lg-inline">
                <i class="fa-regular fa-clock me-1 text-primary"></i>
                {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }} &bull; WIB
            </span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        {{-- User dropdown --}}
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar me-2">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="d-none d-md-block text-start me-1">
                    <div class="fw-bold fs-6 lh-1">{{ auth()->user()->name }}</div>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        {{ auth()->user()->primary_role_name }}
                    </small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userMenuDropdown" style="min-width: 220px; border-radius: 10px;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold">{{ auth()->user()->name }}</div>
                    <div class="text-muted small">&#64;{{ auth()->user()->username }}</div>
                    <span class="badge bg-info text-dark mt-1">{{ auth()->user()->primary_role_name }}</span>
                </li>
                <li>
                    <button type="button" class="dropdown-item py-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fa-solid fa-key text-muted me-2"></i> Ganti Password
                    </button>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <button type="button" class="dropdown-item py-2 text-danger" onclick="confirmAction({title: 'Konfirmasi Keluar', text: 'Apakah Anda yakin ingin keluar dari sistem?', confirmButtonText: 'Ya, Keluar', onConfirm: () => document.getElementById('logoutForm').submit()})">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>

{{-- Modal Ganti Password --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="changePasswordModalLabel">
                    <i class="fa-solid fa-key text-info me-2"></i> Ganti Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('password.change') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Saat Ini</label>
                        <input type="password" name="current_password" class="form-control" required placeholder="Masukkan password saat ini">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" required minlength="8" placeholder="Minimal 8 karakter">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-rme-primary">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
