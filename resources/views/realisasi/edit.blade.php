@extends('layouts.app')

@section('title', 'Edit Realisasi')
@section('page-title', 'Edit Realisasi')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header">
        <h4>Form Edit Realisasi</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('realisasi.update', $realisasi) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kode_opd" class="form-label">Kode OPD</label>
                        <input type="text" class="form-control @error('kode_opd') is-invalid @enderror" 
                               id="kode_opd" name="kode_opd" value="{{ old('kode_opd', $realisasi->kode_opd) }}" required>
                        @error('kode_opd')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode</label>
                        <input type="date" class="form-control @error('periode') is-invalid @enderror" 
                               id="periode" name="periode" value="{{ old('periode', $realisasi->periode->format('Y-m-d')) }}" required>
                        @error('periode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="kode_rekening" class="form-label">Kode Rekening</label>
                        <input type="text" class="form-control @error('kode_rekening') is-invalid @enderror" 
                               id="kode_rekening" name="kode_rekening" value="{{ old('kode_rekening', $realisasi->kode_rekening) }}" required>
                        @error('kode_rekening')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="uraian" class="form-label">Uraian</label>
                        <input type="text" class="form-control @error('uraian') is-invalid @enderror" 
                               id="uraian" name="uraian" value="{{ old('uraian', $realisasi->uraian) }}" required>
                        @error('uraian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="anggaran" class="form-label">Anggaran</label>
                        <input type="number" step="0.01" class="form-control @error('anggaran') is-invalid @enderror" 
                               id="anggaran" name="anggaran" value="{{ old('anggaran', $realisasi->anggaran) }}" required>
                        @error('anggaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="realisasi" class="form-label">Realisasi</label>
                        <input type="number" step="0.01" class="form-control @error('realisasi') is-invalid @enderror" 
                               id="realisasi" name="realisasi" value="{{ old('realisasi', $realisasi->realisasi) }}" required>
                        @error('realisasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="persentase" class="form-label">Persentase</label>
                        <input type="number" step="0.01" class="form-control @error('persentase') is-invalid @enderror" 
                               id="persentase" name="persentase" value="{{ old('persentase', $realisasi->persentase) }}" required>
                        @error('persentase')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="realisasi_ly" class="form-label">Realisasi LY</label>
                        <input type="number" step="0.01" class="form-control @error('realisasi_ly') is-invalid @enderror" 
                               id="realisasi_ly" name="realisasi_ly" value="{{ old('realisasi_ly', $realisasi->realisasi_ly) }}" required>
                        @error('realisasi_ly')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('realisasi.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection 