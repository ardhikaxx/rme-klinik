@extends('layouts.auth')

@section('title', 'Masuk ke Sistem')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <div class="d-inline-flex align-items-center justify-content-center p-3 rounded-circle mb-3" style="background-color: #e8f5fb;">
            <i class="fa-solid fa-hospital-user fa-2x" style="color: #2EACF0;"></i>
        </div>
        <h4 class="fw-bold text-dark mb-1">RME KLINIK</h4>
        <p class="text-muted small mb-0">Sistem Rekam Medis Elektronik & Manajemen Operasional Terpadu</p>
    </div>

    <div class="p-4">
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small border-0 mb-3" style="border-radius: 8px;">
                <i class="fa-solid fa-circle-exclamation me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('status'))
            <div class="alert alert-success py-2 px-3 small border-0 mb-3" style="border-radius: 8px;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold text-dark small">Username atau Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="login" value="{{ old('login') }}" class="form-control border-start-0 ps-0" placeholder="Masukkan username atau email" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label class="form-label fw-semibold text-dark small mb-0">Password</label>
                </div>
                <div class="input-group mt-1">
                    <span class="input-group-text bg-light text-muted border-end-0">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="passwordInput" class="form-control border-start-0 ps-0" placeholder="Masukkan password" required>
                    <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePasswordVisibility()">
                        <i class="fa-solid fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                    <label class="form-check-label small text-muted" for="rememberMe">
                        Ingat Saya
                    </label>
                </div>
                <small class="text-muted">WIB (Asia/Jakarta)</small>
            </div>

            <button type="submit" class="btn btn-rme-primary w-100 py-2 fw-semibold">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk ke Dashboard
            </button>
        </form>

        {{-- Quick Demo Credentials Helper for Verification --}}
        <div class="mt-4 pt-3 border-top text-center">
            <span class="badge bg-light text-secondary mb-2">Akun Demo Cepat (Klik untuk mengisi):</span>
            <div class="d-flex flex-wrap gap-1 justify-content-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCredential('admisi', 'password123')">Admisi/Kasir</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCredential('perawat', 'password123')">Perawat</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCredential('dokter', 'password123')">Dokter</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCredential('farmasi', 'password123')">Farmasi</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="fillCredential('kepala', 'password123')">Kepala Klinik</button>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function fillCredential(user, pass) {
        document.querySelector('input[name="login"]').value = user;
        document.querySelector('input[name="password"]').value = pass;
    }
</script>
@endsection
