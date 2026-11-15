@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard Operasional')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('content')
<div class="card-healthcare p-5 text-center">
    <div class="mb-3">
        <i class="fa-solid fa-hospital-user fa-3x text-info"></i>
    </div>
    <h4 class="fw-bold text-dark">Selamat Datang di RME KLINIK</h4>
    <p class="text-muted">Sistem Informasi Rekam Medis Elektronik dan Manajemen Operasional Klinik Terpadu.</p>
    <div class="mt-3">
        <a href="{{ route('patients.index') }}" class="btn btn-rme-primary me-2">Data Pasien</a>
        <a href="{{ route('visits.index') }}" class="btn btn-rme-secondary">Daftar Kunjungan</a>
    </div>
</div>
@endsection
