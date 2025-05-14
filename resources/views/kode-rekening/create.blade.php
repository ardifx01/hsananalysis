@extends('layouts.app')

@section('title', 'Tambah Kode Rekening')
@section('page-title', 'Tambah Kode Rekening')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Tambah Kode Rekening</h4>
        <a href="{{ route('kode-rekening.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
    
    <div class="card-body">
        <form action="{{ route('kode-rekening.store') }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="kode_rekening" class="form-label">Kode Rekening</label>
                <input type="text" class="form-control @error('kode_rekening') is-invalid @enderror" id="kode_rekening" name="kode_rekening" value="{{ old('kode_rekening') }}" required>
                @error('kode_rekening')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <input type="text" class="form-control @error('uraian') is-invalid @enderror" id="uraian" name="uraian" value="{{ old('uraian') }}" required>
                @error('uraian')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection 