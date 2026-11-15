<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}">
            <i class="fa-solid fa-hospital text-info"></i>
            <span class="brand-text">RME KLINIK</span>
        </a>
    </div>

    <div class="sidebar-menu">
        <div class="sidebar-heading">Menu Utama</div>
        <ul class="nav flex-column mb-3">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <i class="fa-solid fa-gauge-high"></i>
                    <span>Dashboard</span>
                </a>
            </li>
        </ul>

        {{-- PASIEN & KUNJUNGAN --}}
        @if(auth()->user()->hasAnyRole(['admisi_kasir', 'kepala_klinik', 'dokter', 'perawat_bidan']))
            <div class="sidebar-heading">Administrasi Pasien</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}">
                        <i class="fa-solid fa-user-injured"></i>
                        <span>Data Pasien (MPI)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('visits.*') ? 'active' : '' }}" href="{{ route('visits.index') }}">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Daftar Kunjungan</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- KEPERAWATAN --}}
        @if(auth()->user()->hasAnyRole(['perawat_bidan', 'kepala_klinik']))
            <div class="sidebar-heading">Pelayanan Keperawatan</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('nursing.*') ? 'active' : '' }}" href="{{ route('nursing.index') }}">
                        <i class="fa-solid fa-user-nurse"></i>
                        <span>Pengkajian Awal</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- DOKTER & REKAM MEDIS --}}
        @if(auth()->user()->hasAnyRole(['dokter', 'kepala_klinik']))
            <div class="sidebar-heading">Pelayanan Medis (RME)</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clinical.index', 'clinical.examination') ? 'active' : '' }}" href="{{ route('clinical.index') }}">
                        <i class="fa-solid fa-stethoscope"></i>
                        <span>Pemeriksaan Dokter</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('odontogram.*') ? 'active' : '' }}" href="{{ route('odontogram.index') }}">
                        <i class="fa-solid fa-tooth"></i>
                        <span>Odontogram (Poli Gigi)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('kia.*') ? 'active' : '' }}" href="{{ route('kia.index') }}">
                        <i class="fa-solid fa-baby"></i>
                        <span>Poli KIA & Balita</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- FARMASI --}}
        @if(auth()->user()->hasAnyRole(['farmasi', 'kepala_klinik', 'dokter']))
            <div class="sidebar-heading">Instalasi Farmasi</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('pharmacy.prescriptions*') ? 'active' : '' }}" href="{{ route('pharmacy.prescriptions') }}">
                        <i class="fa-solid fa-prescription"></i>
                        <span>Resep Elektronik</span>
                    </a>
                </li>
                @if(auth()->user()->hasAnyRole(['farmasi', 'kepala_klinik']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pharmacy.medicines*') ? 'active' : '' }}" href="{{ route('pharmacy.medicines') }}">
                            <i class="fa-solid fa-pills"></i>
                            <span>Master Obat & Stok</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pharmacy.receipts*') ? 'active' : '' }}" href="{{ route('pharmacy.receipts') }}">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>Penerimaan Obat</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pharmacy.opnames*') ? 'active' : '' }}" href="{{ route('pharmacy.opnames') }}">
                            <i class="fa-solid fa-clipboard-check"></i>
                            <span>Stock Opname</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        {{-- LABORATORIUM --}}
        @if(auth()->user()->hasAnyRole(['dokter', 'perawat_bidan', 'kepala_klinik', 'admisi_kasir']))
            <div class="sidebar-heading">Laboratorium</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('laboratory.*') ? 'active' : '' }}" href="{{ route('laboratory.index') }}">
                        <i class="fa-solid fa-vial-virus"></i>
                        <span>Pemeriksaan Lab</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- BILLING & KASIR --}}
        @if(auth()->user()->hasAnyRole(['admisi_kasir', 'kepala_klinik']))
            <div class="sidebar-heading">Billing & Kasir</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('billing.*') ? 'active' : '' }}" href="{{ route('billing.index') }}">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Tagihan & Pembayaran</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- SURAT & DOKUMEN --}}
        @if(auth()->user()->hasAnyRole(['dokter', 'admisi_kasir', 'kepala_klinik']))
            <div class="sidebar-heading">Dokumen Medis</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('documents.*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
                        <i class="fa-solid fa-file-medical"></i>
                        <span>Surat Keterangan & Rujukan</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- LAPORAN --}}
        @if(auth()->user()->hasAnyRole(['kepala_klinik', 'admisi_kasir', 'dokter', 'farmasi']))
            <div class="sidebar-heading">Laporan & Analitik</div>
            <ul class="nav flex-column mb-3">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Laporan Terpadu</span>
                    </a>
                </li>
            </ul>
        @endif

        {{-- MANAJEMEN & AUDIT --}}
        @if(auth()->user()->hasRole('kepala_klinik'))
            <div class="sidebar-heading">Pengaturan & Keamanan</div>
            <ul class="nav flex-column mb-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.tariffs*') ? 'active' : '' }}" href="{{ route('settings.tariffs') }}">
                        <i class="fa-solid fa-tags"></i>
                        <span>Master Tarif & Jasa Medis</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.schedules*') ? 'active' : '' }}" href="{{ route('settings.schedules') }}">
                        <i class="fa-solid fa-clock"></i>
                        <span>Jadwal Dokter</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('settings.users*') ? 'active' : '' }}" href="{{ route('settings.users') }}">
                        <i class="fa-solid fa-users-cog"></i>
                        <span>Pengguna & Pegawai</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Audit Trail Keamanan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('fhir.*') ? 'active' : '' }}" href="{{ route('fhir.index') }}">
                        <i class="fa-solid fa-network-wired"></i>
                        <span>Kesiapan SATUSEHAT</span>
                    </a>
                </li>
            </ul>
        @endif
    </div>
</aside>
<div class="sidebar-backdrop"></div>
