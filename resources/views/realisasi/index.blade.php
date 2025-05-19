@extends('layouts.app')

@section('title', 'Realisasi')
@section('page-title', 'Realisasi')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Data Realisasi</h4>
        <div class="gap-2 d-flex">
            <form method="GET" action="" class="flex-wrap gap-2 d-flex align-items-center">
                <select name="periode" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <option value="">Pilih Periode</option>
                    @foreach($periods as $period)
                        <option value="{{ substr($period, 0, 7) }}" {{ request('periode') == substr($period, 0, 7) ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromFormat('Y-m', substr($period, 0, 7))->format('F Y') }}
                        </option>
                    @endforeach
                </select>
                <select name="kode_opd" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <option value="">Pilih OPD</option>
                    @foreach($opds as $opd)
                        <option value="{{ $opd->kode_skpd }}" {{ request('kode_opd') == $opd->kode_skpd ? 'selected' : '' }}>
                            {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                        </option>
                    @endforeach
                </select>
            </form>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-upload"></i> Upload Excel
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="bi bi-plus-circle"></i> Tambah Data
            </button>
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal">
                <i class="bi bi-trash"></i> Hapus Massal
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            @if(!request('periode') || !request('kode_opd'))
                <div class="alert alert-info">
                    Silakan pilih periode dan OPD untuk melihat data realisasi.
                </div>
            @else
            <table class="table align-middle table-sm table-bordered table-striped" style="font-size: 0.8rem;">
                <thead class="table-primary">
                    <tr>
                        <th style="width:40px">No</th>
                        <th>Kode Rekening</th>
                        <th style="max-width: 200px;">Uraian</th>
                        <th class="text-end">Anggaran</th>
                        <th class="text-end">Realisasi</th>
                        <th class="text-end">Persentase</th>
                        <th class="text-end">Realisasi LY</th>
                        <th style="width:100px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($realisasis as $i => $realisasi)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $realisasi->kode_rekening }}</td>
                        <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $realisasi->uraian }}">{{ $realisasi->uraian }}</td>
                        <td class="text-end">{{ number_format($realisasi->anggaran, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($realisasi->realisasi, 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($realisasi->persentase, 2, ',', '.') }}%</td>
                        <td class="text-end">{{ number_format($realisasi->realisasi_ly, 2, ',', '.') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $realisasi->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('realisasi.destroy', $realisasi) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                    </tr>
                    @endforelse
                    @if($realisasis->count() > 0)
                    <tr class="table-primary fw-bold">
                        <td colspan="3" class="text-center">TOTAL</td>
                        <td class="text-end">{{ number_format($realisasis->sum('anggaran'), 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($realisasis->sum('realisasi'), 2, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($realisasis->avg('persentase'), 2, ',', '.') }}%</td>
                        <td class="text-end">{{ number_format($realisasis->sum('realisasi_ly'), 2, ',', '.') }}</td>
                        <td></td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">Tambah Data Realisasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('realisasi.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_opd" class="form-label">Kode OPD</label>
                        <select class="form-select @error('kode_opd') is-invalid @enderror" 
                                id="kode_opd" name="kode_opd" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->kode_skpd }}" {{ old('kode_opd') == $opd->kode_skpd ? 'selected' : '' }}>
                                    {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                                </option>
                            @endforeach
                        </select>
                        @error('kode_opd')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode</label>
                        <input type="date" class="form-control @error('periode') is-invalid @enderror" 
                               id="periode" name="periode" value="{{ old('periode') }}" required>
                        @error('periode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="kode_rekening" class="form-label">Kode Rekening</label>
                        <input type="text" class="form-control @error('kode_rekening') is-invalid @enderror" 
                               id="kode_rekening" name="kode_rekening" value="{{ old('kode_rekening') }}" required>
                        @error('kode_rekening')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="uraian" class="form-label">Uraian</label>
                        <textarea class="form-control @error('uraian') is-invalid @enderror" 
                                  id="uraian" name="uraian" rows="2" required>{{ old('uraian') }}</textarea>
                        @error('uraian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="anggaran" class="form-label">Anggaran</label>
                        <input type="number" step="0.01" class="form-control @error('anggaran') is-invalid @enderror" 
                               id="anggaran" name="anggaran" value="{{ old('anggaran') }}" required>
                        @error('anggaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="realisasi" class="form-label">Realisasi</label>
                        <input type="number" step="0.01" class="form-control @error('realisasi') is-invalid @enderror" 
                               id="realisasi" name="realisasi" value="{{ old('realisasi') }}" required>
                        @error('realisasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="persentase" class="form-label">Persentase</label>
                        <input type="number" step="0.01" class="form-control @error('persentase') is-invalid @enderror" 
                               id="persentase" name="persentase" value="{{ old('persentase') }}" required>
                        @error('persentase')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="realisasi_ly" class="form-label">Realisasi LY</label>
                        <input type="number" step="0.01" class="form-control @error('realisasi_ly') is-invalid @enderror" 
                               id="realisasi_ly" name="realisasi_ly" value="{{ old('realisasi_ly') }}" required>
                        @error('realisasi_ly')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modals -->
@foreach($realisasis as $realisasi)
<div class="modal fade" id="editModal{{ $realisasi->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $realisasi->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel{{ $realisasi->id }}">Edit Data Realisasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('realisasi.update', $realisasi) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_kode_opd{{ $realisasi->id }}" class="form-label">Kode OPD</label>
                        <select class="form-select @error('kode_opd') is-invalid @enderror" 
                                id="edit_kode_opd{{ $realisasi->id }}" name="kode_opd" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->kode_skpd }}" {{ $realisasi->kode_opd == $opd->kode_skpd ? 'selected' : '' }}>
                                    {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                                </option>
                            @endforeach
                        </select>
                        @error('kode_opd')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_periode{{ $realisasi->id }}" class="form-label">Periode</label>
                        <input type="date" class="form-control @error('periode') is-invalid @enderror" 
                               id="edit_periode{{ $realisasi->id }}" name="periode" 
                               value="{{ $realisasi->periode->format('Y-m-d') }}" required>
                        @error('periode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_kode_rekening{{ $realisasi->id }}" class="form-label">Kode Rekening</label>
                        <input type="text" class="form-control @error('kode_rekening') is-invalid @enderror" 
                               id="edit_kode_rekening{{ $realisasi->id }}" name="kode_rekening" 
                               value="{{ $realisasi->kode_rekening }}" required>
                        @error('kode_rekening')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_uraian{{ $realisasi->id }}" class="form-label">Uraian</label>
                        <textarea class="form-control @error('uraian') is-invalid @enderror" 
                                  id="edit_uraian{{ $realisasi->id }}" name="uraian" rows="2" required>{{ $realisasi->uraian }}</textarea>
                        @error('uraian')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_anggaran{{ $realisasi->id }}" class="form-label">Anggaran</label>
                        <input type="number" step="0.01" class="form-control @error('anggaran') is-invalid @enderror" 
                               id="edit_anggaran{{ $realisasi->id }}" name="anggaran" 
                               value="{{ $realisasi->anggaran }}" required>
                        @error('anggaran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_realisasi{{ $realisasi->id }}" class="form-label">Realisasi</label>
                        <input type="number" step="0.01" class="form-control @error('realisasi') is-invalid @enderror" 
                               id="edit_realisasi{{ $realisasi->id }}" name="realisasi" 
                               value="{{ $realisasi->realisasi }}" required>
                        @error('realisasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_persentase{{ $realisasi->id }}" class="form-label">Persentase</label>
                        <input type="number" step="0.01" class="form-control @error('persentase') is-invalid @enderror" 
                               id="edit_persentase{{ $realisasi->id }}" name="persentase" 
                               value="{{ $realisasi->persentase }}" required>
                        @error('persentase')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="edit_realisasi_ly{{ $realisasi->id }}" class="form-label">Realisasi LY</label>
                        <input type="number" step="0.01" class="form-control @error('realisasi_ly') is-invalid @enderror" 
                               id="edit_realisasi_ly{{ $realisasi->id }}" name="realisasi_ly" 
                               value="{{ $realisasi->realisasi_ly }}" required>
                        @error('realisasi_ly')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadModalLabel">Upload Data Realisasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('realisasi.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="kode_opd" class="form-label">Kode OPD</label>
                        <select class="form-select @error('kode_opd') is-invalid @enderror" 
                                id="kode_opd" name="kode_opd" required>
                            <option value="">Pilih OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->kode_skpd }}" {{ old('kode_opd') == $opd->kode_skpd ? 'selected' : '' }}>
                                    {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                                </option>
                            @endforeach
                        </select>
                        @error('kode_opd')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="periode" class="form-label">Periode</label>
                        <input type="date" class="form-control @error('periode') is-invalid @enderror" 
                               id="periode" name="periode" value="{{ old('periode') }}" required>
                        @error('periode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" 
                               id="file" name="file" accept=".xlsx,.xls" required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Format file harus Excel (.xlsx atau .xls). Kolom yang harus ada: kode rekening, uraian, anggaran, realisasi, %, realisasi ly
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">Hapus Data Massal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('realisasi.bulk-delete') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulk_kode_opd" class="form-label">Kode OPD</label>
                        <select class="form-select" id="bulk_kode_opd" name="kode_opd">
                            <option value="">Semua OPD</option>
                            @foreach($opds as $opd)
                                <option value="{{ $opd->kode_skpd }}">
                                    {{ $opd->kode_skpd }} - {{ $opd->nama_skpd }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulk_periode" class="form-label">Periode</label>
                        <input type="month" class="form-control" id="bulk_periode" name="periode">
                    </div>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Perhatian: Tindakan ini akan menghapus semua data yang sesuai dengan kriteria yang dipilih.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data yang sesuai dengan kriteria yang dipilih?')">
                        Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');

    // Handle select all checkbox
    selectAll.addEventListener('change', function() {
        itemCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // Handle individual checkboxes
    itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
            // Update select all checkbox
            selectAll.checked = Array.from(itemCheckboxes).every(cb => cb.checked);
        });
    });

    // Update bulk delete button visibility
    function updateBulkDeleteButton() {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        bulkDeleteBtn.style.display = checkedBoxes.length > 0 ? 'inline-block' : 'none';
    }

    // Handle bulk delete
    bulkDeleteBtn.addEventListener('click', function() {
        if (confirm('Apakah Anda yakin ingin menghapus data yang dipilih?')) {
            bulkDeleteForm.submit();
        }
    });
});
</script>
@endpush 