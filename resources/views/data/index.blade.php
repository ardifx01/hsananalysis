@extends('layouts.app')

@section('title', 'Tools Data Anggaran')
@section('page-title', 'Tools Data Anggaran')

@section('content')

<div class="container">
    <div class="row">
        <!-- Status Data -->
        <div class="col-md-4">
            <div class="card shadow-lg border-0 mb-4" data-aos="fade-up">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-bar-chart-fill text-primary"></i> Status Data</h5>
                    <p class="text-muted">Jumlah data dalam sistem</p>
                    <div class="mb-2">
                        <h6 class="text-success"><i class="bi bi-database-fill"></i> Total Data Original: <span class="fw-bold">{{ $totalOriginal }}</span></h6>
                        <h6 class="text-warning"><i class="bi bi-pencil-square"></i> Total Data Revisi: <span class="fw-bold">{{ $totalRevisi }}</span></h6>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Data -->
        <div class="col-md-8">
            <div class="card shadow-lg border-0" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-cloud-upload-fill text-primary"></i> Upload Data</h5>
                    <p class="text-muted">Pilih file Excel untuk mengunggah data anggaran.</p>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-x-circle-fill"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-folder-fill"></i> Pilih Tipe Data:</label>
                            <select name="tipe_data" class="form-select">
                                <option value="original">Original</option>
                                <option value="revisi">Revisi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><i class="bi bi-file-earmark-spreadsheet-fill"></i> Pilih File Excel:</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-cloud-upload-fill"></i> Upload Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Manajemen Data -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow border-0" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-trash-fill text-danger"></i> Hapus Data Original</h5>
                    <p class="text-muted">Menghapus seluruh data original dari sistem.</p>
                    <form action="{{ route('hapus.original') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data original?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-x-circle-fill"></i> Hapus Data Original
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow border-0" data-aos="fade-up" data-aos-delay="300">
                <div class="card-body text-center">
                    <h5 class="card-title"><i class="bi bi-trash-fill text-warning"></i> Hapus Data Revisi</h5>
                    <p class="text-muted">Menghapus seluruh data revisi dari sistem.</p>
                    <form action="{{ route('hapus.revisi') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data revisi?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-x-circle-fill"></i> Hapus Data Revisi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigasi -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="{{ route('compare') }}" class="btn btn-secondary">
                <i class="bi bi-bar-chart-fill"></i> Lihat Perbandingan Data
            </a>
        </div>
    </div>

</div>

@endsection
