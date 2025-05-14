@extends('layouts.app')

@section('title', 'Daftar Kode Rekening')
@section('page-title', 'Daftar Kode Rekening')

@section('content')
<div class="card" data-aos="fade-up" data-aos-delay="300">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Daftar Kode Rekening</h4>
        <div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-excel"></i> Import Excel
            </button>
            <a href="{{ route('kode-rekening.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Tambah Kode Rekening
            </a>
        </div>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="kodeRekeningTable">
                <thead class="table-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Kode Rekening</th>
                        <th width="50%">Uraian</th>
                        <th width="20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kodeRekenings as $index => $kodeRekening)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $kodeRekening->kode_rekening }}</td>
                        <td>{{ $kodeRekening->uraian }}</td>
                        <td class="text-center">
                            <a href="{{ route('kode-rekening.show', $kodeRekening->id) }}" class="btn btn-info btn-sm" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('kode-rekening.edit', $kodeRekening->id) }}" class="btn btn-warning btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('kode-rekening.destroy', $kodeRekening->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dt-buttons {
        margin-bottom: 10px;
    }
    .btn-group-sm > .btn, .btn-sm {
        margin: 0 2px;
    }
    .table td, .table th {
        vertical-align: middle;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
    $(document).ready(function() {
        $('#kodeRekeningTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-5'i><'col-sm-7'p>>" +
                 "<'row'<'col-sm-12'B>>",
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
            },
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class=\"fas fa-file-excel\"></i> Export Excel',
                    className: 'btn btn-success btn-sm mt-2',
                    title: 'Daftar Kode Rekening',
                    exportOptions: {
                        columns: [0, 1, 2]
                    }
                },
                {
                    extend: 'pdf',
                    text: '<i class=\"fas fa-file-pdf\"></i> Export PDF',
                    className: 'btn btn-danger btn-sm mt-2',
                    title: 'Daftar Kode Rekening',
                    orientation: 'portrait',
                    pageSize: 'A4',
                    exportOptions: {
                        columns: [0, 1, 2]
                    }
                },
                {
                    extend: 'print',
                    text: '<i class=\"fas fa-print\"></i> Print',
                    className: 'btn btn-info btn-sm mt-2',
                    title: 'Daftar Kode Rekening',
                    exportOptions: {
                        columns: [0, 1, 2]
                    }
                }
            ],
            order: [[1, 'asc']]
        });
    });
</script>
@endpush
@endsection

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Kode Rekening</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('kode-rekening.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="file" class="form-label">Pilih File Excel</label>
                        <input type="file" class="form-control" id="file" name="file" required accept=".xlsx, .xls, .csv">
                    </div>
                    <div class="alert alert-info">
                        <p><strong>Petunjuk:</strong></p>
                        <ul>
                            <li>File harus dalam format Excel (.xlsx, .xls) atau CSV (.csv)</li>
                            <li>File harus memiliki header kolom: <strong>kode</strong> dan <strong>uraian</strong></li>
                            <li>Jika kode rekening sudah ada, data akan diperbarui</li>
                            <li>Jika kode rekening belum ada, data baru akan ditambahkan</li>
                        </ul>
                        <a href="{{ route('kode-rekening.template.download') }}" class="mt-2 btn btn-sm btn-info">
                            <i class="fas fa-download"></i> Download Template Excel
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div> 