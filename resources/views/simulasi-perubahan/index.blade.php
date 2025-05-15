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
                <div class="mb-3 col-12 col-md-6 mb-md-0">
                    @if($rekap->isNotEmpty())
                    <div class="mb-2">
                        <strong>SKPD:</strong> {{ $skpdTerpilih ? ($skpdTerpilih->kode_skpd . ' - ' . $skpdTerpilih->nama_skpd) : '-' }}<br>
                        <strong>Tahapan:</strong> {{ $tahapanTerpilih ? $tahapanTerpilih->name : '-' }}
                    </div>
                    <div class="table-responsive" style="max-height: 80vh; overflow-y: auto;">
                        <table class="table align-middle table-sm table-bordered table-striped table-hover" id="rekapTable">
                            <thead class="table-primary">
                                <tr>
                                    <th style="font-size:12px; width:40px">No</th>
                                    <th style="font-size:12px; width:120px">Kode Rekening</th>
                                    <th style="font-size:12px; max-width: 180px;">Nama Rekening</th>
                                    <th style="font-size:12px; width:120px">Total Pagu</th>
                                    <th style="font-size:12px; width:140px">Pagu Setelah Penyesuaian</th>
                                    <th style="font-size:12px; width:120px">Penyesuaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekap as $i => $item)
                                <tr>
                                    <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $i + 1 }}</td>
                                    <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->kode_rekening }}</td>
                                    <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="nama-rekening-compact" title="{{ $item->nama_rekening }}">{{ \Illuminate\Support\Str::limit($item->nama_rekening, 40) }}</td>
                                    <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="text-end">{{ number_format($item->total_pagu, 2, ',', '.') }}</td>
                                    <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="text-end">
                                        @php
                                            $penyesuaian = $simulasiPenyesuaian->where('kode_rekening', $item->kode_rekening);
                                            $totalPenyesuaian = 0;
                                            foreach ($penyesuaian as $adj) {
                                                if ($adj->operasi == '+') {
                                                    $totalPenyesuaian += $adj->nilai;
                                                } elseif ($adj->operasi == '-') {
                                                    $totalPenyesuaian -= $adj->nilai;
                                                }
                                            }
                                            $paguSetelah = $item->total_pagu + $totalPenyesuaian;
                                        @endphp
                                        {{ number_format($paguSetelah, 2, ',', '.') }}
                                    </td>
                                    <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="text-end">
                                        {{ number_format($totalPenyesuaian, 2, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <th colspan="3" class="text-end">Total</th>
                                    <th class="text-end" style="font-size:12px;">
                                        {{ number_format($rekap->sum('total_pagu'), 2, ',', '.') }}
                                    </th>
                                    <th class="text-end" style="font-size:12px;">
                                        @php
                                            $totalPaguSetelah = 0;
                                            foreach ($rekap as $item) {
                                                $penyesuaian = $simulasiPenyesuaian->where('kode_rekening', $item->kode_rekening);
                                                $totalPenyesuaian = 0;
                                                foreach ($penyesuaian as $adj) {
                                                    if ($adj->operasi == '+') {
                                                        $totalPenyesuaian += $adj->nilai;
                                                    } elseif ($adj->operasi == '-') {
                                                        $totalPenyesuaian -= $adj->nilai;
                                                    }
                                                }
                                                $totalPaguSetelah += ($item->total_pagu + $totalPenyesuaian);
                                            }
                                        @endphp
                                        {{ number_format($totalPaguSetelah, 2, ',', '.') }}
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
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @else
                        <p>Silakan pilih tahapan dan/atau SKPD untuk melihat rekap data anggaran.</p>
                    @endif
                </div>
                <div class="col-12 col-md-6">
                    <div class="h-100 d-flex flex-column" style="min-height: 300px; max-height: 80vh;">
                        <div class="mb-2 text-center">
                            <h5 class="mb-2 text-primary">Struktur Belanja OPD</h5>
                        </div>
                        <div class="table-responsive flex-grow-1" style="max-height: 70vh; overflow-y: auto;">
                            <table class="table mb-0 align-middle table-sm table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th style="font-size:12px; width: 120px;">Kode Rekening</th>
                                        <th style="font-size:12px;">Uraian</th>
                                        <th style="font-size:12px; width: 120px;">Total Pagu Belanja</th>
                                        <th style="font-size:12px; width: 140px;">Pagu Setelah Penyesuaian</th>
                                        <th style="font-size:12px; width: 120px;">Penyesuaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $sumTotalPagu = 0;
                                        $sumTotalPaguSetelah = 0;
                                        $sumTotalPenyesuaian = 0;
                                    @endphp
                                    @foreach($kodeRekenings as $kr)
                                    @php
                                        // Total pagu asli
                                        $totalPagu = $rekap->where(function($item) use ($kr) {
                                            return str_starts_with($item->kode_rekening, $kr->kode_rekening);
                                        })->sum('total_pagu');

                                        // Total pagu setelah penyesuaian
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
                                        $sumTotalPagu += $totalPagu;
                                        $sumTotalPaguSetelah += $totalPaguSetelah;
                                        $sumTotalPenyesuaian += $selisih;
                                    @endphp
                                    <tr>
                                        <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $kr->kode_rekening }}</td>
                                        <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $kr->uraian }}">{{ \Illuminate\Support\Str::limit($kr->uraian, 50) }}</td>
                                        <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="text-end">{{ $totalPagu ? number_format($totalPagu, 2, ',', '.') : '-' }}</td>
                                        <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="text-end">{{ $totalPaguSetelah ? number_format($totalPaguSetelah, 2, ',', '.') : '-' }}</td>
                                        <td style="font-size:12px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" class="text-end">{{ $selisih ? number_format($selisih, 2, ',', '.') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-secondary">
                                        <th colspan="2" class="text-end">Total</th>
                                        <th style="font-size:12px;" class="text-end">
                                            @php
                                                $sumTotalPagu3Segmen = 0;
                                                foreach ($kodeRekenings as $kr) {
                                                    if (count(explode('.', $kr->kode_rekening)) === 3) {
                                                        $sumTotalPagu3Segmen += $rekap->where(function($item) use ($kr) {
                                                            return str_starts_with($item->kode_rekening, $kr->kode_rekening);
                                                        })->sum('total_pagu');
                                                    }
                                                }
                                            @endphp
                                            {{ number_format($sumTotalPagu3Segmen, 2, ',', '.') }}
                                        </th>
                                        <th style="font-size:12px;" class="text-end">
                                            @php
                                                $sumTotalPaguSetelah3Segmen = 0;
                                                foreach ($kodeRekenings as $kr) {
                                                    if (count(explode('.', $kr->kode_rekening)) === 3) {
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
                                                        $sumTotalPaguSetelah3Segmen += $totalPaguSetelah;
                                                    }
                                                }
                                            @endphp
                                            {{ number_format($sumTotalPaguSetelah3Segmen, 2, ',', '.') }}
                                        </th>
                                        <th style="font-size:12px;" class="text-end">
                                            @php
                                                $sumTotalPenyesuaian3Segmen = 0;
                                                foreach ($kodeRekenings as $kr) {
                                                    if (count(explode('.', $kr->kode_rekening)) === 3) {
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
                                                        $sumTotalPenyesuaian3Segmen += $selisih;
                                                    }
                                                }
                                            @endphp
                                            {{ number_format($sumTotalPenyesuaian3Segmen, 2, ',', '.') }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="mt-3 card">
                        

                        <div class="mb-4">
                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 text-primary">Data Simulasi Penyesuaian Anggaran</h5>
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalCreateSimulasi">+ Tambah</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table mb-0 align-middle table-sm table-bordered table-striped" style="font-size:12px;">
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
                                            <td class="text-center" style="font-size:16px;">{{ $row->operasi }}</td>
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
                                        <!-- End Modal Edit -->

                                        @empty
                                        <tr><td colspan="7" class="text-center text-muted">Belum ada data simulasi penyesuaian anggaran.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
            
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
          <div class="mb-2">
            <label for="kode_opd" class="form-label">Kode OPD</label>
            <input type="text" class="form-control form-control-sm" id="kode_opd" name="kode_opd" value="{{ $skpdKode }}" readonly>
          </div>
          <div class="mb-2">
            <label for="kode_rekening" class="form-label">Kode Rekening</label>
            <select class="form-select form-select-sm select2-rekening" id="kode_rekening" name="kode_rekening" required style="width:100%">
              <option value="">Pilih Kode Rekening</option>
              @foreach($rekap as $item)
                <option value="{{ $item->kode_rekening }}">{{ $item->kode_rekening }} - {{ $item->nama_rekening }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-2">
            <label for="operasi" class="form-label">Operasi</label>
            <select class="form-select form-select-sm" id="operasi" name="operasi" required>
              <option value="">Pilih Operasi</option>
              <option value="+">+</option>
              <option value="-">-</option>
            </select>
          </div>
          <div class="mb-2">
            <label for="nilai" class="form-label">Nilai</label>
            <input type="number" step="0.01" class="form-control form-control-sm" id="nilai" name="nilai" required>
          </div>
          <div class="mb-2">
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
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    $(document).ready(function() {
        // DataTable initialization
        $('#rekapTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            order: [[1, 'asc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
            }
        });

        // Inisialisasi Select2 setiap kali modal dibuka
        $('#modalCreateSimulasi').on('shown.bs.modal', function () {
            if ($('.select2-rekening').hasClass('select2-hidden-accessible')) {
                $('.select2-rekening').select2('destroy');
            }
            $('.select2-rekening').select2({
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
    });

    // Fungsi untuk export ke PDF
    function exportToPDF() {
        // Sembunyikan elemen yang tidak perlu
        const printArea = document.getElementById('print-area');
        const originalDisplay = {};
        const elementsToHide = printArea.querySelectorAll('.btn, .modal, .modal-backdrop, nav, aside, .navbar, .sidebar, .footer');
        
        elementsToHide.forEach(el => {
            originalDisplay[el.id] = el.style.display;
            el.style.display = 'none';
        });

        // Konfigurasi PDF
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4');
        const pageWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();
        const margin = 10;

        // Fungsi untuk menambahkan tabel ke PDF
        const addTableToPDF = async (tableElement, yPosition) => {
            const canvas = await html2canvas(tableElement, {
                scale: 2,
                useCORS: true,
                logging: false,
                windowWidth: tableElement.scrollWidth,
                windowHeight: tableElement.scrollHeight
            });

            const imgData = canvas.toDataURL('image/png');
            const imgWidth = pageWidth - (2 * margin);
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            // Jika tabel terlalu panjang, buat halaman baru
            if (yPosition + imgHeight > pageHeight - margin) {
                pdf.addPage();
                yPosition = margin;
            }

            pdf.addImage(imgData, 'PNG', margin, yPosition, imgWidth, imgHeight);
            return yPosition + imgHeight + 10;
        };

        // Export setiap tabel
        (async () => {
            let yPosition = margin;
            
            // Tambahkan judul
            pdf.setFontSize(14);
            pdf.text('Simulasi Perubahan Anggaran', pageWidth/2, yPosition, { align: 'center' });
            yPosition += 10;

            // Tambahkan informasi SKPD dan Tahapan
            pdf.setFontSize(10);
            const skpdInfo = document.querySelector('#print-area .mb-2').textContent;
            pdf.text(skpdInfo, margin, yPosition);
            yPosition += 15;

            // Export tabel rekap
            const rekapTable = document.querySelector('#rekapTable').closest('.table-responsive');
            yPosition = await addTableToPDF(rekapTable, yPosition);

            // Export tabel struktur belanja
            const strukturTable = document.querySelector('.col-md-6:last-child .table-responsive');
            yPosition = await addTableToPDF(strukturTable, yPosition);

            // Export tabel simulasi penyesuaian
            const simulasiTable = document.querySelector('.col-12:last-child .table-responsive');
            yPosition = await addTableToPDF(simulasiTable, yPosition);

            // Simpan PDF
            pdf.save('simulasi-perubahan-anggaran.pdf');

            // Kembalikan tampilan elemen yang disembunyikan
            elementsToHide.forEach(el => {
                el.style.display = originalDisplay[el.id];
            });
        })();
    }
</script>
@endpush
@endsection 