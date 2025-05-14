@extends('layouts.app')

@section('title', 'Simulasi Perubahan Anggaran')
@section('page-title', 'Simulasi Perubahan Anggaran')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Simulasi Perubahan Anggaran</h4>
        <form method="GET" action="" class="d-flex align-items-center flex-wrap gap-2">
            <label for="tahapan_id" class="me-2 mb-0">Filter Tahapan:</label>
            <select name="tahapan_id" id="tahapan_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                <option value="">Pilih Tahapan</option>
                @foreach($tahapans as $tahapan)
                    <option value="{{ $tahapan->id }}" {{ $tahapanId == $tahapan->id ? 'selected' : '' }}>{{ $tahapan->name }}</option>
                @endforeach
            </select>
            <label for="skpd" class="me-2 mb-0">SKPD:</label>
            <select name="skpd" id="skpd" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                @foreach($skpds as $skpd)
                    <option value="{{ $skpd->kode_skpd }}" {{ $skpdKode == $skpd->kode_skpd ? 'selected' : '' }}>{{ $skpd->kode_skpd }} - {{ $skpd->nama_skpd }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6 mb-3 mb-md-0">
                @if($rekap->isNotEmpty())
                <div class="mb-2">
                    <strong>SKPD:</strong> {{ $skpdTerpilih ? ($skpdTerpilih->kode_skpd . ' - ' . $skpdTerpilih->nama_skpd) : '-' }}<br>
                    <strong>Tahapan:</strong> {{ $tahapanTerpilih ? $tahapanTerpilih->name : '-' }}
                </div>
                <div class="table-responsive" style="max-height: 80vh; overflow-y: auto;">
                    <table class="table table-sm table-bordered table-striped table-hover align-middle" id="rekapTable">
                        <thead class="table-primary">
                            <tr>
                                <th style="width:40px">No</th>
                                <th style="width:120px">Kode Rekening</th>
                                <th style="max-width: 180px;">Nama Rekening</th>
                                <th style="width:120px">Total Pagu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekap as $i => $item)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $item->kode_rekening }}</td>
                                <td class="nama-rekening-compact" title="{{ $item->nama_rekening }}">{{ \Illuminate\Support\Str::limit($item->nama_rekening, 40) }}</td>
                                <td class="text-end">{{ number_format($item->total_pagu, 2, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-secondary">
                                <th colspan="3" class="text-end">Total Anggaran</th>
                                <th class="text-end">
                                    {{ number_format($rekap->sum('total_pagu'), 2, ',', '.') }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                    <p>Silakan pilih tahapan dan/atau SKPD untuk melihat rekap data anggaran.</p>
                @endif
            </div>
            <div class="col-12 col-md-6">
                <div class="h-100 d-flex flex-column justify-content-center align-items-center" style="min-height: 300px; max-height: 80vh;">
                    <div class="w-100 text-center mt-3">
                        <h5 class="text-muted">Rekap/Analisis Perubahan Anggaran</h5>
                        <p class="text-muted">(Kolom ini akan diisi fitur rekap/analisis selanjutnya)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<style>
    #rekapTable th, #rekapTable td {
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
        font-size: 0.92rem;
    }
    .nama-rekening-compact {
        font-size: 0.85rem;
        max-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            order: [[1, 'asc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
            }
        });
    });
</script>
@endpush
@endsection 