<header class="topbar">
    <div class="d-flex align-items-center gap-2 gap-md-3 min-w-0">
        <button type="button" class="nav-toggle-btn flex-shrink-0" title="Toggle Navigasi">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
        <div class="d-flex align-items-center gap-2 min-w-0">
            <h6 class="fw-bold text-dark mb-0 topbar-page-title text-truncate" title="@yield('page_title', 'RME KLINIK Terpadu')">
                @yield('page_title', 'RME KLINIK Terpadu')
            </h6>
        </div>
    </div>

    {{-- Official Government & Healthcare Platform Badges --}}
    <div class="topbar-official-badges d-none d-md-flex align-items-center" title="Standar Pelayanan Terintegrasi Resmi">
        <div class="official-badge-item" title="Kementerian Kesehatan Republik Indonesia">
            <img src="{{ asset('images/logo-kemenkes.webp') }}" alt="Logo Kemenkes RI" class="official-logo-img kemenkes-logo" loading="eager" draggable="false">
        </div>
        <span class="official-badge-divider"></span>
        <div class="official-badge-item" title="Terintegrasi Platform SATUSEHAT Kemenkes RI">
            <img src="{{ asset('images/logo-satusehat.webp') }}" alt="Logo SATUSEHAT" class="official-logo-img satusehat-logo" loading="eager" draggable="false">
        </div>
        <span class="official-badge-divider"></span>
        <div class="official-badge-item" title="Badan Penyelenggara Jaminan Sosial Kesehatan (BPJS Kesehatan)">
            <img src="{{ asset('images/logo-bpjs-kesehatan.webp') }}" alt="Logo BPJS Kesehatan" class="official-logo-img bpjs-logo" loading="eager" draggable="false">
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 gap-md-3">
        {{-- User dropdown --}}
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle user-pill" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar me-2">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="d-none d-md-block text-start me-1">
                    <div class="fw-bold fs-6 lh-1 text-dark">{{ auth()->user()->name }}</div>
                    <small class="text-muted" style="font-size: 0.74rem;">
                        {{ auth()->user()->primary_role_name }}
                    </small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" aria-labelledby="userMenuDropdown" style="min-width: 240px; border-radius: 14px; padding: 0.5rem;">
                <li class="px-3 py-2 border-bottom">
                    <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                    <div class="text-muted small">&#64;{{ auth()->user()->username }}</div>
                    <span class="badge bg-info text-dark mt-1" style="font-size: 0.75rem;">{{ auth()->user()->primary_role_name }}</span>
                </li>
                <li>
                    <button type="button" class="dropdown-item py-2 rounded-2 mt-1" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fa-solid fa-key text-muted me-2"></i> Ganti Password
                    </button>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                        @csrf
                        <button type="button" class="dropdown-item py-2 rounded-2 text-danger" onclick="confirmAction({title: 'Konfirmasi Keluar', text: 'Apakah Anda yakin ingin keluar dari sistem?', confirmButtonText: 'Ya, Keluar', onConfirm: () => document.getElementById('logoutForm').submit()})">
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
