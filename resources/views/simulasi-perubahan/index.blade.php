@extends('layouts.app')

@section('title', 'Simulasi Perubahan Anggaran')
@section('page-title', 'Simulasi Perubahan Anggaran')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Simulasi Perubahan Anggaran</h4>
        <form method="GET" action="" class="flex-wrap gap-2 d-flex align-items-center">
            <label for="tahapan_id" class="mb-0 me-2">Filter Tahapan:</label>
            <select name="tahapan_id" id="tahapan_id" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                <option value="">Pilih Tahapan</option>
                @foreach($tahapans as $tahapan)
                    <option value="{{ $tahapan->id }}" {{ $tahapanId == $tahapan->id ? 'selected' : '' }}>{{ $tahapan->name }}</option>
                @endforeach
            </select>
            <label for="skpd" class="mb-0 me-2">SKPD:</label>
            <select name="skpd" id="skpd" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                @foreach($skpds as $skpd)
                    <option value="{{ $skpd->kode_skpd }}" {{ $skpdKode == $skpd->kode_skpd ? 'selected' : '' }}>{{ $skpd->kode_skpd }} - {{ $skpd->nama_skpd }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="btn-group">
                <a href="{{ route('simulasi.belanja-opd') }}" class="btn btn-info btn-sm">
                    <i class="bi bi-list-ul"></i> Lihat total simulasi seluruh OPD
                </a>
                <a href="{{ route('simulasi.rekapitulasi-struktur-opd') }}" class="btn btn-success btn-sm">
                    <i class="bi bi-table"></i> Rekapitulasi Struktur Semua OPD
                </a>
            </div>
        </div>
        <div class="mb-3">
            <div class="btn-group">
                <button class="btn btn-primary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i> Cetak
                </button>
                <button class="btn btn-danger btn-sm" onclick="exportToPDF()">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </button>
            </div>
        </div>
        <div id="print-area">
            <div class="row">
                <div class="col-12">
                    @if($rekap->isNotEmpty())
                    <div class="mb-2">
                        <strong>SKPD:</strong> {{ $skpdTerpilih ? ($skpdTerpilih->kode_skpd . ' - ' . $skpdTerpilih->nama_skpd) : '-' }}<br>
                        <strong>Tahapan:</strong> {{ $tahapanTerpilih ? $tahapanTerpilih->name : '-' }}
                    </div>
                    <!-- Struktur Belanja OPD -->
                    <div class="mb-4 table-responsive" style="max-height: 80vh; overflow-y: auto;">
                        <h5 class="mb-2 text-primary">Struktur Belanja OPD : {{ $skpdTerpilih ? ($skpdTerpilih->kode_skpd . ' - ' . $skpdTerpilih->nama_skpd) : '-' }}</h5>
                        <table class="table mb-0 align-middle table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Kode Rekening</th>
                                    <th>Uraian</th>
                                    <th style="width: 120px;">Anggaran</th>
                                    <th style="width: 120px;">Realisasi</th>
                                    <th style="width: 140px;">Anggaran-Realisasi</th>
                                    <th style="width: 120px;">Penyesuaian</th>
                                    <th style="width: 140px;">Proyeksi Perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $sumTotalPagu = 0;
                                    $sumTotalPaguSetelah = 0;
                                    $sumTotalPenyesuaian = 0;
                                    $sumTotalRealisasi = 0;
                                    $sumTotalProyeksi = 0;
                                @endphp
                                @foreach($kodeRekenings as $kr)
                                @php
                                    $is3Segmen = count(explode('.', $kr->kode_rekening)) === 3;
                                    $totalPagu = $rekap->where(function($item) use ($kr) {
                                        return str_starts_with($item->kode_rekening, $kr->kode_rekening);
                                    })->sum('total_pagu');
                                    $totalPaguSetelah = 0;
                                    $matchingRekaps = $rekap->where(function($item) use ($kr) {
                                        return str_starts_with($item->kode_rekening, $kr->kode_rekening);
                                    });
                                    foreach ($matchingRekaps as $item) {
                                        $penyesuaian = $simulasiPenyesuaian->where('kode_rekening', $item->kode_rekening);
                                        $totalPenyesuaian = 0;
                                        foreach ($penyesuaian as $adj) {
                                            if ($adj->operasi == '+') {
                                                $totalPenyesuaian += $adj->nilai;
                                            } elseif ($adj->operasi == '-') {
                                                $totalPenyesuaian -= $adj->nilai;
                                            }
                                        }
                                        $totalPaguSetelah += $item->total_pagu + $totalPenyesuaian;
                                    }
                                    $selisih = $totalPaguSetelah - $totalPagu;
                                    $realisasiSegmen = $realisasiSegmenMap[$kr->kode_rekening] ?? 0;
                                    $anggaranRealisasi = $totalPagu - $realisasiSegmen;
                                    $proyeksiPerubahan = $anggaranRealisasi + $selisih;
                                    if ($is3Segmen) {
                                        $sumTotalPagu += $totalPagu;
                                        $sumTotalPaguSetelah += $totalPaguSetelah;
                                        $sumTotalPenyesuaian += $selisih;
                                        $sumTotalRealisasi += $realisasiSegmen;
                                        $sumTotalProyeksi += $proyeksiPerubahan;
                                    }
                                @endphp
                                <tr
                                    @if(count(explode('.', $kr->kode_rekening)) === 2)
                                        class="fw-bold bg-light text-dark"
                                    @endif
                                >
                                    <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $kr->kode_rekening }}</td>
                                    <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $kr->uraian }}">{{ \Illuminate\Support\Str::limit($kr->uraian, 50) }}</td>
                                    <td class="text-end">{{ $totalPagu ? number_format($totalPagu, 2, ',', '.') : '-' }}</td>
                                    <td class="text-end
                                        @if($realisasiSegmen == $totalPaguSetelah && $totalPaguSetelah > 0) bg-success bg-opacity-25
                                        @elseif($realisasiSegmen > $totalPaguSetelah && $totalPaguSetelah > 0) bg-danger bg-opacity-25
                                        @endif">
                                        {{ $realisasiSegmen ? number_format($realisasiSegmen, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end">{{ $anggaranRealisasi ? number_format($anggaranRealisasi, 2, ',', '.') : '-' }}</td>
                                    <td class="text-end">{{ $selisih ? number_format($selisih, 2, ',', '.') : '-' }}</td>
                                    <td class="text-end">{{ $proyeksiPerubahan ? number_format($proyeksiPerubahan, 2, ',', '.') : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th></th>
                                    <th></th>
                                    <th style="font-size:12px;" class="text-end">{{ number_format($sumTotalPagu, 2, ',', '.') }}</th>
                                    <th style="font-size:12px;" class="text-end">{{ number_format($sumTotalRealisasi, 2, ',', '.') }}</th>
                                    <th style="font-size:12px;" class="text-end">{{ number_format($sumTotalPagu - $sumTotalRealisasi, 2, ',', '.') }}</th>
                                    <th style="font-size:12px;" class="text-end">{{ number_format($sumTotalPenyesuaian, 2, ',', '.') }}</th>
                                    <th style="font-size:12px;" class="text-end">{{ number_format($sumTotalProyeksi, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Rekap Belanja OPD -->
                    <div class="mb-4 table-responsive" style="max-height: 80vh; overflow-y: auto;">
                        <h5 class="mb-2 text-primary">Rekap Belanja OPD : {{ $skpdTerpilih ? ($skpdTerpilih->kode_skpd . ' - ' . $skpdTerpilih->nama_skpd) : '-' }}</h5>
                        <table class="table align-middle table-sm table-bordered table-hover" id="rekapTable">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width:40px">No</th>
                                    <th style="width:120px">Kode Rekening</th>
                                    <th style="max-width: 180px;">Nama Rekening</th>
                                    <th style="width:120px">Anggaran</th>
                                    <th style="width:120px">Realisasi</th>
                                    <th style="width:140px">Angggaran-Realisasi</th>
                                    <th style="width:120px">Penyesuaian</th>
                                    <th style="width:140px">Proyeksi Perubahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekap as $i => $item)
                                @php
                                    $realisasi = $realisasiMap[$item->kode_rekening] ?? 0;
                                    $anggaranRealisasi = $item->total_pagu - $realisasi;
                                    $penyesuaian = $simulasiPenyesuaian->where('kode_rekening', $item->kode_rekening);
                                    $totalPenyesuaian = 0;
                                    foreach ($penyesuaian as $adj) {
                                        if ($adj->operasi == '+') {
                                            $totalPenyesuaian += $adj->nilai;
                                        } elseif ($adj->operasi == '-') {
                                            $totalPenyesuaian -= $adj->nilai;
                                        }
                                    }
                                    $proyeksiPerubahan = $anggaranRealisasi + $totalPenyesuaian;
                                @endphp
                                <tr>
                                    <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $i + 1 }}</td>
                                    <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->kode_rekening }}</td>
                                    <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="nama-rekening-compact" title="{{ $item->nama_rekening }}">{{ \Illuminate\Support\Str::limit($item->nama_rekening, 40) }}</td>
                                    <td class="text-end">{{ number_format($item->total_pagu, 2, ',', '.') }}</td>
                                    <td class="text-end
                                        @if($realisasi == $item->total_pagu && $item->total_pagu > 0) bg-success bg-opacity-25
                                        @elseif($realisasi > $item->total_pagu && $item->total_pagu > 0) bg-danger bg-opacity-25
                                        @endif">
                                        {{ $realisasi ? number_format($realisasi, 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="text-end">{{ number_format($anggaranRealisasi, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($totalPenyesuaian, 2, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($proyeksiPerubahan, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th></th>
                                    <th></th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end" style="font-size:12px;">
                                        {{ number_format($rekap->sum('total_pagu'), 2, ',', '.') }}
                                    </th>
                                    <th class="text-end" style="font-size:12px;">
                                        @php
                                            $totalRealisasi = 0;
                                            foreach ($rekap as $item) {
                                                $totalRealisasi += $realisasiMap[$item->kode_rekening] ?? 0;
                                            }
                                        @endphp
                                        {{ number_format($totalRealisasi, 2, ',', '.') }}
                                    </th>
                                    <th class="text-end" style="font-size:12px;">
                                        {{ number_format($rekap->sum('total_pagu') - $totalRealisasi, 2, ',', '.') }}
                                    </th>
                                    <th class="text-end" style="font-size:12px;">
                                        @php
                                            $totalPenyesuaianAll = 0;
                                            foreach ($rekap as $item) {
                                                $penyesuaian = $simulasiPenyesuaian->where('kode_rekening', $item->kode_rekening);
                                                foreach ($penyesuaian as $adj) {
                                                    if ($adj->operasi == '+') {
                                                        $totalPenyesuaianAll += $adj->nilai;
                                                    } elseif ($adj->operasi == '-') {
                                                        $totalPenyesuaianAll -= $adj->nilai;
                                                    }
                                                }
                                            }
                                        @endphp
                                        {{ number_format($totalPenyesuaianAll, 2, ',', '.') }}
                                    </th>
                                    <th class="text-end" style="font-size:12px;">
                                        @php
                                            $totalProyeksi = 0;
                                            foreach ($rekap as $item) {
                                                $realisasi = $realisasiMap[$item->kode_rekening] ?? 0;
                                                $anggaranRealisasi = $item->total_pagu - $realisasi;
                                                $penyesuaian = $simulasiPenyesuaian->where('kode_rekening', $item->kode_rekening);
                                                $totalPenyesuaian = 0;
                                                foreach ($penyesuaian as $adj) {
                                                    if ($adj->operasi == '+') {
                                                        $totalPenyesuaian += $adj->nilai;
                                                    } elseif ($adj->operasi == '-') {
                                                        $totalPenyesuaian -= $adj->nilai;
                                                    }
                                                }
                                                $totalProyeksi += ($anggaranRealisasi + $totalPenyesuaian);
                                            }
                                        @endphp
                                        {{ number_format($totalProyeksi, 2, ',', '.') }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- Data Simulasi Penyesuaian Anggaran -->
                    <div class="mt-3 card">
                        <div class="mb-4">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCreateSimulasi">+ Tambah</button>
                            </div>
                            <div class="table-responsive">
                                <h5 class="mb-0 text-primary">Data Simulasi Penyesuaian Anggaran</h5>
                                <table class="table mb-0 align-middle table-sm table-bordered table-striped">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 80px;">Kode OPD</th>
                                            <th style="width: 120px;">Kode Rekening</th>
                                            <th style="max-width: 180px;">Nama Rekening</th>
                                            <th style="width: 40px;">Op</th>
                                            <th style="width: 120px;">Nilai</th>
                                            <th>Keterangan</th>
                                            <th style="width: 80px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($simulasiPenyesuaian as $row)
                                        <tr>
                                            <td>{{ $row->kode_opd }}</td>
                                            <td>{{ $row->kode_rekening }}</td>
                                            <td>
                                                @php
                                                    $namaRek = optional($rekap->firstWhere('kode_rekening', $row->kode_rekening))->nama_rekening;
                                                @endphp
                                                {{ $namaRek ?? '-' }}
                                            </td>
                                            <td class="text-center">{{ $row->operasi }}</td>
                                            <td class="text-end">{{ number_format($row->nilai, 2, ',', '.') }}</td>
                                            <td style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $row->keterangan }}">{{ \Illuminate\Support\Str::limit($row->keterangan, 40) }}</td>
                                            <td>
                                                <button class="mb-1 btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditSimulasi{{ $row->id }}">Edit</button>
                                                <form action="{{ route('simulasi-penyesuaian-anggaran.destroy', $row->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="tahapan_id" value="{{ $tahapanId }}">
                                                    <input type="hidden" name="skpd" value="{{ $skpdKode }}">
                                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <!-- Modal Edit Simulasi Penyesuaian Anggaran -->
                                        <div class="modal fade" id="modalEditSimulasi{{ $row->id }}" tabindex="-1" aria-labelledby="modalEditSimulasiLabel{{ $row->id }}" aria-hidden="true" data-bs-backdrop="false">
                                          <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                              <form action="{{ route('simulasi-penyesuaian-anggaran.update', $row->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="tahapan_id" value="{{ $tahapanId }}">
                                                <input type="hidden" name="skpd" value="{{ $skpdKode }}">
                                                <div class="modal-header">
                                                  <h5 class="modal-title" id="modalEditSimulasiLabel{{ $row->id }}">Edit Simulasi Penyesuaian Anggaran</h5>
                                                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                  <div class="mb-2">
                                                    <label for="edit_kode_opd_{{ $row->id }}" class="form-label">Kode OPD</label>
                                                    <input type="text" class="form-control form-control-sm" id="edit_kode_opd_{{ $row->id }}" name="kode_opd" value="{{ $row->kode_opd }}" readonly>
                                                  </div>
                                                  <div class="mb-2">
                                                    <label for="edit_kode_rekening_{{ $row->id }}" class="form-label">Kode Rekening</label>
                                                    <select class="form-select form-select-sm select2-rekening-edit" id="edit_kode_rekening_{{ $row->id }}" name="kode_rekening" required style="width:100%">
                                                      <option value="">Pilih Kode Rekening</option>
                                                      @foreach($rekap as $item)
                                                        <option value="{{ $item->kode_rekening }}" {{ $row->kode_rekening == $item->kode_rekening ? 'selected' : '' }}>{{ $item->kode_rekening }} - {{ $item->nama_rekening }}</option>
                                                      @endforeach
                                                    </select>
                                                  </div>
                                                  <div class="mb-2">
                                                    <label for="edit_operasi_{{ $row->id }}" class="form-label">Operasi</label>
                                                    <select class="form-select form-select-sm" id="edit_operasi_{{ $row->id }}" name="operasi" required>
                                                      <option value="">Pilih Operasi</option>
                                                      <option value="+" {{ $row->operasi == '+' ? 'selected' : '' }}>+</option>
                                                      <option value="-" {{ $row->operasi == '-' ? 'selected' : '' }}>-</option>
                                                    </select>
                                                  </div>
                                                  <div class="mb-2">
                                                    <label for="edit_nilai_{{ $row->id }}" class="form-label">Nilai</label>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" id="edit_nilai_{{ $row->id }}" name="nilai" value="{{ $row->nilai }}" required>
                                                  </div>
                                                  <div class="mb-2">
                                                    <label for="edit_keterangan_{{ $row->id }}" class="form-label">Keterangan</label>
                                                    <textarea class="form-control form-control-sm" id="edit_keterangan_{{ $row->id }}" name="keterangan" rows="3">{{ $row->keterangan }}</textarea>
                                                  </div>
                                                </div>
                                                <div class="modal-footer">
                                                  <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                  <button type="submit" class="btn btn-success btn-sm">Simpan Perubahan</button>
                                                </div>
                                              </form>
                                            </div>
                                          </div>
                                        </div>
                                        @empty
                                        <tr><td colspan="7" class="text-center text-muted">Belum ada data simulasi penyesuaian anggaran.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                        <p>Silakan pilih tahapan dan/atau SKPD untuk melihat rekap data anggaran.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create Simulasi Penyesuaian Anggaran -->
<div class="modal fade" id="modalCreateSimulasi" tabindex="-1" aria-labelledby="modalCreateSimulasiLabel" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('simulasi-penyesuaian-anggaran.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tahapan_id" value="{{ $tahapanId }}">
        <input type="hidden" name="skpd" value="{{ $skpdKode }}">
        <div class="modal-header">
          <h5 class="modal-title" id="modalCreateSimulasiLabel">Tambah Simulasi Penyesuaian Anggaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="kode_opd" class="form-label">Kode OPD</label>
            <input type="text" class="form-control form-control-sm" id="kode_opd" name="kode_opd" value="{{ $skpdKode }}" readonly>
          </div>
          <div class="mb-3">
            <label for="kode_rekening" class="form-label">Kode Rekening</label>
            <select class="form-select form-select-sm select2-rekening" id="kode_rekening" name="kode_rekening" required>
              <option value="">Pilih Kode Rekening</option>
              @foreach($rekap as $item)
                <option value="{{ $item->kode_rekening }}">{{ $item->kode_rekening }} - {{ $item->nama_rekening }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="operasi" class="form-label">Operasi</label>
            <select class="form-select form-select-sm" id="operasi" name="operasi" required>
              <option value="">Pilih Operasi</option>
              <option value="+">+</option>
              <option value="-">-</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="nilai" class="form-label">Nilai</label>
            <input type="number" step="0.01" class="form-control form-control-sm" id="nilai" name="nilai" required>
          </div>
          <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <textarea class="form-control form-control-sm" id="keterangan" name="keterangan" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success btn-sm">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css" rel="stylesheet">
<style>



    #rekapTable th, #rekapTable td,
    .table-sm th, .table-sm td,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate,
    .dataTables_wrapper .dataTables_length label,
    .dataTables_wrapper .dataTables_filter label,
    .dataTables_wrapper .dataTables_filter input,
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_paginate a,
    .dataTables_wrapper .dataTables_paginate span {
        font-size: 10px !important;
    }

    /* Table styles for better PDF export */
    .table-responsive {
        margin-bottom: 1rem;
    }
    
    .table {
        width: 100% !important;
        margin-bottom: 0 !important;
    }
    
    .table th,
    .table td {
        white-space: nowrap;
        padding: 0.5rem !important;
        border: 1px solid #dee2e6;
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }
    
    .table tfoot th {
        background-color: #f8f9fa;
        border-top: 2px solid #dee2e6;
    }

    .nama-rekening-compact {
        font-size: 10px !important;
        max-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    /* Print styles */
    @media print {
        body * {
            visibility: hidden !important;
        }
        #print-area, #print-area * {
            visibility: visible !important;
        }
        #print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        .table {
            page-break-inside: avoid;
        }
        .table-responsive {
            overflow: visible !important;
        }
    }

    .select2-container {
        z-index: 9999 !important;
    }
    .select2-dropdown {
        z-index: 99999 !important;
    }
    .select2-container .select2-dropdown {
        background-color: #fff !important;
        color: #212529 !important;
        border: 1px solid #ced4da !important;
    }
    .select2-container .select2-results__option {
        color: #212529 !important;
        background-color: #fff !important;
    }
    .select2-container .select2-results__option--highlighted {
        background-color: #0d6efd !important;
        color: #fff !important;
    }
    .modal { overflow: visible !important; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
<script>
    $(document).ready(function() {
        // DataTable initialization
        // $('#rekapTable').DataTable({
        //     pageLength: 10,
        //     lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        //     order: [[1, 'asc']],
        //     language: {
        //         url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
        //     }
        // });

        // Inisialisasi Select2 setiap kali modal tambah dibuka
        $('#modalCreateSimulasi').on('shown.bs.modal', function () {
            var $select = $(this).find('.select2-rekening');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $('#modalCreateSimulasi'),
                placeholder: 'Pilih Kode Rekening',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Data tidak ditemukan";
                    }
                }
            });
            // Set background dan warna secara inline saat dropdown dibuka
            $select.on('select2:open', function() {
                setTimeout(function() {
                    $('.select2-dropdown').css({
                        'background-color': '#fff',
                        'color': '#212529',
                        'border': '1px solid #ced4da'
                    });
                    $('.select2-results__option').css({
                        'color': '#212529',
                        'background-color': '#fff'
                    });
                    $('.select2-results__option--highlighted').css({
                        'background-color': '#fff',
                        'color': '#fff'
                    });
                }, 0);
            });
        });

        // Inisialisasi Select2 untuk semua select edit saat modal dibuka
        $(document).on('shown.bs.modal', '.modal', function () {
            $(this).find('.select2-rekening-edit').select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $(this),
                placeholder: 'Pilih Kode Rekening',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "Data tidak ditemukan";
                    }
                }
            });
        });

        // Destroy Select2 saat modal tambah ditutup
        $('#modalCreateSimulasi').on('hidden.bs.modal', function () {
            var $select = $(this).find('.select2-rekening');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
        });
    });

    // Fungsi untuk export ke PDF (dijamin global)
    window.exportToPDF = async function() {
    try {
        // Tampilkan semua data di DataTables
        $('.dataTable').each(function() {
            $(this).DataTable().page.len(-1).draw();
        });

        // Tampilkan loading
        const loadingDiv = $('<div>')
            .addClass('position-fixed top-50 start-50 translate-middle')
            .css({
                'z-index': '9999',
                'background': 'rgba(255,255,255,0.8)',
                'padding': '20px',
                'border-radius': '5px',
                'box-shadow': '0 0 10px rgba(0,0,0,0.1)'
            })
            .html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><div class="mt-2">Mempersiapkan PDF...</div>');
        $('body').append(loadingDiv);

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const margin = 10;
        let firstTable = true;

        // Ambil semua tabel di halaman
        const tables = document.querySelectorAll('table');

        for (let i = 0; i < tables.length; i++) {
            const table = tables[i];

            // Ambil judul tabel dari <h5> terdekat di atas <table> (abaikan komentar/whitespace)
            let title = '';
            let prev = table.previousSibling;
            while (prev) {
                if (prev.nodeType === 1 && prev.tagName && prev.tagName.toUpperCase() === 'H5') {
                    title = prev.innerText.trim();
                    break;
                }
                prev = prev.previousSibling;
            }
            if (!title) title = 'Tabel ' + (i + 1);

            // Ambil header dan data
            const headers = [];
            table.querySelectorAll('thead tr th').forEach(th => {
                headers.push(th.innerText.trim());
            });

            const body = [];
            table.querySelectorAll('tbody tr').forEach(tr => {
                const row = [];
                tr.querySelectorAll('td').forEach(td => {
                    row.push(td.innerText.trim());
                });
                if (row.length) body.push(row);
            });

            // Ambil footer jika ada
            let foot = [];
            const tfoot = table.querySelector('tfoot');
            if (tfoot) {
                tfoot.querySelectorAll('tr').forEach(tr => {
                    const row = [];
                    // Pastikan jumlah kolom sama dengan header
                    let colCount = headers.length;
                    let cells = tr.querySelectorAll('th,td');
                    for (let i = 0; i < colCount; i++) {
                        row[i] = cells[i] ? cells[i].innerText.trim() : '';
                    }
                    foot.push(row);
                });
            }

            // Tambahkan judul tabel
            if (!firstTable) pdf.addPage();
            pdf.setFontSize(12);
            pdf.text(title, margin, 18);

            // Render tabel dengan autotable
            pdf.autoTable({
                startY: 22,
                head: [headers],
                body: body,
                foot: foot,
                margin: { left: margin, right: margin },
                styles: { fontSize: 7, cellPadding: 1.5 },
                headStyles: { fillColor: [41, 128, 185], textColor: 255 },
                theme: 'grid',
                showHead: 'everyPage', // header tetap di setiap halaman
                showFoot: 'lastPage',  // Footer hanya di halaman terakhir
                didDrawPage: function (data) {
                    if (data.pageNumber === 1) {
                        pdf.setFontSize(12);
                        pdf.text(title, margin, 18);
                    } else {
                        // Tambahkan margin atas pada halaman lanjutan
                        data.settings.margin.top = 16; // margin atas 16mm di halaman lanjutan
                    }
                }
            });

            firstTable = false;
        }

        pdf.save('tabel-simulasi-anggaran.pdf');
        loadingDiv.remove();
    } catch (error) {
        alert('Terjadi kesalahan saat membuat PDF: ' + error.message);
        if (typeof loadingDiv !== 'undefined') loadingDiv.remove();
    }
}
</script>
@endpush
@endsection 