@extends('layouts.app')

@section('title', 'Detail Kode Rekening')
@section('page-title', 'Detail Kode Rekening')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Detail Kode Rekening</h4>
        <div>
            <a href="{{ route('kode-rekening.edit', $kodeRekening->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('kode-rekening.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
    
    <div class="card-body">
        <table class="table table-striped">
            <tr>
                <th style="width: 200px;">ID</th>
                <td>{{ $kodeRekening->id }}</td>
            </tr>
            <tr>
                <th>Kode Rekening</th>
                <td>{{ $kodeRekening->kode_rekening }}</td>
            </tr>
            <tr>
                <th>Uraian</th>
                <td>{{ $kodeRekening->uraian }}</td>
            </tr>
            <tr>
                <th>Tanggal Dibuat</th>
                <td>{{ $kodeRekening->created_at->format('d-m-Y H:i:s') }}</td>
            </tr>
            <tr>
                <th>Tanggal Diperbarui</th>
                <td>{{ $kodeRekening->updated_at->format('d-m-Y H:i:s') }}</td>
            </tr>
        </table>
        
        <div class="mt-3">
            <form action="{{ route('kode-rekening.destroy', $kodeRekening->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Hapus Kode Rekening
                </button>
            </form>
        </div>
    </div>
</div>
@endsection 