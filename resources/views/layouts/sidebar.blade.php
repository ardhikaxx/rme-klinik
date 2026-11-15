<aside id="sidebar-wrapper">
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}">
            <div class="brand-icon-box">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            <div class="brand-text-container">
                <span class="brand-title">RME KLINIK</span>
                <span class="brand-subtitle">Sistem Terpadu</span>
            </div>
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
        @if(auth()->user()->hasAnyPermission(['patients.view', 'visits.view']))
            <div class="sidebar-heading">Administrasi Pasien</div>
            <ul class="nav flex-column mb-3">
                @if(auth()->user()->hasPermission('patients.view'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('patients.*') ? 'active' : '' }}" href="{{ route('patients.index') }}">
                            <i class="fa-solid fa-user-injured"></i>
                            <span>Data Pasien (MPI)</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasPermission('visits.view'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('visits.*') ? 'active' : '' }}" href="{{ route('visits.index') }}">
                            <i class="fa-solid fa-calendar-check"></i>
                            <span>Daftar Kunjungan</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        {{-- KEPERAWATAN --}}
        @if(auth()->user()->hasPermission('nursing.assessment'))
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
        @if(auth()->user()->hasAnyPermission(['medical_records.view', 'medical_records.create', 'odontogram.manage', 'kia.manage']) || auth()->user()->hasAnyRole(['dokter', 'kepala_klinik']))
            <div class="sidebar-heading">Pelayanan Medis (RME)</div>
            <ul class="nav flex-column mb-3">
                @if(auth()->user()->hasAnyPermission(['medical_records.view', 'medical_records.create']) || auth()->user()->hasAnyRole(['dokter', 'kepala_klinik']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clinical.index', 'clinical.examination') ? 'active' : '' }}" href="{{ route('clinical.index') }}">
                            <i class="fa-solid fa-stethoscope"></i>
                            <span>Pemeriksaan Dokter</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasPermission('odontogram.manage') || auth()->user()->hasAnyRole(['dokter', 'kepala_klinik']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('odontogram.*') ? 'active' : '' }}" href="{{ route('odontogram.index') }}">
                            <i class="fa-solid fa-tooth"></i>
                            <span>Odontogram (Poli Gigi)</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasPermission('kia.manage'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kia.*') ? 'active' : '' }}" href="{{ route('kia.index') }}">
                            <i class="fa-solid fa-baby"></i>
                            <span>Poli KIA & Balita</span>
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        {{-- FARMASI --}}
        @if(auth()->user()->hasAnyPermission(['prescriptions.create', 'prescriptions.verify', 'pharmacy.dispense', 'pharmacy.stock.view', 'pharmacy.stock.manage', 'pharmacy.opname']))
            <div class="sidebar-heading">Instalasi Farmasi</div>
            <ul class="nav flex-column mb-3">
                @if(auth()->user()->hasAnyPermission(['prescriptions.create', 'prescriptions.verify', 'pharmacy.dispense']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pharmacy.prescriptions*') ? 'active' : '' }}" href="{{ route('pharmacy.prescriptions') }}">
                            <i class="fa-solid fa-prescription"></i>
                            <span>Resep Elektronik</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasPermission('pharmacy.stock.view'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pharmacy.medicines*') ? 'active' : '' }}" href="{{ route('pharmacy.medicines') }}">
                            <i class="fa-solid fa-pills"></i>
                            <span>Master Obat & Stok</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasPermission('pharmacy.stock.manage'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('pharmacy.receipts*') ? 'active' : '' }}" href="{{ route('pharmacy.receipts') }}">
                            <i class="fa-solid fa-truck-ramp-box"></i>
                            <span>Penerimaan Obat</span>
                        </a>
                    </li>
                @endif
                @if(auth()->user()->hasPermission('pharmacy.opname'))
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
        @if(auth()->user()->hasAnyPermission(['laboratory.order', 'laboratory.record', 'laboratory.verify']) || auth()->user()->hasRole('kepala_klinik'))
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
        @if(auth()->user()->hasPermission('billing.view'))
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
        @if(auth()->user()->hasAnyPermission(['documents.generate', 'documents.upload']) || auth()->user()->hasRole('kepala_klinik'))
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
        @if(auth()->user()->hasAnyPermission(['reports.financial', 'reports.clinical', 'reports.operational']) || auth()->user()->hasRole('kepala_klinik'))
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

    {{-- Floating Sidebar Footer User Status --}}
    <div class="sidebar-footer">
        <div class="sidebar-user-card">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2 overflow-hidden">
                    <div class="user-avatar-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="user-info-text text-truncate">
                        <div class="user-name fw-bold text-white small text-truncate" style="line-height: 1.2;">{{ auth()->user()->name }}</div>
                        <div class="user-role-badge">
                            <span class="status-pulse-dot"></span>
                            <span class="text-truncate">{{ auth()->user()->primary_role_name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
<div class="sidebar-backdrop"></div>
